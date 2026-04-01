<?php
session_start();
require_once 'config/config.php';

$token       = trim($_GET['token'] ?? '');
$message     = '';
$messageType = '';
$validToken  = false;
$tokenData   = null;

if (empty($token)) {
    $message     = 'No reset token provided.';
    $messageType = 'error';
} else {
    $stmt = $conn->prepare("
        SELECT pr.id, pr.user_id, pr.expires_at, pr.used, u.username
        FROM password_resets pr
        JOIN users u ON u.id = pr.user_id
        WHERE pr.token = ?
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $message     = 'Invalid or unrecognised reset token.';
        $messageType = 'error';
    } else {
        $tokenData = $result->fetch_assoc();
        $stmt->close();

        if ($tokenData['used']) {
            $message     = 'This reset link has already been used.';
            $messageType = 'error';
        } elseif (strtotime($tokenData['expires_at']) < time()) {
            $message     = 'This reset link has expired. Please request a new one.';
            $messageType = 'error';
        } else {
            $validToken = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $newPass     = $_POST['password']         ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (strlen($newPass) < 6) {
        $message     = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } elseif ($newPass !== $confirmPass) {
        $message     = 'Passwords do not match.';
        $messageType = 'error';
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);

        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->bind_param("si", $hashed, $tokenData['user_id']);
        $upd->execute();
        $upd->close();

        $mark = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $mark->bind_param("s", $token);
        $mark->execute();
        $mark->close();

        $message     = 'Password reset successfully! You can now log in.';
        $messageType = 'success';
        $validToken  = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>New Password — Pitlane</title>
    <style>
        .strength-bar { height: 3px; background: var(--f1-border); margin-top: 8px; overflow: hidden; }
        .strength-fill { height: 100%; width: 0%; transition: width 0.3s, background 0.3s; }
    </style>
</head>
<body>
<div class="container" style="max-width:480px; min-height:auto; padding:0;">
    <div class="form-content" style="padding:50px 44px;">
        <div class="form-box active">
            <h2>New Password</h2>

            <?php if ($message): ?>
                <p class="<?= $messageType === 'error' ? 'error-message' : 'success-message' ?>">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php endif; ?>

            <?php if ($messageType === 'success'): ?>
                <div class="footer-links" style="justify-content:center; margin-top:20px;">
                    <a href="index.php">&#8592; Back to Login</a>
                </div>

            <?php elseif ($validToken && $tokenData): ?>
                <div class="token-meta">
                    <div class="row">
                        <span class="label">Driver</span>
                        <span class="value"><?= htmlspecialchars($tokenData['username']) ?></span>
                    </div>
                    <div class="row">
                        <span class="label">Expires</span>
                        <span class="value"><?= htmlspecialchars($tokenData['expires_at']) ?></span>
                    </div>
                    <div class="row">
                        <span class="label">Token</span>
                        <span class="value" style="color:#00c864;">&#10003; Valid</span>
                    </div>
                </div>

                <form action="reset_password.php?token=<?= urlencode($token) ?>" method="post">
                    <div class="input-container">
                        <label>New Password</label>
                        <input type="password" name="password" id="new-pass"
                               placeholder="min. 6 characters" required
                               oninput="checkStrength(this.value)">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                    </div>
                    <div class="input-container">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit">Set New Password</button>
                    <div class="footer-links" style="justify-content:center;">
                        <a href="index.php">&#8592; Back to Login</a>
                    </div>
                </form>

            <?php else: ?>
                <div class="footer-links" style="justify-content:center; margin-top:20px;">
                    <a href="forgot_password.php">Request a new link</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function checkStrength(val) {
    const fill = document.getElementById('strength-fill');
    let s = 0;
    if (val.length >= 6)           s++;
    if (val.length >= 10)          s++;
    if (/[A-Z]/.test(val))        s++;
    if (/[0-9]/.test(val))        s++;
    if (/[^A-Za-z0-9]/.test(val)) s++;
    const colors = ['#E8002D','#e67e22','#FFD700','#2ecc71','#00C864'];
    const widths  = ['20%','40%','60%','80%','100%'];
    fill.style.width      = widths[s - 1]  || '0%';
    fill.style.background = colors[s - 1]  || '#333';
}
</script>
</body>
</html>