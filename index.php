<?php

session_start();

$errors = [
    'login'    => $_SESSION['login_error']    ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];

$activeForm      = $_SESSION['active_form']      ?? 'login';
$registerSuccess = $_SESSION['register_success'] ?? false;

unset(
    $_SESSION['login_error'],
    $_SESSION['register_error'],
    $_SESSION['active_form'],
    $_SESSION['register_success']
);

function showError($error) {
    return !empty($error)
        ? "<p class='error-message'>" . htmlspecialchars($error) . "</p>"
        : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>PITLANE — Login</title>
    <style>
        /* Lap counter ticker top-right */
        .lap-ticker {
            position: absolute;
            top: 18px; left: 24px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: var(--f1-grey);
            letter-spacing: 2px;
            text-transform: uppercase;
            z-index: 2;
        }
        .lap-ticker span { color: var(--f1-red); }

        /* DRS indicator */
        .drs {
            position: absolute;
            bottom: 22px;
            right: 24px;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--f1-grey);
            z-index: 2;
        }

        /* Sector line decorations on side image */
        .sector-lines {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            z-index: 2;
            display: flex;
            height: 4px;
        }
        .sector-lines span:nth-child(1) { flex:1; background:#E8002D; }
        .sector-lines span:nth-child(2) { flex:1; background:#FFD700; }
        .sector-lines span:nth-child(3) { flex:1; background:#00C864; }
    </style>
</head>
<body>

<div class="container">
    <div class="side-image">
        <div class="sector-lines">
            <span></span><span></span><span></span>
        </div>
        <div class="lap-ticker">LAP <span id="lap">01</span> / 57</div>
        <img src="images/new-f1-logo.jpg" alt="Racing Background">
        <div class="stripe"></div>
        <div class="drs">DRS ENABLED</div>
    </div>

    <div class="form-content">

        <!-- LOGIN FORM -->
        <div class="form-box <?= $activeForm === 'login' ? 'active' : '' ?>" id="login-form">
            <form action="login_register.php" method="post">
                <?= showError($errors['login']) ?>
                <h2>Login</h2>

                <div class="input-container">
                    <label>Username</label>
                    <input type="text" name="username" autocomplete="username" required>
                </div>
                <div class="input-container">
                    <label>Password</label>
                    <input type="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" name="login">Enter Pitlane</button>
                <div class="footer-links">
                    <a href="#" onclick="showForm('register-form')">Create account</a>
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div class="form-box <?= $activeForm === 'register' ? 'active' : '' ?>" id="register-form">
            <form action="login_register.php" method="post">
                <?= showError($errors['register']) ?>
                <?php if ($registerSuccess): ?>
                    <p class="success-message">Registered — you can now login.</p>
                <?php endif; ?>
                <h2>Register</h2>

                <div class="input-container">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="min. 8 characters" required>
                </div>
                <div class="input-container">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="input-container">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="min. 6 characters" required>
                </div>
                <button type="submit" name="register">Join the Grid</button>
                <div class="footer-links">
                    <a href="#" onclick="showForm('login-form')">Back to Login</a>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    function showForm(formId) {
        document.querySelectorAll('.form-box').forEach(f => f.classList.remove('active'));
        document.getElementById(formId).classList.add('active');
    }

    // Lap counter animation
    let lap = 1;
    setInterval(() => {
        lap = lap >= 57 ? 1 : lap + 1;
        document.getElementById('lap').textContent = String(lap).padStart(2, '0');
    }, 3000);
</script>
</body>
</html>