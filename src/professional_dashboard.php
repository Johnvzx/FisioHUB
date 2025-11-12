<?php
session_start();
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id'])) {
  header('Location: /src/login.html');
  exit;
}

if (($_SESSION['user_role'] ?? '') !== 'professional') {
  $role = $_SESSION['user_role'] ?? '';
  if ($role === 'user') {
    header('Location: /src/user_dashboard.php');
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
  <title>Área Profissional - Vittalis</title>
  <link rel="stylesheet" href="/src/css/professional_dashboard.css">
</head>
<body>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <div class="header-left">
        <h1>Área Profissional</h1>
        <p>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Profissional'); ?></strong></p>
      </div>
      <div class="header-right">
        <div class="user-avatar">
          <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'P', 0, 1)); ?>
        </div>
        <a href="/src/logout.php" class="btn btn-outline">Sair</a>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-label">Total de Agendamentos</div>
        <div class="stat-value" id="stat-appointments">0</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-label">Pacientes Únicos</div>
        <div class="stat-value" id="stat-pacientes">0</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🧾</div>
        <div class="stat-label">Serviços Distintos</div>
        <div class="stat-value" id="stat-servicos">0</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Receita Total</div>
        <div class="stat-value" id="stat-revenue">R$ 0,00</div>
      </div>
    </div>

    <!-- Agenda -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">
          <span>📋</span>
          Agenda e Pacientes
        </h2>
      </div>
      
      <div class="search-box">
        <input type="text" id="search-input" placeholder="🔍 Buscar por nome, email, serviço...">
      </div>
      <div id="appointments-container">
        <div class="loading">
          <div class="spinner"></div>
          <p>Carregando agenda...</p>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="/src/js/professional_dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>