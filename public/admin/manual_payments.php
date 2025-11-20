<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$message = '';

// Handle approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice_id'])) {
    verify_csrf_token(); // CSRF check

    $invoice_id = (int)$_POST['invoice_id'];
    $action = $_POST['action'];

    // Fetch the invoice to ensure it exists and is awaiting payment
    $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND status = 'Awaiting Payment'");
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();

    if ($invoice) {
        if ($action === 'approve') {
            // Use a transaction
            $db->begin_transaction();
            try {
                // Mark invoice as paid
                $paid_date = date('Y-m-d H:i:s');
                $update_invoice = $db->prepare("UPDATE invoices SET status = 'Paid', paid_date = ?, payment_method = 'Manual' WHERE id = ?");
                $update_invoice->bind_param('si', $paid_date, $invoice_id);
                $update_invoice->execute();

                // If it's a credit invoice, update user balance
                if ($invoice['is_credit_invoice'] == 1) {
                    $update_user = $db->prepare("UPDATE users SET credit_balance = credit_balance + ? WHERE id = ?");
                    $update_user->bind_param('di', $invoice['total'], $invoice['user_id']);
                    $update_user->execute();
                }

                $db->commit();
                $message = "<div class='alert alert-success'>Invoice #{$invoice_id} has been approved and marked as paid.</div>";
            } catch (Exception $e) {
                $db->rollback();
                $message = "<div class='alert alert-danger'>Error approving invoice #{$invoice_id}.</div>";
            }

        } elseif ($action === 'reject') {
            // Simply mark as cancelled
            $db->query("UPDATE invoices SET status = 'Cancelled' WHERE id = {$invoice_id}");
            $message = "<div class='alert alert-warning'>Invoice #{$invoice_id} has been rejected and cancelled.</div>";
        }
    }
}


// Fetch all invoices awaiting manual payment
$invoices_result = $db->query("SELECT i.*, u.email FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.status = 'Awaiting Payment' ORDER BY i.created_date ASC");

?>

<h1 class="mb-4">Manual Payment Verification</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-body">
        <p>The following invoices have been marked as "Awaiting Payment". Please verify the transaction with your bank or payment processor and then approve or reject the invoice.</p>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client Email</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($invoices_result->num_rows > 0): ?>
                    <?php while($invoice = $invoices_result->fetch_assoc()): ?>
                        <tr>
                            <td><a href="../view_invoice.php?id=<?php echo $invoice['id']; ?>" target="_blank"><?php echo $invoice['id']; ?></a></td>
                            <td><?php echo htmlspecialchars($invoice['email']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($invoice['created_date'])); ?></td>
                            <td>NGN <?php echo number_format($invoice['total'], 2); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No payments are awaiting verification.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$db->close();
require_once 'footer.php';
?>
