<?php
session_start();

// Renovar atividade da sessão
$_SESSION['LAST_ACTIVITY'] = time();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'user') {
  http_response_code(401);
  echo json_encode(['ok' => false, 'message' => 'Não autorizado']);
  exit;
}

try {
  $dbHost = getenv('MYSQL_HOST') ?: 'db';
  $dbName = getenv('MYSQL_DATABASE') ?: 'mydatabase';
  $dbUser = getenv('MYSQL_USER') ?: 'root';
  $dbPass = getenv('MYSQL_PASSWORD') ?: 'mypassword';

  $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_SESSION['user_id'];
    
    // Buscar email do usuário na tabela users
    $stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
      echo json_encode(['ok' => false, 'message' => 'Usuário não encontrado']);
      exit;
    }

    $sql = "
      SELECT 
        a.id,
        a.name,
        a.email,
        a.phone,
        a.service,
        a.message,
        a.created_at,
        a.appointment_date,
        a.appointment_time,
        a.price,
        a.status,
        a.professional_id,
        p.name as professional_name,
        p.specialty
      FROM appointments a
      LEFT JOIN professionals p ON a.professional_id = p.id
      WHERE a.user_id = ? OR a.email = ?
      ORDER BY 
        CASE 
        WHEN a.appointment_date IS NOT NULL THEN a.appointment_date
        ELSE a.created_at
        END DESC,
        a.appointment_time DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $user['email']]);
    $appointments = $stmt->fetchAll();

    echo json_encode([
      'ok' => true,
      'appointments' => $appointments,
      'user' => [
        'name' => $user['name'],
        'email' => $user['email']
      ]
    ]);
    exit;
  }

  // Método não permitido
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Método não permitido']);

} catch (PDOException $e) {
  error_log("Erro na API do usuário: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['ok' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
} catch (Exception $e) {
  error_log("Erro geral na API: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['ok' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
