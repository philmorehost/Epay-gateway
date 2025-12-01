<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = $success = null;

// Check if user is already a reseller
$stmt = $db->prepare("SELECT is_reseller FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$is_reseller = $user['is_reseller'];

// Handle the upgrade request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_reseller) {
    try {
        $db->begin_transaction();

        // Update user to be a reseller
        $stmt = $db->prepare("UPDATE users SET is_reseller = 1 WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        // Create a default entry in reseller_settings
        $stmt = $db->prepare("INSERT INTO reseller_settings (user_id) VALUES (?) ON DUPLICATE KEY UPDATE user_id=user_id");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        $db->commit();
        $success = "Congratulations! You are now a reseller. You can access your reseller portal from the main dashboard.";
        $is_reseller = true; // Update the flag for the current page view

    } catch (Exception $e) {
        $db->rollback();
        $error = "An error occurred. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Reseller - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Become a Reseller</h1>
    </div>

    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <div class="card" style="max-width: 600px; margin: auto;">
        <div class="card-body">
            <?php if ($is_reseller): ?>
                <p class="text-center">You are already a reseller. Access your reseller portal from your main dashboard.</p>
            <?php else: ?>
                <p>By becoming a reseller, you will gain access to wholesale pricing and a white-labeled portal to sell our products to your own customers.</p>
                <form action="become_reseller.php" method="post" class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">Upgrade to Reseller Account</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="index.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
