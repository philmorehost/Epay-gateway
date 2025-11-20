<?php
// This page is a placeholder for the full webhook implementation.
// In a real application, you would not rely solely on this redirect for verification.
// The webhook is the source of truth.

$page_title = 'Payment Verification';
require_once '../app/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

$reference = $_GET['reference'] ?? null;

?>

<div class="text-center">
    <h1 class="mb-4">Verifying Your Payment...</h1>

    <?php if ($reference): ?>
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Your payment is being verified. Please do not close this page.</p>
        <p>Your reference is: <strong><?php echo htmlspecialchars($reference); ?></strong></p>
        <p class="text-muted">In a moment, this page would confirm the payment with the gateway and update your invoice. For now, we are waiting for the webhook to process.</p>
        <a href="invoices.php" class="btn btn-primary mt-3">Go to My Invoices</a>

    <?php else: ?>
        <div class="alert alert-danger">
            No payment reference found. Your transaction may not have been successful.
        </div>
        <a href="invoices.php" class="btn btn-secondary mt-3">Go to My Invoices</a>
    <?php endif; ?>
</div>


<?php
$db->close();
require_once '../app/includes/footer.php';
?>
