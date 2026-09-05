<?php
/**
 * API: Data terakhir (1 record)
 * GET /api/latest.php
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Database;
use App\ReadingRepository;

try {
    $pdo = Database::get();
    $repo = new ReadingRepository($pdo);
    $row = $repo->getLatest();
    echo json_encode([
        'success' => true,
        'data'    => $row,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server error',
        'data'    => null,
    ]);
}
