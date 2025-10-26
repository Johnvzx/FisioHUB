<?php

session_start();

$dbHost = getenv('MYSQL_HOST') ?: 'db';
$dbName = getenv('MYSQL_DATABASE') ?: 'mydatabase';
$dbUser = getenv('MYSQL_USER') ?: 'root';
$dbPass = getenv('MYSQL_PASSWORD') ?: 'mypassword';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    die('Erro na conexão com o banco: ' . $e->getMessage());
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$email || !$password) {
    header('Location: /src/login.html');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header('Location: /src/login.html');
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$role = 'user';
$insert = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$insert->execute([$name, $email, $passwordHash, $role]);

$userId = $pdo->lastInsertId();
$_SESSION['user_id'] = $userId;
$_SESSION['user_name'] = $name;
$_SESSION['user_role'] = $role;

?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cadastro realizado</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#f5f7fb}
        .card{background:#fff;padding:24px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.08);max-width:420px;text-align:center}
        .btn{display:inline-block;margin-top:16px;padding:10px 18px;border-radius:6px;background:#2c5282;color:#fff;text-decoration:none}
        .btn-secondary{background:#6b7280}
    </style>
</head>
<body>
    <div class="card">
        <h2>Conta criada e login efetuado</h2>
        <p>Olá, <strong><?= htmlspecialchars($name ?: 'usuário') ?></strong>. Sua conta foi criada e você já está logado.</p>
        <p>Pode continuar quando quiser para o painel ou permanecer nesta página.</p>
        <p>
            <a class="btn" href="/src/dashboard.php">Ir para o painel</a>
            <a class="btn btn-secondary" href="/src/logout.php">Sair</a>
        </p>
    </div>
</body>
</html>

<?php
exit;

?>
