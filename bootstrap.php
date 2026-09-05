<?php
/**
 * Bootstrap - autoload & config
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$configDb = require __DIR__ . '/config/database.php';
\App\Database::config($configDb);
\App\Database::ensureTable();
