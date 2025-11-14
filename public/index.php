<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user details
$stmt = $db->prepare("SELECT name, credit_balance FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <h1>Client Dashboard</h1>
        <p>Welcome! This is a placeholder for the main client dashboard.</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="stat-card-title">Account Status</h5>
                <p class="stat-card-value">Active</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="stat-card-title">Credit Balance</h5>
                <p class="stat-card-value">$<?php echo number_format($user['credit_balance'], 2); ?></p>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="add_funds.php" class="btn btn-primary">Add Funds</a>
        <a href="invoices.php" class="btn btn-secondary">My Invoices</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
