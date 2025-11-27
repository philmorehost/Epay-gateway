<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user's invoices
$user_id = $_SESSION['user_id'];
$query = "SELECT i.id,
                 CASE
                     WHEN o.id IS NULL THEN 'Add Funds'
                     ELSE p.name
                 END as product_name,
                 i.amount, i.status, i.due_date
          FROM invoices i
          LEFT JOIN orders o ON i.order_id = o.id
          LEFT JOIN products p ON o.product_id = p.id
          WHERE i.user_id = ?
          ORDER BY i.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle messages from order processing
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Invoices - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>My Invoices</h1>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="6" class="text-center">You have no invoices.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo $invoice['id']; ?></td>
                                <td><?php echo htmlspecialchars($invoice['product_name']); ?></td>
                                <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                                <td><?php echo date('M j, Y', strtotime($invoice['due_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $invoice['status'] === 'Paid' ? 'success' : 'warning'; ?>">
                                        <?php echo htmlspecialchars($invoice['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="index.php">Back to Dashboard</a> | <a href="products.php">Browse Products</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
