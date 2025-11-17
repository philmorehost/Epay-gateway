<?php
// Reseller Portal Dashboard
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if user is logged in and is an approved reseller
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

$stmt = $db->prepare("SELECT is_reseller, credit_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['is_reseller'] != 1) {
    // Redirect non-resellers to the client dashboard
    header('Location: /index.php?page=dashboard');
    exit;
}


$page_title = 'Reseller Dashboard';
include __DIR__ . '/../../app/includes/header.php'; // Using the main header for now
?>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="/public/reseller/index.php" class="list-group-item list-group-item-action active">Dashboard</a>
            <a href="/public/reseller/products.php" class="list-group-item list-group-item-action">Wholesale Products</a>
            <a href="/public/reseller/settings.php" class="list-group-item list-group-item-action">Settings</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card mb-4">
            <div class="card-body text-center">
                <h4>Your Balance</h4>
                <h2>$<?php echo htmlspecialchars(number_format($user['credit_balance'], 2)); ?></h2>
                <a href="/index.php?page=add_funds" class="btn btn-primary">Add Funds</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                Reseller Dashboard
            </div>
            <div class="card-body">
                <h1>Welcome, Reseller!</h1>
                <p>This is your reseller dashboard. You can manage your customers, products, and settings from here.</p>
            </div>
        </div>
    </div>
</div>


<?php
include __DIR__ . '/../../app/includes/footer.php';
