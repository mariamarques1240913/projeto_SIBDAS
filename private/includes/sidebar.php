<?php
try {
    $dsnSidebar = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";
    $ligSidebar = new PDO($dsnSidebar, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligSidebar->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $naoLidasSidebar = (int)$ligSidebar->query("SELECT COUNT(*) FROM MensagemContacto WHERE lida = 0")->fetchColumn();
    $ligSidebar = null;
} catch (PDOException $e) { $naoLidasSidebar = 0; }
$_perfilSidebar = perfil_atual();
?>
<nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar p-3 border-end" style="min-height: calc(100vh - 56px);">
    <div class="position-sticky pt-3">
        <h5 class="text-secondary small text-uppercase fw-bold mb-3">Menu</h5>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link py-2 px-3 text-muted" href="<?= BASE_URL ?>/private/dashboard.php">
                    <i class="fa-solid fa-chart-pie me-2" style="color: #7097a8;"></i>
                    <span>Painel de Controlo</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link py-2 px-3 text-muted" href="<?= BASE_URL ?>/private/equipamentos.php">
                    <i class="fa-solid fa-microscope me-2" style="color: #7097a8;"></i>
                    <span>Equipamentos Médicos</span>
                </a>
            </li>
            <?php if ($_perfilSidebar !== 'profissional de saude'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link py-2 px-3 text-muted" href="<?= BASE_URL ?>/private/localizacoes.php">
                    <i class="fa-solid fa-map-location-dot me-2" style="color: #7097a8;"></i>
                    <span>Localizações Hospitalares</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link py-2 px-3 text-muted" href="<?= BASE_URL ?>/private/fornecedores.php">
                    <i class="fa-solid fa-truck-field me-2" style="color: #7097a8;"></i>
                    <span>Fornecedores</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mb-2">
                <a class="nav-link py-2 px-3 text-muted" href="<?= BASE_URL ?>/private/documentacao.php">
                    <i class="fa-solid fa-file-medical me-2" style="color: #7097a8;"></i>
                    <span>Documentação Técnica</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link py-2 px-3 text-muted" href="<?= BASE_URL ?>/private/garantias.php">
                    <i class="fa-solid fa-file-signature me-2" style="color: #7097a8;"></i>
                    <span>Garantias e Contratos</span>
                </a>
            </li>
            <?php if ($_perfilSidebar === 'administrador'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link py-2 px-3 text-muted" href="<?= BASE_URL ?>/private/gestao_portal.php">
                    <i class="fa-solid fa-sliders me-2" style="color: #7097a8;"></i>
                    <span>Gestão do Portal Público</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mb-2">
                <a class="nav-link py-2 px-3 text-muted d-flex align-items-center justify-content-between" href="<?= BASE_URL ?>/private/mensagens.php">
                    <span>
                        <i class="fa-solid fa-envelope me-2" style="color: #7097a8;"></i>
                        Mensagens de Contacto
                    </span>
                    <?php if ($naoLidasSidebar > 0) : ?>
                    <span class="badge rounded-pill text-white" style="background-color: #1d5370; font-size: 0.7rem;"><?= $naoLidasSidebar ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>
</nav>
