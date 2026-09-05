<?php
/**
 * API: Export CSV
 * GET /api/export.php?date_from=2025-03-01&date_to=2025-03-03
 */

declare(strict_types=1);

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
    $rows = $repo->getForExport($dateFrom, $dateTo, 5000);
    $filename = 'monitoring_export_' . date('Y-m-d_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
    fputcsv($out, ['id', 'ph', 'tds', 'suhu', 'status', 'created_at']);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['id'] ?? '',
            $row['ph'] ?? '',
            $row['tds'] ?? '',
            $row['suhu'] ?? '',
            $row['status'] ?? '',
            $row['created_at'] ?? '',
        ]);
    }
    fclose($out);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Export failed']);
}
