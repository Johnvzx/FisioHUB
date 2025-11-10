const apiPath = '/src/professional_api.php';
let state = { appointments: [], profile: null };
let allAppointments = []; // Para busca

function escapeHtml(s) {
  return (s || '').toString().replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[c]);
}

async function loadData() {
  console.log('🔄 Carregando dados do profissional...');
  const container = document.getElementById('appointments-container');
  const loader = document.getElementById('global-loader');
  
  try {
    const r = await fetch(apiPath);
    console.log('📡 Resposta da API:', r.status);
    if (!r.ok) throw new Error('API indisponível');
    const d = await r.json();
    console.log('📦 Dados recebidos:', d);
    if (!d.ok) throw new Error(d.message || 'Falha ao carregar');
    
    state.appointments = d.appointments || [];
    state.profile = d.profile || {};
    allAppointments = [...state.appointments];
    
    console.log('📋 Agendamentos:', state.appointments.length);
    console.log('👤 Perfil:', state.profile);
    
    // Calcular estatísticas
    const total = state.appointments.length;
    const uniquePatients = new Set(state.appointments.map(a => (a.email || a.name || '').toLowerCase())).size;
    const distinctServices = new Set(state.appointments.map(a => (a.service || '').trim()).filter(Boolean)).size;
    const totalRevenue = state.appointments.reduce((sum, a) => sum + (parseFloat(a.price) || 0), 0);
    
    // Atualizar stats
    document.getElementById('stat-appointments').textContent = total;
    document.getElementById('stat-pacientes').textContent = uniquePatients;
    document.getElementById('stat-servicos').textContent = distinctServices;
    document.getElementById('stat-revenue').textContent = formatPrice(totalRevenue);
    
    renderAppointments(state.appointments);
    renderChart(state.appointments);
    if (loader) { loader.style.opacity='0'; setTimeout(()=>{ loader.style.display='none'; },400); }
  } catch (err) {
    console.error('❌ Erro ao carregar:', err);
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-state-icon">⚠️</div>
        <h3>Erro ao carregar dados</h3>
        <p>${err.message}</p>
        <button onclick="loadData()" class="btn btn-outline" style="margin-top: 20px;">
          Tentar Novamente
        </button>
      </div>
    `;
  }
}

function renderAppointments(appts) {
  console.log('🎨 Renderizando agendamentos:', appts.length);
  const container = document.getElementById('appointments-container');
  
  if (!container) {
    console.error('❌ Container não encontrado!');
    return;
  }
  
  if (appts.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-state-icon">📅</div>
        <h3>Nenhum agendamento</h3>
        <p>Ainda não há pacientes na sua agenda.</p>
      </div>
    `;
    return;
  }
  
  container.innerHTML = '<div class="appointments-list"></div>';
  const list = container.querySelector('.appointments-list');
  
  appts.forEach(appt => {
    const item = document.createElement('div');
    item.className = 'appointment-item';
    item.dataset.id = appt.id;
    
    const statusMap = {
      'pending': { cls: 'status-pending', txt: 'Pendente' },
      'confirmed': { cls: 'status-confirmed', txt: 'Confirmada' },
      'in_progress': { cls: 'status-in-progress', txt: 'Em andamento' },
      'completed': { cls: 'status-completed', txt: 'Concluída' },
      'canceled': { cls: 'status-canceled', txt: 'Cancelada' }
    };
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
          <span class="detail-icon">👤</span>
          <div>
            <div class="detail-label">Paciente</div>
            <div class="detail-value">${escapeHtml(appt.name || 'N/A')}</div>
          </div>
        </div>
        <div class="detail-item">
          <span class="detail-icon">📧</span>
          <div>
            <div class="detail-label">Email</div>
            <div class="detail-value">${escapeHtml(appt.email || 'N/A')}</div>
          </div>
        </div>
        <div class="detail-item">
          <span class="detail-icon">📱</span>
          <div>
            <div class="detail-label">Telefone</div>
            <div class="detail-value">${escapeHtml(appt.phone || 'N/A')}</div>
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
          <div style="margin-top: 5px; color: #4a5568; white-space: pre-wrap;">${escapeHtml(appt.message)}</div>
        </div>
      ` : ''}
    `;
    
    // Adicionar evento de clique para mostrar mais detalhes
    item.style.cursor = 'pointer';
    item.addEventListener('click', (e) => {
      // Evitar abrir detalhes se clicar especificamente no dropdown status futuramente
      if (e.target.closest('.appointment-status')) return;
      showAppointmentDetails(appt);
    });

    // Tornar status clicável para trocar
    const statusEl = item.querySelector('.appointment-status');
    if (statusEl) {
      statusEl.style.cursor = 'pointer';
      statusEl.title = 'Clique para alterar o status';
      statusEl.addEventListener('click', (ev) => {
        ev.stopPropagation();
        changeStatus(appt);
      });
    }
    
    list.appendChild(item);
  });
  
  console.log('✅ Renderização completa. Cards:', list.children.length);
}

function showAppointmentDetails(appt) {
  if (!window.Swal) return;
  
  const html = `
    <div style="text-align:left; font-size: 1rem;">
      <div style="margin-bottom: 20px;">
        <h4 style="color: #667eea; margin-bottom: 10px;">📋 Informações do Paciente</h4>
        <p><strong>Nome:</strong> ${escapeHtml(appt.name)}</p>
        <p><strong>Email:</strong> ${escapeHtml(appt.email)}</p>
        <p><strong>Telefone:</strong> ${escapeHtml(appt.phone || 'N/A')}</p>
      </div>
      <div style="margin-bottom: 20px;">
        <h4 style="color: #667eea; margin-bottom: 10px;">🏥 Detalhes da Consulta</h4>
        <p><strong>Serviço:</strong> ${escapeHtml(appt.service || 'N/A')}</p>
        <p><strong>Data:</strong> ${formatDate(appt.appointment_date || appt.created_at)}</p>
        <p><strong>Horário:</strong> ${appt.appointment_time || 'A confirmar'}</p>
        <p><strong>Valor:</strong> ${formatPrice(appt.price || 0)}</p>
        <p><strong>Status:</strong> ${(() => {
          const map = { pending: '⏳ Pendente', confirmed: '✅ Confirmada', in_progress: '🚧 Em andamento', completed: '🎉 Concluída', canceled: '❌ Cancelada' };
          return map[appt.status] || '⏳ Pendente';
        })()}</p>
      </div>
      ${appt.message ? `
        <div>
          <h4 style="color: #667eea; margin-bottom: 10px;">💬 Mensagem do Paciente</h4>
          <div style="white-space: pre-wrap; padding: 15px; background: #f3f4f6; border-radius: 8px; color: #4a5568;">
            ${escapeHtml(appt.message)}
          </div>
        </div>
      ` : ''}
    </div>
  `;
  
  Swal.fire({
    title: `Agendamento #${appt.id}`,
    html,
    width: 700,
    icon: 'info',
    confirmButtonText: 'Fechar',
    confirmButtonColor: '#667eea'
  });
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

function initSearch() {
  const searchInput = document.getElementById('search-input');
  if (!searchInput) return;
  
  searchInput.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    
    if (!query) {
      renderAppointments(allAppointments);
      return;
    }
    
    const filtered = allAppointments.filter(appt => {
      const searchText = [
        appt.name,
        appt.email,
        appt.phone,
        appt.service,
        appt.message
      ].join(' ').toLowerCase();
      
      return searchText.includes(query);
    });
    
    renderAppointments(filtered);
  });
}

let chartInstance = null;
function renderChart(appts) {
  console.log('📊 Renderizando gráfico...');
  const chartCanvas = document.getElementById('apptsChart');
  
  if (!chartCanvas) {
    console.log('⚠️ Canvas do gráfico não encontrado');
    return;
  }
  
  const ctx = chartCanvas.getContext('2d');
  const counts = new Map();
  
  appts.forEach(a => {
    const d = new Date(a.created_at || Date.now());
    if (isNaN(d)) return;
    const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
    counts.set(key, (counts.get(key) || 0) + 1);
  });
  
  const labels = Array.from(counts.keys()).sort();
  const values = labels.map(k => counts.get(k));
  
  if (chartInstance) { chartInstance.destroy(); }
  
  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels.map(l => {
        const [year, month] = l.split('-');
        return new Date(year, month - 1).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
      }),
      datasets: [{
        label: 'Agendamentos por Mês',
        data: values,
        backgroundColor: 'rgba(102, 126, 234, 0.8)',
        borderColor: 'rgba(102, 126, 234, 1)',
        borderWidth: 2,
        borderRadius: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          padding: 12,
          titleFont: { size: 14 },
          bodyFont: { size: 13 }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0 },
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });
  
  console.log('✅ Gráfico renderizado');
}

function init() {
  console.log('🚀 Inicializando dashboard profissional...');
  initSearch();
  loadData();
}

async function changeStatus(appt) {
  if (!window.Swal) return;
  const { value: choice } = await Swal.fire({
    title: `Alterar status (#${appt.id})`,
    input: 'select',
    inputOptions: {
      'pendente': 'Pendente',
      'confirmada': 'Confirmada',
      'em_andamento': 'Em andamento',
      'concluida': 'Concluída',
      'cancelada': 'Cancelada'
    },
    inputValue: (() => {
      const revMap = { pending: 'pendente', confirmed: 'confirmada', in_progress: 'em_andamento', completed: 'concluida', canceled: 'cancelada' };
      return revMap[appt.status] || 'pendente';
    })(),
    showCancelButton: true,
    confirmButtonText: 'Salvar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#667eea'
  });
  if (!choice) return;
  try {
    const fd = new FormData();
    fd.append('action', 'update_status');
    fd.append('appointment_id', appt.id);
    fd.append('status', choice);
    const res = await fetch(apiPath, { method: 'POST', body: fd });
    const data = await res.json();
    if (!data.ok) throw new Error(data.message || 'Falha ao atualizar');
    // Atualizar localmente
    const mapToDb = { pendente: 'pending', confirmada: 'confirmed', em_andamento: 'in_progress', concluida: 'completed', cancelada: 'canceled' };
    appt.status = mapToDb[choice] || 'pending';
    // Re-render
    renderAppointments(allAppointments);
    Swal.fire({ icon: 'success', title: 'Status atualizado', timer: 1500, showConfirmButton: false });
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Erro', text: e.message });
  }
}

// Keep-alive: mantém sessão ativa enquanto aba estiver aberta
let keepAliveInterval;
let currentUserId = null;

function startKeepAlive() {
  // Enviar keep-alive imediatamente
  sendKeepAlive();
  // Depois enviar a cada 1 minuto (60 segundos)
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

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    init();
    startKeepAlive();
  });
} else {
  init();
  startKeepAlive();
}
