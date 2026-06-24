========================================
 HOSPITALLY — Sistema de Gestão de
 Inventário Hospitalar de Equipamentos
========================================

Projeto: Hospitally
Estudante: Maria Marques
Número: 1240913
Unidade curricular: Sistemas de Informação e Bases de Dados Aplicados à Saúde (SIBDAS)
Ano letivo: 2025/2026


----------------------------------------
INSTALAÇÃO E EXECUÇÃO
----------------------------------------

Pré-requisitos:
- Laragon (ou XAMPP/WAMP) com PHP 8.3+ e MySQL 8+

Passos:

1. Colocar a pasta "hospitally" no seguinte caminho:
      www/sibdas/1240913/hospitally

2. Importar a base de dados:
   - Abrir o phpMyAdmin ou HeidiSQL
   - Criar a base de dados: db1240913
   - Importar o ficheiro: database/tables.sql

3. Confirmar as credenciais em config/config.php:
   - Host:     vsgate-s1.dei.isep.ipp.pt
   - Porta:    10464
   - Base de dados: db1240913
   - Utilizador:    1240913
   - Password:      marques_913

4. Aceder à aplicação no browser:
   - Portal público: http://127.0.0.1/sibdas/1240913/hospitally/public/
   - Login:          http://127.0.0.1/sibdas/1240913/hospitally/public/login.php


----------------------------------------
CREDENCIAIS DE ACESSO
----------------------------------------

Perfil: Administrador
  Email:    admin@hospitally.pt
  Password: admin123
  Acesso:   total a todos os módulos

Perfil: Técnico
  Email:    tecnico@hospitally.pt
  Password: tecnico123
  Acesso:   equipamentos, documentação, garantias, localizações, fornecedores

Perfil: Profissional de Saúde
  Email:    profissional@hospitally.pt
  Password: profissional123
  Acesso:   consulta de equipamentos (modo leitura)


----------------------------------------
TESTES PRINCIPAIS
----------------------------------------

1. Login e sessão
   - Aceder a http://127.0.0.1/sibdas/1240913/hospitally/public/login.php
   - Testar login com cada um dos perfis acima
   - Verificar que o acesso direto a páginas privadas sem login redireciona para o login

2. Gestão de equipamentos (perfil administrador ou técnico)
   - Criar um novo equipamento
   - Editar os dados do equipamento criado
   - Consultar os detalhes (modal com abas: dados, fornecedores, documentos, garantias)
   - Eliminar (soft delete) — o registo não desaparece da base de dados

3. Fornecedores, localizações, documentação, garantias
   - Testar criação, edição e eliminação em cada módulo

4. Dashboard
   - Verificar KPIs e gráficos atualizados com os dados inseridos

5. Portal público e backoffice
   - Aceder ao portal: http://127.0.0.1/sibdas/1240913/hospitally/public/
   - Submeter o formulário de contacto
   - No backoffice, aceder a "Gestão do Portal" e editar os conteúdos
   - Verificar que as alterações se refletem no portal público

6. Controlo de acesso por perfil
   - Fazer login como "profissional de saude" e verificar que não é possível criar/editar/eliminar equipamentos

7. Alteração de palavra-passe
   - No backoffice, aceder ao menu do utilizador e alterar a password


----------------------------------------
NOTAS ADICIONAIS
----------------------------------------

- Os IDs nos URLs são encriptados com AES-256-CBC para prevenir IDOR
- As passwords estão armazenadas com bcrypt (password_hash)
- O registo de eventos é automático nas operações de criação, edição e eliminação
- O ficheiro commits.txt contém o histórico completo de commits do projeto
