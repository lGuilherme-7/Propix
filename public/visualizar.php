<?php
session_start();
require_once '../config/config.php';

$token = trim($_GET['token'] ?? '');
$msg   = $_GET['msg']  ?? '';
$erro  = $_GET['erro'] ?? '';

// Token obrigatório
if (empty($token)) {
    http_response_code(404);
    die('Proposta não encontrada.');
}

$pdo  = conectar();
$stmt = $pdo->prepare(
    'SELECT cliente, email, telefone, servico, valor, descricao, prazo,
            status, hash,
            DATE_FORMAT(data_criacao, "%d/%m/%Y") AS data_criacao
     FROM orcamentos
     WHERE hash = ?
     LIMIT 1'
);
$stmt->execute([$token]);
$orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

// Não encontrou
if (!$orcamento) {
    http_response_code(404);
    die('Proposta não encontrada ou link inválido.');
}

$status  = $orcamento['status'];
$hash    = $orcamento['hash'];
$pendente = $status === 'pendente';

$status_label = match($status) {
    'aprovado' => 'Aprovado',
    'recusado' => 'Recusado',
    default    => 'Aguardando resposta',
};

$status_cor = match($status) {
    'aprovado' => 'aprovado',
    'recusado' => 'recusado',
    default    => 'pendente',
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propix — Proposta</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --roxo:         #7C3AED;
      --roxo-dark:    #5B21B6;
      --roxo-light:   #EDE9FE;
      --roxo-glow:    rgba(124, 58, 237, 0.13);
      --texto:        #1E1B2E;
      --sub:          #6B7280;
      --borda:        #E5E7EB;
      --fundo:        #F8F7FF;
      --branco:       #FFFFFF;
      --verde:        #10B981;
      --verde-bg:     #ECFDF5;
      --verde-borda:  #A7F3D0;
      --verde-glow:   rgba(16, 185, 129, 0.15);
      --vermelho:     #EF4444;
      --vermelho-bg:  #FEF2F2;
      --vermelho-borda:#FECACA;
      --vermelho-glow:rgba(239, 68, 68, 0.15);
      --amarelo:      #F59E0B;
      --amarelo-bg:   #FFFBEB;
      --amarelo-borda:#FDE68A;
      --erro-bg:      #FEF2F2;
      --erro-cor:     #EF4444;
      --erro-borda:   #FECACA;
      --ok-bg:        #ECFDF5;
      --ok-cor:       #10B981;
      --ok-borda:     #A7F3D0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--fundo);
      min-height: 100vh;
      padding: 2rem 1.25rem 4rem;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 55% 40% at 80% 10%, rgba(124,58,237,0.06) 0%, transparent 70%),
        radial-gradient(ellipse 45% 40% at 10% 90%, rgba(167,139,250,0.05) 0%, transparent 60%);
      pointer-events: none;
      z-index: 0;
    }

    .container {
      position: relative;
      z-index: 1;
      max-width: 620px;
      margin: 0 auto;
    }

    /* Topo */
    .topo {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 2rem;
      animation: fadeDown .4s ease both;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: .45rem;
      text-decoration: none;
    }

    .logo-icon {
      width: 30px;
      height: 30px;
      background: var(--roxo);
      border-radius: 8px;
      display: grid;
      place-items: center;
    }

    .logo-icon svg {
      width: 14px;
      height: 14px;
      fill: none;
      stroke: #fff;
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .logo-nome {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--texto);
      letter-spacing: -.02em;
    }

    .logo-nome span { color: var(--roxo); }

    /* Badge de status */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .35rem .75rem;
      border-radius: 99px;
      font-size: .75rem;
      font-weight: 600;
      letter-spacing: .01em;
    }

    .badge-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
    }

    .badge.pendente  { background: var(--amarelo-bg);  color: var(--amarelo);  border: 1px solid var(--amarelo-borda); }
    .badge.aprovado  { background: var(--verde-bg);    color: var(--verde);    border: 1px solid var(--verde-borda);   }
    .badge.recusado  { background: var(--vermelho-bg); color: var(--vermelho); border: 1px solid var(--vermelho-borda); }

    .badge.pendente  .badge-dot { background: var(--amarelo);  animation: pulse 1.8s ease infinite; }
    .badge.aprovado  .badge-dot { background: var(--verde);    }
    .badge.recusado  .badge-dot { background: var(--vermelho); }

    @keyframes pulse {
      0%,100% { opacity: 1; transform: scale(1); }
      50%      { opacity: .5; transform: scale(.7); }
    }

    /* Card principal */
    .card {
      background: var(--branco);
      border: 1px solid var(--borda);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 8px 40px rgba(124,58,237,0.07), 0 1px 3px rgba(0,0,0,0.04);
      animation: fadeUp .45s cubic-bezier(.22,.68,0,1.2) both;
      animation-delay: .05s;
    }

    /* Header do card */
    .card-header {
      background: linear-gradient(135deg, var(--roxo) 0%, var(--roxo-dark) 100%);
      padding: 2rem 2rem 1.75rem;
      color: #fff;
    }

    .card-header-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }

    .servico-label {
      font-size: .72rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: .08em;
      opacity: .75;
      margin-bottom: .3rem;
    }

    .servico-nome {
      font-size: 1.25rem;
      font-weight: 700;
      line-height: 1.3;
    }

    .valor-wrap { text-align: right; flex-shrink: 0; }

    .valor-label {
      font-size: .72rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: .08em;
      opacity: .75;
      margin-bottom: .3rem;
    }

    .valor-num {
      font-size: 1.75rem;
      font-weight: 700;
      line-height: 1;
      letter-spacing: -.02em;
    }

    .valor-num sup {
      font-size: .9rem;
      font-weight: 600;
      vertical-align: super;
      margin-right: .1rem;
    }

    .divider-header {
      height: 1px;
      background: rgba(255,255,255,.18);
      margin: 0 0 1.25rem;
    }

    .meta-row {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      flex-wrap: wrap;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: .4rem;
      font-size: .78rem;
      opacity: .85;
    }

    .meta-item svg {
      width: 13px;
      height: 13px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2;
      stroke-linecap: round;
      flex-shrink: 0;
      opacity: .9;
    }

    /* Corpo */
    .card-body { padding: 1.75rem 2rem; }

    .secao { margin-bottom: 1.5rem; }
    .secao:last-child { margin-bottom: 0; }

    .secao-titulo {
      font-size: .72rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: var(--sub);
      margin-bottom: .85rem;
    }

    /* Dados do cliente */
    .dados-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .75rem;
    }

    .dado {
      background: var(--fundo);
      border: 1px solid var(--borda);
      border-radius: 10px;
      padding: .75rem 1rem;
    }

    .dado-label {
      font-size: .7rem;
      font-weight: 600;
      color: var(--sub);
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: .2rem;
    }

    .dado-valor {
      font-size: .85rem;
      font-weight: 500;
      color: var(--texto);
      word-break: break-word;
    }

    /* Descrição */
    .descricao-box {
      background: var(--fundo);
      border: 1px solid var(--borda);
      border-radius: 10px;
      padding: 1rem 1.1rem;
      font-size: .87rem;
      color: var(--texto);
      line-height: 1.65;
    }

    /* Prazo */
    .prazo-box {
      display: flex;
      align-items: center;
      gap: .6rem;
      background: var(--roxo-light);
      border: 1px solid #DDD6FE;
      border-radius: 10px;
      padding: .85rem 1.1rem;
    }

    .prazo-box svg {
      width: 16px;
      height: 16px;
      fill: none;
      stroke: var(--roxo);
      stroke-width: 2;
      stroke-linecap: round;
      flex-shrink: 0;
    }

    .prazo-txt {
      font-size: .85rem;
      font-weight: 500;
      color: var(--roxo-dark);
    }

    /* Alertas */
    .alerta {
      display: flex;
      align-items: center;
      gap: .5rem;
      padding: .7rem 1rem;
      border-radius: 10px;
      font-size: .82rem;
      font-weight: 500;
      margin-bottom: 1.25rem;
      animation: fadeUp .3s ease both;
    }

    .alerta.erro    { background: var(--erro-bg); color: var(--erro-cor); border: 1px solid var(--erro-borda); }
    .alerta.sucesso { background: var(--ok-bg);   color: var(--ok-cor);   border: 1px solid var(--ok-borda);  }
    .alerta svg { flex-shrink: 0; width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

    /* Ações */
    .acoes {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .75rem;
      margin-top: 1.75rem;
      animation: fadeUp .5s cubic-bezier(.22,.68,0,1.2) both;
      animation-delay: .15s;
    }

    .btn-acao {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      padding: .9rem 1rem;
      border: none;
      border-radius: 12px;
      font-family: 'Poppins', sans-serif;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: transform .15s, box-shadow .2s, filter .2s;
      text-decoration: none;
    }

    .btn-acao svg {
      width: 17px;
      height: 17px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2.2;
      stroke-linecap: round;
      flex-shrink: 0;
    }

    .btn-aprovar {
      background: var(--verde);
      color: #fff;
    }

    .btn-aprovar:hover {
      filter: brightness(1.08);
      box-shadow: 0 4px 20px var(--verde-glow);
    }

    .btn-recusar {
      background: var(--vermelho-bg);
      color: var(--vermelho);
      border: 1.5px solid var(--vermelho-borda);
    }

    .btn-recusar:hover {
      background: var(--vermelho);
      color: #fff;
      box-shadow: 0 4px 20px var(--vermelho-glow);
    }

    .btn-acao:active { transform: scale(.97); }

    .ripple {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,.25);
      transform: scale(0);
      animation: ripple .5s linear;
      pointer-events: none;
    }

    @keyframes ripple {
      to { transform: scale(4); opacity: 0; }
    }

    /* Status final */
    .status-final {
      text-align: center;
      padding: 1.5rem 1rem;
      border-radius: 12px;
      margin-top: 1.75rem;
      animation: fadeUp .5s ease both;
      animation-delay: .1s;
    }

    .status-final.aprovado {
      background: var(--verde-bg);
      border: 1px solid var(--verde-borda);
    }

    .status-final.recusado {
      background: var(--vermelho-bg);
      border: 1px solid var(--vermelho-borda);
    }

    .status-final-icon {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      margin: 0 auto .75rem;
    }

    .status-final.aprovado .status-final-icon { background: var(--verde);    }
    .status-final.recusado .status-final-icon { background: var(--vermelho); }

    .status-final-icon svg {
      width: 22px;
      height: 22px;
      fill: none;
      stroke: #fff;
      stroke-width: 2.5;
      stroke-linecap: round;
    }

    .status-final h3 {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: .3rem;
    }

    .status-final.aprovado h3 { color: var(--verde); }
    .status-final.recusado h3 { color: var(--vermelho); }

    .status-final p {
      font-size: .82rem;
      color: var(--sub);
    }

    /* Rodapé */
    .rodape {
      text-align: center;
      margin-top: 2rem;
      font-size: .75rem;
      color: var(--sub);
      animation: fadeUp .5s ease both;
      animation-delay: .2s;
    }

    .rodape a {
      color: var(--roxo);
      font-weight: 600;
      text-decoration: none;
    }

    .rodape a:hover { text-decoration: underline; }

    /* Animações */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(14px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* Modal de confirmação */
    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 10, 30, .45);
      backdrop-filter: blur(4px);
      z-index: 100;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    .overlay.ativo { display: flex; animation: fadeOverlay .2s ease; }

    @keyframes fadeOverlay {
      from { opacity: 0; }
      to   { opacity: 1; }
    }

    .modal {
      background: var(--branco);
      border-radius: 18px;
      padding: 2rem 1.75rem;
      width: 100%;
      max-width: 360px;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,.15);
      animation: scaleIn .25s cubic-bezier(.22,.68,0,1.2) both;
    }

    @keyframes scaleIn {
      from { opacity: 0; transform: scale(.92); }
      to   { opacity: 1; transform: scale(1); }
    }

    .modal-icon {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      margin: 0 auto 1rem;
    }

    .modal-icon svg {
      width: 24px;
      height: 24px;
      fill: none;
      stroke: #fff;
      stroke-width: 2.2;
      stroke-linecap: round;
    }

    .modal-icon.verde    { background: var(--verde);    }
    .modal-icon.vermelho { background: var(--vermelho); }

    .modal h3 {
      font-size: 1.05rem;
      font-weight: 600;
      color: var(--texto);
      margin-bottom: .4rem;
    }

    .modal p {
      font-size: .83rem;
      color: var(--sub);
      line-height: 1.55;
      margin-bottom: 1.5rem;
    }

    .modal-btns {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .6rem;
    }

    .btn-cancelar {
      padding: .7rem;
      background: var(--fundo);
      border: 1.5px solid var(--borda);
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: .85rem;
      font-weight: 600;
      color: var(--sub);
      cursor: pointer;
      transition: background .2s;
    }

    .btn-cancelar:hover { background: var(--borda); }

    .btn-confirmar {
      padding: .7rem;
      border: none;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: .85rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      transition: filter .2s;
    }

    .btn-confirmar.verde    { background: var(--verde);    }
    .btn-confirmar.vermelho { background: var(--vermelho); }
    .btn-confirmar:hover    { filter: brightness(1.08);    }

    /* Responsivo */
    @media (max-width: 480px) {
      .card-header { padding: 1.5rem 1.25rem; }
      .card-body   { padding: 1.5rem 1.25rem; }
      .dados-grid  { grid-template-columns: 1fr; }
      .card-header-top { flex-direction: column; gap: .75rem; }
      .valor-wrap { text-align: left; }
      .valor-num  { font-size: 1.5rem; }
      .acoes      { grid-template-columns: 1fr; }
      .modal-btns { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="container">

  <!-- Topo -->
  <div class="topo">
    <a href="#" class="logo">
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

    <span class="badge <?= $status_cor ?>">
      <span class="badge-dot"></span>
      <?= $status_label ?>
    </span>
  </div>

  <!-- Alertas -->
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

  <!-- Card da proposta -->
  <div class="card">

    <!-- Header roxo -->
    <div class="card-header">
      <div class="card-header-top">
        <div>
          <p class="servico-label">Proposta de serviço</p>
          <h2 class="servico-nome"><?= htmlspecialchars($orcamento['servico']) ?></h2>
        </div>
        <div class="valor-wrap">
          <p class="valor-label">Valor total</p>
          <p class="valor-num">
            <sup>R$</sup><?= number_format((float)$orcamento['valor'], 2, ',', '.') ?>
          </p>
        </div>
      </div>

      <div class="divider-header"></div>

      <div class="meta-row">
        <span class="meta-item">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <?= htmlspecialchars($orcamento['data_criacao']) ?>
        </span>
        <span class="meta-item">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Prazo: <?= htmlspecialchars($orcamento['prazo']) ?>
        </span>
      </div>
    </div>

    <!-- Corpo -->
    <div class="card-body">

      <!-- Cliente -->
      <div class="secao">
        <p class="secao-titulo">Dados do cliente</p>
        <div class="dados-grid">
          <div class="dado">
            <p class="dado-label">Nome</p>
            <p class="dado-valor"><?= htmlspecialchars($orcamento['cliente']) ?></p>
          </div>
          <div class="dado">
            <p class="dado-label">E-mail</p>
            <p class="dado-valor"><?= htmlspecialchars($orcamento['email']) ?></p>
          </div>
          <div class="dado">
            <p class="dado-label">Telefone</p>
            <p class="dado-valor"><?= htmlspecialchars($orcamento['telefone']) ?></p>
          </div>
          <div class="dado">
            <p class="dado-label">Prazo de entrega</p>
            <p class="dado-valor"><?= htmlspecialchars($orcamento['prazo']) ?></p>
          </div>
        </div>
      </div>

      <!-- Descrição -->
      <div class="secao">
        <p class="secao-titulo">Descrição da proposta</p>
        <div class="descricao-box">
          <?= nl2br(htmlspecialchars($orcamento['descricao'])) ?>
        </div>
      </div>

    </div><!-- /card-body -->
  </div><!-- /card -->

  <!-- Ações (pendente) ou Status final -->
  <?php if ($pendente): ?>
    <div class="acoes">
      <button class="btn-acao btn-aprovar" id="btnAprovar">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Aprovar proposta
      </button>
      <button class="btn-acao btn-recusar" id="btnRecusar">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Recusar
      </button>
    </div>

  <?php elseif ($status === 'aprovado'): ?>
    <div class="status-final aprovado">
      <div class="status-final-icon">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h3>Proposta aprovada!</h3>
      <p>Obrigado pela confirmação. Entraremos em contato em breve.</p>
    </div>

  <?php elseif ($status === 'recusado'): ?>
    <div class="status-final recusado">
      <div class="status-final-icon">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </div>
      <h3>Proposta recusada</h3>
      <p>Recebemos sua resposta. Fique à vontade para entrar em contato.</p>
    </div>
  <?php endif; ?>

  <!-- Rodapé -->
  <p class="rodape">
    Proposta gerada por <a href="#">Propix</a> &mdash; Orçamentos profissionais
  </p>

</div><!-- /container -->

<!-- Modal Aprovar -->
<div class="overlay" id="modalAprovar">
  <div class="modal">
    <div class="modal-icon verde">
      <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h3>Confirmar aprovação?</h3>
    <p>Ao confirmar, o responsável será notificado e a proposta ficará marcada como aprovada.</p>
    <div class="modal-btns">
      <button class="btn-cancelar" id="cancelarAprovar">Cancelar</button>
      <form action="/actions/acao.php" method="POST" style="display:contents">
        <input type="hidden" name="acao"   value="aprovar">
        <input type="hidden" name="hash"   value="<?= htmlspecialchars($hash) ?>">
        <button type="submit" class="btn-confirmar verde">Aprovar</button>
      </form>
    </div>
  </div>
</div>

<!-- Modal Recusar -->
<div class="overlay" id="modalRecusar">
  <div class="modal">
    <div class="modal-icon vermelho">
      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </div>
    <h3>Recusar proposta?</h3>
    <p>Tem certeza? O responsável será informado que a proposta foi recusada.</p>
    <div class="modal-btns">
      <button class="btn-cancelar" id="cancelarRecusar">Cancelar</button>
      <form action="/actions/acao.php" method="POST" style="display:contents">
        <input type="hidden" name="acao"  value="recusar">
        <input type="hidden" name="hash"  value="<?= htmlspecialchars($hash) ?>">
        <button type="submit" class="btn-confirmar vermelho">Recusar</button>
      </form>
    </div>
  </div>
</div>

<script>
  // Abre modais
  <?php if ($pendente): ?>
  document.getElementById('btnAprovar').addEventListener('click', () => {
    document.getElementById('modalAprovar').classList.add('ativo');
  });

  document.getElementById('btnRecusar').addEventListener('click', () => {
    document.getElementById('modalRecusar').classList.add('ativo');
  });

  // Fecha modais
  document.getElementById('cancelarAprovar').addEventListener('click', () => {
    document.getElementById('modalAprovar').classList.remove('ativo');
  });

  document.getElementById('cancelarRecusar').addEventListener('click', () => {
    document.getElementById('modalRecusar').classList.remove('ativo');
  });

  // Fecha ao clicar fora
  document.querySelectorAll('.overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) overlay.classList.remove('ativo');
    });
  });

  // Ripple nos botões de ação
  document.querySelectorAll('.btn-acao').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const r    = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      r.className     = 'ripple';
      r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - rect.left - size/2}px;top:${e.clientY - rect.top - size/2}px`;
      this.appendChild(r);
      setTimeout(() => r.remove(), 500);
    });
  });
  <?php endif; ?>
</script>

</body>
</html>