<?php
$page_title = 'Client Dashboard';
require_once '../app/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT first_name, last_name, credit_balance FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();
?>

<h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                Account Information
            </div>
            <div class="card-body">
                <p>Welcome to your client area. Here you can manage your services, view invoices, and update your account details.</p>
                <a href="products.php" class="btn btn-primary">Order New Services</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                Account Balance
            </div>
            <div class="card-body">
                <h4 class="card-title">NGN <?php echo number_format($user['credit_balance'], 2); ?></h4>
                <p>This is your available credit balance. You can use it to pay for new orders or invoices.</p>
                <a href="add_funds.php" class="btn btn-success">Add Funds</a>
            </div>
        </div>
    </div>
</div>


<?php
require_once '../app/includes/footer.php';
?>
