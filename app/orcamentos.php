<?php
session_start();
require_once '../config/auth.php';
require_once '../config/config.php';

$nome_usuario = $_SESSION['nome'] ?? '';
$msg    = $_GET['msg']    ?? '';
$erro   = $_GET['erro']   ?? '';
$filtro = $_GET['filtro'] ?? 'todos';
$busca  = trim($_GET['busca'] ?? '');
$uid    = $_SESSION['usuario_id'];

$pdo = conectar();

// Contadores — apenas do usuário logado
$stmt  = $pdo->prepare('SELECT COUNT(*) FROM orcamentos WHERE usuario_id = ?');
$stmt->execute([$uid]);
$total = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM orcamentos WHERE usuario_id = ? AND status = ?');
$stmt->execute([$uid, 'aprovado']); $aprovados = (int) $stmt->fetchColumn();
$stmt->execute([$uid, 'pendente']); $pendentes = (int) $stmt->fetchColumn();
$stmt->execute([$uid, 'recusado']); $recusados = (int) $stmt->fetchColumn();

// Query principal — sempre filtra por usuario_id
$where  = ['usuario_id = ?'];
$params = [$uid];

if ($filtro !== 'todos') {
    $where[]  = 'status = ?';
    $params[] = $filtro;
}

if ($busca !== '') {
    $where[]  = '(cliente LIKE ? OR servico LIKE ?)';
    $params[] = '%' . $busca . '%';
    $params[] = '%' . $busca . '%';
}

$sql = 'SELECT cliente, email, servico, valor, status, hash,
               DATE_FORMAT(data_criacao, "%d/%m/%Y") AS data_criacao
        FROM orcamentos
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY data_criacao DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lista = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propix — Orçamentos</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --roxo:          #7C3AED;
      --roxo-dark:     #5B21B6;
      --roxo-light:    #EDE9FE;
      --roxo-mid:      #8B5CF6;
      --roxo-glow:     rgba(124, 58, 237, 0.14);
      --texto:         #1E1B2E;
      --sub:           #6B7280;
      --borda:         #E5E7EB;
      --fundo:         #F8F7FF;
      --branco:        #FFFFFF;
      --sidebar-w:     240px;
      --verde:         #10B981;
      --verde-bg:      #ECFDF5;
      --verde-borda:   #A7F3D0;
      --amarelo:       #F59E0B;
      --amarelo-bg:    #FFFBEB;
      --amarelo-borda: #FDE68A;
      --vermelho:      #EF4444;
      --vermelho-bg:   #FEF2F2;
      --vermelho-borda:#FECACA;
      --nav-h:         60px;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--fundo);
      color: var(--texto);
      min-height: 100vh;
    }

    /* ── SIDEBAR ── */
    .sidebar {
      position: fixed;
      top: 0; left: 0;
      width: var(--sidebar-w);
      height: 100vh;
      background: var(--branco);
      border-right: 1px solid var(--borda);
      display: flex;
      flex-direction: column;
      z-index: 50;
      transition: transform .3s cubic-bezier(.22,.68,0,1.2);
    }

    .sidebar-logo {
      display: flex; align-items: center; gap: .55rem;
      padding: 1.5rem 1.25rem 1.25rem;
      border-bottom: 1px solid var(--borda);
      text-decoration: none;
    }

    .logo-icon {
      width: 32px; height: 32px;
      background: var(--roxo); border-radius: 9px;
      display: grid; place-items: center; flex-shrink: 0;
    }

    .logo-icon svg {
      width: 15px; height: 15px; fill: none; stroke: #fff;
      stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round;
    }

    .logo-nome { font-size: 1.2rem; font-weight: 700; color: var(--texto); letter-spacing: -.02em; }
    .logo-nome span { color: var(--roxo); }

    .sidebar-nav {
      flex: 1; padding: 1rem .75rem;
      display: flex; flex-direction: column; gap: .25rem;
    }

    .nav-label {
      font-size: .65rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: .1em;
      color: var(--sub); padding: .5rem .6rem .25rem; margin-top: .5rem;
    }

    .nav-item {
      display: flex; align-items: center; gap: .65rem;
      padding: .65rem .85rem; border-radius: 10px;
      text-decoration: none; font-size: .855rem; font-weight: 500;
      color: var(--sub); transition: background .18s, color .18s;
    }

    .nav-item svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; }
    .nav-item:hover { background: var(--fundo); color: var(--texto); }
    .nav-item.ativo { background: var(--roxo-light); color: var(--roxo); font-weight: 600; }
    .nav-item.ativo svg { stroke: var(--roxo); }

    .nav-badge {
      margin-left: auto;
      background: var(--amarelo-bg); color: var(--amarelo);
      border: 1px solid var(--amarelo-borda);
      font-size: .65rem; font-weight: 700;
      padding: .1rem .45rem; border-radius: 99px;
    }

    .sidebar-footer { padding: 1rem .75rem; border-top: 1px solid var(--borda); }

    .usuario-box { display: flex; align-items: center; gap: .65rem; padding: .6rem .75rem; margin-bottom: .4rem; }

    .avatar {
      width: 32px; height: 32px; border-radius: 50%;
      background: linear-gradient(135deg, var(--roxo), var(--roxo-mid));
      display: grid; place-items: center;
      font-size: .78rem; font-weight: 700; color: #fff; flex-shrink: 0;
    }

    .usuario-nome { font-size: .8rem; font-weight: 600; color: var(--texto); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .usuario-cargo { font-size: .7rem; color: var(--sub); }

    .btn-logout {
      display: flex; align-items: center; gap: .55rem;
      width: 100%; padding: .6rem .85rem; border-radius: 10px;
      border: none; background: none; font-family: 'Poppins', sans-serif;
      font-size: .82rem; font-weight: 500; color: var(--sub);
      cursor: pointer; text-decoration: none; transition: background .18s, color .18s;
    }

    .btn-logout svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }
    .btn-logout:hover { background: var(--vermelho-bg); color: var(--vermelho); }

    .overlay-menu { display: none; position: fixed; inset: 0; background: rgba(15,10,30,.4); backdrop-filter: blur(3px); z-index: 40; opacity: 0; transition: opacity .3s; }

    .topbar {
      display: none; position: fixed; top: 0; left: 0; right: 0;
      height: var(--nav-h); background: var(--branco); border-bottom: 1px solid var(--borda);
      align-items: center; justify-content: space-between; padding: 0 1.25rem; z-index: 30;
    }

    .topbar-logo { display: flex; align-items: center; gap: .45rem; text-decoration: none; }

    .btn-hamburguer {
      background: none; border: none; cursor: pointer; color: var(--texto);
      padding: .35rem; display: grid; place-items: center;
      border-radius: 8px; transition: background .18s;
    }

    .btn-hamburguer:hover { background: var(--fundo); }
    .btn-hamburguer svg { width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; }

    /* ── MAIN ── */
    .main { margin-left: var(--sidebar-w); min-height: 100vh; padding: 2rem 2rem 3rem; }

    /* ── PAGE HEADER ── */
    .page-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 1.75rem; animation: fadeDown .4s ease both;
      flex-wrap: wrap; gap: 1rem;
    }

    .page-title h1 { font-size: 1.4rem; font-weight: 700; color: var(--texto); letter-spacing: -.02em; }
    .page-title p  { font-size: .82rem; color: var(--sub); margin-top: .15rem; }

    .btn-criar {
      display: inline-flex; align-items: center; gap: .5rem;
      padding: .7rem 1.25rem; background: var(--roxo); color: #fff;
      border: none; border-radius: 10px; font-family: 'Poppins', sans-serif;
      font-size: .87rem; font-weight: 600; cursor: pointer; text-decoration: none;
      transition: background .2s, box-shadow .2s, transform .15s; white-space: nowrap;
    }

    .btn-criar svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2.5; stroke-linecap: round; }
    .btn-criar:hover  { background: var(--roxo-dark); box-shadow: 0 4px 18px var(--roxo-glow); }
    .btn-criar:active { transform: scale(.97); }

    /* ── ALERTAS ── */
    .alerta {
      display: flex; align-items: center; gap: .5rem;
      padding: .75rem 1rem; border-radius: 12px;
      font-size: .83rem; font-weight: 500;
      margin-bottom: 1.5rem; animation: fadeUp .3s ease both;
    }

    .alerta.erro    { background: var(--vermelho-bg); color: var(--vermelho); border: 1px solid var(--vermelho-borda); }
    .alerta.sucesso { background: var(--verde-bg);    color: var(--verde);    border: 1px solid var(--verde-borda);   }
    .alerta svg { flex-shrink: 0; width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

    /* ── MINI STATS ── */
    .mini-stats {
      display: flex; gap: .75rem; margin-bottom: 1.5rem;
      flex-wrap: wrap; animation: fadeUp .4s ease both; animation-delay: .05s;
    }

    .mini-stat {
      display: flex; align-items: center; gap: .6rem;
      background: var(--branco); border: 1px solid var(--borda);
      border-radius: 12px; padding: .75rem 1.1rem;
      flex: 1; min-width: 120px;
      transition: transform .2s, box-shadow .2s;
    }

    .mini-stat:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,0,0,.06); }

    .mini-stat-dot {
      width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
    }

    .mini-stat-dot.todos    { background: var(--roxo); }
    .mini-stat-dot.aprovado { background: var(--verde); }
    .mini-stat-dot.pendente { background: var(--amarelo); animation: pulse 1.8s ease infinite; }
    .mini-stat-dot.recusado { background: var(--vermelho); }

    @keyframes pulse {
      0%,100% { opacity: 1; transform: scale(1); }
      50%      { opacity: .5; transform: scale(.7); }
    }

    .mini-stat-info { display: flex; flex-direction: column; }
    .mini-stat-num  { font-size: 1.15rem; font-weight: 700; color: var(--texto); line-height: 1; }
    .mini-stat-label{ font-size: .7rem; color: var(--sub); font-weight: 500; margin-top: .1rem; }

    /* ── TOOLBAR ── */
    .toolbar {
      display: flex; align-items: center; justify-content: space-between;
      gap: 1rem; margin-bottom: 1rem;
      flex-wrap: wrap; animation: fadeUp .45s ease both; animation-delay: .1s;
    }

    /* Filtros */
    .filtros { display: flex; gap: .4rem; flex-wrap: wrap; }

    .filtro-btn {
      display: inline-flex; align-items: center; gap: .35rem;
      padding: .45rem .85rem; border-radius: 8px;
      border: 1.5px solid var(--borda);
      background: var(--branco); color: var(--sub);
      font-family: 'Poppins', sans-serif; font-size: .78rem; font-weight: 600;
      cursor: pointer; text-decoration: none;
      transition: background .18s, color .18s, border-color .18s;
      white-space: nowrap;
    }

    .filtro-btn:hover { background: var(--fundo); color: var(--texto); }

    .filtro-btn.ativo         { background: var(--roxo-light);  color: var(--roxo);    border-color: #C4B5FD; }
    .filtro-btn.ativo.aprovado{ background: var(--verde-bg);    color: var(--verde);   border-color: var(--verde-borda); }
    .filtro-btn.ativo.pendente{ background: var(--amarelo-bg);  color: var(--amarelo); border-color: var(--amarelo-borda); }
    .filtro-btn.ativo.recusado{ background: var(--vermelho-bg); color: var(--vermelho);border-color: var(--vermelho-borda); }

    .filtro-count {
      background: currentColor; color: #fff; border-radius: 99px;
      font-size: .62rem; font-weight: 700; padding: .05rem .4rem;
      opacity: .85;
    }

    /* Busca */
    .busca-wrap { position: relative; }

    .busca-wrap svg {
      position: absolute; left: .85rem; top: 50%;
      transform: translateY(-50%);
      width: 15px; height: 15px; fill: none; stroke: var(--sub);
      stroke-width: 2; stroke-linecap: round; pointer-events: none;
    }

    .busca-input {
      padding: .5rem .9rem .5rem 2.4rem;
      border: 1.5px solid var(--borda); border-radius: 9px;
      font-family: 'Poppins', sans-serif; font-size: .84rem;
      color: var(--texto); background: var(--branco); outline: none;
      width: 220px; transition: border-color .2s, box-shadow .2s;
    }

    .busca-input:focus { border-color: var(--roxo); box-shadow: 0 0 0 3px var(--roxo-glow); }
    .busca-input::placeholder { color: #C4B5FD; }

    /* ── TABELA ── */
    .painel {
      background: var(--branco); border: 1px solid var(--borda);
      border-radius: 18px; overflow: hidden;
      animation: fadeUp .5s ease both; animation-delay: .15s;
    }

    .tabela-wrap { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; }

    thead th {
      font-size: .69rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: .07em;
      color: var(--sub); padding: .85rem 1.25rem;
      text-align: left; background: var(--fundo);
      border-bottom: 1px solid var(--borda);
      white-space: nowrap;
    }

    thead th:last-child { text-align: right; }

    tbody td {
      padding: 1rem 1.25rem; font-size: .85rem; color: var(--texto);
      border-bottom: 1px solid var(--borda); vertical-align: middle;
    }

    tbody tr:last-child td { border-bottom: none; }

    tbody tr {
      transition: background .15s;
      animation: fadeUp .35s ease both;
    }

    tbody tr:hover { background: var(--fundo); }

    /* Animação escalonada nas linhas */
    tbody tr:nth-child(1)  { animation-delay: .18s; }
    tbody tr:nth-child(2)  { animation-delay: .21s; }
    tbody tr:nth-child(3)  { animation-delay: .24s; }
    tbody tr:nth-child(4)  { animation-delay: .27s; }
    tbody tr:nth-child(5)  { animation-delay: .30s; }
    tbody tr:nth-child(n+6){ animation-delay: .33s; }

    /* Células */
    .cel-cliente { display: flex; flex-direction: column; }
    .cel-cliente-nome  { font-weight: 600; }
    .cel-cliente-email { font-size: .74rem; color: var(--sub); margin-top: .1rem; }

    .cel-valor { font-weight: 700; white-space: nowrap; }

    .cel-data { font-size: .78rem; color: var(--sub); white-space: nowrap; }

    /* Badge status */
    .status-badge {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .28rem .7rem; border-radius: 99px;
      font-size: .72rem; font-weight: 600; white-space: nowrap;
    }

    .badge-dot { width: 5px; height: 5px; border-radius: 50%; }

    .status-badge.pendente  { background: var(--amarelo-bg);  color: var(--amarelo);  border: 1px solid var(--amarelo-borda); }
    .status-badge.aprovado  { background: var(--verde-bg);    color: var(--verde);    border: 1px solid var(--verde-borda);   }
    .status-badge.recusado  { background: var(--vermelho-bg); color: var(--vermelho); border: 1px solid var(--vermelho-borda); }
    .status-badge.pendente .badge-dot { background: var(--amarelo);  animation: pulse 1.8s ease infinite; }
    .status-badge.aprovado .badge-dot { background: var(--verde);   }
    .status-badge.recusado .badge-dot { background: var(--vermelho); }

    /* Ações da linha */
    .cel-acoes {
      display: flex; align-items: center; justify-content: flex-end; gap: .4rem;
    }

    .btn-acao-linha {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .32rem .7rem; border-radius: 7px;
      border: 1px solid var(--borda); background: var(--branco);
      font-family: 'Poppins', sans-serif; font-size: .74rem; font-weight: 600;
      color: var(--sub); text-decoration: none; cursor: pointer;
      transition: background .18s, color .18s, border-color .18s;
      white-space: nowrap;
    }

    .btn-acao-linha svg { width: 12px; height: 12px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }
    .btn-acao-linha:hover { background: var(--roxo-light); color: var(--roxo); border-color: #C4B5FD; }

    .btn-acao-linha.copiar:hover { background: var(--verde-bg); color: var(--verde); border-color: var(--verde-borda); }
    .btn-acao-linha.excluir:hover { background: var(--vermelho-bg); color: var(--vermelho); border-color: var(--vermelho-borda); }

    /* ── ESTADO VAZIO ── */
    .vazio {
      text-align: center;
      padding: 4rem 2rem;
      animation: fadeUp .5s ease both;
    }

    .vazio-icon {
      width: 60px; height: 60px; background: var(--roxo-light); border-radius: 16px;
      display: grid; place-items: center; margin: 0 auto 1.25rem;
    }

    .vazio-icon svg { width: 28px; height: 28px; fill: none; stroke: var(--roxo); stroke-width: 1.8; stroke-linecap: round; }

    .vazio h3 { font-size: 1rem; font-weight: 700; color: var(--texto); margin-bottom: .4rem; }
    .vazio p  { font-size: .83rem; color: var(--sub); margin-bottom: 1.5rem; }

    /* ── PAGINAÇÃO (estrutura, sem lógica ainda) ── */
    .paginacao {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1rem 1.5rem; border-top: 1px solid var(--borda);
      font-size: .8rem; color: var(--sub); flex-wrap: wrap; gap: .75rem;
    }

    .pag-btns { display: flex; gap: .35rem; }

    .pag-btn {
      width: 32px; height: 32px; border-radius: 8px; display: grid; place-items: center;
      border: 1.5px solid var(--borda); background: var(--branco);
      font-family: 'Poppins', sans-serif; font-size: .8rem; font-weight: 600;
      color: var(--sub); cursor: pointer; text-decoration: none;
      transition: background .18s, color .18s, border-color .18s;
    }

    .pag-btn:hover  { background: var(--fundo); color: var(--texto); }
    .pag-btn.ativo  { background: var(--roxo); color: #fff; border-color: var(--roxo); }
    .pag-btn svg    { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

    /* ── MODAL DE CONFIRMAÇÃO EXCLUSÃO ── */
    .overlay {
      position: fixed; inset: 0; background: rgba(15,10,30,.45);
      backdrop-filter: blur(4px); z-index: 100;
      display: none; align-items: center; justify-content: center; padding: 1.5rem;
    }

    .overlay.ativo { display: flex; animation: fadeOverlay .2s ease; }

    @keyframes fadeOverlay { from { opacity: 0; } to { opacity: 1; } }

    .modal {
      background: var(--branco); border-radius: 18px; padding: 2rem 1.75rem;
      width: 100%; max-width: 360px; text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,.15);
      animation: scaleIn .25s cubic-bezier(.22,.68,0,1.2) both;
    }

    @keyframes scaleIn { from { opacity: 0; transform: scale(.93); } to { opacity: 1; transform: scale(1); } }

    .modal-icon {
      width: 50px; height: 50px; border-radius: 50%;
      background: var(--vermelho-bg); border: 1px solid var(--vermelho-borda);
      display: grid; place-items: center; margin: 0 auto 1rem;
    }

    .modal-icon svg { width: 22px; height: 22px; fill: none; stroke: var(--vermelho); stroke-width: 2.2; stroke-linecap: round; }

    .modal h3 { font-size: 1rem; font-weight: 700; color: var(--texto); margin-bottom: .4rem; }
    .modal p  { font-size: .82rem; color: var(--sub); line-height: 1.55; margin-bottom: 1.5rem; }

    .modal-btns { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }

    .btn-cancelar {
      padding: .7rem; background: var(--fundo); border: 1.5px solid var(--borda);
      border-radius: 10px; font-family: 'Poppins', sans-serif;
      font-size: .85rem; font-weight: 600; color: var(--sub);
      cursor: pointer; transition: background .18s;
    }

    .btn-cancelar:hover { background: var(--borda); }

    .btn-excluir-conf {
      padding: .7rem; background: var(--vermelho); border: none; border-radius: 10px;
      font-family: 'Poppins', sans-serif; font-size: .85rem; font-weight: 600; color: #fff;
      cursor: pointer; transition: filter .2s;
    }

    .btn-excluir-conf:hover { filter: brightness(1.08); }

    /* Toast de cópia */
    .toast {
      position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%) translateY(10px);
      background: var(--texto); color: #fff; padding: .65rem 1.25rem;
      border-radius: 10px; font-size: .82rem; font-weight: 500;
      opacity: 0; transition: opacity .25s, transform .25s; pointer-events: none;
      white-space: nowrap; z-index: 200;
      display: flex; align-items: center; gap: .5rem;
    }

    .toast.visivel { opacity: 1; transform: translateX(-50%) translateY(0); }
    .toast svg { width: 14px; height: 14px; fill: none; stroke: var(--verde); stroke-width: 2.5; stroke-linecap: round; }

    /* ── ANIMAÇÕES ── */
    @keyframes fadeUp   { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* ── RESPONSIVO ── */
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.aberta { transform: translateX(0); box-shadow: 4px 0 30px rgba(0,0,0,.12); }
      .topbar { display: flex; }
      .main { margin-left: 0; padding: calc(var(--nav-h) + 1.5rem) 1.25rem 3rem; }
      .busca-input { width: 100%; }
      .toolbar { flex-direction: column; align-items: flex-start; }
      .busca-wrap { width: 100%; }
    }

    @media (max-width: 600px) {
      .mini-stats { gap: .5rem; }
      .mini-stat  { min-width: calc(50% - .25rem); }
      thead th:nth-child(3),
      tbody td:nth-child(3),
      thead th:nth-child(4),
      tbody td:nth-child(4) { display: none; }
    }

    @media (max-width: 400px) {
      .filtros { gap: .3rem; }
      .filtro-btn { font-size: .72rem; padding: .38rem .65rem; }
    }
  </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
  <a href="dashboard.php" class="sidebar-logo">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="9" y1="13" x2="15" y2="13"/>
        <line x1="9" y1="17" x2="13" y2="17"/>
      </svg>
    </div>
    <span class="logo-nome">Pro<span>pix</span></span>
  </a>

  <nav class="sidebar-nav">
    <span class="nav-label">Menu</span>
    <a href="dashboard.php" class="nav-item">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a href="criar.php" class="nav-item">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Novo orçamento
    </a>
    <a href="orcamentos.php" class="nav-item ativo">
      <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
      Orçamentos
      <?php if ($pendentes > 0): ?>
        <span class="nav-badge"><?= $pendentes ?></span>
      <?php endif; ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="usuario-box">
      <div class="avatar"><?= mb_strtoupper(mb_substr($nome_usuario, 0, 1)) ?></div>
      <div class="usuario-info">
        <p class="usuario-nome"><?= htmlspecialchars($nome_usuario) ?></p>
        <p class="usuario-cargo">Administrador</p>
      </div>
    </div>
    <a href="../actions/acao.php?acao=logout" class="btn-logout">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sair da conta
    </a>
  </div>
</aside>

<div class="overlay-menu" id="overlayMenu"></div>

<!-- ── TOPBAR MOBILE ── -->
<header class="topbar">
  <button class="btn-hamburguer" id="btnMenu" aria-label="Abrir menu">
    <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
  <a href="dashboard.php" class="topbar-logo">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24" style="width:13px;height:13px;fill:none;stroke:#fff;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
      </svg>
    </div>
    <span class="logo-nome" style="font-size:1rem">Pro<span>pix</span></span>
  </a>
  <a href="criar.php" class="btn-criar" style="padding:.5rem .9rem;font-size:.8rem">
    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Novo
  </a>
</header>

<!-- ── MAIN ── -->
<main class="main">

  <div class="page-header">
    <div class="page-title">
      <h1>Orçamentos</h1>
      <p>Gerencie e acompanhe todas as propostas</p>
    </div>
    <a href="criar.php" class="btn-criar">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Novo orçamento
    </a>
  </div>

  <?php if ($erro): ?>
    <div class="alerta erro">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($erro) ?>
    </div>
  <?php endif; ?>

  <?php if ($msg): ?>
    <div class="alerta sucesso">
      <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <!-- Mini stats -->
  <div class="mini-stats">
    <div class="mini-stat">
      <span class="mini-stat-dot todos"></span>
      <div class="mini-stat-info">
        <span class="mini-stat-num"><?= $total ?></span>
        <span class="mini-stat-label">Total</span>
      </div>
    </div>
    <div class="mini-stat">
      <span class="mini-stat-dot aprovado"></span>
      <div class="mini-stat-info">
        <span class="mini-stat-num"><?= $aprovados ?></span>
        <span class="mini-stat-label">Aprovados</span>
      </div>
    </div>
    <div class="mini-stat">
      <span class="mini-stat-dot pendente"></span>
      <div class="mini-stat-info">
        <span class="mini-stat-num"><?= $pendentes ?></span>
        <span class="mini-stat-label">Pendentes</span>
      </div>
    </div>
    <div class="mini-stat">
      <span class="mini-stat-dot recusado"></span>
      <div class="mini-stat-info">
        <span class="mini-stat-num"><?= $recusados ?></span>
        <span class="mini-stat-label">Recusados</span>
      </div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="filtros">
      <?php
        $filtros_def = [
          'todos'    => ['label' => 'Todos',    'count' => $total,     'classe' => ''],
          'aprovado' => ['label' => 'Aprovados','count' => $aprovados, 'classe' => 'aprovado'],
          'pendente' => ['label' => 'Pendentes','count' => $pendentes, 'classe' => 'pendente'],
          'recusado' => ['label' => 'Recusados','count' => $recusados, 'classe' => 'recusado'],
        ];

        foreach ($filtros_def as $key => $f):
          $ativo = $filtro === $key ? 'ativo ' . $f['classe'] : '';
      ?>
        <a href="?filtro=<?= $key ?><?= $busca ? '&busca='.urlencode($busca) : '' ?>"
           class="filtro-btn <?= $ativo ?>">
          <?= $f['label'] ?>
          <span class="filtro-count" style="background:<?= $ativo ? 'currentColor' : 'var(--borda)' ?>;color:<?= $ativo ? '#fff' : 'var(--sub)' ?>">
            <?= $f['count'] ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>

    <form method="GET" action="" class="busca-wrap">
      <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtro) ?>">
      <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input
        type="text"
        name="busca"
        class="busca-input"
        placeholder="Buscar cliente ou serviço..."
        value="<?= htmlspecialchars($busca) ?>"
        autocomplete="off"
      >
    </form>
  </div>

  <!-- Tabela -->
  <div class="painel">
    <div class="tabela-wrap">
      <?php if (empty($lista)): ?>
        <div class="vazio">
          <div class="vazio-icon">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
          </div>
          <?php if ($busca || $filtro !== 'todos'): ?>
            <h3>Nenhum resultado encontrado</h3>
            <p>Tente ajustar os filtros ou o termo de busca.</p>
            <a href="orcamentos.php" class="btn-criar" style="display:inline-flex">Limpar filtros</a>
          <?php else: ?>
            <h3>Nenhum orçamento ainda</h3>
            <p>Crie seu primeiro orçamento e comece a fechar negócios.</p>
            <a href="criar.php" class="btn-criar" style="display:inline-flex">
              <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Criar orçamento
            </a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Serviço</th>
              <th>Valor</th>
              <th>Data</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lista as $o): ?>
              <tr>
                <td>
                  <div class="cel-cliente">
                    <span class="cel-cliente-nome"><?= htmlspecialchars($o['cliente']) ?></span>
                    <span class="cel-cliente-email"><?= htmlspecialchars($o['email']) ?></span>
                  </div>
                </td>
                <td><?= htmlspecialchars($o['servico']) ?></td>
                <td class="cel-valor">R$ <?= number_format((float)$o['valor'], 2, ',', '.') ?></td>
                <td class="cel-data"><?= htmlspecialchars($o['data_criacao']) ?></td>
                <td>
                  <span class="status-badge <?= htmlspecialchars($o['status']) ?>">
                    <span class="badge-dot"></span>
                    <?= match($o['status']) { 'aprovado' => 'Aprovado', 'recusado' => 'Recusado', default => 'Pendente' } ?>
                  </span>
                </td>
                <td>
                  <div class="cel-acoes">
                    <a href="../public/visualizar.php?token=<?= htmlspecialchars($o['hash']) ?>"
                       class="btn-acao-linha" target="_blank" title="Ver proposta">
                      <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      Ver
                    </a>
                    <button
                      class="btn-acao-linha copiar"
                      title="Copiar link"
                      onclick="copiarLink('<?= htmlspecialchars($o['hash']) ?>')">
                      <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                      Link
                    </button>
                    <button
                      class="btn-acao-linha excluir"
                      title="Excluir"
                      onclick="confirmarExcluir('<?= htmlspecialchars($o['hash']) ?>', '<?= htmlspecialchars(addslashes($o['cliente'])) ?>')">
                      <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                      Excluir
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Paginação (estrutura pronta) -->
        <div class="paginacao">
          <span>Mostrando <?= count($lista) ?> de <?= $total ?> orçamentos</span>
          <div class="pag-btns">
            <button class="pag-btn" disabled>
              <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <a href="#" class="pag-btn ativo">1</a>
            <button class="pag-btn" disabled>
              <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

</main>

<!-- Modal de exclusão -->
<div class="overlay" id="modalExcluir">
  <div class="modal">
    <div class="modal-icon">
      <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
    </div>
    <h3>Excluir orçamento?</h3>
    <p id="modalExcluirTxt">Essa ação não pode ser desfeita.</p>
    <div class="modal-btns">
      <button class="btn-cancelar" id="cancelarExcluir">Cancelar</button>
      <form id="formExcluir" action="/actions/acao.php" method="POST" style="display:contents">
        <input type="hidden" name="acao" value="excluir_orcamento">
        <input type="hidden" name="hash" id="hashExcluir" value="">
        <button type="submit" class="btn-excluir-conf">Excluir</button>
      </form>
    </div>
  </div>
</div>

<!-- Toast de cópia -->
<div class="toast" id="toast">
  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
  Link copiado!
</div>

<script>
  // ── Menu hambúrguer ──
  const sidebar     = document.getElementById('sidebar');
  const btnMenu     = document.getElementById('btnMenu');
  const overlayMenu = document.getElementById('overlayMenu');

  function abrirMenu() {
    sidebar.classList.add('aberta');
    overlayMenu.style.display = 'block';
    setTimeout(() => overlayMenu.style.opacity = '1', 10);
  }

  function fecharMenu() {
    sidebar.classList.remove('aberta');
    overlayMenu.style.opacity = '0';
    setTimeout(() => overlayMenu.style.display = 'none', 300);
  }

  btnMenu?.addEventListener('click', abrirMenu);
  overlayMenu.addEventListener('click', fecharMenu);

  // ── Busca com debounce ──
  const buscaInput = document.querySelector('.busca-input');
  let debounceTimer;

  buscaInput?.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      buscaInput.closest('form').submit();
    }, 450);
  });

  // ── Copiar link ──
  function copiarLink(hash) {
    const url = `${location.origin}/propix/public/visualizar.php?token=${hash}`;
    navigator.clipboard.writeText(url).then(() => {
      const toast = document.getElementById('toast');
      toast.classList.add('visivel');
      setTimeout(() => toast.classList.remove('visivel'), 2500);
    });
  }

  // ── Modal de exclusão ──
  const modalExcluir = document.getElementById('modalExcluir');

  function confirmarExcluir(hash, cliente) {
    document.getElementById('hashExcluir').value = hash;
    document.getElementById('modalExcluirTxt').textContent =
      `Tem certeza que deseja excluir o orçamento de "${cliente}"? Essa ação não pode ser desfeita.`;
    modalExcluir.classList.add('ativo');
  }

  document.getElementById('cancelarExcluir').addEventListener('click', () => {
    modalExcluir.classList.remove('ativo');
  });

  modalExcluir.addEventListener('click', (e) => {
    if (e.target === modalExcluir) modalExcluir.classList.remove('ativo');
  });
</script>

</body>
</html>