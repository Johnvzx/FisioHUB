document.querySelectorAll('a[href^="#"]').forEach(anchor => {
anchor.addEventListener('click', function (e) {
e.preventDefault();
const target = document.querySelector(this.getAttribute('href'));
if (target) {
    target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}
});
});

const observerOptions = {
threshold: 0.1,
rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
entries.forEach(entry => {
if (entry.isIntersecting) {
    entry.target.classList.add('revealed');
}
});
}, observerOptions);

document.querySelectorAll('.scroll-reveal').forEach(el => {
observer.observe(el);
});

window.addEventListener('scroll', function() {
const header = document.querySelector('header');
if (window.scrollY > 100) {
header.style.background = 'rgba(44, 82, 130, 0.95)';
header.style.backdropFilter = 'blur(10px)';
} else {
header.style.background = 'linear-gradient(135deg, #2c5282 0%, #3182ce 100%)';
header.style.backdropFilter = 'none';
}
});

async function loadProfessionals() {
console.log('🔄 Carregando profissionais...');
const select = document.getElementById('professional_id');
console.log('🎯 Select encontrado:', select);

if (!select) {
console.warn('⚠️ Select professional_id não encontrado no DOM');
return;
}

try {
const res = await fetch('/src/get_professionals.php');
console.log('� Resposta recebida:', res.status);
const data = await res.json();
console.log('📦 Dados recebidos:', data);

if (data && data.ok && data.professionals) {
    if (data.professionals.length === 0) {
        select.innerHTML = '<option value="">Nenhum profissional disponível</option>';
    } else {
        select.innerHTML = '<option value="">Selecione um profissional</option>';
        data.professionals.forEach(prof => {
            const opt = document.createElement('option');
            opt.value = prof.id;
            opt.textContent = `${prof.name}${prof.specialty ? ' - ' + prof.specialty : ''}`;
            select.appendChild(opt);
            console.log('➕ Profissional adicionado:', prof.name);
        });
        console.log('✅ Total de profissionais carregados:', data.professionals.length);
    }
} else {
    throw new Error('Resposta inválida da API');
}
} catch (err) {
console.error('❌ Erro ao carregar profissionais:', err);
if (select) {
    select.innerHTML = '<option value="">Erro ao carregar profissionais</option>';
}
}
}

window.addEventListener('DOMContentLoaded', function() {
console.log('🚀 DOM carregado, tentando carregar profissionais...');
loadProfessionals();
});

window.addEventListener('load', function() {
const select = document.getElementById('professional_id');
if (select && select.options.length <= 1) {
console.log('🔄 Load completo: recarregando profissionais...');
loadProfessionals();
}
});

document.addEventListener('DOMContentLoaded', function() {
console.log('📝 Configurando listener do formulário...');
const appointmentForm = document.getElementById('appointment-form');

if (!appointmentForm) {
console.warn('⚠️ Formulário appointment-form não encontrado');
return;
}

console.log('✅ Formulário encontrado, adicionando listener');

appointmentForm.addEventListener('submit', async function(e) {
e.preventDefault();
console.log('📤 Formulário submetido!');

const submitBtn = document.getElementById('submit-btn');
const loading = document.getElementById('loading');
const professionalId = document.getElementById('professional_id').value;

console.log('🔍 Professional ID selecionado:', professionalId);

if (!professionalId) {
    console.error('❌ Nenhum profissional selecionado');
    if (window.Swal) {
        Swal.fire({icon: 'warning', title: 'Atenção', text: 'Por favor, selecione um profissional.'});
    } else {
        alert('Por favor, selecione um profissional.');
    }
    return;
}

submitBtn.disabled = true;
submitBtn.textContent = 'Enviando...';
loading.classList.add('active');

const fd = new FormData(this);
fd.append('action', 'create_appointment');

console.log('📋 Dados do formulário:');
for (let pair of fd.entries()) {
    console.log('  ', pair[0] + ':', pair[1]);
}

try {
    console.log('🌐 Enviando requisição para admin_api.php...');
    const res = await fetch('/src/admin_api.php', { 
        method: 'POST', 
        body: fd 
    });
    console.log('📡 Resposta recebida:', res.status);
    const result = await res.json();
    console.log('📦 Resultado:', result);
    
    loading.classList.remove('active');
    
    if (result && result.ok) {
        console.log('✅ Agendamento criado com sucesso!');
        try {
            const sessionRes = await fetch('/src/session_info.php');
            const sessionInfo = await sessionRes.json();
            console.log('👤 Info da sessão:', sessionInfo);
            
            if (sessionInfo && sessionInfo.loggedIn) {
                if (sessionInfo.role === 'professional') {
                    console.log('🔄 Redirecionando para dashboard do profissional...');
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Consulta Agendada!',
                            text: 'Redirecionando para seu dashboard...',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                    setTimeout(() => {
                        window.location.href = '/src/professional_dashboard.php';
                    }, 1500);
                    return;
                } else if (sessionInfo.role === 'admin') {
                    // Se é admin, redirecionar para dashboard admin
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Consulta Agendada!',
                            text: 'Redirecionando para o painel administrativo...',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                    setTimeout(() => {
                        window.location.href = '/src/admin_dashboard.php';
                    }, 1500);
                    return;
                }
            }
        } catch (err) {
            console.log('Não logado ou erro ao verificar sessão:', err);
        }
        
        this.reset();
        loadProfessionals(); 
        
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Consulta Agendada!',
                html: 'Sua consulta foi agendada com sucesso.<br>Entraremos em contato em breve para confirmar.',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Consulta agendada com sucesso! Entraremos em contato em breve.');
        }
        
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
    
    if (window.Swal) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao agendar consulta: ' + err.message
        });
    } else {
        alert('Erro ao agendar consulta: ' + err.message);
    }
}
});
});

setTimeout(function() {
    const select = document.getElementById('professional_id');
    if (select && select.options.length <= 1) {
        console.log('⚠️ Fallback: Recarregando profissionais...');
        if (typeof loadProfessionals === 'function') {
            loadProfessionals();
        }
    }
}, 2000);

window.addEventListener('load', function() {
document.body.style.opacity = '1';
});

