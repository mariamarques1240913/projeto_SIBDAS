<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();
verificar_perfil(['administrador', 'tecnico']);

$erros = [];
$erro_sistema = "";
$abrirModal = false;
$abrirDetalheModal = false;
$abrirConfirmarModal = false;
$modoEditar = false;
$fornecedorEditar = null;
$idEncriptado = null;
$fornecedorVer = null;
$idEncriptadoVer = null;
$fornecedorRemover = null;
$idEncriptadoRemover = null;

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";

// GET: ver detalhes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ver'])) {
    $idEncriptadoVer = $_GET['ver'];
    $idDecriptado = aes_decrypt($idEncriptadoVer);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: fornecedores.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT * FROM Fornecedor WHERE codFornecedor = :id AND eliminado = 0");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $fornecedorVer = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$fornecedorVer) { header('Location: fornecedores.php'); exit; }
        $abrirDetalheModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar os detalhes do fornecedor.";
        $ligacao = null;
    }
}

// GET: confirmação de remoção
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remover'])) {
    $idEncriptadoRemover = $_GET['remover'];
    $idDecriptado = aes_decrypt($idEncriptadoRemover);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: fornecedores.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT codFornecedor, nomeEmpresa, NIF FROM Fornecedor WHERE codFornecedor = :id AND eliminado = 0");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $fornecedorRemover = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$fornecedorRemover) { header('Location: fornecedores.php'); exit; }
        $abrirConfirmarModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar o fornecedor.";
        $ligacao = null;
    }
}

// POST: remover
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "remover") {
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: fornecedores.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("UPDATE Fornecedor SET eliminado=1 WHERE codFornecedor = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $ligacao = null;
        header('Location: fornecedores.php'); exit;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao remover o fornecedor: " . $err->getMessage();
        $ligacao = null;
    }
}

// GET: editar
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
    $idEncriptado = $_GET['editar'];
    $idDecriptado = aes_decrypt($idEncriptado);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: fornecedores.php'); exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT * FROM Fornecedor WHERE codFornecedor = :id AND eliminado = 0");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $fornecedorEditar = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$fornecedorEditar) { header('Location: fornecedores.php'); exit; }
        $modoEditar = true;
        $abrirModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar o fornecedor.";
        $ligacao = null;
    }
}

// POST: inserir novo fornecedor
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "novo") {
    $abrirModal = true;
    $nomeEmpresa            = trim($_POST["nomeEmpresa"]            ?? "");
    $NIF                    = trim($_POST["NIF"]                    ?? "");
    $contactoTelefonico     = trim($_POST["contactoTelefonico"]     ?? "");
    $email                  = trim($_POST["email"]                  ?? "");
    $morada                 = trim($_POST["morada"]                 ?? "");
    $website                = trim($_POST["website"]                ?? "");
    $pessoaContacto         = trim($_POST["pessoaContacto"]         ?? "");
    $telefonePessoaContacto = trim($_POST["telefonePessoaContacto"] ?? "");
    $tipoFornecedor         = trim($_POST["tipoFornecedor"]         ?? "");
    $observacoes            = trim($_POST["observacoes"]            ?? "");

    if (empty($nomeEmpresa))    $erros[] = "O nome da empresa é obrigatório.";
    if (empty($NIF))            $erros[] = "O NIF é obrigatório.";
    elseif (!preg_match('/^\d{9}$/', $NIF)) $erros[] = "O NIF deve ter exatamente 9 dígitos numéricos."; // NIF português: sempre 9 dígitos
    if (empty($tipoFornecedor)) $erros[] = "O tipo de fornecedor é obrigatório.";
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $erros[] = "O email não tem um formato válido.";
    // Aceita formato internacional: +351 220 000 000, com espaços ou hífens
    if (!empty($contactoTelefonico) && !preg_match('/^\+?[\d\s\-]{9,15}$/', $contactoTelefonico))
        $erros[] = "O contacto telefónico não tem um formato válido (ex: +351 220 000 000).";
    if (!empty($telefonePessoaContacto) && !preg_match('/^\+?[\d\s\-]{9,15}$/', $telefonePessoaContacto))
        $erros[] = "O telefone da pessoa de contacto não tem um formato válido.";
    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL))
        $erros[] = "O website não tem um formato válido (ex: https://www.exemplo.pt).";

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Fornecedor (nomeEmpresa, NIF, contactoTelefonico, email, morada, website, pessoaContacto, telefonePessoaContacto, tipoFornecedor, observacoes) VALUES (:nomeEmpresa, :NIF, :contactoTelefonico, :email, :morada, :website, :pessoaContacto, :telefonePessoaContacto, :tipoFornecedor, :observacoes)");
            $stmt->execute([
                ':nomeEmpresa'            => $nomeEmpresa,
                ':NIF'                    => $NIF,
                ':contactoTelefonico'     => $contactoTelefonico     ?: null,
                ':email'                  => $email                  ?: null,
                ':morada'                 => $morada                 ?: null,
                ':website'                => $website                ?: null,
                ':pessoaContacto'         => $pessoaContacto         ?: null,
                ':telefonePessoaContacto' => $telefonePessoaContacto ?: null,
                ':tipoFornecedor'         => $tipoFornecedor,
                ':observacoes'            => $observacoes            ?: null,
            ]);
            $ligacao = null;
            header('Location: fornecedores.php'); exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar o fornecedor: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// POST: atualizar fornecedor
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "editar") {
    $abrirModal = true;
    $modoEditar = true;
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: fornecedores.php'); exit;
    }
    $nomeEmpresa            = trim($_POST["nomeEmpresa"]            ?? "");
    $NIF                    = trim($_POST["NIF"]                    ?? "");
    $contactoTelefonico     = trim($_POST["contactoTelefonico"]     ?? "");
    $email                  = trim($_POST["email"]                  ?? "");
    $morada                 = trim($_POST["morada"]                 ?? "");
    $website                = trim($_POST["website"]                ?? "");
    $pessoaContacto         = trim($_POST["pessoaContacto"]         ?? "");
    $telefonePessoaContacto = trim($_POST["telefonePessoaContacto"] ?? "");
    $tipoFornecedor         = trim($_POST["tipoFornecedor"]         ?? "");
    $observacoes            = trim($_POST["observacoes"]            ?? "");

    if (empty($nomeEmpresa))    $erros[] = "O nome da empresa é obrigatório.";
    if (empty($NIF))            $erros[] = "O NIF é obrigatório.";
    elseif (!preg_match('/^\d{9}$/', $NIF)) $erros[] = "O NIF deve ter exatamente 9 dígitos numéricos."; // NIF português: sempre 9 dígitos
    if (empty($tipoFornecedor)) $erros[] = "O tipo de fornecedor é obrigatório.";
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $erros[] = "O email não tem um formato válido.";
    // Aceita formato internacional: +351 220 000 000, com espaços ou hífens
    if (!empty($contactoTelefonico) && !preg_match('/^\+?[\d\s\-]{9,15}$/', $contactoTelefonico))
        $erros[] = "O contacto telefónico não tem um formato válido (ex: +351 220 000 000).";
    if (!empty($telefonePessoaContacto) && !preg_match('/^\+?[\d\s\-]{9,15}$/', $telefonePessoaContacto))
        $erros[] = "O telefone da pessoa de contacto não tem um formato válido.";
    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL))
        $erros[] = "O website não tem um formato válido (ex: https://www.exemplo.pt).";

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("UPDATE Fornecedor SET nomeEmpresa=:nomeEmpresa, NIF=:NIF, contactoTelefonico=:contactoTelefonico, email=:email, morada=:morada, website=:website, pessoaContacto=:pessoaContacto, telefonePessoaContacto=:telefonePessoaContacto, tipoFornecedor=:tipoFornecedor, observacoes=:observacoes WHERE codFornecedor=:id");
            $stmt->execute([
                ':nomeEmpresa'            => $nomeEmpresa,
                ':NIF'                    => $NIF,
                ':contactoTelefonico'     => $contactoTelefonico     ?: null,
                ':email'                  => $email                  ?: null,
                ':morada'                 => $morada                 ?: null,
                ':website'                => $website                ?: null,
                ':pessoaContacto'         => $pessoaContacto         ?: null,
                ':telefonePessoaContacto' => $telefonePessoaContacto ?: null,
                ':tipoFornecedor'         => $tipoFornecedor,
                ':observacoes'            => $observacoes            ?: null,
                ':id'                     => $idDecriptado,
            ]);
            $ligacao = null;
            header('Location: fornecedores.php'); exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao atualizar o fornecedor: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// Carregar listagem
try {
    $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("SELECT * FROM Fornecedor WHERE eliminado=0 ORDER BY nomeEmpresa")->fetchAll(PDO::FETCH_OBJ);
    $erro = '';
} catch (PDOException $err) {
    $erro = "Aconteceu um erro na ligação à base de dados.";
    $resultados = [];
}
$ligacao = null;

$v = function(string $campo) use ($fornecedorEditar): string {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return (string)($_POST[$campo] ?? '');
    if ($fornecedorEditar !== null) return (string)($fornecedorEditar->$campo ?? '');
    return '';
};

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Fornecedores</h1>
            <p class="text-muted">Gestão de Contactos e Fabricantes de Dispositivos Médicos.</p>

            <?php if (!empty($erro_sistema)) : ?>
                <div class="alert alert-danger"><strong>Erro:</strong> <?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-end mb-3">
                <button class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1d5370;"
                        data-bs-toggle="modal" data-bs-target="#modalFornecedor">
                    <i class="fa-solid fa-plus me-2"></i>Novo Fornecedor
                </button>
            </div>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <?php if (!empty($erro)) : ?>
                    <p class="text-center text-danger p-4"><?= $erro ?></p>
                <?php elseif (count($resultados) == 0) : ?>
                    <p class="text-muted p-4">Não existem fornecedores registados.</p>
                <?php else : ?>
                    <table class="table table-hover align-middle mb-0" id="tabelaFornecedores">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center ps-3" style="color: #1d5370;">ID</th>
                                <th class="text-center" style="color: #1d5370;">Nome da Empresa</th>
                                <th class="text-center" style="color: #1d5370;">NIF</th>
                                <th class="text-center" style="color: #1d5370;">Telefone</th>
                                <th class="text-center" style="color: #1d5370;">Email</th>
                                <th class="text-center" style="color: #1d5370;">Tipo</th>
                                <th class="text-center pe-3" style="color: #1d5370;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $forn) : ?>
                            <tr>
                                <td class="text-center ps-3 fw-bold text-secondary"><?= $forn->codFornecedor ?></td>
                                <td class="text-center fw-bold text-dark"><?= htmlspecialchars($forn->nomeEmpresa) ?></td>
                                <td class="text-center"><code><?= htmlspecialchars($forn->NIF) ?></code></td>
                                <td class="text-center"><?= htmlspecialchars($forn->contactoTelefonico ?? '-') ?></td>
                                <td class="text-center"><?= htmlspecialchars($forn->email ?? '-') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($forn->tipoFornecedor) ?></span>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <a href="fornecedores.php?ver=<?= aes_encrypt($forn->codFornecedor) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Consultar">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="fornecedores.php?editar=<?= aes_encrypt($forn->codFornecedor) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="fornecedores.php?remover=<?= aes_encrypt($forn->codFornecedor) ?>"
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

<!-- Modal Fornecedor (novo e editar) -->
<div class="modal fade" id="modalFornecedor" tabindex="-1" aria-labelledby="modalFornecedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white modal-header-hospital">
                <h5 class="modal-title fw-bold" id="modalFornecedorLabel">
                    <i class="fa-solid fa-truck-field me-2"></i>
                    <?= $modoEditar ? 'Editar Fornecedor' : 'Novo Fornecedor' ?>
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
                <form id="formFornecedor" method="post" action="#">
                    <input type="hidden" name="acao" value="<?= $modoEditar ? 'editar' : 'novo' ?>">
                    <?php if ($modoEditar && $idEncriptado) : ?>
                        <input type="hidden" name="idEncriptado" value="<?= htmlspecialchars($idEncriptado) ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-secondary small">Nome da Empresa</label>
                            <input type="text" class="form-control" name="nomeEmpresa"
                                   placeholder="Ex: Dräger Portugal Lda."
                                   value="<?= htmlspecialchars($v('nomeEmpresa')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">NIF</label>
                            <input type="text" class="form-control" name="NIF"
                                   placeholder="Ex: 501234567"
                                   value="<?= htmlspecialchars($v('NIF')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Contacto Telefónico</label>
                            <input type="tel" class="form-control" name="contactoTelefonico"
                                   placeholder="Ex: 214 907 500"
                                   value="<?= htmlspecialchars($v('contactoTelefonico')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Email</label>
                            <input type="email" class="form-control" name="email"
                                   placeholder="Ex: contacto@empresa.com"
                                   value="<?= htmlspecialchars($v('email')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Tipo de Fornecedor</label>
                            <select class="form-select" name="tipoFornecedor" required>
                                <option value="" disabled <?= $v('tipoFornecedor') === '' ? 'selected' : '' ?>>Selecione...</option>
                                <option value="fabricante"          <?= $v('tipoFornecedor') === 'fabricante'          ? 'selected' : '' ?>>Fabricante</option>
                                <option value="distribuidor"        <?= $v('tipoFornecedor') === 'distribuidor'        ? 'selected' : '' ?>>Distribuidor</option>
                                <option value="assistencia tecnica" <?= $v('tipoFornecedor') === 'assistencia tecnica' ? 'selected' : '' ?>>Assistência Técnica</option>
                                <option value="consumiveis"         <?= $v('tipoFornecedor') === 'consumiveis'         ? 'selected' : '' ?>>Consumíveis</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Morada</label>
                            <input type="text" class="form-control" name="morada"
                                   placeholder="Ex: Rua da Tecnologia, Lisboa"
                                   value="<?= htmlspecialchars($v('morada')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Website</label>
                            <input type="text" class="form-control" name="website"
                                   placeholder="Ex: https://www.empresa.pt"
                                   value="<?= htmlspecialchars($v('website')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Pessoa de Contacto</label>
                            <input type="text" class="form-control" name="pessoaContacto"
                                   placeholder="Ex: João Silva"
                                   value="<?= htmlspecialchars($v('pessoaContacto')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Telefone Direto</label>
                            <input type="tel" class="form-control" name="telefonePessoaContacto"
                                   placeholder="Ex: 912 345 678"
                                   value="<?= htmlspecialchars($v('telefonePessoaContacto')) ?>">
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
                <button type="submit" form="formFornecedor" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes Fornecedor -->
<div class="modal fade" id="modalDetalhesFornecedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white modal-header-hospital border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-building me-2"></i>Ficha do Fornecedor
                </h5>
                <span class="badge bg-light text-dark ms-auto me-3 px-3 py-2">
                    #<?= $fornecedorVer ? str_pad($fornecedorVer->codFornecedor, 3, '0', STR_PAD_LEFT) : '---' ?>
                </span>
                <a href="fornecedores.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body p-4">
                <?php if ($fornecedorVer) : ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="small text-muted d-block">Nome da Empresa</label>
                        <strong class="fs-5 text-dark"><?= htmlspecialchars($fornecedorVer->nomeEmpresa) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted d-block">Tipo</label>
                        <span class="badge bg-secondary fs-6"><?= htmlspecialchars($fornecedorVer->tipoFornecedor) ?></span>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted d-block">NIF</label>
                        <code><?= htmlspecialchars($fornecedorVer->NIF) ?></code>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted d-block">Telefone</label>
                        <span><?= htmlspecialchars($fornecedorVer->contactoTelefonico ?? '-') ?></span>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted d-block">Email</label>
                        <span><?= htmlspecialchars($fornecedorVer->email ?? '-') ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Morada</label>
                        <span><?= htmlspecialchars($fornecedorVer->morada ?? '-') ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Website</label>
                        <?php if ($fornecedorVer->website) : ?>
                            <a href="<?= htmlspecialchars($fornecedorVer->website) ?>" target="_blank" rel="noopener noreferrer">
                                <?= htmlspecialchars($fornecedorVer->website) ?>
                            </a>
                        <?php else : ?>
                            <span>-</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Pessoa de Contacto</label>
                        <span><?= htmlspecialchars($fornecedorVer->pessoaContacto ?? '-') ?></span>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Telefone Direto</label>
                        <span><?= htmlspecialchars($fornecedorVer->telefonePessoaContacto ?? '-') ?></span>
                    </div>
                    <?php if ($fornecedorVer->observacoes) : ?>
                    <div class="col-12">
                        <label class="small text-muted d-block">Observações</label>
                        <p class="text-muted mb-0"><?= htmlspecialchars($fornecedorVer->observacoes) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <p class="text-muted">Sem dados disponíveis.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light">
                <a href="fornecedores.php" class="btn btn-secondary px-4">Fechar</a>
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
                <a href="fornecedores.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fa-solid fa-trash fa-3x text-danger mb-3 d-block"></i>
                <p class="mb-1">Tem a certeza que pretende remover o fornecedor?</p>
                <p class="fw-bold fs-5 mb-1"><?= htmlspecialchars($fornecedorRemover->nomeEmpresa ?? '') ?></p>
                <p class="text-muted small mb-3">NIF: <?= htmlspecialchars($fornecedorRemover->NIF ?? '') ?></p>
                <p class="text-danger small mb-0"><i class="fa-solid fa-exclamation-triangle me-1"></i>Esta ação é permanente e não pode ser revertida.</p>
            </div>
            <div class="modal-footer bg-light">
                <a href="fornecedores.php" class="btn btn-secondary">
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
    $('#tabelaFornecedores').DataTable({
        pageLength: 10,
        lengthChange: false,
        searching: false,
        paging: false,
        info: false,
        language: {
            emptyTable: "Sem fornecedores disponíveis.",
            paginate: { first: "Primeira", last: "Última", next: "Seguinte", previous: "Anterior" }
        }
    });

    <?php if ($abrirModal) : ?>
    new bootstrap.Modal(document.getElementById('modalFornecedor')).show();
    <?php endif; ?>
    <?php if ($abrirDetalheModal) : ?>
    new bootstrap.Modal(document.getElementById('modalDetalhesFornecedor')).show();
    <?php endif; ?>
    <?php if ($abrirConfirmarModal) : ?>
    new bootstrap.Modal(document.getElementById('modalConfirmarRemocao')).show();
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
