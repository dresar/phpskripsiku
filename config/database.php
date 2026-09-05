<?php
/**
 * Database Configuration - SQLite
 * Clean architecture: mudah diganti ke MySQL dengan mengubah DSN saja
 */

declare(strict_types=1);

return [
    'driver'   => 'sqlite',
    'path'     => __DIR__ . '/../database/monitoring.db',
    // Untuk migrasi ke MySQL nanti, gunakan:
    // 'driver' => 'mysql',
    // 'host' => 'localhost',
    // 'dbname' => 'monitoring',
    // 'user' => 'root',
    // 'password' => '',
    // 'charset' => 'utf8mb4',
];
