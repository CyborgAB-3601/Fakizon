<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

$updates = [
    1 => 'assets/images/echo-dot.svg',
    2 => 'assets/images/kindle.svg',
    3 => 'assets/images/laptop.svg',
    4 => 'assets/images/headphones.svg',
    5 => 'assets/images/console.svg',
    6 => 'assets/images/camera.svg',
    7 => 'assets/images/mouse.svg',
    8 => 'assets/images/security-book.svg',
];

$stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
foreach ($updates as $id => $img) {
    $stmt->execute([$img, $id]);
}

$count = $pdo->query("SELECT count(*) FROM products WHERE image LIKE 'assets%'")->fetchColumn();
echo "Updated {$count} product images to local SVG assets.\n";
