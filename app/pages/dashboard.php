<?php
// Client Dashboard
// Note: bootstrap.php is included by the router (public/index.php)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

$page_title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="/index.php?page=dashboard" class="list-group-item list-group-item-action active">Dashboard</a>
            <a href="/index.php?page=invoices" class="list-group-item list-group-item-action">My Invoices</a>
            <!-- Add more links here as features are added -->
        </div>
    </div>
    <div class="col-md-9">
        <?php if (isset($_GET['order_success'])): ?>
            <div class="alert alert-success">Your order has been placed successfully and an invoice has been generated.</div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header">
                Client Dashboard
            </div>
            <div class="card-body">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                <p>This is your client dashboard. You can manage your account and services from here.</p>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
