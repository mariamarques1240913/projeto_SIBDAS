<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";
$erro   = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordAtual    = $_POST['passwordAtual']    ?? '';
    $passwordNova     = $_POST['passwordNova']     ?? '';
    $passwordConfirma = $_POST['passwordConfirma'] ?? '';

    if (empty($passwordAtual) || empty($passwordNova) || empty($passwordConfirma)) {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif (strlen($passwordNova) < 6) {
        $erro = 'A nova palavra-passe deve ter pelo menos 6 caracteres.';
    } elseif ($passwordNova !== $passwordConfirma) {
        $erro = 'A nova palavra-passe e a confirmação não coincidem.';
    } else {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $ligacao->prepare("SELECT password FROM Utilizador WHERE codUtilizador = ?");
            $stmt->execute([$_SESSION['codUtilizador']]);
            $utilizador = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$utilizador || !password_verify($passwordAtual, $utilizador->password)) {
                $erro = 'A palavra-passe atual está incorreta.';
            } else {
                $novaHash = password_hash($passwordNova, PASSWORD_BCRYPT);
                $stmtUpd  = $ligacao->prepare("UPDATE Utilizador SET password = ? WHERE codUtilizador = ?");
                $stmtUpd->execute([$novaHash, $_SESSION['codUtilizador']]);
                $sucesso = 'Palavra-passe alterada com sucesso.';
            }
            $ligacao = null;
        } catch (PDOException $e) {
            $erro = 'Erro ao ligar à base de dados.';
        }
    }
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold mb-1" style="color: #1d5370;">Alterar Palavra-passe</h1>
            <p class="text-muted mb-4">Altere a palavra-passe da sua conta.</p>

            <div class="row justify-content-start">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded p-4 bg-white">

                        <?php if ($sucesso): ?>
                            <div class="alert alert-success">
                                <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($sucesso) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($erro): ?>
                            <div class="alert alert-danger">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($erro) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="passwordAtual" class="form-label fw-bold text-secondary small">Palavra-passe atual</label>
                                <input type="password" class="form-control" id="passwordAtual" name="passwordAtual" required autocomplete="current-password">
                            </div>
                            <div class="mb-3">
                                <label for="passwordNova" class="form-label fw-bold text-secondary small">Nova palavra-passe</label>
                                <input type="password" class="form-control" id="passwordNova" name="passwordNova" required autocomplete="new-password" minlength="6">
                            </div>
                            <div class="mb-4">
                                <label for="passwordConfirma" class="form-label fw-bold text-secondary small">Confirmar nova palavra-passe</label>
                                <input type="password" class="form-control" id="passwordConfirma" name="passwordConfirma" required autocomplete="new-password" minlength="6">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn fw-bold text-white px-4" style="background-color: #1d5370;">
                                    <i class="fa-solid fa-lock me-1"></i> Alterar
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <footer class="pt-4 mt-5 text-muted border-top">
                &copy; 2026 Hospitally &middot; Sistema de Gestão Hospitalar Privado
            </footer>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
