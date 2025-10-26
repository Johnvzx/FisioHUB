<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
if ($action !== 'create_appointment') {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden', 'message' => 'Acesso restrito a administradores']);
        exit;
    }
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

// Ensure appointments table exists (helpful if migrations/init.sql wasn't executed)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        service VARCHAR(150) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    // if creation fails, continue; other parts of the code handle missing table gracefully
}

// Ensure professional link column on appointments
try {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN professional_id INT UNSIGNED NULL AFTER id");
    $pdo->exec("CREATE INDEX idx_appointments_professional_id ON appointments (professional_id)");
} catch (Exception $e) {
    // column/index might already exist
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();
    
    // Add specialty column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE professionals ADD COLUMN specialty VARCHAR(150) DEFAULT NULL");
    } catch (Exception $e) {
        // Column might already exist, ignore
    }
    
    $profs = $pdo->query('SELECT id, name, email, specialty, created_at FROM professionals ORDER BY id DESC')->fetchAll();
    $admins = $pdo->query('SELECT id, name, email, created_at FROM users WHERE role = "admin" ORDER BY id DESC')->fetchAll();
    // carregar agendamentos (se existir a tabela)
    try {
        $appts = $pdo->query('SELECT id, professional_id, name, email, phone, service, message, created_at FROM appointments ORDER BY id DESC')->fetchAll();
    } catch (Exception $e) {
        $appts = [];
    }
    echo json_encode(['ok' => true, 'users' => $users, 'professionals' => $profs, 'admins' => $admins, 'appointments' => $appts]);
    exit;
}

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
        $specialty = trim($_POST['specialty'] ?? '');
        
        if ($name === '' || $email === '' || $password === '') throw new Exception('Dados incompletos');
        
        // If creating a professional, specialty is required
        if ($role === 'professional' && $specialty === '') {
            throw new Exception('Especialidade é obrigatória para profissionais');
        }
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // If role is professional, add directly to professionals table
            if ($role === 'professional') {
                $stmt = $pdo->prepare('INSERT INTO professionals (name,email,password_hash,specialty,created_at) VALUES (?,?,?,?,NOW())');
                $stmt->execute([$name, $email, $hash, $specialty]);
            } else {
                // Otherwise add to users table with role
                $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
                $stmt->execute([$name, $email, $hash, $role]);
            }
            echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
            exit;
        } catch (PDOException $pe) {
            // Duplicate entry (unique email) -> MySQL error code 1062
            $sqlState = $pe->errorInfo[1] ?? null;
            if ($sqlState == 1062) {
                echo json_encode(['error' => 'duplicate_email', 'message' => 'E-mail já cadastrado']);
                exit;
            }
            throw $pe;
        }
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
        $specialty = trim($_POST['specialty'] ?? '');
        if ($id <= 0) throw new Exception('id inválido');
        if ($specialty === '') throw new Exception('Especialidade é obrigatória');
        
        $pdo->beginTransaction();
        $u = $pdo->prepare('SELECT name,email,password_hash FROM users WHERE id = ? LIMIT 1');
        $u->execute([$id]);
        $row = $u->fetch();
        if (!$row) throw new Exception('usuário não encontrado');
        
        $ins = $pdo->prepare('INSERT INTO professionals (name,email,password_hash,specialty,created_at) VALUES (?,?,?,?,NOW())');
        $ins->execute([$row['name'],$row['email'],$row['password_hash'],$specialty]);
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

    if ($action === 'demote_admin') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('id inválido');
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute(['user', $id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Editar profissional
    if ($action === 'edit_professional') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $specialty = trim($_POST['specialty'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if ($id <= 0) throw new Exception('id inválido');
        if ($name === '' || $email === '') throw new Exception('Nome e email são obrigatórios');
        if ($specialty === '') throw new Exception('Especialidade é obrigatória');
        
        try {
            if ($password !== '') {
                // Update with new password
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE professionals SET name = ?, email = ?, specialty = ?, password_hash = ? WHERE id = ?');
                $stmt->execute([$name, $email, $specialty, $hash, $id]);
            } else {
                // Update without changing password
                $stmt = $pdo->prepare('UPDATE professionals SET name = ?, email = ?, specialty = ? WHERE id = ?');
                $stmt->execute([$name, $email, $specialty, $id]);
            }
            echo json_encode(['ok' => true]);
            exit;
        } catch (PDOException $pe) {
            $sqlState = $pe->errorInfo[1] ?? null;
            if ($sqlState == 1062) {
                echo json_encode(['error' => 'duplicate_email', 'message' => 'E-mail já cadastrado']);
                exit;
            }
            throw $pe;
        }
    }

    // Editar administrador
if ($action === 'edit_admin') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if ($id <= 0) throw new Exception('id inválido');
        if ($name === '' || $email === '') throw new Exception('Nome e email são obrigatórios');
        
        try {
            if ($password !== '') {
                // Update with new password
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?');
                $stmt->execute([$name, $email, $hash, $id]);
            } else {
                // Update without changing password
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
                $stmt->execute([$name, $email, $id]);
            }
            echo json_encode(['ok' => true]);
            exit;
        } catch (PDOException $pe) {
            $sqlState = $pe->errorInfo[1] ?? null;
            if ($sqlState == 1062) {
                echo json_encode(['error' => 'duplicate_email', 'message' => 'E-mail já cadastrado']);
                exit;
            }
            throw $pe;
        }
    }

if ($action === 'create_appointment') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $service = trim($_POST['service'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $professionalId = isset($_POST['professional_id']) && $_POST['professional_id'] !== '' ? intval($_POST['professional_id']) : null;
        
        // Log para debug (remover em produção)
        error_log("📝 Create appointment - professional_id recebido: " . ($_POST['professional_id'] ?? 'não enviado'));
        error_log("📝 professional_id processado: " . ($professionalId ?? 'null'));
        
        if ($name === '' || $email === '') throw new Exception('Dados incompletos');
        
        // Build dynamic insert with/without professional_id
        if ($professionalId !== null && $professionalId > 0) {
            $stmt = $pdo->prepare('INSERT INTO appointments (professional_id, name, email, phone, service, message, created_at) VALUES (?,?,?,?,?,?,NOW())');
            $stmt->execute([$professionalId, $name, $email, $phone, $service, $message]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO appointments (name, email, phone, service, message, created_at) VALUES (?,?,?,?,?,NOW())');
            $stmt->execute([$name, $email, $phone, $service, $message]);
        }
        echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId(), 'professional_id' => $professionalId]);
        exit;
    }

    // Deletar agendamento
    if ($action === 'delete_appointment') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('id inválido');
        $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['error' => 'unsupported_action', 'message' => 'Ação não suportada']);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
    exit;
}

?>