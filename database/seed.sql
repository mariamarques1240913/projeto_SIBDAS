INSERT INTO `Utilizador` (`username`, `nome`, `email`, `password`, `perfil`) VALUES
('admin',    'Administrador do Sistema',   'admin@hospitally.pt',    '$2y$10$HUJDFjFub2uZV0iEGMKpI.fzo4lKds32K0HBTBzodGU2zn8MZOVXW', 'administrador'),
('tecnico1', 'João Silva',                 'tecnico@hospitally.pt',  '$2y$10$HUJDFjFub2uZV0iEGMKpI.fzo4lKds32K0HBTBzodGU2zn8MZOVXW', 'tecnico'),
('profissional1', 'Ana Costa',            'profissional@hospitally.pt', '$2y$10$HUJDFjFub2uZV0iEGMKpI.fzo4lKds32K0HBTBzodGU2zn8MZOVXW', 'profissional de saude')
ON DUPLICATE KEY UPDATE `perfil` = VALUES(`perfil`), `nome` = VALUES(`nome`);

INSERT INTO `ConteudoPortal` (`chave`, `valor`) VALUES
('quem_somos', 'O Hospitally é uma unidade hospitalar de referência, dedicada a oferecer as melhores soluções de saúde e bem-estar à comunidade, com base em elevados padrões de exigência técnica e humana.'),
('missao',     'Garantir uma gestão eficiente e inovadora dos serviços de saúde, promovendo a articulação perfeita entre as equipas clínicas, o controlo e supervisão de ativos biomédicos e o foco contínuo no bem-estar dos pacientes.'),
('telefone',   '+351 225 913 028'),
('email',      'geral@hospitally.pt'),
('morada',     'Avenida da Boavista, nº 2045, 4100-131 Porto, Portugal'),
('horario',    '2ª a 6ª Feira: 7h — 21h | Sábado e Feriados: 9h — 15h | Domingo: Encerrado')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

INSERT INTO `FAQ` (`codFAQ`, `pergunta`, `resposta`) VALUES
(1, 'O que se entende por criticidade de um dispositivo médico?', 'A criticidade clínica avalia o impacto e o risco potencial que a falha de um equipamento pode causar ao paciente. Equipamentos de suporte de vida, como ventiladores e desfibrilhadores, têm criticidade máxima, exigindo planos de manutenção preventiva rigorosos e monitorização constante.'),
(2, 'Qual é a importância de manter a ficha técnica e a documentação atualizadas?', 'A documentação centralizada garante que manuais de operação, diretrizes de segurança e registos do fabricante estejam acessíveis a qualquer momento. Facilita auditorias, suporta a rastreabilidade e assegura conformidade com normas regulatórias aplicáveis ao setor da saúde.'),
(3, 'Como se define a periodicidade das manutenções preventivas e calibrações?', 'Os intervalos de verificação técnica são determinados com base nas recomendações explícitas do fabricante, na intensidade de uso, no historial de avarias registado e nas exigências legais ou normativas. O sistema permite configurar alertas automáticos quando as datas de manutenção se aproximam.'),
(4, 'Qual é o papel da gestão de fornecedores num ambiente biomédico hospitalar?', 'Controlar o histórico de fornecedores autorizados permite rastrear a origem das peças de substituição, validar contratos de assistência técnica e garantir que apenas entidades certificadas intervêm nos equipamentos médicos, reduzindo riscos clínicos e administrativos.')
ON DUPLICATE KEY UPDATE `pergunta` = VALUES(`pergunta`), `resposta` = VALUES(`resposta`);

-- ── Categorias ────────────────────────────────────────────────────────────────
INSERT INTO `Categoria` (`designacao`, `descricao`) VALUES
('Imagiologia e Diagnóstico',       'Equipamentos de diagnóstico por imagem, TAC, RMN e ecografia.'),
('Monitorização Clínica',           'Monitores de sinais vitais, pressão e oximetria.'),
('Anestesia e Ventilação',          'Ventiladores, CPAP e carros de anestesia.'),
('Cardiologia e Circulação',        'ECG, desfibrilhadores e monitores cardíacos.'),
('Laboratório e Análises Clínicas', 'Analisadores hematológicos, centrífugas e bioquímica.'),
('Cirurgia e Bloco Operatório',     'Bisturis elétricos, insufladores e equipamentos cirúrgicos.')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);

-- ── Localizações ──────────────────────────────────────────────────────────────
INSERT INTO `Localizacao` (`edificio`, `piso`, `servico`, `sala`) VALUES
('Edifício A', '0', 'Urgência Geral',                    'Sala de Trauma'),
('Edifício A', '1', 'UCI - Unidade de Cuidados Intensivos', 'Enfermaria UCI'),
('Edifício A', '2', 'Cardiologia',                       'Sala de Exames'),
('Edifício B', '0', 'Bloco Operatório',                  'Sala 1'),
('Edifício B', '1', 'Imagiologia',                       'Sala de TAC/RMN'),
('Edifício B', '2', 'Laboratório Clínico',               'Área de Análises'),
('Edifício C', '0', 'Pediatria',                         'Enfermaria Pediátrica'),
('Edifício C', '1', 'Ortopedia e Traumatologia',         'Sala de Consultas');

-- ── Fornecedores ──────────────────────────────────────────────────────────────
INSERT INTO `Fornecedor` (`nomeEmpresa`, `NIF`, `contactoTelefonico`, `email`, `morada`, `website`, `pessoaContacto`, `telefonePessoaContacto`, `tipoFornecedor`) VALUES
('Philips Healthcare Portugal SA',    '500123456', '+351 214 000 100', 'philips@healthcarept.com',  'Rua do Salgado, 1, Lisboa',          'www.philips.pt/healthcare',     'Pedro Sousa',   '+351 910 100 200', 'fabricante'),
('Siemens Healthineers Portugal Lda', '501234567', '+351 213 000 200', 'info@siemens-health.pt',    'Av. José Malhoa, 16, Lisboa',        'www.siemens-healthineers.pt',   'Ana Ferreira',  '+351 920 200 300', 'fabricante'),
('MedTech Solutions Portugal Lda',    '502345678', '+351 220 100 300', 'geral@medtech.pt',          'Rua das Flores, 45, Porto',          'www.medtechpt.com',             'Rui Lopes',     '+351 930 300 400', 'distribuidor'),
('BioMedServ Unipessoal Lda',         '503456789', '+351 222 300 400', 'servico@biomedserv.pt',     'Av. da República, 88, Braga',        'www.biomedserv.pt',             'Carla Neves',   '+351 940 400 500', 'assistencia tecnica'),
('ConsumMed SA',                      '504567890', '+351 231 400 500', 'comercial@consummed.pt',    'Parque Industrial de Aveiro, Aveiro','www.consummed.pt',              'Tiago Pinto',   '+351 950 500 600', 'consumiveis')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

-- ── Equipamentos (25) ─────────────────────────────────────────────────────────
INSERT INTO `Equipamento` (`codCategoria`, `codLocalizacao`, `designacao`, `marca`, `modelo`, `nrSerie`, `fabricante`, `dataAquisicao`, `anoFabrico`, `custoAquisicao`, `tipoEntrada`, `estado`, `criticidade`, `observacoes`) VALUES
((SELECT codCategoria FROM Categoria WHERE designacao='Anestesia e Ventilação'),          (SELECT codLocalizacao FROM Localizacao WHERE servico='UCI - Unidade de Cuidados Intensivos'), 'Ventilador de UTI',               'Dräger',      'Evita V800',         'DRG-EV800-001', 'Dräger Medical GmbH',      '2021-03-15', 2021,  28500.00, 'compra', 'Ativo',         'Suporte de vida', 'Equipamento de suporte ventilatório de alta performance.'),
((SELECT codCategoria FROM Categoria WHERE designacao='Anestesia e Ventilação'),          (SELECT codLocalizacao FROM Localizacao WHERE servico='Urgência Geral'),                        'Ventilador Portátil',             'Dräger',      'Oxylog 3000 Plus',   'DRG-OX3K-002',  'Dräger Medical GmbH',      '2020-06-10', 2020,  14200.00, 'compra', 'Ativo',         'Suporte de vida', 'Transporte de doentes críticos.'),
((SELECT codCategoria FROM Categoria WHERE designacao='Monitorização Clínica'),           (SELECT codLocalizacao FROM Localizacao WHERE servico='UCI - Unidade de Cuidados Intensivos'), 'Monitor Multiparamétrico',        'Philips',     'IntelliVue MX500',   'PHI-MX500-003', 'Philips Medical Systems',  '2022-01-20', 2022,  19800.00, 'compra', 'Ativo',         'Alta',            NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Monitorização Clínica'),           (SELECT codLocalizacao FROM Localizacao WHERE servico='Cardiologia'),                           'Monitor Multiparamétrico',        'Philips',     'IntelliVue MX400',   'PHI-MX400-004', 'Philips Medical Systems',  '2021-09-05', 2021,  15600.00, 'compra', 'Em manutencao', 'Alta',            'Em revisão periódica programada.'),
((SELECT codCategoria FROM Categoria WHERE designacao='Cardiologia e Circulação'),        (SELECT codLocalizacao FROM Localizacao WHERE servico='Urgência Geral'),                        'Desfibrilhador Bifásico',         'Philips',     'HeartStart MRx',     'PHI-MRX-005',   'Philips Medical Systems',  '2020-11-30', 2020,  22300.00, 'compra', 'Ativo',         'Suporte de vida', NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Cardiologia e Circulação'),        (SELECT codLocalizacao FROM Localizacao WHERE servico='Urgência Geral'),                        'Desfibrilhador Automático DEA',   'Zoll',        'AED Plus',           'ZOL-AEDP-006',  'Zoll Medical Corporation', '2022-04-12', 2022,   3200.00, 'compra', 'Ativo',         'Suporte de vida', NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Imagiologia e Diagnóstico'),       (SELECT codLocalizacao FROM Localizacao WHERE servico='Imagiologia'),                           'Ecógrafo Portátil',               'Siemens',     'Acuson P500',        'SIE-P500-007',  'Siemens Healthineers',     '2023-02-18', 2023,  34000.00, 'compra', 'Ativo',         'Alta',            NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Imagiologia e Diagnóstico'),       (SELECT codLocalizacao FROM Localizacao WHERE servico='Imagiologia'),                           'Tomógrafo Computorizado (TAC)',    'Siemens',     'SOMATOM Edge Plus',  'SIE-EDGE-008',  'Siemens Healthineers',     '2019-08-20', 2019, 980000.00, 'compra', 'Ativo',         'Alta',            'Contrato de manutenção anual com Siemens.'),
((SELECT codCategoria FROM Categoria WHERE designacao='Imagiologia e Diagnóstico'),       (SELECT codLocalizacao FROM Localizacao WHERE servico='Imagiologia'),                           'Ressonância Magnética (RMN)',      'Siemens',     'MAGNETOM Sola',      'SIE-SOLA-009',  'Siemens Healthineers',     '2020-12-01', 2020,1250000.00,'compra', 'Em calibracao', 'Alta',            'Calibração anual obrigatória em curso.'),
((SELECT codCategoria FROM Categoria WHERE designacao='Cardiologia e Circulação'),        (SELECT codLocalizacao FROM Localizacao WHERE servico='Cardiologia'),                           'Eletrocardiógrafo 12 Derivações',  'Nihon Kohden','ECG-1550P',          'NIH-1550-010',  'Nihon Kohden Corporation', '2021-05-14', 2021,   4800.00, 'compra', 'Ativo',         'Media',           NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Cardiologia e Circulação'),        (SELECT codLocalizacao FROM Localizacao WHERE servico='Cardiologia'),                           'Ecocardiógrafo',                  'GE Healthcare','Vivid E95',         'GEH-VE95-011',  'GE Healthcare',            '2022-07-08', 2022,  89000.00, 'compra', 'Ativo',         'Alta',            NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Cardiologia e Circulação'),        (SELECT codLocalizacao FROM Localizacao WHERE servico='Cardiologia'),                           'Monitor Holter 24h',              'Spacelabs',   'Pathfinder II',      'SPC-PF2-012',   'Spacelabs Healthcare',     '2021-03-22', 2021,   5600.00, 'compra', 'Ativo',         'Media',           NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Anestesia e Ventilação'),          (SELECT codLocalizacao FROM Localizacao WHERE servico='UCI - Unidade de Cuidados Intensivos'), 'Bomba de Infusão Volumétrica',    'B.Braun',     'Infusomat Space',    'BBR-INF-013',   'B. Braun Melsungen AG',    '2022-10-05', 2022,   2900.00, 'compra', 'Ativo',         'Alta',            NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Anestesia e Ventilação'),          (SELECT codLocalizacao FROM Localizacao WHERE servico='UCI - Unidade de Cuidados Intensivos'), 'Bomba de Infusão Volumétrica',    'B.Braun',     'Infusomat Space',    'BBR-INF-014',   'B. Braun Melsungen AG',    '2022-10-05', 2022,   2900.00, 'compra', 'Ativo',         'Alta',            NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Cirurgia e Bloco Operatório'),     (SELECT codLocalizacao FROM Localizacao WHERE servico='Bloco Operatório'),                      'Carro de Anestesia',              'Dräger',      'Perseus A500',       'DRG-PA500-015', 'Dräger Medical GmbH',      '2020-02-28', 2020,  67000.00, 'compra', 'Ativo',         'Suporte de vida', NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Cirurgia e Bloco Operatório'),     (SELECT codLocalizacao FROM Localizacao WHERE servico='Bloco Operatório'),                      'Bisturi Elétrico',                'Erbe',        'VIO 300 D',          'ERB-VIO3-016',  'Erbe Elektromedizin GmbH', '2021-11-15', 2021,  18500.00, 'compra', 'Ativo',         'Media',           NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Cirurgia e Bloco Operatório'),     (SELECT codLocalizacao FROM Localizacao WHERE servico='Bloco Operatório'),                      'Insuflador Laparoscópico',        'Stryker',     'Elevate 45L',        'STR-EL45-017',  'Stryker Corporation',      '2023-01-10', 2023,  12000.00, 'compra', 'Ativo',         'Media',           NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Cirurgia e Bloco Operatório'),     (SELECT codLocalizacao FROM Localizacao WHERE servico='Bloco Operatório'),                      'Arco Cirúrgico C',                'Siemens',     'Arcadis Avantic',    'SIE-ARC-018',   'Siemens Healthineers',     '2019-06-20', 2019, 145000.00, 'compra', 'Em quarentena', 'Alta',            'Detetada anomalia na colimação. Aguarda peça.'),
((SELECT codCategoria FROM Categoria WHERE designacao='Laboratório e Análises Clínicas'), (SELECT codLocalizacao FROM Localizacao WHERE servico='Laboratório Clínico'),                   'Analisador de Gases Sanguíneos',  'Abbott',      'iStat 1',            'ABT-IST1-019',  'Abbott Diagnostics',       '2022-03-18', 2022,   8700.00, 'compra', 'Ativo',         'Alta',            NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Laboratório e Análises Clínicas'), (SELECT codLocalizacao FROM Localizacao WHERE servico='Laboratório Clínico'),                   'Analisador Hematológico',         'Sysmex',      'XN-1000',            'SYS-XN1K-020',  'Sysmex Corporation',       '2021-07-30', 2021,  42000.00, 'compra', 'Ativo',         'Alta',            NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Laboratório e Análises Clínicas'), (SELECT codLocalizacao FROM Localizacao WHERE servico='Laboratório Clínico'),                   'Centrifugadora de Laboratório',   'Hettich',     'EBA 200',            'HTT-EBA2-021',  'Andreas Hettich GmbH',     '2020-09-12', 2020,   3100.00, 'compra', 'Ativo',         'Baixa',           NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Monitorização Clínica'),           (SELECT codLocalizacao FROM Localizacao WHERE servico='Pediatria'),                             'Monitor de Recém-Nascidos',       'Philips',     'IntelliVue MP30',    'PHI-MP30-022',  'Philips Medical Systems',  '2022-05-06', 2022,  11200.00, 'compra', 'Ativo',         'Alta',            NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Monitorização Clínica'),           (SELECT codLocalizacao FROM Localizacao WHERE servico='Pediatria'),                             'Incubadora Neonatal',             'Dräger',      'Isolette C2000',     'DRG-IC2K-023',  'Dräger Medical GmbH',      '2021-02-14', 2021,  24500.00, 'compra', 'Ativo',         'Suporte de vida', NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Monitorização Clínica'),           (SELECT codLocalizacao FROM Localizacao WHERE servico='Urgência Geral'),                        'Oxímetro de Pulso',               'Masimo',      'Radical-7',          'MSM-RAD7-024',  'Masimo Corporation',       '2023-03-25', 2023,   4200.00, 'compra', 'Ativo',         'Media',           NULL),
((SELECT codCategoria FROM Categoria WHERE designacao='Imagiologia e Diagnóstico'),       (SELECT codLocalizacao FROM Localizacao WHERE servico='Imagiologia'),                           'Densitómetro Ósseo DEXA',         'Hologic',     'Horizon Wi',         'HOL-HRZ-025',   'Hologic Inc.',             '2020-10-08', 2020,  78000.00, 'compra', 'Abatido',       'Media',           'Equipamento descontinuado pelo fabricante.')
ON DUPLICATE KEY UPDATE `estado` = VALUES(`estado`);

-- ── Garantias ─────────────────────────────────────────────────────────────────
INSERT INTO `Garantia` (`codInventario`, `dataInicio`, `dataFim`, `temContrato`, `tipoContrato`, `entidadeResponsavel`, `periodicidade`, `observacoes`) VALUES
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-EV800-001'), '2021-03-15','2026-03-15', 1,'garantia fabricante', 'Dräger Medical GmbH',    'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-OX3K-002'),  '2020-06-10','2025-06-10', 1,'garantia fabricante', 'Dräger Medical GmbH',    'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='PHI-MX500-003'), '2022-01-20','2027-01-20', 1,'contrato manutencao', 'Philips Healthcare',      'semestral',NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='PHI-MX400-004'), '2021-09-05','2024-09-05', 1,'garantia fabricante', 'Philips Healthcare',      'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='PHI-MRX-005'),   '2020-11-30','2025-11-30', 1,'garantia fabricante', 'Philips Healthcare',      'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='ZOL-AEDP-006'),  '2022-04-12','2027-04-12', 0, NULL,                  NULL,                      NULL,       NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SIE-P500-007'),  '2023-02-18','2028-02-18', 1,'contrato manutencao', 'Siemens Healthineers',    'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SIE-EDGE-008'),  '2019-08-20','2024-08-20', 1,'contrato manutencao', 'Siemens Healthineers',    'anual',    'Contrato inclui peças e mão-de-obra.'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SIE-SOLA-009'),  '2020-12-01','2025-12-01', 1,'contrato manutencao', 'Siemens Healthineers',    'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='GEH-VE95-011'),  '2022-07-08','2027-07-08', 1,'garantia fabricante', 'GE Healthcare',           'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-PA500-015'), '2020-02-28','2025-02-28', 1,'contrato manutencao', 'Dräger Medical GmbH',    'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='ERB-VIO3-016'),  '2021-11-15','2025-11-15', 0, NULL,                  NULL,                      NULL,       NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='ABT-IST1-019'),  '2022-03-18','2027-03-18', 1,'garantia fabricante', 'Abbott Diagnostics',      'anual',    NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SYS-XN1K-020'),  '2021-07-30','2026-07-30', 1,'contrato manutencao', 'Sysmex Corporation',      'semestral',NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-IC2K-023'),  '2021-02-14','2026-02-14', 1,'garantia fabricante', 'Dräger Medical GmbH',    'anual',    NULL);

-- ── Documentos ────────────────────────────────────────────────────────────────
INSERT INTO `Documento` (`codInventario`, `tipoDocumento`, `nomeDocumento`, `dataDocumento`, `dataValidade`) VALUES
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-EV800-001'), 'manual utilizador',       'Manual do Operador Evita V800',              '2021-03-15', '2031-03-15'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-EV800-001'), 'manual servico',           'Manual de Serviço Evita V800',               '2021-03-15',  NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-EV800-001'), 'declaracao conformidade',  'Declaração de Conformidade CE - Evita V800', '2021-03-15',  NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='PHI-MX500-003'), 'manual utilizador',       'Manual IntelliVue MX500 PT',                 '2022-01-20', '2032-01-20'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='PHI-MX500-003'), 'contrato manutencao',      'Contrato Philips Care 2022-2027',            '2022-01-20', '2027-01-20'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='PHI-MRX-005'),   'manual utilizador',       'Guia de Utilização HeartStart MRx',          '2020-11-30', '2030-11-30'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='PHI-MRX-005'),   'declaracao conformidade',  'Certificado CE HeartStart MRx',              '2020-11-30',  NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SIE-EDGE-008'),  'contrato manutencao',      'Contrato SiemensCare TAC 2019-2024',         '2019-08-20', '2024-08-20'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SIE-EDGE-008'),  'relatorio tecnico',        'Relatório de Instalação SOMATOM Edge Plus',  '2019-09-01',  NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SIE-SOLA-009'),  'certificado calibracao',   'Certificado de Calibração RMN 2024',         '2024-01-15', '2025-01-15'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SIE-SOLA-009'),  'contrato manutencao',      'Contrato Siemens RMN 2020-2025',             '2020-12-01', '2025-12-01'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='GEH-VE95-011'),  'manual utilizador',       'Guia do Utilizador Vivid E95',               '2022-07-08', '2032-07-08'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-PA500-015'), 'manual utilizador',       'Manual Perseus A500',                        '2020-02-28', '2030-02-28'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-PA500-015'), 'declaracao conformidade',  'Declaração CE Perseus A500',                 '2020-02-28',  NULL),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SYS-XN1K-020'),  'manual servico',           'Manual Técnico Sysmex XN-1000',              '2021-07-30', '2031-07-30'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='SYS-XN1K-020'),  'certificado calibracao',   'Certificado Calibração XN-1000 2024',        '2024-02-10', '2025-02-10'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-IC2K-023'),  'manual utilizador',       'Manual Isolette C2000 PT',                   '2021-02-14', '2031-02-14'),
((SELECT codInventario FROM Equipamento WHERE nrSerie='DRG-IC2K-023'),  'declaracao conformidade',  'Declaração CE Isolette C2000',               '2021-02-14',  NULL);
