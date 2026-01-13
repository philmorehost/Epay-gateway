<?php
$page_title = 'My Invoices';
require_once '../app/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

$user_id = $_SESSION['user_id'];
$invoices_result = $db->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY created_date DESC");
$invoices_result->bind_param('i', $user_id);
$invoices_result->execute();
$invoices = $invoices_result->get_result();

?>

<h1>My Invoices</h1>

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date Created</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($invoices->num_rows > 0): ?>
                    <?php while($invoice = $invoices->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $invoice['id']; ?></td>
                            <td><?php echo date('F j, Y', strtotime($invoice['created_date'])); ?></td>
                            <td><?php echo date('F j, Y', strtotime($invoice['due_date'])); ?></td>
                            <td>NGN <?php echo number_format($invoice['total'], 2); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                switch ($invoice['status']) {
                                    case 'Paid': $status_class = 'bg-success'; break;
                                    case 'Unpaid': $status_class = 'bg-danger'; break;
                                    case 'Cancelled': $status_class = 'bg-secondary'; break;
                                    case 'Awaiting Payment': $status_class = 'bg-warning text-dark'; break;
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $invoice['status']; ?></span>
                            </td>
                            <td>
                                <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-primary">View Invoice</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">You have no invoices.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$db->close();
require_once '../app/includes/footer.php';
?>
