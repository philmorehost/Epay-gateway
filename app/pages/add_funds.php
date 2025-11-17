<?php
// Add Funds Page
// Note: bootstrap.php is included by the router (public/index.php)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if ($amount === false || $amount <= 0) {
        $error = "Please enter a valid amount.";
    } else {
        // Create a special credit invoice
        $stmt = $db->prepare("INSERT INTO invoices (user_id, amount, status, is_credit_invoice, due_date) VALUES (?, ?, 'Unpaid', 1, CURDATE())");
        $stmt->bind_param("id", $_SESSION['user_id'], $amount);

        if ($stmt->execute()) {
            $invoice_id = $stmt->insert_id;
            header('Location: /index.php?page=view_invoice&id=' . $invoice_id);
            exit;
        } else {
            $error = "Could not create invoice. Please try again.";
        }
        $stmt->close();
    }
}

$page_title = 'Add Funds';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Add Funds to Your Account</h2>
    </div>
    <div class="card-body">
        <p>Add credit to your account to automatically pay for new orders and renewing services.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="/index.php?page=add_funds" method="post">
            <div class="mb-3">
                <label for="amount" class="form-label">Amount to Add</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" min="1.00" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Funds</button>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
