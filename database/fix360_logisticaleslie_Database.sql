-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 29-09-2025 a las 23:05:45
-- Versión del servidor: 5.7.23-23
-- Versión de PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fix360_logisticaleslie`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Quesos Frescos', 'Quesos de producción diaria', '2025-09-28 07:24:56'),
(2, 'Quesos Curados', 'Quesos con proceso de maduración', '2025-09-28 07:24:56'),
(3, 'Productos Especiales', 'Productos de temporada y especiales', '2025-09-28 07:24:56'),
(4, 'Quesos Frescos', 'Quesos de producción diaria', '2025-09-28 08:20:26'),
(5, 'Quesos Curados', 'Quesos con proceso de maduración', '2025-09-28 08:20:26'),
(6, 'Productos Especiales', 'Productos de temporada y especiales', '2025-09-28 08:20:26'),
(7, 'Quesos Frescos', 'Quesos de producción diaria', '2025-09-28 08:20:52'),
(8, 'Quesos Curados', 'Quesos con proceso de maduración', '2025-09-28 08:20:52'),
(9, 'Productos Especiales', 'Productos de temporada y especiales', '2025-09-28 08:20:52'),
(10, 'Quesos Frescos', 'Quesos de producción diaria', '2025-09-30 02:21:31'),
(11, 'Quesos Curados', 'Quesos con proceso de maduración', '2025-09-30 02:21:31'),
(12, 'Productos Especiales', 'Productos de temporada y especiales', '2025-09-30 02:21:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `business_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `contact_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8_unicode_ci,
  `city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `credit_limit` decimal(10,2) DEFAULT '0.00',
  `credit_days` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `customers`
--

INSERT INTO `customers` (`id`, `code`, `business_name`, `contact_name`, `phone`, `email`, `address`, `city`, `state`, `postal_code`, `credit_limit`, `credit_days`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'CLI001', 'Tienda Don Carlos', 'Carlos Ramírez', '555-1001', 'carlos@tienda.com', 'Av. Principal 123', 'México', 'CDMX', NULL, 5000.00, 15, 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(2, 'CLI002', 'Supermercado La Esquina', 'Rosa Hernández', '555-1002', 'rosa@esquina.com', 'Calle 5 de Mayo 456', 'Guadalajara', 'Jalisco', NULL, 8000.00, 30, 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(3, 'CLI003', 'Abarrotes El Buen Precio', 'Luis Torres', '555-1003', 'luis@buenprecio.com', 'Calle Morelos 789', 'Zapopan', 'Jalisco', NULL, 3000.00, 15, 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(101, '', '', 'Cliente de prueba', NULL, 'cliente101@example.com', NULL, NULL, NULL, NULL, 0.00, 0, 1, '2025-09-29 23:13:36', '2025-09-29 23:13:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `customer_surveys`
--

CREATE TABLE `customer_surveys` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `survey_date` date NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `product_quality_rating` int(11) DEFAULT NULL,
  `service_rating` int(11) DEFAULT NULL,
  `delivery_rating` int(11) DEFAULT NULL,
  `comments` text COLLATE utf8_unicode_ci,
  `channel` enum('whatsapp','email','phone','web') COLLATE utf8_unicode_ci DEFAULT 'web',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `delivery_routes`
--

CREATE TABLE `delivery_routes` (
  `id` int(11) NOT NULL,
  `route_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `driver_id` int(11) NOT NULL,
  `route_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') COLLATE utf8_unicode_ci DEFAULT 'planned',
  `notes` text COLLATE utf8_unicode_ci,
  `total_orders` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direct_sales`
--

CREATE TABLE `direct_sales` (
  `id` int(11) NOT NULL,
  `sale_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `route_id` int(11) DEFAULT NULL,
  `sale_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','transfer') COLLATE utf8_unicode_ci NOT NULL,
  `payment_status` enum('paid','pending') COLLATE utf8_unicode_ci DEFAULT 'paid',
  `seller_id` int(11) NOT NULL,
  `qr_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `final_amount` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direct_sale_details`
--

CREATE TABLE `direct_sale_details` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `lot_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `lot_id` int(11) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `reserved_quantity` decimal(10,3) DEFAULT '0.000',
  `location` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `type` enum('production','sale','return','adjustment','transfer') COLLATE utf8_unicode_ci NOT NULL,
  `product_id` int(11) NOT NULL,
  `lot_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `movement_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` enum('pending','confirmed','in_route','delivered','cancelled') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `final_amount` decimal(10,2) DEFAULT '0.00',
  `payment_method` enum('cash','card','transfer','credit') COLLATE utf8_unicode_ci DEFAULT 'cash',
  `payment_status` enum('pending','partial','paid') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8_unicode_ci,
  `qr_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `lot_id` int(11) DEFAULT NULL,
  `quantity_ordered` decimal(10,3) NOT NULL,
  `quantity_delivered` decimal(10,3) DEFAULT '0.000',
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `production_lots`
--

CREATE TABLE `production_lots` (
  `id` int(11) NOT NULL,
  `lot_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `product_id` int(11) NOT NULL,
  `production_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `quantity_produced` decimal(10,3) NOT NULL,
  `quantity_available` decimal(10,3) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `quality_status` enum('excellent','good','fair','rejected') COLLATE utf8_unicode_ci DEFAULT 'good',
  `notes` text COLLATE utf8_unicode_ci,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `production_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'regular'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `category_id` int(11) DEFAULT NULL,
  `unit_type` enum('granel','pieza','paquete') COLLATE utf8_unicode_ci NOT NULL,
  `unit_weight` decimal(8,3) DEFAULT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `minimum_stock` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `code`, `name`, `description`, `category_id`, `unit_type`, `unit_weight`, `price_per_unit`, `minimum_stock`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PRD001', 'Queso Oaxaca 500g', 'Queso Oaxaca tradicional de 500 gramos', 1, 'pieza', NULL, 75.00, 20, 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(2, 'PRD002', 'Queso Panela 400g', 'Queso Panela fresco de 400 gramos', 1, 'pieza', NULL, 45.00, 15, 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(3, 'PRD003', 'Queso Manchego 300g', 'Queso Manchego curado de 300 gramos', 2, 'pieza', NULL, 95.00, 10, 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(4, 'PRD004', 'Crema Ácida 200ml', 'Crema ácida natural', 1, 'pieza', NULL, 25.00, 30, 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(5, 'PRD005', 'Yogurt Natural 1L', 'Yogurt natural sin azúcar', 1, 'pieza', NULL, 35.00, 25, 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `return_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `return_date` date NOT NULL,
  `reason` enum('expired','damaged','quality','excess','other') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected','processed') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `processed_by` int(11) DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `return_details`
--

CREATE TABLE `return_details` (
  `id` int(11) NOT NULL,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `lot_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `disposition` enum('restock','discard','donate') COLLATE utf8_unicode_ci DEFAULT 'restock'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `routes`
--

CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `route_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `route_date` date NOT NULL,
  `driver_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') COLLATE utf8_unicode_ci DEFAULT 'planned',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `total_distance` decimal(8,2) DEFAULT NULL,
  `fuel_cost` decimal(8,2) DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `route_orders`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `route_orders` (
`id` int(11)
,`route_id` int(11)
,`order_id` int(11)
,`stop_sequence` int(11)
,`estimated_arrival` time
,`actual_arrival` time
,`status` enum('pending','arrived','delivered','failed')
,`delivery_status` enum('pending','delivered','failed','partial')
,`notes` text
,`delivery_notes` text
,`delivered_by` int(11)
,`sequence_order` int(11)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `route_stops`
--

CREATE TABLE `route_stops` (
  `id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `stop_order` int(11) NOT NULL,
  `stop_sequence` int(11) DEFAULT '1',
  `estimated_arrival` time DEFAULT NULL,
  `estimated_arrival_time` time DEFAULT NULL,
  `actual_arrival` time DEFAULT NULL,
  `status` enum('pending','arrived','delivered','failed') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `delivery_status` enum('pending','delivered','failed','partial') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8_unicode_ci,
  `delivery_notes` text COLLATE utf8_unicode_ci,
  `delivered_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `config_value` text COLLATE utf8_unicode_ci,
  `description` text COLLATE utf8_unicode_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `system_config`
--

INSERT INTO `system_config` (`id`, `config_key`, `config_value`, `description`, `updated_at`) VALUES
(1, 'company_name', 'Quesos y Productos Leslie', 'Nombre de la empresa', '2025-09-28 07:24:56'),
(2, 'company_address', 'Av. Industria 123, Guadalajara, Jalisco', 'Dirección de la empresa', '2025-09-28 07:24:56'),
(3, 'company_phone', '33-1234-5678', 'Teléfono de la empresa', '2025-09-28 07:24:56'),
(4, 'company_email', 'info@leslie.com', 'Email de contacto', '2025-09-28 07:24:56'),
(5, 'qr_code_size', '200', 'Tamaño de códigos QR en píxeles', '2025-09-28 07:24:56'),
(6, 'session_timeout', '3600', 'Tiempo de sesión en segundos', '2025-09-28 07:24:56'),
(7, 'backup_frequency', 'daily', 'Frecuencia de respaldos', '2025-09-28 07:24:56'),
(8, 'notification_email', 'admin@leslie.com', 'Email para notificaciones del sistema', '2025-09-28 07:24:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `role` enum('admin','manager','seller','driver','warehouse') COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@leslie.com', '$2y$10$A1jtEow3304cQl7lmi42XOMzEYmNSDgLkOuuxPVr2L161Q4fMHWRi', 'Administrador', 'Sistema', 'admin', '555-0001', 1, '2025-09-28 07:24:56', '2025-09-29 18:02:29'),
(2, 'gerente', 'gerente@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Pérez', 'manager', '555-0002', 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(3, 'vendedor1', 'vendedor@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'González', 'seller', '555-0003', 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56'),
(4, 'chofer1', 'chofer@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'José', 'Martínez', 'driver', '555-0004', 1, '2025-09-28 07:24:56', '2025-09-28 07:24:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logout_time` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `login_time`, `logout_time`, `ip_address`, `user_agent`) VALUES
(1, 1, '2025-09-28 07:44:05', '2025-09-28 07:44:57', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(2, 1, '2025-09-28 07:45:02', '2025-09-28 08:05:32', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(3, 1, '2025-09-28 08:18:47', '2025-09-28 08:23:13', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(4, 1, '2025-09-28 08:23:17', '2025-09-29 18:01:47', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(5, 1, '2025-09-29 18:02:11', '2025-09-29 18:02:33', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(6, 1, '2025-09-29 18:02:39', NULL, '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(7, 1, '2025-09-29 18:30:28', '2025-09-29 18:44:48', '189.128.190.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(8, 1, '2025-09-29 18:45:25', NULL, '189.128.190.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(9, 1, '2025-09-29 20:59:02', NULL, '189.128.190.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(10, 1, '2025-09-29 21:44:59', NULL, '189.128.190.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'),
(11, 1, '2025-09-29 22:47:42', '2025-09-29 23:21:13', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(12, 1, '2025-09-29 23:23:08', '2025-09-29 23:23:25', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(13, 3, '2025-09-29 23:23:31', '2025-09-29 23:24:04', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(14, 1, '2025-09-29 23:24:11', NULL, '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(15, 1, '2025-09-30 00:45:30', '2025-09-30 00:53:59', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(16, 2, '2025-09-30 00:54:26', '2025-09-30 00:54:30', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(17, 3, '2025-09-30 00:54:37', '2025-09-30 00:54:49', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(18, 4, '2025-09-30 00:55:00', '2025-09-30 00:56:36', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(19, 1, '2025-09-30 00:56:41', '2025-09-30 01:35:39', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(20, 1, '2025-09-30 02:40:09', NULL, '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(21, 1, '2025-09-30 04:03:14', '2025-09-30 04:03:44', '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(22, 1, '2025-09-30 04:03:47', NULL, '189.128.190.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `plate` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `brand` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `capacity` decimal(8,2) DEFAULT NULL,
  `fuel_type` enum('gasoline','diesel','electric') COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura para la vista `route_orders`
--
DROP TABLE IF EXISTS `route_orders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fix360`@`localhost` SQL SECURITY DEFINER VIEW `route_orders`  AS SELECT `route_stops`.`id` AS `id`, `route_stops`.`route_id` AS `route_id`, `route_stops`.`order_id` AS `order_id`, `route_stops`.`stop_order` AS `stop_sequence`, `route_stops`.`estimated_arrival` AS `estimated_arrival`, `route_stops`.`actual_arrival` AS `actual_arrival`, `route_stops`.`status` AS `status`, `route_stops`.`delivery_status` AS `delivery_status`, `route_stops`.`notes` AS `notes`, `route_stops`.`delivery_notes` AS `delivery_notes`, `route_stops`.`delivered_by` AS `delivered_by`, `route_stops`.`stop_sequence` AS `sequence_order` FROM `route_stops` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_customers_active` (`is_active`);

--
-- Indices de la tabla `customer_surveys`
--
ALTER TABLE `customer_surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `idx_surveys_date` (`survey_date`);

--
-- Indices de la tabla `delivery_routes`
--
ALTER TABLE `delivery_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `idx_delivery_routes_date` (`route_date`);

--
-- Indices de la tabla `direct_sales`
--
ALTER TABLE `direct_sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_number` (`sale_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indices de la tabla `direct_sale_details`
--
ALTER TABLE `direct_sale_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `lot_id` (`lot_id`);

--
-- Indices de la tabla `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_lot` (`product_id`,`lot_id`),
  ADD KEY `lot_id` (`lot_id`),
  ADD KEY `idx_inventory_product` (`product_id`);

--
-- Indices de la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `lot_id` (`lot_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_movements_date` (`movement_date`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_orders_date` (`order_date`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indices de la tabla `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `lot_id` (`lot_id`);

--
-- Indices de la tabla `production_lots`
--
ALTER TABLE `production_lots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lot_number` (`lot_number`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_production_lots_expiry` (`expiry_date`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indices de la tabla `return_details`
--
ALTER TABLE `return_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `lot_id` (`lot_id`);

--
-- Indices de la tabla `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `idx_routes_date` (`route_date`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indices de la tabla `route_stops`
--
ALTER TABLE `route_stops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_route_stops_delivered_by` (`delivered_by`);

--
-- Indices de la tabla `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_sessions_user` (`user_id`),
  ADD KEY `idx_user_sessions_login` (`login_time`);

--
-- Indices de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate` (`plate`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `customer_surveys`
--
ALTER TABLE `customer_surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `delivery_routes`
--
ALTER TABLE `delivery_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direct_sales`
--
ALTER TABLE `direct_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direct_sale_details`
--
ALTER TABLE `direct_sale_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `production_lots`
--
ALTER TABLE `production_lots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `return_details`
--
ALTER TABLE `return_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `customer_surveys`
--
ALTER TABLE `customer_surveys`
  ADD CONSTRAINT `customer_surveys_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `customer_surveys_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `customer_surveys_ibfk_3` FOREIGN KEY (`sale_id`) REFERENCES `direct_sales` (`id`);

--
-- Filtros para la tabla `delivery_routes`
--
ALTER TABLE `delivery_routes`
  ADD CONSTRAINT `delivery_routes_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `direct_sales`
--
ALTER TABLE `direct_sales`
  ADD CONSTRAINT `direct_sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `direct_sales_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`),
  ADD CONSTRAINT `direct_sales_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `direct_sale_details`
--
ALTER TABLE `direct_sale_details`
  ADD CONSTRAINT `direct_sale_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `direct_sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `direct_sale_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `direct_sale_details_ibfk_3` FOREIGN KEY (`lot_id`) REFERENCES `production_lots` (`id`);

--
-- Filtros para la tabla `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`lot_id`) REFERENCES `production_lots` (`id`);

--
-- Filtros para la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`lot_id`) REFERENCES `production_lots` (`id`),
  ADD CONSTRAINT `inventory_movements_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_details_ibfk_3` FOREIGN KEY (`lot_id`) REFERENCES `production_lots` (`id`);

--
-- Filtros para la tabla `production_lots`
--
ALTER TABLE `production_lots`
  ADD CONSTRAINT `production_lots_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `production_lots_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_4` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_5` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_6` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Filtros para la tabla `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `direct_sales` (`id`),
  ADD CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `return_details`
--
ALTER TABLE `return_details`
  ADD CONSTRAINT `return_details_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `return_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `return_details_ibfk_3` FOREIGN KEY (`lot_id`) REFERENCES `production_lots` (`id`);

--
-- Filtros para la tabla `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `routes_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `routes_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `routes_ibfk_4` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `routes_ibfk_5` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `routes_ibfk_6` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `routes_ibfk_7` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Filtros para la tabla `route_stops`
--
ALTER TABLE `route_stops`
  ADD CONSTRAINT `fk_route_stops_delivered_by` FOREIGN KEY (`delivered_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `route_stops_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `route_stops_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Filtros para la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
