<?php
// Simple endpoint to expose current session role for client-side redirects
session_start();
header('Content-Type: application/json; charset=utf-8');

$role = $_SESSION['user_role'] ?? null;
$name = $_SESSION['user_name'] ?? null;
$id = $_SESSION['user_id'] ?? null;

echo json_encode([
    'loggedIn' => isset($_SESSION['user_id']),
    'role' => $role,
    'user' => [
        'id' => $id,
        'name' => $name,
    ]
]);
