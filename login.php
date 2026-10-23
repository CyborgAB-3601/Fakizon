<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = null;
$executedQuery = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($isVuln) {
        // VULNERABLE: Direct concatenation allowing SQL Injection Authentication Bypass
        // Example test payload: admin' --
        $executedQuery = "SELECT * FROM users WHERE username = '$username'";
        try {
            $stmt = $pdo->query($executedQuery);
            $user = $stmt ? $stmt->fetch() : null;

            if ($user) {
                // In vulnerable authentication bypass logic:
                // If comment syntax '--' is in username or payload, bypass password check
                if (strpos($username, "'") !== false || password_verify($password, $user['password']) || $password === 'admin123' || $password === 'alex123') {
                    $_SESSION['user'] = $user;
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "No user found with those credentials.";
            }
        } catch (Exception $e) {
            $error = "Database syntax error: " . $e->getMessage();
        }
    } else {
        // SECURE / HARDENED: Parameterized Query + password_verify
        $executedQuery = "SELECT * FROM users WHERE username = :u OR email = :u";
        $stmt = $pdo->prepare($executedQuery);
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}

$pageTitle = "Fakizon Sign-In";
require_once __DIR__ . '/header.php';
?>

<div class="auth-box">
    <h1 style="font-size: 1.7rem; font-weight: 600; margin-bottom: 18px;">Sign in</h1>

    <?php if ($error): ?>
        <div style="background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 16px; font-size: 0.85rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label class="form-label">Email or mobile phone number</label>
            <input type="text" name="username" class="form-control" placeholder="e.g. admin or alex" required>
        </div>

        <div class="form-group">
            <div style="display: flex; justify-content: space-between;">
                <label class="form-label">Password</label>
                <a href="#" style="font-size: 0.78rem;">Forgot your password?</a>
            </div>
            <input type="password" name="password" class="form-control" placeholder="Your password">
        </div>

        <button type="submit" class="btn btn-add-cart" style="padding: 9px 0; margin-top: 8px;">
            Sign in
        </button>
    </form>

    <div style="font-size: 0.78rem; color: #555; margin-top: 18px; line-height: 1.4;">
        By continuing, you agree to Fakizon's <a href="#">Conditions of Use</a> and <a href="#">Privacy Notice</a>.
    </div>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-top: 20px; font-size: 0.78rem;">
        <strong>Demo Lab Accounts:</strong><br>
        &bull; Admin: <code>admin</code> / <code>admin123</code><br>
        &bull; Customer: <code>alex</code> / <code>alex123</code>
    </div>

    <div style="text-align: center; margin-top: 24px; position: relative;">
        <hr style="border: 0; border-top: 1px solid #e7e7e7;">
        <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 10px; font-size: 0.75rem; color: #767676;">
            New to Fakizon?
        </span>
    </div>

    <a href="register.php" class="btn btn-secondary" style="margin-top: 18px; padding: 8px 0; display: block;">
        Create your Fakizon account
    </a>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
