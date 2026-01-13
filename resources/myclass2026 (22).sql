-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2026 at 05:46 PM
-- Server version: 8.0.33
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myclass2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `active` tinyint NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `name`, `start_date`, `end_date`, `school_id`, `active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '2026-2027', '2025-08-06', '2026-06-03', 1, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `activity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `page_url`, `created_at`) VALUES
(1, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/questions', '2026-01-13 15:21:49'),
(2, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:21:50'),
(3, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:21:50'),
(4, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:21:50'),
(5, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:21:50'),
(6, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:21:50'),
(7, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:21:50'),
(8, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:21:51'),
(9, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:21:51'),
(10, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/questions', '2026-01-13 15:23:54'),
(11, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:23:55'),
(12, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:23:55'),
(13, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:23:56'),
(14, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:23:56'),
(15, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:23:56'),
(16, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:23:56'),
(17, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:23:56'),
(18, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:23:57'),
(19, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/questions', '2026-01-13 15:24:16'),
(20, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:24:16'),
(21, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:24:17'),
(22, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:24:17'),
(23, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:24:17'),
(24, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:24:17'),
(25, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:24:17'),
(26, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:24:18'),
(27, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:24:18'),
(28, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/questions', '2026-01-13 15:25:11'),
(29, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:25:12'),
(30, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:25:12'),
(31, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:25:12'),
(32, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:25:12'),
(33, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:25:12'),
(34, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:25:12'),
(35, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:25:13'),
(36, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:25:13'),
(37, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/questions', '2026-01-13 15:25:58'),
(38, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:25:59'),
(39, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:25:59'),
(40, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:25:59'),
(41, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:25:59'),
(42, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:26:00'),
(43, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:26:00'),
(44, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:26:00'),
(45, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:26:00'),
(46, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/questions', '2026-01-13 15:40:16'),
(47, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/questions', '2026-01-13 15:40:16'),
(48, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:40:16'),
(49, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:40:16'),
(50, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:42:47'),
(51, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:42:48'),
(52, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:42:48'),
(53, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:42:58'),
(54, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:42:58'),
(55, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:42:58'),
(56, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:44:26'),
(57, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:44:27'),
(58, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:44:27'),
(59, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-13 15:44:33'),
(60, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:44:33'),
(61, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:44:34'),
(62, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:44:34'),
(63, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:44:34'),
(64, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:44:34'),
(65, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:44:35'),
(66, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 15:45:54'),
(67, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:50:38'),
(68, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:50:39'),
(69, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:50:39'),
(70, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 15:51:14'),
(71, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:51:34'),
(72, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:51:34'),
(73, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:51:34'),
(74, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 15:51:42'),
(75, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:52:15'),
(76, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:52:16'),
(77, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:52:16'),
(78, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:52:16'),
(79, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 15:52:21'),
(80, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:53:04'),
(81, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:53:04'),
(82, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:53:04'),
(83, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 15:53:39'),
(84, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:53:39'),
(85, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:53:39'),
(86, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 15:53:53'),
(87, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 15:57:55'),
(88, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:58:17'),
(89, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:58:18'),
(90, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:58:18'),
(91, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:58:18'),
(92, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 15:59:19'),
(93, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:59:30'),
(94, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:59:30'),
(95, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 15:59:33'),
(96, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:59:33'),
(97, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 15:59:33'),
(98, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 15:59:43'),
(99, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:02:21'),
(100, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:02:21'),
(101, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:02:25'),
(102, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:02:26'),
(103, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:02:27'),
(104, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:02:27'),
(105, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:02:28'),
(106, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:02:28'),
(107, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:02:37'),
(108, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:10:04'),
(109, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:10:04'),
(110, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:10:11'),
(111, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:12:47'),
(112, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:12:47'),
(113, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:12:48'),
(114, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:12:49'),
(115, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:14:14'),
(116, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:14:15'),
(117, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:14:15'),
(118, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:14:55'),
(119, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:21:32'),
(120, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:21:32'),
(121, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:21:34'),
(122, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:21:34'),
(123, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:21:34'),
(124, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:21:38'),
(125, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1/edit', '2026-01-13 16:22:37'),
(126, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1/edit', '2026-01-13 16:22:41'),
(127, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:41'),
(128, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:41'),
(129, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:42'),
(130, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:42'),
(131, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:42'),
(132, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:42'),
(133, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:22:43'),
(134, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:22:44'),
(135, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:44'),
(136, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:44'),
(137, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:44'),
(138, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:44'),
(139, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:22:56'),
(140, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:57'),
(141, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:22:57'),
(142, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:33:13'),
(143, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:33:13'),
(144, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:33:13'),
(145, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:33:19'),
(146, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:33:25'),
(147, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:33:25'),
(148, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:34:13'),
(149, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/1', '2026-01-13 16:34:21'),
(150, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:34:21'),
(151, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 16:35:08'),
(152, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:35:31'),
(153, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:35:31'),
(154, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:35:31'),
(155, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 16:35:35'),
(156, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:35:40'),
(157, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:35:40'),
(158, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 16:39:42'),
(159, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:39:47'),
(160, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:39:48'),
(161, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:39:48'),
(162, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 16:41:36'),
(163, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 16:41:43'),
(164, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:41:59'),
(165, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:41:59'),
(166, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:42:00'),
(167, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:43:17'),
(168, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:43:17'),
(169, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:43:17'),
(170, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:44:00'),
(171, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:44:00'),
(172, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:44:00'),
(173, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 16:44:04'),
(174, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:44:40'),
(175, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:45:11'),
(176, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:45:11'),
(177, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 16:46:18'),
(178, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 16:46:35'),
(179, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:46:35'),
(180, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:46:35'),
(181, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:46:53'),
(182, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:46:54'),
(183, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:46:54'),
(184, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:46:54'),
(185, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 16:47:25'),
(186, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:47:28'),
(187, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 16:47:37'),
(188, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 16:47:49'),
(189, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:47:49'),
(190, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:47:49'),
(191, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 16:48:02'),
(192, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:48:12'),
(193, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:53:29'),
(194, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:53:29'),
(195, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:53:37'),
(196, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:53:37'),
(197, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:53:37'),
(198, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:53:46'),
(199, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 16:54:15'),
(200, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:54:15'),
(201, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 16:54:15'),
(202, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:54:19'),
(203, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:56:04'),
(204, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:56:04'),
(205, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:56:04'),
(206, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:56:31'),
(207, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:56:32'),
(208, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:56:32'),
(209, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:56:57'),
(210, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:56:57'),
(211, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 16:56:57'),
(212, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 16:57:24'),
(213, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 17:00:29'),
(214, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 17:00:32'),
(215, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:00:40'),
(216, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:00:41'),
(217, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 17:00:43'),
(218, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 17:00:47'),
(219, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 17:00:47'),
(220, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 17:00:47'),
(221, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:00:58'),
(222, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:00:59'),
(223, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 17:00:59'),
(224, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 17:00:59'),
(225, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 17:01:14'),
(226, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 17:01:20'),
(227, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 17:01:25'),
(228, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:01:25'),
(229, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:01:25'),
(230, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 17:01:28'),
(231, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:01:34'),
(232, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:01:34'),
(233, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2', '2026-01-13 17:01:41'),
(234, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:01:48'),
(235, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:01:48'),
(236, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/2/edit', '2026-01-13 17:01:51'),
(237, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 17:02:00'),
(238, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 17:02:06'),
(239, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 17:02:11'),
(240, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 17:02:14'),
(241, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 17:02:17'),
(242, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 17:02:18'),
(243, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 17:02:20'),
(244, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams/questions/available', '2026-01-13 17:02:22'),
(245, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:02:23'),
(246, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/exams', '2026-01-13 17:02:36'),
(247, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 17:02:36'),
(248, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 17:02:36'),
(249, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/student/exams', '2026-01-13 17:23:42'),
(250, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 17:23:43'),
(251, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-13 17:23:43'),
(252, 19, 'Visited a page', 'http://127.0.0.1:8000/qu/student/exams/2/start', '2026-01-13 17:23:58');

-- --------------------------------------------------------

--
-- Table structure for table `behaviors`
--

CREATE TABLE `behaviors` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `year_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('positive','negative') COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `behavior_incidents`
--

CREATE TABLE `behavior_incidents` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `classroom_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `reported_by` bigint UNSIGNED DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `student_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade` tinyint UNSIGNED DEFAULT NULL,
  `student_grade_snapshot` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `student_section_snapshot` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `period_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incident_type` json NOT NULL,
  `location` json NOT NULL,
  `behavior` json NOT NULL,
  `description` json DEFAULT NULL,
  `motivation` json DEFAULT NULL,
  `others_involved` json DEFAULT NULL,
  `teacher_action` json DEFAULT NULL,
  `admin_action` json DEFAULT NULL,
  `primary_behavior_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_location_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `severity` enum('minor','moderate','major') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minor',
  `status` enum('open','in_review','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `follow_up_needed` tinyint(1) NOT NULL DEFAULT '0',
  `points_deducted` smallint NOT NULL DEFAULT '0',
  `points_awarded` smallint NOT NULL DEFAULT '0',
  `visible_to_parent` tinyint(1) NOT NULL DEFAULT '1',
  `parent_viewed_at` timestamp NULL DEFAULT NULL,
  `parent_notified_at` timestamp NULL DEFAULT NULL,
  `parent_notified_by` bigint UNSIGNED DEFAULT NULL,
  `critical_alert` tinyint(1) NOT NULL DEFAULT '0',
  `escalated_at` timestamp NULL DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `submitted_via` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_year_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('myclass_cache_66b9459dcf12e626ae799bab31d9144c', 'i:1;', 1768314452),
('myclass_cache_66b9459dcf12e626ae799bab31d9144c:timer', 'i:1768314452;', 1768314452),
('myclass_cache_9ed5c6585c35a166c410067382a1dd6b', 'i:1;', 1768150177),
('myclass_cache_9ed5c6585c35a166c410067382a1dd6b:timer', 'i:1768150177;', 1768150177),
('myclass_cache_spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:50:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"manage app\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:22:\"manage system settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"manage users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"view users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"create users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:10:\"edit users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:12:\"delete users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:14:\"manage schools\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:9;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"view schools\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:14:\"create schools\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:9;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"edit schools\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:14:\"delete schools\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:9:\"manage hr\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:9;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:7:\"view hr\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:9;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:15:\"manage teachers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:9;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:13:\"view teachers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:9;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:15:\"create teachers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:13:\"edit teachers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:15:\"delete teachers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:15:\"import teachers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:9;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:15:\"manage students\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:13:\"view students\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:5;i:3;i:9;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:15:\"create students\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:13:\"edit students\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:15:\"delete students\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:15:\"import students\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:15:\"manage subjects\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:13:\"manage grades\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:17:\"manage classrooms\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:16:\"manage schedules\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:17:\"manage curriculum\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:18:\"create assignments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:9;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:17:\"grade assignments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:9;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:23:\"create course materials\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:9;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:21:\"manage student grades\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:9;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:21:\"view student progress\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:5;i:4;i:7;i:5;i:9;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:23:\"access course materials\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:5;i:2;i:6;i:3;i:9;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:18:\"submit assignments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:6;i:2;i:9;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:11:\"view grades\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:5;i:2;i:6;i:3;i:7;i:4;i:9;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:25:\"communicate with students\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:9;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:25:\"communicate with teachers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:7;i:4;i:9;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:24:\"communicate with parents\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:5;i:3;i:9;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:9;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:16:\"generate reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:12:\"manage roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:18:\"manage permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:15:\"manage settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:13:\"view settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:21:\"review course content\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:4;i:2;i:9;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:21:\"participate in forums\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:5;i:2;i:6;i:3;i:9;}}}s:5:\"roles\";a:8:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"SuperSystem\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:8:\"hr_admin\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"supervisor\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:7:\"teacher\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:6:\"parent\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:7:\"student\";s:1:\"c\";s:3:\"web\";}}}', 1768145857);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendars`
--

CREATE TABLE `calendars` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `semester_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1: work, 0: day_off, 2: activity, 3: test, 4: final_exam ,5: holiday ,6: more ',
  `vacation_all` tinyint(1) NOT NULL DEFAULT '0',
  `vacation_teachers` tinyint(1) DEFAULT NULL,
  `vacation_students` tinyint(1) DEFAULT NULL,
  `day_number` tinyint NOT NULL COMMENT '1: Sunday, 2: Monday, 3: Tuesday, 4: Wednesday, 5: Thursday',
  `week_number` tinyint DEFAULT NULL,
  `data` json DEFAULT NULL,
  `notes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `calendars`
--

INSERT INTO `calendars` (`id`, `date`, `semester_id`, `academic_year_id`, `school_id`, `status`, `vacation_all`, `vacation_teachers`, `vacation_students`, `day_number`, `week_number`, `data`, `notes`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '2025-08-24', 1, 1, 1, 1, 0, NULL, NULL, 7, 1, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(2, '2025-08-25', 1, 1, 1, 1, 0, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(3, '2025-08-26', 1, 1, 1, 1, 0, NULL, NULL, 2, 1, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(4, '2025-08-27', 1, 1, 1, 1, 0, NULL, NULL, 3, 1, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(5, '2025-08-28', 1, 1, 1, 1, 0, NULL, NULL, 4, 1, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(6, '2025-08-29', 1, 1, 1, 0, 0, NULL, NULL, 5, 1, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(7, '2025-08-30', 1, 1, 1, 0, 0, NULL, NULL, 6, 1, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(8, '2025-08-31', 1, 1, 1, 1, 0, NULL, NULL, 7, 2, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(9, '2025-09-01', 1, 1, 1, 1, 0, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(10, '2025-09-02', 1, 1, 1, 1, 0, NULL, NULL, 2, 2, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(11, '2025-09-03', 1, 1, 1, 1, 0, NULL, NULL, 3, 2, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(12, '2025-09-04', 1, 1, 1, 1, 0, NULL, NULL, 4, 2, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(13, '2025-09-05', 1, 1, 1, 0, 0, NULL, NULL, 5, 2, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(14, '2025-09-06', 1, 1, 1, 0, 0, NULL, NULL, 6, 2, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(15, '2025-09-07', 1, 1, 1, 1, 0, NULL, NULL, 7, 3, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(16, '2025-09-08', 1, 1, 1, 1, 0, NULL, NULL, 1, 3, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(17, '2025-09-09', 1, 1, 1, 1, 0, NULL, NULL, 2, 3, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(18, '2025-09-10', 1, 1, 1, 1, 0, NULL, NULL, 3, 3, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(19, '2025-09-11', 1, 1, 1, 1, 0, NULL, NULL, 4, 3, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(20, '2025-09-12', 1, 1, 1, 0, 0, NULL, NULL, 5, 3, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(21, '2025-09-13', 1, 1, 1, 0, 0, NULL, NULL, 6, 3, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(22, '2025-09-14', 1, 1, 1, 1, 0, NULL, NULL, 7, 4, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(23, '2025-09-15', 1, 1, 1, 1, 0, NULL, NULL, 1, 4, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(24, '2025-09-16', 1, 1, 1, 1, 0, NULL, NULL, 2, 4, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(25, '2025-09-17', 1, 1, 1, 1, 0, NULL, NULL, 3, 4, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(26, '2025-09-18', 1, 1, 1, 1, 0, NULL, NULL, 4, 4, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(27, '2025-09-19', 1, 1, 1, 0, 0, NULL, NULL, 5, 4, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(28, '2025-09-20', 1, 1, 1, 0, 0, NULL, NULL, 6, 4, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(29, '2025-09-21', 1, 1, 1, 1, 0, NULL, NULL, 7, 5, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(30, '2025-09-22', 1, 1, 1, 1, 0, NULL, NULL, 1, 5, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(31, '2025-09-23', 1, 1, 1, 1, 0, NULL, NULL, 2, 5, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(32, '2025-09-24', 1, 1, 1, 1, 0, NULL, NULL, 3, 5, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(33, '2025-09-25', 1, 1, 1, 1, 0, NULL, NULL, 4, 5, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(34, '2025-09-26', 1, 1, 1, 0, 0, NULL, NULL, 5, 5, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(35, '2025-09-27', 1, 1, 1, 0, 0, NULL, NULL, 6, 5, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(36, '2025-09-28', 1, 1, 1, 1, 0, NULL, NULL, 7, 6, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(37, '2025-09-29', 1, 1, 1, 1, 0, NULL, NULL, 1, 6, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(38, '2025-09-30', 1, 1, 1, 1, 0, NULL, NULL, 2, 6, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(39, '2025-10-01', 1, 1, 1, 1, 0, NULL, NULL, 3, 6, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(40, '2025-10-02', 1, 1, 1, 1, 0, NULL, NULL, 4, 6, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(41, '2025-10-03', 1, 1, 1, 0, 0, NULL, NULL, 5, 6, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(42, '2025-10-04', 1, 1, 1, 0, 0, NULL, NULL, 6, 6, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(43, '2025-10-05', 1, 1, 1, 1, 0, NULL, NULL, 7, 7, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(44, '2025-10-06', 1, 1, 1, 1, 0, NULL, NULL, 1, 7, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(45, '2025-10-07', 1, 1, 1, 1, 0, NULL, NULL, 2, 7, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(46, '2025-10-08', 1, 1, 1, 1, 0, NULL, NULL, 3, 7, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(47, '2025-10-09', 1, 1, 1, 1, 0, NULL, NULL, 4, 7, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(48, '2025-10-10', 1, 1, 1, 0, 0, NULL, NULL, 5, 7, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(49, '2025-10-11', 1, 1, 1, 0, 0, NULL, NULL, 6, 7, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(50, '2025-10-12', 1, 1, 1, 1, 0, NULL, NULL, 7, 8, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(51, '2025-10-13', 1, 1, 1, 1, 0, NULL, NULL, 1, 8, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(52, '2025-10-14', 1, 1, 1, 1, 0, NULL, NULL, 2, 8, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(53, '2025-10-15', 1, 1, 1, 1, 0, NULL, NULL, 3, 8, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(54, '2025-10-16', 1, 1, 1, 1, 0, NULL, NULL, 4, 8, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(55, '2025-10-17', 1, 1, 1, 0, 0, NULL, NULL, 5, 8, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(56, '2025-10-18', 1, 1, 1, 0, 0, NULL, NULL, 6, 8, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(57, '2025-10-19', 1, 1, 1, 1, 0, NULL, NULL, 7, 9, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(58, '2025-10-20', 1, 1, 1, 1, 0, NULL, NULL, 1, 9, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(59, '2025-10-21', 1, 1, 1, 1, 0, NULL, NULL, 2, 9, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(60, '2025-10-22', 1, 1, 1, 1, 0, NULL, NULL, 3, 9, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(61, '2025-10-23', 1, 1, 1, 1, 0, NULL, NULL, 4, 9, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(62, '2025-10-24', 1, 1, 1, 0, 0, NULL, NULL, 5, 9, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(63, '2025-10-25', 1, 1, 1, 0, 0, NULL, NULL, 6, 9, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(64, '2025-10-26', 1, 1, 1, 1, 0, NULL, NULL, 7, 10, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(65, '2025-10-27', 1, 1, 1, 1, 0, NULL, NULL, 1, 10, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(66, '2025-10-28', 1, 1, 1, 1, 0, NULL, NULL, 2, 10, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(67, '2025-10-29', 1, 1, 1, 1, 0, NULL, NULL, 3, 10, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(68, '2025-10-30', 1, 1, 1, 1, 0, NULL, NULL, 4, 10, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(69, '2025-10-31', 1, 1, 1, 0, 0, NULL, NULL, 5, 10, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(70, '2025-11-01', 1, 1, 1, 0, 0, NULL, NULL, 6, 10, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(71, '2025-11-02', 1, 1, 1, 1, 0, NULL, NULL, 7, 11, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(72, '2025-11-03', 1, 1, 1, 1, 0, NULL, NULL, 1, 11, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(73, '2025-11-04', 1, 1, 1, 1, 0, NULL, NULL, 2, 11, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(74, '2025-11-05', 1, 1, 1, 1, 0, NULL, NULL, 3, 11, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(75, '2025-11-06', 1, 1, 1, 1, 0, NULL, NULL, 4, 11, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(76, '2025-11-07', 1, 1, 1, 0, 0, NULL, NULL, 5, 11, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(77, '2025-11-08', 1, 1, 1, 0, 0, NULL, NULL, 6, 11, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(78, '2025-11-09', 1, 1, 1, 1, 0, NULL, NULL, 7, 12, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(79, '2025-11-10', 1, 1, 1, 1, 0, NULL, NULL, 1, 12, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(80, '2025-11-11', 1, 1, 1, 1, 0, NULL, NULL, 2, 12, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(81, '2025-11-12', 1, 1, 1, 1, 0, NULL, NULL, 3, 12, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(82, '2025-11-13', 1, 1, 1, 1, 0, NULL, NULL, 4, 12, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(83, '2025-11-14', 1, 1, 1, 0, 0, NULL, NULL, 5, 12, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(84, '2025-11-15', 1, 1, 1, 0, 0, NULL, NULL, 6, 12, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(85, '2025-11-16', 1, 1, 1, 1, 0, NULL, NULL, 7, 13, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(86, '2025-11-17', 1, 1, 1, 1, 0, NULL, NULL, 1, 13, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(87, '2025-11-18', 1, 1, 1, 1, 0, NULL, NULL, 2, 13, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(88, '2025-11-19', 1, 1, 1, 1, 0, NULL, NULL, 3, 13, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(89, '2025-11-20', 1, 1, 1, 1, 0, NULL, NULL, 4, 13, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(90, '2025-11-21', 1, 1, 1, 0, 0, NULL, NULL, 5, 13, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(91, '2025-11-22', 1, 1, 1, 0, 0, NULL, NULL, 6, 13, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(92, '2025-11-23', 1, 1, 1, 1, 0, NULL, NULL, 7, 14, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(93, '2025-11-24', 1, 1, 1, 1, 0, NULL, NULL, 1, 14, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(94, '2025-11-25', 1, 1, 1, 1, 0, NULL, NULL, 2, 14, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(95, '2025-11-26', 1, 1, 1, 1, 0, NULL, NULL, 3, 14, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(96, '2025-11-27', 1, 1, 1, 1, 0, NULL, NULL, 4, 14, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(97, '2025-11-28', 1, 1, 1, 0, 0, NULL, NULL, 5, 14, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(98, '2025-11-29', 1, 1, 1, 0, 0, NULL, NULL, 6, 14, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(99, '2025-11-30', 1, 1, 1, 1, 0, NULL, NULL, 7, 15, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(100, '2025-12-01', 1, 1, 1, 1, 0, NULL, NULL, 1, 15, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(101, '2025-12-02', 1, 1, 1, 1, 0, NULL, NULL, 2, 15, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(102, '2025-12-03', 1, 1, 1, 1, 0, NULL, NULL, 3, 15, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(103, '2025-12-04', 1, 1, 1, 1, 0, NULL, NULL, 4, 15, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(104, '2025-12-05', 1, 1, 1, 0, 0, NULL, NULL, 5, 15, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(105, '2025-12-06', 1, 1, 1, 0, 0, NULL, NULL, 6, 15, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(106, '2025-12-07', 1, 1, 1, 1, 0, NULL, NULL, 7, 16, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(107, '2025-12-08', 1, 1, 1, 1, 0, NULL, NULL, 1, 16, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(108, '2025-12-09', 1, 1, 1, 1, 0, NULL, NULL, 2, 16, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(109, '2025-12-10', 1, 1, 1, 1, 0, NULL, NULL, 3, 16, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(110, '2025-12-11', 1, 1, 1, 1, 0, NULL, NULL, 4, 16, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(111, '2025-12-12', 1, 1, 1, 0, 0, NULL, NULL, 5, 16, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(112, '2025-12-13', 1, 1, 1, 0, 0, NULL, NULL, 6, 16, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(113, '2025-12-14', 1, 1, 1, 1, 0, NULL, NULL, 7, 17, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(114, '2025-12-15', 1, 1, 1, 1, 0, NULL, NULL, 1, 17, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(115, '2025-12-16', 1, 1, 1, 1, 0, NULL, NULL, 2, 17, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(116, '2025-12-17', 1, 1, 1, 1, 0, NULL, NULL, 3, 17, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(117, '2025-12-18', 1, 1, 1, 1, 0, NULL, NULL, 4, 17, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(118, '2025-12-19', 1, 1, 1, 0, 0, NULL, NULL, 5, 17, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(119, '2025-12-20', 1, 1, 1, 0, 0, NULL, NULL, 6, 17, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(120, '2025-12-21', 1, 1, 1, 1, 0, NULL, NULL, 7, 18, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(121, '2025-12-22', 1, 1, 1, 1, 0, NULL, NULL, 1, 18, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(122, '2025-12-23', 1, 1, 1, 1, 0, NULL, NULL, 2, 18, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(123, '2025-12-24', 1, 1, 1, 1, 0, NULL, NULL, 3, 18, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(124, '2025-12-25', 1, 1, 1, 1, 0, NULL, NULL, 4, 18, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(125, '2025-12-26', 1, 1, 1, 0, 0, NULL, NULL, 5, 18, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(126, '2025-12-27', 1, 1, 1, 0, 0, NULL, NULL, 6, 18, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(127, '2025-12-28', 1, 1, 1, 1, 0, NULL, NULL, 7, 19, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(128, '2025-12-29', 1, 1, 1, 1, 0, NULL, NULL, 1, 19, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(129, '2025-12-30', 1, 1, 1, 1, 0, NULL, NULL, 2, 19, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(130, '2025-12-31', 1, 1, 1, 1, 0, NULL, NULL, 3, 19, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(131, '2026-01-01', 1, 1, 1, 1, 0, NULL, NULL, 4, 19, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(132, '2026-01-02', 1, 1, 1, 0, 0, NULL, NULL, 5, 19, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(133, '2026-01-03', 1, 1, 1, 0, 0, NULL, NULL, 6, 19, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(134, '2026-01-04', 1, 1, 1, 1, 0, NULL, NULL, 7, 20, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(135, '2026-01-05', 1, 1, 1, 1, 0, NULL, NULL, 1, 20, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(136, '2026-01-06', 1, 1, 1, 1, 0, NULL, NULL, 2, 20, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(137, '2026-01-07', 1, 1, 1, 1, 0, NULL, NULL, 3, 20, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(138, '2026-01-08', 1, 1, 1, 1, 0, NULL, NULL, 4, 20, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(139, '2026-01-09', 1, 1, 1, 0, 0, NULL, NULL, 5, 20, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(140, '2026-01-10', 1, 1, 1, 0, 0, NULL, NULL, 6, 20, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(141, '2026-01-11', 1, 1, 1, 1, 0, NULL, NULL, 7, 21, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(142, '2026-01-12', 1, 1, 1, 1, 0, NULL, NULL, 1, 21, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(143, '2026-01-13', 1, 1, 1, 1, 0, NULL, NULL, 2, 21, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(144, '2026-01-14', 1, 1, 1, 1, 0, NULL, NULL, 3, 21, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(145, '2026-01-15', 1, 1, 1, 1, 0, NULL, NULL, 4, 21, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(146, '2026-01-16', 1, 1, 1, 0, 0, NULL, NULL, 5, 21, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(147, '2026-01-17', 1, 1, 1, 0, 0, NULL, NULL, 6, 21, NULL, NULL, NULL, '2026-01-11 14:23:16', '2026-01-11 14:23:16'),
(148, '2026-01-18', 2, 1, 1, 1, 0, NULL, NULL, 7, 1, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(149, '2026-01-19', 2, 1, 1, 1, 0, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(150, '2026-01-20', 2, 1, 1, 1, 0, NULL, NULL, 2, 1, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(151, '2026-01-21', 2, 1, 1, 1, 0, NULL, NULL, 3, 1, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(152, '2026-01-22', 2, 1, 1, 1, 0, NULL, NULL, 4, 1, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(153, '2026-01-23', 2, 1, 1, 0, 0, NULL, NULL, 5, 1, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(154, '2026-01-24', 2, 1, 1, 0, 0, NULL, NULL, 6, 1, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(155, '2026-01-25', 2, 1, 1, 1, 0, NULL, NULL, 7, 2, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(156, '2026-01-26', 2, 1, 1, 1, 0, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(157, '2026-01-27', 2, 1, 1, 1, 0, NULL, NULL, 2, 2, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(158, '2026-01-28', 2, 1, 1, 1, 0, NULL, NULL, 3, 2, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(159, '2026-01-29', 2, 1, 1, 1, 0, NULL, NULL, 4, 2, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(160, '2026-01-30', 2, 1, 1, 0, 0, NULL, NULL, 5, 2, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(161, '2026-01-31', 2, 1, 1, 0, 0, NULL, NULL, 6, 2, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(162, '2026-02-01', 2, 1, 1, 1, 0, NULL, NULL, 7, 3, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(163, '2026-02-02', 2, 1, 1, 1, 0, NULL, NULL, 1, 3, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(164, '2026-02-03', 2, 1, 1, 1, 0, NULL, NULL, 2, 3, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(165, '2026-02-04', 2, 1, 1, 1, 0, NULL, NULL, 3, 3, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(166, '2026-02-05', 2, 1, 1, 1, 0, NULL, NULL, 4, 3, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(167, '2026-02-06', 2, 1, 1, 0, 0, NULL, NULL, 5, 3, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(168, '2026-02-07', 2, 1, 1, 0, 0, NULL, NULL, 6, 3, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(169, '2026-02-08', 2, 1, 1, 1, 0, NULL, NULL, 7, 4, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(170, '2026-02-09', 2, 1, 1, 1, 0, NULL, NULL, 1, 4, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(171, '2026-02-10', 2, 1, 1, 1, 0, NULL, NULL, 2, 4, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(172, '2026-02-11', 2, 1, 1, 1, 0, NULL, NULL, 3, 4, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(173, '2026-02-12', 2, 1, 1, 1, 0, NULL, NULL, 4, 4, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(174, '2026-02-13', 2, 1, 1, 0, 0, NULL, NULL, 5, 4, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(175, '2026-02-14', 2, 1, 1, 0, 0, NULL, NULL, 6, 4, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(176, '2026-02-15', 2, 1, 1, 1, 0, NULL, NULL, 7, 5, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(177, '2026-02-16', 2, 1, 1, 1, 0, NULL, NULL, 1, 5, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(178, '2026-02-17', 2, 1, 1, 1, 0, NULL, NULL, 2, 5, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(179, '2026-02-18', 2, 1, 1, 1, 0, NULL, NULL, 3, 5, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(180, '2026-02-19', 2, 1, 1, 1, 0, NULL, NULL, 4, 5, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(181, '2026-02-20', 2, 1, 1, 0, 0, NULL, NULL, 5, 5, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(182, '2026-02-21', 2, 1, 1, 0, 0, NULL, NULL, 6, 5, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(183, '2026-02-22', 2, 1, 1, 1, 0, NULL, NULL, 7, 6, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(184, '2026-02-23', 2, 1, 1, 1, 0, NULL, NULL, 1, 6, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(185, '2026-02-24', 2, 1, 1, 1, 0, NULL, NULL, 2, 6, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(186, '2026-02-25', 2, 1, 1, 1, 0, NULL, NULL, 3, 6, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(187, '2026-02-26', 2, 1, 1, 1, 0, NULL, NULL, 4, 6, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(188, '2026-02-27', 2, 1, 1, 0, 0, NULL, NULL, 5, 6, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(189, '2026-02-28', 2, 1, 1, 0, 0, NULL, NULL, 6, 6, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(190, '2026-03-01', 2, 1, 1, 1, 0, NULL, NULL, 7, 7, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(191, '2026-03-02', 2, 1, 1, 1, 0, NULL, NULL, 1, 7, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(192, '2026-03-03', 2, 1, 1, 1, 0, NULL, NULL, 2, 7, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(193, '2026-03-04', 2, 1, 1, 1, 0, NULL, NULL, 3, 7, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(194, '2026-03-05', 2, 1, 1, 1, 0, NULL, NULL, 4, 7, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(195, '2026-03-06', 2, 1, 1, 0, 0, NULL, NULL, 5, 7, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(196, '2026-03-07', 2, 1, 1, 0, 0, NULL, NULL, 6, 7, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(197, '2026-03-08', 2, 1, 1, 1, 0, NULL, NULL, 7, 8, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(198, '2026-03-09', 2, 1, 1, 1, 0, NULL, NULL, 1, 8, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(199, '2026-03-10', 2, 1, 1, 1, 0, NULL, NULL, 2, 8, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(200, '2026-03-11', 2, 1, 1, 1, 0, NULL, NULL, 3, 8, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(201, '2026-03-12', 2, 1, 1, 1, 0, NULL, NULL, 4, 8, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(202, '2026-03-13', 2, 1, 1, 0, 0, NULL, NULL, 5, 8, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(203, '2026-03-14', 2, 1, 1, 0, 0, NULL, NULL, 6, 8, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(204, '2026-03-15', 2, 1, 1, 1, 0, NULL, NULL, 7, 9, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(205, '2026-03-16', 2, 1, 1, 1, 0, NULL, NULL, 1, 9, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(206, '2026-03-17', 2, 1, 1, 1, 0, NULL, NULL, 2, 9, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(207, '2026-03-18', 2, 1, 1, 1, 0, NULL, NULL, 3, 9, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(208, '2026-03-19', 2, 1, 1, 1, 0, NULL, NULL, 4, 9, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(209, '2026-03-20', 2, 1, 1, 0, 0, NULL, NULL, 5, 9, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(210, '2026-03-21', 2, 1, 1, 0, 0, NULL, NULL, 6, 9, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(211, '2026-03-22', 2, 1, 1, 1, 0, NULL, NULL, 7, 10, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(212, '2026-03-23', 2, 1, 1, 1, 0, NULL, NULL, 1, 10, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(213, '2026-03-24', 2, 1, 1, 1, 0, NULL, NULL, 2, 10, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(214, '2026-03-25', 2, 1, 1, 1, 0, NULL, NULL, 3, 10, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(215, '2026-03-26', 2, 1, 1, 1, 0, NULL, NULL, 4, 10, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(216, '2026-03-27', 2, 1, 1, 0, 0, NULL, NULL, 5, 10, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(217, '2026-03-28', 2, 1, 1, 0, 0, NULL, NULL, 6, 10, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(218, '2026-03-29', 2, 1, 1, 1, 0, NULL, NULL, 7, 11, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(219, '2026-03-30', 2, 1, 1, 1, 0, NULL, NULL, 1, 11, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(220, '2026-03-31', 2, 1, 1, 1, 0, NULL, NULL, 2, 11, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(221, '2026-04-01', 2, 1, 1, 1, 0, NULL, NULL, 3, 11, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(222, '2026-04-02', 2, 1, 1, 1, 0, NULL, NULL, 4, 11, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(223, '2026-04-03', 2, 1, 1, 0, 0, NULL, NULL, 5, 11, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(224, '2026-04-04', 2, 1, 1, 0, 0, NULL, NULL, 6, 11, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(225, '2026-04-05', 2, 1, 1, 1, 0, NULL, NULL, 7, 12, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(226, '2026-04-06', 2, 1, 1, 1, 0, NULL, NULL, 1, 12, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(227, '2026-04-07', 2, 1, 1, 1, 0, NULL, NULL, 2, 12, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(228, '2026-04-08', 2, 1, 1, 1, 0, NULL, NULL, 3, 12, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(229, '2026-04-09', 2, 1, 1, 1, 0, NULL, NULL, 4, 12, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(230, '2026-04-10', 2, 1, 1, 0, 0, NULL, NULL, 5, 12, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(231, '2026-04-11', 2, 1, 1, 0, 0, NULL, NULL, 6, 12, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(232, '2026-04-12', 2, 1, 1, 1, 0, NULL, NULL, 7, 13, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(233, '2026-04-13', 2, 1, 1, 1, 0, NULL, NULL, 1, 13, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(234, '2026-04-14', 2, 1, 1, 1, 0, NULL, NULL, 2, 13, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(235, '2026-04-15', 2, 1, 1, 1, 0, NULL, NULL, 3, 13, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(236, '2026-04-16', 2, 1, 1, 1, 0, NULL, NULL, 4, 13, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(237, '2026-04-17', 2, 1, 1, 0, 0, NULL, NULL, 5, 13, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(238, '2026-04-18', 2, 1, 1, 0, 0, NULL, NULL, 6, 13, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(239, '2026-04-19', 2, 1, 1, 1, 0, NULL, NULL, 7, 14, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(240, '2026-04-20', 2, 1, 1, 1, 0, NULL, NULL, 1, 14, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(241, '2026-04-21', 2, 1, 1, 1, 0, NULL, NULL, 2, 14, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(242, '2026-04-22', 2, 1, 1, 1, 0, NULL, NULL, 3, 14, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(243, '2026-04-23', 2, 1, 1, 1, 0, NULL, NULL, 4, 14, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(244, '2026-04-24', 2, 1, 1, 0, 0, NULL, NULL, 5, 14, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(245, '2026-04-25', 2, 1, 1, 0, 0, NULL, NULL, 6, 14, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(246, '2026-04-26', 2, 1, 1, 1, 0, NULL, NULL, 7, 15, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(247, '2026-04-27', 2, 1, 1, 1, 0, NULL, NULL, 1, 15, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(248, '2026-04-28', 2, 1, 1, 1, 0, NULL, NULL, 2, 15, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(249, '2026-04-29', 2, 1, 1, 1, 0, NULL, NULL, 3, 15, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(250, '2026-04-30', 2, 1, 1, 1, 0, NULL, NULL, 4, 15, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(251, '2026-05-01', 2, 1, 1, 0, 0, NULL, NULL, 5, 15, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(252, '2026-05-02', 2, 1, 1, 0, 0, NULL, NULL, 6, 15, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(253, '2026-05-03', 2, 1, 1, 1, 0, NULL, NULL, 7, 16, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(254, '2026-05-04', 2, 1, 1, 1, 0, NULL, NULL, 1, 16, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(255, '2026-05-05', 2, 1, 1, 1, 0, NULL, NULL, 2, 16, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(256, '2026-05-06', 2, 1, 1, 1, 0, NULL, NULL, 3, 16, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(257, '2026-05-07', 2, 1, 1, 1, 0, NULL, NULL, 4, 16, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(258, '2026-05-08', 2, 1, 1, 0, 0, NULL, NULL, 5, 16, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(259, '2026-05-09', 2, 1, 1, 0, 0, NULL, NULL, 6, 16, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(260, '2026-05-10', 2, 1, 1, 1, 0, NULL, NULL, 7, 17, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(261, '2026-05-11', 2, 1, 1, 1, 0, NULL, NULL, 1, 17, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(262, '2026-05-12', 2, 1, 1, 1, 0, NULL, NULL, 2, 17, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(263, '2026-05-13', 2, 1, 1, 1, 0, NULL, NULL, 3, 17, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(264, '2026-05-14', 2, 1, 1, 1, 0, NULL, NULL, 4, 17, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(265, '2026-05-15', 2, 1, 1, 0, 0, NULL, NULL, 5, 17, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(266, '2026-05-16', 2, 1, 1, 0, 0, NULL, NULL, 6, 17, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(267, '2026-05-17', 2, 1, 1, 1, 0, NULL, NULL, 7, 18, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(268, '2026-05-18', 2, 1, 1, 1, 0, NULL, NULL, 1, 18, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(269, '2026-05-19', 2, 1, 1, 1, 0, NULL, NULL, 2, 18, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(270, '2026-05-20', 2, 1, 1, 1, 0, NULL, NULL, 3, 18, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(271, '2026-05-21', 2, 1, 1, 1, 0, NULL, NULL, 4, 18, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(272, '2026-05-22', 2, 1, 1, 0, 0, NULL, NULL, 5, 18, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(273, '2026-05-23', 2, 1, 1, 0, 0, NULL, NULL, 6, 18, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(274, '2026-05-24', 2, 1, 1, 1, 0, NULL, NULL, 7, 19, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(275, '2026-05-25', 2, 1, 1, 1, 0, NULL, NULL, 1, 19, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(276, '2026-05-26', 2, 1, 1, 1, 0, NULL, NULL, 2, 19, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(277, '2026-05-27', 2, 1, 1, 1, 0, NULL, NULL, 3, 19, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(278, '2026-05-28', 2, 1, 1, 1, 0, NULL, NULL, 4, 19, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(279, '2026-05-29', 2, 1, 1, 0, 0, NULL, NULL, 5, 19, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(280, '2026-05-30', 2, 1, 1, 0, 0, NULL, NULL, 6, 19, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(281, '2026-05-31', 2, 1, 1, 1, 0, NULL, NULL, 7, 20, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(282, '2026-06-01', 2, 1, 1, 1, 0, NULL, NULL, 1, 20, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(283, '2026-06-02', 2, 1, 1, 1, 0, NULL, NULL, 2, 20, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(284, '2026-06-03', 2, 1, 1, 1, 0, NULL, NULL, 3, 20, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(285, '2026-06-04', 2, 1, 1, 1, 0, NULL, NULL, 4, 20, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(286, '2026-06-05', 2, 1, 1, 0, 0, NULL, NULL, 5, 20, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(287, '2026-06-06', 2, 1, 1, 0, 0, NULL, NULL, 6, 20, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(288, '2026-06-07', 2, 1, 1, 1, 0, NULL, NULL, 7, 21, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(289, '2026-06-08', 2, 1, 1, 1, 0, NULL, NULL, 1, 21, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(290, '2026-06-09', 2, 1, 1, 1, 0, NULL, NULL, 2, 21, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(291, '2026-06-10', 2, 1, 1, 1, 0, NULL, NULL, 3, 21, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(292, '2026-06-11', 2, 1, 1, 1, 0, NULL, NULL, 4, 21, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(293, '2026-06-12', 2, 1, 1, 0, 0, NULL, NULL, 5, 21, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(294, '2026-06-13', 2, 1, 1, 0, 0, NULL, NULL, 6, 21, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(295, '2026-06-14', 2, 1, 1, 1, 0, NULL, NULL, 7, 22, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(296, '2026-06-15', 2, 1, 1, 1, 0, NULL, NULL, 1, 22, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(297, '2026-06-16', 2, 1, 1, 1, 0, NULL, NULL, 2, 22, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(298, '2026-06-17', 2, 1, 1, 1, 0, NULL, NULL, 3, 22, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(299, '2026-06-18', 2, 1, 1, 1, 0, NULL, NULL, 4, 22, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(300, '2026-06-19', 2, 1, 1, 0, 0, NULL, NULL, 5, 22, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38'),
(301, '2026-06-20', 2, 1, 1, 0, 0, NULL, NULL, 6, 22, NULL, NULL, NULL, '2026-01-11 14:24:38', '2026-01-11 14:24:38');

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` int NOT NULL,
  `stage_id` bigint UNSIGNED NOT NULL,
  `grade_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`id`, `name`, `name_ar`, `capacity`, `stage_id`, `grade_id`, `school_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '4A', NULL, 30, 1, 4, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(2, '4B', NULL, 30, 1, 4, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(3, '4C', NULL, 30, 1, 4, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(4, '5A', NULL, 30, 1, 5, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(5, '5B', NULL, 30, 1, 5, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(6, '5C', NULL, 30, 1, 5, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(7, '6A', NULL, 30, 1, 6, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(8, '6B', NULL, 30, 1, 6, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(9, '7A', NULL, 30, 2, 7, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(10, '7B', NULL, 30, 2, 7, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(11, '8A', NULL, 30, 2, 8, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(12, '9A', NULL, 30, 2, 9, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(13, '10A', NULL, 30, 3, 10, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(14, '11A', NULL, 30, 3, 11, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `classroom_records`
--

CREATE TABLE `classroom_records` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `year_id` bigint UNSIGNED NOT NULL,
  `period_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `classroom_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `attend` tinyint DEFAULT NULL,
  `book` tinyint DEFAULT NULL,
  `homework` tinyint DEFAULT NULL,
  `out_classroom` tinyint DEFAULT NULL,
  `out_classroom_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `turn1` tinyint DEFAULT NULL,
  `turn2` tinyint DEFAULT NULL,
  `turn3` tinyint DEFAULT NULL,
  `plus` tinyint DEFAULT NULL,
  `minus` tinyint DEFAULT NULL,
  `total` tinyint DEFAULT NULL,
  `date` date NOT NULL,
  `time` time DEFAULT NULL,
  `points_details` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classroom_subject_teachers`
--

CREATE TABLE `classroom_subject_teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `classroom_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `classes_per_week` int NOT NULL,
  `color_custom` varchar(22) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_custom_text` varchar(22) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Custom color for UI display (hex format: #RRGGBB)',
  `data` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classroom_subject_teachers`
--

INSERT INTO `classroom_subject_teachers` (`id`, `school_id`, `academic_year_id`, `classroom_id`, `subject_id`, `teacher_id`, `classes_per_week`, `color_custom`, `color_custom_text`, `data`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 5, 1, 7, '#0f8998', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(2, 1, 1, 1, 15, 2, 5, '#9d5c74', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(3, 1, 1, 1, 25, 3, 5, '#8c4c07', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(4, 1, 1, 1, 7, 4, 4, '#905498', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(5, 1, 1, 1, 3, 5, 4, '#e18a00', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(6, 1, 1, 1, 24, 6, 2, '#bd29bf', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(7, 1, 1, 1, 26, 7, 2, '#ac5150', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(8, 1, 1, 1, 27, 1, 2, '#5a649c', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(9, 1, 1, 1, 21, 8, 1, '#9a3ef1', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(10, 1, 1, 1, 6, 1, 1, '#d2692e', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(11, 1, 1, 1, 17, 8, 1, '#8ed272', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(12, 1, 1, 1, 18, 9, 1, '#153150', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(13, 1, 1, 1, 2, 10, 1, '#2a7c6e', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(14, 1, 1, 1, 16, 7, 1, '#49592f', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(15, 1, 1, 1, 20, 11, 1, '#11ac24', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(16, 1, 1, 1, 19, 9, 1, '#30591e', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(17, 1, 1, 2, 5, 1, 7, '#2a8911', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(18, 1, 1, 2, 15, 2, 5, '#a3b153', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(19, 1, 1, 2, 25, 3, 5, '#8db14a', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(20, 1, 1, 2, 7, 4, 4, '#a749c4', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(21, 1, 1, 2, 3, 5, 4, '#cdab2b', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(22, 1, 1, 2, 24, 6, 2, '#e0c4a5', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(23, 1, 1, 2, 26, 7, 2, '#909526', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(24, 1, 1, 2, 27, 1, 2, '#0e927d', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(25, 1, 1, 2, 21, 8, 1, '#7c144a', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(26, 1, 1, 2, 6, 1, 1, '#f28e74', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(27, 1, 1, 2, 17, 8, 1, '#e002fb', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(28, 1, 1, 2, 18, 9, 1, '#f1830a', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(29, 1, 1, 2, 2, 10, 1, '#248018', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(30, 1, 1, 2, 16, 7, 1, '#a72954', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(31, 1, 1, 2, 20, 11, 1, '#a5e9b4', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(32, 1, 1, 2, 19, 9, 1, '#84faaf', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(33, 1, 1, 4, 5, 12, 7, '#2f4158', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(34, 1, 1, 4, 15, 2, 5, '#e19117', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(35, 1, 1, 4, 25, 3, 5, '#2fb5c0', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(36, 1, 1, 4, 7, 4, 4, '#c6696d', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(37, 1, 1, 4, 3, 13, 4, '#e3b1e9', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(38, 1, 1, 4, 24, 10, 2, '#778cb4', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(39, 1, 1, 4, 26, 7, 2, '#01eed5', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(40, 1, 1, 4, 27, 14, 2, '#97bd57', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(41, 1, 1, 4, 21, 8, 1, '#f0ce5d', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(42, 1, 1, 4, 6, 14, 1, '#08b6f0', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(43, 1, 1, 4, 17, 8, 1, '#dc9c5b', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(44, 1, 1, 4, 18, 9, 1, '#d3bf37', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(45, 1, 1, 4, 2, 10, 1, '#501947', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(46, 1, 1, 4, 16, 4, 1, '#782f9c', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(47, 1, 1, 4, 20, 11, 1, '#4fdb6b', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(48, 1, 1, 4, 19, 9, 1, '#2c68b8', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(49, 1, 1, 5, 5, 12, 7, '#045e30', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(50, 1, 1, 5, 15, 2, 5, '#465cf7', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(51, 1, 1, 5, 25, 3, 5, '#621873', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(52, 1, 1, 5, 7, 4, 4, '#588cfb', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(53, 1, 1, 5, 3, 13, 4, '#35d12e', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(54, 1, 1, 5, 24, 10, 2, '#c76c74', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(55, 1, 1, 5, 26, 7, 2, '#4e4a59', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(56, 1, 1, 5, 27, 14, 2, '#facb85', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(57, 1, 1, 5, 21, 8, 1, '#458f8f', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(58, 1, 1, 5, 6, 14, 1, '#696242', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(59, 1, 1, 5, 17, 8, 1, '#7fc46a', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(60, 1, 1, 5, 18, 9, 1, '#12deb9', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(61, 1, 1, 5, 2, 10, 1, '#03d23c', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(62, 1, 1, 5, 16, 4, 1, '#e85e76', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(63, 1, 1, 5, 20, 11, 1, '#158b5f', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(64, 1, 1, 5, 19, 9, 1, '#0bf3c9', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(65, 1, 1, 6, 15, 2, 5, '#375d61', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(66, 1, 1, 6, 25, 3, 5, '#18d137', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(67, 1, 1, 6, 7, 4, 4, '#0c19e1', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(68, 1, 1, 6, 5, 15, 4, '#59b1cf', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(69, 1, 1, 6, 3, 10, 4, '#95462d', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(70, 1, 1, 6, 24, 10, 2, '#3ab213', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(71, 1, 1, 6, 26, 7, 2, '#4e06d9', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(72, 1, 1, 6, 27, 15, 2, '#8f3422', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(73, 1, 1, 6, 21, 8, 1, '#d51ba2', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(74, 1, 1, 6, 6, 15, 1, '#f1e65d', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(75, 1, 1, 6, 17, 8, 1, '#82fb25', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(76, 1, 1, 6, 18, 9, 1, '#d7ffdf', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(77, 1, 1, 6, 2, 10, 1, '#163c86', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(78, 1, 1, 6, 16, 4, 1, '#3e210b', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(79, 1, 1, 6, 20, 11, 1, '#cf47d1', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(80, 1, 1, 6, 19, 9, 1, '#ce430a', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(81, 1, 1, 7, 15, 16, 5, '#473bca', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(82, 1, 1, 7, 25, 17, 5, '#b03fdd', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(83, 1, 1, 7, 7, 18, 4, '#6a2aa5', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(84, 1, 1, 7, 5, 19, 4, '#495747', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(85, 1, 1, 7, 3, 5, 4, '#f0dac4', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(86, 1, 1, 7, 24, 10, 2, '#513f4f', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(87, 1, 1, 7, 26, 7, 2, '#8eb9e6', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(88, 1, 1, 7, 27, 19, 2, '#44b0d9', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(89, 1, 1, 7, 6, 19, 1, '#28459e', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(90, 1, 1, 7, 17, 8, 1, '#317622', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(91, 1, 1, 7, 18, 9, 1, '#6cee2d', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(92, 1, 1, 7, 2, 17, 1, '#56a077', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(93, 1, 1, 7, 16, 18, 1, '#fa0592', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(94, 1, 1, 7, 20, 11, 1, '#a35675', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(95, 1, 1, 7, 19, 9, 1, '#f293ae', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(96, 1, 1, 7, 4, 5, 1, '#cee516', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(97, 1, 1, 8, 15, 16, 5, '#b4b810', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(98, 1, 1, 8, 25, 17, 5, '#61f644', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(99, 1, 1, 8, 7, 18, 4, '#b4d825', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(100, 1, 1, 8, 5, 19, 4, '#2c39ad', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(101, 1, 1, 8, 3, 5, 4, '#849789', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(102, 1, 1, 8, 24, 10, 2, '#5e951f', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(103, 1, 1, 8, 26, 7, 2, '#dfde60', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(104, 1, 1, 8, 27, 19, 2, '#07e3f1', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(105, 1, 1, 8, 6, 19, 1, '#438adc', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(106, 1, 1, 8, 17, 8, 1, '#b49514', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(107, 1, 1, 8, 18, 9, 1, '#48a30e', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(108, 1, 1, 8, 2, 17, 1, '#6b9077', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(109, 1, 1, 8, 16, 18, 1, '#93eefd', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(110, 1, 1, 8, 20, 11, 1, '#af924c', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(111, 1, 1, 8, 19, 9, 1, '#deed94', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(112, 1, 1, 8, 4, 5, 1, '#daf0f6', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(113, 1, 1, 9, 15, 16, 5, '#4af8ac', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(114, 1, 1, 9, 25, 17, 5, '#1001dd', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(115, 1, 1, 9, 7, 18, 4, '#d56d0e', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(116, 1, 1, 9, 5, 12, 4, '#99971b', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(117, 1, 1, 9, 3, 13, 4, '#d3db54', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(118, 1, 1, 9, 17, 8, 2, '#9d08c1', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(119, 1, 1, 9, 26, 7, 2, '#d5ee54', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(120, 1, 1, 9, 27, 12, 2, '#57d9ed', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(121, 1, 1, 9, 24, 6, 1, '#153cb9', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(122, 1, 1, 9, 6, 12, 1, '#499bd2', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(123, 1, 1, 9, 18, 9, 1, '#679303', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(124, 1, 1, 9, 2, 17, 1, '#9a63fb', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(125, 1, 1, 9, 20, 11, 1, '#ea4540', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(126, 1, 1, 9, 19, 9, 1, '#a638d6', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(127, 1, 1, 9, 4, 6, 1, '#a4abde', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(128, 1, 1, 10, 5, 14, 7, '#894d93', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(129, 1, 1, 10, 15, 16, 5, '#6de614', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(130, 1, 1, 10, 25, 17, 5, '#fbd9e6', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(131, 1, 1, 10, 7, 18, 4, '#c66f4b', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(132, 1, 1, 10, 3, 13, 4, '#bf8d94', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(133, 1, 1, 10, 17, 8, 2, '#46fe66', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(134, 1, 1, 10, 26, 7, 2, '#51a4c1', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(135, 1, 1, 10, 27, 14, 2, '#90c2c8', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(136, 1, 1, 10, 24, 6, 1, '#b5a38e', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(137, 1, 1, 10, 6, 14, 1, '#c50775', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(138, 1, 1, 10, 18, 9, 1, '#aa7b1f', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(139, 1, 1, 10, 2, 17, 1, '#05aaa0', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(140, 1, 1, 10, 20, 11, 1, '#1fa5bf', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(141, 1, 1, 10, 19, 9, 1, '#332d8f', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(142, 1, 1, 10, 4, 6, 1, '#c3b10a', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(143, 1, 1, 11, 15, 16, 5, '#a8f033', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(144, 1, 1, 11, 25, 20, 5, '#6786c1', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(145, 1, 1, 11, 7, 18, 4, '#f2458b', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(146, 1, 1, 11, 5, 21, 4, '#ad66e8', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(147, 1, 1, 11, 3, 6, 4, '#a5adf9', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(148, 1, 1, 11, 24, 6, 2, '#f05e92', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(149, 1, 1, 11, 17, 8, 2, '#11d3e8', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(150, 1, 1, 11, 26, 7, 2, '#41154d', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(151, 1, 1, 11, 27, 21, 2, '#6a6d8e', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(152, 1, 1, 11, 6, 21, 1, '#c4acff', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(153, 1, 1, 11, 18, 9, 1, '#aca922', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(154, 1, 1, 11, 2, 20, 1, '#1de945', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(155, 1, 1, 11, 20, 11, 1, '#fbb940', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(156, 1, 1, 11, 19, 9, 1, '#1dc344', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(157, 1, 1, 11, 4, 6, 1, '#c45869', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(158, 1, 1, 12, 15, 22, 5, '#a81cfd', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(159, 1, 1, 12, 25, 20, 5, '#5ba491', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(160, 1, 1, 12, 7, 22, 3, '#55bc59', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(161, 1, 1, 12, 5, 15, 3, '#70271b', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(162, 1, 1, 12, 8, 6, 2, '#c67b74', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(163, 1, 1, 12, 9, 13, 2, '#959d7b', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(164, 1, 1, 12, 11, 15, 2, '#cfa0f7', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(165, 1, 1, 12, 10, 10, 2, '#1647a2', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(166, 1, 1, 12, 26, 22, 2, '#ce573a', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(167, 1, 1, 12, 24, 5, 1, '#bb85fa', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(168, 1, 1, 12, 6, 15, 1, '#119bc6', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(169, 1, 1, 12, 17, 8, 1, '#d4cbcd', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(170, 1, 1, 12, 18, 9, 1, '#72d43e', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(171, 1, 1, 12, 2, 20, 1, '#cfc116', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(172, 1, 1, 12, 20, 11, 1, '#a8114d', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(173, 1, 1, 12, 19, 9, 1, '#a26093', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(174, 1, 1, 12, 23, 21, 1, '#c66379', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(175, 1, 1, 12, 23, 20, 1, '#cfbf32', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(176, 1, 1, 12, 4, 6, 1, '#923798', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(177, 1, 1, 13, 25, 20, 5, '#de2837', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(178, 1, 1, 13, 7, 22, 3, '#90fece', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(179, 1, 1, 13, 8, 6, 3, '#d06f7d', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(180, 1, 1, 13, 9, 13, 3, '#a26dce', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(181, 1, 1, 13, 5, 21, 3, '#281883', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(182, 1, 1, 13, 15, 22, 3, '#9fa43d', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(183, 1, 1, 13, 10, 10, 3, '#8293a8', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(184, 1, 1, 13, 26, 22, 2, '#199c23', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(185, 1, 1, 13, 12, 21, 2, '#f416f5', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(186, 1, 1, 13, 24, 5, 1, '#57bfe0', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(187, 1, 1, 13, 17, 8, 1, '#2cbba4', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(188, 1, 1, 13, 22, 22, 1, '#482a4f', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(189, 1, 1, 13, 22, 20, 1, '#d4e58d', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(190, 1, 1, 13, 18, 9, 1, '#1cd0af', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(191, 1, 1, 13, 20, 11, 1, '#2b2f85', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(192, 1, 1, 13, 19, 9, 1, '#bc29b2', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(193, 1, 1, 13, 23, 21, 1, '#14e560', '#000000', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(194, 1, 1, 13, 23, 20, 1, '#0793da', '#FFFFFF', NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_group` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_user`
--

CREATE TABLE `conversation_user` (
  `id` bigint UNSIGNED NOT NULL,
  `conversation_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `last_read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_lessons`
--

CREATE TABLE `course_lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci,
  `data` json DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `course_section_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_levels`
--

CREATE TABLE `course_levels` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `course_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_sections`
--

CREATE TABLE `course_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `course_level_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_teacher_assignments`
--

CREATE TABLE `course_teacher_assignments` (
  `id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `assigned_by` bigint UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curricula`
--

CREATE TABLE `curricula` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `grade_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `active` tinyint NOT NULL DEFAULT '0' COMMENT '0=inactive, 1=active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_lessons`
--

CREATE TABLE `curriculum_lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `topic_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `lesson_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesson_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_number` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `standard` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `strand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `skill` text COLLATE utf8mb4_unicode_ci,
  `activities` text COLLATE utf8mb4_unicode_ci,
  `assignment` text COLLATE utf8mb4_unicode_ci,
  `assessment` text COLLATE utf8mb4_unicode_ci,
  `objective` text COLLATE utf8mb4_unicode_ci,
  `data` json DEFAULT NULL,
  `type` enum('main','revision','quiz','project','extra') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'main',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_lesson_plans`
--

CREATE TABLE `curriculum_lesson_plans` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `curriculum_lesson_id` bigint UNSIGNED DEFAULT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `grade_id` bigint UNSIGNED NOT NULL,
  `classroom_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `co_teacher_ids` json DEFAULT NULL COMMENT 'Array of teacher IDs for co-teachers',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_number` int DEFAULT NULL,
  `cw` text COLLATE utf8mb4_unicode_ci COMMENT 'Class work',
  `hw` text COLLATE utf8mb4_unicode_ci COMMENT 'Home work',
  `objectives` text COLLATE utf8mb4_unicode_ci,
  `materials` json DEFAULT NULL COMMENT 'Teaching materials and resources',
  `plan` json DEFAULT NULL COMMENT 'Detailed lesson plan structure',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=draft, 1=active, 2=completed',
  `planned_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_maps`
--

CREATE TABLE `curriculum_maps` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `grade_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `curriculum_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `weekly_plan` json DEFAULT NULL COMMENT 'JSON structure: {week_number: {lessons: [], objectives: [], assessments: []}}',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=draft, 1=active, 2=completed',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_topics`
--

CREATE TABLE `curriculum_topics` (
  `id` bigint UNSIGNED NOT NULL,
  `curriculum_id` bigint UNSIGNED NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documentations`
--

CREATE TABLE `documentations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('code','comment','idea','tutorial','reference','question','note','research','guide','api') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'note',
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `author_id` bigint UNSIGNED NOT NULL,
  `tags` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dp_daily_tasks`
--

CREATE TABLE `dp_daily_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `dp_task_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dp_focus_logs`
--

CREATE TABLE `dp_focus_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `dp_daily_task_id` bigint UNSIGNED DEFAULT NULL,
  `start_time` timestamp NOT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `duration_minutes` int NOT NULL DEFAULT '0',
  `distraction_count` int NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dp_rewards`
--

CREATE TABLE `dp_rewards` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `badge` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dp_tasks`
--

CREATE TABLE `dp_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `subject_ids` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `name`, `name_ar`, `stage_id`, `school_id`, `subject_ids`, `created_at`, `updated_at`) VALUES
(1, 'Grade 1', NULL, 1, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(2, 'Grade 2', NULL, 1, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(3, 'Grade 3', NULL, 1, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(4, 'Grade 4', NULL, 1, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(5, 'Grade 5', NULL, 1, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(6, 'Grade 6', NULL, 1, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(7, 'Grade 7', NULL, 2, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(8, 'Grade 8', NULL, 2, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(9, 'Grade 9', NULL, 2, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(10, 'Grade 10', NULL, 3, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(11, 'Grade 11', NULL, 3, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(12, 'Grade 12', NULL, 3, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `grade_subject`
--

CREATE TABLE `grade_subject` (
  `id` bigint UNSIGNED NOT NULL,
  `grade_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `h_r_s`
--

CREATE TABLE `h_r_s` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `h_r_s`
--

INSERT INTO `h_r_s` (`id`, `user_id`, `name`, `name_ar`, `data`, `active`, `created_at`, `updated_at`) VALUES
(1, 2, 'Main HR Department', NULL, '{\"phone\": \"\", \"address\": \"\"}', 1, '2026-01-03 01:24:40', '2026-01-03 01:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `curriculum_id` bigint UNSIGNED NOT NULL,
  `lesson_number` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_number` int DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_plan_templates`
--

CREATE TABLE `lesson_plan_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `structure` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_practice_submissions`
--

CREATE TABLE `lesson_practice_submissions` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_student_progress_id` bigint UNSIGNED NOT NULL,
  `submission_type` enum('upload','drawing') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drawing_data` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_presentations`
--

CREATE TABLE `lesson_presentations` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `grade_id` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `quiz_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_presentation_slides`
--

CREATE TABLE `lesson_presentation_slides` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_presentation_id` bigint UNSIGNED NOT NULL,
  `section` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slide_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slide_content` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lesson_student_progress`
--

CREATE TABLE `lesson_student_progress` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_presentation_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `opened_by_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('locked','opened','learning','practice_pending','practice_submitted','quiz_unlocked','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'locked',
  `color_status` enum('gray','light_blue','blue','purple','green','yellow','dark_yellow','orange','red') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gray',
  `learn_completed_at` timestamp NULL DEFAULT NULL,
  `practice_score` int DEFAULT NULL,
  `practice_submitted_at` timestamp NULL DEFAULT NULL,
  `practice_graded_at` timestamp NULL DEFAULT NULL,
  `quiz_attempts` int NOT NULL DEFAULT '0',
  `quiz_best_score` int DEFAULT NULL,
  `quiz_passed` tinyint(1) NOT NULL DEFAULT '0',
  `force_passed` tinyint(1) NOT NULL DEFAULT '0',
  `opened_at` timestamp NULL DEFAULT NULL,
  `practice_data` json DEFAULT NULL,
  `quiz_data` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint UNSIGNED NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Named route',
  `permission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Spatie permission name',
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Feature grouping e.g. Academics',
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_feature_flag` tinyint(1) NOT NULL DEFAULT '0',
  `feature_flag_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` json DEFAULT NULL COMMENT 'Additional metadata like badges, descriptions',
  `v2_component` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Specific V2 component to render',
  `requires_context` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Requires school/tenant context',
  `role_specific` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Specific role this menu belongs to: SuperSystem, SystemAdmin, SchoolAdmin, Teacher, Student, Parent',
  `v2_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Enable this menu in V2 system',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `label`, `route`, `permission`, `module`, `parent_id`, `order`, `icon`, `is_active`, `is_feature_flag`, `feature_flag_key`, `meta`, `v2_component`, `requires_context`, `role_specific`, `v2_enabled`, `created_at`, `updated_at`) VALUES
(1, 'Super System', 'v2.super-system.dashboard', NULL, 'super-system', NULL, 900, 'build', 1, 0, NULL, NULL, NULL, 0, 'SuperSystem', 1, '2026-01-10 02:03:09', '2026-01-10 02:03:09'),
(2, 'Configuration', 'v2.super-system.config', NULL, 'super-system', 1, 2, 'settings', 1, 0, NULL, NULL, NULL, 0, 'SuperSystem', 1, '2026-01-10 02:03:38', '2026-01-10 02:03:38'),
(3, 'Jobs Monitor', 'v2.super-system.jobs', NULL, 'super-system', 1, 3, 'memory', 1, 0, NULL, NULL, NULL, 0, 'SuperSystem', 1, '2026-01-10 02:03:38', '2026-01-10 02:03:38'),
(4, 'System Logs', 'v2.super-system.logs', NULL, 'super-system', 1, 4, 'article', 1, 0, NULL, NULL, NULL, 0, 'SuperSystem', 1, '2026-01-10 02:03:38', '2026-01-10 02:03:38'),
(5, 'System Admin', 'v2.system-admin.dashboard', NULL, 'system-admin', NULL, 100, 'admin_panel_settings', 1, 0, NULL, NULL, NULL, 0, 'SystemAdmin', 1, '2026-01-10 02:03:38', '2026-01-10 02:03:38'),
(6, 'Schools', 'v2.system-admin.schools.index', NULL, 'system-admin', 5, 2, 'school', 1, 0, NULL, NULL, NULL, 0, 'SystemAdmin', 1, '2026-01-10 02:03:38', '2026-01-10 02:33:59'),
(7, 'Global Users', 'v2.system-admin.users.index', NULL, 'system-admin', 5, 3, 'group', 1, 0, NULL, NULL, NULL, 0, 'SystemAdmin', 1, '2026-01-10 02:03:38', '2026-01-10 02:33:59'),
(8, 'School Admin', 'v2.school-admin.dashboard', NULL, 'school-admin', NULL, 200, 'domain', 1, 0, NULL, NULL, NULL, 1, 'SchoolAdmin', 1, '2026-01-10 02:03:38', '2026-01-10 02:03:38'),
(9, 'Academics', 'v2.school-admin.academics.index', NULL, 'school-admin', 8, 2, 'menu_book', 1, 0, NULL, NULL, NULL, 1, 'SchoolAdmin', 1, '2026-01-10 02:03:38', '2026-01-10 02:03:38'),
(10, 'Staff & Students', 'v2.school-admin.people.index', NULL, 'school-admin', 8, 3, 'people', 1, 0, NULL, NULL, NULL, 1, 'SchoolAdmin', 1, '2026-01-10 02:03:38', '2026-01-10 02:03:38'),
(11, 'Schools', 'v2.system-admin.schools.index', NULL, 'system-admin', 5, 2, 'school', 1, 0, NULL, NULL, NULL, 0, 'SystemAdmin', 1, '2026-01-10 02:26:18', '2026-01-10 02:26:18'),
(12, 'Global Users', 'v2.system-admin.users.index', NULL, 'system-admin', 5, 3, 'group', 1, 0, NULL, NULL, NULL, 0, 'SystemAdmin', 1, '2026-01-10 02:26:18', '2026-01-10 02:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `conversation_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `attachment_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_seen` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2023_12_31_000000_create_h_r_s_table', 1),
(6, '2024_01_10_000000_create_messages_table', 1),
(7, '2024_11_29_145112_create_subjects_table', 1),
(8, '2024_11_29_145121_create_stages_table', 1),
(9, '2024_11_29_145125_create_grades_table', 1),
(10, '2024_11_29_145126_create_grade_subject_table', 1),
(11, '2024_11_29_145129_create_classrooms_table', 1),
(12, '2024_11_29_145203_create_teachers_table', 1),
(13, '2024_11_29_145303_create_student_parents_table', 1),
(14, '2024_11_29_145354_create_students_table', 1),
(15, '2024_11_29_145504_create_academic_years_table', 1),
(16, '2024_11_29_145511_create_semesters_table', 1),
(17, '2024_11_29_145512_create_semester_tests_table', 1),
(18, '2024_11_29_145519_create_calendars_table', 1),
(19, '2024_11_29_145520_create_classroom_subject_teachers_table', 1),
(20, '2024_11_29_145521_create_period_details_table', 1),
(21, '2024_11_29_145521_create_schedule_copies_table', 1),
(22, '2024_11_29_145522_create_schedule_timings_table', 1),
(24, '2024_11_30_130314_create_curricula_table', 1),
(25, '2024_11_30_130327_create_curriculum_topics_table', 1),
(26, '2024_11_30_130328_create_curriculum_lessons_table', 1),
(27, '2025_03_16_212543_create_personal_access_tokens_table', 1),
(28, '2025_03_16_214736_create_permission_tables', 1),
(29, '2025_03_19_000004_create_period_activities_table', 1),
(30, '2025_03_19_000005_create_student_period_records_table', 1),
(31, '2025_03_20_075001_create_activity_logs_table', 1),
(32, '2025_03_23_235403_create_schedule_dailies_table', 1),
(33, '2025_03_23_235403_create_schedule_daily_records_table', 1),
(34, '2025_03_24_233203_create_documentations_table', 1),
(35, '2025_04_26_105033_create_tree_structures_table', 1),
(36, '2025_05_14_095803_create_push_subscriptions_table', 1),
(37, '2025_05_14_145623_create_notifications_table', 1),
(38, '2025_05_16_000000_create_conversations_table', 1),
(39, '2025_05_16_000001_create_conversation_user_table', 1),
(40, '2025_05_16_000002_create_messages_table', 1),
(41, '2025_05_16_000003_drop_message_recipients_table', 1),
(42, '2025_05_20_000000_create_lessons_table', 1),
(43, '2025_05_20_000000_create_user_messages_table', 1),
(44, '2025_05_22_025858_create_curriculum_lesson_plans_table', 1),
(45, '2025_05_22_064638_create_curriculum_maps_table', 1),
(46, '2025_06_01_000000_create_tasks_table', 1),
(47, '2025_06_01_000001_create_pomodoro_sessions_table', 1),
(48, '2025_07_06_000000_create_project_tasks_table', 1),
(49, '2025_07_06_000000_create_resume_questions_table', 1),
(50, '2025_07_06_000001_create_resume_answers_table', 1),
(51, '2025_07_06_000002_create_resume_question_comments_table', 1),
(52, '2025_07_06_000003_create_resume_answer_ratings_table', 1),
(53, '2025_07_06_000004_create_resume_answer_likes_table', 1),
(54, '2025_07_06_000005_create_resume_comment_likes_table', 1),
(55, '2025_07_06_000006_create_resume_answer_bookmarks_table', 1),
(56, '2025_07_06_000007_create_resume_answer_reports_table', 1),
(57, '2025_07_06_000008_add_voice_notes_to_resume_answers_table', 1),
(58, '2025_07_07_000000_add_enhanced_fields_to_resume_tables', 1),
(59, '2025_07_11_100000_create_qdrat_skill_levels_table', 1),
(60, '2025_07_11_101000_create_qdrat_skills_table', 1),
(61, '2025_07_11_102000_create_qdrat_lesson_categories_table', 1),
(62, '2025_07_11_103000_create_qdrat_lessons_table', 1),
(63, '2025_07_11_104000_create_qdrat_question_difficulties_table', 1),
(64, '2025_07_11_105000_create_qdrat_question_types_table', 1),
(65, '2025_07_11_105000_create_qdrat_questions_table', 1),
(66, '2025_07_12_125418_add_import_batch_to_qdrat_skills_table', 1),
(67, '2025_07_16_204055_create_courses_table', 1),
(68, '2025_07_16_204338_create_course_levels_table', 1),
(69, '2025_07_16_204342_create_course_sections_table', 1),
(70, '2025_07_16_204345_create_course_lessons_table', 1),
(71, '2025_07_17_062935_create_course_teacher_assignments_table', 1),
(72, '2025_07_17_100000_add_is_active_to_course_teacher_table', 1),
(73, '2025_07_17_194000_create_lesson_plan_templates_table', 1),
(76, '2025_08_28_080044_create_myproject_tasks_table', 1),
(77, '2025_08_28_082137_add_parent_id_to_myproject_tasks_table', 1),
(78, '2025_10_18_111239_create_behaviors_table', 1),
(79, '2025_10_18_112034_create_classroom_records_table', 1),
(80, '2025_10_18_112034_create_student_behaviors_mains_table', 1),
(81, '2025_10_18_112034_create_student_behaviors_table', 1),
(82, '2025_10_18_112035_create_student_behaviors_point_actions_table', 1),
(83, '2025_11_22_090121_create_lesson_presentations_table', 1),
(84, '2025_11_22_090122_create_lesson_presentation_slides_table', 1),
(85, '2025_11_22_221201_create_lesson_student_progress_table', 1),
(86, '2025_11_22_221240_create_lesson_practice_submissions_table', 1),
(87, '2025_11_22_221951_add_order_and_quiz_id_to_lesson_presentations_table', 1),
(88, '2025_11_24_061553_create_behavior_incidents_table', 1),
(89, '2025_11_25_100000_create_question_types_table', 1),
(90, '2025_11_25_100001_create_questions_table', 1),
(91, '2025_11_25_100002_create_question_options_table', 1),
(92, '2025_11_25_100003_create_quiz_attempts_table', 1),
(93, '2025_11_25_100004_create_quiz_attempt_answers_table', 1),
(94, '2025_11_25_200000_add_performance_indexes_to_quiz_tables', 1),
(95, '2025_11_26_000000_create_quizzes_table', 1),
(96, '2025_11_26_000000_migrate_lesson_questions_to_quiz_system', 1),
(97, '2025_11_29_103013_create_live_quiz_tables', 1),
(98, '2025_11_29_103014_add_quiz_session_id_to_quiz_attempts_table', 1),
(99, '2025_11_30_130333_create_question_banks_table', 1),
(100, '2025_12_02_181400_create_dp_tasks_table', 1),
(101, '2025_12_02_181401_create_dp_daily_tasks_table', 1),
(102, '2025_12_02_181401_create_dp_focus_logs_table', 1),
(103, '2025_12_02_181401_create_dp_rewards_table', 1),
(104, '2025_12_31_133828_create_menus_table', 1),
(105, 'create_resume_themes_table', 1),
(106, '2026_01_03_084441_add_period_order_to_schedules_table', 2),
(109, '2024_11_29_145522_create_schedules_table', 4),
(110, '2025_07_18_135501_create_weekly_plan_sessions_table', 4),
(111, '2025_07_18_135500_create_weekly_plans_table', 5),
(113, '2024_01_01_000000_create_schools_table', 6),
(114, '2026_01_10_000000_add_v2_fields_to_menus_table', 7),
(115, '2026_01_11_193500_create_student_classroom_history_table', 8),
(116, '2026_01_13_074456_add_deleted_at_to_curricula_table', 9),
(117, '2026_01_13_094217_create_qu_questions_table', 10),
(118, '2026_01_13_094428_create_qu_exams_table', 10),
(119, '2026_01_13_094430_create_qu_exam_questions_table', 10),
(120, '2026_01_13_094431_create_qu_attempts_table', 10),
(121, '2026_01_13_094433_create_qu_answers_table', 10),
(122, '2026_01_13_140512_add_exam_management_fields_to_qu_exams_table', 11),
(123, '2026_01_13_162758_add_settings_json_column_to_qu_exams_table', 12),
(124, '2026_01_13_174427_add_target_audience_to_qu_exams_table', 13);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 19),
(5, 'App\\Models\\User', 19),
(1, 'App\\Models\\User', 25),
(9, 'App\\Models\\User', 25);

-- --------------------------------------------------------

--
-- Table structure for table `myproject_tasks`
--

CREATE TABLE `myproject_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in_progress','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `period_activities`
--

CREATE TABLE `period_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `schedule_id` bigint UNSIGNED NOT NULL,
  `calendar_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `teacher_substitute_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_present` tinyint(1) NOT NULL DEFAULT '1',
  `teacher_plan` json DEFAULT NULL,
  `period_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed' COMMENT 'completed, cancelled, modified, event_affected',
  `lesson_notes` text COLLATE utf8mb4_unicode_ci,
  `lesson_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Links to weekly_plans.code if exists, format like 12.1.1.1',
  `improvement_notes` text COLLATE utf8mb4_unicode_ci,
  `was_duty_period` tinyint(1) NOT NULL DEFAULT '0',
  `duty_notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `period_details`
--

CREATE TABLE `period_details` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `code` tinyint NOT NULL,
  `sequence` tinyint NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main` tinyint NOT NULL DEFAULT '1',
  `time_before` tinyint DEFAULT NULL,
  `from` time DEFAULT NULL,
  `to` time DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage app', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(2, 'manage system settings', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(3, 'manage users', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(4, 'view users', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(5, 'create users', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(6, 'edit users', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(7, 'delete users', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(8, 'manage schools', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(9, 'view schools', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(10, 'create schools', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(11, 'edit schools', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(12, 'delete schools', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(13, 'manage hr', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(14, 'view hr', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(15, 'manage teachers', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(16, 'view teachers', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(17, 'create teachers', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(18, 'edit teachers', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(19, 'delete teachers', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(20, 'import teachers', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(21, 'manage students', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(22, 'view students', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(23, 'create students', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(24, 'edit students', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(25, 'delete students', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(26, 'import students', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(27, 'manage subjects', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(28, 'manage grades', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(29, 'manage classrooms', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(30, 'manage schedules', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(31, 'manage curriculum', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(32, 'create assignments', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(33, 'grade assignments', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(34, 'create course materials', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(35, 'manage student grades', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(36, 'view student progress', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(37, 'access course materials', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(38, 'submit assignments', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(39, 'view grades', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(40, 'communicate with students', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(41, 'communicate with teachers', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(42, 'communicate with parents', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(43, 'view reports', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(44, 'generate reports', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(45, 'manage roles', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(46, 'manage permissions', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(47, 'manage settings', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(48, 'view settings', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(49, 'review course content', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(50, 'participate in forums', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pomodoro_sessions`
--

CREATE TABLE `pomodoro_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('work','break') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'work',
  `duration` int NOT NULL DEFAULT '25' COMMENT 'Duration in minutes',
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'What was accomplished',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed' COMMENT 'completed, interrupted, extended',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE `project_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `subscribable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribable_id` bigint UNSIGNED NOT NULL,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qdrat_lessons`
--

CREATE TABLE `qdrat_lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `lesson_category_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qdrat_lesson_categories`
--

CREATE TABLE `qdrat_lesson_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qdrat_questions`
--

CREATE TABLE `qdrat_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_type_id` bigint UNSIGNED NOT NULL,
  `options` json DEFAULT NULL,
  `answer_text` text COLLATE utf8mb4_unicode_ci,
  `difficulty_level_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qdrat_question_difficulties`
--

CREATE TABLE `qdrat_question_difficulties` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qdrat_question_types`
--

CREATE TABLE `qdrat_question_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qdrat_question_types`
--

INSERT INTO `qdrat_question_types` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'multiple_choice', 'اختيار من متعدد', 'سؤال يحتوي على خيارات متعددة.', NULL, NULL),
(2, 'open_ended', 'سؤال مفتوح', 'سؤال يجيب عليه الطالب كتابة نص.', NULL, NULL),
(3, 'true_false', 'صح أو خطأ', 'سؤال به خياران: صح أو خطأ.', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `qdrat_skills`
--

CREATE TABLE `qdrat_skills` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `skill_level_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `import_batch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qdrat_skill_levels`
--

CREATE TABLE `qdrat_skill_levels` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` bigint UNSIGNED NOT NULL,
  `question_type_id` bigint UNSIGNED NOT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade_id` bigint UNSIGNED DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `topic_id` bigint UNSIGNED DEFAULT NULL,
  `bloom_level` tinyint DEFAULT NULL COMMENT 'Bloom taxonomy level 1-6',
  `difficulty_level` tinyint DEFAULT NULL COMMENT 'Difficulty level 1-5',
  `estimated_time_sec` int DEFAULT NULL COMMENT 'Estimated time to complete in seconds',
  `usage_count` int NOT NULL DEFAULT '0' COMMENT 'Number of times question has been used',
  `avg_success_rate` decimal(5,2) DEFAULT NULL COMMENT 'Average success rate percentage',
  `discrimination_index` decimal(5,2) DEFAULT NULL COMMENT 'Statistical measure of question quality',
  `author_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('draft','active','archived','review') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `hints` json DEFAULT NULL COMMENT 'Array of hint strings',
  `explanation` json DEFAULT NULL COMMENT 'Object with text and revealed_after_attempt fields',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_banks`
--

CREATE TABLE `question_banks` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `curriculum_id` bigint UNSIGNED DEFAULT NULL,
  `curriculum_lessons_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Question head/title',
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Question details/content',
  `options` json DEFAULT NULL COMMENT 'Multiple-choice options: {A: "option1", B: "option2", ...}',
  `correct_answer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Correct answer key (A, B, C, etc.)',
  `resources` json DEFAULT NULL COMMENT 'Images, PDFs, attachments',
  `type` enum('mcq','true_false','fill_blank','essay','short_answer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mcq',
  `score` int NOT NULL DEFAULT '1',
  `difficulty` enum('easy','medium','hard') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Comma-separated tags',
  `status` enum('draft','active','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL COMMENT 'Additional question metadata',
  `notes_admin` text COLLATE utf8mb4_unicode_ci,
  `notes_teacher` text COLLATE utf8mb4_unicode_ci,
  `explanation` json DEFAULT NULL COMMENT 'Answer explanation and reasoning',
  `question_data` json DEFAULT NULL COMMENT 'Additional structured question data',
  `created_by_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `option_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Option identifier: A, B, C, D, etc.',
  `option_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `distractor_strength` decimal(5,2) DEFAULT NULL COMMENT 'Analytics metric for incorrect options',
  `order_index` int NOT NULL DEFAULT '0' COMMENT 'Display order of option',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_types`
--

CREATE TABLE `question_types` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `has_options` tinyint(1) NOT NULL DEFAULT '0',
  `supports_hints` tinyint(1) NOT NULL DEFAULT '1',
  `supports_explanation` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_types`
--

INSERT INTO `question_types` (`id`, `slug`, `name`, `has_options`, `supports_hints`, `supports_explanation`, `created_at`, `updated_at`) VALUES
(1, 'multiple_choice', 'Multiple Choice', 1, 1, 1, '2026-01-03 01:24:39', '2026-01-03 01:24:39'),
(2, 'multi_select', 'Multi Select', 1, 1, 1, '2026-01-03 01:24:39', '2026-01-03 01:24:39'),
(3, 'true_false', 'True/False', 1, 1, 1, '2026-01-03 01:24:39', '2026-01-03 01:24:39'),
(4, 'fill_blank', 'Fill in the Blank', 0, 1, 1, '2026-01-03 01:24:39', '2026-01-03 01:24:39'),
(5, 'short_answer', 'Short Answer', 0, 1, 1, '2026-01-03 01:24:39', '2026-01-03 01:24:39'),
(6, 'essay', 'Essay', 0, 1, 1, '2026-01-03 01:24:39', '2026-01-03 01:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `school_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `grade_id` bigint UNSIGNED DEFAULT NULL,
  `created_by_id` bigint UNSIGNED NOT NULL,
  `status` enum('draft','active','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `time_limit_minutes` int DEFAULT NULL,
  `shuffle_questions` tinyint(1) NOT NULL DEFAULT '0',
  `shuffle_options` tinyint(1) NOT NULL DEFAULT '0',
  `allow_review` tinyint(1) NOT NULL DEFAULT '1',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `quiz_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Optional reference to a quiz collection',
  `quiz_session_id` bigint UNSIGNED DEFAULT NULL,
  `started_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `total_questions` int NOT NULL,
  `correct_answers` int NOT NULL DEFAULT '0',
  `percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `metadata` json DEFAULT NULL COMMENT 'Additional quiz attempt metadata',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempt_answers`
--

CREATE TABLE `quiz_attempt_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `attempt_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `selected_option_id` bigint UNSIGNED DEFAULT NULL,
  `selected_text` text COLLATE utf8mb4_unicode_ci COMMENT 'For text-based answers like fill-in-blank or short answer',
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `time_spent_sec` int NOT NULL DEFAULT '0' COMMENT 'Time spent on this question in seconds',
  `answered_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_question`
--

CREATE TABLE `quiz_question` (
  `id` bigint UNSIGNED NOT NULL,
  `quiz_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `order_index` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_sessions`
--

CREATE TABLE `quiz_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `quiz_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `access_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('waiting','active','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `current_question_id` bigint UNSIGNED DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_session_participants`
--

CREATE TABLE `quiz_session_participants` (
  `id` bigint UNSIGNED NOT NULL,
  `quiz_session_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `score` int NOT NULL DEFAULT '0',
  `status` enum('joined','active','disconnected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'joined',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qu_answers`
--

CREATE TABLE `qu_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `qu_attempt_id` bigint UNSIGNED NOT NULL,
  `qu_question_id` bigint UNSIGNED NOT NULL,
  `selected_options` json DEFAULT NULL,
  `answer_text` text COLLATE utf8mb4_unicode_ci,
  `marks_obtained` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qu_attempts`
--

CREATE TABLE `qu_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `qu_exam_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `score` int DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qu_exams`
--

CREATE TABLE `qu_exams` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `exam_type` enum('practice','quiz','midterm','final','survey') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quiz',
  `custom_group` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_attempts` int DEFAULT NULL COMMENT 'NULL = unlimited attempts',
  `mark_calculation_method` enum('last','best','average') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'last',
  `passing_score` decimal(5,2) DEFAULT NULL COMMENT 'Minimum score to pass, NULL = no passing requirement',
  `subject_id` bigint UNSIGNED NOT NULL,
  `duration_minutes` int NOT NULL,
  `total_marks` int NOT NULL,
  `bloom_distribution` json DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL COMMENT 'When exam becomes available',
  `end_date` datetime DEFAULT NULL COMMENT 'Submission deadline',
  `publish_results_timing` enum('immediate','after_end','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `settings` json DEFAULT NULL,
  `target_audience` json DEFAULT NULL COMMENT 'Defines who can see/take the exam. NULL = Public/Everyone.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qu_exams`
--

INSERT INTO `qu_exams` (`id`, `title`, `description`, `exam_type`, `custom_group`, `max_attempts`, `mark_calculation_method`, `passing_score`, `subject_id`, `duration_minutes`, `total_marks`, `bloom_distribution`, `created_by`, `is_published`, `start_date`, `end_date`, `publish_results_timing`, `settings`, `target_audience`, `created_at`, `updated_at`) VALUES
(2, 'Dolore obcaecati autem doloribus dolores in nihil illo qui aut ab quis odio harum recusandae', 'Sit consequatur labo', 'quiz', 'In quia qui id nihil', NULL, 'last', 50.00, 1, 60, 6, NULL, 19, 1, NULL, NULL, 'immediate', '{\"shuffle_options\": false, \"shuffle_questions\": true}', NULL, '2026-01-13 13:35:31', '2026-01-13 14:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `qu_exam_questions`
--

CREATE TABLE `qu_exam_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `qu_exam_id` bigint UNSIGNED NOT NULL,
  `qu_question_id` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qu_exam_questions`
--

INSERT INTO `qu_exam_questions` (`id`, `qu_exam_id`, `qu_question_id`, `order`, `created_at`, `updated_at`) VALUES
(7, 2, 5, 0, '2026-01-13 13:35:31', '2026-01-13 13:35:31'),
(8, 2, 6, 0, '2026-01-13 13:35:31', '2026-01-13 13:35:31'),
(12, 2, 7, 0, '2026-01-13 13:47:49', '2026-01-13 13:47:49'),
(13, 2, 8, 0, '2026-01-13 13:47:49', '2026-01-13 13:47:49'),
(14, 2, 9, 0, '2026-01-13 13:47:49', '2026-01-13 13:47:49'),
(15, 2, 10, 0, '2026-01-13 13:47:49', '2026-01-13 13:47:49');

-- --------------------------------------------------------

--
-- Table structure for table `qu_questions`
--

CREATE TABLE `qu_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `topic_id` bigint UNSIGNED DEFAULT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_type` enum('mcq','true_false','short','long') COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json DEFAULT NULL,
  `correct_answer` json DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `bloom_level` enum('remember','understand','apply','analyze','evaluate','create') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marks` int NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qu_questions`
--

INSERT INTO `qu_questions` (`id`, `subject_id`, `topic_id`, `question_text`, `question_type`, `options`, `correct_answer`, `difficulty`, `bloom_level`, `marks`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 1, NULL, 'Ipsa illum eius id illum aut excepturi deserunt sint commodo natus', 'mcq', '{\"A\": \"Quis Nam doloribus r\", \"B\": \"In dolor minima cons\", \"C\": \"Officia cupidatat es\", \"D\": \"Dolor culpa incidunt\"}', '[\"A\"]', 'medium', NULL, 66, 19, '2026-01-13 07:54:45', '2026-01-13 08:01:47'),
(4, 1, NULL, 'Id fugit ipsum vitae deserunt in qui qui Nam voluptas eos qui inventore sit asperiores in esse', 'mcq', '{\"A\": \"Quia ut dolor sequi\", \"B\": \"Veniam consectetur\", \"C\": \"Est praesentium anim\", \"D\": \"Neque voluptas offic\"}', '[\"A\"]', 'medium', NULL, 32, 19, '2026-01-13 08:01:34', '2026-01-13 08:01:34'),
(5, 1, NULL, 'What is the sum of $\\frac{5}{12}$ and $\\frac{7}{18}$ in its simplest form?', 'mcq', '{\"A\": \"$\\\\frac{29}{36}$\", \"B\": \"$\\\\frac{12}{30}$\", \"C\": \"$\\\\frac{35}{36}$\", \"D\": \"$\\\\frac{17}{18}$\"}', '[\"A\"]', 'medium', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(6, 1, NULL, 'Add $\\frac{11}{15}$, $\\frac{4}{25}$, and $\\frac{7}{10}$. Express the result as a mixed number in simplest form.', 'short', '[]', '[\"N/A\"]', 'hard', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(7, 1, NULL, 'Which of the following is equivalent to $\\frac{17}{24} + \\frac{5}{16}$?', 'mcq', '{\"A\": \"$\\\\frac{89}{96}$\", \"B\": \"$\\\\frac{22}{40}$\", \"C\": \"$\\\\frac{61}{96}$\", \"D\": \"$\\\\frac{47}{96}$\"}', '[\"A\"]', 'medium', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(8, 1, NULL, 'Evaluate $\\frac{9}{14} + \\frac{11}{21} + \\frac{5}{28}$ and simplify completely.', 'short', '[]', '[\"N/A\"]', 'hard', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(9, 1, NULL, 'What value of $x$ makes the equation true: $\\frac{x}{5} + \\frac{7}{12} = \\frac{19}{20}$?', 'mcq', '{\"A\": \"$\\\\frac{11}{30}$\", \"B\": \"$\\\\frac{4}{15}$\", \"C\": \"$\\\\frac{2}{5}$\", \"D\": \"$\\\\frac{13}{60}$\"}', '[\"A\"]', 'hard', 'analyze', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(10, 1, NULL, 'Simplify the expression: $\\frac{13}{18} + \\frac{5}{24} - \\frac{7}{36}$', 'short', '[]', '[\"N/A\"]', 'hard', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(11, 1, NULL, 'Which sum is greatest? (A) $\\frac{5}{8} + \\frac{7}{12}$, (B) $\\frac{11}{15} + \\frac{4}{9}$, (C) $\\frac{13}{20} + \\frac{5}{12}$, (D) $\\frac{17}{24} + \\frac{3}{10}$', 'mcq', '{\"A\": \"A\", \"B\": \"B\", \"C\": \"C\", \"D\": \"D\"}', '[\"B\"]', 'hard', 'evaluate', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(12, 1, NULL, 'A recipe requires $\\frac{3}{4}$ cup of sugar for one batch and $\\frac{5}{6}$ cup for another. How much sugar is needed in total? Give answer as improper fraction and mixed number.', 'short', '[]', '[\"N/A\"]', 'medium', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(13, 1, NULL, 'Find the value of $\\frac{8}{15} + \\frac{11}{20} + \\frac{7}{30}$ in simplest form.', 'mcq', '{\"A\": \"$\\\\frac{79}{60}$\", \"B\": \"$\\\\frac{26}{20}$\", \"C\": \"$\\\\frac{53}{60}$\", \"D\": \"$\\\\frac{41}{60}$\"}', '[\"A\"]', 'hard', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(14, 1, NULL, 'Which of the following statements is true about the sum $\\frac{19}{24} + \\frac{13}{40}$?', 'mcq', '{\"A\": \"The result is greater than 1 but less than $1\\\\frac{1}{4}$\", \"B\": \"The result is exactly 1\", \"C\": \"The result is greater than $1\\\\frac{1}{4}$\", \"D\": \"The result is less than $\\\\frac{3}{4}$\"}', '[\"A\"]', 'hard', 'evaluate', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(15, 1, NULL, 'Calculate: $\\frac{23}{36} + \\frac{17}{48} + \\frac{5}{18}$ and express in simplest form.', 'short', '[]', '[\"N/A\"]', 'hard', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(16, 1, NULL, 'If $\\frac{a}{12} + \\frac{5}{18} = \\frac{11}{12}$, what is the value of $a$?', 'mcq', '{\"A\": \"7\", \"B\": \"6\", \"C\": \"8\", \"D\": \"9\"}', '[\"A\"]', 'medium', 'analyze', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(17, 1, NULL, 'Three friends ate $\\frac{3}{10}$, $\\frac{1}{4}$, and $\\frac{7}{20}$ of a cake. What fraction of the cake did they eat together?', 'short', '[]', '[\"N/A\"]', 'medium', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(18, 1, NULL, 'Which option is the correct simplified sum of $\\frac{29}{42} + \\frac{11}{35} + \\frac{4}{21}$?', 'mcq', '{\"A\": \"$\\\\frac{19}{14}$\", \"B\": \"$\\\\frac{37}{42}$\", \"C\": \"$\\\\frac{53}{70}$\", \"D\": \"$\\\\frac{79}{105}$\"}', '[\"A\"]', 'hard', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(19, 1, NULL, 'Determine whether $\\frac{7}{15} + \\frac{8}{25} + \\frac{11}{30}$ is greater than, less than, or equal to 1. Justify briefly by calculation.', 'short', '[]', '[\"N/A\"]', 'hard', 'evaluate', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(20, 1, NULL, 'What is the least common multiple of the denominators 18, 24, and 36 that would be most efficient when adding $\\frac{5}{18} + \\frac{7}{24} + \\frac{11}{36}$?', 'mcq', '{\"A\": \"72\", \"B\": \"144\", \"C\": \"216\", \"D\": \"108\"}', '[\"A\"]', 'medium', 'analyze', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(21, 1, NULL, 'Add $\\frac{47}{60} + \\frac{19}{75} + \\frac{11}{100}$ and simplify to lowest terms.', 'short', '[]', '[\"N/A\"]', 'hard', 'apply', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(22, 1, NULL, 'Which of these sums is closest to 2? (A) $\\frac{5}{6} + \\frac{7}{8} + \\frac{11}{12}$, (B) $\\frac{3}{4} + \\frac{13}{15} + \\frac{17}{20}$, (C) $\\frac{9}{10} + \\frac{19}{24} + \\frac{29}{30}$', 'mcq', '{\"A\": \"A\", \"B\": \"B\", \"C\": \"C\"}', '[\"C\"]', 'hard', 'evaluate', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(23, 1, NULL, 'Solve for the missing fraction: $\\frac{?}{28} + \\frac{5}{14} + \\frac{11}{35} = \\frac{17}{20}$', 'short', '[]', '[\"N/A\"]', 'hard', 'analyze', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(24, 1, NULL, 'A tank is $\\frac{5}{12}$ full. After adding $\\frac{7}{15}$ more, it becomes $\\frac{17}{20}$ full. Was the amount added correct? Justify with calculation.', 'short', '[]', '[\"N/A\"]', 'hard', 'evaluate', 1, 19, '2026-01-13 08:25:10', '2026-01-13 08:25:10'),
(25, 1, NULL, 'What is $\\frac{1}{5} + \\frac{2}{5}$?', 'mcq', '{\"A\": \"$\\\\frac{3}{10}$\", \"B\": \"$\\\\frac{3}{5}$\", \"C\": \"$\\\\frac{1}{5}$\", \"D\": \"$\\\\frac{3}{25}$\"}', '[\"B\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36'),
(26, 1, NULL, 'When adding fractions with the same denominator, what do you do with the denominators?', 'mcq', '{\"A\": \"Add them together\", \"B\": \"Keep the same denominator\", \"C\": \"Multiply them\", \"D\": \"Find a new common denominator\"}', '[\"B\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36'),
(27, 1, NULL, 'What is $\\frac{3}{8} + \\frac{4}{8}$?', 'mcq', '{\"A\": \"$\\\\frac{7}{16}$\", \"B\": \"$\\\\frac{7}{8}$\", \"C\": \"$\\\\frac{12}{8}$\", \"D\": \"$\\\\frac{7}{64}$\"}', '[\"B\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36'),
(28, 1, NULL, 'What is the sum of $\\frac{2}{10}$ and $\\frac{5}{10}$?', 'mcq', '{\"A\": \"$\\\\frac{7}{20}$\", \"B\": \"$\\\\frac{7}{10}$\", \"C\": \"$\\\\frac{10}{10}$\", \"D\": \"$\\\\frac{3}{10}$\"}', '[\"B\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36'),
(29, 1, NULL, 'Which of these is the correct way to add $\\frac{1}{6} + \\frac{3}{6}$?', 'mcq', '{\"A\": \"$\\\\frac{4}{12}$\", \"B\": \"$\\\\frac{1+3}{6}$\", \"C\": \"$\\\\frac{3}{6}$\", \"D\": \"$\\\\frac{4}{6+6}$\"}', '[\"B\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36'),
(30, 1, NULL, 'What is $\\frac{4}{9} + \\frac{2}{9}$ in simplest form?', 'mcq', '{\"A\": \"$\\\\frac{6}{18}$\", \"B\": \"$\\\\frac{6}{9}$\", \"C\": \"$\\\\frac{2}{3}$\", \"D\": \"$\\\\frac{8}{9}$\"}', '[\"C\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36'),
(31, 1, NULL, 'Adding $\\frac{5}{7} + \\frac{1}{7}$ gives:', 'mcq', '{\"A\": \"$\\\\frac{6}{14}$\", \"B\": \"$\\\\frac{6}{7}$\", \"C\": \"$\\\\frac{5}{7}$\", \"D\": \"$\\\\frac{1}{7}$\"}', '[\"B\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36'),
(32, 1, NULL, 'What is the result of $\\frac{0}{4} + \\frac{3}{4}$?', 'mcq', '{\"A\": \"$\\\\frac{3}{8}$\", \"B\": \"$\\\\frac{3}{4}$\", \"C\": \"$\\\\frac{0}{4}$\", \"D\": \"$\\\\frac{3}{0}$\"}', '[\"B\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36'),
(33, 1, NULL, 'Which fraction is the sum of $\\frac{6}{12}$ and $\\frac{4}{12}$?', 'mcq', '{\"A\": \"$\\\\frac{10}{24}$\", \"B\": \"$\\\\frac{10}{12}$\", \"C\": \"$\\\\frac{2}{12}$\", \"D\": \"$\\\\frac{24}{12}$\"}', '[\"B\"]', 'easy', 'remember', 1, 19, '2026-01-13 08:29:36', '2026-01-13 08:29:36');

-- --------------------------------------------------------

--
-- Table structure for table `resume_answers`
--

CREATE TABLE `resume_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `answer_text` text COLLATE utf8mb4_unicode_ci,
  `media_links` json DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `status` enum('draft','published','review','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `answer` text COLLATE utf8mb4_unicode_ci,
  `media_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `voice_note_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voice_note_duration` int DEFAULT NULL COMMENT 'Duration in seconds',
  `voice_note_metadata` json DEFAULT NULL,
  `views_count` int NOT NULL DEFAULT '0',
  `average_rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `ratings_count` int NOT NULL DEFAULT '0',
  `likes_count` int NOT NULL DEFAULT '0',
  `comments_count` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `featured_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_answer_bookmarks`
--

CREATE TABLE `resume_answer_bookmarks` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `answer_id` bigint UNSIGNED NOT NULL,
  `bookmark_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'favorite',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_answer_likes`
--

CREATE TABLE `resume_answer_likes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `answer_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_answer_ratings`
--

CREATE TABLE `resume_answer_ratings` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `answer_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint UNSIGNED NOT NULL COMMENT 'Rating from 1 to 5',
  `review_comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_answer_reports`
--

CREATE TABLE `resume_answer_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `answer_id` bigint UNSIGNED NOT NULL,
  `report_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_comment_likes`
--

CREATE TABLE `resume_comment_likes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `comment_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_questions`
--

CREATE TABLE `resume_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` json DEFAULT NULL,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `tags` json DEFAULT NULL,
  `options` json DEFAULT NULL,
  `default_answer` text COLLATE utf8mb4_unicode_ci,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `body` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,
  `type` enum('text','audio','video','file') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_question_comments`
--

CREATE TABLE `resume_question_comments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `answer_id` bigint UNSIGNED DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `media_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_themes`
--

CREATE TABLE `resume_themes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `style` json NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(2, 'admin', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(3, 'hr_admin', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(4, 'supervisor', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(5, 'teacher', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(6, 'student', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(7, 'parent', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(8, 'user', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40'),
(9, 'SuperSystem', 'web', '2026-01-10 02:17:13', '2026-01-10 02:17:13');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(9, 2),
(11, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(36, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(47, 2),
(48, 2),
(3, 3),
(4, 3),
(5, 3),
(6, 3),
(8, 3),
(9, 3),
(10, 3),
(11, 3),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(20, 3),
(43, 3),
(15, 4),
(16, 4),
(36, 4),
(41, 4),
(43, 4),
(49, 4),
(22, 5),
(32, 5),
(33, 5),
(34, 5),
(35, 5),
(36, 5),
(37, 5),
(39, 5),
(40, 5),
(42, 5),
(50, 5),
(37, 6),
(38, 6),
(39, 6),
(50, 6),
(36, 7),
(39, 7),
(41, 7),
(1, 9),
(2, 9),
(3, 9),
(4, 9),
(5, 9),
(6, 9),
(7, 9),
(8, 9),
(9, 9),
(10, 9),
(11, 9),
(12, 9),
(13, 9),
(14, 9),
(15, 9),
(16, 9),
(17, 9),
(18, 9),
(19, 9),
(20, 9),
(21, 9),
(22, 9),
(23, 9),
(24, 9),
(25, 9),
(26, 9),
(27, 9),
(28, 9),
(29, 9),
(30, 9),
(31, 9),
(32, 9),
(33, 9),
(34, 9),
(35, 9),
(36, 9),
(37, 9),
(38, 9),
(39, 9),
(40, 9),
(41, 9),
(42, 9),
(43, 9),
(44, 9),
(45, 9),
(46, 9),
(47, 9),
(48, 9),
(49, 9),
(50, 9);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `copy_id` bigint UNSIGNED NOT NULL,
  `cst_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `teacher_substitute_id` bigint UNSIGNED DEFAULT NULL,
  `co_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `co_subject_id` bigint UNSIGNED DEFAULT NULL,
  `period_number` tinyint DEFAULT NULL,
  `day_number` tinyint DEFAULT NULL,
  `period_order` tinyint DEFAULT NULL,
  `place` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Physical location or classroom',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether this schedule is currently active',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Additional notes or comments',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `copy_id`, `cst_id`, `school_id`, `teacher_substitute_id`, `co_teacher_id`, `co_subject_id`, `period_number`, `day_number`, `period_order`, `place`, `active`, `notes`, `created_at`, `updated_at`) VALUES
(680, 5, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(681, 5, 1, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(682, 5, 1, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(683, 5, 1, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(684, 5, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(685, 5, 1, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(686, 5, 1, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(687, 5, 2, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(688, 5, 2, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(689, 5, 2, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(690, 5, 2, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(691, 5, 2, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(692, 5, 3, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(693, 5, 3, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(694, 5, 3, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(695, 5, 3, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(696, 5, 3, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(697, 5, 4, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(698, 5, 4, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(699, 5, 4, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(700, 5, 4, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(701, 5, 5, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(702, 5, 5, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(703, 5, 5, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(704, 5, 5, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(705, 5, 6, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(706, 5, 6, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(707, 5, 7, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(708, 5, 7, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(709, 5, 8, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(710, 5, 8, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(711, 5, 9, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(712, 5, 10, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(713, 5, 11, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(714, 5, 12, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(715, 5, 13, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(716, 5, 14, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(717, 5, 15, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(718, 5, 16, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(719, 5, 17, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(720, 5, 17, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(721, 5, 17, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(722, 5, 17, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(723, 5, 17, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(724, 5, 17, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(725, 5, 17, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(726, 5, 18, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(727, 5, 18, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(728, 5, 18, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(729, 5, 18, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(730, 5, 18, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(731, 5, 19, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(732, 5, 19, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(733, 5, 19, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(734, 5, 19, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(735, 5, 19, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(736, 5, 20, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(737, 5, 20, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(738, 5, 20, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(739, 5, 20, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(740, 5, 21, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(741, 5, 21, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(742, 5, 21, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(743, 5, 21, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(744, 5, 22, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(745, 5, 22, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(746, 5, 23, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(747, 5, 23, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(748, 5, 24, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(749, 5, 24, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(750, 5, 25, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(751, 5, 26, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(752, 5, 27, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(753, 5, 28, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(754, 5, 29, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(755, 5, 30, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(756, 5, 31, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(757, 5, 32, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(758, 5, 33, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(759, 5, 33, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(760, 5, 33, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(761, 5, 33, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(762, 5, 33, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(763, 5, 33, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(764, 5, 33, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(765, 5, 34, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(766, 5, 34, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(767, 5, 34, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:33', '2026-01-12 08:08:33'),
(768, 5, 34, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(769, 5, 34, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(770, 5, 35, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(771, 5, 35, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(772, 5, 35, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(773, 5, 35, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(774, 5, 35, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(775, 5, 36, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(776, 5, 36, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(777, 5, 36, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(778, 5, 36, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(779, 5, 37, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(780, 5, 37, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(781, 5, 37, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(782, 5, 37, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(783, 5, 38, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(784, 5, 38, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(785, 5, 39, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(786, 5, 39, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(787, 5, 40, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(788, 5, 40, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(789, 5, 41, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(790, 5, 42, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(791, 5, 43, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(792, 5, 44, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(793, 5, 45, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(794, 5, 46, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(795, 5, 47, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(796, 5, 48, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(797, 5, 49, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(798, 5, 49, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(799, 5, 49, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(800, 5, 49, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(801, 5, 49, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(802, 5, 49, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(803, 5, 49, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(804, 5, 50, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(805, 5, 50, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(806, 5, 50, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(807, 5, 50, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(808, 5, 50, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(809, 5, 51, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(810, 5, 51, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(811, 5, 51, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(812, 5, 51, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(813, 5, 51, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(814, 5, 52, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(815, 5, 52, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(816, 5, 52, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(817, 5, 52, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(818, 5, 53, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(819, 5, 53, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(820, 5, 53, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(821, 5, 53, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(822, 5, 54, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(823, 5, 54, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(824, 5, 55, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(825, 5, 55, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(826, 5, 56, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(827, 5, 56, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(828, 5, 57, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(829, 5, 58, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(830, 5, 59, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(831, 5, 60, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(832, 5, 61, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(833, 5, 62, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(834, 5, 63, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(835, 5, 64, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(836, 5, 65, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(837, 5, 65, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(838, 5, 65, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(839, 5, 65, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(840, 5, 65, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(841, 5, 66, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(842, 5, 66, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(843, 5, 66, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(844, 5, 66, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(845, 5, 66, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(846, 5, 67, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(847, 5, 67, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(848, 5, 67, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(849, 5, 67, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(850, 5, 68, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(851, 5, 68, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(852, 5, 68, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(853, 5, 68, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(854, 5, 69, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(855, 5, 69, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(856, 5, 69, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(857, 5, 69, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(858, 5, 70, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(859, 5, 70, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(860, 5, 71, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(861, 5, 71, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(862, 5, 72, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(863, 5, 72, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(864, 5, 73, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(865, 5, 74, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(866, 5, 75, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(867, 5, 76, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(868, 5, 77, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(869, 5, 78, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(870, 5, 79, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(871, 5, 80, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(872, 5, 81, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(873, 5, 81, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(874, 5, 81, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(875, 5, 81, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(876, 5, 81, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(877, 5, 82, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(878, 5, 82, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(879, 5, 82, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(880, 5, 82, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(881, 5, 82, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(882, 5, 83, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(883, 5, 83, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(884, 5, 83, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(885, 5, 83, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(886, 5, 84, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(887, 5, 84, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(888, 5, 84, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(889, 5, 84, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(890, 5, 85, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(891, 5, 85, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(892, 5, 85, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(893, 5, 85, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(894, 5, 86, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(895, 5, 86, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(896, 5, 87, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(897, 5, 87, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(898, 5, 88, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(899, 5, 88, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(900, 5, 89, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(901, 5, 90, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(902, 5, 91, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(903, 5, 92, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(904, 5, 93, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(905, 5, 94, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(906, 5, 95, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(907, 5, 96, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(908, 5, 97, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(909, 5, 97, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(910, 5, 97, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(911, 5, 97, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(912, 5, 97, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(913, 5, 98, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(914, 5, 98, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(915, 5, 98, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(916, 5, 98, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(917, 5, 98, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(918, 5, 99, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(919, 5, 99, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(920, 5, 99, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(921, 5, 99, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(922, 5, 100, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(923, 5, 100, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(924, 5, 100, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(925, 5, 100, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(926, 5, 101, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(927, 5, 101, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(928, 5, 101, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(929, 5, 101, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(930, 5, 102, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(931, 5, 102, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(932, 5, 103, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(933, 5, 103, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(934, 5, 104, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(935, 5, 104, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(936, 5, 105, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(937, 5, 106, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(938, 5, 107, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(939, 5, 108, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(940, 5, 109, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(941, 5, 110, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(942, 5, 111, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(943, 5, 112, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(944, 5, 113, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(945, 5, 113, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(946, 5, 113, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(947, 5, 113, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(948, 5, 113, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(949, 5, 114, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(950, 5, 114, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(951, 5, 114, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(952, 5, 114, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(953, 5, 114, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(954, 5, 115, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(955, 5, 115, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(956, 5, 115, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(957, 5, 115, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(958, 5, 116, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(959, 5, 116, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(960, 5, 116, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(961, 5, 116, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(962, 5, 117, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(963, 5, 117, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(964, 5, 117, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(965, 5, 117, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(966, 5, 118, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(967, 5, 118, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(968, 5, 119, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(969, 5, 119, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(970, 5, 120, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(971, 5, 120, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(972, 5, 121, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(973, 5, 122, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(974, 5, 123, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(975, 5, 124, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(976, 5, 125, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(977, 5, 126, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(978, 5, 127, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(979, 5, 128, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(980, 5, 128, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(981, 5, 128, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(982, 5, 128, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(983, 5, 128, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(984, 5, 128, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(985, 5, 128, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(986, 5, 129, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(987, 5, 129, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(988, 5, 129, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(989, 5, 129, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(990, 5, 129, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(991, 5, 130, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(992, 5, 130, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(993, 5, 130, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(994, 5, 130, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(995, 5, 130, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(996, 5, 131, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(997, 5, 131, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(998, 5, 131, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(999, 5, 131, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1000, 5, 132, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1001, 5, 132, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1002, 5, 132, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1003, 5, 132, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1004, 5, 133, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1005, 5, 133, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1006, 5, 134, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1007, 5, 134, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1008, 5, 135, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1009, 5, 135, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1010, 5, 136, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1011, 5, 137, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1012, 5, 138, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1013, 5, 139, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1014, 5, 140, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1015, 5, 141, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1016, 5, 142, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1017, 5, 143, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1018, 5, 143, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1019, 5, 143, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1020, 5, 143, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1021, 5, 143, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1022, 5, 144, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1023, 5, 144, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1024, 5, 144, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1025, 5, 144, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1026, 5, 144, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1027, 5, 145, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1028, 5, 145, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1029, 5, 145, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1030, 5, 145, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1031, 5, 146, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1032, 5, 146, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1033, 5, 146, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1034, 5, 146, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1035, 5, 147, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1036, 5, 147, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1037, 5, 147, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1038, 5, 147, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1039, 5, 148, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1040, 5, 148, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1041, 5, 149, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1042, 5, 149, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1043, 5, 150, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1044, 5, 150, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1045, 5, 151, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1046, 5, 151, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1047, 5, 152, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1048, 5, 153, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1049, 5, 154, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1050, 5, 155, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1051, 5, 156, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1052, 5, 157, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1053, 5, 158, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1054, 5, 158, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1055, 5, 158, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1056, 5, 158, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1057, 5, 158, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1058, 5, 159, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1059, 5, 159, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1060, 5, 159, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1061, 5, 159, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1062, 5, 159, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1063, 5, 160, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1064, 5, 160, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1065, 5, 160, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1066, 5, 161, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1067, 5, 161, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1068, 5, 161, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1069, 5, 162, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1070, 5, 162, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1071, 5, 163, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1072, 5, 163, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1073, 5, 164, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1074, 5, 164, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1075, 5, 165, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1076, 5, 165, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1077, 5, 166, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1078, 5, 166, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1079, 5, 167, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1080, 5, 168, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1081, 5, 169, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1082, 5, 170, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1083, 5, 171, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1084, 5, 172, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1085, 5, 173, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1086, 5, 174, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1087, 5, 175, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1088, 5, 176, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1089, 5, 177, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1090, 5, 177, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1091, 5, 177, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1092, 5, 177, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1093, 5, 177, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1094, 5, 178, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1095, 5, 178, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1096, 5, 178, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1097, 5, 179, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1098, 5, 179, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1099, 5, 179, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1100, 5, 180, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1101, 5, 180, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1102, 5, 180, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1103, 5, 181, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1104, 5, 181, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1105, 5, 181, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1106, 5, 182, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1107, 5, 182, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1108, 5, 182, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1109, 5, 183, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1110, 5, 183, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1111, 5, 183, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1112, 5, 184, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1113, 5, 184, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1114, 5, 185, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1115, 5, 185, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1116, 5, 186, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1117, 5, 187, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1118, 5, 188, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1119, 5, 189, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1120, 5, 190, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1121, 5, 191, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1122, 5, 192, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1123, 5, 193, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1124, 5, 194, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:34', '2026-01-12 08:08:34'),
(1125, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1126, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1127, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1128, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1129, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1130, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1131, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1132, 4, 2, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41');
INSERT INTO `schedules` (`id`, `copy_id`, `cst_id`, `school_id`, `teacher_substitute_id`, `co_teacher_id`, `co_subject_id`, `period_number`, `day_number`, `period_order`, `place`, `active`, `notes`, `created_at`, `updated_at`) VALUES
(1133, 4, 2, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1134, 4, 2, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1135, 4, 2, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1136, 4, 2, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1137, 4, 3, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1138, 4, 3, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1139, 4, 3, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1140, 4, 3, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1141, 4, 3, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1142, 4, 4, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1143, 4, 4, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1144, 4, 4, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1145, 4, 4, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1146, 4, 5, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1147, 4, 5, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1148, 4, 5, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1149, 4, 5, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1150, 4, 6, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1151, 4, 6, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1152, 4, 7, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1153, 4, 7, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1154, 4, 8, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1155, 4, 8, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1156, 4, 9, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1157, 4, 10, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1158, 4, 11, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1159, 4, 12, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1160, 4, 13, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1161, 4, 14, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1162, 4, 15, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1163, 4, 16, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1164, 4, 17, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1165, 4, 17, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1166, 4, 17, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1167, 4, 17, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1168, 4, 17, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1169, 4, 17, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1170, 4, 17, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1171, 4, 18, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1172, 4, 18, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1173, 4, 18, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1174, 4, 18, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1175, 4, 18, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1176, 4, 19, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1177, 4, 19, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1178, 4, 19, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1179, 4, 19, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1180, 4, 19, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1181, 4, 20, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1182, 4, 20, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1183, 4, 20, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1184, 4, 20, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1185, 4, 21, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1186, 4, 21, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1187, 4, 21, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1188, 4, 21, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1189, 4, 22, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1190, 4, 22, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1191, 4, 23, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1192, 4, 23, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1193, 4, 24, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1194, 4, 24, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1195, 4, 25, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1196, 4, 26, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1197, 4, 27, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1198, 4, 28, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1199, 4, 29, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1200, 4, 30, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1201, 4, 31, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1202, 4, 32, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1203, 4, 33, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1204, 4, 33, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1205, 4, 33, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1206, 4, 33, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1207, 4, 33, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1208, 4, 33, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1209, 4, 33, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1210, 4, 34, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1211, 4, 34, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1212, 4, 34, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1213, 4, 34, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1214, 4, 34, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1215, 4, 35, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1216, 4, 35, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1217, 4, 35, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1218, 4, 35, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1219, 4, 35, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1220, 4, 36, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1221, 4, 36, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1222, 4, 36, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1223, 4, 36, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1224, 4, 37, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1225, 4, 37, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1226, 4, 37, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1227, 4, 37, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1228, 4, 38, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1229, 4, 38, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1230, 4, 39, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1231, 4, 39, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1232, 4, 40, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1233, 4, 40, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1234, 4, 41, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1235, 4, 42, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1236, 4, 43, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1237, 4, 44, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1238, 4, 45, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1239, 4, 46, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1240, 4, 47, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1241, 4, 48, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1242, 4, 49, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1243, 4, 49, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1244, 4, 49, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1245, 4, 49, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1246, 4, 49, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1247, 4, 49, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1248, 4, 49, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1249, 4, 50, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1250, 4, 50, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1251, 4, 50, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1252, 4, 50, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1253, 4, 50, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1254, 4, 51, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1255, 4, 51, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1256, 4, 51, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1257, 4, 51, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1258, 4, 51, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1259, 4, 52, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1260, 4, 52, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1261, 4, 52, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1262, 4, 52, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1263, 4, 53, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1264, 4, 53, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1265, 4, 53, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1266, 4, 53, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1267, 4, 54, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1268, 4, 54, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1269, 4, 55, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1270, 4, 55, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1271, 4, 56, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1272, 4, 56, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1273, 4, 57, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1274, 4, 58, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1275, 4, 59, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1276, 4, 60, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1277, 4, 61, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1278, 4, 62, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1279, 4, 63, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1280, 4, 64, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1281, 4, 65, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1282, 4, 65, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1283, 4, 65, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1284, 4, 65, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1285, 4, 65, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1286, 4, 66, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1287, 4, 66, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1288, 4, 66, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1289, 4, 66, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1290, 4, 66, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1291, 4, 67, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1292, 4, 67, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1293, 4, 67, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1294, 4, 67, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1295, 4, 68, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1296, 4, 68, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1297, 4, 68, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1298, 4, 68, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1299, 4, 69, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1300, 4, 69, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1301, 4, 69, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1302, 4, 69, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1303, 4, 70, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1304, 4, 70, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1305, 4, 71, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1306, 4, 71, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1307, 4, 72, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1308, 4, 72, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1309, 4, 73, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1310, 4, 74, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1311, 4, 75, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1312, 4, 76, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1313, 4, 77, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1314, 4, 78, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1315, 4, 79, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1316, 4, 80, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1317, 4, 81, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1318, 4, 81, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1319, 4, 81, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1320, 4, 81, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1321, 4, 81, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1322, 4, 82, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1323, 4, 82, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1324, 4, 82, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1325, 4, 82, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1326, 4, 82, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1327, 4, 83, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1328, 4, 83, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1329, 4, 83, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1330, 4, 83, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1331, 4, 84, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1332, 4, 84, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1333, 4, 84, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1334, 4, 84, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1335, 4, 85, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1336, 4, 85, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1337, 4, 85, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1338, 4, 85, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1339, 4, 86, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1340, 4, 86, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1341, 4, 87, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1342, 4, 87, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1343, 4, 88, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1344, 4, 88, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1345, 4, 89, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1346, 4, 90, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1347, 4, 91, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1348, 4, 92, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1349, 4, 93, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1350, 4, 94, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1351, 4, 95, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1352, 4, 96, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1353, 4, 97, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1354, 4, 97, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1355, 4, 97, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1356, 4, 97, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1357, 4, 97, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1358, 4, 98, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1359, 4, 98, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1360, 4, 98, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1361, 4, 98, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1362, 4, 98, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1363, 4, 99, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1364, 4, 99, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1365, 4, 99, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1366, 4, 99, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1367, 4, 100, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1368, 4, 100, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1369, 4, 100, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1370, 4, 100, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1371, 4, 101, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1372, 4, 101, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1373, 4, 101, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1374, 4, 101, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1375, 4, 102, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1376, 4, 102, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1377, 4, 103, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1378, 4, 103, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1379, 4, 104, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1380, 4, 104, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1381, 4, 105, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1382, 4, 106, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1383, 4, 107, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1384, 4, 108, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1385, 4, 109, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1386, 4, 110, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1387, 4, 111, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1388, 4, 112, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1389, 4, 113, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1390, 4, 113, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1391, 4, 113, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1392, 4, 113, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1393, 4, 113, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1394, 4, 114, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1395, 4, 114, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1396, 4, 114, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1397, 4, 114, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1398, 4, 114, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1399, 4, 115, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1400, 4, 115, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1401, 4, 115, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1402, 4, 115, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1403, 4, 116, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1404, 4, 116, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1405, 4, 116, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1406, 4, 116, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1407, 4, 117, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1408, 4, 117, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1409, 4, 117, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1410, 4, 117, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1411, 4, 118, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1412, 4, 118, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1413, 4, 119, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1414, 4, 119, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1415, 4, 120, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1416, 4, 120, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1417, 4, 121, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1418, 4, 122, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1419, 4, 123, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1420, 4, 124, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1421, 4, 125, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1422, 4, 126, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1423, 4, 127, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1424, 4, 128, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1425, 4, 128, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1426, 4, 128, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1427, 4, 128, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1428, 4, 128, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1429, 4, 128, 1, NULL, NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1430, 4, 128, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1431, 4, 129, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1432, 4, 129, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1433, 4, 129, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1434, 4, 129, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1435, 4, 129, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1436, 4, 130, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1437, 4, 130, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1438, 4, 130, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1439, 4, 130, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1440, 4, 130, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1441, 4, 131, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1442, 4, 131, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1443, 4, 131, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1444, 4, 131, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1445, 4, 132, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1446, 4, 132, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1447, 4, 132, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1448, 4, 132, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1449, 4, 133, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1450, 4, 133, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1451, 4, 134, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1452, 4, 134, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1453, 4, 135, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1454, 4, 135, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1455, 4, 136, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1456, 4, 137, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1457, 4, 138, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1458, 4, 139, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1459, 4, 140, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1460, 4, 141, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1461, 4, 142, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1462, 4, 143, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1463, 4, 143, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1464, 4, 143, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1465, 4, 143, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1466, 4, 143, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1467, 4, 144, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1468, 4, 144, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1469, 4, 144, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1470, 4, 144, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1471, 4, 144, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1472, 4, 145, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1473, 4, 145, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1474, 4, 145, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1475, 4, 145, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1476, 4, 146, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1477, 4, 146, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1478, 4, 146, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1479, 4, 146, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1480, 4, 147, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1481, 4, 147, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1482, 4, 147, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1483, 4, 147, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1484, 4, 148, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1485, 4, 148, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1486, 4, 149, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1487, 4, 149, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1488, 4, 150, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1489, 4, 150, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1490, 4, 151, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1491, 4, 151, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1492, 4, 152, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1493, 4, 153, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1494, 4, 154, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1495, 4, 155, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1496, 4, 156, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1497, 4, 157, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1498, 4, 158, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1499, 4, 158, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1500, 4, 158, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1501, 4, 158, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1502, 4, 158, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1503, 4, 159, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1504, 4, 159, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1505, 4, 159, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1506, 4, 159, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1507, 4, 159, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1508, 4, 160, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1509, 4, 160, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1510, 4, 160, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1511, 4, 161, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1512, 4, 161, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1513, 4, 161, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1514, 4, 162, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1515, 4, 162, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1516, 4, 163, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1517, 4, 163, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1518, 4, 164, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1519, 4, 164, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1520, 4, 165, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1521, 4, 165, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1522, 4, 166, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1523, 4, 166, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1524, 4, 167, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1525, 4, 168, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1526, 4, 169, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1527, 4, 170, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1528, 4, 171, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1529, 4, 172, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1530, 4, 173, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1531, 4, 174, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1532, 4, 175, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1533, 4, 176, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1534, 4, 177, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1535, 4, 177, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1536, 4, 177, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1537, 4, 177, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1538, 4, 177, 1, NULL, NULL, NULL, NULL, NULL, 5, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1539, 4, 178, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1540, 4, 178, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1541, 4, 178, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1542, 4, 179, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1543, 4, 179, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1544, 4, 179, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1545, 4, 180, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1546, 4, 180, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1547, 4, 180, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1548, 4, 181, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1549, 4, 181, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1550, 4, 181, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1551, 4, 182, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1552, 4, 182, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1553, 4, 182, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1554, 4, 183, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1555, 4, 183, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1556, 4, 183, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1557, 4, 184, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1558, 4, 184, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1559, 4, 185, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1560, 4, 185, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1561, 4, 186, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1562, 4, 187, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1563, 4, 188, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1564, 4, 189, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1565, 4, 190, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1566, 4, 191, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1567, 4, 192, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1568, 4, 193, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1569, 4, 194, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, '2026-01-12 08:08:41', '2026-01-12 08:08:41'),
(1570, 5, 186, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1571, 5, 177, 1, NULL, NULL, NULL, 2, 1, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1572, 5, 192, 1, NULL, NULL, NULL, 3, 1, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1573, 5, 194, 1, NULL, NULL, NULL, 4, 1, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1574, 5, 178, 1, NULL, NULL, NULL, 5, 1, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1575, 5, 178, 1, NULL, NULL, NULL, 6, 1, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1576, 5, 185, 1, NULL, NULL, NULL, 7, 1, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1577, 5, 193, 1, NULL, NULL, NULL, 8, 1, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1578, 5, 180, 1, NULL, NULL, NULL, 1, 2, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1579, 5, 185, 1, NULL, NULL, NULL, 2, 2, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1580, 5, 194, 1, NULL, NULL, NULL, 3, 2, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1581, 5, 179, 1, NULL, NULL, NULL, 4, 2, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1582, 5, 189, 1, NULL, NULL, NULL, 5, 2, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1583, 5, 188, 1, NULL, NULL, NULL, 6, 2, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47');
INSERT INTO `schedules` (`id`, `copy_id`, `cst_id`, `school_id`, `teacher_substitute_id`, `co_teacher_id`, `co_subject_id`, `period_number`, `day_number`, `period_order`, `place`, `active`, `notes`, `created_at`, `updated_at`) VALUES
(1584, 5, 193, 1, NULL, NULL, NULL, 7, 2, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1585, 5, 187, 1, NULL, NULL, NULL, 8, 2, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1586, 5, 180, 1, NULL, NULL, NULL, 1, 3, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1587, 5, 185, 1, NULL, NULL, NULL, 2, 3, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1588, 5, 187, 1, NULL, NULL, NULL, 3, 3, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1589, 5, 178, 1, NULL, NULL, NULL, 4, 3, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1590, 5, 184, 1, NULL, NULL, NULL, 5, 3, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1591, 5, 183, 1, NULL, NULL, NULL, 6, 3, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1592, 5, 190, 1, NULL, NULL, NULL, 7, 3, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1593, 5, 180, 1, NULL, NULL, NULL, 8, 3, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1594, 5, 177, 1, NULL, NULL, NULL, 1, 4, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1595, 5, 188, 1, NULL, NULL, NULL, 2, 4, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1596, 5, 191, 1, NULL, NULL, NULL, 3, 4, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1597, 5, 190, 1, NULL, NULL, NULL, 4, 4, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1598, 5, 189, 1, NULL, NULL, NULL, 5, 4, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1599, 5, 182, 1, NULL, NULL, NULL, 6, 4, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1600, 5, 190, 1, NULL, NULL, NULL, 7, 4, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1601, 5, 189, 1, NULL, NULL, NULL, 8, 4, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1602, 5, 180, 1, NULL, NULL, NULL, 1, 5, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1603, 5, 187, 1, NULL, NULL, NULL, 2, 5, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1604, 5, 183, 1, NULL, NULL, NULL, 3, 5, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1605, 5, 184, 1, NULL, NULL, NULL, 4, 5, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1606, 5, 192, 1, NULL, NULL, NULL, 5, 5, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1607, 5, 191, 1, NULL, NULL, NULL, 6, 5, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1608, 5, 179, 1, NULL, NULL, NULL, 7, 5, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47'),
(1609, 5, 179, 1, NULL, NULL, NULL, 8, 5, NULL, NULL, 1, NULL, '2026-01-12 08:27:47', '2026-01-12 08:27:47');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_copies`
--

CREATE TABLE `schedule_copies` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copy_date` date DEFAULT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `semester_id` bigint UNSIGNED DEFAULT NULL,
  `week_number` tinyint DEFAULT NULL,
  `status` enum('draft','pending','active','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `activated_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL COMMENT 'Additional configurable data',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `last_modified_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedule_copies`
--

INSERT INTO `schedule_copies` (`id`, `school_id`, `name`, `description`, `copy_date`, `academic_year_id`, `semester_id`, `week_number`, `status`, `activated_at`, `metadata`, `notes`, `created_by`, `last_modified_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 1, 'Schedule Main Copy', NULL, '2026-01-03', 1, 1, 1, 'draft', NULL, NULL, NULL, 2, 19, '2026-01-03 02:59:54', '2026-01-12 08:08:41', NULL),
(5, 1, 'Schedule Copy2', NULL, '2026-01-12', 1, 1, 1, 'active', NULL, NULL, NULL, 19, 19, '2026-01-12 08:07:27', '2026-01-12 08:08:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schedule_dailies`
--

CREATE TABLE `schedule_dailies` (
  `id` bigint UNSIGNED NOT NULL,
  `schedule_id` bigint UNSIGNED NOT NULL,
  `teacher_substitute_id` bigint UNSIGNED DEFAULT NULL,
  `schedule_copy_id` bigint UNSIGNED NOT NULL,
  `day` tinyint UNSIGNED NOT NULL COMMENT '1-5: Sunday to Thursday',
  `week` tinyint UNSIGNED NOT NULL COMMENT '1-52: Week number in year',
  `semester` tinyint UNSIGNED NOT NULL COMMENT '1-2: First or Second semester',
  `date` date NOT NULL,
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_daily_records`
--

CREATE TABLE `schedule_daily_records` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_timings`
--

CREATE TABLE `schedule_timings` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `options` json DEFAULT NULL,
  `timing` json DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint UNSIGNED NOT NULL,
  `h_r_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `academic_year_id` bigint UNSIGNED DEFAULT NULL,
  `semester_id` bigint UNSIGNED DEFAULT NULL,
  `schedule_copy_id` bigint UNSIGNED DEFAULT NULL,
  `data` json DEFAULT NULL,
  `weekly_plan_settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `h_r_id`, `name`, `name_ar`, `section`, `section_ar`, `is_active`, `academic_year_id`, `semester_id`, `schedule_copy_id`, `data`, `weekly_plan_settings`, `created_at`, `updated_at`) VALUES
(1, 1, 'MSC ', 'msc ar', NULL, NULL, 1, 1, 1, 5, NULL, NULL, NULL, '2026-01-12 08:08:10');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester_number` tinyint NOT NULL,
  `total_weeks` tinyint DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `data` json DEFAULT NULL,
  `active` tinyint NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `semester_number`, `total_weeks`, `start_date`, `end_date`, `academic_year_id`, `school_id`, `data`, `active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Semester 1', 1, NULL, '2025-08-24', '2026-01-17', 1, 1, NULL, 1, NULL, '2026-01-03 01:30:12', '2026-01-11 14:11:26'),
(2, 'Semester 2', 2, NULL, '2026-01-18', '2026-06-20', 1, 1, NULL, 0, NULL, '2026-01-03 01:30:12', '2026-01-11 14:24:34'),
(3, 'Semester 3', 3, NULL, NULL, NULL, 1, 1, NULL, 0, '2026-01-03 05:42:37', '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(4, 'Semester 4', 4, NULL, NULL, NULL, 1, 1, NULL, 0, '2026-01-03 05:42:44', '2026-01-03 01:30:12', '2026-01-03 01:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `semester_tests`
--

CREATE TABLE `semester_tests` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester_number` tinyint NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `data` json DEFAULT NULL,
  `active` tinyint NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('8cI1BshRDpTEzGHSAEUKeegSs8AEvyawVVA5uomG', 19, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiYzV1dVdEc2xiRVU2RHBudGR0QkpldVczdW9mNU41WWZ4Mllubkt6QiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcXUvc3R1ZGVudC9leGFtcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE5O3M6MjE6InBhc3N3b3JkX2hhc2hfc2FuY3R1bSI7czo2MDoiJDJ5JDEyJEtwMUVFRkZwTzdGU0lCRFhJRlBUd2VGczNoTUh0SVY0L3ZjZ3piOGtuemZjR1F4a3poT29DIjt9', 1768325023),
('L6kCfCaOhXZQxL5LPuw7kYALCTJ47UKluzLmUXNF', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTUo5N3NaVk9Fak01Y2ZENGNJbG1rUmRGcmVsYWw1NlNqaUxtQU43ciI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo4NzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3F1L3F1ZXN0aW9ucz9ibG9vbV9sZXZlbD0mZGlmZmljdWx0eT0mcXVlc3Rpb25fdHlwZT0mc3ViamVjdF9pZD0xIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768320768);

-- --------------------------------------------------------

--
-- Table structure for table `stages`
--

CREATE TABLE `stages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `school_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stages`
--

INSERT INTO `stages` (`id`, `name`, `name_ar`, `description`, `school_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Primary', NULL, NULL, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(2, 'Intermediate', NULL, NULL, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL),
(3, 'Secondary', NULL, NULL, 1, '2026-01-03 01:30:12', '2026-01-03 01:30:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint UNSIGNED NOT NULL,
  `s_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_cute` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `data` json DEFAULT NULL,
  `stage_id` bigint UNSIGNED NOT NULL,
  `grade_id` bigint UNSIGNED NOT NULL,
  `classroom_id` bigint UNSIGNED NOT NULL,
  `classroom_history` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `s_id`, `name`, `name_ar`, `name_cute`, `avatar`, `order_1`, `order_2`, `notes`, `user_id`, `parent_id`, `school_id`, `data`, `stage_id`, `grade_id`, `classroom_id`, `classroom_history`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'pxmiq59123', 'Ibrahim Firas Fouad Awad Abdeljawad', 'إبراهيم', 'Hima', NULL, NULL, NULL, '', 588, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:02', '2026-01-12 10:42:02'),
(2, 'prhjk86462', 'Ahmed Abdulaziz Khalil Abdulaziz Naseer', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 589, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:02', '2026-01-12 10:42:02'),
(3, 'peglw60327', 'Adam Islam Salah Salem Mokhtar Abu Amoud', 'آدم', 'Adoumi', NULL, NULL, NULL, '', 590, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:03', '2026-01-12 10:42:03'),
(4, 'ptfpi91901', 'Asser Ahmed Abd El-Moaty Abd El-Moneim', 'آسر', 'Aroura', NULL, NULL, NULL, '', 591, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:03', '2026-01-12 10:42:03'),
(5, 'plgaj80574', 'Thamer Sultan Saad Abdullah Al-Omari', 'ثامر', 'Thoum', NULL, NULL, NULL, '', 592, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:03', '2026-01-12 10:42:03'),
(6, 'pno4h68386', 'Hamza Sharif Salah Al-Sayed Jabr', 'حمزة', 'Hamzo', NULL, NULL, NULL, '', 593, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:03', '2026-01-12 10:42:03'),
(7, 'pj9qe81503', 'Khaled Thamer Khaled Abdulaziz Bin Najifan', 'خالد', 'Khloud', NULL, NULL, NULL, '', 594, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:03', '2026-01-12 10:42:03'),
(8, 'p0h3v73923', 'Rayan Jamal Mohamed Taysir Haykal', 'ريان', 'Ryno', NULL, NULL, NULL, '', 595, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:04', '2026-01-12 10:42:04'),
(9, 'prkqd45760', 'Rayan Firas Nizar Daaboul', 'ريان', 'Ryno', NULL, NULL, NULL, '', 596, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:04', '2026-01-12 10:42:04'),
(10, 'p6ald95633', 'Rayan Ridwan Mohamedwali Mohamed Othman Aziz Khan', 'ريان', 'Ryno', NULL, NULL, NULL, '', 597, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:04', '2026-01-12 10:42:04'),
(11, 'peg8i39834', 'Salim Moamen Ayesh Salim Jaradah', '', '', NULL, NULL, NULL, '', 598, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:04', '2026-01-12 10:42:04'),
(12, 'pfkyr12043', 'Suleiman Abdullah Suleiman Mohamed Al-Omari', '', '', NULL, NULL, NULL, '', 599, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:05', '2026-01-12 10:42:05'),
(13, 'pzqrd91463', 'Samher Mohamed Bashar Hafiz Nazir Al-Ankashari', '', '', NULL, NULL, NULL, '', 600, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:05', '2026-01-12 10:42:05'),
(14, 'pztb086004', 'Sohaib Saber Abdelmagsoud Tawfiq', '', '', NULL, NULL, NULL, '', 601, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:05', '2026-01-12 10:42:05'),
(15, 'pbvek47860', 'Abdulaziz Shady Mahmoud Ahmed Al-Masri', '', '', NULL, NULL, NULL, '', 602, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:05', '2026-01-12 10:42:05'),
(16, 'pgq3i47752', 'Adnan Reda Waddah Mohamed Al-Khamash', '', '', NULL, NULL, NULL, '', 603, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:05', '2026-01-12 10:42:05'),
(17, 'pnpig93393', 'Odai Mohannad Mahmoud Mohamed Rajab', '', '', NULL, NULL, NULL, '', 604, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:06', '2026-01-12 10:42:06'),
(18, 'peqfq44669', 'Ali Ahmed Waheed Hassan Ali', 'علي', 'Aloush', NULL, NULL, NULL, '', 605, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:06', '2026-01-12 10:42:06'),
(19, 'pms5c26157', 'Ammar Diyaa Adel Hassan Ayourzeza', '', '', NULL, NULL, NULL, '', 606, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:06', '2026-01-12 10:42:06'),
(20, 'prvve62164', 'Omar Sultan Adnan Abdulrahim Akbar', 'عمر', 'Amour', NULL, NULL, NULL, '', 607, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:06', '2026-01-12 10:42:06'),
(21, 'phvkg81837', 'Mohamed Hassan Asheq Abdullah Hassan Al-Mohamad', '', '', NULL, NULL, NULL, '', 608, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:07', '2026-01-12 10:42:07'),
(22, 'pqgpo63065', 'Yusuf Ahmed Mohamed Al-Nazzawi Al-Jahni', '', '', NULL, NULL, NULL, '', 609, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:07', '2026-01-12 10:42:07'),
(23, 'pdqha80039', 'Yusuf Ibrahim Adel Mohamed Al-Maghazi', '', '', NULL, NULL, NULL, '', 610, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:07', '2026-01-12 10:42:07'),
(24, 'pgqb898748', 'Turki Ahmed abdullah Rabiea', '', '', NULL, NULL, NULL, '', 611, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:07', '2026-01-12 10:42:07'),
(25, 'pjyvk78782', 'Yamen Sultan Aref Mustafa Al-Dweikat', '', '', NULL, NULL, NULL, '', 612, NULL, 1, NULL, 1, 4, 1, NULL, NULL, '2026-01-12 10:42:07', '2026-01-12 10:42:07'),
(26, 'pbtxz31108', 'Ibrahim Sultan Ibrahim Khalil Rawas', 'إبراهيم', 'Hima', NULL, NULL, NULL, '', 613, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:08', '2026-01-12 10:42:08'),
(27, 'pp98r43451', 'Elias Tarek Jubeir Al-Qurashi', '', '', NULL, NULL, NULL, '', 614, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:08', '2026-01-12 10:42:08'),
(28, 'pnf6b11927', 'Ameer Hani Saeed Suleiman Dahdoolan', '', '', NULL, NULL, NULL, '', 615, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:08', '2026-01-12 10:42:08'),
(29, 'pbh0283973', 'Saeed Mohamed Saeed Fawaz Al-Harbi', '', '', NULL, NULL, NULL, '', 616, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:08', '2026-01-12 10:42:08'),
(30, 'pyycb50394', 'Saif Bassam Salamah Ateeq Allah Al-Harbi', '', '', NULL, NULL, NULL, '', 617, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:08', '2026-01-12 10:42:08'),
(31, 'pb7v934497', 'Omar Sharif Abdelfattah Al-Mutawali Abd Al-Razek', 'عمر', 'Amour', NULL, NULL, NULL, '', 618, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:09', '2026-01-12 10:42:09'),
(32, 'pffed28424', 'Omar Shady Jabr Shaaban Salem', 'عمر', 'Amour', NULL, NULL, NULL, '', 619, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:09', '2026-01-12 10:42:09'),
(33, 'p4kzs30180', 'Omar Mohamed Abdo Mohamed Saleh', 'عمر', 'Amour', NULL, NULL, NULL, '', 620, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:09', '2026-01-12 10:42:09'),
(34, 'pagfd31105', 'Omar Ayman Ahmed Abdulrahman Al-Shazly', 'عمر', 'Amour', NULL, NULL, NULL, '', 621, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:09', '2026-01-12 10:42:09'),
(35, 'p4vxr99648', 'Omar Mohamed Omar Mohamed Balbeed', 'عمر', 'Amour', NULL, NULL, NULL, '', 622, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:10', '2026-01-12 10:42:10'),
(36, 'pmbpl55107', 'Gaith Emad Abbas Hassan Ghandoura', '', '', NULL, NULL, NULL, '', 623, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:10', '2026-01-12 10:42:10'),
(37, 'p2qfs53246', 'Fahd Tarek Jubeir Al-Qurashi', '', '', NULL, NULL, NULL, '', 624, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:10', '2026-01-12 10:42:10'),
(38, 'p3jnm10130', 'Kanan Raed Mohamed Youssef Sabah', '', '', NULL, NULL, NULL, '', 625, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:10', '2026-01-12 10:42:10'),
(39, 'pmuhy46737', 'Mazen Sayed Mohamed Sayed Ali', '', '', NULL, NULL, NULL, '', 626, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:10', '2026-01-12 10:42:10'),
(40, 'pkvif36170', 'Malek Mohamed Fikry Abdel Majeed Hassan', 'مالك', 'Mlouki', NULL, NULL, NULL, '', 627, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:11', '2026-01-12 10:42:11'),
(41, 'pzcff38143', 'Mohamed Ehab Abdelfattah Ali Ghazi', '', '', NULL, NULL, NULL, '', 628, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:11', '2026-01-12 10:42:11'),
(42, 'piteb39764', 'Mohamed Ibrahim Alam Bah Bah', '', '', NULL, NULL, NULL, '', 629, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:11', '2026-01-12 10:42:11'),
(43, 'pteg997894', 'Mahdi Mohamed Mahdi Haidar Hassan', '', '', NULL, NULL, NULL, '', 630, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:11', '2026-01-12 10:42:11'),
(44, 'peupo18461', 'Yaseen Mohamed Hussein Mohamed Ghazali', '', '', NULL, NULL, NULL, '', 631, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:12', '2026-01-12 10:42:12'),
(45, 'pkp4a87759', 'Yazan Ahmed Fahmi Zaki Al-Banna', '', '', NULL, NULL, NULL, '', 632, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:12', '2026-01-12 10:42:12'),
(46, 'p1x7z25492', 'Yusuf Majed Abdullah Mohamed Badghish', '', '', NULL, NULL, NULL, '', 633, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:12', '2026-01-12 10:42:12'),
(47, 'pb1gm79765', 'Yaseen Mohamed Najm AlDin', '', '', NULL, NULL, NULL, '', 634, NULL, 1, NULL, 1, 4, 2, NULL, NULL, '2026-01-12 10:42:12', '2026-01-12 10:42:12'),
(48, 'pweao85942', 'Ibrahim Hazem Ibrahim Hazem Al-Ghanem', 'إبراهيم', 'Hima', NULL, NULL, NULL, '', 635, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:12', '2026-01-12 10:42:12'),
(49, 'psvyg84718', 'Anas Youssef Ahmed Salah El-Din Al-Atbani', '', '', NULL, NULL, NULL, '', 636, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:13', '2026-01-12 10:42:13'),
(50, 'pefq053828', 'Jad Hetan Jamal Abdullah Baqis', '', '', NULL, NULL, NULL, '', 637, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:13', '2026-01-12 10:42:13'),
(51, 'pfhj274421', 'Jaafar Abdullah Jaafar bin Mahfouz', '', '', NULL, NULL, NULL, '', 638, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:13', '2026-01-12 10:42:13'),
(52, 'pwgut32877', 'Reda Moamen Ayesh Salim Jaradah', '', '', NULL, NULL, NULL, '', 639, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:13', '2026-01-12 10:42:13'),
(53, 'pn7xn98433', 'Saif Mohammed Abdullah Salem Al-Amoudi', '', '', NULL, NULL, NULL, '', 640, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:13', '2026-01-12 10:42:13'),
(54, 'pgb4i93101', 'Abdulaziz Ghazi Ghaleb Al-Otaibi', '', '', NULL, NULL, NULL, '', 641, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:14', '2026-01-12 10:42:14'),
(55, 'psbfb66839', 'Abdullah Mazen Abdelkader Al-Amoudi', '', '', NULL, NULL, NULL, '', 642, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:14', '2026-01-12 10:42:14'),
(56, 'pec9r79353', 'Ali Khaled Mohammed Saleh Al-Malki', 'علي', 'Aloush', NULL, NULL, NULL, '', 643, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:14', '2026-01-12 10:42:14'),
(57, 'pvoyy99115', 'Omar Hussein Ahmed Otair', 'عمر', 'Amour', NULL, NULL, NULL, '', 644, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:14', '2026-01-12 10:42:14'),
(58, 'pms4a24582', 'Omar Mohammed Abdul-Haq Mohammed Hanif', 'عمر', 'Amour', NULL, NULL, NULL, '', 645, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:15', '2026-01-12 10:42:15'),
(59, 'pkmdh39477', 'Ghassan Maher Reda Badawi', '', '', NULL, NULL, NULL, '', 646, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:15', '2026-01-12 10:42:15'),
(60, 'pivjg94924', 'Karam Ahmed Mohamed Hamed El-Sharshaby', '', '', NULL, NULL, NULL, '', 647, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:15', '2026-01-12 10:42:15'),
(61, 'ptcnb49596', 'Mohamed Ahmed El-Sayed Ahmed El-Baz', '', '', NULL, NULL, NULL, '', 648, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:15', '2026-01-12 10:42:15'),
(62, 'pqf9t81374', 'Mohamed Ihab Mohamed Mamdouh Ahmed Amer', '', '', NULL, NULL, NULL, '', 649, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:15', '2026-01-12 10:42:15'),
(63, 'pavhk68872', 'Mohammed Sultan Adnan Abdulrahim Akbar', 'محمد', 'Hamoudi', NULL, NULL, NULL, '', 650, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:16', '2026-01-12 10:42:16'),
(64, 'pcpjj74977', 'Mahmoud Sayed Mahmoud Mostafa El-Zoghbi', '', '', NULL, NULL, NULL, '', 651, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:16', '2026-01-12 10:42:16'),
(65, 'p6swc55677', 'Muath Sultan Ali Al-Zahrani', '', '', NULL, NULL, NULL, '', 652, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:16', '2026-01-12 10:42:16'),
(66, 'p6zlw56373', 'Musaab Abdul-Rahman Musaab Sabr', '', '', NULL, NULL, NULL, '', 653, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:16', '2026-01-12 10:42:16'),
(67, 'pzxx743644', 'Nasser Maher Nasser Ahmed Mahdi', '', '', NULL, NULL, NULL, '', 654, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:16', '2026-01-12 10:42:16'),
(68, 'ppt8294162', 'Nawaf Ahmed Fouad Ali bin Mahfouz', '', '', NULL, NULL, NULL, '', 655, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:17', '2026-01-12 10:42:17'),
(69, 'pmmdf97531', 'Noah Abdullah Hussein Al-Attas', '', '', NULL, NULL, NULL, '', 656, NULL, 1, NULL, 1, 5, 4, NULL, NULL, '2026-01-12 10:42:17', '2026-01-12 10:42:17'),
(70, 'pwfur14025', 'Ibrahim Reda Waddah Mohamed Al-Khamash', 'إبراهيم', 'Hima', NULL, NULL, NULL, '', 657, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:17', '2026-01-12 10:42:17'),
(71, 'pgb8z60742', 'Ahmed El-Said Ibrahim Ali El-Hady', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 658, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:17', '2026-01-12 10:42:17'),
(72, 'padc567644', 'Adam Abdulaziz Shami Mohamed', 'آدم', 'Adoumi', NULL, NULL, NULL, '', 659, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:18', '2026-01-12 10:42:18'),
(73, 'pkvni28156', 'Adam Omar Hussein Ibrahim Al-Shafi\'i', 'آدم', 'Adoumi', NULL, NULL, NULL, '', 660, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:18', '2026-01-12 10:42:18'),
(74, 'pou3z91178', 'Amin Mohamed Ibrahim Omar Khidr', '', '', NULL, NULL, NULL, '', 661, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:18', '2026-01-12 10:42:18'),
(75, 'p5wot43217', 'Anas Reda Fouad Al-Sayed Al-Shorbagy', '', '', NULL, NULL, NULL, '', 662, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:18', '2026-01-12 10:42:18'),
(76, 'p3upq87335', 'Bassam Ibrahim Jamil bin Hamad', '', '', NULL, NULL, NULL, '', 663, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:18', '2026-01-12 10:42:18'),
(77, 'pe9wl97613', 'Hassan Bassam Hassan Abu Ali', '', '', NULL, NULL, NULL, '', 664, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:19', '2026-01-12 10:42:19'),
(78, 'pckvd43657', 'Hamza Osama Shawky Fawzy Ahmed', 'حمزة', 'Hamzo', NULL, NULL, NULL, '', 665, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:19', '2026-01-12 10:42:19'),
(79, 'p5fcn86500', 'Khalid Basem Khalid bin Mahfouz', 'خالد', 'Khloud', NULL, NULL, NULL, '', 666, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:19', '2026-01-12 10:42:19'),
(80, 'pyfqt57803', 'Rayan Ibrahim Suleiman bin Abdullah Al-Omari', 'ريان', 'Ryno', NULL, NULL, NULL, '', 667, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:19', '2026-01-12 10:42:19'),
(81, 'pmfqu58971', 'Rayan Ahmed Mohamed Ibrahim Abualainin', 'ريان', 'Ryno', NULL, NULL, NULL, '', 668, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:20', '2026-01-12 10:42:20'),
(82, 'pqynu59082', 'Rayan Majed Abdullah Mohamed Badghish', 'ريان', 'Ryno', NULL, NULL, NULL, '', 669, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:20', '2026-01-12 10:42:20'),
(83, 'pkv1r65906', 'Ziyad Abdul-Rahman Mohammed Sadiq Ghalib', '', '', NULL, NULL, NULL, '', 670, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:20', '2026-01-12 10:42:20'),
(84, 'ppvze28058', 'Saud Ahmed Abdullah bin Rabiea', '', '', NULL, NULL, NULL, '', 671, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:20', '2026-01-12 10:42:20'),
(85, 'ptfx988893', 'Sultan Naif Sultan Al-Harbi', '', '', NULL, NULL, NULL, '', 672, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:20', '2026-01-12 10:42:20'),
(86, 'p8c5r18591', 'Suleiman Abdulaziz Suleiman Al-Hammad', '', '', NULL, NULL, NULL, '', 673, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:21', '2026-01-12 10:42:21'),
(87, 'pkdbr70877', 'Tariq Abdul Rahman Mahfouz bin Mahfouz', '', '', NULL, NULL, NULL, '', 674, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:21', '2026-01-12 10:42:21'),
(88, 'pb89l32676', 'Abdul Karim Khalid Ali bin Mahfouz', '', '', NULL, NULL, NULL, '', 675, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:21', '2026-01-12 10:42:21'),
(89, 'pytdx82837', 'Abdullah Ali Abdullah Al-Amoudi', '', '', NULL, NULL, NULL, '', 676, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:21', '2026-01-12 10:42:21'),
(90, 'pzmwa77407', 'Ali Mohamed Mohamed Taysir Haykal', 'علي', 'Aloush', NULL, NULL, NULL, '', 677, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:21', '2026-01-12 10:42:21'),
(91, 'pstez39568', 'Omar Sultan Adnan Abdulrahim Akbar', 'عمر', 'Amour', NULL, NULL, NULL, '', 678, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:22', '2026-01-12 10:42:22'),
(92, 'pmeaa84655', 'Mohammed Fawzi Mohammed Al-Hadi', 'محمد', 'Hamoudi', NULL, NULL, NULL, '', 679, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:22', '2026-01-12 10:42:22'),
(93, 'ptdbf22043', 'Mahmoud Mohamed Faraj Suleiman Faraj', '', '', NULL, NULL, NULL, '', 680, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:22', '2026-01-12 10:42:22'),
(94, 'pymbz64877', 'Yazan Mohamed Bashar Hafiz Al-Ankashari', '', '', NULL, NULL, NULL, '', 681, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:22', '2026-01-12 10:42:22'),
(95, 'plkd379606', 'Yusuf Majed Saeed Ali Al-Amoudi', '', '', NULL, NULL, NULL, '', 682, NULL, 1, NULL, 1, 5, 5, NULL, NULL, '2026-01-12 10:42:23', '2026-01-12 10:42:23'),
(96, 'ph5ph74406', 'Ahmed Mohamed Ahmed El-Daly', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 683, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:23', '2026-01-12 10:42:23'),
(97, 'p6xg762825', 'Ismail Reda Fouad Al-Sayed Al-Shorbagy', '', '', NULL, NULL, NULL, '', 684, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:23', '2026-01-12 10:42:23'),
(98, 'ptrnd69275', 'Baraa Moamen Ayesh Salim Jaradah', '', '', NULL, NULL, NULL, '', 685, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:23', '2026-01-12 10:42:23'),
(99, 'px5oz19274', 'Tamim Mazen Ahmed Al-Amoudi', '', '', NULL, NULL, NULL, '', 686, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:23', '2026-01-12 10:42:23'),
(100, 'psqsi63412', 'Hamza Shady Mahmoud Ahmed Al-Masri', 'حمزة', 'Hamzo', NULL, NULL, NULL, '', 687, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:24', '2026-01-12 10:42:24'),
(101, 'proqq26474', 'Sultan Tarek Jabir Al-Qurashi', '', '', NULL, NULL, NULL, '', 688, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:24', '2026-01-12 10:42:24'),
(102, 'puyrj53286', 'Abdulaziz Ghassan Mohammed Al-Shehri', '', '', NULL, NULL, NULL, '', 689, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:24', '2026-01-12 10:42:24'),
(103, 'pofn051054', 'Abdulsalam Khaled Saad Saeed Al-Ghamdi', '', '', NULL, NULL, NULL, '', 690, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:24', '2026-01-12 10:42:24'),
(104, 'pptu918308', 'Abdullah Mazen Abdullah bin Mahfouz', '', '', NULL, NULL, NULL, '', 691, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:24', '2026-01-12 10:42:24'),
(105, 'pvust25350', 'Abdullah Mohammed Abdullah Salem Al-Amoudi', '', '', NULL, NULL, NULL, '', 692, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:25', '2026-01-12 10:42:25'),
(106, 'pdqfl34286', 'Ali Khaled Ali bin Mahfouz', 'علي', 'Aloush', NULL, NULL, NULL, '', 693, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:25', '2026-01-12 10:42:25'),
(107, 'pjxbo21627', 'Omar Abdul Rahman Mahfouz bin Mahfouz', 'عمر', 'Amour', NULL, NULL, NULL, '', 694, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:25', '2026-01-12 10:42:25'),
(108, 'pmnxz89666', 'Omar Mohammed Fouad Ali bin Mahfouz', 'عمر', 'Amour', NULL, NULL, NULL, '', 695, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:25', '2026-01-12 10:42:25'),
(109, 'p6wwc64739', 'Faisal Ahmed Sultan Abdo Al-Qurashi', '', '', NULL, NULL, NULL, '', 696, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:25', '2026-01-12 10:42:25'),
(110, 'pbbms75739', 'Majed Majed Saeed Ali Al-Amoudi', '', '', NULL, NULL, NULL, '', 697, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:26', '2026-01-12 10:42:26'),
(111, 'pr78s82564', 'Malek Ahmed El-Sayed Ahmed El-Baz', 'مالك', 'Mlouki', NULL, NULL, NULL, '', 698, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:26', '2026-01-12 10:42:26'),
(112, 'pn9hb91443', 'Mohamed Fawzi Mohamed El-Hadi', '', '', NULL, NULL, NULL, '', 699, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:26', '2026-01-12 10:42:26'),
(113, 'pyowx31754', 'Moataz Ammar Hussein Salem Baosman', '', '', NULL, NULL, NULL, '', 700, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:26', '2026-01-12 10:42:26'),
(114, 'panxj50964', 'Moath Mohammed Jamil Al-Saeed', '', '', NULL, NULL, NULL, '', 701, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:27', '2026-01-12 10:42:27'),
(115, 'pdwbo13711', 'Yahya Majed Abdullah Mohammed Badghish', '', '', NULL, NULL, NULL, '', 702, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:27', '2026-01-12 10:42:27'),
(116, 'pr1jn68473', 'Youssef Mohammed Ahmed Al-Zubayri', 'يوسف', 'Joe', NULL, NULL, NULL, '', 703, NULL, 1, NULL, 1, 6, 7, NULL, NULL, '2026-01-12 10:42:27', '2026-01-12 10:42:27'),
(117, 'pzbxb70383', 'Ibrahim Reda Waddah Mohamed Al-Khamash', 'إبراهيم', 'Hima', NULL, NULL, NULL, '', 704, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:27', '2026-01-12 10:42:27'),
(118, 'pdihl76756', 'Ahmed Bassam Hassan Abu Ali', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 705, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:28', '2026-01-12 10:42:28'),
(119, 'prodf79699', 'Ahmed Suleiman Abdullah Mohamed Al-Omari', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 706, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:28', '2026-01-12 10:42:28'),
(120, 'povcg88446', 'Adam Reda Fouad Al-Sayed Al-Shorbagy', 'آدم', 'Adoumi', NULL, NULL, NULL, '', 707, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:28', '2026-01-12 10:42:28'),
(121, 'pazft72588', 'Elias Tarek Jubeir Al-Qurashi', '', '', NULL, NULL, NULL, '', 708, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:28', '2026-01-12 10:42:28'),
(122, 'pydtq96392', 'Anas Ayman Ahmed Abdulrahman Al-Shazly', '', '', NULL, NULL, NULL, '', 709, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:28', '2026-01-12 10:42:28'),
(123, 'pbp5x21510', 'Anas Mohamed Ali Hussein Sharafuddin', '', '', NULL, NULL, NULL, '', 710, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:29', '2026-01-12 10:42:29'),
(124, 'phhds30485', 'Basel Ahmed Wahid Hassan Ali', '', '', NULL, NULL, NULL, '', 711, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:29', '2026-01-12 10:42:29'),
(125, 'pomeb56790', 'Khaled Fawzi Mohammed Al-Hadi', 'خالد', 'Khloud', NULL, NULL, NULL, '', 712, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:29', '2026-01-12 10:42:29'),
(126, 'p6vxc30306', 'Rayan Ahmed Abdullah bin Rabiea', 'ريان', 'Ryno', NULL, NULL, NULL, '', 713, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:29', '2026-01-12 10:42:29'),
(127, 'p2tlf20310', 'Rayan Khaled Mohammed Sadiq Ghalib', 'ريان', 'Ryno', NULL, NULL, NULL, '', 714, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:29', '2026-01-12 10:42:29'),
(128, 'pb81b47974', 'Rayan Mohamed Mohamed Taysir Haykal', 'ريان', 'Ryno', NULL, NULL, NULL, '', 715, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:30', '2026-01-12 10:42:30'),
(129, 'p5nz454968', 'Saeed Sultan Ibrahim Khalil Rawas', '', '', NULL, NULL, NULL, '', 716, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:30', '2026-01-12 10:42:30'),
(130, 'pjtpz77937', 'Sultan Shady Mahmoud Ahmed Al-Masri', '', '', NULL, NULL, NULL, '', 717, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:30', '2026-01-12 10:42:30'),
(131, 'plpwj74208', 'Sultan Mazen Ahmed Al-Amoudi', '', '', NULL, NULL, NULL, '', 718, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:30', '2026-01-12 10:42:30'),
(132, 'pagrj75471', 'Seif Mohamed Faraj Suleiman Faraj', '', '', NULL, NULL, NULL, '', 719, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:31', '2026-01-12 10:42:31'),
(133, 'pjcqt42617', 'Abdul Rahman Mahfouz bin Mahfouz', '', '', NULL, NULL, NULL, '', 720, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:31', '2026-01-12 10:42:31'),
(134, 'pxcob68966', 'Abdullah Ibrahim Jamil bin Hamad', '', '', NULL, NULL, NULL, '', 721, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:31', '2026-01-12 10:42:31'),
(135, 'p1xkj73712', 'Abdullah Shady Jabr Shaaban Salem', '', '', NULL, NULL, NULL, '', 722, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:31', '2026-01-12 10:42:31'),
(136, 'p1omk64052', 'Ali Reda Waddah Mohamed Al-Khamash', 'علي', 'Aloush', NULL, NULL, NULL, '', 723, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:31', '2026-01-12 10:42:31'),
(137, 'ppaxb55065', 'Ali Mazen Abdullah bin Mahfouz', 'علي', 'Aloush', NULL, NULL, NULL, '', 724, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:32', '2026-01-12 10:42:32'),
(138, 'pdnz776904', 'Omar Ahmed Ahmed Wahid Hassan Ali', 'عمر', 'Amour', NULL, NULL, NULL, '', 725, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:32', '2026-01-12 10:42:32'),
(139, 'p9b1w20395', 'Mohamed Ibrahim Mohamed Alam Bah Bah', '', '', NULL, NULL, NULL, '', 726, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:32', '2026-01-12 10:42:32'),
(140, 'pdllf33395', 'Mohamed Ahmed Mohamed Ibrahim Abualainin', '', '', NULL, NULL, NULL, '', 727, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:32', '2026-01-12 10:42:32'),
(141, 'pcmof15843', 'Mohamed Osama Shawky Fawzy Ahmed', '', '', NULL, NULL, NULL, '', 728, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:32', '2026-01-12 10:42:32'),
(142, 'p1v3k36247', 'Mohamed Ehab Mohamed Mamdouh Ahmed Amer', '', '', NULL, NULL, NULL, '', 729, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:33', '2026-01-12 10:42:33'),
(143, 'pabx130807', 'Moataz Saber Abdelmagsoud Tawfiq', '', '', NULL, NULL, NULL, '', 730, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:33', '2026-01-12 10:42:33'),
(144, 'pkckw61715', 'Yasin Ahmed Hamza Mohamed Suleiman', '', '', NULL, NULL, NULL, '', 731, NULL, 1, NULL, 1, 6, 8, NULL, NULL, '2026-01-12 10:42:33', '2026-01-12 10:42:33'),
(145, 'pksna73337', 'Ahmed Reda Waddah Mohamed Al-Khamash', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 732, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:33', '2026-01-12 10:42:33'),
(146, 'pma0060019', 'Ahmed Shady Jabr Shaaban Salem', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 733, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:34', '2026-01-12 10:42:34'),
(147, 'p61ju24920', 'Ahmed Amr Sabry El-Sayed Metwally', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 734, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:34', '2026-01-12 10:42:34'),
(148, 'pervi68971', 'Ahmed Mohammed Jamil Al-Saeed', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 735, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:34', '2026-01-12 10:42:34'),
(149, 'pa7qa88281', 'Adam Mohamed Ihab Mohamed Mamdouh Ahmed Amer', 'آدم', 'Adoumi', NULL, NULL, NULL, '', 736, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:34', '2026-01-12 10:42:34'),
(150, 'pyrur41986', 'Ibrahim Amr Mohamed Hussein Al-Ghazali', 'إبراهيم', 'Hima', NULL, NULL, NULL, '', 737, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:34', '2026-01-12 10:42:34'),
(151, 'p1ual52572', 'Anas Ibrahim Jamil bin Hamad', '', '', NULL, NULL, NULL, '', 738, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:35', '2026-01-12 10:42:35'),
(152, 'psw4i21802', 'Baraa Mohamed Ahmed El-Daly', '', '', NULL, NULL, NULL, '', 739, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:35', '2026-01-12 10:42:35'),
(153, 'pmjtu86206', 'Khaled Mazen Abdullah bin Mahfouz', 'خالد', 'Khloud', NULL, NULL, NULL, '', 740, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:35', '2026-01-12 10:42:35'),
(154, 'plmjn92422', 'Reda Moamen Ayesh Salim Jaradah', '', '', NULL, NULL, NULL, '', 741, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:35', '2026-01-12 10:42:35'),
(155, 'p90tc57404', 'Saud Sultan Adnan Abdulrahim Akbar', '', '', NULL, NULL, NULL, '', 742, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:35', '2026-01-12 10:42:35'),
(156, 'pwqvn81759', 'Sultan Ahmed Sultan Abdo Al-Qurashi', '', '', NULL, NULL, NULL, '', 743, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:36', '2026-01-12 10:42:36'),
(157, 'pryut86392', 'Salman Naif Sultan Al-Harbi', '', '', NULL, NULL, NULL, '', 744, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:36', '2026-01-12 10:42:36'),
(158, 'pdi1h77846', 'Abdul Rahman Khaled Ali bin Mahfouz', '', '', NULL, NULL, NULL, '', 745, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:36', '2026-01-12 10:42:36'),
(159, 'pymxg65997', 'Abdullah Mazen Ahmed Al-Amoudi', '', '', NULL, NULL, NULL, '', 746, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:36', '2026-01-12 10:42:36'),
(160, 'pwdgs83863', 'Ali Khaled Mohammed Saleh Al-Malki', 'علي', 'Aloush', NULL, NULL, NULL, '', 747, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:37', '2026-01-12 10:42:37'),
(161, 'p0rpv21697', 'Omar Reda Fouad Al-Sayed Al-Shorbagy', 'عمر', 'Amour', NULL, NULL, NULL, '', 748, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:37', '2026-01-12 10:42:37'),
(162, 'pshvn36530', 'Mohamed Ihab Abdelfattah Ali Ghazi', '', '', NULL, NULL, NULL, '', 749, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:37', '2026-01-12 10:42:37'),
(163, 'po1qw15929', 'Mohamed Basem Khalid bin Mahfouz', '', '', NULL, NULL, NULL, '', 750, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:37', '2026-01-12 10:42:37'),
(164, 'pgbta58635', 'Mohamed Shady Mahmoud Ahmed Al-Masri', '', '', NULL, NULL, NULL, '', 751, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:37', '2026-01-12 10:42:37'),
(165, 'pltqj41250', 'Moataz Mohamed Bashar Hafiz Al-Ankashari', '', '', NULL, NULL, NULL, '', 752, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:38', '2026-01-12 10:42:38'),
(166, 'pioua25779', 'Yahya Majed Saeed Ali Al-Amoudi', '', '', NULL, NULL, NULL, '', 753, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:38', '2026-01-12 10:42:38'),
(167, 'p2ayw90956', 'Yassin Ahmed Hamza Mohamed Suleiman', '', '', NULL, NULL, NULL, '', 754, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:38', '2026-01-12 10:42:38'),
(168, 'posof22393', 'Youssef Mohamed Hussein Mohamed Ghazali', 'يوسف', 'Joe', NULL, NULL, NULL, '', 755, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:38', '2026-01-12 10:42:38'),
(169, 'pz6jb16780', 'Youssef Hani Saeed Suleiman Dahdoolan', 'يوسف', 'Joe', NULL, NULL, NULL, '', 756, NULL, 1, NULL, 2, 7, 9, NULL, NULL, '2026-01-12 10:42:38', '2026-01-12 10:42:38'),
(170, 'pf5ly82414', 'Ahmed Ibrahim Ahmed Mohamed Khalil', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 757, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:39', '2026-01-12 10:42:39'),
(171, 'psvea41129', 'Ahmed Tarek Jubeir Al-Qurashi', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 758, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:39', '2026-01-12 10:42:39'),
(172, 'ptk7v63984', 'Ahmed Mohamed Ahmed Mohamed Al-Zubayri', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 759, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:39', '2026-01-12 10:42:39'),
(173, 'pma1r47738', 'Ahmed Majed Saeed Ali Al-Amoudi', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 760, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:39', '2026-01-12 10:42:39'),
(174, 'pmagd88565', 'Anas Osama Shawky Fawzy Ahmed', '', '', NULL, NULL, NULL, '', 761, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:40', '2026-01-12 10:42:40'),
(175, 'ppil259601', 'Hamza Mazen Ahmed Al-Amoudi', 'حمزة', 'Hamzo', NULL, NULL, NULL, '', 762, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:40', '2026-01-12 10:42:40'),
(176, 'pvgix15642', 'Khaled Khaled Ahmed Ali bin Mahfouz', 'خالد', 'Khloud', NULL, NULL, NULL, '', 763, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:40', '2026-01-12 10:42:40'),
(177, 'phrdv61007', 'Rayan Amr Sabry El-Sayed Metwally', 'ريان', 'Ryno', NULL, NULL, NULL, '', 764, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:40', '2026-01-12 10:42:40'),
(178, 'ppzk916153', 'Rayan Moamen Ayesh Salim Jaradah', 'ريان', 'Ryno', NULL, NULL, NULL, '', 765, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:40', '2026-01-12 10:42:40'),
(179, 'pgbkd93547', 'Saif Bassam Hassan Abu Ali', '', '', NULL, NULL, NULL, '', 766, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:41', '2026-01-12 10:42:41'),
(180, 'pbue871751', 'Abdulaziz Reda Waddah Mohamed Al-Khamash', '', '', NULL, NULL, NULL, '', 767, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:41', '2026-01-12 10:42:41'),
(181, 'p48xv46860', 'Abdulaziz Mazen Abdullah bin Mahfouz', '', '', NULL, NULL, NULL, '', 768, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:41', '2026-01-12 10:42:41'),
(182, 'pukix26786', 'Abdulrahman Ahmed Wahid Hassan Ali', '', '', NULL, NULL, NULL, '', 769, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:41', '2026-01-12 10:42:41'),
(183, 'pxxgy89299', 'Abdullah Shady Mahmoud Ahmed Al-Masri', '', '', NULL, NULL, NULL, '', 770, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:41', '2026-01-12 10:42:41'),
(184, 'pkfsl96236', 'Ali Khaled Ali bin Mahfouz', 'علي', 'Aloush', NULL, NULL, NULL, '', 771, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:42', '2026-01-12 10:42:42'),
(185, 'pkggd69414', 'Omar Amr Mohamed Hussein Al-Ghazali', 'عمر', 'Amour', NULL, NULL, NULL, '', 772, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:42', '2026-01-12 10:42:42'),
(186, 'plukt56283', 'Omar Mohamed Faraj Suleiman Faraj', 'عمر', 'Amour', NULL, NULL, NULL, '', 773, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:42', '2026-01-12 10:42:42'),
(187, 'px8gy26049', 'Mohamed Reda Fouad Al-Sayed Al-Shorbagy', '', '', NULL, NULL, NULL, '', 774, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:42', '2026-01-12 10:42:42'),
(188, 'ps4nb86158', 'Mohamed Fawzi Mohammed Al-Hadi', '', '', NULL, NULL, NULL, '', 775, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:43', '2026-01-12 10:42:43'),
(189, 'pr1fu77509', 'Mohamed Moataz Ammar Hussein Salem Baosman', '', '', NULL, NULL, NULL, '', 776, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:43', '2026-01-12 10:42:43'),
(190, 'pbeux28390', 'Mahmoud Sayed Mahmoud Mostafa El-Zoghbi', '', '', NULL, NULL, NULL, '', 777, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:43', '2026-01-12 10:42:43'),
(191, 'pjodp84469', 'Muath Sultan Ali Al-Zahrani', '', '', NULL, NULL, NULL, '', 778, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:43', '2026-01-12 10:42:43'),
(192, 'pwoio25525', 'Yahya Mazen Abdullah bin Mahfouz', '', '', NULL, NULL, NULL, '', 779, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:43', '2026-01-12 10:42:43'),
(193, 'p1pha65105', 'Yaseen Majed Abdullah Mohamed Badghish', '', '', NULL, NULL, NULL, '', 780, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:44', '2026-01-12 10:42:44'),
(194, 'pw19x96971', 'Youssef Ahmed Hamza Mohamed Suleiman', 'يوسف', 'Joe', NULL, NULL, NULL, '', 781, NULL, 1, NULL, 2, 8, 11, NULL, NULL, '2026-01-12 10:42:44', '2026-01-12 10:42:44'),
(195, 'p0ihf87539', 'Ibrahim Majed Saeed Ali Al-Amoudi', 'إبراهيم', 'Hima', NULL, NULL, NULL, '', 782, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:44', '2026-01-12 10:42:44'),
(196, 'pkr2f44713', 'Ahmed Amr Mohamed Hussein Al-Ghazali', 'أحمد', 'Hamada', NULL, NULL, NULL, '', 783, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:44', '2026-01-12 10:42:44'),
(197, 'ppwtr10324', 'Adam Amr Sabry El-Sayed Metwally', 'آدم', 'Adoumi', NULL, NULL, NULL, '', 784, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:44', '2026-01-12 10:42:44'),
(198, 'p52kx99558', 'Anas Mohamed Ihab Mohamed Mamdouh Ahmed Amer', '', '', NULL, NULL, NULL, '', 785, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:45', '2026-01-12 10:42:45'),
(199, 'p9luw86009', 'Khaled Reda Waddah Mohamed Al-Khamash', 'خالد', 'Khloud', NULL, NULL, NULL, '', 786, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:45', '2026-01-12 10:42:45'),
(200, 'pu6dz28339', 'Reda Moamen Ayesh Salim Jaradah', '', '', NULL, NULL, NULL, '', 787, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:45', '2026-01-12 10:42:45'),
(201, 'pe1nq43326', 'Sultan Mazen Abdullah bin Mahfouz', '', '', NULL, NULL, NULL, '', 788, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:45', '2026-01-12 10:42:45'),
(202, 'pp8cm44799', 'Seif Ahmed Sultan Abdo Al-Qurashi', '', '', NULL, NULL, NULL, '', 789, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:46', '2026-01-12 10:42:46'),
(203, 'pn40i86836', 'Abdelaziz Ghassan Mohamed Al-Shehri', '', '', NULL, NULL, NULL, '', 790, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:46', '2026-01-12 10:42:46'),
(204, 'pkcge53365', 'Abdullah Khaled Ali bin Mahfouz', '', '', NULL, NULL, NULL, '', 791, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:46', '2026-01-12 10:42:46'),
(205, 'plljf13624', 'Ali Khaled Mohammed Saleh Al-Malki', 'علي', 'Aloush', NULL, NULL, NULL, '', 792, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:46', '2026-01-12 10:42:46'),
(206, 'pwbn679043', 'Omar Reda Fouad Al-Sayed Al-Shorbagy', 'عمر', 'Amour', NULL, NULL, NULL, '', 793, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:46', '2026-01-12 10:42:46'),
(207, 'pbpnt84897', 'Omar Mohamed Faraj Suleiman Faraj', 'عمر', 'Amour', NULL, NULL, NULL, '', 794, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:47', '2026-01-12 10:42:47'),
(208, 'ppeg160947', 'Mohamed Basem Khalid bin Mahfouz', '', '', NULL, NULL, NULL, '', 795, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:47', '2026-01-12 10:42:47'),
(209, 'pd7ms61142', 'Mohamed Shady Mahmoud Ahmed Al-Masri', '', '', NULL, NULL, NULL, '', 796, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:47', '2026-01-12 10:42:47'),
(210, 'petmp80317', 'Mahmoud Mohamed Faraj Suleiman Faraj', '', '', NULL, NULL, NULL, '', 797, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:47', '2026-01-12 10:42:47'),
(211, 'psmoa70332', 'Moataz Mohamed Bashar Hafiz Al-Ankashari', '', '', NULL, NULL, NULL, '', 798, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:47', '2026-01-12 10:42:47'),
(212, 'pnug243860', 'Yassin Ahmed Hamza Mohamed Suleiman', '', '', NULL, NULL, NULL, '', 799, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:48', '2026-01-12 10:42:48'),
(213, 'ppbbv93472', 'Youssef Mohamed Hussein Mohamed Ghazali', 'يوسف', 'Joe', NULL, NULL, NULL, '', 800, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:48', '2026-01-12 10:42:48'),
(214, 'pnxso92676', 'Youssef Mustafa Hassan Yahya Al-Yamani', 'يوسف', 'Joe', NULL, NULL, NULL, '', 801, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:48', '2026-01-12 10:42:48'),
(215, 'pdyri51742', 'Mohammed Abdullah Mohammed Al Twaim', 'محمد', 'Hamoudi', NULL, NULL, NULL, '', 802, NULL, 1, NULL, 2, 9, 12, NULL, NULL, '2026-01-12 10:42:48', '2026-01-12 10:42:48'),
(216, 'pcubr22718', 'Betal Nabil Atiyah Al-Khatabi', '', '', NULL, NULL, NULL, '', 803, NULL, 1, NULL, 3, 10, 13, NULL, NULL, '2026-01-12 10:42:49', '2026-01-12 10:42:49'),
(217, 'p50vs34518', 'Begad Wael Fathi Ibrahim Mohamed', '', '', NULL, NULL, NULL, '', 804, NULL, 1, NULL, 3, 10, 13, NULL, NULL, '2026-01-12 10:42:49', '2026-01-12 10:42:49'),
(218, 'pmdbf61457', 'Abdelilah Khaled Saad Saeed Al-Ghamdi', '', '', NULL, NULL, NULL, '', 805, NULL, 1, NULL, 3, 10, 13, NULL, NULL, '2026-01-12 10:42:49', '2026-01-12 10:42:49'),
(219, 'pqv4q32039', 'Ali Hassan Ali Hassan Ali', 'علي', 'Aloush', NULL, NULL, NULL, '', 806, NULL, 1, NULL, 3, 10, 13, NULL, NULL, '2026-01-12 10:42:49', '2026-01-12 10:42:49'),
(220, 'pjhjg32004', 'Omar Ahmed Mohamed Ibrahim Abualainin', 'عمر', 'Amour', NULL, NULL, NULL, '', 807, NULL, 1, NULL, 3, 10, 13, NULL, NULL, '2026-01-12 10:42:49', '2026-01-12 10:42:49'),
(221, 'ps6jk32466', 'Malek Mohamed Sayed Abdel Hamid Al-Rifa’i', 'مالك', 'Mlouki', NULL, NULL, NULL, '', 808, NULL, 1, NULL, 3, 10, 13, NULL, NULL, '2026-01-12 10:42:50', '2026-01-12 10:42:50');

-- --------------------------------------------------------

--
-- Table structure for table `student_behaviors`
--

CREATE TABLE `student_behaviors` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `student_behaviors_mains_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `attend` tinyint DEFAULT NULL,
  `points_plus` int DEFAULT NULL,
  `points_minus` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_behaviors_mains`
--

CREATE TABLE `student_behaviors_mains` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `year_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `classroom_id` bigint UNSIGNED NOT NULL,
  `period_code_main` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_behaviors_mains`
--

INSERT INTO `student_behaviors_mains` (`id`, `school_id`, `year_id`, `teacher_id`, `subject_id`, `classroom_id`, `period_code_main`, `period_code`, `date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 17, 25, 7, '7.25.17', '1.1.1.1', '2026-01-07', NULL, '2026-01-07 09:52:36', '2026-01-07 09:52:36');

-- --------------------------------------------------------

--
-- Table structure for table `student_behaviors_point_actions`
--

CREATE TABLE `student_behaviors_point_actions` (
  `id` bigint UNSIGNED NOT NULL,
  `student_behaviors_id` bigint UNSIGNED NOT NULL,
  `reason_id` bigint UNSIGNED DEFAULT NULL,
  `value` int NOT NULL,
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `canceled` tinyint(1) NOT NULL DEFAULT '0',
  `canceled_by` bigint UNSIGNED DEFAULT NULL,
  `canceled_at` timestamp NULL DEFAULT NULL,
  `cancel_reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_classroom_history`
--

CREATE TABLE `student_classroom_history` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `from_classroom_id` bigint UNSIGNED DEFAULT NULL,
  `to_classroom_id` bigint UNSIGNED NOT NULL,
  `from_grade_id` bigint UNSIGNED DEFAULT NULL,
  `to_grade_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED DEFAULT NULL,
  `semester_id` bigint UNSIGNED DEFAULT NULL,
  `changed_by_user_id` bigint UNSIGNED NOT NULL,
  `change_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `changed_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_parents`
--

CREATE TABLE `student_parents` (
  `id` bigint UNSIGNED NOT NULL,
  `t_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `data` json DEFAULT NULL,
  `report` tinyint NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_period_records`
--

CREATE TABLE `student_period_records` (
  `id` bigint UNSIGNED NOT NULL,
  `period_activity_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `attendance_status` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present' COMMENT 'present, absent, late, excused',
  `late_minutes` int DEFAULT NULL,
  `homework_completed` tinyint(1) NOT NULL DEFAULT '0',
  `homework_score` decimal(5,2) DEFAULT NULL,
  `behavior_plus_marks` int NOT NULL DEFAULT '0',
  `behavior_minus_marks` int NOT NULL DEFAULT '0',
  `behavior_notes` text COLLATE utf8mb4_unicode_ci,
  `participation_score` int DEFAULT NULL,
  `participation_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nour_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nour_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `active` tinyint NOT NULL DEFAULT '1',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lesson_plan_templates` json DEFAULT NULL,
  `school_id` bigint UNSIGNED NOT NULL,
  `color_bg` varchar(22) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_text` varchar(22) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `name_ar`, `nour_name`, `nour_id`, `description`, `active`, `notes`, `lesson_plan_templates`, `school_id`, `color_bg`, `color_text`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Mathematics', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#3B82F6', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(2, 'Math-Nafs', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#2563EB', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 02:29:42'),
(3, 'Science', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#10B981', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(4, 'Science (N)', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#059669', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(5, 'English', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#F59E0B', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(6, 'English-Nafs', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#D97706', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 02:29:42'),
(7, 'Arabic', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#EF4444', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(8, 'Biology', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#22C55E', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(9, 'Chemistry', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#14B8A6', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(10, 'Physics', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#06B6D4', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(11, 'Geography', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#8B5CF6', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(12, 'Us History', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#A855F7', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 02:29:42'),
(13, 'SSA', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#9333EA', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(14, 'SSE', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#7C3AED', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(15, 'Islamic', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#059669', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(16, 'Noor Albian', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#047857', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 02:29:42'),
(17, 'French', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#DC2626', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(18, 'Ict', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#6366F1', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 02:29:42'),
(19, 'Robot', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#4F46E5', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(20, 'Pe', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#0EA5E9', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 02:29:42'),
(21, 'Art', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#EC4899', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(22, 'Gat', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#F97316', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 02:29:42'),
(23, 'Sat', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#EA580C', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 02:29:42'),
(24, 'Capstone', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, '#84CC16', '#FFFFFF', NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(25, 'Math', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(26, 'Ssa (Social Studies Arabic)', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42'),
(27, 'Sse (Social Studies English)', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `classification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Category or tag for the task',
  `due_date` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0' COMMENT 'For ordering tasks within the same parent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `t_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint UNSIGNED DEFAULT NULL,
  `schools_number` tinyint NOT NULL DEFAULT '1',
  `school_extra_ids` json DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_cute` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `national_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_1` int DEFAULT NULL,
  `order_2` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `t_id`, `school_id`, `schools_number`, `school_extra_ids`, `user_id`, `name`, `name_ar`, `name_cute`, `national_id`, `email`, `phone_number`, `whatsapp_number`, `gender`, `date_of_birth`, `nationality`, `address`, `order_1`, `order_2`, `notes`, `data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'tlhal3184', 1, 1, NULL, 3, 'Ahmed Gewily', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(2, 'tqlo16608', 1, 1, NULL, 4, 'Mohamed Elsayed', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(3, 't7tvw2141', 1, 1, NULL, 5, 'Mahmoud Allam', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(4, 'tbush3525', 1, 1, NULL, 6, 'Saeed Morsy', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(5, 'tux4y2991', 1, 1, NULL, 7, 'Hafeez', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(6, 'tgjjo4525', 1, 1, NULL, 8, 'Hameed', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(7, 'tr3z79429', 1, 1, NULL, 9, 'Fahd Almalky', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(8, 'tmdd96005', 1, 1, NULL, 10, 'Abdullah Diara', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(9, 'tp9eu4700', 1, 1, NULL, 11, 'Omar Gamil', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(10, 't80fz9490', 1, 1, NULL, 12, 'Osama', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(11, 'tmazu4303', 1, 1, NULL, 13, 'Sayed Zalama', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(12, 't0nzs6149', 1, 1, NULL, 14, 'Abdulwahab Yusr', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(13, 'tnmc69961', 1, 1, NULL, 15, 'Kassim Ibrahim', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(14, 't5x046026', 1, 1, NULL, 16, 'Lucky', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(15, 'txo8z2526', 1, 1, NULL, 17, 'Ahmed Rabie', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(16, 'tslaw6724', 1, 1, NULL, 18, 'Abdulwahab Saleh', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(17, 'tuhn06837', 1, 1, NULL, 19, 'Ahmed Mosad', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(18, 'tngqe2659', 1, 1, NULL, 20, 'Emad Maghawry', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(19, 'tpgtx8677', 1, 1, NULL, 21, 'Tarek Zanaty', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(20, 'tiuwd6356', 1, 1, NULL, 22, 'Yaser', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(21, 'tzxjm3691', 1, 1, NULL, 23, 'Mosaab', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(22, 'tvox02722', 1, 1, NULL, 24, 'Hatem Alsawi', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tree_structures`
--

CREATE TABLE `tree_structures` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tree_data` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `school_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('SuperAdmin','admin','hr_admin','supervisor','teacher','student','parent','user','guest') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `first_login` tinyint NOT NULL DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `last_active` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `school_id`, `name`, `email`, `email_verified`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `role`, `first_login`, `last_login`, `last_active`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Super Admin', 'admin@myclass.com', NULL, '2026-01-03 01:24:40', '$2y$12$woHIUAmSa3UCZxvMAjSdE.VROSuQ.AOow1TiiNPQEbIzjb4Uwz1Ii', NULL, NULL, NULL, NULL, NULL, NULL, 'SuperAdmin', 1, NULL, '2026-01-11 13:56:22', 1, '2026-01-03 01:24:40', '2026-01-11 13:56:22', NULL),
(2, 1, 'HR Manager', 'hr@myclass.com', NULL, '2026-01-03 01:24:40', '$2y$12$c.GQ.kig4XFwBOLvLMEXy.YhHZBP5XPz0TIx2Xi9mIUWp6v8NRbIi', NULL, NULL, NULL, NULL, NULL, NULL, 'hr_admin', 1, NULL, '2026-01-03 10:10:12', 1, '2026-01-03 01:24:40', '2026-01-03 10:10:12', NULL),
(3, 1, 'Ahmed Gewily', 'tlhal3184', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(4, 1, 'Mohamed Elsayed', 'tqlo16608', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(5, 1, 'Mahmoud Allam', 't7tvw2141', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(6, 1, 'Saeed Morsy', 'tbush3525', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(7, 1, 'Hafeez', 'tux4y2991', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(8, 1, 'Hameed', 'tgjjo4525', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(9, 1, 'Fahd Almalky', 'tr3z79429', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(10, 1, 'Abdullah Diara', 'tmdd96005', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(11, 1, 'Omar Gamil', 'tp9eu4700', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(12, 1, 'Osama', 't80fz9490', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(13, 1, 'Sayed Zalama', 'tmazu4303', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(14, 1, 'Abdulwahab Yusr', 't0nzs6149', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(15, 1, 'Kassim Ibrahim', 'tnmc69961', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(16, 1, 'Lucky', 't5x046026', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(17, 1, 'Ahmed Rabie', 'txo8z2526', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(18, 1, 'Abdulwahab Saleh', 'tslaw6724', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(19, 1, 'Ahmed Mosad', 'tuhn06837', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, 'YUICbvFNOEvsBcouIIOOFLsVdD29wGiwSdZlNOabsRdA5qDmYgypeka25iPH', NULL, NULL, 'teacher', 1, NULL, '2026-01-13 14:23:58', 1, '2026-01-03 02:29:42', '2026-01-13 14:23:58', NULL),
(20, 1, 'Emad Maghawry', 'tngqe2659', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(21, 1, 'Tarek Zanaty', 'tpgtx8677', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(22, 1, 'Yaser', 'tiuwd6356', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(23, 1, 'Mosaab', 'tzxjm3691', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(24, 1, 'Hatem Alsawi', 'tvox02722', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(25, NULL, 'Super System Developer', 'developer@myclass.com', NULL, '2026-01-10 02:17:13', '$2y$12$mR2gPP07bwzlqCsvHWTH4u0cgXDHMksqFZzX7Pv3AvL.tDHcVy486', NULL, NULL, NULL, '7DISgxjrb4lb74z3IgakapYtxI8PjHnm6fm0L3hHSvywIYIgIxeOHm31dxRH', NULL, NULL, 'user', 1, NULL, '2026-01-10 02:39:51', 1, '2026-01-10 02:17:13', '2026-01-10 02:39:51', NULL),
(588, NULL, 'Ibrahim Firas Fouad Awad Abdeljawad', 'pxmiq59123', NULL, NULL, '$2y$12$hpSV3MCnMUSLad07nbhLp.Sk8w.cXthhHx82E.l7ydW3VtuOhY.ou', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:02', '2026-01-12 10:42:02', NULL),
(589, NULL, 'Ahmed Abdulaziz Khalil Abdulaziz Naseer', 'prhjk86462', NULL, NULL, '$2y$12$6omO9q4WARJ4W/t9QE5QHOoCluFMNJcDc0yp6A/dzqMeq4XLE2Ge.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:02', '2026-01-12 10:42:02', NULL),
(590, NULL, 'Adam Islam Salah Salem Mokhtar Abu Amoud', 'peglw60327', NULL, NULL, '$2y$12$DjfmrFEw5On1wwRPwz0L4Oxi3PsL0IHuzCglum2OtKWVq4gGs7CTy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:03', '2026-01-12 10:42:03', NULL),
(591, NULL, 'Asser Ahmed Abd El-Moaty Abd El-Moneim', 'ptfpi91901', NULL, NULL, '$2y$12$QT72JdlkKTpYHUGHZ5ERmOX4kXzMmujaoeipaziwtKDlV0qDCKF82', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:03', '2026-01-12 10:42:03', NULL),
(592, NULL, 'Thamer Sultan Saad Abdullah Al-Omari', 'plgaj80574', NULL, NULL, '$2y$12$Xkw18r8SxmZgou5pghAPs.J/cjYrLfKh7.YtqKkrMd5YVAik.N6uG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:03', '2026-01-12 10:42:03', NULL),
(593, NULL, 'Hamza Sharif Salah Al-Sayed Jabr', 'pno4h68386', NULL, NULL, '$2y$12$Ujemz73VGQSjNQawfOXQQeiXyIORDMUwuAPB6mRJ9vjXMucDVUymy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:03', '2026-01-12 10:42:03', NULL),
(594, NULL, 'Khaled Thamer Khaled Abdulaziz Bin Najifan', 'pj9qe81503', NULL, NULL, '$2y$12$/Ph.TPq0mD1AgGvGUpaEXOpiixMXeHmFKAv6P5w8C4qhqA7QKdn/i', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:03', '2026-01-12 10:42:03', NULL),
(595, NULL, 'Rayan Jamal Mohamed Taysir Haykal', 'p0h3v73923', NULL, NULL, '$2y$12$nMOa2wh0CBbMF1vWJcNdXucHwXtqFXP6CCl2TlFaCZjECJ6TanYDe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:04', '2026-01-12 10:42:04', NULL),
(596, NULL, 'Rayan Firas Nizar Daaboul', 'prkqd45760', NULL, NULL, '$2y$12$9EKkrptOPyQTLH7NU8fk2uPvInfHCvPrCHfLtTpoSdV5Qx4U6NVUG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:04', '2026-01-12 10:42:04', NULL),
(597, NULL, 'Rayan Ridwan Mohamedwali Mohamed Othman Aziz Khan', 'p6ald95633', NULL, NULL, '$2y$12$G58sWwG8Ey4TjifZH18Gge5WaoDoU.2XjWT12emMltXcCQpW0q/Ry', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:04', '2026-01-12 10:42:04', NULL),
(598, NULL, 'Salim Moamen Ayesh Salim Jaradah', 'peg8i39834', NULL, NULL, '$2y$12$dRXwfH.g./aw7aSNYZG5X.uxzQu6Zx04ebp6Zd0FhXrqI.nulDDYC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:04', '2026-01-12 10:42:04', NULL),
(599, NULL, 'Suleiman Abdullah Suleiman Mohamed Al-Omari', 'pfkyr12043', NULL, NULL, '$2y$12$WZ/P4i1EbSLZ440GnB0ZJO8nAQIzFJd8cl3oQfRvYifspZXlpCIUW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:05', '2026-01-12 10:42:05', NULL),
(600, NULL, 'Samher Mohamed Bashar Hafiz Nazir Al-Ankashari', 'pzqrd91463', NULL, NULL, '$2y$12$fa32N58scP.4KHjmvwqV1e8uWYkk8S8y17m8hgaQUxGPN2xgqfb7y', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:05', '2026-01-12 10:42:05', NULL),
(601, NULL, 'Sohaib Saber Abdelmagsoud Tawfiq', 'pztb086004', NULL, NULL, '$2y$12$JG309nHl4Du6RP8dyYrKAeDzmj2Z7Jx.QrfhBirIpKiwZNrYT7amy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:05', '2026-01-12 10:42:05', NULL),
(602, NULL, 'Abdulaziz Shady Mahmoud Ahmed Al-Masri', 'pbvek47860', NULL, NULL, '$2y$12$/UnrIXQlc93YCBUhjiCQGunZXxvspC8cOuCD2I6Q/z35TqmzzXSp2', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:05', '2026-01-12 10:42:05', NULL),
(603, NULL, 'Adnan Reda Waddah Mohamed Al-Khamash', 'pgq3i47752', NULL, NULL, '$2y$12$oXsE7kP8Wy8DdLIv/F8YcuGEPGdDg9YpNBp9c5Lp1HLRo.l.tLxxi', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:05', '2026-01-12 10:42:05', NULL),
(604, NULL, 'Odai Mohannad Mahmoud Mohamed Rajab', 'pnpig93393', NULL, NULL, '$2y$12$jgD/pirfp./wRR/VSIxqquajrUMPtb0yVXhSZZOIMZ5unhnvL.YOe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:06', '2026-01-12 10:42:06', NULL),
(605, NULL, 'Ali Ahmed Waheed Hassan Ali', 'peqfq44669', NULL, NULL, '$2y$12$YrhYzlj1cNikn3YZarau7uCUkORL/k1AJyOie5JXogXmLYE57J.4W', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:06', '2026-01-12 10:42:06', NULL),
(606, NULL, 'Ammar Diyaa Adel Hassan Ayourzeza', 'pms5c26157', NULL, NULL, '$2y$12$MKblNjPeA1BMHy1WER27PeqT4pclHIgqDQVbkyvQnpOEYbtIpZneW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:06', '2026-01-12 10:42:06', NULL),
(607, NULL, 'Omar Sultan Adnan Abdulrahim Akbar', 'prvve62164', NULL, NULL, '$2y$12$uWPcPWquxU7yhIiaT2q8IO74mI1fTARrMDt3xysmqrznfFYLh4dxa', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:06', '2026-01-12 10:42:06', NULL),
(608, NULL, 'Mohamed Hassan Asheq Abdullah Hassan Al-Mohamad', 'phvkg81837', NULL, NULL, '$2y$12$uxm5wvNKrrQ7m7vWjgdcAuaqq5rmp36PuEVHJlwDgitMCD0TUTh3C', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:07', '2026-01-12 10:42:07', NULL),
(609, NULL, 'Yusuf Ahmed Mohamed Al-Nazzawi Al-Jahni', 'pqgpo63065', NULL, NULL, '$2y$12$v6ED1bFRfYgXjoMNzv6CWueSUc6.AstUTN5QpzPCG5YjKHhdua9aa', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:07', '2026-01-12 10:42:07', NULL),
(610, NULL, 'Yusuf Ibrahim Adel Mohamed Al-Maghazi', 'pdqha80039', NULL, NULL, '$2y$12$Pqp38J4N5U1lRK2CLOEu3.edRdVBTrBdP1NcAJxqGloGp5aAmFPc.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:07', '2026-01-12 10:42:07', NULL),
(611, NULL, 'Turki Ahmed Abdullah Rabiea', 'pgqb898748', NULL, NULL, '$2y$12$jVS4f21cqKM9uDZaRNH1ne7VNl1NaUmgGJ6MK09W9xRu3X1G5.Vcy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:07', '2026-01-12 10:42:07', NULL),
(612, NULL, 'Yamen Sultan Aref Mustafa Al-Dweikat', 'pjyvk78782', NULL, NULL, '$2y$12$NUBAEdB6ZGJWSxsK4SIZVe1J2SL3I5QT3bVWYVFl2KMbwl8EdkarW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:07', '2026-01-12 10:42:07', NULL),
(613, NULL, 'Ibrahim Sultan Ibrahim Khalil Rawas', 'pbtxz31108', NULL, NULL, '$2y$12$.EPUVCsW15Ws5wnylWa5Y.L8yoAeBHNA4cLrCpAlpoSx1jKl/eaSi', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:08', '2026-01-12 10:42:08', NULL),
(614, NULL, 'Elias Tarek Jubeir Al-Qurashi', 'pp98r43451', NULL, NULL, '$2y$12$fYf3gfL6GekzyMFqRrHaY.a.ll/KNbzLC.KEo6NqZY5h6HCl.feFm', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:08', '2026-01-12 10:42:08', NULL),
(615, NULL, 'Ameer Hani Saeed Suleiman Dahdoolan', 'pnf6b11927', NULL, NULL, '$2y$12$nV3EUhYGOaCXO62T/6kBV.H1hF9WMKhTP97yAqZq.LoFcnIhFw.1q', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:08', '2026-01-12 10:42:08', NULL),
(616, NULL, 'Saeed Mohamed Saeed Fawaz Al-Harbi', 'pbh0283973', NULL, NULL, '$2y$12$L7pGW1UlhEPXU6fi1GDdVOTmathZQEKDDNsQoGOdMGeTQOtoRZ8lu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:08', '2026-01-12 10:42:08', NULL),
(617, NULL, 'Saif Bassam Salamah Ateeq Allah Al-Harbi', 'pyycb50394', NULL, NULL, '$2y$12$4o4j0EMQhcFk.FYhK4ktaehyrvPTsTnAOErenvi80RKZHGGCTcv4W', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:08', '2026-01-12 10:42:08', NULL),
(618, NULL, 'Omar Sharif Abdelfattah Al-Mutawali Abd Al-Razek', 'pb7v934497', NULL, NULL, '$2y$12$5GjJURIvq/3MCM.BN7iQTO8TZgs1zBalWOlqr2Z2EjTA2ho7Hy.Fy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:09', '2026-01-12 10:42:09', NULL),
(619, NULL, 'Omar Shady Jabr Shaaban Salem', 'pffed28424', NULL, NULL, '$2y$12$uCfeRgbPfurOS9e87eZQNOcdEa8dkDXo13LKOizSQhlmg7wIoDQ6O', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:09', '2026-01-12 10:42:09', NULL),
(620, NULL, 'Omar Mohamed Abdo Mohamed Saleh', 'p4kzs30180', NULL, NULL, '$2y$12$.mnBTOAWTTDaUPVdfxh42O.mzjnW4CPrPsYpEOeB805Rn/upBthL6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:09', '2026-01-12 10:42:09', NULL),
(621, NULL, 'Omar Ayman Ahmed Abdulrahman Al-Shazly', 'pagfd31105', NULL, NULL, '$2y$12$itLJDImJ9FSt8IPqxGjB7ujFZmWC3IMEUqdOxmxNMvGM9tkoIR5oe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:09', '2026-01-12 10:42:09', NULL),
(622, NULL, 'Omar Mohamed Omar Mohamed Balbeed', 'p4vxr99648', NULL, NULL, '$2y$12$2ZxpX9Y/YQjGtgb4b8qmqOHg5zRlmz0zx3169vN3O0EgPGvnhaNuG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:10', '2026-01-12 10:42:10', NULL),
(623, NULL, 'Gaith Emad Abbas Hassan Ghandoura', 'pmbpl55107', NULL, NULL, '$2y$12$3U28F/owhoy5.EyZuwQJfusXKaFkIOgx/L8MfmH7VbkyyXoW1PzH.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:10', '2026-01-12 10:42:10', NULL),
(624, NULL, 'Fahd Tarek Jubeir Al-Qurashi', 'p2qfs53246', NULL, NULL, '$2y$12$d7K1DD5B6S/O0DAWXfCCgOlcbf7/GPMG804JgWsGFnKgAEz4tgS46', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:10', '2026-01-12 10:42:10', NULL),
(625, NULL, 'Kanan Raed Mohamed Youssef Sabah', 'p3jnm10130', NULL, NULL, '$2y$12$pJl.JvzPpyyRIeWkmOiRteZKmbpo0HP5OujAIrqu8VjYkHgmUQlj6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:10', '2026-01-12 10:42:10', NULL),
(626, NULL, 'Mazen Sayed Mohamed Sayed Ali', 'pmuhy46737', NULL, NULL, '$2y$12$wqG8R5KxXyP4/uOc2PkA2eFR34rnHZLBBVB1/czLgeo.aqO7XQ00i', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:10', '2026-01-12 10:42:10', NULL),
(627, NULL, 'Malek Mohamed Fikry Abdel Majeed Hassan', 'pkvif36170', NULL, NULL, '$2y$12$015kPyZTONx4BjhQtT7tW.QQgd4LxyKLgb7f9/Vzpo7BX76TOiCN6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:11', '2026-01-12 10:42:11', NULL),
(628, NULL, 'Mohamed Ehab Abdelfattah Ali Ghazi', 'pzcff38143', NULL, NULL, '$2y$12$0bITOynUqOw64No71Tzbb.8R2b5CS94r976DBymdKDrrAWfgz/LwS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:11', '2026-01-12 10:42:11', NULL),
(629, NULL, 'Mohamed Ibrahim Alam Bah Bah', 'piteb39764', NULL, NULL, '$2y$12$iAl0YcQqWT642gmwPYiFyeS2wOS3AFfdm9FsFvendd0g.fQBo01Bm', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:11', '2026-01-12 10:42:11', NULL),
(630, NULL, 'Mahdi Mohamed Mahdi Haidar Hassan', 'pteg997894', NULL, NULL, '$2y$12$rUApdjKRjl5KkIBGO1wPd.u9pbWjHrOrBC4ezDvHCBXbEsuAYrrRi', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:11', '2026-01-12 10:42:11', NULL),
(631, NULL, 'Yaseen Mohamed Hussein Mohamed Ghazali', 'peupo18461', NULL, NULL, '$2y$12$vHD1cfZkd/EoYfF9S.hogeQrfywChn5IQ0eQQOdZESY9iYD/qHa2S', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:12', '2026-01-12 10:42:12', NULL),
(632, NULL, 'Yazan Ahmed Fahmi Zaki Al-Banna', 'pkp4a87759', NULL, NULL, '$2y$12$BTUGoQz/dcy87XwUegSRde/KxdlwtSB5Zl9a7HJSojL4j.E8dJGkG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:12', '2026-01-12 10:42:12', NULL),
(633, NULL, 'Yusuf Majed Abdullah Mohamed Badghish', 'p1x7z25492', NULL, NULL, '$2y$12$rBD.KLopPSMUngmdL6C51e.62LRd/8.XewhGTLWI2qX7FXO5Kdccq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:12', '2026-01-12 10:42:12', NULL),
(634, NULL, 'Yaseen Mohamed Najm Aldin', 'pb1gm79765', NULL, NULL, '$2y$12$EZIBCR7zDKH8rz3ODuKcZeRy3nhdyTydG72uZoq4nUA6mJmT.RT86', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:12', '2026-01-12 10:42:12', NULL),
(635, NULL, 'Ibrahim Hazem Ibrahim Hazem Al-Ghanem', 'pweao85942', NULL, NULL, '$2y$12$Dkt9/yAteBRueTF3y/hGT.LrY9X5JKyW1Rv5/vlSDd9Czk7YBVrti', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:12', '2026-01-12 10:42:12', NULL),
(636, NULL, 'Anas Youssef Ahmed Salah El-Din Al-Atbani', 'psvyg84718', NULL, NULL, '$2y$12$Urv4O58rmeKcKVt9EgMBDuTif0PLzcl0UhXUWOGgxh4urLGxdlN2.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:13', '2026-01-12 10:42:13', NULL),
(637, NULL, 'Jad Hetan Jamal Abdullah Baqis', 'pefq053828', NULL, NULL, '$2y$12$B2fJVQ1zLC7u/v/8fzpSrekxvAC24fwMkiWWM0XEy3WaTDB.IcsmS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:13', '2026-01-12 10:42:13', NULL),
(638, NULL, 'Jaafar Abdullah Jaafar Bin Mahfouz', 'pfhj274421', NULL, NULL, '$2y$12$/aMKhLWvCxh3ATL181lZI.TIpTjaTJ9u8kAGmxtPEWUtF1Dfs8YeS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:13', '2026-01-12 10:42:13', NULL),
(639, NULL, 'Reda Moamen Ayesh Salim Jaradah', 'pwgut32877', NULL, NULL, '$2y$12$WqajWqmS2VLGoqDUhytQdeirwOpxqbkpROvoZBU502oyUdZU4rcgG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:13', '2026-01-12 10:42:13', NULL),
(640, NULL, 'Saif Mohammed Abdullah Salem Al-Amoudi', 'pn7xn98433', NULL, NULL, '$2y$12$hmXuqLrogg9yjG13G/q6TuZEap.cQjzBjQB5KMOYG7oJblHPDpLVy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:13', '2026-01-12 10:42:13', NULL),
(641, NULL, 'Abdulaziz Ghazi Ghaleb Al-Otaibi', 'pgb4i93101', NULL, NULL, '$2y$12$dSE8bxvDdE8gNkCJjCMYHuTJdjR3mlBXmwlu4y62t8EVVvF.BF4Vy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:14', '2026-01-12 10:42:14', NULL),
(642, NULL, 'Abdullah Mazen Abdelkader Al-Amoudi', 'psbfb66839', NULL, NULL, '$2y$12$/Nq0H4rvJvfGfcrwpKtBauGwBfxl125EejVDy5bbNe857pLbUVtSq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:14', '2026-01-12 10:42:14', NULL),
(643, NULL, 'Ali Khaled Mohammed Saleh Al-Malki', 'pec9r79353', NULL, NULL, '$2y$12$9MqgxRDB5248PhYnhCrsi.CWENeDGiqOS9r4bZHec3OTAwU1CBNjq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:14', '2026-01-12 10:42:14', NULL),
(644, NULL, 'Omar Hussein Ahmed Otair', 'pvoyy99115', NULL, NULL, '$2y$12$VSWO1oEHZpbAcdc0.kiIHOcPNB8/Xag9uvtQnjyWoSahJ.8CBQtIa', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:14', '2026-01-12 10:42:14', NULL),
(645, NULL, 'Omar Mohammed Abdul-Haq Mohammed Hanif', 'pms4a24582', NULL, NULL, '$2y$12$bXSsg9d9IolLIQmOUZ3pn.hgbf0yRrnMFpdo7GiAzEkEKG7eaI6IW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:15', '2026-01-12 10:42:15', NULL),
(646, NULL, 'Ghassan Maher Reda Badawi', 'pkmdh39477', NULL, NULL, '$2y$12$jaow4mECF7NvwyWQwB.oOOIw6UmddK/xmURYDCESkxokM4MMz2YUO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:15', '2026-01-12 10:42:15', NULL),
(647, NULL, 'Karam Ahmed Mohamed Hamed El-Sharshaby', 'pivjg94924', NULL, NULL, '$2y$12$SisANDyRYf1.sRc5QAQndONfqviGBlKm.xukhrtaqSt6rzQdjRWWG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:15', '2026-01-12 10:42:15', NULL),
(648, NULL, 'Mohamed Ahmed El-Sayed Ahmed El-Baz', 'ptcnb49596', NULL, NULL, '$2y$12$sy7e6cqQrlf.Hims.1wG/.JOmCXgbTiCdYH.x1UqLFD0M.utCU0Pi', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:15', '2026-01-12 10:42:15', NULL),
(649, NULL, 'Mohamed Ihab Mohamed Mamdouh Ahmed Amer', 'pqf9t81374', NULL, NULL, '$2y$12$8bisGKhD6hVY3F8LbkUg8ORQbDTraI4GiKRvrqj6M0FP.EnpIqnnW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:15', '2026-01-12 10:42:15', NULL),
(650, NULL, 'Mohammed Sultan Adnan Abdulrahim Akbar', 'pavhk68872', NULL, NULL, '$2y$12$vdzi7uCZZAzMe6Xqw/IbJusxLifNBeqVv.cFSF4xY74NROVyLuDSe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:16', '2026-01-12 10:42:16', NULL),
(651, NULL, 'Mahmoud Sayed Mahmoud Mostafa El-Zoghbi', 'pcpjj74977', NULL, NULL, '$2y$12$H1e0NWLCax.nFjMpl2RCQeYEy7EX66i5Ak5l/KOl5qkOaJ344Vy16', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:16', '2026-01-12 10:42:16', NULL),
(652, NULL, 'Muath Sultan Ali Al-Zahrani', 'p6swc55677', NULL, NULL, '$2y$12$UmN7SaoUtYV2VYtC8B7oRO5/YmbbMm8TzPF31JrtxvZ3OrlBMmdY6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:16', '2026-01-12 10:42:16', NULL),
(653, NULL, 'Musaab Abdul-Rahman Musaab Sabr', 'p6zlw56373', NULL, NULL, '$2y$12$okz8K/xwg4gwst06v3f1jut9HzQ06an.wHKLk3hYBf4UDRMimejxq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:16', '2026-01-12 10:42:16', NULL),
(654, NULL, 'Nasser Maher Nasser Ahmed Mahdi', 'pzxx743644', NULL, NULL, '$2y$12$dZDLU3gTC5CQPiIqb8EEVuOntzk.1p6cfZJsuDj42JdjeQPZQkpuW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:16', '2026-01-12 10:42:16', NULL),
(655, NULL, 'Nawaf Ahmed Fouad Ali Bin Mahfouz', 'ppt8294162', NULL, NULL, '$2y$12$yjwejl6ky3W.aAyBxixZZeWD5LHtEHo7lkQ/BGihIDQ2MBIDZz..K', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:17', '2026-01-12 10:42:17', NULL),
(656, NULL, 'Noah Abdullah Hussein Al-Attas', 'pmmdf97531', NULL, NULL, '$2y$12$KPAHPD6E46vSTsEYJlHrJelhpN4SaIHvil56yUC1b/hWVgOGMhkAO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:17', '2026-01-12 10:42:17', NULL),
(657, NULL, 'Ibrahim Reda Waddah Mohamed Al-Khamash', 'pwfur14025', NULL, NULL, '$2y$12$/BkmeLUwgc5Bx9sY7BrEz.LWqG9ebGgaS7Rp6umFEFmZNvfhbZmHu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:17', '2026-01-12 10:42:17', NULL),
(658, NULL, 'Ahmed El-Said Ibrahim Ali El-Hady', 'pgb8z60742', NULL, NULL, '$2y$12$YnYdtwoNSnsHWvVItL.OcuREFOD7bYors2dlTDcnYm2Var1a7fadu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:17', '2026-01-12 10:42:17', NULL),
(659, NULL, 'Adam Abdulaziz Shami Mohamed', 'padc567644', NULL, NULL, '$2y$12$Q6dmqH1ve7xmpZS8E8rY4OBVm/EjUkmcpsGLiAOx8yiedQmBVY9e.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:18', '2026-01-12 10:42:18', NULL),
(660, NULL, 'Adam Omar Hussein Ibrahim Al-Shafi\'i', 'pkvni28156', NULL, NULL, '$2y$12$AoKb7bahqb7dybeOmPfMt.d.0NTPvlSBTV2XRi2XEP1/2QgFQW0aq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:18', '2026-01-12 10:42:18', NULL),
(661, NULL, 'Amin Mohamed Ibrahim Omar Khidr', 'pou3z91178', NULL, NULL, '$2y$12$Qir4RR1R8hKKiHE6Nv3yvOdZVvOrJ2wHak0i242eyuMg2lv.vfXBK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:18', '2026-01-12 10:42:18', NULL),
(662, NULL, 'Anas Reda Fouad Al-Sayed Al-Shorbagy', 'p5wot43217', NULL, NULL, '$2y$12$12YydrFSP6R8TimjihdElezmTG4jWX/Fd6/cND/NqtXPZycX0PSyC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:18', '2026-01-12 10:42:18', NULL),
(663, NULL, 'Bassam Ibrahim Jamil Bin Hamad', 'p3upq87335', NULL, NULL, '$2y$12$MS3UdbkmGxYfX0Q13OKx7.X7s4aYEHiSpocozQi62rFwQRNLponZW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:18', '2026-01-12 10:42:18', NULL),
(664, NULL, 'Hassan Bassam Hassan Abu Ali', 'pe9wl97613', NULL, NULL, '$2y$12$kPuHRyIoQcJaEOKYL9.mreG6ik9YFOeY9McMdxvZwsi05JEabiVR6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:19', '2026-01-12 10:42:19', NULL),
(665, NULL, 'Hamza Osama Shawky Fawzy Ahmed', 'pckvd43657', NULL, NULL, '$2y$12$svZ5CKhAZObuS5flBEH2ruFNj9Gig3EO.2C4GZXgS6VZeYOMSwsL.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:19', '2026-01-12 10:42:19', NULL),
(666, NULL, 'Khalid Basem Khalid Bin Mahfouz', 'p5fcn86500', NULL, NULL, '$2y$12$wrZ5doy6tO2WSpwc8NRPnO.z/3sGNPaP4iVk/gclI84M9uE5lat4e', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:19', '2026-01-12 10:42:19', NULL),
(667, NULL, 'Rayan Ibrahim Suleiman Bin Abdullah Al-Omari', 'pyfqt57803', NULL, NULL, '$2y$12$4Ju5zuURmyyIqFnSu3hKEeiLlAry6Tqrs8eviz9L7Xi29kmzSaKYi', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:19', '2026-01-12 10:42:19', NULL),
(668, NULL, 'Rayan Ahmed Mohamed Ibrahim Abualainin', 'pmfqu58971', NULL, NULL, '$2y$12$jZUPKSKUZCJW4.76LfQxJODfUqMdtvgwaaZP1/uQHsbpQ0ytC0djK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:20', '2026-01-12 10:42:20', NULL),
(669, NULL, 'Rayan Majed Abdullah Mohamed Badghish', 'pqynu59082', NULL, NULL, '$2y$12$eWEd4EtDaoiaEwyITrBEQO1sn/B6gxipL3A3Tl4ywCiq2u85UDQVS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:20', '2026-01-12 10:42:20', NULL),
(670, NULL, 'Ziyad Abdul-Rahman Mohammed Sadiq Ghalib', 'pkv1r65906', NULL, NULL, '$2y$12$VMivUvho3DmsSlkFj22UYu.dWxDcDT9DqTGBuIB9Kh5ebidK6JKza', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:20', '2026-01-12 10:42:20', NULL),
(671, NULL, 'Saud Ahmed Abdullah Bin Rabiea', 'ppvze28058', NULL, NULL, '$2y$12$KYeaNbT.poclxBvk1GdDZO45QsBkGzAAEi.HAAV/kcZ1961/1sXZS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:20', '2026-01-12 10:42:20', NULL),
(672, NULL, 'Sultan Naif Sultan Al-Harbi', 'ptfx988893', NULL, NULL, '$2y$12$fbFkqs8/FJhnBU1LJQ0anu1AO6dvSkkn2OjpkQe5geQDQaIL.r/Pa', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:20', '2026-01-12 10:42:20', NULL),
(673, NULL, 'Suleiman Abdulaziz Suleiman Al-Hammad', 'p8c5r18591', NULL, NULL, '$2y$12$T2ge/P9w4ZMBIJWKVeV3I.tLnUeKFjP.p3628kw78cSdp1LJx4PbW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:21', '2026-01-12 10:42:21', NULL),
(674, NULL, 'Tariq Abdul Rahman Mahfouz Bin Mahfouz', 'pkdbr70877', NULL, NULL, '$2y$12$YlsHSqIizPusW6oz6esVH.KUWO7p4WDfvq9I0bdMzTPnuXE7r93uO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:21', '2026-01-12 10:42:21', NULL),
(675, NULL, 'Abdul Karim Khalid Ali Bin Mahfouz', 'pb89l32676', NULL, NULL, '$2y$12$wvQYu1xCxrbtQjderdOvDeZKidqK6ZXz0/MFzSVX08WUXPw/3TN6e', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:21', '2026-01-12 10:42:21', NULL),
(676, NULL, 'Abdullah Ali Abdullah Al-Amoudi', 'pytdx82837', NULL, NULL, '$2y$12$L/UEqJykYc9l39P1q95wpetjWfOn9x3M2L4FOd9vfntoUObrkmAbG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:21', '2026-01-12 10:42:21', NULL),
(677, NULL, 'Ali Mohamed Mohamed Taysir Haykal', 'pzmwa77407', NULL, NULL, '$2y$12$/1afvtUHWSyWAXJ2ahaymO/gTem2XlB2VZ7qcVGkymofpruvBqq/S', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:21', '2026-01-12 10:42:21', NULL),
(678, NULL, 'Omar Sultan Adnan Abdulrahim Akbar', 'pstez39568', NULL, NULL, '$2y$12$a7aOThBJ6D17jpXp1lxqqO6G1Lng02.xygy66nXBbeyqnxcBCrIKS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:22', '2026-01-12 10:42:22', NULL),
(679, NULL, 'Mohammed Fawzi Mohammed Al-Hadi', 'pmeaa84655', NULL, NULL, '$2y$12$NEZqWiChLDMK62.HlqpZbuQih409zxcrNBmEIwEwBQV1iyopPKPgO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:22', '2026-01-12 10:42:22', NULL),
(680, NULL, 'Mahmoud Mohamed Faraj Suleiman Faraj', 'ptdbf22043', NULL, NULL, '$2y$12$bIDYcA7T.cuLNEdrBxjFB.43RmWoMt92pZcpj0/NmhA0XPaXCRdSO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:22', '2026-01-12 10:42:22', NULL),
(681, NULL, 'Yazan Mohamed Bashar Hafiz Al-Ankashari', 'pymbz64877', NULL, NULL, '$2y$12$os0iCbiswEXWHpibbEiOReQSkYGVo8LigfRAc19IbsYLEndKQVHH.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:22', '2026-01-12 10:42:22', NULL),
(682, NULL, 'Yusuf Majed Saeed Ali Al-Amoudi', 'plkd379606', NULL, NULL, '$2y$12$uhRmFXepop.m9rP40OYr0ewEcwwhQ4h3TphKZ0IIrP3rrlVyxKQ/y', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:23', '2026-01-12 10:42:23', NULL),
(683, NULL, 'Ahmed Mohamed Ahmed El-Daly', 'ph5ph74406', NULL, NULL, '$2y$12$LrtZtOET6zdMUfFgdZvq7.VhmeOGPTNiUBfNU7I3p7GjAcB4qjKSC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:23', '2026-01-12 10:42:23', NULL),
(684, NULL, 'Ismail Reda Fouad Al-Sayed Al-Shorbagy', 'p6xg762825', NULL, NULL, '$2y$12$xj5yfj.BoSOkzkXwlqP7g.jf8PV0NqNh/koBr0RcUIEpKjOvNZ5zy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:23', '2026-01-12 10:42:23', NULL),
(685, NULL, 'Baraa Moamen Ayesh Salim Jaradah', 'ptrnd69275', NULL, NULL, '$2y$12$bOS3u78UWZX6U5hXS34huen9gmMUVnZ3QSOc80EBOjZOCoqa2VLea', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:23', '2026-01-12 10:42:23', NULL),
(686, NULL, 'Tamim Mazen Ahmed Al-Amoudi', 'px5oz19274', NULL, NULL, '$2y$12$7v5guBtSf.Lu/BZgIq2f5OlB9oy2VvRTrzKx5rI2xgEiK/GT02YkC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:23', '2026-01-12 10:42:23', NULL),
(687, NULL, 'Hamza Shady Mahmoud Ahmed Al-Masri', 'psqsi63412', NULL, NULL, '$2y$12$.P5KTh4gk1f3UbCfpgqL/OjwOqlckFUzwQmI6iX1WIobo14HWUCWm', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:24', '2026-01-12 10:42:24', NULL),
(688, NULL, 'Sultan Tarek Jabir Al-Qurashi', 'proqq26474', NULL, NULL, '$2y$12$RH0GKRPf/u3Cn5bgie6CCui2QVZKqWniveb2wbL5jaLZ.L7F53GDO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:24', '2026-01-12 10:42:24', NULL),
(689, NULL, 'Abdulaziz Ghassan Mohammed Al-Shehri', 'puyrj53286', NULL, NULL, '$2y$12$ZCs3FtgF64zYk6zmnNTojOssOqmbOIYmG.QHJwYDBvpZDLSONe7EW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:24', '2026-01-12 10:42:24', NULL),
(690, NULL, 'Abdulsalam Khaled Saad Saeed Al-Ghamdi', 'pofn051054', NULL, NULL, '$2y$12$F1aDnuc8ZgP3LpmD5oPOxuKM3mbw1sRrepUgLKT6Lp9eLVUF5XeK2', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:24', '2026-01-12 10:42:24', NULL),
(691, NULL, 'Abdullah Mazen Abdullah Bin Mahfouz', 'pptu918308', NULL, NULL, '$2y$12$PsrG0uPKzCZ46Y3mTgvFIOpqtOC5k3xrNXZB1W5ZAleMGrVPUF8AC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:24', '2026-01-12 10:42:24', NULL),
(692, NULL, 'Abdullah Mohammed Abdullah Salem Al-Amoudi', 'pvust25350', NULL, NULL, '$2y$12$bXNgnNpPcrpBBMYsFz4TjeDFpACMZEihXQh78838aAkfaFF6imzFu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:25', '2026-01-12 10:42:25', NULL),
(693, NULL, 'Ali Khaled Ali Bin Mahfouz', 'pdqfl34286', NULL, NULL, '$2y$12$U0WAiB9UY5v9SBRpbEuJs.r5hfsEUHOxIteWG.NsKwGF9je0Ol7z.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:25', '2026-01-12 10:42:25', NULL),
(694, NULL, 'Omar Abdul Rahman Mahfouz Bin Mahfouz', 'pjxbo21627', NULL, NULL, '$2y$12$ZAjMYAzz0HJBoXmxkCm7qe7At4x8wQpfOFs7w734fhXbPOaVXlMnK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:25', '2026-01-12 10:42:25', NULL),
(695, NULL, 'Omar Mohammed Fouad Ali Bin Mahfouz', 'pmnxz89666', NULL, NULL, '$2y$12$X7HdITthfZXIe3zYp8Ijquw70F8Mb5tGkU1C8impk6MvxJmgPxwca', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:25', '2026-01-12 10:42:25', NULL),
(696, NULL, 'Faisal Ahmed Sultan Abdo Al-Qurashi', 'p6wwc64739', NULL, NULL, '$2y$12$LzIpNK.SHhkRIB27zc9xH.xvf7NDQwoxBo4SJRXGpobXbtYOM3dmq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:25', '2026-01-12 10:42:25', NULL),
(697, NULL, 'Majed Majed Saeed Ali Al-Amoudi', 'pbbms75739', NULL, NULL, '$2y$12$QtOvgWpFM.RfPtajEFiyCeY0nS/PlhzpF6ciZTd7exrOjbRLyiPiC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:26', '2026-01-12 10:42:26', NULL),
(698, NULL, 'Malek Ahmed El-Sayed Ahmed El-Baz', 'pr78s82564', NULL, NULL, '$2y$12$IazmQTFlzbVMANfIgNZZbuKjEz5ZBgJjcxNXwiGF3..iGZ/Llwqlm', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:26', '2026-01-12 10:42:26', NULL),
(699, NULL, 'Mohamed Fawzi Mohamed El-Hadi', 'pn9hb91443', NULL, NULL, '$2y$12$ARLCuNXNAS55FNACmzrkl.95uE0wDhPsJ0sP1p.HDtjc0wKVgs6Rq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:26', '2026-01-12 10:42:26', NULL),
(700, NULL, 'Moataz Ammar Hussein Salem Baosman', 'pyowx31754', NULL, NULL, '$2y$12$tkFKlAZ99H2TDUGKMqEoAeR/U0hc3cvaLk0AGBNNogC4v9Vak7yfG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:26', '2026-01-12 10:42:26', NULL),
(701, NULL, 'Moath Mohammed Jamil Al-Saeed', 'panxj50964', NULL, NULL, '$2y$12$DjgLCl7rhLw/w2WMLNlBsuvSCEVq8fxGqLi2Pu97Yai1emdRVYU7.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:27', '2026-01-12 10:42:27', NULL),
(702, NULL, 'Yahya Majed Abdullah Mohammed Badghish', 'pdwbo13711', NULL, NULL, '$2y$12$TK7J37ZMqMekSE0XnvvTZehZn5HX8jr4xs.qi9gkHvtbEiOYYTcna', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:27', '2026-01-12 10:42:27', NULL),
(703, NULL, 'Youssef Mohammed Ahmed Al-Zubayri', 'pr1jn68473', NULL, NULL, '$2y$12$U7dQWJTgT0uj8OPl8ZWXre24bczGDtohmzppmZxGwI5O8v5BoqCpu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:27', '2026-01-12 10:42:27', NULL),
(704, NULL, 'Ibrahim Reda Waddah Mohamed Al-Khamash', 'pzbxb70383', NULL, NULL, '$2y$12$t.Ja0Q9lTNC9vDaSkSUJ3un1snrhQGwUn17dSxqiP92lE9SvW734S', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:27', '2026-01-12 10:42:27', NULL),
(705, NULL, 'Ahmed Bassam Hassan Abu Ali', 'pdihl76756', NULL, NULL, '$2y$12$XMUO4vchsckIUZ.smmceeuqA87MtFdqhRFowMcWCJ3J2sUpLygOKO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:28', '2026-01-12 10:42:28', NULL),
(706, NULL, 'Ahmed Suleiman Abdullah Mohamed Al-Omari', 'prodf79699', NULL, NULL, '$2y$12$alQiQHu6YPtycWcsRTqHeeznjtT57teOa11z1EcksgRxFzTOrMQxu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:28', '2026-01-12 10:42:28', NULL),
(707, NULL, 'Adam Reda Fouad Al-Sayed Al-Shorbagy', 'povcg88446', NULL, NULL, '$2y$12$719K9dwv/0xlgZmy2o01NuPdOGMupnIvdwm.uBxSQsFYEWKi.2Ay6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:28', '2026-01-12 10:42:28', NULL),
(708, NULL, 'Elias Tarek Jubeir Al-Qurashi', 'pazft72588', NULL, NULL, '$2y$12$XL1QQ2BQOtEEHp6ItOvZuuDuE7fAaYqRAJ1BEKBFGhqC251//i4Oa', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:28', '2026-01-12 10:42:28', NULL),
(709, NULL, 'Anas Ayman Ahmed Abdulrahman Al-Shazly', 'pydtq96392', NULL, NULL, '$2y$12$ZIHgglTTLJjhmN/tWVv2F.I8wPGXdxLpvkgil13T6u0e/g72NHkOW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:28', '2026-01-12 10:42:28', NULL),
(710, NULL, 'Anas Mohamed Ali Hussein Sharafuddin', 'pbp5x21510', NULL, NULL, '$2y$12$VlWW9d/3csA.kl1jjI2MxO6UEonF6hk.y55kZ1EoRLm2JyYLIXaLe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:29', '2026-01-12 10:42:29', NULL),
(711, NULL, 'Basel Ahmed Wahid Hassan Ali', 'phhds30485', NULL, NULL, '$2y$12$QLeyjjoAu/N4Kq4Q1gfEbu4RBkKmWz4NWmNJGvNx3qwBGaa4XYxly', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:29', '2026-01-12 10:42:29', NULL),
(712, NULL, 'Khaled Fawzi Mohammed Al-Hadi', 'pomeb56790', NULL, NULL, '$2y$12$qUtvmYNgiEH2BSH52wQps.pkVKkLPR0T4p0CfdTh9HWYnI9NWJ4jS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:29', '2026-01-12 10:42:29', NULL),
(713, NULL, 'Rayan Ahmed Abdullah Bin Rabiea', 'p6vxc30306', NULL, NULL, '$2y$12$9KyntfQ2..hJcB6Y/sS35uEfGbnjs2hf9YudbkLr3ugqHzxUPAmv.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:29', '2026-01-12 10:42:29', NULL),
(714, NULL, 'Rayan Khaled Mohammed Sadiq Ghalib', 'p2tlf20310', NULL, NULL, '$2y$12$yIqLtnwmer6so6OIgMRmruj1Lb2FFyj5hus9yJ6HvuTCVxWszP5gW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:29', '2026-01-12 10:42:29', NULL),
(715, NULL, 'Rayan Mohamed Mohamed Taysir Haykal', 'pb81b47974', NULL, NULL, '$2y$12$F2vzq2DF.qINVCbuv/OIlOyppBwpmHxcay/Kc0FkVWSiQ7j07aeSq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:30', '2026-01-12 10:42:30', NULL),
(716, NULL, 'Saeed Sultan Ibrahim Khalil Rawas', 'p5nz454968', NULL, NULL, '$2y$12$CK7U8o/cu2NMSGn6IxAz2uHm56/kzqnYUb1mNVnvt4gVro/o45xre', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:30', '2026-01-12 10:42:30', NULL),
(717, NULL, 'Sultan Shady Mahmoud Ahmed Al-Masri', 'pjtpz77937', NULL, NULL, '$2y$12$wCmqdJxK2byntc5LF/tKTeirus7b/l.ODxEQDmhv0Pp9UJauCuEkG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:30', '2026-01-12 10:42:30', NULL),
(718, NULL, 'Sultan Mazen Ahmed Al-Amoudi', 'plpwj74208', NULL, NULL, '$2y$12$Y.Lrj7FWZi50xZTvL9vHwuZ7HWeOnXgWXOAzMugpgD4XDdEletuEG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:30', '2026-01-12 10:42:30', NULL),
(719, NULL, 'Seif Mohamed Faraj Suleiman Faraj', 'pagrj75471', NULL, NULL, '$2y$12$./VrjwH4PfpwV1dtqMjLQuvkv1MMMbg1yHb/GY4PnsuOeDH8EXZ1S', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:31', '2026-01-12 10:42:31', NULL),
(720, NULL, 'Abdul Rahman Mahfouz Bin Mahfouz', 'pjcqt42617', NULL, NULL, '$2y$12$CFDkh6E2Kcdo8wcIuC817.dU0v66RTgsI2BsqzNy1c0GZfhLHvWVe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:31', '2026-01-12 10:42:31', NULL),
(721, NULL, 'Abdullah Ibrahim Jamil Bin Hamad', 'pxcob68966', NULL, NULL, '$2y$12$jrpIZ311FbTtFINvvRovk.o0O5AfZKwuGVmZRPCbUg1Y58tlW76PW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:31', '2026-01-12 10:42:31', NULL),
(722, NULL, 'Abdullah Shady Jabr Shaaban Salem', 'p1xkj73712', NULL, NULL, '$2y$12$qOqxr2ovLuj/hGI/fcO6CuFiRp/o6/XwF.VFVCk25s8a4UyJVby9S', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:31', '2026-01-12 10:42:31', NULL),
(723, NULL, 'Ali Reda Waddah Mohamed Al-Khamash', 'p1omk64052', NULL, NULL, '$2y$12$xCGE4ZFsxCuXwIjYKXJ9IOCv7HqC7X7z2WmW47YWqIfdAz6Zv65qe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:31', '2026-01-12 10:42:31', NULL),
(724, NULL, 'Ali Mazen Abdullah Bin Mahfouz', 'ppaxb55065', NULL, NULL, '$2y$12$yevrls1Mv6IJKg11VAosbeqvDgmHC/iebdXxm8uaSOcnQK/AsL8U2', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:32', '2026-01-12 10:42:32', NULL),
(725, NULL, 'Omar Ahmed Ahmed Wahid Hassan Ali', 'pdnz776904', NULL, NULL, '$2y$12$sZvMfT7W95cYNe7DMuV24OjFTfkBlzUPJWj/I.L5/uxsZdjNUHDfK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:32', '2026-01-12 10:42:32', NULL),
(726, NULL, 'Mohamed Ibrahim Mohamed Alam Bah Bah', 'p9b1w20395', NULL, NULL, '$2y$12$/pGFvF6v6vXbj0iHcQps4u2/tRUeBNRxjT08RL.9s5A9czhchCf/e', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:32', '2026-01-12 10:42:32', NULL),
(727, NULL, 'Mohamed Ahmed Mohamed Ibrahim Abualainin', 'pdllf33395', NULL, NULL, '$2y$12$XbykR2YnQAWTu1aPiN2QVuHBWqZQ9yqca5ucZsBhCWYGkNP6lcXPq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:32', '2026-01-12 10:42:32', NULL),
(728, NULL, 'Mohamed Osama Shawky Fawzy Ahmed', 'pcmof15843', NULL, NULL, '$2y$12$EIXKbGiP6CQpboGMqdrI7.TviFOmJbdpxku5.PfCsodP5fUYS./JG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:32', '2026-01-12 10:42:32', NULL),
(729, NULL, 'Mohamed Ehab Mohamed Mamdouh Ahmed Amer', 'p1v3k36247', NULL, NULL, '$2y$12$qOSxjbioE9s1MKGr4ypY4u.P61hVeFbqsYqDYAiaMuA8rRh7XdO0S', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:33', '2026-01-12 10:42:33', NULL),
(730, NULL, 'Moataz Saber Abdelmagsoud Tawfiq', 'pabx130807', NULL, NULL, '$2y$12$K4CjAf6Td9VoRZJ5kDBVsekyZSpfGbqdlCjIv2GjgIzzknQRPCfru', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:33', '2026-01-12 10:42:33', NULL),
(731, NULL, 'Yasin Ahmed Hamza Mohamed Suleiman', 'pkckw61715', NULL, NULL, '$2y$12$qcsI3P/4TzFN9sjcdBltv.G5Ap08PvUSoe/UYPU/cS.SmUfXsQ6DC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:33', '2026-01-12 10:42:33', NULL),
(732, NULL, 'Ahmed Reda Waddah Mohamed Al-Khamash', 'pksna73337', NULL, NULL, '$2y$12$02qbtLRHVNJ6y5k9G21VAuhmxRZvUvWGv9fwioHtTvq1YuPhV2PxK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:33', '2026-01-12 10:42:33', NULL),
(733, NULL, 'Ahmed Shady Jabr Shaaban Salem', 'pma0060019', NULL, NULL, '$2y$12$s8Hyev33Q12YgtNHQXgdDO/s7FTKR0bT6XXlOmuwJl9.Qf4e0IUrG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:34', '2026-01-12 10:42:34', NULL),
(734, NULL, 'Ahmed Amr Sabry El-Sayed Metwally', 'p61ju24920', NULL, NULL, '$2y$12$3tzA5/KgTIY9IwsFDyUUr.82QAYZbGwRxIDwff6dxUaMbcJBOOgB6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:34', '2026-01-12 10:42:34', NULL),
(735, NULL, 'Ahmed Mohammed Jamil Al-Saeed', 'pervi68971', NULL, NULL, '$2y$12$189jbPdi6ePxrL5ysmJzJeklXVNsSc2rO/hzpTadNGjpteUQmK9lO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:34', '2026-01-12 10:42:34', NULL),
(736, NULL, 'Adam Mohamed Ihab Mohamed Mamdouh Ahmed Amer', 'pa7qa88281', NULL, NULL, '$2y$12$0ZMR7AThk9hmumcGExFE8OU047Srw1unYb99mAEMPEcfTTdW4/Iem', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:34', '2026-01-12 10:42:34', NULL),
(737, NULL, 'Ibrahim Amr Mohamed Hussein Al-Ghazali', 'pyrur41986', NULL, NULL, '$2y$12$YnI4JCQw00YglkhmSrYlMukoigeK9PksmSCsddtUKWXq2Dn75bmQq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:34', '2026-01-12 10:42:34', NULL),
(738, NULL, 'Anas Ibrahim Jamil Bin Hamad', 'p1ual52572', NULL, NULL, '$2y$12$/aZlQS8pa3mn6ydr6q3fgeSOajJQ3Uy2mnL6qnXix2fEZn4yJIOuW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:35', '2026-01-12 10:42:35', NULL),
(739, NULL, 'Baraa Mohamed Ahmed El-Daly', 'psw4i21802', NULL, NULL, '$2y$12$c5t8SSEICEoOoT0YMVlCee0aPMFXyxiys8Cxddzy3ZuB2DmqiDGV2', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:35', '2026-01-12 10:42:35', NULL),
(740, NULL, 'Khaled Mazen Abdullah Bin Mahfouz', 'pmjtu86206', NULL, NULL, '$2y$12$KhW4EI3k36wnhhl8HkinKeUYSwo/Xm8DE0ZmsaZF6W4815nRm6pR.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:35', '2026-01-12 10:42:35', NULL),
(741, NULL, 'Reda Moamen Ayesh Salim Jaradah', 'plmjn92422', NULL, NULL, '$2y$12$IM1vOvEyxepzFG.VS2bS..48E8o6pPyV7zIwjKSqENZgRQfsgbI4O', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:35', '2026-01-12 10:42:35', NULL),
(742, NULL, 'Saud Sultan Adnan Abdulrahim Akbar', 'p90tc57404', NULL, NULL, '$2y$12$O3zE9CXftlME44hf5Wr3s.3vK1vFs45fBjemz40K.BUY3GvMLMurW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:35', '2026-01-12 10:42:35', NULL),
(743, NULL, 'Sultan Ahmed Sultan Abdo Al-Qurashi', 'pwqvn81759', NULL, NULL, '$2y$12$euWmFdiKDOqHBh7o1McCXOV6Qc3f5ANTkolmeA85dgkykvZV/eR6m', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:36', '2026-01-12 10:42:36', NULL),
(744, NULL, 'Salman Naif Sultan Al-Harbi', 'pryut86392', NULL, NULL, '$2y$12$7hSpt3r0OM7JAGWroZak4.lHc8erAdNInEHd8zToi.Yku4a3scJRa', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:36', '2026-01-12 10:42:36', NULL),
(745, NULL, 'Abdul Rahman Khaled Ali Bin Mahfouz', 'pdi1h77846', NULL, NULL, '$2y$12$JQJupBFh0bIbEI5ygBAhYOGl5bCZdUAYCxqaD2wytEVbIn3yZ1d/u', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:36', '2026-01-12 10:42:36', NULL),
(746, NULL, 'Abdullah Mazen Ahmed Al-Amoudi', 'pymxg65997', NULL, NULL, '$2y$12$D6dWIxyAVFyru2MSOQ/Kp.UgQ4w8moaMs834.zz8M8BDHRcdeBDwS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:36', '2026-01-12 10:42:36', NULL),
(747, NULL, 'Ali Khaled Mohammed Saleh Al-Malki', 'pwdgs83863', NULL, NULL, '$2y$12$/QZBcBBO0YzFrTLs9KxJR.8kZqhGDdbaJxI.wEHIN2KtjhVt60uMm', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:37', '2026-01-12 10:42:37', NULL),
(748, NULL, 'Omar Reda Fouad Al-Sayed Al-Shorbagy', 'p0rpv21697', NULL, NULL, '$2y$12$p8Bcmb/HHzrJqRgdoKyHY.uNUzIw4AEbbMCPSXezfFctuUluZ12V2', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:37', '2026-01-12 10:42:37', NULL),
(749, NULL, 'Mohamed Ihab Abdelfattah Ali Ghazi', 'pshvn36530', NULL, NULL, '$2y$12$zJl6YhWSgkU/y3wSdma5VuHRLCc1G.Hv4QpJAxPZFhVxFuEh.zd2y', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:37', '2026-01-12 10:42:37', NULL),
(750, NULL, 'Mohamed Basem Khalid Bin Mahfouz', 'po1qw15929', NULL, NULL, '$2y$12$IBuEkwav.XuEHfMBUry0xeLCwJkns2xG9EJLwhx15MOUcMZBYA1Ja', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:37', '2026-01-12 10:42:37', NULL),
(751, NULL, 'Mohamed Shady Mahmoud Ahmed Al-Masri', 'pgbta58635', NULL, NULL, '$2y$12$MvS8H/.ZDoGk9DsZd7LH0unw7PBrRr7HhY2oWPIS4zZ/9o/aKeL8q', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:37', '2026-01-12 10:42:37', NULL),
(752, NULL, 'Moataz Mohamed Bashar Hafiz Al-Ankashari', 'pltqj41250', NULL, NULL, '$2y$12$AzR3K795BHxRAvzPSsHHwOTMp2AJcuqXNi0/rRqJlS2G7i.9nL6Ri', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:38', '2026-01-12 10:42:38', NULL),
(753, NULL, 'Yahya Majed Saeed Ali Al-Amoudi', 'pioua25779', NULL, NULL, '$2y$12$OMF8yGR0edBh6y9ETssGIOxl36l6bwPDuTPNDdU6Au.aNkBCYz3K6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:38', '2026-01-12 10:42:38', NULL),
(754, NULL, 'Yassin Ahmed Hamza Mohamed Suleiman', 'p2ayw90956', NULL, NULL, '$2y$12$WEhfZkVi6J6rsKPeIb/mcuhak.ETpRtl9VTBH0R1irzlD9yirLLR.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:38', '2026-01-12 10:42:38', NULL),
(755, NULL, 'Youssef Mohamed Hussein Mohamed Ghazali', 'posof22393', NULL, NULL, '$2y$12$4Fc7AQ6TAwL.2qT.8Kq1ueRJq2Zi5HBvMZM6mLbS.66jgjM6dDPOK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:38', '2026-01-12 10:42:38', NULL),
(756, NULL, 'Youssef Hani Saeed Suleiman Dahdoolan', 'pz6jb16780', NULL, NULL, '$2y$12$wCXnKkfc8/AstuPZPHzwM.jPinjA9iZSA78Z0dDPpI4SaGrZzaM8y', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:38', '2026-01-12 10:42:38', NULL),
(757, NULL, 'Ahmed Ibrahim Ahmed Mohamed Khalil', 'pf5ly82414', NULL, NULL, '$2y$12$0ImfvLNPUcyB698Oll7B7eWwzuAxGY0KfQFyQreQ4VVkEJdqQi8mu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:39', '2026-01-12 10:42:39', NULL);
INSERT INTO `users` (`id`, `school_id`, `name`, `email`, `email_verified`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `role`, `first_login`, `last_login`, `last_active`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(758, NULL, 'Ahmed Tarek Jubeir Al-Qurashi', 'psvea41129', NULL, NULL, '$2y$12$PcNnxun1CsrWHrNtqjPMP.vc/WMvYoKMYvNUJbEy82mA37SrCRoEi', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:39', '2026-01-12 10:42:39', NULL),
(759, NULL, 'Ahmed Mohamed Ahmed Mohamed Al-Zubayri', 'ptk7v63984', NULL, NULL, '$2y$12$a/pfUrBjALz2rJn84hUQGuMrpkgo7dHFb49niZjNm3aUQjku2.bzG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:39', '2026-01-12 10:42:39', NULL),
(760, NULL, 'Ahmed Majed Saeed Ali Al-Amoudi', 'pma1r47738', NULL, NULL, '$2y$12$LzGt4OHmuy0Q4GaCKoqLVOQqk.zoPhzPiBp5cJfx9.aHgVjw1uECW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:39', '2026-01-12 10:42:39', NULL),
(761, NULL, 'Anas Osama Shawky Fawzy Ahmed', 'pmagd88565', NULL, NULL, '$2y$12$d87v7L6PwKBEitOrsaz/1e3OYgAjQ19Yt3yAfjT5rpZzAVNNFf0p6', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:40', '2026-01-12 10:42:40', NULL),
(762, NULL, 'Hamza Mazen Ahmed Al-Amoudi', 'ppil259601', NULL, NULL, '$2y$12$OxRyc9QAiS2h5N1T2mjXdeNkmbN/o6s8PqvtHSy5uz18LGOLgoSSK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:40', '2026-01-12 10:42:40', NULL),
(763, NULL, 'Khaled Khaled Ahmed Ali Bin Mahfouz', 'pvgix15642', NULL, NULL, '$2y$12$LY6ICes5N8ZgBZnDVzhRaOVIjr/6wXWDcj/Q.N7WhG1ied/NWGo76', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:40', '2026-01-12 10:42:40', NULL),
(764, NULL, 'Rayan Amr Sabry El-Sayed Metwally', 'phrdv61007', NULL, NULL, '$2y$12$zgCqLosVkIV5yAgro/a5zuzTlIpOMKA9/qxg4Qdn.tl4/GxL3OjPW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:40', '2026-01-12 10:42:40', NULL),
(765, NULL, 'Rayan Moamen Ayesh Salim Jaradah', 'ppzk916153', NULL, NULL, '$2y$12$zHhaxkpahFCy3OGp1uR42O.wOtmsy2Yele.iIGhXl6nupcb.D5aGG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:40', '2026-01-12 10:42:40', NULL),
(766, NULL, 'Saif Bassam Hassan Abu Ali', 'pgbkd93547', NULL, NULL, '$2y$12$4w8k0NttBN.6YNVHj0dIB.vU2RQ/yYfjVwQbovdErGUFsEcq11nqK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:41', '2026-01-12 10:42:41', NULL),
(767, NULL, 'Abdulaziz Reda Waddah Mohamed Al-Khamash', 'pbue871751', NULL, NULL, '$2y$12$Uo2mzf9Xj3bCIlaWaVLCIOgxqHpJmbcs5EmUy909OdkUgHuMtRi.O', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:41', '2026-01-12 10:42:41', NULL),
(768, NULL, 'Abdulaziz Mazen Abdullah Bin Mahfouz', 'p48xv46860', NULL, NULL, '$2y$12$9hUDQFUMssXde5IAqk9A/OJM8Rmdsmdynrpye4CC.3H.IsGe6/7le', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:41', '2026-01-12 10:42:41', NULL),
(769, NULL, 'Abdulrahman Ahmed Wahid Hassan Ali', 'pukix26786', NULL, NULL, '$2y$12$NzWCGwHj/FZLOOOGDOFTIOpGoxtqpPA9QDwc25srCw8K.R1HDpwcm', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:41', '2026-01-12 10:42:41', NULL),
(770, NULL, 'Abdullah Shady Mahmoud Ahmed Al-Masri', 'pxxgy89299', NULL, NULL, '$2y$12$9zyXvNPZuT/x5eMLfXrSCeZHmEbGOQPWcnxuPKAj5H6o4T8I63xGC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:41', '2026-01-12 10:42:41', NULL),
(771, NULL, 'Ali Khaled Ali Bin Mahfouz', 'pkfsl96236', NULL, NULL, '$2y$12$invQgoBwTl6h95K36gEn1uogihEwtN5E6.gydm0ew9J2DM6lz/YGe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:42', '2026-01-12 10:42:42', NULL),
(772, NULL, 'Omar Amr Mohamed Hussein Al-Ghazali', 'pkggd69414', NULL, NULL, '$2y$12$XbnlqpDoPJa7DjXqBUZ/Yu4GrkZ6W9O.Lk2HkG5zEfAvEo.BogwNu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:42', '2026-01-12 10:42:42', NULL),
(773, NULL, 'Omar Mohamed Faraj Suleiman Faraj', 'plukt56283', NULL, NULL, '$2y$12$JVn4K0ksCrhCjac70lLPpOyysbXpVBGtlb8u2QtUNXGwMevBLYYsm', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:42', '2026-01-12 10:42:42', NULL),
(774, NULL, 'Mohamed Reda Fouad Al-Sayed Al-Shorbagy', 'px8gy26049', NULL, NULL, '$2y$12$tvnzqi6tuF0LVXl2lMwWZudqe7mLH3bvYcT8M5OSQxf2vWN8IXoIy', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:42', '2026-01-12 10:42:42', NULL),
(775, NULL, 'Mohamed Fawzi Mohammed Al-Hadi', 'ps4nb86158', NULL, NULL, '$2y$12$pRnM/G.b4dckAh.mcAiy4.4qcygDA4ZrtnPx.n55huoQTjlz0jdv.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:42', '2026-01-12 10:42:42', NULL),
(776, NULL, 'Mohamed Moataz Ammar Hussein Salem Baosman', 'pr1fu77509', NULL, NULL, '$2y$12$2QMq5iMEfrNb.QlZRI0w5eEZ9M5eUVI0U4KSdMoP99pvfbM845Kkq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:43', '2026-01-12 10:42:43', NULL),
(777, NULL, 'Mahmoud Sayed Mahmoud Mostafa El-Zoghbi', 'pbeux28390', NULL, NULL, '$2y$12$e4VJt51CuOwHn7/zhgike.54KQtTB5u18uAvessuY2pVPjpJ/qRKu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:43', '2026-01-12 10:42:43', NULL),
(778, NULL, 'Muath Sultan Ali Al-Zahrani', 'pjodp84469', NULL, NULL, '$2y$12$gTgvM.NxHoEyANT.t1PyS..sCYO/RB6qfXypUdKOGVi6NdPT5.2C.', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:43', '2026-01-12 10:42:43', NULL),
(779, NULL, 'Yahya Mazen Abdullah Bin Mahfouz', 'pwoio25525', NULL, NULL, '$2y$12$1jUQxaT2.oenRgMnIQj7weYpWeK./gWZERG7pfBMelVCLMy9pw2xW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:43', '2026-01-12 10:42:43', NULL),
(780, NULL, 'Yaseen Majed Abdullah Mohamed Badghish', 'p1pha65105', NULL, NULL, '$2y$12$ZhZ.uTlHKH8ABIXerr5Oi.rhIoLKYRrS5VSd6hr8xCoI.KjqjjHOK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:44', '2026-01-12 10:42:44', NULL),
(781, NULL, 'Youssef Ahmed Hamza Mohamed Suleiman', 'pw19x96971', NULL, NULL, '$2y$12$ylCVZ0LcRd.d3JkQIqNM7OadvDZe75iQzO2kN2p8nO7G1sf/1yT7q', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:44', '2026-01-12 10:42:44', NULL),
(782, NULL, 'Ibrahim Majed Saeed Ali Al-Amoudi', 'p0ihf87539', NULL, NULL, '$2y$12$pm3VLCqmeZie96Ctqi.QN.Toej96.xXBp46eb4UH6sE7CDCsqKUsa', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:44', '2026-01-12 10:42:44', NULL),
(783, NULL, 'Ahmed Amr Mohamed Hussein Al-Ghazali', 'pkr2f44713', NULL, NULL, '$2y$12$PtwmjUNB/4x0laiwncFKres7ZFPcAaRFIBTmWft2x/G7/zfEnOoE2', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:44', '2026-01-12 10:42:44', NULL),
(784, NULL, 'Adam Amr Sabry El-Sayed Metwally', 'ppwtr10324', NULL, NULL, '$2y$12$XnvXq3lb2zURTTx7TyoCKeSv0FFHDJO00a5H7HSSM6K9nLI0Nkony', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:44', '2026-01-12 10:42:44', NULL),
(785, NULL, 'Anas Mohamed Ihab Mohamed Mamdouh Ahmed Amer', 'p52kx99558', NULL, NULL, '$2y$12$u62FWexoCnXSYZOV.NNPEuQo8tKwDNVX3pdPgHEmDnzGZ0j8JuE4u', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:45', '2026-01-12 10:42:45', NULL),
(786, NULL, 'Khaled Reda Waddah Mohamed Al-Khamash', 'p9luw86009', NULL, NULL, '$2y$12$QDCKsHqCi/1yHZVqOAoEv.MQ6FEugOhTb.4au4ANnPjvE3FypOrlm', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:45', '2026-01-12 10:42:45', NULL),
(787, NULL, 'Reda Moamen Ayesh Salim Jaradah', 'pu6dz28339', NULL, NULL, '$2y$12$H2Y/xxiG7NsUleQ2d3XhkulKvTSfQR2VOFvudFTyMV6H.OZ2yxWfG', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:45', '2026-01-12 10:42:45', NULL),
(788, NULL, 'Sultan Mazen Abdullah Bin Mahfouz', 'pe1nq43326', NULL, NULL, '$2y$12$hXNMskK0.ebDLekFP9IApeiOheQfcfRDcW5183abTW4CXRnbvbAjO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:45', '2026-01-12 10:42:45', NULL),
(789, NULL, 'Seif Ahmed Sultan Abdo Al-Qurashi', 'pp8cm44799', NULL, NULL, '$2y$12$NveKfbKDrhtXlTE9yxGi7uHShHp51ZbjlmI7ejwicB1Hz7E2fWipC', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:46', '2026-01-12 10:42:46', NULL),
(790, NULL, 'Abdelaziz Ghassan Mohamed Al-Shehri', 'pn40i86836', NULL, NULL, '$2y$12$PfdbjEA3bHROmeaiwn2gk.I1aqHWkUEo3oXp20uEWCLEjt3Rt5APu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:46', '2026-01-12 10:42:46', NULL),
(791, NULL, 'Abdullah Khaled Ali Bin Mahfouz', 'pkcge53365', NULL, NULL, '$2y$12$kSBrESgFBt4BPeAHCvzWJ.MLiLR2q..vYTy9.EV0alDq2wjGxTnqO', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:46', '2026-01-12 10:42:46', NULL),
(792, NULL, 'Ali Khaled Mohammed Saleh Al-Malki', 'plljf13624', NULL, NULL, '$2y$12$kPPhNcuGS5./Xg2W4GuGEOI5MvLiklwPzju4Q0FP3oVFurPbHwgNq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:46', '2026-01-12 10:42:46', NULL),
(793, NULL, 'Omar Reda Fouad Al-Sayed Al-Shorbagy', 'pwbn679043', NULL, NULL, '$2y$12$qhXI7hz1JAUvxvkZzVzvheIjm5NAlit.AdKbrqmhylzQXRaBj1Ns2', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:46', '2026-01-12 10:42:46', NULL),
(794, NULL, 'Omar Mohamed Faraj Suleiman Faraj', 'pbpnt84897', NULL, NULL, '$2y$12$jPy7d2tmLk6SkHmnzDJyW.NrKfYewLqn9djDdUDaaMkKN8r.R9uoq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:47', '2026-01-12 10:42:47', NULL),
(795, NULL, 'Mohamed Basem Khalid Bin Mahfouz', 'ppeg160947', NULL, NULL, '$2y$12$Z6bdgFX.eMWiCqn4zfTEeO1bp3wPwxXJoaAK./foR.mHheiMWaW5y', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:47', '2026-01-12 10:42:47', NULL),
(796, NULL, 'Mohamed Shady Mahmoud Ahmed Al-Masri', 'pd7ms61142', NULL, NULL, '$2y$12$hGrbUcvF1CcV0CaJLPrEruL71znadzlQqOSkzCYp7pdQ9FtCxqa9C', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:47', '2026-01-12 10:42:47', NULL),
(797, NULL, 'Mahmoud Mohamed Faraj Suleiman Faraj', 'petmp80317', NULL, NULL, '$2y$12$pthzzu4r3N./xtwlUfhw6ev1zOJibf5wrz4lMDjuX74kkUEzRsSBe', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:47', '2026-01-12 10:42:47', NULL),
(798, NULL, 'Moataz Mohamed Bashar Hafiz Al-Ankashari', 'psmoa70332', NULL, NULL, '$2y$12$zj6yDsvwbRySjhjPx5NdyeX7rb1/CYbUwM2J3exqqHZLtFy5pwqLa', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:47', '2026-01-12 10:42:47', NULL),
(799, NULL, 'Yassin Ahmed Hamza Mohamed Suleiman', 'pnug243860', NULL, NULL, '$2y$12$8Dw8QzgO0aa4MvEyhP9ppucYRMCX3mAHgeakNkR9nYumn.11CDU46', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:48', '2026-01-12 10:42:48', NULL),
(800, NULL, 'Youssef Mohamed Hussein Mohamed Ghazali', 'ppbbv93472', NULL, NULL, '$2y$12$2gZ1V0azci6bx7pB/y9MK.3EuMOIESadA83Wc4dqfKE.lineqnPhq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:48', '2026-01-12 10:42:48', NULL),
(801, NULL, 'Youssef Mustafa Hassan Yahya Al-Yamani', 'pnxso92676', NULL, NULL, '$2y$12$d5toZCA1wHhSFaLJKpSq4eRYP/j9icO9JfquLLcQt1BY/6IW9U3tW', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:48', '2026-01-12 10:42:48', NULL),
(802, NULL, 'Mohammed Abdullah Mohammed Al Twaim', 'pdyri51742', NULL, NULL, '$2y$12$dCRdQOKQUpx4YDWnPE5OEOKwFs8R7PFUBqtPrmWL.8O4nebR8flpS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:48', '2026-01-12 10:42:48', NULL),
(803, NULL, 'Betal Nabil Atiyah Al-Khatabi', 'pcubr22718', NULL, NULL, '$2y$12$.Wt0RxxropRgFpil9zVfJ.DX.mw5VaL3OkWH7l6W.w//QhdWhB.dS', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:49', '2026-01-12 10:42:49', NULL),
(804, NULL, 'Begad Wael Fathi Ibrahim Mohamed', 'p50vs34518', NULL, NULL, '$2y$12$3rlO7S8qPleDDYg6a5DfzenT39Xh4eqdfsSm.tgOnv483IDgPlkBu', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:49', '2026-01-12 10:42:49', NULL),
(805, NULL, 'Abdelilah Khaled Saad Saeed Al-Ghamdi', 'pmdbf61457', NULL, NULL, '$2y$12$X1ZEsfvLxRFg9RnmrqWtIu8FocsOWwiMgqbBWg8S.oVOJTb48gKbq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:49', '2026-01-12 10:42:49', NULL),
(806, NULL, 'Ali Hassan Ali Hassan Ali', 'pqv4q32039', NULL, NULL, '$2y$12$1QrMT2OqPn44sZXzERpy8eueUTH2IFeHTQ.bLoTv5kSbYmCUlSF.C', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:49', '2026-01-12 10:42:49', NULL),
(807, NULL, 'Omar Ahmed Mohamed Ibrahim Abualainin', 'pjhjg32004', NULL, NULL, '$2y$12$UC8UjqtRSjn6.5v6CCSXNOTxJUwY/LleSh6X/DMzXM1rOAAPsu3Lq', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:49', '2026-01-12 10:42:49', NULL),
(808, NULL, 'Malek Mohamed Sayed Abdel Hamid Al-Rifa’i', 'ps6jk32466', NULL, NULL, '$2y$12$nL92QxLuq3xzpV7FAEcLAu4f0mHITNASDusfyEQ5DDgW/mNO29kvK', NULL, NULL, NULL, NULL, NULL, NULL, 'student', 1, NULL, NULL, 0, '2026-01-12 10:42:50', '2026-01-12 10:42:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_messages`
--

CREATE TABLE `user_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_message_recipients`
--

CREATE TABLE `user_message_recipients` (
  `id` bigint UNSIGNED NOT NULL,
  `user_message_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weekly_plans`
--

CREATE TABLE `weekly_plans` (
  `id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `semester_number` tinyint NOT NULL COMMENT '1 or 2',
  `week_number` tinyint NOT NULL COMMENT '1-18 or 1-36',
  `copy_id` bigint UNSIGNED NOT NULL,
  `schedule_id` bigint UNSIGNED NOT NULL,
  `cw` text COLLATE utf8mb4_unicode_ci,
  `hw` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weekly_plan_sessions`
--

CREATE TABLE `weekly_plan_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `weekly_plan_id` bigint UNSIGNED NOT NULL,
  `session_index` int NOT NULL COMMENT 'Order within week (1, 2, 3...)',
  `period_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Format: academic_year.semester.week.day',
  `type` enum('lesson','quiz','exam','extra','note') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL COMMENT 'Flexible storage for materials, zoom links, homework, skill tags',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD KEY `academic_years_school_id_foreign` (`school_id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `behaviors`
--
ALTER TABLE `behaviors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_behavior_per_year` (`school_id`,`year_id`,`name`),
  ADD KEY `behaviors_year_id_foreign` (`year_id`);

--
-- Indexes for table `behavior_incidents`
--
ALTER TABLE `behavior_incidents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `behavior_incidents_uuid_unique` (`uuid`),
  ADD KEY `behavior_incidents_created_by_foreign` (`created_by`),
  ADD KEY `behavior_incidents_reported_by_foreign` (`reported_by`),
  ADD KEY `behavior_incidents_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `behavior_incidents_parent_notified_by_foreign` (`parent_notified_by`),
  ADD KEY `behavior_incidents_school_id_occurred_at_index` (`school_id`,`occurred_at`),
  ADD KEY `behavior_incidents_student_id_occurred_at_index` (`student_id`,`occurred_at`),
  ADD KEY `behavior_incidents_classroom_id_occurred_at_index` (`classroom_id`,`occurred_at`),
  ADD KEY `behavior_incidents_school_id_severity_occurred_at_index` (`school_id`,`severity`,`occurred_at`),
  ADD KEY `behavior_incidents_school_id_status_occurred_at_index` (`school_id`,`status`,`occurred_at`),
  ADD KEY `behavior_incidents_grade_occurred_at_index` (`grade`,`occurred_at`),
  ADD KEY `behavior_incidents_school_year_id_occurred_at_index` (`school_year_id`,`occurred_at`),
  ADD KEY `behavior_incidents_school_id_critical_alert_occurred_at_index` (`school_id`,`critical_alert`,`occurred_at`),
  ADD KEY `behavior_incidents_student_name_index` (`student_name`),
  ADD KEY `behavior_incidents_grade_index` (`grade`),
  ADD KEY `behavior_incidents_occurred_at_index` (`occurred_at`),
  ADD KEY `behavior_incidents_period_code_index` (`period_code`),
  ADD KEY `behavior_incidents_primary_behavior_code_index` (`primary_behavior_code`),
  ADD KEY `behavior_incidents_primary_location_code_index` (`primary_location_code`),
  ADD KEY `behavior_incidents_severity_index` (`severity`),
  ADD KEY `behavior_incidents_status_index` (`status`),
  ADD KEY `behavior_incidents_follow_up_needed_index` (`follow_up_needed`),
  ADD KEY `behavior_incidents_critical_alert_index` (`critical_alert`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `calendars_date_school_id_unique` (`date`,`school_id`),
  ADD KEY `calendars_semester_id_foreign` (`semester_id`),
  ADD KEY `calendars_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `calendars_school_id_foreign` (`school_id`),
  ADD KEY `calendars_date_index` (`date`),
  ADD KEY `calendars_status_index` (`status`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classrooms_stage_id_foreign` (`stage_id`),
  ADD KEY `classrooms_grade_id_foreign` (`grade_id`),
  ADD KEY `classrooms_school_id_foreign` (`school_id`);

--
-- Indexes for table `classroom_records`
--
ALTER TABLE `classroom_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classroom_records_school_id_foreign` (`school_id`),
  ADD KEY `classroom_records_year_id_foreign` (`year_id`),
  ADD KEY `classroom_records_teacher_id_foreign` (`teacher_id`),
  ADD KEY `classroom_records_classroom_id_foreign` (`classroom_id`),
  ADD KEY `classroom_records_student_id_foreign` (`student_id`),
  ADD KEY `classroom_records_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `classroom_subject_teachers`
--
ALTER TABLE `classroom_subject_teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment_idx` (`school_id`,`academic_year_id`,`classroom_id`,`subject_id`,`teacher_id`),
  ADD KEY `classroom_subject_teachers_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `classroom_subject_teachers_subject_id_foreign` (`subject_id`),
  ADD KEY `school_academic_year_idx` (`school_id`,`academic_year_id`),
  ADD KEY `teacher_school_idx` (`teacher_id`,`school_id`),
  ADD KEY `classroom_subject_idx` (`classroom_id`,`subject_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation_user`
--
ALTER TABLE `conversation_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `conversation_user_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  ADD KEY `conversation_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_created_by_foreign` (`created_by`);

--
-- Indexes for table `course_lessons`
--
ALTER TABLE `course_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_lessons_course_section_id_foreign` (`course_section_id`),
  ADD KEY `course_lessons_created_by_foreign` (`created_by`);

--
-- Indexes for table `course_levels`
--
ALTER TABLE `course_levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_levels_course_id_foreign` (`course_id`),
  ADD KEY `course_levels_created_by_foreign` (`created_by`);

--
-- Indexes for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_sections_course_level_id_foreign` (`course_level_id`),
  ADD KEY `course_sections_created_by_foreign` (`created_by`);

--
-- Indexes for table `course_teacher_assignments`
--
ALTER TABLE `course_teacher_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_teacher_assignments_course_id_teacher_id_unique` (`course_id`,`teacher_id`),
  ADD KEY `course_teacher_assignments_teacher_id_foreign` (`teacher_id`),
  ADD KEY `course_teacher_assignments_assigned_by_foreign` (`assigned_by`);

--
-- Indexes for table `curricula`
--
ALTER TABLE `curricula`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `curricula_school_id_grade_id_subject_id_name_unique` (`school_id`,`grade_id`,`subject_id`,`name`),
  ADD KEY `curricula_grade_id_foreign` (`grade_id`),
  ADD KEY `curricula_subject_id_foreign` (`subject_id`),
  ADD KEY `curricula_school_id_active_index` (`school_id`,`active`),
  ADD KEY `curricula_school_id_grade_id_subject_id_index` (`school_id`,`grade_id`,`subject_id`);

--
-- Indexes for table `curriculum_lessons`
--
ALTER TABLE `curriculum_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curriculum_lessons_topic_id_foreign` (`topic_id`),
  ADD KEY `curriculum_lessons_school_id_foreign` (`school_id`);

--
-- Indexes for table `curriculum_lesson_plans`
--
ALTER TABLE `curriculum_lesson_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curriculum_lesson_plans_curriculum_lesson_id_foreign` (`curriculum_lesson_id`),
  ADD KEY `curriculum_lesson_plans_subject_id_foreign` (`subject_id`),
  ADD KEY `curriculum_lesson_plans_grade_id_foreign` (`grade_id`),
  ADD KEY `curriculum_lesson_plans_classroom_id_foreign` (`classroom_id`),
  ADD KEY `curriculum_lesson_plans_teacher_id_foreign` (`teacher_id`),
  ADD KEY `curriculum_lesson_plans_school_id_teacher_id_index` (`school_id`,`teacher_id`),
  ADD KEY `curriculum_lesson_plans_school_id_classroom_id_index` (`school_id`,`classroom_id`),
  ADD KEY `curriculum_lesson_plans_school_id_status_index` (`school_id`,`status`),
  ADD KEY `curriculum_lesson_plans_planned_date_index` (`planned_date`);

--
-- Indexes for table `curriculum_maps`
--
ALTER TABLE `curriculum_maps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `curriculum_maps_unique` (`school_id`,`academic_year_id`,`subject_id`,`grade_id`,`teacher_id`),
  ADD KEY `curriculum_maps_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `curriculum_maps_subject_id_foreign` (`subject_id`),
  ADD KEY `curriculum_maps_grade_id_foreign` (`grade_id`),
  ADD KEY `curriculum_maps_teacher_id_foreign` (`teacher_id`),
  ADD KEY `curriculum_maps_curriculum_id_foreign` (`curriculum_id`),
  ADD KEY `curriculum_maps_school_id_academic_year_id_index` (`school_id`,`academic_year_id`),
  ADD KEY `curriculum_maps_school_id_teacher_id_index` (`school_id`,`teacher_id`),
  ADD KEY `curriculum_maps_school_id_status_index` (`school_id`,`status`);

--
-- Indexes for table `curriculum_topics`
--
ALTER TABLE `curriculum_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curriculum_topics_curriculum_id_foreign` (`curriculum_id`);

--
-- Indexes for table `documentations`
--
ALTER TABLE `documentations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documentations_author_id_foreign` (`author_id`);

--
-- Indexes for table `dp_daily_tasks`
--
ALTER TABLE `dp_daily_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dp_daily_tasks_user_id_foreign` (`user_id`),
  ADD KEY `dp_daily_tasks_dp_task_id_foreign` (`dp_task_id`);

--
-- Indexes for table `dp_focus_logs`
--
ALTER TABLE `dp_focus_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dp_focus_logs_user_id_foreign` (`user_id`),
  ADD KEY `dp_focus_logs_dp_daily_task_id_foreign` (`dp_daily_task_id`);

--
-- Indexes for table `dp_rewards`
--
ALTER TABLE `dp_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dp_rewards_user_id_foreign` (`user_id`);

--
-- Indexes for table `dp_tasks`
--
ALTER TABLE `dp_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dp_tasks_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grades_stage_id_foreign` (`stage_id`),
  ADD KEY `grades_school_id_foreign` (`school_id`);

--
-- Indexes for table `grade_subject`
--
ALTER TABLE `grade_subject`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grade_subject_grade_id_subject_id_unique` (`grade_id`,`subject_id`),
  ADD KEY `grade_subject_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `h_r_s`
--
ALTER TABLE `h_r_s`
  ADD PRIMARY KEY (`id`),
  ADD KEY `h_r_s_user_id_foreign` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lessons_curriculum_id_lesson_number_unique` (`curriculum_id`,`lesson_number`);

--
-- Indexes for table `lesson_plan_templates`
--
ALTER TABLE `lesson_plan_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_plan_templates_created_by_foreign` (`created_by`),
  ADD KEY `lesson_plan_templates_is_active_created_by_index` (`is_active`,`created_by`),
  ADD KEY `lesson_plan_templates_name_index` (`name`);

--
-- Indexes for table `lesson_practice_submissions`
--
ALTER TABLE `lesson_practice_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_practice_submissions_lesson_student_progress_id_index` (`lesson_student_progress_id`);

--
-- Indexes for table `lesson_presentations`
--
ALTER TABLE `lesson_presentations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_presentations_school_id_foreign` (`school_id`),
  ADD KEY `lesson_presentations_teacher_id_foreign` (`teacher_id`),
  ADD KEY `lesson_presentations_subject_id_foreign` (`subject_id`),
  ADD KEY `lesson_presentations_grade_id_subject_id_order_index` (`grade_id`,`subject_id`,`order`),
  ADD KEY `lesson_presentations_quiz_id_foreign` (`quiz_id`);

--
-- Indexes for table `lesson_presentation_slides`
--
ALTER TABLE `lesson_presentation_slides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_presentation_slides_lesson_presentation_id_foreign` (`lesson_presentation_id`);

--
-- Indexes for table `lesson_student_progress`
--
ALTER TABLE `lesson_student_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_student_progress_opened_by_teacher_id_foreign` (`opened_by_teacher_id`),
  ADD KEY `lesson_student_progress_lesson_presentation_id_student_id_index` (`lesson_presentation_id`,`student_id`),
  ADD KEY `lesson_student_progress_student_id_status_index` (`student_id`,`status`),
  ADD KEY `lesson_student_progress_lesson_presentation_id_status_index` (`lesson_presentation_id`,`status`),
  ADD KEY `lesson_student_progress_status_index` (`status`),
  ADD KEY `lesson_student_progress_color_status_index` (`color_status`),
  ADD KEY `lesson_student_progress_practice_score_index` (`practice_score`),
  ADD KEY `lesson_student_progress_quiz_passed_index` (`quiz_passed`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menus_parent_id_foreign` (`parent_id`),
  ADD KEY `menus_module_index` (`module`),
  ADD KEY `menus_role_specific_index` (`role_specific`),
  ADD KEY `menus_v2_enabled_index` (`v2_enabled`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_conversation_id_foreign` (`conversation_id`),
  ADD KEY `messages_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `myproject_tasks`
--
ALTER TABLE `myproject_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `myproject_tasks_status_index` (`status`),
  ADD KEY `myproject_tasks_priority_index` (`priority`),
  ADD KEY `myproject_tasks_due_date_index` (`due_date`),
  ADD KEY `myproject_tasks_created_at_index` (`created_at`),
  ADD KEY `myproject_tasks_parent_id_index` (`parent_id`),
  ADD KEY `myproject_tasks_sort_order_index` (`sort_order`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `period_activities`
--
ALTER TABLE `period_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `period_activities_calendar_id_foreign` (`calendar_id`),
  ADD KEY `period_activities_teacher_substitute_id_foreign` (`teacher_substitute_id`),
  ADD KEY `period_activities_created_by_foreign` (`created_by`),
  ADD KEY `period_activities_updated_by_foreign` (`updated_by`),
  ADD KEY `period_activities_schedule_id_calendar_id_index` (`schedule_id`,`calendar_id`),
  ADD KEY `period_activities_teacher_id_calendar_id_index` (`teacher_id`,`calendar_id`),
  ADD KEY `period_activities_period_status_index` (`period_status`);

--
-- Indexes for table `period_details`
--
ALTER TABLE `period_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `period_details_school_id_index` (`school_id`),
  ADD KEY `period_details_code_index` (`code`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pomodoro_sessions`
--
ALTER TABLE `pomodoro_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pomodoro_sessions_user_id_foreign` (`user_id`),
  ADD KEY `pomodoro_sessions_task_id_foreign` (`task_id`);

--
-- Indexes for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  ADD KEY `push_subscriptions_subscribable_type_subscribable_id_index` (`subscribable_type`,`subscribable_id`);

--
-- Indexes for table `qdrat_lessons`
--
ALTER TABLE `qdrat_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qdrat_lessons_lesson_category_id_foreign` (`lesson_category_id`),
  ADD KEY `qdrat_lessons_created_by_foreign` (`created_by`);

--
-- Indexes for table `qdrat_lesson_categories`
--
ALTER TABLE `qdrat_lesson_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qdrat_lesson_categories_created_by_foreign` (`created_by`);

--
-- Indexes for table `qdrat_questions`
--
ALTER TABLE `qdrat_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qdrat_questions_question_type_id_foreign` (`question_type_id`),
  ADD KEY `qdrat_questions_difficulty_level_id_foreign` (`difficulty_level_id`),
  ADD KEY `qdrat_questions_created_by_foreign` (`created_by`);

--
-- Indexes for table `qdrat_question_difficulties`
--
ALTER TABLE `qdrat_question_difficulties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qdrat_question_difficulties_created_by_foreign` (`created_by`);

--
-- Indexes for table `qdrat_question_types`
--
ALTER TABLE `qdrat_question_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qdrat_question_types_name_unique` (`name`);

--
-- Indexes for table `qdrat_skills`
--
ALTER TABLE `qdrat_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qdrat_skills_skill_level_id_foreign` (`skill_level_id`),
  ADD KEY `qdrat_skills_created_by_foreign` (`created_by`),
  ADD KEY `qdrat_skills_import_batch_index` (`import_batch`);

--
-- Indexes for table `qdrat_skill_levels`
--
ALTER TABLE `qdrat_skill_levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qdrat_skill_levels_created_by_foreign` (`created_by`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_question_type_id_index` (`question_type_id`),
  ADD KEY `questions_grade_id_subject_id_index` (`grade_id`,`subject_id`),
  ADD KEY `questions_status_index` (`status`),
  ADD KEY `questions_author_id_index` (`author_id`),
  ADD KEY `idx_questions_status_grade` (`status`,`grade_id`),
  ADD KEY `idx_questions_subject_difficulty` (`subject_id`,`difficulty_level`),
  ADD KEY `idx_questions_analytics` (`usage_count`,`avg_success_rate`),
  ADD KEY `idx_questions_bloom_level` (`bloom_level`),
  ADD KEY `idx_questions_difficulty_level` (`difficulty_level`);

--
-- Indexes for table `question_banks`
--
ALTER TABLE `question_banks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_banks_subject_id_foreign` (`subject_id`),
  ADD KEY `question_banks_curriculum_id_foreign` (`curriculum_id`),
  ADD KEY `question_banks_curriculum_lessons_id_foreign` (`curriculum_lessons_id`),
  ADD KEY `question_banks_school_id_subject_id_index` (`school_id`,`subject_id`),
  ADD KEY `question_banks_school_id_type_index` (`school_id`,`type`),
  ADD KEY `question_banks_school_id_difficulty_index` (`school_id`,`difficulty`),
  ADD KEY `question_banks_school_id_status_index` (`school_id`,`status`),
  ADD KEY `question_banks_created_by_id_index` (`created_by_id`);
ALTER TABLE `question_banks` ADD FULLTEXT KEY `question_banks_title_body_tags_fulltext` (`title`,`body`,`tags`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_options_question_id_index` (`question_id`),
  ADD KEY `idx_options_question_correct` (`question_id`,`is_correct`),
  ADD KEY `idx_options_question_order` (`question_id`,`order_index`);

--
-- Indexes for table `question_types`
--
ALTER TABLE `question_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `question_types_slug_unique` (`slug`),
  ADD KEY `question_types_slug_index` (`slug`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizzes_subject_id_foreign` (`subject_id`),
  ADD KEY `quizzes_grade_id_foreign` (`grade_id`),
  ADD KEY `quizzes_school_id_status_index` (`school_id`,`status`),
  ADD KEY `quizzes_created_by_id_index` (`created_by_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_attempts_user_id_index` (`user_id`),
  ADD KEY `quiz_attempts_completed_at_index` (`completed_at`),
  ADD KEY `idx_attempts_user_completed` (`user_id`,`completed_at`),
  ADD KEY `idx_attempts_user_started` (`user_id`,`started_at`),
  ADD KEY `idx_attempts_quiz` (`quiz_id`),
  ADD KEY `quiz_attempts_quiz_session_id_foreign` (`quiz_session_id`);

--
-- Indexes for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_attempt_answers_selected_option_id_foreign` (`selected_option_id`),
  ADD KEY `quiz_attempt_answers_attempt_id_index` (`attempt_id`),
  ADD KEY `quiz_attempt_answers_question_id_index` (`question_id`),
  ADD KEY `idx_answers_attempt_correct` (`attempt_id`,`is_correct`),
  ADD KEY `idx_answers_question_correct` (`question_id`,`is_correct`),
  ADD KEY `idx_answers_answered_at` (`answered_at`);

--
-- Indexes for table `quiz_question`
--
ALTER TABLE `quiz_question`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quiz_question_quiz_id_question_id_unique` (`quiz_id`,`question_id`),
  ADD KEY `quiz_question_question_id_foreign` (`question_id`),
  ADD KEY `quiz_question_order_index_index` (`order_index`);

--
-- Indexes for table `quiz_sessions`
--
ALTER TABLE `quiz_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quiz_sessions_access_code_unique` (`access_code`),
  ADD KEY `quiz_sessions_quiz_id_foreign` (`quiz_id`),
  ADD KEY `quiz_sessions_teacher_id_foreign` (`teacher_id`),
  ADD KEY `quiz_sessions_current_question_id_foreign` (`current_question_id`);

--
-- Indexes for table `quiz_session_participants`
--
ALTER TABLE `quiz_session_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_session_participants_quiz_session_id_foreign` (`quiz_session_id`),
  ADD KEY `quiz_session_participants_student_id_foreign` (`student_id`);

--
-- Indexes for table `qu_answers`
--
ALTER TABLE `qu_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qu_answers_qu_attempt_id_qu_question_id_unique` (`qu_attempt_id`,`qu_question_id`),
  ADD KEY `qu_answers_qu_question_id_foreign` (`qu_question_id`);

--
-- Indexes for table `qu_attempts`
--
ALTER TABLE `qu_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qu_attempts_user_id_foreign` (`user_id`),
  ADD KEY `qu_attempts_qu_exam_id_user_id_index` (`qu_exam_id`,`user_id`);

--
-- Indexes for table `qu_exams`
--
ALTER TABLE `qu_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qu_exams_subject_id_foreign` (`subject_id`),
  ADD KEY `qu_exams_created_by_foreign` (`created_by`);

--
-- Indexes for table `qu_exam_questions`
--
ALTER TABLE `qu_exam_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qu_exam_questions_qu_exam_id_qu_question_id_unique` (`qu_exam_id`,`qu_question_id`),
  ADD KEY `qu_exam_questions_qu_question_id_foreign` (`qu_question_id`);

--
-- Indexes for table `qu_questions`
--
ALTER TABLE `qu_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qu_questions_topic_id_foreign` (`topic_id`),
  ADD KEY `qu_questions_created_by_foreign` (`created_by`),
  ADD KEY `qu_questions_subject_id_topic_id_difficulty_bloom_level_index` (`subject_id`,`topic_id`,`difficulty`,`bloom_level`);

--
-- Indexes for table `resume_answers`
--
ALTER TABLE `resume_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resume_answers_user_id_foreign` (`user_id`),
  ADD KEY `resume_answers_question_id_foreign` (`question_id`),
  ADD KEY `resume_answers_average_rating_ratings_count_index` (`average_rating`,`ratings_count`),
  ADD KEY `resume_answers_likes_count_index` (`likes_count`),
  ADD KEY `resume_answers_is_featured_featured_at_index` (`is_featured`,`featured_at`);

--
-- Indexes for table `resume_answer_bookmarks`
--
ALTER TABLE `resume_answer_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resume_answer_bookmarks_user_id_answer_id_bookmark_type_unique` (`user_id`,`answer_id`,`bookmark_type`),
  ADD KEY `resume_answer_bookmarks_user_id_bookmark_type_index` (`user_id`,`bookmark_type`),
  ADD KEY `resume_answer_bookmarks_answer_id_index` (`answer_id`);

--
-- Indexes for table `resume_answer_likes`
--
ALTER TABLE `resume_answer_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resume_answer_likes_user_id_answer_id_unique` (`user_id`,`answer_id`),
  ADD KEY `resume_answer_likes_answer_id_index` (`answer_id`);

--
-- Indexes for table `resume_answer_ratings`
--
ALTER TABLE `resume_answer_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resume_answer_ratings_user_id_answer_id_unique` (`user_id`,`answer_id`),
  ADD KEY `resume_answer_ratings_answer_id_rating_index` (`answer_id`,`rating`);

--
-- Indexes for table `resume_answer_reports`
--
ALTER TABLE `resume_answer_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resume_answer_reports_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `resume_answer_reports_status_created_at_index` (`status`,`created_at`),
  ADD KEY `resume_answer_reports_answer_id_index` (`answer_id`),
  ADD KEY `resume_answer_reports_user_id_index` (`user_id`);

--
-- Indexes for table `resume_comment_likes`
--
ALTER TABLE `resume_comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resume_comment_likes_user_id_comment_id_unique` (`user_id`,`comment_id`),
  ADD KEY `resume_comment_likes_comment_id_index` (`comment_id`);

--
-- Indexes for table `resume_questions`
--
ALTER TABLE `resume_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resume_questions_user_id_foreign` (`user_id`);

--
-- Indexes for table `resume_question_comments`
--
ALTER TABLE `resume_question_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resume_question_comments_user_id_foreign` (`user_id`),
  ADD KEY `resume_question_comments_question_id_foreign` (`question_id`),
  ADD KEY `resume_question_comments_answer_id_foreign` (`answer_id`),
  ADD KEY `resume_question_comments_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `resume_themes`
--
ALTER TABLE `resume_themes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resume_themes_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedules_cst_id_foreign` (`cst_id`),
  ADD KEY `schedules_teacher_substitute_id_foreign` (`teacher_substitute_id`),
  ADD KEY `schedules_co_teacher_id_foreign` (`co_teacher_id`),
  ADD KEY `schedules_co_subject_id_foreign` (`co_subject_id`),
  ADD KEY `schedules_school_id_index` (`school_id`),
  ADD KEY `schedules_copy_id_active_index` (`copy_id`,`active`),
  ADD KEY `schedules_copy_id_cst_id_period_order_index` (`copy_id`,`cst_id`,`period_order`);

--
-- Indexes for table `schedule_copies`
--
ALTER TABLE `schedule_copies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_copies_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `schedule_copies_semester_id_foreign` (`semester_id`),
  ADD KEY `schedule_copies_created_by_foreign` (`created_by`),
  ADD KEY `schedule_copies_last_modified_by_foreign` (`last_modified_by`),
  ADD KEY `schedule_copies_school_id_academic_year_id_semester_id_index` (`school_id`,`academic_year_id`,`semester_id`),
  ADD KEY `schedule_copies_active_status_index` (`status`),
  ADD KEY `schedule_copies_copy_date_index` (`copy_date`);

--
-- Indexes for table `schedule_dailies`
--
ALTER TABLE `schedule_dailies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_dailies_schedule_id_foreign` (`schedule_id`),
  ADD KEY `schedule_dailies_teacher_substitute_id_foreign` (`teacher_substitute_id`),
  ADD KEY `schedule_dailies_schedule_copy_id_foreign` (`schedule_copy_id`);

--
-- Indexes for table `schedule_daily_records`
--
ALTER TABLE `schedule_daily_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_timings`
--
ALTER TABLE `schedule_timings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_timings_school_id_index` (`school_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schools_h_r_id_foreign` (`h_r_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `semesters_academic_year_id_semester_number_unique` (`academic_year_id`,`semester_number`),
  ADD KEY `semesters_school_id_foreign` (`school_id`);

--
-- Indexes for table `semester_tests`
--
ALTER TABLE `semester_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `semester_tests_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `semester_tests_school_id_foreign` (`school_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stages`
--
ALTER TABLE `stages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stages_school_id_foreign` (`school_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_s_id_unique` (`s_id`),
  ADD KEY `students_user_id_foreign` (`user_id`),
  ADD KEY `students_parent_id_foreign` (`parent_id`),
  ADD KEY `students_school_id_foreign` (`school_id`),
  ADD KEY `students_stage_id_foreign` (`stage_id`),
  ADD KEY `students_grade_id_foreign` (`grade_id`),
  ADD KEY `students_classroom_id_foreign` (`classroom_id`);

--
-- Indexes for table `student_behaviors`
--
ALTER TABLE `student_behaviors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_behaviors_school_id_foreign` (`school_id`),
  ADD KEY `student_behaviors_student_behaviors_mains_id_foreign` (`student_behaviors_mains_id`),
  ADD KEY `student_behaviors_student_id_foreign` (`student_id`);

--
-- Indexes for table `student_behaviors_mains`
--
ALTER TABLE `student_behaviors_mains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_behaviors_mains_school_id_foreign` (`school_id`),
  ADD KEY `student_behaviors_mains_year_id_foreign` (`year_id`),
  ADD KEY `student_behaviors_mains_teacher_id_foreign` (`teacher_id`),
  ADD KEY `student_behaviors_mains_subject_id_foreign` (`subject_id`),
  ADD KEY `student_behaviors_mains_classroom_id_foreign` (`classroom_id`);

--
-- Indexes for table `student_behaviors_point_actions`
--
ALTER TABLE `student_behaviors_point_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_behaviors_point_actions_student_behaviors_id_foreign` (`student_behaviors_id`),
  ADD KEY `student_behaviors_point_actions_reason_id_foreign` (`reason_id`),
  ADD KEY `student_behaviors_point_actions_canceled_by_foreign` (`canceled_by`),
  ADD KEY `student_behaviors_point_actions_created_by_foreign` (`created_by`);

--
-- Indexes for table `student_classroom_history`
--
ALTER TABLE `student_classroom_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_classroom_history_from_classroom_id_foreign` (`from_classroom_id`),
  ADD KEY `student_classroom_history_to_classroom_id_foreign` (`to_classroom_id`),
  ADD KEY `student_classroom_history_from_grade_id_foreign` (`from_grade_id`),
  ADD KEY `student_classroom_history_to_grade_id_foreign` (`to_grade_id`),
  ADD KEY `student_classroom_history_semester_id_foreign` (`semester_id`),
  ADD KEY `student_classroom_history_changed_by_user_id_foreign` (`changed_by_user_id`),
  ADD KEY `student_classroom_history_student_id_index` (`student_id`),
  ADD KEY `student_classroom_history_academic_year_id_index` (`academic_year_id`),
  ADD KEY `student_classroom_history_changed_at_index` (`changed_at`);

--
-- Indexes for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_parents_t_id_unique` (`t_id`),
  ADD KEY `student_parents_user_id_foreign` (`user_id`),
  ADD KEY `student_parents_school_id_foreign` (`school_id`);

--
-- Indexes for table `student_period_records`
--
ALTER TABLE `student_period_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_period_records_period_activity_id_student_id_unique` (`period_activity_id`,`student_id`),
  ADD KEY `student_period_records_student_id_index` (`student_id`),
  ADD KEY `student_period_records_attendance_status_index` (`attendance_status`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subjects_school_id_foreign` (`school_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_user_id_foreign` (`user_id`),
  ADD KEY `tasks_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teachers_t_id_unique` (`t_id`),
  ADD UNIQUE KEY `teachers_national_id_unique` (`national_id`),
  ADD UNIQUE KEY `teachers_email_unique` (`email`),
  ADD UNIQUE KEY `teachers_phone_number_unique` (`phone_number`),
  ADD UNIQUE KEY `teachers_whatsapp_number_unique` (`whatsapp_number`),
  ADD KEY `teachers_school_id_foreign` (`school_id`),
  ADD KEY `teachers_user_id_foreign` (`user_id`);

--
-- Indexes for table `tree_structures`
--
ALTER TABLE `tree_structures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_email_verified_unique` (`email_verified`);

--
-- Indexes for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_messages_sender_id_foreign` (`sender_id`);

--
-- Indexes for table `user_message_recipients`
--
ALTER TABLE `user_message_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_message_recipients_user_message_id_foreign` (`user_message_id`),
  ADD KEY `user_message_recipients_user_id_foreign` (`user_id`);

--
-- Indexes for table `weekly_plans`
--
ALTER TABLE `weekly_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `weekly_plan_unique` (`schedule_id`,`academic_year_id`,`semester_number`,`week_number`),
  ADD KEY `weekly_plans_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `weekly_plans_copy_id_foreign` (`copy_id`);

--
-- Indexes for table `weekly_plan_sessions`
--
ALTER TABLE `weekly_plan_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `weekly_plan_sessions_weekly_plan_id_session_index_index` (`weekly_plan_id`,`session_index`),
  ADD KEY `weekly_plan_sessions_period_code_index` (`period_code`),
  ADD KEY `weekly_plan_sessions_type_index` (`type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT for table `behaviors`
--
ALTER TABLE `behaviors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `behavior_incidents`
--
ALTER TABLE `behavior_incidents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calendars`
--
ALTER TABLE `calendars`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `classroom_records`
--
ALTER TABLE `classroom_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classroom_subject_teachers`
--
ALTER TABLE `classroom_subject_teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_user`
--
ALTER TABLE `conversation_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_lessons`
--
ALTER TABLE `course_lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_levels`
--
ALTER TABLE `course_levels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_sections`
--
ALTER TABLE `course_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_teacher_assignments`
--
ALTER TABLE `course_teacher_assignments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curricula`
--
ALTER TABLE `curricula`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `curriculum_lessons`
--
ALTER TABLE `curriculum_lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curriculum_lesson_plans`
--
ALTER TABLE `curriculum_lesson_plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curriculum_maps`
--
ALTER TABLE `curriculum_maps`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curriculum_topics`
--
ALTER TABLE `curriculum_topics`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documentations`
--
ALTER TABLE `documentations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dp_daily_tasks`
--
ALTER TABLE `dp_daily_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dp_focus_logs`
--
ALTER TABLE `dp_focus_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dp_rewards`
--
ALTER TABLE `dp_rewards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dp_tasks`
--
ALTER TABLE `dp_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `grade_subject`
--
ALTER TABLE `grade_subject`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `h_r_s`
--
ALTER TABLE `h_r_s`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_plan_templates`
--
ALTER TABLE `lesson_plan_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_practice_submissions`
--
ALTER TABLE `lesson_practice_submissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_presentations`
--
ALTER TABLE `lesson_presentations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_presentation_slides`
--
ALTER TABLE `lesson_presentation_slides`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_student_progress`
--
ALTER TABLE `lesson_student_progress`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `myproject_tasks`
--
ALTER TABLE `myproject_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `period_activities`
--
ALTER TABLE `period_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `period_details`
--
ALTER TABLE `period_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pomodoro_sessions`
--
ALTER TABLE `pomodoro_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qdrat_lessons`
--
ALTER TABLE `qdrat_lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qdrat_lesson_categories`
--
ALTER TABLE `qdrat_lesson_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qdrat_questions`
--
ALTER TABLE `qdrat_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qdrat_question_difficulties`
--
ALTER TABLE `qdrat_question_difficulties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qdrat_question_types`
--
ALTER TABLE `qdrat_question_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `qdrat_skills`
--
ALTER TABLE `qdrat_skills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qdrat_skill_levels`
--
ALTER TABLE `qdrat_skill_levels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_banks`
--
ALTER TABLE `question_banks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_types`
--
ALTER TABLE `question_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_question`
--
ALTER TABLE `quiz_question`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_sessions`
--
ALTER TABLE `quiz_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_session_participants`
--
ALTER TABLE `quiz_session_participants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qu_answers`
--
ALTER TABLE `qu_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qu_attempts`
--
ALTER TABLE `qu_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qu_exams`
--
ALTER TABLE `qu_exams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `qu_exam_questions`
--
ALTER TABLE `qu_exam_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `qu_questions`
--
ALTER TABLE `qu_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `resume_answers`
--
ALTER TABLE `resume_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_answer_bookmarks`
--
ALTER TABLE `resume_answer_bookmarks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_answer_likes`
--
ALTER TABLE `resume_answer_likes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_answer_ratings`
--
ALTER TABLE `resume_answer_ratings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_answer_reports`
--
ALTER TABLE `resume_answer_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_comment_likes`
--
ALTER TABLE `resume_comment_likes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_questions`
--
ALTER TABLE `resume_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_question_comments`
--
ALTER TABLE `resume_question_comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_themes`
--
ALTER TABLE `resume_themes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1610;

--
-- AUTO_INCREMENT for table `schedule_copies`
--
ALTER TABLE `schedule_copies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schedule_dailies`
--
ALTER TABLE `schedule_dailies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_daily_records`
--
ALTER TABLE `schedule_daily_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_timings`
--
ALTER TABLE `schedule_timings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `semester_tests`
--
ALTER TABLE `semester_tests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stages`
--
ALTER TABLE `stages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `student_behaviors`
--
ALTER TABLE `student_behaviors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_behaviors_mains`
--
ALTER TABLE `student_behaviors_mains`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_behaviors_point_actions`
--
ALTER TABLE `student_behaviors_point_actions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_classroom_history`
--
ALTER TABLE `student_classroom_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_parents`
--
ALTER TABLE `student_parents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_period_records`
--
ALTER TABLE `student_period_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tree_structures`
--
ALTER TABLE `tree_structures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=809;

--
-- AUTO_INCREMENT for table `user_messages`
--
ALTER TABLE `user_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_message_recipients`
--
ALTER TABLE `user_message_recipients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `weekly_plans`
--
ALTER TABLE `weekly_plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=298;

--
-- AUTO_INCREMENT for table `weekly_plan_sessions`
--
ALTER TABLE `weekly_plan_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD CONSTRAINT `academic_years_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `behaviors`
--
ALTER TABLE `behaviors`
  ADD CONSTRAINT `behaviors_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `behaviors_year_id_foreign` FOREIGN KEY (`year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `behavior_incidents`
--
ALTER TABLE `behavior_incidents`
  ADD CONSTRAINT `behavior_incidents_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `behavior_incidents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `behavior_incidents_parent_notified_by_foreign` FOREIGN KEY (`parent_notified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `behavior_incidents_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `behavior_incidents_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `behavior_incidents_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `behavior_incidents_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `behavior_incidents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `calendars`
--
ALTER TABLE `calendars`
  ADD CONSTRAINT `calendars_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `calendars_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `calendars_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD CONSTRAINT `classrooms_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classrooms_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classrooms_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classroom_records`
--
ALTER TABLE `classroom_records`
  ADD CONSTRAINT `classroom_records_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_records_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_records_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_records_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_records_year_id_foreign` FOREIGN KEY (`year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classroom_subject_teachers`
--
ALTER TABLE `classroom_subject_teachers`
  ADD CONSTRAINT `classroom_subject_teachers_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_subject_teachers_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_subject_teachers_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_subject_teachers_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classroom_subject_teachers_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_user`
--
ALTER TABLE `conversation_user`
  ADD CONSTRAINT `conversation_user_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_lessons`
--
ALTER TABLE `course_lessons`
  ADD CONSTRAINT `course_lessons_course_section_id_foreign` FOREIGN KEY (`course_section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_lessons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_levels`
--
ALTER TABLE `course_levels`
  ADD CONSTRAINT `course_levels_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_levels_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD CONSTRAINT `course_sections_course_level_id_foreign` FOREIGN KEY (`course_level_id`) REFERENCES `course_levels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_sections_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_teacher_assignments`
--
ALTER TABLE `course_teacher_assignments`
  ADD CONSTRAINT `course_teacher_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_teacher_assignments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_teacher_assignments_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `curricula`
--
ALTER TABLE `curricula`
  ADD CONSTRAINT `curricula_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curricula_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curricula_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `curriculum_lessons`
--
ALTER TABLE `curriculum_lessons`
  ADD CONSTRAINT `curriculum_lessons_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_lessons_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `curriculum_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `curriculum_lesson_plans`
--
ALTER TABLE `curriculum_lesson_plans`
  ADD CONSTRAINT `curriculum_lesson_plans_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_lesson_plans_curriculum_lesson_id_foreign` FOREIGN KEY (`curriculum_lesson_id`) REFERENCES `curriculum_lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_lesson_plans_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_lesson_plans_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_lesson_plans_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_lesson_plans_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `curriculum_maps`
--
ALTER TABLE `curriculum_maps`
  ADD CONSTRAINT `curriculum_maps_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_maps_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curricula` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_maps_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_maps_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_maps_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_maps_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `curriculum_topics`
--
ALTER TABLE `curriculum_topics`
  ADD CONSTRAINT `curriculum_topics_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curricula` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documentations`
--
ALTER TABLE `documentations`
  ADD CONSTRAINT `documentations_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dp_daily_tasks`
--
ALTER TABLE `dp_daily_tasks`
  ADD CONSTRAINT `dp_daily_tasks_dp_task_id_foreign` FOREIGN KEY (`dp_task_id`) REFERENCES `dp_tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dp_daily_tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dp_focus_logs`
--
ALTER TABLE `dp_focus_logs`
  ADD CONSTRAINT `dp_focus_logs_dp_daily_task_id_foreign` FOREIGN KEY (`dp_daily_task_id`) REFERENCES `dp_daily_tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dp_focus_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dp_rewards`
--
ALTER TABLE `dp_rewards`
  ADD CONSTRAINT `dp_rewards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dp_tasks`
--
ALTER TABLE `dp_tasks`
  ADD CONSTRAINT `dp_tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grade_subject`
--
ALTER TABLE `grade_subject`
  ADD CONSTRAINT `grade_subject_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grade_subject_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `h_r_s`
--
ALTER TABLE `h_r_s`
  ADD CONSTRAINT `h_r_s_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curricula` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_plan_templates`
--
ALTER TABLE `lesson_plan_templates`
  ADD CONSTRAINT `lesson_plan_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lesson_practice_submissions`
--
ALTER TABLE `lesson_practice_submissions`
  ADD CONSTRAINT `lesson_practice_submissions_lesson_student_progress_id_foreign` FOREIGN KEY (`lesson_student_progress_id`) REFERENCES `lesson_student_progress` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_presentations`
--
ALTER TABLE `lesson_presentations`
  ADD CONSTRAINT `lesson_presentations_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_presentations_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lesson_presentations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_presentations_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_presentations_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_presentation_slides`
--
ALTER TABLE `lesson_presentation_slides`
  ADD CONSTRAINT `lesson_presentation_slides_lesson_presentation_id_foreign` FOREIGN KEY (`lesson_presentation_id`) REFERENCES `lesson_presentations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_student_progress`
--
ALTER TABLE `lesson_student_progress`
  ADD CONSTRAINT `lesson_student_progress_lesson_presentation_id_foreign` FOREIGN KEY (`lesson_presentation_id`) REFERENCES `lesson_presentations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_student_progress_opened_by_teacher_id_foreign` FOREIGN KEY (`opened_by_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lesson_student_progress_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `myproject_tasks`
--
ALTER TABLE `myproject_tasks`
  ADD CONSTRAINT `myproject_tasks_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `myproject_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `period_activities`
--
ALTER TABLE `period_activities`
  ADD CONSTRAINT `period_activities_calendar_id_foreign` FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`id`),
  ADD CONSTRAINT `period_activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `period_activities_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `period_activities_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `period_activities_teacher_substitute_id_foreign` FOREIGN KEY (`teacher_substitute_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `period_activities_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `period_details`
--
ALTER TABLE `period_details`
  ADD CONSTRAINT `period_details_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pomodoro_sessions`
--
ALTER TABLE `pomodoro_sessions`
  ADD CONSTRAINT `pomodoro_sessions_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pomodoro_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qdrat_lessons`
--
ALTER TABLE `qdrat_lessons`
  ADD CONSTRAINT `qdrat_lessons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qdrat_lessons_lesson_category_id_foreign` FOREIGN KEY (`lesson_category_id`) REFERENCES `qdrat_lesson_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `qdrat_lesson_categories`
--
ALTER TABLE `qdrat_lesson_categories`
  ADD CONSTRAINT `qdrat_lesson_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qdrat_questions`
--
ALTER TABLE `qdrat_questions`
  ADD CONSTRAINT `qdrat_questions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qdrat_questions_difficulty_level_id_foreign` FOREIGN KEY (`difficulty_level_id`) REFERENCES `qdrat_question_difficulties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `qdrat_questions_question_type_id_foreign` FOREIGN KEY (`question_type_id`) REFERENCES `qdrat_question_types` (`id`);

--
-- Constraints for table `qdrat_question_difficulties`
--
ALTER TABLE `qdrat_question_difficulties`
  ADD CONSTRAINT `qdrat_question_difficulties_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qdrat_skills`
--
ALTER TABLE `qdrat_skills`
  ADD CONSTRAINT `qdrat_skills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qdrat_skills_skill_level_id_foreign` FOREIGN KEY (`skill_level_id`) REFERENCES `qdrat_skill_levels` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `qdrat_skill_levels`
--
ALTER TABLE `qdrat_skill_levels`
  ADD CONSTRAINT `qdrat_skill_levels_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `questions_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `questions_question_type_id_foreign` FOREIGN KEY (`question_type_id`) REFERENCES `question_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `question_banks`
--
ALTER TABLE `question_banks`
  ADD CONSTRAINT `question_banks_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_banks_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curricula` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_banks_curriculum_lessons_id_foreign` FOREIGN KEY (`curriculum_lessons_id`) REFERENCES `curriculum_lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_banks_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_banks_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quizzes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quiz_attempts_quiz_session_id_foreign` FOREIGN KEY (`quiz_session_id`) REFERENCES `quiz_sessions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD CONSTRAINT `quiz_attempt_answers_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempt_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempt_answers_selected_option_id_foreign` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_question`
--
ALTER TABLE `quiz_question`
  ADD CONSTRAINT `quiz_question_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_question_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_sessions`
--
ALTER TABLE `quiz_sessions`
  ADD CONSTRAINT `quiz_sessions_current_question_id_foreign` FOREIGN KEY (`current_question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quiz_sessions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quiz_sessions_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_session_participants`
--
ALTER TABLE `quiz_session_participants`
  ADD CONSTRAINT `quiz_session_participants_quiz_session_id_foreign` FOREIGN KEY (`quiz_session_id`) REFERENCES `quiz_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_session_participants_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qu_answers`
--
ALTER TABLE `qu_answers`
  ADD CONSTRAINT `qu_answers_qu_attempt_id_foreign` FOREIGN KEY (`qu_attempt_id`) REFERENCES `qu_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qu_answers_qu_question_id_foreign` FOREIGN KEY (`qu_question_id`) REFERENCES `qu_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qu_attempts`
--
ALTER TABLE `qu_attempts`
  ADD CONSTRAINT `qu_attempts_qu_exam_id_foreign` FOREIGN KEY (`qu_exam_id`) REFERENCES `qu_exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qu_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qu_exams`
--
ALTER TABLE `qu_exams`
  ADD CONSTRAINT `qu_exams_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qu_exams_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qu_exam_questions`
--
ALTER TABLE `qu_exam_questions`
  ADD CONSTRAINT `qu_exam_questions_qu_exam_id_foreign` FOREIGN KEY (`qu_exam_id`) REFERENCES `qu_exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qu_exam_questions_qu_question_id_foreign` FOREIGN KEY (`qu_question_id`) REFERENCES `qu_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qu_questions`
--
ALTER TABLE `qu_questions`
  ADD CONSTRAINT `qu_questions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qu_questions_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qu_questions_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `curriculum_topics` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `resume_answers`
--
ALTER TABLE `resume_answers`
  ADD CONSTRAINT `resume_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `resume_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_answers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_answer_bookmarks`
--
ALTER TABLE `resume_answer_bookmarks`
  ADD CONSTRAINT `resume_answer_bookmarks_answer_id_foreign` FOREIGN KEY (`answer_id`) REFERENCES `resume_answers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_answer_bookmarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_answer_likes`
--
ALTER TABLE `resume_answer_likes`
  ADD CONSTRAINT `resume_answer_likes_answer_id_foreign` FOREIGN KEY (`answer_id`) REFERENCES `resume_answers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_answer_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_answer_ratings`
--
ALTER TABLE `resume_answer_ratings`
  ADD CONSTRAINT `resume_answer_ratings_answer_id_foreign` FOREIGN KEY (`answer_id`) REFERENCES `resume_answers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_answer_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_answer_reports`
--
ALTER TABLE `resume_answer_reports`
  ADD CONSTRAINT `resume_answer_reports_answer_id_foreign` FOREIGN KEY (`answer_id`) REFERENCES `resume_answers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_answer_reports_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resume_answer_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_comment_likes`
--
ALTER TABLE `resume_comment_likes`
  ADD CONSTRAINT `resume_comment_likes_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `resume_question_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_comment_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_questions`
--
ALTER TABLE `resume_questions`
  ADD CONSTRAINT `resume_questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_question_comments`
--
ALTER TABLE `resume_question_comments`
  ADD CONSTRAINT `resume_question_comments_answer_id_foreign` FOREIGN KEY (`answer_id`) REFERENCES `resume_answers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_question_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `resume_question_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_question_comments_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `resume_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resume_question_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_themes`
--
ALTER TABLE `resume_themes`
  ADD CONSTRAINT `resume_themes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_co_subject_id_foreign` FOREIGN KEY (`co_subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_co_teacher_id_foreign` FOREIGN KEY (`co_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_copy_id_foreign` FOREIGN KEY (`copy_id`) REFERENCES `schedule_copies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_cst_id_foreign` FOREIGN KEY (`cst_id`) REFERENCES `classroom_subject_teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_teacher_substitute_id_foreign` FOREIGN KEY (`teacher_substitute_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_copies`
--
ALTER TABLE `schedule_copies`
  ADD CONSTRAINT `schedule_copies_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_copies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `schedule_copies_last_modified_by_foreign` FOREIGN KEY (`last_modified_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `schedule_copies_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_copies_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_dailies`
--
ALTER TABLE `schedule_dailies`
  ADD CONSTRAINT `schedule_dailies_schedule_copy_id_foreign` FOREIGN KEY (`schedule_copy_id`) REFERENCES `schedule_copies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_dailies_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_dailies_teacher_substitute_id_foreign` FOREIGN KEY (`teacher_substitute_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `schedule_timings`
--
ALTER TABLE `schedule_timings`
  ADD CONSTRAINT `schedule_timings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schools`
--
ALTER TABLE `schools`
  ADD CONSTRAINT `schools_h_r_id_foreign` FOREIGN KEY (`h_r_id`) REFERENCES `h_r_s` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `semesters_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `semester_tests`
--
ALTER TABLE `semester_tests`
  ADD CONSTRAINT `semester_tests_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `semester_tests_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stages`
--
ALTER TABLE `stages`
  ADD CONSTRAINT `stages_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `student_parents` (`id`),
  ADD CONSTRAINT `students_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_behaviors`
--
ALTER TABLE `student_behaviors`
  ADD CONSTRAINT `student_behaviors_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_behaviors_student_behaviors_mains_id_foreign` FOREIGN KEY (`student_behaviors_mains_id`) REFERENCES `student_behaviors_mains` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_behaviors_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_behaviors_mains`
--
ALTER TABLE `student_behaviors_mains`
  ADD CONSTRAINT `student_behaviors_mains_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_behaviors_mains_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_behaviors_mains_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_behaviors_mains_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_behaviors_mains_year_id_foreign` FOREIGN KEY (`year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_behaviors_point_actions`
--
ALTER TABLE `student_behaviors_point_actions`
  ADD CONSTRAINT `student_behaviors_point_actions_canceled_by_foreign` FOREIGN KEY (`canceled_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `student_behaviors_point_actions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `student_behaviors_point_actions_reason_id_foreign` FOREIGN KEY (`reason_id`) REFERENCES `behaviors` (`id`),
  ADD CONSTRAINT `student_behaviors_point_actions_student_behaviors_id_foreign` FOREIGN KEY (`student_behaviors_id`) REFERENCES `student_behaviors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_classroom_history`
--
ALTER TABLE `student_classroom_history`
  ADD CONSTRAINT `student_classroom_history_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_classroom_history_changed_by_user_id_foreign` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classroom_history_from_classroom_id_foreign` FOREIGN KEY (`from_classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_classroom_history_from_grade_id_foreign` FOREIGN KEY (`from_grade_id`) REFERENCES `grades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_classroom_history_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_classroom_history_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classroom_history_to_classroom_id_foreign` FOREIGN KEY (`to_classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classroom_history_to_grade_id_foreign` FOREIGN KEY (`to_grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD CONSTRAINT `student_parents_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_parents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_period_records`
--
ALTER TABLE `student_period_records`
  ADD CONSTRAINT `student_period_records_period_activity_id_foreign` FOREIGN KEY (`period_activity_id`) REFERENCES `period_activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_period_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD CONSTRAINT `user_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_message_recipients`
--
ALTER TABLE `user_message_recipients`
  ADD CONSTRAINT `user_message_recipients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_message_recipients_user_message_id_foreign` FOREIGN KEY (`user_message_id`) REFERENCES `user_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `weekly_plans`
--
ALTER TABLE `weekly_plans`
  ADD CONSTRAINT `weekly_plans_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  ADD CONSTRAINT `weekly_plans_copy_id_foreign` FOREIGN KEY (`copy_id`) REFERENCES `schedule_copies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `weekly_plans_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `weekly_plan_sessions`
--
ALTER TABLE `weekly_plan_sessions`
  ADD CONSTRAINT `weekly_plan_sessions_weekly_plan_id_foreign` FOREIGN KEY (`weekly_plan_id`) REFERENCES `weekly_plans` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
