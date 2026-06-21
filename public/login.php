<?php require_once '../config/config.php'; ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <link rel="icon" type="image/png" href="/projeto_SIBDAS/assets/img/coracao.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-sm bg-white border-0" style="max-width: 400px; width: 100%;">

            <div class="text-center mb-4">
                <h2 class="fw-bold" style="color: #1d5370;"><?php echo strtoupper(APP_NAME); ?></h2>
                <p class="text-muted small">Sistema de Gestão de Cuidados Hospitalares</p>
            </div>

            <form id="formLogin" onsubmit="validarLogin(event)">
                <div class="mb-3 text-start">
                    <label for="utilizador" class="form-label fw-semibold text-secondary">Utilizador</label>
                    <input type="text" class="form-control" id="utilizador" placeholder="Insira o seu utilizador" required>
                </div>

                <div class="mb-4 text-start">
                    <label for="password" class="form-label fw-semibold text-secondary">Password</label>
                    <input type="password" class="form-control" id="password" placeholder="Insira a sua password" required>
                </div>

                <div id="mensagemErro" class="alert alert-danger py-2 small text-center d-none mb-3" role="alert">
                    Utilizador ou password incorretos.
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn text-white fw-bold" style="background-color: #7097a8;">
                        Entrar <i class="fa-solid fa-right-from-bracket ms-1"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/projeto_SIBDAS/assets/js/1240913.js"></script>
</body>
</html>
