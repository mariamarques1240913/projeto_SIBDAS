<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$erros = [];
$erro_sistema = "";
$abrirModal = false;
$abrirDetalheModal = false;
$abrirConfirmarModal = false;
$modoEditar = false;
$localizacaoEditar = null;
$idEncriptado = null;
$localizacaoVer = null;
$idEncriptadoVer = null;
$localizacaoRemover = null;
$idEncriptadoRemover = null;

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";

// GET: ver detalhes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ver'])) {
    $idEncriptadoVer = $_GET['ver'];
    $idDecriptado = aes_decrypt($idEncriptadoVer);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: localizacoes.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT * FROM Localizacao WHERE codLocalizacao = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $localizacaoVer = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$localizacaoVer) { header('Location: localizacoes.php'); exit; }
        $abrirDetalheModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar os detalhes da localização.";
        $ligacao = null;
    }
}

// GET: confirmação de remoção
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remover'])) {
    $idEncriptadoRemover = $_GET['remover'];
    $idDecriptado = aes_decrypt($idEncriptadoRemover);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: localizacoes.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT codLocalizacao, edificio, servico FROM Localizacao WHERE codLocalizacao = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $localizacaoRemover = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$localizacaoRemover) { header('Location: localizacoes.php'); exit; }
        $abrirConfirmarModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar a localização.";
        $ligacao = null;
    }
}

// POST: remover
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "remover") {
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: localizacoes.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("DELETE FROM Localizacao WHERE codLocalizacao = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $ligacao = null;
        header('Location: localizacoes.php'); exit;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao remover a localização: " . $err->getMessage();
        $ligacao = null;
    }
}

// GET: editar
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
    $idEncriptado = $_GET['editar'];
    $idDecriptado = aes_decrypt($idEncriptado);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: localizacoes.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT * FROM Localizacao WHERE codLocalizacao = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $localizacaoEditar = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$localizacaoEditar) { header('Location: localizacoes.php'); exit; }
        $modoEditar = true;
        $abrirModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar a localização.";
        $ligacao = null;
    }
}

// POST: inserir nova localização
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "novo") {
    $abrirModal = true;
    $edificio = trim($_POST["edificio"] ?? "");
    $piso     = trim($_POST["piso"]     ?? "");
    $servico  = trim($_POST["servico"]  ?? "");
    $sala     = trim($_POST["sala"]     ?? "");

    if (empty($edificio)) $erros[] = "O edifício é obrigatório.";
    if (empty($piso))     $erros[] = "O piso é obrigatório.";
    if (empty($servico))  $erros[] = "O serviço é obrigatório.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Localizacao (edificio, piso, servico, sala) VALUES (:edificio, :piso, :servico, :sala)");
            $stmt->execute([':edificio' => $edificio, ':piso' => $piso, ':servico' => $servico, ':sala' => $sala ?: null]);
            $ligacao = null;
            header('Location: localizacoes.php'); exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar a localização: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// POST: atualizar localização
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "editar") {
    $abrirModal = true;
    $modoEditar = true;
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: localizacoes.php'); exit;
    }
    $edificio = trim($_POST["edificio"] ?? "");
    $piso     = trim($_POST["piso"]     ?? "");
    $servico  = trim($_POST["servico"]  ?? "");
    $sala     = trim($_POST["sala"]     ?? "");

    if (empty($edificio)) $erros[] = "O edifício é obrigatório.";
    if (empty($piso))     $erros[] = "O piso é obrigatório.";
    if (empty($servico))  $erros[] = "O serviço é obrigatório.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("UPDATE Localizacao SET edificio=:edificio, piso=:piso, servico=:servico, sala=:sala WHERE codLocalizacao=:id");
            $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
            $stmt->execute([':edificio' => $edificio, ':piso' => $piso, ':servico' => $servico, ':sala' => $sala ?: null]);
            $ligacao = null;
            header('Location: localizacoes.php'); exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao atualizar a localização: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// Carregar listagem
try {
    $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("SELECT * FROM Localizacao ORDER BY edificio, piso, servico")->fetchAll(PDO::FETCH_OBJ);
    $erro = '';
} catch (PDOException $err) {
    $erro = "Aconteceu um erro na ligação à base de dados.";
    $resultados = [];
}
$ligacao = null;

$v = function(string $campo) use ($localizacaoEditar): string {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return (string)($_POST[$campo] ?? '');
    if ($localizacaoEditar !== null) return (string)($localizacaoEditar->$campo ?? '');
    return '';
};

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Localizações Hospitalares</h1>
            <p class="text-muted">Gestão de Edifícios, Pisos e Unidades Clínicas.</p>

            <?php if (!empty($erro_sistema)) : ?>
                <div class="alert alert-danger"><strong>Erro:</strong> <?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-end mb-3">
                <button class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1d5370;"
                        data-bs-toggle="modal" data-bs-target="#modalLocalizacao">
                    <i class="fa-solid fa-plus me-2"></i>Nova Localização
                </button>
            </div>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <?php if (!empty($erro)) : ?>
                    <p class="text-center text-danger p-4"><?= $erro ?></p>
                <?php elseif (count($resultados) == 0) : ?>
                    <p class="text-muted p-4">Não existem localizações registadas.</p>
                <?php else : ?>
                    <table class="table table-hover align-middle mb-0" id="tabelaLocalizacoes">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center ps-3" style="color: #1d5370;">ID</th>
                                <th class="text-center" style="color: #1d5370;">Edifício</th>
                                <th class="text-center" style="color: #1d5370;">Piso</th>
                                <th class="text-center" style="color: #1d5370;">Serviço / Departamento</th>
                                <th class="text-center" style="color: #1d5370;">Sala / Gabinete</th>
                                <th class="text-center pe-3" style="color: #1d5370;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $loc) : ?>
                            <tr>
                                <td class="text-center ps-3 fw-bold text-secondary"><?= $loc->codLocalizacao ?></td>
                                <td class="text-center fw-bold text-dark"><?= htmlspecialchars($loc->edificio) ?></td>
                                <td class="text-center"><?= htmlspecialchars($loc->piso) ?></td>
                                <td class="text-center"><?= htmlspecialchars($loc->servico) ?></td>
                                <td class="text-center text-muted"><?= htmlspecialchars($loc->sala ?? '-') ?></td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <a href="localizacoes.php?ver=<?= aes_encrypt($loc->codLocalizacao) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Consultar">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="localizacoes.php?editar=<?= aes_encrypt($loc->codLocalizacao) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="localizacoes.php?remover=<?= aes_encrypt($loc->codLocalizacao) ?>"
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

<!-- Modal Localização (novo e editar) -->
<div class="modal fade" id="modalLocalizacao" tabindex="-1" aria-labelledby="modalLocalizacaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white modal-header-hospital">
                <h5 class="modal-title fw-bold" id="modalLocalizacaoLabel">
                    <i class="fa-solid fa-map-location-dot me-2"></i>
                    <?= $modoEditar ? 'Editar Localização' : 'Nova Localização' ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?php if (!empty($erros)) : ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($erros as $e) : ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form id="formLocalizacao" method="post" action="#">
                    <input type="hidden" name="acao" value="<?= $modoEditar ? 'editar' : 'novo' ?>">
                    <?php if ($modoEditar && $idEncriptado) : ?>
                        <input type="hidden" name="idEncriptado" value="<?= htmlspecialchars($idEncriptado) ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Edifício / Ala</label>
                        <input type="text" class="form-control" name="edificio"
                               placeholder="Ex: Edifício Central (A)"
                               value="<?= htmlspecialchars($v('edificio')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Piso</label>
                        <input type="text" class="form-control" name="piso"
                               placeholder="Ex: Piso 2"
                               value="<?= htmlspecialchars($v('piso')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Serviço / Departamento</label>
                        <input type="text" class="form-control" name="servico"
                               placeholder="Ex: Bloco Operatório"
                               value="<?= htmlspecialchars($v('servico')) ?>" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-secondary small">Sala / Gabinete <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" class="form-control" name="sala"
                               placeholder="Ex: Sala de Cirurgia 2"
                               value="<?= htmlspecialchars($v('sala')) ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formLocalizacao" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes Localização -->
<div class="modal fade" id="modalDetalhesLocalizacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white modal-header-hospital border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-map-pin me-2"></i>Detalhes da Localização
                </h5>
                <span class="badge bg-light text-dark ms-auto me-3 px-3 py-2">
                    #<?= $localizacaoVer ? str_pad($localizacaoVer->codLocalizacao, 3, '0', STR_PAD_LEFT) : '---' ?>
                </span>
                <a href="localizacoes.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body p-4">
                <?php if ($localizacaoVer) : ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="small text-muted d-block">Edifício / Ala</label>
                        <strong class="fs-5 text-dark"><?= htmlspecialchars($localizacaoVer->edificio) ?></strong>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block">Piso</label>
                        <span><?= htmlspecialchars($localizacaoVer->piso) ?></span>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block">Serviço / Departamento</label>
                        <span><?= htmlspecialchars($localizacaoVer->servico) ?></span>
                    </div>
                    <div class="col-12">
                        <label class="small text-muted d-block">Sala / Gabinete</label>
                        <span><?= htmlspecialchars($localizacaoVer->sala ?? '-') ?></span>
                    </div>
                </div>
                <?php else : ?>
                <p class="text-muted">Sem dados disponíveis.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light">
                <a href="localizacoes.php" class="btn btn-secondary px-4">Fechar</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação de Remoção -->
<div class="modal fade" id="modalConfirmarRemocao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Confirmar Remoção
                </h5>
                <a href="localizacoes.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fa-solid fa-trash fa-3x text-danger mb-3 d-block"></i>
                <p class="mb-1">Tem a certeza que pretende remover a localização?</p>
                <p class="fw-bold fs-5 mb-1"><?= htmlspecialchars($localizacaoRemover->edificio ?? '') ?></p>
                <p class="text-muted small mb-3"><?= htmlspecialchars($localizacaoRemover->servico ?? '') ?></p>
                <p class="text-danger small mb-0"><i class="fa-solid fa-exclamation-triangle me-1"></i>Esta ação é permanente e não pode ser revertida.</p>
            </div>
            <div class="modal-footer bg-light">
                <a href="localizacoes.php" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </a>
                <form method="post" action="#" class="d-inline">
                    <input type="hidden" name="acao" value="remover">
                    <input type="hidden" name="idEncriptado" value="<?= htmlspecialchars($idEncriptadoRemover ?? '') ?>">
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
    $('#tabelaLocalizacoes').DataTable({
        pageLength: 10,
        lengthChange: false,
        searching: false,
        paging: false,
        info: false,
        language: {
            emptyTable: "Sem localizações disponíveis.",
            paginate: { first: "Primeira", last: "Última", next: "Seguinte", previous: "Anterior" }
        }
    });

    <?php if ($abrirModal) : ?>
    new bootstrap.Modal(document.getElementById('modalLocalizacao')).show();
    <?php endif; ?>
    <?php if ($abrirDetalheModal) : ?>
    new bootstrap.Modal(document.getElementById('modalDetalhesLocalizacao')).show();
    <?php endif; ?>
    <?php if ($abrirConfirmarModal) : ?>
    new bootstrap.Modal(document.getElementById('modalConfirmarRemocao')).show();
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
