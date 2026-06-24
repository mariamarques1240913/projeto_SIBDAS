<?php
require_once __DIR__ . '/../../config/config.php';

function start_session()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function check_session()
{
    return isset($_SESSION['utilizador']);
}

// Chamada no topo de cada página privada — redireciona para login se não houver sessão ativa
function redirect_if_not_logged($redirect_to = '/public/login.php')
{
    start_session();
    if (!check_session()) {
        header("Location: " . BASE_URL . $redirect_to);
        exit;
    }
}

function logout_and_redirect($redirect_to = '/public/login.php')
{
    start_session();
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . $redirect_to);
    exit;
}

function perfil_atual()
{
    start_session();
    return $_SESSION['perfil'] ?? '';
}

// Redireciona para o dashboard se o perfil do utilizador não estiver na lista de permitidos
function verificar_perfil(array $permitidos)
{
    if (!in_array(perfil_atual(), $permitidos)) {
        $_SESSION['server_error'] = 'Não tem permissão para aceder a esta funcionalidade.';
        header('Location: ' . BASE_URL . '/private/dashboard.php');
        exit;
    }
}

// Converte o ID para hex após encriptar — o resultado é seguro para usar em URLs
function aes_encrypt($value)
{
    $encrypted = openssl_encrypt($value, OPENSSL_METHOD, OPENSSL_KEY, OPENSSL_RAW_DATA, OPENSSL_IV);
    return bin2hex($encrypted);
}

function aes_decrypt($value)
{
    if (!is_string($value) || strlen($value) === 0) return false;
    $raw = @hex2bin($value);
    if ($raw === false) return false;
    return openssl_decrypt($raw, OPENSSL_METHOD, OPENSSL_KEY, OPENSSL_RAW_DATA, OPENSSL_IV);
}
