<?php
// Paystack Webhook Handler

// It's important to respond quickly to the webhook request, so we'll do that first.
http_response_code(200);

require_once '../app/core/bootstrap.php';

// --- Security Check: Verify the Webhook Signature ---
// Get the secret key from the database
$paystack_secret_key = $db->query("SELECT value FROM settings WHERE setting = 'paystack_secret_key'")->fetch_assoc()['value'];

// Only proceed if the secret key is set
if (empty($paystack_secret_key)) {
    // Log this error
    exit();
}

// Validate the request is from Paystack
if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
    // Log this
    exit();
}

$input = @file_get_contents("php://input");
$hash = hash_hmac('sha512', $input, $paystack_secret_key);

if ($hash !== $_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) {
    // Log this invalid request attempt
    exit();
}

// --- Process the Event ---
$event = json_decode($input);

if ($event && $event->event === 'charge.success') {
    $reference = $event->data->reference;
    $amount_paid_kobo = $event->data->amount; // Amount is in kobo (or cents for USD)

    // The reference we generated was like INV123-TIMESTAMP
    // We need to extract the invoice ID.
    if (preg_match('/^INV(\d+)-/', $reference, $matches)) {
        $invoice_id = (int)$matches[1];

        // Use a transaction for database updates
        $db->begin_transaction();

        try {
            // --- Fetch the invoice and lock the row for update ---
            $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND status = 'Unpaid' FOR UPDATE");
            $stmt->bind_param('i', $invoice_id);
            $stmt->execute();
            $invoice = $stmt->get_result()->fetch_assoc();

            if ($invoice) {
                $user_id = $invoice['user_id'];

                // --- Mark Invoice as Paid ---
                $paid_date = date('Y-m-d H:i:s');
                $update_invoice = $db->prepare("UPDATE invoices SET status = 'Paid', paid_date = ?, payment_method = 'Paystack' WHERE id = ?");
                $update_invoice->bind_param('si', $paid_date, $invoice_id);
                $update_invoice->execute();

                // --- Check if it's a Credit Invoice ---
                if ($invoice['is_credit_invoice'] == 1) {
                    $credit_amount = $invoice['total'];
                    $update_user = $db->prepare("UPDATE users SET credit_balance = credit_balance + ? WHERE id = ?");
                    $update_user->bind_param('di', $credit_amount, $user_id);
                    $update_user->execute();
                }

                // --- Add a transaction record ---
                $description = "Payment for Invoice #{$invoice_id}";
                $trans_stmt = $db->prepare("INSERT INTO transactions (invoice_id, user_id, gateway, transaction_id, amount_in, description) VALUES (?, ?, 'Paystack', ?, ?, ?)");
                $trans_stmt->bind_param('iisds', $invoice_id, $user_id, $reference, $invoice['total'], $description);
                $trans_stmt->execute();

                // --- TO-DO: Trigger Provisioning for Regular Product Invoices ---
                // if ($invoice['is_credit_invoice'] == 0 && $invoice['order_id']) {
                //     // Logic to trigger the provisioning module for the order associated with this invoice
                // }

            }

            $db->commit();
        } catch (mysqli_sql_exception $exception) {
            $db->rollback();
            // Log the error
        }
    }
}

exit();
