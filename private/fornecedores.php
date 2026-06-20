<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Fornecedores</h1>
            <p class="text-muted">Gestão de Contactos e Fabricantes de Dispositivos Médicos.</p>

            <div class="row g-3 align-items-center mt-3 mb-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="inputPesquisa" oninput="filtrarTabela()" class="form-control border-start-0 ps-0" placeholder="Pesquisar fornecedor...">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-8 text-md-end">
                    <button class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1d5370;" data-bs-toggle="modal" data-bs-target="#modalNovoFornecedor" onclick="configurarModalParaNovo()">
                        <i class="fa-solid fa-plus me-2"></i>Novo Fornecedor
                    </button>
                </div>
            </div>

            <div class="table-responsive bg-white rounded shadow-sm border">
                <table class="table table-hover align-middle mb-0" id="tabelaFornecedores">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center ps-3" style="color: #1d5370; width: 100px;">ID</th>
                            <th class="text-center" style="color: #1d5370;">Nome da Empresa</th>
                            <th class="text-center" style="color: #1d5370;">Contacto Telefónico</th>
                            <th class="text-center" style="color: #1d5370;">E-mail</th>
                            <th class="text-center" style="color: #1d5370;">NIF</th>
                            <th class="text-center pe-3" style="color: #1d5370; width: 160px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-bold text-secondary id-celula">1</td>
                            <td class="text-center nome-celula">Dräger Portugal Lda.</td>
                            <td class="text-center telefone-celula">214 907 500</td>
                            <td class="text-center email-celula">assistencia.pt@drager.com</td>
                            <td class="text-center nif-celula">501234567</td>
                            <td class="text-center">
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

<div class="modal fade" id="modalNovoFornecedor" tabindex="-1" aria-labelledby="modalNovoFornecedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #1d5370;">
                <h5 class="modal-title fw-bold" id="modalNovoFornecedorLabel">
                    <i class="fa-solid fa-truck-field me-2"></i>Registar Novo Fornecedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formNovoFornecedor" onsubmit="salvarFormulario(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Nome da Empresa</label>
                            <input type="text" id="inputNome" class="form-control" placeholder="Ex: Dräger Portugal Lda." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">NIF (Número de Identificação Fiscal)</label>
                            <input type="text" id="inputNif" class="form-control" placeholder="Ex: 501234567" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Contacto Telefónico</label>
                            <input type="tel" id="inputTelefone" class="form-control" placeholder="Ex: 214 907 500" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">E-mail de Contacto</label>
                            <input type="email" id="inputEmail" class="form-control" placeholder="Ex: contacto@empresa.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Tipo de Dispositivos / Especialidade</label>
                            <input type="text" id="inputEspecialidade" class="form-control" placeholder="Ex: Ventiladores e Bloco Operatório">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Morada / Sede</label>
                            <input type="text" id="inputMorada" class="form-control" placeholder="Ex: Rua da Tecnologia, Lisboa">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Website</label>
                            <input type="url" id="inputWebsite" class="form-control" placeholder="Ex: https://www.empresa.pt">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Pessoa de Contacto</label>
                            <input type="text" id="inputPessoaContacto" class="form-control" placeholder="Ex: João Silva">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Telefone de Contacto Direto</label>
                            <input type="tel" id="inputTelefoneContacto" class="form-control" placeholder="Ex: 912 345 678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">Tipo de Fornecedor</label>
                            <select id="inputTipoFornecedor" class="form-select">
                                <option value="" selected disabled>Selecione...</option>
                                <option value="Fabricante">Fabricante</option>
                                <option value="Distribuidor">Distribuidor</option>
                                <option value="Prestador de Serviços">Prestador de Serviços</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small">Observações</label>
                            <textarea id="inputObservacoes" class="form-control" rows="2" placeholder="Notas adicionais sobre o fornecedor..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formNovoFornecedor" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">Salvar Registo</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
