const apiPath = '/src/admin_api.php';

async function apiRequest(fd) {
    try {
        const res = await fetch(apiPath, { method: 'POST', body: fd });
        return await res.json();
    } catch (e) {
        return { error: 'network', message: e.message };
    }
}

async function loadLists() {
    try {
        const r = await fetch(apiPath);
        if (!r.ok) throw new Error('API indisponível');
        const data = await r.json();
        
        console.log('Data loaded:', data);
        console.log('Professionals:', data.professionals);
        
        document.getElementById('total-users').textContent = (data.users || []).length;
        document.getElementById('total-appointments').textContent = (data.appointments || []).length;
        document.getElementById('total-professionals').textContent = (data.professionals || []).length;
        document.getElementById('total-admins').textContent = (data.admins || []).length;
        
    renderUsers(data.users || []);
        renderAdmins(data.admins || []);
        renderProfs(data.professionals || []);
        renderAppointments(data.appointments || [], data.professionals || []);
        
        const profSelect = document.getElementById('appt-professional-select');
        console.log('Professional select element:', profSelect);
        if (profSelect) {
            const professionals = data.professionals || [];
            console.log('Populating with professionals:', professionals);
            
            if (professionals.length === 0) {
                profSelect.innerHTML = '<option value="">Nenhum profissional cadastrado</option>';
            } else {
                profSelect.innerHTML = '<option value="">Selecione o profissional</option>';
                professionals.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `${p.name}${p.specialty ? ' - ' + p.specialty : ''}`;
                    profSelect.appendChild(opt);
                    console.log('Added professional:', p.name, p.id);
                });
            }
        } else {
            console.error('Profissional selecionado não encontrado!');
        }
        
        document.getElementById('users-wrap').textContent = '';
        document.getElementById('admins-wrap').textContent = '';
        document.getElementById('profs-wrap').textContent = '';
        document.getElementById('appts-wrap').textContent = '';

        // Avisos amigáveis quando listas estão vazias
        try {
            const users = data.users || [];
            const profs = data.professionals || [];
            const appts = data.appointments || [];
            const admins = data.admins || [];
            if (window.Swal) {
                if (users.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Nenhum usuário cadastrado',
                        text: 'Crie o primeiro usuário no bloco "Gerenciar Usuários".',
                        timer: 2200,
                        showConfirmButton: false
                    });
                } else if (profs.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Nenhum profissional cadastrado',
                        text: 'Você pode promover um usuário para profissional.',
                        timer: 2200,
                        showConfirmButton: false
                    });
                } else if (appts.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sem agendamentos ainda',
                        text: 'Crie ou importe agendamentos para começar.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }
        } catch(e) { /* ignore */ }
    } catch (err) {
        console.error('Error loading data:', err);
        document.getElementById('users-wrap').innerHTML = '<div class="loading">⚠️ Erro ao carregar dados</div>';
        document.getElementById('admins-wrap').innerHTML = '<div class="loading">⚠️ Erro ao carregar dados</div>';
        document.getElementById('profs-wrap').innerHTML = '<div class="loading">⚠️ Erro ao carregar dados</div>';
        document.getElementById('appts-wrap').innerHTML = '<div class="loading">⚠️ Erro ao carregar dados</div>';
        
        const profSelect = document.getElementById('appt-professional-select');
        if (profSelect) {
            profSelect.innerHTML = '<option value="">Erro ao carregar profissionais</option>';
        }
    }
}

function renderAppointments(appts, professionals) {
    const tbody = document.querySelector('#appts-table tbody');
    tbody.innerHTML = '';
    appts.forEach(a => {
        const tr = document.createElement('tr');
        let profName = 'Não atribuído';
        if (a.professional_id) {
            const prof = (professionals || []).find(p => p.id === a.professional_id);
            if (prof) profName = prof.name;
        }
        tr.innerHTML = `
            <td>${a.id}</td>
            <td>${escapeHtml(a.name)}</td>
            <td>${escapeHtml(a.email)}</td>
            <td>${escapeHtml(a.phone || '')}</td>
            <td>${escapeHtml(a.service || '')}</td>
            <td>${profName}</td>
            <td class="table-actions">
                <button class="btn btn-sm btn-primary" data-id="${a.id}" data-act="view_appt">Informações</button>
                <button class="btn btn-sm btn-danger" data-id="${a.id}" data-act="delete_appointment">Remover</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderUsers(users) {
    const tbody = document.querySelector('#users-table tbody');
    tbody.innerHTML = '';
    users.forEach(u => {
        const tr = document.createElement('tr');
        const roleClass = u.role === 'admin' ? 'badge-danger' : u.role === 'professional' ? 'badge-warning' : 'badge-info';
        tr.innerHTML = `
            <td>${u.id}</td>
            <td><strong>${escapeHtml(u.name)}</strong></td>
            <td>${escapeHtml(u.email)}</td>
            <td><span class="badge ${roleClass}">${escapeHtml(u.role || 'user')}</span></td>
        `;
        tbody.appendChild(tr);
    });
}

function renderProfs(profs) {
    const tbody = document.querySelector('#profs-table tbody');
    tbody.innerHTML = '';
    profs.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${p.id}</td>
            <td><strong>${escapeHtml(p.name)}</strong></td>
            <td>${escapeHtml(p.email)}</td>
            <td>${escapeHtml(p.specialty || 'Não definida')}</td>
            <td class="table-actions">
                <button class="btn btn-sm btn-danger" data-id="${p.id}" data-act="delete_prof">Remover</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderAdmins(admins) {
    const tbody = document.querySelector('#admins-table tbody');
    tbody.innerHTML = '';
    admins.forEach(a => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${a.id}</td>
            <td><strong>${escapeHtml(a.name)}</strong></td>
            <td>${escapeHtml(a.email)}</td>
            <td class="table-actions">
                <button class="btn btn-sm btn-danger" data-id="${a.id}" data-act="delete_user">Remover</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function initTableSearch() {
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

function escapeHtml(s) {
    return (s || '').toString().replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[c]);
}

document.getElementById('user-role-select').addEventListener('change', function() {
    const specialtyField = document.getElementById('specialty-field');
    const specialtySelect = document.getElementById('specialty-select');
    
    if (this.value === 'professional') {
        specialtyField.style.display = 'block';
        specialtySelect.setAttribute('required', 'required');
    } else {
        specialtyField.style.display = 'none';
        specialtySelect.removeAttribute('required');
        specialtySelect.value = '';
    }
});

document.getElementById('create-user').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const role = document.getElementById('user-role-select').value;
    const specialty = document.getElementById('specialty-select').value;
    
    if (role === 'professional' && !specialty) {
        Swal.fire({
            icon: 'warning',
            title: '⚠️ Atenção',
            text: 'Por favor, selecione uma especialidade para o profissional'
        });
        return;
    }
    
    const fd = new FormData(this);
    fd.append('action', 'create_user');
    const j = await apiRequest(fd);
    if (j && j.ok) {
        Swal.fire({
            icon: 'success',
            title: '✅ Usuário Adicionado!',
            text: 'O usuário foi criado com sucesso',
            timer: 2000,
            showConfirmButton: false
        });
        this.reset();
        document.getElementById('specialty-field').style.display = 'none';
        loadLists();
    } else {
        console.error('API error', j);
        const msg = (j && (j.message || j.error || JSON.stringify(j)) || 'unknown');
        Swal.fire({
            icon: 'error',
            title: '❌ Erro',
            text: String(msg)
        });
    }
});

const createApptForm = document.getElementById('create-appointment');
if (createApptForm) {
    createApptForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const professionalId = document.getElementById('appt-professional-select').value;
        if (!professionalId) {
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Atenção',
                text: 'Por favor, selecione um profissional para o agendamento'
            });
            return;
        }
        
        const fd = new FormData(this);
        fd.append('action', 'create_appointment');
        const j = await apiRequest(fd);
        if (j && j.ok) {
            Swal.fire({
                icon: 'success',
                title: '✅ Agendamento Criado!',
                text: 'O agendamento foi criado com sucesso',
                timer: 2000,
                showConfirmButton: false
            });
            this.reset();
            loadLists();
        } else {
            console.error('API error', j);
            const msg = (j && (j.message || j.error || JSON.stringify(j)) || 'unknown');
            Swal.fire({
                icon: 'error',
                title: '❌ Erro',
                text: String(msg)
            });
        }
    });
}

document.addEventListener('click', async function(e) {
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;
    const act = btn.dataset.act;
    const id = btn.dataset.id;
    
    // Editar profissional
    if (act === 'edit_prof') {
        const currentName = btn.dataset.name;
        const currentEmail = btn.dataset.email;
        const currentSpecialty = btn.dataset.specialty;
        
        const { value: formValues } = await Swal.fire({
            title: '✏️ Editar Profissional',
            html: `
                <div style="text-align: left;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Nome:</label>
                    <input id="edit-name" class="swal2-input" value="${currentName}" style="width: 85%; margin-bottom: 15px;">
                    
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Email:</label>
                    <input id="edit-email" type="email" class="swal2-input" value="${currentEmail}" style="width: 85%; margin-bottom: 15px;">
                    
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Especialidade:</label>
                    <select id="edit-specialty" class="swal2-input" style="width: 85%; padding: 10px;">
                        <option value="Fisioterapia Ortopédica" ${currentSpecialty === 'Fisioterapia Ortopédica' ? 'selected' : ''}>Fisioterapia Ortopédica</option>
                        <option value="Pilates Clínico" ${currentSpecialty === 'Pilates Clínico' ? 'selected' : ''}>Pilates Clínico</option>
                        <option value="Fisioterapia Neurológica" ${currentSpecialty === 'Fisioterapia Neurológica' ? 'selected' : ''}>Fisioterapia Neurológica</option>
                        <option value="Fisioterapia Pélvica" ${currentSpecialty === 'Fisioterapia Pélvica' ? 'selected' : ''}>Fisioterapia Pélvica</option>
                        <option value="Fisioterapia Esportiva" ${currentSpecialty === 'Fisioterapia Esportiva' ? 'selected' : ''}>Fisioterapia Esportiva</option>
                        <option value="Fisioterapia Geriátrica" ${currentSpecialty === 'Fisioterapia Geriátrica' ? 'selected' : ''}>Fisioterapia Geriátrica</option>
                    </select>
                    
                    <label style="display: block; margin-bottom: 5px; margin-top: 15px; font-weight: 600;">Nova Senha (deixe em branco para não alterar):</label>
                    <input id="edit-password" type="password" class="swal2-input" placeholder="Nova senha (opcional)" style="width: 85%;">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Salvar',
            cancelButtonText: 'Cancelar',
            width: 600,
            preConfirm: () => {
                const name = document.getElementById('edit-name').value.trim();
                const email = document.getElementById('edit-email').value.trim();
                const specialty = document.getElementById('edit-specialty').value;
                const password = document.getElementById('edit-password').value.trim();
                
                if (!name || !email) {
                    Swal.showValidationMessage('Nome e email são obrigatórios');
                    return false;
                }
                
                return { name, email, specialty, password };
            }
        });
        
if (formValues) {
            const fd = new FormData();
            fd.append('action', 'edit_professional');
            fd.append('id', id);
            fd.append('name', formValues.name);
            fd.append('email', formValues.email);
            fd.append('specialty', formValues.specialty);
            if (formValues.password) {
                fd.append('password', formValues.password);
            }
            
            const j = await apiRequest(fd);
            if (j && j.ok) {
                Swal.fire({
                    icon: 'success',
                    title: '✅ Sucesso!',
                    text: 'Profissional atualizado com sucesso!',
                    timer: 2000,
                    showConfirmButton: false
                });
                loadLists();
            } else {
                console.error('API error', j);
                const msg = (j && (j.message || j.error || JSON.stringify(j)) || 'unknown');
                Swal.fire({
                    icon: 'error',
                    title: '❌ Erro',
                    text: String(msg)
                });
            }
        }
        return;
    }
    
if (act === 'edit_admin') {
        const currentName = btn.dataset.name;
        const currentEmail = btn.dataset.email;
        
        const { value: formValues } = await Swal.fire({
            title: '✏️ Editar Administrador',
            html: `
                <div style="text-align: left;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Nome:</label>
                    <input id="edit-name" class="swal2-input" value="${currentName}" style="width: 85%; margin-bottom: 15px;">
                    
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Email:</label>
                    <input id="edit-email" type="email" class="swal2-input" value="${currentEmail}" style="width: 85%; margin-bottom: 15px;">
                    
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Nova Senha (deixe em branco para não alterar):</label>
                    <input id="edit-password" type="password" class="swal2-input" placeholder="Nova senha (opcional)" style="width: 85%;">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Salvar',
            cancelButtonText: 'Cancelar',
            width: 600,
            preConfirm: () => {
                const name = document.getElementById('edit-name').value.trim();
                const email = document.getElementById('edit-email').value.trim();
                const password = document.getElementById('edit-password').value.trim();
                
                if (!name || !email) {
                    Swal.showValidationMessage('Nome e email são obrigatórios');
                    return false;
                }
                
                return { name, email, password };
            }
        });
        
if (formValues) {
            const fd = new FormData();
            fd.append('action', 'edit_admin');
            fd.append('id', id);
            fd.append('name', formValues.name);
            fd.append('email', formValues.email);
            if (formValues.password) {
                fd.append('password', formValues.password);
            }
            
            const j = await apiRequest(fd);
            if (j && j.ok) {
                Swal.fire({
                    icon: 'success',
                    title: '✅ Sucesso!',
                    text: 'Administrador atualizado com sucesso!',
                    timer: 2000,
                    showConfirmButton: false
                });
                loadLists();
            } else {
                console.error('API error', j);
                const msg = (j && (j.message || j.error || JSON.stringify(j)) || 'unknown');
                Swal.fire({
                    icon: 'error',
                    title: '❌ Erro',
                    text: String(msg)
                });
            }
        }
        return;
    }
    
if (act === 'promote_prof') {
        const { value: specialty } = await Swal.fire({
            title: 'Promover para Profissional',
            html: `
                <p style="margin-bottom: 15px; text-align: left;">Selecione a especialidade do profissional:</p>
                <select id="specialty-select" class="swal2-input" style="width: 85%; padding: 10px; font-size: 16px;">
                    <option value="">Selecione uma especialidade</option>
                    <option value="Fisioterapia Ortopédica">Fisioterapia Ortopédica</option>
                    <option value="Pilates Clínico">Pilates Clínico</option>
                    <option value="Fisioterapia Neurológica">Fisioterapia Neurológica</option>
                    <option value="Fisioterapia Pélvica">Fisioterapia Pélvica</option>
                    <option value="Fisioterapia Esportiva">Fisioterapia Esportiva</option>
                    <option value="Fisioterapia Geriátrica">Fisioterapia Geriátrica</option>
                </select>
            `,
            showCancelButton: true,
            confirmButtonText: 'Promover',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const select = document.getElementById('specialty-select');
                if (!select.value) {
                    Swal.showValidationMessage('Por favor, selecione uma especialidade');
                    return false;
                }
                return select.value;
            }
        });
        
if (specialty) {
            const fd = new FormData();
            fd.append('action', 'promote_to_professional');
            fd.append('id', id);
            fd.append('specialty', specialty);
            
            const j = await apiRequest(fd);
            if (j && j.ok) {
                Swal.fire({
                    icon: 'success',
                    title: '✅ Sucesso!',
                    text: 'Usuário promovido a profissional com sucesso!',
                    timer: 2000,
                    showConfirmButton: false
                });
                loadLists();
            } else {
                console.error('API error', j);
                const msg = (j && (j.message || j.error || JSON.stringify(j)) || 'unknown');
                Swal.fire({
                    icon: 'error',
                    title: '❌ Erro',
                    text: String(msg)
                });
            }
        }
        return;
    }
    
if (act === 'view_appt') {
        try {
            const r = await fetch(apiPath);
            if (!r.ok) throw new Error('API indisponível');
            const d = await r.json();
            const ap = (d.appointments || []).find(x => String(x.id) === String(id));
            if (ap) {
                const html = `
                    <div style="text-align: center; "">
                        <p><strong>Email:</strong> ${escapeHtml(ap.email)}</p>
                        <p><strong>Telefone:</strong> ${escapeHtml(ap.phone || 'N/A')}</p>
                        <p><strong>Serviço:</strong> ${escapeHtml(ap.service || 'N/A')}</p>
                        <hr>
                        <p><strong>Mensagem:</strong></p>
                        <div style="white-space: pre-wrap; padding: 15px; background: #f3f4f6; border-radius: 8px;">${escapeHtml(ap.message || 'Sem mensagem')}</div>
                    </div>
                `;
                Swal.fire({
                    title: 'Agendamento #' + ap.id + ' - ' + escapeHtml(ap.name),
                    html: html,
                    width: 700,
                    icon: 'info',
                    confirmButtonText: 'Fechar'
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Não encontrado',
                    text: 'Agendamento não encontrado'
                });
            }
        } catch (er) {
            Swal.fire({
                icon: 'error',
                title: '❌ Erro',
                text: 'Erro ao carregar agendamento'
            });
        }
        return;
    }
    
    if (!confirmAction(act)) return;
    
    const fd = new FormData();
    if (act === 'promote_admin') { fd.append('action', 'promote_to_admin'); fd.append('id', id); }
    if (act === 'demote_admin') { fd.append('action', 'demote_admin'); fd.append('id', id); }
    if (act === 'delete_user') { fd.append('action', 'delete'); fd.append('table', 'users'); fd.append('id', id); }
    if (act === 'move_to_user') { fd.append('action', 'move_prof_to_user'); fd.append('id', id); }
    if (act === 'delete_prof') { fd.append('action', 'delete'); fd.append('table', 'professionals'); fd.append('id', id); }
    if (act === 'delete_appointment') { fd.append('action', 'delete_appointment'); fd.append('id', id); }
    
    const j = await apiRequest(fd);
    if (j && j.ok) {
        Swal.fire({
            icon: 'success',
            title: '✅ Sucesso!',
            text: 'Ação realizada com sucesso',
            timer: 1500,
            showConfirmButton: false
        });
        loadLists();
    } else {
        console.error('API error', j);
        const msg = (j && (j.message || j.error || JSON.stringify(j)) || 'unknown');
        Swal.fire({
            icon: 'error',
            title: '❌ Erro',
            text: String(msg)
        });
    }
});

function confirmAction(act) {
    const msgs = {
        promote_admin: '⚡ Promover este usuário a admin?',
        demote_admin: '⬇️ Rebaixar este administrador para usuário?',
        delete_user: '🗑️ Remover usuário permanentemente?',
        move_to_user: '👤 Mover profissional para usuário comum?',
        delete_prof: '🗑️ Remover profissional?',
        delete_appointment: '🗑️ Excluir este agendamento?'
    };
    return confirm(msgs[act] || 'Confirmar ação?');
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

// Parar keep-alive ao sair da página
window.addEventListener('beforeunload', () => {
    if (keepAliveInterval) clearInterval(keepAliveInterval);
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing admin dashboard...');
    const loader = document.getElementById('global-loader');
    function hideLoader(){ if (loader) loader.style.opacity = '0'; setTimeout(()=>{ if(loader) loader.style.display='none'; },400); }
    // Carregar dados
    loadLists().finally(()=>{ hideLoader(); });
    initTableSearch();
    // Iniciar keep-alive
    startKeepAlive();
});

    document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (this.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

document.addEventListener('click', function(e) {
    if (e.target.closest('button[data-act]')) return;
    const profRow = e.target.closest('#profs-table tbody tr');
    if (profRow) {
        const editBtn = profRow.querySelector('button[data-act="edit_prof"]');
        if (editBtn) editBtn.click();
        return;
    }
    const adminRow = e.target.closest('#admins-table tbody tr');
    if (adminRow) {
        const editBtn = adminRow.querySelector('button[data-act="edit_admin"]');
        if (editBtn) editBtn.click();
    }
});
