<?php
$pageTitle = "Online Shopping for Electronics, Books, Smart Home & More";
require_once __DIR__ . '/header.php';

// Handle quick add-to-cart from homepage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_cart') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    $stmtCheck = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmtCheck->execute([$productId]);
    $prod = $stmtCheck->fetch();

    if ($prod) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'id' => $prod['id'],
                'name' => $prod['name'],
                'price' => $prod['price'],
                'image' => $prod['image'],
                'quantity' => $quantity
            ];
        }

        header("Location: cart.php?added=1");
        exit;
    }
}

// Fetch products based on deals filter or all
$filter = $_GET['filter'] ?? '';
if ($filter === 'deals') {
    $stmt = $pdo->query("SELECT * FROM products WHERE badge != '' ORDER BY price ASC");
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
}
$products = $stmt->fetchAll();
?>

<!-- Hero Section with Amazon Vibe -->
<section class="fakizon-hero">
    <div class="hero-text-box">
        <h1>Welcome to Fakizon</h1>
        <p>Explore thousands of tech gadgets, kindle readers, and smart home essentials with Prime same-day delivery.</p>
        <div class="hero-buttons">
            <a href="#products-showcase" class="btn btn-add-cart" style="width: auto; padding: 10px 24px;">Shop Deals Today</a>
            <a href="search.php?category=electronics" class="btn btn-secondary" style="width: auto; padding: 10px 20px;">
                <i class="fa-solid fa-bolt" style="color: #f59e0b; margin-right: 6px;"></i> Explore Tech &amp; Gadgets
            </a>
        </div>
    </div>
</section>

<!-- Category Teaser 4-Card Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-top: -80px; position: relative; z-index: 20; margin-bottom: 30px;">
    <?php
    $teaserCats = array_slice($allCategories, 0, 4);
    foreach ($teaserCats as $tCat):
        // Fetch 1 sample product image for category
        $stmtCatSample = $pdo->prepare("SELECT image FROM products WHERE category_id = ? LIMIT 1");
        $stmtCatSample->execute([$tCat['id']]);
        $sampleImg = $stmtCatSample->fetchColumn() ?: 'assets/images/placeholder.svg';
    ?>
    <div style="background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); display: flex; flex-direction: column;">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 12px;"><?= htmlspecialchars($tCat['name']) ?></h3>
        <div style="height: 180px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 14px;">
            <img src="<?= htmlspecialchars($sampleImg) ?>" alt="<?= htmlspecialchars($tCat['name']) ?>" onerror="this.src='assets/images/placeholder.svg'" style="max-height: 100%; max-width: 100%; object-fit: contain;">
        </div>
        <a href="search.php?category=<?= urlencode($tCat['slug']) ?>" style="font-size: 0.85rem; font-weight: 600; margin-top: auto;">Explore <?= htmlspecialchars($tCat['name']) ?> &rarr;</a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Main Products Showcase -->
<div id="products-showcase" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px;">
        <div>
            <h2 style="font-size: 1.4rem; font-weight: 700;"><?= $filter === 'deals' ? "Today's Prime Lightning Deals" : "Popular Items & Featured Tech" ?></h2>
            <p style="color: var(--text-muted); font-size: 0.88rem;">Handpicked products tested for quality and customer satisfaction</p>
        </div>
        <div>
            <a href="index.php" class="btn btn-secondary" style="width: auto; padding: 6px 14px; font-size: 0.82rem; <?= empty($filter) ? 'background:#e2e8f0;' : '' ?>">All Products</a>
            <a href="index.php?filter=deals" class="btn btn-secondary" style="width: auto; padding: 6px 14px; font-size: 0.82rem; <?= $filter === 'deals' ? 'background:#e2e8f0;' : '' ?>">Deals Only</a>
        </div>
    </div>

    <div class="product-grid" style="margin-top: 0;">
        <?php foreach ($products as $prod): ?>
            <div class="product-card">
                <?php if (!empty($prod['badge'])): ?>
                    <span class="product-badge <?= stripos($prod['badge'], 'deal') !== false || stripos($prod['badge'], 'save') !== false ? 'deal' : '' ?>">
                        <?= htmlspecialchars($prod['badge']) ?>
                    </span>
                <?php endif; ?>

                <a href="product.php?id=<?= $prod['id'] ?>" class="product-img-box">
                    <img src="<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" onerror="this.src='assets/images/placeholder.svg'" loading="lazy">
                </a>

                <a href="product.php?id=<?= $prod['id'] ?>" class="product-title" title="<?= htmlspecialchars($prod['name']) ?>">
                    <?= htmlspecialchars($prod['name']) ?>
                </a>

                <div class="product-rating">
                    <span class="star-gold">
                        <?php 
                        $stars = round($prod['rating']);
                        for ($i = 0; $i < 5; $i++) {
                            echo ($i < $stars) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                        }
                        ?>
                    </span>
                    <span class="reviews-num">(<?= number_format($prod['reviews_count']) ?>)</span>
                </div>

                <div class="product-price-row">
                    <span class="price-currency">$</span>
                    <span class="price-main"><?= number_format($prod['price'], 2) ?></span>
                    <?php if ($prod['original_price'] > $prod['price']): ?>
                        <span class="original-price">$<?= number_format($prod['original_price'], 2) ?></span>
                    <?php endif; ?>
                </div>

                <div class="prime-delivery">
                    <span class="prime-tag">prime</span> FREE One-Day Delivery by Tomorrow 8 PM
                </div>

                <div style="display: flex; gap: 8px;">
                    <form method="POST" action="index.php" style="flex: 1;">
                        <input type="hidden" name="action" value="add_cart">
                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-add-cart">Add to Cart</button>
                    </form>
                    <a href="product.php?id=<?= $prod['id'] ?>" class="btn btn-secondary" style="width: auto; padding: 0 12px;" title="View Details">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
