const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});

(function handleQueryMessages(){
    try {
        const params = new URLSearchParams(window.location.search);
    const panel = params.get('panel');
    const error = params.get('error');
    const prefillEmail = params.get('prefillEmail');

        if (panel === 'register') {
            container.classList.add('active');
            if (prefillEmail) {
                const emailInput = document.querySelector('.sign-up form input[name="email"]');
                if (emailInput) {
                    emailInput.value = prefillEmail;
                    emailInput.focus();
                }
            }
        }

        const showSwal = (icon, title, text, confirm='Ok') => {
            if (window.Swal) {
                Swal.fire({ icon, title, text, confirmButtonText: confirm, confirmButtonColor: '#667eea' });
            } else {
                alert(title + ': ' + text);
            }
        };

        if (error === 'email_exists') {
            showSwal('warning', 'E-mail já cadastrado', 'Este e-mail já está cadastrado. Faça login ou use outro e-mail.', 'Entendi');
        }
        if (error === 'user_not_found') {
            container.classList.add('active'); // abrir cadastro
            const emailInput = document.querySelector('.sign-up form input[name="email"]');
            if (prefillEmail && emailInput) {
                emailInput.value = prefillEmail;
                emailInput.focus();
            }
            showSwal('info', 'E-mail não encontrado', 'Este e-mail ainda não foi cadastrado. Crie uma conta para continuar.', 'Criar Conta');
        }
        if (error === 'invalid_credentials') {
            showSwal('error', 'Credenciais inválidas', 'Senha incorreta. Tente novamente.', 'Ok');
        }
        if (error === 'missing_fields') {
            showSwal('warning', 'Campos obrigatórios', 'Preencha e-mail e senha para continuar.', 'Ok');
        }
        if (error === 'session_expired') {
            showSwal('info', 'Sessão expirada', 'Sua sessão expirou por inatividade. Faça login novamente.', 'Entendi');
        }
    } catch(e) { }
})();
