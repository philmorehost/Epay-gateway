<?php
require_once '../templates/header.php';
require_permission('view_reports');

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
?>

<h1>Tax Report</h1>

<div class="card">
    <div class="card-header">Filter by Date</div>
    <div class="card-body">
        <form action="tax.php" method="get" class="mb-4">
            <div class="row">
                <div class="col-md-5">
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-5">
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <?php
        if ($start_date && $end_date) {
            $stmt = $db->prepare("SELECT SUM(tax_amount) as total_tax, COUNT(id) as total_invoices FROM invoices WHERE status = 'Paid' AND created_at BETWEEN ? AND ?");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $tax_summary = $stmt->get_result()->fetch_assoc();

            $stmt = $db->prepare("SELECT i.id, i.tax_amount, i.created_at, u.name as client_name FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.status = 'Paid' AND i.created_at BETWEEN ? AND ? ORDER BY i.created_at DESC");
            $stmt->bind_param('ss', $start_date, $end_date);
            $stmt->execute();
            $tax_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>
            <hr>
            <h4>Results for <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h4>
            <p><strong>Total Tax Collected:</strong> $<?php echo number_format($tax_summary['total_tax'] ?? 0, 2); ?></p>

            <table class="table table-striped mt-3">
                <thead>
                    <tr><th>Invoice ID</th><th>Client Name</th><th>Tax Amount</th><th>Date Paid</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tax_transactions as $tx): ?>
                        <tr>
                            <td><?php echo $tx['id']; ?></td>
                            <td><?php echo htmlspecialchars($tx['client_name']); ?></td>
                            <td>$<?php echo number_format($tx['tax_amount'], 2); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($tx['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
