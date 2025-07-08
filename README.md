# Gestão de Clínicas

Sistema web para gerenciamento de clínicas, exames, usuários e empresas, desenvolvido em PHP. O objetivo do projeto é facilitar o cadastro, edição, visualização e administração de clínicas, exames e usuários, proporcionando uma interface simples e eficiente para o controle de informações clínicas.

## Funcionalidades Principais

- Cadastro, edição, exclusão e reativação de clínicas
- Gerenciamento de exames (inclusão, atualização de valores, ocultação)
- Cadastro e edição de usuários
- Autenticação de usuários (login/logout)
- Gerenciamento de empresas associadas
- Interface personalizável via arquivo de configuração

## Tecnologias Utilizadas

- PHP (backend)
- MySQL (banco de dados)
- HTML/CSS (frontend)
- Composer (gerenciamento de dependências)

## Instalação

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/seu-usuario/GestaoClinicas.git
   cd GestaoClinicas
   ```

2. **Instale as dependências via Composer:**
   ```bash
   composer install
   ```

3. **Configure o banco de dados:**
   - Crie um banco de dados MySQL.
   - Importe o arquivo `config/gestaoclinicas.sql` para criar as tabelas necessárias.

4. **Configure o sistema:**
   - Renomeie o arquivo `config/config-exemplo.php` para `config.php` e ajuste as configurações conforme seu ambiente (credenciais do banco, cores, etc).

5. **Acesse o sistema:**
   - Coloque o projeto em um servidor web (Apache/Nginx) com suporte a PHP.
   - Acesse via navegador pelo endereço configurado.

## Usuário Padrão Inicial

- **Usuário:** `dev`
- **Senha:** `dev123`

## Estrutura de Pastas

- `/config` — Arquivos de configuração e script SQL do banco
- `/css` — Arquivos de estilo
- `/src` — Scripts auxiliares e funções
- `/vendor` — Dependências gerenciadas pelo Composer
- Arquivos `.php` na raiz — Páginas principais do sistema

## Observações

- O sistema utiliza um arquivo `config.php` para centralizar as configurações, incluindo cores e parâmetros do banco de dados.
- O projeto foi desenvolvido para rodar em ambiente local ou servidor com PHP e MySQL.
