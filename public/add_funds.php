<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if ($amount === false || $amount <= 0) {
        $error = "Please enter a valid amount.";
    } else {
        try {
            $user_id = $_SESSION['user_id'];

            // Create a credit invoice directly without a product/order
            $due_date = date('Y-m-d');
            $stmt = $db->prepare("INSERT INTO invoices (user_id, order_id, amount, due_date, status, is_credit_invoice) VALUES (?, 0, ?, ?, 'Unpaid', 1)");
            $stmt->bind_param('ids', $user_id, $amount, $due_date);
            $stmt->execute();
            $invoice_id = $db->insert_id;

            // Redirect to the invoice to be paid
            header('Location: view_invoice.php?id=' . $invoice_id);
            exit;

        } catch (Exception $e) {
            $error = "An error occurred. Please try again. " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Funds - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Add Funds to Your Account</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card" style="max-width: 500px; margin: auto;">
        <div class="card-body">
            <form action="add_funds.php" method="post">
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount to Add ($)</label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required min="1.00">
                </div>
                <button type="submit" class="btn btn-primary w-100">Add Funds</button>
            </form>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="index.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
