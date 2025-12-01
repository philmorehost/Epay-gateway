<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id) {
    header('Location: invoices.php');
    exit;
}

// Fetch invoice details
$stmt = $db->prepare(
    "SELECT i.id, i.amount, i.status, i.due_date, p.name as product_name
     FROM invoices i
     JOIN orders o ON i.order_id = o.id
     JOIN products p ON o.product_id = p.id
     WHERE i.id = ? AND i.user_id = ?"
);
$stmt->bind_param('ii', $invoice_id, $_SESSION['user_id']);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    $_SESSION['error_message'] = "Invoice not found.";
    header('Location: invoices.php');
    exit;
}

// Fetch user's credit balance
$stmt = $db->prepare("SELECT credit_balance FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$credit_balance = $user['credit_balance'];

// Fetch currency settings for display
$settings_result = $db->query("SELECT * FROM settings WHERE setting IN ('base_currency', 'secondary_currency')");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting']] = $row['value'];
}
$base_currency = $settings['base_currency'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invoice #<?php echo htmlspecialchars($invoice['id']); ?> - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Invoice #<?php echo htmlspecialchars($invoice['id']); ?></h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Billed To:</h5>
                    <p><!-- User details would go here --></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Invoice Details:</h5>
                    <p><strong>Date:</strong> <?php echo date('M j, Y'); ?></p>
                    <p><strong>Due Date:</strong> <?php echo date('M j, Y', strtotime($invoice['due_date'])); ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-<?php echo $invoice['status'] === 'Paid' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($invoice['status']); ?></span></p>
                </div>
            </div>
            <hr>
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['product_name']); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars($base_currency); ?> <?php echo number_format($invoice['amount'], 2); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-end">Total:</th>
                        <th class="text-end"><?php echo htmlspecialchars($base_currency); ?> <?php echo number_format($invoice['amount'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>

            <?php if ($invoice['status'] === 'Unpaid'): ?>
            <div class="text-center mt-4">
                <h5>Pay with Credit</h5>
                <p>Your current credit balance: <strong>$<?php echo number_format($credit_balance, 2); ?></strong></p>
                <?php if ($credit_balance >= $invoice['amount']): ?>
                    <form action="pay_with_credit.php" method="post" class="d-inline">
                        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                        <button type="submit" class="btn btn-primary btn-lg mx-2">Apply Credit ($<?php echo number_format($invoice['amount'], 2); ?>)</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted">You do not have enough credit to pay this invoice.</p>
                <?php endif; ?>
            </div>
            <hr>
            <div class="text-center mt-4">
                <h5>Other Payment Methods</h5>
                <form action="pay.php" method="get" class="d-inline">
                    <input type="hidden" name="id" value="<?php echo $invoice['id']; ?>">
                    <select name="currency" class="form-select-sm">
                        <option value="<?php echo $settings['base_currency']; ?>"><?php echo $settings['base_currency']; ?></option>
                        <option value="<?php echo $settings['secondary_currency']; ?>"><?php echo $settings['secondary_currency']; ?></option>
                    </select>
                    <button type="submit" class="btn btn-success btn-lg mx-2">Pay with Paystack</button>
                </form>
                <a href="manual_payment.php?id=<?php echo $invoice['id']; ?>&method=bank" class="btn btn-secondary btn-lg mx-2">Pay with Bank Transfer</a>
                <a href="manual_payment.php?id=<?php echo $invoice['id']; ?>&method=crypto" class="btn btn-secondary btn-lg mx-2">Pay with Cryptocurrency</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="invoices.php">Back to My Invoices</a>
    </div>
</div>
</body>
</html>
