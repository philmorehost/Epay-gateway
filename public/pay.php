<?php
// Paystack Payment Initiation
require_once __DIR__ . '/../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to pay.");
}

$invoice_id = $_GET['invoice_id'] ?? null;
if (!$invoice_id) {
    die("Invoice ID is required.");
}

// Fetch invoice details
$stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $invoice_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("Invoice not found or does not belong to you.");
}

// Paystack API details (use placeholders)
$paystack_secret_key = 'sk_test_...'; // Replace with your actual secret key
$paystack_url = 'https://api.paystack.co/transaction/initialize';

// User details
$user_stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();


$fields = [
    'email' => $user['email'],
    'amount' => $invoice['amount'] * 100, // Amount in kobo
    'callback_url' => BASE_URL . '/public/webhook.php',
    'reference' => 'INV' . $invoice_id . '_' . time(),
    'metadata' => [
        'invoice_id' => $invoice_id,
        'user_id' => $_SESSION['user_id']
    ]
];

$fields_string = http_build_query($fields);

// cURL to initialize transaction
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $paystack_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $paystack_secret_key,
    "Cache-Control: no-cache",
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    die("cURL Error: " . $err);
}

$response = json_decode($result, true);

if ($response['status']) {
    header('Location: ' . $response['data']['authorization_url']);
    exit;
} else {
    die("Paystack Error: " . $response['message']);
}
