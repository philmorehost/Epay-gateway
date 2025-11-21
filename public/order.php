<?php
$page_title = 'Complete Your Order';
require_once '../app/includes/header.php';

// User must be logged in to order
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

$product_id = $_GET['pid'] ?? null;
if (!$product_id) {
    header('Location: products.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND hidden = 0");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Reseller Pricing Logic
$order_total = $product['price_annually'];
if (defined('IS_RESELLER_STOREFRONT') && IS_RESELLER_STOREFRONT === true) {
    $wholesale_discount = (float)$product['wholesale_discount_percent'];
    $reseller_markup = (float)$GLOBALS['reseller_data']['settings']['retail_markup_percent'];
    $wholesale_price = $product['price_annually'] * (1 - ($wholesale_discount / 100));
    $order_total = $wholesale_price * (1 + ($reseller_markup / 100));
}

// Handle the order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $user_id = $_SESSION['user_id'];

    $db->begin_transaction();
    try {
        // 1. Create the Order, now including product_id
        $order_number = 'ORD-' . time() . '-' . $user_id;
        $stmt = $db->prepare("INSERT INTO orders (user_id, product_id, order_number, total, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param('iisd', $user_id, $product['id'], $order_number, $order_total);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // 2. Create the Invoice
        $due_date = date('Y-m-d', strtotime('+14 days'));
        $stmt = $db->prepare("INSERT INTO invoices (user_id, order_id, created_date, due_date, subtotal, total, status) VALUES (?, ?, NOW(), ?, ?, ?, 'Unpaid')");
        $stmt->bind_param('iisdd', $user_id, $order_id, $due_date, $order_total, $order_total);
        $stmt->execute();
        $invoice_id = $stmt->insert_id;

        // 3. Create the Invoice Item
        $item_description = $product['name'] . " - Annual Plan";
        $stmt = $db->prepare("INSERT INTO invoice_items (invoice_id, description, amount) VALUES (?, ?, ?)");
        $stmt->bind_param('isd', $invoice_id, $item_description, $order_total);
        $stmt->execute();

        $db->commit();

        header("Location: view_invoice.php?id=" . $invoice_id);
        exit;

    } catch (mysqli_sql_exception $exception) {
        $db->rollback();
        die('There was an error processing your order. Please try again.');
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h1 class="mb-4">Confirm Your Order</h1>
        <div class="card">
            <div class="card-header"><h4>Order Summary</h4></div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th scope="row">Product:</th>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Billing Cycle:</th>
                            <td>Annually</td>
                        </tr>
                         <tr>
                            <th scope="row" class="fs-4">Total Due Today:</th>
                            <td class="fs-4">
                                <strong>
                                    <?php if(defined('IS_RESELLER_STOREFRONT') && IS_RESELLER_STOREFRONT === true) echo 'USD '; else echo 'NGN '; ?>
                                    <?php echo number_format($order_total, 2); ?>
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-end">
                     <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$db->close();
require_once '../app/includes/footer.php';
?>
