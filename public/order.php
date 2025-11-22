<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$coupon_code = $_POST['coupon_code'] ?? null;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

try {
    // Fetch product details
    $stmt = $db->prepare("SELECT price_monthly FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        throw new Exception("Product not found.");
    }

    $final_amount = $product['price_monthly'];
    $coupon_id_to_update = null;

    // Validate coupon if provided
    if (!empty($coupon_code)) {
        $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND (expires_at IS NULL OR expires_at >= CURDATE()) AND (max_uses = 0 OR uses < max_uses)");
        $stmt->bind_param('s', $coupon_code);
        $stmt->execute();
        $coupon = $stmt->get_result()->fetch_assoc();

        if ($coupon) {
            if ($coupon['type'] === 'percentage') {
                $final_amount -= $final_amount * ($coupon['value'] / 100);
            } else { // fixed
                $final_amount -= $coupon['value'];
            }
            if ($final_amount < 0) $final_amount = 0;
            $coupon_id_to_update = $coupon['id'];
        } else {
            throw new Exception("Invalid or expired coupon code.");
        }
    }

    $db->begin_transaction();

    // Create the order
    $stmt = $db->prepare("INSERT INTO orders (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $_SESSION['user_id'], $product_id);
    $stmt->execute();
    $order_id = $db->insert_id;

    // Create the invoice
    $due_date = date('Y-m-d', strtotime('+14 days'));
    $stmt = $db->prepare("INSERT INTO invoices (user_id, order_id, amount, due_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iids', $_SESSION['user_id'], $order_id, $final_amount, $due_date);
    $stmt->execute();

    // Increment coupon usage
    if ($coupon_id_to_update) {
        $db->query("UPDATE coupons SET uses = uses + 1 WHERE id = $coupon_id_to_update");
    }

    $db->commit();

    $_SESSION['success_message'] = "Order placed successfully! Your invoice has been generated.";
    header('Location: invoices.php');
    exit;

} catch (Exception $e) {
    $db->rollback();
    $_SESSION['error_message'] = "Order failed: " . $e->getMessage();
    header('Location: order_summary.php?id=' . $product_id);
    exit;
}
