<?php
// Admin - Manual Payment Approval
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice_id'])) {
    $invoice_id = $_POST['invoice_id'];
    $action = $_POST['action']; // 'approve' or 'reject'

    $new_status = ($action === 'approve') ? 'Paid' : 'Unpaid'; // Reject sets it back to Unpaid

    $db->begin_transaction();
    try {
        $stmt = $db->prepare("UPDATE invoices SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $invoice_id);
        $stmt->execute();
        $stmt->close();

        // If approved and it's a credit invoice, update the user's balance
        if ($new_status === 'Paid') {
            $credit_stmt = $db->prepare("SELECT user_id, amount, is_credit_invoice FROM invoices WHERE id = ?");
            $credit_stmt->bind_param("i", $invoice_id);
            $credit_stmt->execute();
            $credit_result = $credit_stmt->get_result();
            $invoice = $credit_result->fetch_assoc();
            $credit_stmt->close();

            if ($invoice && $invoice['is_credit_invoice']) {
                $user_stmt = $db->prepare("UPDATE users SET credit_balance = credit_balance + ? WHERE id = ?");
                $user_stmt->bind_param("di", $invoice['amount'], $invoice['user_id']);
                $user_stmt->execute();
                $user_stmt->close();
            }
        }

        $db->commit();

    } catch (Exception $e) {
        $db->rollback();
        die("Error updating invoice: " . $e->getMessage());
    }

    header('Location: manual_payments.php');
    exit;
}

// Fetch all invoices awaiting payment
$result = $db->query("SELECT i.id, i.amount, u.name as user_name, i.created_at
                      FROM invoices i
                      JOIN users u ON i.user_id = u.id
                      WHERE i.status = 'Awaiting Payment'
                      ORDER BY i.created_at ASC");
$invoices = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'Manual Payments';
include __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manual Payment Approval</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No payments are awaiting approval.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><a href="/index.php?page=view_invoice&id=<?php echo $invoice['id']; ?>" target="_blank"><?php echo $invoice['id']; ?></a></td>
                            <td><?php echo htmlspecialchars($invoice['user_name']); ?></td>
                            <td>$<?php echo htmlspecialchars($invoice['amount']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['created_at']); ?></td>
                            <td>
                                <form action="manual_payments.php" method="post" class="d-inline">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form action="manual_payments.php" method="post" class="d-inline">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/footer.php';
