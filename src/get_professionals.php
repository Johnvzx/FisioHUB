<?php

$dbHost = getenv('MYSQL_HOST') ?: 'db';
$dbName = getenv('MYSQL_DATABASE') ?: 'mydatabase';
$dbUser = getenv('MYSQL_USER') ?: 'root';
$dbPass = getenv('MYSQL_PASSWORD') ?: 'mypassword';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    if (($_GET['format'] ?? '') === 'js') {
        header('Content-Type: application/javascript; charset=utf-8');
        echo "console.error('Erro ao conectar no banco');";
        exit;
    }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['error' => 'db', 'message' => 'Erro na conexão com o banco']);
    exit;
}

try {
    // Busca profissionais ativos com nome e especialidade
    $stmt = $pdo->query('SELECT id, name, specialty FROM professionals ORDER BY name ASC');
    $professionals = $stmt->fetchAll();
} catch (Exception $e) {
    $professionals = [];
}

// Se solicitado como JS, emite script que preenche o select diretamente (fallback sem fetch)
if (($_GET['format'] ?? '') === 'js') {
    header('Content-Type: application/javascript; charset=utf-8');
    $json = json_encode($professionals, JSON_UNESCAPED_UNICODE);
    // Gera JS seguro (sem concatenar HTML com aspas) usando createElement
    echo "(function(){var tries=10;function inject(){var s=document.getElementById('professional_id');if(!s){if(tries-->0){setTimeout(inject,300);}return;}try{var list=" . $json . ";s.innerHTML='';var first=document.createElement('option');first.value='';first.textContent=list && list.length ? 'Selecione um profissional' : 'Nenhum profissional disponível';s.appendChild(first);if(list&&list.length){for(var i=0;i<list.length;i++){var p=list[i]||{};var o=document.createElement('option');o.value=String(p.id||'');var label=String(p.name||'');if(p.specialty){label += ' - ' + p.specialty;}o.textContent=label;s.appendChild(o);} }console.log('Profissionais carregados via fallback JS');}catch(e){console.error('Erro ao preencher profissionais (fallback):',e);} }inject();})();";
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'professionals' => $professionals]);
