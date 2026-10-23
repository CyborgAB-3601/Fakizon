<?php
/**
 * Vercel Serverless Entry Point Router for Fakizon (ExploitZero)
 * Dispatches serverless requests to the appropriate PHP script in the project root.
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH);
$path = trim($parsedPath ?? '/', '/');

$rootDir = realpath(__DIR__ . '/..');

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
