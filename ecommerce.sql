-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 20 يونيو 2026 الساعة 01:40
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `ecommerce_db`
CREATE DATABASE IF NOT EXISTS `ecommerce_db`;
USE `ecommerce_db`;

-- 

-- Table: cart_items
CREATE TABLE IF NOT EXISTS `cart_items` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 

-- Table: categories
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for categories
INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
(1, 'رجالي', 'ملابس وإكسسوارات رجالية'),
(2, 'نسائي', 'ملابس وإكسسوارات نسائية'),
(3, 'أطفال', 'ملابس وإكسسوارات للأطفال'),
(4, 'إكسسوارات', 'إكسسوارات متنوعة'),
(11, 'Shoes', 'احذية بانواعها');

-- 

-- Table: contacts
CREATE TABLE IF NOT EXISTS `contacts` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for contacts
INSERT INTO `contacts` (`message_id`, `name`, `email`, `subject`, `message`, `is_read`, `submitted_at`) VALUES
(1, 'اختبار', 'test@test.com', 'استفسار', 'هذه رسالة تجريبية', 1, '2026-06-19 23:29:17'),
(2, 'علياء الدحدوح', 'alyaadahdouh52005@gmail.com', 'اقتراح', 'نص الرسالة', 1, '2026-06-19 23:32:11');

-- 

-- Table: orders
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for orders
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `status`, `shipping_address`) VALUES
(1, 1, '2026-06-19 23:35:14', 69.99, 'pending', 'b');

-- 

-- Table: order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for order_items
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1, 93, 1, 69.99);

-- 

-- Table: products
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for products (with corrected image paths)
INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock_quantity`, `image_url`, `category_id`, `created_at`) VALUES
(92, 'فستان أزرق طويل', 'فستان أزرق أنيق بطول طويل مناسب للمناسبات', 89.99, 15, 'assets/uploads/dress_blue1.jpg', 2, '2026-06-19 23:05:37'),
(93, 'فستان أزرق قصير', 'فستان أزرق قصير عصري للصيف', 69.99, 19, 'assets/uploads/dress_blue2.jpg', 2, '2026-06-19 23:05:53'),
(94, 'فستان عنابي طويل', 'فستان عنابي فاخر بطول طويل', 99.99, 12, 'assets/uploads/dress_maroon1.jpg', 2, '2026-06-19 23:06:14'),
(95, 'فستان عنابي قصير', 'فستان عنابي قصير أنيق', 79.99, 18, 'assets/uploads/dress_maroon2.jpg', 2, '2026-06-19 23:06:31'),
(96, 'بيجاما صيفي وردي', 'بيجاما صيفي مريح بلون وردي ناعم', 39.99, 25, 'assets/uploads/pajama_pink.jpg', 2, '2026-06-19 23:06:46'),
(97, 'بيجاما صيفي نعناعي', 'بيجاما صيفي مريح بلون نعناعي منعش', 39.99, 25, 'assets/uploads/pajama_mint.jpg', 2, '2026-06-19 23:07:00'),
(98, 'طقم أطفال قطني', 'طقم أطفال قطني 100% مريح وناعم', 49.99, 30, 'assets/uploads/kids_set.jpg', 3, '2026-06-19 23:07:39'),
(99, 'بلوزة أطفال توم وجيري', 'بلوزة أطفال برسومات توم وجيري المضحكة', 29.99, 35, 'assets/uploads/kids_tom_jerry.jpg', 3, '2026-06-19 23:07:53'),
(100, 'بلوزة قطن شبابي أسود', 'بلوزة قطن شبابي كلاسيكية بلون أسود', 34.99, 40, 'assets/uploads/shirt_black.jpg', 1, '2026-06-19 23:08:05'),
(101, 'بلوزة قطن شبابي أبيض', 'بلوزة قطن شبابي أنيقة بلون أبيض', 34.99, 40, 'assets/uploads/shirt_white.jpg', 1, '2026-06-19 23:08:21'),
(102, 'بلوزة قطن شبابي رمادي', 'بلوزة قطن شبابي عصرية بلون رمادي', 34.99, 40, 'assets/uploads/shirt_gray.jpg', 1, '2026-06-19 23:08:35'),
(103, 'بلوزة قطن بناتي كاجوال', 'بلوزة قطن بناتي مريحة للاستخدام اليومي', 29.99, 30, 'assets/uploads/blouse_girls.jpg', 2, '2026-06-19 23:08:51'),
(108, 'طقم إكسسوارات ذهبي', 'طقم إكسسوارات أنيق بلون ذهبي', 49.99, 15, 'assets/uploads/accessories_gold.jpg', 4, '2026-06-19 23:10:52');

-- 

-- Table: users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for users
INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', '', 'admin', '2026-06-19 18:51:27'),
(3, 'علياء الدحدوح', 'alyaadahdouh52005@gmail.com', '$2y$10$QeR57m9GwhtytcrCSOYWd.pNmzB51MLXcEWTbUpSJAQkYogok3eaO', '123456', '', 'customer', '2026-06-19 23:33:03');

-- 

-- Foreign Keys
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;