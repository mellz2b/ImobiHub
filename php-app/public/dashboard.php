<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$allowedDealTypes = ['comprar'];
$allowedPropertyTypes = ['apartamento', 'casa', 'imovel-comercial', 'terreno'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Token CSRF invalido. Atualize a pagina e tente novamente.';
    } else {
        $action = request_post_string('action');

        if ($action === 'create') {
            $data = [
                'title' => request_post_string('title'),
                'deal_type' => request_post_string('deal_type'),
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

            foreach (['title', 'city', 'neighborhood', 'description', 'sustainability_tag'] as $requiredField) {
                if ($data[$requiredField] === '') {
                    $errors[] = 'Preencha todos os campos obrigatorios.';
                    break;
                }
            }

            if (!in_array($data['deal_type'], $allowedDealTypes, true)) {
                $errors[] = 'Tipo de negocio invalido.';
            }
            if (!in_array($data['property_type'], $allowedPropertyTypes, true)) {
                $errors[] = 'Tipo de imovel invalido.';
            }

            $price = (float) $data['price'];
            $area = (int) $data['area'];
            $bedrooms = (int) $data['bedrooms'];
            $bathrooms = (int) $data['bathrooms'];

            if ($price <= 0 || $area <= 0 || $bedrooms < 0 || $bathrooms < 0) {
                $errors[] = 'Valores numericos invalidos.';
            }

            $photoPaths = [];
            if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
                $totalFiles = count($_FILES['photos']['name']);

                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
                        $errors[] = 'Falha ao enviar uma das fotos.';
                        continue;
                    }

                    $tmpPath = (string) $_FILES['photos']['tmp_name'][$i];
                    $originalName = (string) $_FILES['photos']['name'][$i];
                    $size = (int) $_FILES['photos']['size'][$i];

                    if ($size > 5 * 1024 * 1024) {
                        $errors[] = 'Cada imagem deve ter no maximo 5MB.';
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
                        $errors[] = 'Apenas arquivos de imagem sao permitidos.';
                        continue;
                    }

                    $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
                    if ($extension === '') {
                        $extension = 'jpg';
                    }

                    $filename = 'img_' . bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-z0-9]/', '', $extension);
                    $destination = $config['upload_dir'] . DIRECTORY_SEPARATOR . $filename;

                    if (!move_uploaded_file($tmpPath, $destination)) {
                        $errors[] = 'Nao foi possivel salvar uma imagem no servidor.';
                        continue;
                    }

                    $photoPaths[] = $config['upload_web_prefix'] . '/' . $filename;
                }
            }

            if (count($errors) === 0) {
                $repository->create($data, $photoPaths);
                header('Location: /dashboard.php?ok=created');
                exit;
            }
        }

        if ($action === 'update_price') {
            $id = (int) request_post_string('id');
            $price = (float) request_post_string('price');

            if ($id > 0 && $price > 0) {
                $repository->updatePrice($id, $price);
                header('Location: /dashboard.php?ok=price');
                exit;
            }

            $errors[] = 'Nao foi possivel atualizar o preco.';
        }

        if ($action === 'toggle_sold') {
            $id = (int) request_post_string('id');

            if ($id > 0) {
                $repository->toggleSold($id);
                header('Location: /dashboard.php?ok=sold');
                exit;
            }

            $errors[] = 'Nao foi possivel atualizar o status do imovel.';
        }

          if ($action === 'edit') {
            $id = (int) request_post_string('id');
            $data = [
              'title' => request_post_string('title'),
              'deal_type' => 'comprar',
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

            foreach (['title', 'city', 'neighborhood', 'description', 'sustainability_tag'] as $requiredField) {
              if ($data[$requiredField] === '') {
                $errors[] = 'Preencha todos os campos obrigatorios da edicao.';
                break;
              }
            }

            if (!in_array($data['property_type'], $allowedPropertyTypes, true)) {
              $errors[] = 'Tipo de imovel invalido na edicao.';
            }

            $price = (float) $data['price'];
            $area = (int) $data['area'];
            $bedrooms = (int) $data['bedrooms'];
            $bathrooms = (int) $data['bathrooms'];

            if ($id <= 0 || $price <= 0 || $area <= 0 || $bedrooms < 0 || $bathrooms < 0) {
              $errors[] = 'Valores invalidos para editar o imovel.';
            }

            if (count($errors) === 0) {
              $repository->update($id, $data);
              header('Location: /dashboard.php?ok=edited');
              exit;
            }
          }

          if ($action === 'delete') {
            $id = (int) request_post_string('id');

            if ($id > 0) {
              $repository->delete($id);
              header('Location: /dashboard.php?ok=deleted');
              exit;
            }

            $errors[] = 'Nao foi possivel excluir o anuncio.';
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
