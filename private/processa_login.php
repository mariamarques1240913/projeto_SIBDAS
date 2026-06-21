<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/funcoes.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /projeto_SIBDAS/public/login.php');
    exit;
}

$validation_errors = [];

$username = trim($_POST['text_username'] ?? '');
$password = trim($_POST['text_password'] ?? '');

if (empty($username)) {
    $validation_errors[] = 'O campo email é obrigatório.';
}
if (empty($password)) {
    $validation_errors[] = 'O campo password é obrigatório.';
}

if (!empty($validation_errors)) {
    $_SESSION['validation_errors'] = $validation_errors;
    header('Location: /projeto_SIBDAS/public/login.php');
    exit;
}

// Simulação de verificação de credenciais (substituir por consulta BD futuramente)
$result['status'] = 0;
if ($username === 'admin@hospitally.pt' && $password === 'admin123') {
    $result['status'] = 1;
    $result['utilizador'] = 'Administrador';
}

if ($result['status'] === 1) {
    $_SESSION['utilizador'] = $result['utilizador'];
    header('Location: /projeto_SIBDAS/private/dashboard.php');
    exit;
} else {
    $_SESSION['server_error'] = 'Email ou password incorretos.';
    header('Location: /projeto_SIBDAS/public/login.php');
    exit;
}
