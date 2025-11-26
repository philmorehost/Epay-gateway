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

try {
    // 1. Fetch settings, invoice, and user details
    $settings_result = $db->query("SELECT * FROM settings WHERE setting IN ('base_url', 'base_currency', 'secondary_currency', 'usd_conversion_rate', 'paystack_secret_key')");
    $settings = [];
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting']] = $row['value'];
    }

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

    // 2. Handle currency conversion
    $selected_currency = $_GET['currency'] ?? $settings['base_currency'];
    $amount_to_pay = $invoice['amount'];

    if ($selected_currency === $settings['secondary_currency']) {
        $amount_to_pay *= (float)$settings['usd_conversion_rate'];
    }

    // 3. Prepare data for Paystack API
    $amount_in_kobo = round($amount_to_pay * 100);
    $reference = 'INV-' . $invoice_id . '-' . time();
    $callback_url = $settings['base_url'] . '/verify_payment.php';

    $post_data = [
        'email' => $invoice['email'],
        'amount' => $amount_in_kobo,
        'reference' => $reference,
        'currency' => $selected_currency,
        'callback_url' => $callback_url,
    ];

    // 3. Initialize cURL to call Paystack API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . ($settings['paystack_secret_key'] ?? ''),
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
