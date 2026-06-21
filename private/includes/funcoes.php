<?php
function start_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function check_session() {
    start_session();
    return isset($_SESSION['utilizador']);
}

function redirect_if_not_logged() {
    start_session();
    if (!isset($_SESSION['utilizador'])) {
        header('Location: /projeto_SIBDAS/public/login.php');
        exit;
    }
}

function logout_and_redirect() {
    start_session();
    session_unset();
    session_destroy();
    header('Location: /projeto_SIBDAS/public/login.php');
    exit;
}
