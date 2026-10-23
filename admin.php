<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

$currentUser = $_SESSION['user'] ?? null;

// Allow quick admin session switch for lab testing convenience
if (isset($_GET['grant_admin'])) {
    $stmtAdmin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $_SESSION['user'] = $stmtAdmin->fetch();
    header("Location: admin.php");
    exit;
}

$pingOutput = null;
$pingHost = '';
$diagCmd = '';

// Handle Server Health / Host Connectivity Diagnostics (Command Injection Lab Point)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ping_host') {
    $pingHost = $_POST['host'] ?? '127.0.0.1';

    if ($isVuln) {
        // VULNERABLE: Direct concatenation of user input into shell execution
        // Example test input: 127.0.0.1 & whoami
        $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        $diagCmd = $isWindows ? "ping -n 1 " . $pingHost : "ping -c 1 " . $pingHost;
        $pingOutput = shell_exec($diagCmd);
    } else {
        // SECURE / HARDENED: Strict hostname / IP format validation
        if (filter_var($pingHost, FILTER_VALIDATE_IP) || preg_match('/^[a-zA-Z0-9.-]+$/', $pingHost)) {
            $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
            $safeHost = escapeshellarg($pingHost);
            $diagCmd = $isWindows ? "ping -n 1 " . $safeHost : "ping -c 1 " . $safeHost;
            $pingOutput = shell_exec($diagCmd);
        } else {
            $pingOutput = "Security Filter Triggered: Invalid host character detected. Only valid IPs or alphanumeric hostnames are allowed.";
        }
    }
}

// Handle Add Product
$addProdMsg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    $image = trim($_POST['image'] ?? 'assets/images/placeholder.svg');

    if (!empty($name) && $price > 0) {
        $stmtAdd = $pdo->prepare("INSERT INTO products (category_id, name, description, price, original_price, rating, reviews_count, image, badge, in_stock) VALUES (?, ?, ?, ?, ?, 5.0, 1, ?, 'New', 1)");
        $stmtAdd->execute([$category_id, $name, $description, $price, $price * 1.2, $image]);
        $addProdMsg = "Product added successfully to Fakizon catalog!";
    }
}

// Store Statistics
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalSales = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn() ?: 0;

$recentOrders = $pdo->query("SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5")->fetchAll();

$pageTitle = "Admin & Vendor Console - Fakizon";
require_once __DIR__ . '/header.php';
?>

<div style="margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 700;">Fakizon Seller Central & Operations Console</h1>
            <p style="color: #666; font-size: 0.88rem;">Manage product listings, inventory metrics, and server diagnostics</p>
        </div>
        <div>
            <?php if (!$currentUser || $currentUser['role'] !== 'admin'): ?>
                <a href="admin.php?grant_admin=1" class="btn btn-secondary" style="width: auto; padding: 6px 14px; font-size: 0.82rem; border-color: #3b82f6; color: #1d4ed8;">
                    <i class="fa-solid fa-key"></i> Switch to Admin Session
                </a>
            <?php else: ?>
                <span style="background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 700;">
                    <i class="fa-solid fa-user-shield"></i> Logged in as: <?= htmlspecialchars($currentUser['username']) ?> (Administrator)
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #fff; padding: 18px; border-radius: 6px; border: 1px solid #ddd; border-left: 4px solid #f59e0b;">
            <div style="font-size: 0.8rem; color: #666; font-weight: 700; text-transform: uppercase;">Total Sales</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f1111; margin-top: 4px;">$<?= number_format($totalSales, 2) ?></div>
        </div>
        <div style="background: #fff; padding: 18px; border-radius: 6px; border: 1px solid #ddd; border-left: 4px solid #3b82f6;">
            <div style="font-size: 0.8rem; color: #666; font-weight: 700; text-transform: uppercase;">Total Orders</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f1111; margin-top: 4px;"><?= $totalOrders ?></div>
        </div>
        <div style="background: #fff; padding: 18px; border-radius: 6px; border: 1px solid #ddd; border-left: 4px solid #10b981;">
            <div style="font-size: 0.8rem; color: #666; font-weight: 700; text-transform: uppercase;">Active Products</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f1111; margin-top: 4px;"><?= $totalProducts ?></div>
        </div>
        <div style="background: #fff; padding: 18px; border-radius: 6px; border: 1px solid #ddd; border-left: 4px solid #8b5cf6;">
            <div style="font-size: 0.8rem; color: #666; font-weight: 700; text-transform: uppercase;">Registered Users</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f1111; margin-top: 4px;"><?= $totalUsers ?></div>
        </div>
    </div>

    <!-- Diagnostic Network Tool (Command Injection Point) -->
    <div style="background: #fff; padding: 24px; border-radius: 6px; border: 1px solid #ddd; margin-bottom: 30px;">
        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 10px;">
            <i class="fa-solid fa-network-wired" style="color: #0284c7;"></i> Supplier API & Network Health Diagnostic Tool
        </h3>
        <p style="font-size: 0.85rem; color: #555; margin-bottom: 16px;">
            Test latency and connectivity to external warehouse endpoints or shipping API hosts.
        </p>

        <form method="POST" action="admin.php">
            <input type="hidden" name="action" value="ping_host">
            <div style="display: flex; gap: 12px; max-width: 600px;">
                <input type="text" name="host" class="form-control" placeholder="e.g. 127.0.0.1 or api.fakizon.local" value="<?= htmlspecialchars($pingHost ?: '127.0.0.1') ?>" required>
                <button type="submit" class="btn btn-add-cart" style="width: auto; padding: 0 20px; white-space: nowrap;">
                    <i class="fa-solid fa-play"></i> Run Health Check
                </button>
            </div>
        </form>

        <?php if ($pingOutput !== null): ?>
            <div style="margin-top: 16px;">
                <div style="font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Command Executed:</div>
                <div class="code-snippet" style="margin-bottom: 8px;"><code><?= htmlspecialchars($diagCmd) ?></code></div>
                
                <div style="font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Output Stream:</div>
                <pre class="code-snippet" style="white-space: pre-wrap; font-size: 0.82rem; background: #0f172a; color: #38bdf8;"><?= htmlspecialchars($pingOutput) ?></pre>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Product & Recent Orders Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Add Product Form -->
        <div style="background: #fff; padding: 24px; border-radius: 6px; border: 1px solid #ddd;">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 14px;">Add New Inventory Item</h3>

            <?php if ($addProdMsg): ?>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 10px; border-radius: 4px; margin-bottom: 14px; font-size: 0.85rem;">
                    <?= htmlspecialchars($addProdMsg) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="add_product">

                <div class="form-group">
                    <label class="form-label">Product Title:</label>
                    <input type="text" name="name" class="form-control" placeholder="Item name and brand" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Category:</label>
                    <select name="category_id" class="form-control">
                        <?php foreach ($allCategories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Price (USD):</label>
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="49.99" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Image URL:</label>
                    <input type="text" name="image" class="form-control" value="assets/images/placeholder.svg">
                </div>

                <div class="form-group">
                    <label class="form-label">Product Description:</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Key specs and features..."></textarea>
                </div>

                <button type="submit" class="btn btn-add-cart" style="padding: 9px 0;">Publish Product</button>
            </form>
        </div>

        <!-- Recent Customer Orders -->
        <div style="background: #fff; padding: 24px; border-radius: 6px; border: 1px solid #ddd;">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 14px;">Recent Orders</h3>
            <?php if (empty($recentOrders)): ?>
                <p style="color: #666; font-size: 0.88rem;">No recent orders recorded.</p>
            <?php else: ?>
                <div style="font-size: 0.85rem;">
                    <?php foreach ($recentOrders as $ro): ?>
                        <div style="border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <a href="orders.php?id=<?= $ro['id'] ?>" style="font-weight: 700;"><?= htmlspecialchars($ro['order_number']) ?></a>
                                <div style="color: #666; font-size: 0.78rem;">Customer: <?= htmlspecialchars($ro['full_name'] ?? 'Guest') ?> &bull; <?= htmlspecialchars($ro['created_at']) ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--price-red);">$<?= number_format($ro['total_amount'], 2) ?></div>
                                <span style="font-size: 0.72rem; background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 3px;"><?= htmlspecialchars($ro['status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
