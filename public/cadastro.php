<?php
session_start();

// Já logado? Redireciona
if (isset($_SESSION['usuario_id'])) {
    header('Location: ../app/dashboard.php');
    exit;
}

$erro = $_GET['erro'] ?? '';
$msg  = $_GET['msg']  ?? '';

// Repopula campos em caso de erro
$nome  = htmlspecialchars($_GET['nome']  ?? '');
$email = htmlspecialchars($_GET['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propix — Criar conta</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --roxo:        #7C3AED;
      --roxo-dark:   #5B21B6;
      --roxo-glow:   rgba(124, 58, 237, 0.15);
      --texto:       #1E1B2E;
      --sub:         #6B7280;
      --borda:       #E5E7EB;
      --fundo:       #F8F7FF;
      --branco:      #FFFFFF;
      --erro-bg:     #FEF2F2;
      --erro-cor:    #EF4444;
      --erro-borda:  #FECACA;
      --ok-bg:       #ECFDF5;
      --ok-cor:      #10B981;
      --ok-borda:    #A7F3D0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--fundo);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 60% 50% at 30% 10%, rgba(124,58,237,0.07) 0%, transparent 70%),
        radial-gradient(ellipse 40% 40% at 80% 85%, rgba(167,139,250,0.06) 0%, transparent 60%);
      pointer-events: none;
    }

    .card {
      position: relative;
      background: var(--branco);
      border: 1px solid var(--borda);
      border-radius: 20px;
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 8px 40px rgba(124,58,237,0.07), 0 1px 3px rgba(0,0,0,0.04);
      animation: fadeUp .4s cubic-bezier(.22,.68,0,1.2) both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .logo {
      display: flex;
      align-items: center;
      gap: .5rem;
      margin-bottom: 1.75rem;
    }

    .logo-icon {
      width: 34px;
      height: 34px;
      background: var(--roxo);
      border-radius: 9px;
      display: grid;
      place-items: center;
    }

    .logo-icon svg {
      width: 17px;
      height: 17px;
      fill: none;
      stroke: #fff;
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .logo-nome {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--texto);
      letter-spacing: -.02em;
    }

    .logo-nome span { color: var(--roxo); }

    h1   { font-size: 1.2rem; font-weight: 600; color: var(--texto); }
    .sub { font-size: .83rem; color: var(--sub); margin: .3rem 0 1.75rem; }

    .alerta {
      display: flex;
      align-items: center;
      gap: .5rem;
      padding: .65rem .9rem;
      border-radius: 10px;
      font-size: .81rem;
      font-weight: 500;
      margin-bottom: 1.2rem;
      animation: fadeUp .3s ease both;
    }

    .alerta.erro    { background: var(--erro-bg); color: var(--erro-cor); border: 1px solid var(--erro-borda); }
    .alerta.sucesso { background: var(--ok-bg);   color: var(--ok-cor);   border: 1px solid var(--ok-borda); }
    .alerta svg { flex-shrink: 0; width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

    .campo { margin-bottom: 1rem; }

    label {
      display: block;
      font-size: .77rem;
      font-weight: 600;
      color: var(--texto);
      margin-bottom: .38rem;
    }

    input {
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
    }

    input:focus {
      border-color: var(--roxo);
      box-shadow: 0 0 0 3px var(--roxo-glow);
    }

    input::placeholder { color: #C4B5FD; }

    /* Força da senha */
    .forca-wrap {
      margin-top: .5rem;
      display: none;
    }

    .forca-wrap.visivel { display: block; }

    .forca-barras {
      display: flex;
      gap: 4px;
      margin-bottom: .3rem;
    }

    .barra {
      flex: 1;
      height: 3px;
      border-radius: 99px;
      background: var(--borda);
      transition: background .3s;
    }

    .forca-label {
      font-size: .72rem;
      color: var(--sub);
      font-weight: 500;
    }

    /* Campos com senha */
    .campo-senha { position: relative; }
    .campo-senha input { padding-right: 2.8rem; }

    .toggle-senha {
      position: absolute;
      right: .85rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: var(--sub);
      display: grid;
      place-items: center;
      padding: .2rem;
      transition: color .2s;
    }

    .toggle-senha:hover { color: var(--roxo); }
    .toggle-senha svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; }

    .btn {
      width: 100%;
      padding: .8rem;
      background: var(--roxo);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: .5rem;
      position: relative;
      overflow: hidden;
      transition: background .2s, box-shadow .2s, transform .15s;
    }

    .btn:hover  { background: var(--roxo-dark); box-shadow: 0 4px 18px var(--roxo-glow); }
    .btn:active { transform: scale(.97); }

    .ripple {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,.22);
      transform: scale(0);
      animation: ripple .5s linear;
      pointer-events: none;
    }

    @keyframes ripple {
      to { transform: scale(4); opacity: 0; }
    }

    .rodape {
      text-align: center;
      margin-top: 1.5rem;
      font-size: .81rem;
      color: var(--sub);
    }

    .rodape a {
      color: var(--roxo);
      font-weight: 600;
      text-decoration: none;
      transition: color .2s;
    }

    .rodape a:hover { color: var(--roxo-dark); }

    .shake { animation: shake .38s ease; }

    @keyframes shake {
      0%,100% { transform: translateX(0); }
      20%     { transform: translateX(-6px); }
      40%     { transform: translateX(6px); }
      60%     { transform: translateX(-4px); }
      80%     { transform: translateX(4px); }
    }
  </style>
</head>
<body>

<div class="card" id="card">

  <div class="logo">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="9" y1="13" x2="15" y2="13"/>
        <line x1="9" y1="17" x2="13" y2="17"/>
      </svg>
    </div>
    <span class="logo-nome">Pro<span>pix</span></span>
  </div>

  <h1>Criar conta grátis</h1>
  <p class="sub">Comece a enviar propostas profissionais</p>

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

  <form action="../actions/acao.php" method="POST" id="form">
    <input type="hidden" name="acao" value="cadastro">

    <div class="campo">
      <label for="nome">Nome completo</label>
      <input
        type="text"
        id="nome"
        name="nome"
        placeholder="Seu nome"
        value="<?= $nome ?>"
        autocomplete="name"
        required
      >
    </div>

    <div class="campo">
      <label for="email">E-mail</label>
      <input
        type="email"
        id="email"
        name="email"
        placeholder="seu@email.com"
        value="<?= $email ?>"
        autocomplete="email"
        required
      >
    </div>

    <div class="campo">
      <label for="senha">Senha</label>
      <div class="campo-senha">
        <input
          type="password"
          id="senha"
          name="senha"
          placeholder="Mínimo 8 caracteres"
          autocomplete="new-password"
          required
          minlength="8"
        >
        <button type="button" class="toggle-senha" id="toggleSenha" aria-label="Mostrar senha">
          <svg id="icone-olho" viewBox="0 0 24 24">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </button>
      </div>
      <div class="forca-wrap" id="forcaWrap">
        <div class="forca-barras">
          <div class="barra" id="b1"></div>
          <div class="barra" id="b2"></div>
          <div class="barra" id="b3"></div>
          <div class="barra" id="b4"></div>
        </div>
        <span class="forca-label" id="forcaLabel"></span>
      </div>
    </div>

    <div class="campo">
      <label for="confirmar">Confirmar senha</label>
      <div class="campo-senha">
        <input
          type="password"
          id="confirmar"
          name="confirmar"
          placeholder="Repita a senha"
          autocomplete="new-password"
          required
        >
        <button type="button" class="toggle-senha" id="toggleConfirmar" aria-label="Mostrar senha">
          <svg id="icone-olho-2" viewBox="0 0 24 24">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </button>
      </div>
    </div>

    <button type="submit" class="btn" id="btnCadastrar">Criar conta</button>
  </form>

  <p class="rodape">Já tem conta? <a href="index.php">Entrar</a></p>

</div>

<script>
  // Toggle senha principal
  const SVG_ABERTO  = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  const SVG_FECHADO = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;

  function criarToggle(btnId, inputId, iconeId) {
    const btn   = document.getElementById(btnId);
    const input = document.getElementById(inputId);
    const icone = document.getElementById(iconeId);
    btn.addEventListener('click', () => {
      const visivel   = input.type === 'text';
      input.type      = visivel ? 'password' : 'text';
      icone.innerHTML = visivel ? SVG_ABERTO : SVG_FECHADO;
    });
  }

  criarToggle('toggleSenha',    'senha',     'icone-olho');
  criarToggle('toggleConfirmar','confirmar', 'icone-olho-2');

  // Força da senha
  const inputSenha = document.getElementById('senha');
  const forcaWrap  = document.getElementById('forcaWrap');
  const forcaLabel = document.getElementById('forcaLabel');
  const barras     = [document.getElementById('b1'), document.getElementById('b2'), document.getElementById('b3'), document.getElementById('b4')];

  const cores  = ['#EF4444', '#F59E0B', '#3B82F6', '#10B981'];
  const labels = ['Muito fraca', 'Fraca', 'Boa', 'Forte'];

  function calcularForca(senha) {
    let score = 0;
    if (senha.length >= 8)  score++;
    if (/[A-Z]/.test(senha)) score++;
    if (/[0-9]/.test(senha)) score++;
    if (/[^A-Za-z0-9]/.test(senha)) score++;
    return score;
  }

  inputSenha.addEventListener('input', () => {
    const val = inputSenha.value;
    if (!val) { forcaWrap.classList.remove('visivel'); return; }

    forcaWrap.classList.add('visivel');
    const forca = calcularForca(val);

    barras.forEach((b, i) => {
      b.style.background = i < forca ? cores[forca - 1] : 'var(--borda)';
    });

    forcaLabel.textContent = labels[forca - 1] || '';
    forcaLabel.style.color = cores[forca - 1] || 'var(--sub)';
  });

  // Ripple
  document.getElementById('btnCadastrar').addEventListener('click', function(e) {
    const r    = document.createElement('span');
    const rect = this.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    r.className     = 'ripple';
    r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - rect.left - size/2}px;top:${e.clientY - rect.top - size/2}px`;
    this.appendChild(r);
    setTimeout(() => r.remove(), 500);
  });

  // Shake se erro
  <?php if ($erro): ?>
  document.getElementById('card').classList.add('shake');
  <?php endif; ?>
</script>

</body>
</html>