<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$soLeitura = (perfil_atual() === 'profissional de saude');
if ($soLeitura && ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['editar']) || isset($_GET['remover']))) {
    $_SESSION['server_error'] = 'Não tem permissão para realizar esta ação.';
    header('Location: documentacao.php');
    exit;
}

$erros = [];
$erro_sistema = "";
$abrirModal = false;
$abrirDetalheModal = false;
$abrirConfirmarModal = false;
$modoEditar = false;
$documentoEditar = null;
$idEncriptado = null;
$documentoVer = null;
$idEncriptadoVer = null;
$documentoRemover = null;
$idEncriptadoRemover = null;

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";

// GET: ver detalhes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ver'])) {
    $idEncriptadoVer = $_GET['ver'];
    $idDecriptado = aes_decrypt($idEncriptadoVer);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: documentacao.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("
            SELECT d.*, e.designacao AS nomeEquipamento, f.nomeEmpresa AS nomeFornecedor
            FROM Documento d
            LEFT JOIN Equipamento e ON d.codInventario = e.codInventario
            LEFT JOIN Fornecedor f ON d.codFornecedor = f.codFornecedor
            WHERE d.codDocumento = :id AND d.eliminado = 0
        ");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $documentoVer = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$documentoVer) { header('Location: documentacao.php'); exit; }
        $abrirDetalheModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar os detalhes do documento.";
        $ligacao = null;
    }
}

// GET: confirmação de remoção
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remover'])) {
    $idEncriptadoRemover = $_GET['remover'];
    $idDecriptado = aes_decrypt($idEncriptadoRemover);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: documentacao.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT codDocumento, nomeDocumento, tipoDocumento FROM Documento WHERE codDocumento = :id AND eliminado = 0");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $documentoRemover = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$documentoRemover) { header('Location: documentacao.php'); exit; }
        $abrirConfirmarModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar o documento.";
        $ligacao = null;
    }
}

// POST: remover
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "remover") {
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: documentacao.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("UPDATE Documento SET eliminado=1 WHERE codDocumento = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $ligacao = null;
        header('Location: documentacao.php'); exit;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao remover o documento: " . $err->getMessage();
        $ligacao = null;
    }
}

// GET: editar
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
    $idEncriptado = $_GET['editar'];
    $idDecriptado = aes_decrypt($idEncriptado);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: documentacao.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT * FROM Documento WHERE codDocumento = :id AND eliminado = 0");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $documentoEditar = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$documentoEditar) { header('Location: documentacao.php'); exit; }
        $modoEditar = true;
        $abrirModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar o documento.";
        $ligacao = null;
    }
}

// POST: inserir novo documento
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "novo") {
    $abrirModal = true;
    $codInventario       = trim($_POST["codInventario"]       ?? "");
    $codFornecedor       = trim($_POST["codFornecedor"]       ?? "");
    $tipoDocumento       = trim($_POST["tipoDocumento"]       ?? "");
    $nomeDocumento       = trim($_POST["nomeDocumento"]       ?? "");
    $dataDocumento       = trim($_POST["dataDocumento"]       ?? "");
    $dataValidade        = trim($_POST["dataValidade"]        ?? "");
    $localizacaoFicheiro = trim($_POST["localizacaoFicheiro"] ?? "");

    if (empty($codInventario)) $erros[] = "O equipamento associado é obrigatório.";
    if (empty($tipoDocumento)) $erros[] = "O tipo de documento é obrigatório.";
    if (empty($nomeDocumento)) $erros[] = "O nome do documento é obrigatório.";
    if (!empty($dataDocumento) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataDocumento))
        $erros[] = "Formato de data do documento inválido.";
    if (!empty($dataValidade) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataValidade))
        $erros[] = "Formato de data de validade inválido.";
    if (!empty($dataDocumento) && !empty($dataValidade) && $dataValidade < $dataDocumento)
        $erros[] = "A data de validade não pode ser anterior à data do documento.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Documento (codInventario, codFornecedor, tipoDocumento, nomeDocumento, dataDocumento, dataValidade, localizacaoFicheiro) VALUES (:codInventario, :codFornecedor, :tipoDocumento, :nomeDocumento, :dataDocumento, :dataValidade, :localizacaoFicheiro)");
            $stmt->execute([
                ':codInventario'       => $codInventario,
                ':codFornecedor'       => $codFornecedor       ?: null,
                ':tipoDocumento'       => $tipoDocumento,
                ':nomeDocumento'       => $nomeDocumento,
                ':dataDocumento'       => $dataDocumento       ?: null,
                ':dataValidade'        => $dataValidade        ?: null,
                ':localizacaoFicheiro' => $localizacaoFicheiro ?: null,
            ]);
            $ligacao = null;
            header('Location: documentacao.php'); exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar o documento: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// POST: atualizar documento
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "editar") {
    $abrirModal = true;
    $modoEditar = true;
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: documentacao.php'); exit;
    }
    $codInventario       = trim($_POST["codInventario"]       ?? "");
    $codFornecedor       = trim($_POST["codFornecedor"]       ?? "");
    $tipoDocumento       = trim($_POST["tipoDocumento"]       ?? "");
    $nomeDocumento       = trim($_POST["nomeDocumento"]       ?? "");
    $dataDocumento       = trim($_POST["dataDocumento"]       ?? "");
    $dataValidade        = trim($_POST["dataValidade"]        ?? "");
    $localizacaoFicheiro = trim($_POST["localizacaoFicheiro"] ?? "");

    if (empty($codInventario)) $erros[] = "O equipamento associado é obrigatório.";
    if (empty($tipoDocumento)) $erros[] = "O tipo de documento é obrigatório.";
    if (empty($nomeDocumento)) $erros[] = "O nome do documento é obrigatório.";
    if (!empty($dataDocumento) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataDocumento))
        $erros[] = "Formato de data do documento inválido.";
    if (!empty($dataValidade) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataValidade))
        $erros[] = "Formato de data de validade inválido.";
    if (!empty($dataDocumento) && !empty($dataValidade) && $dataValidade < $dataDocumento)
        $erros[] = "A data de validade não pode ser anterior à data do documento.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("UPDATE Documento SET codInventario=:codInventario, codFornecedor=:codFornecedor, tipoDocumento=:tipoDocumento, nomeDocumento=:nomeDocumento, dataDocumento=:dataDocumento, dataValidade=:dataValidade, localizacaoFicheiro=:localizacaoFicheiro WHERE codDocumento=:id");
            $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
            $stmt->execute([
                ':codInventario'       => $codInventario,
                ':codFornecedor'       => $codFornecedor       ?: null,
                ':tipoDocumento'       => $tipoDocumento,
                ':nomeDocumento'       => $nomeDocumento,
                ':dataDocumento'       => $dataDocumento       ?: null,
                ':dataValidade'        => $dataValidade        ?: null,
                ':localizacaoFicheiro' => $localizacaoFicheiro ?: null,
            ]);
            $ligacao = null;
            header('Location: documentacao.php'); exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao atualizar o documento: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// Carregar listagem + selects
try {
    $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("
        SELECT d.*, e.designacao AS nomeEquipamento, f.nomeEmpresa AS nomeFornecedor
        FROM Documento d
        LEFT JOIN Equipamento e ON d.codInventario = e.codInventario
        LEFT JOIN Fornecedor f ON d.codFornecedor = f.codFornecedor
        WHERE d.eliminado = 0
        ORDER BY d.codDocumento DESC
    ")->fetchAll(PDO::FETCH_OBJ);
    $equipamentos = $ligacao->query("SELECT codInventario, designacao, marca, modelo FROM Equipamento WHERE eliminado=0 ORDER BY designacao")->fetchAll(PDO::FETCH_OBJ);
    $fornecedores = $ligacao->query("SELECT codFornecedor, nomeEmpresa FROM Fornecedor WHERE eliminado=0 ORDER BY nomeEmpresa")->fetchAll(PDO::FETCH_OBJ);
    $erro = '';
} catch (PDOException $err) {
    $erro = "Aconteceu um erro na ligação à base de dados.";
    $resultados = []; $equipamentos = []; $fornecedores = [];
}
$ligacao = null;

$v = function(string $campo) use ($documentoEditar): string {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return (string)($_POST[$campo] ?? '');
    if ($documentoEditar !== null) return (string)($documentoEditar->$campo ?? '');
    return '';
};

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Documentação Técnica</h1>
            <p class="text-muted">Manuais, Relatórios e Guias de Operação dos Equipamentos.</p>

            <?php if (!empty($erro_sistema)) : ?>
                <div class="alert alert-danger"><strong>Erro:</strong> <?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <?php if (!$soLeitura): ?>
            <div class="d-flex justify-content-end mb-3">
                <button class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1d5370;"
                        data-bs-toggle="modal" data-bs-target="#modalDocumento">
                    <i class="fa-solid fa-paperclip me-2"></i>Registar Documento
                </button>
            </div>
            <?php endif; ?>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <?php if (!empty($erro)) : ?>
                    <p class="text-center text-danger p-4"><?= $erro ?></p>
                <?php elseif (count($resultados) == 0) : ?>
                    <p class="text-muted p-4">Não existem documentos registados.</p>
                <?php else : ?>
                    <table class="table table-hover align-middle mb-0" id="tabelaDocumentos">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center ps-3" style="color: #1d5370;">ID</th>
                                <th class="text-center" style="color: #1d5370;">Equipamento</th>
                                <th class="text-center" style="color: #1d5370;">Tipo</th>
                                <th class="text-center" style="color: #1d5370;">Nome do Documento</th>
                                <th class="text-center" style="color: #1d5370;">Data</th>
                                <th class="text-center" style="color: #1d5370;">Validade</th>
                                <th class="text-center pe-3" style="color: #1d5370;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $doc) :
                                $tipoCor = match($doc->tipoDocumento) {
                                    'Manual'      => 'primary',
                                    'Guia'        => 'success',
                                    'Relatório'   => 'warning',
                                    'Certificado' => 'info',
                                    'Contrato'    => 'dark',
                                    default       => 'secondary'
                                };
                            ?>
                            <tr>
                                <td class="text-center ps-3 fw-bold text-secondary"><?= $doc->codDocumento ?></td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($doc->nomeEquipamento ?? '-') ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $tipoCor ?>"><?= htmlspecialchars($doc->tipoDocumento) ?></span>
                                </td>
                                <td class="text-center">
                                    <i class="fa-solid fa-file-lines text-muted me-1"></i>
                                    <?= htmlspecialchars($doc->nomeDocumento) ?>
                                </td>
                                <td class="text-center text-muted">
                                    <?= $doc->dataDocumento ? date('d/m/Y', strtotime($doc->dataDocumento)) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($doc->dataValidade) :
                                        $validade = new DateTime($doc->dataValidade);
                                        $hoje = new DateTime();
                                        $expirado = $validade < $hoje;
                                    ?>
                                        <span class="badge bg-<?= $expirado ? 'danger' : 'success' ?>">
                                            <?= date('d/m/Y', strtotime($doc->dataValidade)) ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <a href="documentacao.php?ver=<?= aes_encrypt($doc->codDocumento) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Consultar">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if (!$soLeitura): ?>
                                        <a href="documentacao.php?editar=<?= aes_encrypt($doc->codDocumento) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="documentacao.php?remover=<?= aes_encrypt($doc->codDocumento) ?>"
                                           class="btn btn-sm btn-outline-danger" title="Remover">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
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

<!-- Modal Documento (novo e editar) -->
<div class="modal fade" id="modalDocumento" tabindex="-1" aria-labelledby="modalDocumentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white modal-header-hospital">
                <h5 class="modal-title fw-bold" id="modalDocumentoLabel">
                    <i class="fa-solid fa-paperclip me-2"></i>
                    <?= $modoEditar ? 'Editar Documento' : 'Registar Documento' ?>
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
                <form id="formDocumento" method="post" action="#">
                    <input type="hidden" name="acao" value="<?= $modoEditar ? 'editar' : 'novo' ?>">
                    <?php if ($modoEditar && $idEncriptado) : ?>
                        <input type="hidden" name="idEncriptado" value="<?= htmlspecialchars($idEncriptado) ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
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
                            <label class="form-label fw-bold text-secondary small">Fornecedor <span class="text-muted fw-normal">(opcional)</span></label>
                            <select class="form-select" name="codFornecedor">
                                <option value="">Nenhum</option>
                                <?php foreach ($fornecedores as $forn) : ?>
                                    <option value="<?= $forn->codFornecedor ?>"
                                        <?= $v('codFornecedor') == $forn->codFornecedor ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($forn->nomeEmpresa) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Tipo de Documento</label>
                            <select class="form-select" name="tipoDocumento" required>
                                <option value="" disabled <?= $v('tipoDocumento') === '' ? 'selected' : '' ?>>Selecione...</option>
                                <option value="Manual"      <?= $v('tipoDocumento') === 'Manual'      ? 'selected' : '' ?>>Manual do Utilizador</option>
                                <option value="Guia"        <?= $v('tipoDocumento') === 'Guia'        ? 'selected' : '' ?>>Guia de Consulta Rápida</option>
                                <option value="Relatório"   <?= $v('tipoDocumento') === 'Relatório'   ? 'selected' : '' ?>>Relatório de Manutenção</option>
                                <option value="Certificado" <?= $v('tipoDocumento') === 'Certificado' ? 'selected' : '' ?>>Certificado de Calibração</option>
                                <option value="Contrato"    <?= $v('tipoDocumento') === 'Contrato'    ? 'selected' : '' ?>>Contrato</option>
                                <option value="Outro"       <?= $v('tipoDocumento') === 'Outro'       ? 'selected' : '' ?>>Outro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Nome do Documento</label>
                            <input type="text" class="form-control" name="nomeDocumento"
                                   placeholder="Ex: manual_draeger_v3.pdf"
                                   value="<?= htmlspecialchars($v('nomeDocumento')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Data do Documento</label>
                            <input type="date" class="form-control" name="dataDocumento"
                                   value="<?= htmlspecialchars($v('dataDocumento')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Data de Validade <span class="text-muted fw-normal">(opcional)</span></label>
                            <input type="date" class="form-control" name="dataValidade"
                                   value="<?= htmlspecialchars($v('dataValidade')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Link ou Caminho do Ficheiro <span class="text-muted fw-normal">(opcional)</span></label>
                            <input type="text" class="form-control" name="localizacaoFicheiro"
                                   placeholder="Ex: https://... ou /docs/manual.pdf"
                                   value="<?= htmlspecialchars($v('localizacaoFicheiro')) ?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formDocumento" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes Documento -->
<div class="modal fade" id="modalDetalhesDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white modal-header-hospital border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-file-lines me-2"></i>Detalhes do Documento
                </h5>
                <span class="badge bg-light text-dark ms-auto me-3 px-3 py-2">
                    #<?= $documentoVer ? str_pad($documentoVer->codDocumento, 3, '0', STR_PAD_LEFT) : '---' ?>
                </span>
                <a href="documentacao.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body p-4">
                <?php if ($documentoVer) :
                    $tipoCor = match($documentoVer->tipoDocumento) {
                        'Manual'      => 'primary',
                        'Guia'        => 'success',
                        'Relatório'   => 'warning',
                        'Certificado' => 'info',
                        'Contrato'    => 'dark',
                        default       => 'secondary'
                    };
                ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="small text-muted d-block">Nome do Documento</label>
                        <strong class="fs-5 text-dark"><?= htmlspecialchars($documentoVer->nomeDocumento) ?></strong>
                    </div>
                    <div class="col-md-4 text-center">
                        <label class="small text-muted d-block">Tipo</label>
                        <span class="badge bg-<?= $tipoCor ?> fs-6"><?= htmlspecialchars($documentoVer->tipoDocumento) ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Equipamento Associado</label>
                        <span class="fw-bold"><?= htmlspecialchars($documentoVer->nomeEquipamento ?? '-') ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Fornecedor</label>
                        <span><?= htmlspecialchars($documentoVer->nomeFornecedor ?? '-') ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Data do Documento</label>
                        <span><?= $documentoVer->dataDocumento ? date('d/m/Y', strtotime($documentoVer->dataDocumento)) : '-' ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Data de Validade</label>
                        <?php if ($documentoVer->dataValidade) :
                            $validade = new DateTime($documentoVer->dataValidade);
                            $expirado = $validade < new DateTime();
                        ?>
                            <span class="badge bg-<?= $expirado ? 'danger' : 'success' ?>">
                                <?= date('d/m/Y', strtotime($documentoVer->dataValidade)) ?>
                                <?= $expirado ? ' (expirado)' : '' ?>
                            </span>
                        <?php else : ?>
                            <span>-</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($documentoVer->localizacaoFicheiro) : ?>
                    <div class="col-12">
                        <label class="small text-muted d-block">Link / Ficheiro</label>
                        <a href="<?= htmlspecialchars($documentoVer->localizacaoFicheiro) ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>
                            <?= htmlspecialchars($documentoVer->localizacaoFicheiro) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <p class="text-muted">Sem dados disponíveis.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light">
                <a href="documentacao.php" class="btn btn-secondary px-4">Fechar</a>
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
                <a href="documentacao.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fa-solid fa-trash fa-3x text-danger mb-3 d-block"></i>
                <p class="mb-1">Tem a certeza que pretende remover o documento?</p>
                <p class="fw-bold fs-5 mb-1"><?= htmlspecialchars($documentoRemover->nomeDocumento ?? '') ?></p>
                <p class="text-muted small mb-3"><?= htmlspecialchars($documentoRemover->tipoDocumento ?? '') ?></p>
                <p class="text-danger small mb-0"><i class="fa-solid fa-exclamation-triangle me-1"></i>Esta ação é permanente e não pode ser revertida.</p>
            </div>
            <div class="modal-footer bg-light">
                <a href="documentacao.php" class="btn btn-secondary">
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
    $('#tabelaDocumentos').DataTable({
        pageLength: 10,
        lengthChange: false,
        searching: false,
        paging: false,
        info: false,
        language: {
            emptyTable: "Sem documentos disponíveis.",
            paginate: { first: "Primeira", last: "Última", next: "Seguinte", previous: "Anterior" }
        }
    });

    <?php if ($abrirModal) : ?>
    new bootstrap.Modal(document.getElementById('modalDocumento')).show();
    <?php endif; ?>
    <?php if ($abrirDetalheModal) : ?>
    new bootstrap.Modal(document.getElementById('modalDetalhesDocumento')).show();
    <?php endif; ?>
    <?php if ($abrirConfirmarModal) : ?>
    new bootstrap.Modal(document.getElementById('modalConfirmarRemocao')).show();
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
