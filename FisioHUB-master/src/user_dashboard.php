<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'user') {
    header('Location: login.html');
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Usuário</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:24px} .card{background:#fff;padding:16px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.06)}</style>
</head>
<body>
  <h1>Área do Usuário</h1>
  <p>Bem-vindo, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>.</p>
  <div class="card">
    <h2>Suas informações</h2>
    <p>Este espaço é destinado aos usuários (pacientes/clientes).</p>
  </div>

  <p><a href="/logout.php">Sair</a></p>
</body>
</html>
