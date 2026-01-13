<?php
$page_title = 'View Invoice';
require_once '../app/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

$user_id = $_SESSION['user_id'];
$invoice_id = $_GET['id'] ?? null;

if (!$invoice_id) {
    header('Location: invoices.php');
    exit;
}

// Fetch settings first
$settings_result = $db->query("SELECT * FROM settings WHERE setting IN ('paystack_public_key', 'usd_conversion_rate')");
$settings = [];
while($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting']] = $row['value'];
}
$paystack_public_key = $settings['paystack_public_key'] ?? '';
$usd_conversion_rate = (float)($settings['usd_conversion_rate'] ?? 1500); // Default rate


// Fetch invoice making sure it belongs to the logged-in user
$stmt = $db->prepare("SELECT i.*, u.first_name, u.last_name, u.email, u.company_name, u.address_1, u.city, u.state, u.zip_code, u.country
                     FROM invoices i
                     JOIN users u ON i.user_id = u.id
                     WHERE i.id = ? AND i.user_id = ?");
$stmt->bind_param('ii', $invoice_id, $user_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    header('Location: invoices.php');
    exit;
}

// Fetch invoice items
$items_stmt = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$items_stmt->bind_param('i', $invoice_id);
$items_stmt->execute();
$invoice_items = $items_stmt->get_result();

// Calculate USD amount
$total_ngn = $invoice['total'];
$total_usd = $total_ngn / $usd_conversion_rate;
$total_usd_cents = round($total_usd * 100);

?>

<div class="card">
    <div class="card-header">
        <h2>Invoice #<?php echo $invoice['id']; ?></h2>
        Date: <?php echo date('F j, Y', strtotime($invoice['created_date'])); ?>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-sm-6">
                <h6 class="mb-3">Billed To:</h6>
                <div><strong><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></strong></div>
                 <?php if($invoice['company_name']) echo '<div>' . htmlspecialchars($invoice['company_name']) . '</div>'; ?>
                <div><?php echo htmlspecialchars($invoice['address_1']); ?></div>
                <div><?php echo htmlspecialchars($invoice['city'] . ', ' . $invoice['state'] . ' ' . $invoice['zip_code']); ?></div>
                <div><?php echo htmlspecialchars($invoice['country']); ?></div>
                <div>Email: <?php echo htmlspecialchars($invoice['email']); ?></div>
            </div>
        </div>

        <div class="table-responsive-sm">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="center">#</th>
                        <th>Item</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while($item = $invoice_items->fetch_assoc()): ?>
                    <tr>
                        <td class="center"><?php echo $i++; ?></td>
                        <td class="left strong"><?php echo htmlspecialchars($item['description']); ?></td>
                        <td class="right">NGN <?php echo number_format($item['amount'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-lg-4 col-sm-5 ms-auto">
                <table class="table table-clear">
                    <tbody>
                        <tr>
                            <td class="left"><strong>Subtotal</strong></td>
                            <td class="right">NGN <?php echo number_format($invoice['subtotal'], 2); ?></td>
                        </tr>
                        <tr class="table-primary">
                            <td class="left"><strong>Total (NGN)</strong></td>
                            <td class="right"><strong>NGN <?php echo number_format($invoice['total'], 2); ?></strong></td>
                        </tr>
                         <tr>
                            <td class="left"><strong>Total (USD Approx.)</strong></td>
                            <td class="right"><strong>$<?php echo number_format($total_usd, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($invoice['status'] == 'Unpaid'): ?>
            <div class="text-center mt-4 p-4 border rounded bg-light">
                <h4>Select Payment Method</h4>
                <p>You will be redirected to the payment gateway to complete your purchase.</p>
                <?php if (!empty($paystack_public_key)): ?>
                    <button class="btn btn-lg btn-success" onclick="payWithPaystack()">Pay with Paystack (USD)</button>
                <?php else: ?>
                    <p class="text-danger">Online payments are currently unavailable. Please contact support.</p>
                <?php endif; ?>
            </div>
        <?php elseif ($invoice['status'] == 'Paid'): ?>
             <div class="alert alert-success text-center mt-4">
                <h4>Payment Confirmed</h4>
                <p>This invoice was paid on <?php echo date('F j, Y', strtotime($invoice['paid_date'])); ?>.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function payWithPaystack() {
  let handler = PaystackPop.setup({
    key: '<?php echo $paystack_public_key; ?>',
    email: '<?php echo $invoice['email']; ?>',
    amount: <?php echo $total_usd_cents; ?>,
    currency: 'USD',
    ref: 'INV<?php echo $invoice['id']; ?>-<?php echo time(); ?>',
    callback: function(response){
      // This will be the URL to a verification script
      window.location.href = 'verify_payment.php?reference=' + response.reference;
    },
    onClose: function(){
      alert('Payment window closed.');
    }
  });
  handler.openIframe();
}
</script>

<?php
$db->close();
require_once '../app/includes/footer.php';
?>
