-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 11:41 PM
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
-- Database: `vote`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `posted_by` varchar(100) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `posted_by`, `start_date`, `end_date`, `created_at`, `read_status`) VALUES
(1, 'dsdv', 'asD', 'staff_user', '2025-04-30 01:47:00', '2025-05-16 01:47:00', '2025-05-11 22:47:31', 1),
(14, 'jhf', 'sdj', 'staff_user', '2025-05-21 21:25:00', '2025-05-22 21:26:00', '2025-05-12 18:26:46', 1),
(15, 'sad', 'dc', 'staff_user', '2025-05-21 21:33:00', '2025-05-22 21:33:00', '2025-05-12 18:33:09', 1),
(16, 'qqq', 'qqq', 'staff_user', '2025-05-21 21:36:00', '2025-05-23 21:36:00', '2025-05-12 18:36:52', 1),
(17, 'dfvvsc', 'v dcx', 'staff_user', '2025-05-14 23:03:00', '2025-05-17 23:03:00', '2025-05-12 20:03:47', 1),
(18, 'presedant election', 'be ready', 'staff_user', '2025-05-14 22:29:00', '2025-05-15 22:29:00', '2025-05-16 19:29:35', 1),
(19, 'rep election', 'be ready', 'staff_user', '2025-05-14 09:49:00', '2025-05-08 09:50:00', '2025-05-19 06:50:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

CREATE TABLE `candidate` (
  `candidate_id` int(11) NOT NULL,
  `position` int(2) DEFAULT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `year_level` varchar(100) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `img` varchar(100) NOT NULL,
  `party` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate`
--

INSERT INTO `candidate` (`candidate_id`, `position`, `firstname`, `lastname`, `year_level`, `gender`, `img`, `party`) VALUES
(27, 7, 'Absir', 'Mulugeta', '1st Year', 'Male', 'upload/67e93801584be_amu logo.gif', 'Avs'),
(28, 7, 'Absir', 'Tesfaye', '2nd Year', 'Male', 'upload/67e93e72ba4f0_photo_2024-11-18_17-09-11.jpg', 'aaa'),
(29, 7, 'lema', 'shita', '3rd Year', 'Female', 'upload/67e9478a5f706_photo_2024-11-18_17-28-03.jpg', 'sema'),
(30, 9, 'demise', 'chernt', '4th Year', 'Female', 'upload/67f031cde9fe4_photo_2025-01-07_13-54-10.jpg', 'prr'),
(31, 6, 'later', 'come', '4th Year', 'Female', 'upload/67f0320f5c6e6_amu logo.gif', 'tech'),
(32, 6, 'Absir', 'Mulugeta', '1st Year', 'Male', 'upload/67f032c122efb_amu logo.gif', 'Absir'),
(33, 6, 'Absir', 'Tesfaye', '3rd Year', 'Male', 'upload/67f032e0b11ae_amu logo.gif', 'sema'),
(36, 8, 'mmm', 'kkk', '4th Year', 'Female', 'upload/67f5feed32c8f_amu logo.gif', 'teshome'),
(37, 7, 'Absir', 'Mulugeta', '2nd Year', 'Male', 'upload/681f258f6c298_download (1).jpg', 'Absir'),
(38, 12, 'lkjhv', 'nmbn', '1st Year', 'Female', 'upload/6821134d1fb45_Screenshot 2025-05-10 190130.png', 'kjlkhvbc'),
(39, 6, 'wrgvsdgwr', 'wrgRW', '3rd Year', 'Male', 'upload/682639d0a68c9_Grey Orange Modern Circle Class Logo.png', 'wrgr'),
(40, 6, 'ACF', 'DSC', '4th Year', 'Male', 'upload/68263a15bb347_Grey Orange Modern Circle Class Logo.png', 'SSXSA');

-- --------------------------------------------------------

--
-- Table structure for table `ids`
--

CREATE TABLE `ids` (
  `id_number` varchar(100) NOT NULL,
  `date` datetime DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ids`
--

INSERT INTO `ids` (`id_number`, `date`, `firstname`, `lastname`, `username`) VALUES
('NSR/01/01', '2025-05-18 14:50:28', 'aa', 'aa', 'NSR.01.01'),
('NSR/02/02', '2025-05-18 14:47:56', 'aa', 'aa', 'NSR.02.02'),
('NSR/11/11', '2025-05-10 17:00:33', 'aa', 'aa', 'NSR.11.11'),
('NSR/1111/11', '2025-05-16 16:33:00', 'aa', 'aa', 'NSR.1111.11'),
('NSR/12/12', '2025-05-09 20:27:42', 'aa', 'aa', 'NSR.12.12'),
('NSR/123/14', '2025-05-08 11:22:47', 'Absir', 'aa', 'NSR.123.14'),
('NSR/1234/11', '2025-05-18 11:39:42', 'aa', 'aa', 'NSR.1234.11'),
('NSR/12345/12', '2025-05-19 08:32:12', 'aa', 'aa', 'NSR.12345.12'),
('NSR/14/11', '2025-05-10 17:03:30', 'aa', 'aa', 'NSR.14.11'),
('NSR/18/12', '2025-05-08 19:50:22', 'nn', 'nn', 'NSR.18.12'),
('NSR/1856/14', '2025-05-08 10:52:23', 'nat', 'set', 'NSR.1856.14'),
('PGR/123/12', '2025-05-18 16:52:31', 'aa', 'aa', 'PGR.123.12'),
('PGR/185/12', '2025-05-11 23:16:22', 'hghf', 'jhg', 'PGR.185.12'),
('PGR/1856/14', '2025-05-10 16:38:37', 'aa', 'aa', 'PGR.1856.14'),
('SSR/1856/19', '2025-05-10 17:17:28', 'm', 'm', 'SSR.1856.19');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `login_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id`, `user_id`, `username`, `login_time`) VALUES
(1, 17, 'NSR.12.12', '2025-05-14 23:05:21'),
(2, 16, 'NSR.18.12', '2025-05-14 23:15:40'),
(3, 22, 'NSR.1.11', '2025-05-14 23:18:55'),
(4, 17, 'NSR.12.12', '2025-05-14 23:23:13'),
(5, 17, 'NSR.12.12', '2025-05-14 23:24:16'),
(6, 22, 'NSR.1.11', '2025-05-14 23:46:56'),
(7, 22, 'NSR.1.11', '2025-05-14 23:53:50'),
(8, 15, 'NSR.123.14', '2025-05-15 02:51:42'),
(9, 15, 'NSR.123.14', '2025-05-15 02:52:22'),
(10, 15, 'NSR.123.14', '2025-05-15 02:55:21'),
(11, 15, 'NSR.123.14', '2025-05-15 18:55:31'),
(12, 15, 'NSR.123.14', '2025-05-15 18:56:40'),
(13, 17, 'NSR.12.12', '2025-05-16 00:34:46'),
(14, 23, 'NSR.11.90', '2025-05-16 00:36:07'),
(15, 17, 'NSR.12.12', '2025-05-16 14:41:28'),
(16, 17, 'NSR.12.12', '2025-05-16 14:44:12'),
(17, 17, 'NSR.12.12', '2025-05-16 14:46:50'),
(18, 23, 'NSR.11.90', '2025-05-16 14:48:26'),
(19, 23, 'NSR.11.90', '2025-05-16 16:32:25'),
(20, 17, 'NSR.12.12', '2025-05-16 18:44:51'),
(21, 17, 'NSR.12.12', '2025-05-16 18:55:14'),
(22, 17, 'NSR.12.12', '2025-05-16 19:00:43'),
(23, 17, 'NSR.12.12', '2025-05-16 19:01:25'),
(24, 17, 'NSR.12.12', '2025-05-16 19:02:47'),
(25, 17, 'NSR.12.12', '2025-05-16 19:05:52'),
(26, 17, 'NSR.12.12', '2025-05-16 19:07:42'),
(27, 23, 'NSR.11.90', '2025-05-16 19:08:34'),
(28, 23, 'NSR.11.90', '2025-05-16 19:09:28'),
(29, 23, 'NSR.11.90', '2025-05-16 19:11:13'),
(30, 17, 'NSR.12.12', '2025-05-16 19:12:26'),
(31, 17, 'NSR.12.12', '2025-05-16 19:14:37'),
(32, 17, 'NSR.12.12', '2025-05-16 19:17:03'),
(33, 17, 'NSR.12.12', '2025-05-16 19:18:47'),
(34, 23, 'NSR.11.90', '2025-05-16 20:59:20'),
(35, 23, 'NSR.11.90', '2025-05-16 21:00:25'),
(36, 17, 'NSR.12.12', '2025-05-16 21:07:47'),
(37, 16, 'NSR.18.12', '2025-05-16 21:22:06'),
(38, 23, 'NSR.11.90', '2025-05-16 21:27:27'),
(39, 23, 'NSR.11.90', '2025-05-16 22:52:24'),
(40, 17, 'NSR.12.12', '2025-05-16 23:02:53'),
(41, 17, 'NSR.12.12', '2025-05-16 23:04:33'),
(42, 15, 'NSR.123.14', '2025-05-17 10:18:52'),
(43, 15, 'NSR.123.14', '2025-05-17 10:20:03'),
(44, 17, 'NSR.12.12', '2025-05-17 11:58:05'),
(45, 17, 'NSR.12.12', '2025-05-17 11:59:11'),
(46, 23, 'NSR.11.90', '2025-05-17 12:02:36'),
(47, 23, 'NSR.11.90', '2025-05-17 12:04:05'),
(48, 23, 'NSR.11.90', '2025-05-17 12:41:39'),
(49, 17, 'NSR.12.12', '2025-05-17 12:44:42'),
(50, 15, 'NSR.123.14', '2025-05-17 12:51:20'),
(51, 17, 'NSR.12.12', '2025-05-17 13:50:07'),
(52, 15, 'NSR.123.14', '2025-05-17 13:51:28'),
(53, 15, 'NSR.123.14', '2025-05-17 13:54:22'),
(54, 17, 'NSR.12.12', '2025-05-17 17:26:50'),
(55, 17, 'NSR.12.12', '2025-05-17 17:27:38'),
(56, 23, 'NSR.11.90', '2025-05-17 17:32:33'),
(57, 15, 'NSR.123.14', '2025-05-17 17:36:02'),
(58, 17, 'NSR.12.12', '2025-05-17 21:40:12'),
(59, 23, 'NSR.11.90', '2025-05-17 21:46:13'),
(60, 23, 'NSR.11.90', '2025-05-17 23:41:12'),
(61, 17, 'NSR.12.12', '2025-05-18 09:53:00'),
(62, 17, 'NSR.12.12', '2025-05-18 10:20:12'),
(63, 17, 'NSR.12.12', '2025-05-18 10:49:12'),
(64, 15, 'NSR.123.14', '2025-05-18 11:23:20'),
(65, 23, 'NSR.11.90', '2025-05-18 11:32:10'),
(66, 23, 'NSR.11.90', '2025-05-18 11:33:41'),
(67, 43, 'NSR.1234.11', '2025-05-18 12:06:07'),
(68, 15, 'NSR.123.14', '2025-05-18 13:45:26'),
(69, 17, 'NSR.12.12', '2025-05-18 14:12:57'),
(70, 17, 'NSR.12.12', '2025-05-18 14:52:30'),
(71, 43, 'NSR.1234.11', '2025-05-18 14:59:42'),
(72, 43, 'NSR.1234.11', '2025-05-18 15:07:39'),
(73, 17, 'NSR.12.12', '2025-05-18 16:48:13'),
(74, 45, 'PGR.123.12', '2025-05-18 16:54:58'),
(75, 42, 'NSR.1111.00', '2025-05-18 16:58:44'),
(76, 23, 'NSR.11.90', '2025-05-18 17:01:28'),
(77, 44, 'NSR.01.01', '2025-05-18 17:12:34'),
(78, 23, 'NSR.11.90', '2025-05-18 17:17:24'),
(79, 23, 'NSR.11.90', '2025-05-18 17:20:37'),
(80, 17, 'NSR.12.12', '2025-05-18 17:21:53'),
(81, 17, 'NSR.12.12', '2025-05-18 19:40:52'),
(82, 17, 'NSR.12.12', '2025-05-19 08:27:22'),
(83, 46, 'NSR.12345.12', '2025-05-19 08:37:53'),
(84, 23, 'NSR.11.90', '2025-05-19 08:41:38'),
(85, 17, 'NSR.12.12', '2025-05-19 08:45:10'),
(86, 17, 'NSR.12.12', '2025-05-19 08:48:07'),
(87, 46, 'NSR.12345.12', '2025-05-19 08:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `logins`
--

CREATE TABLE `logins` (
  `user_id` int(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `login_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `logins`
--

INSERT INTO `logins` (`user_id`, `username`, `login_time`) VALUES
(1, ' user', '2019-02-12 13:21:43'),
(2, ' user', '2019-02-12 13:37:32'),
(3, ' user', '2019-02-12 18:44:37'),
(4, ' user', '2025-03-06 23:19:16'),
(5, ' user', '2025-03-07 14:30:11'),
(6, ' user', '2025-03-12 02:03:05'),
(7, ' user', '2025-03-29 00:38:47'),
(8, ' user', '2025-03-29 11:12:32'),
(9, ' user', '2025-03-29 12:33:24'),
(10, ' user', '2025-03-29 23:50:59'),
(11, ' user', '2025-03-30 15:51:19'),
(12, ' user', '2025-03-30 16:29:00'),
(13, ' user', '2025-04-04 09:08:22'),
(14, ' user', '2025-04-04 22:22:12'),
(15, ' user', '2025-04-04 22:27:31'),
(16, ' user', '2025-04-08 10:19:38'),
(17, ' user', '2025-04-09 06:17:55'),
(18, 'user', '2025-04-09 06:56:58'),
(19, 'user', '2025-04-09 07:07:00'),
(20, 'user', '2025-04-09 07:41:10'),
(21, 'user', '2025-04-09 07:58:45'),
(22, 'user', '2025-04-13 21:40:46'),
(23, 'user', '2025-04-13 21:57:36'),
(24, 'absir', '2025-04-14 09:30:57'),
(25, 'absir', '2025-04-14 09:53:15'),
(26, 'absir', '2025-04-14 10:04:04'),
(27, 'absir', '2025-04-14 11:07:12'),
(28, 'absir', '2025-04-14 11:12:39'),
(29, 'absir', '2025-04-14 11:17:58'),
(30, 'absir', '2025-04-14 11:26:10'),
(31, 'absir', '2025-04-14 11:39:53'),
(32, 'absir', '2025-04-14 11:50:16'),
(33, 'absir', '2025-04-14 11:56:58'),
(34, 'user', '2025-04-14 14:08:23'),
(35, 'absir', '2025-04-15 08:00:11'),
(36, 'absir', '2025-04-15 09:57:03'),
(37, 'absir', '2025-04-15 09:58:28'),
(38, 'absir', '2025-04-15 10:11:37'),
(39, 'absir', '2025-04-15 10:14:43'),
(40, 'absir', '2025-04-16 01:03:38'),
(41, 'absir', '2025-04-16 01:04:27'),
(42, 'abel', '2025-04-16 01:08:49'),
(43, 'abel', '2025-04-18 22:48:01'),
(44, 'abel', '2025-04-19 00:28:32'),
(45, 'abel', '2025-04-19 00:31:35'),
(46, 'abel', '2025-04-24 14:56:59'),
(47, 'absir', '2025-04-24 16:28:08'),
(48, 'absir', '2025-04-24 19:59:46'),
(49, 'absir', '2025-04-24 21:03:51'),
(50, 'absir', '2025-04-24 21:05:24'),
(51, 'absir', '2025-04-24 21:28:59'),
(52, 'absir', '2025-04-24 21:32:38'),
(53, 'absir', '2025-04-24 21:43:05'),
(54, 'absir', '2025-04-24 22:41:38'),
(55, 'absir', '2025-04-24 23:30:39'),
(56, 'absir', '2025-04-24 23:42:36'),
(57, 'absir', '2025-04-24 23:46:28'),
(58, 'absir', '2025-04-24 23:49:53'),
(59, 'absir', '2025-04-24 23:54:04'),
(60, 'abel', '2025-04-25 00:19:14'),
(61, 'absir', '2025-04-25 03:12:21'),
(62, 'absir', '2025-04-25 04:36:27'),
(63, 'absir', '2025-04-25 05:02:19'),
(64, 'abel', '2025-04-25 20:53:35'),
(65, 'abel', '2025-04-28 16:39:32'),
(66, 'abel', '2025-04-28 16:58:33'),
(67, 'absir', '2025-04-28 17:56:39'),
(68, 'abel', '2025-04-28 20:26:50'),
(69, 'abel', '2025-04-29 00:44:47'),
(70, 'absir', '2025-04-29 09:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `otp_table`
--

CREATE TABLE `otp_table` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `expiration_time` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_table`
--

INSERT INTO `otp_table` (`id`, `phone_number`, `otp`, `expiration_time`, `is_verified`, `attempts`, `created_at`, `updated_at`) VALUES
(7, '+251996785771', '409561', '2025-04-29 09:17:17', 1, 0, '2025-04-11 21:05:48', '2025-05-16 13:55:08'),
(8, '******5771', '879315', '2025-05-14 08:41:29', 0, 0, '2025-05-08 06:51:48', '2025-05-16 13:55:08'),
(9, '******7652', '015972', '2025-05-11 16:13:45', 1, 0, '2025-05-10 22:56:54', '2025-05-16 13:55:08'),
(10, '******5770', '676065', '2025-05-13 00:03:16', 0, 0, '2025-05-12 21:29:53', '2025-05-16 13:55:08'),
(11, '******5774', '722677', '2025-05-13 00:00:15', 0, 0, '2025-05-12 21:58:15', '2025-05-16 13:55:08'),
(12, '******0884', '478807', '2025-05-19 09:36:45', 1, 16, '2025-05-15 00:48:40', '2025-05-19 06:34:59'),
(13, '+251905610884', '527381', '2025-05-16 17:38:20', 0, 0, '2025-05-15 17:08:21', '2025-05-16 15:33:20'),
(14, '******9897', '557', '2025-05-16 16:21:02', 0, 2, '2025-05-16 14:16:22', '2025-05-16 14:19:02'),
(15, '******0899', '645703', '2025-05-16 16:23:44', 0, 1, '2025-05-16 14:21:44', '2025-05-16 14:21:44'),
(16, '******0893', '975622', '2025-05-16 16:43:10', 0, 3, '2025-05-16 14:36:20', '2025-05-16 14:41:10'),
(17, '******1212', '205234', '2025-05-18 17:56:21', 1, 4, '2025-05-16 14:43:04', '2025-05-18 14:54:30'),
(18, '+251916528975', '315046', '2025-05-16 18:47:35', 1, 0, '2025-05-16 15:34:37', '2025-05-16 15:42:44'),
(19, '******8975', '666247', '2025-05-16 18:50:45', 0, 0, '2025-05-16 15:48:45', '2025-05-16 15:48:45'),
(20, '******1415', '844464', '2025-05-16 18:52:30', 0, 0, '2025-05-16 15:50:30', '2025-05-16 15:50:30'),
(21, '******+251', '773617', '2025-05-16 18:58:59', 1, 0, '2025-05-16 15:56:59', '2025-05-16 15:57:08'),
(22, '******1111', '526029', '2025-05-16 19:00:37', 1, 0, '2025-05-16 15:58:37', '2025-05-16 15:58:44');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verification`
--

CREATE TABLE `otp_verification` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expiration_time` datetime NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `position`
--

CREATE TABLE `position` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position`
--

INSERT INTO `position` (`position_id`, `position_name`) VALUES
(6, 'techno'),
(7, 'cyber'),
(8, 'Prime Minister_ethiopia'),
(9, 'minch'),
(10, 'nati'),
(11, 'lkn'),
(12, 'pre'),
(13, 'Serrectery');

-- --------------------------------------------------------

--
-- Table structure for table `report_complaints`
--

CREATE TABLE `report_complaints` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `voting_event_id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in-progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_complaints`
--

INSERT INTO `report_complaints` (`id`, `username`, `voting_event_id`, `category`, `subject`, `description`, `status`, `created_at`, `response`) VALUES
(1, '', 1, 'Fraud', 'assssxxx', 'vbF', 'resolved', '2025-05-08 20:48:08', 'jhfsuhus'),
(2, '', 1, 'Fraud', 'assssxxx', 'vbF', 'resolved', '2025-05-08 21:12:57', NULL),
(3, '', 1, 'Fraud', 'lkjh', 'sdvldavnkrequire_once(&#39;dbcon.php&#39;);jADKJNklADvmdkALNVkldaNbkanKLZNgbjlsnjfBjj c b,vmnbmzc vbjsv', 'resolved', '2025-05-08 21:13:45', 'khdGFBKWGKDSbwkhbds'),
(4, 'NSR/18/12', 2, 'Intimidation', 'h', 'sgbsf', 'resolved', '2025-05-08 21:24:46', NULL),
(5, 'NSR/18/12', 2, 'Mismanagement', 'kjhg', 'cgvhjn', 'resolved', '2025-05-08 21:41:36', 'fghkj'),
(6, 'NSR/123/14', 1, 'Mismanagement', 'kjhg', 'cgvhjn', 'resolved', '2025-05-08 21:43:27', 'nncdbhadbhfbiwrbsnm zhfbvkb czs'),
(7, 'NSR/18/12', 1, 'Other', 'uhjhbhj', 'uguhiuhiuhiuh', 'resolved', '2025-05-08 21:54:32', NULL),
(8, 'NSR/123/14', 1, 'Fraud', 'bjhb', 'mhjv', '', '2025-05-09 22:00:34', 'kjkhjghxcv'),
(9, 'NSR/18/12', 1, 'Mismanagement', 'kkjiohuhgyuggjkjnuihu', 'hbhiokdkhnhjnk,ojnkkjkkgkouikkhkkmmmmmnnfgkgkhkouityyyoyoyyoyooymxnmfkmn&#39;\r\n\r\nn xfp\r\nx\r\n\r\nxx\r\nxxfk\r\nx\r\nx\r\nx\r\nxm\r\n\r\nxj\r\nxm\r\n\r\nxjk[ykkjm\r\nkofx\r\n', 'resolved', '2025-05-10 09:05:07', 'hgfdcvn'),
(10, 'NSR/12/12', 1, 'Mismanagement', 'hgfdgchvb', ';koiuyfdtcvgbhknmk,', 'resolved', '2025-05-16 19:14:46', 'lbihhjbhnjlnl'),
(11, 'NSR/01/01', 2, 'Mismanagement', 'make me l', 'asdfghjkl;&#39;\r\n&#39;;lkjhgfdsa', 'resolved', '2025-05-18 15:14:36', 'ok i will'),
(12, 'NSR/12345/12', 1, 'Fraud', 'make me l', 'rheahdhst', 'resolved', '2025-05-19 06:40:43', 'bvhdsbvkjdavbjd,');

-- --------------------------------------------------------

--
-- Table structure for table `role_table`
--

CREATE TABLE `role_table` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_table`
--

INSERT INTO `role_table` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Staff'),
(3, 'Voter');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `role_id` int(11) DEFAULT 3,
  `gender` varchar(10) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Unvoted',
  `account` varchar(20) DEFAULT 'Inactive',
  `registration_date` date DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `last_failed_attempt_time` datetime DEFAULT NULL,
  `is_locked` tinyint(1) DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `firstname`, `lastname`, `phone`, `email`, `role_id`, `gender`, `status`, `account`, `registration_date`, `id_number`, `failed_login_attempts`, `last_failed_attempt_time`, `is_locked`, `lockout_until`) VALUES
(4, 'user', '$2y$10$FIRdan8wY0T4lPnqREwkq.u5KJ6r9ZeRh1YFmJZB.YL9ehnQ/R13O', 'Absir', 'Mulugeta', '996785771', 'mulu@mail.com', 3, NULL, 'Unvoted', 'Active', NULL, NULL, 0, NULL, 0, NULL),
(5, 'helin', '$2y$10$Ht7qZx6RU26wIRElN4PzSOnk0NplxKRWcQIsbav9SvKexelBKiAlC', 'Absir', 'Mulugeta', '<br /><b>Warning</b>', 'absirmulugeta@gmail.com', 2, NULL, 'Unvoted', 'Inactive', NULL, NULL, 0, NULL, 0, NULL),
(15, 'NSR.123.14', '$2y$10$dH5VRyGxWnicWCaqBZBrOuMeLlcejB191TRrIcTv/THBcxuVK6HGa', 'Absir', 'aa', '******5771', 'dessieeshete@gmail.com', 3, 'male', 'Voted', 'Inactive', '2025-05-08', 'NSR/123/14', 5, NULL, 1, '2025-05-18 15:14:15'),
(16, 'NSR.18.12', '$2y$10$zhZsc5pqyKpRLcVTXZ050eBlDBBZtFqYLJDHJUAPz8fx/khS7G2Gy', 'nn', 'nn', '******5771', '', 3, 'male', 'Voted', 'Inactive', '2025-05-08', 'NSR/18/12', 0, NULL, 0, NULL),
(17, 'NSR.12.12', '$2y$10$D1nPzG6RoWNN4pWSGtoqtup0soBRRyI4R3zVAQYoqrjz9fs50FmYS', 'aa', 'aa', '******5771', 'nattysertse@gmail.com', 1, 'male', 'Voted', 'Inactive', '2025-05-09', 'NSR/12/12', 0, NULL, 0, NULL),
(18, 'nat', '$2y$10$YrQCw4fSmJFp0iC0b5YA0.nGx9AFmoCw.17ToP.wC6oDWnh/.JIci', 'nat', 'nat', '<br /><b>Warning</b>', 'jdsfh@gmail.com', 3, NULL, 'Unvoted', 'Active', NULL, NULL, 0, NULL, 0, NULL),
(19, 'NSR.14.11', '$2y$10$tSgSc2HnHSIfgzcb5qExYOIqZP07Hb/44MWZuWvY0PXFj5yHlmzsi', 'aa', '', '0996785771', '', 2, 'Male', 'Unvoted', 'Inactive', NULL, NULL, 0, NULL, 0, NULL),
(20, 'SSR.1856.19', '$2y$10$53PlpLkjIQOANraGLDAwNOsgStDvdfLN5F86s2gawzMo6yhGqzUg.', 'm', '', '0996785771', '', 2, 'Female', 'Unvoted', 'Inactive', NULL, NULL, 0, NULL, 0, NULL),
(21, 'PGR.10.10', '$2y$10$Rvqth.hMwagBl7eZECH6yObDo4YnDa1cGtODjW2lWHgfom0o5h9/e', 'qq', 'qq', '0996785771', '', 2, 'Male', NULL, 'Inactive', NULL, NULL, 0, NULL, 0, NULL),
(22, 'NSR.1.11', '$2y$10$Wf7sUBcar7mMQevlkUJ1kuOtmzjbhKGWwaypYMiCDzmYbs7.72lnS', 'bakala', 'chala', '0996785771', '', 2, 'Male', 'Unvoted', 'Inactive', NULL, NULL, 0, NULL, 0, NULL),
(23, 'NSR.11.90', '$2y$10$T676o89kE6oXdhuL9APExekigH7GY7l.gTE8iny4p3eb8Mh.JIrSu', 'qq', 'qq', '0996785771', 'nattysertse@gmail.com', 2, 'Male', 'active', 'Inactive', '2025-05-10', 'NSR/11/90', 0, '2025-05-16 19:14:19', 0, NULL),
(24, 'PGR.11.44', '$2y$10$X27sN/KBmcyLz1SDVgOChu2bRLMnoCz0VUXfI0s5d7NXvL65mCVHS', 'aa', 'aa', '0996785771', '', 2, 'Male', 'active', 'Inactive', '2025-05-10', 'PGR/11/44', 0, NULL, 0, NULL),
(39, 'NSR.1111.11', '$2y$10$nFeVBIO3h5QVpbfw5twu5ur53zXWuLPSTtfadOjFlWF9ZXMNmx3be', 'aa', 'aa', '******0884', '', 3, 'Male', 'Unvoted', 'Inactive', '2025-05-16', 'NSR/1111/11', 0, NULL, 0, NULL),
(40, 'PGR.185.12', '$2y$10$9vVN14FS2VB3uJqd5dp0z.RSeSwP6i5kKiuIP6040fNV2qjJAoHki', 'hghf', 'jhg', '******0884', '', 3, 'Female', 'Unvoted', 'Inactive', '2025-05-16', 'PGR/185/12', 0, NULL, 0, NULL),
(42, 'NSR.1111.00', '$2y$10$OSREOPb5FD3YG1NUsCmxTem2uCfZ/gm/A2uTqLI26VWw8w/k49mT6', 'kk', 'jj', '0905610884', '', 2, 'Male', 'inactive', 'Inactive', '2025-05-16', 'NSR/1111/00', 0, NULL, 0, NULL),
(43, 'NSR.1234.11', '$2y$10$v1xBJ.hW8BRmwzIK2EApVeUwPaYhKovb7PvC2PrRUDQDiTFgxyF96', 'aa', 'aa', '******0884', 'nattysertse@gmail.com', 3, 'Male', 'Unvoted', 'Inactive', '2025-05-18', 'NSR/1234/11', 0, NULL, 0, NULL),
(44, 'NSR.01.01', '$2y$10$Fo/yLvaORM7MlutgKsKVQOzb8PCr3seRz5zZPqRHiQz8oxqP6OGCG', 'aa', 'aa', '******0884', 'nattysertse@gmail.com', 3, 'Male', 'Unvoted', 'Inactive', '2025-05-18', 'NSR/01/01', 0, NULL, 0, NULL),
(45, 'PGR.123.12', '$2y$10$LAmr7y4CqEN3VZtchS3PGuCvZYPi2NwsZYCbHpWG1Cn0GoSy4IMJy', 'aa', 'aa', '******1212', 'minte@gmail.com', 3, 'Male', 'Unvoted', 'Inactive', '2025-05-18', 'PGR/123/12', 5, NULL, 1, '2025-05-18 17:15:19'),
(46, 'NSR.12345.12', '$2y$10$vc6x8iinsTw1oE5McuLGJOCgSKph9xHa5hsQEs8yp6f7/xRZu7DjO', 'aa', 'aa', '******0884', 'helina@gmail.com', 3, 'Female', 'Voted', 'Active', '2025-05-19', 'NSR/12345/12', 0, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `voters_id` int(11) NOT NULL,
  `id_number` varchar(12) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `voter_type` varchar(255) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `account` varchar(100) NOT NULL DEFAULT 'Inactive',
  `date` date DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`voters_id`, `id_number`, `firstname`, `lastname`, `gender`, `voter_type`, `status`, `account`, `date`, `password`, `phone_number`) VALUES
(77, 'nsr001', 'A', 'Mulugeta', 'male', 'student', 'Voted', 'Active', '2025-04-29', '$2y$10$UU0FvkX8bM3WritleC4VQuQJTTjNV/25zb9F9xyFE/5UpB.X/CNVG', '+251996785771'),
(78, 'nsr1856', 'aa', 'aa', 'male', 'student', 'Unvoted', 'Active', '2025-05-08', '$2y$10$gh9x7PKOMt2QqyQuha1LUeYWfSLEaN1Ph.7CUJdIt9C8wyZCPhQnK', '******5771');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `vote_id` int(255) NOT NULL,
  `candidate_id` varchar(255) NOT NULL,
  `voters_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`vote_id`, `candidate_id`, `voters_id`) VALUES
(158, 'TZIZ+rosHGJSQueg2aE6KA==', '77'),
(159, 'OxcBCnreKHYs6O/4zulFPQ==', '77'),
(160, 'G4KaG0WAzamDKC6schxhPA==', '77'),
(161, 'YtWizWAm1ustdO1ICJ+e6w==', '77'),
(162, 'TrW4zjuqlPEjq+N9WYZ+kg==', '16'),
(163, 'fonaf+fb1KV3wqmBP3FE+g==', '16'),
(164, 'UsBJRWyQhxaA49s4uR1J9g==', '16'),
(165, 'kO7Gf6KSUP02kCrJpg436w==', '16'),
(166, 'FBErpg7pyuvSCm2XAkx/BA==', '16'),
(167, 'PiE5vHo4EjXT4ZsSRWq6gA==', '16'),
(168, 'fonaf+fb1KV3wqmBP3FE+g==', '16'),
(169, 'UsBJRWyQhxaA49s4uR1J9g==', '16'),
(170, 'kO7Gf6KSUP02kCrJpg436w==', '16'),
(171, 'FBErpg7pyuvSCm2XAkx/BA==', '16'),
(172, 'PiE5vHo4EjXT4ZsSRWq6gA==', '16'),
(173, 'fonaf+fb1KV3wqmBP3FE+g==', '16'),
(174, 'UsBJRWyQhxaA49s4uR1J9g==', '16'),
(175, 'kO7Gf6KSUP02kCrJpg436w==', '16'),
(176, 'FBErpg7pyuvSCm2XAkx/BA==', '16'),
(177, 'TrW4zjuqlPEjq+N9WYZ+kg==', '15'),
(178, 'G9yDzs5mV2crMhAb3lt9ww==', '15'),
(179, 'UsBJRWyQhxaA49s4uR1J9g==', '15'),
(180, 'kO7Gf6KSUP02kCrJpg436w==', '15'),
(181, 'FBErpg7pyuvSCm2XAkx/BA==', '15'),
(182, 'TrW4zjuqlPEjq+N9WYZ+kg==', '16'),
(183, 'fonaf+fb1KV3wqmBP3FE+g==', '16'),
(184, 'UsBJRWyQhxaA49s4uR1J9g==', '16'),
(185, 'kO7Gf6KSUP02kCrJpg436w==', '16'),
(186, 'FBErpg7pyuvSCm2XAkx/BA==', '16'),
(187, 'TrW4zjuqlPEjq+N9WYZ+kg==', '17'),
(188, 'fonaf+fb1KV3wqmBP3FE+g==', '17'),
(189, 'UsBJRWyQhxaA49s4uR1J9g==', '17'),
(190, 'kO7Gf6KSUP02kCrJpg436w==', '17'),
(191, 'FBErpg7pyuvSCm2XAkx/BA==', '17'),
(192, 'PiE5vHo4EjXT4ZsSRWq6gA==', '46'),
(193, 'fonaf+fb1KV3wqmBP3FE+g==', '46'),
(194, 'UsBJRWyQhxaA49s4uR1J9g==', '46'),
(195, 'kO7Gf6KSUP02kCrJpg436w==', '46'),
(196, 'FBErpg7pyuvSCm2XAkx/BA==', '46');

-- --------------------------------------------------------

--
-- Table structure for table `voting_events`
--

CREATE TABLE `voting_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voting_events`
--

INSERT INTO `voting_events` (`id`, `title`, `start_time`, `end_time`, `is_active`) VALUES
(1, 'Student union', 1745545063, 1745554377, 0),
(2, 'Student union', 1745909027, 1745916227, 0),
(3, 'rep election', 1747475994, 1747640967, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidate`
--
ALTER TABLE `candidate`
  ADD PRIMARY KEY (`candidate_id`);

--
-- Indexes for table `ids`
--
ALTER TABLE `ids`
  ADD PRIMARY KEY (`id_number`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `otp_table`
--
ALTER TABLE `otp_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_number` (`phone_number`);

--
-- Indexes for table `otp_verification`
--
ALTER TABLE `otp_verification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `report_complaints`
--
ALTER TABLE `report_complaints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_table`
--
ALTER TABLE `role_table`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_users_role_id` (`role_id`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`voters_id`),
  ADD UNIQUE KEY `id_number` (`id_number`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vote_id`);

--
-- Indexes for table `voting_events`
--
ALTER TABLE `voting_events`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `logins`
--
ALTER TABLE `logins`
  MODIFY `user_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `otp_table`
--
ALTER TABLE `otp_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `otp_verification`
--
ALTER TABLE `otp_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `report_complaints`
--
ALTER TABLE `report_complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `voters_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `voting_events`
--
ALTER TABLE `voting_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `role_table` (`role_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
