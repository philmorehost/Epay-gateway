<?php
// app/views/client_dashboard.php
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

<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Client Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</p>
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

    <div class="mt-4 text-center">
        <a href="add_funds.php" class="btn btn-primary">Add Funds</a>
        <a href="my_domains.php" class="btn btn-secondary">My Domains</a>
        <a href="invoices.php" class="btn btn-info">My Invoices</a>
        <a href="security.php" class="btn btn-warning">Security</a>
        <a href="affiliates.php" class="btn btn-light">Affiliate Program</a>
        <?php if ($user['is_reseller']): ?>
            <a href="reseller/index.php" class="btn btn-success">Reseller Portal</a>
        <?php else: ?>
            <a href="become_reseller.php" class="btn btn-outline-primary">Become a Reseller</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
