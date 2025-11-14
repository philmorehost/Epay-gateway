<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store the intended destination and redirect to login
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header('Location: products.php');
    exit;
}

try {
    // 1. Fetch product details
    $stmt = $db->prepare("SELECT price_monthly FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        throw new Exception("Product not found.");
    }

    $db->begin_transaction();

    // 2. Create the order
    $stmt = $db->prepare("INSERT INTO orders (user_id, product_id, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param('ii', $_SESSION['user_id'], $product_id);
    $stmt->execute();
    $order_id = $db->insert_id;

    // 3. Create the invoice
    $due_date = date('Y-m-d', strtotime('+14 days'));
    $stmt = $db->prepare("INSERT INTO invoices (user_id, order_id, amount, status, due_date) VALUES (?, ?, ?, 'Unpaid', ?)");
    $stmt->bind_param('iids', $_SESSION['user_id'], $order_id, $product['price_monthly'], $due_date);
    $stmt->execute();

    $db->commit();

    // Redirect to invoices page with a success message
    $_SESSION['success_message'] = "Order placed successfully! Your invoice has been generated.";
    header('Location: invoices.php');
    exit;

} catch (Exception $e) {
    $db->rollback();
    // In a real app, log this error
    $_SESSION['error_message'] = "There was an error placing your order. Please try again.";
    header('Location: products.php');
    exit;
}
