<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user details
$stmt = $db->prepare("SELECT name, email, credit_balance, is_reseller FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// --- Client Dashboard View ---
$page_title = "Client Dashboard";
// Since there's no consistent header/footer for the client area, we'll do it all in one file for now.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - HostBill-1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Assuming a general stylesheet exists -->
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="client_dashboard.php">HostBill-1</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="client_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="invoices.php">Invoices</a></li>
                <li class="nav-item"><a class="nav-link" href="add_funds.php">Add Funds</a></li>
                <li class="nav-item"><a class="nav-link" href="security.php">Security</a></li>
                <?php if ($user['is_reseller']): ?>
                    <li class="nav-item"><a class="nav-link" href="reseller_admin/">Reseller Area</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <div class="text-end">
            <strong>Credit Balance:</strong> $<?php echo number_format($user['credit_balance'], 2); ?>
        </div>
    </div>

    <div class="row">
        <!-- Quick Access Stat Cards -->
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Services</h5>
                    <p class="card-text display-4">0</p> <!-- Placeholder -->
                    <a href="#" class="btn btn-primary">Manage Services</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Domains</h5>
                    <p class="card-text display-4">0</p> <!-- Placeholder -->
                    <a href="#" class="btn btn-primary">Manage Domains</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Unpaid Invoices</h5>
                    <p class="card-text display-4">0</p> <!-- Placeholder -->
                    <a href="invoices.php" class="btn btn-primary">View Invoices</a>
                </div>
            </div>
        </div>
    </div>

    <!-- More sections like Recent Activity, Account Information etc. can be added here -->

</div>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">&copy; <?php echo date("Y"); ?> HostBill-1. All Rights Reserved.</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
