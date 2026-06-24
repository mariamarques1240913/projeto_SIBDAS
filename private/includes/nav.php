<?php
require_once __DIR__ . '/funcoes.php';
redirect_if_not_logged();
$nome   = $_SESSION['utilizador'];
$perfil = $_SESSION['perfil'] ?? '';
$perfilLabel = ['administrador' => 'Administrador', 'tecnico' => 'Técnico', 'profissional de saude' => 'Profissional de Saúde'][$perfil] ?? $perfil;
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
                <?php if ($perfil): ?>
                    <span class="badge ms-2" style="background-color:#1d5370; font-size:0.7em;"><?= htmlspecialchars($perfilLabel) ?></span>
                <?php endif; ?>
            </span>
            <a href="<?= BASE_URL ?>/private/alterar_password.php" class="btn btn-sm btn-outline-light me-2 px-3" title="Alterar palavra-passe">
                <i class="fa-solid fa-lock"></i>
            </a>
            <a href="<?= BASE_URL ?>/public/logout.php" class="btn btn-sm btn-light fw-bold px-3" style="color: #1d5370;">
                Sair <i class="fa-solid fa-right-from-bracket ms-1"></i>
            </a>
        </div>
    </div>
</nav>
