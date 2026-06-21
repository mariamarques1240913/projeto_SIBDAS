<?php require_once '../config/config.php'; ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dúvidas Frequentes</title>
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

    <main class="conteudo-quemsomos">
        <div class="container">
            <h2 class="text-center mb-2"><i class="fa-solid fa-circle-question me-2" style="color: #7fa4b6;"></i>Dúvidas Frequentes</h2>
            <p class="text-center mb-4">Consulte os esclarecimentos fundamentais sobre as boas práticas na gestão e controlo do inventário de dispositivos hospitalares.</p>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">

                    <div class="accordion shadow-sm" id="faqAccordion">

                        <div class="accordion-item border-0 mb-2 rounded overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1" style="color: #2b4c5e; background-color: #eef5f8;">
                                    1. O que se entende por criticidade de um dispositivo médico?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    A criticidade clínica avalia o impacto e o risco potencial que a falha de um equipamento pode causar ao paciente. Dispositivos de suporte de vida (como ventiladores) têm prioridade e criticidade máxima, exigindo planos de monitorização e contingência rigorosos.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-2 rounded overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2" style="color: #2b4c5e; background-color: #eef5f8;">
                                    2. Qual é a importância de manter a ficha técnica e a documentação atualizadas?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    A documentação centralizada garante que manuais de operação, diretrizes de segurança e registos do fabricante estejam acessíveis a qualquer momento. Isto previne erros de operação pelas equipas de saúde e assegura a conformidade legal do hospital.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-2 rounded overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3" style="color: #2b4c5e; background-color: #eef5f8;">
                                    3. Como se define a periodicidade das manutenções preventivas e calibrações?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Os intervalos de verificação técnica são determinados com base nas recomendações explícitas do fabricante, na intensidade de uso do dispositivo dentro das respetivas unidades e na regulamentação de segurança médica em vigor.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 rounded overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4" style="color: #2b4c5e; background-color: #eef5f8;">
                                    4. Qual é o papel da gestão de fornecedores num ambiente biomédico hospitalar?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Controlar o histórico de fornecedores autorizados permite rastrear a origem das peças de substituição, validar contratos de assistência pós-venda técnica e garantir que apenas entidades certificadas realizam intervenções nos equipamentos do inventário.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

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
