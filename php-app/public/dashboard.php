<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$allowedDealTypes = ['comprar'];
$allowedPropertyTypes = ['apartamento', 'casa', 'imovel-comercial', 'terreno'];
$errors = [];

/**
 * Campos textuais obrigatorios compartilhados entre cadastro e edicao.
 * Mantemos essa lista centralizada para evitar divergencia de regras.
 */
const REQUIRED_TEXT_FIELDS = ['title', 'city', 'neighborhood', 'description', 'sustainability_tag'];

function redirect_dashboard(string $status): void
{
  header('Location: /dashboard.php?ok=' . urlencode($status));
  exit;
}

function collect_property_payload(bool $forceComprar = false): array
{
  return [
    'title' => request_post_string('title'),
    'deal_type' => $forceComprar ? 'comprar' : request_post_string('deal_type'),
    'property_type' => request_post_string('property_type'),
    'city' => request_post_string('city'),
    'neighborhood' => request_post_string('neighborhood'),
    'price' => request_post_string('price'),
    'area' => request_post_string('area'),
    'bedrooms' => request_post_string('bedrooms'),
    'bathrooms' => request_post_string('bathrooms'),
    'description' => request_post_string('description'),
    'sustainability_tag' => request_post_string('sustainability_tag'),
  ];
}

function validate_property_payload(
  array $data,
  array $allowedDealTypes,
  array $allowedPropertyTypes,
  string $requiredMessage,
  string $invalidTypeMessage,
  string $invalidNumbersMessage
): array {
  $validationErrors = [];

  foreach (REQUIRED_TEXT_FIELDS as $requiredField) {
    if ($data[$requiredField] === '') {
      $validationErrors[] = $requiredMessage;
      break;
    }
  }

  if (!in_array($data['deal_type'], $allowedDealTypes, true) || !in_array($data['property_type'], $allowedPropertyTypes, true)) {
    $validationErrors[] = $invalidTypeMessage;
  }

  $price = (float) $data['price'];
  $area = (int) $data['area'];
  $bedrooms = (int) $data['bedrooms'];
  $bathrooms = (int) $data['bathrooms'];

  if ($price <= 0 || $area <= 0 || $bedrooms < 0 || $bathrooms < 0) {
    $validationErrors[] = $invalidNumbersMessage;
  }

  return $validationErrors;
}

function upload_property_photos(array $files, array $config): array
{
  $uploadErrors = [];
  $photoPaths = [];

  if (!isset($files['photos']) || !is_array($files['photos']['name'])) {
    return ['errors' => $uploadErrors, 'paths' => $photoPaths];
  }

  $totalFiles = count($files['photos']['name']);

  for ($i = 0; $i < $totalFiles; $i++) {
    if ($files['photos']['error'][$i] === UPLOAD_ERR_NO_FILE) {
      continue;
    }

    if ($files['photos']['error'][$i] !== UPLOAD_ERR_OK) {
      $uploadErrors[] = 'Falha ao enviar uma das fotos.';
      continue;
    }

    $tmpPath = (string) $files['photos']['tmp_name'][$i];
    $originalName = (string) $files['photos']['name'][$i];
    $size = (int) $files['photos']['size'][$i];

    if ($size > 5 * 1024 * 1024) {
      $uploadErrors[] = 'Cada imagem deve ter no maximo 5MB.';
      continue;
    }

    $mime = '';
    if (function_exists('finfo_open')) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      if ($finfo !== false) {
        $mime = (string) finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
      }
    }

    if ($mime === '') {
      $mime = (string) mime_content_type($tmpPath);
    }

    if (strpos($mime, 'image/') !== 0) {
      $uploadErrors[] = 'Apenas arquivos de imagem sao permitidos.';
      continue;
    }

    $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
    if ($extension === '') {
      $extension = 'jpg';
    }

    $filename = 'img_' . bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-z0-9]/', '', $extension);
    $destination = $config['upload_dir'] . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpPath, $destination)) {
      $uploadErrors[] = 'Nao foi possivel salvar uma imagem no servidor.';
      continue;
    }

    $photoPaths[] = $config['upload_web_prefix'] . '/' . $filename;
  }

  return ['errors' => $uploadErrors, 'paths' => $photoPaths];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Token CSRF invalido. Atualize a pagina e tente novamente.';
    } else {
        $action = request_post_string('action');

    switch ($action) {
      case 'create':
        $data = collect_property_payload();
        $errors = array_merge(
          $errors,
          validate_property_payload(
            $data,
            $allowedDealTypes,
            $allowedPropertyTypes,
            'Preencha todos os campos obrigatorios.',
            'Tipo de negocio ou tipo de imovel invalido.',
            'Valores numericos invalidos.'
          )
        );

        $uploadResult = upload_property_photos($_FILES, $config);
        $errors = array_merge($errors, $uploadResult['errors']);

        if (count($errors) === 0) {
          $repository->create($data, $uploadResult['paths']);
          redirect_dashboard('created');
        }
        break;

      case 'update_price':
        $id = (int) request_post_string('id');
        $price = (float) request_post_string('price');

        if ($id > 0 && $price > 0) {
          $repository->updatePrice($id, $price);
          redirect_dashboard('price');
        }

        $errors[] = 'Nao foi possivel atualizar o preco.';
        break;

      case 'toggle_sold':
        $id = (int) request_post_string('id');

        if ($id > 0) {
          $repository->toggleSold($id);
          redirect_dashboard('sold');
        }

        $errors[] = 'Nao foi possivel atualizar o status do imovel.';
        break;

      case 'edit':
        $id = (int) request_post_string('id');
        $data = collect_property_payload(true);

        $errors = array_merge(
          $errors,
          validate_property_payload(
            $data,
            ['comprar'],
            $allowedPropertyTypes,
            'Preencha todos os campos obrigatorios da edicao.',
            'Tipo de imovel invalido na edicao.',
            'Valores invalidos para editar o imovel.'
          )
        );

        if ($id <= 0) {
          $errors[] = 'Identificador de anuncio invalido para edicao.';
        }

        if (count($errors) === 0) {
          $repository->update($id, $data);
          redirect_dashboard('edited');
        }
        break;

      case 'delete':
        $id = (int) request_post_string('id');

        if ($id > 0) {
          $repository->delete($id);
          redirect_dashboard('deleted');
        }

        $errors[] = 'Nao foi possivel excluir o anuncio.';
        break;

      default:
        $errors[] = 'Acao desconhecida.';
        break;
    }
    }
}

$stats = $repository->stats();
$properties = $repository->listAll();
$ok = request_get_string('ok');
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ImobiHub PHP - Dashboard</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
  <header class="topbar">
    <div class="container topbar-inner">
      <div>
        <h1>Dashboard da Imobiliaria</h1>
        <p>Gestao de anuncios, preco, status e fotos</p>
      </div>
      <a class="button" href="/index.php">Ver catalogo publico</a>
    </div>
  </header>

  <main class="container page-content">
    <?php if ($ok !== ''): ?>
      <section class="notice success">Operacao concluida com sucesso.</section>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
      <section class="notice error"><?= e($error) ?></section>
    <?php endforeach; ?>

    <section class="stats-grid">
      <article class="panel stat-card">
        <small>Total</small>
        <strong><?= (int) $stats['total'] ?></strong>
      </article>
      <article class="panel stat-card">
        <small>Disponiveis</small>
        <strong><?= (int) $stats['available'] ?></strong>
      </article>
      <article class="panel stat-card">
        <small>Vendidos</small>
        <strong><?= (int) $stats['sold'] ?></strong>
      </article>
    </section>

    <section class="panel">
      <h2>Cadastrar imovel</h2>
      <form method="post" enctype="multipart/form-data" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">

        <label>
          Titulo
          <input name="title" required>
        </label>

        <label>
          Tipo de negocio
          <select name="deal_type" required>
            <?php foreach ($allowedDealTypes as $item): ?>
              <option value="<?= e($item) ?>"><?= e(deal_type_label($item)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Tipo de imovel
          <select name="property_type" required>
            <?php foreach ($allowedPropertyTypes as $item): ?>
              <option value="<?= e($item) ?>"><?= e(property_type_label($item)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Cidade
          <input name="city" required>
        </label>

        <label>
          Bairro
          <input name="neighborhood" required>
        </label>

        <label>
          Preco
          <input name="price" type="number" min="1" required>
        </label>

        <label>
          Area (m2)
          <input name="area" type="number" min="1" required>
        </label>

        <label>
          Quartos
          <input name="bedrooms" type="number" min="0" required>
        </label>

        <label>
          Banheiros
          <input name="bathrooms" type="number" min="0" required>
        </label>

        <label>
          Tag de sustentabilidade
          <input name="sustainability_tag" required>
        </label>

        <label class="full">
          Fotos (JPG/PNG/WebP)
          <input name="photos[]" type="file" accept="image/*" multiple>
        </label>

        <label class="full">
          Descricao
          <textarea name="description" required></textarea>
        </label>

        <button class="button button-dark full" type="submit">Cadastrar imovel</button>
      </form>
    </section>

    <section>
      <h2>Anuncios cadastrados</h2>
      <div class="list">
        <?php foreach ($properties as $item): ?>
          <article class="panel list-item">
            <div class="list-head">
              <div>
                <h3><?= e($item['title']) ?></h3>
                <p><?= e($item['neighborhood']) ?>, <?= e($item['city']) ?></p>
                <p class="meta-row">
                  <span><?= e(deal_type_label($item['deal_type'])) ?></span>
                  <span><?= e(property_type_label($item['property_type'])) ?></span>
                </p>
              </div>
              <span class="badge <?= $item['sold'] ? 'badge-muted' : 'badge-primary' ?>">
                <?= $item['sold'] ? 'Vendido' : 'Disponivel' ?>
              </span>
            </div>

            <div class="list-actions">
              <form method="post" class="inline-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update_price">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <input type="number" min="1" name="price" value="<?= (int) $item['price'] ?>" required>
                <button class="button" type="submit">Atualizar preco</button>
              </form>

              <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="toggle_sold">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button class="button button-dark" type="submit">
                  <?= $item['sold'] ? 'Marcar como disponivel' : 'Marcar como vendido' ?>
                </button>
              </form>

              <details>
                <summary class="button">Editar anuncio</summary>
                <form method="post" class="form-grid edit-box">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">

                  <label>
                    Titulo
                    <input name="title" value="<?= e((string) $item['title']) ?>" required>
                  </label>

                  <label>
                    Tipo de imovel
                    <select name="property_type" required>
                      <?php foreach ($allowedPropertyTypes as $propertyType): ?>
                        <option value="<?= e($propertyType) ?>" <?= $propertyType === $item['property_type'] ? 'selected' : '' ?>><?= e(property_type_label($propertyType)) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>

                  <label>
                    Cidade
                    <input name="city" value="<?= e((string) $item['city']) ?>" required>
                  </label>

                  <label>
                    Bairro
                    <input name="neighborhood" value="<?= e((string) $item['neighborhood']) ?>" required>
                  </label>

                  <label>
                    Preco
                    <input name="price" type="number" min="1" value="<?= (int) $item['price'] ?>" required>
                  </label>

                  <label>
                    Area (m2)
                    <input name="area" type="number" min="1" value="<?= (int) $item['area'] ?>" required>
                  </label>

                  <label>
                    Quartos
                    <input name="bedrooms" type="number" min="0" value="<?= (int) $item['bedrooms'] ?>" required>
                  </label>

                  <label>
                    Banheiros
                    <input name="bathrooms" type="number" min="0" value="<?= (int) $item['bathrooms'] ?>" required>
                  </label>

                  <label>
                    Tag de sustentabilidade
                    <input name="sustainability_tag" value="<?= e((string) $item['sustainability_tag']) ?>" required>
                  </label>

                  <label class="full">
                    Descricao
                    <textarea name="description" required><?= e((string) $item['description']) ?></textarea>
                  </label>

                  <button class="button" type="submit">Salvar edicao</button>
                </form>
              </details>

              <form method="post" onsubmit="return confirm('Deseja excluir este anuncio?');">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button class="button button-danger" type="submit">Excluir anuncio</button>
              </form>
            </div>

            <?php if (!empty($item['photos'])): ?>
              <div class="thumbs">
                <?php foreach ($item['photos'] as $photo): ?>
                  <img src="<?= e((string) $photo) ?>" alt="Foto do imovel">
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
</body>
</html>
