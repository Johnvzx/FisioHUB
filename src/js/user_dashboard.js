    (function injectLoaderStyles(){
      if (!document.getElementById('loader-styles')) {
        const style = document.createElement('style');
        style.id = 'loader-styles';
        style.textContent = `
          .global-loader { position:fixed; inset:0; background:rgba(255,255,255,.9); backdrop-filter:saturate(1.2) blur(2px); display:flex; align-items:center; justify-content:center; z-index:9999; }
          .global-loader .center { display:flex; flex-direction:column; align-items:center; gap:12px; }
          .global-loader .spinner { width:56px; height:56px; border-radius:50%; border:4px solid #e5e7eb; border-top-color:#667eea; animation:spin 1s linear infinite; }
          .global-loader .msg { color:#4a5568; font-weight:600; }
          @keyframes spin { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }
        `;
        document.head.appendChild(style);
      }
    })();
    console.log('🚀 Dashboard do usuário carregado');

    async function loadAppointments() {
      console.log('📡 Buscando consultas...');
      const container = document.getElementById('appointments-container');
      const loader = document.getElementById('global-loader');

      try {
        const res = await fetch('/src/user_api.php');
        const data = await res.json();
        
        console.log('📦 Dados recebidos:', data);

        if (!data.ok) {
          throw new Error(data.message || 'Erro ao carregar dados');
        }

  const appointments = data.appointments || [];
        
  // Atualizar stats
  const upcomingCount = appointments.filter(a => a.status !== 'completed' && a.status !== 'canceled').length;
  const completedCount = appointments.filter(a => a.status === 'completed').length;
  document.getElementById('stat-total').textContent = appointments.length;
  document.getElementById('stat-upcoming').textContent = upcomingCount;
  document.getElementById('stat-completed').textContent = completedCount;

        if (appointments.length === 0) {
          container.innerHTML = `
            <div class="empty-state">
              <div class="empty-state-icon">📅</div>
              <h3>Nenhuma consulta agendada</h3>
              <p>Você ainda não tem consultas marcadas.</p>
              <a href="/src/agendar.html" class="btn btn-primary" style="display: inline-block; margin-top: 20px;">
                Agendar Primeira Consulta
              </a>
            </div>
          `;
        } else {
          container.innerHTML = '<div class="appointments-list"></div>';
          const list = container.querySelector('.appointments-list');

          const statusMap = {
            'pending': { cls: 'status-pending', txt: 'Pendente' },
            'confirmed': { cls: 'status-confirmed', txt: 'Confirmada' },
            'in_progress': { cls: 'status-in-progress', txt: 'Em andamento' },
            'completed': { cls: 'status-completed', txt: 'Concluída' },
            'canceled': { cls: 'status-canceled', txt: 'Cancelada' }
          };

          appointments.forEach(appt => {
            const item = document.createElement('div');
            item.className = 'appointment-item';
            
            const st = statusMap[appt.status] || statusMap['pending'];
            const statusClass = st.cls;
            const statusText = st.txt;
            
            item.innerHTML = `
              <div class="appointment-header">
                <div class="appointment-service">${escapeHtml(appt.service || 'Consulta')}</div>
                <div class="appointment-status ${statusClass}">${statusText}</div>
              </div>
              <div class="appointment-details">
                <div class="detail-item">
                  <span class="detail-icon">👨‍⚕️</span>
                  <div>
                    <div class="detail-label">Profissional</div>
                    <div class="detail-value">${escapeHtml(appt.professional_name || 'A confirmar')}</div>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">📅</span>
                  <div>
                    <div class="detail-label">Data</div>
                    <div class="detail-value">${formatDate(appt.appointment_date || appt.created_at)}</div>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">⏰</span>
                  <div>
                    <div class="detail-label">Horário</div>
                    <div class="detail-value">${appt.appointment_time || 'A confirmar'}</div>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">💰</span>
                  <div>
                    <div class="detail-label">Valor</div>
                    <div class="detail-value">${formatPrice(appt.price || 0)}</div>
                  </div>
                </div>
              </div>
              ${appt.message ? `
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                  <div class="detail-label">Observações:</div>
                  <div style="margin-top: 5px; color: #4a5568;">${escapeHtml(appt.message)}</div>
                </div>
              ` : ''}
            `;
            
            list.appendChild(item);
          });
        }

      } catch (err) {
        console.error('❌ Erro:', err);
        container.innerHTML = `
          <div class="empty-state">
            <div class="empty-state-icon">⚠️</div>
            <h3>Erro ao carregar consultas</h3>
            <p>${err.message}</p>
            <button onclick="loadAppointments()" class="btn btn-primary" style="margin-top: 20px;">
              Tentar Novamente
            </button>
          </div>
        `;
      }
      if (loader) { loader.style.opacity='0'; setTimeout(()=>{ loader.style.display='none'; },400); }
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function formatDate(dateStr) {
      if (!dateStr) return 'Data a confirmar';
      const date = new Date(dateStr);
      return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' });
    }

    function formatPrice(price) {
      if (!price || price === 0) return 'A consultar';
      return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
    }

    // Keep-alive: mantém sessão ativa enquanto aba estiver aberta
    let keepAliveInterval;
    let expectedRole = 'user';
    let currentUserId = null;

    function startKeepAlive() {
      // Enviar keep-alive imediatamente
      sendKeepAlive();
      // Depois enviar a cada 1 minuto (60 segundos) - bem antes dos 5 minutos de timeout
      keepAliveInterval = setInterval(sendKeepAlive, 60000);
    }

    async function sendKeepAlive() {
      try {
        const response = await fetch('/src/keep_alive.php', { 
          method: 'GET',
          cache: 'no-cache'
        });
        const data = await response.json();
        if (!data.ok) {
          console.warn('Sessão expirada, redirecionando...');
          clearInterval(keepAliveInterval);
          window.location.href = '/src/login.html?error=session_expired';
        } else {
          console.log('Keep-alive OK:', new Date().toLocaleTimeString());
          // Verificar se o usuário mudou (login em outra aba)
          if (currentUserId === null) {
            currentUserId = data.user_id;
          } else if (data.user_id && data.user_id !== currentUserId) {
            // Usuário mudou! Recarregar a página para redirecionar corretamente
            console.warn('Conta alterada, redirecionando...');
            clearInterval(keepAliveInterval);
            window.location.reload();
          }
        }
      } catch (e) {
        console.error('Erro no keep-alive:', e);
      }
    }

    window.addEventListener('beforeunload', () => {
      if (keepAliveInterval) clearInterval(keepAliveInterval);
    });

    window.addEventListener('DOMContentLoaded', () => {
      loadAppointments();
      startKeepAlive();
    });
