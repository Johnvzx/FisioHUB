<?php
session_start();

// Atualizar timestamp de atividade
$_SESSION['LAST_ACTIVITY'] = time();

// Verificar se a sessão pertence a este tipo de usuário
if (!isset($_SESSION['user_id'])) {
  header('Location: /src/login.html');
  exit;
}

// Se o usuário atual não é do tipo 'user', redirecionar
if (($_SESSION['user_role'] ?? '') !== 'user') {
  // Conta foi trocada, redirecionar para o dashboard correto
  $role = $_SESSION['user_role'] ?? '';
  if ($role === 'professional') {
    header('Location: /src/professional_dashboard.php');
  } elseif ($role === 'admin') {
    header('Location: /src/admin_dashboard.php');
  } else {
    header('Location: /src/login.html');
  }
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Usuário - Vittalis</title>
  <link rel="stylesheet" href="/src/css/user_dashboard.css">

</head>
<body>
  <div class="container">
    <div id="global-loader" class="global-loader" style="display:flex;">
      <div class="center">
        <div class="spinner"></div>
        <div class="msg loader-message">Carregando suas consultas...</div>
      </div>
    </div>
    <!-- Header -->
    <div class="header">
      <div class="header-left">
        <h1>Minha Área</h1>
        <p>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Paciente'); ?></strong></p>
      </div>
      <div class="header-right">
        <div class="user-avatar">
          <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'P', 0, 1)); ?>
        </div>
        <a href="/src/agendar.html" class="btn btn-primary">Nova Consulta</a>
        <a href="/src/logout.php" class="btn btn-outline">Sair</a>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-label">Total de Consultas</div>
        <div class="stat-value" id="stat-total">0</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⏰</div>
        <div class="stat-label">Próximas Consultas</div>
        <div class="stat-value" id="stat-upcoming">0</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-label">Consultas Realizadas</div>
        <div class="stat-value" id="stat-completed">0</div>
      </div>
    </div>

    <!-- Appointments -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">
          <span>📋</span>
          Minhas Consultas
        </h2>
      </div>
      
      <div id="appointments-container">
        <div class="loading">
          <div class="spinner"></div>
          <p>Carregando suas consultas...</p>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/src/js/user_dashboard.js"></script>
</body>
</html>
