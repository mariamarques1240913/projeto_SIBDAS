<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$erros = [];
$erro_sistema = "";
$abrirModal = false;
$abrirDetalheModal = false;
$abrirConfirmarModal = false;
$modoEditar = false;
$equipamentoEditar = null;
$idEncriptado = null;
$equipamentoVer = null;
$idEncriptadoVer = null;
$equipamentoRemover = null;
$idEncriptadoRemover = null;

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";

// GET: ver detalhes completos (Ficha 14 - Secção 1)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ver'])) {
    $idEncriptadoVer = $_GET['ver'];
    $idDecriptado = aes_decrypt($idEncriptadoVer);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: equipamentos.php');
        exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("
            SELECT e.*,
                   c.designacao AS nomeCategoria,
                   CONCAT(l.edificio, ' - ', l.servico, ' (Piso ', l.piso, ')') AS nomeLocalizacao
            FROM Equipamento e
            LEFT JOIN Categoria c ON e.codCategoria = c.codCategoria
            LEFT JOIN Localizacao l ON e.codLocalizacao = l.codLocalizacao
            WHERE e.codInventario = :id
        ");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $equipamentoVer = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$equipamentoVer) {
            header('Location: equipamentos.php');
            exit;
        }
        $abrirDetalheModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar os detalhes do equipamento.";
        $ligacao = null;
    }
}

// GET: confirmação de remoção (Ficha 14 - Secção 2)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remover'])) {
    $idEncriptadoRemover = $_GET['remover'];
    $idDecriptado = aes_decrypt($idEncriptadoRemover);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: equipamentos.php');
        exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT codInventario, designacao, marca, modelo FROM Equipamento WHERE codInventario = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $equipamentoRemover = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$equipamentoRemover) {
            header('Location: equipamentos.php');
            exit;
        }
        $abrirConfirmarModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar o equipamento.";
        $ligacao = null;
    }
}

// POST: remover equipamento (Ficha 14 - Secção 2)
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "remover") {
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: equipamentos.php');
        exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("DELETE FROM Equipamento WHERE codInventario = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $ligacao = null;
        header('Location: equipamentos.php');
        exit;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao remover o equipamento: " . $err->getMessage();
        $ligacao = null;
    }
}

// GET: editar (Ficha 13)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
    $idEncriptado = $_GET['editar'];
    $idDecriptado = aes_decrypt($idEncriptado);
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: equipamentos.php');
        exit;
    }
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT * FROM Equipamento WHERE codInventario = :id");
        $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
        $stmt->execute();
        $equipamentoEditar = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if (!$equipamentoEditar) {
            header('Location: equipamentos.php');
            exit;
        }
        $modoEditar = true;
        $abrirModal = true;
    } catch (PDOException $err) {
        $erro_sistema = "Erro ao carregar o equipamento.";
        $ligacao = null;
    }
}

// POST: inserir novo equipamento (Ficha 12)
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "novo") {
    $abrirModal = true;

    $designacao     = trim($_POST["designacao"]     ?? "");
    $codCategoria   = trim($_POST["codCategoria"]   ?? "");
    $marca          = trim($_POST["marca"]          ?? "");
    $modelo         = trim($_POST["modelo"]         ?? "");
    $nrSerie        = trim($_POST["nrSerie"]        ?? "");
    $fabricante     = trim($_POST["fabricante"]     ?? "");
    $dataAquisicao  = trim($_POST["dataAquisicao"]  ?? "");
    $anoFabrico     = trim($_POST["anoFabrico"]     ?? "");
    $custoAquisicao = trim($_POST["custoAquisicao"] ?? "");
    $tipoEntrada    = trim($_POST["tipoEntrada"]    ?? "");
    $estado         = trim($_POST["estado"]         ?? "");
    $codLocalizacao = trim($_POST["codLocalizacao"] ?? "");
    $criticidade    = trim($_POST["criticidade"]    ?? "");
    $observacoes    = trim($_POST["observacoes"]    ?? "");

    if (empty($designacao))     $erros[] = "A designação é obrigatória.";
    if (empty($codCategoria))   $erros[] = "A categoria é obrigatória.";
    if (empty($marca))          $erros[] = "A marca é obrigatória.";
    if (empty($modelo))         $erros[] = "O modelo é obrigatório.";
    if (empty($nrSerie))        $erros[] = "O número de série é obrigatório.";
    if (empty($fabricante))     $erros[] = "O fabricante é obrigatório.";
    if (empty($tipoEntrada))    $erros[] = "O tipo de entrada é obrigatório.";
    if (empty($estado))         $erros[] = "O estado é obrigatório.";
    if (empty($codLocalizacao)) $erros[] = "A localização é obrigatória.";
    if (empty($criticidade))    $erros[] = "A criticidade é obrigatória.";
    if (!empty($dataAquisicao) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAquisicao)) {
        $erros[] = "Formato de data inválido (use AAAA-MM-DD).";
    }

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO Equipamento
                        (codCategoria, codLocalizacao, designacao, marca, modelo, nrSerie,
                         fabricante, dataAquisicao, anoFabrico, custoAquisicao,
                         tipoEntrada, estado, criticidade, observacoes)
                    VALUES
                        (:codCategoria, :codLocalizacao, :designacao, :marca, :modelo, :nrSerie,
                         :fabricante, :dataAquisicao, :anoFabrico, :custoAquisicao,
                         :tipoEntrada, :estado, :criticidade, :observacoes)";
            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':codCategoria'   => $codCategoria,   ':codLocalizacao' => $codLocalizacao,
                ':designacao'     => $designacao,     ':marca'          => $marca,
                ':modelo'         => $modelo,         ':nrSerie'        => $nrSerie,
                ':fabricante'     => $fabricante,     ':dataAquisicao'  => $dataAquisicao ?: null,
                ':anoFabrico'     => $anoFabrico ?: null,
                ':custoAquisicao' => $custoAquisicao ?: null,
                ':tipoEntrada'    => $tipoEntrada,    ':estado'         => $estado,
                ':criticidade'    => $criticidade,    ':observacoes'    => $observacoes ?: null,
            ]);
            $ligacao = null;
            header('Location: equipamentos.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao gravar os dados: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// POST: atualizar equipamento (Ficha 13)
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "editar") {
    $abrirModal = true;
    $modoEditar = true;
    $idEncriptado = $_POST["idEncriptado"] ?? null;
    $idDecriptado = $idEncriptado ? aes_decrypt($idEncriptado) : null;
    if (!$idDecriptado || !is_numeric($idDecriptado)) {
        header('Location: equipamentos.php');
        exit;
    }

    $designacao     = trim($_POST["designacao"]     ?? "");
    $codCategoria   = trim($_POST["codCategoria"]   ?? "");
    $marca          = trim($_POST["marca"]          ?? "");
    $modelo         = trim($_POST["modelo"]         ?? "");
    $nrSerie        = trim($_POST["nrSerie"]        ?? "");
    $fabricante     = trim($_POST["fabricante"]     ?? "");
    $dataAquisicao  = trim($_POST["dataAquisicao"]  ?? "");
    $anoFabrico     = trim($_POST["anoFabrico"]     ?? "");
    $custoAquisicao = trim($_POST["custoAquisicao"] ?? "");
    $tipoEntrada    = trim($_POST["tipoEntrada"]    ?? "");
    $estado         = trim($_POST["estado"]         ?? "");
    $codLocalizacao = trim($_POST["codLocalizacao"] ?? "");
    $criticidade    = trim($_POST["criticidade"]    ?? "");
    $observacoes    = trim($_POST["observacoes"]    ?? "");

    if (empty($designacao))     $erros[] = "A designação é obrigatória.";
    if (empty($codCategoria))   $erros[] = "A categoria é obrigatória.";
    if (empty($marca))          $erros[] = "A marca é obrigatória.";
    if (empty($modelo))         $erros[] = "O modelo é obrigatório.";
    if (empty($nrSerie))        $erros[] = "O número de série é obrigatório.";
    if (empty($fabricante))     $erros[] = "O fabricante é obrigatório.";
    if (empty($tipoEntrada))    $erros[] = "O tipo de entrada é obrigatório.";
    if (empty($estado))         $erros[] = "O estado é obrigatório.";
    if (empty($codLocalizacao)) $erros[] = "A localização é obrigatória.";
    if (empty($criticidade))    $erros[] = "A criticidade é obrigatória.";
    if (!empty($dataAquisicao) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAquisicao)) {
        $erros[] = "Formato de data inválido.";
    }

    if (empty($erros)) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "UPDATE Equipamento SET
                        codCategoria   = :codCategoria,  codLocalizacao = :codLocalizacao,
                        designacao     = :designacao,    marca          = :marca,
                        modelo         = :modelo,        nrSerie        = :nrSerie,
                        fabricante     = :fabricante,    dataAquisicao  = :dataAquisicao,
                        anoFabrico     = :anoFabrico,    custoAquisicao = :custoAquisicao,
                        tipoEntrada    = :tipoEntrada,   estado         = :estado,
                        criticidade    = :criticidade,   observacoes    = :observacoes
                    WHERE codInventario = :id";
            $stmt = $ligacao->prepare($sql);
            $stmt->bindParam(':id', $idDecriptado, PDO::PARAM_INT);
            $stmt->execute([
                ':codCategoria'   => $codCategoria,   ':codLocalizacao' => $codLocalizacao,
                ':designacao'     => $designacao,     ':marca'          => $marca,
                ':modelo'         => $modelo,         ':nrSerie'        => $nrSerie,
                ':fabricante'     => $fabricante,     ':dataAquisicao'  => $dataAquisicao ?: null,
                ':anoFabrico'     => $anoFabrico ?: null,
                ':custoAquisicao' => $custoAquisicao ?: null,
                ':tipoEntrada'    => $tipoEntrada,    ':estado'         => $estado,
                ':criticidade'    => $criticidade,    ':observacoes'    => $observacoes ?: null,
            ]);
            $ligacao = null;
            header('Location: equipamentos.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao atualizar os dados: " . $err->getMessage();
        }
        $ligacao = null;
    }
}

// Carregar dados para listagem e selects
try {
    $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("
        SELECT e.codInventario, e.designacao, e.marca, e.modelo, e.nrSerie, e.estado, e.criticidade,
               c.designacao AS categoria,
               CONCAT(l.edificio, ' - ', l.servico) AS localizacao
        FROM Equipamento e
        LEFT JOIN Categoria c ON e.codCategoria = c.codCategoria
        LEFT JOIN Localizacao l ON e.codLocalizacao = l.codLocalizacao
        ORDER BY e.codInventario
    ")->fetchAll(PDO::FETCH_OBJ);
    $categorias   = $ligacao->query("SELECT codCategoria, designacao FROM Categoria ORDER BY designacao")->fetchAll(PDO::FETCH_OBJ);
    $localizacoes = $ligacao->query("SELECT codLocalizacao, edificio, piso, servico FROM Localizacao ORDER BY edificio, servico")->fetchAll(PDO::FETCH_OBJ);
    $erro = '';
} catch (PDOException $err) {
    $erro = "Aconteceu um erro na ligação à base de dados.";
    $resultados = []; $categorias = []; $localizacoes = [];
}
$ligacao = null;

// Helper para valores do formulário (GET editar → DB; POST → $_POST; novo limpo → '')
$v = function(string $campo) use ($equipamentoEditar): string {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return (string)($_POST[$campo] ?? '');
    if ($equipamentoEditar !== null) return (string)($equipamentoEditar->$campo ?? '');
    return '';
};

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Equipamentos Médicos</h1>
            <p class="text-muted">Gestão e Inventário de Dispositivos Clínicos.</p>

            <?php if (!empty($erro_sistema)) : ?>
                <div class="alert alert-danger">
                    <strong>Erro:</strong> <?= htmlspecialchars($erro_sistema) ?>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-end mb-3">
                <button class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1d5370;"
                        data-bs-toggle="modal" data-bs-target="#modalNovoEquipamento">
                    <i class="fa-solid fa-plus me-2"></i>Novo Equipamento
                </button>
            </div>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <?php if (!empty($erro)) : ?>
                    <p class="text-center text-danger p-4"><?= $erro ?></p>
                <?php elseif (count($resultados) == 0) : ?>
                    <p class="text-muted p-4">Não existem equipamentos registados.</p>
                <?php else : ?>
                    <table class="table table-hover align-middle mb-0" id="tabelaEquipamentos">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center ps-3" style="color: #1d5370;">Cód. Inventário</th>
                                <th class="text-center" style="color: #1d5370;">Equipamento</th>
                                <th class="text-center" style="color: #1d5370;">Marca / Modelo</th>
                                <th class="text-center" style="color: #1d5370;">Nº de Série</th>
                                <th class="text-center" style="color: #1d5370;">Localização</th>
                                <th class="text-center" style="color: #1d5370;">Estado / Risco</th>
                                <th class="text-center pe-3" style="color: #1d5370;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $eq) : ?>
                            <?php
                                $estadoCor = match($eq->estado) {
                                    'Ativo'         => 'success',
                                    'Em manutencao' => 'warning',
                                    'Em calibracao' => 'info',
                                    'Em quarentena' => 'danger',
                                    'Abatido'       => 'dark',
                                    default         => 'secondary'
                                };
                                $criticidadeCor = match($eq->criticidade) {
                                    'Alta', 'Suporte de vida' => 'danger',
                                    'Media'                   => 'warning',
                                    default                   => 'success'
                                };
                            ?>
                            <tr>
                                <td class="text-center ps-3 fw-bold">#<?= str_pad($eq->codInventario, 3, '0', STR_PAD_LEFT) ?></td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($eq->designacao) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($eq->categoria ?? '-') ?></small>
                                </td>
                                <td class="text-center"><?= htmlspecialchars($eq->marca) ?> / <?= htmlspecialchars($eq->modelo) ?></td>
                                <td class="text-center"><code class="text-dark"><?= htmlspecialchars($eq->nrSerie) ?></code></td>
                                <td class="text-center"><?= htmlspecialchars($eq->localizacao ?? '-') ?></td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <span class="badge bg-<?= $estadoCor ?> mb-1"><?= htmlspecialchars($eq->estado) ?></span>
                                        <small class="text-<?= $criticidadeCor ?> fw-bold" style="font-size: 0.75rem; white-space: nowrap;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($eq->criticidade) ?>
                                        </small>
                                    </div>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <a href="equipamentos.php?ver=<?= aes_encrypt($eq->codInventario) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Consultar Ficha">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="equipamentos.php?editar=<?= aes_encrypt($eq->codInventario) ?>"
                                           class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="equipamentos.php?remover=<?= aes_encrypt($eq->codInventario) ?>"
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

<!-- Modal Equipamento (novo e editar) -->
<div class="modal fade" id="modalNovoEquipamento" tabindex="-1" aria-labelledby="modalNovoEquipamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white modal-header-hospital">
                <h5 class="modal-title fw-bold" id="modalNovoEquipamentoLabel">
                    <i class="fa-solid fa-microscope me-2"></i>
                    <?= $modoEditar ? 'Editar Equipamento' : 'Nova Ficha Técnica' ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?php if (!empty($erros)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Foram encontrados os seguintes erros:</strong>
                        <ul class="mb-0">
                            <?php foreach ($erros as $erro_msg) : ?>
                                <li><?= htmlspecialchars($erro_msg) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form id="formNovoEquipamento" method="post" action="#">
                    <input type="hidden" name="acao" value="<?= $modoEditar ? 'editar' : 'novo' ?>">
                    <?php if ($modoEditar && $idEncriptado) : ?>
                        <input type="hidden" name="idEncriptado" value="<?= htmlspecialchars($idEncriptado) ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-secondary small">Nome / Designação</label>
                            <input type="text" class="form-control" name="designacao"
                                   placeholder="Ex: Ventilador Clínico Avançado"
                                   value="<?= htmlspecialchars($v('designacao')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Categoria / Tipo</label>
                            <select class="form-select" name="codCategoria" required>
                                <option value="" disabled <?= $v('codCategoria') === '' ? 'selected' : '' ?>>Selecione...</option>
                                <?php foreach ($categorias as $cat) : ?>
                                    <option value="<?= $cat->codCategoria ?>"
                                        <?= $v('codCategoria') == $cat->codCategoria ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat->designacao) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Marca</label>
                            <input type="text" class="form-control" name="marca"
                                   placeholder="Ex: Dräger" value="<?= htmlspecialchars($v('marca')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Modelo</label>
                            <input type="text" class="form-control" name="modelo"
                                   placeholder="Ex: Evita V300" value="<?= htmlspecialchars($v('modelo')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Número de Série (SN)</label>
                            <input type="text" class="form-control" name="nrSerie"
                                   placeholder="Ex: SN-987654" value="<?= htmlspecialchars($v('nrSerie')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Fabricante</label>
                            <input type="text" class="form-control" name="fabricante"
                                   placeholder="Ex: Dräger Medical GmbH" value="<?= htmlspecialchars($v('fabricante')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Data de Aquisição</label>
                            <input type="date" class="form-control" name="dataAquisicao"
                                   value="<?= htmlspecialchars($v('dataAquisicao')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Ano de Fabrico</label>
                            <input type="number" class="form-control" name="anoFabrico"
                                   placeholder="Ex: 2022" min="1900" max="2099"
                                   value="<?= htmlspecialchars($v('anoFabrico')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Custo de Aquisição (€)</label>
                            <input type="number" class="form-control" name="custoAquisicao"
                                   placeholder="Ex: 15000" min="0" step="0.01"
                                   value="<?= htmlspecialchars($v('custoAquisicao')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Tipo de Entrada</label>
                            <select class="form-select" name="tipoEntrada" required>
                                <option value="" disabled <?= $v('tipoEntrada') === '' ? 'selected' : '' ?>>Selecione...</option>
                                <option value="compra"     <?= $v('tipoEntrada') === 'compra'     ? 'selected' : '' ?>>Compra</option>
                                <option value="doacao"     <?= $v('tipoEntrada') === 'doacao'     ? 'selected' : '' ?>>Doação</option>
                                <option value="aluguer"    <?= $v('tipoEntrada') === 'aluguer'    ? 'selected' : '' ?>>Aluguer</option>
                                <option value="emprestimo" <?= $v('tipoEntrada') === 'emprestimo' ? 'selected' : '' ?>>Empréstimo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Estado Atual</label>
                            <select class="form-select" name="estado" required>
                                <option value="" disabled <?= $v('estado') === '' ? 'selected' : '' ?>>Selecione...</option>
                                <option value="Ativo"         <?= $v('estado') === 'Ativo'         ? 'selected' : '' ?>>Ativo</option>
                                <option value="Em manutencao" <?= $v('estado') === 'Em manutencao' ? 'selected' : '' ?>>Em Manutenção</option>
                                <option value="Inativo"       <?= $v('estado') === 'Inativo'       ? 'selected' : '' ?>>Inativo</option>
                                <option value="Em calibracao" <?= $v('estado') === 'Em calibracao' ? 'selected' : '' ?>>Em Calibração</option>
                                <option value="Em quarentena" <?= $v('estado') === 'Em quarentena' ? 'selected' : '' ?>>Em Quarentena</option>
                                <option value="Abatido"       <?= $v('estado') === 'Abatido'       ? 'selected' : '' ?>>Abatido</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Localização Hospitalar</label>
                            <select class="form-select" name="codLocalizacao" required>
                                <option value="" disabled <?= $v('codLocalizacao') === '' ? 'selected' : '' ?>>Escolha a sala...</option>
                                <?php foreach ($localizacoes as $loc) : ?>
                                    <option value="<?= $loc->codLocalizacao ?>"
                                        <?= $v('codLocalizacao') == $loc->codLocalizacao ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loc->edificio . ' – ' . $loc->servico . ' (' . $loc->piso . 'º)') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Criticidade / Risco</label>
                            <select class="form-select" name="criticidade" required>
                                <option value="" disabled <?= $v('criticidade') === '' ? 'selected' : '' ?>>Selecione...</option>
                                <option value="Baixa"           <?= $v('criticidade') === 'Baixa'           ? 'selected' : '' ?>>Baixa</option>
                                <option value="Media"           <?= $v('criticidade') === 'Media'           ? 'selected' : '' ?>>Média</option>
                                <option value="Alta"            <?= $v('criticidade') === 'Alta'            ? 'selected' : '' ?>>Alta</option>
                                <option value="Suporte de vida" <?= $v('criticidade') === 'Suporte de vida' ? 'selected' : '' ?>>Suporte de Vida</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="2"
                                      placeholder="Notas adicionais..."><?= htmlspecialchars($v('observacoes')) ?></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formNovoEquipamento" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes Equipamento (Ficha 14 - Secção 1) -->
<div class="modal fade" id="modalDetalhesEquipamento" tabindex="-1" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white modal-header-hospital border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalDetalhesLabel">
                    <i class="fa-solid fa-info-circle me-2"></i>Ficha do Equipamento
                </h5>
                <span class="badge bg-light text-dark ms-auto me-3 px-3 py-2">
                    #<?= $equipamentoVer ? str_pad($equipamentoVer->codInventario, 3, '0', STR_PAD_LEFT) : '---' ?>
                </span>
                <a href="equipamentos.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#dados-pane" type="button">
                            <i class="fa-solid fa-box me-1"></i> Dados Gerais
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#localizacao-pane" type="button">
                            <i class="fa-solid fa-map-location-dot me-1"></i> Localização
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="dados-pane" tabindex="0">
                        <?php if ($equipamentoVer) :
                            $estadoCorVer = match($equipamentoVer->estado) {
                                'Ativo'         => 'success',
                                'Em manutencao' => 'warning',
                                'Em calibracao' => 'info',
                                'Em quarentena' => 'danger',
                                'Abatido'       => 'dark',
                                default         => 'secondary'
                            };
                            $critCorVer = match($equipamentoVer->criticidade) {
                                'Alta', 'Suporte de vida' => 'danger',
                                'Media'                   => 'warning',
                                default                   => 'success'
                            };
                        ?>
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="small text-muted d-block">Nome do Equipamento</label>
                                <strong class="fs-5 text-dark"><?= htmlspecialchars($equipamentoVer->designacao) ?></strong>
                                <span class="text-muted d-block small"><?= htmlspecialchars($equipamentoVer->nomeCategoria ?? '-') ?></span>
                            </div>
                            <div class="col-md-2 text-center">
                                <label class="small text-muted d-block">Estado</label>
                                <span class="badge bg-<?= $estadoCorVer ?>"><?= htmlspecialchars($equipamentoVer->estado) ?></span>
                            </div>
                            <div class="col-md-3 text-center">
                                <label class="small text-muted d-block">Criticidade</label>
                                <span class="badge bg-<?= $critCorVer ?>"><?= htmlspecialchars($equipamentoVer->criticidade) ?></span>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted d-block">Marca / Modelo</label>
                                <span class="fw-bold"><?= htmlspecialchars($equipamentoVer->marca . ' / ' . $equipamentoVer->modelo) ?></span>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted d-block">Nº de Série</label>
                                <code><?= htmlspecialchars($equipamentoVer->nrSerie) ?></code>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted d-block">Fabricante</label>
                                <span><?= htmlspecialchars($equipamentoVer->fabricante) ?></span>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted d-block">Tipo de Entrada</label>
                                <span><?= ucfirst(htmlspecialchars($equipamentoVer->tipoEntrada)) ?></span>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted d-block">Custo de Aquisição</label>
                                <span><?= $equipamentoVer->custoAquisicao ? '€ ' . number_format((float)$equipamentoVer->custoAquisicao, 2, ',', '.') : '-' ?></span>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted d-block">Data de Aquisição</label>
                                <span><?= $equipamentoVer->dataAquisicao ? date('d/m/Y', strtotime($equipamentoVer->dataAquisicao)) : '-' ?></span>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted d-block">Ano de Fabrico</label>
                                <span><?= htmlspecialchars((string)($equipamentoVer->anoFabrico ?? '-')) ?></span>
                            </div>
                            <?php if ($equipamentoVer->observacoes) : ?>
                            <div class="col-12">
                                <label class="small text-muted d-block">Observações</label>
                                <p class="text-muted mb-0"><?= htmlspecialchars($equipamentoVer->observacoes) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else : ?>
                        <p class="text-muted">Sem dados disponíveis.</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="localizacao-pane" tabindex="0">
                        <p class="text-dark mt-2">
                            <i class="fa-solid fa-map-pin text-danger me-2"></i>
                            Localização Atual:
                            <strong><?= $equipamentoVer ? htmlspecialchars($equipamentoVer->nomeLocalizacao ?? '-') : '-' ?></strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a href="equipamentos.php" class="btn btn-secondary px-4">Fechar Ficha</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação de Remoção (Ficha 14 - Secção 2) -->
<div class="modal fade" id="modalConfirmarRemocao" tabindex="-1" aria-labelledby="modalConfirmarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="modalConfirmarLabel">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Confirmar Remoção
                </h5>
                <a href="equipamentos.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fa-solid fa-trash fa-3x text-danger mb-3 d-block"></i>
                <p class="mb-1">Tem a certeza que pretende remover o equipamento?</p>
                <p class="fw-bold fs-5 mb-1"><?= htmlspecialchars($equipamentoRemover->designacao ?? '') ?></p>
                <p class="text-muted small mb-3"><?= htmlspecialchars($equipamentoRemover ? $equipamentoRemover->marca . ' / ' . $equipamentoRemover->modelo : '') ?></p>
                <p class="text-danger small mb-0"><i class="fa-solid fa-exclamation-triangle me-1"></i>Esta ação é permanente e não pode ser revertida.</p>
            </div>
            <div class="modal-footer bg-light">
                <a href="equipamentos.php" class="btn btn-secondary">
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
        $('#tabelaEquipamentos').DataTable({
            pageLength: 5,
            lengthChange: false,
            searching: false,
            paging: false,
            info: false,
            pagingType: "full_numbers",
            language: {
                decimal:        "",
                emptyTable:     "Sem equipamentos disponíveis.",
                info:           "Mostrando _START_ até _END_ de _TOTAL_ registos",
                infoEmpty:      "Mostrando 0 até 0 de 0 registos",
                infoFiltered:   "(filtrando _MAX_ total de registos)",
                infoPostFix:    "",
                thousands:      ",",
                lengthMenu:     "Mostrando _MENU_ registos por página.",
                loadingRecords: "Carregando...",
                processing:     "Processando...",
                search:         "Filtrar:",
                zeroRecords:    "Nenhum equipamento encontrado.",
                paginate: {
                    first:    "Primeira", last: "Última",
                    next:     "Seguinte", previous: "Anterior"
                },
                aria: {
                    sortAscending:  ": ativar para ordenar crescente.",
                    sortDescending: ": ativar para ordenar decrescente."
                }
            }
        });

        <?php if ($abrirModal) : ?>
        new bootstrap.Modal(document.getElementById('modalNovoEquipamento')).show();
        <?php endif; ?>
        <?php if ($abrirDetalheModal) : ?>
        new bootstrap.Modal(document.getElementById('modalDetalhesEquipamento')).show();
        <?php endif; ?>
        <?php if ($abrirConfirmarModal) : ?>
        new bootstrap.Modal(document.getElementById('modalConfirmarRemocao')).show();
        <?php endif; ?>
    });
</script>

<?php include 'includes/footer.php'; ?>
