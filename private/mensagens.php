<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$erro_sistema = '';
$abrirDetalheModal = false;
$abrirConfirmarModal = false;
$mensagemVer = null;
$mensagemRemover = null;

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";

// GET: ver mensagem (marca como lida automaticamente)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ver'])) {
    $id = (int)$_GET['ver'];
    if ($id > 0) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("SELECT * FROM MensagemContacto WHERE codMensagem = ?");
            $stmt->execute([$id]);
            $mensagemVer = $stmt->fetch(PDO::FETCH_OBJ);
            if ($mensagemVer && !$mensagemVer->lida) {
                $ligacao->prepare("UPDATE MensagemContacto SET lida = 1 WHERE codMensagem = ?")->execute([$id]);
                $mensagemVer->lida = 1;
            }
            $ligacao = null;
            if (!$mensagemVer) { header('Location: mensagens.php'); exit; }
            $abrirDetalheModal = true;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao carregar a mensagem.";
            $ligacao = null;
        }
    }
}

// GET: confirmação de remoção
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remover'])) {
    $id = (int)$_GET['remover'];
    if ($id > 0) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("SELECT * FROM MensagemContacto WHERE codMensagem = ?");
            $stmt->execute([$id]);
            $mensagemRemover = $stmt->fetch(PDO::FETCH_OBJ);
            $ligacao = null;
            if (!$mensagemRemover) { header('Location: mensagens.php'); exit; }
            $abrirConfirmarModal = true;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao carregar a mensagem.";
            $ligacao = null;
        }
    }
}

// POST: remover mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'remover') {
    $id = (int)($_POST['codMensagem'] ?? 0);
    if ($id > 0) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $ligacao->prepare("DELETE FROM MensagemContacto WHERE codMensagem = ?")->execute([$id]);
            $ligacao = null;
            header('Location: mensagens.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao remover a mensagem.";
            $ligacao = null;
        }
    }
}

// Carregar todas as mensagens
try {
    $ligacao    = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mensagens  = $ligacao->query("SELECT * FROM MensagemContacto ORDER BY dataHora DESC")->fetchAll(PDO::FETCH_OBJ);
    $naoLidas   = (int)$ligacao->query("SELECT COUNT(*) FROM MensagemContacto WHERE lida = 0")->fetchColumn();
    $ligacao    = null;
    $erro       = '';
} catch (PDOException $err) {
    $mensagens = []; $naoLidas = 0;
    $erro = "Erro ao ligar à base de dados.";
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Mensagens de Contacto</h1>
            <p class="text-muted">Pedidos de contacto e esclarecimento recebidos através do portal público.</p>

            <?php if (!empty($erro_sistema)) : ?>
                <div class="alert alert-danger"><strong>Erro:</strong> <?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <?php if ($naoLidas > 0) : ?>
                <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-envelope fa-lg"></i>
                    <span>Tem <strong><?= $naoLidas ?></strong> mensagem(ns) por ler.</span>
                </div>
            <?php endif; ?>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <?php if (!empty($erro)) : ?>
                    <p class="text-center text-danger p-4"><?= $erro ?></p>
                <?php elseif (empty($mensagens)) : ?>
                    <p class="text-muted p-4 text-center"><i class="fa-solid fa-inbox me-2"></i>Ainda não foram recebidas mensagens.</p>
                <?php else : ?>
                    <table class="table table-hover align-middle mb-0" id="tabelaMensagens">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="color: #1d5370; width: 40px;"></th>
                                <th style="color: #1d5370;">Nome</th>
                                <th style="color: #1d5370;">Email</th>
                                <th style="color: #1d5370;">Pré-visualização</th>
                                <th style="color: #1d5370;">Data</th>
                                <th class="text-center pe-3" style="color: #1d5370;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mensagens as $msg) : ?>
                            <tr class="<?= !$msg->lida ? 'table-light fw-semibold' : '' ?>">
                                <td class="ps-3 text-center">
                                    <?php if (!$msg->lida) : ?>
                                        <i class="fa-solid fa-circle" style="color: #1d5370; font-size: 0.5rem;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($msg->nome) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($msg->email) ?></td>
                                <td class="text-muted small">
                                    <span class="d-inline-block text-truncate" style="max-width: 280px;">
                                        <?= htmlspecialchars($msg->mensagem) ?>
                                    </span>
                                </td>
                                <td class="small text-muted" style="white-space: nowrap;">
                                    <?= date('d/m/Y H:i', strtotime($msg->dataHora)) ?>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="mensagens.php?ver=<?= $msg->codMensagem ?>"
                                           class="btn btn-sm btn-outline-secondary" title="Ver mensagem">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="mensagens.php?remover=<?= $msg->codMensagem ?>"
                                           class="btn btn-sm btn-outline-danger" title="Remover">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <footer class="pt-4 mt-5 text-muted border-top">
                &copy; 2026 Hospitally &middot; Sistema de Gestão Hospitalar Privado
            </footer>
        </main>
    </div>
</div>

<!-- Modal: Ver Mensagem -->
<div class="modal fade" id="modalVerMensagem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white modal-header-hospital border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-envelope-open me-2"></i>Mensagem de Contacto
                </h5>
                <a href="mensagens.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body p-4">
                <?php if ($mensagemVer) : ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Nome</label>
                        <strong><?= htmlspecialchars($mensagemVer->nome) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Email</label>
                        <a href="mailto:<?= htmlspecialchars($mensagemVer->email) ?>" class="text-decoration-none">
                            <?= htmlspecialchars($mensagemVer->email) ?>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Data de Receção</label>
                        <span><?= date('d/m/Y \à\s H:i', strtotime($mensagemVer->dataHora)) ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Estado</label>
                        <span class="badge bg-success">Lida</span>
                    </div>
                    <div class="col-12">
                        <label class="small text-muted d-block">Mensagem</label>
                        <div class="bg-light rounded p-3 text-dark" style="white-space: pre-wrap;"><?= htmlspecialchars($mensagemVer->mensagem) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light">
                <a href="mensagens.php" class="btn btn-secondary px-4">Fechar</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirmação de Remoção -->
<div class="modal fade" id="modalConfirmarRemocao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Confirmar Remoção
                </h5>
                <a href="mensagens.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fa-solid fa-trash fa-3x text-danger mb-3 d-block"></i>
                <p class="mb-1">Tem a certeza que pretende remover a mensagem de</p>
                <p class="fw-bold fs-5 mb-0"><?= htmlspecialchars($mensagemRemover->nome ?? '') ?></p>
                <p class="text-muted small mb-3"><?= htmlspecialchars($mensagemRemover->email ?? '') ?></p>
                <p class="text-danger small"><i class="fa-solid fa-exclamation-triangle me-1"></i>Esta ação é permanente e não pode ser revertida.</p>
            </div>
            <div class="modal-footer bg-light">
                <a href="mensagens.php" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </a>
                <form method="POST" action="mensagens.php" class="d-inline">
                    <input type="hidden" name="acao" value="remover">
                    <input type="hidden" name="codMensagem" value="<?= $mensagemRemover->codMensagem ?? '' ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-1"></i>Confirmar Remoção
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#tabelaMensagens').DataTable({
            pageLength: 10,
            lengthChange: false,
            searching: false,
            paging: false,
            info: false,
            language: {
                emptyTable: "Sem mensagens disponíveis.",
                zeroRecords: "Nenhuma mensagem encontrada."
            }
        });
        <?php if ($abrirDetalheModal) : ?>
        new bootstrap.Modal(document.getElementById('modalVerMensagem')).show();
        <?php endif; ?>
        <?php if ($abrirConfirmarModal) : ?>
        new bootstrap.Modal(document.getElementById('modalConfirmarRemocao')).show();
        <?php endif; ?>
    });
</script>

<?php include 'includes/footer.php'; ?>
