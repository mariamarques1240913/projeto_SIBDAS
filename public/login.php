<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../private/includes/funcoes.php';
start_session();

$validation_errors = [];
if (isset($_SESSION['validation_errors'])) {
    $validation_errors = $_SESSION['validation_errors'];
    unset($_SESSION['validation_errors']);
}

$server_error = '';
if (isset($_SESSION['server_error'])) {
    $server_error = $_SESSION['server_error'];
    unset($_SESSION['server_error']);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/coracao.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-sm bg-white border-0" style="max-width: 400px; width: 100%;">

            <div class="text-center mb-4">
                <h2 class="fw-bold" style="color: #1d5370;"><?php echo strtoupper(APP_NAME); ?></h2>
                <p class="text-muted small">Sistema de Gestão de Cuidados Hospitalares</p>
            </div>

            <form method="post" action="<?= BASE_URL ?>/private/processa_login.php" name="formulario">
                <div class="mb-3 text-start">
                    <label for="text_username" class="form-label fw-semibold text-secondary">Email</label>
                    <input type="email" class="form-control" id="text_username" name="text_username" placeholder="Insira o seu email" required>
                </div>

                <div class="mb-4 text-start">
                    <label for="text_password" class="form-label fw-semibold text-secondary">Password</label>
                    <input type="password" class="form-control" id="text_password" name="text_password" placeholder="Insira a sua password" required>
                </div>

                <?php if (!empty($validation_errors)): ?>
                    <?php foreach ($validation_errors as $error): ?>
                        <div class="alert alert-danger py-2 small text-center mb-3" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($server_error)): ?>
                    <div class="alert alert-danger py-2 small text-center mb-3" role="alert">
                        <?php echo htmlspecialchars($server_error); ?>
                    </div>
                <?php endif; ?>

                <div class="d-grid">
                    <button type="submit" class="btn text-white fw-bold" style="background-color: #7097a8;">
                        Entrar <i class="fa-solid fa-right-from-bracket ms-1"></i>
                    </button>
                </div>
            </form>

            <div class="mt-3 pt-3 border-top text-center">
                <p class="text-muted small mb-2">Teste rápido de acesso:</p>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                        onclick="document.getElementById('text_username').value='admin@hospitally.pt';document.getElementById('text_password').value='admin123';">
                    <i class="fa-solid fa-user-shield me-1"></i> Preencher como Administrador
                </button>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/1240913.js"></script>
</body>
</html>
