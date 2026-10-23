<?php
$rawQuery = $_GET['q'] ?? '';
$categorySlug = $_GET['category'] ?? '';

$pageTitle = "Search: " . ($rawQuery ?: 'All Products');
require_once __DIR__ . '/header.php';

$products = [];
$errorMsg = null;
$executedQuery = "";

// Security Lab: SQL Injection Point
if ($isVuln) {
    // VULNERABLE IMPLEMENTATION: Direct string concatenation into SQL
    $whereParts = [];
    if (!empty($rawQuery)) {
        // Intentionally flawed query concatenation for security testing
        $whereParts[] = "(name LIKE '%" . $rawQuery . "%' OR description LIKE '%" . $rawQuery . "%')";
    }
    if (!empty($categorySlug)) {
        $whereParts[] = "category_id IN (SELECT id FROM categories WHERE slug = '" . $categorySlug . "')";
    }

    $whereSql = !empty($whereParts) ? "WHERE " . implode(" AND ", $whereParts) : "";
    $executedQuery = "SELECT * FROM products " . $whereSql . " ORDER BY id DESC";

    try {
        $stmt = $pdo->query($executedQuery);
        $products = $stmt ? $stmt->fetchAll() : [];
    } catch (Exception $e) {
        $errorMsg = "Database error: " . $e->getMessage();
    }
} else {
    // SECURE / HARDENED IMPLEMENTATION: Parameterized Prepared Statements
    $sql = "SELECT p.* FROM products p ";
    $params = [];
    $where = [];

    if (!empty($categorySlug)) {
        $where[] = "p.category_id = (SELECT id FROM categories WHERE slug = :catSlug)";
        $params[':catSlug'] = $categorySlug;
    }

    if (!empty($rawQuery)) {
        $where[] = "(p.name LIKE :q OR p.description LIKE :q)";
        $params[':q'] = '%' . $rawQuery . '%';
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY p.id DESC";
    $executedQuery = $sql;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
}
?>

<div style="margin-top: 20px;">
    <!-- Search Header & Reflected XSS Point -->
    <div style="background: #fff; padding: 18px 24px; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 20px;">
        <div style="font-size: 1.1rem; color: #555;">
            <!-- SECURITY NOTE: Reflected XSS occurs here in vulnerable mode -->
            Results for: <strong style="color: #0f1111;">
                <?php if ($isVuln): ?>
                    <?= $rawQuery /* VULNERABLE: Direct output without escaping */ ?>
                <?php else: ?>
                    <?= htmlspecialchars($rawQuery, ENT_QUOTES, 'UTF-8') /* SECURE */ ?>
                <?php endif; ?>
            </strong>
            <?php if (!empty($categorySlug)): ?>
                in <span style="color: var(--link-color); font-weight: 600;"><?= htmlspecialchars($categorySlug) ?></span>
            <?php endif; ?>
        </div>
        <div style="font-size: 0.85rem; color: #777; margin-top: 4px;">
            <?= count($products) ?> items found
        </div>

    </div>

    <!-- Results Grid -->
    <?php if (empty($products)): ?>
        <div style="background: #fff; padding: 40px; text-align: center; border-radius: 6px; border: 1px solid #ddd;">
            <i class="fa-solid fa-magnifying-glass" style="font-size: 3rem; color: #ccc; margin-bottom: 16px;"></i>
            <h3>No matching products found</h3>
            <p style="color: #666; margin-top: 8px;">Try checking your spelling or use more general terms.</p>
            <a href="index.php" class="btn btn-secondary" style="width: auto; margin-top: 16px;">Back to All Products</a>
        </div>
    <?php else: ?>
        <div class="product-grid" style="margin-top: 0;">
            <?php foreach ($products as $prod): ?>
                <div class="product-card">
                    <?php if (!empty($prod['badge'])): ?>
                        <span class="product-badge <?= stripos($prod['badge'], 'deal') !== false ? 'deal' : '' ?>">
                            <?= htmlspecialchars($prod['badge']) ?>
                        </span>
                    <?php endif; ?>

                    <a href="product.php?id=<?= $prod['id'] ?>" class="product-img-box">
                        <img src="<?= htmlspecialchars($prod['image'] ?: 'assets/images/placeholder.svg') ?>" alt="<?= htmlspecialchars($prod['name']) ?>" onerror="this.src='assets/images/placeholder.svg'" loading="lazy">
                    </a>

                    <a href="product.php?id=<?= $prod['id'] ?>" class="product-title" title="<?= htmlspecialchars($prod['name']) ?>">
                        <?= htmlspecialchars($prod['name']) ?>
                    </a>

                    <div class="product-rating">
                        <span class="star-gold">
                            <?php 
                            $stars = round($prod['rating'] ?? 5);
                            for ($i = 0; $i < 5; $i++) {
                                echo ($i < $stars) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                            }
                            ?>
                        </span>
                        <span class="reviews-num">(<?= number_format($prod['reviews_count'] ?? 0) ?>)</span>
                    </div>

                    <div class="product-price-row">
                        <span class="price-currency">$</span>
                        <span class="price-main"><?= number_format($prod['price'], 2) ?></span>
                        <?php if (($prod['original_price'] ?? 0) > $prod['price']): ?>
                            <span class="original-price">$<?= number_format($prod['original_price'], 2) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="prime-delivery">
                        <span class="prime-tag">prime</span> FREE Delivery for Prime Members
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <form method="POST" action="index.php" style="flex: 1;">
                            <input type="hidden" name="action" value="add_cart">
                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-add-cart">Add to Cart</button>
                        </form>
                        <a href="product.php?id=<?= $prod['id'] ?>" class="btn btn-secondary" style="width: auto; padding: 0 12px;">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
