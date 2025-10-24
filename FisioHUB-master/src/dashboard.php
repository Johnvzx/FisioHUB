<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// Redireciona para dashboard específico baseado na role
$role = $_SESSION['user_role'] ?? 'professional';
if ($role === 'admin') {
    header('Location: /admin_dashboard.php');
    exit;
} elseif ($role === 'professional') {
    header('Location: /professional_dashboard.php');
    exit;
}

// Se role desconhecida, mostra dashboard genérico
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
</head>
<body>
  <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuário') ?>!</h1>
  <p>Role: <?= htmlspecialchars($role) ?></p>
  <p>Você está logado.</p>
  <p><a href="/logout.php">Sair</a></p>
</body>
</html>
