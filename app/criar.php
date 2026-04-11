<?php
session_start();
require_once '../config/auth.php';
require_once '../config/config.php';

$nome_usuario = $_SESSION['nome'] ?? '';

$erro = $_GET['erro'] ?? '';
$msg  = $_GET['msg']  ?? '';

// Repopula campos em caso de erro
$v = [
    'cliente'   => htmlspecialchars($_GET['cliente']   ?? ''),
    'email'     => htmlspecialchars($_GET['email']     ?? ''),
    'telefone'  => htmlspecialchars($_GET['telefone']  ?? ''),
    'servico'   => htmlspecialchars($_GET['servico']   ?? ''),
    'valor'     => htmlspecialchars($_GET['valor']     ?? ''),
    'descricao' => htmlspecialchars($_GET['descricao'] ?? ''),
    'prazo'     => htmlspecialchars($_GET['prazo']     ?? ''),
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propix — Novo orçamento</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --roxo:         #7C3AED;
      --roxo-dark:    #5B21B6;
      --roxo-light:   #EDE9FE;
      --roxo-mid:     #8B5CF6;
      --roxo-glow:    rgba(124, 58, 237, 0.14);
      --texto:        #1E1B2E;
      --sub:          #6B7280;
      --borda:        #E5E7EB;
      --fundo:        #F8F7FF;
      --branco:       #FFFFFF;
      --sidebar-w:    240px;
      --verde:        #10B981;
      --verde-bg:     #ECFDF5;
      --verde-borda:  #A7F3D0;
      --amarelo:      #F59E0B;
      --amarelo-bg:   #FFFBEB;
      --amarelo-borda:#FDE68A;
      --vermelho:     #EF4444;
      --vermelho-bg:  #FEF2F2;
      --vermelho-borda:#FECACA;
      --nav-h:        60px;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--fundo);
      color: var(--texto);
      min-height: 100vh;
    }

    /* ── SIDEBAR (igual ao dashboard) ── */
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
      display: flex;
      align-items: center;
      gap: .55rem;
      padding: 1.5rem 1.25rem 1.25rem;
      border-bottom: 1px solid var(--borda);
      text-decoration: none;
    }

    .logo-icon {
      width: 32px; height: 32px;
      background: var(--roxo);
      border-radius: 9px;
      display: grid;
      place-items: center;
      flex-shrink: 0;
    }

    .logo-icon svg {
      width: 15px; height: 15px;
      fill: none; stroke: #fff;
      stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round;
    }

    .logo-nome { font-size: 1.2rem; font-weight: 700; color: var(--texto); letter-spacing: -.02em; }
    .logo-nome span { color: var(--roxo); }

    .sidebar-nav {
      flex: 1;
      padding: 1rem .75rem;
      display: flex;
      flex-direction: column;
      gap: .25rem;
    }

    .nav-label {
      font-size: .65rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: var(--sub);
      padding: .5rem .6rem .25rem;
      margin-top: .5rem;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: .65rem;
      padding: .65rem .85rem;
      border-radius: 10px;
      text-decoration: none;
      font-size: .855rem;
      font-weight: 500;
      color: var(--sub);
      transition: background .18s, color .18s;
    }

    .nav-item svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; }
    .nav-item:hover { background: var(--fundo); color: var(--texto); }
    .nav-item.ativo { background: var(--roxo-light); color: var(--roxo); font-weight: 600; }
    .nav-item.ativo svg { stroke: var(--roxo); }

    .nav-badge {
      margin-left: auto;
      background: var(--amarelo-bg);
      color: var(--amarelo);
      border: 1px solid var(--amarelo-borda);
      font-size: .65rem; font-weight: 700;
      padding: .1rem .45rem;
      border-radius: 99px;
    }

    .sidebar-footer { padding: 1rem .75rem; border-top: 1px solid var(--borda); }

    .usuario-box { display: flex; align-items: center; gap: .65rem; padding: .6rem .75rem; border-radius: 10px; margin-bottom: .4rem; }

    .avatar {
      width: 32px; height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--roxo), var(--roxo-mid));
      display: grid; place-items: center;
      font-size: .78rem; font-weight: 700; color: #fff;
      flex-shrink: 0;
    }

    .usuario-info { overflow: hidden; }
    .usuario-nome { font-size: .8rem; font-weight: 600; color: var(--texto); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .usuario-cargo { font-size: .7rem; color: var(--sub); }

    .btn-logout {
      display: flex; align-items: center; gap: .55rem;
      width: 100%; padding: .6rem .85rem;
      border-radius: 10px; border: none; background: none;
      font-family: 'Poppins', sans-serif;
      font-size: .82rem; font-weight: 500; color: var(--sub);
      cursor: pointer; text-decoration: none;
      transition: background .18s, color .18s;
    }

    .btn-logout svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }
    .btn-logout:hover { background: var(--vermelho-bg); color: var(--vermelho); }

    .overlay-menu { display: none; position: fixed; inset: 0; background: rgba(15,10,30,.4); backdrop-filter: blur(3px); z-index: 40; }

    .topbar {
      display: none;
      position: fixed; top: 0; left: 0; right: 0;
      height: var(--nav-h);
      background: var(--branco); border-bottom: 1px solid var(--borda);
      align-items: center; justify-content: space-between;
      padding: 0 1.25rem; z-index: 30;
    }

    .topbar-logo { display: flex; align-items: center; gap: .45rem; text-decoration: none; }

    .btn-hamburguer {
      background: none; border: none; cursor: pointer;
      color: var(--texto); padding: .35rem;
      display: grid; place-items: center;
      border-radius: 8px; transition: background .18s;
    }

    .btn-hamburguer:hover { background: var(--fundo); }
    .btn-hamburguer svg { width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; }

    /* ── MAIN ── */
    .main {
      margin-left: var(--sidebar-w);
      min-height: 100vh;
      padding: 2rem 2rem 3rem;
    }

    /* ── PAGE HEADER ── */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 2rem;
      animation: fadeDown .4s ease both;
    }

    .page-header-left { display: flex; align-items: center; gap: .85rem; }

    .btn-voltar {
      display: grid; place-items: center;
      width: 36px; height: 36px;
      border-radius: 9px;
      border: 1.5px solid var(--borda);
      background: var(--branco);
      color: var(--sub);
      text-decoration: none;
      transition: background .18s, color .18s, border-color .18s;
    }

    .btn-voltar svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }
    .btn-voltar:hover { background: var(--fundo); color: var(--texto); border-color: #C4B5FD; }

    .page-title h1 { font-size: 1.4rem; font-weight: 700; color: var(--texto); letter-spacing: -.02em; }
    .page-title p  { font-size: .82rem; color: var(--sub); margin-top: .15rem; }

    /* ── ALERTAS ── */
    .alerta {
      display: flex; align-items: center; gap: .5rem;
      padding: .75rem 1rem;
      border-radius: 12px;
      font-size: .83rem; font-weight: 500;
      margin-bottom: 1.5rem;
      animation: fadeUp .3s ease both;
    }

    .alerta.erro    { background: var(--vermelho-bg); color: var(--vermelho); border: 1px solid var(--vermelho-borda); }
    .alerta.sucesso { background: var(--verde-bg);    color: var(--verde);    border: 1px solid var(--verde-borda);   }
    .alerta svg { flex-shrink: 0; width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

    /* ── LAYOUT DO FORM ── */
    .form-layout {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 1.25rem;
      align-items: start;
    }

    /* ── CARD ── */
    .card {
      background: var(--branco);
      border: 1px solid var(--borda);
      border-radius: 18px;
      overflow: hidden;
      animation: fadeUp .45s cubic-bezier(.22,.68,0,1.2) both;
    }

    .card:nth-child(2) { animation-delay: .07s; }

    .card-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--borda);
      display: flex;
      align-items: center;
      gap: .75rem;
    }

    .card-header-icon {
      width: 34px; height: 34px;
      border-radius: 9px;
      background: var(--roxo-light);
      display: grid; place-items: center;
      flex-shrink: 0;
    }

    .card-header-icon svg { width: 16px; height: 16px; fill: none; stroke: var(--roxo); stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .card-header-text h2  { font-size: .95rem; font-weight: 700; color: var(--texto); }
    .card-header-text p   { font-size: .76rem; color: var(--sub); margin-top: .05rem; }

    .card-body { padding: 1.5rem; }

    /* ── FORM ── */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .campo   { display: flex; flex-direction: column; gap: .38rem; }
    .campo.full { grid-column: 1 / -1; }

    label {
      font-size: .76rem;
      font-weight: 600;
      color: var(--texto);
      letter-spacing: .01em;
    }

    .campo-obrigatorio::after { content: ' *'; color: var(--roxo); }

    input, textarea, select {
      width: 100%;
      padding: .72rem 1rem;
      border: 1.5px solid var(--borda);
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: .87rem;
      color: var(--texto);
      background: var(--branco);
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      resize: none;
    }

    input:focus, textarea:focus, select:focus {
      border-color: var(--roxo);
      box-shadow: 0 0 0 3px var(--roxo-glow);
    }

    input::placeholder, textarea::placeholder { color: #C4B5FD; }

    /* Valor com prefixo */
    .input-prefix-wrap { position: relative; }

    .input-prefix {
      position: absolute;
      left: 1rem; top: 50%;
      transform: translateY(-50%);
      font-size: .87rem;
      font-weight: 600;
      color: var(--sub);
      pointer-events: none;
    }

    .input-prefix-wrap input { padding-left: 2.4rem; }

    /* Contador de chars */
    .campo-footer {
      display: flex;
      justify-content: flex-end;
      margin-top: .25rem;
    }

    .char-count { font-size: .7rem; color: var(--sub); }
    .char-count.alerta-chars { color: var(--amarelo); }
    .char-count.limite { color: var(--vermelho); }

    /* ── CARD LATERAL (PREVIEW + SUBMIT) ── */
    .lateral { display: flex; flex-direction: column; gap: 1.25rem; }

    /* Preview */
    .preview-card {
      background: linear-gradient(135deg, var(--roxo) 0%, var(--roxo-dark) 100%);
      border-radius: 18px;
      padding: 1.5rem;
      color: #fff;
      position: relative;
      overflow: hidden;
      animation: fadeUp .5s cubic-bezier(.22,.68,0,1.2) both;
      animation-delay: .1s;
    }

    .preview-card::before {
      content: '';
      position: absolute;
      top: -25px; right: -25px;
      width: 100px; height: 100px;
      border-radius: 50%;
      background: rgba(255,255,255,.07);
    }

    .preview-titulo {
      font-size: .68rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .1em;
      opacity: .7;
      margin-bottom: 1rem;
    }

    .preview-servico {
      font-size: 1.05rem;
      font-weight: 700;
      margin-bottom: .3rem;
      min-height: 1.4em;
      opacity: .9;
    }

    .preview-cliente {
      font-size: .8rem;
      opacity: .65;
      margin-bottom: 1rem;
    }

    .preview-divider { height: 1px; background: rgba(255,255,255,.15); margin-bottom: 1rem; }

    .preview-valor {
      font-size: 1.9rem;
      font-weight: 700;
      letter-spacing: -.03em;
      line-height: 1;
    }

    .preview-valor sup { font-size: .9rem; vertical-align: super; margin-right: .1rem; font-weight: 600; }
    .preview-valor-vazio { font-size: 1rem; font-weight: 500; opacity: .45; }

    .preview-prazo {
      font-size: .75rem;
      opacity: .65;
      margin-top: .5rem;
      display: flex; align-items: center; gap: .35rem;
    }

    .preview-prazo svg { width: 12px; height: 12px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

    /* Card de ação */
    .acao-card {
      background: var(--branco);
      border: 1px solid var(--borda);
      border-radius: 18px;
      overflow: hidden;
      animation: fadeUp .55s cubic-bezier(.22,.68,0,1.2) both;
      animation-delay: .15s;
    }

    .acao-card .card-body { display: flex; flex-direction: column; gap: .85rem; }

    .btn-submit {
      width: 100%;
      padding: .9rem;
      background: var(--roxo);
      color: #fff;
      border: none;
      border-radius: 11px;
      font-family: 'Poppins', sans-serif;
      font-size: .92rem;
      font-weight: 600;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: background .2s, box-shadow .2s, transform .15s;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
    }

    .btn-submit svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 2.2; stroke-linecap: round; }
    .btn-submit:hover  { background: var(--roxo-dark); box-shadow: 0 4px 20px var(--roxo-glow); }
    .btn-submit:active { transform: scale(.97); }

    .btn-rascunho {
      width: 100%;
      padding: .75rem;
      background: var(--fundo);
      color: var(--sub);
      border: 1.5px solid var(--borda);
      border-radius: 11px;
      font-family: 'Poppins', sans-serif;
      font-size: .87rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .18s, color .18s;
    }

    .btn-rascunho:hover { background: var(--borda); color: var(--texto); }

    .info-item {
      display: flex; align-items: flex-start; gap: .5rem;
      font-size: .76rem; color: var(--sub);
    }

    .info-item svg { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; flex-shrink: 0; margin-top: .1rem; }

    .divisor-or {
      display: flex; align-items: center; gap: .5rem;
      font-size: .75rem; color: var(--sub);
    }

    .divisor-or::before, .divisor-or::after {
      content: '';
      flex: 1; height: 1px;
      background: var(--borda);
    }

    /* Ripple */
    .ripple {
      position: absolute; border-radius: 50%;
      background: rgba(255,255,255,.22);
      transform: scale(0);
      animation: ripple .5s linear;
      pointer-events: none;
    }

    @keyframes ripple { to { transform: scale(4); opacity: 0; } }

    /* ── ANIMAÇÕES ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(14px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── RESPONSIVO ── */
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.aberta { transform: translateX(0); box-shadow: 4px 0 30px rgba(0,0,0,.12); }
      .topbar { display: flex; }
      .main { margin-left: 0; padding: calc(var(--nav-h) + 1.5rem) 1.25rem 3rem; }
      .form-layout { grid-template-columns: 1fr; }
      .lateral { order: -1; }
      .preview-card { display: none; }
    }

    @media (max-width: 520px) {
      .grid-2 { grid-template-columns: 1fr; }
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
    <a href="criar.php" class="nav-item ativo">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Novo orçamento
    </a>
    <a href="orcamentos.php" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
      Orçamentos
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
  <div style="width:60px"></div>
</header>

<!-- ── MAIN ── -->
<main class="main">

  <div class="page-header">
    <div class="page-header-left">
      <a href="dashboard.php" class="btn-voltar" title="Voltar">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
      <div class="page-title">
        <h1>Novo orçamento</h1>
        <p>Preencha os dados e compartilhe com o cliente</p>
      </div>
    </div>
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

  <form action="https://propix.xo.je/actions/acao.php" method="POST" id="formCriar">
    <input type="hidden" name="acao" value="criar_orcamento">

    <div class="form-layout">

      <!-- Coluna principal -->
      <div style="display:flex;flex-direction:column;gap:1.25rem">

        <!-- Dados do cliente -->
        <div class="card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="card-header-text">
              <h2>Dados do cliente</h2>
              <p>Informações de contato</p>
            </div>
          </div>
          <div class="card-body">
            <div class="grid-2">
              <div class="campo">
                <label class="campo-obrigatorio" for="cliente">Nome do cliente</label>
                <input type="text" id="cliente" name="cliente" placeholder="Nome completo" value="<?= $v['cliente'] ?>" required maxlength="120">
              </div>
              <div class="campo">
                <label class="campo-obrigatorio" for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="cliente@email.com" value="<?= $v['email'] ?>" required maxlength="120">
              </div>
              <div class="campo full">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" value="<?= $v['telefone'] ?>" maxlength="20">
              </div>
            </div>
          </div>
        </div>

        <!-- Detalhes do orçamento -->
        <div class="card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
            </div>
            <div class="card-header-text">
              <h2>Detalhes da proposta</h2>
              <p>Serviço, valor e prazo</p>
            </div>
          </div>
          <div class="card-body">
            <div class="grid-2">
              <div class="campo">
                <label class="campo-obrigatorio" for="servico">Serviço / Produto</label>
                <input type="text" id="servico" name="servico" placeholder="Ex: Desenvolvimento de site" value="<?= $v['servico'] ?>" required maxlength="120" oninput="atualizarPreview()">
              </div>
              <div class="campo">
                <label class="campo-obrigatorio" for="valor">Valor total</label>
                <div class="input-prefix-wrap">
                  <span class="input-prefix">R$</span>
                  <!-- Campo visível com máscara -->
                  <input type="text" id="valor_display" placeholder="0,00" value="<?= $v['valor'] ? number_format((float)$v['valor'], 2, ',', '.') : '' ?>" autocomplete="off" inputmode="numeric" oninput="mascaraValor(this)">
                  <!-- Campo real enviado ao backend (sempre em formato numérico) -->
                  <input type="hidden" id="valor" name="valor" value="<?= $v['valor'] ?>">
                </div>
              </div>
              <div class="campo">
                <label class="campo-obrigatorio" for="prazo">Prazo de entrega</label>
                <input type="text" id="prazo" name="prazo" placeholder="Ex: 15 dias úteis" value="<?= $v['prazo'] ?>" required maxlength="80" oninput="atualizarPreview()">
              </div>
              <div class="campo">
                <label for="cliente-preview">Cliente (preview)</label>
                <input type="text" id="cliente-preview-input" placeholder="Aparece no preview →" value="<?= $v['cliente'] ?>" disabled style="opacity:.5;cursor:not-allowed">
              </div>
              <div class="campo full">
                <label class="campo-obrigatorio" for="descricao">Descrição da proposta</label>
                <textarea id="descricao" name="descricao" rows="5" placeholder="Descreva detalhadamente o que está incluso nesta proposta..." required maxlength="1000" oninput="contarChars(this)"><?= $v['descricao'] ?></textarea>
                <div class="campo-footer">
                  <span class="char-count" id="charCount">0 / 1000</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Lateral -->
      <div class="lateral">

        <!-- Preview da proposta -->
        <div class="preview-card" id="previewCard">
          <p class="preview-titulo">Preview da proposta</p>
          <p class="preview-servico" id="prevServico">Serviço não informado</p>
          <p class="preview-cliente" id="prevCliente">Cliente não informado</p>
          <div class="preview-divider"></div>
          <div id="prevValorWrap">
            <p class="preview-valor-vazio">Valor não informado</p>
          </div>
          <p class="preview-prazo" id="prevPrazo" style="display:none">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span id="prevPrazoTxt"></span>
          </p>
        </div>

        <!-- Ação -->
        <div class="acao-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
            </div>
            <div class="card-header-text">
              <h2>Publicar proposta</h2>
              <p>Um link único será gerado</p>
            </div>
          </div>
          <div class="card-body">
            <button type="submit" class="btn-submit" id="btnSubmit">
              <svg viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
              Criar e gerar link
            </button>

            <div class="divisor-or">ou</div>

            <button type="button" class="btn-rascunho" onclick="history.back()">
              Cancelar
            </button>

            <div class="info-item">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              O link gerado pode ser compartilhado por WhatsApp, e-mail ou qualquer canal.
            </div>
            <div class="info-item">
              <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              O cliente só consegue aprovar ou recusar — sem acesso ao sistema.
            </div>
          </div>
        </div>

      </div>
    </div>
  </form>

</main>

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

  // ── Preview em tempo real ──
  const inpServico = document.getElementById('servico');
  const inpCliente = document.getElementById('cliente');
  const inpValor   = document.getElementById('valor');        // hidden — valor numérico
  const inpPrazo   = document.getElementById('prazo');

  // Sincroniza campo cliente com preview
  inpCliente.addEventListener('input', atualizarPreview);

  function atualizarPreview() {
    const servico = inpServico.value.trim();
    const cliente = inpCliente.value.trim();
    const valor   = parseFloat(inpValor.value);
    const prazo   = inpPrazo.value.trim();

    document.getElementById('prevServico').textContent =
      servico || 'Serviço não informado';

    document.getElementById('prevCliente').textContent =
      cliente ? 'Para: ' + cliente : 'Cliente não informado';

    const valorWrap = document.getElementById('prevValorWrap');
    if (inpValor.value && !isNaN(valor)) {
      valorWrap.innerHTML = `<p class="preview-valor"><sup>R$</sup>${valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>`;
    } else {
      valorWrap.innerHTML = `<p class="preview-valor-vazio">Valor não informado</p>`;
    }

    const prazoEl  = document.getElementById('prevPrazo');
    const prazoTxt = document.getElementById('prevPrazoTxt');
    if (prazo) {
      prazoEl.style.display  = 'flex';
      prazoTxt.textContent   = 'Prazo: ' + prazo;
    } else {
      prazoEl.style.display  = 'none';
    }
  }

  // Dispara preview inicial (campos repopulados do PHP)
  atualizarPreview();

  // ── Contador de caracteres ──
  function contarChars(el) {
    const max    = parseInt(el.getAttribute('maxlength')) || 1000;
    const atual  = el.value.length;
    const span   = document.getElementById('charCount');
    span.textContent = atual + ' / ' + max;
    span.className   = 'char-count' +
      (atual >= max ? ' limite' : atual >= max * .85 ? ' alerta-chars' : '');
  }

  // Dispara contador inicial
  const textarea = document.getElementById('descricao');
  if (textarea.value) contarChars(textarea);

  // ── Máscara de valor (R$ 1.000,00) ──
  function mascaraValor(el) {
    let v = el.value.replace(/\D/g, '');          // só dígitos
    v = (parseInt(v) || 0).toString();            // remove zeros à esquerda
    v = v.padStart(3, '0');                       // mínimo 3 dígitos
    const cents   = v.slice(-2);
    const inteiro = v.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    el.value = inteiro + ',' + cents;             // ex: 1.000,00

    // Sincroniza campo hidden (backend recebe em ponto flutuante)
    const numerico = inteiro.replace(/\./g, '') + '.' + cents;
    document.getElementById('valor').value = numerico;

    atualizarPreview();
  }

  // Dispara máscara nos campos repopulados
  const displayValor = document.getElementById('valor_display');
  if (displayValor.value) mascaraValor(displayValor);


  document.getElementById('telefone').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 2)  v = '(' + v.slice(0,2) + ') ' + v.slice(2);
    if (v.length > 10) v = v.slice(0,10) + '-' + v.slice(10);
    this.value = v;
  });

  // ── Ripple no botão ──
  document.getElementById('btnSubmit').addEventListener('click', function(e) {
    const r    = document.createElement('span');
    const rect = this.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    r.className     = 'ripple';
    r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - rect.left - size/2}px;top:${e.clientY - rect.top - size/2}px`;
    this.appendChild(r);
    setTimeout(() => r.remove(), 500);
  });

  // ── Loading no submit ──
  document.getElementById('formCriar').addEventListener('submit', function() {
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = `
      <svg viewBox="0 0 24 24" style="animation:spin .8s linear infinite">
        <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,.3)" stroke-width="3" fill="none"/>
        <path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round"/>
      </svg>
      Criando orçamento...
    `;
  });
</script>

<style>
  @keyframes spin { to { transform: rotate(360deg); } }
</style>

</body>
</html>