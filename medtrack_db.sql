-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 04, 2026 at 08:18 PM
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
-- Database: `medtrack_lite`
--

-- --------------------------------------------------------

--
-- Table structure for table `drugs`
--

CREATE TABLE `drugs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `batch_no` varchar(50) DEFAULT NULL,
  `mfg_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drugs`
--

INSERT INTO `drugs` (`id`, `name`, `category`, `quantity`, `supplier`, `batch_no`, `mfg_date`, `exp_date`, `description`, `price`, `created_at`, `status`) VALUES
(2, 'Mara moja', 'Tablet', 210, 'Supplier C', 'AH19189', '2024-01-07', '2026-01-12', 'painkiller', 10.00, '2026-01-01 15:08:46', 'active'),
(4, 'panadol', 'Tablet', 6, 'Supplier C', 'px19190', '2024-12-31', '2026-01-07', 'painkiller', 25.00, '2026-01-01 16:54:29', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed') DEFAULT 'sent',
  `last_sent` datetime DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `recipient`, `subject`, `message`, `sent_at`, `status`, `last_sent`, `type`) VALUES
(1, NULL, NULL, NULL, '2026-01-01 15:18:53', 'sent', '2026-01-01 00:00:00', 'general'),
(2, NULL, NULL, NULL, '2026-01-01 15:19:25', 'sent', '2026-01-01 00:00:00', 'general'),
(3, NULL, NULL, NULL, '2026-01-01 15:19:44', 'sent', '2026-01-01 00:00:00', 'general'),
(4, NULL, NULL, NULL, '2026-01-01 16:09:10', 'sent', '2026-01-01 00:00:00', 'general'),
(5, NULL, NULL, NULL, '2026-01-01 16:09:33', 'sent', '2026-01-01 00:00:00', 'general'),
(6, NULL, NULL, NULL, '2026-01-01 16:09:39', 'sent', '2026-01-01 00:00:00', 'general'),
(7, NULL, NULL, NULL, '2026-01-01 16:09:43', 'sent', '2026-01-01 00:00:00', 'general'),
(8, NULL, NULL, NULL, '2026-01-01 16:10:00', 'sent', '2026-01-01 00:00:00', 'general'),
(9, NULL, NULL, NULL, '2026-01-01 16:10:12', 'sent', '2026-01-01 00:00:00', 'general'),
(10, NULL, NULL, NULL, '2026-01-01 16:10:16', 'sent', '2026-01-01 00:00:00', 'general'),
(11, NULL, NULL, NULL, '2026-01-01 16:19:35', 'sent', '2026-01-01 00:00:00', 'general'),
(12, NULL, NULL, NULL, '2026-01-01 16:28:44', 'sent', '2026-01-01 00:00:00', 'general'),
(13, NULL, NULL, NULL, '2026-01-01 16:28:53', 'sent', '2026-01-01 00:00:00', 'general'),
(14, NULL, NULL, NULL, '2026-01-01 16:29:44', 'sent', '2026-01-01 00:00:00', 'general'),
(15, NULL, NULL, NULL, '2026-01-01 16:30:56', 'sent', '2026-01-01 00:00:00', 'general'),
(16, NULL, NULL, NULL, '2026-01-01 16:32:34', 'sent', '2026-01-01 00:00:00', 'general'),
(17, NULL, NULL, NULL, '2026-01-01 16:33:23', 'sent', '2026-01-01 00:00:00', 'general'),
(18, NULL, NULL, NULL, '2026-01-01 16:43:57', 'sent', '2026-01-01 00:00:00', 'general'),
(19, NULL, NULL, NULL, '2026-01-01 16:44:13', 'sent', '2026-01-01 00:00:00', 'general'),
(20, NULL, NULL, NULL, '2026-01-01 16:44:40', 'sent', '2026-01-01 00:00:00', 'general'),
(21, NULL, NULL, NULL, '2026-01-01 16:45:12', 'sent', '2026-01-01 00:00:00', 'general'),
(22, NULL, NULL, NULL, '2026-01-01 16:50:54', 'sent', '2026-01-01 00:00:00', 'general'),
(23, NULL, NULL, NULL, '2026-01-01 16:52:01', 'sent', '2026-01-01 00:00:00', 'general'),
(24, NULL, NULL, NULL, '2026-01-01 16:52:38', 'sent', '2026-01-01 00:00:00', 'general'),
(25, NULL, NULL, NULL, '2026-01-01 16:54:35', 'sent', '2026-01-01 00:00:00', 'general'),
(26, NULL, NULL, NULL, '2026-01-01 16:55:09', 'sent', '2026-01-01 00:00:00', 'general'),
(27, NULL, NULL, NULL, '2026-01-01 16:56:10', 'sent', '2026-01-01 00:00:00', 'general'),
(28, NULL, NULL, NULL, '2026-01-01 16:56:55', 'sent', '2026-01-01 00:00:00', 'general'),
(29, NULL, NULL, NULL, '2026-01-01 16:57:13', 'sent', '2026-01-01 00:00:00', 'general'),
(30, NULL, NULL, NULL, '2026-01-01 17:37:46', 'sent', '2026-01-01 00:00:00', 'general'),
(31, NULL, NULL, NULL, '2026-01-01 17:38:05', 'sent', '2026-01-01 00:00:00', 'general'),
(32, NULL, NULL, NULL, '2026-01-01 17:38:34', 'sent', '2026-01-01 00:00:00', 'general'),
(33, NULL, NULL, NULL, '2026-01-01 17:39:57', 'sent', '2026-01-01 00:00:00', 'general'),
(34, NULL, NULL, NULL, '2026-01-01 17:40:50', 'sent', '2026-01-01 00:00:00', 'general'),
(35, NULL, NULL, NULL, '2026-01-01 17:41:04', 'sent', '2026-01-01 00:00:00', 'general'),
(36, NULL, NULL, NULL, '2026-01-01 17:41:16', 'sent', '2026-01-01 00:00:00', 'general'),
(37, NULL, NULL, NULL, '2026-01-01 18:31:43', 'sent', '2026-01-01 00:00:00', 'general'),
(38, NULL, NULL, NULL, '2026-01-01 18:43:12', 'sent', '2026-01-01 00:00:00', 'general'),
(39, NULL, NULL, NULL, '2026-01-01 19:05:48', 'sent', '2026-01-01 00:00:00', 'general'),
(40, NULL, NULL, NULL, '2026-01-01 19:19:37', 'sent', '2026-01-01 00:00:00', 'general'),
(41, NULL, NULL, NULL, '2026-01-01 19:19:40', 'sent', '2026-01-01 00:00:00', 'general'),
(42, NULL, NULL, NULL, '2026-01-01 19:19:42', 'sent', '2026-01-01 00:00:00', 'general'),
(43, NULL, NULL, NULL, '2026-01-01 19:20:10', 'sent', '2026-01-01 00:00:00', 'general'),
(44, NULL, NULL, NULL, '2026-01-01 19:21:48', 'sent', '2026-01-01 00:00:00', 'general'),
(45, NULL, NULL, NULL, '2026-01-01 19:22:03', 'sent', '2026-01-01 00:00:00', 'general'),
(46, NULL, NULL, NULL, '2026-01-01 19:30:09', 'sent', '2026-01-01 00:00:00', 'general'),
(47, NULL, NULL, NULL, '2026-01-01 19:30:12', 'sent', '2026-01-01 00:00:00', 'general'),
(48, NULL, NULL, NULL, '2026-01-01 19:30:21', 'sent', '2026-01-01 00:00:00', 'general'),
(49, NULL, NULL, NULL, '2026-01-01 19:35:21', 'sent', '2026-01-01 00:00:00', 'general'),
(50, NULL, NULL, NULL, '2026-01-01 19:35:34', 'sent', '2026-01-01 00:00:00', 'general'),
(51, NULL, NULL, NULL, '2026-01-01 19:35:41', 'sent', '2026-01-01 00:00:00', 'general'),
(52, NULL, NULL, NULL, '2026-01-01 19:35:54', 'sent', '2026-01-01 00:00:00', 'general'),
(53, NULL, NULL, NULL, '2026-01-01 19:36:05', 'sent', '2026-01-01 00:00:00', 'general'),
(54, NULL, NULL, NULL, '2026-01-01 19:36:08', 'sent', '2026-01-01 00:00:00', 'general'),
(55, NULL, NULL, NULL, '2026-01-01 19:36:10', 'sent', '2026-01-01 00:00:00', 'general'),
(56, NULL, NULL, NULL, '2026-01-01 19:36:13', 'sent', '2026-01-01 00:00:00', 'general'),
(57, NULL, NULL, NULL, '2026-01-01 19:44:14', 'sent', '2026-01-01 00:00:00', 'general'),
(58, NULL, NULL, NULL, '2026-01-01 19:54:25', 'sent', '2026-01-01 00:00:00', 'expiry'),
(59, NULL, NULL, NULL, '2026-01-01 19:54:38', 'sent', '2026-01-01 00:00:00', 'expiry'),
(60, NULL, NULL, NULL, '2026-01-01 20:03:13', 'sent', '2026-01-01 00:00:00', 'expiry'),
(61, NULL, NULL, NULL, '2026-01-01 20:03:17', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(62, NULL, NULL, NULL, '2026-01-01 20:03:22', 'sent', '2026-01-01 00:00:00', 'expiry'),
(63, NULL, NULL, NULL, '2026-01-01 20:03:26', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(64, NULL, NULL, NULL, '2026-01-01 20:03:56', 'sent', '2026-01-01 00:00:00', 'expiry'),
(65, NULL, NULL, NULL, '2026-01-01 20:04:00', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(66, NULL, NULL, NULL, '2026-01-01 20:19:15', 'sent', '2026-01-01 00:00:00', 'expiry'),
(67, NULL, NULL, NULL, '2026-01-01 20:19:18', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(68, NULL, NULL, NULL, '2026-01-01 20:19:23', 'sent', '2026-01-01 00:00:00', 'expiry'),
(69, NULL, NULL, NULL, '2026-01-01 20:19:27', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(70, NULL, NULL, NULL, '2026-01-01 20:19:49', 'sent', '2026-01-01 00:00:00', 'expiry'),
(71, NULL, NULL, NULL, '2026-01-01 20:19:53', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(72, NULL, NULL, NULL, '2026-01-01 20:20:52', 'sent', '2026-01-01 00:00:00', 'expiry'),
(73, NULL, NULL, NULL, '2026-01-01 20:20:56', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(74, NULL, NULL, NULL, '2026-01-01 20:21:33', 'sent', '2026-01-01 00:00:00', 'expiry'),
(75, NULL, NULL, NULL, '2026-01-01 20:21:37', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(76, NULL, NULL, NULL, '2026-01-01 20:22:19', 'sent', '2026-01-01 00:00:00', 'expiry'),
(77, NULL, NULL, NULL, '2026-01-01 20:22:23', 'sent', '2026-01-01 00:00:00', 'low_stock'),
(78, NULL, NULL, NULL, '2026-01-03 18:11:26', 'sent', '2026-01-03 00:00:00', 'expiry'),
(79, NULL, NULL, NULL, '2026-01-03 18:11:29', 'sent', '2026-01-03 00:00:00', 'low_stock'),
(80, NULL, NULL, NULL, '2026-01-04 11:05:43', 'sent', '2026-01-04 00:00:00', 'expiry'),
(81, NULL, NULL, NULL, '2026-01-04 11:05:47', 'sent', '2026-01-04 00:00:00', 'low_stock');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `sold_by` int(11) DEFAULT NULL,
  `sold_at` datetime DEFAULT current_timestamp(),
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `drug_id`, `quantity_sold`, `sale_date`, `sold_by`, `sold_at`, `unit_price`, `total_price`) VALUES
(14, 2, 4, '2026-01-03 21:00:00', 2, '2026-01-04 15:09:52', 10.00, 40.00),
(22, 2, 3, '2026-01-03 21:00:00', 2, '2026-01-04 21:43:06', 10.00, 30.00),
(23, 2, 10, '2026-01-03 21:00:00', 3, '2026-01-04 22:07:20', 10.00, 100.00),
(24, 4, 1, '2026-01-03 21:00:00', 3, '2026-01-04 22:07:20', 25.00, 25.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `status`) VALUES
(1, 'admin', '$2y$10$ucfILdCnPCDVommolqApzezMOU/M0EYv6dbd6iaz4B8guN0z4q2GS', 'admin', '2026-01-01 13:51:43', 'active'),
(2, 'staff1', '$2y$10$JotROXgSiG/djswJK0n4ue/fD6F7rxJZOXXN2UXIC72zhhrdQGgse', 'staff', '2026-01-01 15:24:05', 'active'),
(3, 'staff2', '$2y$10$xak43R2Ir5NbUQMwjrs9F.94EIYWMdyeI7FIwSMh98uhQ/KiIOUCa', 'staff', '2026-01-01 16:45:05', 'active'),
(4, 'staff3', '$2y$10$jvF8jp1SY76CTnTGw/ZvNOBFw.PQY7dlGhrXrYVB29vEBy7WRuRim', 'staff', '2026-01-01 16:51:55', 'inactive'),
(6, 'ABDI', '$2y$10$viNUZIf8Q7Mkf8i.Ukmuru2Ur1M98uuT1b0LfDufzMwlTWszqbfse', 'staff', '2026-01-04 13:44:55', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `drugs`
--
ALTER TABLE `drugs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `drug_id` (`drug_id`),
  ADD KEY `sold_by` (`sold_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `drugs`
--
ALTER TABLE `drugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`drug_id`) REFERENCES `drugs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`sold_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
