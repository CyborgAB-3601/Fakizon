<?php
// Fakizon - Database & Environment Setup
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default security mode is 'vulnerable' for security lab testing
if (!isset($_SESSION['security_mode'])) {
    $_SESSION['security_mode'] = 'vulnerable'; // 'vulnerable' or 'secure'
}

function is_vulnerable() {
    return ($_SESSION['security_mode'] ?? 'vulnerable') === 'vulnerable';
}

function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbFile = __DIR__ . '/fakizon.db';
    $needInit = !file_exists($dbFile) || filesize($dbFile) === 0;

    try {
        $pdo = new PDO("sqlite:" . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if ($needInit) {
            init_database($pdo);
        }
    } catch (PDOException $e) {
        die("<div style='font-family:sans-serif;padding:20px;background:#fee;border:1px solid #f99;margin:20px;border-radius:8px;'>
            <h3>Database Error</h3>
            <p>Could not connect to SQLite database: " . htmlspecialchars($e->getMessage()) . "</p>
            <p>Ensure PHP has the <code>pdo_sqlite</code> extension enabled.</p>
        </div>");
    }

    return $pdo;
}

function init_database($pdo) {
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        full_name TEXT NOT NULL,
        role TEXT DEFAULT 'customer',
        avatar TEXT DEFAULT 'default-avatar.png',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        icon TEXT NOT NULL
    )");

    // Products table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER,
        name TEXT NOT NULL,
        description TEXT NOT NULL,
        price REAL NOT NULL,
        original_price REAL NOT NULL,
        rating REAL DEFAULT 4.5,
        reviews_count INTEGER DEFAULT 120,
        image TEXT NOT NULL,
        badge TEXT DEFAULT '',
        in_stock INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(category_id) REFERENCES categories(id)
    )");

    // Orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        order_number TEXT UNIQUE NOT NULL,
        total_amount REAL NOT NULL,
        status TEXT DEFAULT 'Processing',
        shipping_address TEXT NOT NULL,
        payment_method TEXT DEFAULT 'Credit Card',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");

    // Order items
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        product_name TEXT NOT NULL,
        price REAL NOT NULL,
        quantity INTEGER NOT NULL,
        FOREIGN KEY(order_id) REFERENCES orders(id)
    )");

    // Reviews table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        user_name TEXT NOT NULL,
        rating INTEGER NOT NULL,
        title TEXT NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(product_id) REFERENCES products(id)
    )");

    // Seed default Users (passwords hashed with password_hash, but for vulnerable mode raw checks or known hashes)
    $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, avatar) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtUser->execute(['admin', 'admin@fakizon.local', password_hash('admin123', PASSWORD_DEFAULT), 'Fakizon Administrator', 'admin', 'admin.png']);
    $stmtUser->execute(['alex', 'alex@example.com', password_hash('alex123', PASSWORD_DEFAULT), 'Alex Mercer', 'customer', 'alex.png']);
    $stmtUser->execute(['jane', 'jane@example.com', password_hash('jane123', PASSWORD_DEFAULT), 'Jane Doe', 'customer', 'jane.png']);

    // Seed Categories
    $categories = [
        ['Electronics & Tech', 'electronics', 'fa-laptop'],
        ['Computers & Accessories', 'computers', 'fa-desktop'],
        ['Smart Home & IoT', 'smart-home', 'fa-home'],
        ['Audio & Headphones', 'audio', 'fa-headphones'],
        ['Books & Kindle', 'books', 'fa-book'],
        ['Gaming & Consoles', 'gaming', 'fa-gamepad']
    ];
    $stmtCat = $pdo->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)");
    foreach ($categories as $cat) {
        $stmtCat->execute($cat);
    }

    // Seed Products
    $products = [
        [
            1,
            'Echo Dot (5th Gen) Smart Speaker with Alexa | Deep Bass & Clear Sound',
            'Our best-sounding Echo Dot yet: Enjoy an improved audio experience compared to any previous Echo Dot with Alexa for clearer vocals, deeper bass, and vibrant sound in any room.',
            49.99,
            69.99,
            4.7,
            18420,
            'assets/images/echo-dot.svg',
            'Best Seller',
            1
        ],
        [
            1,
            'Kindle Paperwhite (16 GB) – Now with a 6.8" display and adjustable warm light',
            'Kindle Paperwhite is thin, lightweight, and travels easily so you can enjoy your favorite books at any time. With our signature 300 ppi glare-free display—now 10% brighter at its max setting.',
            139.99,
            169.99,
            4.8,
            9540,
            'assets/images/kindle.svg',
            "Amazon's Choice",
            1
        ],
        [
            2,
            'QuantumBook Pro 16" Laptop - M3 Max Chip, 36GB Unified RAM, 1TB SSD',
            'Powerhouse performance designed for professionals, engineers, and creators. Liquid Retina XDR display with 1600 nits peak brightness and up to 22 hours of battery life.',
            2499.00,
            2799.00,
            4.9,
            3120,
            'assets/images/laptop.svg',
            'Limited Deal',
            1
        ],
        [
            4,
            'Sony WH-1000XM5 Wireless Noise Canceling Headphones',
            'Industry Leading noise cancellation with two processors and 8 microphones for unprecedented noise canceling and exceptional call quality.',
            348.00,
            399.99,
            4.6,
            14230,
            'assets/images/headphones.svg',
            'Save 13%',
            1
        ],
        [
            6,
            'PlayNova 5 Pro Gaming Console (1TB Custom NVMe SSD)',
            'Experience lightning-fast loading with an ultra-high speed SSD, deeper immersion with support for haptic feedback, adaptive triggers, and 3D Audio.',
            499.99,
            549.99,
            4.8,
            24110,
            'assets/images/console.svg',
            'Popular',
            1
        ],
        [
            3,
            'Blink Outdoor 4 - Wireless smart security camera, two-year battery life',
            'Wire-free smart security camera with 1080p HD live view, infrared night vision, and crisp two-way audio. Weather-resistant for outdoor peace of mind.',
            79.99,
            119.99,
            4.4,
            6820,
            'assets/images/camera.svg',
            'Deal of Day',
            1
        ],
        [
            2,
            'UltraGlide Ergonomic Wireless Mouse with Hyper-Scroll',
            'Precision tracking on any surface with silent clicks, thumb rest, and 70-day rechargeable battery life via USB-C fast charging.',
            59.99,
            79.99,
            4.5,
            4950,
            'assets/images/mouse.svg',
            '',
            1
        ],
        [
            5,
            'The Web Application Hacker\'s Handbook: Finding and Exploiting Security Flaws',
            'The definitive guide to web security testing, penetration analysis, and defensive engineering. Must-read for developers and security analysts.',
            42.50,
            55.00,
            4.9,
            820,
            'assets/images/security-book.svg',
            'Top Rated',
            1
        ]
    ];

    $stmtProd = $pdo->prepare("INSERT INTO products (category_id, name, description, price, original_price, rating, reviews_count, image, badge, in_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $prod) {
        $stmtProd->execute($prod);
    }

    // Seed Sample Reviews
    $reviews = [
        [1, 'Sarah K.', 5, 'Best smart speaker I ever bought!', 'The bass is surprisingly powerful for such a small device. Connected seamlessly to all my smart home lights.'],
        [1, 'Dave M.', 4, 'Great sound, setup was quick', 'Very responsive microphones, works across the living room without raising my voice.'],
        [2, 'Emily R.', 5, 'Changed how I read', 'Battery lasts for weeks, and the warm light adjustment makes nighttime reading so comfortable.'],
        [4, 'Marcus T.', 5, 'Unmatched noise cancellation', 'Wore these on a 14-hour flight and heard virtually zero jet engine noise. Super lightweight.'],
        [8, 'CyberSecPro', 5, 'Essential reading for any web researcher', 'Covers fundamental architecture flaws and how vulnerabilities arise in dynamic web applications. Highly recommended!']
    ];

    $stmtRev = $pdo->prepare("INSERT INTO reviews (product_id, user_name, rating, title, comment) VALUES (?, ?, ?, ?, ?)");
    foreach ($reviews as $rev) {
        $stmtRev->execute($rev);
    }

    // Seed Demo Orders for Alex and Jane
    $stmtOrd = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtOrd->execute([2, 'FAK-1009281', 139.99, 'Delivered', '124 Maple Avenue, Seattle, WA 98101', 'Visa ending in 4242']);
    $orderId1 = $pdo->lastInsertId();

    $stmtOrdItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
    $stmtOrdItem->execute([$orderId1, 2, 'Kindle Paperwhite (16 GB)', 139.99, 1]);

    $stmtOrd->execute([3, 'FAK-1009282', 348.00, 'Shipped', '742 Evergreen Terrace, Springfield, OR 97477', 'MasterCard ending in 8812']);
    $orderId2 = $pdo->lastInsertId();
    $stmtOrdItem->execute([$orderId2, 4, 'Sony WH-1000XM5 Wireless Headphones', 348.00, 1]);
}
