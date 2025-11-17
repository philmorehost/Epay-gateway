<?php
// Paystack Webhook Handler
require_once __DIR__ . '/../app/core/bootstrap.php';

// Retrieve the request's body and parse it as JSON
$input = @file_get_contents("php://input");

// 1. Verify the event is from Paystack by checking the signature
if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) || empty(PAYSTACK_SECRET_KEY)) {
    http_response_code(401); // Unauthorized
    exit();
}

$paystack_signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'];
$expected_signature = hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY);

if (!hash_equals($expected_signature, $paystack_signature)) {
    // The signature doesn't match, this might be a fraudulent request.
    http_response_code(401); // Unauthorized
    error_log("Webhook Error: Invalid Paystack signature.");
    exit();
}

// 2. If signature is valid, decode the event
$event = json_decode($input);

// 3. Process the event
if (isset($event->event) && $event->event === 'charge.success') {
    $reference = $event->data->reference;
    $invoice_id = $event->data->metadata->invoice_id ?? null;
    $user_id = $event->data->metadata->user_id ?? null;
    $amount_paid = $event->data->amount / 100; // Amount in base currency

    if ($invoice_id && $user_id) {
        $db->begin_transaction();
        try {
            // Update the invoice status to Paid
            $inv_stmt = $db->prepare("UPDATE invoices SET status = 'Paid' WHERE id = ? AND user_id = ?");
            $inv_stmt->bind_param("ii", $invoice_id, $user_id);
            $inv_stmt->execute();
            $inv_stmt->close();

            // Check if it's a credit invoice and update user's balance
            $credit_stmt = $db->prepare("SELECT is_credit_invoice FROM invoices WHERE id = ?");
            $credit_stmt->bind_param("i", $invoice_id);
            $credit_stmt->execute();
            $credit_result = $credit_stmt->get_result();
            $invoice = $credit_result->fetch_assoc();
            $credit_stmt->close();

            if ($invoice && $invoice['is_credit_invoice']) {
                $user_stmt = $db->prepare("UPDATE users SET credit_balance = credit_balance + ? WHERE id = ?");
                $user_stmt->bind_param("di", $amount_paid, $user_id);
                $user_stmt->execute();
                $user_stmt->close();
            }

            $db->commit();
            http_response_code(200); // Acknowledge receipt

        } catch (Exception $e) {
            $db->rollback();
            // Log the error
            error_log("Webhook Error: " . $e->getMessage());
            http_response_code(500);
        }
    }
} else {
    http_response_code(400); // Bad request
}
