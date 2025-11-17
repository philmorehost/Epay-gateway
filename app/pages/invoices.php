<?php
// Client Invoices Page
// Note: bootstrap.php is included by the router (public/index.php)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

// Fetch all invoices for the current user
$stmt = $db->prepare("SELECT i.id, i.amount, i.status, i.due_date, p.name as product_name
                      FROM invoices i
                      LEFT JOIN orders o ON i.order_id = o.id
                      LEFT JOIN products p ON o.product_id = p.id
                      WHERE i.user_id = ?
                      ORDER BY i.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$invoices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'My Invoices';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>My Invoices</h2>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th></th>
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
                            <td><?php echo htmlspecialchars($invoice['product_name'] ?? 'N/A'); ?></td>
                            <td>$<?php echo htmlspecialchars($invoice['amount']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['due_date']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $invoice['status'] === 'Paid' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($invoice['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/index.php?page=view_invoice&id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
