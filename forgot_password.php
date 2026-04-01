<?php
session_start();
require_once 'config/config.php';
require_once 'config/mailer.php';

$message     = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message     = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $message     = 'Email has been sent!';
        $messageType = 'success';

        if ($result->num_rows > 0) {
            $user     = $result->fetch_assoc();
            $userId   = $user['id'];
            $username = $user['username'];
            $stmt->close();

            $del = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $del->bind_param("i", $userId);
            $del->execute();
            $del->close();

            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 1800);

            $ins = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $ins->bind_param("iss", $userId, $token, $expiresAt);
            $ins->execute();
            $ins->close();

            $baseUrl   = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                       . '://' . $_SERVER['HTTP_HOST']
                       . dirname($_SERVER['REQUEST_URI']);
            $resetLink = $baseUrl . '/reset_password.php?token=' . $token;

            sendResetEmail($email, $username, $resetLink);
        } else {
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Password Reset — Pitlane</title>
</head>
<body>
<div class="container" style="max-width:460px; min-height:auto; padding:0;">
    <div class="form-content" style="padding:50px 44px;">
        <div class="form-box active">
            <h2>Reset</h2>

            <?php if ($message): ?>
                <p class="<?= $messageType === 'error' ? 'error-message' : 'success-message' ?>">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php endif; ?>

            <p style="font-family:'Barlow',sans-serif; font-size:13px; color:var(--f1-grey); margin-bottom:22px; line-height:1.6;">
                Enter your registered email and we'll send you a secure reset link.
            </p>

            <form action="forgot_password.php" method="post">
                <div class="input-container">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <button type="submit">Send Reset Link</button>
                <div class="footer-links" style="justify-content:center;">
                    <a href="index.php">&#8592; Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>