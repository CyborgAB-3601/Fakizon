<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'customer')");
            $stmt->execute([$username, $email, $hashed, $fullName]);

            // Auto-login
            $userId = $pdo->lastInsertId();
            $_SESSION['user'] = [
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName,
                'role' => 'customer',
                'avatar' => 'default-avatar.png'
            ];

            header("Location: index.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $error = "Registration failed: Username or email might already be taken.";
        }
    }
}

$pageTitle = "Create Account - Fakizon";
require_once __DIR__ . '/header.php';
?>

<div class="auth-box">
    <h1 style="font-size: 1.7rem; font-weight: 600; margin-bottom: 18px;">Create account</h1>

    <?php if ($error): ?>
        <div style="background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 16px; font-size: 0.85rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label class="form-label">Your name</label>
            <input type="text" name="full_name" class="form-control" placeholder="First and last name" required>
        </div>

        <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Unique username" required>
        </div>

        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="name@domain.com" required>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required minlength="6">
            <div style="font-size: 0.72rem; color: #666; margin-top: 4px;">
                <i class="fa-solid fa-info-circle"></i> Passwords must be at least 6 characters.
            </div>
        </div>

        <button type="submit" class="btn btn-add-cart" style="padding: 9px 0; margin-top: 8px;">
            Continue
        </button>
    </form>

    <div style="font-size: 0.78rem; color: #555; margin-top: 18px; line-height: 1.4;">
        By creating an account, you agree to Fakizon's <a href="#">Conditions of Use</a> and <a href="#">Privacy Notice</a>.
    </div>

    <div style="margin-top: 24px; border-top: 1px solid #eee; padding-top: 16px; font-size: 0.85rem;">
        Already have an account? <a href="login.php">Sign in &rarr;</a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
