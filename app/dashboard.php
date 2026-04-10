<?php
session_start();
require_once '../config/auth.php';
require_once '../config/config.php';

// Dados reais virão do banco — variáveis preparadas para o backend
$nome_usuario = $_SESSION['nome'] ?? '';

// Contadores (serão substituídos por queries reais)
$total     = $total     ?? 0;
$aprovados = $aprovados ?? 0;
$pendentes = $pendentes ?? 0;
$recusados = $recusados ?? 0;

// Lista recente (será substituída por query real)
$recentes = $recentes ?? [];

$taxa = $total > 0 ? round(($aprovados / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propix — Dashboard</title>
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
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .logo-nome {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--texto);
      letter-spacing: -.02em;
    }

    .logo-nome span { color: var(--roxo); }

    .sidebar-nav {
      flex: 1;
      padding: 1rem .75rem;
      display: flex;
      flex-direction: column;
      gap: .25rem;
      overflow-y: auto;
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
      position: relative;
    }

    .nav-item svg {
      width: 17px; height: 17px;
      fill: none; stroke: currentColor;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
      flex-shrink: 0;
    }

    .nav-item:hover { background: var(--fundo); color: var(--texto); }

    .nav-item.ativo {
      background: var(--roxo-light);
      color: var(--roxo);
      font-weight: 600;
    }

    .nav-item.ativo svg { stroke: var(--roxo); }

    .nav-badge {
      margin-left: auto;
      background: var(--amarelo-bg);
      color: var(--amarelo);
      border: 1px solid var(--amarelo-borda);
      font-size: .65rem;
      font-weight: 700;
      padding: .1rem .45rem;
      border-radius: 99px;
    }

    .sidebar-footer {
      padding: 1rem .75rem;
      border-top: 1px solid var(--borda);
    }

    .usuario-box {
      display: flex;
      align-items: center;
      gap: .65rem;
      padding: .6rem .75rem;
      border-radius: 10px;
      margin-bottom: .4rem;
    }

    .avatar {
      width: 32px; height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--roxo), var(--roxo-mid));
      display: grid;
      place-items: center;
      font-size: .78rem;
      font-weight: 700;
      color: #fff;
      flex-shrink: 0;
    }

    .usuario-info { overflow: hidden; }

    .usuario-nome {
      font-size: .8rem;
      font-weight: 600;
      color: var(--texto);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .usuario-cargo {
      font-size: .7rem;
      color: var(--sub);
    }

    .btn-logout {
      display: flex;
      align-items: center;
      gap: .55rem;
      width: 100%;
      padding: .6rem .85rem;
      border-radius: 10px;
      border: none;
      background: none;
      font-family: 'Poppins', sans-serif;
      font-size: .82rem;
      font-weight: 500;
      color: var(--sub);
      cursor: pointer;
      text-decoration: none;
      transition: background .18s, color .18s;
    }

    .btn-logout svg {
      width: 16px; height: 16px;
      fill: none; stroke: currentColor;
      stroke-width: 2; stroke-linecap: round;
    }

    .btn-logout:hover { background: var(--vermelho-bg); color: var(--vermelho); }

    /* ── OVERLAY MOBILE ── */
    .overlay-menu {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15,10,30,.4);
      backdrop-filter: blur(3px);
      z-index: 40;
    }

    /* ── TOPBAR MOBILE ── */
    .topbar {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0;
      height: var(--nav-h);
      background: var(--branco);
      border-bottom: 1px solid var(--borda);
      align-items: center;
      justify-content: space-between;
      padding: 0 1.25rem;
      z-index: 30;
    }

    .topbar-logo {
      display: flex;
      align-items: center;
      gap: .45rem;
      text-decoration: none;
    }

    .btn-hamburguer {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--texto);
      padding: .35rem;
      display: grid;
      place-items: center;
      border-radius: 8px;
      transition: background .18s;
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

    .page-title h1 {
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--texto);
      letter-spacing: -.02em;
    }

    .page-title p {
      font-size: .82rem;
      color: var(--sub);
      margin-top: .15rem;
    }

    .btn-criar {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .7rem 1.25rem;
      background: var(--roxo);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: .87rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: background .2s, box-shadow .2s, transform .15s;
      white-space: nowrap;
    }

    .btn-criar svg {
      width: 16px; height: 16px;
      fill: none; stroke: currentColor;
      stroke-width: 2.5; stroke-linecap: round;
    }

    .btn-criar:hover { background: var(--roxo-dark); box-shadow: 0 4px 18px var(--roxo-glow); }
    .btn-criar:active { transform: scale(.97); }

    /* ── CARDS DE MÉTRICAS ── */
    .metricas {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .metrica-card {
      background: var(--branco);
      border: 1px solid var(--borda);
      border-radius: 16px;
      padding: 1.25rem 1.35rem;
      position: relative;
      overflow: hidden;
      transition: transform .2s, box-shadow .2s;
      animation: fadeUp .45s cubic-bezier(.22,.68,0,1.2) both;
    }

    .metrica-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(0,0,0,.07);
    }

    .metrica-card:nth-child(1) { animation-delay: .05s; }
    .metrica-card:nth-child(2) { animation-delay: .1s;  }
    .metrica-card:nth-child(3) { animation-delay: .15s; }
    .metrica-card:nth-child(4) { animation-delay: .2s;  }

    .metrica-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .metrica-label {
      font-size: .75rem;
      font-weight: 600;
      color: var(--sub);
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    .metrica-icone {
      width: 36px; height: 36px;
      border-radius: 10px;
      display: grid;
      place-items: center;
    }

    .metrica-icone svg {
      width: 17px; height: 17px;
      fill: none; stroke: currentColor;
      stroke-width: 2; stroke-linecap: round;
    }

    .metrica-icone.roxo     { background: var(--roxo-light);     color: var(--roxo);    }
    .metrica-icone.verde    { background: var(--verde-bg);        color: var(--verde);   }
    .metrica-icone.amarelo  { background: var(--amarelo-bg);      color: var(--amarelo); }
    .metrica-icone.vermelho { background: var(--vermelho-bg);     color: var(--vermelho);}

    .metrica-num {
      font-size: 2rem;
      font-weight: 700;
      color: var(--texto);
      letter-spacing: -.03em;
      line-height: 1;
      margin-bottom: .35rem;
    }

    .metrica-sub {
      font-size: .75rem;
      color: var(--sub);
    }

    /* Barra de progresso no card total */
    .metrica-barra {
      margin-top: .85rem;
      height: 3px;
      background: var(--borda);
      border-radius: 99px;
      overflow: hidden;
    }

    .metrica-barra-fill {
      height: 100%;
      border-radius: 99px;
      background: linear-gradient(90deg, var(--roxo), var(--roxo-mid));
      transition: width 1s cubic-bezier(.22,.68,0,1.2);
    }

    /* Detalhe decorativo */
    .metrica-card::after {
      content: '';
      position: absolute;
      bottom: -18px; right: -18px;
      width: 60px; height: 60px;
      border-radius: 50%;
      opacity: .045;
    }

    .metrica-card.total::after     { background: var(--roxo);    }
    .metrica-card.aprovado::after  { background: var(--verde);   }
    .metrica-card.pendente::after  { background: var(--amarelo); }
    .metrica-card.recusado::after  { background: var(--vermelho);}

    /* ── GRID INFERIOR ── */
    .grid-inferior {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 1.25rem;
      animation: fadeUp .5s ease both;
      animation-delay: .25s;
    }

    /* ── TABELA RECENTES ── */
    .painel {
      background: var(--branco);
      border: 1px solid var(--borda);
      border-radius: 16px;
      overflow: hidden;
    }

    .painel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--borda);
    }

    .painel-titulo {
      font-size: .9rem;
      font-weight: 700;
      color: var(--texto);
    }

    .painel-link {
      font-size: .78rem;
      font-weight: 600;
      color: var(--roxo);
      text-decoration: none;
      transition: color .2s;
    }

    .painel-link:hover { color: var(--roxo-dark); text-decoration: underline; }

    /* Tabela */
    .tabela { width: 100%; border-collapse: collapse; }

    .tabela th {
      font-size: .68rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: var(--sub);
      padding: .75rem 1.5rem;
      text-align: left;
      background: var(--fundo);
      border-bottom: 1px solid var(--borda);
    }

    .tabela td {
      padding: 1rem 1.5rem;
      font-size: .84rem;
      color: var(--texto);
      border-bottom: 1px solid var(--borda);
      vertical-align: middle;
    }

    .tabela tr:last-child td { border-bottom: none; }

    .tabela tbody tr {
      transition: background .15s;
    }

    .tabela tbody tr:hover { background: var(--fundo); }

    .cliente-info { display: flex; flex-direction: column; }

    .cliente-nome  { font-weight: 600; font-size: .85rem; }
    .cliente-email { font-size: .74rem; color: var(--sub); margin-top: .1rem; }

    .valor-tabela { font-weight: 600; color: var(--texto); white-space: nowrap; }

    /* Badge de status na tabela */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      padding: .28rem .65rem;
      border-radius: 99px;
      font-size: .72rem;
      font-weight: 600;
      white-space: nowrap;
    }

    .status-badge-dot { width: 5px; height: 5px; border-radius: 50%; }

    .status-badge.pendente  { background: var(--amarelo-bg);  color: var(--amarelo);  border: 1px solid var(--amarelo-borda); }
    .status-badge.aprovado  { background: var(--verde-bg);    color: var(--verde);    border: 1px solid var(--verde-borda);   }
    .status-badge.recusado  { background: var(--vermelho-bg); color: var(--vermelho); border: 1px solid var(--vermelho-borda); }

    .status-badge.pendente .status-badge-dot  { background: var(--amarelo);  animation: pulse 1.8s ease infinite; }
    .status-badge.aprovado .status-badge-dot  { background: var(--verde);   }
    .status-badge.recusado .status-badge-dot  { background: var(--vermelho); }

    @keyframes pulse {
      0%,100% { opacity: 1; transform: scale(1); }
      50%      { opacity: .5; transform: scale(.7); }
    }

    .btn-ver {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      padding: .32rem .7rem;
      background: var(--fundo);
      border: 1px solid var(--borda);
      border-radius: 7px;
      font-family: 'Poppins', sans-serif;
      font-size: .74rem;
      font-weight: 600;
      color: var(--sub);
      text-decoration: none;
      cursor: pointer;
      transition: background .18s, color .18s, border-color .18s;
    }

    .btn-ver svg { width: 12px; height: 12px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }
    .btn-ver:hover { background: var(--roxo-light); color: var(--roxo); border-color: #DDD6FE; }

    /* Estado vazio */
    .vazio {
      text-align: center;
      padding: 3rem 2rem;
    }

    .vazio-icon {
      width: 52px; height: 52px;
      background: var(--roxo-light);
      border-radius: 14px;
      display: grid;
      place-items: center;
      margin: 0 auto 1rem;
    }

    .vazio-icon svg {
      width: 24px; height: 24px;
      fill: none; stroke: var(--roxo);
      stroke-width: 1.8; stroke-linecap: round;
    }

    .vazio h3 { font-size: .95rem; font-weight: 600; color: var(--texto); margin-bottom: .35rem; }
    .vazio p  { font-size: .82rem; color: var(--sub); }

    /* ── PAINEL LATERAL ── */
    .painel-lateral { display: flex; flex-direction: column; gap: 1.25rem; }

    /* Taxa de aprovação */
    .taxa-card {
      background: linear-gradient(135deg, var(--roxo) 0%, var(--roxo-dark) 100%);
      border-radius: 16px;
      padding: 1.5rem;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .taxa-card::before {
      content: '';
      position: absolute;
      top: -30px; right: -30px;
      width: 120px; height: 120px;
      border-radius: 50%;
      background: rgba(255,255,255,.06);
    }

    .taxa-card::after {
      content: '';
      position: absolute;
      bottom: -40px; left: -20px;
      width: 100px; height: 100px;
      border-radius: 50%;
      background: rgba(255,255,255,.04);
    }

    .taxa-titulo {
      font-size: .72rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .08em;
      opacity: .75;
      margin-bottom: 1rem;
    }

    .taxa-num {
      font-size: 3rem;
      font-weight: 700;
      letter-spacing: -.04em;
      line-height: 1;
      margin-bottom: .35rem;
    }

    .taxa-num sup { font-size: 1.4rem; vertical-align: super; }

    .taxa-sub { font-size: .78rem; opacity: .75; }

    /* Anel de progresso */
    .taxa-anel {
      position: relative;
      width: 80px; height: 80px;
      margin: 1.25rem auto 0;
    }

    .taxa-anel svg { transform: rotate(-90deg); }
    .taxa-anel circle { fill: none; stroke-width: 6; stroke-linecap: round; }
    .taxa-anel .fundo { stroke: rgba(255,255,255,.15); }
    .taxa-anel .fill  { stroke: rgba(255,255,255,.9); transition: stroke-dashoffset 1.2s cubic-bezier(.22,.68,0,1.2); }

    .taxa-anel-label {
      position: absolute;
      inset: 0;
      display: grid;
      place-items: center;
      font-size: .78rem;
      font-weight: 700;
      color: #fff;
    }

    /* Ações rápidas */
    .acoes-rapidas { background: var(--branco); border: 1px solid var(--borda); border-radius: 16px; overflow: hidden; }

    .acao-item {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: 1rem 1.25rem;
      text-decoration: none;
      border-bottom: 1px solid var(--borda);
      transition: background .18s;
    }

    .acao-item:last-child { border-bottom: none; }
    .acao-item:hover { background: var(--fundo); }

    .acao-icone {
      width: 34px; height: 34px;
      border-radius: 9px;
      display: grid;
      place-items: center;
      flex-shrink: 0;
    }

    .acao-icone svg {
      width: 15px; height: 15px;
      fill: none; stroke: currentColor;
      stroke-width: 2; stroke-linecap: round;
    }

    .acao-icone.roxo    { background: var(--roxo-light);  color: var(--roxo);    }
    .acao-icone.verde   { background: var(--verde-bg);     color: var(--verde);   }
    .acao-icone.amarelo { background: var(--amarelo-bg);   color: var(--amarelo); }

    .acao-texto { flex: 1; }
    .acao-nome  { font-size: .84rem; font-weight: 600; color: var(--texto); }
    .acao-desc  { font-size: .73rem; color: var(--sub); margin-top: .05rem; }

    .acao-seta { color: var(--sub); }
    .acao-seta svg { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

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
    @media (max-width: 1100px) {
      .metricas { grid-template-columns: repeat(2, 1fr); }
      .grid-inferior { grid-template-columns: 1fr; }
      .painel-lateral { flex-direction: row; }
    }

    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.aberta { transform: translateX(0); box-shadow: 4px 0 30px rgba(0,0,0,.12); }
      .topbar { display: flex; }
      .main { margin-left: 0; padding: calc(var(--nav-h) + 1.5rem) 1.25rem 3rem; }
      .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
      .painel-lateral { flex-direction: column; }
    }

    @media (max-width: 520px) {
      .metricas { grid-template-columns: 1fr 1fr; }
      .metrica-num { font-size: 1.6rem; }
      .tabela th:nth-child(3),
      .tabela td:nth-child(3) { display: none; }
    }

    @media (max-width: 380px) {
      .metricas { grid-template-columns: 1fr; }
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

    <a href="dashboard.php" class="nav-item ativo">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>

    <a href="criar.php" class="nav-item">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Novo orçamento
    </a>

    <a href="orcamentos.php" class="nav-item">
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

<!-- Overlay mobile -->
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

  <!-- Page header -->
  <div class="page-header">
    <div class="page-title">
      <h1>Dashboard</h1>
      <p>Olá, <?= htmlspecialchars($nome_usuario) ?>! Aqui está seu resumo.</p>
    </div>
    <a href="criar.php" class="btn-criar">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Novo orçamento
    </a>
  </div>

  <!-- Métricas -->
  <div class="metricas">

    <div class="metrica-card total">
      <div class="metrica-top">
        <span class="metrica-label">Total</span>
        <div class="metrica-icone roxo">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
      </div>
      <p class="metrica-num" data-count="<?= $total ?>"><?= $total ?></p>
      <p class="metrica-sub">orçamentos criados</p>
      <div class="metrica-barra">
        <div class="metrica-barra-fill" id="barraTotal" style="width:0%"></div>
      </div>
    </div>

    <div class="metrica-card aprovado">
      <div class="metrica-top">
        <span class="metrica-label">Aprovados</span>
        <div class="metrica-icone verde">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
      </div>
      <p class="metrica-num" data-count="<?= $aprovados ?>"><?= $aprovados ?></p>
      <p class="metrica-sub">propostas aceitas</p>
    </div>

    <div class="metrica-card pendente">
      <div class="metrica-top">
        <span class="metrica-label">Pendentes</span>
        <div class="metrica-icone amarelo">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
      </div>
      <p class="metrica-num" data-count="<?= $pendentes ?>"><?= $pendentes ?></p>
      <p class="metrica-sub">aguardando resposta</p>
    </div>

    <div class="metrica-card recusado">
      <div class="metrica-top">
        <span class="metrica-label">Recusados</span>
        <div class="metrica-icone vermelho">
          <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
      </div>
      <p class="metrica-num" data-count="<?= $recusados ?>"><?= $recusados ?></p>
      <p class="metrica-sub">propostas recusadas</p>
    </div>

  </div>

  <!-- Grid inferior -->
  <div class="grid-inferior">

    <!-- Tabela recentes -->
    <div class="painel">
      <div class="painel-header">
        <span class="painel-titulo">Orçamentos recentes</span>
        <a href="orcamentos.php" class="painel-link">Ver todos →</a>
      </div>

      <?php if (empty($recentes)): ?>
        <div class="vazio">
          <div class="vazio-icon">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
          </div>
          <h3>Nenhum orçamento ainda</h3>
          <p>Crie seu primeiro orçamento e compartilhe com um cliente.</p>
        </div>
      <?php else: ?>
        <table class="tabela">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Serviço</th>
              <th>Valor</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentes as $o): ?>
              <tr>
                <td>
                  <div class="cliente-info">
                    <span class="cliente-nome"><?= htmlspecialchars($o['cliente']) ?></span>
                    <span class="cliente-email"><?= htmlspecialchars($o['email']) ?></span>
                  </div>
                </td>
                <td><?= htmlspecialchars($o['servico']) ?></td>
                <td class="valor-tabela">R$ <?= number_format((float)$o['valor'], 2, ',', '.') ?></td>
                <td>
                  <span class="status-badge <?= htmlspecialchars($o['status']) ?>">
                    <span class="status-badge-dot"></span>
                    <?= match($o['status']) { 'aprovado' => 'Aprovado', 'recusado' => 'Recusado', default => 'Pendente' } ?>
                  </span>
                </td>
                <td>
                  <a href="../public/visualizar.php?token=<?= htmlspecialchars($o['hash']) ?>" class="btn-ver" target="_blank">
                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Ver
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- Lateral -->
    <div class="painel-lateral">

      <!-- Taxa de aprovação -->
      <div class="taxa-card">
        <p class="taxa-titulo">Taxa de aprovação</p>
        <p class="taxa-num"><?= $taxa ?><sup>%</sup></p>
        <p class="taxa-sub">
          <?= $aprovados ?> de <?= $total ?> orçamentos aprovados
        </p>
        <div class="taxa-anel">
          <svg viewBox="0 0 80 80" width="80" height="80">
            <circle class="fundo" cx="40" cy="40" r="32" />
            <circle
              class="fill"
              cx="40" cy="40" r="32"
              stroke-dasharray="201"
              stroke-dashoffset="201"
              id="anelFill"
            />
          </svg>
          <span class="taxa-anel-label"><?= $taxa ?>%</span>
        </div>
      </div>

      <!-- Ações rápidas -->
      <div class="acoes-rapidas painel">
        <div class="painel-header">
          <span class="painel-titulo">Ações rápidas</span>
        </div>
        <a href="criar.php" class="acao-item">
          <div class="acao-icone roxo">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </div>
          <div class="acao-texto">
            <p class="acao-nome">Novo orçamento</p>
            <p class="acao-desc">Criar e compartilhar proposta</p>
          </div>
          <span class="acao-seta"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
        </a>
        <a href="orcamentos.php?filtro=pendente" class="acao-item">
          <div class="acao-icone amarelo">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="acao-texto">
            <p class="acao-nome">Ver pendentes</p>
            <p class="acao-desc"><?= $pendentes ?> aguardando resposta</p>
          </div>
          <span class="acao-seta"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
        </a>
        <a href="orcamentos.php?filtro=aprovado" class="acao-item">
          <div class="acao-icone verde">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div class="acao-texto">
            <p class="acao-nome">Ver aprovados</p>
            <p class="acao-desc"><?= $aprovados ?> propostas aceitas</p>
          </div>
          <span class="acao-seta"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
        </a>
      </div>

    </div>
  </div>

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

  btnMenu.addEventListener('click', abrirMenu);
  overlayMenu.addEventListener('click', fecharMenu);

  // ── Contador animado ──
  function animarContador(el) {
    const alvo = parseInt(el.dataset.count) || 0;
    if (alvo === 0) return;
    let atual = 0;
    const duracao = 800;
    const passo   = Math.ceil(duracao / alvo);
    const timer   = setInterval(() => {
      atual += Math.ceil(alvo / 40);
      if (atual >= alvo) { atual = alvo; clearInterval(timer); }
      el.textContent = atual;
    }, passo);
  }

  document.querySelectorAll('.metrica-num[data-count]').forEach(animarContador);

  // ── Barra de progresso total ──
  const taxa = <?= $taxa ?>;

  setTimeout(() => {
    const barra = document.getElementById('barraTotal');
    if (barra) barra.style.width = Math.min(taxa, 100) + '%';
  }, 300);

  // ── Anel SVG de taxa ──
  setTimeout(() => {
    const anel = document.getElementById('anelFill');
    if (!anel) return;
    const circunferencia = 201;
    const offset = circunferencia - (taxa / 100) * circunferencia;
    anel.style.strokeDashoffset = offset;
  }, 400);
</script>

</body>
</html>