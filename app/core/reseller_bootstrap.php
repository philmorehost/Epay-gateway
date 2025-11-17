<?php
// app/core/reseller_bootstrap.php

/**
 * This script is loaded when a valid reseller domain is detected.
 * It fetches the reseller's settings, calculates retail prices based on their markup,
 * and makes this information available globally for the storefront.
 */

// This script should only be included, not accessed directly.
if (!defined('IS_RESELLER_STOREFRONT') || !IS_RESELLER_STOREFRONT) {
    die('Direct access not allowed.');
}

// The database connection ($db) is expected to be available from the main bootstrap.php
if (!isset($db)) {
    // This should not happen in the normal flow.
    die('Database connection not found.');
}

// --- 1. Fetch Reseller Settings ---

// RESELLER_USER_ID is defined in host_check.php
$stmt = $db->prepare("SELECT * FROM reseller_settings WHERE user_id = ?");
$stmt->bind_param('i', RESELLER_USER_ID);
$stmt->execute();
$reseller_settings_result = $stmt->get_result();

if ($reseller_settings_result->num_rows !== 1) {
    // Should not happen if host_check is working correctly.
    // This is a failsafe.
    http_response_code(500);
    echo "Error: Could not load reseller configuration.";
    exit;
}

$reseller_settings = $reseller_settings_result->fetch_assoc();
$stmt->close();

// --- 2. Fetch All Products and Calculate Retail Prices ---

$products_result = $db->query("SELECT * FROM products WHERE status = 'active'");
$products_with_retail_price = [];

if ($products_result) {
    while ($product = $products_result->fetch_assoc()) {
        // First, calculate the wholesale price for the reseller
        $wholesale_discount = $product['wholesale_discount_percent'];
        $base_price = $product['price'];
        $wholesale_price = $base_price - ($base_price * ($wholesale_discount / 100));

        // Now, calculate the final retail price using the reseller's markup
        $retail_markup = $reseller_settings['retail_markup_percent'];
        $retail_price = $wholesale_price * (1 + ($retail_markup / 100));

        // Add the calculated prices to the product array
        $product['wholesale_price'] = $wholesale_price;
        $product['retail_price'] = $retail_price;

        $products_with_retail_price[] = $product;
    }
}

// --- 3. Make Data Globally Available ---

// Store all reseller-specific data in a global array for easy access in the views.
$GLOBALS['reseller_data'] = [
    'settings' => $reseller_settings,
    'products' => $products_with_retail_price
];

// Example of how to access this later in a page:
// global $reseller_data;
// echo $reseller_data['settings']['company_name'];
// foreach ($reseller_data['products'] as $product) { ... }

?>
