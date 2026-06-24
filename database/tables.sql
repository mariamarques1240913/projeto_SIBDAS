CREATE TABLE IF NOT EXISTS `Utilizador` (
  `codUtilizador` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `perfil` ENUM('administrador','profissional de saude','tecnico') NOT NULL DEFAULT 'profissional de saude',
  CONSTRAINT `pk_Utilizador` PRIMARY KEY (`codUtilizador`),
  CONSTRAINT `uq_Utilizador_username` UNIQUE (`username`),
  CONSTRAINT `uq_Utilizador_email` UNIQUE (`email`)
);

CREATE TABLE IF NOT EXISTS `Categoria` (
  `codCategoria` int NOT NULL AUTO_INCREMENT,
  `designacao` varchar(100) NOT NULL,
  `descricao` text,
  CONSTRAINT `pk_Categoria` PRIMARY KEY (`codCategoria`)
);

CREATE TABLE IF NOT EXISTS `Localizacao` (
  `codLocalizacao` int NOT NULL AUTO_INCREMENT,
  `edificio` varchar(100) NOT NULL,
  `piso` varchar(50) NOT NULL,
  `servico` varchar(100) NOT NULL,
  `sala` varchar(100),
  `eliminado` TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT `pk_Localizacao` PRIMARY KEY (`codLocalizacao`)
);

CREATE TABLE IF NOT EXISTS `Equipamento` (
  `codInventario` int NOT NULL AUTO_INCREMENT,
  `codCategoria` int NOT NULL,
  `codLocalizacao` int NOT NULL,
  `designacao` varchar(150) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `nrSerie` varchar(100) NOT NULL,
  `fabricante` varchar(100) NOT NULL,
  `dataAquisicao` date,
  `anoFabrico` int,
  `custoAquisicao` decimal(10,2),
  `tipoEntrada` varchar(20) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `criticidade` varchar(20) NOT NULL,
  `observacoes` text,
  `eliminado` TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT `pk_Equipamento` PRIMARY KEY (`codInventario`),
  CONSTRAINT `uq_Equipamento_serie` UNIQUE (`nrSerie`, `fabricante`, `modelo`),
  CONSTRAINT `ck_Equipamento_tipoEntrada` CHECK (`tipoEntrada` IN ('compra', 'doacao', 'aluguer', 'emprestimo')),
  CONSTRAINT `ck_Equipamento_estado` CHECK (`estado` IN ('Ativo', 'Em manutencao', 'Inativo', 'Em calibracao', 'Em quarentena', 'Abatido')),
  CONSTRAINT `ck_Equipamento_criticidade` CHECK (`criticidade` IN ('Baixa', 'Media', 'Alta', 'Suporte de vida'))
);

CREATE TABLE IF NOT EXISTS `Fornecedor` (
  `codFornecedor` int NOT NULL AUTO_INCREMENT,
  `nomeEmpresa` varchar(150) NOT NULL,
  `NIF` varchar(20) NOT NULL,
  `contactoTelefonico` varchar(20),
  `email` varchar(100),
  `morada` varchar(200),
  `website` varchar(150),
  `pessoaContacto` varchar(100),
  `telefonePessoaContacto` varchar(20),
  `tipoFornecedor` varchar(50) NOT NULL,
  `observacoes` text,
  `eliminado` TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT `pk_Fornecedor` PRIMARY KEY (`codFornecedor`),
  CONSTRAINT `uq_Fornecedor_NIF` UNIQUE (`NIF`),
  CONSTRAINT `ck_Fornecedor_tipoFornecedor` CHECK (`tipoFornecedor` IN ('fabricante', 'distribuidor', 'assistencia tecnica', 'consumiveis'))
);

CREATE TABLE IF NOT EXISTS `EquipamentoFornecedor` (
  `codInventario` int NOT NULL,
  `codFornecedor` int NOT NULL,
  CONSTRAINT `pk_EquipamentoFornecedor` PRIMARY KEY (`codInventario`, `codFornecedor`)
);

CREATE TABLE IF NOT EXISTS `Documento` (
  `codDocumento` int NOT NULL AUTO_INCREMENT,
  `codInventario` int NOT NULL,
  `codFornecedor` int,
  `tipoDocumento` varchar(50) NOT NULL,
  `nomeDocumento` varchar(150) NOT NULL,
  `dataDocumento` date,
  `dataValidade` date,
  `localizacaoFicheiro` varchar(255),
  `eliminado` TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT `pk_Documento` PRIMARY KEY (`codDocumento`),
  CONSTRAINT `ck_Documento_tipoDocumento` CHECK (`tipoDocumento` IN ('manual utilizador', 'manual servico', 'certificado calibracao', 'contrato manutencao', 'fatura', 'declaracao conformidade', 'relatorio tecnico'))
);

CREATE TABLE IF NOT EXISTS `Garantia` (
  `codGarantia` int NOT NULL AUTO_INCREMENT,
  `codInventario` int NOT NULL,
  `dataInicio` date NOT NULL,
  `dataFim` date NOT NULL,
  `temContrato` boolean NOT NULL,
  `tipoContrato` varchar(50),
  `entidadeResponsavel` varchar(150),
  `periodicidade` varchar(50),
  `observacoes` text,
  `eliminado` TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT `pk_Garantia` PRIMARY KEY (`codGarantia`)
);

ALTER TABLE `Equipamento` ADD CONSTRAINT `fk_Equipamento_Categoria` FOREIGN KEY (`codCategoria`) REFERENCES `Categoria` (`codCategoria`);

ALTER TABLE `Equipamento` ADD CONSTRAINT `fk_Equipamento_Localizacao` FOREIGN KEY (`codLocalizacao`) REFERENCES `Localizacao` (`codLocalizacao`);

ALTER TABLE `EquipamentoFornecedor` ADD CONSTRAINT `fk_EqForn_Equipamento` FOREIGN KEY (`codInventario`) REFERENCES `Equipamento` (`codInventario`);

ALTER TABLE `EquipamentoFornecedor` ADD CONSTRAINT `fk_EqForn_Fornecedor` FOREIGN KEY (`codFornecedor`) REFERENCES `Fornecedor` (`codFornecedor`);

ALTER TABLE `Documento` ADD CONSTRAINT `fk_Documento_Equipamento` FOREIGN KEY (`codInventario`) REFERENCES `Equipamento` (`codInventario`);

ALTER TABLE `Documento` ADD CONSTRAINT `fk_Documento_Fornecedor` FOREIGN KEY (`codFornecedor`) REFERENCES `Fornecedor` (`codFornecedor`);

ALTER TABLE `Garantia` ADD CONSTRAINT `fk_Garantia_Equipamento` FOREIGN KEY (`codInventario`) REFERENCES `Equipamento` (`codInventario`);

ALTER TABLE `Equipamento` ADD COLUMN `eliminado` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `Fornecedor`  ADD COLUMN `eliminado` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `Localizacao` ADD COLUMN `eliminado` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `Documento`   ADD COLUMN `eliminado` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `Garantia`    ADD COLUMN `eliminado` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `Utilizador`  ADD COLUMN `perfil` ENUM('administrador','profissional de saude','tecnico') NOT NULL DEFAULT 'profissional de saude';

CREATE TABLE IF NOT EXISTS `ConteudoPortal` (
  `chave` varchar(50) NOT NULL,
  `valor` text,
  CONSTRAINT `pk_ConteudoPortal` PRIMARY KEY (`chave`)
);

CREATE TABLE IF NOT EXISTS `FAQ` (
  `codFAQ` int NOT NULL AUTO_INCREMENT,
  `pergunta` text NOT NULL,
  `resposta` text NOT NULL,
  CONSTRAINT `pk_FAQ` PRIMARY KEY (`codFAQ`)
);

CREATE TABLE IF NOT EXISTS `MensagemContacto` (
  `codMensagem` int NOT NULL AUTO_INCREMENT,
  `nome`        varchar(150) NOT NULL,
  `email`       varchar(150) NOT NULL,
  `mensagem`    text NOT NULL,
  `dataHora`    datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lida`        boolean NOT NULL DEFAULT 0,
  CONSTRAINT `pk_MensagemContacto` PRIMARY KEY (`codMensagem`)
);

CREATE TABLE IF NOT EXISTS `RegistoEventos` (
  `codEvento`     int NOT NULL AUTO_INCREMENT,
  `acao`          varchar(20) NOT NULL,
  `codInventario` int NOT NULL,
  `descricao`     varchar(255),
  `dataHora`      datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codUtilizador` int,
  CONSTRAINT `pk_RegistoEventos` PRIMARY KEY (`codEvento`)
);