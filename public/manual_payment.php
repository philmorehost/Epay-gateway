<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$invoice_id = $_GET['id'] ?? null;
$method = $_GET['method'] ?? null;

if (!$invoice_id || !$method || !in_array($method, ['bank', 'crypto'])) {
    header('Location: invoices.php');
    exit;
}

// In a real application, these instructions should come from the database settings
$payment_instructions = [
    'bank' => [
        'title' => 'Bank Transfer Instructions',
        'details' => '
            <p><strong>Bank Name:</strong> Example Bank</p>
            <p><strong>Account Name:</strong> Your Company Name</p>
            <p><strong>Account Number:</strong> 1234567890</p>
            <p><strong>Reference:</strong> Invoice #' . htmlspecialchars($invoice_id) . '</p>
            <p>After making the payment, please allow up to 24 hours for manual verification.</p>
        '
    ],
    'crypto' => [
        'title' => 'Cryptocurrency Payment Instructions',
        'details' => '
            <p><strong>Asset:</strong> Bitcoin (BTC)</p>
            <p><strong>Address:</strong> bc1q... (example address)</p>
            <p><strong>Reference:</strong> Please include your invoice number in the transaction memo if possible.</p>
            <p>After sending the payment, please allow up to 48 hours for manual verification.</p>
        '
    ]
];

$instructions = $payment_instructions[$method];

// Update invoice status to "Awaiting Payment"
try {
    $stmt = $db->prepare("UPDATE invoices SET status = 'Awaiting Payment' WHERE id = ? AND user_id = ? AND status = 'Unpaid'");
    $stmt->bind_param('ii', $invoice_id, $_SESSION['user_id']);
    $stmt->execute();
} catch (Exception $e) {
    $_SESSION['error_message'] = "Could not update invoice status. Please try again.";
    header('Location: view_invoice.php?id=' . $invoice_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $instructions['title']; ?> - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1><?php echo $instructions['title']; ?></h1>
    </div>

    <div class="card">
        <div class="card-body">
            <?php echo $instructions['details']; ?>
            <div class="alert alert-info mt-4">
                Your invoice has been marked as "Awaiting Payment". It will be confirmed once your payment is received.
            </div>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="invoices.php">Back to My Invoices</a>
    </div>
</div>
</body>
</html>
