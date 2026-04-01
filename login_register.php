<?php
// -------------------------------------------------------
//  login_register.php
//  Handles register + login with JWT issuance
// -------------------------------------------------------

session_start();
require_once 'config/config.php';
require_once 'config/jwt.php';

// ═══════════════════════════════════════════════════════
//  REGISTER
// ═══════════════════════════════════════════════════════
if (isset($_POST['register'])) {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $rawPass  = $_POST['password'];

    if (strlen($username) < 8) {
        $_SESSION['register_error'] = 'Username must be at least 8 characters.';
        $_SESSION['active_form']    = 'register';
        header("Location: index.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = 'Please enter a valid email address.';
        $_SESSION['active_form']    = 'register';
        header("Location: index.php");
        exit();
    }

    if (strlen($rawPass) < 6) {
        $_SESSION['register_error'] = 'Password must be at least 6 characters.';
        $_SESSION['active_form']    = 'register';
        header("Location: index.php");
        exit();
    }

    // Check duplicate username or email
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $_SESSION['register_error'] = 'Username or email is already taken.';
        $_SESSION['active_form']    = 'register';
        header("Location: index.php");
        exit();
    }
    $stmt->close();

    $hashedPassword = password_hash($rawPass, PASSWORD_DEFAULT);
    $role = 'user';

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $_SESSION['register_success'] = true;
        $_SESSION['active_form']      = 'register';
    } else {
        $_SESSION['register_error'] = 'Registration failed. Please try again.';
        $_SESSION['active_form']    = 'register';
    }

    $stmt->close();
    header("Location: index.php");
    exit();
}

// ═══════════════════════════════════════════════════════
//  LOGIN
// ═══════════════════════════════════════════════════════
if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $rawPass  = $_POST['password'];

    // --- Login Attempt Limiting (Task 1) ---
    $maxAttempts = 5;
    $lockoutTime = 30;

    if (!isset($_SESSION['login_attempts']))    $_SESSION['login_attempts']    = 0;
    if (!isset($_SESSION['last_attempt_time'])) $_SESSION['last_attempt_time'] = 0;

    if ($_SESSION['login_attempts'] >= $maxAttempts) {
        $secondsLeft = $lockoutTime - (time() - $_SESSION['last_attempt_time']);
        if ($secondsLeft > 0) {
            $_SESSION['login_error'] = "Too many failed attempts. Please wait {$secondsLeft} second(s).";
            $_SESSION['active_form'] = 'login';
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['login_attempts']    = 0;
            $_SESSION['last_attempt_time'] = 0;
        }
    }

    if (empty($username) || empty($rawPass)) {
        $_SESSION['login_error'] = 'Username and password are required.';
        $_SESSION['active_form'] = 'login';
        header("Location: index.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($rawPass, $user['password'])) {
            $stmt->close();

            $_SESSION['login_attempts']    = 0;
            $_SESSION['last_attempt_time'] = 0;
            $_SESSION['user_id']           = $user['id'];
            $_SESSION['username']          = $user['username'];
            $_SESSION['role']              = $user['role'];

            // Issue JWT cookie
            $token = generateJWT($user['id'], $user['username'], $user['role']);
            setcookie('jwt_token', $token, [
                'expires'  => time() + JWT_TTL,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            if ($user['role'] === 'admin') {
                header("Location: screens/admin_dashboard.php");
            } else {
                header("Location: screens/dashboard.php");
            }
            exit();
        }
    }

    $stmt->close();

    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    $attemptsLeft = $maxAttempts - $_SESSION['login_attempts'];

    if ($attemptsLeft > 0) {
        $_SESSION['login_error'] = "Incorrect username or password. {$attemptsLeft} attempt(s) remaining.";
    } else {
        $_SESSION['login_error'] = "Too many failed attempts. Please wait {$lockoutTime} seconds.";
    }

    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}