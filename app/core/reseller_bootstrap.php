<?php
// app/core/reseller_bootstrap.php

function initialize_reseller_environment() {
    global $db;

    $current_host = $_SERVER['HTTP_HOST'];

    // 1. Fetch the reseller's settings based on the custom domain
    $stmt = $db->prepare("SELECT * FROM reseller_settings WHERE custom_domain = ?");
    $stmt->bind_param('s', $current_host);
    $stmt->execute();
    $reseller_settings = $stmt->get_result()->fetch_assoc();

    if (!$reseller_settings) {
        // This should not happen if host_check is working, but as a fallback:
        die("Error: Reseller configuration not found.");
    }

    // 2. Fetch all products
    $products = $db->query("SELECT * FROM products")->fetch_all(MYSQLI_ASSOC);

    // 3. Calculate retail prices
    $retail_products = [];
    $markup_multiplier = 1 + ($reseller_settings['retail_markup_percent'] / 100);

    foreach ($products as $product) {
        // First, apply the wholesale discount
        $wholesale_price_monthly = $product['price_monthly'] * (1 - ($product['wholesale_discount_percent'] / 100));
        $wholesale_price_annually = $product['price_annually'] * (1 - ($product['wholesale_discount_percent'] / 100));

        // Then, apply the reseller's retail markup
        $product['price_monthly'] = $wholesale_price_monthly * $markup_multiplier;
        $product['price_annually'] = $wholesale_price_annually * $markup_multiplier;

        $retail_products[] = $product;
    }

    // 4. Return the reseller's environment data
    return [
        'reseller_settings' => $reseller_settings,
        'products' => $retail_products
    ];
}
