<?php
declare(strict_types=1);

/**
 * Local development server router for PHP CLI:
 * Run with: php -S 0.0.0.0:8000 server.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$publicFile = __DIR__ . '/public' . $uri;

// If static file exists inside public/, serve it with the proper Content-Type
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimes = [
        'css'  => 'text/css; charset=UTF-8',
        'js'   => 'application/javascript; charset=UTF-8',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'pdf'  => 'application/pdf',
        'json' => 'application/json',
    ];

    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    header('Content-Length: ' . filesize($publicFile));
    readfile($publicFile);
    exit;
}

// Otherwise pass all dynamic requests to public/index.php
require_once __DIR__ . '/public/index.php';
