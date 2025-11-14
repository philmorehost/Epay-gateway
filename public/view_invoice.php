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

// In a real application, you would fetch the invoice details from the database here.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invoice - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Invoice #<?php echo htmlspecialchars($invoice_id); ?></h1>
    </div>

    <div class="card">
        <div class="card-body">
            <p>This is a placeholder for the invoice details. The full invoice view with payment options will be implemented in a future step.</p>

            <div class="mt-4">
                <h4>Payment Details</h4>
                <p><strong>Amount Due:</strong> $XX.XX</p>
                <p><strong>Due Date:</strong> YYYY-MM-DD</p>
                <button class="btn btn-success">Pay Now with Paystack</button>
            </div>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="invoices.php">Back to My Invoices</a>
    </div>
</div>
</body>
</html>
