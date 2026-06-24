<?php
require_once '../config/config.php';

$mensagemEnviada = false;
$erroContacto    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome     = trim($_POST['nome']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    if ($nome && $email && $mensagem && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $dsnPub = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8";
            $ligacao = new PDO($dsnPub, MYSQL_USERNAME, MYSQL_PASSWORD);
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $ligacao->prepare("INSERT INTO MensagemContacto (nome, email, mensagem) VALUES (?,?,?)")->execute([$nome, $email, $mensagem]);
            $ligacao = null;
            $mensagemEnviada = true;
        } catch (PDOException $e) {
            $erroContacto = "Erro ao enviar a mensagem. Tente novamente.";
            $ligacao = null;
        }
    } else {
        $erroContacto = "Por favor preencha todos os campos corretamente e indique um email válido.";
    }
}

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
    <title><?php echo APP_NAME; ?> - Início</title>
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

    <div class="banner-container">
        <img src="<?= BASE_URL ?>/assets/img/banner.png" alt="Hospital Hospitally" class="banner-imagem">
        <div class="banner-texto">
            <h2>Excelência e Rigor na Gestão Hospitalar</h2>
        </div>
    </div>

    <main class="conteudo-principal">
        <div class="container">
            <h2>Bem-vindo ao Portal de Gestão Hospitally</h2>
            <p>Portal de informação sobre o sistema de gestão e catalogação de ativos clínicos.</p>

            <div class="row g-4 justify-content-center mt-2">
                <div class="col-12 col-md-4">
                    <div class="caixa-pastel caixa-servicos h-100">
                        <h3>Os nossos Serviços</h3>
                        <p>Conheça a nossa unidade de cuidados continuados e assistência médica especializada.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="caixa-pastel caixa-especialidades h-100">
                        <h3>Rastreio de Ativos</h3>
                        <p>Monitorização permanente do histórico de localizações e serviços por onde cada equipamento já passou.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="caixa-pastel caixa-especialidades h-100">
                        <h3>Especialidades</h3>
                        <p>Dispomos de um corpo clínico altamente qualificado nas áreas de cardiologia, pediatria e cirurgia geral.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <section id="contacto">
        <h2>Pedido de Contacto / Esclarecimento</h2>
        <?php if ($mensagemEnviada) : ?>
            <div class="alert alert-success text-center py-4">
                <i class="fa-solid fa-circle-check fa-2x mb-2 d-block"></i>
                <strong>Mensagem enviada com sucesso!</strong><br>
                Entraremos em contacto brevemente através do email indicado.
            </div>
        <?php else : ?>
            <?php if ($erroContacto) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erroContacto) ?></div>
            <?php endif; ?>
            <form action="index.php" method="POST">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="Introduza o seu nome completo" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="O seu email para receber a resposta" required>
                </div>
                <div class="mb-3">
                    <label for="mensagem" class="form-label">Mensagem / Dúvida:</label>
                    <textarea id="mensagem" name="mensagem" class="form-control" rows="4" placeholder="Escreva aqui a sua mensagem..." required></textarea>
                </div>
                <button type="submit">Enviar Pedido</button>
            </form>
        <?php endif; ?>
    </section>

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
