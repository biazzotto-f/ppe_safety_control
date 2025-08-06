-- Cria a base de dados se ela não existir e define o conjunto de caracteres padrão.
CREATE DATABASE IF NOT EXISTS `ppe_safety_control` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ppe_safety_control`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `empresas`
--
DROP TABLE IF EXISTS `empresas`;
CREATE TABLE `empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_empresa` varchar(255) NOT NULL,
  `cnpj` varchar(18) NOT NULL UNIQUE,
  `foto_empresa` varchar(255) DEFAULT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Inserindo dados de teste
INSERT INTO `empresas` (`id`, `nome_empresa`, `cnpj`, `status`, `cep`, `logradouro`, `numero`, `bairro`, `cidade`, `estado`, `telefone`, `contato`) VALUES
(1, 'Empresa Padrão LTDA', '00.000.000/0001-00', 'ativo', '09521-100', 'Rua Amazonas', '123', 'Centro', 'São Caetano do Sul', 'SP', '(11) 4224-1234', 'Sr. João'),
(2, 'Segurança Total S.A.', '11.111.111/0001-11', 'ativo', '01001-000', 'Praça da Sé', 's/n', 'Sé', 'São Paulo', 'SP', '(11) 3105-5678', 'Sra. Maria');

-- --------------------------------------------------------

--
-- Estrutura da tabela `empresa_unidades`
--
DROP TABLE IF EXISTS `empresa_unidades`;
CREATE TABLE `empresa_unidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `nome_unidade` varchar(255) NOT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `empresa_unidades_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserindo unidades de teste
INSERT INTO `empresa_unidades` (`id`, `id_empresa`, `nome_unidade`, `cep`, `logradouro`, `numero`, `bairro`, `cidade`, `estado`) VALUES
(1, 1, 'Fábrica Matriz', '09530-250', 'Rua Pernambuco', '100', 'Centro', 'São Caetano do Sul', 'SP'),
(2, 1, 'Cliente Y', '04538-133', 'Avenida Brigadeiro Faria Lima', '200', 'Jardim Paulistano', 'São Paulo', 'SP'),
(3, 2, 'Sede Administrativa', '01001-000', 'Praça da Sé', 's/n', 'Sé', 'São Paulo', 'SP');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `nivel_acesso` enum('superadmin','admin','funcionario') NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Inserindo dados de teste
INSERT INTO `usuarios` (`id`, `nome`, `username`, `password`, `nivel_acesso`, `status`) VALUES
(1, 'Super Administrador', 'superadmin', 'admin', 'superadmin', 'ativo'),
(2, 'Admin Multi-Empresa', 'admin', 'senha', 'admin', 'ativo'),
(3, 'Funcionário Teste', 'funcionario', 'senha', 'funcionario', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario_empresa_acesso`
--
DROP TABLE IF EXISTS `usuario_empresa_acesso`;
CREATE TABLE `usuario_empresa_acesso` (
  `id_usuario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  PRIMARY KEY (`id_usuario`, `id_empresa`),
  KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `usuario_empresa_acesso_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuario_empresa_acesso_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Associando o admin a duas empresas
INSERT INTO `usuario_empresa_acesso` (`id_usuario`, `id_empresa`) VALUES
(2, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `setores`
--
DROP TABLE IF EXISTS `setores`;
CREATE TABLE `setores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `nome_setor` varchar(255) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `setores_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserindo setores de teste
INSERT INTO `setores` (`id`, `id_empresa`, `nome_setor`, `status`) VALUES
(1, 1, 'Produção', 'ativo'),
(2, 1, 'Manutenção', 'ativo'),
(3, 2, 'Segurança do Trabalho', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcoes`
--
DROP TABLE IF EXISTS `funcoes`;
CREATE TABLE `funcoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `id_setor` int(11) NOT NULL,
  `nome_funcao` varchar(255) NOT NULL,
  `riscos` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  KEY `id_setor` (`id_setor`),
  CONSTRAINT `funcoes_ibfk_1` FOREIGN KEY (`id_setor`) REFERENCES `setores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserindo funções de teste
INSERT INTO `funcoes` (`id`, `id_empresa`, `id_setor`, `nome_funcao`, `riscos`, `status`) VALUES
(1, 1, 1, 'Operador de Máquinas', 'Fisico,Acidentes', 'ativo'),
(2, 1, 2, 'Soldador', 'Fisico,Quimico,Acidentes', 'ativo'),
(3, 2, 3, 'Analista de Segurança', 'Ergonomico', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `epi_classificacoes`
--
DROP TABLE IF EXISTS `epi_classificacoes`;
CREATE TABLE `epi_classificacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` char(1) NOT NULL,
  `nome_classificacao` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Inserindo classificações padrão
INSERT INTO `epi_classificacoes` (`id`, `tipo`, `nome_classificacao`) VALUES
(1, 'A', 'Proteção para a cabeça'),
(2, 'B', 'Proteção para os olhos e face'),
(3, 'C', 'Proteção auditiva'),
(4, 'D', 'Proteção respiratória'),
(5, 'E', 'Proteção para o tronco e membros superiores'),
(6, 'F', 'Proteção para os membros inferiores');

-- --------------------------------------------------------

--
-- Estrutura da tabela `epi_categorias`
--
DROP TABLE IF EXISTS `epi_categorias`;
CREATE TABLE `epi_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `id_classificacao` int(11) NOT NULL,
  `nome_categoria` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`),
  KEY `id_classificacao` (`id_classificacao`),
  CONSTRAINT `epi_categorias_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epi_categorias_ibfk_2` FOREIGN KEY (`id_classificacao`) REFERENCES `epi_classificacoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserindo categorias de teste
INSERT INTO `epi_categorias` (`id`, `id_empresa`, `id_classificacao`, `nome_categoria`) VALUES
(1, 1, 1, 'Capacetes'),
(2, 1, 5, 'Luvas de Proteção');

-- --------------------------------------------------------

--
-- Estrutura da tabela `epis` (Catálogo mestre)
--
DROP TABLE IF EXISTS `epis`;
CREATE TABLE `epis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `nome_epi` varchar(255) NOT NULL,
  `ca` varchar(50) NOT NULL,
  `validade_ca` date DEFAULT NULL,
  `foto_epi` varchar(255) DEFAULT NULL,
  `frequencia_troca` int(11) DEFAULT NULL,
  `unidade_frequencia` enum('dias','meses') DEFAULT 'dias',
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `epis_cat_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epis_cat_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `epi_categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserindo dados de teste
INSERT INTO `epis` (`id`, `id_empresa`, `id_categoria`, `nome_epi`, `ca`, `validade_ca`, `status`) VALUES
(1, 1, 1, 'Capacete de Segurança Classe A', '31469', '2028-12-31', 'ativo'),
(2, 1, 2, 'Luva de Malha Pigmentada Tam. P', '20573', '2023-01-01', 'ativo'),
(3, 1, 2, 'Luva de Malha Pigmentada Tam. M', '20573', '2023-01-01', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcao_classificacoes`
--
DROP TABLE IF EXISTS `funcao_classificacoes`;
CREATE TABLE `funcao_classificacoes` (
  `id_funcao` int(11) NOT NULL,
  `id_classificacao` int(11) NOT NULL,
  PRIMARY KEY (`id_funcao`,`id_classificacao`),
  KEY `id_classificacao` (`id_classificacao`),
  CONSTRAINT `funcao_classificacoes_ibfk_1` FOREIGN KEY (`id_funcao`) REFERENCES `funcoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `funcao_classificacoes_ibfk_2` FOREIGN KEY (`id_classificacao`) REFERENCES `epi_classificacoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcao_categorias`
--
DROP TABLE IF EXISTS `funcao_categorias`;
CREATE TABLE `funcao_categorias` (
  `id_funcao` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  PRIMARY KEY (`id_funcao`,`id_categoria`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `funcao_categorias_ibfk_1` FOREIGN KEY (`id_funcao`) REFERENCES `funcoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `funcao_categorias_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `epi_categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserindo associações de teste
INSERT INTO `funcao_classificacoes` (`id_funcao`, `id_classificacao`) VALUES
(1, 1);

INSERT INTO `funcao_categorias` (`id_funcao`, `id_categoria`) VALUES
(1, 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `colaboradores`
--
DROP TABLE IF EXISTS `colaboradores`;
CREATE TABLE `colaboradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_unidade_operacao` int(11) DEFAULT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `matricula` varchar(50) NOT NULL,
  `id_funcao` int(11) NOT NULL,
  `id_setor` int(11) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `foto_perfil` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula_empresa` (`id_empresa`,`matricula`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_funcao` (`id_funcao`),
  KEY `id_setor_colab` (`id_setor`),
  KEY `id_unidade_operacao` (`id_unidade_operacao`),
  CONSTRAINT `colaboradores_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `colaboradores_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `colaboradores_ibfk_3` FOREIGN KEY (`id_funcao`) REFERENCES `funcoes` (`id`),
  CONSTRAINT `colaboradores_ibfk_4` FOREIGN KEY (`id_setor`) REFERENCES `setores` (`id`),
  CONSTRAINT `colaboradores_ibfk_5` FOREIGN KEY (`id_unidade_operacao`) REFERENCES `empresa_unidades` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Inserindo colaborador de teste
INSERT INTO `colaboradores` (`id`, `id_empresa`, `id_usuario`, `id_unidade_operacao`, `nome_completo`, `matricula`, `id_funcao`, `id_setor`) VALUES
(1, 1, 3, 1, 'Funcionário de Teste da Silva', 'F-12345', 1, 1),
(2, 2, NULL, 3, 'Joana Santos', 'S-54321', 3, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `fornecedores`
--
DROP TABLE IF EXISTS `fornecedores`;
CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `nome_fornecedor` varchar(255) NOT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `foto_fornecedor` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `fornecedores_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Estrutura da tabela `epi_fornecedores`
--
DROP TABLE IF EXISTS `epi_fornecedores`;
CREATE TABLE `epi_fornecedores` (
  `id_epi` int(11) NOT NULL,
  `id_fornecedor` int(11) NOT NULL,
  PRIMARY KEY (`id_epi`,`id_fornecedor`),
  KEY `id_fornecedor` (`id_fornecedor`),
  CONSTRAINT `epi_fornecedores_ibfk_1` FOREIGN KEY (`id_epi`) REFERENCES `epis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epi_fornecedores_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Estrutura da tabela `epi_lotes`
--
DROP TABLE IF EXISTS `epi_lotes`;
CREATE TABLE `epi_lotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_epi` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_fornecedor` int(11) DEFAULT NULL,
  `quantidade_inicial` int(11) NOT NULL,
  `quantidade_atual` int(11) NOT NULL,
  `data_compra` date NOT NULL,
  `data_vencimento` date DEFAULT NULL,
  `nota_fiscal` varchar(100) DEFAULT NULL,
  `custo_unitario` decimal(10,2) DEFAULT 0.00,
  `custo_total` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `id_epi` (`id_epi`),
  KEY `id_fornecedor` (`id_fornecedor`),
  CONSTRAINT `epi_lotes_ibfk_1` FOREIGN KEY (`id_epi`) REFERENCES `epis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epi_lotes_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Inserindo lotes de teste
INSERT INTO `epi_lotes` (`id`, `id_epi`, `id_empresa`, `quantidade_inicial`, `quantidade_atual`, `data_compra`, `data_vencimento`, `custo_unitario`, `custo_total`) VALUES
(1, 1, 1, 30, 30, '2025-06-01', '2028-06-01', 15.50, 465.00),
(2, 1, 1, 20, 20, '2025-07-01', '2027-07-01', 16.00, 320.00),
(3, 2, 1, 150, 148, '2025-05-15', NULL, 2.25, 337.50);

-- --------------------------------------------------------

--
-- Estrutura da tabela `entregas`
--
DROP TABLE IF EXISTS `entregas`;
CREATE TABLE `entregas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `id_colaborador` int(11) NOT NULL,
  `id_epi` int(11) NOT NULL,
  `quantidade_entregue` int(11) NOT NULL,
  `data_entrega` datetime NOT NULL DEFAULT current_timestamp(),
  `assinatura_digital` text DEFAULT NULL,
  `id_usuario_entrega` int(11) NOT NULL,
  `data_proxima_troca` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`),
  KEY `id_colaborador` (`id_colaborador`),
  KEY `id_epi` (`id_epi`),
  CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entregas_ibfk_2` FOREIGN KEY (`id_colaborador`) REFERENCES `colaboradores` (`id`),
  CONSTRAINT `entregas_ibfk_3` FOREIGN KEY (`id_epi`) REFERENCES `epis` (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Estrutura da tabela `entrega_lotes`
--
DROP TABLE IF EXISTS `entrega_lotes`;
CREATE TABLE `entrega_lotes` (
  `id_entrega` int(11) NOT NULL,
  `id_lote` int(11) NOT NULL,
  `quantidade_retirada` int(11) NOT NULL,
  PRIMARY KEY (`id_entrega`,`id_lote`),
  KEY `id_lote` (`id_lote`),
  CONSTRAINT `entrega_lotes_ibfk_1` FOREIGN KEY (`id_entrega`) REFERENCES `entregas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entrega_lotes_ibfk_2` FOREIGN KEY (`id_lote`) REFERENCES `epi_lotes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserindo dados de teste
INSERT INTO `entregas` (`id`, `id_empresa`, `id_colaborador`, `id_epi`, `quantidade_entregue`, `data_entrega`, `id_usuario_entrega`) VALUES
(1, 1, 1, 2, 2, '2025-07-21 11:30:00', 2);

INSERT INTO `entrega_lotes` (`id_entrega`, `id_lote`, `quantidade_retirada`) VALUES
(1, 3, 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `log_acoes`
--
DROP TABLE IF EXISTS `log_acoes`;
CREATE TABLE `log_acoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nome_usuario` varchar(100) NOT NULL,
  `acao` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB;
