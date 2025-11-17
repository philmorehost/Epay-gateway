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


$page_title = 'Dashboard';
include __DIR__ . '/../../app/includes/reseller_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="card mb-4">
    <div class="card-body text-center">
        <h4>Your Balance</h4>
        <h2>$<?php echo htmlspecialchars(number_format($user['credit_balance'], 2)); ?></h2>
        <a href="/index.php?page=add_funds" class="btn btn-primary">Add Funds</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Welcome, Reseller!
    </div>
    <div class="card-body">
        <p>This is your reseller dashboard. You can manage your customers, products, and settings from the sidebar.</p>
    </div>
</div>


<?php
include __DIR__ . '/../../app/includes/reseller_footer.php';
