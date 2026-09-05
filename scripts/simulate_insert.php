<?php
/**
 * Simulasi insert data (testing tanpa ESP32)
 * Jalankan: php scripts/simulate_insert.php
 * Atau kirim manual ke MQTT topic latex/monitoring dengan payload JSON.
 * Script ini insert langsung ke SQLite untuk testing dashboard.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Database;
use App\ReadingRepository;

$samples = [
    ['ph' => 6.85, 'tds' => 520, 'suhu' => 29.5, 'status' => 'Mutu Prima'],
    ['ph' => 6.92, 'tds' => 480, 'suhu' => 28.8, 'status' => 'Mutu Prima'],
    ['ph' => 6.45, 'tds' => 380, 'suhu' => 30.2, 'status' => 'Mutu Rendah'],
    ['ph' => 7.10, 'tds' => 550, 'suhu' => 29.0, 'status' => 'Terawetkan'],
    ['ph' => 6.70, 'tds' => 420, 'suhu' => 28.5, 'status' => 'Mutu Prima'],
    ['ph' => 5.80, 'tds' => 200, 'suhu' => 31.0, 'status' => 'Oplos Air'],
    ['ph' => 6.95, 'tds' => 510, 'suhu' => 29.2, 'status' => 'Mutu Prima'],
];

$pdo = Database::get();
$repo = new ReadingRepository($pdo);

echo "Inserting " . count($samples) . " sample readings...\n";
foreach ($samples as $s) {
    $id = $repo->insert($s['ph'], $s['tds'], $s['suhu'], $s['status']);
    echo "  Inserted id={$id} ph={$s['ph']} tds={$s['tds']} suhu={$s['suhu']} status={$s['status']}\n";
}
echo "Done. Buka dashboard dan refresh.\n";
