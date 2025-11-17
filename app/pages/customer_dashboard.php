<?php
// app/pages/customer_dashboard.php

if (!defined('IS_RESELLER_STOREFRONT') || !IS_RESELLER_STOREFRONT || !isset($_SESSION['customer_id'])) {
    header('Location: /index.php');
    exit;
}

global $db, $reseller_data;
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

$page_title = 'Dashboard - ' . htmlspecialchars($reseller_data['settings']['company_name'] ?? 'Reseller');
include __DIR__ . '/../includes/reseller_storefront_header.php';
?>

<div class="container mt-4">
    <h1>Welcome, <?php echo htmlspecialchars($customer_name); ?>!</h1>
    <p>This is your dashboard. You can view your services and invoices here.</p>

    <div class="card">
        <div class="card-header">
            Your Details
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
            <p><strong>Coming Soon:</strong> Manage your profile, view invoices, and order new services.</p>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
