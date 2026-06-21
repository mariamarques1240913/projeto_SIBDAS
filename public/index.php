<?php require_once '../config/config.php'; ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Início</title>
    <link rel="icon" type="image/png" href="/projeto_SIBDAS/assets/img/coracao.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/projeto_SIBDAS/assets/css/1240913.css">
</head>
<body>

    <header class="topo-site">
        <img src="/projeto_SIBDAS/assets/img/logotipo_hospital.png" alt="Logótipo Hospitally" class="logo-hospital">
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
        <img src="/projeto_SIBDAS/assets/img/banner.png" alt="Hospital Hospitally" class="banner-imagem">
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
        <form action="#" method="POST">
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
    </section>

    <footer class="rodape-site">
        <div class="rodape-container">
            <div class="rodape-coluna">
                <h3>LOCALIZAÇÃO</h3>
                <p>Avenida da Boavista, nº 2045</p>
                <p>4100-131 Porto</p>
                <p>Portugal</p>
            </div>
            <div class="rodape-coluna">
                <h3>HORÁRIO</h3>
                <p>2ª a 6ª Feira: 7h — 21h</p>
                <p>Sábado e Feriados: 9h — 15h</p>
                <p>Domingo: Encerrado</p>
            </div>
            <div class="rodape-coluna">
                <h3>CONTACTOS</h3>
                <p>Email: geral@hospitally.pt</p>
                <p>Telefone: +351 225 913 028</p>
            </div>
        </div>
        <div class="rodape-direitos">
            <p><?php echo APP_COPYRIGHT; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/projeto_SIBDAS/assets/js/1240913.js"></script>
</body>
</html>
