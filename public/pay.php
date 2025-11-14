<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id) {
    header('Location: invoices.php');
    exit;
}

// --- PAYSTACK CONFIGURATION (PLACEHOLDERS) ---
// In a real application, these should be stored in the database settings
define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
$base_url = 'http://localhost:8000'; // This should come from a system setting

try {
    // 1. Fetch invoice and user details
    $stmt = $db->prepare(
        "SELECT i.amount, u.email
         FROM invoices i
         JOIN users u ON i.user_id = u.id
         WHERE i.id = ? AND i.user_id = ?"
    );
    $stmt->bind_param('ii', $invoice_id, $_SESSION['user_id']);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();

    if (!$invoice) {
        throw new Exception("Invoice not found.");
    }

    // 2. Prepare data for Paystack API
    $amount_in_kobo = $invoice['amount'] * 100;
    $reference = 'INV-' . $invoice_id . '-' . time();
    $callback_url = $base_url . '/verify_payment.php';

    $post_data = [
        'email' => $invoice['email'],
        'amount' => $amount_in_kobo,
        'reference' => $reference,
        'callback_url' => $callback_url,
    ];

    // 3. Initialize cURL to call Paystack API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new Exception("cURL Error: " . $err);
    }

    $result = json_decode($response, true);

    if (isset($result['status']) && $result['status'] === true) {
        // 4. Redirect user to the payment page
        $authorization_url = $result['data']['authorization_url'];
        header('Location: ' . $authorization_url);
        exit;
    } else {
        throw new Exception("Paystack API Error: " . ($result['message'] ?? 'Unknown error'));
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Payment initialization failed: " . $e->getMessage();
    header('Location: invoices.php');
    exit;
}
