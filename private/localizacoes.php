<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Localizações Hospitalares</h1>
            <p class="text-muted">Gestão de Edifícios, Pisos e Unidades Clínicas.</p>

            <div class="row g-3 align-items-center mt-3 mb-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="inputPesquisa" oninput="filtrarTabela()" class="form-control border-start-0 ps-0" placeholder="Pesquisar localização...">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-8 text-md-end">
                    <button class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1d5370;" onclick="configurarModalParaNovo()">
                        <i class="fa-solid fa-plus me-2"></i>Nova Localização
                    </button>
                </div>
            </div>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <table class="table table-hover align-middle mb-0" id="tabelaLocalizacoes">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center ps-3" style="color: #1d5370; width: 80px;">ID</th>
                            <th class="text-center" style="color: #1d5370;">Edifício</th>
                            <th class="text-center" style="color: #1d5370;">Piso</th>
                            <th class="text-center" style="color: #1d5370;">Código</th>
                            <th class="text-center" style="color: #1d5370;">Serviço / Departamento</th>
                            <th class="text-center" style="color: #1d5370;">Sala / Gabinete</th>
                            <th class="text-center pe-3" style="color: #1d5370; width: 140px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-bold text-secondary id-celula">1</td>
                            <td class="text-center edificio-celula">Edifício Central (A)</td>
                            <td class="text-center piso-celula">Piso 2</td>
                            <td class="text-center codigo-celula">S-202</td>
                            <td class="text-center servico-celula">Bloco Operatório</td>
                            <td class="text-center sala-celula">Sala de Cirurgia 2</td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" title="Editar" onclick="editarLinha(this)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" title="Apagar" onclick="apagarLinha(this)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center fw-bold text-secondary id-celula">2</td>
                            <td class="text-center edificio-celula">Edifício Geral (B)</td>
                            <td class="text-center piso-celula">Piso 0</td>
                            <td class="text-center codigo-celula">R-01</td>
                            <td class="text-center servico-celula">Urgência</td>
                            <td class="text-center sala-celula">Sala de Reanimação</td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" title="Editar" onclick="editarLinha(this)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" title="Apagar" onclick="apagarLinha(this)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer class="pt-4 mt-5 text-muted border-top">
                &copy; 2026 Hospitally &middot; Sistema de Gestão Hospitalar Privado
            </footer>
        </main>
    </div>
</div>

<div class="modal fade" id="modalNovaLocalizacao" tabindex="-1" aria-labelledby="modalNovaLocalizacaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #1d5370;" id="modalHeader">
                <h5 class="modal-title fw-bold" id="modalNovaLocalizacaoLabel">
                    <i class="fa-solid fa-map-location-dot me-2"></i>Registar Nova Localização
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formNovaLocalizacao" onsubmit="salvarFormulario(event)">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Edifício / Ala</label>
                        <input type="text" id="inputEdificio" class="form-control" placeholder="Ex: Edifício Central (A)" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Piso</label>
                            <input type="text" id="inputPiso" class="form-control" placeholder="Ex: Piso 2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Código da Sala</label>
                            <input type="text" id="inputCodigo" class="form-control" placeholder="Ex: S-204">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Serviço Associado</label>
                        <select id="selectServico" class="form-select" required>
                            <option value="" selected disabled>Selecione o serviço...</option>
                            <option value="Bloco Operatório">Bloco Operatório</option>
                            <option value="Urgência">Urgência</option>
                            <option value="UCI">UCI</option>
                            <option value="Imagiologia">Imagiologia</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-secondary small">Descrição da Sala / Gabinete</label>
                        <input type="text" id="inputSala" class="form-control" placeholder="Ex: Sala de Cirurgia 2" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formNovaLocalizacao" id="btnSalvarModal" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">Salvar Localização</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
