-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 23, 2025 at 02:25 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tama_kp`
--

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Pelurusan KPI', '2025-07-17 04:52:26', '2025-07-17 07:36:42'),
(2, 'Fallout CONS/EBIS', '2025-07-17 04:52:26', '2025-07-17 04:52:26'),
(3, 'UP ODP', '2025-07-17 04:52:26', '2025-07-17 04:52:26'),
(4, 'Cek Port BT', '2025-07-17 04:52:26', '2025-07-17 04:52:26'),
(5, 'Val Tiang', '2025-07-17 04:52:26', '2025-07-17 04:52:26'),
(6, 'ODP Kendala', '2025-07-17 04:52:26', '2025-07-17 04:52:26'),
(7, 'Validasi FTM', '2025-07-17 04:52:26', '2025-07-17 04:52:26'),
(8, 'Pelurusan GDOC HS Fallout UIM DAMAN', '2025-07-17 04:52:26', '2025-07-28 03:31:52'),
(9, 'Pelurusan EBIS', '2025-07-17 04:52:26', '2025-07-17 04:52:26'),
(10, 'E2E', '2025-07-17 04:52:26', '2025-07-21 23:27:13'),
(14, 'Pelurusan GDOC ASO', '2025-07-28 03:34:24', '2025-07-28 03:34:24');

-- --------------------------------------------------------

--
-- Table structure for table `task_achievements`
--

CREATE TABLE `task_achievements` (
  `id` int NOT NULL,
  `user_task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `work_orders` int DEFAULT '0',
  `work_orders_completed` int DEFAULT '0',
  `progress_int` int DEFAULT NULL,
  `notes` text,
  `kendala` text,
  `status` enum('In Progress','Achieved','Non Achieved') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_achievements`
--

INSERT INTO `task_achievements` (`id`, `user_task_id`, `user_id`, `work_orders`, `work_orders_completed`, `progress_int`, `notes`, `kendala`, `status`, `created_at`) VALUES
(34, 76, 10, 40, 40, 100, NULL, '', 'Achieved', '2025-07-15 08:54:18'),
(35, 88, 11, 40, 40, 100, NULL, '', 'Achieved', '2025-07-15 08:54:54'),
(36, 79, 13, 60, 60, 100, NULL, '', 'Achieved', '2025-07-15 08:56:02'),
(37, 97, 14, 35, 35, 100, NULL, '', 'Achieved', '2025-07-15 08:56:37'),
(38, 96, 14, 7, 7, 100, NULL, '', 'Achieved', '2025-07-15 08:56:53'),
(39, 95, 14, 8, 8, 100, NULL, '', 'Achieved', '2025-07-15 08:57:06'),
(40, 86, 3, 50, 25, 50, NULL, 'Technical problems', 'Non Achieved', '2025-07-15 09:05:38'),
(41, 89, 15, 19, 19, 100, NULL, 'Other', 'Achieved', '2025-07-15 09:06:49'),
(42, 82, 15, 40, 30, 75, NULL, 'Technical problems', 'Non Achieved', '2025-07-15 09:07:15'),
(43, 90, 15, 2, 2, 100, NULL, '', 'Achieved', '2025-07-15 09:08:36'),
(44, 83, 16, 5, 5, 100, NULL, '', 'Achieved', '2025-07-15 09:09:18'),
(45, 85, 16, 2, 2, 100, NULL, '', 'Achieved', '2025-07-15 09:09:30'),
(46, 84, 16, 3, 3, 100, NULL, '', 'Achieved', '2025-07-15 09:09:44'),
(47, 94, 16, 20, 20, 100, NULL, '', 'Achieved', '2025-07-15 09:09:59'),
(48, 98, 13, 20, 20, 100, NULL, '', 'Achieved', '2025-07-15 09:11:51'),
(50, 79, 13, 110, 110, 100, NULL, '1 returned', 'Achieved', '2025-07-16 09:14:49'),
(51, 88, 11, 40, 40, 100, NULL, '', 'Achieved', '2025-07-16 09:15:27'),
(52, 92, 12, 40, 40, 100, NULL, '', 'Achieved', '2025-07-16 09:16:01'),
(53, 93, 2, 30, 30, 100, NULL, '', 'Achieved', '2025-07-16 09:16:59'),
(54, 94, 16, 14, 14, 100, NULL, '', 'Achieved', '2025-07-16 09:17:41'),
(55, 85, 16, 12, 12, 100, NULL, '', 'Achieved', '2025-07-16 09:17:54'),
(56, 84, 16, 1, 1, 100, NULL, '', 'Achieved', '2025-07-16 09:18:05'),
(57, 83, 16, 10, 10, 100, NULL, '', 'Achieved', '2025-07-16 09:18:18'),
(58, 86, 3, 50, 30, 60, NULL, '', 'Non Achieved', '2025-07-16 09:19:16'),
(59, 97, 14, 31, 31, 100, NULL, '', 'Achieved', '2025-07-16 09:20:06'),
(60, 96, 14, 32, 32, 100, NULL, '', 'Achieved', '2025-07-16 09:20:17'),
(61, 95, 14, 7, 7, 100, NULL, '', 'Achieved', '2025-07-16 09:20:32'),
(62, 97, 14, 21, 21, 100, NULL, '', 'Achieved', '2025-07-17 09:55:59'),
(64, 96, 14, 28, 28, 100, NULL, '16 reserved', 'Achieved', '2025-07-17 10:07:22'),
(65, 95, 14, 14, 14, 100, NULL, '', 'Achieved', '2025-07-17 10:07:39'),
(66, 88, 11, 40, 40, 100, NULL, '', 'Achieved', '2025-07-17 10:08:27'),
(67, 76, 10, 40, 40, 100, NULL, '', 'Achieved', '2025-07-17 10:09:05'),
(68, 78, 12, 5, 5, 100, NULL, '', 'Achieved', '2025-07-17 10:09:41'),
(70, 92, 12, 32, 32, 100, NULL, '', 'Achieved', '2025-07-17 10:16:19'),
(72, 79, 13, 150, 150, 100, NULL, '', 'Achieved', '2025-07-17 10:18:22'),
(73, 98, 13, 20, 20, 100, NULL, '', 'Achieved', '2025-07-17 10:18:33'),
(74, 93, 2, 35, 35, 100, NULL, '', 'Achieved', '2025-07-17 10:19:25'),
(75, 86, 3, 50, 30, 60, NULL, '', 'Non Achieved', '2025-07-17 10:20:18'),
(76, 83, 16, 5, 5, 100, NULL, '', 'Achieved', '2025-07-17 10:21:00'),
(77, 94, 16, 18, 18, 100, NULL, '', 'Achieved', '2025-07-17 10:40:11'),
(78, 85, 16, 8, 8, 100, NULL, 'reserved 2', 'Achieved', '2025-07-17 10:40:30'),
(79, 84, 16, 2, 2, 100, NULL, '', 'Achieved', '2025-07-17 10:40:40'),
(80, 77, 11, 1, 1, 100, NULL, '', 'Achieved', '2025-07-18 10:42:07'),
(81, 76, 10, 40, 40, 100, NULL, '', 'Achieved', '2025-07-18 10:42:33'),
(82, 99, 14, 169, 167, 99, NULL, 'returned 2', 'Non Achieved', '2025-07-18 10:43:30'),
(83, 82, 15, 40, 30, 75, NULL, '', 'Non Achieved', '2025-07-18 10:44:14'),
(85, 78, 12, 4, 4, 100, NULL, '', 'Achieved', '2025-07-18 10:45:01'),
(86, 109, 3, 26, 26, 100, NULL, '', 'Achieved', '2025-07-18 10:55:40'),
(87, 108, 3, 13, 13, 100, NULL, 'reserved 16', 'Achieved', '2025-07-18 10:56:11'),
(88, 107, 3, 9, 9, 100, NULL, '', 'Achieved', '2025-07-18 10:56:23'),
(89, 109, 3, 30, 30, 100, NULL, '', 'Achieved', '2025-07-21 10:57:15'),
(90, 108, 3, 4, 4, 100, NULL, '', 'Achieved', '2025-07-21 10:57:23'),
(91, 107, 3, 1, 1, 100, NULL, '', 'Achieved', '2025-07-21 10:57:31'),
(92, 86, 3, 50, 15, 30, NULL, 'Tool problem', 'Non Achieved', '2025-07-21 10:57:56'),
(94, 92, 12, 25, 25, 100, NULL, '', 'Achieved', '2025-07-21 10:59:16'),
(95, 78, 12, 4, 4, 100, NULL, '', 'Achieved', '2025-07-21 10:59:26'),
(96, 82, 15, 40, 30, 75, NULL, '', 'Non Achieved', '2025-07-21 11:00:10'),
(97, 99, 14, 169, 167, 99, NULL, 'returned 2', 'Non Achieved', '2025-07-21 11:01:05'),
(98, 77, 11, 1, 1, 100, NULL, '', 'Achieved', '2025-07-21 11:01:35'),
(99, 76, 10, 40, 40, 100, NULL, '', 'Achieved', '2025-07-21 11:02:20'),
(100, 85, 16, 16, 16, 100, NULL, 'reserved 5', 'Achieved', '2025-07-21 11:03:09'),
(101, 84, 16, 11, 11, 100, NULL, '', 'Achieved', '2025-07-21 11:03:27'),
(102, 83, 16, 14, 14, 100, NULL, '', 'Achieved', '2025-07-21 11:03:38'),
(108, 86, 3, 50, 30, 60, NULL, '', 'Non Achieved', '2025-07-23 11:15:45'),
(109, 91, 15, 7, 7, 100, NULL, '', 'Achieved', '2025-07-23 11:16:16'),
(110, 89, 15, 40, 40, 100, NULL, '', 'Achieved', '2025-07-23 11:16:27'),
(111, 82, 15, 40, 40, 100, NULL, '', 'Achieved', '2025-07-23 11:16:35'),
(112, 76, 10, 40, 40, 100, NULL, '', 'Achieved', '2025-07-23 11:17:00'),
(113, 88, 11, 40, 40, 100, NULL, '', 'Achieved', '2025-07-23 11:17:23'),
(114, 98, 13, 15, 15, 100, NULL, '', 'Achieved', '2025-07-23 11:17:59'),
(115, 79, 13, 84, 84, 100, NULL, '', 'Achieved', '2025-07-23 11:18:27'),
(116, 97, 14, 1, 1, 100, NULL, '', 'Achieved', '2025-07-23 11:19:19'),
(117, 81, 14, 50, 30, 60, NULL, '', 'Non Achieved', '2025-07-23 11:19:32'),
(118, 92, 12, 30, 30, 100, NULL, '', 'Achieved', '2025-07-23 11:19:57'),
(120, 105, 2, 35, 35, 100, NULL, '', 'Achieved', '2025-07-23 11:20:46'),
(121, 103, 2, 10, 10, 100, NULL, '', 'Achieved', '2025-07-23 11:20:56'),
(122, 104, 2, 19, 19, 100, NULL, 'reserved 12', 'Achieved', '2025-07-23 11:22:46'),
(123, 86, 3, 50, 40, 80, NULL, 'piket jam istirahat', 'Non Achieved', '2025-07-24 11:24:00'),
(124, 79, 13, 160, 160, 100, NULL, '', 'Achieved', '2025-07-24 11:24:32'),
(125, 76, 10, 40, 40, 100, NULL, '', 'Achieved', '2025-07-24 11:24:57'),
(126, 97, 14, 38, 38, 100, NULL, '', 'Achieved', '2025-07-24 11:25:30'),
(127, 96, 14, 34, 34, 100, NULL, 'reserved 11', 'Achieved', '2025-07-24 11:25:56'),
(128, 95, 14, 4, 4, 100, NULL, '', 'Achieved', '2025-07-24 11:26:04'),
(129, 96, 14, 14, 14, 100, NULL, 'reserved 7', 'Achieved', '2025-07-25 11:26:51'),
(130, 97, 14, 42, 42, 100, NULL, '', 'Achieved', '2025-07-25 11:27:04'),
(131, 95, 14, 13, 13, 100, NULL, '', 'Achieved', '2025-07-25 11:27:13'),
(132, 76, 10, 40, 40, 100, NULL, '', 'Achieved', '2025-07-25 11:27:43'),
(133, 79, 13, 153, 153, 100, NULL, '', 'Achieved', '2025-07-25 11:28:12'),
(134, 98, 13, 15, 15, 100, NULL, '', 'Achieved', '2025-07-25 11:28:29'),
(135, 86, 3, 50, 50, 100, NULL, '', 'Achieved', '2025-07-25 11:28:56'),
(136, 108, 3, 3, 3, 100, NULL, '', 'Achieved', '2025-07-25 11:29:07'),
(137, 91, 15, 8, 8, 100, NULL, '', 'Achieved', '2025-07-25 11:29:39'),
(138, 89, 15, 20, 20, 100, NULL, '', 'Achieved', '2025-07-25 11:29:50'),
(139, 82, 15, 40, 10, 25, NULL, '', 'Non Achieved', '2025-07-25 11:29:59'),
(140, 92, 12, 40, 40, 100, NULL, '', 'Achieved', '2025-07-25 11:30:30'),
(141, 76, 10, 40, 40, 100, NULL, '', 'Achieved', '2025-07-28 09:23:17'),
(142, 105, 2, 25, 23, 92, NULL, 'Tool problem', 'Non Achieved', '2025-07-29 09:48:54'),
(143, 87, 2, 40, 40, 100, NULL, '', 'Achieved', '2025-07-29 09:49:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee') DEFAULT 'employee',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `nik`, `phone`, `email`, `gender`, `profile_photo`, `status`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Nurul Fadhillah', '960201', '+62 852 80426789', 'nurulfadhillah@gmail.com', NULL, NULL, 'Active', '$2y$10$IRPUKFiz90pQo9DnjJ.RLerw4yZfJkd6dhSpf1r/u.jXxuiqC4D.y', 'admin', '2025-07-17 04:17:22', '2025-08-23 14:21:08'),
(2, 'Imam Sutrisno', '24910020', '082279604194', 'imamsutrisno@gmail.com', 'male', 'profile_2_1753782792.png', 'Active', '$2y$10$5xyB6xRI4WblxLIsDAtC8Oer.pp2.XnMwgdY6gapoTwyjANgonNk.', 'employee', '2025-07-17 04:18:27', '2025-08-23 14:14:17'),
(3, 'Herlando', '24940026', '082383588676', 'herlando@gmail.com', 'male', NULL, 'Active', '$2y$10$tJ64vEFC5J689Ai3XniNYetfBKHqqAMNRRmkibbBwsJyjR5hKvKG6', 'employee', '2025-07-17 04:25:57', '2025-08-23 14:14:17'),
(9, 'Muhammad Iqbal', '830100', '081200000000', 'muhammadiqbal@example.com', 'male', NULL, 'Active', '$2y$10$5eIkZ2foBITZecS.uNPYaegh0wJoruyMaQbuYXMAzli1O/FDUxzRm', 'manager', '2025-07-22 01:38:01', '2025-08-23 14:18:42'),
(10, 'Fajar Rafiudin', '24950029', '082177687813', 'fajarrafiudin@gmail.com', 'male', NULL, 'Active', '$2y$10$WbKq397QF07BKXPnHdPF9OiXUHfFW.uz2sMWcpNUQ8yVVjhsYFJjS', 'employee', '2025-07-22 02:18:16', '2025-08-23 14:14:17'),
(11, 'Odi Rinanda', '24000016', '081373624022', 'odirinanda@gmail.com', 'male', NULL, 'Active', '$2y$10$UzBDatbCq4VwRL7pBgbs1eSjfBgTPQD7UMJZueXZGAjiiRuZvtoRm', 'employee', '2025-07-22 02:28:13', '2025-08-23 14:14:17'),
(12, 'Yosef Tobir', '24990026', '081368039861', 'yoseptobir@gmail.com', 'male', NULL, 'Active', '$2y$10$WJDBVEt2zTJNpsdzv7t1/OYX0SLI3Ku8MGvRukHUDJ1zpC5BATgHq', 'employee', '2025-07-22 02:28:13', '2025-08-23 14:14:17'),
(13, 'M. Nuril Adinata', '24020011', '089626075244', 'mnuriladinata@gmail.com', 'male', NULL, 'Active', '$2y$10$.uC.T5p22C0QwsEKboL1wOtMKLEO58OhZ5fzlSzv0qUet3V20Cq.C', 'employee', '2025-07-22 02:28:13', '2025-08-23 14:14:17'),
(14, 'Aji Pangestu', '24990027', '085357032702', 'ajipangestu@gmail.com', 'male', NULL, 'Active', '$2y$10$wTD63c//IBX9aRw1SM.OC.ucOYp0Up7IGLon135MyQYB4TJR3MX3m', 'employee', '2025-07-22 02:28:13', '2025-08-23 14:14:17'),
(15, 'Erik Efendi', '24910021', '082294496177', 'erikefendi@gmail.com', 'male', NULL, 'Active', '$2y$10$qTNvwswGtZY1RT4AxJKx2OPjDgH61hsDYxKwGxR6WQ2FVv8MFVaNG', 'employee', '2025-07-22 02:28:13', '2025-08-23 14:14:17'),
(16, 'Eddo Bentano', '24900014', '081289755873', 'eddobentano@gmail.com', 'male', NULL, 'Active', '$2y$10$SpyE/3LkOZIVtOSc28opUeIvtR.FoXSPhd/YTbFE0q6J3pol6Hu92', 'employee', '2025-07-22 02:28:13', '2025-08-23 14:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_tasks`
--

CREATE TABLE `user_tasks` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `task_id` int NOT NULL,
  `task_type` enum('numeric','textual') NOT NULL DEFAULT 'numeric',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `target_int` int DEFAULT NULL,
  `target_str` varchar(255) DEFAULT NULL,
  `progress_int` int DEFAULT '0',
  `total_completed` int DEFAULT '0',
  `status` enum('In Progress','Achieved','Non Achieved') DEFAULT 'In Progress',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_tasks`
--

INSERT INTO `user_tasks` (`id`, `user_id`, `task_id`, `task_type`, `start_date`, `end_date`, `description`, `target_int`, `target_str`, `progress_int`, `total_completed`, `status`, `created_at`, `updated_at`) VALUES
(76, 10, 10, 'numeric', '2025-07-15', '2025-07-31', 'web access quality', 40, NULL, 100, 320, 'In Progress', '2025-07-14 04:45:03', '2025-07-28 09:23:17'),
(77, 11, 7, 'textual', '2025-07-15', '2025-07-31', 'web access quality', NULL, '1 RACK EA , 1 RACK OA', 100, 2, 'In Progress', '2025-07-14 04:46:04', '2025-07-21 11:01:35'),
(78, 12, 9, 'textual', '2025-07-15', '2025-07-31', 'gdoc ebis, dalapa ', NULL, 'Semua wo tersolusikan', 100, 13, 'In Progress', '2025-07-14 04:47:29', '2025-07-21 10:59:26'),
(79, 13, 5, 'textual', '2025-07-15', '2025-07-31', 'web access quality', NULL, 'Semua fo tersolusikan', 100, 717, 'In Progress', '2025-07-14 04:50:17', '2025-07-25 11:28:12'),
(80, 13, 6, 'textual', '2025-07-15', '2025-07-31', 'web access quality', NULL, 'Semua fo tersolusikan', 0, 0, 'In Progress', '2025-07-14 04:50:42', '2025-07-14 04:50:42'),
(81, 14, 1, 'numeric', '2025-07-15', '2025-07-31', 'web access quality', 50, NULL, 60, 30, 'In Progress', '2025-07-14 04:51:41', '2025-07-23 11:19:32'),
(82, 15, 10, 'numeric', '2025-07-15', '2025-07-31', 'web access quality', 40, NULL, 70, 140, 'In Progress', '2025-07-14 04:52:12', '2025-07-25 11:29:59'),
(83, 16, 2, 'textual', '2025-07-15', '2025-07-31', '', NULL, 'Semua fo tersolusikan', 100, 34, 'In Progress', '2025-07-14 07:14:17', '2025-07-21 11:03:38'),
(84, 16, 4, 'textual', '2025-07-15', '2025-07-31', '', NULL, 'Semua fo tersolusikan', 100, 17, 'In Progress', '2025-07-14 07:14:42', '2025-07-21 11:03:27'),
(85, 16, 3, 'textual', '2025-07-15', '2025-07-31', '', NULL, 'Semua fo tersolusikan', 100, 38, 'In Progress', '2025-07-14 07:15:15', '2025-07-21 11:03:09'),
(86, 3, 1, 'numeric', '2025-07-15', '2025-07-31', '', 50, NULL, 63, 220, 'In Progress', '2025-07-14 07:17:59', '2025-07-25 11:28:56'),
(87, 2, 8, 'numeric', '2025-07-15', '2025-07-31', '-', 40, NULL, 100, 40, 'In Progress', '2025-07-14 07:20:50', '2025-07-29 09:49:51'),
(88, 11, 10, 'textual', '2025-07-15', '2025-07-31', 'utamain val ftm', NULL, 'menyesuaikan tidak ada wo', 100, 160, 'In Progress', '2025-07-14 07:33:35', '2025-07-23 11:17:23'),
(89, 15, 2, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 79, 'In Progress', '2025-07-14 07:34:58', '2025-07-25 11:29:50'),
(90, 15, 4, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 2, 'In Progress', '2025-07-14 07:36:49', '2025-07-15 09:08:36'),
(91, 15, 3, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 15, 'In Progress', '2025-07-14 07:38:01', '2025-07-25 11:29:39'),
(92, 12, 10, 'textual', '2025-07-15', '2025-07-31', '-', NULL, '40 untuk ebis 0', 100, 167, 'In Progress', '2025-07-14 07:39:29', '2025-07-25 11:30:30'),
(93, 2, 10, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Menyesuaikan tidak ada wo', 100, 65, 'In Progress', '2025-07-14 07:40:18', '2025-07-17 10:19:25'),
(94, 16, 1, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'menyesuikan wo', 100, 52, 'In Progress', '2025-07-14 07:41:20', '2025-07-17 10:40:11'),
(95, 14, 4, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 46, 'In Progress', '2025-07-14 07:42:04', '2025-07-25 11:27:13'),
(96, 14, 3, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 115, 'In Progress', '2025-07-14 07:42:44', '2025-07-25 11:26:51'),
(97, 14, 2, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 168, 'In Progress', '2025-07-14 07:43:32', '2025-07-25 11:27:04'),
(98, 13, 10, 'textual', '2025-07-15', '2025-07-31', 'menyesuaikan tugas utama', NULL, '40 untuk utama 0', 100, 70, 'In Progress', '2025-07-14 07:44:51', '2025-07-25 11:28:29'),
(99, 14, 5, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 99, 334, 'In Progress', '2025-07-14 08:21:04', '2025-07-21 11:01:05'),
(103, 2, 4, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 10, 'In Progress', '2025-07-14 08:30:46', '2025-07-23 11:20:56'),
(104, 2, 3, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 19, 'In Progress', '2025-07-14 08:31:10', '2025-07-23 11:22:46'),
(105, 2, 2, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 96, 58, 'In Progress', '2025-07-14 08:31:34', '2025-07-29 09:48:54'),
(107, 3, 4, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 10, 'In Progress', '2025-07-14 10:53:52', '2025-07-21 10:57:31'),
(108, 3, 3, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 20, 'In Progress', '2025-07-14 10:54:19', '2025-07-25 11:29:07'),
(109, 3, 2, 'textual', '2025-07-15', '2025-07-31', '-', NULL, 'Semua fo tersolusikan', 100, 56, 'In Progress', '2025-07-14 10:54:45', '2025-07-21 10:57:15'),
(111, 3, 1, 'numeric', '2025-08-13', '2025-08-20', '-', 50, NULL, 0, 0, 'In Progress', '2025-08-13 08:28:20', '2025-08-13 08:28:20');

--
-- Triggers `user_tasks`
--
DELIMITER $$
CREATE TRIGGER `check_target_consistency_insert` BEFORE INSERT ON `user_tasks` FOR EACH ROW BEGIN 
    IF NEW.task_type = 'numeric' AND (NEW.target_int IS NULL OR NEW.target_str IS NOT NULL) THEN 
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Numeric task type must have target_int value and target_str must be NULL'; 
    END IF; 
    
    IF NEW.task_type = 'textual' AND (NEW.target_str IS NULL OR NEW.target_int IS NOT NULL) THEN 
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Textual task type must have target_str value and target_int must be NULL'; 
    END IF; 
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `check_target_consistency_update` BEFORE UPDATE ON `user_tasks` FOR EACH ROW BEGIN 
    IF NEW.task_type = 'numeric' AND (NEW.target_int IS NULL OR NEW.target_str IS NOT NULL) THEN 
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Numeric task type must have target_int value and target_str must be NULL'; 
    END IF; 
    
    IF NEW.task_type = 'textual' AND (NEW.target_str IS NULL OR NEW.target_int IS NOT NULL) THEN 
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Textual task type must have target_str value and target_int must be NULL'; 
    END IF; 
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_achievements`
--
ALTER TABLE `task_achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_task_id` (`user_task_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_tasks`
--
ALTER TABLE `user_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `task_id` (`task_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `task_achievements`
--
ALTER TABLE `task_achievements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_tasks`
--
ALTER TABLE `user_tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `task_achievements`
--
ALTER TABLE `task_achievements`
  ADD CONSTRAINT `task_achievements_ibfk_1` FOREIGN KEY (`user_task_id`) REFERENCES `user_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tasks`
--
ALTER TABLE `user_tasks`
  ADD CONSTRAINT `user_tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_tasks_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
