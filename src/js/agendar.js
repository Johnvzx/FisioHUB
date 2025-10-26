console.log('🚀 Página de agendamento carregada');

async function loadProfessionals() {
    console.log('🔄 Carregando profissionais...');
    const select = document.getElementById('professional_id');
    
    if (!select) {
        console.error('❌ Select professional_id não encontrado');
        return;
    }
    
    try {
        const res = await fetch('/src/get_professionals.php');
        console.log('📡 Resposta:', res.status);
        const data = await res.json();
        console.log('📦 Dados:', data);
        
        if (data && data.ok && data.professionals) {
            if (data.professionals.length === 0) {
                select.innerHTML = '<option value="">Nenhum profissional disponível</option>';
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Nenhum profissional disponível no momento. Por favor, entre em contato por telefone.'
                });
            } else {
                select.innerHTML = '<option value="">Selecione um profissional</option>';
                data.professionals.forEach(prof => {
                    const opt = document.createElement('option');
                    opt.value = prof.id;
                    opt.textContent = `${prof.name}${prof.specialty ? ' - ' + prof.specialty : ''}`;
                    select.appendChild(opt);
                    console.log('✅ Adicionado:', prof.name, '(ID:', prof.id + ')');
                });
                console.log('✅ Total de profissionais carregados:', data.professionals.length);
            }
        } else {
            throw new Error('Resposta inválida da API');
        }
    } catch (err) {
        console.error('❌ Erro ao carregar profissionais:', err);
        select.innerHTML = '<option value="">Erro ao carregar profissionais</option>';
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível carregar a lista de profissionais. Tente recarregar a página.'
        });
    }
}

document.getElementById('appointment-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    console.log('📤 Formulário submetido');
    
    const submitBtn = document.getElementById('submit-btn');
    const loading = document.getElementById('loading');
    const professionalId = document.getElementById('professional_id').value;
    
    console.log('🔍 Professional ID selecionado:', professionalId);
    
    if (!professionalId) {
        console.error('❌ Nenhum profissional selecionado');
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Por favor, selecione um profissional.'
        });
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Enviando...';
    loading.classList.add('active');
    
    const fd = new FormData(this);
    fd.append('action', 'create_appointment');
    
    // Log dos dados
    console.log('📋 Dados do formulário:');
    for (let pair of fd.entries()) {
        console.log('  ', pair[0] + ':', pair[1]);
    }
    
    try {
        console.log('🌐 Enviando para admin_api.php...');
        const res = await fetch('/src/admin_api.php', { 
            method: 'POST', 
            body: fd 
        });
        console.log('📡 Resposta:', res.status);
        const result = await res.json();
        console.log('📦 Resultado:', result);
        
        loading.classList.remove('active');
        
        if (result && result.ok) {
            console.log('✅ Agendamento criado! ID:', result.id);
            
            try {
                const sessionRes = await fetch('/src/session_info.php');
                const sessionInfo = await sessionRes.json();
                console.log('👤 Sessão:', sessionInfo);
                
                if (sessionInfo && sessionInfo.loggedIn) {
                    if (sessionInfo.role === 'professional') {
                        console.log('🔄 Redirecionando para dashboard profissional...');
                        Swal.fire({
                            icon: 'success',
                            title: 'Consulta Agendada!',
                            text: 'Redirecionando para seu dashboard...',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            window.location.href = '/src/professional_dashboard.php';
                        }, 1500);
                        return;
                    } else if (sessionInfo.role === 'admin') {
                        console.log('🔄 Redirecionando para dashboard admin...');
                        Swal.fire({
                            icon: 'success',
                            title: 'Consulta Agendada!',
                            text: 'Redirecionando para o painel administrativo...',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            window.location.href = '/src/admin_dashboard.php';
                        }, 1500);
                        return;
                    }
                }
            } catch (err) {
                console.log('ℹ️ Usuário não logado:', err);
            }
            
            // Não está logado - mostrar mensagem de sucesso
            this.reset();
            loadProfessionals();
            
            Swal.fire({
                icon: 'success',
                title: 'Consulta Agendada!',
                html: `Sua consulta foi agendada com sucesso!<br><br>
                        <strong>Número do agendamento:</strong> #${result.id}<br><br>
                        Entraremos em contato em breve para confirmar o horário.`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#667eea'
            });
            
            submitBtn.disabled = false;
            submitBtn.textContent = 'Agendar Consulta';
        } else {
            throw new Error(result.message || result.error || 'Erro desconhecido');
        }
    } catch (err) {
        loading.classList.remove('active');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Agendar Consulta';
        
        console.error('❌ Erro ao enviar:', err);
        
        Swal.fire({
            icon: 'error',
            title: 'Erro ao Agendar',
            text: 'Ocorreu um erro ao processar seu agendamento: ' + err.message,
            confirmButtonColor: '#667eea'
        });
    }
});

window.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM carregado');
    loadProfessionals();
});

// Fallback
window.addEventListener('load', function() {
    const select = document.getElementById('professional_id');
    if (select && select.options.length <= 1) {
        console.log('🔄 Fallback: recarregando profissionais...');
        loadProfessionals();
    }
});

document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("appointment-form");
            const loading = document.getElementById("loading");
            const submitBtn = document.getElementById("submit-btn");

            form.addEventListener("submit", function(event) {
                event.preventDefault();
                loading.classList.add("active");
                submitBtn.disabled = true;

                setTimeout(() => {
                    loading.classList.remove("active");
                    submitBtn.disabled = false;
                    Swal.fire({
                        icon: 'success',
                        title: 'Agendamento realizado!',
                        text: 'Sua consulta foi agendada com sucesso.',
                    });
                }, 2000);
            });
        });
