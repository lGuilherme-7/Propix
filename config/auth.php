<?php

// ──────────────────────────────────────────────
// PROTEÇÃO DE ROTA
// Inclua no topo de qualquer página que exija login
// ──────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: /public/index.php?erro=' . urlencode('Faça login para continuar.'));
    exit;
}