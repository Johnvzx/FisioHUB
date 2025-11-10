<?php
session_start();

// Atualizar timestamp de atividade
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id'])) {
    header('Location: /src/login.html');
    exit;
}
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    // Conta foi trocada, redirecionar para o dashboard correto
    $role = $_SESSION['user_role'] ?? '';
    if ($role === 'user') {
        header('Location: /src/user_dashboard.php');
    } elseif ($role === 'professional') {
        header('Location: /src/professional_dashboard.php');
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
    <title>Painel Administrativo - Vittalis</title>
    <link rel="stylesheet" href="/src/css/admin_dashboard.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter','Segoe UI',Arial,sans-serif; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); min-height:100vh; padding:20px; }
        .container { max-width:1600px; margin:0 auto; }
        
        /* Header */
        .header { background:#fff; border-radius:20px; padding:30px; margin-bottom:30px; box-shadow:0 10px 40px rgba(0,0,0,.15); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; }
        .header-left h1 { font-size:1.8rem; color:#2d3748; margin-bottom:5px; font-weight:700; }
        .header-left p { color:#718096; font-size:0.95rem; }
        .header-right { display:flex; gap:15px; align-items:center; }
        .user-avatar { width:50px; height:50px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.3rem; font-weight:700; }
        
        /* Buttons */
        .btn { padding:10px 20px; border-radius:10px; font-weight:600; font-size:.9rem; text-decoration:none; border:2px solid transparent; transition:.25s; cursor:pointer; display:inline-block; }
        .btn-outline { background:#fff; color:#667eea; border-color:#667eea; } 
        .btn-outline:hover { background:#667eea; color:#fff; }
        .btn-primary { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; border:none; } 
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(102,126,234,.3); }
        .btn-sm { padding:6px 12px; font-size:.75rem; border-radius:7px; }
        .btn-danger { background:#e53e3e; color:#fff; border:none; } 
        .btn-danger:hover { background:#c53030; }
        .btn-secondary { background:#4a5568; color:#fff; border:none; } 
        .btn-secondary:hover { background:#2d3748; }
        
        /* Stats */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; margin-bottom:30px; }
        .stat-card { background:#fff; border-radius:16px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,.1); position:relative; overflow:hidden; transition:.3s; }
        .stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 25px rgba(0,0,0,.15); }
        .stat-icon { font-size:2.5rem; margin-bottom:10px; }
        .stat-label { font-size:.75rem; letter-spacing:.8px; text-transform:uppercase; color:#718096; font-weight:600; margin-bottom:5px; }
        .stat-value { font-size:2.2rem; font-weight:700; color:#2d3748; }
        
        /* Content Grid */
        .content-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:25px; }
        .card { background:#fff; border-radius:18px; padding:25px; box-shadow:0 8px 30px rgba(0,0,0,.12); display:flex; flex-direction:column; }
        .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #e2e8f0; }
        .card-title { font-size:1.15rem; color:#2d3748; display:flex; align-items:center; gap:8px; font-weight:700; }
        
        /* Forms */
        .form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:20px; }
        .form-field { display:flex; flex-direction:column; gap:5px; }
        .form-field label { font-size:.7rem; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:#4a5568; }
        .form-field input, .form-field select { padding:10px 12px; border:2px solid #e2e8f0; border-radius:8px; font-size:.9rem; transition:.2s; }
        .form-field input:focus, .form-field select:focus { outline:none; border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.15); }
        
        /* Search */
        .search-box { margin-bottom:15px; }
        .search-box input { width:100%; padding:11px 15px; border:2px solid #e2e8f0; border-radius:10px; font-size:.9rem; transition:.2s; }
        .search-box input:focus { outline:none; border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.15); }
        
        /* Tables */
        table { width:100%; border-collapse:collapse; }
        th, td { padding:12px 10px; font-size:.8rem; text-align:left; }
        thead th { background:#f7fafc; color:#4a5568; font-weight:700; border-bottom:2px solid #e2e8f0; text-transform:uppercase; letter-spacing:.5px; font-size:.7rem; }
        tbody tr { background:#fff; border-bottom:1px solid #edf2f7; transition:.2s; }
        tbody tr:hover { background:#f8fafc; }
        .table-actions { display:flex; gap:5px; flex-wrap:wrap; }
        
        /* Badges */
        .badge { padding:4px 10px; border-radius:20px; font-size:.65rem; font-weight:700; letter-spacing:.5px; text-transform:uppercase; }
        .badge-info { background:#ebf8ff; color:#2b6cb0; }
        .badge-warning { background:#fefcbf; color:#b7791f; }
        .badge-danger { background:#ffe6e6; color:#c53030; }
        
        /* Empty State */
        .empty-state { text-align:center; padding:50px 20px; color:#718096; }
        .empty-state-icon { font-size:3rem; opacity:.5; margin-bottom:15px; }
        
        /* Global Loader */
        .global-loader { position:fixed; inset:0; background:rgba(255,255,255,.92); backdrop-filter:saturate(1.2) blur(2px); display:flex; align-items:center; justify-content:center; z-index:9999; }
        .global-loader .center { display:flex; flex-direction:column; align-items:center; gap:12px; }
        .global-loader .spinner { width:50px; height:50px; border-radius:50%; border:4px solid #e5e7eb; border-top-color:#667eea; animation:spin 1s linear infinite; }
        .global-loader .msg { color:#4a5568; font-weight:600; font-size:.95rem; }
        @keyframes spin { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }
        
        /* Responsive */
        @media (max-width:1200px){ .content-grid { grid-template-columns:1fr; } }
        @media (max-width:768px){ .stats-grid { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:500px){ .stats-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <!-- Global Loader -->
        <div id="global-loader" class="global-loader" style="display:flex;">
            <div class="center">
                <div class="spinner"></div>
                <div class="msg loader-message">Carregando painel...</div>
            </div>
        </div>
        <div class="header">
            <div class="header-left">
                <h1>Painel Administrativo</h1>
                <p>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong></p>
            </div>
            <div class="header-right">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A',0,1)); ?></div>
                <a href="/src/logout.php" class="btn btn-outline">Sair</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-label">Usuários</div>
                <div class="stat-value" id="total-users">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-label">Agendamentos</div>
                <div class="stat-value" id="total-appointments">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨‍⚕️</div>
                <div class="stat-label">Profissionais</div>
                <div class="stat-value" id="total-professionals">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚡</div>
                <div class="stat-label">Admins</div>
                <div class="stat-value" id="total-admins">0</div>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">👥 Gerenciar Usuários</h2>
                </div>
                <form id="create-user" class="form-grid">
                    <div class="form-field">
                        <label>Nome</label>
                        <input type="text" name="name" required placeholder="Nome completo">
                    </div>
                    <div class="form-field">
                        <label>Email</label>
                        <input type="email" name="email" required placeholder="email@exemplo.com">
                    </div>
                    <div class="form-field">
                        <label>Senha</label>
                        <input type="password" name="password" required placeholder="••••••">
                    </div>
                    <div class="form-field">
                        <label>Tipo</label>
                        <select name="role" id="user-role-select">
                            <option value="user">Usuário</option>
                            <option value="professional">Profissional</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div id="specialty-field" class="form-field" style="display:none;">
                        <label>Especialidade</label>
                        <select name="specialty" id="specialty-select">
                            <option value="">Selecione</option>
                            <option value="Fisioterapia Ortopédica">Fisioterapia Ortopédica</option>
                            <option value="Pilates Clínico">Pilates Clínico</option>
                            <option value="Fisioterapia Neurológica">Fisioterapia Neurológica</option>
                            <option value="Fisioterapia Pélvica">Fisioterapia Pélvica</option>
                            <option value="Fisioterapia Esportiva">Fisioterapia Esportiva</option>
                            <option value="Fisioterapia Geriátrica">Fisioterapia Geriátrica</option>
                        </select>
                    </div>
                    <div class="form-field" style="display:flex; align-items:flex-end;">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Adicionar</button>
                    </div>
                </form>
                <div class="search-box"><input type="text" class="table-search" data-target="#users-table" placeholder="🔍 Buscar usuários..."></div>
                <div id="users-wrap"></div>
                <table id="users-table"><thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Cargo</th><th></th></tr></thead><tbody></tbody></table>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">📅 Agendamentos</h2></div>
                <div class="search-box"><input type="text" class="table-search" data-target="#appts-table" placeholder="🔍 Buscar agendamentos..."></div>
                <div id="appts-wrap"></div>
                <table id="appts-table"><thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Serviço</th><th>Profissional</th><th></th></tr></thead><tbody></tbody></table>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">⚡ Administradores</h2></div>
                <div class="search-box"><input type="text" class="table-search" data-target="#admins-table" placeholder="🔍 Buscar admins..."></div>
                <div id="admins-wrap"></div>
                <table id="admins-table"><thead><tr><th>ID</th><th>Nome</th><th>Email</th><th></th></tr></thead><tbody></tbody></table>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">👨‍⚕️ Profissionais</h2></div>
                <div class="search-box"><input type="text" class="table-search" data-target="#profs-table" placeholder="🔍 Buscar profissionais..."></div>
                <div id="profs-wrap"></div>
                <table id="profs-table"><thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Especialidade</th><th></th></tr></thead><tbody></tbody></table>
            </div>
        </div>
    </div>
    <script src="/src/js/admin_dashboard.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
