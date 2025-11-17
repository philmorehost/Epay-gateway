-- Adminer 4.8.1 MySQL 5.7.33 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tfa_secret` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reseller_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `reseller_id` (`reseller_id`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`reseller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `email_templates` (`id`, `name`, `subject`, `body`) VALUES
(1,	'Welcome Email',	'Welcome to Our Service, {name}!',	'<h3>Hi {name},</h3><p>Thank you for registering with us. We are excited to have you on board.</p><p>You can log in to your account at any time to manage your services.</p><p>Thank you,<br>The Team</p>');


DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Unpaid',
  `due_date` date DEFAULT NULL,
  `is_credit_invoice` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price_monthly` decimal(10,2) DEFAULT NULL,
  `price_annually` decimal(10,2) DEFAULT NULL,
  `wholesale_discount_percent` decimal(5,2) DEFAULT '0.00',
  `welcome_email_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `reseller_settings`;
CREATE TABLE `reseller_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reseller_id` int(11) NOT NULL,
  `retail_markup_percent` decimal(5,2) DEFAULT '0.00',
  `custom_domain` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reseller_id` (`reseller_id`),
  CONSTRAINT `reseller_settings_ibfk_1` FOREIGN KEY (`reseller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `credit_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_reseller` tinyint(1) NOT NULL DEFAULT '0',
  `tfa_secret` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- 2024-05-20 14:00:00
