# ImobiHub - PHP

Aplicacao web para imobiliaria com foco no ODS 11 (Cidades e Comunidades Sustentaveis), implementada em PHP + SQLite.

## Modulos

- Catalogo publico em `php-app/public/index.php`
- Dashboard de gestao em `php-app/public/dashboard.php`

## Funcionalidades

- Cadastro de anuncio com upload de fotos
- Edicao de anuncio cadastrado
- Edicao rapida de preco
- Exclusao de anuncio
- Alternancia de status vendido/disponivel
- Filtros no catalogo (tipo, busca, ordenacao e vendidos)

## Persistencia

- Banco SQLite em `php-app/data/imobihub.sqlite`
- Uploads em `php-app/public/uploads/`

## Estrutura principal

- `php-app/public/index.php`: catalogo publico
- `php-app/public/dashboard.php`: painel administrativo
- `php-app/public/styles.css`: estilos globais
- `php-app/src/PropertyRepository.php`: regras de acesso a dados
- `php-app/src/Database.php`: conexao e schema
- `php-app/src/helpers.php`: funcoes utilitarias
- `php-app/config/config.php`: configuracoes da aplicacao

## Executar localmente

1. Instale PHP 8.1+.
2. Rode no terminal:

```bash
cd php-app
php -S localhost:8000 -t public
```

3. Acesse:

- `http://localhost:8000/`
- `http://localhost:8000/dashboard.php`
