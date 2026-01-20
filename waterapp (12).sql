-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2026 at 05:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `waterapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  `address_line` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `label`, `address_line`, `city`, `pincode`, `lat`, `lng`, `is_default`, `created_at`) VALUES
(11, 6, 'Home', '22C8+QRF, Saveetha Rd, Numbal, Chennai, Kuthambakkam, Tamil Nadu 602105, India', 'Saveetha Rd', '602105', 13.0220343, 80.0168372, 0, '2025-12-26 09:22:11'),
(12, 6, 'Home', '5, masjid, Madhena garden, near Meppur, Chennai, Tamil Nadu 600123, India', 'masjid', '600123', 13.0266364, 80.0795088, 0, '2025-12-28 07:53:14'),
(13, 6, 'Home', '23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India', 'Chembarambakkam', '600123', 13.0349883, 80.0587406, 0, '2025-12-28 08:16:43'),
(14, 6, 'scad', 'Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India', 'Saveetha Nagar', '602105', 13.0281475, 80.0156199, 0, '2025-12-30 04:18:29'),
(15, 6, 'Home', 'Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India', 'Saveetha Nagar', '602105', 13.0279724, 80.0160293, 0, '2026-01-06 04:57:19'),
(16, 6, 'Home', 'Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India', 'Saveetha Nagar', '602105', 13.0282048, 80.0152814, 0, '2026-01-07 02:33:20');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT 1,
  `price_snapshot` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `variant_id`, `qty`, `price_snapshot`, `created_at`) VALUES
(5, 7, 4, 6, 3, 35.00, '2025-12-25 14:15:00'),
(6, 7, 6, NULL, 2, 65.00, '2025-12-25 14:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','fixed') DEFAULT 'fixed',
  `value` decimal(10,2) NOT NULL,
  `min_order` decimal(10,2) DEFAULT 0.00,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order`, `valid_from`, `valid_to`, `usage_limit`, `used_count`, `created_at`) VALUES
(1, 'WATER10', 'percent', 10.00, 100.00, '2024-12-01', '2025-01-31', 100, 15, '2025-12-25 14:15:00'),
(2, 'FLAT20', 'fixed', 20.00, 150.00, '2024-12-01', '2025-01-15', 50, 8, '2025-12-25 14:15:00'),
(3, 'NEWYEAR25', 'percent', 25.00, 200.00, '2025-01-01', '2025-01-07', 200, 0, '2025-12-25 14:15:00'),
(4, 'FIRSTORDER', 'fixed', 30.00, 50.00, '2024-01-01', '2025-12-31', 1000, 245, '2025-12-25 14:15:00'),
(5, 'AQUA15', 'percent', 15.00, 75.00, '2024-12-15', '2025-01-31', 80, 12, '2025-12-25 14:15:00'),
(6, 'BULK50', 'fixed', 50.00, 500.00, '2024-12-01', '2025-03-31', 30, 3, '2025-12-25 14:15:00'),
(7, 'XMAS20', 'percent', 20.00, 100.00, '2024-12-24', '2024-12-26', 500, 89, '2025-12-25 14:15:00'),
(8, 'SUBSCRIBE10', 'percent', 10.00, 0.00, '2024-01-01', '2025-12-31', NULL, 156, '2025-12-25 14:15:00'),
(9, 'REFILL5', 'fixed', 5.00, 30.00, '2024-12-01', '2025-06-30', NULL, 432, '2025-12-25 14:15:00'),
(10, 'VIP30', 'percent', 30.00, 300.00, '2024-12-01', '2025-12-31', 20, 2, '2025-12-25 14:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_no` varchar(50) DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `status` enum('available','on_trip','inactive') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_locations`
--

CREATE TABLE `driver_locations` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `read_flag` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `body`, `meta`, `read_flag`, `created_at`) VALUES
(3, 6, 'Order Confirmed', 'Your order #3 has been confirmed.', '{\"order_id\": 3}', 1, '2025-12-25 14:15:00'),
(4, 6, 'New Offer!', 'Get 10% off on your next order. Use code WATER10.', '{\"coupon\": \"WATER10\"}', 1, '2025-12-25 14:15:00'),
(5, 7, 'Welcome Partner!', 'Start fulfilling orders and earn money!', NULL, 1, '2025-12-25 14:15:00'),
(7, 6, 'Order Pending', 'Your order #4 is awaiting confirmation.', '{\"order_id\": 4}', 1, '2025-12-25 14:15:00'),
(8, 7, 'New Order Available', 'A new order is available for delivery.', '{\"order_id\": 6}', 1, '2025-12-25 14:15:00'),
(10, 6, 'Rate Your Delivery', 'How was your recent delivery experience?', '{\"order_id\": 7}', 1, '2025-12-25 14:15:00'),
(11, 6, 'Order Placed Successfully', 'Your order #30 has been placed successfully.\n\nItems: 1x Bisleri 5L, 1x Bisleri 20L, 2x Mineral Water 20L\nTotal: ₹200\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":30}', 1, '2025-12-27 13:38:13'),
(12, 7, 'New Order Received', 'New order #30 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L, 1x Bisleri 20L, 2x Mineral Water 20L\nTotal: ₹200\nPayment: COD\nDelivery Address: 22C8+QRF, Saveetha Rd, Numbal, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Rd\n\nPlease accept or reject this order.', '{\"order_id\":30}', 1, '2025-12-27 13:38:13'),
(13, 6, 'Order Confirmed', 'Great news! Your order #30 has been confirmed by chanti (Chanti Water Service).\n\nItems: Bisleri 5L\nTotal: ₹200\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":30}', 1, '2025-12-28 04:53:25'),
(14, 6, 'Order Confirmed', 'Great news! Your order #29 has been confirmed by chanti (Chanti Water Service).\n\nItems: Bisleri 5L\nTotal: ₹135\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":29}', 1, '2025-12-28 04:53:26'),
(15, 6, 'Order Placed Successfully', 'Your order #31 has been placed successfully.\n\nItems: 2x Bisleri 20L\nTotal: ₹90\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":31,\"type\":\"order_update\"}', 1, '2025-12-28 05:18:23'),
(16, 5, 'New Order Received', 'New order #31 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x Bisleri 20L\nTotal: ₹90\nPayment: COD\nDelivery Address: 22C8+QRF, Saveetha Rd, Numbal, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Rd\n\nPlease accept or reject this order.', '{\"order_id\":31,\"type\":\"new_order\"}', 0, '2025-12-28 05:18:23'),
(17, 6, 'Order Placed Successfully', 'Your order #32 has been placed successfully.\n\nItems: 2x Mineral Water 20L\nTotal: ₹130\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":32,\"type\":\"order_update\"}', 1, '2025-12-28 05:36:57'),
(18, 7, 'New Order Received', 'New order #32 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x Mineral Water 20L\nTotal: ₹130\nPayment: COD\nDelivery Address: 22C8+QRF, Saveetha Rd, Numbal, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Rd\n\nPlease accept or reject this order.', '{\"order_id\":32,\"type\":\"new_order\"}', 1, '2025-12-28 05:36:57'),
(19, 6, 'Order Out for Delivery', 'Your order #30 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹200\n\nThe delivery partner from chanti (Chanti Water Service) is heading to your location. Please be available to receive your order.', '{\"order_id\":30,\"type\":\"order_update\"}', 1, '2025-12-28 06:09:01'),
(20, 6, 'Order Out for Delivery', 'Your order #29 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹135\n\nThe delivery partner from chanti (Chanti Water Service) is heading to your location. Please be available to receive your order.', '{\"order_id\":29,\"type\":\"order_update\"}', 1, '2025-12-28 06:09:03'),
(21, 6, 'Order Delivered Successfully', 'Your order #30 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹200\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":30,\"type\":\"order_update\"}', 1, '2025-12-28 06:12:34'),
(22, 6, 'Order Delivered Successfully', 'Your order #29 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹135\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":29,\"type\":\"order_update\"}', 1, '2025-12-28 06:12:38'),
(23, 6, 'Order Placed Successfully', 'Your order #33 has been placed successfully.\n\nItems: 1x Kinley 1L Pack\nTotal: ₹120\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":33,\"type\":\"order_update\"}', 1, '2025-12-28 06:40:14'),
(24, 7, 'New Order Received', 'New order #33 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Kinley 1L Pack\nTotal: ₹120\nPayment: COD\nDelivery Address: 22C8+QRF, Saveetha Rd, Numbal, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Rd\n\nPlease accept or reject this order.', '{\"order_id\":33,\"type\":\"new_order\"}', 1, '2025-12-28 06:40:15'),
(25, 6, 'Order Confirmed', 'Great news! Your order #33 has been confirmed by chanti water service.\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":33,\"type\":\"order_update\"}', 1, '2025-12-28 06:40:49'),
(26, 6, 'Order Out for Delivery', 'Your order #33 is on its way!\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":33,\"type\":\"order_update\"}', 1, '2025-12-28 06:40:50'),
(27, 6, 'Order Delivered Successfully', 'Your order #33 has been delivered successfully!\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":33,\"type\":\"order_update\"}', 1, '2025-12-28 06:40:51'),
(28, 6, 'Order Placed Successfully', 'Your order #34 has been placed successfully.\n\nItems: 1x Mineral Water 20L, 1x Bisleri 5L\nTotal: ₹90\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":34,\"type\":\"order_update\"}', 1, '2025-12-28 07:57:22'),
(29, 7, 'New Order Received', 'New order #34 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L, 1x Bisleri 5L\nTotal: ₹90\nPayment: COD\nDelivery Address: 5, masjid, Madhena garden, near Meppur, Chennai, Tamil Nadu 600123, India, masjid\n\nPlease accept or reject this order.', '{\"order_id\":34,\"type\":\"new_order\"}', 1, '2025-12-28 07:57:42'),
(30, 6, 'Order Confirmed', 'Great news! Your order #34 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹90\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":34,\"type\":\"order_update\"}', 1, '2025-12-28 07:58:29'),
(31, 6, 'Order Out for Delivery', 'Your order #34 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹90\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":34,\"type\":\"order_update\"}', 1, '2025-12-28 07:59:42'),
(32, 6, 'Order Placed Successfully', 'Your order #35 has been placed successfully.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":35,\"type\":\"order_update\"}', 1, '2025-12-28 08:13:05'),
(33, 5, 'New Order Received', 'New order #35 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\nDelivery Address: 5, masjid, Madhena garden, near Meppur, Chennai, Tamil Nadu 600123, India, masjid\n\nPlease accept or reject this order.', '{\"order_id\":35,\"type\":\"new_order\"}', 0, '2025-12-28 08:13:06'),
(34, 6, 'Order Placed Successfully', 'Your order #36 has been placed successfully.\n\nItems: 1x Kinley 1L Pack\nTotal: ₹120\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":36,\"type\":\"order_update\"}', 1, '2025-12-28 08:23:28'),
(35, 7, 'New Order Received', 'New order #36 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Kinley 1L Pack\nTotal: ₹120\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":36,\"type\":\"new_order\"}', 1, '2025-12-28 08:23:29'),
(36, 6, 'Order Confirmed', 'Great news! Your order #36 has been confirmed by chanti water service.\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":36,\"type\":\"order_update\"}', 1, '2025-12-28 08:24:12'),
(37, 6, 'Order Out for Delivery', 'Your order #36 is on its way!\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":36,\"type\":\"order_update\"}', 1, '2025-12-28 08:24:38'),
(38, 6, 'Order Placed Successfully', 'Your order #37 has been placed successfully.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":37,\"type\":\"order_update\"}', 1, '2025-12-28 08:24:48'),
(39, 5, 'New Order Received', 'New order #37 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":37,\"type\":\"new_order\"}', 0, '2025-12-28 08:25:13'),
(40, 6, 'Order Delivered Successfully', 'Your order #36 has been delivered successfully!\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":36,\"type\":\"order_update\"}', 1, '2025-12-28 08:26:04'),
(41, 6, 'Order Delivered Successfully', 'Your order #34 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹90\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":34,\"type\":\"order_update\"}', 1, '2025-12-28 08:26:12'),
(42, 6, 'Order Placed Successfully', 'Your order #38 has been placed successfully.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":38,\"type\":\"order_update\"}', 1, '2025-12-28 08:40:14'),
(43, 6, 'Order Placed Successfully', 'Your order #39 has been placed successfully.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":39,\"type\":\"order_update\"}', 1, '2025-12-28 08:40:44'),
(44, 5, 'New Order Received', 'New order #38 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":38,\"type\":\"new_order\"}', 0, '2025-12-28 08:40:49'),
(45, 5, 'New Order Received', 'New order #39 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":39,\"type\":\"new_order\"}', 0, '2025-12-28 08:41:00'),
(46, 6, 'Order Placed Successfully', 'Your order #40 has been placed successfully.\n\nItems: 1x Himalayan Water 20L\nTotal: ₹75\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":40,\"type\":\"order_update\"}', 1, '2025-12-28 08:44:44'),
(47, 7, 'New Order Received', 'New order #40 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Himalayan Water 20L\nTotal: ₹75\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":40,\"type\":\"new_order\"}', 1, '2025-12-28 08:44:47'),
(48, 6, 'Order Confirmed', 'Great news! Your order #40 has been confirmed by chanti water service.\n\nItems: Himalayan Water 20L\nTotal: ₹75\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":40,\"type\":\"order_update\"}', 1, '2025-12-28 08:46:41'),
(49, 6, 'Order Out for Delivery', 'Your order #40 is on its way!\n\nItems: Himalayan Water 20L\nTotal: ₹75\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":40,\"type\":\"order_update\"}', 1, '2025-12-28 08:46:55'),
(50, 6, 'Order Delivered Successfully', 'Your order #40 has been delivered successfully!\n\nItems: Himalayan Water 20L\nTotal: ₹75\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":40,\"type\":\"order_update\"}', 1, '2025-12-28 08:47:25'),
(51, 6, 'Order Placed Successfully', 'Your order #41 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":41,\"type\":\"order_update\"}', 1, '2025-12-29 02:57:38'),
(52, 7, 'New Order Received', 'New order #41 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":41,\"type\":\"new_order\"}', 1, '2025-12-29 02:57:38'),
(53, 6, 'Order Confirmed', 'Great news! Your order #41 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":41,\"type\":\"order_update\"}', 1, '2025-12-29 02:58:10'),
(54, 6, 'Order Out for Delivery', 'Your order #41 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":41,\"type\":\"order_update\"}', 1, '2025-12-29 02:58:25'),
(55, 6, 'Order Delivered Successfully', 'Your order #41 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":41,\"type\":\"order_update\"}', 1, '2025-12-29 02:58:28'),
(56, 6, 'Order Placed Successfully', 'Your order #42 has been placed successfully.\n\nItems: 2x Bisleri 5L\nTotal: ₹50\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":42,\"type\":\"order_update\"}', 1, '2025-12-29 03:23:31'),
(57, 7, 'New Order Received', 'New order #42 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x Bisleri 5L\nTotal: ₹50\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":42,\"type\":\"new_order\"}', 1, '2025-12-29 03:23:31'),
(58, 6, 'Order Placed Successfully', 'Your order #43 has been placed successfully.\n\nItems: 1x Kinley 1L Pack\nTotal: ₹120\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":43,\"type\":\"order_update\"}', 1, '2025-12-29 03:23:56'),
(59, 7, 'New Order Received', 'New order #43 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Kinley 1L Pack\nTotal: ₹120\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":43,\"type\":\"new_order\"}', 1, '2025-12-29 03:23:56'),
(60, 6, 'Order Confirmed', 'Great news! Your order #42 has been confirmed by chanti water service.\n\nItems: Bisleri 5L\nTotal: ₹50\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":42,\"type\":\"order_update\"}', 1, '2025-12-29 03:24:43'),
(61, 6, 'Order Out for Delivery', 'Your order #42 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹50\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":42,\"type\":\"order_update\"}', 1, '2025-12-29 03:24:59'),
(62, 6, 'Order Delivered Successfully', 'Your order #42 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹50\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":42,\"type\":\"order_update\"}', 1, '2025-12-29 03:25:03'),
(63, 6, 'Order Placed Successfully', 'Your order #44 has been placed successfully.\n\nItems: 1x RO Water 20L\nTotal: ₹30\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":44,\"type\":\"order_update\"}', 1, '2025-12-29 03:33:06'),
(64, 7, 'New Order Received', 'New order #44 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x RO Water 20L\nTotal: ₹30\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":44,\"type\":\"new_order\"}', 1, '2025-12-29 03:33:06'),
(65, 6, 'Order Confirmed', 'Great news! Your order #44 has been confirmed by chanti water service.\n\nItems: RO Water 20L\nTotal: ₹30\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":44,\"type\":\"order_update\"}', 1, '2025-12-29 03:34:55'),
(66, 6, 'Order Out for Delivery', 'Your order #44 is on its way!\n\nItems: RO Water 20L\nTotal: ₹30\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":44,\"type\":\"order_update\"}', 1, '2025-12-29 03:35:02'),
(67, 6, 'Order Delivered Successfully', 'Your order #44 has been delivered successfully!\n\nItems: RO Water 20L\nTotal: ₹30\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":44,\"type\":\"order_update\"}', 1, '2025-12-29 03:35:03'),
(68, 6, 'Order Confirmed', 'Great news! Your order #43 has been confirmed by chanti water service.\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":43,\"type\":\"order_update\"}', 1, '2025-12-29 03:35:05'),
(69, 6, 'Order Out for Delivery', 'Your order #43 is on its way!\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":43,\"type\":\"order_update\"}', 1, '2025-12-29 03:35:07'),
(70, 6, 'Order Delivered Successfully', 'Your order #43 has been delivered successfully!\n\nItems: Kinley 1L Pack\nTotal: ₹120\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":43,\"type\":\"order_update\"}', 1, '2025-12-29 03:35:08'),
(71, 6, 'Order Placed Successfully', 'Your order #45 has been placed successfully.\n\nItems: 2x Himalayan Water 20L\nTotal: ₹150\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":45,\"type\":\"order_update\"}', 1, '2025-12-29 03:36:34'),
(72, 7, 'New Order Received', 'New order #45 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x Himalayan Water 20L\nTotal: ₹150\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":45,\"type\":\"new_order\"}', 1, '2025-12-29 03:36:34'),
(73, 6, 'Order Rejected', 'Unfortunately, your order #45 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Himalayan Water 20L\nTotal: ₹150\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":45,\"type\":\"order_update\"}', 1, '2025-12-29 03:36:51'),
(74, 6, 'Order Placed Successfully', 'Your order #46 has been placed successfully.\n\nItems: 1x Mineral Water 20L, 1x Kinley 1L Pack\nTotal: ₹185\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":46,\"type\":\"order_update\"}', 1, '2025-12-29 05:00:43'),
(75, 7, 'New Order Received', 'New order #46 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L, 1x Kinley 1L Pack\nTotal: ₹185\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":46,\"type\":\"new_order\"}', 1, '2025-12-29 05:00:44'),
(76, 6, 'Order Rejected', 'Unfortunately, your order #46 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Mineral Water 20L\nTotal: ₹185\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":46,\"type\":\"order_update\"}', 1, '2025-12-29 05:01:08'),
(77, 6, 'Order Placed Successfully', 'Your order #47 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":47,\"type\":\"order_update\"}', 1, '2025-12-29 05:14:39'),
(78, 7, 'New Order Received', 'New order #47 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":47,\"type\":\"new_order\"}', 1, '2025-12-29 05:14:39'),
(79, 6, 'Order Confirmed', 'Great news! Your order #47 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":47,\"type\":\"order_update\"}', 1, '2025-12-29 05:15:57'),
(80, 6, 'Order Out for Delivery', 'Your order #47 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":47,\"type\":\"order_update\"}', 1, '2025-12-29 05:16:09'),
(81, 6, 'Order Delivered Successfully', 'Your order #47 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":47,\"type\":\"order_update\"}', 1, '2025-12-29 05:16:11'),
(82, 6, 'Order Placed Successfully', 'Your order #48 has been placed successfully.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":48,\"type\":\"order_update\"}', 1, '2025-12-30 04:19:04'),
(83, 5, 'New Order Received', 'New order #48 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\nDelivery Address: 23M5+WVX, Chembarambakkam, Tamil Nadu 600123, India, Chembarambakkam\n\nPlease accept or reject this order.', '{\"order_id\":48,\"type\":\"new_order\"}', 0, '2025-12-30 04:19:05'),
(84, 6, 'Order Placed Successfully', 'Your order #49 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":49,\"type\":\"order_update\"}', 1, '2025-12-30 04:20:50'),
(85, 7, 'New Order Received', 'New order #49 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":49,\"type\":\"new_order\"}', 1, '2025-12-30 04:20:53'),
(86, 6, 'Order Confirmed', 'Great news! Your order #49 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":49,\"type\":\"order_update\"}', 1, '2025-12-30 04:21:15'),
(87, 6, 'Order Out for Delivery', 'Your order #49 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":49,\"type\":\"order_update\"}', 1, '2025-12-30 04:21:42'),
(88, 6, 'Order Delivered Successfully', 'Your order #49 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":49,\"type\":\"order_update\"}', 1, '2025-12-30 04:21:48'),
(89, 6, 'Order Placed Successfully', 'Your order #50 has been placed successfully.\n\nItems: 2x Bisleri 5L\nTotal: ₹50\nPayment: RAZORPAY\n\nThe partner will confirm your order shortly.', '{\"order_id\":50,\"type\":\"order_update\"}', 1, '2025-12-30 09:12:41'),
(90, 7, 'New Order Received', 'New order #50 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x Bisleri 5L\nTotal: ₹50\nPayment: RAZORPAY\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":50,\"type\":\"new_order\"}', 1, '2025-12-30 09:12:42'),
(91, 6, 'Order Placed Successfully', 'Your order #51 has been placed successfully.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: RAZORPAY\n\nThe partner will confirm your order shortly.', '{\"order_id\":51,\"type\":\"order_update\"}', 1, '2025-12-30 09:13:56'),
(92, 7, 'New Order Received', 'New order #51 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: RAZORPAY\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":51,\"type\":\"new_order\"}', 1, '2025-12-30 09:13:57'),
(93, 6, 'Order Out for Delivery', 'Your order #51 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":51,\"type\":\"order_update\"}', 1, '2025-12-30 09:17:03'),
(94, 6, 'Order Delivered Successfully', 'Your order #51 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":51,\"type\":\"order_update\"}', 1, '2025-12-30 09:17:09'),
(95, 6, 'Order Out for Delivery', 'Your order #50 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹50\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":50,\"type\":\"order_update\"}', 1, '2025-12-30 09:17:18'),
(96, 6, 'Order Delivered Successfully', 'Your order #50 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹50\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":50,\"type\":\"order_update\"}', 1, '2025-12-30 09:17:19'),
(97, 6, 'Order Placed Successfully', 'Your order #52 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":52,\"type\":\"order_update\"}', 1, '2026-01-02 08:59:21'),
(98, 7, 'New Order Received', 'New order #52 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":52,\"type\":\"new_order\"}', 1, '2026-01-02 08:59:22'),
(99, 6, 'Order Confirmed', 'Great news! Your order #52 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":52,\"type\":\"order_update\"}', 1, '2026-01-02 09:00:14'),
(100, 6, 'Order Out for Delivery', 'Your order #52 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":52,\"type\":\"order_update\"}', 1, '2026-01-02 09:00:40'),
(101, 6, 'Order Delivered Successfully', 'Your order #52 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":52,\"type\":\"order_update\"}', 1, '2026-01-02 09:18:31'),
(102, 6, 'Order Placed Successfully', 'Your order #53 has been placed successfully.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":53,\"type\":\"order_update\"}', 1, '2026-01-03 06:40:02'),
(103, 5, 'New Order Received', 'New order #53 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 20L\nTotal: ₹45\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":53,\"type\":\"new_order\"}', 0, '2026-01-03 06:40:08'),
(104, 6, 'Order Placed Successfully', 'Your order #54 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":54,\"type\":\"order_update\"}', 1, '2026-01-03 06:53:41'),
(105, 7, 'New Order Received', 'New order #54 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":54,\"type\":\"new_order\"}', 1, '2026-01-03 06:53:49'),
(106, 6, 'Order Confirmed', 'Great news! Your order #54 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":54,\"type\":\"order_update\"}', 1, '2026-01-03 06:54:12'),
(107, 6, 'Order Out for Delivery', 'Your order #54 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":54,\"type\":\"order_update\"}', 1, '2026-01-03 06:54:26'),
(108, 6, 'Order Delivered Successfully', 'Your order #54 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":54,\"type\":\"order_update\"}', 1, '2026-01-03 06:54:33'),
(109, 6, 'Order Placed Successfully', 'Your order #55 has been placed successfully.\n\nItems: 1x Bisleri 20L, 1x Mineral Water 20L\nTotal: ₹110\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":55,\"type\":\"order_update\"}', 1, '2026-01-03 06:58:33'),
(110, 5, 'New Order Received', 'New order #55 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 20L, 1x Mineral Water 20L\nTotal: ₹110\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":55,\"type\":\"new_order\"}', 0, '2026-01-03 06:58:34'),
(111, 6, 'Order Placed Successfully', 'Your order #56 has been placed successfully.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":56,\"type\":\"order_update\"}', 1, '2026-01-03 06:59:40'),
(112, 7, 'New Order Received', 'New order #56 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":56,\"type\":\"new_order\"}', 1, '2026-01-03 06:59:42'),
(113, 6, 'Order Confirmed', 'Great news! Your order #56 has been confirmed by chanti water service.\n\nItems: Bisleri 5L\nTotal: ₹25\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":56,\"type\":\"order_update\"}', 1, '2026-01-03 07:00:07'),
(114, 6, 'Order Out for Delivery', 'Your order #56 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":56,\"type\":\"order_update\"}', 1, '2026-01-03 07:00:16'),
(115, 6, 'Order Delivered Successfully', 'Your order #56 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":56,\"type\":\"order_update\"}', 1, '2026-01-03 07:00:22'),
(116, 6, 'Order Placed Successfully', 'Your order #57 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":57,\"type\":\"order_update\"}', 1, '2026-01-03 07:20:42'),
(117, 7, 'New Order Received', 'New order #57 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":57,\"type\":\"new_order\"}', 1, '2026-01-03 07:20:45'),
(118, 6, 'Order Placed Successfully', 'Your order #58 has been placed successfully.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":58,\"type\":\"order_update\"}', 1, '2026-01-03 07:21:07'),
(119, 7, 'New Order Received', 'New order #58 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":58,\"type\":\"new_order\"}', 1, '2026-01-03 07:21:09'),
(120, 6, 'Order Confirmed', 'Great news! Your order #58 has been confirmed by chanti water service.\n\nItems: Bisleri 5L\nTotal: ₹25\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":58,\"type\":\"order_update\"}', 1, '2026-01-03 07:21:55'),
(121, 6, 'Order Out for Delivery', 'Your order #58 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":58,\"type\":\"order_update\"}', 1, '2026-01-03 07:22:25'),
(122, 6, 'Order Delivered Successfully', 'Your order #58 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":58,\"type\":\"order_update\"}', 1, '2026-01-03 07:22:34'),
(123, 6, 'Order Placed Successfully', 'Your order #59 has been placed successfully.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":59,\"type\":\"order_update\"}', 1, '2026-01-05 12:00:09'),
(124, 7, 'New Order Received', 'New order #59 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":59,\"type\":\"new_order\"}', 1, '2026-01-05 12:00:10'),
(125, 6, 'Order Confirmed', 'Great news! Your order #59 has been confirmed by chanti water service.\n\nItems: Bisleri 5L\nTotal: ₹25\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":59,\"type\":\"order_update\"}', 1, '2026-01-05 12:00:48'),
(126, 6, 'Order Out for Delivery', 'Your order #59 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":59,\"type\":\"order_update\"}', 1, '2026-01-05 12:01:00'),
(127, 6, 'Order Delivered Successfully', 'Your order #59 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":59,\"type\":\"order_update\"}', 1, '2026-01-05 12:01:05'),
(128, 6, 'Order Placed Successfully', 'Your order #60 has been placed successfully.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":60,\"type\":\"order_update\"}', 1, '2026-01-05 12:24:38'),
(129, 7, 'New Order Received', 'New order #60 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":60,\"type\":\"new_order\"}', 1, '2026-01-05 12:24:39'),
(130, 6, 'Order Confirmed', 'Great news! Your order #60 has been confirmed by chanti water service.\n\nItems: Bisleri 5L\nTotal: ₹25\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":60,\"type\":\"order_update\"}', 1, '2026-01-05 12:25:24'),
(131, 6, 'Order Out for Delivery', 'Your order #60 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":60,\"type\":\"order_update\"}', 1, '2026-01-05 12:26:00'),
(132, 6, 'Order Delivered Successfully', 'Your order #60 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹25\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":60,\"type\":\"order_update\"}', 1, '2026-01-05 12:27:25'),
(133, 6, 'Order Placed Successfully', 'Your order #61 has been placed successfully.\n\nItems: 2x Mineral Water 20L\nTotal: ₹130\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":61,\"type\":\"order_update\"}', 1, '2026-01-05 13:22:38'),
(134, 7, 'New Order Received', 'New order #61 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x Mineral Water 20L\nTotal: ₹130\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":61,\"type\":\"new_order\"}', 1, '2026-01-05 13:22:38'),
(135, 6, 'Order Confirmed', 'Great news! Your order #61 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹130\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":61,\"type\":\"order_update\"}', 1, '2026-01-05 13:35:22'),
(136, 6, 'Order Out for Delivery', 'Your order #61 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹130\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":61,\"type\":\"order_update\"}', 1, '2026-01-05 13:35:30'),
(137, 6, 'Order Delivered Successfully', 'Your order #61 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹130\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":61,\"type\":\"order_update\"}', 1, '2026-01-05 13:35:32'),
(138, 6, 'Order Placed Successfully', 'Your order #62 has been placed successfully.\n\nItems: 1x Bisleri 5L, 1x Mineral Water 20L\nTotal: ₹90\nPayment: RAZORPAY\n\nThe partner will confirm your order shortly.', '{\"order_id\":62,\"type\":\"order_update\"}', 1, '2026-01-06 05:02:20'),
(139, 7, 'New Order Received', 'New order #62 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L, 1x Mineral Water 20L\nTotal: ₹90\nPayment: RAZORPAY\nDelivery Address: \n\nPlease accept or reject this order.', '{\"order_id\":62,\"type\":\"new_order\"}', 1, '2026-01-06 05:02:21'),
(140, 6, 'Order Out for Delivery', 'Your order #62 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹90\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":62,\"type\":\"order_update\"}', 1, '2026-01-06 05:04:52'),
(141, 6, 'Order Delivered Successfully', 'Your order #62 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹90\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":62,\"type\":\"order_update\"}', 1, '2026-01-06 05:05:24'),
(142, 6, 'Order Placed Successfully', 'Your order #63 has been placed successfully.\n\nItems: 1x Bisleri 5L, 1x Mineral Water 20L\nTotal: ₹90\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":63,\"type\":\"order_update\"}', 1, '2026-01-06 05:06:21'),
(143, 7, 'New Order Received', 'New order #63 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L, 1x Mineral Water 20L\nTotal: ₹90\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":63,\"type\":\"new_order\"}', 1, '2026-01-06 05:06:22'),
(144, 6, 'Order Placed Successfully', 'Your order #64 has been placed successfully.\n\nItems: 1x Bisleri 5L, 2x Mineral Water 20L, 1x Kinley 1L Pack\nTotal: ₹275\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":64,\"type\":\"order_update\"}', 1, '2026-01-06 05:21:50'),
(145, 7, 'New Order Received', 'New order #64 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L, 2x Mineral Water 20L, 1x Kinley 1L Pack\nTotal: ₹275\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":64,\"type\":\"new_order\"}', 1, '2026-01-06 05:21:51'),
(146, 6, 'Order Confirmed', 'Great news! Your order #64 has been confirmed by chanti water service.\n\nItems: Bisleri 5L\nTotal: ₹275\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":64,\"type\":\"order_update\"}', 1, '2026-01-06 05:22:29'),
(147, 6, 'Order Out for Delivery', 'Your order #64 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹275\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":64,\"type\":\"order_update\"}', 1, '2026-01-06 05:25:43'),
(148, 6, 'Order Delivered Successfully', 'Your order #64 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹275\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":64,\"type\":\"order_update\"}', 1, '2026-01-06 05:25:44'),
(149, 6, 'Order Placed Successfully', 'Your order #65 has been placed successfully.\n\nItems: 2x RO Water 20L\nTotal: ₹60\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":65,\"type\":\"order_update\"}', 1, '2026-01-06 05:36:15'),
(150, 7, 'New Order Received', 'New order #65 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x RO Water 20L\nTotal: ₹60\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":65,\"type\":\"new_order\"}', 1, '2026-01-06 05:36:16'),
(151, 6, 'Order Confirmed', 'Great news! Your order #65 has been confirmed by chanti water service.\n\nItems: RO Water 20L\nTotal: ₹60\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":65,\"type\":\"order_update\"}', 1, '2026-01-06 05:36:52'),
(152, 6, 'Order Out for Delivery', 'Your order #65 is on its way!\n\nItems: RO Water 20L\nTotal: ₹60\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":65,\"type\":\"order_update\"}', 1, '2026-01-06 05:37:19'),
(153, 6, 'Order Delivered Successfully', 'Your order #65 has been delivered successfully!\n\nItems: RO Water 20L\nTotal: ₹60\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":65,\"type\":\"order_update\"}', 1, '2026-01-06 05:37:23'),
(154, 6, 'Order Placed Successfully', 'Your order #66 has been placed successfully.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":66,\"type\":\"order_update\"}', 1, '2026-01-06 05:39:09'),
(155, 7, 'New Order Received', 'New order #66 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L\nTotal: ₹25\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":66,\"type\":\"new_order\"}', 1, '2026-01-06 05:39:10'),
(156, 6, 'Order Rejected', 'Unfortunately, your order #66 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Bisleri 5L\nTotal: ₹25\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":66,\"type\":\"order_update\"}', 1, '2026-01-06 05:39:24'),
(157, 6, 'Order Placed Successfully', 'Your order #67 has been placed successfully.\n\nItems: 1x Bisleri 5L, 2x Mineral Water 20L\nTotal: ₹155\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":67,\"type\":\"order_update\"}', 1, '2026-01-06 06:27:12'),
(158, 7, 'New Order Received', 'New order #67 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Bisleri 5L, 2x Mineral Water 20L\nTotal: ₹155\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":67,\"type\":\"new_order\"}', 1, '2026-01-06 06:27:13'),
(159, 6, 'Order Confirmed', 'Great news! Your order #67 has been confirmed by chanti water service.\n\nItems: Bisleri 5L\nTotal: ₹155\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":67,\"type\":\"order_update\"}', 1, '2026-01-06 06:27:39'),
(160, 6, 'Order Out for Delivery', 'Your order #67 is on its way!\n\nItems: Bisleri 5L\nTotal: ₹155\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":67,\"type\":\"order_update\"}', 1, '2026-01-06 06:27:46'),
(161, 6, 'Order Delivered Successfully', 'Your order #67 has been delivered successfully!\n\nItems: Bisleri 5L\nTotal: ₹155\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":67,\"type\":\"order_update\"}', 1, '2026-01-06 06:27:48'),
(162, 6, 'Order Placed Successfully', 'Your order #68 has been placed successfully.\n\nItems: 1x Purified drinking water\nTotal: ₹40\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":68,\"type\":\"order_update\"}', 1, '2026-01-06 06:45:51'),
(163, 7, 'New Order Received', 'New order #68 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Purified drinking water\nTotal: ₹40\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":68,\"type\":\"new_order\"}', 1, '2026-01-06 06:45:52'),
(164, 6, 'Order Placed Successfully', 'Your order #69 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":69,\"type\":\"order_update\"}', 1, '2026-01-06 07:23:42'),
(165, 7, 'New Order Received', 'New order #69 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":69,\"type\":\"new_order\"}', 1, '2026-01-06 07:23:43'),
(166, 6, 'Order Confirmed', 'Great news! Your order #69 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":69,\"type\":\"order_update\"}', 1, '2026-01-06 07:24:08'),
(167, 6, 'Order Out for Delivery', 'Your order #69 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":69,\"type\":\"order_update\"}', 1, '2026-01-06 07:24:17'),
(168, 6, 'Order Delivered Successfully', 'Your order #69 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":69,\"type\":\"order_update\"}', 1, '2026-01-06 07:24:21'),
(169, 6, 'Order Placed Successfully', 'Your order #70 has been placed successfully.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":70,\"type\":\"order_update\"}', 1, '2026-01-06 07:29:22'),
(170, 7, 'New Order Received', 'New order #70 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":70,\"type\":\"new_order\"}', 1, '2026-01-06 07:29:23'),
(171, 6, 'Order Confirmed', 'Great news! Your order #70 has been confirmed by chanti water service.\n\nItems: Mineral water\nTotal: ₹60\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":70,\"type\":\"order_update\"}', 1, '2026-01-06 07:30:32');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `body`, `meta`, `read_flag`, `created_at`) VALUES
(172, 6, 'Order Out for Delivery', 'Your order #70 is on its way!\n\nItems: Mineral water\nTotal: ₹60\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":70,\"type\":\"order_update\"}', 1, '2026-01-06 07:30:39'),
(173, 6, 'Order Delivered Successfully', 'Your order #70 has been delivered successfully!\n\nItems: Mineral water\nTotal: ₹60\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":70,\"type\":\"order_update\"}', 1, '2026-01-06 07:30:41'),
(174, 6, 'Order Placed Successfully', 'Your order #71 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":71,\"type\":\"order_update\"}', 1, '2026-01-06 07:35:23'),
(175, 7, 'New Order Received', 'New order #71 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":71,\"type\":\"new_order\"}', 1, '2026-01-06 07:35:35'),
(176, 6, 'Order Rejected', 'Unfortunately, your order #71 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":71,\"type\":\"order_update\"}', 1, '2026-01-06 07:36:05'),
(177, 6, 'Order Placed Successfully', 'Your order #72 has been placed successfully.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":72,\"type\":\"order_update\"}', 1, '2026-01-06 07:37:18'),
(178, 7, 'New Order Received', 'New order #72 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":72,\"type\":\"new_order\"}', 1, '2026-01-06 07:37:22'),
(179, 6, 'Order Confirmed', 'Great news! Your order #72 has been confirmed by chanti water service.\n\nItems: Mineral water\nTotal: ₹60\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":72,\"type\":\"order_update\"}', 1, '2026-01-06 07:37:54'),
(180, 6, 'Order Out for Delivery', 'Your order #72 is on its way!\n\nItems: Mineral water\nTotal: ₹60\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":72,\"type\":\"order_update\"}', 1, '2026-01-06 07:38:06'),
(181, 6, 'Order Delivered Successfully', 'Your order #72 has been delivered successfully!\n\nItems: Mineral water\nTotal: ₹60\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":72,\"type\":\"order_update\"}', 1, '2026-01-06 07:38:16'),
(182, 6, 'Order Placed Successfully', 'Your order #73 has been placed successfully.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":73,\"type\":\"order_update\"}', 1, '2026-01-06 07:42:36'),
(183, 7, 'New Order Received', 'New order #73 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":73,\"type\":\"new_order\"}', 1, '2026-01-06 07:42:42'),
(184, 6, 'Order Confirmed', 'Great news! Your order #73 has been confirmed by chanti water service.\n\nItems: Mineral water\nTotal: ₹60\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":73,\"type\":\"order_update\"}', 1, '2026-01-06 07:43:10'),
(185, 6, 'Order Out for Delivery', 'Your order #73 is on its way!\n\nItems: Mineral water\nTotal: ₹60\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":73,\"type\":\"order_update\"}', 1, '2026-01-06 07:43:22'),
(186, 6, 'Order Delivered Successfully', 'Your order #73 has been delivered successfully!\n\nItems: Mineral water\nTotal: ₹60\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":73,\"type\":\"order_update\"}', 1, '2026-01-06 07:43:26'),
(187, 6, 'Order Placed Successfully', 'Your order #74 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":74,\"type\":\"order_update\"}', 1, '2026-01-07 02:33:35'),
(188, 7, 'New Order Received', 'New order #74 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":74,\"type\":\"new_order\"}', 1, '2026-01-07 02:33:36'),
(189, 6, 'Order Placed Successfully', 'Your order #75 has been placed successfully.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":75,\"type\":\"order_update\"}', 1, '2026-01-07 02:45:07'),
(190, 7, 'New Order Received', 'New order #75 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":75,\"type\":\"new_order\"}', 1, '2026-01-07 02:45:24'),
(191, 6, 'Order Rejected', 'Unfortunately, your order #74 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":74,\"type\":\"order_update\"}', 1, '2026-01-08 02:34:55'),
(192, 6, 'Order Confirmed', 'Great news! Your order #75 has been confirmed by chanti water service.\n\nItems: Mineral water\nTotal: ₹60\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":75,\"type\":\"order_update\"}', 1, '2026-01-08 02:35:02'),
(193, 6, 'Order Out for Delivery', 'Your order #75 is on its way!\n\nItems: Mineral water\nTotal: ₹60\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":75,\"type\":\"order_update\"}', 1, '2026-01-08 02:35:06'),
(194, 6, 'Order Delivered Successfully', 'Your order #75 has been delivered successfully!\n\nItems: Mineral water\nTotal: ₹60\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":75,\"type\":\"order_update\"}', 1, '2026-01-08 02:35:12'),
(195, 6, 'Order Placed Successfully', 'Your order #76 has been placed successfully.\n\nItems: 2x Mineral water, 1x Pure water\nTotal: ₹170\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":76,\"type\":\"order_update\"}', 1, '2026-01-08 02:48:50'),
(196, 7, 'New Order Received', 'New order #76 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x Mineral water, 1x Pure water\nTotal: ₹170\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":76,\"type\":\"new_order\"}', 1, '2026-01-08 02:48:51'),
(197, 6, 'Order Placed Successfully', 'Your order #77 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":77,\"type\":\"order_update\"}', 1, '2026-01-08 03:57:16'),
(198, 7, 'New Order Received', 'New order #77 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":77,\"type\":\"new_order\"}', 1, '2026-01-08 03:57:18'),
(199, 6, 'Order Confirmed', 'Great news! Your order #76 has been confirmed by chanti water service.\n\nItems: Mineral water\nTotal: ₹170\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":76,\"type\":\"order_update\"}', 1, '2026-01-08 03:58:42'),
(200, 6, 'Order Out for Delivery', 'Your order #76 is on its way!\n\nItems: Mineral water\nTotal: ₹170\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":76,\"type\":\"order_update\"}', 1, '2026-01-08 03:58:56'),
(201, 6, 'Order Delivered Successfully', 'Your order #76 has been delivered successfully!\n\nItems: Mineral water\nTotal: ₹170\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":76,\"type\":\"order_update\"}', 1, '2026-01-08 03:59:03'),
(202, 6, 'Order Confirmed', 'Great news! Your order #77 has been confirmed by chanti water service.\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":77,\"type\":\"order_update\"}', 1, '2026-01-08 03:59:07'),
(203, 6, 'Order Out for Delivery', 'Your order #77 is on its way!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":77,\"type\":\"order_update\"}', 1, '2026-01-08 03:59:16'),
(204, 6, 'Order Delivered Successfully', 'Your order #77 has been delivered successfully!\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":77,\"type\":\"order_update\"}', 1, '2026-01-08 03:59:19'),
(205, 6, 'Order Placed Successfully', 'Your order #78 has been placed successfully.\n\nItems: 1x Pure water, 1x Mineral Water 20L\nTotal: ₹115\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":78,\"type\":\"order_update\"}', 1, '2026-01-08 04:58:36'),
(206, 7, 'New Order Received', 'New order #78 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Pure water, 1x Mineral Water 20L\nTotal: ₹115\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":78,\"type\":\"new_order\"}', 1, '2026-01-08 04:58:36'),
(207, 6, 'Order Placed Successfully', 'Your order #79 has been placed successfully.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":79,\"type\":\"order_update\"}', 1, '2026-01-19 05:53:01'),
(208, 7, 'New Order Received', 'New order #79 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral Water 20L\nTotal: ₹65\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":79,\"type\":\"new_order\"}', 1, '2026-01-19 05:53:01'),
(209, 6, 'Order Placed Successfully', 'Your order #80 has been placed successfully.\n\nItems: 2x Mineral Water 20L\nTotal: ₹130\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":80,\"type\":\"order_update\"}', 1, '2026-01-20 03:48:23'),
(210, 7, 'New Order Received', 'New order #80 from Timmareddy Prem Kumar Reddy.\n\nItems: 2x Mineral Water 20L\nTotal: ₹130\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":80,\"type\":\"new_order\"}', 1, '2026-01-20 03:48:23'),
(211, 6, 'Order Rejected', 'Unfortunately, your order #78 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Pure water\nTotal: ₹115\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":78,\"type\":\"order_update\"}', 1, '2026-01-20 03:49:42'),
(212, 6, 'Order Rejected', 'Unfortunately, your order #79 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Mineral Water 20L\nTotal: ₹65\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":79,\"type\":\"order_update\"}', 1, '2026-01-20 03:49:44'),
(213, 6, 'Order Placed Successfully', 'Your order #81 has been placed successfully.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":81,\"type\":\"order_update\"}', 0, '2026-01-20 03:59:04'),
(214, 7, 'New Order Received', 'New order #81 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":81,\"type\":\"new_order\"}', 0, '2026-01-20 03:59:04'),
(215, 6, 'Order Rejected', 'Unfortunately, your order #80 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Mineral Water 20L\nTotal: ₹130\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":80,\"type\":\"order_update\"}', 0, '2026-01-20 03:59:55'),
(216, 6, 'Order Placed Successfully', 'Your order #82 has been placed successfully.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\n\nThe partner will confirm your order shortly.', '{\"order_id\":82,\"type\":\"order_update\"}', 0, '2026-01-20 04:03:21'),
(217, 7, 'New Order Received', 'New order #82 from Timmareddy Prem Kumar Reddy.\n\nItems: 1x Mineral water\nTotal: ₹60\nPayment: COD\nDelivery Address: Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India, Saveetha Nagar\n\nPlease accept or reject this order.', '{\"order_id\":82,\"type\":\"new_order\"}', 0, '2026-01-20 04:03:33'),
(218, 6, 'Order Rejected', 'Unfortunately, your order #81 has been declined by chanti water service.\n\nReason: Rejected by partner\n\nItems: Mineral water\nTotal: ₹60\n\nPlease try ordering from another partner, or try again later.', '{\"order_id\":81,\"type\":\"order_update\"}', 0, '2026-01-20 04:04:08'),
(219, 6, 'Order Confirmed', 'Great news! Your order #82 has been confirmed by chanti water service.\n\nItems: Mineral water\nTotal: ₹60\n\nEstimated delivery: 30-45 minutes. You\'ll be notified when it\'s out for delivery.', '{\"order_id\":82,\"type\":\"order_update\"}', 0, '2026-01-20 04:04:12'),
(220, 6, 'Order Out for Delivery', 'Your order #82 is on its way!\n\nItems: Mineral water\nTotal: ₹60\n\nThe delivery partner from chanti water service is heading to your location. Please be available to receive your order.', '{\"order_id\":82,\"type\":\"order_update\"}', 0, '2026-01-20 04:04:21'),
(221, 6, 'Order Delivered Successfully', 'Your order #82 has been delivered successfully!\n\nItems: Mineral water\nTotal: ₹60\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.', '{\"order_id\":82,\"type\":\"order_update\"}', 0, '2026-01-20 04:04:25');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT 'COD',
  `payment_status` varchar(20) DEFAULT 'pending',
  `delivery_date` varchar(50) DEFAULT 'Today',
  `delivery_slot` varchar(100) DEFAULT 'Morning (6 AM - 9 AM)',
  `upi_response` text DEFAULT NULL,
  `txn_id` varchar(100) DEFAULT NULL,
  `utr_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `partner_id`, `address_id`, `product_name`, `quantity`, `total`, `status`, `payment_method`, `payment_status`, `delivery_date`, `delivery_slot`, `upi_response`, `txn_id`, `utr_number`, `created_at`, `delivery_notes`) VALUES
(26, 6, 7, 11, 'Bisleri 5L', 2, 90, 'cancelled', 'COD', 'pending', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2025-12-26 09:22:15', NULL),
(27, 6, 7, 11, 'Mineral Water 20L', 2, 130, 'delivered', 'COD', 'pending', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2025-12-26 09:45:02', NULL),
(28, 6, 7, 11, 'Bisleri 5L', 2, 70, 'cancelled', 'UPI', 'pending', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2025-12-26 17:50:33', NULL),
(29, 6, 7, 11, 'Bisleri 5L', 3, 135, 'delivered', 'COD', 'paid', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2025-12-27 10:02:37', NULL),
(30, 6, 7, 11, 'Bisleri 5L', 4, 200, 'delivered', 'COD', 'paid', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2025-12-27 13:38:13', NULL),
(31, 6, 5, 11, 'Bisleri 20L', 2, 90, 'pending', 'COD', 'pending', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2025-12-28 05:18:23', NULL),
(32, 6, 7, 11, 'Mineral Water 20L', 2, 130, 'cancelled', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 05:36:57', NULL),
(33, 6, 7, 11, 'Kinley 1L Pack', 1, 120, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 06:40:14', NULL),
(34, 6, 7, 12, 'Mineral Water 20L', 2, 90, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 07:57:22', NULL),
(35, 6, 5, 12, 'Bisleri 20L', 1, 45, 'pending', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 08:13:05', NULL),
(36, 6, 7, 13, 'Kinley 1L Pack', 1, 120, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 08:23:28', NULL),
(37, 6, 5, 13, 'Bisleri 20L', 1, 45, 'pending', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 08:24:48', NULL),
(38, 6, 5, 13, 'Bisleri 20L', 1, 45, 'pending', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 08:40:14', NULL),
(39, 6, 5, 13, 'Bisleri 20L', 1, 45, 'pending', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 08:40:44', NULL),
(40, 6, 7, 13, 'Himalayan Water 20L', 1, 75, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-28 08:44:44', NULL),
(41, 6, 7, 13, 'Mineral Water 20L', 1, 65, 'delivered', 'COD', 'paid', 'Today', 'Evening (5 PM - 8 PM)', NULL, NULL, NULL, '2025-12-29 02:57:38', NULL),
(42, 6, 7, 13, 'Bisleri 5L', 2, 50, 'delivered', 'COD', 'paid', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2025-12-29 03:23:31', NULL),
(43, 6, 7, 13, 'Kinley 1L Pack', 1, 120, 'delivered', 'COD', 'paid', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2025-12-29 03:23:56', NULL),
(44, 6, 7, 13, 'RO Water 20L', 1, 30, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-29 03:33:05', NULL),
(45, 6, 7, 13, 'Himalayan Water 20L', 2, 150, 'cancelled', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-29 03:36:34', NULL),
(46, 6, 7, 13, 'Mineral Water 20L', 2, 185, 'cancelled', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-29 05:00:43', NULL),
(47, 6, 7, 13, 'Mineral Water 20L', 1, 65, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-29 05:14:39', NULL),
(48, 6, 5, 13, 'Bisleri 20L', 1, 45, 'cancelled', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-30 04:19:04', NULL),
(49, 6, 7, 14, 'Mineral Water 20L', 1, 65, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2025-12-30 04:20:50', NULL),
(50, 6, 7, 14, 'Bisleri 5L', 2, 50, 'delivered', 'RAZORPAY', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', 'Razorpay Payment: pay_RxlkOsqOBHDYOV', '', '', '2025-12-30 09:12:41', NULL),
(51, 6, 7, 14, 'Bisleri 5L', 1, 25, 'delivered', 'RAZORPAY', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', 'Razorpay Payment: pay_RxllkBVPQ1g30Q', '', '', '2025-12-30 09:13:56', NULL),
(52, 6, 7, 14, 'Mineral Water 20L', 1, 65, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-02 08:59:21', NULL),
(53, 6, 5, 14, 'Bisleri 20L', 1, 45, 'cancelled', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-03 06:40:02', NULL),
(54, 6, 7, 14, 'Mineral Water 20L', 1, 65, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-03 06:53:41', NULL),
(55, 6, 5, 14, 'Bisleri 20L', 2, 110, 'cancelled', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-03 06:58:33', NULL),
(56, 6, 7, 14, 'Bisleri 5L', 1, 25, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-03 06:59:40', NULL),
(57, 6, 7, 14, 'Mineral Water 20L', 1, 65, 'cancelled', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-03 07:20:42', NULL),
(58, 6, 7, 14, 'Bisleri 5L', 1, 25, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-03 07:21:07', NULL),
(59, 6, 7, 14, 'Bisleri 5L', 1, 25, 'delivered', 'COD', 'paid', 'Today', 'Evening (5 PM - 8 PM)', NULL, NULL, NULL, '2026-01-05 12:00:09', NULL),
(60, 6, 7, 14, 'Bisleri 5L', 1, 25, 'delivered', 'COD', 'paid', 'Today', 'Evening (5 PM - 8 PM)', NULL, NULL, NULL, '2026-01-05 12:24:38', NULL),
(61, 6, 7, 14, 'Mineral Water 20L', 2, 130, 'delivered', 'COD', 'paid', 'Today', 'Evening (5 PM - 8 PM)', NULL, NULL, NULL, '2026-01-05 13:22:38', NULL),
(62, 6, 7, 1, 'Bisleri 5L', 2, 90, 'delivered', 'RAZORPAY', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', 'Razorpay Payment: pay_S0TDpXSUGqqW0t', '', '', '2026-01-06 05:02:20', NULL),
(63, 6, 7, 15, 'Bisleri 5L', 2, 90, 'cancelled', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-06 05:06:21', NULL),
(64, 6, 7, 15, 'Bisleri 5L', 4, 275, 'delivered', 'COD', 'paid', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-06 05:21:50', NULL),
(65, 6, 7, 15, 'RO Water 20L', 2, 60, 'delivered', 'COD', 'paid', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-06 05:36:15', NULL),
(66, 6, 7, 15, 'Bisleri 5L', 1, 25, 'rejected', 'COD', 'pending', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-06 05:39:09', NULL),
(67, 6, 7, 15, 'Bisleri 5L', 3, 155, 'delivered', 'COD', 'paid', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-06 06:27:12', NULL),
(68, 6, 7, 15, 'Purified drinking water', 1, 40, 'cancelled', 'COD', 'pending', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-06 06:45:51', NULL),
(69, 6, 7, 15, 'Mineral Water 20L', 1, 65, 'delivered', 'COD', 'paid', 'Today', 'Evening (5 PM - 8 PM)', NULL, NULL, NULL, '2026-01-06 07:23:42', NULL),
(70, 6, 7, 15, 'Mineral water', 1, 60, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-06 07:29:22', NULL),
(71, 6, 7, 15, 'Mineral Water 20L', 1, 65, 'rejected', 'COD', 'pending', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-06 07:35:23', NULL),
(72, 6, 7, 15, 'Mineral water', 1, 60, 'delivered', 'COD', 'paid', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-06 07:37:18', NULL),
(73, 6, 7, 15, 'Mineral water', 1, 60, 'delivered', 'COD', 'paid', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-06 07:42:36', NULL),
(74, 6, 7, 16, 'Mineral Water 20L', 1, 65, 'rejected', 'COD', 'pending', 'Today', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2026-01-07 02:33:35', NULL),
(75, 6, 7, 16, 'Mineral water', 1, 60, 'delivered', 'COD', 'paid', 'Jan 12', 'Morning (6 AM - 9 AM)', NULL, NULL, NULL, '2026-01-07 02:45:07', NULL),
(76, 6, 7, 16, 'Mineral water', 3, 170, 'delivered', 'COD', 'paid', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-08 02:48:50', NULL),
(77, 6, 7, 16, 'Mineral Water 20L', 1, 65, 'delivered', 'COD', 'paid', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-08 03:57:16', NULL),
(78, 6, 7, 16, 'Pure water', 2, 115, 'rejected', 'COD', 'pending', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-08 04:58:36', NULL),
(79, 6, 7, 16, 'Mineral Water 20L', 1, 65, 'rejected', 'COD', 'pending', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-19 05:53:01', NULL),
(80, 6, 7, 16, 'Mineral Water 20L', 2, 130, 'rejected', 'COD', 'pending', 'Today', 'Afternoon (12 PM - 3 PM)', NULL, NULL, NULL, '2026-01-20 03:48:23', NULL),
(81, 6, 7, 16, 'Mineral water', 1, 60, 'rejected', 'COD', 'pending', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-20 03:59:04', NULL),
(82, 6, 7, 16, 'Mineral water', 1, 60, 'delivered', 'COD', 'paid', 'Today', 'Immediate Delivery (20-30 mins)', NULL, NULL, NULL, '2026-01-20 04:03:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `qty`, `price`, `total_price`) VALUES
(26, 26, 7, NULL, 1, 25.00, 25.00),
(27, 26, 6, NULL, 1, 65.00, 65.00),
(28, 27, 6, NULL, 2, 65.00, 130.00),
(29, 28, 7, NULL, 1, 25.00, 25.00),
(30, 28, 1, NULL, 1, 45.00, 45.00),
(31, 30, 7, NULL, 1, 25.00, 25.00),
(32, 30, 1, NULL, 1, 45.00, 45.00),
(33, 30, 6, NULL, 2, 65.00, 130.00),
(34, 31, 1, NULL, 2, 45.00, 90.00),
(35, 32, 6, NULL, 2, 65.00, 130.00),
(36, 33, 8, NULL, 1, 120.00, 120.00),
(37, 34, 6, NULL, 1, 65.00, 65.00),
(38, 34, 7, NULL, 1, 25.00, 25.00),
(39, 35, 1, NULL, 1, 45.00, 45.00),
(40, 36, 8, NULL, 1, 120.00, 120.00),
(41, 37, 1, NULL, 1, 45.00, 45.00),
(42, 38, 1, NULL, 1, 45.00, 45.00),
(43, 39, 1, NULL, 1, 45.00, 45.00),
(44, 40, 0, NULL, 1, 75.00, 75.00),
(45, 41, 6, NULL, 1, 65.00, 65.00),
(46, 42, 7, NULL, 2, 25.00, 50.00),
(47, 43, 8, NULL, 1, 120.00, 120.00),
(48, 44, 10, NULL, 1, 30.00, 30.00),
(49, 45, 0, NULL, 2, 75.00, 150.00),
(50, 46, 6, NULL, 1, 65.00, 65.00),
(51, 46, 8, NULL, 1, 120.00, 120.00),
(52, 47, 6, NULL, 1, 65.00, 65.00),
(53, 48, 1, NULL, 1, 45.00, 45.00),
(54, 49, 6, NULL, 1, 65.00, 65.00),
(55, 50, 7, NULL, 2, 25.00, 50.00),
(56, 51, 7, NULL, 1, 25.00, 25.00),
(57, 52, 6, NULL, 1, 65.00, 65.00),
(58, 53, 1, NULL, 1, 45.00, 45.00),
(59, 54, 6, NULL, 1, 65.00, 65.00),
(60, 55, 1, NULL, 1, 45.00, 45.00),
(61, 55, 6, NULL, 1, 65.00, 65.00),
(62, 56, 7, NULL, 1, 25.00, 25.00),
(63, 57, 6, NULL, 1, 65.00, 65.00),
(64, 58, 7, NULL, 1, 25.00, 25.00),
(65, 59, 7, NULL, 1, 25.00, 25.00),
(66, 60, 7, NULL, 1, 25.00, 25.00),
(67, 61, 6, NULL, 2, 65.00, 130.00),
(68, 62, 7, NULL, 1, 25.00, 25.00),
(69, 62, 6, NULL, 1, 65.00, 65.00),
(70, 63, 7, NULL, 1, 25.00, 25.00),
(71, 63, 6, NULL, 1, 65.00, 65.00),
(72, 64, 7, NULL, 1, 25.00, 25.00),
(73, 64, 6, NULL, 2, 65.00, 130.00),
(74, 64, 8, NULL, 1, 120.00, 120.00),
(75, 65, 10, NULL, 2, 30.00, 60.00),
(76, 66, 7, NULL, 1, 25.00, 25.00),
(77, 67, 7, NULL, 1, 25.00, 25.00),
(78, 67, 6, NULL, 2, 65.00, 130.00),
(79, 68, 13, NULL, 1, 40.00, 40.00),
(80, 69, 6, NULL, 1, 65.00, 65.00),
(81, 70, 7, NULL, 1, 60.00, 60.00),
(82, 71, 6, NULL, 1, 65.00, 65.00),
(83, 72, 7, NULL, 1, 60.00, 60.00),
(84, 73, 7, NULL, 1, 60.00, 60.00),
(85, 74, 6, NULL, 1, 65.00, 65.00),
(86, 75, 7, NULL, 1, 60.00, 60.00),
(87, 76, 7, NULL, 2, 60.00, 120.00),
(88, 76, 8, NULL, 1, 50.00, 50.00),
(89, 77, 6, NULL, 1, 65.00, 65.00),
(90, 78, 8, NULL, 1, 50.00, 50.00),
(91, 78, 6, NULL, 1, 65.00, 65.00),
(92, 79, 6, NULL, 1, 65.00, 65.00),
(93, 80, 6, NULL, 2, 65.00, 130.00),
(94, 81, 7, NULL, 1, 60.00, 60.00),
(95, 82, 7, NULL, 1, 60.00, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `email`, `otp`, `expires_at`, `used`, `created_at`) VALUES
(15, 10, 'gorantlagorantla50@gmail.com', '877251', '2025-12-28 12:56:51', 0, '2025-12-28 11:46:51'),
(16, 6, 'tprem6565@gmail.com', '832190', '2025-12-28 12:58:24', 0, '2025-12-28 11:48:24'),
(31, 11, 'gorantlasrikrishna0@gmail.com', '970004', '2026-01-02 10:30:42', 0, '2026-01-02 09:20:42'),
(42, 9, 'chantigorantla848@gmail.com', '3731fee984', '2026-01-19 08:13:48', 0, '2026-01-19 07:03:48');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `partner_id`, `name`, `size`, `price`, `description`, `image_url`, `stock`) VALUES
(1, 5, 'Bisleri 20L', '20L', 45, NULL, 'uploads/products/water_can_20l.png', 100),
(2, 5, 'Kinley 20L', '20L', 50, NULL, 'uploads/products/water_can_20l.png', 100),
(3, 5, 'Aquafina 20L', '20L', 55, NULL, 'uploads/products/water_can_20l.png', 100),
(4, 5, 'Local Brand 20L', '20L', 35, NULL, 'uploads/products/water_can_20l.png', 100),
(5, 5, 'Premium Alkaline 20L', '20L', 80, NULL, 'uploads/products/water_can_20l.png', 100),
(6, 7, 'Mineral Water 20L', '20L', 65, NULL, 'uploads/products/water_can_20l.png', 100),
(7, 7, 'Mineral water', '20L', 60, '', 'uploads/products/water_can_20l.png', 200),
(8, 7, 'Pure water', '20L', 50, '', 'uploads/products/water_can_20l.png', 100),
(10, 7, 'RO Water 20L', '20L', 30, NULL, 'uploads/products/water_can_20l.png', 100),
(13, 7, 'Purified drinking water', '20L', 40, '0', 'uploads/products/product_7_1767681857_695caf4187f31.jpg', 50);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_label` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `sku` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_label`, `price`, `stock`, `sku`, `created_at`) VALUES
(1, 1, 'Standard Bottle', 45.00, 100, 'BIS-20L-STD', '2025-12-25 14:15:00'),
(2, 1, 'Refill Only', 40.00, 200, 'BIS-20L-REF', '2025-12-25 14:15:00'),
(3, 2, 'Standard Bottle', 50.00, 80, 'KIN-20L-STD', '2025-12-25 14:15:00'),
(4, 2, 'Refill Only', 45.00, 150, 'KIN-20L-REF', '2025-12-25 14:15:00'),
(5, 3, 'Standard Bottle', 55.00, 60, 'AQF-20L-STD', '2025-12-25 14:15:00'),
(6, 4, 'Standard Bottle', 35.00, 200, 'LOC-20L-STD', '2025-12-25 14:15:00'),
(7, 5, 'pH 8.5+', 80.00, 40, 'ALK-20L-85', '2025-12-25 14:15:00'),
(8, 5, 'pH 9+', 90.00, 30, 'ALK-20L-90', '2025-12-25 14:15:00'),
(9, 7, 'Standard', 25.00, 150, 'BIS-5L-STD', '2025-12-25 14:15:00'),
(10, 8, 'Pack of 12', 120.00, 100, 'KIN-1L-12', '2025-12-25 14:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5 COMMENT '1-5 stars',
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `frequency` enum('weekly','monthly') DEFAULT 'monthly',
  `next_delivery_date` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_name`, `price`, `frequency`, `next_delivery_date`, `active`, `created_at`) VALUES
(3, 6, 'Weekly Kinley 20L x 3', 150.00, 'weekly', '2024-12-28', 1, '2025-12-25 14:15:00'),
(4, 6, 'Monthly RO Water x 10', 300.00, 'monthly', '2025-01-01', 0, '2025-12-25 14:15:00'),
(5, 7, 'Weekly Local 20L x 5', 175.00, 'weekly', '2024-12-29', 1, '2025-12-25 14:15:00'),
(7, 6, 'Monthly Alkaline x 2', 160.00, 'monthly', '2025-01-10', 1, '2025-12-25 14:15:00'),
(8, 7, 'Weekly Mineral x 2', 130.00, 'weekly', '2024-12-26', 0, '2025-12-25 14:15:00'),
(10, 6, 'Weekly Aquafina x 2', 110.00, 'weekly', '2024-12-31', 1, '2025-12-25 14:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `type`, `source`, `note`, `created_at`) VALUES
(1, 5, 150.00, 'credit', 'Order #1', 'Delivery commission', '2025-12-25 14:15:00'),
(2, 5, 200.00, 'credit', 'Order #2', 'Delivery commission', '2025-12-25 14:15:00'),
(3, 5, 50.00, 'debit', 'Withdrawal', 'Bank transfer', '2025-12-25 14:15:00'),
(4, 7, 180.00, 'credit', 'Order #6', 'Delivery commission', '2025-12-25 14:15:00'),
(5, 7, 100.00, 'credit', 'Order #7', 'Delivery commission', '2025-12-25 14:15:00'),
(6, 5, 165.00, 'credit', 'Order #3', 'Delivery commission', '2025-12-25 14:15:00'),
(7, 7, 225.00, 'credit', 'Order #9', 'Delivery commission', '2025-12-25 14:15:00'),
(8, 5, 100.00, 'debit', 'Withdrawal', 'UPI transfer', '2025-12-25 14:15:00'),
(9, 7, 300.00, 'credit', 'Order #10', 'Delivery commission', '2025-12-25 14:15:00'),
(10, 5, 80.00, 'credit', 'Order #5', 'Delivery commission', '2025-12-25 14:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `shop_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('customer','partner') DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 1,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `service_radius_km` int(11) DEFAULT 10,
  `fcm_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `shop_name`, `email`, `phone`, `upi_id`, `password`, `role`, `token`, `is_online`, `lat`, `lng`, `service_radius_km`, `fcm_token`) VALUES
(5, 'Test Partner', 'test water service\r\n', 'partner@gmail.com', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'partner', 'f767d7a4b3dbbfdb144defb5e72f5372', 1, 13.0418000, 80.2341000, 15, NULL),
(6, 'Timmareddy Prem Kumar Reddy', NULL, 'tprem6565@gmail.com', NULL, NULL, '$2y$10$e9JYdtMUxeY80E4MaxZXgeuJ.wN9cg22CVC7U5oSOO9ixnRqUbPH6', 'customer', NULL, 1, NULL, NULL, 10, 'fKhRqH3fSpS33X6evJ7eTm:APA91bEkfGaiA-_hVQ1kUfHqOz2lvNSFFgmlUg37Fe0AP9-uFwcRfU7K8n8f8W15ASQE65J1sPrQG6cgUD6kJS9T_cZcNjH0c6a0KDWaXtUVSs7eL5rCPlc'),
(7, 'chanti', 'chanti water service', 'prem@gmail.com', NULL, '8985545407@fam', '$2y$10$wQDoTHtA1YUF7VHYzn8bsueYSg22T/Grz9mc19ZYye4bAHUXoqp16', 'partner', NULL, 1, 13.0349883, 80.0587406, 10, 'fKhRqH3fSpS33X6evJ7eTm:APA91bEkfGaiA-_hVQ1kUfHqOz2lvNSFFgmlUg37Fe0AP9-uFwcRfU7K8n8f8W15ASQE65J1sPrQG6cgUD6kJS9T_cZcNjH0c6a0KDWaXtUVSs7eL5rCPlc'),
(8, 'tharun ', 'Tharun Water Service', 'prem1@gmail.com', NULL, NULL, '$2y$10$q0eU9a/99CQa6DZacpjy5OYHjhlO83Is.AYjBDVsadn8czgvme.L.', 'partner', NULL, 0, 13.0220343, 80.0168372, 10, NULL),
(9, 'Gorantla Srikrishna', NULL, 'chantigorantla848@gmail.com', NULL, NULL, '$2y$10$NwTmYgpC8kCzk6u6XVJ56e/1auIY.tdKlqD/e7KprXXwjdlbwVoDu', 'customer', NULL, 1, NULL, NULL, 10, 'fxhqAofwRS2M_fwnsMLGos:APA91bFNj10H_aHBIo6o-wL2YdHn8WU8lgpWPwH_p_EH2TPgXAezwkL3xgJO2cRcEPy88HlyOxtsEjFVT3IYSSl449kOlpUACXSfhSoXmCYIUVtSVq5hi0Y'),
(10, 'Chanti Gorantla', 'Krishna water Cans service', 'gorantlagorantla50@gmail.com', '8247480163', '8247480163@ibl', '$2y$10$O7tfRe9l0Nb55A.hiEuOGOsQ/S8uAn34BcHxLJcD1nFY1Mp5.912a', 'partner', '7a1ea0ce9673c4a9e2a8570b9f51f248c2920bf00ba6bc6d2fcda8600256586e', 1, NULL, NULL, 10, NULL),
(11, 'Gorantla', NULL, 'gorantlasrikrishna0@gmail.com', NULL, NULL, '$2y$10$XcYAsZdC.kaSw1IJDrG96uuVTsRAGQeI3pxjmUVDaKbOFQZmrFNL.', 'customer', NULL, 1, NULL, NULL, 10, 'fxhqAofwRS2M_fwnsMLGos:APA91bFNj10H_aHBIo6o-wL2YdHn8WU8lgpWPwH_p_EH2TPgXAezwkL3xgJO2cRcEPy88HlyOxtsEjFVT3IYSSl449kOlpUACXSfhSoXmCYIUVtSVq5hi0Y');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_partner` (`partner_id`),
  ADD KEY `idx_address_id` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp` (`otp`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_partner` (`partner_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_rating` (`order_id`),
  ADD KEY `fk_rating_user` (`user_id`),
  ADD KEY `fk_rating_partner` (`partner_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_locations`
--
ALTER TABLE `driver_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `driver_locations_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_partner` FOREIGN KEY (`partner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_partner` FOREIGN KEY (`partner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `fk_rating_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rating_partner` FOREIGN KEY (`partner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rating_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
