<?php
require_once 'templates/header.php';
require_permission('view_reports');

// --- Fetch At-a-Glance Metrics ---

// 1. Monthly Revenue (for the current month)
$current_month = date('Y-m');
$stmt = $db->prepare("SELECT SUM(amount) as monthly_revenue FROM invoices WHERE status = 'Paid' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->bind_param('s', $current_month);
$stmt->execute();
$monthly_revenue = $stmt->get_result()->fetch_assoc()['monthly_revenue'] ?? 0;

// 2. New Signups (for the current month)
$stmt = $db->prepare("SELECT COUNT(id) as new_signups FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->bind_param('s', $current_month);
$stmt->execute();
$new_signups = $stmt->get_result()->fetch_assoc()['new_signups'] ?? 0;

// 3. Pending Orders
$pending_orders = $db->query("SELECT COUNT(id) as pending_orders FROM orders WHERE status = 'Pending'")->fetch_assoc()['pending_orders'] ?? 0;

?>

<h1>Reports Dashboard</h1>

<!-- At-a-Glance Metrics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <h5 class="stat-card-title">Monthly Revenue (<?php echo date('F'); ?>)</h5>
            <p class="stat-card-value">$<?php echo number_format($monthly_revenue, 2); ?></p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h5 class="stat-card-title">New Signups (<?php echo date('F'); ?>)</h5>
            <p class="stat-card-value"><?php echo $new_signups; ?></p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h5 class="stat-card-title">Pending Orders</h5>
            <p class="stat-card-value"><?php echo $pending_orders; ?></p>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Quick Reports</span>
        <a href="reports/tax.php" class="btn btn-sm btn-info">View Tax Report</a>
    </div>
</div>

<!-- Revenue Report -->
<div class="card mt-4">
    <div class="card-header">Revenue Report</div>
    <div class="card-body">
        <form action="reports.php" method="get" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Filter Revenue</button>
                </div>
            </div>
        </form>

        <?php
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        if ($start_date && $end_date) {
            $stmt = $db->prepare("SELECT SUM(amount) as total_revenue, COUNT(id) as total_invoices FROM invoices WHERE status = 'Paid' AND created_at BETWEEN ? AND ?");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $revenue_summary = $stmt->get_result()->fetch_assoc();

            $stmt = $db->prepare("SELECT i.id, i.amount, i.created_at, u.name as client_name FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.status = 'Paid' AND i.created_at BETWEEN ? AND ? ORDER BY i.created_at DESC");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $revenue_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>
            <hr>
            <h4>Results for <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h4>
            <p><strong>Total Revenue:</strong> $<?php echo number_format($revenue_summary['total_revenue'] ?? 0, 2); ?></p>
            <p><strong>Total Paid Invoices:</strong> <?php echo $revenue_summary['total_invoices'] ?? 0; ?></p>

            <table class="table table-striped mt-3">
                <thead>
                    <tr><th>Invoice ID</th><th>Client Name</th><th>Amount</th><th>Date Paid</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($revenue_transactions as $tx): ?>
                        <tr>
                            <td><?php echo $tx['id']; ?></td>
                            <td><?php echo htmlspecialchars($tx['client_name']); ?></td>
                            <td>$<?php echo number_format($tx['amount'], 2); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($tx['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>

<!-- New Signups Report -->
<div class="card mt-4">
    <div class="card-header">New Signups Report</div>
    <div class="card-body">
        <form action="reports.php" method="get" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="date" name="signup_start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['signup_start_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="date" name="signup_end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['signup_end_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Filter Signups</button>
                </div>
            </div>
        </form>

        <?php
        $signup_start_date = $_GET['signup_start_date'] ?? null;
        $signup_end_date = $_GET['signup_end_date'] ?? null;

        if ($signup_start_date && $signup_end_date) {
            $stmt = $db->prepare("SELECT COUNT(id) as total_signups FROM users WHERE created_at BETWEEN ? AND ?");
            $stmt->bind_param('ss', $signup_start_date, $signup_end_date);
            $stmt->execute();
            $signups_summary = $stmt->get_result()->fetch_assoc();

            $stmt = $db->prepare("SELECT name, email, created_at FROM users WHERE created_at BETWEEN ? AND ? ORDER BY created_at DESC");
            $stmt->bind_param('ss', $signup_start_date, $signup_end_date);
            $stmt->execute();
            $signups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>
            <hr>
            <h4>Results for <?php echo htmlspecialchars($signup_start_date); ?> to <?php echo htmlspecialchars($signup_end_date); ?></h4>
            <p><strong>Total New Signups:</strong> <?php echo $signups_summary['total_signups'] ?? 0; ?></p>

            <table class="table table-striped mt-3">
                <thead>
                    <tr><th>Client Name</th><th>Email</th><th>Date Registered</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($signups as $signup): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($signup['name']); ?></td>
                            <td><?php echo htmlspecialchars($signup['email']); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($signup['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>


<?php require_once 'templates/footer.php'; ?>
