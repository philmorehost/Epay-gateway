<?php
// app/core/provisioning.php

function trigger_provisioning($db, $order_id) {
    $query = "SELECT
                o.id as order_id, o.domain, o.cpanel_username,
                u.id as user_id, u.email, u.first_name, u.last_name,
                p.id as product_id, p.name as product_name, p.module, p.package_name,
                s.id as server_id, s.hostname, s.api_key_1
              FROM orders o
              JOIN users u ON o.user_id = u.id
              JOIN products p ON o.product_id = p.id
              LEFT JOIN servers s ON p.server_id = s.id
              WHERE o.id = ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data && !empty($data['module'])) {
        $module_file = __DIR__ . '/../modules/' . $data['module'] . '.php';
        if (file_exists($module_file)) {
            require_once $module_file;

            if (class_exists($data['module']) && in_array('ApiModule', class_implements($data['module']))) {

                $module_instance = null;
                $provisioning_params = [];
                $result = ['success' => false, 'message' => 'Module not initialized.'];

                // --- Module-specific logic ---
                if ($data['module'] === 'Cpanel') {
                    if(!empty($data['server_id'])) {
                        $server_details = ['hostname' => $data['hostname'], 'api_key_1' => $data['api_key_1']];
                        $module_instance = new Cpanel($server_details);
                        $cpanel_username = $data['cpanel_username'] ?: strtolower(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['domain']), 0, 8));
                        $provisioning_params = [
                            'domain' => $data['domain'], 'username' => $cpanel_username, 'package' => $data['package_name'],
                            'client_details' => ['email' => $data['email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name']]
                        ];
                        $result = $module_instance->createAccount($provisioning_params);
                    }
                } elseif ($data['module'] === 'ConnectReseller') {
                    $api_key = $db->query("SELECT value FROM settings WHERE setting = 'connectreseller_api_key'")->fetch_assoc()['value'] ?? '';
                    if(!empty($api_key)) {
                        $module_instance = new ConnectReseller($api_key);
                        $provisioning_params = ['domain' => $data['domain']];
                        $result = $module_instance->createAccount($provisioning_params);
                    }
                }

                // Log result and update order status
                if ($result['success']) {
                    $update_stmt = $db->prepare("UPDATE orders SET status = 'Active' WHERE id = ?");
                    $update_stmt->bind_param('i', $order_id);
                    $update_stmt->execute();
                    // Log success: error_log("Provisioning success for order #{$order_id}: " . $result['message']);
                } else {
                    // Log failure: error_log("Provisioning failed for order #{$order_id}: " . $result['message']);
                }
            }
        }
    }
}
