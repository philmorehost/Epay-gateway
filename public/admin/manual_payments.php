<?php
require_once 'templates/header.php';

// Handle approval/rejection
$action = $_GET['action'] ?? null;
$invoice_id = $_GET['id'] ?? null;
$error = $success = null;

if ($action && $invoice_id) {
    try {
        if ($action === 'approve') {
            $stmt = $db->prepare("UPDATE invoices SET status = 'Paid' WHERE id = ? AND status = 'Awaiting Payment'");
            $stmt->bind_param('i', $invoice_id);
            $stmt->execute();
            $success = "Invoice #$invoice_id has been approved and marked as Paid.";
        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE invoices SET status = 'Unpaid' WHERE id = ? AND status = 'Awaiting Payment'");
            $stmt->bind_param('i', $invoice_id);
            $stmt->execute();
            $success = "Invoice #$invoice_id has been rejected and marked as Unpaid.";
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}


// Fetch invoices awaiting payment
$query = "SELECT
            i.id, i.amount, i.updated_at,
            u.name as user_name, p.name as product_name
          FROM invoices i
          JOIN users u ON i.user_id = u.id
          JOIN orders o ON i.order_id = o.id
          JOIN products p ON o.product_id = p.id
          WHERE i.status = 'Awaiting Payment'
          ORDER BY i.updated_at ASC";
$pending_invoices = $db->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<h1>Manual Payment Approval</h1>
<p>Review and approve payments made via Bank Transfer or Cryptocurrency.</p>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="card">
    <div class="card-header">Pending Payments</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_invoices)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No pending manual payments.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pending_invoices as $invoice): ?>
                        <tr>
                            <td><?php echo $invoice['id']; ?></td>
                            <td><?php echo htmlspecialchars($invoice['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['product_name']); ?></td>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($invoice['updated_at'])); ?></td>
                            <td>
                                <a href="?action=approve&id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve this payment?')">Approve</a>
                                <a href="?action=reject&id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject this payment?')">Reject</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
