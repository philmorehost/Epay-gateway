<?php
$page_title = 'Register Domain';
require_once '../app/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

$domain_name = $_GET['domain'] ?? null;
if (!$domain_name) {
    header('Location: domain_checker.php');
    exit;
}

$tld = substr($domain_name, strpos($domain_name, '.') + 1);
$stmt = $db->prepare("SELECT registration_price FROM tld_pricing WHERE tld = ? AND is_active = 1");
$stmt->bind_param('s', $tld);
$stmt->execute();
$price_result = $stmt->get_result()->fetch_assoc();

if (!$price_result) {
    header('Location: domain_checker.php?error=tld_unavailable');
    exit;
}
$registration_price = $price_result['registration_price'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $user_id = $_SESSION['user_id'];
    $db->begin_transaction();
    try {
        // Create a 'product' on the fly for this TLD if it doesn't exist
        $product_name = "Domain Registration - ." . $tld;
        $prod_stmt = $db->prepare("SELECT id FROM products WHERE name = ?");
        $prod_stmt->bind_param('s', $product_name);
        $prod_stmt->execute();
        $prod_result = $prod_stmt->get_result()->fetch_assoc();

        if ($prod_result) {
            $product_id = $prod_result['id'];
        } else {
            $insert_prod = $db->prepare("INSERT INTO products (name, category, module) VALUES (?, 'Domains', 'ConnectReseller')");
            $insert_prod->bind_param('s', $product_name);
            $insert_prod->execute();
            $product_id = $insert_prod->insert_id;
        }

        // Create the Order
        $order_number = 'DOM-' . time() . '-' . $user_id;
        $order_stmt = $db->prepare("INSERT INTO orders (user_id, product_id, order_number, total, domain, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $order_stmt->bind_param('iisds', $user_id, $product_id, $order_number, $registration_price, $domain_name);
        $order_stmt->execute();
        $order_id = $order_stmt->insert_id;

        // Create the Invoice
        $due_date = date('Y-m-d', strtotime('+7 days'));
        $inv_stmt = $db->prepare("INSERT INTO invoices (user_id, order_id, created_date, due_date, subtotal, total) VALUES (?, ?, NOW(), ?, ?, ?)");
        $inv_stmt->bind_param('iisdd', $user_id, $order_id, $due_date, $registration_price, $registration_price);
        $inv_stmt->execute();
        $invoice_id = $inv_stmt->insert_id;

        // Create Invoice Item
        $item_desc = "Registration of domain: " . $domain_name;
        $item_stmt = $db->prepare("INSERT INTO invoice_items (invoice_id, description, amount) VALUES (?, ?, ?)");
        $item_stmt->bind_param('isd', $invoice_id, $item_desc, $registration_price);
        $item_stmt->execute();

        $db->commit();
        header("Location: view_invoice.php?id=" . $invoice_id);
        exit;
    } catch (Exception $e) {
        $db->rollback();
        die("An error occurred while processing your order. Please try again.");
    }
}
?>

<h1 class="mb-4">Confirm Domain Registration</h1>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4>Order Summary</h4></div>
            <div class="card-body">
                <p>You are about to register the following domain name. Please review the details below and confirm your order.</p>
                <table class="table">
                    <tr>
                        <th>Domain Name</th>
                        <td><?php echo htmlspecialchars($domain_name); ?></td>
                    </tr>
                    <tr>
                        <th>Registration Period</th>
                        <td>1 Year</td>
                    </tr>
                    <tr class="fs-4">
                        <th>Total Due</th>
                        <td><strong>NGN <?php echo number_format($registration_price, 2); ?></strong></td>
                    </tr>
                </table>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="d-flex justify-content-between">
                        <a href="domain_checker.php?domain=<?php echo htmlspecialchars($domain_name); ?>" class="btn btn-secondary">Go Back</a>
                        <button type="submit" class="btn btn-primary">Confirm & Create Invoice</button>
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
