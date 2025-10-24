<?php
// admin_api.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Simple admin-only API to list and manage users/professionals.
// Protect: only admin role can access.
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden', 'message' => 'Acesso restrito a administradores']);
    exit;
}

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
    http_response_code(500);
    echo json_encode(['error' => 'db', 'message' => 'Erro na conexão com o banco']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Return lists of users and professionals
    $users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();
    $profs = $pdo->query('SELECT id, name, email, created_at FROM professionals ORDER BY id DESC')->fetchAll();
    echo json_encode(['ok' => true, 'users' => $users, 'professionals' => $profs]);
    exit;
}

// POST actions
$action = isset($_POST['action']) ? $_POST['action'] : '';
if (!$action) {
    echo json_encode(['error' => 'missing_action', 'message' => 'Ação não informada']);
    exit;
}

try {
    if ($action === 'create_user') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? 'user');
        if ($name === '' || $email === '' || $password === '') throw new Exception('Dados incompletos');
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
        $stmt->execute([$name, $email, $hash, $role]);
        echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
        exit;
    }

    if ($action === 'delete') {
        $table = ($_POST['table'] ?? 'users') === 'professionals' ? 'professionals' : 'users';
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('id inválido');
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'promote_to_professional') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('id inválido');
        // move user to professionals table
        $pdo->beginTransaction();
        $u = $pdo->prepare('SELECT name,email,password_hash FROM users WHERE id = ? LIMIT 1');
        $u->execute([$id]);
        $row = $u->fetch();
        if (!$row) throw new Exception('usuário não encontrado');
        $ins = $pdo->prepare('INSERT INTO professionals (name,email,password_hash,created_at) VALUES (?,?,?,NOW())');
        $ins->execute([$row['name'],$row['email'],$row['password_hash']]);
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        $pdo->commit();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'move_prof_to_user') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('id inválido');
        $pdo->beginTransaction();
        $u = $pdo->prepare('SELECT name,email,password_hash FROM professionals WHERE id = ? LIMIT 1');
        $u->execute([$id]);
        $row = $u->fetch();
        if (!$row) throw new Exception('profissional não encontrado');
        $ins = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
        $ins->execute([$row['name'],$row['email'],$row['password_hash'],'user']);
        $pdo->prepare('DELETE FROM professionals WHERE id = ?')->execute([$id]);
        $pdo->commit();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'promote_to_admin') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('id inválido');
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute(['admin',$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // default: unsupported
    echo json_encode(['error' => 'unsupported_action', 'message' => 'Ação não suportada']);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
    exit;
}

?>