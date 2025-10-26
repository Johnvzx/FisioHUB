<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
  header('Location: /src/login.html');
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Área de Administração</title>
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
                <h2>Dashboard Administrativo</h2>
                <p class="topbar-subtitle">Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong></p>
                <a href="/src/index.html" class="nav-link">Sair</a>
            </div>
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></div>
                        <div class="user-role">Administrador</div>
                    </div>
                </div>
            </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid fade-in">
            <div class="stat-card purple">
                <div class="stat-header">
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-title">Total de Usuários</div>
                <div class="stat-value" id="total-users">0</div>
            </div>

            <div class="stat-card green">
                  <div class="stat-header">
                    <div class="stat-icon">📅</div>
                </div>
                <div class="stat-title">Agendamentos</div>
                <div class="stat-value" id="total-appointments">0</div>
            </div>

            <div class="stat-card orange">
                <div class="stat-header">
                <div class="stat-icon">👨‍⚕️</div>
                </div>
                <div class="stat-title">Profissionais Ativos</div>
                <div class="stat-value" id="total-professionals">0</div>
            </div>

            <div class="stat-card blue">
                <div class="stat-header">
                <div class="stat-icon">⚡</div>
                </div>
                <div class="stat-title">Admins</div>
                <div class="stat-value" id="total-admins">0</div>
            </div>
</div>

<!-- Main Grid -->
<div class="grid-layout"><div>

<!-- Users Card -->
<div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">
                                <span>👥</span>
                                Gerenciar Usuários
                            </h3>
                        </div>

                        <form id="create-user" class="form-row" style="margin-bottom: 30px;">
                            <div class="form-group">
                                <label class="form-label">Nome</label>
                                <input type="text" name="name" placeholder="Nome completo" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" placeholder="email@exemplo.com" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Senha</label>
                                <input type="password" name="password" placeholder="••••••••" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tipo</label>
                                <select name="role" id="user-role-select">
                                    <option value="user">Usuário</option>
                                    <option value="professional">Profissional</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="form-group" id="specialty-field" style="display: none;">
                                <label class="form-label">Especialidade</label>
                                <select name="specialty" id="specialty-select">
                                    <option value="">Selecione uma especialidade</option>
                                    <option value="Fisioterapia Ortopédica">Fisioterapia Ortopédica</option>
                                    <option value="Pilates Clínico">Pilates Clínico</option>
                                    <option value="Fisioterapia Neurológica">Fisioterapia Neurológica</option>
                                    <option value="Fisioterapia Pélvica">Fisioterapia Pélvica</option>
                                    <option value="Fisioterapia Esportiva">Fisioterapia Esportiva</option>
                                    <option value="Fisioterapia Geriátrica">Fisioterapia Geriátrica</option>
                                </select>
                            </div>
                            <div class="form-group" style="display: flex; align-items: flex-end;">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">
                                    Adicionar 
                                </button>
                            </div>
                        </form>

                        <div class="search-box">
                            <input type="text" class="table-search" data-target="#users-table" placeholder="Buscar usuários por nome, email...">
                        </div>

                        <div id="users-wrap"></div>
                        <table id="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Cargo</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
</div>

<!-- agendamentos -->
<div class="card fade-in">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>📅</span>
                        Agendamentos
                    </h3>
                </div>


                <div class="search-box">
                    <input type="text" class="table-search" data-target="#appts-table" placeholder="Buscar agendamentos...">
                </div>

                <div id="appts-wrap"></div>
                <table id="appts-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Serviço</th>
                            <th>Profissional</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
<div>
                
<!-- admin -->
<div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">
                            Administradores
                        </h3>
                    </div>

                    <div class="search-box">
                        <input type="text" class="table-search" data-target="#admins-table" placeholder="Buscar admins...">
                    </div>

                    <div id="admins-wrap"></div>
                    <table id="admins-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
</div>

<!-- profissional -->
<div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">
                            Profissionais
                        </h3>
                    </div>

                    <div class="search-box">
                        <input type="text" class="table-search" data-target="#profs-table" placeholder="Buscar profissionais...">
                    </div>

                    <div id="profs-wrap"></div>
                    <table id="profs-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profissional</th>
                        <th>Email</th>
                        <th>Especialidade</th>
                    </tr>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
</div>
</main>

<script src="/src/js/admin_dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
