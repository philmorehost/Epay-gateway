<?php
require_once '../app/core/bootstrap.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $tos_agree = isset($_POST['tos_agree']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $password_confirm) {
        $error = "Passwords do not match.";
    } elseif (!$tos_agree) {
        $error = "You must agree to the Terms of Service and Privacy Policy.";
    } else {
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $new_user_id = $db->insert_id;

                // Check for affiliate referral
                if (isset($_COOKIE['affiliate_ref'])) {
                    $referral_code = $_COOKIE['affiliate_ref'];
                    $stmt = $db->prepare("SELECT user_id FROM affiliates WHERE referral_code = ?");
                    $stmt->bind_param('s', $referral_code);
                    $stmt->execute();
                    $affiliate = $stmt->get_result()->fetch_assoc();
                    if ($affiliate) {
                        $stmt = $db->prepare("INSERT INTO affiliate_referrals (referred_user_id, affiliate_user_id) VALUES (?, ?)");
                        $stmt->bind_param('ii', $new_user_id, $affiliate['user_id']);
                        $stmt->execute();
                        // Clear the cookie after use
                        setcookie('affiliate_ref', '', time() - 3600, "/");
                    }
                }

                // Send welcome email
                $template_result = $db->query("SELECT * FROM email_templates WHERE name = 'Welcome Email'");
                if ($template = $template_result->fetch_assoc()) {
                    $subject = $template['subject'];
                    $body = str_replace('{client_name}', $name, $template['body']);
                    send_email($email, $subject, $body);
                }

                $success = "Registration successful! You can now <a href='login.php'>log in</a>.";
            } else {
                $error = "An error occurred during registration. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <h1>Create an Account</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="tos_agree" name="tos_agree">
            <label class="form-check-label" for="tos_agree">
                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>.
            </label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
    <div class="text-center mt-3">
        <a href="login.php">Already have an account? Login</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
