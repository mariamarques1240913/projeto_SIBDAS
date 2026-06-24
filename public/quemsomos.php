<?php
require_once '../config/config.php';

try {
    $dsn_pub = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";
    $ligacao = new PDO($dsn_pub, MYSQL_USERNAME, MYSQL_PASSWORD);
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cp_bd = $ligacao->query("SELECT chave, valor FROM ConteudoPortal")->fetchAll(PDO::FETCH_KEY_PAIR);
    $ligacao = null;
} catch (PDOException $e) { $cp_bd = []; }
$cp = fn($k, $d = '') => htmlspecialchars($cp_bd[$k] ?? $d);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Quem Somos</title>
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
            <div class="row justify-content-center g-4">
                <div class="col-12 col-lg-8">

                    <div class="card border-0 shadow-sm rounded p-4 mb-4" style="border-left: 5px solid #7fa4b6 !important;">
                        <h2 class="mb-3">Quem Somos</h2>
                        <p><?= $cp('quem_somos', 'O Hospitally é uma unidade hospitalar de referência, dedicada a oferecer as melhores soluções de saúde e bem-estar à comunidade, com base em elevados padrões de exigência técnica e humana.') ?></p>
                    </div>

                    <div class="card border-0 shadow-sm rounded p-4" style="border-left: 5px solid #7fa4b6 !important;">
                        <h2 class="mb-3">A Nossa Missão</h2>
                        <p><?= $cp('missao', 'Garantir uma gestão eficiente e inovadora dos serviços de saúde, promovendo a articulação perfeita entre as equipas clínicas, o controlo e supervisão de ativos biomédicos e o foco contínuo no bem-estar dos pacientes.') ?></p>
                    </div>

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
