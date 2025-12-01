<?php
require_once '../app/core/bootstrap.php';

$error = null;
$ip_address = $_SERVER['REMOTE_ADDR'];

// Fetch max_login_attempts from settings
$max_attempts_stmt = $db->query("SELECT value FROM settings WHERE setting = 'max_login_attempts'");
$max_login_attempts = $max_attempts_stmt->fetch_assoc()['value'] ?? 5; // Default to 5 if not set

// --- Brute Force Check ---
$stmt = $db->prepare("SELECT COUNT(*) as attempt_count FROM login_attempts WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL ? SECOND)");
$block_time = LOGIN_BLOCK_TIME;
$stmt->bind_param('si', $ip_address, $block_time);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['attempt_count'] >= $max_login_attempts) {
    $error = "Too many failed login attempts. Please try again later.";
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT id, password, 2fa_enabled FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, now check for 2FA
        if ($user['2fa_enabled']) {
            $_SESSION['2fa_user_id'] = $user['id'];
            header('Location: 2fa_verify.php');
            exit;
        } else {
            // No 2FA, complete the login
            $_SESSION['user_id'] = $user['id'];
            $return_to = $_SESSION['return_to'] ?? 'index.php';
            unset($_SESSION['return_to']);
            header("Location: $return_to");
            exit;
        }
    } else {
        // Record failed attempt
        $stmt = $db->prepare("INSERT INTO login_attempts (ip_address) VALUES (?)");
        $stmt->bind_param('s', $ip_address);
        $stmt->execute();

        $error = "Invalid email or password.";
    }
}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <h1>Client Login</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="text-center mt-3">
        <a href="register.php">Don't have an account? Register</a> | <a href="forgot_password.php">Forgot Password?</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
