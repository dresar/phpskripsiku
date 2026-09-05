<?php
/**
 * Development router: / → public/index.php, /api/* → api/*
 * Jalankan: php -S localhost:8000 router.php (dari folder project root)
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/public/index.php';
    return;
}
if (strpos($uri, '/api/') === 0) {
    $path = __DIR__ . '/api/' . substr($uri, 5);
    if (is_file($path)) {
        require $path;
        return;
    }
}
return false;
