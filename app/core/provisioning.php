<?php
// app/core/provisioning.php

/**
 * Triggers the provisioning process for a given order.
 *
 * @param mysqli $db The database connection object.
 * @param int $order_id The ID of the order to be provisioned.
 * @return void
 */
function trigger_provisioning($db, $order_id) {
    // 1. Fetch all necessary data in one go: order, user, product, server
    $query = "SELECT
                o.id as order_id, o.domain,
                u.id as user_id, u.email, u.first_name, u.last_name,
                p.id as product_id, p.name as product_name, p.module, p.package_name,
                s.id as server_id, s.hostname, s.api_key_1
              FROM orders o
              JOIN users u ON o.user_id = u.id
              JOIN invoices i ON o.id = i.order_id
              JOIN invoice_items ii ON i.id = ii.invoice_id
              JOIN products p ON p.name = SUBSTRING_INDEX(ii.description, ' - ', 1)
              LEFT JOIN servers s ON p.server_id = s.id
              WHERE o.id = ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    // 2. Check if provisioning is needed and possible
    if ($data && !empty($data['module']) && !empty($data['server_id'])) {

        $module_file = __DIR__ . '/../modules/' . $data['module'] . '.php';
        if (file_exists($module_file)) {
            require_once $module_file;

            // Check if the class exists and implements the interface
            if (class_exists($data['module']) && in_array('ApiModule', class_implements($data['module']))) {

                // 3. Instantiate the module with server details
                $server_details = [
                    'hostname' => $data['hostname'],
                    'api_key_1' => $data['api_key_1'],
                ];
                $module_instance = new $data['module']($server_details);

                // 4. Prepare parameters for the createAccount function

                // Generate a cPanel username (example logic)
                $cpanel_username = strtolower(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['domain']), 0, 8));

                $provisioning_params = [
                    'domain' => $data['domain'],
                    'username' => $cpanel_username,
                    'package' => $data['package_name'],
                    'client_details' => [
                        'email' => $data['email'],
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                    ]
                ];

                // 5. Call the createAccount method
                $result = $module_instance->createAccount($provisioning_params);

                // 6. Log the result and update the order
                if ($result['success']) {
                    // Update order status to 'Active' and store the cPanel username
                    $update_stmt = $db->prepare("UPDATE orders SET status = 'Active', cpanel_username = ? WHERE id = ?");
                    $update_stmt->bind_param('si', $cpanel_username, $order_id);
                    $update_stmt->execute();
                    // Log success
                } else {
                    // Log the failure message: $result['message']
                }
            }
        }
    }
}
