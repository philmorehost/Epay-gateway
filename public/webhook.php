<?php
// Paystack Webhook Handler

http_response_code(200);
require_once '../app/core/bootstrap.php';

// --- Security Check ---
$paystack_secret_key = $db->query("SELECT value FROM settings WHERE setting = 'paystack_secret_key'")->fetch_assoc()['value'];
if (empty($paystack_secret_key) || !isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
    exit();
}
$input = @file_get_contents("php://input");
$hash = hash_hmac('sha512', $input, $paystack_secret_key);
if ($hash !== $_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) {
    exit();
}

// --- Process the Event ---
$event = json_decode($input);
if ($event && $event->event === 'charge.success') {
    $reference = $event->data->reference;
    if (preg_match('/^INV(\d+)-/', $reference, $matches)) {
        $invoice_id = (int)$matches[1];

        $db->begin_transaction();
        try {
            $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND status = 'Unpaid' FOR UPDATE");
            $stmt->bind_param('i', $invoice_id);
            $stmt->execute();
            $invoice = $stmt->get_result()->fetch_assoc();

            if ($invoice) {
                // --- Mark Invoice as Paid ---
                $paid_date = date('Y-m-d H:i:s');
                $update_invoice = $db->prepare("UPDATE invoices SET status = 'Paid', paid_date = ?, payment_method = 'Paystack' WHERE id = ?");
                $update_invoice->bind_param('si', $paid_date, $invoice_id);
                $update_invoice->execute();

                // --- Handle Credit Invoice ---
                if ($invoice['is_credit_invoice'] == 1) {
                    $credit_amount = $invoice['total'];
                    $update_user = $db->prepare("UPDATE users SET credit_balance = credit_balance + ? WHERE id = ?");
                    $update_user->bind_param('di', $credit_amount, $invoice['user_id']);
                    $update_user->execute();
                }

                // --- Handle Product Provisioning ---
                if ($invoice['is_credit_invoice'] == 0 && $invoice['order_id']) {
                    // This is a product invoice, so trigger provisioning.
                    include_once '../app/core/provisioning.php';
                    trigger_provisioning($db, $invoice['order_id']);
                }

                // --- Add a transaction record ---
                // (Code for this is omitted for brevity but would be here)
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            // Log the exception: error_log($e->getMessage());
        }
    }
}

exit();
