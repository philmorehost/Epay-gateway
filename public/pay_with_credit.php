<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: invoices.php');
    exit;
}

$invoice_id = $_POST['invoice_id'] ?? null;
if (!$invoice_id) {
    header('Location: invoices.php');
    exit;
}

try {
    $db->begin_transaction();

    // 1. Lock the user and invoice rows to prevent race conditions
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT credit_balance FROM users WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    $stmt = $db->prepare("SELECT amount, status FROM invoices WHERE id = ? AND user_id = ? FOR UPDATE");
    $stmt->bind_param('ii', $invoice_id, $user_id);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();

    // 2. Verify everything is correct
    if (!$user || !$invoice) {
        throw new Exception("User or invoice not found.");
    }
    if ($invoice['status'] !== 'Unpaid') {
        throw new Exception("This invoice cannot be paid.");
    }
    if ($user['credit_balance'] < $invoice['amount']) {
        throw new Exception("Insufficient credit balance.");
    }

    // 3. Perform the transaction
    $new_balance = $user['credit_balance'] - $invoice['amount'];
    $stmt = $db->prepare("UPDATE users SET credit_balance = ? WHERE id = ?");
    $stmt->bind_param('di', $new_balance, $user_id);
    $stmt->execute();

    $stmt = $db->prepare("UPDATE invoices SET status = 'Paid' WHERE id = ?");
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();

    $db->commit();

    $_SESSION['success_message'] = "Invoice #" . $invoice_id . " has been paid successfully using your credit balance.";
    header('Location: view_invoice.php?id=' . $invoice_id);
    exit;

} catch (Exception $e) {
    $db->rollback();
    $_SESSION['error_message'] = "Payment failed: " . $e->getMessage();
    header('Location: view_invoice.php?id=' . $invoice_id);
    exit;
}
