<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

$id = (int)($_GET['id'] ?? 1);

// Fetch product details
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

$pageTitle = $product['name'];
$msg = null;

// Handle Review Submission (Stored XSS Point)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
    $author = trim($_POST['author'] ?? 'Anonymous Buyer');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $title = trim($_POST['title'] ?? 'Product Review');
    $comment = trim($_POST['comment'] ?? '');

    if (!empty($comment)) {
        $stmtRev = $pdo->prepare("INSERT INTO reviews (product_id, user_name, rating, title, comment) VALUES (?, ?, ?, ?, ?)");
        $stmtRev->execute([$id, $author, $rating, $title, $comment]);

        // Update product reviews count and recalculate rating
        $stmtAvg = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as rev_count FROM reviews WHERE product_id = ?");
        $stmtAvg->execute([$id]);
        $stats = $stmtAvg->fetch();
        if ($stats) {
            $stmtUpd = $pdo->prepare("UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?");
            $stmtUpd->execute([round($stats['avg_rating'], 1), $stats['rev_count'], $id]);
        }

        $msg = "Review submitted successfully!";
    }
}

// Fetch all reviews for this product
$stmtReviews = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC");
$stmtReviews->execute([$id]);
$reviews = $stmtReviews->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div style="font-size: 0.82rem; color: #555; margin: 15px 0 10px 0;">
    <a href="index.php">Home</a> &rsaquo; 
    <span><?= htmlspecialchars($product['category_name'] ?? 'Departments') ?></span> &rsaquo; 
    <span style="color: #0f1111;"><?= htmlspecialchars(mb_strimwidth($product['name'], 0, 50, "...")) ?></span>
</div>

<div class="product-detail-container">
    <div class="product-detail-layout">
        <!-- Image Column -->
        <div class="detail-img-box">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='assets/images/placeholder.svg'">
        </div>

        <!-- Info Column -->
        <div class="detail-info-box">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div style="font-size: 0.85rem; color: var(--link-color); margin-bottom: 8px;">
                Visit the Fakizon Certified Store
            </div>

            <div class="product-rating" style="font-size: 0.95rem;">
                <span class="star-gold">
                    <?php 
                    $stars = round($product['rating']);
                    for ($i = 0; $i < 5; $i++) {
                        echo ($i < $stars) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                    }
                    ?>
                </span>
                <span style="font-weight: 700; margin-left: 6px;"><?= number_format($product['rating'], 1) ?> out of 5</span>
                <span class="reviews-num">(<?= number_format($product['reviews_count']) ?> ratings)</span>
            </div>

            <hr style="border: 0; border-top: 1px solid #e7e7e7; margin: 14px 0;">

            <div class="product-price-row" style="margin-bottom: 16px;">
                <span style="font-size: 0.9rem; color: var(--price-red); font-weight: 700;">Price:</span>
                <span class="price-currency" style="color: var(--price-red); font-size: 1rem; top: -6px;">$</span>
                <span class="price-main" style="color: var(--price-red); font-size: 1.8rem;"><?= number_format($product['price'], 2) ?></span>
                <?php if ($product['original_price'] > $product['price']): ?>
                    <span style="font-size: 0.85rem; color: #565959;">Typical price: <span style="text-decoration: line-through;">$<?= number_format($product['original_price'], 2) ?></span></span>
                <?php endif; ?>
            </div>

            <div style="font-size: 0.95rem; line-height: 1.6; color: #333; margin-bottom: 20px;">
                <h4 style="font-size: 1rem; margin-bottom: 6px;">About this item</h4>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; font-size: 0.85rem;">
                <strong><i class="fa-solid fa-shield-check" style="color: #059669;"></i> Fakizon Return Policy:</strong> Returnable within 30 days of receipt. Prime free shipping included.
            </div>
        </div>

        <!-- Buy Action Box Column -->
        <div class="detail-buy-box">
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--price-red);">
                $<?= number_format($product['price'], 2) ?>
            </div>

            <div style="font-size: 0.85rem; color: #007600; font-weight: 700;">
                <i class="fa-solid fa-check"></i> In Stock.
            </div>

            <div style="font-size: 0.82rem; color: #555;">
                Ships from: <strong style="color: #111;">Fakizon Fulfillment Center</strong><br>
                Sold by: <strong style="color: #111;">Fakizon Retail Inc.</strong>
            </div>

            <form method="POST" action="cart.php">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-size: 0.8rem;">Quantity:</label>
                    <select name="quantity" class="form-control" style="width: 80px; padding: 4px 8px;">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="5">5</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-add-cart" style="margin-bottom: 10px;">
                    <i class="fa-solid fa-cart-plus" style="margin-right: 6px;"></i> Add to Cart
                </button>
            </form>

            <a href="checkout.php?buy_now=<?= $product['id'] ?>" class="btn btn-buy-now">
                <i class="fa-solid fa-bolt" style="margin-right: 6px;"></i> Buy Now
            </a>

            <div style="font-size: 0.75rem; color: #666; text-align: center; margin-top: 6px;">
                <i class="fa-solid fa-lock"></i> Secure transaction
            </div>
        </div>
    </div>

    <!-- Customer Reviews & Stored XSS Lab Point -->
    <div class="reviews-section">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 700;">Customer Reviews</h2>
                <div style="font-size: 0.85rem; color: #555;">Hear what verified buyers have to say about this item.</div>
            </div>
            <a href="#write-review" class="btn btn-secondary" style="width: auto; padding: 6px 14px; font-size: 0.82rem;">Write a Review</a>
        </div>

        <?php if ($msg): ?>
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <!-- List of existing reviews -->
        <?php if (empty($reviews)): ?>
            <p style="color: #666; font-style: italic;">No reviews yet. Be the first to share your thoughts!</p>
        <?php else: ?>
            <div style="margin-bottom: 30px;">
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-item">
                        <div class="review-author">
                            <i class="fa-solid fa-circle-user" style="color: #888; font-size: 1.2rem;"></i>
                            <span><?= htmlspecialchars($rev['user_name']) ?></span>
                            <span style="font-size: 0.75rem; color: #e67a00; font-weight: 700; margin-left: 4px;">Verified Purchase</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; margin: 4px 0;">
                            <span class="star-gold" style="font-size: 0.85rem;">
                                <?php for ($i = 0; $i < $rev['rating']; $i++): ?><i class="fa-solid fa-star"></i><?php endfor; ?>
                            </span>
                            <span style="font-weight: 700; font-size: 0.9rem;">
                                <?php if ($isVuln): ?>
                                    <?= $rev['title'] /* VULNERABLE: Direct rendering */ ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($rev['title'], ENT_QUOTES, 'UTF-8') /* SECURE */ ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div style="font-size: 0.75rem; color: #666; margin-bottom: 6px;">
                            Reviewed on <?= htmlspecialchars($rev['created_at']) ?>
                        </div>
                        <div class="review-comment">
                            <?php if ($isVuln): ?>
                                <?= nl2br($rev['comment']) /* VULNERABLE: Direct rendering */ ?>
                            <?php else: ?>
                                <?= nl2br(htmlspecialchars($rev['comment'], ENT_QUOTES, 'UTF-8')) /* SECURE */ ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Review Form -->
        <div id="write-review" style="background: #fafafa; border: 1px solid #ddd; border-radius: 6px; padding: 20px; max-width: 650px;">
            <h3 style="font-size: 1.1rem; margin-bottom: 12px;"><i class="fa-solid fa-pen"></i> Submit a Customer Review</h3>
            <form method="POST" action="product.php?id=<?= $product['id'] ?>#write-review">
                <input type="hidden" name="action" value="add_review">

                <div class="form-group">
                    <label class="form-label">Your Name or Pseudonym:</label>
                    <input type="text" name="author" class="form-control" value="<?= $currentUser ? htmlspecialchars($currentUser['full_name']) : 'Verified Customer' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Overall Rating:</label>
                    <select name="rating" class="form-control" style="width: 140px;">
                        <option value="5">5 Stars - Outstanding</option>
                        <option value="4">4 Stars - Very Good</option>
                        <option value="3">3 Stars - Average</option>
                        <option value="2">2 Stars - Below Average</option>
                        <option value="1">1 Star - Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Review Headline / Title:</label>
                    <input type="text" name="title" class="form-control" placeholder="What's most important to know?" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Written Review:</label>
                    <textarea name="comment" class="form-control" rows="4" placeholder="Write your review here. In vulnerable mode, HTML tags and scripts will be stored." required></textarea>
                </div>

                <button type="submit" class="btn btn-add-cart" style="width: auto; padding: 8px 24px;">Submit Review</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
