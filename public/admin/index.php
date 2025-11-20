<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

// Fetch some basic stats for the dashboard cards
$user_count = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$product_count = $db->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$unpaid_invoices_count = $db->query("SELECT COUNT(*) as count FROM invoices WHERE status = 'Unpaid'")->fetch_assoc()['count'];
$monthly_revenue = $db->query("SELECT SUM(total) as revenue FROM invoices WHERE status = 'Paid' AND paid_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")->fetch_assoc()['revenue'] ?? 0;

?>

<h1 class="mb-4">Dashboard</h1>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total Clients</h5>
                        <p class="card-text fs-4"><?php echo $user_count; ?></p>
                    </div>
                    <i class="bi bi-people" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Active Products</h5>
                        <p class="card-text fs-4"><?php echo $product_count; ?></p>
                    </div>
                    <i class="bi bi-box-seam" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Unpaid Invoices</h5>
                        <p class="card-text fs-4"><?php echo $unpaid_invoices_count; ?></p>
                    </div>
                    <i class="bi bi-receipt" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Monthly Revenue</h5>
                        <p class="card-text fs-4"><?php echo 'NGN ' . number_format($monthly_revenue, 2); ?></p>
                    </div>
                    <i class="bi bi-cash-stack" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- You can add more dashboard widgets here, like recent orders, etc. -->


<?php
require_once 'footer.php';
?>
