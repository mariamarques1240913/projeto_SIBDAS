<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$erros = [];
$erro_sistema = "";
$abrirModal = false;

// Verificar se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") === "novo") {
    $abrirModal = true;

    // 1. Recolher dados
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

    // 2. Validar dados
    if (empty($designacao))    $erros[] = "A designação é obrigatória.";
    if (empty($codCategoria))  $erros[] = "A categoria é obrigatória.";
    if (empty($marca))         $erros[] = "A marca é obrigatória.";
    if (empty($modelo))        $erros[] = "O modelo é obrigatório.";
    if (empty($nrSerie))       $erros[] = "O número de série é obrigatório.";
    if (empty($fabricante))    $erros[] = "O fabricante é obrigatório.";
    if (empty($tipoEntrada))   $erros[] = "O tipo de entrada é obrigatório.";
    if (empty($estado))        $erros[] = "O estado é obrigatório.";
    if (empty($codLocalizacao)) $erros[] = "A localização é obrigatória.";
    if (empty($criticidade))   $erros[] = "A criticidade é obrigatória.";

    if (!empty($dataAquisicao) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAquisicao)) {
        $erros[] = "Formato de data inválido (use AAAA-MM-DD).";
    }

    // 3. Se não houver erros, guardar na base de dados
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );
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
                ':codCategoria'   => $codCategoria,
                ':codLocalizacao' => $codLocalizacao,
                ':designacao'     => $designacao,
                ':marca'          => $marca,
                ':modelo'         => $modelo,
                ':nrSerie'        => $nrSerie,
                ':fabricante'     => $fabricante,
                ':dataAquisicao'  => $dataAquisicao ?: null,
                ':anoFabrico'     => $anoFabrico ?: null,
                ':custoAquisicao' => $custoAquisicao ?: null,
                ':tipoEntrada'    => $tipoEntrada,
                ':estado'         => $estado,
                ':criticidade'    => $criticidade,
                ':observacoes'    => $observacoes ?: null,
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

// Carregar dados para listagem e selects
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
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
    $resultados  = [];
    $categorias  = [];
    $localizacoes = [];
}

$ligacao = null;

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
                    <strong>Erro:</strong>
                    <p><?= htmlspecialchars($erro_sistema) ?></p>
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
                <?php else : ?>
                    <?php if (count($resultados) == 0) : ?>
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
                                        'Ativo'          => 'success',
                                        'Em manutencao'  => 'warning',
                                        'Em calibracao'  => 'info',
                                        'Em quarentena'  => 'danger',
                                        'Abatido'        => 'dark',
                                        default          => 'secondary'
                                    };
                                    $criticidadeCor = match($eq->criticidade) {
                                        'Alta', 'Suporte de vida' => 'danger',
                                        'Media'                  => 'warning',
                                        default                  => 'success'
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
                                            <button class="btn btn-sm btn-outline-secondary me-1" title="Consultar Ficha"
                                                    onclick="verDetalhes(<?= $eq->codInventario ?>)" data-bs-toggle="modal" data-bs-target="#modalDetalhesEquipamento">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary me-1" title="Editar"
                                                    onclick="configurarModalParaEditar(<?= $eq->codInventario ?>)" data-bs-toggle="modal" data-bs-target="#modalNovoEquipamento">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Remover"
                                                    onclick="confirmarRemocao(<?= $eq->codInventario ?>)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <footer class="pt-4 mt-5 text-muted border-top">
                &copy; 2026 Hospitally &middot; Sistema de Gestão Hospitalar Privado
            </footer>
        </main>
    </div>
</div>

<!-- Modal Novo Equipamento -->
<div class="modal fade" id="modalNovoEquipamento" tabindex="-1" aria-labelledby="modalNovoEquipamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white modal-header-hospital">
                <h5 class="modal-title fw-bold" id="modalNovoEquipamentoLabel">
                    <i class="fa-solid fa-microscope me-2"></i>Ficha Técnica do Equipamento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

                <!-- Área de erros -->
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
                    <input type="hidden" name="acao" value="novo">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-secondary small">Nome / Designação</label>
                            <input type="text" class="form-control" id="eq-nome" name="designacao"
                                   placeholder="Ex: Ventilador Clínico Avançado"
                                   value="<?= htmlspecialchars($_POST['designacao'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Categoria / Tipo</label>
                            <select class="form-select" id="eq-categoria" name="codCategoria" required>
                                <option value="" disabled <?= empty($_POST['codCategoria']) ? 'selected' : '' ?>>Selecione...</option>
                                <?php foreach ($categorias as $cat) : ?>
                                    <option value="<?= $cat->codCategoria ?>"
                                        <?= (($_POST['codCategoria'] ?? '') == $cat->codCategoria) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat->designacao) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Marca</label>
                            <input type="text" class="form-control" id="eq-marca" name="marca"
                                   placeholder="Ex: Dräger"
                                   value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Modelo</label>
                            <input type="text" class="form-control" id="eq-modelo" name="modelo"
                                   placeholder="Ex: Evita V300"
                                   value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Número de Série (SN)</label>
                            <input type="text" class="form-control" id="eq-serie" name="nrSerie"
                                   placeholder="Ex: SN-987654"
                                   value="<?= htmlspecialchars($_POST['nrSerie'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Fabricante</label>
                            <input type="text" class="form-control" id="eq-fabricante" name="fabricante"
                                   placeholder="Ex: Dräger Medical GmbH"
                                   value="<?= htmlspecialchars($_POST['fabricante'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Data de Aquisição</label>
                            <input type="date" class="form-control" id="eq-data" name="dataAquisicao"
                                   value="<?= htmlspecialchars($_POST['dataAquisicao'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Ano de Fabrico</label>
                            <input type="number" class="form-control" id="eq-ano" name="anoFabrico"
                                   placeholder="Ex: 2022" min="1900" max="2099"
                                   value="<?= htmlspecialchars($_POST['anoFabrico'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Custo de Aquisição (€)</label>
                            <input type="number" class="form-control" id="eq-custo" name="custoAquisicao"
                                   placeholder="Ex: 15000" min="0" step="0.01"
                                   value="<?= htmlspecialchars($_POST['custoAquisicao'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Tipo de Entrada</label>
                            <select class="form-select" id="eq-tipo" name="tipoEntrada" required>
                                <option value="" disabled <?= empty($_POST['tipoEntrada']) ? 'selected' : '' ?>>Selecione...</option>
                                <option value="compra"     <?= (($_POST['tipoEntrada'] ?? '') === 'compra')     ? 'selected' : '' ?>>Compra</option>
                                <option value="doacao"     <?= (($_POST['tipoEntrada'] ?? '') === 'doacao')     ? 'selected' : '' ?>>Doação</option>
                                <option value="aluguer"    <?= (($_POST['tipoEntrada'] ?? '') === 'aluguer')    ? 'selected' : '' ?>>Aluguer</option>
                                <option value="emprestimo" <?= (($_POST['tipoEntrada'] ?? '') === 'emprestimo') ? 'selected' : '' ?>>Empréstimo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Estado Atual</label>
                            <select class="form-select" id="eq-estado" name="estado" required>
                                <option value="" disabled <?= empty($_POST['estado']) ? 'selected' : '' ?>>Selecione...</option>
                                <option value="Ativo"          <?= (($_POST['estado'] ?? '') === 'Ativo')          ? 'selected' : '' ?>>Ativo</option>
                                <option value="Em manutencao"  <?= (($_POST['estado'] ?? '') === 'Em manutencao')  ? 'selected' : '' ?>>Em Manutenção</option>
                                <option value="Inativo"        <?= (($_POST['estado'] ?? '') === 'Inativo')        ? 'selected' : '' ?>>Inativo</option>
                                <option value="Em calibracao"  <?= (($_POST['estado'] ?? '') === 'Em calibracao')  ? 'selected' : '' ?>>Em Calibração</option>
                                <option value="Em quarentena"  <?= (($_POST['estado'] ?? '') === 'Em quarentena')  ? 'selected' : '' ?>>Em Quarentena</option>
                                <option value="Abatido"        <?= (($_POST['estado'] ?? '') === 'Abatido')        ? 'selected' : '' ?>>Abatido</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Localização Hospitalar</label>
                            <select class="form-select" id="eq-localizacao" name="codLocalizacao" required>
                                <option value="" disabled <?= empty($_POST['codLocalizacao']) ? 'selected' : '' ?>>Escolha a sala...</option>
                                <?php foreach ($localizacoes as $loc) : ?>
                                    <option value="<?= $loc->codLocalizacao ?>"
                                        <?= (($_POST['codLocalizacao'] ?? '') == $loc->codLocalizacao) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loc->edificio . ' – ' . $loc->servico . ' (' . $loc->piso . 'º)') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Criticidade / Risco</label>
                            <select class="form-select" id="eq-criticidade" name="criticidade" required>
                                <option value="" disabled <?= empty($_POST['criticidade']) ? 'selected' : '' ?>>Selecione...</option>
                                <option value="Baixa"          <?= (($_POST['criticidade'] ?? '') === 'Baixa')          ? 'selected' : '' ?>>Baixa</option>
                                <option value="Media"          <?= (($_POST['criticidade'] ?? '') === 'Media')          ? 'selected' : '' ?>>Média</option>
                                <option value="Alta"           <?= (($_POST['criticidade'] ?? '') === 'Alta')           ? 'selected' : '' ?>>Alta</option>
                                <option value="Suporte de vida" <?= (($_POST['criticidade'] ?? '') === 'Suporte de vida') ? 'selected' : '' ?>>Suporte de Vida</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Observações</label>
                            <textarea class="form-control" id="eq-obs" name="observacoes" rows="2"
                                      placeholder="Notas adicionais sobre o equipamento..."><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
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

<!-- Modal Detalhes Equipamento -->
<div class="modal fade" id="modalDetalhesEquipamento" tabindex="-1" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white modal-header-hospital border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalDetalhesLabel">
                    <i class="fa-solid fa-info-circle me-2"></i>Ficha do Equipamento
                </h5>
                <span class="badge bg-light text-dark ms-auto me-3 px-3 py-2" id="badgeInventario">#001</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-tabs mb-4" id="equipamentoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados-pane" type="button" role="tab">
                            <i class="fa-solid fa-box me-1"></i> Dados Gerais
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="garantia-tab" data-bs-toggle="tab" data-bs-target="#garantia-pane" type="button" role="tab">
                            <i class="fa-solid fa-file-signature me-1"></i> Garantia e Contrato
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="localizacao-tab" data-bs-toggle="tab" data-bs-target="#localizacao-pane" type="button" role="tab">
                            <i class="fa-solid fa-map-location-dot me-1"></i> Localização
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fornecedor-tab" data-bs-toggle="tab" data-bs-target="#fornecedor-pane" type="button" role="tab">
                            <i class="fa-solid fa-truck me-1"></i> Fornecedor
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="documento-tab" data-bs-toggle="tab" data-bs-target="#documento-pane" type="button" role="tab">
                            <i class="fa-solid fa-file-medical me-1"></i> Documentação
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="equipamentoTabsContent">
                    <div class="tab-pane fade show active" id="dados-pane" role="tabpanel" tabindex="0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small text-muted d-block">Nome do Equipamento</label>
                                <strong class="fs-5 text-dark" id="detalheNome">-</strong>
                                <span class="text-muted d-block small" id="detalheSubtitulo">-</span>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted d-block">Marca / Modelo</label>
                                <span class="fw-bold text-dark" id="detalheModelo">-</span>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted d-block">Nº de Série</label>
                                <span class="fw-bold text-dark" id="detalheSerie">-</span>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="small text-muted d-block">Criticidade</label>
                                <span class="badge bg-danger" id="detalheCriticidade">-</span>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="small text-muted d-block">Estado Operacional</label>
                                <span class="badge bg-success" id="detalheEstado">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="garantia-pane" role="tabpanel" tabindex="0">
                        <p class="text-muted">Sem informação de garantia registada.</p>
                    </div>
                    <div class="tab-pane fade" id="localizacao-pane" role="tabpanel" tabindex="0">
                        <p class="text-dark"><i class="fa-solid fa-map-pin text-danger me-2"></i>Localização Atual: <strong id="detalheLocalizacao">-</strong></p>
                    </div>
                    <div class="tab-pane fade" id="fornecedor-pane" role="tabpanel" tabindex="0">
                        <p class="text-dark"><i class="fa-solid fa-building text-primary me-2"></i>Fornecedor Registado: <strong id="detalheFornecedor">-</strong></p>
                    </div>
                    <div class="tab-pane fade" id="documento-pane" role="tabpanel" tabindex="0">
                        <p class="text-muted">Sem documentos associados.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Fechar Ficha</button>
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
                    first:    "Primeira",
                    last:     "Última",
                    next:     "Seguinte",
                    previous: "Anterior"
                },
                aria: {
                    sortAscending:  ": ativar para ordenar crescente.",
                    sortDescending: ": ativar para ordenar decrescente."
                }
            }
        });

        <?php if ($abrirModal && (!empty($erros) || !empty($erro_sistema))) : ?>
        var modal = new bootstrap.Modal(document.getElementById('modalNovoEquipamento'));
        modal.show();
        <?php endif; ?>
    });
</script>

<?php include 'includes/footer.php'; ?>
