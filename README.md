# BizFlow — Sistema de Gestão Empresarial

O **BizFlow** é um sistema web de gestão empresarial desenvolvido para centralizar e facilitar o controlo das principais operações de um negócio.

O sistema permite gerir clientes, produtos, vendas, stock, caixa, utilizadores e relatórios através de uma interface web simples e organizada.

## 🌐 Demonstração online

**Sistema:** https://bizflow.freehosting.dev/index.php

> Projeto desenvolvido para fins de portfólio e demonstração de competências em desenvolvimento web.

---

## 🚀 Funcionalidades

* 🔐 Sistema de autenticação e sessões
* 👥 Gestão de clientes
* 📦 Gestão de produtos
* 🗂️ Gestão de categorias
* 🛒 Registo e gestão de vendas
* 📊 Controlo automático de stock
* 💰 Gestão de caixa
* ➕ Registo de entradas
* ➖ Registo de saídas
* 📈 Relatórios
* 🖨️ Visualização e impressão de informações
* 👤 Gestão de utilizadores
* 🔑 Níveis de acesso
* 📋 Dashboard administrativo
* 📱 Interface responsiva

---

## 🔐 Segurança

O projeto utiliza algumas práticas de segurança, incluindo:

* Passwords armazenadas com `password_hash()`
* Validação através de `password_verify()`
* Prepared Statements para consultas à base de dados
* Proteção de páginas através de sessões
* Controlo de acesso baseado no nível do utilizador
* Regeneração do ID da sessão após autenticação
* Proteção das credenciais da base de dados através do `.gitignore`

---

## 🛠️ Tecnologias utilizadas

### Front-end

* HTML5
* CSS3
* JavaScript
* Bootstrap

### Back-end

* PHP
* MySQL

### Ferramentas

* Visual Studio Code
* XAMPP
* phpMyAdmin
* Git
* GitHub

### Hospedagem

* FreeHosting

---

## 🗄️ Estrutura do sistema

O BizFlow está organizado em diferentes módulos:

```text
bizflow/
│
├── assets/
├── auth/
├── caixa/
├── clientes/
├── config/
├── dashboard/
├── includes/
├── produtos/
├── vendas/
│
├── index.php
├── .gitignore
└── README.md
```

---

## ⚙️ Como executar localmente

### 1. Clonar o projeto

```bash
git clone https://github.com/Jose-Ngimbi/portofolio.git
```

### 2. Colocar o projeto no XAMPP

Coloque a pasta do projeto dentro de:

```text
C:\xampp\htdocs\
```

### 3. Criar a base de dados

Abra o phpMyAdmin e crie uma base de dados MySQL para o projeto.

Depois importe o ficheiro SQL disponibilizado no projeto, caso exista uma versão de estrutura/demonstração da base de dados.

### 4. Configurar a ligação

Crie o arquivo:

```text
config/database.php
```

com as credenciais da sua própria instalação.

> As credenciais reais da base de dados não são disponibilizadas neste repositório.

### 5. Iniciar o XAMPP

Ative:

```text
Apache
MySQL
```

Depois acesse o projeto através do navegador.

---

## 📸 Screenshots

Em breve serão adicionadas capturas de ecrã das principais áreas do sistema:

* Dashboard
* Gestão de clientes
* Gestão de produtos
* Vendas
* Caixa
* Relatórios
* Login

---

## 🎯 Objetivo do projeto

O BizFlow foi desenvolvido como um projeto de portfólio com o objetivo de demonstrar conhecimentos práticos em:

* Desenvolvimento de sistemas web
* PHP e MySQL
* CRUD
* Autenticação
* Gestão de sessões
* Controlo de permissões
* Segurança básica de aplicações web
* Gestão de bases de dados
* Desenvolvimento de interfaces
* Git e GitHub
* Deploy de aplicações web

---

## 👨‍💻 Desenvolvedor

**José Ngimbi**

Desenvolvedor web interessado em desenvolvimento de sistemas, programação e tecnologias web.

### Projeto

**BizFlow — Sistema de Gestão Empresarial**

🌐 Demonstração online:
https://bizflow.freehosting.dev/index.php

💻 GitHub:
https://github.com/Jose-Ngimbi/portofolio

---

## 📄 Licença

Este projeto foi desenvolvido para fins educacionais e de portfólio.
