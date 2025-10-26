<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'professional') {
  header('Location: /src/login.html');
  exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Área do Profissional</title>
  <link rel="stylesheet" href="/src/css/admin_dashboard.css">
</head>

<body>

  <!-- Sidebar -->
<aside class="sidebar">
        </div>
            </li>
        </ul>
</aside>
<!-- Main Content -->
<main class="main-content">

<!-- Top Bar -->
<div class="topbar fade-in">
  <div class="topbar-left">
    <h2>Área do Profissional</h2>
    <p class="topbar-subtitle">Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Profissional'); ?></strong></p>
    <a href="/src/logout.php" class="nav-link">Sair</a>
  </div>
  <div class="user-profile">
    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'P', 0, 1)); ?></div>
    <div class="user-info">
      <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Profissional'); ?></div>
      <div class="user-role">Profissional</div>
    </div>
  </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid fade-in">
  <div class="stat-card green">
    <div class="stat-header">
      <div class="stat-icon">📅</div>
    </div>
    <div class="stat-title">Agendamentos</div>
    <div class="stat-value" id="stat-appointments">0</div>
  </div>
  <div class="stat-card purple">
    <div class="stat-header">
      <div class="stat-icon">👥</div>
    </div>
    <div class="stat-title">Pacientes Únicos</div>
    <div class="stat-value" id="stat-pacientes">0</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-header">
      <div class="stat-icon">🧾</div>
    </div>
    <div class="stat-title">Serviços Distintos</div>
    <div class="stat-value" id="stat-servicos">0</div>
  </div>
</div>

<!-- Main Grid -->
<div>
<!-- Agenda e Pacientes -->
<div class="card fade-in" id="agenda">
  <div class="card-header">
    <h3 class="card-title"><span>📋</span> Agenda e Pacientes</h3>
  </div>
  <div class="search-box">
    <input type="text" class="table-search" data-target="#appts-table" placeholder="Buscar por nome, email, serviço...">
  </div>
  <div id="appts-wrap"></div>
  <table id="appts-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Telefone</th>
        <th>Serviço</th>
        <th></th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Relatórios pessoais -->
<div class="card fade-in" id="relatorios">
  <div class="card-header">
    <h3 class="card-title"><span>📈</span> Relatórios pessoais</h3>
  </div>
  <div class="chart-container">
    <canvas id="apptsChart" height="300"></canvas>
    </div>
    </div>
    </div>
    </div>
  </div>
</main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="/src/js/professional_dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
