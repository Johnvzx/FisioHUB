// Aguarda o documento HTML ser totalmente carregado
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Rolagem Suave para os Links de Navegação
    // Seleciona todos os links da navegação que apontam para uma seção da página
    const navLinks = document.querySelectorAll('.main-nav a[href^="#"]');

    // Adiciona um evento de clique para cada um desses links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Previne o comportamento padrão do link (que é pular para a seção)
            e.preventDefault();

            // Pega o ID da seção do atributo 'href' do link (ex: '#protocolo')
            const targetId = this.getAttribute('href');
            
            // Encontra o elemento da seção correspondente
            const targetElement = document.querySelector(targetId);

            // Se o elemento existir, rola a página suavemente até ele
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth' // Define a animação de rolagem para suave
                });
            }
        });
    });

    // 2. Validação Simples do Formulário de Agendamento
    // Seleciona o formulário pelo seu nome de classe
    const agendamentoForm = document.querySelector('.agendamento-form');

    // Adiciona um evento de 'submit' (quando o formulário é enviado)
    agendamentoForm.addEventListener('submit', function(e) {
        
        // Seleciona os campos de nome e telefone
        const nome = document.querySelector('input[name="nome"]');
        const telefone = document.querySelector('input[name="telefone"]');

        // Checa se os campos estão vazios
        if (nome.value.trim() === '' || telefone.value.trim() === '') {
            // Se estiverem vazios, exibe um alerta e impede o envio do formulário
            alert('Por favor, preencha todos os campos obrigatórios.');
            e.preventDefault(); 
        } else {
            // Se estiverem preenchidos, exibe uma mensagem de sucesso
            alert('Seu agendamento foi enviado com sucesso! Entraremos em contato em breve.');
            // O formulário será enviado, mas para um projeto real, você teria aqui o código para enviar os dados para um servidor.
        }
    });

});
