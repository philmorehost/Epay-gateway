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
$domain_name = $_POST['domain_name'] ?? null;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

try {
    // Fetch product details
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        throw new Exception("Product not found.");
    }

    $final_amount = ($product['product_type'] === 'domain') ? $product['price_annually'] : $product['price_monthly'];
    $coupon_id_to_update = null;

    // Fetch tax rate
    $tax_rate_setting = $db->query("SELECT value FROM settings WHERE setting = 'tax_rate'")->fetch_assoc();
    $tax_rate = (float)($tax_rate_setting['value'] ?? 0);
    $tax_amount = 0;

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

    // Calculate tax on the final amount (after discount)
    if ($tax_rate > 0) {
        $tax_amount = $final_amount * ($tax_rate / 100);
        $final_amount += $tax_amount;
    }

    $db->begin_transaction();

    // Create the order
    $stmt = $db->prepare("INSERT INTO orders (user_id, product_id, domain_name) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $_SESSION['user_id'], $product_id, $domain_name);
    $stmt->execute();
    $order_id = $db->insert_id;

    // Create the invoice
    $due_date = date('Y-m-d', strtotime('+14 days'));
    $stmt = $db->prepare("INSERT INTO invoices (user_id, order_id, amount, tax_amount, due_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iidds', $_SESSION['user_id'], $order_id, $final_amount, $tax_amount, $due_date);
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
