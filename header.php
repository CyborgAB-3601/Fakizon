<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

$currentUser = $_SESSION['user'] ?? null;
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_column($cart, 'quantity'));

// Fetch categories for search bar dropdown and subnav
$stmtCategories = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$allCategories = $stmtCategories->fetchAll();

$isVuln = is_vulnerable();
$currentUrl = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Fakizon' : 'Fakizon.com: Spend less. Smile more.' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Main Amazon Header -->
<header class="fakizon-navbar">
    <!-- Logo -->
    <a href="index.php" class="nav-logo-box">
        <div class="fakizon-logo">
            <span class="logo-text">fakizon</span>
            <span class="logo-smile"><i class="fa-solid fa-arrow-trend-up"></i> prime</span>
        </div>
    </a>

    <!-- Deliver To -->
    <div class="nav-deliver-to">
        <i class="fa-solid fa-location-dot"></i>
        <div class="nav-deliver-text">
            <span class="deliver-sub">Deliver to <?= $currentUser ? htmlspecialchars($currentUser['username']) : 'Guest' ?></span>
            <span class="deliver-bold">Seattle 98101</span>
        </div>
    </div>

    <!-- Search Bar -->
    <form action="search.php" method="GET" class="nav-search-bar">
        <select name="category" class="nav-search-category">
            <option value="">All Departments</option>
            <?php foreach ($allCategories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= (isset($_GET['category']) && $_GET['category'] === $cat['slug']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="q" class="nav-search-input" placeholder="Search Fakizon products, brands and deals..." value="<?= isset($_GET['q']) ? ($isVuln ? $_GET['q'] : htmlspecialchars($_GET['q'])) : '' ?>" required>
        <button type="submit" class="nav-search-btn" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>

    <!-- Right Controls -->
    <div class="nav-right-items">
        <!-- Account / Login -->
        <?php if ($currentUser): ?>
            <a href="profile.php" class="nav-link-item">
                <span class="nav-link-sub">Hello, <?= htmlspecialchars($currentUser['full_name']) ?></span>
                <span class="nav-link-bold">Account & Lists <i class="fa-solid fa-caret-down" style="font-size:0.7rem;"></i></span>
            </a>
            <a href="logout.php" class="nav-link-item" title="Sign Out">
                <span class="nav-link-sub">Exit</span>
                <span class="nav-link-bold">Sign Out</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="nav-link-item">
                <span class="nav-link-sub">Hello, sign in</span>
                <span class="nav-link-bold">Account & Lists <i class="fa-solid fa-caret-down" style="font-size:0.7rem;"></i></span>
            </a>
        <?php endif; ?>

        <!-- Orders -->
        <a href="orders.php" class="nav-link-item">
            <span class="nav-link-sub">Returns</span>
            <span class="nav-link-bold">& Orders</span>
        </a>

        <!-- Cart -->
        <a href="cart.php" class="nav-cart-btn">
            <div class="cart-icon-wrapper">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-counter"><?= $cartCount ?></span>
            </div>
            <span class="cart-label">Cart</span>
        </a>
    </div>
</header>

<!-- Sub Navigation -->
<nav class="fakizon-subnav">
    <a href="index.php" class="subnav-item"><i class="fa-solid fa-bars"></i> All</a>
    <a href="index.php?filter=deals" class="subnav-item subnav-highlight"><i class="fa-solid fa-bolt"></i> Today's Deals</a>
    <?php foreach ($allCategories as $cat): ?>
        <a href="search.php?category=<?= urlencode($cat['slug']) ?>" class="subnav-item">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
    <a href="orders.php" class="subnav-item">Order History</a>
    <a href="admin.php" class="subnav-item"><i class="fa-solid fa-gear"></i> Seller Central</a>
    <a href="#" class="subnav-item">Customer Service</a>
</nav>

<main class="main-content">
