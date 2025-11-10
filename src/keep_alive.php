<?php
session_start();

header('Content-Type: application/json');

// Verificar se tem sessão ativa
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'no_session']);
    exit;
}

// Verificar inatividade (5 minutos = 300 segundos)
$inactive_timeout = 300;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $inactive_timeout)) {
    // Sessão expirada por inatividade
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'session_expired']);
    exit;
}

// Renovar timestamp de atividade
$_SESSION['LAST_ACTIVITY'] = time();
echo json_encode([
    'ok' => true, 
    'timestamp' => time(),
    'user_id' => $_SESSION['user_id'],
    'user_role' => $_SESSION['user_role'] ?? null
]);

