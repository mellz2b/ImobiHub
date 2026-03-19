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

## Guia rapido para contribuicao

### Organizacao do codigo

- Camada HTTP/UI: `php-app/public/*.php`
- Camada de dados: `php-app/src/PropertyRepository.php`
- Infraestrutura de banco: `php-app/src/Database.php`
- Utilitarios comuns: `php-app/src/helpers.php`
- Bootstrap da aplicacao: `php-app/bootstrap.php`

### Boas praticas adotadas no projeto

- Sempre usar `declare(strict_types=1);` nos arquivos PHP.
- Validar entrada de formulario antes de chamar o repositorio.
- Usar bind de parametros no PDO (evitar SQL interpolada).
- Escapar saida HTML com `e()` para prevenir XSS.
- Manter mensagens de erro simples para o usuario e detalhamento no codigo.

### Fluxo de desenvolvimento sugerido

1. Criar branch de feature.
2. Rodar servidor local e testar catalogo e dashboard.
3. Validar sintaxe:

```bash
php -l php-app/public/index.php
php -l php-app/public/dashboard.php
php -l php-app/src/PropertyRepository.php
```

4. Commit com mensagem descritiva.
5. Abrir PR no GitHub com resumo da mudanca e riscos.
