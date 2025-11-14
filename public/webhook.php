<?php
require_once '../app/core/bootstrap.php';

// --- PAYSTACK CONFIGURATION (PLACEHOLDERS) ---
define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// 1. Retrieve the request's body and parse it as JSON
$input = @file_get_contents("php://input");

// 2. Validate the event
$event = json_decode($input);
if (!$event || !isset($event->event)) {
    http_response_code(400); // Invalid request
    exit();
}

// 3. Verify the signature
if (isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
    $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'];
    $expected_signature = hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY);
    if ($signature !== $expected_signature) {
        http_response_code(401); // Unauthorized
        exit();
    }
}

// 4. Handle the event
http_response_code(200); // Acknowledge receipt of the event

if ($event->event === 'charge.success') {
    $reference = $event->data->reference;

    // Extract invoice ID from reference (e.g., INV-123-timestamp)
    if (preg_match('/^INV-(\d+)-/', $reference, $matches)) {
        $invoice_id = (int)$matches[1];

        try {
            // Check if it's a credit invoice
            $stmt = $db->prepare("SELECT user_id, amount, is_credit_invoice FROM invoices WHERE id = ?");
            $stmt->bind_param('i', $invoice_id);
            $stmt->execute();
            $invoice = $stmt->get_result()->fetch_assoc();

            if ($invoice) {
                $db->begin_transaction();

                // Update the invoice status to 'Paid'
                $stmt = $db->prepare("UPDATE invoices SET status = 'Paid' WHERE id = ? AND status = 'Unpaid'");
                $stmt->bind_param('i', $invoice_id);
                $stmt->execute();

                // If it's a credit invoice, add funds to the user's balance
                if ($invoice['is_credit_invoice']) {
                    $stmt = $db->prepare("UPDATE users SET credit_balance = credit_balance + ? WHERE id = ?");
                    $stmt->bind_param('di', $invoice['amount'], $invoice['user_id']);
                    $stmt->execute();
                } else {
                    // It's a regular invoice, so provision the service
                    // (e.g., create a hosting account)
                }

                $db->commit();
            }

            // In a real application, you might also:
            // - Send a payment confirmation email to the user
            // - Log the transaction for auditing purposes

        } catch (Exception $e) {
            // Log the error. For now, we'll just exit.
            // In a production environment, you would want to monitor these logs.
        }
    }
}

exit();
