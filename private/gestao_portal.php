<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();
verificar_perfil(['administrador']);

$dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";
$erro_sistema = '';
$abrirModalFAQ = false;
$faqEditar = null;
$modoEditarFAQ = false;

// POST: salvar conteúdo de texto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_conteudo') {
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("INSERT INTO ConteudoPortal (chave, valor) VALUES (:k, :v) ON DUPLICATE KEY UPDATE valor = :v");
        foreach (['quem_somos', 'missao', 'telefone', 'email', 'morada', 'horario'] as $k) {
            $stmt->execute([':k' => $k, ':v' => trim($_POST[$k] ?? '')]);
        }
        $ligacao = null;
        header('Location: gestao_portal.php?sucesso=1');
        exit;
    } catch (PDOException $e) {
        $erro_sistema = "Erro ao guardar conteúdo.";
        $ligacao = null;
    }
}

// POST: nova FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'nova_faq') {
    $pergunta = trim($_POST['pergunta'] ?? '');
    $resposta  = trim($_POST['resposta']  ?? '');
    if ($pergunta && $resposta) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $ligacao->prepare("INSERT INTO FAQ (pergunta, resposta) VALUES (?, ?)")->execute([$pergunta, $resposta]);
            $ligacao = null;
            header('Location: gestao_portal.php?sucesso=1');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = "Erro ao adicionar FAQ.";
            $abrirModalFAQ = true;
            $ligacao = null;
        }
    } else {
        $erro_sistema = "A pergunta e a resposta são obrigatórias.";
        $abrirModalFAQ = true;
    }
}

// POST: editar FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'editar_faq') {
    $id       = (int)($_POST['codFAQ'] ?? 0);
    $pergunta = trim($_POST['pergunta'] ?? '');
    $resposta  = trim($_POST['resposta']  ?? '');
    if ($id && $pergunta && $resposta) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $ligacao->prepare("UPDATE FAQ SET pergunta = ?, resposta = ? WHERE codFAQ = ?")->execute([$pergunta, $resposta, $id]);
            $ligacao = null;
            header('Location: gestao_portal.php?sucesso=1');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = "Erro ao editar FAQ.";
            $ligacao = null;
        }
    }
}

// POST: remover FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'remover_faq') {
    $id = (int)($_POST['codFAQ'] ?? 0);
    if ($id) {
        try {
            $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $ligacao->prepare("DELETE FROM FAQ WHERE codFAQ = ?")->execute([$id]);
            $ligacao = null;
            header('Location: gestao_portal.php?sucesso=1');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = "Erro ao remover FAQ.";
            $ligacao = null;
        }
    }
}

// GET: carregar FAQ para editar
if (isset($_GET['editar_faq'])) {
    $id = (int)$_GET['editar_faq'];
    try {
        $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
        $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $ligacao->prepare("SELECT * FROM FAQ WHERE codFAQ = ?");
        $stmt->execute([$id]);
        $faqEditar = $stmt->fetch(PDO::FETCH_OBJ);
        $ligacao = null;
        if ($faqEditar) { $abrirModalFAQ = true; $modoEditarFAQ = true; }
    } catch (PDOException $e) { $ligacao = null; }
}

// Carregar conteúdo da BD
try {
    $ligacao = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conteudo = $ligacao->query("SELECT chave, valor FROM ConteudoPortal")->fetchAll(PDO::FETCH_KEY_PAIR);
    $faqs     = $ligacao->query("SELECT * FROM FAQ ORDER BY codFAQ")->fetchAll(PDO::FETCH_OBJ);
    $ligacao  = null;
} catch (PDOException $e) {
    $conteudo = []; $faqs = [];
}

$cp = fn($key, $default = '') => htmlspecialchars($conteudo[$key] ?? $default);

include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <h1 class="h3 fw-bold" style="color: #1d5370;">Gestão do Portal Público</h1>
            <p class="text-muted">Administração de conteúdos informativos, institucionais e de contacto apresentados no site exterior.</p>

            <?php if (!empty($erro_sistema)) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <form method="POST" action="gestao_portal.php">
                <input type="hidden" name="acao" value="salvar_conteudo">

                <div class="row g-4 mt-2">
                    <div class="col-12 col-xl-7">

                        <div class="card border shadow-sm rounded bg-white mb-4">
                            <div class="card-header bg-light py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold" style="color: #1d5370;">
                                    <i class="fa-solid fa-address-card me-2"></i>Conteúdo Institucional "Quem Somos"
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary small">Texto do Quem Somos</label>
                                    <textarea class="form-control" name="quem_somos" rows="3" required><?= $cp('quem_somos', 'O Hospitally é uma unidade hospitalar de referência...') ?></textarea>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold text-secondary small">Texto da Nossa Missão</label>
                                    <textarea class="form-control" name="missao" rows="3" required><?= $cp('missao', 'Garantir uma gestão eficiente e inovadora...') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card border shadow-sm rounded bg-white">
                            <div class="card-header bg-light py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold" style="color: #1d5370;">
                                    <i class="fa-solid fa-map-pin me-2"></i>Informações de Contacto e Morada
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold text-secondary small">Linha Telefónica Geral</label>
                                        <input type="text" class="form-control" name="telefone" value="<?= $cp('telefone', '+351 225 913 028') ?>" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold text-secondary small">E-mail Institucional</label>
                                        <input type="email" class="form-control" name="email" value="<?= $cp('email', 'geral@hospitally.pt') ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary small">Endereço / Morada Física</label>
                                    <input type="text" class="form-control" name="morada" value="<?= $cp('morada', 'Avenida da Boavista, nº 2045, 4100-131 Porto, Portugal') ?>" required>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold text-secondary small">Horário de Funcionamento Geral</label>
                                    <input type="text" class="form-control" name="horario" value="<?= $cp('horario', '2ª a 6ª Feira: 7h — 21h | Sábado e Feriados: 9h — 15h | Domingo: Encerrado') ?>" required>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-12 col-xl-5">
                        <div class="card border shadow-sm rounded bg-white" style="min-height: 100%;">
                            <div class="card-header bg-light py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0 fw-bold" style="color: #1d5370;">
                                    <i class="fa-solid fa-circle-question me-2"></i>Perguntas Frequentes (FAQs)
                                </h5>
                                <a href="gestao_portal.php" class="btn btn-sm text-white px-3 fw-bold" style="background-color: #1d5370;"
                                   onclick="event.preventDefault(); document.getElementById('modalFAQ').classList.add('show'); document.getElementById('modalFAQ').style.display='block'; document.body.classList.add('modal-open');">
                                    <i class="fa-solid fa-plus me-1"></i> Nova FAQ
                                </a>
                            </div>
                            <div class="card-body p-4">
                                <div class="table-responsive border rounded">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3" style="color: #1d5370;">Pergunta / Resposta</th>
                                                <th class="text-center pe-3" style="color: #1d5370; width: 100px;">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($faqs)) : ?>
                                                <tr><td colspan="2" class="text-center text-muted p-3">Sem FAQs registadas.</td></tr>
                                            <?php else : ?>
                                                <?php foreach ($faqs as $faq) : ?>
                                                <tr>
                                                    <td class="ps-3">
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($faq->pergunta) ?></div>
                                                        <small class="text-muted d-block text-truncate" style="max-width: 280px;"><?= htmlspecialchars($faq->resposta) ?></small>
                                                    </td>
                                                    <td class="text-center pe-3">
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <a href="gestao_portal.php?editar_faq=<?= $faq->codFAQ ?>"
                                                               class="btn btn-sm btn-outline-primary" title="Editar">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </a>
                                                            <form method="POST" action="gestao_portal.php" class="d-inline"
                                                                  onsubmit="return confirm('Remover esta FAQ?');">
                                                                <input type="hidden" name="acao" value="remover_faq">
                                                                <input type="hidden" name="codFAQ" value="<?= $faq->codFAQ ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Apagar">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4 mb-5">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn text-white fw-bold btn-lg px-5 shadow-sm" style="background-color: #1d5370;">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Guardar Todas as Alterações
                        </button>
                    </div>
                </div>
            </form>

            <footer class="pt-4 mt-5 text-muted border-top">
                &copy; 2026 Hospitally &middot; Sistema de Gestão Hospitalar Privado
            </footer>
        </main>
    </div>
</div>

<!-- Modal FAQ (nova / editar) -->
<div class="modal fade" id="modalFAQ" tabindex="-1" aria-labelledby="modalFAQLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #1d5370;">
                <h5 class="modal-title fw-bold" id="modalFAQLabel">
                    <?= $modoEditarFAQ ? 'Editar Pergunta Frequente' : 'Nova Pergunta Frequente' ?>
                </h5>
                <a href="gestao_portal.php" class="btn-close btn-close-white" aria-label="Close"></a>
            </div>
            <form method="POST" action="gestao_portal.php">
                <input type="hidden" name="acao" value="<?= $modoEditarFAQ ? 'editar_faq' : 'nova_faq' ?>">
                <?php if ($modoEditarFAQ && $faqEditar) : ?>
                    <input type="hidden" name="codFAQ" value="<?= $faqEditar->codFAQ ?>">
                <?php endif; ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Pergunta</label>
                        <input type="text" class="form-control" name="pergunta" required
                               value="<?= $faqEditar ? htmlspecialchars($faqEditar->pergunta) : '' ?>"
                               placeholder="Ex: Qual o período de revisões?">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-secondary small">Resposta</label>
                        <textarea class="form-control" name="resposta" rows="4" required
                                  placeholder="Escreva a resposta..."><?= $faqEditar ? htmlspecialchars($faqEditar->resposta) : '' ?></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <a href="gestao_portal.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #7fa2b1;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast de sucesso -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastGlobal" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fa-solid fa-circle-check me-2"></i>Alterações guardadas com sucesso na base de dados!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    <?php if ($abrirModalFAQ) : ?>
    new bootstrap.Modal(document.getElementById('modalFAQ')).show();
    <?php endif; ?>
    <?php if (isset($_GET['sucesso'])) : ?>
    new bootstrap.Toast(document.getElementById('toastGlobal')).show();
    <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
