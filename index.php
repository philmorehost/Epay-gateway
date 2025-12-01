<?php
// Root index.php

// This file acts as the primary entry point for the application.
// Its purpose is to check if the application has been installed correctly.

$config_path = __DIR__ . '/config/config.php';

// A flag to determine if the installation is complete and valid.
$is_installed = false;

// 1. Check if the config file exists and is not empty.
if (file_exists($config_path) && filesize($config_path) > 0) {
    // 2. Include the config file to check its contents.
    // Use @ to suppress errors if the file is corrupt.
    @include_once($config_path);

    // 3. Check if a key constant from the config is defined.
    // This confirms the file was written correctly.
    if (defined('DB_HOST')) {
        $is_installed = true;
    }
}

// If the installation is complete, forward the user to the public landing page.
if ($is_installed) {
    header('Location: public/');
    exit;
} else {
    // If the installation is incomplete or the config file is missing/corrupt,
    // direct the user to the web-based installer.
    header('Location: installer/');
    exit;
}
