<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $prod = $stmt->fetch();

        if ($prod) {
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $qty;
            } else {
                $_SESSION['cart'][$productId] = [
                    'id' => $prod['id'],
                    'name' => $prod['name'],
                    'price' => $prod['price'],
                    'image' => $prod['image'],
                    'quantity' => $qty
                ];
            }
        }
        header("Location: cart.php");
        exit;
    }

    if ($action === 'update') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);

        if ($qty <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $qty;
        }
        header("Location: cart.php");
        exit;
    }

    if ($action === 'remove') {
        $productId = (int)($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$productId]);
        header("Location: cart.php");
        exit;
    }
}

$pageTitle = "Fakizon Shopping Cart";
require_once __DIR__ . '/header.php';

$cartItems = $_SESSION['cart'];
$subtotal = 0;
$totalItems = 0;

foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}
?>

<div style="margin-top: 20px;">
    <?php if (isset($_GET['added'])): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
            <span>Item successfully added to your cart!</span>
        </div>
    <?php endif; ?>

    <div class="cart-layout">
        <!-- Cart Items List -->
        <div class="cart-items-card">
            <h1 style="font-size: 1.6rem; font-weight: 700; border-bottom: 1px solid #e7e7e7; padding-bottom: 12px; margin-bottom: 8px;">
                Shopping Cart
            </h1>

            <?php if (empty($cartItems)): ?>
                <div style="padding: 40px 0; text-align: center;">
                    <i class="fa-solid fa-basket-shopping" style="font-size: 3.5rem; color: #cbd5e1; margin-bottom: 16px;"></i>
                    <h2 style="font-size: 1.3rem; margin-bottom: 8px;">Your Fakizon Cart is empty</h2>
                    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Check out today's deals or explore your recommended items.</p>
                    <a href="index.php" class="btn btn-add-cart" style="width: auto; padding: 10px 24px; display: inline-block;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div style="text-align: right; font-size: 0.85rem; color: #565959; border-bottom: 1px solid #e7e7e7; padding-bottom: 6px;">
                    Price
                </div>

                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-row">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='assets/images/placeholder.svg'" class="cart-item-img">
                        <div style="flex: 1;">
                            <a href="product.php?id=<?= $item['id'] ?>" style="font-size: 1rem; font-weight: 600; color: #0f1111; line-height: 1.3; display: block; margin-bottom: 6px;">
                                <?= htmlspecialchars($item['name']) ?>
                            </a>
                            <div style="font-size: 0.8rem; color: #007600; font-weight: 700; margin-bottom: 6px;">
                                In Stock
                            </div>
                            <div style="font-size: 0.75rem; color: #565959; margin-bottom: 10px;">
                                Eligible for FREE Shipping
                            </div>

                            <!-- Actions Row -->
                            <div style="display: flex; align-items: center; gap: 14px; font-size: 0.85rem;">
                                <form method="POST" action="cart.php" style="display: flex; align-items: center; gap: 6px;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <label style="color: #555;">Qty:</label>
                                    <select name="quantity" class="form-control" style="width: 60px; padding: 2px 6px; font-size: 0.82rem;" onchange="this.form.submit()">
                                        <?php for ($q = 1; $q <= 10; $q++): ?>
                                            <option value="<?= $q ?>" <?= $item['quantity'] == $q ? 'selected' : '' ?>><?= $q ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </form>
                                <span style="color: #ddd;">|</span>
                                <form method="POST" action="cart.php" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: var(--link-color); cursor: pointer; padding: 0; font-size: 0.85rem;">Delete</button>
                                </form>
                            </div>
                        </div>

                        <!-- Price -->
                        <div style="font-size: 1.15rem; font-weight: 700; color: #0f1111; text-align: right; min-width: 90px;">
                            $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="text-align: right; font-size: 1.1rem; padding-top: 16px;">
                    Subtotal (<?= $totalItems ?> <?= $totalItems === 1 ? 'item' : 'items' ?>): 
                    <strong style="font-weight: 800; color: var(--price-red);">$<?= number_format($subtotal, 2) ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cart Summary / Proceed Box -->
        <?php if (!empty($cartItems)): ?>
            <div class="cart-summary-card">
                <div style="font-size: 0.85rem; color: #007600; margin-bottom: 12px; display: flex; gap: 8px;">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.1rem; margin-top: 2px;"></i>
                    <span>Your order qualifies for <strong>FREE Prime Delivery</strong>. Choose this option at checkout.</span>
                </div>

                <div style="font-size: 1.15rem; margin-bottom: 16px;">
                    Subtotal (<?= $totalItems ?> items): <br>
                    <span style="font-size: 1.5rem; font-weight: 800; color: var(--price-red);">$<?= number_format($subtotal, 2) ?></span>
                </div>

                <a href="checkout.php" class="btn btn-add-cart" style="padding: 10px 0; font-size: 0.95rem; margin-bottom: 12px;">
                    Proceed to checkout
                </a>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; font-size: 0.78rem; color: #64748b;">
                    <i class="fa-solid fa-shield"></i> Fakizon 100% Purchase Protection guarantee on every order.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
