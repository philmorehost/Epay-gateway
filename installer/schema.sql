-- Hostbill Initial Database Schema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 0,
  `enable_2fa` tinyint(1) NOT NULL DEFAULT 0,
  `secret_2fa` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `credit_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_reseller` tinyint(1) NOT NULL DEFAULT 0,
  `reseller_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: Not a reseller, 1: Pending, 2: Approved',
  `enable_2fa` tinyint(1) NOT NULL DEFAULT 0,
  `secret_2fa` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Inactive','Closed') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers` (Tier 2)
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reseller_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `reseller_id` (`reseller_id`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`reseller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('Domains','Shared Hosting','Dedicated Servers','VPS Hosting','Other') NOT NULL,
  `price_monthly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_quarterly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_semiannually` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_annually` decimal(10,2) NOT NULL DEFAULT 0.00,
  `setup_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `wholesale_discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `module` varchar(100) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `welcome_email_template_id` int(11) DEFAULT NULL,
  `stock_control` tinyint(1) NOT NULL DEFAULT 0,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_number` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('Pending','Active','Cancelled','Fraud') NOT NULL DEFAULT 'Pending',
  `domain` varchar(255) DEFAULT NULL,
  `cpanel_username` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date NOT NULL,
  `paid_date` timestamp NULL DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `credit_applied` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `status` enum('Paid','Unpaid','Cancelled','Awaiting Payment') NOT NULL DEFAULT 'Unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `is_credit_invoice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 for Add Funds invoices',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_id` int(11) DEFAULT NULL,
    `user_id` int(11) NOT NULL,
    `gateway` varchar(50) NOT NULL,
    `transaction_id` varchar(255) DEFAULT NULL,
    `amount_in` decimal(10,2) NOT NULL DEFAULT 0.00,
    `amount_out` decimal(10,2) NOT NULL DEFAULT 0.00,
    `description` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `invoice_id` (`invoice_id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default settings
INSERT INTO `settings` (`setting`, `value`) VALUES
('company_name', 'Hostbill'),
('system_url', ''),
('currency_code_primary', 'NGN'),
('currency_code_secondary', 'USD'),
('usd_conversion_rate', '1500.00'),
('smtp_host', ''),
('smtp_port', ''),
('smtp_username', ''),
('smtp_password', ''),
('smtp_encryption', 'tls'),
('terms_of_service', 'Please define your terms of service.'),
('privacy_policy', 'Please define your privacy policy.'),
('paystack_secret_key', ''),
('paystack_public_key', ''),
('connectreseller_api_key', '');


-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

CREATE TABLE `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `api_key_1` text DEFAULT NULL,
  `api_key_2` text DEFAULT NULL,
  `module` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reseller_settings`
--

CREATE TABLE `reseller_settings` (
  `reseller_id` int(11) NOT NULL,
  `custom_domain` varchar(255) DEFAULT NULL,
  `retail_markup_percent` decimal(5,2) NOT NULL DEFAULT 10.00,
  PRIMARY KEY (`reseller_id`),
  UNIQUE KEY `custom_domain` (`custom_domain`),
  CONSTRAINT `reseller_settings_ibfk_1` FOREIGN KEY (`reseller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cron_jobs`
--

CREATE TABLE `cron_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `schedule` varchar(100) NOT NULL,
  `action` varchar(255) NOT NULL,
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tld_pricing`
--

CREATE TABLE `tld_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tld` varchar(100) NOT NULL,
  `registration_price` decimal(10,2) NOT NULL,
  `renewal_price` decimal(10,2) NOT NULL,
  `transfer_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tld` (`tld`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


COMMIT;
