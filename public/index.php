<?php
// Include the bootstrap file
require_once __DIR__ . '/../app/core/bootstrap.php';

// --- Simple Router ---

// Check if it's a reseller storefront and set the default page accordingly
if (defined('IS_RESELLER_STOREFRONT') && IS_RESELLER_STOREFRONT === true) {
    // For resellers, the default page is their product list
    $page = $_GET['page'] ?? 'reseller_storefront';
} else {
    // For the main site, the default is the home page
    $page = $_GET['page'] ?? 'home';
}


// Whitelist of allowed pages
$allowed_pages = [
    'home', 'login', 'dashboard', 'register', 'logout',
    'products', 'order', 'invoices', 'view_invoice', 'add_funds',
    'reseller_apply', 'reseller_storefront',
    // Tier 2 Customer Auth
    'reseller_customer_login', 'reseller_customer_register',
    'customer_dashboard', 'customer_logout',
    'wordpress_manager'
];

if (in_array($page, $allowed_pages)) {
    $page_file = __DIR__ . '/../app/pages/' . $page . '.php';
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        // Handle 404 Not Found
        http_response_code(404);
        echo 'Page not found.';
    }
} else {
    http_response_code(404);
    echo 'Page not found.';
}
