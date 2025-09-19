<?php
$host = 'db';
$dbname = 'mydatabase';
$username = 'root';
$password = 'mypassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FisioHub</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="img/teste.png">

</head>
<body>

        <header class="main-header">
        <img src="img/teste.png" alt="logo" class="logo-fisio">
        <nav class="main-nav">
            <ul>
                <li><a href="#protocolo">Protocolo</a></li>
                <li><a href="#beneficios">Planos</a></li>
                <li><a href="#atendimento">Profissionais</a></li>
                <li><a href="#contato">Contato</a></li>
            </ul>
        </nav>
        </header>

    <main>
        <section id="hero" class="hero-section">
            <div class="hero-content">
                <h1>Título de impacto para a clínica</h1>
                <p>Texto que explica a proposta de valor, o que a clínica oferece e qual problema resolve.</p>
                <a href="#agendamento" class="btn-agendar-hero">AGENDAR CONSULTA</a>
            </div>
        </section>

        <!-- <section id="protocolo" class="protocolo-section">
            <h2>O Protocolo que Funciona</h2>
            <p>Descreva o método de atendimento da clínica, passo a passo.</p>
            </section> -->

            <section id="beneficios-fisio" class="beneficios-fisio-section">
            <h2>Benefícios da Fisioterapia</h2>
            <p>A fisioterapia oferece uma ampla gama de benefícios que vão além da recuperação de lesões, melhorando a qualidade de vida, promovendo bem-estar e autonomia.</p>

            <div class="beneficios-container">
                <div class="beneficio-item-fisio">
                <img src="img/alivio-de-dor.png" alt="Ícone de Alívio da Dor" class="beneficio-icone">                    
                <h3>Alívio da Dor</h3>
                    <p>Ajuda a reduzir dores crônicas ou agudas através de técnicas manuais e exercícios, evitando a necessidade de medicamentos ou cirurgias.</p>
                </div>
                
                <div class="beneficio-item-fisio">
                <img src="img/melhora-mobilidade.png" alt="Ícone de Mobilidade" class="beneficio-icone">
                    <h3>Melhora da Mobilidade</h3>
                    <p>Restaura a amplitude de movimento e a força, permitindo que a pessoa retorne às suas atividades diárias com mais autonomia.</p>
                </div>
                <div class="beneficio-item-fisio">
                <img src="img/reablitação.png" alt="Ícone de Reabilitação" class="beneficio-icone">
                    <h3>Reabilitação Pós-cirúrgica</h3>
                    <p>Acelera a recuperação, restaurando a função do membro ou área afetada e evitando complicações como rigidez ou perda de massa muscular.</p>
                </div>
                
                <div class="beneficio-item-fisio">
                <img src="img/aumento-de-capacidade.png" alt="Ícone de Capacidade Física" class="beneficio-icone">
                    <h3>Aumento da Capacidade Física</h3>
                    <p>Melhora a capacidade respiratória, fortalece o corpo e contribui para um estilo de vida mais ativo e saudável.</p>
                </div>
            </div>
            </section>

        <section id="contato" class="contato-section">
            <h2>Contato</h2>
            <p>Endereço, telefone, e-mail e redes sociais.</p>
            </section>
    </main>

    <script src="script.js"></script>
</body>
</html>