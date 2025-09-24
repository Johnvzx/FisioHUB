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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vittalis</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="media.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="img/vittalis.png">
    
</head>
<body>

    <header class="main-header">
    <div class="header-container">
        <div class="logo"><img src="img/vittalis.png"></div>

        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="">PLANOS</a></li>
                <li><a href="">SERVIÇOS</a></li>
                <li><a href="">PROFISSIONAIS</a></li>
                <li><a href="">SOBRE NÓS</a></li>
            </ul>
        </nav>

        <div class="actions">
            <a class="lgn-button" href="cadastro_paciente.html">
                <i class="fas fa-user"></i>
            </a>
            <a class="cta-button" href="">
                <i class="fas fa-envelope"></i>
            </a>
        </div>

</div>
</header>

    <main>
        <section class="secao-principal">
            <h2>Bem-vindo!</h2>
            <p>Este é o conteúdo principal da sua página. Você pode adicionar parágrafos, imagens e outros elementos aqui.</p>
        </section>

        <section class="secao-secundaria">
            <h2>Outra Seção</h2>
            <p>Aqui você pode falar sobre outro tópico ou mostrar mais conteúdo.</p>
            <ul>
                <li>Item 1</li>
                <li>Item 2</li>
                <li>Item 3</li>
            </ul>
        </section>
    </main>


        <section id="beneficios-fisio" class="beneficios-fisio-section">
            <h2>Nossos Benefícios</h2>
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


    <footer>
        <p>&copy; 2024 Meu Site. Todos os direitos reservados.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>