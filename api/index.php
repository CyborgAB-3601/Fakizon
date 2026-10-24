<?php
/**
 * Vercel Serverless Entry Point Router for Fakizon (ExploitZero)
 * Dispatches serverless requests to the appropriate PHP script in the project root.
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH);
$path = trim($parsedPath ?? '/', '/');

$rootDir = realpath(__DIR__ . '/..');

// Handle dynamic serving of uploaded assets (persisted in /tmp/uploads on Vercel)
if (strpos($path, 'assets/uploads/') === 0) {
    $uploadFileName = basename($path);
    $possiblePaths = [
        '/tmp/uploads/' . $uploadFileName,
        $rootDir . '/assets/uploads/' . $uploadFileName,
    ];
    foreach ($possiblePaths as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            $ext = strtolower(pathinfo($uploadFileName, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'svg'  => 'image/svg+xml',
                'html' => 'text/html',
                'htm'  => 'text/html',
                'txt'  => 'text/plain',
                'php'  => 'text/plain',
            ];
            $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: public, max-age=3600');
            readfile($filePath);
            exit;
        }
    }
}

// Default home
if ($path === '' || $path === 'index.php') {
    require $rootDir . '/index.php';
    exit;
}

// 1. Direct file match (e.g. login.php, product.php)
$targetFile = realpath($rootDir . '/' . $path);
if ($targetFile && strpos($targetFile, $rootDir) === 0 && is_file($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    require $targetFile;
    exit;
}

// 2. Extensionless match (e.g. /login -> login.php)
$targetFilePhp = realpath($rootDir . '/' . $path . '.php');
if ($targetFilePhp && strpos($targetFilePhp, $rootDir) === 0 && is_file($targetFilePhp)) {
    require $targetFilePhp;
    exit;
}

// Fallback to index.php
require $rootDir . '/index.php';
