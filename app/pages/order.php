<?php
// Order Page
// Note: bootstrap.php is included by the router (public/index.php)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Save the intended destination and redirect to login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: /index.php?page=login');
    exit;
}

// Handle the order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $domain = $_POST['domain'] ?? null;

    if ($product_id) {
        // Fetch product details to get the price
        $prod_stmt = $db->prepare("SELECT price_monthly FROM products WHERE id = ?");
        $prod_stmt->bind_param("i", $product_id);
        $prod_stmt->execute();
        $prod_result = $prod_stmt->get_result();
        $product = $prod_result->fetch_assoc();
        $prod_stmt->close();

        if ($product) {
            $db->begin_transaction();
            try {
                // Create the order
                $order_stmt = $db->prepare("INSERT INTO orders (user_id, product_id, domain, status) VALUES (?, ?, ?, 'Pending')");
                $order_stmt->bind_param("iis", $_SESSION['user_id'], $product_id, $domain);
                $order_stmt->execute();
                $order_id = $order_stmt->insert_id;
                $order_stmt->close();

                // Generate the invoice
                $invoice_stmt = $db->prepare("INSERT INTO invoices (user_id, order_id, amount, due_date, status) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Unpaid')");
                $invoice_stmt->bind_param("iid", $_SESSION['user_id'], $order_id, $product['price_monthly']);
                $invoice_stmt->execute();
                $invoice_id = $invoice_stmt->insert_id;
                $invoice_stmt->close();

                $db->commit();

                // Redirect to the new invoice
                header('Location: /index.php?page=view_invoice&id=' . $invoice_id);
                exit;

            } catch (Exception $e) {
                $db->rollback();
                die("Error creating order and invoice: " . $e->getMessage());
            }
        } else {
            die("Product not found.");
        }
    }
}

// Get the product ID from the URL for the confirmation page
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header('Location: /index.php?page=products');
    exit;
}

// Fetch the product from the database
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: /index.php?page=products');
    exit;
}

$page_title = 'Confirm Order';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Confirm Your Order</h2>
    </div>
    <div class="card-body">
        <h4>Product: <?php echo htmlspecialchars($product['name']); ?></h4>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($product['price_monthly']); ?> / month</p>

        <form action="/index.php?page=order" method="post">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <?php if (!empty($product['module'])): ?>
                <div class="mb-3">
                    <label for="domain" class="form-label">Domain Name</label>
                    <input type="text" class="form-control" id="domain" name="domain" required>
                    <small class="form-text text-muted">Enter the domain name for this hosting account.</small>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Complete Order</button>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
