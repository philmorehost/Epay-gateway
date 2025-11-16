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

<div class="card">
    <div class="card-header">
        Client Dashboard
    </div>
    <div class="card-body">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p>This is your client dashboard. You can manage your account and services from here.</p>
        <p>Your user ID is: <?php echo $_SESSION['user_id']; ?></p>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
