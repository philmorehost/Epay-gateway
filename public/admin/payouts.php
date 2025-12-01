<?php
require_once 'templates/header.php';

$error = $success = null;

// Handle approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $payout_id = $_GET['id'];
    $new_status = ($_GET['action'] === 'approve') ? 'Paid' : 'Rejected';

    try {
        $stmt = $db->prepare("UPDATE affiliate_payouts SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $new_status, $payout_id);
        $stmt->execute();
        $success = "Payout status updated.";
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Fetch pending payouts
$payouts = $db->query("
    SELECT ap.id, ap.amount, ap.status, ap.created_at, u.name as affiliate_name
    FROM affiliate_payouts ap
    JOIN users u ON ap.affiliate_user_id = u.id
    WHERE ap.status = 'Pending'
    ORDER BY ap.created_at ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<h1>Affiliate Payout Requests</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="card">
    <div class="card-header">Pending Requests</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Affiliate</th><th>Amount</th><th>Date Requested</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payouts)): ?>
                    <tr><td colspan="4" class="text-center">No pending payout requests.</td></tr>
                <?php else: ?>
                    <?php foreach ($payouts as $payout): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payout['affiliate_name']); ?></td>
                            <td>$<?php echo number_format($payout['amount'], 2); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($payout['created_at'])); ?></td>
                            <td>
                                <a href="?action=approve&id=<?php echo $payout['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <a href="?action=reject&id=<?php echo $payout['id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
