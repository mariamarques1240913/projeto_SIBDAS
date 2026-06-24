# Hospitally — Sistema de Gestão de Inventário Hospitalar

Projeto desenvolvido no âmbito da unidade curricular de **Sistemas de Informação e Bases de Dados** (SIBDAS), curso de Licenciatura em Engenharia Biomédica (LEBIOM), Instituto Superior de Engenharia do Porto (ISEP).

**Autora:** Maria Marques — nº 1240913  
**Ano letivo:** 2025/2026

---

## Descrição

O Hospitally é uma aplicação web para gestão e catalogação de ativos clínicos hospitalares. Permite controlar o ciclo de vida de equipamentos médicos — desde o registo inicial até ao historial de localizações, documentação técnica, coberturas de garantia e contratos de manutenção.

Inclui ainda um portal público com informações institucionais, formulário de contacto e perguntas frequentes, gerido dinamicamente pelo backoffice.

---

## Funcionalidades

**Backoffice (área privada)**
- Dashboard com indicadores em tempo real, gráficos e alertas críticos
- Gestão de equipamentos médicos (registo, edição, soft delete)
- Gestão de fornecedores
- Gestão de localizações hospitalares (hierárquica)
- Documentação técnica associada a equipamentos
- Garantias e contratos de manutenção
- Registo de eventos automático
- Caixa de mensagens de contacto recebidas do portal
- Gestão de conteúdo do portal público
- Alteração de palavra-passe
- Controlo de acesso por perfil (administrador, técnico, profissional de saúde)

**Portal público**
- Página inicial com formulário de contacto
- Página "Quem Somos"
- Página de perguntas frequentes (FAQ)
- Conteúdo gerido dinamicamente a partir do backoffice

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Linguagem servidor | PHP 8.3 |
| Base de dados | MySQL 8 / InnoDB |
| Frontend | Bootstrap 5, Chart.js, DataTables, Font Awesome 6, jQuery |
| Servidor local | Laragon |
| Segurança IDs | AES-256-CBC (prevenção de IDOR) |
| Autenticação | Sessões PHP + bcrypt |

---

## Estrutura de ficheiros

```
hospitally/
├── config/
│   └── config.php          # Configurações globais e credenciais BD
├── public/                 # Páginas acessíveis sem autenticação
│   ├── index.php
│   ├── login.php
│   ├── quemsomos.php
│   └── faq.php
├── private/                # Backoffice (requer autenticação)
│   ├── includes/
│   │   ├── funcoes.php     # Funções auxiliares (sessão, encriptação, perfis)
│   │   ├── header.php
│   │   ├── nav.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   ├── dashboard.php
│   ├── equipamentos.php
│   ├── fornecedores.php
│   ├── localizacoes.php
│   ├── documentacao.php
│   ├── garantias.php
│   ├── mensagens.php
│   ├── gestao_portal.php
│   ├── alterar_password.php
│   └── processa_login.php
├── database/
│   └── tables.sql          # Script de criação da base de dados
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
└── commits.txt             # Histórico de commits do projeto
```

---

## Configuração e instalação

### Pré-requisitos
- [Laragon](https://laragon.org/) (ou XAMPP/WAMP equivalente) com PHP 8.3+ e MySQL 8+

### Passos

1. Colocar a pasta do projeto em `www/sibdas/1240913/hospitally`

2. Importar a base de dados:
   - Abrir o phpMyAdmin ou HeidiSQL
   - Criar a base de dados `db1240913`
   - Importar o ficheiro `database/tables.sql`

3. Confirmar as credenciais em `config/config.php`:
   ```php
   define('MYSQL_HOST',     'vsgate-s1.dei.isep.ipp.pt');
   define('MYSQL_PORT',     '10464');
   define('MYSQL_DATABASE', 'db1240913');
   define('MYSQL_USERNAME', '1240913');
   define('MYSQL_PASSWORD', 'marques_913');
   ```

4. Aceder à aplicação:
   - Portal público: `http://127.0.0.1/sibdas/1240913/hospitally/public/`
   - Login: `http://127.0.0.1/sibdas/1240913/hospitally/public/login.php`

---

## Perfis de utilizador

| Perfil | Acesso |
|---|---|
| `administrador` | Acesso total a todos os módulos |
| `tecnico` | Equipamentos, documentação, garantias, localizações, fornecedores |
| `profissional de saude` | Consulta de equipamentos (modo leitura) |

---

## Repositório

[https://github.com/mariamarques1240913/projeto_SIBDAS](https://github.com/mariamarques1240913/projeto_SIBDAS)
