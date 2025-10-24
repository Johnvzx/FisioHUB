<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: login.html');
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Área de Administração</title>

<style>
    :root{--bg:#f4f6fb;
      --card:#fff;
      --primary:#2c5282;
      --muted:#6b7280;
    }
    body{
          font-family:Inter,Arial,Helvetica,sans-serif;
          background:var(--bg);
          margin:0;
          color:#0f172a;
    }
    header{
          background:linear-gradient(90deg,var(--primary),#3182ce);
          color:#fff;
          padding:20px ;
    }
    .wrap{
          max-width:1100px;
          margin:24px auto;
          padding:0 16px;
    }
    .top{
          display:flex;
          align-items:center;
          justify-content:space-between;
          gap:12px;
    }
    h1{
      margin:0;font-size:20px
    }
    .card{
        background:var(--card);
        padding:18px;
        border-radius:10px;
        box-shadow:0 6px 18px rgba(2,6,23,.06);
        margin-top:16px;
    }
    .grid{
          display:grid;
          grid-template-columns:1fr 360px;
          gap:16px;
    }
    .small{
      font-size:13px;
      color:var(--muted);
      }
    form.inline{
      display:flex;
      flex-direction:column;
      gap:8px;
    }
    input,select{
      padding:10px;
      border:1px solid #e6eef8;
      border-radius:8px;
      font-size:14px;
    }
    button{
      background:var(--primary);
      color:#fff;
      border:none;
      padding:10px 12px;
      border-radius:8px;
      cursor:pointer;
    }
    table{
      width:100%;
      border-collapse:collapse;
      margin-top:12px;
    }
    th,td{
      padding:8px 1;
      border-bottom:1px solid #eef3fb;
      text-align:left;
      font-size:14px;
    }
    th{
      background:#f8fbff;
      }

    .actions button{
      margin-right:6px;
      padding:6px 8px;
      border-radius:6px;
      background:#e2e8f0;
      color:#0f172a;
      border:none;
      cursor:pointer;
    }

    .muted{
          color:var(--muted);
    }
    @media(max-width:900px){
      .grid{grid-template-columns:1fr;
      }
    }
</style>
</head>
<body>
  <header>
    <div class="wrap top">
      <div>
        <h1>Área de Administração</h1>
        <div class="small">Olá, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong> — painel administrativo</div>
      </div>
      <div>
        <a href="/logout.php" style="color:#fff;text-decoration:none;padding:8px 12px;border-radius:8px;background:rgba(255,255,255,.12)">Sair</a>
      </div>
    </div>
  </header>

  <main class="wrap">
    <div class="grid">
      <section class="card">
        <h2 style="margin:0 0 8px 0">Gerenciar usuários</h2>

        <div style="margin-top:12px">
          <h3 style="margin:0 0 8px 0">Criar usuário</h3>
          <form id="create-user" class="inline">
            <input name="name" placeholder="Nome" required>
            <input name="email" placeholder="Email" type="email" required>
            <input name="password" placeholder="Senha" type="password" required>
            <select name="role"><option value="user">Usuário</option><option value="admin">Admin</option></select>
            <div><button type="submit">Criar usuário</button></div>
          </form>
        </div>

        <div style="margin-top:18px">
          <h3 style="margin:0 0 8px 0">Lista de Usuários</h3>
          <div id="users-wrap" class="small muted">Carregando usuários...</div>
          <table id="users-table" aria-live="polite"><thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Role</th><th>Ações</th></tr></thead><tbody></tbody></table>
        </div>
      </section>

      <aside class="card">
        <h3 style="margin-top:0">Profissionais</h3>
        <p class="small">Lista de profissionais registrados.</p>
        <div id="profs-wrap" class="small muted">Carregando profissionais...</div>
        <table id="profs-table"><thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Ações</th></tr></thead><tbody></tbody></table>
      </aside>
    </div>
  </main>

<script>
    const apiPath = '/admin_api.php';

    async function apiRequest(fd){
      try{
        const res = await fetch(apiPath, {method:'POST', body:fd});
        return await res.json();
      }catch(e){
        return {error:'network', message: e.message};
      }
    }

    async function loadLists(){
      try{
        const r = await fetch(apiPath);
        if(!r.ok) throw new Error('API indisponível');
        const data = await r.json();
        renderUsers(data.users || []);
        renderProfs(data.professionals || []);
        document.getElementById('users-wrap').textContent = '';
        document.getElementById('profs-wrap').textContent = '';
      }catch(err){
        document.getElementById('users-wrap').textContent = 'API não disponível — instale admin_api.php';
        document.getElementById('profs-wrap').textContent = 'API não disponível — instale admin_api.php';
      }
    }

    function renderUsers(users){
      const tbody = document.querySelector('#users-table tbody'); tbody.innerHTML = '';
      users.forEach(u=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${u.id}</td><td>${escapeHtml(u.name)}</td><td>${escapeHtml(u.email)}</td><td>${escapeHtml(u.role||'')}</td><td class="actions">
          <button data-id="${u.id}" data-act="promote_prof">Promover Prof</button>
          <button data-id="${u.id}" data-act="promote_admin">Promover Admin</button>
          <button data-id="${u.id}" data-act="delete_user">Remover</button>
        </td>`;
        tbody.appendChild(tr);
      });
    }

    function renderProfs(profs){
      const tbody = document.querySelector('#profs-table tbody'); tbody.innerHTML = '';
      profs.forEach(p=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${p.id}</td><td>${escapeHtml(p.name)}</td><td>${escapeHtml(p.email)}</td><td>
          <button data-id="${p.id}" data-act="move_to_user">Mover para Usuário</button>
          <button data-id="${p.id}" data-act="delete_prof">Remover</button>
        </td>`;
        tbody.appendChild(tr);
      });
    }

    function escapeHtml(s){ return (s||'').toString().replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]); }

    document.getElementById('create-user').addEventListener('submit', async function(e){
      e.preventDefault();
      const fd = new FormData(this); fd.append('action','create_user');
      const j = await apiRequest(fd);
      if(j && j.ok){ alert('Usuário criado'); this.reset(); loadLists(); }
      else alert('Erro: '+(j && (j.error||j.message) || 'unknown'));
    });

    document.addEventListener('click', async function(e){
      const btn = e.target.closest('button[data-act]'); if(!btn) return;
      const act = btn.dataset.act; const id = btn.dataset.id;
      if(!confirmAction(act)) return;
      const fd = new FormData();
      if(act === 'promote_prof'){ fd.append('action','promote_to_professional'); fd.append('id',id); }
      if(act === 'promote_admin'){ fd.append('action','promote_to_admin'); fd.append('id',id); }
      if(act === 'delete_user'){ fd.append('action','delete'); fd.append('table','users'); fd.append('id',id); }
      if(act === 'move_to_user'){ fd.append('action','move_prof_to_user'); fd.append('id',id); }
      if(act === 'delete_prof'){ fd.append('action','delete'); fd.append('table','professionals'); fd.append('id',id); }
      const j = await apiRequest(fd);
      if(j && j.ok){ loadLists(); } else alert('Erro: '+(j && (j.error||j.message) || 'unknown'));
    });

    function confirmAction(act){
      const msgs = {
        promote_prof:'Promover este usuário a profissional?',
        promote_admin:'Promover este usuário a admin?',
        delete_user:'Remover usuário?',
        move_to_user:'Mover profissional para usuário?',
        delete_prof:'Remover profissional?'
      };
      return confirm(msgs[act] || 'Confirmar ação?');
    }
    loadLists();
</script>
</body>
</html>
