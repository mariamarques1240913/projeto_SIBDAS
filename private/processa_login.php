<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/funcoes.php';
start_session();

// Bloqueia acesso direto via GET — este ficheiro só deve ser chamado pelo formulário de login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/public/login.php');
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
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

try {
    $dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";
    $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("SELECT * FROM Utilizador WHERE email = :email");
    $stmt->bindParam(':email', $username);
    $stmt->execute();
    $utilizador = $stmt->fetch(PDO::FETCH_OBJ);
    $ligacao = null;

    // A mensagem de erro é propositadamente genérica para não revelar se o email existe
    if (!$utilizador || !password_verify($password, $utilizador->password)) {
        $_SESSION['server_error'] = 'Email ou password incorretos.';
        header('Location: ' . BASE_URL . '/public/login.php');
        exit;
    }

    $_SESSION['utilizador']     = $utilizador->nome;
    $_SESSION['codUtilizador']  = $utilizador->codUtilizador;
    $_SESSION['perfil']         = $utilizador->perfil;
    header('Location: ' . BASE_URL . '/private/dashboard.php');
    exit;
} catch (PDOException $e) {
    $ligacao = null;
    $_SESSION['server_error'] = 'Erro ao ligar à base de dados.';
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}
