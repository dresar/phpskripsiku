<?php
/**
 * API: Statistik - rata-rata pH, TDS, total, distribusi status
 * GET /api/stats.php?date_from=2025-03-01&date_to=2025-03-03
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Database;
use App\ReadingRepository;

$dateFrom = !empty($_GET['date_from']) ? preg_replace('/[^0-9\-]/', '', $_GET['date_from']) : null;
$dateTo   = !empty($_GET['date_to']) ? preg_replace('/[^0-9\-]/', '', $_GET['date_to']) : null;
if ($dateFrom && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $dateFrom = null;
}
if ($dateTo && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $dateTo = null;
}

try {
    $pdo = Database::get();
    $repo = new ReadingRepository($pdo);
    $stats = $repo->getStats($dateFrom, $dateTo);
    echo json_encode([
        'success' => true,
        'data'    => $stats,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server error',
        'data'    => null,
    ]);
}
