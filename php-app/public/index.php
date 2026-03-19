<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$dealType = request_get_string('dealType', 'comprar');
$propertyType = request_get_string('propertyType', 'todos');
$search = request_get_string('q');
$sort = request_get_string('sort', 'default');
$showSold = request_get_string('showSold') === '1';

$allowedDealTypes = ['comprar'];
$allowedPropertyTypes = ['todos', 'apartamento', 'casa', 'imovel-comercial', 'terreno'];
$allowedSort = ['default', 'latest', 'affordable'];

if (!in_array($dealType, $allowedDealTypes, true)) {
    $dealType = 'comprar';
}
if (!in_array($propertyType, $allowedPropertyTypes, true)) {
    $propertyType = 'todos';
}
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'default';
}

$filters = [
    'deal_type' => $dealType,
    'property_type' => $propertyType,
    'q' => $search,
    'sort' => $sort,
    'show_sold' => $showSold,
];

$properties = $repository->listPublic($filters);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ImobiHub PHP - Catalogo</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
  <header class="topbar">
    <div class="container topbar-inner">
      <div>
        <h1>ImobiHub </h1>
        <p>Catalogo publico de imoveis</p>
      </div>
      <a class="button button-dark" href="/dashboard.php">Abrir dashboard</a>
    </div>
  </header>

  <main class="container page-content">
    <section class="panel">
      <form method="get" class="filters">
        <label>
          Negocio
          <select name="dealType">
            <?php foreach ($allowedDealTypes as $item): ?>
              <option value="<?= e($item) ?>" <?= $item === $dealType ? 'selected' : '' ?>><?= e(deal_type_label($item)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Tipo
          <select name="propertyType">
            <?php foreach ($allowedPropertyTypes as $item): ?>
              <option value="<?= e($item) ?>" <?= $item === $propertyType ? 'selected' : '' ?>><?= e($item === 'todos' ? 'Todos os tipos' : property_type_label($item)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Busca
          <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cidade, bairro, descricao">
        </label>

        <label>
          Ordenacao
          <select name="sort">
            <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Padrao</option>
            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Mais recentes</option>
            <option value="affordable" <?= $sort === 'affordable' ? 'selected' : '' ?>>Mais baratos</option>
          </select>
        </label>

        <label class="checkline">
          <input type="checkbox" name="showSold" value="1" <?= $showSold ? 'checked' : '' ?>>
          Mostrar vendidos
        </label>

        <button class="button button-dark" type="submit">Buscar</button>
      </form>
    </section>

    <section class="result-header">
      <h2>Resultados (<?= count($properties) ?>)</h2>
      <p>Filtro ativo: <?= e(deal_type_label($dealType)) ?><?= $propertyType !== 'todos' ? ' | ' . e(property_type_label($propertyType)) : '' ?></p>
    </section>

    <section class="cards">
      <?php foreach ($properties as $item): ?>
        <article class="card <?= $item['sold'] ? 'is-sold' : '' ?>">
          <div class="card-cover">
            <?php if (!empty($item['photos'][0])): ?>
              <img src="<?= e((string) $item['photos'][0]) ?>" alt="<?= e($item['title']) ?>">
            <?php else: ?>
              <div class="cover-placeholder">Sem foto</div>
            <?php endif; ?>
            <span class="badge <?= $item['sold'] ? 'badge-muted' : 'badge-primary' ?>">
              <?= $item['sold'] ? 'Vendido' : 'Disponivel' ?>
            </span>
          </div>
          <div class="card-body">
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['neighborhood']) ?>, <?= e($item['city']) ?></p>
            <div class="meta-row">
              <span><?= e(deal_type_label($item['deal_type'])) ?></span>
              <span><?= e(property_type_label($item['property_type'])) ?></span>
              <span><?= e($item['sustainability_tag']) ?></span>
            </div>
            <p class="desc"><?= e($item['description']) ?></p>
            <p class="price"><?= e(currency_br((float) $item['price'])) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

    <?php if (count($properties) === 0): ?>
      <section class="panel empty-state">
        <p>Nenhum imovel encontrado para os filtros selecionados.</p>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
