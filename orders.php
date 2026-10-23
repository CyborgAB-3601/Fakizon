<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

$currentUser = $_SESSION['user'] ?? null;
// Demo fallback user if not logged in
$activeUserId = $currentUser ? $currentUser['id'] : 2; // Demo user Alex is ID 2

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$orderDetails = null;
$orderItems = [];
$idorError = null;

if ($orderId > 0) {
    if ($isVuln) {
        // VULNERABLE: Direct access to any order ID without checking ownership (IDOR)
        $stmtOrder = $pdo->prepare("SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmtOrder->execute([$orderId]);
        $orderDetails = $stmtOrder->fetch();
    } else {
        // SECURE / HARDENED: Validates that the requested order belongs to the authenticated user
        $stmtOrder = $pdo->prepare("SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
        $stmtOrder->execute([$orderId, $activeUserId]);
        $orderDetails = $stmtOrder->fetch();

        if (!$orderDetails) {
            $idorError = "Access Denied: You do not have permission to view Order #" . htmlspecialchars($orderId) . " belonging to another customer.";
        }
    }

    if ($orderDetails) {
        $stmtItems = $pdo->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmtItems->execute([$orderId]);
        $orderItems = $stmtItems->fetchAll();
    }
}

// Fetch list of user's own orders
$stmtUserOrders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmtUserOrders->execute([$activeUserId]);
$userOrders = $stmtUserOrders->fetchAll();

$pageTitle = "Your Orders - Fakizon";
require_once __DIR__ . '/header.php';
?>

<div style="margin-top: 24px;">
    <?php if (isset($_GET['placed'])): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 16px; border-radius: 6px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
            <i class="fa-solid fa-circle-check" style="font-size: 1.8rem;"></i>
            <div>
                <h3 style="font-size: 1.1rem; margin-bottom: 2px;">Thank you! Your order has been placed.</h3>
                <div style="font-size: 0.85rem;">An email confirmation has been sent to your registered address.</div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($idorError): ?>
        <div style="background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 16px; border-radius: 6px; margin-bottom: 24px;">
            <i class="fa-solid fa-shield-halved" style="font-size: 1.2rem; margin-right: 6px;"></i>
            <strong>Security Protection:</strong> <?= htmlspecialchars($idorError) ?>
        </div>
    <?php endif; ?>

    <?php if ($orderDetails): ?>
        <!-- Specific Order View -->
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px; margin-bottom: 30px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 14px; margin-bottom: 16px;">
                <div>
                    <h2 style="font-size: 1.3rem;">Order Details: <span style="color: var(--link-color);"><?= htmlspecialchars($orderDetails['order_number']) ?></span></h2>
                    <div style="font-size: 0.82rem; color: #666;">Placed on <?= htmlspecialchars($orderDetails['created_at']) ?> | Order ID: #<?= $orderDetails['id'] ?></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.85rem; color: #666;">Customer Account:</div>
                    <strong style="color: #0f1111;"><?= htmlspecialchars($orderDetails['full_name'] ?? 'Customer') ?> (<?= htmlspecialchars($orderDetails['email'] ?? '') ?>)</strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; background: #fafafa; padding: 16px; border-radius: 6px; margin-bottom: 20px;">
                <div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: #777; font-weight: 700;">Shipping Address</div>
                    <div style="font-size: 0.88rem; margin-top: 4px;"><?= nl2br(htmlspecialchars($orderDetails['shipping_address'])) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: #777; font-weight: 700;">Payment Method</div>
                    <div style="font-size: 0.88rem; margin-top: 4px;"><?= htmlspecialchars($orderDetails['payment_method']) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: #777; font-weight: 700;">Order Status</div>
                    <div style="font-size: 0.88rem; margin-top: 4px; color: #0284c7; font-weight: 700;"><?= htmlspecialchars($orderDetails['status']) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: #777; font-weight: 700;">Total Charged</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: var(--price-red); margin-top: 2px;">$<?= number_format($orderDetails['total_amount'], 2) ?></div>
                </div>
            </div>

            <h4 style="font-size: 1rem; margin-bottom: 12px;">Items in this Shipment</h4>
            <?php foreach ($orderItems as $item): ?>
                <div style="display: flex; gap: 16px; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <img src="<?= htmlspecialchars($item['image'] ?: 'assets/images/placeholder.svg') ?>" alt="" onerror="this.src='assets/images/placeholder.svg'" style="width: 60px; height: 60px; object-fit: contain;">
                    <div style="flex: 1;">
                        <a href="product.php?id=<?= $item['product_id'] ?>" style="font-weight: 600; font-size: 0.92rem;"><?= htmlspecialchars($item['product_name']) ?></a>
                        <div style="font-size: 0.82rem; color: #666; margin-top: 2px;">Qty: <?= $item['quantity'] ?> &times; $<?= number_format($item['price'], 2) ?></div>
                    </div>
                    <div style="font-weight: 700;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top: 16px;">
                <a href="orders.php" class="btn btn-secondary" style="width: auto; padding: 6px 16px; font-size: 0.85rem;">&larr; View All Your Orders</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- User Orders List -->
    <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px;">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 16px;">Your Order History</h1>

        <?php if (empty($userOrders)): ?>
            <p style="color: #666;">You haven't placed any orders yet.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #eee; text-align: left;">
                        <th style="padding: 10px;">Order #</th>
                        <th style="padding: 10px;">Date</th>
                        <th style="padding: 10px;">Total</th>
                        <th style="padding: 10px;">Status</th>
                        <th style="padding: 10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userOrders as $uOrd): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 12px 10px; font-weight: 600; color: var(--link-color);"><?= htmlspecialchars($uOrd['order_number']) ?></td>
                            <td style="padding: 12px 10px; color: #555;"><?= htmlspecialchars($uOrd['created_at']) ?></td>
                            <td style="padding: 12px 10px; font-weight: 700;">$<?= number_format($uOrd['total_amount'], 2) ?></td>
                            <td style="padding: 12px 10px;"><span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-size: 0.78rem; font-weight: 700;"><?= htmlspecialchars($uOrd['status']) ?></span></td>
                            <td style="padding: 12px 10px;">
                                <a href="orders.php?id=<?= $uOrd['id'] ?>" class="btn btn-secondary" style="width: auto; padding: 4px 12px; font-size: 0.8rem;">View Order Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
