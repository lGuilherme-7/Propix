<?php
session_start();
require_once '../config/config.php';

// ──────────────────────────────────────────────
// SEGURANÇA: apenas POST ou GET com acao=logout
// ──────────────────────────────────────────────
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

if (empty($acao)) {
    redirecionar('../public/index.php');
}

// ──────────────────────────────────────────────
// ROTEADOR
// ──────────────────────────────────────────────
switch ($acao) {
    case 'login':             acao_login();             break;
    case 'cadastro':          acao_cadastro();          break;
    case 'logout':            acao_logout();            break;
    case 'criar_orcamento':   acao_criar_orcamento();   break;
    case 'aprovar':           acao_responder('aprovado'); break;
    case 'recusar':           acao_responder('recusado'); break;
    case 'excluir_orcamento': acao_excluir_orcamento(); break;
    default:                  redirecionar('../public/index.php');
}

// ══════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════

/**
 * Redireciona com parâmetros opcionais na URL
 */
function redirecionar(string $url, array $params = []): void
{
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Garante que o usuário está logado — senão redireciona
 */
function exigir_login(): void
{
    if (empty($_SESSION['usuario_id'])) {
        redirecionar('../public/index.php', ['erro' => 'Faça login para continuar.']);
    }
}

/**
 * Sanitiza string simples
 */
function limpar(string $valor): string
{
    return trim(htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'));
}

/**
 * Gera hash único de 32 caracteres para o orçamento
 */
function gerar_hash(): string
{
    return bin2hex(random_bytes(16));
}

// ══════════════════════════════════════════════
// AÇÃO: LOGIN
// ══════════════════════════════════════════════
function acao_login(): void
{
    // Já logado
    if (!empty($_SESSION['usuario_id'])) {
        redirecionar('../app/dashboard.php');
    }

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Validação básica
    if (empty($email) || empty($senha)) {
        redirecionar('../public/index.php', ['erro' => 'Preencha e-mail e senha.']);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirecionar('../public/index.php', ['erro' => 'E-mail inválido.']);
    }

    $pdo = conectar();

    $stmt = $pdo->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($senha, $usuario['senha'])) {
        redirecionar('../public/index.php', [
            'erro'  => 'E-mail ou senha incorretos.',
            'email' => $email,
        ]);
    }

    // Sessão segura
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nome']       = $usuario['nome'];

    redirecionar('../app/dashboard.php');
}

// ══════════════════════════════════════════════
// AÇÃO: CADASTRO
// ══════════════════════════════════════════════
function acao_cadastro(): void
{
    if (!empty($_SESSION['usuario_id'])) {
        redirecionar('../app/dashboard.php');
    }

    $nome      = trim($_POST['nome']      ?? '');
    $email     = trim($_POST['email']     ?? '');
    $senha     = $_POST['senha']          ?? '';
    $confirmar = $_POST['confirmar']      ?? '';

    $voltar = '../public/cadastro.php';
    $repop  = ['nome' => $nome, 'email' => $email];

    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar)) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'Preencha todos os campos.']));
    }

    if (mb_strlen($nome) < 2) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'Nome muito curto.']));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'E-mail inválido.']));
    }

    if (mb_strlen($senha) < 8) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'A senha deve ter no mínimo 8 caracteres.']));
    }

    if ($senha !== $confirmar) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'As senhas não coincidem.']));
    }

    $pdo = conectar();

    // E-mail já existe?
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'Este e-mail já está cadastrado.']));
    }

    // Salva usuário
    $hash_senha = password_hash($senha, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
    $stmt->execute([$nome, $email, $hash_senha]);

    redirecionar('../public/index.php', [
        'msg'   => 'Conta criada com sucesso! Faça login.',
        'email' => $email,
    ]);
}

// ══════════════════════════════════════════════
// AÇÃO: LOGOUT
// ══════════════════════════════════════════════
function acao_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $p['path'], $p['domain'],
            $p['secure'], $p['httponly']
        );
    }

    session_destroy();
    redirecionar('../public/index.php', ['msg' => 'Você saiu da conta.']);
}

// ══════════════════════════════════════════════
// AÇÃO: CRIAR ORÇAMENTO
// ══════════════════════════════════════════════
function acao_criar_orcamento(): void
{
    exigir_login();

    $campos = ['cliente', 'email', 'servico', 'valor', 'descricao', 'prazo'];
    $dados  = [];

    foreach ($campos as $campo) {
        $dados[$campo] = trim($_POST[$campo] ?? '');
    }

    $dados['telefone'] = trim($_POST['telefone'] ?? '');

    $voltar = '../app/criar.php';
    $repop  = $dados;

    // Validações obrigatórias
    foreach ($campos as $campo) {
        if (empty($dados[$campo])) {
            redirecionar($voltar, array_merge($repop, ['erro' => 'Preencha todos os campos obrigatórios.']));
        }
    }

    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'E-mail do cliente inválido.']));
    }

    if (!is_numeric($dados['valor']) || (float)$dados['valor'] <= 0) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'Informe um valor válido maior que zero.']));
    }

    if (mb_strlen($dados['descricao']) > 1000) {
        redirecionar($voltar, array_merge($repop, ['erro' => 'Descrição muito longa (máximo 1000 caracteres).']));
    }

    $pdo  = conectar();
    $hash = gerar_hash();

    $stmt = $pdo->prepare(
        'INSERT INTO orcamentos
            (hash, cliente, email, telefone, servico, valor, descricao, prazo, status)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $hash,
        limpar($dados['cliente']),
        $dados['email'],
        limpar($dados['telefone']),
        limpar($dados['servico']),
        (float)$dados['valor'],
        limpar($dados['descricao']),
        limpar($dados['prazo']),
        'pendente',
    ]);

    // Redireciona para listagem com mensagem de sucesso e link gerado
    $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST']
          . dirname($_SERVER['SCRIPT_NAME'], 2)
          . '/public/visualizar.php?token=' . $hash;

    redirecionar('../app/orcamentos.php', [
        'msg'  => 'Orçamento criado! Link: ' . $link,
    ]);
}

// ══════════════════════════════════════════════
// AÇÃO: APROVAR ou RECUSAR (página pública)
// ══════════════════════════════════════════════
function acao_responder(string $novo_status): void
{
    $hash = trim($_POST['hash'] ?? '');

    if (empty($hash)) {
        redirecionar('../public/index.php');
    }

    $pdo = conectar();

    // Verifica se existe e está pendente
    $stmt = $pdo->prepare('SELECT id, status FROM orcamentos WHERE hash = ? LIMIT 1');
    $stmt->execute([$hash]);
    $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orcamento) {
        redirecionar('../public/visualizar.php', [
            'token' => $hash,
            'erro'  => 'Orçamento não encontrado.',
        ]);
    }

    if ($orcamento['status'] !== 'pendente') {
        redirecionar('../public/visualizar.php', [
            'token' => $hash,
            'erro'  => 'Esta proposta já foi respondida.',
        ]);
    }

    // Atualiza status
    $stmt = $pdo->prepare('UPDATE orcamentos SET status = ? WHERE hash = ?');
    $stmt->execute([$novo_status, $hash]);

    $mensagem = $novo_status === 'aprovado'
        ? 'Proposta aprovada! Obrigado.'
        : 'Proposta recusada. Recebemos sua resposta.';

    redirecionar('../public/visualizar.php', [
        'token' => $hash,
        'msg'   => $mensagem,
    ]);
}

// ══════════════════════════════════════════════
// AÇÃO: EXCLUIR ORÇAMENTO
// ══════════════════════════════════════════════
function acao_excluir_orcamento(): void
{
    exigir_login();

    $hash = trim($_POST['hash'] ?? '');

    if (empty($hash)) {
        redirecionar('../app/orcamentos.php', ['erro' => 'Orçamento inválido.']);
    }

    $pdo = conectar();

    // Verifica se existe
    $stmt = $pdo->prepare('SELECT id FROM orcamentos WHERE hash = ? LIMIT 1');
    $stmt->execute([$hash]);

    if (!$stmt->fetch()) {
        redirecionar('../app/orcamentos.php', ['erro' => 'Orçamento não encontrado.']);
    }

    // Exclui
    $stmt = $pdo->prepare('DELETE FROM orcamentos WHERE hash = ?');
    $stmt->execute([$hash]);

    redirecionar('../app/orcamentos.php', ['msg' => 'Orçamento excluído com sucesso.']);
}