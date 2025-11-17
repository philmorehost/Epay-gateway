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
            } else {
                // --- Start Provisioning Logic ---
                // Get order and product details
                $order_info_stmt = $db->prepare("
                    SELECT o.id as order_id, o.domain, p.module, p.server_id, p.package_name, s.hostname, s.username, s.api_token, u.email as user_email
                    FROM invoices i
                    JOIN orders o ON i.order_id = o.id
                    JOIN products p ON o.product_id = p.id
                    JOIN users u ON i.user_id = u.id
                    LEFT JOIN servers s ON p.server_id = s.id
                    WHERE i.id = ?
                ");
                $order_info_stmt->bind_param("i", $invoice_id);
                $order_info_stmt->execute();
                $order_info_result = $order_info_stmt->get_result();
                $order_info = $order_info_result->fetch_assoc();
                $order_info_stmt->close();

                if ($order_info && !empty($order_info['module'])) {
                    $module_file = __DIR__ . '/../app/modules/' . $order_info['module'] . '.php';
                    if (file_exists($module_file)) {
                        require_once $module_file;
                        $module_class = $order_info['module'];

                        $server_host = $order_info['hostname'];
                        $server_user = $order_info['username'];
                        $server_token = $order_info['api_token'];

                        $module = new $module_class($server_host, $server_user, $server_token);

                        // Generate a secure password for the new account
                        $secure_password = generate_strong_password();

                        $params = [
                            'domain' => $order_info['domain'],
                            'username' => substr(preg_replace('/[^a-z0-9]/', '', strtolower($order_info['domain'])), 0, 8),
                            'password' => $secure_password,
                            'plan' => $order_info['package_name'],
                            'contactemail' => $order_info['user_email'],
                        ];

                        $provisioning_result = $module->createAccount($params);

                        if ($provisioning_result['success']) {
                            // Update order status and store the cPanel username
                            $order_status_stmt = $db->prepare("UPDATE orders SET status = 'Active', cpanel_username = ? WHERE id = ?");
                            $order_status_stmt->bind_param("si", $params['username'], $order_info['order_id']);
                            $order_status_stmt->execute();
                            $order_status_stmt->close();

                            // If product is flagged, install WordPress
                            if ($order_info['install_wordpress']) {
                                $wp_params = [
                                    'cpanel_user' => $params['username'],
                                    'domain' => $order_info['domain'],
                                    'admin_email' => $order_info['user_email'],
                                    'admin_pass' => generate_strong_password(20), // Generate another secure password for WP admin
                                ];
                                $wp_install_result = $module->installWordPress($wp_params);
                                if (!$wp_install_result['success']) {
                                    // Log the WordPress installation error
                                    error_log("WordPress Installation Error for Order ID {$order_info['order_id']}: " . $wp_install_result['message']);
                                }
                            }
                        } else {
                            // Log the provisioning error
                            error_log("Provisioning Error for Order ID {$order_info['order_id']}: " . $provisioning_result['message']);
                        }
                    }
                }
                // --- End Provisioning Logic ---
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
