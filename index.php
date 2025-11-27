<?php
// Root index.php

// This file acts as the primary entry point for the application.
// Its purpose is to check if the application has been installed.

// If the configuration file exists, it means the installation is complete,
// and we can safely forward the user to the public landing page.
if (file_exists(__DIR__ . '/config/config.php')) {
    header('Location: public/');
    exit;
} else {
    // If the configuration file is missing, it's a fresh instance,
    // so we must direct the user to the web-based installer.
    header('Location: installer/');
    exit;
}
