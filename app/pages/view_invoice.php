<?php
// View Invoice Page
// Note: bootstrap.php is included by the router (public/index.php)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

// Get invoice ID from URL
$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id) {
    header('Location: /index.php?page=invoices');
    exit;
}

// Fetch the invoice and ensure it belongs to the current user
$stmt = $db->prepare("SELECT i.*, p.name as product_name, p.description as product_description
                      FROM invoices i
                      LEFT JOIN orders o ON i.order_id = o.id
                      LEFT JOIN products p ON o.product_id = p.id
                      WHERE i.id = ? AND i.user_id = ?");
$stmt->bind_param("ii", $invoice_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    // Redirect if invoice not found or doesn't belong to the user
    header('Location: /index.php?page=invoices');
    exit;
}

$page_title = 'Invoice #' . $invoice['id'];
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2>Invoice #<?php echo $invoice['id']; ?></h2>
        <span class="badge bg-<?php echo $invoice['status'] === 'Paid' ? 'success' : 'warning'; ?> p-2">
            <?php echo htmlspecialchars($invoice['status']); ?>
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Billed To:</strong><br>
                <?php echo htmlspecialchars($_SESSION['user_name']); ?><br>
                <!-- Add more user details here as needed -->
            </div>
            <div class="col-md-6 text-md-end">
                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($invoice['created_at'])); ?><br>
                <strong>Due Date:</strong> <?php echo date('F j, Y', strtotime($invoice['due_date'])); ?><br>
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
                    <td>
                        <strong><?php echo htmlspecialchars($invoice['product_name'] ?? 'Credit Deposit'); ?></strong><br>
                        <small><?php echo htmlspecialchars($invoice['product_description'] ?? 'Adding funds to your account.'); ?></small>
                    </td>
                    <td class="text-end">$<?php echo htmlspecialchars($invoice['amount']); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end">Total:</th>
                    <th class="text-end">$<?php echo htmlspecialchars($invoice['amount']); ?></th>
                </tr>
            </tfoot>
        </table>

        <?php if ($invoice['status'] === 'Unpaid'): ?>
        <div class="text-end">
            <p>Payment options will appear here.</p>
            <!-- Payment buttons will be added in a future step -->
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
