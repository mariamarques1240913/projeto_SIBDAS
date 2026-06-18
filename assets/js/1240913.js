
// ---- funções que são usadas em várias páginas ----

function apagarLinha(botao) {
    botao.closest('tr').remove();
}

// pesquisa genérica - funciona em qualquer tabela da app
function filtrarTabela() {
    const input = document.getElementById('inputPesquisa');
    if (!input) return;
    const termo = input.value.toLowerCase();
    document.querySelectorAll('table tbody tr').forEach(function(linha) {
        linha.style.display = linha.textContent.toLowerCase().includes(termo) ? '' : 'none';
    });
}


// ---- login ----

function validarLogin(event) {
    event.preventDefault();
    const utilizador = document.getElementById('utilizador').value.trim();
    const password = document.getElementById('password').value.trim();
    const mensagemErro = document.getElementById('mensagemErro');
    if (utilizador === 'admin' && password === 'admin') {
        window.location.href = '../private/dashboard.html';
    } else {
        mensagemErro.classList.remove('d-none');
    }
}


// ---- quando a página carrega, vejo em que página estou e inicializo o que for preciso ----

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('graficoEstado'))      initDashboard();
    if (document.getElementById('tabelaEquipamentos')) initEquipamentos();
    if (document.getElementById('tabelaFornecedores')) initFornecedores();
    if (document.getElementById('tabelaLocalizacoes')) initLocalizacoes();
    if (document.getElementById('tabelaDocumentos'))   initDocumentacao();
    if (document.getElementById('tabelaGarantias'))    initGarantias();
    if (document.getElementById('tabelaFAQs'))         initGestaoPortal();
});


// ---- dashboard - os dois gráficos com Chart.js ----

function initDashboard() {
    // gráfico de donuts - estado do inventário
    const ctxEstado = document.getElementById('graficoEstado').getContext('2d');
    new Chart(ctxEstado, {
        type: 'doughnut',
        data: {
            labels: ['Operacionais', 'Em Manutenção', 'Avariados'],
            datasets: [{
                data: [132, 5, 5],
                backgroundColor: ['#b2dfdb', '#ffb3ba', '#b39ddb'],
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

    // gráfico de barras - equipamentos por serviço
    const ctxServicos = document.getElementById('graficoServicos').getContext('2d');
    new Chart(ctxServicos, {
        type: 'bar',
        data: {
            labels: ['Urgências', 'Bloco Operatório', 'UCI', 'Radiologia', 'Consultas'],
            datasets: [{
                label: 'Equipamentos',
                data: [45, 38, 22, 25, 12],
                backgroundColor: ['#a2c2cf', '#ffb3ba', '#b39ddb', '#b2dfdb', '#ffc6ff'],
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


// ---- equipamentos ----

function initEquipamentos() {
    const modalDetalhes = new bootstrap.Modal(document.getElementById('modalDetalhesEquipamento'));

    // ao clicar em "Consultar Ficha" preencho o modal com os dados da linha
    document.querySelectorAll('[title="Consultar Ficha"]').forEach(function(botao) {
        botao.addEventListener('click', function() {
            const toggleLinha = this.closest('tr');

            const codInventario   = toggleLinha.cells[0].innerText.trim();
            const nomeEquipamento = toggleLinha.cells[1].querySelector('.fw-bold').innerText.trim();
            const subTexto        = toggleLinha.cells[1].querySelector('.text-muted').innerText.trim();
            const marcaModelo     = toggleLinha.cells[2].innerText.trim();
            const numSerie        = toggleLinha.cells[3].innerText.trim();
            const localizacao     = toggleLinha.cells[4].innerText.trim();

            // dados do fornecedor dependem do equipamento (hardcoded por agora)
            if (codInventario === '#001') {
                document.getElementById('detalheEntidade').innerText = 'Dräger Portugal Lda.';
                document.getElementById('detalheFornecedor').innerText = 'Dräger Medical Solutions (suporte@draeger.pt)';
            } else {
                document.getElementById('detalheEntidade').innerText = 'Zoll Medical Iberia';
                document.getElementById('detalheFornecedor').innerText = 'Zoll Lifeline Distribution (contacto@zoll.pt)';
            }

            document.getElementById('badgeInventario').innerText    = codInventario;
            document.getElementById('detalheNome').innerText        = nomeEquipamento;
            document.getElementById('detalheSubtitulo').innerText   = subTexto;
            document.getElementById('detalheModelo').innerText      = marcaModelo;
            document.getElementById('detalheSerie').innerText       = numSerie;
            document.getElementById('detalheLocalizacao').innerText = localizacao;

            // abro sempre no primeiro separador
            const primeiroTab = new bootstrap.Tab(document.getElementById('dados-tab'));
            primeiroTab.show();

            modalDetalhes.show();
        });
    });

    // botão remover - apaga a linha da tabela
    document.querySelectorAll('[title="Remover"]').forEach(function(botao) {
        botao.addEventListener('click', function() {
            this.closest('tr').remove();
        });
    });
}


// ---- fornecedores ----

function initFornecedores() {
    let linhaSendoEditada = null;
    const modalBootstrap = new bootstrap.Modal(document.getElementById('modalNovoFornecedor'));

    window.editarLinha = function(botao) {
        linhaSendoEditada = botao.closest('tr');

        const nome     = linhaSendoEditada.querySelector('.nome-celula').innerText;
        const telefone = linhaSendoEditada.querySelector('.telefone-celula').innerText;
        const email    = linhaSendoEditada.querySelector('.email-celula').innerText;
        const nif      = linhaSendoEditada.querySelector('.nif-celula').innerText;

        document.getElementById('inputNome').value      = nome;
        document.getElementById('inputTelefone').value  = telefone;
        document.getElementById('inputEmail').value     = email;
        document.getElementById('inputNif').value       = nif;
        document.getElementById('inputEspecialidade').value = '';
        document.getElementById('inputMorada').value   = '';

        document.getElementById('modalNovoFornecedorLabel').innerHTML = '<i class="fa-solid fa-truck-field me-2"></i>Editar Fornecedor';
        modalBootstrap.show();
    };

    window.configurarModalParaNovo = function() {
        linhaSendoEditada = null;
        document.getElementById('formNovoFornecedor').reset();
        document.getElementById('modalNovoFornecedorLabel').innerHTML = '<i class="fa-solid fa-truck-field me-2"></i>Registar Novo Fornecedor';
    };

    window.salvarFormulario = function(event) {
        event.preventDefault();

        const nome     = document.getElementById('inputNome').value;
        const telefone = document.getElementById('inputTelefone').value;
        const email    = document.getElementById('inputEmail').value;
        const nif      = document.getElementById('inputNif').value;

        if (linhaSendoEditada) {
            // atualiza os dados da linha que estava a ser editada
            linhaSendoEditada.querySelector('.nome-celula').innerText     = nome;
            linhaSendoEditada.querySelector('.telefone-celula').innerText = telefone;
            linhaSendoEditada.querySelector('.email-celula').innerText    = email;
            linhaSendoEditada.querySelector('.nif-celula').innerText      = nif;
        } else {
            // cria uma nova linha na tabela
            const tabela    = document.getElementById('tabelaFornecedores').getElementsByTagName('tbody')[0];
            const proximoId = tabela.rows.length + 1;
            const novaLinha = tabela.insertRow();
            novaLinha.innerHTML = `
                <td class="text-center fw-bold text-secondary id-celula">${proximoId}</td>
                <td class="text-center nome-celula">${nome}</td>
                <td class="text-center telefone-celula">${telefone}</td>
                <td class="text-center email-celula">${email}</td>
                <td class="text-center nif-celula">${nif}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" title="Editar" onclick="editarLinha(this)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-outline-danger" title="Apagar" onclick="apagarLinha(this)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>`;
        }

        modalBootstrap.hide();
    };
}


// ---- localizações ----

function initLocalizacoes() {
    let linhaSendoEditada = null;
    const modalBootstrap = new bootstrap.Modal(document.getElementById('modalNovaLocalizacao'));

    window.editarLinha = function(botao) {
        linhaSendoEditada = botao.closest('tr');

        const edificio = linhaSendoEditada.querySelector('.edificio-celula').innerText;
        const piso     = linhaSendoEditada.querySelector('.piso-celula').innerText;
        const codigo   = linhaSendoEditada.querySelector('.codigo-celula').innerText;
        const servico  = linhaSendoEditada.querySelector('.servico-celula').innerText;
        const sala     = linhaSendoEditada.querySelector('.sala-celula').innerText;

        document.getElementById('inputEdificio').value = edificio;
        document.getElementById('inputPiso').value     = piso;
        document.getElementById('inputCodigo').value   = codigo;
        document.getElementById('inputSala').value     = sala;

        const select = document.getElementById('selectServico');
        select.value = select.querySelector(`option[value="${servico}"]`) ? servico : '';

        document.getElementById('modalNovaLocalizacaoLabel').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Editar Localização';
        document.getElementById('btnSalvarModal').innerText = 'Guardar Alterações';
        modalBootstrap.show();
    };

    window.configurarModalParaNovo = function() {
        linhaSendoEditada = null;
        document.getElementById('formNovaLocalizacao').reset();
        document.getElementById('modalNovaLocalizacaoLabel').innerHTML = '<i class="fa-solid fa-map-location-dot me-2"></i>Registar Nova Localização';
        document.getElementById('btnSalvarModal').innerText = 'Salvar Localização';
        modalBootstrap.show();
    };

    window.salvarFormulario = function(event) {
        event.preventDefault();

        const edificio = document.getElementById('inputEdificio').value;
        const piso     = document.getElementById('inputPiso').value;
        const codigo   = document.getElementById('inputCodigo').value;
        const servico  = document.getElementById('selectServico').value;
        const sala     = document.getElementById('inputSala').value;

        if (linhaSendoEditada) {
            linhaSendoEditada.querySelector('.edificio-celula').innerText = edificio;
            linhaSendoEditada.querySelector('.piso-celula').innerText     = piso;
            linhaSendoEditada.querySelector('.codigo-celula').innerText   = codigo;
            linhaSendoEditada.querySelector('.servico-celula').innerText  = servico;
            linhaSendoEditada.querySelector('.sala-celula').innerText     = sala;
        } else {
            const tabela    = document.getElementById('tabelaLocalizacoes').getElementsByTagName('tbody')[0];
            const proximoId = tabela.rows.length + 1;
            const novaLinha = tabela.insertRow();
            novaLinha.innerHTML = `
                <td class="text-center fw-bold text-secondary id-celula">${proximoId}</td>
                <td class="text-center edificio-celula">${edificio}</td>
                <td class="text-center piso-celula">${piso}</td>
                <td class="text-center codigo-celula">${codigo}</td>
                <td class="text-center servico-celula">${servico}</td>
                <td class="text-center sala-celula">${sala}</td>
                <td class="text-center pe-3">
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" title="Editar" onclick="editarLinha(this)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-outline-danger" title="Apagar" onclick="apagarLinha(this)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>`;
        }

        modalBootstrap.hide();
    };
}


// ---- documentação técnica ----

function initDocumentacao() {
    let linhaSendoEditada = null;
    const modalBootstrap = new bootstrap.Modal(document.getElementById('modalSubmeterDocumento'));

    // mapeamento do tipo de documento para o badge correspondente
    const mapeamentoBadges = {
        'Manual':    { texto: 'Manual do Utilizador',                 classe: 'bg-primary' },
        'Guia':      { texto: 'Guia de Consulta Rápida',              classe: 'bg-success' },
        'Relatorio': { texto: 'Relatório de Calibração / Manutenção', classe: 'bg-warning text-dark' }
    };

    window.editarLinha = function(botao) {
        linhaSendoEditada = botao.closest('tr');

        const nomeEquipamento = linhaSendoEditada.querySelector('.nome-equipamento').innerText;
        const refEquipamento  = linhaSendoEditada.querySelector('.ref-equipamento').innerText;
        const tipoValor       = linhaSendoEditada.querySelector('.tipo-celula').getAttribute('data-tipo-valor');

        document.getElementById('inputEquipamento').value = `${nomeEquipamento} (${refEquipamento.replace('Inventário: ', '')})`;
        document.getElementById('selectTipo').value = tipoValor;

        // ficheiro não é obrigatório ao editar
        document.getElementById('inputFicheiro').required = false;
        document.getElementById('labelFicheiro').innerText = 'Substituir Ficheiro (Opcional)';

        document.getElementById('modalSubmeterDocumentoLabel').innerHTML = '<i class="fa-solid fa-paperclip me-2"></i>Editar Documento Técnico';
        modalBootstrap.show();
    };

    window.configurarModalParaNovo = function() {
        linhaSendoEditada = null;
        document.getElementById('formSubmeterDocumento').reset();
        document.getElementById('inputFicheiro').required = true;
        document.getElementById('labelFicheiro').innerText = 'Escolher Ficheiro';
        document.getElementById('modalSubmeterDocumentoLabel').innerHTML = '<i class="fa-solid fa-paperclip me-2"></i>Submeter Novo Documento';
    };

    window.salvarFormulario = function(event) {
        event.preventDefault();

        const equipamentoValor = document.getElementById('inputEquipamento').value;
        const tipoValor        = document.getElementById('selectTipo').value;
        const inputFich        = document.getElementById('inputFicheiro');

        // separo o nome do equipamento do código de inventário
        let nomeEquip = equipamentoValor;
        let refEquip  = 'Inventário: #---';
        if (equipamentoValor.includes('(')) {
            const partes = equipamentoValor.split('(');
            nomeEquip = partes[0].trim();
            refEquip  = 'Inventário: ' + partes[1].replace(')', '').trim();
        }

        const infoBadge = mapeamentoBadges[tipoValor] || { texto: tipoValor, classe: 'bg-secondary' };

        const hoje = new Date();
        const dataFormatada = String(hoje.getDate()).padStart(2, '0') + '/' + String(hoje.getMonth() + 1).padStart(2, '0') + '/' + hoje.getFullYear();

        if (linhaSendoEditada) {
            linhaSendoEditada.querySelector('.nome-equipamento').innerText = nomeEquip;
            linhaSendoEditada.querySelector('.ref-equipamento').innerText  = refEquip;

            const celulaTipo = linhaSendoEditada.querySelector('.tipo-celula');
            celulaTipo.setAttribute('data-tipo-valor', tipoValor);
            celulaTipo.innerHTML = `<span class="badge ${infoBadge.classe} px-3 py-2">${infoBadge.texto}</span>`;

            if (inputFich.files.length > 0) {
                linhaSendoEditada.querySelector('.nome-real-ficheiro').innerText = inputFich.files[0].name;
                linhaSendoEditada.querySelector('.data-celula').innerText = dataFormatada;
            }
        } else {
            const tabela    = document.getElementById('tabelaDocumentos').getElementsByTagName('tbody')[0];
            const proximoId = tabela.rows.length + 1;
            const nomeFicheiro = inputFich.files.length > 0 ? inputFich.files[0].name : 'sem_ficheiro.pdf';

            const novaLinha = tabela.insertRow();
            novaLinha.innerHTML = `
                <td class="text-center fw-bold text-secondary id-celula">${proximoId}</td>
                <td class="text-center equipamento-celula">
                    <div class="fw-bold text-dark nome-equipamento">${nomeEquip}</div>
                    <small class="text-muted ref-equipamento">${refEquip}</small>
                </td>
                <td class="text-center tipo-celula" data-tipo-valor="${tipoValor}">
                    <span class="badge ${infoBadge.classe} px-3 py-2">${infoBadge.texto}</span>
                </td>
                <td class="text-center ficheiro-celula">
                    <span class="text-dark"><i class="fa-solid fa-file-pdf text-danger me-2"></i><span class="nome-real-ficheiro">${nomeFicheiro}</span></span>
                </td>
                <td class="text-center text-muted data-celula">${dataFormatada}</td>
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
                </td>`;
        }

        modalBootstrap.hide();
    };
}


// ---- garantias e contratos ----

function initGarantias() {
    let linhaSendoEditada = null;
    const modalBootstrap = new bootstrap.Modal(document.getElementById('modalRegistarGarantia'));

    const mapeamentoBadgesGarantia = {
        'Fabrica':     { texto: 'Garantia de Fábrica',  classe: 'bg-primary' },
        'Assistencia': { texto: 'Contrato Assistência', classe: 'text-white', estilo: 'background-color: #6f42c1;' },
        'Extensao':    { texto: 'Extensão de Garantia', classe: 'bg-info text-dark' }
    };

    // converte data do formato ISO (2024-06-10) para o formato PT (10/06/2024)
    function formatarDataBR(dataString) {
        if (!dataString) return '';
        const partes = dataString.split('-');
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    // verifica se a garantia ainda está dentro do prazo
    function calcularEstadoGarantia(dataFimString) {
        const dataFim = new Date(dataFimString);
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        dataFim.setHours(0, 0, 0, 0);

        if (dataFim >= hoje) {
            return `<span class="badge bg-success px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i> Ativa</span>`;
        } else {
            return `<span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-circle-xmark me-1"></i> Expirada</span>`;
        }
    }

    window.apagarLinha = function(botao) {
        botao.closest('tr').remove();
    };

    window.editarLinha = function(botao) {
        linhaSendoEditada = botao.closest('tr');

        const nomeEquipamento = linhaSendoEditada.querySelector('.nome-equipamento').innerText;
        const refEquipamento  = linhaSendoEditada.querySelector('.ref-equipamento').innerText;
        const tipoValor       = linhaSendoEditada.querySelector('.tipo-celula').getAttribute('data-tipo-valor');
        const celulaPeriodo   = linhaSendoEditada.cells[3];
        const dataInicio      = celulaPeriodo.getAttribute('data-inicio');
        const dataFim         = celulaPeriodo.getAttribute('data-fim');

        document.getElementById('inputEquipamentoGarantia').value = `${nomeEquipamento} (${refEquipamento.replace('Inventário: ', '')})`;
        document.getElementById('selectTipoGarantia').value = tipoValor;
        document.getElementById('inputDataInicio').value    = dataInicio;
        document.getElementById('inputDataFim').value       = dataFim;

        document.getElementById('modalRegistarGarantiaLabel').innerHTML = '<i class="fa-solid fa-file-signature me-2"></i>Editar Cobertura';
        modalBootstrap.show();
    };

    window.configurarModalParaNovo = function() {
        linhaSendoEditada = null;
        document.getElementById('formRegistarGarantia').reset();
        document.getElementById('modalRegistarGarantiaLabel').innerHTML = '<i class="fa-solid fa-file-signature me-2"></i>Registar Nova Cobertura';
    };

    window.salvarFormulario = function(event) {
        event.preventDefault();

        const equipamentoValor = document.getElementById('inputEquipamentoGarantia').value;
        const tipoValor  = document.getElementById('selectTipoGarantia').value;
        const dataInicio = document.getElementById('inputDataInicio').value;
        const dataFim    = document.getElementById('inputDataFim').value;

        let nomeEquip = equipamentoValor;
        let refEquip  = 'Inventário: #---';
        if (equipamentoValor.includes('(')) {
            const partes = equipamentoValor.split('(');
            nomeEquip = partes[0].trim();
            refEquip  = 'Inventário: ' + partes[1].replace(')', '').trim();
        }

        const infoBadge      = mapeamentoBadgesGarantia[tipoValor] || { texto: tipoValor, classe: 'bg-secondary' };
        const estiloAtributo = infoBadge.estilo ? `style="${infoBadge.estilo}"` : '';
        const htmlEstado     = calcularEstadoGarantia(dataFim);
        const periodoTexto   = `${formatarDataBR(dataInicio)} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> ${formatarDataBR(dataFim)}`;

        if (linhaSendoEditada) {
            linhaSendoEditada.querySelector('.nome-equipamento').innerText = nomeEquip;
            linhaSendoEditada.querySelector('.ref-equipamento').innerText  = refEquip;

            const celulaTipo = linhaSendoEditada.querySelector('.tipo-celula');
            celulaTipo.setAttribute('data-tipo-valor', tipoValor);
            celulaTipo.innerHTML = `<span class="badge ${infoBadge.classe} px-3 py-2" ${estiloAtributo}>${infoBadge.texto}</span>`;

            const celulaPeriodo = linhaSendoEditada.cells[3];
            celulaPeriodo.setAttribute('data-inicio', dataInicio);
            celulaPeriodo.setAttribute('data-fim', dataFim);
            celulaPeriodo.innerHTML = periodoTexto;

            linhaSendoEditada.cells[4].innerHTML = htmlEstado;
        } else {
            const tabela    = document.getElementById('tabelaGarantias').getElementsByTagName('tbody')[0];
            const proximoId = tabela.rows.length + 1;

            const novaLinha = tabela.insertRow();
            novaLinha.innerHTML = `
                <td class="text-center fw-bold text-secondary id-celula">${proximoId}</td>
                <td class="text-center equipamento-celula">
                    <div class="fw-bold text-dark nome-equipamento">${nomeEquip}</div>
                    <small class="text-muted ref-equipamento">${refEquip}</small>
                </td>
                <td class="text-center tipo-celula" data-tipo-valor="${tipoValor}">
                    <span class="badge ${infoBadge.classe} px-3 py-2" ${estiloAtributo}>${infoBadge.texto}</span>
                </td>
                <td class="text-center text-dark" data-inicio="${dataInicio}" data-fim="${dataFim}">
                    ${periodoTexto}
                </td>
                <td class="text-center">${htmlEstado}</td>
                <td class="text-center pe-3">
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-success" title="Visualizar Termos/Contrato">
                            <i class="fa-solid fa-download"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarLinha(this)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Apagar" onclick="apagarLinha(this)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>`;
        }

        modalBootstrap.hide();
    };
}


// ---- gestão do portal público (backoffice das FAQs) ----

function initGestaoPortal() {
    let linhaFAQEditada = null;
    const modalFAQBootstrap = new bootstrap.Modal(document.getElementById('modalFAQ'));

    window.abrirModalFAQ = function() {
        linhaFAQEditada = null;
        document.getElementById('formFAQ').reset();
        document.getElementById('modalFAQLabel').innerText = 'Nova Pergunta Frequente';
        modalFAQBootstrap.show();
    };

    window.editarFAQ = function(botao) {
        linhaFAQEditada = botao.closest('tr');
        const pergunta = linhaFAQEditada.querySelector('.faq-pergunta').innerText;
        const resposta = linhaFAQEditada.querySelector('.faq-resposta').innerText;

        document.getElementById('inputPerguntaFAQ').value = pergunta;
        document.getElementById('inputRespostaFAQ').value = resposta;

        document.getElementById('modalFAQLabel').innerText = 'Editar Pergunta Frequente';
        modalFAQBootstrap.show();
    };

    window.apagarFAQ = function(botao) {
        botao.closest('tr').remove();
    };

    window.salvarFormularioFAQ = function(event) {
        event.preventDefault();
        const pergunta = document.getElementById('inputPerguntaFAQ').value;
        const resposta = document.getElementById('inputRespostaFAQ').value;

        if (linhaFAQEditada) {
            linhaFAQEditada.querySelector('.faq-pergunta').innerText = pergunta;
            linhaFAQEditada.querySelector('.faq-resposta').innerText = resposta;
        } else {
            const tabela    = document.getElementById('tabelaFAQs').getElementsByTagName('tbody')[0];
            const novaLinha = tabela.insertRow();
            novaLinha.innerHTML = `
                <td class="ps-3">
                    <div class="fw-bold text-dark faq-pergunta">${pergunta}</div>
                    <small class="text-muted d-block text-truncate faq-resposta" style="max-width: 280px;">${resposta}</small>
                </td>
                <td class="text-center pe-3">
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editarFAQ(this)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Apagar" onclick="apagarFAQ(this)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>`;
        }
        modalFAQBootstrap.hide();
    };

    // botão "Guardar Todas as Alterações" - mostra o toast de confirmação
    window.salvarTudoGlobal = function() {
        const toastEl = document.getElementById('toastGlobal');
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    };
}
