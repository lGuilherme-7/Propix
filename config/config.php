<?php

// ──────────────────────────────────────────────
// CONFIGURAÇÕES DO BANCO DE DADOS
// ──────────────────────────────────────────────
define('DB_HOST', 'sql113.infinityfree.com');
define('DB_NAME', 'if0_41632013_propix');
define('DB_USER', 'if0_41632013');
define('DB_PASS', 'RgZ4zvfAeHBTCq');
define('DB_CHARSET', 'utf8mb4');

// ──────────────────────────────────────────────
// CONFIGURAÇÕES DA APLICAÇÃO
// ──────────────────────────────────────────────
define('APP_NOME', 'Propix');
define('APP_URL', 'https://propix.xo.je'); // sem barra no final, sem /propix

// ──────────────────────────────────────────────
// AMBIENTE (development | production)
// ──────────────────────────────────────────────
define('APP_ENV', 'development');

// Exibe erros apenas em desenvolvimento
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ──────────────────────────────────────────────
// CONEXÃO PDO (singleton simples)
// ──────────────────────────────────────────────
function conectar(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname='    . DB_NAME
         . ';charset='   . DB_CHARSET;

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
    } catch (PDOException $e) {
        if (APP_ENV === 'development') {
            die('<pre>Erro de conexão: ' . $e->getMessage() . '</pre>');
        } else {
            die('Erro interno. Tente novamente mais tarde.');
        }
    }

    return $pdo;
}