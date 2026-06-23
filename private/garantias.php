<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$erros = [];
$erro_sistema = "";
$abrirModal = false;
$abrirDetalheModal = false;
$abrirConfirmarModal = false;
$modoEditar = false;
$garantiaEditar = null;
$idEncriptado = null;
$garantiaVer = null;
$idEncriptadoVer = null;
$garantiaRemover = null;
$idEncriptadoRemover = null;

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";

// GET: ver detalhes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ver'])) {
    $idEncriptadoVer = $_GET['ver'];
    $idDecriptado = aes_decrypt($idEncriptadoVer);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: garantias.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("
            SELECT g.*, e.designacao AS nomeEquipamento, e.marca, e.modelo
            FROM Garantia g
            LEFT JOIN Equipamento e ON g.codInventario = e.codInventario
            WHERE g.codGarantia = :id
        ");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $garantiaVer = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$garantiaVer) { header('Location: garantias.php'); exit; }
        $abrirDetalheModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar os detalhes da garantia.";
        $ligacao = null;
    }
}

// GET: confirmação de remoção
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remover'])) {
    $idEncriptadoRemover = $_GET['remover'];
    $idDecriptado = aes_decrypt($idEncriptadoRemover);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: garantias.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("
            SELECT g.codGarantia, g.dataInicio, g.dataFim, e.designacao AS nomeEquipamento
            FROM Garantia g
            LEFT JOIN Equipamento e ON g.codInventario = e.codInventario
            WHERE g.codGarantia = :id
        ");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $garantiaRemover = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$garantiaRemover) { header('Location: garantias.php'); exit; }
        $abrirConfirmarModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar a garantia.";
        $ligacao = null;
    }
}

// POST: remover
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "remover") {
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: garantias.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("DELETE FROM Garantia WHERE codGarantia = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $ligacao = null;
        header('Location: garantias.php'); exit;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao remover a garantia: " . $err->getMessage();
        $ligacao = null;
    }
}

// GET: editar
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
    $idEncriptado = $_GET['editar'];
    $idDecriptado = aes_decrypt($idEncriptado);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: garantias.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT * FROM Garantia WHERE codGarantia = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $garantiaEditar = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$garantiaEditar) { header('Location: garantias.php'); exit; }
        $modoEditar = true;
        $abrirModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar a garantia.";
        $ligacao = null;
    }
}

// POST: inserir nova garantia
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "novo") {
    $abrirModal = true;
    $codInventario       = trim($_POST["codInventario"]       ?? "");
    $dataInicio          = trim($_POST["dataInicio"]          ?? "");
    $dataFim             = trim($_POST["dataFim"]             ?? "");
    $temContrato         = isset($_POST["temContrato"])        ? 1 : 0;
    $tipoContrato        = trim($_POST["tipoContrato"]        ?? "");
    $entidadeResponsavel = trim($_POST["entidadeResponsavel"] ?? "");
    $periodicidade       = trim($_POST["periodicidade"]       ?? "");
    $observacoes         = trim($_POST["observacoes"]         ?? "");

    if (empty($codInventario)) $erros[] = "O equipamento associado é obrigatório.";
    if (empty($dataInicio))    $erros[] = "A data de início é obrigatória.";
    if (empty($dataFim))       $erros[] = "A data de fim é obrigatória.";
    if (!empty($dataInicio) && !empty($dataFim) && $dataFim < $dataInicio) {
        $erros[] = "A data de fim não pode ser anterior à data de início.";
    }

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Garantia (codInventario, dataInicio, dataFim, temContrato, tipoContrato, entidadeResponsavel, periodicidade, observacoes) VALUES (:codInventario, :dataInicio, :dataFim, :temContrato, :tipoContrato, :entidadeResponsavel, :periodicidade, :observacoes)");
            $stmt->execute([
                ':codInventario'       => $codInventario,
                ':dataInicio'          => $dataInicio,
                ':dataFim'             => $dataFim,
                ':temContrato'         => $temContrato,
                ':tipoContrato'        => $tipoContrato        ?: null,
                ':entidadeResponsavel' => $entidadeResponsavel ?: null,
                ':periodicidade'       => $periodicidade       ?: null,
                ':observacoes'         => $observacoes         ?: null,
            ]);
            $ligacao = null;
            header('Location: garantias.php'); exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar a garantia: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// POST: atualizar garantia
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "editar") {
    $abrirModal = true;
    $modoEditar = true;
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: garantias.php'); exit;
    }
    $codInventario       = trim($_POST["codInventario"]       ?? "");
    $dataInicio          = trim($_POST["dataInicio"]          ?? "");
    $dataFim             = trim($_POST["dataFim"]             ?? "");
    $temContrato         = isset($_POST["temContrato"])        ? 1 : 0;
    $tipoContrato        = trim($_POST["tipoContrato"]        ?? "");
    $entidadeResponsavel = trim($_POST["entidadeResponsavel"] ?? "");
    $periodicidade       = trim($_POST["periodicidade"]       ?? "");
    $observacoes         = trim($_POST["observacoes"]         ?? "");

    if (empty($codInventario)) $erros[] = "O equipamento associado é obrigatório.";
    if (empty($dataInicio))    $erros[] = "A data de início é obrigatória.";
    if (empty($dataFim))       $erros[] = "A data de fim é obrigatória.";
    if (!empty($dataInicio) && !empty($dataFim) && $dataFim < $dataInicio) {
        $erros[] = "A data de fim não pode ser anterior à data de início.";
    }

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("UPDATE Garantia SET codInventario=:codInventario, dataInicio=:dataInicio, dataFim=:dataFim, temContrato=:temContrato, tipoContrato=:tipoContrato, entidadeResponsavel=:entidadeResponsavel, periodicidade=:periodicidade, observacoes=:observacoes WHERE codGarantia=:id");
            $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
            $stmt->execute([
                ':codInventario'       => $codInventario,
                ':dataInicio'          => $dataInicio,
                ':dataFim'             => $dataFim,
                ':temContrato'         => $temContrato,
                ':tipoContrato'        => $tipoContrato        ?: null,
                ':entidadeResponsavel' => $entidadeResponsavel ?: null,
                ':periodicidade'       => $periodicidade       ?: null,
                ':observacoes'         => $observacoes         ?: null,
            ]);
            $ligacao = null;
            header('Location: garantias.php'); exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao atualizar a garantia: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// Carregar listagem + select equipamentos
try {
    $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("
        SELECT g.*, e.designacao AS nomeEquipamento, e.marca, e.modelo
        FROM Garantia g
        LEFT JOIN Equipamento e ON g.codInventario = e.codInventario
        ORDER BY g.dataFim ASC
    ")->fetchAll(PDO::FETCH_OBJ);
    $equipamentos = $ligacao->query("SELECT codInventario, designacao, marca, modelo FROM Equipamento ORDER BY designacao")->fetchAll(PDO::FETCH_OBJ);
    $erro = '';
} catch (PDOException $err) {
    $erro = "Aconteceu um erro na ligação à base de dados.";
    $resultados = []; $equipamentos = [];
}
$ligacao = null;

$v = function(string $campo) use ($garantiaEditar): string {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return (string)($_POST[$campo] ?? '');
    if ($garantiaEditar !== null) return (string)($garantiaEditar->$campo ?? '');
    return '';
};

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Garantias e Contratos</h1>
            <p class="text-muted">Gestão de Prazos de Validade, Coberturas e Contratos de Manutenção.</p>

            <?php if (!empty($erro_sistema)) : ?>
                <div class="alert alert-danger"><strong>Erro:</strong> <?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-end mb-3">
                <button class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1d5370;"
                        data-bs-toggle="modal" data-bs-target="#modalGarantia">
                    <i class="fa-solid fa-plus me-2"></i>Registar Cobertura
                </button>
            </div>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <?php if (!empty($erro)) : ?>
                    <p class="text-center text-danger p-4"><?= $erro ?></p>
                <?php elseif (count($resultados) == 0) : ?>
                    <p class="text-muted p-4">Não existem garantias registadas.</p>
                <?php else : ?>
                    <table class="table table-hover align-middle mb-0" id="tabelaGarantias">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center ps-3" style="color: #1d5370;">ID</th>
                                <th class="text-center" style="color: #1d5370;">Equipamento</th>
                                <th class="text-center" style="color: #1d5370;">Período</th>
                                <th class="text-center" style="color: #1d5370;">Contrato</th>
                                <th class="text-center" style="color: #1d5370;">Entidade</th>
                                <th class="text-center" style="color: #1d5370;">Estado</th>
                                <th class="text-center pe-3" style="color: #1d5370;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $gar) :
                                $hoje = new DateTime();
                                $fim  = new DateTime($gar->dataFim);
                                $diff = $hoje->diff($fim);
                                $diasRestantes = (int)$diff->format('%r%a');

                                if ($diasRestantes < 0) {
                                    $estadoCor   = 'danger';
                                    $estadoLabel = 'Expirada';
                                } elseif ($diasRestantes <= 30) {
                                    $estadoCor   = 'warning';
                                    $estadoLabel = 'Expira em breve';
                                } else {
                                    $estadoCor   = 'success';
                                    $estadoLabel = 'Ativa';
                                }
                            ?>
                            <tr>
                                <td class="text-center ps-3 fw-bold text-secondary"><?= $gar->codGarantia ?></td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($gar->nomeEquipamento ?? '-') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars(($gar->marca ?? '') . ' ' . ($gar->modelo ?? '')) ?></small>
                                </td>
                                <td class="text-center text-dark">
                                    <?= date('d/m/Y', strtotime($gar->dataInicio)) ?>
                                    <i class="fa-solid fa-arrow-right mx-1 text-muted small"></i>
                                    <?= date('d/m/Y', strtotime($gar->dataFim)) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($gar->temContrato) : ?>
                                        <span class="badge bg-primary"><?= htmlspecialchars($gar->tipoContrato ?? 'Sim') ?></span>
                                    <?php else : ?>
                                        <span class="text-muted small">Sem contrato</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-muted small"><?= htmlspecialchars($gar->entidadeResponsavel ?? '-') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $estadoCor ?>"><?= $estadoLabel ?></span>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <a href="garantias.php?ver=<?= aes_encrypt($gar->codGarantia) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Consultar">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="garantias.php?editar=<?= aes_encrypt($gar->codGarantia) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="garantias.php?remover=<?= aes_encrypt($gar->codGarantia) ?>"
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

<!-- Modal Garantia (novo e editar) -->
<div class="modal fade" id="modalGarantia" tabindex="-1" aria-labelledby="modalGarantiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white modal-header-hospital">
                <h5 class="modal-title fw-bold" id="modalGarantiaLabel">
                    <i class="fa-solid fa-file-signature me-2"></i>
                    <?= $modoEditar ? 'Editar Cobertura' : 'Registar Cobertura' ?>
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
                <form id="formGarantia" method="post" action="#">
                    <input type="hidden" name="acao" value="<?= $modoEditar ? 'editar' : 'novo' ?>">
                    <?php if ($modoEditar && $idEncriptado) : ?>
                        <input type="hidden" name="idEncriptado" value="<?= htmlspecialchars($idEncriptado) ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Equipamento Associado</label>
                            <select class="form-select" name="codInventario" required>
                                <option value="" disabled <?= $v('codInventario') === '' ? 'selected' : '' ?>>Selecione o equipamento...</option>
                                <?php foreach ($equipamentos as $eq) : ?>
                                    <option value="<?= $eq->codInventario ?>"
                                        <?= $v('codInventario') == $eq->codInventario ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($eq->designacao . ' (' . $eq->marca . ' ' . $eq->modelo . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Data de Início</label>
                            <input type="date" class="form-control" name="dataInicio"
                                   value="<?= htmlspecialchars($v('dataInicio')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Data de Fim</label>
                            <input type="date" class="form-control" name="dataFim"
                                   value="<?= htmlspecialchars($v('dataFim')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Tipo de Contrato <span class="text-muted fw-normal">(opcional)</span></label>
                            <select class="form-select" name="tipoContrato">
                                <option value="">Nenhum</option>
                                <option value="Garantia de Fábrica"         <?= $v('tipoContrato') === 'Garantia de Fábrica'         ? 'selected' : '' ?>>Garantia de Fábrica</option>
                                <option value="Contrato de Assistência"     <?= $v('tipoContrato') === 'Contrato de Assistência'     ? 'selected' : '' ?>>Contrato de Assistência</option>
                                <option value="Extensão de Garantia"        <?= $v('tipoContrato') === 'Extensão de Garantia'        ? 'selected' : '' ?>>Extensão de Garantia</option>
                                <option value="Contrato de Manutenção"      <?= $v('tipoContrato') === 'Contrato de Manutenção'      ? 'selected' : '' ?>>Contrato de Manutenção</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Periodicidade <span class="text-muted fw-normal">(opcional)</span></label>
                            <select class="form-select" name="periodicidade">
                                <option value="">Não aplicável</option>
                                <option value="Mensal"     <?= $v('periodicidade') === 'Mensal'     ? 'selected' : '' ?>>Mensal</option>
                                <option value="Trimestral" <?= $v('periodicidade') === 'Trimestral' ? 'selected' : '' ?>>Trimestral</option>
                                <option value="Semestral"  <?= $v('periodicidade') === 'Semestral'  ? 'selected' : '' ?>>Semestral</option>
                                <option value="Anual"      <?= $v('periodicidade') === 'Anual'      ? 'selected' : '' ?>>Anual</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Entidade Responsável <span class="text-muted fw-normal">(opcional)</span></label>
                            <input type="text" class="form-control" name="entidadeResponsavel"
                                   placeholder="Ex: Dräger Portugal Lda."
                                   value="<?= htmlspecialchars($v('entidadeResponsavel')) ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="temContrato" id="checkTemContrato"
                                       <?= ($v('temContrato') == '1' || (isset($_POST['temContrato']))) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-secondary small" for="checkTemContrato">
                                    Possui contrato formal associado
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="2"
                                      placeholder="Notas adicionais..."><?= htmlspecialchars($v('observacoes')) ?></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formGarantia" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes Garantia -->
<div class="modal fade" id="modalDetalhesGarantia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white modal-header-hospital border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-file-signature me-2"></i>Detalhes da Garantia
                </h5>
                <span class="badge bg-light text-dark ms-auto me-3 px-3 py-2">
                    #<?= $garantiaVer ? str_pad($garantiaVer->codGarantia, 3, '0', STR_PAD_LEFT) : '---' ?>
                </span>
                <a href="garantias.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body p-4">
                <?php if ($garantiaVer) :
                    $hoje2 = new DateTime();
                    $fim2  = new DateTime($garantiaVer->dataFim);
                    $diff2 = $hoje2->diff($fim2);
                    $dias2 = (int)$diff2->format('%r%a');
                    if ($dias2 < 0)       { $corVer = 'danger';  $labelVer = 'Expirada'; }
                    elseif ($dias2 <= 30) { $corVer = 'warning'; $labelVer = 'Expira em breve'; }
                    else                  { $corVer = 'success'; $labelVer = 'Ativa'; }
                ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="small text-muted d-block">Equipamento</label>
                        <strong class="fs-5 text-dark"><?= htmlspecialchars($garantiaVer->nomeEquipamento ?? '-') ?></strong>
                        <span class="text-muted d-block small"><?= htmlspecialchars(($garantiaVer->marca ?? '') . ' ' . ($garantiaVer->modelo ?? '')) ?></span>
                    </div>
                    <div class="col-md-4 text-center">
                        <label class="small text-muted d-block">Estado</label>
                        <span class="badge bg-<?= $corVer ?> fs-6"><?= $labelVer ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Data de Início</label>
                        <span><?= date('d/m/Y', strtotime($garantiaVer->dataInicio)) ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Data de Fim</label>
                        <span><?= date('d/m/Y', strtotime($garantiaVer->dataFim)) ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Tipo de Contrato</label>
                        <span><?= htmlspecialchars($garantiaVer->tipoContrato ?? '-') ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Periodicidade</label>
                        <span><?= htmlspecialchars($garantiaVer->periodicidade ?? '-') ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Entidade Responsável</label>
                        <span><?= htmlspecialchars($garantiaVer->entidadeResponsavel ?? '-') ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Possui Contrato Formal</label>
                        <span class="badge bg-<?= $garantiaVer->temContrato ? 'success' : 'secondary' ?>">
                            <?= $garantiaVer->temContrato ? 'Sim' : 'Não' ?>
                        </span>
                    </div>
                    <?php if ($garantiaVer->observacoes) : ?>
                    <div class="col-12">
                        <label class="small text-muted d-block">Observações</label>
                        <p class="text-muted mb-0"><?= htmlspecialchars($garantiaVer->observacoes) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <p class="text-muted">Sem dados disponíveis.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light">
                <a href="garantias.php" class="btn btn-secondary px-4">Fechar</a>
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
                <a href="garantias.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fa-solid fa-trash fa-3x text-danger mb-3 d-block"></i>
                <p class="mb-1">Tem a certeza que pretende remover esta cobertura?</p>
                <p class="fw-bold fs-5 mb-1"><?= htmlspecialchars($garantiaRemover->nomeEquipamento ?? '') ?></p>
                <?php if ($garantiaRemover) : ?>
                <p class="text-muted small mb-3">
                    <?= date('d/m/Y', strtotime($garantiaRemover->dataInicio)) ?> →
                    <?= date('d/m/Y', strtotime($garantiaRemover->dataFim)) ?>
                </p>
                <?php endif; ?>
                <p class="text-danger small mb-0"><i class="fa-solid fa-exclamation-triangle me-1"></i>Esta ação é permanente e não pode ser revertida.</p>
            </div>
            <div class="modal-footer bg-light">
                <a href="garantias.php" class="btn btn-secondary">
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
    $('#tabelaGarantias').DataTable({
        pageLength: 10,
        lengthChange: false,
        searching: false,
        paging: false,
        info: false,
        language: {
            emptyTable: "Sem garantias disponíveis.",
            paginate: { first: "Primeira", last: "Última", next: "Seguinte", previous: "Anterior" }
        }
    });

    <?php if ($abrirModal) : ?>
    new bootstrap.Modal(document.getElementById('modalGarantia')).show();
    <?php endif; ?>
    <?php if ($abrirDetalheModal) : ?>
    new bootstrap.Modal(document.getElementById('modalDetalhesGarantia')).show();
    <?php endif; ?>
    <?php if ($abrirConfirmarModal) : ?>
    new bootstrap.Modal(document.getElementById('modalConfirmarRemocao')).show();
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
