<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

$currentUser = $_SESSION['user'] ?? null;
$buyNowId = isset($_GET['buy_now']) ? (int)$_GET['buy_now'] : 0;

$itemsToCheckout = [];
$total = 0;

if ($buyNowId > 0) {
    $stmtBuy = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmtBuy->execute([$buyNowId]);
    $prod = $stmtBuy->fetch();
    if ($prod) {
        $itemsToCheckout[] = [
            'id' => $prod['id'],
            'name' => $prod['name'],
            'price' => $prod['price'],
            'quantity' => 1,
            'image' => $prod['image']
        ];
        $total = $prod['price'];
    }
} else if (!empty($_SESSION['cart'])) {
    $itemsToCheckout = $_SESSION['cart'];
    foreach ($itemsToCheckout as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

if (empty($itemsToCheckout)) {
    header("Location: cart.php");
    exit;
}

// Handle Order Placement
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $shippingAddress = trim($_POST['address'] ?? '123 Fake Street, Seattle, WA');
    $paymentMethod = trim($_POST['payment_method'] ?? 'Fakizon Visa Card (ending 4242)');
    $userId = $currentUser ? $currentUser['id'] : 2; // Default to demo user Alex if not logged in

    try {
        $orderNumber = 'FAK-' . rand(1000000, 9999999);
        $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method) VALUES (?, ?, ?, 'Processing', ?, ?)");
        $stmtOrder->execute([$userId, $orderNumber, $total, $shippingAddress, $paymentMethod]);
        $orderId = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
        foreach ($itemsToCheckout as $item) {
            $stmtItem->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['quantity']]);
        }

        // Clear cart if we checked out from cart
        if ($buyNowId === 0) {
            $_SESSION['cart'] = [];
        }

        header("Location: orders.php?id=" . $orderId . "&placed=1");
        exit;
    } catch (Exception $e) {
        $error = "Failed to place order: " . $e->getMessage();
    }
}

$pageTitle = "Checkout - Fakizon";
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 900px; margin: 30px auto;">
    <h1 style="font-size: 1.7rem; font-weight: 700; margin-bottom: 20px;">Review your order & Checkout</h1>

    <?php if ($error): ?>
        <div style="background: #fee2e2; border: 1px solid #f87171; color: #b91c1c; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px;">
        <!-- Checkout Details Form -->
        <div style="background: #fff; padding: 24px; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
            <form method="POST" action="checkout.php<?= $buyNowId ? '?buy_now=' . $buyNowId : '' ?>">
                <input type="hidden" name="action" value="place_order">

                <h3 style="font-size: 1.15rem; margin-bottom: 14px; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                    1. Shipping Address
                </h3>
                <div class="form-group">
                    <label class="form-label">Full Name:</label>
                    <input type="text" class="form-control" value="<?= $currentUser ? htmlspecialchars($currentUser['full_name']) : 'Alex Mercer' ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Address:</label>
                    <input type="text" name="address" class="form-control" value="124 Maple Avenue, Apt 4B, Seattle, WA 98101" required>
                </div>

                <h3 style="font-size: 1.15rem; margin: 24px 0 14px 0; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                    2. Payment Method
                </h3>
                <div class="form-group">
                    <label class="form-label">Choose Payment:</label>
                    <select name="payment_method" class="form-control">
                        <option value="Fakizon Prime Rewards Visa ending in 4242">Fakizon Prime Rewards Visa ending in 4242</option>
                        <option value="MasterCard ending in 8812">MasterCard ending in 8812</option>
                        <option value="Fakizon Gift Card Balance">Fakizon Store Credit / Gift Card</option>
                    </select>
                </div>

                <h3 style="font-size: 1.15rem; margin: 24px 0 14px 0; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                    3. Review Items in Shipment
                </h3>
                <div>
                    <?php foreach ($itemsToCheckout as $it): ?>
                        <div style="display: flex; gap: 12px; margin-bottom: 12px; align-items: center;">
                            <img src="<?= htmlspecialchars($it['image']) ?>" alt="" onerror="this.src='assets/images/placeholder.svg'" style="width: 50px; height: 50px; object-fit: contain;">
                            <div style="flex: 1; font-size: 0.9rem;">
                                <strong><?= htmlspecialchars($it['name']) ?></strong>
                                <div style="color: #666; font-size: 0.8rem;">Qty: <?= $it['quantity'] ?> &times; $<?= number_format($it['price'], 2) ?></div>
                            </div>
                            <div style="font-weight: 700;">
                                $<?= number_format($it['price'] * $it['quantity'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-add-cart" style="margin-top: 24px; padding: 12px 0; font-size: 1.05rem;">
                    Place Your Order in USD
                </button>
            </form>
        </div>

        <!-- Order Summary Box -->
        <div style="background: #fff; padding: 24px; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); height: fit-content;">
            <h3 style="font-size: 1.1rem; margin-bottom: 14px;">Order Summary</h3>
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 8px;">
                <span>Items:</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 8px;">
                <span>Shipping & handling:</span>
                <span style="color: #007600;">$0.00 (Prime Free)</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 8px;">
                <span>Estimated tax:</span>
                <span>$0.00</span>
            </div>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 12px 0;">
            <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 800; color: var(--price-red);">
                <span>Order total:</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
