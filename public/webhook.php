<?php
require_once '../app/core/bootstrap.php';
require_once '../app/modules/Cpanel.php';
require_once '../app/modules/ConnectReseller.php';
require_once '../app/modules/Nocix.php';

// 1. Fetch Paystack secret key from database
$stmt = $db->prepare("SELECT value FROM settings WHERE setting = ?");
$setting_name = 'paystack_secret_key';
$stmt->bind_param('s', $setting_name);
$stmt->execute();
$paystack_secret_key = $stmt->get_result()->fetch_assoc()['value'] ?? '';

// 2. Retrieve the request's body and parse it as JSON
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
    $expected_signature = hash_hmac('sha512', $input, $paystack_secret_key);
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
                    $stmt = $db->prepare("SELECT p.product_type, p.server_type, p.package_name, o.id as order_id, o.domain_name FROM invoices i
                                         JOIN orders o ON i.order_id = o.id
                                         JOIN products p ON o.product_id = p.id
                                         WHERE i.id = ?");
                    $stmt->bind_param('i', $invoice_id);
                    $stmt->execute();
                    $provision_data = $stmt->get_result()->fetch_assoc();

                    if ($provision_data) {
                        if ($provision_data['server_type'] === 'cpanel') {
                            // --- cPanel Provisioning ---
                            $cpanel = new Cpanel();

                            $user_details_stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
                            $user_details_stmt->bind_param('i', $invoice['user_id']);
                            $user_details_stmt->execute();
                            $user_details = $user_details_stmt->get_result()->fetch_assoc();

                            // **FIX:** Use the actual domain name from the order, not the customer's email domain.
                            $domain = $provision_data['domain_name'];

                            // Generate a more secure username and password
                            $username = strtolower(substr(preg_replace('/[^a-zA-Z0-9]/', '', $user_details['name']), 0, 5)) . rand(100, 999);
                            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()'), 0, 12) . '!';

                            $cpanel->create_account($domain, $username, $password, $provision_data['package_name']);

                            $stmt = $db->prepare("INSERT INTO hosting_accounts (order_id, domain, username) VALUES (?, ?, ?)");
                            $stmt->bind_param('iss', $provision_data['order_id'], $domain, $username);
                            $stmt->execute();
                        } elseif ($provision_data['product_type'] === 'domain') {
                            // --- Domain Registration ---
                            $connect_reseller = new ConnectReseller();

                            $user_details_stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
                            $user_details_stmt->bind_param('i', $invoice['user_id']);
                            $user_details_stmt->execute();
                            $customer_details = $user_details_stmt->get_result()->fetch_assoc();

                            $connect_reseller->register_domain($provision_data['domain_name'], $customer_details);

                            $expires_at = date('Y-m-d', strtotime('+1 year'));
                            $stmt = $db->prepare("INSERT INTO domains (order_id, domain_name, registrar, expires_at) VALUES (?, ?, 'ConnectReseller', ?)");
                            $stmt->bind_param('iss', $provision_data['order_id'], $provision_data['domain_name'], $expires_at);
                            $stmt->execute();
                        } elseif ($provision_data['server_type'] === 'nocix') {
                            // --- NOCIX Provisioning ---
                            $nocix = new Nocix();
                            $server_details = $nocix->provision_server($provision_data['package_name']);

                            if (isset($server_details['status']) && $server_details['status'] === 'success') {
                                $stmt = $db->prepare("INSERT INTO dedicated_servers (order_id, server_id, ip_address, status) VALUES (?, ?, ?, 'active')");
                                $stmt->bind_param('iss', $provision_data['order_id'], $server_details['id'], $server_details['ip']);
                                $stmt->execute();
                            }
                        }
                    }
                }

                $db->commit();

                // --- Affiliate Commission Logic ---
                // Check if this is a referred user's first paid invoice
                $ref_stmt = $db->prepare("SELECT affiliate_user_id FROM affiliate_referrals WHERE referred_user_id = ?");
                $ref_stmt->bind_param('i', $invoice['user_id']);
                $ref_stmt->execute();
                $referral = $ref_stmt->get_result()->fetch_assoc();

                if ($referral) {
                    $paid_invoices_stmt = $db->prepare("SELECT COUNT(id) as count FROM invoices WHERE user_id = ? AND status = 'Paid'");
                    $paid_invoices_stmt->bind_param('i', $invoice['user_id']);
                    $paid_invoices_stmt->execute();
                    $paid_count = $paid_invoices_stmt->get_result()->fetch_assoc()['count'];

                    if ($paid_count === 1) { // This is the first paid invoice
                        $commission_rate = (float)($settings['affiliate_commission_percentage'] ?? 0);
                        if ($commission_rate > 0) {
                            $commission_amount = $invoice['amount'] * ($commission_rate / 100);

                            $update_aff_stmt = $db->prepare("UPDATE affiliates SET commission_balance = commission_balance + ? WHERE user_id = ?");
                            $update_aff_stmt->bind_param('di', $commission_amount, $referral['affiliate_user_id']);
                            $update_aff_stmt->execute();
                        }
                    }
                }
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
