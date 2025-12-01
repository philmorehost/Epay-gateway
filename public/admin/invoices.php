<?php
require_once 'templates/header.php';

// Fetch all invoices with user and product details
$query = "SELECT
            i.id,
            i.amount,
            i.status,
            i.due_date,
            i.created_at,
            u.name as user_name,
            CASE
                WHEN o.id IS NULL THEN 'Add Funds'
                ELSE p.name
            END as product_name
          FROM invoices i
          JOIN users u ON i.user_id = u.id
          LEFT JOIN orders o ON i.order_id = o.id
          LEFT JOIN products p ON o.product_id = p.id
          ORDER BY i.created_at DESC";
$invoices = $db->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<h1>All Invoices</h1>

<div class="card">
    <div class="card-header">Invoice List</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client Name</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Date Created</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No invoices found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo $invoice['id']; ?></td>
                            <td><?php echo htmlspecialchars($invoice['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['product_name']); ?></td>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                            <td><?php echo date('M j, Y', strtotime($invoice['created_at'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($invoice['due_date'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $invoice['status'] === 'Paid' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($invoice['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
