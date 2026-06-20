<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Painel de Controlo</h1>
            <p class="text-muted">Área de Gestão do Inventário Hospitalar.</p>

            <div class="row g-3 mt-2 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded p-3 h-100 bg-white" style="border-left: 5px solid #a2c2cf !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Equipamentos</h6>
                                <span class="h3 fw-bold text-dark">142</span>
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
                                <span class="h3 fw-bold text-dark">5</span>
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
                                <span class="h3 fw-bold text-dark">2</span>
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
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Manuais PDF</h6>
                                <span class="h3 fw-bold text-dark">28</span>
                            </div>
                            <div class="fs-2 opacity-75" style="color: #b2dfdb;">
                                <i class="fa-solid fa-file-medical"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded p-4 bg-white">
                        <h5 class="fw-bold mb-3" style="color: #1d5370;">
                            <i class="fa-solid fa-circle-half-stroke me-2" style="color: #a2c2cf;"></i>Estado do Inventário
                        </h5>
                        <p class="text-muted small">Percentagem de equipamentos disponíveis versus imobilizados.</p>
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

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded p-4 bg-white">
                        <h5 class="fw-bold mb-3" style="color: #1d5370;">
                            <i class="fa-solid fa-bell me-2" style="color: #ffb3ba;"></i>Alertas Críticos e Manutenções Urgentes
                        </h5>
                        <p class="text-muted small">Intervenções técnicas e prazos de garantia que requerem atenção imediata.</p>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Equipamento</th>
                                        <th class="text-center">Serviço</th>
                                        <th class="text-center">Tipo de Ocorrência</th>
                                        <th class="text-center">Data Limite</th>
                                        <th class="text-center">Criticidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold text-center">#EQ-0412</td>
                                        <td class="text-center">Ventilador Pulmonar Oxylog</td>
                                        <td class="text-center">UCI</td>
                                        <td class="text-center">Calibração Preventiva</td>
                                        <td class="text-center">18/06/2026</td>
                                        <td class="text-center"><span class="badge rounded-pill px-3 py-2" style="background-color: #ffb3ba; color: #721c24;">Alta</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-center">#EQ-0922</td>
                                        <td class="text-center">Monitor Sinais Vitais</td>
                                        <td class="text-center">Urgências</td>
                                        <td class="text-center">Fim de Garantia</td>
                                        <td class="text-center">22/06/2026</td>
                                        <td class="text-center"><span class="badge rounded-pill px-3 py-2" style="background-color: #ffc6ff; color: #4a154b;">Média</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-center">#EQ-0115</td>
                                        <td class="text-center">Desfibrilhador Lifepak</td>
                                        <td class="text-center">Bloco Operatório</td>
                                        <td class="text-center">Substituição de Bateria</td>
                                        <td class="text-center">30/06/2026</td>
                                        <td class="text-center"><span class="badge rounded-pill px-3 py-2" style="background-color: #b2dfdb; color: #0e433e;">Normal</span></td>
                                    </tr>
                                </tbody>
                            </table>
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
