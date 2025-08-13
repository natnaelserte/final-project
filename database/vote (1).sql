-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2025 at 10:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
(19, 'rep election', 'be ready', 'staff_user', '2025-05-14 09:49:00', '2025-05-08 09:50:00', '2025-05-19 06:50:09', 1),
(20, 'infoc', 'b-e ready', 'staff_user', '2025-05-22 19:49:00', '2025-05-31 22:48:00', '2025-05-21 15:48:26', 1);

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

CREATE TABLE `candidate` (
  `candidate_id` int(11) NOT NULL,
  `position` int(2) DEFAULT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `candidate_type` varchar(20) DEFAULT NULL,
  `gender` varchar(100) NOT NULL,
  `img` varchar(100) NOT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `primary_evidence_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate`
--

INSERT INTO `candidate` (`candidate_id`, `position`, `firstname`, `lastname`, `candidate_type`, `gender`, `img`, `slogan`, `primary_evidence_path`) VALUES
(76, 22, 'absir', 'mulugeta', 'Faculty', 'Male', 'upload/profile_683a38b2b6ae26.46171854_download39.jpg', 'we grow', 'candidate_detail/evidence_683a38b2b76836.16185760_evidence_683712f5575a19.57406501_Candidate_Profile_Demo49.docx'),
(77, 22, 'natty', 'serte', 'Faculty', 'Male', 'upload/profile_683a38e4957cd0.54219108_download38.jpg', 'we can', 'candidate_detail/evidence_683a38e495bb63.49742663_evidence_683a38b2b76836.16185760_evidence_683712f5575a19.57406501_Candidate_Profile_Demo49.docx'),
(78, 23, 'Tamen', 'Terfe', 'Faculty', 'Male', 'upload/profile_683a39158bac75.65208840_download37.jpg', 'we grow', NULL),
(79, 23, 'Tamen', 'serte', 'Faculty', 'Male', 'upload/profile_683a393e70e544.66156486_download35.jpg', 'we grow', NULL),
(80, 24, 'Michaele Gashaw', 'Terfe', 'Student', 'Male', 'upload/profile_683a3aef568368.82854243_download34.jpg', 'we grow', NULL),
(81, 24, 'Tamen', 'Terfe', 'Student', 'Male', 'upload/profile_683a3b24ae3270.99133514_download35.jpg', 'we grow together', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `candidate_evaluations`
--

CREATE TABLE `candidate_evaluations` (
  `evaluation_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `evaluator_user_id` int(11) NOT NULL,
  `evaluation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `max_10_service` decimal(5,2) DEFAULT NULL,
  `performance_max_10` decimal(5,2) DEFAULT NULL,
  `rank_max_5` decimal(5,2) DEFAULT NULL,
  `service_diff_pos_max_10` decimal(5,2) DEFAULT NULL,
  `publication_max_5` decimal(5,2) DEFAULT NULL,
  `community_max_10` decimal(5,2) DEFAULT NULL,
  `committee_max_5` decimal(5,2) DEFAULT NULL,
  `hdp_max_5` decimal(5,2) DEFAULT NULL,
  `file_nearness_max_5` decimal(5,2) DEFAULT NULL,
  `colleagues_eval_max_15` decimal(5,2) DEFAULT NULL,
  `supervisor_eval_max_20` decimal(5,2) DEFAULT NULL,
  `evaluator_comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate_evaluations`
--

INSERT INTO `candidate_evaluations` (`evaluation_id`, `candidate_id`, `evaluator_user_id`, `evaluation_date`, `max_10_service`, `performance_max_10`, `rank_max_5`, `service_diff_pos_max_10`, `publication_max_5`, `community_max_10`, `committee_max_5`, `hdp_max_5`, `file_nearness_max_5`, `colleagues_eval_max_15`, `supervisor_eval_max_20`, `evaluator_comments`) VALUES
(4, 76, 59, '2025-05-31 07:14:26', 3.00, 3.00, 4.00, 3.00, 5.00, 3.00, 3.00, 3.00, 4.00, 2.00, 12.00, 'dfgcghvgh'),
(5, 77, 59, '2025-05-31 07:15:15', 2.00, 4.00, 4.00, 6.00, 2.00, 6.00, 3.00, 3.00, 3.00, 4.00, 3.00, 'hvjhvbhb'),
(6, 79, 59, '2025-05-31 07:15:45', 2.00, 4.00, 5.00, 4.00, 3.00, 3.00, 4.00, 4.00, 4.00, 4.00, 4.00, 'esesd'),
(7, 78, 59, '2025-05-31 07:16:27', 3.00, 5.00, 4.00, 3.00, 4.00, 7.00, 5.00, 4.00, 2.00, 8.00, 5.00, 'gdfdcf'),
(8, 76, 60, '2025-05-31 08:56:13', 3.00, 3.00, 3.00, NULL, NULL, NULL, NULL, NULL, 3.00, 3.00, 3.00, ''),
(9, 77, 60, '2025-05-31 08:56:46', 3.00, 3.00, 3.00, NULL, NULL, NULL, NULL, NULL, 3.00, 3.00, 3.00, ''),
(10, 79, 60, '2025-05-31 09:03:36', 2.00, 3.00, 1.00, 3.00, 1.00, 3.00, 1.00, 2.00, 1.00, 1.00, 1.00, 'yes'),
(11, 78, 60, '2025-05-31 09:04:27', 1.00, 7.00, 2.00, 3.00, 2.00, 2.00, 4.00, 1.00, 2.00, 8.00, 6.00, 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `ids`
--

CREATE TABLE `ids` (
  `id_number` varchar(100) NOT NULL,
  `date` datetime DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `faculty` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ids`
--

INSERT INTO `ids` (`id_number`, `date`, `firstname`, `lastname`, `faculty`, `department`, `role_id`, `institution`, `username`) VALUES
('123nat', '2025-05-31 00:46:38', 'nat', 'nat', 'computer science', 'comp', 5, 'AMit', '123nat'),
('nat123', '2025-05-31 01:23:50', 'Tamen', 'Terfe', NULL, NULL, 3, NULL, 'nat123'),
('NSR/00/00', '2025-05-25 13:47:55', 'natnael', 'sertse', 'computer science', NULL, 5, NULL, 'NSR.00.00'),
('NSR/01/01', '2025-05-18 14:50:28', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.01.01'),
('NSR/01/10', '2025-05-25 14:09:30', 'aa', 'aa', 'computer science', NULL, 5, NULL, 'NSR.01.10'),
('NSR/02/02', '2025-05-18 14:47:56', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.02.02'),
('NSR/11/11', '2025-05-10 17:00:33', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.11.11'),
('NSR/11/12', '2025-05-31 01:21:04', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.11.12'),
('NSR/11/16', '2025-05-25 12:45:27', 'aa', 'aa', NULL, NULL, 3, NULL, 'NSR.11.16'),
('NSR/1111/11', '2025-05-16 16:33:00', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.1111.11'),
('NSR/12/12', '2025-05-09 20:27:42', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.12.12'),
('NSR/123/14', '2025-05-08 11:22:47', 'Absir', 'aa', NULL, NULL, NULL, NULL, 'NSR.123.14'),
('NSR/1234/11', '2025-05-18 11:39:42', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.1234.11'),
('NSR/1234/16', '2025-05-25 11:44:51', 'natnael', 'esh', NULL, NULL, 5, NULL, 'NSR.1234.16'),
('NSR/1234/33', '2025-05-21 17:40:25', 'Absir', 'sertse', NULL, NULL, NULL, NULL, 'NSR.1234.33'),
('NSR/12345/12', '2025-05-19 08:32:12', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.12345.12'),
('NSR/14/11', '2025-05-10 17:03:30', 'aa', 'aa', NULL, NULL, NULL, NULL, 'NSR.14.11'),
('NSR/18/12', '2025-05-08 19:50:22', 'nn', 'nn', NULL, NULL, NULL, NULL, 'NSR.18.12'),
('NSR/1856/14', '2025-05-08 10:52:23', 'nat', 'set', NULL, NULL, NULL, NULL, 'NSR.1856.14'),
('NSR/199/15', '2025-05-23 20:49:59', 'natty', 'serte', NULL, NULL, NULL, NULL, 'NSR.199.15'),
('NSR/2487/14', '2025-05-23 14:43:46', 'minte', 'hhh', NULL, NULL, NULL, NULL, 'NSR.2487.14'),
('NSR/33/33', '2025-05-25 15:33:19', 'aa', 'aa', 'registerer', NULL, 5, NULL, 'NSR.33.33'),
('PGR/123/12', '2025-05-18 16:52:31', 'aa', 'aa', NULL, NULL, NULL, NULL, 'PGR.123.12'),
('PGR/185/12', '2025-05-11 23:16:22', 'hghf', 'jhg', NULL, NULL, NULL, NULL, 'PGR.185.12'),
('PGR/1856/14', '2025-05-10 16:38:37', 'aa', 'aa', NULL, NULL, NULL, NULL, 'PGR.1856.14'),
('SSR/1856/19', '2025-05-10 17:17:28', 'm', 'm', NULL, NULL, NULL, NULL, 'SSR.1856.19');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `login_time` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id`, `user_id`, `username`, `login_time`, `ip_address`) VALUES
(1, 17, 'NSR.12.12', '2025-05-14 23:05:21', NULL),
(2, 16, 'NSR.18.12', '2025-05-14 23:15:40', NULL),
(3, 22, 'NSR.1.11', '2025-05-14 23:18:55', NULL),
(4, 17, 'NSR.12.12', '2025-05-14 23:23:13', NULL),
(5, 17, 'NSR.12.12', '2025-05-14 23:24:16', NULL),
(6, 22, 'NSR.1.11', '2025-05-14 23:46:56', NULL),
(7, 22, 'NSR.1.11', '2025-05-14 23:53:50', NULL),
(8, 15, 'NSR.123.14', '2025-05-15 02:51:42', NULL),
(9, 15, 'NSR.123.14', '2025-05-15 02:52:22', NULL),
(10, 15, 'NSR.123.14', '2025-05-15 02:55:21', NULL),
(11, 15, 'NSR.123.14', '2025-05-15 18:55:31', NULL),
(12, 15, 'NSR.123.14', '2025-05-15 18:56:40', NULL),
(13, 17, 'NSR.12.12', '2025-05-16 00:34:46', NULL),
(14, 23, 'NSR.11.90', '2025-05-16 00:36:07', NULL),
(15, 17, 'NSR.12.12', '2025-05-16 14:41:28', NULL),
(16, 17, 'NSR.12.12', '2025-05-16 14:44:12', NULL),
(17, 17, 'NSR.12.12', '2025-05-16 14:46:50', NULL),
(18, 23, 'NSR.11.90', '2025-05-16 14:48:26', NULL),
(19, 23, 'NSR.11.90', '2025-05-16 16:32:25', NULL),
(20, 17, 'NSR.12.12', '2025-05-16 18:44:51', NULL),
(21, 17, 'NSR.12.12', '2025-05-16 18:55:14', NULL),
(22, 17, 'NSR.12.12', '2025-05-16 19:00:43', NULL),
(23, 17, 'NSR.12.12', '2025-05-16 19:01:25', NULL),
(24, 17, 'NSR.12.12', '2025-05-16 19:02:47', NULL),
(25, 17, 'NSR.12.12', '2025-05-16 19:05:52', NULL),
(26, 17, 'NSR.12.12', '2025-05-16 19:07:42', NULL),
(27, 23, 'NSR.11.90', '2025-05-16 19:08:34', NULL),
(28, 23, 'NSR.11.90', '2025-05-16 19:09:28', NULL),
(29, 23, 'NSR.11.90', '2025-05-16 19:11:13', NULL),
(30, 17, 'NSR.12.12', '2025-05-16 19:12:26', NULL),
(31, 17, 'NSR.12.12', '2025-05-16 19:14:37', NULL),
(32, 17, 'NSR.12.12', '2025-05-16 19:17:03', NULL),
(33, 17, 'NSR.12.12', '2025-05-16 19:18:47', NULL),
(34, 23, 'NSR.11.90', '2025-05-16 20:59:20', NULL),
(35, 23, 'NSR.11.90', '2025-05-16 21:00:25', NULL),
(36, 17, 'NSR.12.12', '2025-05-16 21:07:47', NULL),
(37, 16, 'NSR.18.12', '2025-05-16 21:22:06', NULL),
(38, 23, 'NSR.11.90', '2025-05-16 21:27:27', NULL),
(39, 23, 'NSR.11.90', '2025-05-16 22:52:24', NULL),
(40, 17, 'NSR.12.12', '2025-05-16 23:02:53', NULL),
(41, 17, 'NSR.12.12', '2025-05-16 23:04:33', NULL),
(42, 15, 'NSR.123.14', '2025-05-17 10:18:52', NULL),
(43, 15, 'NSR.123.14', '2025-05-17 10:20:03', NULL),
(44, 17, 'NSR.12.12', '2025-05-17 11:58:05', NULL),
(45, 17, 'NSR.12.12', '2025-05-17 11:59:11', NULL),
(46, 23, 'NSR.11.90', '2025-05-17 12:02:36', NULL),
(47, 23, 'NSR.11.90', '2025-05-17 12:04:05', NULL),
(48, 23, 'NSR.11.90', '2025-05-17 12:41:39', NULL),
(49, 17, 'NSR.12.12', '2025-05-17 12:44:42', NULL),
(50, 15, 'NSR.123.14', '2025-05-17 12:51:20', NULL),
(51, 17, 'NSR.12.12', '2025-05-17 13:50:07', NULL),
(52, 15, 'NSR.123.14', '2025-05-17 13:51:28', NULL),
(53, 15, 'NSR.123.14', '2025-05-17 13:54:22', NULL),
(54, 17, 'NSR.12.12', '2025-05-17 17:26:50', NULL),
(55, 17, 'NSR.12.12', '2025-05-17 17:27:38', NULL),
(56, 23, 'NSR.11.90', '2025-05-17 17:32:33', NULL),
(57, 15, 'NSR.123.14', '2025-05-17 17:36:02', NULL),
(58, 17, 'NSR.12.12', '2025-05-17 21:40:12', NULL),
(59, 23, 'NSR.11.90', '2025-05-17 21:46:13', NULL),
(60, 23, 'NSR.11.90', '2025-05-17 23:41:12', NULL),
(61, 17, 'NSR.12.12', '2025-05-18 09:53:00', NULL),
(62, 17, 'NSR.12.12', '2025-05-18 10:20:12', NULL),
(63, 17, 'NSR.12.12', '2025-05-18 10:49:12', NULL),
(64, 15, 'NSR.123.14', '2025-05-18 11:23:20', NULL),
(65, 23, 'NSR.11.90', '2025-05-18 11:32:10', NULL),
(66, 23, 'NSR.11.90', '2025-05-18 11:33:41', NULL),
(67, 43, 'NSR.1234.11', '2025-05-18 12:06:07', NULL),
(68, 15, 'NSR.123.14', '2025-05-18 13:45:26', NULL),
(69, 17, 'NSR.12.12', '2025-05-18 14:12:57', NULL),
(70, 17, 'NSR.12.12', '2025-05-18 14:52:30', NULL),
(71, 43, 'NSR.1234.11', '2025-05-18 14:59:42', NULL),
(72, 43, 'NSR.1234.11', '2025-05-18 15:07:39', NULL),
(73, 17, 'NSR.12.12', '2025-05-18 16:48:13', NULL),
(74, 45, 'PGR.123.12', '2025-05-18 16:54:58', NULL),
(75, 42, 'NSR.1111.00', '2025-05-18 16:58:44', NULL),
(76, 23, 'NSR.11.90', '2025-05-18 17:01:28', NULL),
(77, 44, 'NSR.01.01', '2025-05-18 17:12:34', NULL),
(78, 23, 'NSR.11.90', '2025-05-18 17:17:24', NULL),
(79, 23, 'NSR.11.90', '2025-05-18 17:20:37', NULL),
(80, 17, 'NSR.12.12', '2025-05-18 17:21:53', NULL),
(81, 17, 'NSR.12.12', '2025-05-18 19:40:52', NULL),
(82, 17, 'NSR.12.12', '2025-05-19 08:27:22', NULL),
(83, 46, 'NSR.12345.12', '2025-05-19 08:37:53', NULL),
(84, 23, 'NSR.11.90', '2025-05-19 08:41:38', NULL),
(85, 17, 'NSR.12.12', '2025-05-19 08:45:10', NULL),
(86, 17, 'NSR.12.12', '2025-05-19 08:48:07', NULL),
(87, 46, 'NSR.12345.12', '2025-05-19 08:53:19', NULL),
(88, 17, 'NSR.12.12', '2025-05-20 09:44:21', NULL),
(89, 17, 'NSR.12.12', '2025-05-20 09:48:15', NULL),
(90, 15, 'NSR.123.14', '2025-05-20 10:03:46', NULL),
(91, 23, 'NSR.11.90', '2025-05-20 10:40:07', NULL),
(92, 23, 'NSR.11.90', '2025-05-20 12:13:26', NULL),
(93, 17, 'NSR.12.12', '2025-05-20 12:28:23', NULL),
(94, 23, 'NSR.11.90', '2025-05-20 13:37:59', NULL),
(95, 17, 'NSR.12.12', '2025-05-20 13:38:43', NULL),
(96, 23, 'NSR.11.90', '2025-05-20 13:46:06', NULL),
(97, 15, 'NSR.123.14', '2025-05-20 14:04:51', NULL),
(98, 23, 'NSR.11.90', '2025-05-20 14:17:51', NULL),
(99, 17, 'NSR.12.12', '2025-05-20 14:29:18', NULL),
(100, 15, 'NSR.123.14', '2025-05-20 14:32:56', NULL),
(101, 15, 'NSR.123.14', '2025-05-20 14:49:26', NULL),
(102, 17, 'NSR.12.12', '2025-05-20 15:42:45', NULL),
(103, 17, 'NSR.12.12', '2025-05-20 15:46:56', NULL),
(104, 17, 'NSR.12.12', '2025-05-20 15:48:20', NULL),
(105, 23, 'NSR.11.90', '2025-05-20 15:48:35', NULL),
(106, 23, 'NSR.11.90', '2025-05-20 15:48:54', NULL),
(107, 23, 'NSR.11.90', '2025-05-20 15:50:09', NULL),
(108, 17, 'NSR.12.12', '2025-05-20 16:21:14', NULL),
(109, 23, 'NSR.11.90', '2025-05-20 16:27:18', NULL),
(110, 15, 'NSR.123.14', '2025-05-20 18:30:04', NULL),
(111, 17, 'NSR.12.12', '2025-05-20 21:39:17', NULL),
(112, 15, 'NSR.123.14', '2025-05-20 21:53:26', NULL),
(113, 17, 'NSR.12.12', '2025-05-20 22:12:52', NULL),
(114, 17, 'NSR.12.12', '2025-05-20 23:29:04', NULL),
(115, 17, 'NSR.12.12', '2025-05-21 16:58:43', NULL),
(116, 20, 'SSR.1856.19', '2025-05-21 17:01:14', NULL),
(117, 17, 'NSR.12.12', '2025-05-21 17:03:01', NULL),
(118, 15, 'NSR.123.14', '2025-05-21 17:06:21', NULL),
(119, 23, 'NSR.11.90', '2025-05-21 17:17:04', NULL),
(120, 15, 'NSR.123.14', '2025-05-21 17:46:35', NULL),
(121, 17, 'NSR.12.12', '2025-05-21 17:51:40', NULL),
(122, 23, 'NSR.11.90', '2025-05-22 14:50:35', NULL),
(123, 23, 'NSR.11.90', '2025-05-22 17:19:40', NULL),
(124, 17, 'NSR.12.12', '2025-05-22 17:29:32', NULL),
(125, 47, 'ADM.123.12', '2025-05-22 17:35:18', NULL),
(126, 17, 'NSR.12.12', '2025-05-22 21:24:54', NULL),
(127, 47, 'ADM.123.12', '2025-05-22 21:26:59', NULL),
(128, 23, 'NSR.11.90', '2025-05-23 00:07:02', NULL),
(129, 15, 'NSR.123.14', '2025-05-23 00:57:58', NULL),
(130, 43, 'NSR.1234.11', '2025-05-23 01:01:17', NULL),
(131, 17, 'NSR.12.12', '2025-05-23 01:05:54', NULL),
(132, 17, 'NSR.12.12', '2025-05-23 07:50:30', NULL),
(133, 23, 'NSR.11.90', '2025-05-23 07:53:37', NULL),
(134, 17, 'NSR.12.12', '2025-05-23 08:23:10', NULL),
(135, 17, 'NSR.12.12', '2025-05-23 08:24:13', NULL),
(136, 17, 'NSR.12.12', '2025-05-23 08:24:31', NULL),
(137, 23, 'NSR.11.90', '2025-05-23 08:25:10', NULL),
(138, 17, 'NSR.12.12', '2025-05-23 08:45:38', NULL),
(139, 17, 'NSR.12.12', '2025-05-23 08:46:50', NULL),
(140, 23, 'NSR.11.90', '2025-05-23 09:21:41', NULL),
(141, 15, 'NSR.123.14', '2025-05-23 10:11:59', NULL),
(142, 48, 'NSR.2487.14', '2025-05-23 14:46:05', NULL),
(143, 48, 'NSR.2487.14', '2025-05-23 14:50:53', NULL),
(144, 47, 'ADM.123.12', '2025-05-23 17:28:07', NULL),
(145, 17, 'NSR.12.12', '2025-05-23 17:36:27', NULL),
(146, 47, 'ADM.123.12', '2025-05-23 18:03:10', NULL),
(147, 47, 'ADM.123.12', '2025-05-23 20:41:22', NULL),
(148, 17, 'NSR.12.12', '2025-05-23 23:11:41', NULL),
(149, 55, 'STF.12.15', '2025-05-23 23:56:55', NULL),
(150, 17, 'NSR.12.12', '2025-05-24 00:06:49', NULL),
(151, 55, 'STF.12.15', '2025-05-24 00:09:09', NULL),
(152, 17, 'NSR.12.12', '2025-05-24 00:11:25', NULL),
(153, 15, 'NSR.123.14', '2025-05-24 00:22:54', NULL),
(154, 56, 'ADM.12.16', '2025-05-24 00:41:58', NULL),
(155, 17, 'NSR.12.12', '2025-05-24 00:42:43', NULL),
(156, 56, 'ADM.12.16', '2025-05-24 01:00:45', NULL),
(157, 17, 'NSR.12.12', '2025-05-24 01:03:13', NULL),
(158, 56, 'ADM.12.16', '2025-05-24 01:05:35', NULL),
(159, 17, 'NSR.12.12', '2025-05-24 01:07:34', NULL),
(160, 56, 'ADM.12.16', '2025-05-24 07:16:31', NULL),
(161, 59, 'NSR.2487.14', '2025-05-24 08:56:16', NULL),
(162, 56, 'ADM.12.16', '2025-05-24 10:05:13', NULL),
(163, 17, 'NSR.12.12', '2025-05-24 10:25:27', NULL),
(164, 17, 'NSR.12.12', '2025-05-25 10:20:10', NULL),
(165, 59, 'NSR.2487.14', '2025-05-25 10:26:09', NULL),
(166, 23, 'NSR.11.90', '2025-05-25 10:47:30', NULL),
(167, 23, 'NSR.11.90', '2025-05-25 11:06:01', NULL),
(168, 59, 'NSR.2487.14', '2025-05-25 11:15:42', NULL),
(169, 17, 'NSR.12.12', '2025-05-25 11:42:19', NULL),
(170, 60, 'NSR.1234.16', '2025-05-25 11:54:30', NULL),
(171, 15, 'NSR.123.14', '2025-05-25 11:56:54', NULL),
(172, 60, 'NSR.1234.16', '2025-05-25 12:02:48', NULL),
(173, 17, 'NSR.12.12', '2025-05-25 12:12:52', NULL),
(174, 56, 'ADM.12.16', '2025-05-25 12:15:23', NULL),
(175, 17, 'NSR.12.12', '2025-05-25 12:41:16', NULL),
(176, 60, 'NSR.1234.16', '2025-05-25 12:52:38', NULL),
(177, 15, 'NSR.123.14', '2025-05-25 12:53:41', NULL),
(178, 17, 'NSR.12.12', '2025-05-25 12:58:58', NULL),
(179, 62, 'NSR.33.33', '2025-05-25 15:37:59', NULL),
(180, 17, 'NSR.12.12', '2025-05-25 15:54:50', NULL),
(181, 23, 'NSR.11.90', '2025-05-25 16:06:51', NULL),
(182, 17, 'NSR.12.12', '2025-05-25 17:21:11', NULL),
(183, 23, 'NSR.11.90', '2025-05-26 19:13:14', NULL),
(184, 17, 'NSR.12.12', '2025-05-26 19:19:22', NULL),
(185, 23, 'NSR.11.90', '2025-05-27 07:44:04', NULL),
(186, 23, 'NSR.11.90', '2025-05-27 14:04:39', NULL),
(187, 23, 'NSR.11.90', '2025-05-28 13:26:48', NULL),
(188, 59, 'NSR.2487.14', '2025-05-28 13:41:38', NULL),
(189, 23, 'NSR.11.90', '2025-05-28 13:48:51', NULL),
(190, 60, 'NSR.1234.16', '2025-05-28 16:19:10', NULL),
(191, 60, 'NSR.1234.16', '2025-05-29 14:47:59', NULL),
(192, 59, 'NSR.2487.14', '2025-05-29 20:36:57', NULL),
(193, 53, 'NSR.199.15', '2025-05-31 05:27:57', NULL);

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
(12, '******0884', '103634', '2025-05-25 16:37:35', 1, 16, '2025-05-15 00:48:40', '2025-05-25 13:35:41'),
(13, '+251905610884', '527381', '2025-05-16 17:38:20', 0, 0, '2025-05-15 17:08:21', '2025-05-16 15:33:20'),
(14, '******9897', '557', '2025-05-16 16:21:02', 0, 2, '2025-05-16 14:16:22', '2025-05-16 14:19:02'),
(15, '******0899', '645703', '2025-05-16 16:23:44', 0, 1, '2025-05-16 14:21:44', '2025-05-16 14:21:44'),
(16, '******0893', '975622', '2025-05-16 16:43:10', 0, 3, '2025-05-16 14:36:20', '2025-05-16 14:41:10'),
(17, '******1212', '763992', '2025-05-24 08:25:52', 1, 4, '2025-05-16 14:43:04', '2025-05-24 05:24:00'),
(18, '+251916528975', '315046', '2025-05-16 18:47:35', 1, 0, '2025-05-16 15:34:37', '2025-05-16 15:42:44'),
(19, '******8975', '666247', '2025-05-16 18:50:45', 0, 0, '2025-05-16 15:48:45', '2025-05-16 15:48:45'),
(20, '******1415', '844464', '2025-05-16 18:52:30', 0, 0, '2025-05-16 15:50:30', '2025-05-16 15:50:30'),
(21, '******+251', '773617', '2025-05-16 18:58:59', 1, 0, '2025-05-16 15:56:59', '2025-05-16 15:57:08'),
(22, '******1111', '276556', '2025-05-31 02:26:44', 1, 0, '2025-05-16 15:58:37', '2025-05-30 23:24:49'),
(23, '******1213', '660670', '2025-05-24 09:13:39', 1, 0, '2025-05-24 06:11:39', '2025-05-24 06:11:46'),
(24, '******0887', '709246', '2025-05-24 09:16:07', 1, 0, '2025-05-24 06:14:07', '2025-05-24 06:14:12'),
(25, '******0886', '035541', '2025-05-24 09:22:57', 1, 0, '2025-05-24 06:20:57', '2025-05-24 06:21:03');

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
(22, 'dean'),
(23, 'chair man'),
(24, 'computer science rep');

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
(8, 'NSR/123/14', 1, 'Fraud', 'bjhb', 'mhjv', 'resolved', '2025-05-09 22:00:34', 'kjkhjghxcv'),
(9, 'NSR/18/12', 1, 'Mismanagement', 'kkjiohuhgyuggjkjnuihu', 'hbhiokdkhnhjnk,ojnkkjkkgkouikkhkkmmmmmnnfgkgkhkouityyyoyoyyoyooymxnmfkmn&#39;\r\n\r\nn xfp\r\nx\r\n\r\nxx\r\nxxfk\r\nx\r\nx\r\nx\r\nxm\r\n\r\nxj\r\nxm\r\n\r\nxjk[ykkjm\r\nkofx\r\n', 'resolved', '2025-05-10 09:05:07', 'hgfdcvn'),
(10, 'NSR/12/12', 1, 'Mismanagement', 'hgfdgchvb', ';koiuyfdtcvgbhknmk,', 'resolved', '2025-05-16 19:14:46', 'lbihhjbhnjlnl'),
(11, 'NSR/01/01', 2, 'Mismanagement', 'make me l', 'asdfghjkl;&#39;\r\n&#39;;lkjhgfdsa', 'resolved', '2025-05-18 15:14:36', 'ok i will'),
(12, 'NSR/12345/12', 1, 'Fraud', 'make me l', 'rheahdhst', 'resolved', '2025-05-19 06:40:43', 'bvhdsbvkjdavbjd,'),
(13, 'NSR/123/14', 1, 'Mismanagement', 'hgfdgchvb', 'jkhsgfswqcvbkne', 'pending', '2025-05-20 17:22:18', 'qwfVAD'),
(14, 'NSR/123/14', 1, 'Intimidation', 'make me l', 'hey', '', '2025-05-20 17:23:52', 'wait'),
(15, 'NSR/123/14', 2, 'Fraud', 'erf', 'mmmmmmmm', 'pending', '2025-05-21 15:08:45', 'ok'),
(16, 'NSR/2487/14', 2, 'Fraud', 'ok', 'bye', 'resolved', '2025-05-23 12:47:50', 'ujkjhjkghghgh'),
(17, 'NSR/123/14', 0, 'froud', 'the election is not fair', 'asdfghtrewasx', 'pending', '2025-05-23 22:31:38', NULL);

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
(5, 'Faculty'),
(4, 'mini_admin'),
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
  `lockout_until` datetime DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `club_membership` varchar(100) DEFAULT NULL,
  `is_class_representative` varchar(3) DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `firstname`, `lastname`, `phone`, `email`, `role_id`, `gender`, `status`, `account`, `registration_date`, `id_number`, `failed_login_attempts`, `last_failed_attempt_time`, `is_locked`, `lockout_until`, `department`, `club_membership`, `is_class_representative`) VALUES
(15, 'NSR.123.14', '$2y$10$dH5VRyGxWnicWCaqBZBrOuMeLlcejB191TRrIcTv/THBcxuVK6HGa', 'Absir', 'aa', '******5771', 'dessieeshete@gmail.com', 3, 'male', 'Unvoted', 'Active', '2025-05-08', 'NSR/123/14', 0, NULL, 0, NULL, 'Civil', 'Minimedia', 'Yes'),
(16, 'NSR.18.12', '$2y$10$zhZsc5pqyKpRLcVTXZ050eBlDBBZtFqYLJDHJUAPz8fx/khS7G2Gy', 'nn', 'nn', '******5771', 'bea@gmail.com', 3, 'male', 'Voted', 'Inactive', '2025-05-08', 'NSR/18/12', 0, NULL, 0, NULL, 'Mechanical', 'Infoken', 'Yes'),
(17, 'NSR.12.12', '$2y$10$D1nPzG6RoWNN4pWSGtoqtup0soBRRyI4R3zVAQYoqrjz9fs50FmYS', 'aa', 'aa', '******5771', 'nattysertse@gmail.com', 1, 'male', 'Voted', 'Inactive', '2025-05-09', 'NSR/12/12', 0, NULL, 0, NULL, NULL, NULL, 'No'),
(19, 'NSR.14.11', '$2y$10$tSgSc2HnHSIfgzcb5qExYOIqZP07Hb/44MWZuWvY0PXFj5yHlmzsi', 'aa', '', '0996785771', '', 2, 'Male', 'Unvoted', 'Inactive', NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'No'),
(20, 'SSR.1856.19', '$2y$10$53PlpLkjIQOANraGLDAwNOsgStDvdfLN5F86s2gawzMo6yhGqzUg.', 'm', '', '0996785771', '', 2, 'Female', 'Unvoted', 'Inactive', NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'No'),
(22, 'NSR.1.11', '$2y$10$Wf7sUBcar7mMQevlkUJ1kuOtmzjbhKGWwaypYMiCDzmYbs7.72lnS', 'bakala', 'chala', '0996785771', '', 2, 'Male', 'Unvoted', 'Inactive', NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'No'),
(23, 'NSR.11.90', '$2y$10$T676o89kE6oXdhuL9APExekigH7GY7l.gTE8iny4p3eb8Mh.JIrSu', 'qq', 'qq', '0996785771', 'nattysertse@gmail.com', 2, 'Male', 'active', 'Inactive', '2025-05-10', 'NSR/11/90', 0, '2025-05-16 19:14:19', 0, NULL, NULL, NULL, 'No'),
(39, 'NSR.1111.11', '$2y$10$nFeVBIO3h5QVpbfw5twu5ur53zXWuLPSTtfadOjFlWF9ZXMNmx3be', 'aa', 'aa', '******0884', '', 3, 'Male', 'Unvoted', 'Active', '2025-05-16', 'NSR/1111/11', 0, NULL, 0, NULL, 'Hydropower', 'Minimedia', 'Yes'),
(40, 'PGR.185.12', '$2y$10$9vVN14FS2VB3uJqd5dp0z.RSeSwP6i5kKiuIP6040fNV2qjJAoHki', 'hghf', 'jhg', '******0884', '', 3, 'Female', 'Unvoted', 'Inactive', '2025-05-16', 'PGR/185/12', 0, NULL, 0, NULL, 'Software', 'Infoken', 'Yes'),
(44, 'NSR.01.01', '$2y$10$50Jufzjx/Vka4s2xOf5AGOpwcDslRg7gTK1w/PF5HEiSYtUqBwtUe', 'aa', 'bb', '******0884', 'nattysertse@gmail.com', 3, 'Male', 'Unvoted', 'Active', '2025-05-18', 'NSR/01/01', 0, NULL, 0, NULL, 'Computer', 'Infoken', 'Yes'),
(45, 'PGR.123.12', '$2y$10$LAmr7y4CqEN3VZtchS3PGuCvZYPi2NwsZYCbHpWG1Cn0GoSy4IMJy', 'aa', 'aa', '******1212', 'minte@gmail.com', 3, 'Male', 'Unvoted', 'Active', '2025-05-18', 'PGR/123/12', 0, NULL, 0, NULL, 'Computer', 'Charity', 'Yes'),
(47, 'ADM.123.12', '$2y$10$qIwBTe/w2jLUljewjvS8UOLO3YIBlyKOmkWeUcd26/Rrx2QLTatrO', 'natnael', 'serte', '0916528975', 'natisertse@gmail.com', 1, 'Male', 'Unvoted', 'Inactive', '2025-05-22', 'ADM/123/12', 0, NULL, 0, NULL, NULL, NULL, 'No'),
(52, 'NSR.12345.12', '$2y$10$zr/V/zb2hPJSA/goQa/to.lMSB7IBgvf6rdxtoka015RkKQh09A8a', 'aa', 'aa', '******0884', 'nataysetse@gmail.com', 3, 'Male', 'Unvoted', 'Inactive', '2025-05-23', 'NSR/12345/12', 0, NULL, 0, NULL, 'Electrical', 'Charity', 'Yes'),
(53, 'NSR.199.15', '$2y$10$kWsYdb5ovBb2FjauKasDA.Bw28MZNzdvUt4o.91t0cUJiMhjIoNE2', 'natty', 'serte', '******1212', 'natsetse@gmail.com', 3, 'Male', 'Unvoted', 'Inactive', '2025-05-23', 'NSR/199/15', 0, NULL, 0, NULL, 'Electrical', 'Charity', 'Yes'),
(55, 'STF.12.15', '$2y$10$KTc4c3FLvr2x8k40XUlrrOaJqvrrkQ6rj4C3x9TzJ.bc3uwr6kFC.', 'natnael', 'serte', '0905610884', 'nattty@gmail.com', 4, 'Male', 'Unvoted', 'Inactive', '2025-05-24', 'STF/12/15', 0, NULL, 0, NULL, NULL, NULL, 'No'),
(56, 'ADM.12.16', '$2y$10$hOm.t9qXGC/lXG77r7jzXOpRrbgTUfyaDqfP6vPd5LoNCR39r3e2i', 'nat', 'serte', '0905610880', 'll@gmail.com', 4, 'Male', 'Unvoted', 'Inactive', '2025-05-24', 'ADM/12/16', 0, NULL, 0, NULL, NULL, NULL, 'No'),
(57, 'STF.12.17', '$2y$10$rYimup7pW4WZ.bw35HgFTecbxLRnKl4BTqSYdhQ7x.uLkQbITGD92', 'natnael', 'ser', '0905610889', 'bab@gmail.com', 4, 'Male', 'Unvoted', 'Inactive', '2025-05-24', 'STF/12/17', 1, NULL, 0, NULL, NULL, NULL, 'No'),
(59, 'NSR.2487.14', '$2y$10$UUw99y9fVSw/GkAbmE.mz.j6AdQ8uGfbjMo8gwph8f4IThaYq8OnO', 'minte', 'hhh', '******0886', 'ac@gmail.com', 5, 'Male', 'Voted', 'Active', '2025-05-24', 'NSR/2487/14', 0, NULL, 0, NULL, 'Computer', 'Charity', 'No'),
(60, 'NSR.1234.16', '$2y$10$bAOvEYxj7VqKWn5Ibq8/FuTqL4aMSKy1eKHJ.h7pq/DERWGAFo8W6', 'natnael', 'esh', '******0884', 'natayserte60@gmail.com', 5, 'Male', 'Voted', 'Active', '2025-05-25', 'NSR/1234/16', 0, NULL, 0, NULL, 'Mechanical', 'None', 'No'),
(61, 'NSR.11.16', '$2y$10$8dNgqjAHsEhvduoxceZUZOV.0iy31RfQc6PWpTyO6gKPYlKwJqUUe', 'aa', 'aa', '******0884', 'natayserte90@gmail.com', 3, 'Male', 'Unvoted', 'Active', '2025-05-25', 'NSR/11/16', 0, NULL, 0, NULL, 'Software', 'Charity', 'No'),
(62, 'NSR.33.33', '$2y$10$PYZtr4nIeBnl53HdofTncOKv/aldoqF2Oy7HT1Sd3PeZU0aqOkFcC', 'aa', 'aa', '******0884', 'nataysere0@gmail.com', 5, 'Male', 'Unvoted', 'Active', '2025-05-25', 'NSR/33/33', 2, NULL, 0, NULL, 'registerer', 'None', 'No'),
(63, '123nat', '$2y$10$S1o3pKhxIWbdetcT53vWWeIOyk5FsdUAf75Jkl5wRGJD.5vRpMWD.', 'nat', 'nat', '******1111', 'michaelegashaw859@gmail.com', 5, 'Male', 'Unvoted', 'Active', '2025-05-31', '123nat', 0, NULL, 0, NULL, 'Computer', 'None', 'No'),
(64, 'nat123', '$2y$10$6ObMm0ORQv4neKhFqV7KOuFymAQc8W5vBVlzVE.XkuVTa.E.P8i/m', 'Tamen', 'Terfe', '******1111', 'michaelegashawu859@gmail.com', 3, 'Male', 'Voted', 'Active', '2025-05-31', 'nat123', 0, NULL, 0, NULL, 'IT', 'Infoken', 'No');

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
(196, 'FBErpg7pyuvSCm2XAkx/BA==', '46'),
(197, '49IW+WEdgtt3bhT7bB+Iww==', '15'),
(198, 'sVAykc2wjPFKlpNUnMOXJA==', '15'),
(199, 'cJRYkLNiQsKdrT1jxkmQFg==', '15'),
(200, 'j7mGOxIibf3JnDCvrci/TQ==', '15'),
(201, '7df4GNFcbLUTHUqQfZJboA==', '15'),
(202, 'cJRYkLNiQsKdrT1jxkmQFg==', '15'),
(203, 'PLyrzXISv41QNqou6Nn/dQ==', '43'),
(204, 'DCtgwzwzQ1PI8rIiHzJdBA==', '43'),
(205, 'LmRFoPkiMuB6/J8sUL2TWw==', '43'),
(206, '49IW+WEdgtt3bhT7bB+Iww==', '48'),
(207, 'DCtgwzwzQ1PI8rIiHzJdBA==', '48'),
(208, 'LX/87gOujycjbIfgrh1eXQ==', '48'),
(209, 'nmg665A3AlCsqHvPSnrWqw==', '59'),
(210, 'EYSPdatfoX1VEjjlH3WygA==', '59'),
(211, '4YWCoc3y2vuKkj8vaNIpjQ==', '59'),
(212, 'M753RE8xtIsPDdn6DZAUCg==', '59'),
(213, 'WAdAJhY9Zt+pCsn65IKFUw==', '64'),
(214, 'uixe84jrJyOntupdeAhFNA==', '59'),
(215, 'M753RE8xtIsPDdn6DZAUCg==', '59'),
(216, 'uixe84jrJyOntupdeAhFNA==', '59'),
(217, 'M753RE8xtIsPDdn6DZAUCg==', '59'),
(218, 'uixe84jrJyOntupdeAhFNA==', '60'),
(219, 'M753RE8xtIsPDdn6DZAUCg==', '60');

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
(3, 'rep election', 1747475994, 1747843422, 0),
(4, 'rep election', 1747942506, 1747946106, 0),
(5, 'leader election', 1748075611, 1748079211, 0);

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
-- Indexes for table `candidate_evaluations`
--
ALTER TABLE `candidate_evaluations`
  ADD PRIMARY KEY (`evaluation_id`),
  ADD UNIQUE KEY `uq_candidate_evaluator` (`candidate_id`,`evaluator_user_id`),
  ADD KEY `evaluator_user_id` (`evaluator_user_id`);

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
-- Indexes for table `otp_table`
--
ALTER TABLE `otp_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_number` (`phone_number`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `candidate_evaluations`
--
ALTER TABLE `candidate_evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `otp_table`
--
ALTER TABLE `otp_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `report_complaints`
--
ALTER TABLE `report_complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `voting_events`
--
ALTER TABLE `voting_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidate_evaluations`
--
ALTER TABLE `candidate_evaluations`
  ADD CONSTRAINT `candidate_evaluations_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidate` (`candidate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `candidate_evaluations_ibfk_2` FOREIGN KEY (`evaluator_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `role_table` (`role_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
