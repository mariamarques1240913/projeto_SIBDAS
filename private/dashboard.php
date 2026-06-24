<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";

try {
    $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // KPIs: contagens simples
    $totalEquipamentos    = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE eliminado=0")->fetchColumn();
    $emManutencao         = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE eliminado=0 AND estado = 'Em manutencao'")->fetchColumn();
    $garantiasAlerta      = $ligacao->query("SELECT COUNT(*) FROM Garantia WHERE eliminado=0 AND dataFim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
    $totalDocumentos      = $ligacao->query("SELECT COUNT(*) FROM Documento WHERE eliminado=0")->fetchColumn();
    // KPIs de alerta
    $equipamentosInativos = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE eliminado=0 AND estado != 'Ativo'")->fetchColumn();
    $garantiasExpiradas   = $ligacao->query("SELECT COUNT(*) FROM Garantia WHERE eliminado=0 AND dataFim < CURDATE()")->fetchColumn();
    $semDocumentacao      = $ligacao->query("SELECT COUNT(*) FROM Equipamento e WHERE eliminado=0 AND NOT EXISTS (SELECT 1 FROM Documento d WHERE d.codInventario = e.codInventario AND d.eliminado=0)")->fetchColumn();

    // Gráfico donut: contagem por estado
    $dadosEstado = $ligacao->query("SELECT estado, COUNT(*) AS total FROM Equipamento WHERE eliminado=0 GROUP BY estado ORDER BY total DESC")->fetchAll(PDO::FETCH_OBJ);

    // Gráfico barras: contagem por serviço (top 6)
    $dadosServico = $ligacao->query("
        SELECT l.servico, COUNT(*) AS total
        FROM Equipamento e
        LEFT JOIN Localizacao l ON e.codLocalizacao = l.codLocalizacao
        WHERE e.eliminado=0 AND l.servico IS NOT NULL
        GROUP BY l.servico
        ORDER BY total DESC
        LIMIT 6
    ")->fetchAll(PDO::FETCH_OBJ);

    // Tabela de alertas: equipamentos fora de serviço + garantias a expirar em 30 dias
    $alertas = $ligacao->query("
        SELECT e.designacao, e.estado AS ocorrencia, l.servico, e.criticidade, 'estado' AS tipoAlerta
        FROM Equipamento e
        LEFT JOIN Localizacao l ON e.codLocalizacao = l.codLocalizacao
        WHERE e.eliminado=0 AND e.estado != 'Ativo'
        UNION ALL
        SELECT e.designacao,
               CONCAT('Garantia expira em ', DATEDIFF(g.dataFim, CURDATE()), ' dia(s)') AS ocorrencia,
               l.servico, e.criticidade, 'garantia' AS tipoAlerta
        FROM Garantia g
        LEFT JOIN Equipamento e ON g.codInventario = e.codInventario
        LEFT JOIN Localizacao l ON e.codLocalizacao = l.codLocalizacao
        WHERE g.eliminado=0 AND g.dataFim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        LIMIT 10
    ")->fetchAll(PDO::FETCH_OBJ);

    $erro = '';
} catch (PDOException $e) {
    $erro = "Erro ao ligar à base de dados.";
    $totalEquipamentos = 0; $emManutencao = 0; $garantiasAlerta = 0; $totalDocumentos = 0;
    $equipamentosInativos = 0; $garantiasExpiradas = 0; $semDocumentacao = 0;
    $dadosEstado = []; $dadosServico = []; $alertas = [];
}
$ligacao = null;

// Preparar dados para os gráficos em formato JSON (para passar ao JavaScript)
$chartEstadoLabels = array_map(fn($r) => $r->estado, $dadosEstado);
$chartEstadoData   = array_map(fn($r) => (int)$r->total, $dadosEstado);

$coresEstado = ['Ativo' => '#b2dfdb', 'Em manutencao' => '#ffb3ba', 'Em calibracao' => '#a2c2cf', 'Em quarentena' => '#ffc6ff', 'Abatido' => '#b0bec5', 'Inativo' => '#d3d3d3'];
$chartEstadoCores = array_map(fn($r) => $coresEstado[$r->estado] ?? '#e0e0e0', $dadosEstado);

$chartServicoLabels = array_map(fn($r) => $r->servico ?? 'Sem localização', $dadosServico);
$chartServicoData   = array_map(fn($r) => (int)$r->total, $dadosServico);

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Painel de Controlo</h1>
            <p class="text-muted">Área de Gestão do Inventário Hospitalar.</p>

            <?php if (!empty($erro)) : ?>
                <div class="alert alert-warning"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- KPIs com dados reais da BD -->
            <div class="row g-3 mt-2 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded p-3 h-100 bg-white" style="border-left: 5px solid #a2c2cf !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Equipamentos</h6>
                                <span class="h3 fw-bold text-dark"><?= $totalEquipamentos ?></span>
                            </div>
                            <div class="fs-2 opacity-75" style="color: #a2c2cf;">
                                <i class="fa-solid fa-microscope"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded p-3 h-100 bg-white" style="border-left: 5px solid #ffb3ba !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Em Manutenção</h6>
                                <span class="h3 fw-bold text-dark"><?= $emManutencao ?></span>
                            </div>
                            <div class="fs-2 opacity-75" style="color: #ffb3ba;">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded p-3 h-100 bg-white" style="border-left: 5px solid #b39ddb !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Garantias Alerta</h6>
                                <span class="h3 fw-bold text-dark"><?= $garantiasAlerta ?></span>
                            </div>
                            <div class="fs-2 opacity-75" style="color: #b39ddb;">
                                <i class="fa-solid fa-file-circle-exclamation"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded p-3 h-100 bg-white" style="border-left: 5px solid #b2dfdb !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Documentos</h6>
                                <span class="h3 fw-bold text-dark"><?= $totalDocumentos ?></span>
                            </div>
                            <div class="fs-2 opacity-75" style="color: #b2dfdb;">
                                <i class="fa-solid fa-file-medical"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs de alerta -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded p-3 h-100 bg-white" style="border-left: 5px solid #ef9a9a !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Equipamentos Inativos</h6>
                                <span class="h3 fw-bold <?= $equipamentosInativos > 0 ? 'text-danger' : 'text-dark' ?>"><?= $equipamentosInativos ?></span>
                                <div class="small text-muted mt-1">estado diferente de "Ativo"</div>
                            </div>
                            <div class="fs-2 opacity-75" style="color: #ef9a9a;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded p-3 h-100 bg-white" style="border-left: 5px solid #ffcc80 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Garantias Expiradas</h6>
                                <span class="h3 fw-bold <?= $garantiasExpiradas > 0 ? 'text-warning' : 'text-dark' ?>"><?= $garantiasExpiradas ?></span>
                                <div class="small text-muted mt-1">data de fim anterior a hoje</div>
                            </div>
                            <div class="fs-2 opacity-75" style="color: #ffcc80;">
                                <i class="fa-solid fa-calendar-xmark"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded p-3 h-100 bg-white" style="border-left: 5px solid #ce93d8 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Sem Documentação</h6>
                                <span class="h3 fw-bold <?= $semDocumentacao > 0 ? 'text-danger' : 'text-dark' ?>"><?= $semDocumentacao ?></span>
                                <div class="small text-muted mt-1">equipamentos sem ficheiros</div>
                            </div>
                            <div class="fs-2 opacity-75" style="color: #ce93d8;">
                                <i class="fa-solid fa-file-circle-xmark"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row g-4 mt-2">
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded p-4 bg-white">
                        <h5 class="fw-bold mb-3" style="color: #1d5370;">
                            <i class="fa-solid fa-circle-half-stroke me-2" style="color: #a2c2cf;"></i>Estado do Inventário
                        </h5>
                        <p class="text-muted small">Distribuição de equipamentos por estado atual.</p>
                        <div style="max-height: 260px; position: relative;">
                            <canvas id="graficoEstado"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded p-4 bg-white">
                        <h5 class="fw-bold mb-3" style="color: #1d5370;">
                            <i class="fa-solid fa-chart-bar me-2" style="color: #b2dfdb;"></i>Equipamentos por Serviço
                        </h5>
                        <p class="text-muted small">Volume de inventário alocado em cada ala do hospital.</p>
                        <div style="max-height: 260px; position: relative;">
                            <canvas id="graficoServicos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de alertas com dados reais -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded p-4 bg-white">
                        <h5 class="fw-bold mb-3" style="color: #1d5370;">
                            <i class="fa-solid fa-bell me-2" style="color: #ffb3ba;"></i>Alertas Críticos e Manutenções Urgentes
                        </h5>
                        <p class="text-muted small">Equipamentos fora de serviço e garantias a expirar nos próximos 30 dias.</p>
                        <div class="table-responsive">
                            <?php if (empty($alertas)) : ?>
                                <p class="text-success text-center py-3">
                                    <i class="fa-solid fa-circle-check me-2"></i>Sem alertas ativos. Tudo em ordem!
                                </p>
                            <?php else : ?>
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Equipamento</th>
                                        <th class="text-center">Serviço</th>
                                        <th class="text-center">Ocorrência</th>
                                        <th class="text-center">Criticidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alertas as $alerta) :
                                        $critCor = match($alerta->criticidade ?? '') {
                                            'Alta', 'Suporte de vida' => 'danger',
                                            'Media'                   => 'warning',
                                            default                   => 'secondary'
                                        };
                                        $alertaCor = $alerta->tipoAlerta === 'garantia' ? 'warning' : 'danger';
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-center"><?= htmlspecialchars($alerta->designacao ?? '-') ?></td>
                                        <td class="text-center text-muted"><?= htmlspecialchars($alerta->servico ?? '-') ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $alertaCor ?>">
                                                <?= htmlspecialchars($alerta->ocorrencia) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $critCor ?>">
                                                <?= htmlspecialchars($alerta->criticidade ?? '-') ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="pt-4 mt-5 text-muted border-top">
                &copy; 2026 Hospitally &middot; Sistema de Gestão Hospitalar Privado
            </footer>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Inicialização dos gráficos com dados reais da BD.
     Este script corre depois do 1240913.js e sobrepõe a função initDashboard(). -->
<script>
function initDashboard() {
    // Dados vindos do PHP via json_encode()
    var estadoLabels = <?= json_encode($chartEstadoLabels) ?>;
    var estadoData   = <?= json_encode($chartEstadoData) ?>;
    var estadoCores  = <?= json_encode($chartEstadoCores) ?>;

    var servicoLabels = <?= json_encode($chartServicoLabels) ?>;
    var servicoData   = <?= json_encode($chartServicoData) ?>;

    // Gráfico donut — estado do inventário
    var ctxEstado = document.getElementById('graficoEstado').getContext('2d');
    new Chart(ctxEstado, {
        type: 'doughnut',
        data: {
            labels: estadoLabels,
            datasets: [{
                data: estadoData,
                backgroundColor: estadoCores,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, font: { family: 'Segoe UI', size: 12 } }
                }
            }
        }
    });

    // Gráfico barras — equipamentos por serviço
    var ctxServicos = document.getElementById('graficoServicos').getContext('2d');
    new Chart(ctxServicos, {
        type: 'bar',
        data: {
            labels: servicoLabels,
            datasets: [{
                label: 'Equipamentos',
                data: servicoData,
                backgroundColor: ['#a2c2cf', '#ffb3ba', '#b39ddb', '#b2dfdb', '#ffc6ff', '#ffe0b2'],
                borderRadius: 5,
                borderWidth: 0
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Segoe UI' } } },
                y: { grid: { display: false }, ticks: { font: { family: 'Segoe UI' } } }
            }
        }
    });
}
</script>
