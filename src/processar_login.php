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

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$email || !$password) {
    header('Location: /src/login.html?error=missing_fields');
    exit;
}

$loginAs = strtolower(trim($_POST['login_as'] ?? ''));

// Preparar consultas
$profStmt = $pdo->prepare('SELECT id, password_hash, name FROM professionals WHERE email = ? LIMIT 1');
$userStmt = $pdo->prepare('SELECT id, password_hash, name, role FROM users WHERE email = ? LIMIT 1');

// Funções auxiliares de autenticação
$authProfessional = function() use ($profStmt, $email, $password) {
    $profStmt->execute([$email]);
    $prof = $profStmt->fetch();
    if ($prof && password_verify($password, $prof['password_hash'])) {
        $_SESSION['user_id'] = $prof['id'];
        $_SESSION['user_name'] = $prof['name'];
        $_SESSION['user_role'] = 'professional';
        $_SESSION['LAST_ACTIVITY'] = time(); // Inicializar controle de inatividade
        header('Location: /src/professional_dashboard.php');
        exit;
    }
    return false;
};

$authUser = function() use ($userStmt, $email, $password) {
    $userStmt->execute([$email]);
    $user = $userStmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        $_SESSION['LAST_ACTIVITY'] = time(); // Inicializar controle de inatividade
        if (isset($user['role']) && $user['role'] === 'admin') {
            header('Location: /src/admin_dashboard.php');
        } else {
            header('Location: /src/user_dashboard.php');
        }
        exit;
    }
    return false;
};

// Ordem de autenticação: por padrão prioriza usuário/admin; se login_as=professional, prioriza profissional
if ($loginAs === 'professional') {
    if ($authProfessional() !== false) { /* redirected */ }
    if ($authUser() !== false) { /* redirected */ }
} else {
    if ($authUser() !== false) { /* redirected */ }
    if ($authProfessional() !== false) { /* redirected */ }
}

// Checar se email existe em alguma tabela para retornar mensagem adequada
$exists = false;
$check = $pdo->prepare('(
    SELECT id FROM users WHERE email = ? LIMIT 1
) UNION (
    SELECT id FROM professionals WHERE email = ? LIMIT 1
) LIMIT 1');
$check->execute([$email, $email]);
$exists = (bool)$check->fetch();

if ($exists) {
    // Email existe mas senha inválida
    header('Location: /src/login.html?error=invalid_credentials');
} else {
    // Email não cadastrado
    $prefill = urlencode($email);
    header("Location: /src/login.html?error=user_not_found&panel=register&prefillEmail=$prefill");
}
exit;

?>
