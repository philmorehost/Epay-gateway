-- Corrected schema.sql

-- Tables with no foreign key dependencies
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `coupons` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(50) NOT NULL,
    `type` enum('percentage','fixed') NOT NULL,
    `value` decimal(10,2) NOT NULL,
    `max_uses` int(11) NOT NULL DEFAULT '0',
    `uses` int(11) NOT NULL DEFAULT '0',
    `expires_at` date DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Core user and product tables
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `credit_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
    `is_reseller` tinyint(1) NOT NULL DEFAULT '0',
    `2fa_secret` varchar(255) DEFAULT NULL,
    `2fa_enabled` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text,
    `product_type` enum('hosting','domain') NOT NULL DEFAULT 'hosting',
    `tld` varchar(50) DEFAULT NULL,
    `price_monthly` decimal(10,2) NOT NULL DEFAULT '0.00',
    `price_annually` decimal(10,2) NOT NULL DEFAULT '0.00',
    `wholesale_discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
    `category` varchar(100) NOT NULL,
    `server_type` varchar(50) DEFAULT NULL,
    `package_name` varchar(100) DEFAULT NULL,
    `server_identifier` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Staff and permissions
CREATE TABLE `staff_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`role_id`) REFERENCES `staff_roles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `staff_permissions` (
  `role_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  PRIMARY KEY (`role_id`, `permission`),
  FOREIGN KEY (`role_id`) REFERENCES `staff_roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tables that depend on users and products
CREATE TABLE `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `domain_name` varchar(255) DEFAULT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'Pending',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `invoices` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `order_id` int(11) DEFAULT NULL,
    `amount` decimal(10,2) NOT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'Unpaid',
    `due_date` date NOT NULL,
    `is_credit_invoice` tinyint(1) NOT NULL DEFAULT '0',
    `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Provisioning tables that depend on orders
CREATE TABLE `hosting_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `domains` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `domain_name` varchar(255) NOT NULL,
    `registrar` varchar(100) NOT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'Active',
    `expires_at` date DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `domain_name` (`domain_name`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dedicated_servers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `server_id` varchar(100) NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'Provisioning',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_id` (`order_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reseller and Tier 2 customer tables
CREATE TABLE `reseller_settings` (
    `user_id` int(11) NOT NULL,
    `company_name` varchar(255) DEFAULT NULL,
    `logo_url` varchar(255) DEFAULT NULL,
    `support_email` varchar(255) DEFAULT NULL,
    `retail_markup_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
    `custom_domain` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `custom_domain` (`custom_domain`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `reseller_user_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    FOREIGN KEY (`reseller_user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Affiliate tables
CREATE TABLE `affiliates` (
    `user_id` int(11) NOT NULL,
    `referral_code` varchar(50) NOT NULL,
    `commission_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `referral_code` (`referral_code`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `affiliate_clicks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `affiliate_user_id` int(11) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`affiliate_user_id`) REFERENCES `affiliates`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `affiliate_referrals` (
    `referred_user_id` int(11) NOT NULL,
    `affiliate_user_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`referred_user_id`),
    FOREIGN KEY (`referred_user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`affiliate_user_id`) REFERENCES `affiliates`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `affiliate_payouts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `affiliate_user_id` int(11) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'Pending',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`affiliate_user_id`) REFERENCES `affiliates`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Insert default data
INSERT INTO `settings` (`setting`, `value`) VALUES
('ip_blacklist', ''),
('ip_whitelist', ''),
('max_login_attempts', '5'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_encryption', 'tls'),
('whm_host', ''),
('whm_user', ''),
('whm_api_token', ''),
('tax_rate', '0.00'),
('connectreseller_api_key', ''),
('connectreseller_reseller_id', ''),
('nocix_api_key', ''),
('paystack_secret_key', ''),
('base_currency', 'NGN'),
('secondary_currency', 'USD'),
('usd_conversion_rate', '1.00'),
('affiliate_commission_percentage', '10.00'),
('affiliate_min_payout', '50.00');

INSERT INTO `email_templates` (`name`, `subject`, `body`) VALUES
('Welcome Email', 'Welcome to Our Service!', '<h1>Welcome, {client_name}!</h1><p>Thank you for registering. We are excited to have you on board.</p>'),
('Invoice Reminder', 'Payment Reminder for Invoice #{invoice_id}', '<h1>Payment Reminder</h1><p>Dear {client_name},</p><p>This is a reminder that Invoice #{invoice_id} for the amount of ${invoice_amount} is due on {invoice_due_date}.</p><p>Please log in to your account to make a payment.</p>');

INSERT INTO `staff_roles` (`id`, `name`) VALUES (1, 'Super Admin');
-- Grant all permissions to Super Admin by default
INSERT INTO `staff_permissions` (`role_id`, `permission`) VALUES
(1, 'manage_settings'),
(1, 'manage_products'),
(1, 'manage_staff'),
(1, 'view_reports'),
(1, 'manage_servers'),
(1, 'manage_affiliates');
