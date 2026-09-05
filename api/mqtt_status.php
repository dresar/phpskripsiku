<?php
/**
 * API: Status koneksi MQTT (berdasarkan last seen heartbeat)
 * GET /api/mqtt_status.php
 * Anggap MQTT "aktif" jika ada data dalam 2 menit terakhir
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$file = dirname(__DIR__) . '/database/mqtt_last_seen.txt';
$threshold = 120; // 2 menit
$lastSeen = @file_get_contents($file);
$ts = $lastSeen !== false ? (int) trim($lastSeen) : 0;
$active = ($ts > 0 && (time() - $ts) <= $threshold);

echo json_encode([
    'success' => true,
    'mqtt_active' => $active,
    'last_seen' => $ts ? date('Y-m-d H:i:s', $ts) : null,
]);
