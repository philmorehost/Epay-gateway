<?php
require_once '../app/core/bootstrap.php';
require_once '../app/modules/Cpanel.php';

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
                    // It's a regular product invoice, so check if it needs provisioning
                    $stmt = $db->prepare("SELECT p.server_type, p.package_name, o.id as order_id FROM invoices i
                                         JOIN orders o ON i.order_id = o.id
                                         JOIN products p ON o.product_id = p.id
                                         WHERE i.id = ?");
                    $stmt->bind_param('i', $invoice_id);
                    $stmt->execute();
                    $provision_data = $stmt->get_result()->fetch_assoc();

                    if ($provision_data && $provision_data['server_type'] === 'cpanel') {
                        // --- cPanel Provisioning ---
                        $cpanel = new Cpanel();

                        // For this example, we'll generate a random username and password
                        // and use the customer's email domain as the main domain.
                        $user_email_stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
                        $user_email_stmt->bind_param('i', $invoice['user_id']);
                        $user_email_stmt->execute();
                        $user_email = $user_email_stmt->get_result()->fetch_assoc()['email'];

                        $domain = substr(strrchr($user_email, "@"), 1);
                        $username = 'user' . substr(md5(time()), 0, 6);
                        $password = 'pass' . substr(md5(rand()), 0, 10) . '!';

                        $cpanel->create_account($domain, $username, $password, $provision_data['package_name']);

                        // Save to hosting_accounts table
                        $stmt = $db->prepare("INSERT INTO hosting_accounts (order_id, domain, username) VALUES (?, ?, ?)");
                        $stmt->bind_param('iss', $provision_data['order_id'], $domain, $username);
                        $stmt->execute();
                    }
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
