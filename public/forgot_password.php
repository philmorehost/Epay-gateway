<?php
require_once '../app/core/bootstrap.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // For now, we'll just show a success message.
    // In a real application, this is where you would generate a reset token,
    // save it to the database, and send an email.
    $success = "If an account with that email exists, a password reset link has been sent.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <h1>Forgot Password</h1>
        <p>Enter your email address to receive a password reset link.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="forgot_password.php" method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
    </form>
    <div class="text-center mt-3">
        <a href="login.php">Back to Login</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
