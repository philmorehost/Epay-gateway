<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$product_id = $_GET['id'] ?? null;
$domain_name = $_GET['domain'] ?? null;
$product = null;

if ($domain_name) {
    $tld = '.' . explode('.', $domain_name, 2)[1];
    $stmt = $db->prepare("SELECT * FROM products WHERE product_type = 'domain' AND tld = ?");
    $stmt->bind_param('s', $tld);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
} elseif ($product_id) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
}

if (!$product) {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Order Summary</h1>
    </div>

    <div class="card" style="max-width: 600px; margin: auto;">
        <div class="card-body">
            <?php if ($domain_name): ?>
                <h4>Registering: <?php echo htmlspecialchars($domain_name); ?></h4>
                <p>1 Year Registration</p>
            <?php else: ?>
                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
            <?php endif; ?>

            <p class="fs-4 fw-bold">$<?php echo number_format($product['price_annually'], 2); ?>/year</p>
            <hr>
            <form action="order.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <?php if ($domain_name) echo "<input type='hidden' name='domain_name' value='".htmlspecialchars($domain_name)."'>"; ?>
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" name="coupon_code" class="form-control" placeholder="Enter Coupon Code (Optional)">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Place Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
