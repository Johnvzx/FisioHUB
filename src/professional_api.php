<?php
session_start();

// Renovar atividade da sessão
$_SESSION['LAST_ACTIVITY'] = time();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'professional') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden', 'message' => 'Acesso restrito a profissionais']);
    exit;
}

$profId = intval($_SESSION['user_id']);

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

try {
    $pdo->exec("ALTER TABLE professionals ADD COLUMN specialty VARCHAR(150) DEFAULT NULL");
} catch (Exception $e) { /* ignore */ }

try {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN professional_id INT UNSIGNED NULL AFTER id");
    $pdo->exec("CREATE INDEX idx_appointments_professional_id ON appointments (professional_id)");
} catch (Exception $e) { /* ignore */ }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Log para debug
    error_log("🔍 Professional API GET - profId: " . $profId);
    
    $profileStmt = $pdo->prepare('SELECT id, name, email, specialty, created_at FROM professionals WHERE id = ? LIMIT 1');
    $profileStmt->execute([$profId]);
    $profile = $profileStmt->fetch() ?: [];
    
    error_log("👤 Profile encontrado: " . json_encode($profile));

    // Lista de agendamentos do profissional logado
    try {
        $stmt = $pdo->prepare('SELECT id, professional_id, name, email, phone, service, message, created_at, appointment_date, appointment_time, price, status FROM appointments WHERE professional_id = ? ORDER BY id DESC');
        $stmt->execute([$profId]);
        $appts = $stmt->fetchAll();
        error_log("📋 Agendamentos encontrados: " . count($appts));
        error_log("📦 Dados: " . json_encode($appts));
    } catch (Exception $e) {
        $appts = [];
    }

    echo json_encode(['ok' => true, 'profile' => $profile, 'appointments' => $appts]);
    exit;
}

if ($method !== 'POST') {
    echo json_encode(['error' => 'unsupported_method', 'message' => 'Use POST para ações']);
    exit;
}

$action = $_POST['action'] ?? '';
if (!$action) {
    echo json_encode(['error' => 'missing_action', 'message' => 'Ação não informada']);
    exit;
}

try {
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $specialty = trim($_POST['specialty'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($name === '' || $email === '') throw new Exception('Nome e email são obrigatórios');

        // Verificar se email já existe em outro profissional
        $chk = $pdo->prepare('SELECT id FROM professionals WHERE email = ? AND id <> ? LIMIT 1');
        $chk->execute([$email, $profId]);
        if ($chk->fetch()) throw new Exception('E-mail já cadastrado');

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE professionals SET name = ?, email = ?, specialty = ?, password_hash = ? WHERE id = ?');
            $stmt->execute([$name, $email, $specialty, $hash, $profId]);
        } else {
            $stmt = $pdo->prepare('UPDATE professionals SET name = ?, email = ?, specialty = ? WHERE id = ?');
            $stmt->execute([$name, $email, $specialty, $profId]);
        }

        // Atualiza nome da sessão se alterado
        $_SESSION['user_name'] = $name;
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'update_status') {
        $apptId = intval($_POST['appointment_id'] ?? 0);
        $newStatus = strtolower(trim($_POST['status'] ?? ''));
        $allowed = ['pendente','confirmada','em_andamento','concluida','cancelada'];
        if (!$apptId || !in_array($newStatus, $allowed, true)) {
            echo json_encode(['error' => 'invalid_input', 'message' => 'Dados inválidos']);
            exit;
        }
        // Normalizar para stored values
        $map = [
            'pendente' => 'pending',
            'confirmada' => 'confirmed',
            'em_andamento' => 'in_progress',
            'concluida' => 'completed',
            'cancelada' => 'canceled'
        ];
        $dbStatus = $map[$newStatus] ?? 'pending';
        // Garantir que o agendamento pertence ao profissional logado
        $chk = $pdo->prepare('SELECT id FROM appointments WHERE id = ? AND professional_id = ? LIMIT 1');
        $chk->execute([$apptId, $profId]);
        if (!$chk->fetch()) {
            echo json_encode(['error' => 'not_found', 'message' => 'Agendamento não encontrado']);
            exit;
        }
        $up = $pdo->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        $up->execute([$dbStatus, $apptId]);
        echo json_encode(['ok' => true, 'appointment_id' => $apptId, 'status' => $dbStatus]);
        exit;
    }

    echo json_encode(['error' => 'unsupported_action', 'message' => 'Ação não suportada']);
    exit;
} catch (Exception $e) {
    echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
    exit;
}
