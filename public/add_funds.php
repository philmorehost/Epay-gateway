<?php
$page_title = 'Add Funds';
require_once '../app/includes/header.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

$error_message = '';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token(); // CSRF check

    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if (!$amount || $amount <= 0) {
        $error_message = "Please enter a valid, positive amount.";
    } else {
        // Use a transaction to ensure both invoice and item are created
        $db->begin_transaction();
        try {
            // 1. Create the special "credit" invoice
            $due_date = date('Y-m-d'); // Due immediately
            $stmt = $db->prepare("INSERT INTO invoices (user_id, created_date, due_date, subtotal, total, status, is_credit_invoice) VALUES (?, NOW(), ?, ?, ?, 'Unpaid', 1)");
            $stmt->bind_param('isdd', $user_id, $due_date, $amount, $amount);
            $stmt->execute();
            $invoice_id = $stmt->insert_id;

            // 2. Create the invoice item
            $item_description = "Credit Deposit - Add Funds to Account Balance";
            $stmt = $db->prepare("INSERT INTO invoice_items (invoice_id, description, amount) VALUES (?, ?, ?)");
            $stmt->bind_param('isd', $invoice_id, $item_description, $amount);
            $stmt->execute();

            $db->commit();

            // Redirect to the new invoice to be paid
            header("Location: view_invoice.php?id=" . $invoice_id);
            exit;

        } catch (mysqli_sql_exception $exception) {
            $db->rollback();
            $error_message = "There was an error creating the invoice. Please try again.";
        }
    }
}

?>

<h1 class="mb-4">Add Funds to Your Account</h1>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <p>Enter the amount you wish to add to your credit balance. An invoice will be generated which you can then pay using any of our available payment methods.</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (NGN)</label>
                        <div class="input-group">
                             <span class="input-group-text">NGN</span>
                             <input type="number" step="0.01" min="1.00" class="form-control" id="amount" name="amount" placeholder="e.g., 5000.00" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Generate Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$db->close();
require_once '../app/includes/footer.php';
?>
