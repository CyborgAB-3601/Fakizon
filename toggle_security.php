<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current = $_SESSION['security_mode'] ?? 'vulnerable';
$_SESSION['security_mode'] = ($current === 'vulnerable') ? 'secure' : 'vulnerable';

$redirect = $_GET['ref'] ?? 'index.php';
// Prevent open redirect
if (strpos($redirect, 'http://') === 0 || strpos($redirect, 'https://') === 0 || strpos($redirect, '//') === 0) {
    $redirect = 'index.php';
}

header("Location: " . $redirect);
exit;
