<?php
require_once __DIR__ . '/db.php';
$pdo = get_db_connection();

$currentUser = $_SESSION['user'] ?? null;
if (!$currentUser) {
    header("Location: login.php");
    exit;
}

$userId = $currentUser['id'];
$statusMsg = null;
$errorMsg = null;
$uploadedFilePath = null;

// Ensure upload directory exists
$uploadDir = __DIR__ . '/assets/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle Profile Update & Avatar File Upload (Security Lab Point)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? $currentUser['full_name']);
    $email = trim($_POST['email'] ?? $currentUser['email']);

    // Check if an avatar was uploaded
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['avatar']['tmp_name'];
        $fileName = basename($_FILES['avatar']['name']);
        
        if ($isVuln) {
            // VULNERABLE IMPLEMENTATION:
            // 1. No extension check (permits .php, .phtml, .html, etc.)
            // 2. No MIME-type validation
            // 3. Retains original filename, allowing direct invocation
            $destination = $uploadDir . $fileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                $avatarVal = $fileName;
                $uploadedFilePath = "assets/uploads/" . $fileName;

                $stmtUpd = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, avatar = ? WHERE id = ?");
                $stmtUpd->execute([$fullName, $email, $avatarVal, $userId]);
                $statusMsg = "Profile & avatar updated successfully!";
            } else {
                $errorMsg = "Failed to save uploaded file.";
            }
        } else {
            // SECURE / HARDENED IMPLEMENTATION:
            // 1. Extension allowlist
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // 2. MIME type verification
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fileTmp);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                $errorMsg = "Security Policy: Only valid image files (JPG, PNG, WEBP, GIF) are allowed.";
            } else {
                // 3. Rename file to random hash to prevent overwrite & execution
                $newFileName = 'avatar_' . bin2hex(random_bytes(10)) . '.' . $ext;
                $destination = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmp, $destination)) {
                    $avatarVal = $newFileName;
                    $uploadedFilePath = "assets/uploads/" . $newFileName;

                    $stmtUpd = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, avatar = ? WHERE id = ?");
                    $stmtUpd->execute([$fullName, $email, $avatarVal, $userId]);
                    $statusMsg = "Profile updated safely with validated image.";
                } else {
                    $errorMsg = "Failed to move uploaded file.";
                }
            }
        }
    } else {
        // Just update names
        $stmtUpd = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmtUpd->execute([$fullName, $email, $userId]);
        $statusMsg = "Profile updated.";
    }

    // Refresh user session data
    $stmtRefresh = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmtRefresh->execute([$userId]);
    $_SESSION['user'] = $stmtRefresh->fetch();
    $currentUser = $_SESSION['user'];
}

$pageTitle = "Your Profile - Fakizon";
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 800px; margin: 30px auto;">
    <h1 style="font-size: 1.7rem; font-weight: 700; margin-bottom: 20px;">Your Account Profile</h1>

    <?php if ($statusMsg): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px;">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($statusMsg) ?>
            <?php if ($uploadedFilePath): ?>
                <div style="margin-top: 6px; font-size: 0.82rem;">
                    File uploaded to: <a href="<?= htmlspecialchars($uploadedFilePath) ?>" target="_blank" style="font-weight: 700; color: #007185;"><?= htmlspecialchars($uploadedFilePath) ?></a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div style="background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
        <form method="POST" action="profile.php" enctype="multipart/form-data">
            <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 24px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background: #e2e8f0; border: 2px solid #cbd5e1; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($currentUser['avatar']) && file_exists(__DIR__ . '/assets/uploads/' . $currentUser['avatar'])): ?>
                        <img src="assets/uploads/<?= htmlspecialchars($currentUser['avatar']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fa-solid fa-user" style="font-size: 2.5rem; color: #94a3b8;"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 style="font-size: 1.2rem;"><?= htmlspecialchars($currentUser['full_name']) ?></h3>
                    <div style="font-size: 0.85rem; color: #666;">Username: <strong><?= htmlspecialchars($currentUser['username']) ?></strong> | Role: <span style="text-transform: capitalize;"><?= htmlspecialchars($currentUser['role']) ?></span></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Full Name:</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($currentUser['full_name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address:</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($currentUser['email']) ?>" required>
            </div>

            <div class="form-group" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 16px;">
                <label class="form-label">Upload Profile Picture / Avatar:</label>
                <input type="file" name="avatar" class="form-control" style="padding: 6px;">
                <div style="font-size: 0.75rem; color: #666; margin-top: 4px;">
                    Supported in Secure Mode: JPG, PNG, WEBP, GIF. In Vulnerable Mode: Any file.
                </div>
            </div>

            <button type="submit" class="btn btn-add-cart" style="width: auto; padding: 9px 24px; margin-top: 10px;">
                Save Changes
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
