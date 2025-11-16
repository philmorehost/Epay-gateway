<?php
// Include the bootstrap file
require_once __DIR__ . '/../app/core/bootstrap.php';

// Simple router
$page = $_GET['page'] ?? 'home';

// Whitelist of allowed pages
$allowed_pages = ['home', 'login', 'dashboard', 'register', 'logout'];

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
