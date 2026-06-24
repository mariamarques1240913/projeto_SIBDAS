<?php
require_once '../config/config.php';

try {
    $dsn_pub = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";
    $ligacao = new PDO($dsn_pub, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cp_bd = $ligacao->query("SELECT chave, valor FROM ConteudoPortal")->fetchAll(PDO::FETCH_KEY_PAIR);
    $faqs  = $ligacao->query("SELECT * FROM FAQ ORDER BY codFAQ")->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $e) { $cp_bd = []; $faqs = []; }
$cp = fn($k, $d = '') => htmlspecialchars($cp_bd[$k] ?? $d);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dúvidas Frequentes</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/coracao.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/1240913.css">
</head>
<body>

    <header class="topo-site">
        <img src="<?= BASE_URL ?>/assets/img/logotipo_hospital.png" alt="Logótipo Hospitally" class="logo-hospital">
        <div class="topo-site-texto">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Sistema de Gestão de Cuidados Hospitalares</p>
        </div>
    </header>

    <nav class="menu-site navbar">
        <ul class="navbar-nav mx-auto flex-row flex-wrap">
            <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
            <li class="nav-item"><a class="nav-link" href="quemsomos.php">Quem Somos</a></li>
            <li class="nav-item"><a class="nav-link" href="faq.php">Dúvidas Frequentes</a></li>
            <li class="nav-item zona-login"><a href="login.php" class="btn-login">Utilizador</a></li>
        </ul>
    </nav>

    <main class="conteudo-quemsomos">
        <div class="container">
            <h2 class="text-center mb-2"><i class="fa-solid fa-circle-question me-2" style="color: #7fa4b6;"></i>Dúvidas Frequentes</h2>
            <p class="text-center mb-4">Consulte os esclarecimentos fundamentais sobre as boas práticas na gestão e controlo do inventário de dispositivos hospitalares.</p>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">

                    <?php if (empty($faqs)) : ?>
                        <div class="text-center text-muted py-4">Não existem perguntas frequentes disponíveis neste momento.</div>
                    <?php else : ?>
                    <div class="accordion shadow-sm" id="faqAccordion">
                        <?php foreach ($faqs as $i => $faq) : ?>
                        <div class="accordion-item border-0 mb-2 rounded overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?> fw-bold" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq<?= $faq->codFAQ ?>"
                                        aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                        aria-controls="faq<?= $faq->codFAQ ?>"
                                        style="color: #2b4c5e; background-color: #eef5f8;">
                                    <?= ($i + 1) . '. ' . htmlspecialchars($faq->pergunta) ?>
                                </button>
                            </h2>
                            <div id="faq<?= $faq->codFAQ ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    <?= htmlspecialchars($faq->resposta) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

    <footer class="rodape-site">
        <div class="rodape-container">
            <div class="rodape-coluna">
                <h3>LOCALIZAÇÃO</h3>
                <p><?= $cp('morada', 'Avenida da Boavista, nº 2045, 4100-131 Porto, Portugal') ?></p>
            </div>
            <div class="rodape-coluna">
                <h3>HORÁRIO</h3>
                <p><?= $cp('horario', '2ª a 6ª Feira: 7h — 21h | Sábado e Feriados: 9h — 15h | Domingo: Encerrado') ?></p>
            </div>
            <div class="rodape-coluna">
                <h3>CONTACTOS</h3>
                <p>Email: <?= $cp('email', 'geral@hospitally.pt') ?></p>
                <p>Telefone: <?= $cp('telefone', '+351 225 913 028') ?></p>
            </div>
        </div>
        <div class="rodape-direitos">
            <p><?php echo APP_COPYRIGHT; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/1240913.js"></script>
</body>
</html>
