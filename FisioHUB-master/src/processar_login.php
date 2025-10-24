<?php
// processar_login.php
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

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$email || !$password) {
    header('Location: login.html');
    exit;
}

$profStmt = $pdo->prepare('SELECT id, password_hash, name FROM professionals WHERE email = ? LIMIT 1');
$profStmt->execute([$email]);
$prof = $profStmt->fetch();

if ($prof && password_verify($password, $prof['password_hash'])) {
    // Autenticado como profissional
    $_SESSION['user_id'] = $prof['id'];
    $_SESSION['user_name'] = $prof['name'];
    $_SESSION['user_role'] = 'professional';
    header('Location: /professional_dashboard.php');
    exit;
}

// Se não for profissional, verifica tabela users (usuários regulares)
$stmt = $pdo->prepare('SELECT id, password_hash, name, role FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    // Sucesso como usuário normal
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    // Redireciona conforme a role
    if (isset($user['role']) && $user['role'] === 'admin') {
        header('Location: /admin_dashboard.php');
    } else {
        header('Location: /user_dashboard.php');
    }
    exit;
}

// Falha de autenticação
header('Location: login.html');
exit;

?>
