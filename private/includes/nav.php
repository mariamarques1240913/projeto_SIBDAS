<?php
require_once __DIR__ . '/funcoes.php';
redirect_if_not_logged();
$nome = $_SESSION['utilizador'];
?>
<nav class="navbar navbar-dark sticky-top shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/private/dashboard.php">
            <img src="<?= BASE_URL ?>/assets/img/logotipo_hospital.png" alt="Logo Hospitally" height="52" class="d-inline-block align-text-top me-2 my-n2" style="margin-top: -8px; margin-bottom: -8px;">
            <?php echo APP_NAME; ?> <span class="fs-6 fw-normal ms-2">| Área Restrita</span>
        </a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 small">
                <i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($nome); ?>
            </span>
            <a href="<?= BASE_URL ?>/public/logout.php" class="btn btn-sm btn-light fw-bold px-3" style="color: #1d5370;">
                Sair <i class="fa-solid fa-right-from-bracket ms-1"></i>
            </a>
        </div>
    </div>
</nav>
