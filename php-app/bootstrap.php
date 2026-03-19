<?php

declare(strict_types=1);

use ImobiHub\Database;
use ImobiHub\PropertyRepository;

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/PropertyRepository.php';
require_once __DIR__ . '/src/helpers.php';

$config = require __DIR__ . '/config/config.php';

// Garante que o diretorio de uploads exista antes de processar formularios.
if (!is_dir($config['upload_dir'])) {
    mkdir($config['upload_dir'], 0777, true);
}

// Sessao e usada para token CSRF e mensagens de seguranca.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = new Database($config['db_path']);
$db->initializeSchema();

$repository = new PropertyRepository($db->pdo());
// Popula o banco apenas no primeiro boot local.
$repository->seedIfEmpty();
