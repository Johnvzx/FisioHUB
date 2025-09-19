<?php

// Dados de conexão com o banco de dados
$servername = "db"; // Nome do serviço no docker-compose.yml
$username = "root";
$password = "mypassword"; // A senha que você definiu
$dbname = "mydatabase"; // O nome do seu banco de dados

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checa a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Restante do seu código
// Dados de conexão com o banco de dados
$servername = "db";
// ...

// Pega os dados do formulário
$nome = $_POST['nome'];
$telefone = $_POST['telefone'];
$data_nascimento = $_POST['data_nascimento'];
$patologia = $_POST['patologia'];

// Prepara a query de inserção
$sql = "INSERT INTO pacientes (nome, telefone, data_nascimento, patologia) VALUES (?, ?, ?, ?)";

// Prepara a declaração SQL (para evitar injeção de SQL)
$stmt = $conn->prepare($sql);

// Associa os parâmetros
$stmt->bind_param("ssss", $nome, $telefone, $data_nascimento, $patologia);

// Executa a declaração
if ($stmt->execute()) {
    echo "<h1>Paciente cadastrado com sucesso!</h1>";
    echo "<a href='cadastro_paciente.html'>Voltar para o formulário</a>";
} else {
    echo "Erro ao cadastrar: " . $stmt->error;
}

// Fecha a conexão
$stmt->close();
$conn->close();

?>