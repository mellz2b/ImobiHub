# ImobiHub

## Descrição
O ImobiHub é uma aplicação web de gerenciamento imobiliário desenvolvida para facilitar a administração e divulgação de imóveis disponíveis para venda ou locação. A plataforma permite que administradores gerenciem anúncios por meio de um painel (dashboard), enquanto os usuários podem visualizar os imóveis em um catálogo público atualizado em tempo real. O projeto está alinhado ao **ODS 11 - Cidades e Comunidades Sustentáveis**, contribuindo para a organização e acesso à informação sobre moradia.

---

## Objetivo
Desenvolver um sistema funcional de gerenciamento imobiliário, permitindo cadastro, edição e visualização de imóveis de forma dinâmica e organizada.

---

## ODS
ODS 11 - Cidades e Comunidades Sustentáveis

---

## Tecnologias Utilizadas
- **Backend:** PHP 8.1+
- **Banco de Dados:** SQLite
- **Frontend:** HTML + CSS
- **Servidor local:** PHP built-in server

---

## Módulos
- Catálogo público (`index.php`)
- Dashboard administrativo (`dashboard.php`)

---

## ⚙️ Funcionalidades
- Cadastro de anúncio com upload de fotos  
- Edição de anúncio cadastrado  
- Edição rápida de preço  
- Exclusão de anúncio  
- Alternância de status (vendido/disponível)  
- Filtros no catálogo (tipo, busca, ordenação e vendidos)  

---

## Persistência de Dados
- Banco: `php-app/data/imobihub.sqlite`  
- Uploads: `php-app/public/uploads/`

---

## Estrutura do Projeto

php-app/
│
├── public/
│ ├── index.php # Catálogo público
│ ├── dashboard.php # Painel administrativo
│ ├── styles.css # Estilos globais
│ └── uploads/ # Imagens dos imóveis
│
├── src/
│ ├── PropertyRepository.php # Regras de acesso a dados
│ ├── Database.php # Conexão e schema
│ └── helpers.php # Funções utilitárias
│
├── config/
│ └── config.php # Configurações da aplicação
│
├── data/
│ └── imobihub.sqlite # Banco de dados
│
└── bootstrap.php # Inicialização da aplicação


---

## Como Executar o Projeto
1. Instale o PHP 8.1 ou superior  
2. No terminal:

```bash
cd php-app
php -S localhost:8000 -t public
```

Acesse:

http://localhost:8000/

http://localhost:8000/dashboard.php

## Arquitetura

O sistema segue uma arquitetura simples com separação de responsabilidades:
- Camada de apresentação: public/*.php
- Camada de dados: src/PropertyRepository.php
- Infraestrutura: src/Database.php
- Utilitários: src/helpers.php

## Boas Práticas

- Uso de declare(strict_types=1);
- Validação de dados de entrada
- Uso de PDO com parâmetros (evita SQL Injection)
- Escape de saída HTML (proteção contra XSS)
- Separação de responsabilidades no código

## Fluxo de Desenvolvimento

- Criar branch de feature
- Rodar servidor local
- Testar catálogo e dashboard
- Validar sintaxe:

php -l php-app/public/index.php
php -l php-app/public/dashboard.php
php -l php-app/src/PropertyRepository.php

- Commit com mensagem descritiva
- Abrir Pull Request no GitHub

## Equipe

Alexandre Rodrigues Ramos – 0021171

Fellipe Ferreira Gomes – 0021345

Icaro Kaic Bernardes Rocha – 0021391

Raycca Mell dos Santos – 0020850 (Gerente do Projeto)

Wallyson Freitas Alves – 0020879
