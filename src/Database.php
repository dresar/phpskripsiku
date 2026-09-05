<?php
/**
 * PDO Database Connection - Singleton
 * Prepared statements only, aman dari SQL injection
 */

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function config(array $config): void
    {
        self::$config = $config;
    }

    public static function get(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }

    private static function createConnection(): PDO
    {
        $config = self::$config ?: require __DIR__ . '/../config/database.php';
        self::$config = $config;

        if (($config['driver'] ?? '') === 'sqlite') {
            $path = $config['path'] ?? __DIR__ . '/../database/monitoring.db';
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $dsn = 'sqlite:' . $path;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdo = new PDO($dsn, null, null, $options);
        } else {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'] ?? 'localhost',
                $config['dbname'] ?? 'monitoring',
                $config['charset'] ?? 'utf8mb4'
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdo = new PDO(
                $dsn,
                $config['user'] ?? '',
                $config['password'] ?? '',
                $options
            );
        }

        return $pdo;
    }

    public static function ensureTable(): void
    {
        $pdo = self::get();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS readings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ph REAL,
                tds REAL,
                suhu REAL,
                status TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}
