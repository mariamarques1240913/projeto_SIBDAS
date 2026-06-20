<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Documentação Técnica</h1>
            <p class="text-muted">Manuais, Manutenções e Guias de Operação.</p>

            <div class="row g-3 align-items-center mt-3 mb-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="inputPesquisa" oninput="filtrarTabela()" class="form-control border-start-0 ps-0" placeholder="Pesquisar documentos...">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-8 text-md-end">
                    <button class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1d5370;" data-bs-toggle="modal" data-bs-target="#modalSubmeterDocumento" onclick="configurarModalParaNovo()">
                        <i class="fa-solid fa-paperclip me-2"></i>Submeter Documento
                    </button>
                </div>
            </div>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <table class="table table-hover align-middle mb-0" id="tabelaDocumentos">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center ps-3" style="color: #1d5370; width: 80px;">ID</th>
                            <th class="text-center" style="color: #1d5370;">Equipamento Associado</th>
                            <th class="text-center" style="color: #1d5370;">Tipo de Documento</th>
                            <th class="text-center" style="color: #1d5370;">Nome do Ficheiro</th>
                            <th class="text-center" style="color: #1d5370; width: 140px;">Data Upload</th>
                            <th class="text-center pe-3" style="color: #1d5370; width: 160px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-bold text-secondary id-celula">1</td>
                            <td class="text-center equipamento-celula">
                                <div class="fw-bold text-dark nome-equipamento">Ventilador Pulmonar</div>
                                <small class="text-muted ref-equipamento">Inventário: #001</small>
                            </td>
                            <td class="text-center tipo-celula" data-tipo-valor="Manual">
                                <span class="badge bg-primary px-3 py-2">Manual do Utilizador</span>
                            </td>
                            <td class="text-center ficheiro-celula">
                                <span class="text-dark"><i class="fa-solid fa-file-pdf text-danger me-2"></i><span class="nome-real-ficheiro">manual_mindray_v3.pdf</span></span>
                            </td>
                            <td class="text-center text-muted data-celula">10/06/2026</td>
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <button class="btn btn-sm btn-outline-success" title="Visualizar / Download">
                                        <i class="fa-solid fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarLinha(this)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Apagar" onclick="apagarLinha(this)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center fw-bold text-secondary id-celula">2</td>
                            <td class="text-center equipamento-celula">
                                <div class="fw-bold text-dark nome-equipamento">Desfibrilhador Automático</div>
                                <small class="text-muted ref-equipamento">Inventário: #002</small>
                            </td>
                            <td class="text-center tipo-celula" data-tipo-valor="Guia">
                                <span class="badge bg-success px-3 py-2">Guia de Consulta Rápida</span>
                            </td>
                            <td class="text-center ficheiro-celula">
                                <span class="text-dark"><i class="fa-solid fa-file-pdf text-danger me-2"></i><span class="nome-real-ficheiro">guia_rapido_zoll.pdf</span></span>
                            </td>
                            <td class="text-center text-muted data-celula">10/06/2026</td>
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <button class="btn btn-sm btn-outline-success" title="Visualizar / Download">
                                        <i class="fa-solid fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarLinha(this)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Apagar" onclick="apagarLinha(this)">
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

<div class="modal fade" id="modalSubmeterDocumento" tabindex="-1" aria-labelledby="modalSubmeterDocumentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #1d5370;">
                <h5 class="modal-title fw-bold" id="modalSubmeterDocumentoLabel">
                    <i class="fa-solid fa-paperclip me-2"></i>Submeter Novo Documento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formSubmeterDocumento" onsubmit="salvarFormulario(event)">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Equipamento Associado</label>
                        <input type="text" class="form-control" id="inputEquipamento" placeholder="Ex: Ventilador Pulmonar (#001)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Tipo de Documento</label>
                        <select class="form-select" id="selectTipo" required>
                            <option value="" selected disabled>Selecione o tipo...</option>
                            <option value="Manual">Manual do Utilizador</option>
                            <option value="Guia">Guia de Consulta Rápida</option>
                            <option value="Relatorio">Relatório de Calibração / Manutenção</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-secondary small" id="labelFicheiro">Escolher Ficheiro</label>
                        <input type="file" class="form-control" id="inputFicheiro" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formSubmeterDocumento" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">Submeter Ficheiro</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
