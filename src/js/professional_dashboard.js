const apiPath = '/src/professional_api.php';
let state = { appointments: [], profile: null };

function escapeHtml(s) {
  return (s || '').toString().replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[c]);
}

async function loadData() {
  console.log('🔄 Carregando dados do profissional...');
  try {
    const r = await fetch(apiPath);
    console.log('📡 Resposta da API:', r.status);
    if (!r.ok) throw new Error('API indisponível');
    const d = await r.json();
    console.log('📦 Dados recebidos:', d);
    if (!d.ok) throw new Error(d.message || 'Falha ao carregar');
    state.appointments = d.appointments || [];
    state.profile = d.profile || {};
    
    console.log('📋 Agendamentos:', state.appointments.length);
    console.log('👤 Perfil:', state.profile);
    
    const total = state.appointments.length;
    const uniquePatients = new Set(state.appointments.map(a => (a.email || a.name || '').toLowerCase())).size;
    const distinctServices = new Set(state.appointments.map(a => (a.service || '').trim()).filter(Boolean)).size;
    
    document.getElementById('stat-appointments').textContent = total;
    document.getElementById('stat-pacientes').textContent = uniquePatients;
    document.getElementById('stat-servicos').textContent = distinctServices;
    
    renderAppointments(state.appointments);
    fillProfileForm(state.profile);
    renderChart(state.appointments);
    
    document.getElementById('appts-wrap').textContent = '';
  } catch (err) {
    console.error('❌ Erro ao carregar:', err);
    document.getElementById('appts-wrap').innerHTML = '<div class="loading">⚠️ Erro ao carregar dados</div>';
  }
}

function renderAppointments(appts) {
  console.log('🎨 Renderizando agendamentos:', appts.length);
  const tbody = document.querySelector('#appts-table tbody');
  
  if (!tbody) {
    console.error('❌ Tbody não encontrado!');
    return;
  }
  
  console.log('✅ Tbody encontrado');
  tbody.innerHTML = '';
  
  appts.forEach((a, index) => {
    console.log(`➕ Adicionando agendamento ${index + 1}:`, a);
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${a.id}</td>
      <td><strong>${escapeHtml(a.name)}</strong></td>
      <td>${escapeHtml(a.email)}</td>
      <td>${escapeHtml(a.phone || '')}</td>
      <td>${escapeHtml(a.service || '')}</td>
      <td class="table-actions">
        <button class="btn btn-sm btn-primary" data-id="${a.id}" data-act="view_appt">Informações</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
  
  console.log('✅ Renderização completa. Linhas na tabela:', tbody.children.length);
}

function fillProfileForm(p) {
  console.log('📝 Preenchendo formulário de perfil...');
  const nameField = document.getElementById('perfil-name');
  const emailField = document.getElementById('perfil-email');
  const specialtyField = document.getElementById('perfil-specialty');
  const passwordField = document.getElementById('perfil-password');
  
  if (nameField) nameField.value = p.name || '';
  if (emailField) emailField.value = p.email || '';
  if (specialtyField) specialtyField.value = p.specialty || '';
  if (passwordField) passwordField.value = '';
  
  if (!nameField && !emailField && !specialtyField) {
    console.log('⚠️ Formulário de perfil não encontrado (OK - não é obrigatório)');
  }
}

function initSearch() {
  document.querySelectorAll('.table-search').forEach(inp => {
    inp.addEventListener('input', function() {
      const selector = this.dataset.target;
      const q = this.value.trim().toLowerCase();
      const rows = document.querySelectorAll(selector + ' tbody tr');
      rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        r.style.display = text.indexOf(q) === -1 ? 'none' : '';
      });
    });
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
      labels,
      datasets: [{
        label: 'Agendamentos/Mês',
        data: values,
        backgroundColor: 'rgba(49, 130, 206, 0.6)'
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, precision: 0 } }
    }
  });
  
  console.log('✅ Gráfico renderizado');
}

document.addEventListener('click', function(e) {
  const btn = e.target.closest('button[data-act="view_appt"]');
  if (!btn) return;
  const id = btn.dataset.id;
  const ap = state.appointments.find(x => String(x.id) === String(id));
  if (!ap) return;
  const html = `
    <div style="text-align:left;">
      <p><strong>Nome:</strong> ${escapeHtml(ap.name)}</p>
      <p><strong>Email:</strong> ${escapeHtml(ap.email)}</p>
      <p><strong>Telefone:</strong> ${escapeHtml(ap.phone || 'N/A')}</p>
      <p><strong>Serviço:</strong> ${escapeHtml(ap.service || 'N/A')}</p>
      <hr>
      <p><strong>Mensagem:</strong></p>
      <div style="white-space: pre-wrap; padding: 15px; background: #f3f4f6; border-radius: 8px;">${escapeHtml(ap.message || 'Sem mensagem')}</div>
    </div>
  `;
  if (window.Swal) {
    Swal.fire({ title: 'Agendamento #' + ap.id, html, width: 700, icon: 'info', confirmButtonText: 'Fechar' });
  } else {
    alert(`Agendamento #${ap.id}\n${ap.name}\n${ap.email}`);
  }
});

function init() {
  console.log('🚀 Inicializando dashboard...');
  
  const formPerfil = document.getElementById('form-perfil');
  if (formPerfil) {
    console.log('✅ Formulário de perfil encontrado');
    formPerfil.addEventListener('submit', async function(e) {
      e.preventDefault();
      const fd = new FormData(formPerfil);
      fd.append('action','update_profile');
      try {
        const r = await fetch(apiPath, { method: 'POST', body: fd });
        const j = await r.json();
        if (j && j.ok) {
          if (window.Swal) {
            Swal.fire({ icon: 'success', title: '✅ Perfil atualizado!', timer: 1500, showConfirmButton: false });
          }
          loadData();
        } else {
          const msg = (j && (j.message || j.error)) || 'Erro desconhecido';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Erro', text: String(msg) });
        }
      } catch (err) {
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Erro', text: err.message });
      }
    });
  } else {
    console.log('ℹ️ Formulário de perfil não encontrado (OK)');
  }
  
  initSearch();
  loadData();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
