-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 09:46 PM
-- Server version: 8.0.33
-- PHP Version: 8.4.15

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
(1, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 04:24:50'),
(2, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:24:50'),
(3, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 04:24:50'),
(4, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:24:50'),
(5, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 04:24:55'),
(6, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:24:56'),
(7, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 04:24:56'),
(8, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:24:56'),
(9, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 04:25:06'),
(10, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:06'),
(11, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 04:25:06'),
(12, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:06'),
(13, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 04:25:22'),
(14, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:23'),
(15, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 04:25:23'),
(16, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:23'),
(17, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/user_management', '2026-01-03 04:25:44'),
(18, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:44'),
(19, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:44'),
(20, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/user_management', '2026-01-03 04:25:52'),
(21, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:52'),
(22, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:52'),
(23, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 04:25:54'),
(24, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:54'),
(25, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:54'),
(26, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/user_management', '2026-01-03 04:25:57'),
(27, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:57'),
(28, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:25:57'),
(29, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 04:26:02'),
(30, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:02'),
(31, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:02'),
(32, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 04:26:05'),
(33, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:05'),
(34, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 04:26:05'),
(35, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:05'),
(36, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 04:26:22'),
(37, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:22'),
(38, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:22'),
(39, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/user_management', '2026-01-03 04:26:24'),
(40, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:24'),
(41, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:24'),
(42, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 04:26:34'),
(43, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:34'),
(44, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 04:26:34'),
(45, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:34'),
(46, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 04:26:35'),
(47, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:35'),
(48, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 04:26:35'),
(49, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:35'),
(50, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 04:26:36'),
(51, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:36'),
(52, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 04:26:36'),
(53, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:36'),
(54, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 04:26:46'),
(55, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:46'),
(56, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:26:46'),
(57, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/classroom-subject-teachers/import', '2026-01-03 04:27:02'),
(58, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:27:02'),
(59, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:27:02'),
(60, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/academic-calendar', '2026-01-03 04:27:04'),
(61, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:27:04'),
(62, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:27:04'),
(63, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr/setup-wizard', '2026-01-03 04:27:16'),
(64, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:27:16'),
(65, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:27:16'),
(66, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr/setup-wizard/validate-step', '2026-01-03 04:27:36'),
(67, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr/setup-wizard/default-data', '2026-01-03 04:27:39'),
(68, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr/setup-wizard/validate-step', '2026-01-03 04:27:43'),
(69, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr/setup-wizard/validate-step', '2026-01-03 04:27:44'),
(70, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr/setup-wizard/validate-step', '2026-01-03 04:28:34'),
(71, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr/setup-wizard', '2026-01-03 04:30:12'),
(72, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr', '2026-01-03 04:30:12'),
(73, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/hr', '2026-01-03 04:30:34'),
(74, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:34'),
(75, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:34'),
(76, 2, 'Visited a page', 'http://127.0.0.1:8000/register-school-admin', '2026-01-03 04:30:35'),
(77, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:35'),
(78, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:35'),
(79, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/classroom-subject-teachers/import', '2026-01-03 04:30:37'),
(80, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:38'),
(81, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:38'),
(82, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 04:30:41'),
(83, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:41'),
(84, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 04:30:41'),
(85, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:41'),
(86, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 04:30:41'),
(87, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 04:30:45'),
(88, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:45'),
(89, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 04:30:45'),
(90, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:30:45'),
(91, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 04:30:45'),
(92, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 04:30:46'),
(93, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 04:30:46'),
(94, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 04:30:48'),
(95, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 04:30:52'),
(96, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 04:30:52'),
(97, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 04:30:54'),
(98, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 04:30:54'),
(99, 2, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 04:30:54'),
(100, 2, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 04:30:54'),
(101, 2, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 04:30:54'),
(102, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 04:30:55'),
(103, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 04:30:55'),
(104, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/menus', '2026-01-03 04:32:44'),
(105, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 04:32:55'),
(106, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:32:56'),
(107, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 04:32:56'),
(108, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:32:56'),
(109, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/validate', '2026-01-03 04:35:29'),
(110, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 04:39:43'),
(111, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:39:43'),
(112, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 04:39:43'),
(113, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 04:39:43'),
(114, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 05:28:56'),
(115, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:28:56'),
(116, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 05:28:56'),
(117, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:28:56'),
(118, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/validate', '2026-01-03 05:29:21'),
(119, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/process', '2026-01-03 05:29:42'),
(120, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 05:36:04'),
(121, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:36:04'),
(122, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 05:36:08'),
(123, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:36:08'),
(124, 2, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 05:36:08'),
(125, 2, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 05:36:08'),
(126, 2, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 05:36:08'),
(127, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:37:09'),
(128, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 05:41:33'),
(129, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:41:33'),
(130, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 05:41:33'),
(131, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:41:33'),
(132, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/user_management', '2026-01-03 05:41:45'),
(133, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:41:45'),
(134, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:41:45'),
(135, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 05:41:59'),
(136, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:42:00'),
(137, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:42:00'),
(138, 2, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 05:42:00'),
(139, 2, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 05:42:00'),
(140, 2, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 05:42:00'),
(141, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:42:00'),
(142, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 05:42:58'),
(143, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:42:58'),
(144, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:42:58'),
(145, 2, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 05:42:58'),
(146, 2, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 05:42:59'),
(147, 2, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 05:42:59'),
(148, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 05:42:59'),
(149, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:43:48'),
(150, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:57:57'),
(151, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:59:54'),
(152, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 05:59:54'),
(153, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 06:00:16'),
(154, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 06:00:16'),
(155, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 06:00:16'),
(156, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 06:00:16'),
(157, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 06:00:16'),
(158, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:00:16'),
(159, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:00:16'),
(160, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:00:16'),
(161, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 06:00:20'),
(162, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:00:23'),
(163, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:00:23'),
(164, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:00:23'),
(165, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 06:00:24'),
(166, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:00:27'),
(167, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:00:27'),
(168, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:00:27'),
(169, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 06:00:45'),
(170, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 06:00:45'),
(171, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 06:00:45'),
(172, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 06:00:45'),
(173, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 06:00:45'),
(174, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 06:00:45'),
(175, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 06:00:45'),
(176, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:00:45'),
(177, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:00:45'),
(178, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:00:45'),
(179, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 06:00:51'),
(180, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:00:54'),
(181, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:00:54'),
(182, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:00:54'),
(183, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:01:02'),
(184, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:01:02'),
(185, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:01:02'),
(186, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:01:04'),
(187, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:01:04'),
(188, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:01:04'),
(189, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:01:10'),
(190, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:01:10'),
(191, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:01:10'),
(192, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:01:12'),
(193, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:01:12'),
(194, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:01:12'),
(195, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:01:16'),
(196, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:01:16'),
(197, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:01:16'),
(198, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:01:18'),
(199, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:01:18'),
(200, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:01:18'),
(201, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:01:23'),
(202, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:01:23'),
(203, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:01:23'),
(204, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 06:55:53'),
(205, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 06:55:53'),
(206, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 06:55:53'),
(207, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 06:55:53'),
(208, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:55:53'),
(209, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:55:53'),
(210, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:55:53'),
(211, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 06:56:22'),
(212, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 06:56:22'),
(213, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 06:56:22'),
(214, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 06:56:22'),
(215, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 06:56:22'),
(216, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 06:56:22'),
(217, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 06:56:22'),
(218, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:56:22'),
(219, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 06:56:22'),
(220, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 06:56:22'),
(221, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:56:49'),
(222, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:58:05'),
(223, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:58:17'),
(224, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:58:27'),
(225, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 06:59:42'),
(226, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:00:49'),
(227, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:00:49'),
(228, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:00:49'),
(229, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:00:49'),
(230, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:00:49'),
(231, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:00:49'),
(232, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:00:49'),
(233, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:00:49'),
(234, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:00:49'),
(235, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:00:49'),
(236, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:00:58'),
(237, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:00:58'),
(238, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:00:59'),
(239, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:00:59'),
(240, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:00:59'),
(241, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:00:59'),
(242, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:00:59'),
(243, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:00:59'),
(244, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:00:59'),
(245, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:00:59'),
(246, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:04:45'),
(247, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:04:45'),
(248, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:04:45'),
(249, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:04:45'),
(250, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:04:45'),
(251, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:04:45'),
(252, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:04:45'),
(253, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:04:45'),
(254, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:04:45'),
(255, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:04:45'),
(256, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:04:54'),
(257, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:05:15'),
(258, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:05:16'),
(259, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:05:16'),
(260, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:05:16'),
(261, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:05:16'),
(262, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:05:16'),
(263, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:05:16'),
(264, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:05:16'),
(265, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:05:16'),
(266, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:05:16'),
(267, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:06:04'),
(268, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:06:12'),
(269, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:06:31'),
(270, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:06:43'),
(271, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:07:18'),
(272, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:07:18'),
(273, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:07:18'),
(274, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:07:18'),
(275, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:07:18'),
(276, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:07:18'),
(277, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:07:18'),
(278, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:07:18'),
(279, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:07:18'),
(280, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:07:18'),
(281, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:07:26'),
(282, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:07:29'),
(283, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:07:29'),
(284, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:07:29'),
(285, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:07:38'),
(286, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:07:39'),
(287, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:07:39'),
(288, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:07:40'),
(289, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:07:50'),
(290, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:07:56'),
(291, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:08:03'),
(292, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:08:03'),
(293, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:08:03'),
(294, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:08:04'),
(295, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:08:26'),
(296, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:08:26'),
(297, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:08:26'),
(298, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:08:26'),
(299, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:08:26'),
(300, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:08:26'),
(301, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:08:26'),
(302, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:08:26'),
(303, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:08:26'),
(304, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:08:26'),
(305, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:08:30'),
(306, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:08:30'),
(307, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:08:30'),
(308, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:08:31'),
(309, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:08:41'),
(310, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:08:41'),
(311, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:08:41'),
(312, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:08:42'),
(313, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:08:48'),
(314, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:08:48'),
(315, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:08:48'),
(316, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:08:52'),
(317, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:08:52'),
(318, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:08:52'),
(319, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:08:53'),
(320, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules/449', '2026-01-03 07:08:57'),
(321, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules/449', '2026-01-03 07:09:05'),
(322, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:08'),
(323, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:11'),
(324, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:14'),
(325, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:09:19'),
(326, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:09:19'),
(327, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:09:19'),
(328, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:09:19'),
(329, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:09:19'),
(330, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:09:19'),
(331, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:09:19'),
(332, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:09:19'),
(333, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:09:19'),
(334, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:09:19'),
(335, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:21'),
(336, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:25'),
(337, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:27'),
(338, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:33'),
(339, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:36'),
(340, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:39'),
(341, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:41'),
(342, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:43'),
(343, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:45'),
(344, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:49'),
(345, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:53'),
(346, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:55'),
(347, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:09:57'),
(348, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:10:02'),
(349, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:10:06'),
(350, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:10:09'),
(351, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:10:13'),
(352, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:10:15'),
(353, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules/447', '2026-01-03 07:10:19'),
(354, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:10:19'),
(355, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:10:19'),
(356, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:10:20'),
(357, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules/449', '2026-01-03 07:10:23'),
(358, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:10:23'),
(359, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:10:23'),
(360, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:10:24'),
(361, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:10:31'),
(362, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:10:31'),
(363, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:10:31'),
(364, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:10:37'),
(365, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:10:38'),
(366, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:10:38'),
(367, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:10:38'),
(368, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:10:38'),
(369, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:10:38'),
(370, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:10:38'),
(371, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:10:38'),
(372, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:10:38'),
(373, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:10:38'),
(374, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:10:57'),
(375, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:10:57'),
(376, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:10:57'),
(377, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:14:17'),
(378, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:16:52'),
(379, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:16:52'),
(380, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:16:52'),
(381, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:16:52'),
(382, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:16:52'),
(383, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:16:52'),
(384, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:16:52'),
(385, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:16:52'),
(386, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:16:52'),
(387, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:16:52'),
(388, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:16:52'),
(389, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:16:52'),
(390, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:16:52'),
(391, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:16:52'),
(392, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:16:52'),
(393, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:16:53'),
(394, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:16:53'),
(395, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:16:53'),
(396, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:17:18'),
(397, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:17:18'),
(398, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:17:18'),
(399, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:17:18'),
(400, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:17:18'),
(401, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:17:18'),
(402, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:17:18'),
(403, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:17:18'),
(404, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:17:18'),
(405, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:17:34'),
(406, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:17:34'),
(407, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:17:34'),
(408, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:17:34'),
(409, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:17:34'),
(410, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:17:34'),
(411, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:17:34'),
(412, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:17:34'),
(413, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:17:34'),
(414, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:17:58'),
(415, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:17:58'),
(416, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:17:58'),
(417, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:18:26'),
(418, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:18:26'),
(419, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:18:26'),
(420, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:18:26'),
(421, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:18:26'),
(422, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:18:26'),
(423, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:18:26'),
(424, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:18:26'),
(425, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:18:26'),
(426, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:19:36'),
(427, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:19:36'),
(428, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:19:36'),
(429, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:19:36'),
(430, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:19:36'),
(431, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:19:36'),
(432, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:19:36'),
(433, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:19:36'),
(434, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:19:36'),
(435, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:21:10'),
(436, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:21:11'),
(437, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:21:11'),
(438, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:21:11'),
(439, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:21:11'),
(440, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:21:11'),
(441, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:21:11'),
(442, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:21:11'),
(443, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:21:11'),
(444, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:27:50'),
(445, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:27:50'),
(446, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:27:50'),
(447, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:27:50'),
(448, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:27:50'),
(449, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:27:50'),
(450, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:27:50'),
(451, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:27:50'),
(452, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:27:50'),
(453, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:28:02'),
(454, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:28:02'),
(455, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:28:02'),
(456, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:28:02'),
(457, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:28:02'),
(458, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:28:02'),
(459, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:28:02'),
(460, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:28:02'),
(461, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:28:02'),
(462, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:34:08'),
(463, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:34:08'),
(464, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:34:08'),
(465, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:34:08'),
(466, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:34:09'),
(467, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:34:09'),
(468, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:34:09'),
(469, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:34:09'),
(470, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:34:09'),
(471, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:34:09'),
(472, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:34:47'),
(473, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:34:47'),
(474, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:34:47'),
(475, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:35:48'),
(476, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:35:48'),
(477, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:35:48'),
(478, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:35:56'),
(479, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:35:56'),
(480, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:35:56'),
(481, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:35:58'),
(482, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:36:03'),
(483, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:36:03'),
(484, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:36:03'),
(485, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:36:08'),
(486, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:36:12'),
(487, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:36:12'),
(488, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:36:12'),
(489, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:36:16'),
(490, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:36:16'),
(491, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:36:16'),
(492, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:36:17'),
(493, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:36:20'),
(494, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:36:20'),
(495, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:36:20'),
(496, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:38:24'),
(497, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:38:38'),
(498, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:38:38'),
(499, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:38:38'),
(500, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:38:38'),
(501, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:38:38'),
(502, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:38:38'),
(503, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:38:38'),
(504, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:38:38'),
(505, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:38:38'),
(506, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:38:38'),
(507, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:39:36'),
(508, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:39:36'),
(509, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:39:36'),
(510, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:40:45'),
(511, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:40:45'),
(512, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:40:45'),
(513, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:40:45'),
(514, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:40:45'),
(515, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:40:45'),
(516, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:40:45'),
(517, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:40:45'),
(518, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:40:45'),
(519, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:40:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `page_url`, `created_at`) VALUES
(520, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:40:50'),
(521, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:40:51'),
(522, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:40:51'),
(523, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:40:51'),
(524, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:40:51'),
(525, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:40:51'),
(526, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:40:51'),
(527, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:40:51'),
(528, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:40:51'),
(529, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:40:51'),
(530, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:41:00'),
(531, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:41:00'),
(532, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:41:00'),
(533, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:41:00'),
(534, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:41:00'),
(535, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:41:00'),
(536, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:41:00'),
(537, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:41:00'),
(538, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:41:00'),
(539, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:41:00'),
(540, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:42:43'),
(541, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:42:43'),
(542, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:42:43'),
(543, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:44:55'),
(544, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:44:55'),
(545, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:44:55'),
(546, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:44:55'),
(547, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:44:55'),
(548, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:44:55'),
(549, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:44:55'),
(550, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:44:55'),
(551, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:44:55'),
(552, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:44:55'),
(553, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:45:02'),
(554, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:45:03'),
(555, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:45:03'),
(556, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:45:03'),
(557, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:45:03'),
(558, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:45:03'),
(559, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:45:03'),
(560, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:45:03'),
(561, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:45:03'),
(562, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:45:03'),
(563, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:45:37'),
(564, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:45:48'),
(565, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:46:49'),
(566, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:46:49'),
(567, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:46:49'),
(568, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:47:00'),
(569, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:47:00'),
(570, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:47:00'),
(571, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:47:00'),
(572, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:47:00'),
(573, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:47:00'),
(574, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:47:00'),
(575, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:47:00'),
(576, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:47:00'),
(577, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:47:00'),
(578, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:47:06'),
(579, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:47:06'),
(580, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:47:06'),
(581, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:47:25'),
(582, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:47:25'),
(583, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:47:25'),
(584, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:17'),
(585, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:17'),
(586, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:17'),
(587, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 07:49:17'),
(588, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:18'),
(589, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:18'),
(590, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:18'),
(591, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:19'),
(592, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:19'),
(593, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:19'),
(594, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:19'),
(595, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:19'),
(596, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:19'),
(597, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 07:49:19'),
(598, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:19'),
(599, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:19'),
(600, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:19'),
(601, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:19'),
(602, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:19'),
(603, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:19'),
(604, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:20'),
(605, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:20'),
(606, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:20'),
(607, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:20'),
(608, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:20'),
(609, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:20'),
(610, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:20'),
(611, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:20'),
(612, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:20'),
(613, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:20'),
(614, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:20'),
(615, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:20'),
(616, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:20'),
(617, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:20'),
(618, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:49:20'),
(619, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:20'),
(620, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:20'),
(621, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:20'),
(622, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:20'),
(623, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:20'),
(624, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:20'),
(625, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:20'),
(626, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:20'),
(627, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:20'),
(628, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:21'),
(629, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:21'),
(630, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:21'),
(631, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:21'),
(632, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:21'),
(633, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:21'),
(634, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:21'),
(635, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:21'),
(636, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:21'),
(637, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:21'),
(638, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:21'),
(639, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:21'),
(640, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:21'),
(641, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:21'),
(642, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:21'),
(643, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:21'),
(644, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:21'),
(645, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:21'),
(646, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:21'),
(647, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:21'),
(648, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:21'),
(649, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:21'),
(650, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:21'),
(651, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:22'),
(652, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:22'),
(653, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:22'),
(654, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:22'),
(655, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:49:22'),
(656, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:22'),
(657, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:22'),
(658, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:22'),
(659, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:22'),
(660, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:22'),
(661, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:22'),
(662, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:22'),
(663, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:49:22'),
(664, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:22'),
(665, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:22'),
(666, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:22'),
(667, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:49:22'),
(668, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:49:22'),
(669, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:22'),
(670, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:49:22'),
(671, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:22'),
(672, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:49:22'),
(673, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:22'),
(674, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:49:22'),
(675, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:22'),
(676, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:22'),
(677, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:22'),
(678, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:22'),
(679, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:23'),
(680, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:23'),
(681, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:23'),
(682, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:23'),
(683, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:49:23'),
(684, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:50:19'),
(685, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:50:20'),
(686, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:50:20'),
(687, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:50:20'),
(688, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:50:20'),
(689, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:50:20'),
(690, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:50:20'),
(691, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:50:20'),
(692, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:50:20'),
(693, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:50:20'),
(694, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:52:13'),
(695, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 07:52:19'),
(696, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:20'),
(697, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:20'),
(698, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:21'),
(699, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:21'),
(700, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:21'),
(701, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:21'),
(702, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:21'),
(703, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:21'),
(704, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:21'),
(705, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:21'),
(706, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:21'),
(707, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:21'),
(708, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:21'),
(709, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:21'),
(710, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:21'),
(711, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:21'),
(712, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:21'),
(713, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:21'),
(714, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:21'),
(715, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:21'),
(716, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:21'),
(717, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:21'),
(718, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:21'),
(719, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:21'),
(720, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:21'),
(721, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:21'),
(722, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:21'),
(723, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:21'),
(724, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:22'),
(725, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:22'),
(726, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:22'),
(727, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:22'),
(728, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:22'),
(729, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:22'),
(730, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:22'),
(731, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:22'),
(732, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:22'),
(733, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:22'),
(734, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:22'),
(735, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:22'),
(736, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:22'),
(737, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:22'),
(738, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:22'),
(739, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:22'),
(740, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:22'),
(741, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:22'),
(742, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:52:22'),
(743, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:22'),
(744, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:22'),
(745, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:22'),
(746, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:22'),
(747, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:22'),
(748, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:22'),
(749, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:52:22'),
(750, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:23'),
(751, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:23'),
(752, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:23'),
(753, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:52:23'),
(754, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:52:23'),
(755, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:52:23'),
(756, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:23'),
(757, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:52:23'),
(758, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:52:23'),
(759, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:53:05'),
(760, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:53:05'),
(761, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:53:05'),
(762, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:53:05'),
(763, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:53:05'),
(764, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:53:05'),
(765, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:53:05'),
(766, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:53:05'),
(767, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:53:05'),
(768, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:53:05'),
(769, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:01'),
(770, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:01'),
(771, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:01'),
(772, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:01'),
(773, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:01'),
(774, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:01'),
(775, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:02'),
(776, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:02'),
(777, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:02'),
(778, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:02'),
(779, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:02'),
(780, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:02'),
(781, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:02'),
(782, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:02'),
(783, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:02'),
(784, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:02'),
(785, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:02'),
(786, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:02'),
(787, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:02'),
(788, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:02'),
(789, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:02'),
(790, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:02'),
(791, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:02'),
(792, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:02'),
(793, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:02'),
(794, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:02'),
(795, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:02'),
(796, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:02'),
(797, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:02'),
(798, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:02'),
(799, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:02'),
(800, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:02'),
(801, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:03'),
(802, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:03'),
(803, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:03'),
(804, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:03'),
(805, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:03'),
(806, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:03'),
(807, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:03'),
(808, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:03'),
(809, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:03'),
(810, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:03'),
(811, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:03'),
(812, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:03'),
(813, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:03'),
(814, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:03'),
(815, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:03'),
(816, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:03'),
(817, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:03'),
(818, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:03'),
(819, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:03'),
(820, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:03'),
(821, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:03'),
(822, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:03'),
(823, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:03'),
(824, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:04'),
(825, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:04'),
(826, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:04'),
(827, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:04'),
(828, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:04'),
(829, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:04'),
(830, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:04'),
(831, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:04'),
(832, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:07'),
(833, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:11'),
(834, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:54:16'),
(835, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:54:16'),
(836, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:54:16'),
(837, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:54:16'),
(838, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:54:16'),
(839, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:54:16'),
(840, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:54:16'),
(841, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:54:16'),
(842, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:54:16'),
(843, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:16'),
(844, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:54:18'),
(845, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:55:35'),
(846, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:55:37'),
(847, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:55:37'),
(848, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:55:37'),
(849, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:55:37'),
(850, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:55:37'),
(851, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:55:37'),
(852, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:55:37'),
(853, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:55:37'),
(854, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:55:37'),
(855, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:55:37'),
(856, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:55:39'),
(857, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:55:43'),
(858, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:55:45'),
(859, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:50'),
(860, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:50'),
(861, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:50'),
(862, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:50'),
(863, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:50'),
(864, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:50'),
(865, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:50'),
(866, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:50'),
(867, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:50'),
(868, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:50'),
(869, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:50'),
(870, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:50'),
(871, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:50'),
(872, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:50'),
(873, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:50'),
(874, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:50'),
(875, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:50'),
(876, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:50'),
(877, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:50'),
(878, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:50'),
(879, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:50'),
(880, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:51'),
(881, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:51'),
(882, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:51'),
(883, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:51'),
(884, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:51'),
(885, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:51'),
(886, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:51'),
(887, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:51'),
(888, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:51'),
(889, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:51'),
(890, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:51'),
(891, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:51'),
(892, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:51'),
(893, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:51'),
(894, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:51'),
(895, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:51'),
(896, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:51'),
(897, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:51'),
(898, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:51'),
(899, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:51'),
(900, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:51'),
(901, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:51'),
(902, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:51'),
(903, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:51'),
(904, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:51'),
(905, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:56:51'),
(906, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:51'),
(907, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:51'),
(908, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:51'),
(909, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:52'),
(910, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:52'),
(911, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:52'),
(912, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:56:52'),
(913, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:52'),
(914, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:52'),
(915, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:52'),
(916, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:56:52'),
(917, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:56:52'),
(918, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:56:52'),
(919, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:52'),
(920, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:56:52'),
(921, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:56:52'),
(922, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:57:11'),
(923, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:57:11'),
(924, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:57:11'),
(925, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:57:11'),
(926, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:57:11'),
(927, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:57:11'),
(928, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:57:11'),
(929, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:57:11'),
(930, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:57:11'),
(931, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:57:11'),
(932, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:57:14'),
(933, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:57:16'),
(934, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:57:18'),
(935, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:57:18'),
(936, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:57:18'),
(937, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:57:18'),
(938, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:57:18'),
(939, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:57:18'),
(940, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:57:18'),
(941, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:57:18'),
(942, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:57:18'),
(943, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:57:19'),
(944, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:57:20'),
(945, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 07:57:33'),
(946, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:57:33'),
(947, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 07:57:33'),
(948, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 07:57:34'),
(949, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 07:57:34'),
(950, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 07:57:34'),
(951, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 07:57:34'),
(952, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 07:57:34'),
(953, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 07:57:34'),
(954, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 07:57:34'),
(955, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-data', '2026-01-03 08:02:10'),
(956, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:02:31'),
(957, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:02:40'),
(958, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:02:41'),
(959, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:02:41'),
(960, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:02:41'),
(961, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:02:41'),
(962, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:02:41'),
(963, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:02:41'),
(964, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:02:41'),
(965, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:02:41'),
(966, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:02:41'),
(967, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-data', '2026-01-03 08:03:05'),
(968, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:05:14'),
(969, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:05:14'),
(970, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:05:14'),
(971, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:05:14'),
(972, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:05:14'),
(973, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:05:14'),
(974, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:05:14'),
(975, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:05:14'),
(976, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:05:14'),
(977, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:05:14'),
(978, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:05:16'),
(979, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:05:47'),
(980, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:05:47'),
(981, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:05:47'),
(982, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:05:47'),
(983, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:05:47'),
(984, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:05:47'),
(985, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:05:47'),
(986, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:05:47'),
(987, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:05:48'),
(988, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:05:48'),
(989, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:05:49'),
(990, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:09:25'),
(991, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:09:25'),
(992, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:09:25'),
(993, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:09:25'),
(994, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:09:25'),
(995, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:09:25'),
(996, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:09:25'),
(997, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:09:25'),
(998, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:09:25'),
(999, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:09:25'),
(1000, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:09:28'),
(1001, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:09:34'),
(1002, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:09:41'),
(1003, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:09:46'),
(1004, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:09:47'),
(1005, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:09:47'),
(1006, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:09:47'),
(1007, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:09:47'),
(1008, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:09:47'),
(1009, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:09:47'),
(1010, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:09:47'),
(1011, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:09:47'),
(1012, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:09:47'),
(1013, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:09:53'),
(1014, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:11:25'),
(1015, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:11:29'),
(1016, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:11:58'),
(1017, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:11:58'),
(1018, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:11:58'),
(1019, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:11:58'),
(1020, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:11:58'),
(1021, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:11:58'),
(1022, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:11:58'),
(1023, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:11:58'),
(1024, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:11:58'),
(1025, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:11:58'),
(1026, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:12:07'),
(1027, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:12:07'),
(1028, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:12:07'),
(1029, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:12:07'),
(1030, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:12:07'),
(1031, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:12:07'),
(1032, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:12:07'),
(1033, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:12:07'),
(1034, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:12:07'),
(1035, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:12:07'),
(1036, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:12'),
(1037, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:15'),
(1038, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:18'),
(1039, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:20'),
(1040, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:22'),
(1041, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:23'),
(1042, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `page_url`, `created_at`) VALUES
(1043, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:29'),
(1044, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:32'),
(1045, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:35'),
(1046, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:37'),
(1047, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:41'),
(1048, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules/452', '2026-01-03 08:12:43'),
(1049, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:12:43'),
(1050, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:12:43'),
(1051, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:44'),
(1052, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:12:48'),
(1053, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:12:48'),
(1054, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:12:48'),
(1055, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:49'),
(1056, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:12:55'),
(1057, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:12:55'),
(1058, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:12:55'),
(1059, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:56'),
(1060, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:12:58'),
(1061, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:13:00'),
(1062, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:13:02'),
(1063, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:13:06'),
(1064, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules/456', '2026-01-03 08:13:10'),
(1065, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:13:10'),
(1066, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:13:10'),
(1067, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:13:17'),
(1068, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:13:17'),
(1069, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:13:17'),
(1070, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:13:17'),
(1071, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:13:17'),
(1072, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:13:17'),
(1073, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:13:18'),
(1074, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:13:18'),
(1075, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:13:18'),
(1076, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:13:18'),
(1077, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:13:24'),
(1078, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:13:24'),
(1079, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:13:24'),
(1080, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:13:26'),
(1081, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:13:31'),
(1082, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:13:37'),
(1083, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:13:37'),
(1084, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:13:37'),
(1085, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:13:40'),
(1086, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:13:48'),
(1087, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:13:48'),
(1088, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:13:48'),
(1089, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 08:13:55'),
(1090, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:14:00'),
(1091, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:14:00'),
(1092, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:14:00'),
(1093, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:14:05'),
(1094, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:14:05'),
(1095, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:14:05'),
(1096, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:14:05'),
(1097, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:14:05'),
(1098, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:14:05'),
(1099, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:14:05'),
(1100, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:14:05'),
(1101, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:14:05'),
(1102, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:14:05'),
(1103, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:14:12'),
(1104, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:14:12'),
(1105, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:14:12'),
(1106, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:14:16'),
(1107, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:14:16'),
(1108, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:14:16'),
(1109, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:14:24'),
(1110, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:14:24'),
(1111, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:14:24'),
(1112, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:14:29'),
(1113, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:14:30'),
(1114, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:14:30'),
(1115, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:14:31'),
(1116, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:14:38'),
(1117, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:15:22'),
(1118, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:15:22'),
(1119, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:15:22'),
(1120, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:15:22'),
(1121, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:15:22'),
(1122, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:15:22'),
(1123, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:15:22'),
(1124, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:15:22'),
(1125, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:15:22'),
(1126, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:15:22'),
(1127, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:15:24'),
(1128, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:15:29'),
(1129, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:16:13'),
(1130, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:10'),
(1131, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:10'),
(1132, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:10'),
(1133, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:10'),
(1134, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:10'),
(1135, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:10'),
(1136, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:10'),
(1137, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:10'),
(1138, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:10'),
(1139, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:10'),
(1140, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:10'),
(1141, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:10'),
(1142, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:10'),
(1143, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:10'),
(1144, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:10'),
(1145, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:10'),
(1146, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:10'),
(1147, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:10'),
(1148, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:10'),
(1149, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:10'),
(1150, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:10'),
(1151, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:10'),
(1152, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:10'),
(1153, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:11'),
(1154, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:11'),
(1155, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:11'),
(1156, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:11'),
(1157, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:11'),
(1158, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:11'),
(1159, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:11'),
(1160, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:11'),
(1161, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:11'),
(1162, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:11'),
(1163, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:11'),
(1164, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:11'),
(1165, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:11'),
(1166, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:11'),
(1167, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:11'),
(1168, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:11'),
(1169, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:11'),
(1170, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:11'),
(1171, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:11'),
(1172, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:11'),
(1173, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:11'),
(1174, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:11'),
(1175, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:11'),
(1176, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:11'),
(1177, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:11'),
(1178, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:11'),
(1179, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:11'),
(1180, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:11'),
(1181, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:11'),
(1182, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:12'),
(1183, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:17:12'),
(1184, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:12'),
(1185, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:12'),
(1186, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:12'),
(1187, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:12'),
(1188, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:12'),
(1189, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:12'),
(1190, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:17:12'),
(1191, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:12'),
(1192, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:12'),
(1193, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:12'),
(1194, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:17:12'),
(1195, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:17:12'),
(1196, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:17:12'),
(1197, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:12'),
(1198, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:17:12'),
(1199, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:12'),
(1200, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:18'),
(1201, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:21'),
(1202, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:17:31'),
(1203, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 08:17:55'),
(1204, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 08:17:55'),
(1205, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 08:17:57'),
(1206, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 08:17:57'),
(1207, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 08:17:57'),
(1208, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 08:17:58'),
(1209, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 08:17:58'),
(1210, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 08:18:01'),
(1211, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 08:18:26'),
(1212, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:18:26'),
(1213, 2, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 08:18:26'),
(1214, 2, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 08:18:26'),
(1215, 2, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 08:18:26'),
(1216, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:18:27'),
(1217, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:18:27'),
(1218, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:18:27'),
(1219, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:18:27'),
(1220, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:18:27'),
(1221, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:18:27'),
(1222, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:18:27'),
(1223, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:18:27'),
(1224, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:06'),
(1225, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:06'),
(1226, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:06'),
(1227, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 08:26:06'),
(1228, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:06'),
(1229, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:07'),
(1230, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:07'),
(1231, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:07'),
(1232, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:07'),
(1233, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:07'),
(1234, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:07'),
(1235, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:07'),
(1236, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:07'),
(1237, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:07'),
(1238, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:07'),
(1239, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:07'),
(1240, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:08'),
(1241, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:08'),
(1242, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:08'),
(1243, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:08'),
(1244, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:08'),
(1245, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 08:26:08'),
(1246, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:08'),
(1247, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:08'),
(1248, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:08'),
(1249, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:08'),
(1250, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:08'),
(1251, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:08'),
(1252, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:08'),
(1253, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:08'),
(1254, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:08'),
(1255, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:08'),
(1256, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:08'),
(1257, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:08'),
(1258, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:08'),
(1259, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:08'),
(1260, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:09'),
(1261, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:09'),
(1262, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:09'),
(1263, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:09'),
(1264, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:09'),
(1265, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:09'),
(1266, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 08:26:09'),
(1267, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:09'),
(1268, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:09'),
(1269, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:09'),
(1270, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:09'),
(1271, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:09'),
(1272, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:09'),
(1273, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:09'),
(1274, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:09'),
(1275, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:09'),
(1276, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:09'),
(1277, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:09'),
(1278, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:09'),
(1279, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:10'),
(1280, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:10'),
(1281, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:10'),
(1282, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-03 08:26:10'),
(1283, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:10'),
(1284, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:10'),
(1285, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:10'),
(1286, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:10'),
(1287, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:10'),
(1288, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:10'),
(1289, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:10'),
(1290, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:10'),
(1291, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:10'),
(1292, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:10'),
(1293, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:10'),
(1294, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:10'),
(1295, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:10'),
(1296, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:10'),
(1297, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:10'),
(1298, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:10'),
(1299, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:10'),
(1300, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 08:26:10'),
(1301, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:10'),
(1302, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:10'),
(1303, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:10'),
(1304, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:11'),
(1305, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:11'),
(1306, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:11'),
(1307, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:11'),
(1308, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:11'),
(1309, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:11'),
(1310, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:11'),
(1311, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:11'),
(1312, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:11'),
(1313, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:11'),
(1314, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:11'),
(1315, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:11'),
(1316, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:11'),
(1317, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:11'),
(1318, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:11'),
(1319, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:11'),
(1320, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:11'),
(1321, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:11'),
(1322, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:11'),
(1323, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:11'),
(1324, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:11'),
(1325, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:11'),
(1326, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:11'),
(1327, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:11'),
(1328, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:11'),
(1329, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:11'),
(1330, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:11'),
(1331, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:11'),
(1332, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:11'),
(1333, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:11'),
(1334, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:11'),
(1335, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:11'),
(1336, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:11'),
(1337, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:11'),
(1338, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:12'),
(1339, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1340, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 08:26:12'),
(1341, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:12'),
(1342, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:12'),
(1343, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:12'),
(1344, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:12'),
(1345, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:12'),
(1346, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:12'),
(1347, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1348, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 08:26:12'),
(1349, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:12'),
(1350, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:12'),
(1351, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:12'),
(1352, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 08:26:12'),
(1353, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 08:26:12'),
(1354, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1355, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 08:26:12'),
(1356, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:12'),
(1357, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 08:26:12'),
(1358, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1359, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 08:26:12'),
(1360, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1361, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1362, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1363, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1364, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1365, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1366, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1367, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1368, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 08:26:12'),
(1369, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 11:40:50'),
(1370, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 11:40:50'),
(1371, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 11:40:50'),
(1372, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 11:40:50'),
(1373, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 11:40:50'),
(1374, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 11:40:50'),
(1375, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 11:40:50'),
(1376, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 11:40:50'),
(1377, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 11:40:50'),
(1378, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 11:40:51'),
(1379, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 12:50:05'),
(1380, 19, 'Visited a page', 'http://127.0.0.1:8000/login', '2026-01-03 12:50:06'),
(1381, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-03 12:50:06'),
(1382, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:06'),
(1383, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:06'),
(1384, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:06'),
(1385, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:06'),
(1386, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:07'),
(1387, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:07'),
(1388, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:08'),
(1389, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:08'),
(1390, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:08'),
(1391, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:08'),
(1392, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:08'),
(1393, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:09'),
(1394, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:09'),
(1395, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:50:09'),
(1396, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:50:09'),
(1397, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:50:09'),
(1398, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:09'),
(1399, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:09'),
(1400, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:09'),
(1401, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:09'),
(1402, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:09'),
(1403, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:50:09'),
(1404, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:09'),
(1405, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:09'),
(1406, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:10'),
(1407, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:50:10'),
(1408, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:50:10'),
(1409, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:50:10'),
(1410, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:10'),
(1411, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:50:10'),
(1412, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:10'),
(1413, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:50:10'),
(1414, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:10'),
(1415, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:50:10'),
(1416, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:50:10'),
(1417, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:50:10'),
(1418, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:50:10'),
(1419, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:10'),
(1420, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:10'),
(1421, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:50:10'),
(1422, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:10'),
(1423, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:50:10'),
(1424, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:50:10'),
(1425, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:50:10'),
(1426, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:50:10'),
(1427, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:10'),
(1428, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:10'),
(1429, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:50:10'),
(1430, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:11'),
(1431, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:50:11'),
(1432, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:50:11'),
(1433, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:50:11'),
(1434, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:50:11'),
(1435, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:11'),
(1436, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:11'),
(1437, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:50:11'),
(1438, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:11'),
(1439, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:50:11'),
(1440, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:50:11'),
(1441, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:50:11'),
(1442, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:50:11'),
(1443, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:11'),
(1444, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:50:11'),
(1445, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:50:11'),
(1446, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:11'),
(1447, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:50:11'),
(1448, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:50:11'),
(1449, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:50:11'),
(1450, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:50:11'),
(1451, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:11'),
(1452, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:50:11'),
(1453, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:11'),
(1454, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:50:11'),
(1455, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:50:11'),
(1456, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:50:11'),
(1457, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:50:11'),
(1458, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:11'),
(1459, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:50:11'),
(1460, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:50:11'),
(1461, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:50:12'),
(1462, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:12'),
(1463, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:50:12'),
(1464, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:12'),
(1465, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:12'),
(1466, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:12'),
(1467, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:12'),
(1468, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:12'),
(1469, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:12'),
(1470, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:12'),
(1471, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-03 12:50:14'),
(1472, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:14'),
(1473, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:14'),
(1474, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 12:50:20'),
(1475, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-03 12:50:34'),
(1476, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/hr', '2026-01-03 12:50:36'),
(1477, 19, 'Visited a page', 'http://127.0.0.1:8000/register-school-admin', '2026-01-03 12:50:38'),
(1478, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:38'),
(1479, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:38'),
(1480, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/academic-calendar', '2026-01-03 12:50:42'),
(1481, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 12:50:45'),
(1482, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:45'),
(1483, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 12:50:46'),
(1484, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:50:46'),
(1485, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 12:50:46'),
(1486, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:52:17'),
(1487, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:52:17'),
(1488, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:52:17'),
(1489, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:52:17'),
(1490, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:52:17'),
(1491, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:52:17'),
(1492, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:52:17'),
(1493, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:52:17'),
(1494, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:52:18'),
(1495, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:52:18'),
(1496, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 12:52:24'),
(1497, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 12:52:24'),
(1498, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 12:52:25'),
(1499, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 12:52:31'),
(1500, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:52:31'),
(1501, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 12:52:46'),
(1502, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:52:46'),
(1503, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 12:52:46'),
(1504, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 12:52:46'),
(1505, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 12:52:46'),
(1506, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 12:52:51'),
(1507, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:52:51'),
(1508, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 12:52:51'),
(1509, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 12:52:51'),
(1510, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 12:52:51'),
(1511, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 12:52:51'),
(1512, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 12:52:51'),
(1513, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 12:52:51'),
(1514, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 12:52:55'),
(1515, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:52:55'),
(1516, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 12:57:31'),
(1517, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:57:32'),
(1518, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:57:32'),
(1519, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 12:57:32'),
(1520, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:57:41'),
(1521, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:57:44'),
(1522, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 12:57:47'),
(1523, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 13:00:33'),
(1524, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 13:00:33'),
(1525, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 13:00:36'),
(1526, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:00:36'),
(1527, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 13:02:10'),
(1528, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:02:11'),
(1529, 2, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 13:02:11'),
(1530, 2, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 13:02:11'),
(1531, 2, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 13:02:11'),
(1532, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies/4', '2026-01-03 13:02:24'),
(1533, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:02:24'),
(1534, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-03 13:02:28'),
(1535, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-03 13:02:28'),
(1536, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 13:02:30'),
(1537, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:02:30'),
(1538, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-03 13:02:35'),
(1539, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:02:38'),
(1540, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-03 13:02:38'),
(1541, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-03 13:02:38'),
(1542, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-03 13:02:42'),
(1543, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 13:02:50'),
(1544, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:02:51'),
(1545, 2, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 13:02:51'),
(1546, 2, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 13:02:51'),
(1547, 2, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 13:02:51'),
(1548, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:02:52'),
(1549, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:02:52'),
(1550, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:02:52'),
(1551, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:02:52'),
(1552, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:02:52'),
(1553, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:02:52'),
(1554, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:02:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `page_url`, `created_at`) VALUES
(1555, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:02:52'),
(1556, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:02:56'),
(1557, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:02:56'),
(1558, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:02:56'),
(1559, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 13:02:57'),
(1560, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:01'),
(1561, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:01'),
(1562, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:03:01'),
(1563, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 13:03:03'),
(1564, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:11'),
(1565, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:11'),
(1566, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:03:11'),
(1567, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 13:03:12'),
(1568, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:21'),
(1569, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:21'),
(1570, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:03:21'),
(1571, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 13:03:23'),
(1572, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:26'),
(1573, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:26'),
(1574, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:03:27'),
(1575, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-03 13:03:27'),
(1576, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:31'),
(1577, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:03:31'),
(1578, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:03:31'),
(1579, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:03:33'),
(1580, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:03:33'),
(1581, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 13:03:37'),
(1582, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:03:37'),
(1583, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:03:37'),
(1584, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:03:41'),
(1585, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 13:03:43'),
(1586, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:03:43'),
(1587, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/sync-week', '2026-01-03 13:03:48'),
(1588, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:03:51'),
(1589, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-03 13:03:51'),
(1590, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-03 13:03:51'),
(1591, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:03:52'),
(1592, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:03:52'),
(1593, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:03:52'),
(1594, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:03:53'),
(1595, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:03:53'),
(1596, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:03:54'),
(1597, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-03 13:03:57'),
(1598, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/sync-week', '2026-01-03 13:03:58'),
(1599, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:04:01'),
(1600, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-03 13:04:01'),
(1601, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-03 13:04:01'),
(1602, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:04:03'),
(1603, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:04:03'),
(1604, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:04:03'),
(1605, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-03 13:04:12'),
(1606, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:04:20'),
(1607, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:04:21'),
(1608, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-03 13:04:25'),
(1609, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-03 13:04:36'),
(1610, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:04:36'),
(1611, 2, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-03 13:04:36'),
(1612, 2, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-03 13:04:36'),
(1613, 2, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-03 13:04:36'),
(1614, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:04:37'),
(1615, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:04:37'),
(1616, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:04:37'),
(1617, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:04:37'),
(1618, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:04:37'),
(1619, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:04:37'),
(1620, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:04:37'),
(1621, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:04:38'),
(1622, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:04:42'),
(1623, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:04:42'),
(1624, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:04:42'),
(1625, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:04:44'),
(1626, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:04:44'),
(1627, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:04:46'),
(1628, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:04:46'),
(1629, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:04:46'),
(1630, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:04:46'),
(1631, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 13:04:50'),
(1632, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:04:50'),
(1633, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:04:50'),
(1634, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:05:02'),
(1635, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:05:03'),
(1636, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 13:05:11'),
(1637, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:05:11'),
(1638, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:05:11'),
(1639, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:05:11'),
(1640, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:05:11'),
(1641, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:07:14'),
(1642, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:07:14'),
(1643, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:07:14'),
(1644, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:07:14'),
(1645, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:07:14'),
(1646, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:07:14'),
(1647, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:07:14'),
(1648, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:07:14'),
(1649, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:07:14'),
(1650, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:07:14'),
(1651, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:07:15'),
(1652, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:07:15'),
(1653, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:07:16'),
(1654, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:07:16'),
(1655, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:07:16'),
(1656, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:07:16'),
(1657, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:07:16'),
(1658, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:07:16'),
(1659, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:07:16'),
(1660, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:07:16'),
(1661, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:07:20'),
(1662, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:07:20'),
(1663, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 13:07:22'),
(1664, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:07:22'),
(1665, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:07:22'),
(1666, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:07:23'),
(1667, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:07:23'),
(1668, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:07:56'),
(1669, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:07:56'),
(1670, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:07:56'),
(1671, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:07:56'),
(1672, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:07:57'),
(1673, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:07:57'),
(1674, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:07:57'),
(1675, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:07:57'),
(1676, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:07:57'),
(1677, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:07:57'),
(1678, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:07:57'),
(1679, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:07:57'),
(1680, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:08:01'),
(1681, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:08:01'),
(1682, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:08:01'),
(1683, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:08:11'),
(1684, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:08:11'),
(1685, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:08:11'),
(1686, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:08:11'),
(1687, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:08:11'),
(1688, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:08:11'),
(1689, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:08:11'),
(1690, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:08:11'),
(1691, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:08:11'),
(1692, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:08:12'),
(1693, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:08:15'),
(1694, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:08:15'),
(1695, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:08:20'),
(1696, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:08:21'),
(1697, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:08:21'),
(1698, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:08:21'),
(1699, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 13:09:24'),
(1700, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:09:24'),
(1701, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:09:24'),
(1702, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:09:24'),
(1703, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:09:24'),
(1704, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:09:26'),
(1705, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:09:26'),
(1706, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:09:26'),
(1707, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:09:26'),
(1708, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:09:26'),
(1709, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:09:26'),
(1710, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:09:26'),
(1711, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:09:26'),
(1712, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:09:28'),
(1713, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:09:28'),
(1714, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:09:30'),
(1715, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:09:31'),
(1716, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:09:31'),
(1717, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:09:31'),
(1718, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:09:45'),
(1719, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:09:45'),
(1720, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:09:45'),
(1721, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:09:46'),
(1722, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:09:57'),
(1723, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:09:58'),
(1724, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:09:58'),
(1725, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:09:58'),
(1726, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:09:58'),
(1727, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:09:58'),
(1728, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:09:58'),
(1729, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:09:58'),
(1730, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:09:58'),
(1731, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:09:58'),
(1732, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:10:04'),
(1733, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:10:04'),
(1734, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:10:12'),
(1735, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:10:12'),
(1736, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:10:12'),
(1737, 2, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:10:12'),
(1738, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:10:12'),
(1739, 2, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:10:12'),
(1740, 2, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:10:12'),
(1741, 2, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:10:12'),
(1742, 2, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:10:12'),
(1743, 2, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:10:12'),
(1744, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-03 13:10:26'),
(1745, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:10:26'),
(1746, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:10:26'),
(1747, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:10:26'),
(1748, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-03 13:10:29'),
(1749, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-03 13:10:29'),
(1750, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-03 13:10:29'),
(1751, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-03 13:10:29'),
(1752, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-03 13:10:29'),
(1753, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-03 13:10:29'),
(1754, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-03 13:10:29'),
(1755, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-03 13:10:29'),
(1756, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:10:32'),
(1757, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:10:32'),
(1758, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-03 13:10:43'),
(1759, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:10:43'),
(1760, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-03 13:10:43'),
(1761, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-03 13:10:43'),
(1762, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-03 13:10:52'),
(1763, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:10:52'),
(1764, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:10:52'),
(1765, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/648', '2026-01-03 13:11:56'),
(1766, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:11:56'),
(1767, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/649', '2026-01-03 13:12:00'),
(1768, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-03 13:12:01'),
(1769, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-04 18:26:56'),
(1770, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:26:56'),
(1771, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:26:56'),
(1772, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-04 18:27:57'),
(1773, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:27:57'),
(1774, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-04 18:27:57'),
(1775, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:27:57'),
(1776, 19, 'Visited a page', 'http://127.0.0.1:8000/register-school-admin', '2026-01-04 18:28:06'),
(1777, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:28:07'),
(1778, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:28:07'),
(1779, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/academic-calendar', '2026-01-04 18:28:10'),
(1780, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:28:10'),
(1781, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:28:10'),
(1782, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/academic-calendar/semester/1/generate', '2026-01-04 18:28:24'),
(1783, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/curriculum', '2026-01-04 18:33:40'),
(1784, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:33:40'),
(1785, 19, 'Visited a page', 'http://127.0.0.1:8000/api/curriculum/user-schools', '2026-01-04 18:33:40'),
(1786, 19, 'Visited a page', 'http://127.0.0.1:8000/api/curriculum/curricula', '2026-01-04 18:33:40'),
(1787, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:33:40'),
(1788, 19, 'Visited a page', 'http://127.0.0.1:8000/api/curriculum/school/1/subjects', '2026-01-04 18:34:02'),
(1789, 19, 'Visited a page', 'http://127.0.0.1:8000/api/curriculum/school/1/grades', '2026-01-04 18:34:02'),
(1790, 19, 'Visited a page', 'http://127.0.0.1:8000/api/curriculum/curricula', '2026-01-04 18:34:26'),
(1791, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/academic-calendar', '2026-01-04 18:35:04'),
(1792, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:35:05'),
(1793, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:35:05'),
(1794, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/classroom-subject-teachers/import', '2026-01-04 18:35:14'),
(1795, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:35:15'),
(1796, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:35:15'),
(1797, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 18:36:02'),
(1798, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:36:02'),
(1799, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 18:36:02'),
(1800, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 18:36:02'),
(1801, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 18:36:02'),
(1802, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-04 18:36:55'),
(1803, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 18:36:55'),
(1804, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 18:36:57'),
(1805, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 18:36:59'),
(1806, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 18:36:59'),
(1807, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 18:37:06'),
(1808, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/sync-week', '2026-01-04 18:37:10'),
(1809, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 18:37:11'),
(1810, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 18:37:11'),
(1811, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 18:37:12'),
(1812, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 18:37:13'),
(1813, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 18:37:13'),
(1814, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 18:37:13'),
(1815, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 18:37:15'),
(1816, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 18:37:15'),
(1817, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-04 18:37:20'),
(1818, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 18:37:20'),
(1819, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-04 18:37:20'),
(1820, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-04 18:37:20'),
(1821, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-04 18:37:20'),
(1822, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 18:37:23'),
(1823, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 18:37:23'),
(1824, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 18:37:23'),
(1825, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 18:37:23'),
(1826, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 18:37:23'),
(1827, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 18:37:23'),
(1828, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 18:37:23'),
(1829, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 18:37:23'),
(1830, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 18:39:13'),
(1831, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 18:39:13'),
(1832, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 18:39:13'),
(1833, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 19:31:04'),
(1834, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:31:05'),
(1835, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 19:31:05'),
(1836, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:31:05'),
(1837, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 19:31:05'),
(1838, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 19:31:05'),
(1839, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 19:31:05'),
(1840, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:31:05'),
(1841, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:31:05'),
(1842, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:31:05'),
(1843, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 19:32:23'),
(1844, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:32:24'),
(1845, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 19:32:24'),
(1846, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:32:24'),
(1847, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 19:32:24'),
(1848, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 19:32:24'),
(1849, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 19:32:24'),
(1850, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:32:24'),
(1851, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:32:24'),
(1852, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:32:24'),
(1853, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 19:33:04'),
(1854, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 19:33:04'),
(1855, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 19:33:04'),
(1856, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 19:33:04'),
(1857, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:33:04'),
(1858, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:33:04'),
(1859, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:33:04'),
(1860, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 19:35:16'),
(1861, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:35:16'),
(1862, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 19:35:17'),
(1863, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:35:17'),
(1864, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 19:35:17'),
(1865, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 19:35:17'),
(1866, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 19:35:17'),
(1867, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:35:17'),
(1868, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:35:17'),
(1869, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:35:17'),
(1870, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:35:24'),
(1871, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:35:24'),
(1872, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:35:24'),
(1873, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 19:40:25'),
(1874, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:40:26'),
(1875, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 19:40:26'),
(1876, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:40:26'),
(1877, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 19:40:26'),
(1878, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 19:40:26'),
(1879, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 19:40:26'),
(1880, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:40:26'),
(1881, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:40:26'),
(1882, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:40:26'),
(1883, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:40:29'),
(1884, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:40:29'),
(1885, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:40:29'),
(1886, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 19:42:42'),
(1887, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:42:42'),
(1888, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 19:42:42'),
(1889, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:42:42'),
(1890, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 19:42:42'),
(1891, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 19:42:42'),
(1892, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 19:42:42'),
(1893, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:42:43'),
(1894, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:42:43'),
(1895, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:42:43'),
(1896, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:42:45'),
(1897, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:42:45'),
(1898, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:42:45'),
(1899, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 19:44:27'),
(1900, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 19:44:44'),
(1901, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:44:46'),
(1902, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:44:46'),
(1903, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 19:44:54'),
(1904, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:44:55'),
(1905, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 19:44:55'),
(1906, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 19:44:55'),
(1907, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 19:44:55'),
(1908, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 19:44:55'),
(1909, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 19:44:55'),
(1910, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:44:55'),
(1911, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:44:55'),
(1912, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:44:55'),
(1913, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:44:57'),
(1914, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 19:44:57'),
(1915, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:44:57'),
(1916, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 19:47:57'),
(1917, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 19:56:47'),
(1918, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 19:56:58'),
(1919, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:57:00'),
(1920, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:57:00'),
(1921, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 19:57:10'),
(1922, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 19:57:40'),
(1923, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:57:42'),
(1924, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:57:42'),
(1925, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 19:57:49'),
(1926, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 19:58:41'),
(1927, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:59:27'),
(1928, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 19:59:27'),
(1929, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 19:59:27'),
(1930, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 19:59:30'),
(1931, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:05:42'),
(1932, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:05:45'),
(1933, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 20:05:58'),
(1934, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:05:59'),
(1935, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:05:59'),
(1936, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:06:10'),
(1937, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 20:06:10'),
(1938, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:06:10'),
(1939, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 20:06:10'),
(1940, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:06:11'),
(1941, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:06:11'),
(1942, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:06:11'),
(1943, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:06:11'),
(1944, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:06:11'),
(1945, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:06:11'),
(1946, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:06:13'),
(1947, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:06:13'),
(1948, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:06:13'),
(1949, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:07:25'),
(1950, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 20:07:39'),
(1951, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:07:41'),
(1952, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:07:41'),
(1953, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:09:38'),
(1954, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:09:38'),
(1955, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:09:38'),
(1956, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:09:38'),
(1957, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:09:38'),
(1958, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:09:38'),
(1959, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:09:38'),
(1960, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:09:58'),
(1961, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:10:07'),
(1962, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:10:07'),
(1963, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:10:07'),
(1964, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:13:22'),
(1965, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:13:24'),
(1966, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 20:13:33'),
(1967, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:13:33'),
(1968, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:13:33'),
(1969, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:14:32'),
(1970, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 20:14:33'),
(1971, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:14:33'),
(1972, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 20:14:33'),
(1973, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:14:33'),
(1974, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:14:33'),
(1975, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:14:33'),
(1976, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:14:33'),
(1977, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:14:33'),
(1978, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:14:33'),
(1979, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:14:38'),
(1980, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:14:38'),
(1981, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:14:38'),
(1982, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 20:16:23'),
(1983, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:16:32'),
(1984, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:16:32'),
(1985, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:16:32'),
(1986, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 20:17:02'),
(1987, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:17:09'),
(1988, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:17:09'),
(1989, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:17:09'),
(1990, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:17:16'),
(1991, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:17:16'),
(1992, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:17:16'),
(1993, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:21:15'),
(1994, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 20:21:16'),
(1995, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:21:16'),
(1996, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 20:21:16'),
(1997, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:21:16'),
(1998, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:21:16'),
(1999, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:21:16'),
(2000, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:21:16'),
(2001, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:21:16'),
(2002, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:21:16'),
(2003, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:21:19'),
(2004, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:21:19'),
(2005, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:21:19'),
(2006, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:22:42'),
(2007, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:22:52'),
(2008, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:22:52'),
(2009, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:22:52'),
(2010, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:27:31'),
(2011, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:27:51'),
(2012, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:27:51'),
(2013, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:27:51'),
(2014, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 20:27:59'),
(2015, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:28:11'),
(2016, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:28:11'),
(2017, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:28:11'),
(2018, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 20:28:13'),
(2019, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:28:19'),
(2020, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:28:19'),
(2021, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:28:19'),
(2022, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:32:57'),
(2023, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:33:01'),
(2024, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:33:01'),
(2025, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:33:02'),
(2026, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 20:33:08'),
(2027, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:33:29'),
(2028, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:33:29'),
(2029, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:33:29'),
(2030, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 20:33:30'),
(2031, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:33:38'),
(2032, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:33:38'),
(2033, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:33:38'),
(2034, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 20:34:18'),
(2035, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 20:34:18'),
(2036, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 20:34:26'),
(2037, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:26'),
(2038, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:27'),
(2039, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-04 20:34:31'),
(2040, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 20:34:31'),
(2041, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 20:34:32'),
(2042, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:34:32'),
(2043, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 20:34:34'),
(2044, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/sync-week', '2026-01-04 20:34:35'),
(2045, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 20:34:37'),
(2046, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:37'),
(2047, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:37'),
(2048, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 20:34:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `page_url`, `created_at`) VALUES
(2049, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 20:34:39'),
(2050, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 20:34:40'),
(2051, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:40'),
(2052, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:40'),
(2053, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:50'),
(2054, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:51'),
(2055, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 20:34:51'),
(2056, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 20:35:34'),
(2057, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 20:35:34'),
(2058, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:35:37'),
(2059, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:35:37'),
(2060, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:35:37'),
(2061, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:35:37'),
(2062, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:35:37'),
(2063, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:35:37'),
(2064, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:35:37'),
(2065, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:35:37'),
(2066, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:35:41'),
(2067, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:35:41'),
(2068, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:35:41'),
(2069, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:35:44'),
(2070, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:35:44'),
(2071, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:35:44'),
(2072, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 20:35:56'),
(2073, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 20:35:56'),
(2074, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:35:57'),
(2075, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:35:57'),
(2076, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:35:57'),
(2077, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:35:57'),
(2078, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:35:57'),
(2079, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:35:57'),
(2080, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:35:57'),
(2081, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:35:57'),
(2082, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 20:35:58'),
(2083, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 20:35:58'),
(2084, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-04 20:36:07'),
(2085, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 20:36:07'),
(2086, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 20:36:09'),
(2087, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 20:36:51'),
(2088, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 20:36:51'),
(2089, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:36:59'),
(2090, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:36:59'),
(2091, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:36:59'),
(2092, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:36:59'),
(2093, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:36:59'),
(2094, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:36:59'),
(2095, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:36:59'),
(2096, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:36:59'),
(2097, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:37:03'),
(2098, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:37:03'),
(2099, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:37:03'),
(2100, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:38:45'),
(2101, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 20:39:02'),
(2102, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:39:02'),
(2103, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:39:03'),
(2104, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:39:17'),
(2105, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:39:17'),
(2106, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:39:17'),
(2107, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:39:20'),
(2108, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:39:20'),
(2109, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:39:20'),
(2110, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:39:26'),
(2111, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:39:32'),
(2112, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:39:32'),
(2113, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:39:32'),
(2114, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:42:51'),
(2115, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:42:51'),
(2116, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:42:51'),
(2117, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:43:19'),
(2118, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 20:43:28'),
(2119, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:43:28'),
(2120, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:43:28'),
(2121, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:43:40'),
(2122, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:43:45'),
(2123, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:43:46'),
(2124, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:43:46'),
(2125, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:43:52'),
(2126, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:43:52'),
(2127, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:43:52'),
(2128, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:45:40'),
(2129, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:45:40'),
(2130, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:45:40'),
(2131, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:45:40'),
(2132, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:45:40'),
(2133, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:45:40'),
(2134, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:45:40'),
(2135, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:45:50'),
(2136, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:45:55'),
(2137, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:45:55'),
(2138, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:45:55'),
(2139, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 20:46:08'),
(2140, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:46:08'),
(2141, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:46:08'),
(2142, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:50:40'),
(2143, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:50:40'),
(2144, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:50:40'),
(2145, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:50:44'),
(2146, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:50:44'),
(2147, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:50:45'),
(2148, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:51:31'),
(2149, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 20:51:31'),
(2150, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:51:31'),
(2151, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 20:51:31'),
(2152, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:51:31'),
(2153, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:51:31'),
(2154, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:51:31'),
(2155, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:51:31'),
(2156, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:51:31'),
(2157, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:51:31'),
(2158, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:51:37'),
(2159, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:51:37'),
(2160, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:51:37'),
(2161, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:54:05'),
(2162, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:54:11'),
(2163, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:54:11'),
(2164, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:54:11'),
(2165, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 20:54:21'),
(2166, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 20:54:21'),
(2167, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-04 20:54:42'),
(2168, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:54:42'),
(2169, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-04 20:54:42'),
(2170, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-04 20:54:42'),
(2171, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-04 20:54:42'),
(2172, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:54:43'),
(2173, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:54:43'),
(2174, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:54:43'),
(2175, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:54:43'),
(2176, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:54:43'),
(2177, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:54:43'),
(2178, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:54:43'),
(2179, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:54:43'),
(2180, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:54:46'),
(2181, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:54:46'),
(2182, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:54:46'),
(2183, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:54:55'),
(2184, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:54:56'),
(2185, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:54:56'),
(2186, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 20:56:05'),
(2187, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 20:56:11'),
(2188, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:56:12'),
(2189, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:56:12'),
(2190, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 20:56:21'),
(2191, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 20:56:21'),
(2192, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 20:56:33'),
(2193, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 20:56:33'),
(2194, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 20:56:33'),
(2195, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 20:56:33'),
(2196, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 20:56:33'),
(2197, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:56:33'),
(2198, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:56:34'),
(2199, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:56:34'),
(2200, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 20:56:37'),
(2201, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 20:56:37'),
(2202, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 20:56:37'),
(2203, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 21:02:47'),
(2204, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 21:02:54'),
(2205, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 21:02:54'),
(2206, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 21:02:54'),
(2207, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 21:03:06'),
(2208, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 21:03:06'),
(2209, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 21:03:17'),
(2210, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:03:17'),
(2211, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:03:17'),
(2212, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-04 21:03:23'),
(2213, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 21:03:23'),
(2214, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 21:03:24'),
(2215, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:03:24'),
(2216, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 21:03:29'),
(2217, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/sync-week', '2026-01-04 21:03:31'),
(2218, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 21:03:34'),
(2219, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 21:03:34'),
(2220, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 21:03:35'),
(2221, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:03:35'),
(2222, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:03:35'),
(2223, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 21:07:13'),
(2224, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 21:07:14'),
(2225, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:07:14'),
(2226, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 21:07:14'),
(2227, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:07:14'),
(2228, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1021', '2026-01-04 21:07:52'),
(2229, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:07:52'),
(2230, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1097', '2026-01-04 21:08:07'),
(2231, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1128', '2026-01-04 21:08:08'),
(2232, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1005', '2026-01-04 21:08:10'),
(2233, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1142', '2026-01-04 21:08:18'),
(2234, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1140', '2026-01-04 21:08:25'),
(2235, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1036', '2026-01-04 21:09:07'),
(2236, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1028', '2026-01-04 21:09:37'),
(2237, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/1036', '2026-01-04 21:10:12'),
(2238, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 21:21:51'),
(2239, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:21:51'),
(2240, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 21:22:03'),
(2241, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 21:22:16'),
(2242, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 21:22:27'),
(2243, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 21:22:27'),
(2244, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 21:22:29'),
(2245, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:22:29'),
(2246, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:22:29'),
(2247, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:22:55'),
(2248, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:22:56'),
(2249, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:22:57'),
(2250, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-04 21:23:05'),
(2251, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:23:05'),
(2252, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-04 21:23:05'),
(2253, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-04 21:23:05'),
(2254, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-04 21:23:05'),
(2255, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 21:23:06'),
(2256, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:23:06'),
(2257, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 21:23:06'),
(2258, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 21:23:06'),
(2259, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 21:23:06'),
(2260, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 21:23:06'),
(2261, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 21:23:06'),
(2262, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 21:23:06'),
(2263, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-04 21:23:08'),
(2264, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 21:23:08'),
(2265, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 21:23:09'),
(2266, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:23:09'),
(2267, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:23:14'),
(2268, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 21:23:14'),
(2269, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 21:23:14'),
(2270, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-04 21:23:19'),
(2271, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 21:51:10'),
(2272, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 21:51:11'),
(2273, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:51:11'),
(2274, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 21:51:11'),
(2275, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 21:51:18'),
(2276, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 21:51:22'),
(2277, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:51:22'),
(2278, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 21:51:22'),
(2279, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 21:51:22'),
(2280, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 21:51:22'),
(2281, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 21:51:22'),
(2282, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 21:51:22'),
(2283, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 21:51:22'),
(2284, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 21:51:25'),
(2285, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 21:51:25'),
(2286, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 21:51:29'),
(2287, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:51:29'),
(2288, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 21:51:29'),
(2289, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 21:51:44'),
(2290, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:51:44'),
(2291, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 21:51:47'),
(2292, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 21:51:47'),
(2293, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 21:51:47'),
(2294, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-04 21:51:53'),
(2295, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 22:00:23'),
(2296, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 22:00:24'),
(2297, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:00:24'),
(2298, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 22:00:24'),
(2299, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 22:00:30'),
(2300, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-04 22:00:48'),
(2301, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:00:48'),
(2302, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-04 22:00:48'),
(2303, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-04 22:00:48'),
(2304, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-04 22:00:48'),
(2305, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 22:00:53'),
(2306, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:00:53'),
(2307, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 22:00:53'),
(2308, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 22:00:53'),
(2309, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 22:00:53'),
(2310, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:00:53'),
(2311, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:00:53'),
(2312, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:00:53'),
(2313, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:01:07'),
(2314, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:01:07'),
(2315, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:01:07'),
(2316, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 22:04:27'),
(2317, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 22:04:46'),
(2318, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:04:46'),
(2319, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:04:46'),
(2320, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 22:05:14'),
(2321, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:05:14'),
(2322, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:05:14'),
(2323, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 22:06:58'),
(2324, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:06:58'),
(2325, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:06:58'),
(2326, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/apply', '2026-01-04 22:07:29'),
(2327, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:07:29'),
(2328, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:07:29'),
(2329, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 22:25:52'),
(2330, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 22:25:52'),
(2331, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:25:53'),
(2332, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-04 22:25:53'),
(2333, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 22:25:53'),
(2334, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 22:25:53'),
(2335, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 22:25:53'),
(2336, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:25:53'),
(2337, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:25:53'),
(2338, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:25:53'),
(2339, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:25:56'),
(2340, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:25:56'),
(2341, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:25:56'),
(2342, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 22:26:47'),
(2343, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 22:27:01'),
(2344, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:27:01'),
(2345, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:27:01'),
(2346, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 22:28:07'),
(2347, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:28:08'),
(2348, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:28:08'),
(2349, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 22:28:28'),
(2350, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:28:28'),
(2351, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 22:28:35'),
(2352, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 22:28:39'),
(2353, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:28:39'),
(2354, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 22:28:39'),
(2355, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 22:28:39'),
(2356, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 22:28:39'),
(2357, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:28:39'),
(2358, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:28:39'),
(2359, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:28:39'),
(2360, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 22:28:41'),
(2361, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 22:28:41'),
(2362, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-04 22:28:42'),
(2363, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 22:28:42'),
(2364, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-04 22:28:42'),
(2365, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:29:59'),
(2366, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:29:59'),
(2367, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:29:59'),
(2368, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 22:30:06'),
(2369, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 22:30:12'),
(2370, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:30:12'),
(2371, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:30:12'),
(2372, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:30:22'),
(2373, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:30:22'),
(2374, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:30:22'),
(2375, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 22:31:32'),
(2376, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 22:31:37'),
(2377, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:31:37'),
(2378, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:31:37'),
(2379, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:31:42'),
(2380, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:31:42'),
(2381, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:31:42'),
(2382, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/validate', '2026-01-04 22:32:55'),
(2383, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/ai-import/update', '2026-01-04 22:33:01'),
(2384, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:33:01'),
(2385, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:33:01'),
(2386, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-04 22:33:08'),
(2387, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-04 22:33:09'),
(2388, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-04 22:33:32'),
(2389, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:33:32'),
(2390, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 22:33:32'),
(2391, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-04 22:33:32'),
(2392, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-04 22:33:32'),
(2393, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:33:32'),
(2394, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:33:32'),
(2395, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:33:33'),
(2396, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:33:47'),
(2397, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:33:47'),
(2398, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:33:48'),
(2399, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:34:50'),
(2400, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:34:50'),
(2401, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:34:50'),
(2402, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:34:54'),
(2403, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:34:54'),
(2404, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:34:54'),
(2405, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:35:00'),
(2406, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-04 22:35:00'),
(2407, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:35:00'),
(2408, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 22:35:14'),
(2409, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules/107', '2026-01-04 22:35:33'),
(2410, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:35:33'),
(2411, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:35:33'),
(2412, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/slot-availability', '2026-01-04 22:35:43'),
(2413, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules/108', '2026-01-04 22:35:54'),
(2414, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-04 22:35:54'),
(2415, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-04 22:35:54'),
(2416, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-04 22:36:22'),
(2417, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 22:36:22'),
(2418, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-04 22:36:24'),
(2419, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-04 22:36:26'),
(2420, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:36:26'),
(2421, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:36:29'),
(2422, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 22:36:29'),
(2423, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 22:36:29'),
(2424, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-04 22:36:35'),
(2425, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:36:46'),
(2426, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/generate', '2026-01-04 22:36:50'),
(2427, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:36:53'),
(2428, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 22:36:53'),
(2429, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-04 22:36:53'),
(2430, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-04 22:36:57'),
(2431, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-04 22:37:21'),
(2432, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-04 22:37:21'),
(2433, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-04 22:37:30'),
(2434, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-04 22:38:57'),
(2435, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-05 13:28:43'),
(2436, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 13:38:29'),
(2437, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-05 13:40:29'),
(2438, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 13:47:44'),
(2439, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 13:47:44'),
(2440, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 13:47:44'),
(2441, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-05 13:47:50'),
(2442, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 13:48:13'),
(2443, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 13:48:13'),
(2444, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-05 13:48:18'),
(2445, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 13:48:59'),
(2446, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 13:48:59'),
(2447, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:48:59'),
(2448, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 13:49:00'),
(2449, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:00'),
(2450, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:00'),
(2451, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 13:49:00'),
(2452, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:00'),
(2453, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 13:49:00'),
(2454, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 13:49:24'),
(2455, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 13:49:24'),
(2456, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:24'),
(2457, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 13:49:24'),
(2458, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:24'),
(2459, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:25'),
(2460, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 13:49:25'),
(2461, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:25'),
(2462, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 13:49:25'),
(2463, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 13:49:28'),
(2464, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:29'),
(2465, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 13:49:29'),
(2466, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:29'),
(2467, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 13:49:29'),
(2468, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:29'),
(2469, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 13:49:29'),
(2470, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 13:49:29'),
(2471, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 13:49:29'),
(2472, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 15:38:17'),
(2473, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-05 15:38:17'),
(2474, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-05 15:38:17'),
(2475, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-05 15:38:30'),
(2476, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 15:39:02'),
(2477, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 15:39:02'),
(2478, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 15:39:02'),
(2479, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 15:39:57'),
(2480, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 15:39:57'),
(2481, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 15:40:17'),
(2482, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 15:40:17'),
(2483, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 15:40:17'),
(2484, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 15:40:17'),
(2485, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 15:40:17'),
(2486, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 15:40:34'),
(2487, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 15:40:34'),
(2488, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 15:40:50'),
(2489, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 15:40:50'),
(2490, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 15:40:50'),
(2491, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 15:40:50'),
(2492, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 15:40:50'),
(2493, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 15:41:43'),
(2494, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 15:41:43'),
(2495, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 15:41:45'),
(2496, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 15:42:06'),
(2497, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 15:42:06'),
(2498, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 16:26:50'),
(2499, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:26:50'),
(2500, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:26:51'),
(2501, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 16:26:51'),
(2502, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:26:51'),
(2503, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 16:26:51'),
(2504, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:26:51'),
(2505, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:26:51'),
(2506, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:26:51'),
(2507, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 16:28:23'),
(2508, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:28:23'),
(2509, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:28:24'),
(2510, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 16:28:24'),
(2511, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:28:24'),
(2512, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:28:24'),
(2513, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 16:28:24'),
(2514, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:28:24'),
(2515, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:28:24'),
(2516, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 16:29:16'),
(2517, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 16:29:16'),
(2518, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:29:17'),
(2519, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 16:29:22'),
(2520, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:29:22'),
(2521, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 16:29:22'),
(2522, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 16:29:22'),
(2523, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 16:29:22'),
(2524, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 16:29:24'),
(2525, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:29:24'),
(2526, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:29:24'),
(2527, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 16:29:25'),
(2528, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 16:29:25'),
(2529, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 16:29:25'),
(2530, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:29:25'),
(2531, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 16:29:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `page_url`, `created_at`) VALUES
(2532, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 16:29:26'),
(2533, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:29:28'),
(2534, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:30:15'),
(2535, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:30:15'),
(2536, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:30:16'),
(2537, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:30:22'),
(2538, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-05 16:30:22'),
(2539, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 16:30:37'),
(2540, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:30:37'),
(2541, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 16:30:37'),
(2542, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 16:30:37'),
(2543, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 16:30:37'),
(2544, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 16:30:41'),
(2545, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:30:41'),
(2546, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 16:30:41'),
(2547, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 16:30:41'),
(2548, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 16:30:41'),
(2549, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 16:30:41'),
(2550, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 16:30:41'),
(2551, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 16:30:41'),
(2552, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 16:30:47'),
(2553, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 16:30:47'),
(2554, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 16:30:47'),
(2555, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 16:30:53'),
(2556, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 16:30:53'),
(2557, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 16:30:56'),
(2558, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:30:58'),
(2559, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:36:04'),
(2560, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-05 16:36:04'),
(2561, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:37:15'),
(2562, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:37:15'),
(2563, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:37:32'),
(2564, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:39:16'),
(2565, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:39:16'),
(2566, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:39:16'),
(2567, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:39:22'),
(2568, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-05 16:39:22'),
(2569, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:39:24'),
(2570, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 16:39:24'),
(2571, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:39:25'),
(2572, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:39:31'),
(2573, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:39:31'),
(2574, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:39:31'),
(2575, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:39:39'),
(2576, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:39:39'),
(2577, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:39:39'),
(2578, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:40:52'),
(2579, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:40:52'),
(2580, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:40:52'),
(2581, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:40:52'),
(2582, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:40:52'),
(2583, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:41:04'),
(2584, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:41:05'),
(2585, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:41:05'),
(2586, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:41:05'),
(2587, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:41:05'),
(2588, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 16:41:15'),
(2589, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 16:41:15'),
(2590, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:41:16'),
(2591, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:41:16'),
(2592, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:41:16'),
(2593, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:41:20'),
(2594, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-05 16:41:20'),
(2595, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:41:21'),
(2596, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 16:41:21'),
(2597, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:41:23'),
(2598, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:41:24'),
(2599, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:44:32'),
(2600, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:44:32'),
(2601, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:44:32'),
(2602, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:44:32'),
(2603, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:44:32'),
(2604, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:45:13'),
(2605, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:45:13'),
(2606, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 16:45:27'),
(2607, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:45:27'),
(2608, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 16:45:27'),
(2609, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 16:45:27'),
(2610, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 16:45:27'),
(2611, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 16:45:49'),
(2612, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:45:49'),
(2613, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 16:45:49'),
(2614, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 16:45:49'),
(2615, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 16:45:49'),
(2616, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 16:45:49'),
(2617, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 16:45:49'),
(2618, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 16:45:49'),
(2619, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:45:58'),
(2620, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:45:58'),
(2621, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:45:58'),
(2622, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:46:14'),
(2623, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:46:15'),
(2624, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:46:29'),
(2625, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:46:29'),
(2626, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 16:46:40'),
(2627, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:46:40'),
(2628, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 16:46:40'),
(2629, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 16:46:40'),
(2630, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 16:46:40'),
(2631, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 16:47:14'),
(2632, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:48:16'),
(2633, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 16:50:03'),
(2634, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:50:03'),
(2635, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 16:50:03'),
(2636, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 16:50:03'),
(2637, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 16:50:03'),
(2638, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 16:50:03'),
(2639, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 16:50:03'),
(2640, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 16:50:03'),
(2641, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 16:50:05'),
(2642, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 16:50:06'),
(2643, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 16:50:06'),
(2644, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 16:51:24'),
(2645, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 16:51:24'),
(2646, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 16:51:24'),
(2647, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 16:51:24'),
(2648, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 16:51:24'),
(2649, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:58:28'),
(2650, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:58:28'),
(2651, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:58:28'),
(2652, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:58:28'),
(2653, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:58:28'),
(2654, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:58:30'),
(2655, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:58:31'),
(2656, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:58:31'),
(2657, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:58:31'),
(2658, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:58:31'),
(2659, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 16:59:06'),
(2660, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:59:06'),
(2661, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:59:06'),
(2662, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 16:59:06'),
(2663, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 16:59:06'),
(2664, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 17:00:04'),
(2665, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 17:00:04'),
(2666, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 17:00:04'),
(2667, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 17:00:04'),
(2668, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 17:00:04'),
(2669, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/batch-create', '2026-01-05 17:00:21'),
(2670, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 17:03:43'),
(2671, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 17:03:43'),
(2672, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 17:03:43'),
(2673, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 17:03:43'),
(2674, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 17:03:43'),
(2675, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/batch-create', '2026-01-05 17:03:56'),
(2676, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 17:04:02'),
(2677, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 17:04:02'),
(2678, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 17:04:02'),
(2679, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 17:04:02'),
(2680, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 17:04:02'),
(2681, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/batch-create', '2026-01-05 17:04:06'),
(2682, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 17:10:18'),
(2683, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 17:10:18'),
(2684, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 17:10:18'),
(2685, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 17:10:18'),
(2686, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 17:10:18'),
(2687, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:09:46'),
(2688, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:09:47'),
(2689, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:09:47'),
(2690, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:09:47'),
(2691, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:09:47'),
(2692, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:09:47'),
(2693, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:09:47'),
(2694, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:09:47'),
(2695, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:09:48'),
(2696, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:09:48'),
(2697, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:09:48'),
(2698, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:09:48'),
(2699, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:09:48'),
(2700, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:09:48'),
(2701, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:17:13'),
(2702, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:17:13'),
(2703, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:17:13'),
(2704, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:17:13'),
(2705, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:17:14'),
(2706, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:17:14'),
(2707, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:17:14'),
(2708, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:17:14'),
(2709, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:17:14'),
(2710, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:17:14'),
(2711, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:17:14'),
(2712, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:17:14'),
(2713, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:17:14'),
(2714, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:17:14'),
(2715, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:24:57'),
(2716, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:24:57'),
(2717, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:25:00'),
(2718, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 18:25:06'),
(2719, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 18:25:06'),
(2720, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 18:25:06'),
(2721, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:25:11'),
(2722, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:25:11'),
(2723, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:28:35'),
(2724, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:30:10'),
(2725, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:30:10'),
(2726, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:10'),
(2727, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:30:10'),
(2728, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:30:10'),
(2729, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:30:10'),
(2730, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:30:10'),
(2731, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:10'),
(2732, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:10'),
(2733, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:30:10'),
(2734, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:10'),
(2735, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:30:11'),
(2736, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:30:11'),
(2737, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:11'),
(2738, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:30:12'),
(2739, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:30:12'),
(2740, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:30:12'),
(2741, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:30:12'),
(2742, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:12'),
(2743, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:12'),
(2744, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:30:12'),
(2745, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:12'),
(2746, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:30:17'),
(2747, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:30:20'),
(2748, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:20'),
(2749, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:30:20'),
(2750, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:30:20'),
(2751, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:30:22'),
(2752, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:30:44'),
(2753, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:30:51'),
(2754, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:30:51'),
(2755, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:31:24'),
(2756, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:31:24'),
(2757, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:31:24'),
(2758, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:31:24'),
(2759, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:31:24'),
(2760, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:32:05'),
(2761, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:32:05'),
(2762, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:32:05'),
(2763, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:32:05'),
(2764, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:32:05'),
(2765, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:32:19'),
(2766, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:32:35'),
(2767, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:33:38'),
(2768, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:33:38'),
(2769, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:33:38'),
(2770, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:33:38'),
(2771, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:33:38'),
(2772, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:34:04'),
(2773, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:34:04'),
(2774, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:34:04'),
(2775, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:34:04'),
(2776, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:34:04'),
(2777, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:34:34'),
(2778, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:34:35'),
(2779, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:34:35'),
(2780, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:34:35'),
(2781, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:34:35'),
(2782, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:34:53'),
(2783, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:34:54'),
(2784, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:34:54'),
(2785, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:34:54'),
(2786, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:34:54'),
(2787, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:39:36'),
(2788, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:39:36'),
(2789, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:39:36'),
(2790, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:39:37'),
(2791, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:39:37'),
(2792, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:40:07'),
(2793, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:40:07'),
(2794, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:40:07'),
(2795, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:40:07'),
(2796, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:40:07'),
(2797, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:43:48'),
(2798, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:43:48'),
(2799, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:43:48'),
(2800, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:43:48'),
(2801, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:48'),
(2802, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:48'),
(2803, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:43:48'),
(2804, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2805, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:43:49'),
(2806, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:43:49'),
(2807, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:43:49'),
(2808, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2809, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2810, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:43:49'),
(2811, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:43:49'),
(2812, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2813, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2814, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2815, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2816, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2817, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2818, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2819, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2820, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2821, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2822, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2823, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:43:49'),
(2824, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2825, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2826, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2827, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2828, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:43:49'),
(2829, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:44:40'),
(2830, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:44:40'),
(2831, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:44:40'),
(2832, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:44:40'),
(2833, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:41'),
(2834, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2835, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:44:41'),
(2836, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:44:41'),
(2837, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:44:41'),
(2838, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:44:41'),
(2839, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:44:41'),
(2840, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:41'),
(2841, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2842, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:44:41'),
(2843, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:41'),
(2844, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2845, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2846, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:41'),
(2847, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2848, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:41'),
(2849, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2850, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2851, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:41'),
(2852, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2853, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2854, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:41'),
(2855, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:44:41'),
(2856, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:41'),
(2857, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:42'),
(2858, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:42'),
(2859, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:42'),
(2860, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:44:42'),
(2861, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:45:34'),
(2862, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:45:34'),
(2863, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:45:34'),
(2864, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:45:34'),
(2865, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:35'),
(2866, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2867, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:45:35'),
(2868, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:45:35'),
(2869, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:45:35'),
(2870, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:35'),
(2871, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2872, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:45:35'),
(2873, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:45:35'),
(2874, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:35'),
(2875, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:45:35'),
(2876, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2877, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:35'),
(2878, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2879, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2880, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:35'),
(2881, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2882, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2883, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:35'),
(2884, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2885, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:35'),
(2886, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:36'),
(2887, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:45:36'),
(2888, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:36'),
(2889, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:36'),
(2890, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:36'),
(2891, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:36'),
(2892, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:45:36'),
(2893, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:46:47'),
(2894, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:46:48'),
(2895, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:46:48'),
(2896, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:46:48'),
(2897, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:46:48'),
(2898, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 18:47:06'),
(2899, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:47:07'),
(2900, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 18:47:07'),
(2901, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 18:47:07'),
(2902, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:47:07'),
(2903, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:48:16'),
(2904, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:48:16'),
(2905, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:48:16'),
(2906, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:48:19'),
(2907, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:48:19'),
(2908, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:48:19'),
(2909, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:48:19'),
(2910, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:48:19'),
(2911, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:13'),
(2912, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:13'),
(2913, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:49:14'),
(2914, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:14'),
(2915, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:14'),
(2916, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:14'),
(2917, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:14'),
(2918, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:49:14'),
(2919, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:49:14'),
(2920, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:49:14'),
(2921, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:14'),
(2922, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:14'),
(2923, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:49:14'),
(2924, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2925, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:15'),
(2926, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:15'),
(2927, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2928, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2929, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2930, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2931, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2932, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2933, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2934, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2935, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2936, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2937, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2938, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2939, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2940, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2941, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2942, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:15'),
(2943, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2944, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2945, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2946, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2947, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:15'),
(2948, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:47'),
(2949, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:47'),
(2950, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 18:49:48'),
(2951, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:48'),
(2952, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:48'),
(2953, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:48'),
(2954, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:48'),
(2955, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 18:49:48'),
(2956, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 18:49:48'),
(2957, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 18:49:48'),
(2958, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:48'),
(2959, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:48'),
(2960, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 18:49:48'),
(2961, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:48'),
(2962, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:48'),
(2963, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:48'),
(2964, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:49:48'),
(2965, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:48'),
(2966, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2967, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:49'),
(2968, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2969, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:49'),
(2970, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:49'),
(2971, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2972, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:49'),
(2973, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:49'),
(2974, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2975, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2976, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:49'),
(2977, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:49'),
(2978, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2979, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2980, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:49:49'),
(2981, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2982, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2983, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2984, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:49:49'),
(2985, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:51:53'),
(2986, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:51:54'),
(2987, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:51:54'),
(2988, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:51:54'),
(2989, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:51:54'),
(2990, 19, 'Visited a page', 'http://127.0.0.1:8000', '2026-01-05 18:59:05'),
(2991, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:07'),
(2992, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:07'),
(2993, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-05 18:59:09'),
(2994, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:09'),
(2995, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-05 18:59:09'),
(2996, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:09'),
(2997, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import', '2026-01-05 18:59:14'),
(2998, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:15'),
(2999, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/teachers/import/academic-year/1', '2026-01-05 18:59:15'),
(3000, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:15'),
(3001, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/classroom-subject-teachers/import', '2026-01-05 18:59:16'),
(3002, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:17'),
(3003, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:17'),
(3004, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 18:59:21'),
(3005, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:21'),
(3006, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 18:59:21'),
(3007, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:21'),
(3008, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 18:59:21'),
(3009, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:59:23'),
(3010, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:59:24'),
(3011, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:59:24'),
(3012, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 18:59:55'),
(3013, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:56'),
(3014, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:59:56'),
(3015, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 18:59:56'),
(3016, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 18:59:56'),
(3017, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:19:16'),
(3018, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:19:17'),
(3019, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:19:17'),
(3020, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:19:17'),
(3021, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:19:17'),
(3022, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:20:02'),
(3023, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:20:02'),
(3024, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:20:02'),
(3025, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:20:02'),
(3026, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:20:02'),
(3027, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:21:26'),
(3028, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:21:26'),
(3029, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:21:26'),
(3030, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:21:26'),
(3031, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:21:26'),
(3032, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:25:14'),
(3033, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:25:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `page_url`, `created_at`) VALUES
(3034, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:25:14'),
(3035, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:25:14'),
(3036, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:25:14'),
(3037, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:25:46'),
(3038, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:25:47'),
(3039, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:25:47'),
(3040, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:25:47'),
(3041, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:25:47'),
(3042, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:26:09'),
(3043, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:26:09'),
(3044, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:26:09'),
(3045, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:26:09'),
(3046, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:26:48'),
(3047, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:26:56'),
(3048, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:26:57'),
(3049, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:26:57'),
(3050, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:26:57'),
(3051, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:28:59'),
(3052, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:29:19'),
(3053, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:31:51'),
(3054, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:32:34'),
(3055, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:33:51'),
(3056, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:34:22'),
(3057, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:34:54'),
(3058, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:36:04'),
(3059, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:37:15'),
(3060, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:37:44'),
(3061, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:37:44'),
(3062, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:37:44'),
(3063, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:37:44'),
(3064, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:43:54'),
(3065, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:43:54'),
(3066, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:43:54'),
(3067, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:43:54'),
(3068, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 19:44:08'),
(3069, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 19:44:08'),
(3070, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 19:44:08'),
(3071, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 19:44:14'),
(3072, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 19:44:14'),
(3073, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 19:44:14'),
(3074, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 19:44:14'),
(3075, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 19:44:14'),
(3076, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 19:44:21'),
(3077, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 19:44:21'),
(3078, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 19:44:21'),
(3079, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 19:44:21'),
(3080, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 19:44:21'),
(3081, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 19:44:21'),
(3082, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 19:44:21'),
(3083, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 19:44:21'),
(3084, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 19:44:23'),
(3085, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 19:44:23'),
(3086, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 19:44:24'),
(3087, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 19:44:24'),
(3088, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 19:44:24'),
(3089, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 19:44:32'),
(3090, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:44:32'),
(3091, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 19:44:32'),
(3092, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:44:32'),
(3093, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 19:44:32'),
(3094, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 19:44:43'),
(3095, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 19:44:43'),
(3096, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 19:44:45'),
(3097, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 19:44:45'),
(3098, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 19:44:45'),
(3099, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 19:44:56'),
(3100, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 19:44:56'),
(3101, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 19:44:56'),
(3102, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/batch-create', '2026-01-05 19:45:34'),
(3103, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 19:45:35'),
(3104, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 19:45:47'),
(3105, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-05 19:45:47'),
(3106, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans', '2026-01-05 19:45:50'),
(3107, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 19:46:04'),
(3108, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 19:46:04'),
(3109, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 19:46:06'),
(3110, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:46:13'),
(3111, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:46:13'),
(3112, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:53:04'),
(3113, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:53:04'),
(3114, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:53:04'),
(3115, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:53:04'),
(3116, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:53:04'),
(3117, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:53:22'),
(3118, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:53:28'),
(3119, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:53:29'),
(3120, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:53:29'),
(3121, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:53:29'),
(3122, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:53:32'),
(3123, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:53:42'),
(3124, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:53:43'),
(3125, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:53:43'),
(3126, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:53:43'),
(3127, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:53:47'),
(3128, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:53:47'),
(3129, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:53:47'),
(3130, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:53:47'),
(3131, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:53:50'),
(3132, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:54:53'),
(3133, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:54:53'),
(3134, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:54:53'),
(3135, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:54:53'),
(3136, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:54:56'),
(3137, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:54:56'),
(3138, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:54:56'),
(3139, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:54:56'),
(3140, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:56:20'),
(3141, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:56:50'),
(3142, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:56:59'),
(3143, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:56:59'),
(3144, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:56:59'),
(3145, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:56:59'),
(3146, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:57:16'),
(3147, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:57:16'),
(3148, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:57:16'),
(3149, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:57:16'),
(3150, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:57:33'),
(3151, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:57:33'),
(3152, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:57:33'),
(3153, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:57:33'),
(3154, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 19:59:45'),
(3155, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:59:45'),
(3156, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 19:59:45'),
(3157, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 19:59:45'),
(3158, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:01:02'),
(3159, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:01:02'),
(3160, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:01:02'),
(3161, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:01:02'),
(3162, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:01:33'),
(3163, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:01:36'),
(3164, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:01:36'),
(3165, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:01:36'),
(3166, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:01:36'),
(3167, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:01:39'),
(3168, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:03:23'),
(3169, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:03:23'),
(3170, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:03:32'),
(3171, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:03:32'),
(3172, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:03:32'),
(3173, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:03:32'),
(3174, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:03:32'),
(3175, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:11:38'),
(3176, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:11:39'),
(3177, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:11:39'),
(3178, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:11:39'),
(3179, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:11:39'),
(3180, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:15:10'),
(3181, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:15:10'),
(3182, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:15:10'),
(3183, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:15:10'),
(3184, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:15:10'),
(3185, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:15:10'),
(3186, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:15:11'),
(3187, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:15:11'),
(3188, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:15:11'),
(3189, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:15:11'),
(3190, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:16:18'),
(3191, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:16:46'),
(3192, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:17:15'),
(3193, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:17:16'),
(3194, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:18:07'),
(3195, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:18:20'),
(3196, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:18:25'),
(3197, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:18:26'),
(3198, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:18:32'),
(3199, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:19:10'),
(3200, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:19:11'),
(3201, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:19:11'),
(3202, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:19:11'),
(3203, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:19:11'),
(3204, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:19:13'),
(3205, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:19:13'),
(3206, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:19:13'),
(3207, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:23:51'),
(3208, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:23:52'),
(3209, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:23:52'),
(3210, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:23:52'),
(3211, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:23:52'),
(3212, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:23:54'),
(3213, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:23:54'),
(3214, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:23:54'),
(3215, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:23:54'),
(3216, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:24:23'),
(3217, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:24:24'),
(3218, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:24:24'),
(3219, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:24:24'),
(3220, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:24:24'),
(3221, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:24:25'),
(3222, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:24:25'),
(3223, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:24:25'),
(3224, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:24:25'),
(3225, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:26:14'),
(3226, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:26:14'),
(3227, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:26:14'),
(3228, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:26:14'),
(3229, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:26:14'),
(3230, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:26:15'),
(3231, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:26:15'),
(3232, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:26:15'),
(3233, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:26:15'),
(3234, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:26:51'),
(3235, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:26:51'),
(3236, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:31:00'),
(3237, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:31:00'),
(3238, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:31:00'),
(3239, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:31:00'),
(3240, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:31:00'),
(3241, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:36:59'),
(3242, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:36:59'),
(3243, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:36:59'),
(3244, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 20:37:24'),
(3245, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:37:24'),
(3246, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 20:37:24'),
(3247, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 20:37:24'),
(3248, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:37:24'),
(3249, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:37:24'),
(3250, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:37:24'),
(3251, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:37:26'),
(3252, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:37:26'),
(3253, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:37:26'),
(3254, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:38:00'),
(3255, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:38:01'),
(3256, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:38:01'),
(3257, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:38:01'),
(3258, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:38:01'),
(3259, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:40:51'),
(3260, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:40:52'),
(3261, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:40:52'),
(3262, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:41:35'),
(3263, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:41:36'),
(3264, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:41:36'),
(3265, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:42:42'),
(3266, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:42:42'),
(3267, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:42:43'),
(3268, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:44:43'),
(3269, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:44:43'),
(3270, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:44:43'),
(3271, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:44:43'),
(3272, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:44:43'),
(3273, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:44:43'),
(3274, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:44:43'),
(3275, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:44:43'),
(3276, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:44:44'),
(3277, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:45:06'),
(3278, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:45:06'),
(3279, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:45:06'),
(3280, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:45:06'),
(3281, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:45:09'),
(3282, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:45:09'),
(3283, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:45:09'),
(3284, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:45:09'),
(3285, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:45:09'),
(3286, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:45:09'),
(3287, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:45:09'),
(3288, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:45:09'),
(3289, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:45:09'),
(3290, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:45:23'),
(3291, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:45:23'),
(3292, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:45:23'),
(3293, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:45:23'),
(3294, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:45:33'),
(3295, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:45:34'),
(3296, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:45:34'),
(3297, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:45:34'),
(3298, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:45:34'),
(3299, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:45:34'),
(3300, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:45:34'),
(3301, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:45:34'),
(3302, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:45:34'),
(3303, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:45:56'),
(3304, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:45:56'),
(3305, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:45:56'),
(3306, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:45:56'),
(3307, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:45:56'),
(3308, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:45:56'),
(3309, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:45:56'),
(3310, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:45:56'),
(3311, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:45:56'),
(3312, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 20:46:06'),
(3313, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:46:06'),
(3314, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:46:06'),
(3315, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 20:46:11'),
(3316, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 20:46:11'),
(3317, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 20:46:11'),
(3318, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:46:11'),
(3319, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:46:11'),
(3320, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 20:47:22'),
(3321, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:47:22'),
(3322, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 20:47:22'),
(3323, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 20:47:22'),
(3324, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:47:22'),
(3325, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:47:22'),
(3326, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 20:47:22'),
(3327, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:47:22'),
(3328, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:47:23'),
(3329, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 20:47:23'),
(3330, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 20:47:23'),
(3331, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:47:23'),
(3332, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:47:23'),
(3333, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:47:23'),
(3334, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:47:26'),
(3335, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:47:26'),
(3336, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:47:26'),
(3337, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:47:26'),
(3338, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:47:26'),
(3339, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:47:26'),
(3340, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:47:26'),
(3341, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 20:47:27'),
(3342, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:47:27'),
(3343, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:47:27'),
(3344, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:47:29'),
(3345, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 20:47:32'),
(3346, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:47:32'),
(3347, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:47:32'),
(3348, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:47:32'),
(3349, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:47:32'),
(3350, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:47:34'),
(3351, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:47:34'),
(3352, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:47:34'),
(3353, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:47:34'),
(3354, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:47:34'),
(3355, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:47:34'),
(3356, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:47:34'),
(3357, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 20:47:35'),
(3358, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:47:35'),
(3359, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:47:35'),
(3360, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 20:47:36'),
(3361, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 20:47:36'),
(3362, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 20:47:36'),
(3363, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:47:36'),
(3364, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:47:36'),
(3365, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 20:47:38'),
(3366, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 20:47:38'),
(3367, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 20:47:38'),
(3368, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 20:47:38'),
(3369, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 20:47:38'),
(3370, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 20:47:38'),
(3371, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 20:47:38'),
(3372, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 20:47:38'),
(3373, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:47:39'),
(3374, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:47:43'),
(3375, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:47:44'),
(3376, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:47:44'),
(3377, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:47:44'),
(3378, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:47:44'),
(3379, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:47:44'),
(3380, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:47:44'),
(3381, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:47:44'),
(3382, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:47:44'),
(3383, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 20:47:45'),
(3384, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:47:45'),
(3385, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:47:45'),
(3386, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 20:48:41'),
(3387, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 20:48:45'),
(3388, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:48:45'),
(3389, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:48:45'),
(3390, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 20:48:45'),
(3391, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:48:45'),
(3392, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 20:48:47'),
(3393, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 20:48:47'),
(3394, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 20:48:47'),
(3395, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:48:47'),
(3396, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:48:47'),
(3397, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 20:48:48'),
(3398, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 20:48:48'),
(3399, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 20:48:48'),
(3400, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 20:48:48'),
(3401, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 20:48:48'),
(3402, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 20:48:48'),
(3403, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 20:48:48'),
(3404, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 20:48:48'),
(3405, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:48:49'),
(3406, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 20:48:49'),
(3407, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:55:18'),
(3408, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:55:40'),
(3409, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:55:50'),
(3410, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:55:55'),
(3411, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:56:20'),
(3412, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:56:29'),
(3413, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:56:45'),
(3414, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:56:56'),
(3415, 19, 'Visited a page', 'http://127.0.0.1:8000/login', '2026-01-05 20:57:46'),
(3416, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-05 20:57:46'),
(3417, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:57:46'),
(3418, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:57:46'),
(3419, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-05 20:57:47'),
(3420, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:57:48'),
(3421, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:57:48'),
(3422, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:57:53'),
(3423, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:57:53'),
(3424, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:58:39'),
(3425, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:58:43'),
(3426, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:59:05'),
(3427, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:59:10'),
(3428, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:59:10'),
(3429, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:59:10'),
(3430, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 20:59:10'),
(3431, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:59:10'),
(3432, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:59:10'),
(3433, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:59:10'),
(3434, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:59:10'),
(3435, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:59:10'),
(3436, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 20:59:15'),
(3437, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:59:16'),
(3438, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 20:59:16'),
(3439, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 20:59:16'),
(3440, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 20:59:16'),
(3441, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 20:59:16'),
(3442, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 20:59:16'),
(3443, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 20:59:16'),
(3444, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 20:59:16'),
(3445, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:00:26'),
(3446, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:00:26'),
(3447, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:00:29'),
(3448, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:00:31'),
(3449, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:00:33'),
(3450, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:01:02'),
(3451, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:01:41'),
(3452, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:01:41'),
(3453, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:01:41'),
(3454, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:02:25'),
(3455, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:02:25'),
(3456, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:02:25'),
(3457, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:02:25'),
(3458, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:02:25'),
(3459, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:02:26'),
(3460, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:02:26'),
(3461, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:02:26'),
(3462, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:02:26'),
(3463, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:02:26'),
(3464, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:02:26'),
(3465, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:02:26'),
(3466, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:02:26'),
(3467, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:02:26'),
(3468, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:02:26'),
(3469, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:02:26'),
(3470, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:02:26'),
(3471, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:02:26'),
(3472, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:03:15'),
(3473, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:03:16'),
(3474, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:03:17'),
(3475, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:03:17'),
(3476, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:03:20'),
(3477, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:03:21'),
(3478, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:03:21'),
(3479, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:03:21'),
(3480, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:03:23'),
(3481, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:03:23'),
(3482, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:03:23'),
(3483, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:03:23'),
(3484, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:03:23'),
(3485, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:03:26'),
(3486, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:03:30'),
(3487, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:03:30'),
(3488, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:03:30'),
(3489, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:03:32'),
(3490, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:03:32'),
(3491, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:03:32'),
(3492, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:03:32'),
(3493, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:03:32'),
(3494, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:04:35'),
(3495, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:04:35'),
(3496, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:04:35'),
(3497, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:04:35'),
(3498, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:05:53'),
(3499, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:05:53'),
(3500, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:05:53'),
(3501, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:05:53'),
(3502, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:05:53'),
(3503, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:05:53'),
(3504, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:05:53'),
(3505, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:05:53'),
(3506, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:05:53'),
(3507, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:05:53'),
(3508, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:06:09'),
(3509, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:06:09'),
(3510, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:06:09'),
(3511, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:06:09'),
(3512, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:06:09'),
(3513, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:06:09'),
(3514, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:06:09'),
(3515, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:06:09'),
(3516, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:06:09'),
(3517, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:06:28'),
(3518, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:06:28'),
(3519, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:06:28'),
(3520, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:06:28'),
(3521, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:06:29'),
(3522, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:06:29'),
(3523, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:06:29'),
(3524, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:06:29'),
(3525, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:06:29'),
(3526, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:06:30'),
(3527, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:06:30'),
(3528, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:06:30'),
(3529, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:06:30'),
(3530, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:06:50'),
(3531, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:06:51'),
(3532, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:06:51'),
(3533, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:06:51'),
(3534, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:06:51'),
(3535, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:06:51'),
(3536, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:06:51'),
(3537, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:06:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `page_url`, `created_at`) VALUES
(3538, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:06:51'),
(3539, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:07:20'),
(3540, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:07:20'),
(3541, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:07:40'),
(3542, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:07:43'),
(3543, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:07:50'),
(3544, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:08:38'),
(3545, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:09:21'),
(3546, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:09:22'),
(3547, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:09:23'),
(3548, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:09:46'),
(3549, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:09:55'),
(3550, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:10:12'),
(3551, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:11:37'),
(3552, 19, 'Visited a page', 'http://127.0.0.1:8000/login', '2026-01-05 21:11:37'),
(3553, 19, 'Visited a page', 'http://127.0.0.1:8000/dashboard', '2026-01-05 21:11:37'),
(3554, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:11:37'),
(3555, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:11:37'),
(3556, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:11:37'),
(3557, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:11:38'),
(3558, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:11:38'),
(3559, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:11:38'),
(3560, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:11:38'),
(3561, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:11:38'),
(3562, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:11:38'),
(3563, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:11:38'),
(3564, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:12:32'),
(3565, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:12:32'),
(3566, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:12:32'),
(3567, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:12:32'),
(3568, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:12:32'),
(3569, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:12:33'),
(3570, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:12:33'),
(3571, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:12:33'),
(3572, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:12:33'),
(3573, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:12:54'),
(3574, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:12:55'),
(3575, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:12:55'),
(3576, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:12:55'),
(3577, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:12:55'),
(3578, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:12:55'),
(3579, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:12:55'),
(3580, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:12:55'),
(3581, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:12:55'),
(3582, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:13:08'),
(3583, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:13:08'),
(3584, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:13:09'),
(3585, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:13:09'),
(3586, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:13:09'),
(3587, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:13:09'),
(3588, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:13:09'),
(3589, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:13:09'),
(3590, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:13:09'),
(3591, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:13:30'),
(3592, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:13:31'),
(3593, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:13:31'),
(3594, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:13:31'),
(3595, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:13:31'),
(3596, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:13:31'),
(3597, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:13:31'),
(3598, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:13:31'),
(3599, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:13:31'),
(3600, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:13:33'),
(3601, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:13:33'),
(3602, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:13:33'),
(3603, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:13:33'),
(3604, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:14:46'),
(3605, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:14:46'),
(3606, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:14:46'),
(3607, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:14:46'),
(3608, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:14:46'),
(3609, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:14:47'),
(3610, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:14:47'),
(3611, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:14:47'),
(3612, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:14:47'),
(3613, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:16:05'),
(3614, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:16:06'),
(3615, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:16:06'),
(3616, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:16:06'),
(3617, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:16:06'),
(3618, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:16:06'),
(3619, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:16:06'),
(3620, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:16:06'),
(3621, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:16:06'),
(3622, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:16:33'),
(3623, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:16:33'),
(3624, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:16:33'),
(3625, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:16:33'),
(3626, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:16:33'),
(3627, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:16:33'),
(3628, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:16:33'),
(3629, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:16:33'),
(3630, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:16:33'),
(3631, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:17:05'),
(3632, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:17:06'),
(3633, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:17:06'),
(3634, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:17:06'),
(3635, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:17:06'),
(3636, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:17:06'),
(3637, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:17:06'),
(3638, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:17:06'),
(3639, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:17:06'),
(3640, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:17:54'),
(3641, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:17:55'),
(3642, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:17:55'),
(3643, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:17:55'),
(3644, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:17:55'),
(3645, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:17:55'),
(3646, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:17:55'),
(3647, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:17:55'),
(3648, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:17:55'),
(3649, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:18:32'),
(3650, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:18:32'),
(3651, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:18:32'),
(3652, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:18:32'),
(3653, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:18:32'),
(3654, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:18:32'),
(3655, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:18:32'),
(3656, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:18:32'),
(3657, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:18:32'),
(3658, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:21:33'),
(3659, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:21:33'),
(3660, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:21:33'),
(3661, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:21:33'),
(3662, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:21:33'),
(3663, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:21:33'),
(3664, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:21:33'),
(3665, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:21:33'),
(3666, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:21:34'),
(3667, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:22:24'),
(3668, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:22:25'),
(3669, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:22:25'),
(3670, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:22:25'),
(3671, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:22:25'),
(3672, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:22:25'),
(3673, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:22:25'),
(3674, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:22:25'),
(3675, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:22:25'),
(3676, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:23:35'),
(3677, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:23:36'),
(3678, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:23:36'),
(3679, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:23:36'),
(3680, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:23:36'),
(3681, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:23:36'),
(3682, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:23:36'),
(3683, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:23:36'),
(3684, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:23:36'),
(3685, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:25:57'),
(3686, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:25:57'),
(3687, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:25:57'),
(3688, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:25:57'),
(3689, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:25:57'),
(3690, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:25:58'),
(3691, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:25:58'),
(3692, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:25:58'),
(3693, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:25:58'),
(3694, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:26:33'),
(3695, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:26:34'),
(3696, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:26:34'),
(3697, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:26:34'),
(3698, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:26:34'),
(3699, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:26:34'),
(3700, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:26:34'),
(3701, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:26:34'),
(3702, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:26:34'),
(3703, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:27:12'),
(3704, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:27:12'),
(3705, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:27:12'),
(3706, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:27:12'),
(3707, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:27:12'),
(3708, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:27:12'),
(3709, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:27:12'),
(3710, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:27:12'),
(3711, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:27:12'),
(3712, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:30:24'),
(3713, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:30:24'),
(3714, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:30:24'),
(3715, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:30:24'),
(3716, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:30:24'),
(3717, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:30:24'),
(3718, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:30:24'),
(3719, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:30:24'),
(3720, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:30:24'),
(3721, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:30:49'),
(3722, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:30:49'),
(3723, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:30:49'),
(3724, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:30:49'),
(3725, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:30:49'),
(3726, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:30:49'),
(3727, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:30:49'),
(3728, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:30:49'),
(3729, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:30:49'),
(3730, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:30:50'),
(3731, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:30:51'),
(3732, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:30:51'),
(3733, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:30:51'),
(3734, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:30:51'),
(3735, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:30:51'),
(3736, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:30:51'),
(3737, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:30:51'),
(3738, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:30:51'),
(3739, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:30:52'),
(3740, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:30:52'),
(3741, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:30:52'),
(3742, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:30:52'),
(3743, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:30:52'),
(3744, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:30:52'),
(3745, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:30:52'),
(3746, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:30:52'),
(3747, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:30:52'),
(3748, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:31:11'),
(3749, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:31:11'),
(3750, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:31:11'),
(3751, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:31:11'),
(3752, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:31:11'),
(3753, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:31:11'),
(3754, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:31:11'),
(3755, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:31:11'),
(3756, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:31:11'),
(3757, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:32:27'),
(3758, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:32:27'),
(3759, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:32:27'),
(3760, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:32:27'),
(3761, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:32:27'),
(3762, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:32:27'),
(3763, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:32:27'),
(3764, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:32:27'),
(3765, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:32:27'),
(3766, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:33:43'),
(3767, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:33:43'),
(3768, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:33:43'),
(3769, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:33:43'),
(3770, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:33:43'),
(3771, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:33:43'),
(3772, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:33:43'),
(3773, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:33:43'),
(3774, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:33:43'),
(3775, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:34:32'),
(3776, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:34:32'),
(3777, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:34:32'),
(3778, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:34:32'),
(3779, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:34:32'),
(3780, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:34:32'),
(3781, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:34:32'),
(3782, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:34:32'),
(3783, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:34:32'),
(3784, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:36:31'),
(3785, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:36:38'),
(3786, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:36:39'),
(3787, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:36:39'),
(3788, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:36:39'),
(3789, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:36:39'),
(3790, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:36:39'),
(3791, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:36:39'),
(3792, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:36:39'),
(3793, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:36:39'),
(3794, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:36:57'),
(3795, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:37:23'),
(3796, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:37:24'),
(3797, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:37:24'),
(3798, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:37:24'),
(3799, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:37:24'),
(3800, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:37:24'),
(3801, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:37:24'),
(3802, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:37:24'),
(3803, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:37:24'),
(3804, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:38:36'),
(3805, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:38:37'),
(3806, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:38:37'),
(3807, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:38:37'),
(3808, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:38:37'),
(3809, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:38:37'),
(3810, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:38:37'),
(3811, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:38:37'),
(3812, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:38:37'),
(3813, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:40:06'),
(3814, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:40:06'),
(3815, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:40:06'),
(3816, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:40:06'),
(3817, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:40:06'),
(3818, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:40:06'),
(3819, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:40:06'),
(3820, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:40:06'),
(3821, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:40:06'),
(3822, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:40:42'),
(3823, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:40:42'),
(3824, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:40:42'),
(3825, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:41:04'),
(3826, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/weekly-plans/teacher-stats', '2026-01-05 21:41:04'),
(3827, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:41:10'),
(3828, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 21:41:10'),
(3829, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:41:14'),
(3830, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:41:14'),
(3831, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:41:14'),
(3832, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:41:14'),
(3833, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:41:14'),
(3834, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 21:41:25'),
(3835, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:41:25'),
(3836, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 21:41:25'),
(3837, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 21:41:25'),
(3838, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 21:41:25'),
(3839, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 21:41:25'),
(3840, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 21:41:25'),
(3841, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 21:41:25'),
(3842, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:41:33'),
(3843, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:41:33'),
(3844, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 21:41:39'),
(3845, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 21:41:39'),
(3846, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 21:41:39'),
(3847, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 21:42:15'),
(3848, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:42:16'),
(3849, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:42:16'),
(3850, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 21:42:16'),
(3851, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:42:16'),
(3852, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:42:16'),
(3853, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 21:42:16'),
(3854, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:42:16'),
(3855, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:42:16'),
(3856, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:42:16'),
(3857, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:42:44'),
(3858, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:43:00'),
(3859, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:43:01'),
(3860, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:43:01'),
(3861, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:43:01'),
(3862, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:43:01'),
(3863, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:43:01'),
(3864, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:43:01'),
(3865, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:43:01'),
(3866, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:43:01'),
(3867, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:43:02'),
(3868, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:43:02'),
(3869, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:43:03'),
(3870, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:43:03'),
(3871, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:43:03'),
(3872, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:43:03'),
(3873, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:43:03'),
(3874, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:43:03'),
(3875, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 21:43:04'),
(3876, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:43:04'),
(3877, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 21:43:04'),
(3878, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 21:43:04'),
(3879, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 21:43:04'),
(3880, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 21:43:04'),
(3881, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 21:43:04'),
(3882, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 21:43:04'),
(3883, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:43:05'),
(3884, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:43:28'),
(3885, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:43:28'),
(3886, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:43:28'),
(3887, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:43:28'),
(3888, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:43:28'),
(3889, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:43:28'),
(3890, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:43:28'),
(3891, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:43:28'),
(3892, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:43:28'),
(3893, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:43:29'),
(3894, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:43:29'),
(3895, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:43:29'),
(3896, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:43:29'),
(3897, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:43:29'),
(3898, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:43:30'),
(3899, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:43:30'),
(3900, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:43:30'),
(3901, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:43:31'),
(3902, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:43:55'),
(3903, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:43:55'),
(3904, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:43:55'),
(3905, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:43:55'),
(3906, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:43:55'),
(3907, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:43:55'),
(3908, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:43:55'),
(3909, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:43:55'),
(3910, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:43:55'),
(3911, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:43:56'),
(3912, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:43:56'),
(3913, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:43:56'),
(3914, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:43:56'),
(3915, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:43:56'),
(3916, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:43:57'),
(3917, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:43:57'),
(3918, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:43:57'),
(3919, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:43:57'),
(3920, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:44:05'),
(3921, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:44:05'),
(3922, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:44:05'),
(3923, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:44:05'),
(3924, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:44:05'),
(3925, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:44:05'),
(3926, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:44:05'),
(3927, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:44:05'),
(3928, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:44:05'),
(3929, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:44:05'),
(3930, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:44:06'),
(3931, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:44:10'),
(3932, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:44:10'),
(3933, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:44:10'),
(3934, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:44:10'),
(3935, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:44:10'),
(3936, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:44:10'),
(3937, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:44:10'),
(3938, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:44:10'),
(3939, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:44:10'),
(3940, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:44:10'),
(3941, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:44:30'),
(3942, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:44:30'),
(3943, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:44:31'),
(3944, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:44:31'),
(3945, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:44:31'),
(3946, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 21:44:32'),
(3947, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:44:32'),
(3948, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 21:44:32'),
(3949, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 21:44:32'),
(3950, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 21:44:32'),
(3951, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 21:44:32'),
(3952, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 21:44:33'),
(3953, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 21:44:33'),
(3954, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:44:33'),
(3955, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:44:33'),
(3956, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:44:33'),
(3957, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:44:33'),
(3958, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:44:33'),
(3959, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:44:34'),
(3960, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:44:34'),
(3961, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:44:41'),
(3962, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:44:41'),
(3963, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:44:41'),
(3964, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:44:41'),
(3965, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:44:41'),
(3966, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:44:41'),
(3967, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:44:41'),
(3968, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:44:41'),
(3969, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:44:42'),
(3970, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:44:46'),
(3971, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:44:46'),
(3972, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:44:46'),
(3973, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:44:47'),
(3974, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:44:47'),
(3975, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:44:47'),
(3976, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:44:47'),
(3977, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:44:47'),
(3978, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 21:44:48'),
(3979, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:44:48'),
(3980, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 21:44:48'),
(3981, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 21:44:48'),
(3982, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 21:44:48'),
(3983, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 21:44:48'),
(3984, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 21:44:48'),
(3985, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 21:44:48'),
(3986, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:44:49'),
(3987, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:44:49'),
(3988, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-weekly-plans', '2026-01-05 21:44:50'),
(3989, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 21:44:51'),
(3990, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-weekly-plans', '2026-01-05 21:44:51'),
(3991, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/school-browser', '2026-01-05 21:45:09'),
(3992, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:45:09'),
(3993, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/weekly-plans-manager', '2026-01-05 21:45:09'),
(3994, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:45:09'),
(3995, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/sync-analysis', '2026-01-05 21:45:10'),
(3996, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/schedule-copies', '2026-01-05 21:45:10'),
(3997, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:45:10'),
(3998, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools', '2026-01-05 21:45:10'),
(3999, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:45:10'),
(4000, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:45:10'),
(4001, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/timetable-editor', '2026-01-05 21:45:11'),
(4002, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedule-copies', '2026-01-05 21:45:11'),
(4003, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classrooms', '2026-01-05 21:45:11'),
(4004, 19, 'Visited a page', 'http://127.0.0.1:8000/api/teachers', '2026-01-05 21:45:11'),
(4005, 19, 'Visited a page', 'http://127.0.0.1:8000/api/subjects', '2026-01-05 21:45:11'),
(4006, 19, 'Visited a page', 'http://127.0.0.1:8000/admin/schedules', '2026-01-05 21:45:11'),
(4007, 19, 'Visited a page', 'http://127.0.0.1:8000/api/classroom-subject-teachers', '2026-01-05 21:45:11'),
(4008, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher-conflicts', '2026-01-05 21:45:11'),
(4009, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:45:11'),
(4010, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:45:11'),
(4011, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/my-schedule', '2026-01-05 21:45:12'),
(4012, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:45:13'),
(4013, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/school-data', '2026-01-05 21:45:13'),
(4014, 19, 'Visited a page', 'http://127.0.0.1:8000/weekly-system/api/teacher/my-schedule', '2026-01-05 21:45:13'),
(4015, 19, 'Visited a page', 'http://127.0.0.1:8000/user-messages', '2026-01-05 21:45:13'),
(4016, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schools/1', '2026-01-05 21:45:13'),
(4017, 19, 'Visited a page', 'http://127.0.0.1:8000/api/academic-years', '2026-01-05 21:45:13'),
(4018, 19, 'Visited a page', 'http://127.0.0.1:8000/api/semesters', '2026-01-05 21:45:13'),
(4019, 19, 'Visited a page', 'http://127.0.0.1:8000/api/schedule-copies', '2026-01-05 21:45:13');

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
('myclass_cache_66b9459dcf12e626ae799bab31d9144c', 'i:1;', 1767647472),
('myclass_cache_66b9459dcf12e626ae799bab31d9144c:timer', 'i:1767647472;', 1767647472),
('myclass_cache_8826ce38c7767e9fd9fe42f1f4dac05f', 'i:1;', 1767648134),
('myclass_cache_8826ce38c7767e9fd9fe42f1f4dac05f:timer', 'i:1767648134;', 1767648134),
('myclass_cache_a9f72b09ef36413c7b77128f99c2916d', 'i:1;', 1767440510),
('myclass_cache_a9f72b09ef36413c7b77128f99c2916d:timer', 'i:1767440510;', 1767440510),
('myclass_cache_tn8d425|127.0.0.1', 'i:1;', 1767648134),
('myclass_cache_tn8d425|127.0.0.1:timer', 'i:1767648134;', 1767648134);

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
  `updated_at` timestamp NULL DEFAULT NULL
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(113, '2024_01_01_000000_create_schools_table', 6);

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
(5, 'App\\Models\\User', 19);

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
(8, 'user', 'web', '2026-01-03 01:24:40', '2026-01-03 01:24:40');

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
(41, 7);

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
(1, 4, 82, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(2, 4, 83, 1, NULL, NULL, NULL, 2, 1, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(3, 4, 85, 1, NULL, NULL, NULL, 3, 1, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(4, 4, 81, 1, NULL, NULL, NULL, 4, 1, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(5, 4, 84, 1, NULL, NULL, NULL, 5, 1, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(6, 4, 84, 1, NULL, NULL, NULL, 6, 1, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(7, 4, 85, 1, NULL, NULL, NULL, 7, 1, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(8, 4, 82, 1, NULL, NULL, NULL, 8, 1, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(9, 4, 81, 1, NULL, NULL, NULL, 1, 2, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(10, 4, 86, 1, NULL, NULL, NULL, 2, 2, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(11, 4, 86, 1, NULL, NULL, NULL, 3, 2, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(12, 4, 91, 1, NULL, NULL, NULL, 4, 2, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(13, 4, 90, 1, NULL, NULL, NULL, 5, 2, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(14, 4, 84, 1, NULL, NULL, NULL, 6, 2, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(15, 4, 84, 1, NULL, NULL, NULL, 7, 2, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(16, 4, 85, 1, NULL, NULL, NULL, 8, 2, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(17, 4, 81, 1, NULL, NULL, NULL, 1, 3, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(18, 4, 82, 1, NULL, NULL, NULL, 2, 3, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(19, 4, 83, 1, NULL, NULL, NULL, 4, 3, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(20, 4, 89, 1, NULL, NULL, NULL, 5, 3, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(21, 4, 93, 1, NULL, NULL, NULL, 6, 3, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(22, 4, 82, 1, NULL, NULL, NULL, 7, 3, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(23, 4, 94, 1, NULL, NULL, NULL, 1, 4, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(24, 4, 94, 1, NULL, NULL, NULL, 2, 4, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(25, 4, 81, 1, NULL, NULL, NULL, 3, 4, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(26, 4, 83, 1, NULL, NULL, NULL, 4, 4, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(27, 4, 95, 1, NULL, NULL, NULL, 5, 4, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(28, 4, 84, 1, NULL, NULL, NULL, 6, 4, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(29, 4, 82, 1, NULL, NULL, NULL, 7, 4, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(30, 4, 85, 1, NULL, NULL, NULL, 8, 4, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(31, 4, 81, 1, NULL, NULL, NULL, 1, 5, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(32, 4, 92, 1, NULL, NULL, NULL, 3, 5, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(33, 4, 84, 1, NULL, NULL, NULL, 4, 5, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(34, 4, 84, 1, NULL, NULL, NULL, 5, 5, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(35, 4, 85, 1, NULL, NULL, NULL, 6, 5, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(36, 4, 83, 1, NULL, NULL, NULL, 7, 5, NULL, NULL, 1, NULL, '2026-01-04 19:27:01', '2026-01-04 19:27:01'),
(37, 4, 100, 1, NULL, NULL, NULL, 2, 1, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(38, 4, 100, 1, NULL, NULL, NULL, 3, 1, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(39, 4, 99, 1, NULL, NULL, NULL, 4, 1, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(40, 4, 97, 1, NULL, NULL, NULL, 5, 1, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(41, 4, 111, 1, NULL, NULL, NULL, 6, 1, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(42, 4, 102, 1, NULL, NULL, NULL, 7, 1, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(43, 4, 102, 1, NULL, NULL, NULL, 8, 1, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(44, 4, 101, 1, NULL, NULL, NULL, 1, 2, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(45, 4, 101, 1, NULL, NULL, NULL, 2, 2, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(46, 4, 99, 1, NULL, NULL, NULL, 3, 2, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(47, 4, 100, 1, NULL, NULL, NULL, 4, 2, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(48, 4, 100, 1, NULL, NULL, NULL, 5, 2, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(49, 4, 97, 1, NULL, NULL, NULL, 6, 2, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(50, 4, 98, 1, NULL, NULL, NULL, 7, 2, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(51, 4, 106, 1, NULL, NULL, NULL, 8, 2, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(52, 4, 98, 1, NULL, NULL, NULL, 1, 3, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(53, 4, 98, 1, NULL, NULL, NULL, 2, 3, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(54, 4, 101, 1, NULL, NULL, NULL, 4, 3, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(55, 4, 100, 1, NULL, NULL, NULL, 5, 3, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(56, 4, 100, 1, NULL, NULL, NULL, 6, 3, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(57, 4, 109, 1, NULL, NULL, NULL, 7, 3, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(58, 4, 107, 1, NULL, NULL, NULL, 1, 4, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(59, 4, 100, 1, NULL, NULL, NULL, 3, 4, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(60, 4, 100, 1, NULL, NULL, NULL, 4, 4, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(61, 4, 98, 1, NULL, NULL, NULL, 5, 4, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(62, 4, 97, 1, NULL, NULL, NULL, 6, 4, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(63, 4, 101, 1, NULL, NULL, NULL, 7, 4, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(64, 4, 99, 1, NULL, NULL, NULL, 8, 4, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(65, 4, 97, 1, NULL, NULL, NULL, 1, 5, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(66, 4, 100, 1, NULL, NULL, NULL, 2, 5, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(67, 4, 100, 1, NULL, NULL, NULL, 3, 5, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(68, 4, 98, 1, NULL, NULL, NULL, 4, 5, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(69, 4, 101, 1, NULL, NULL, NULL, 5, 5, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(70, 4, 108, 1, NULL, NULL, NULL, 6, 5, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(71, 4, 111, 1, NULL, NULL, NULL, 7, 5, NULL, NULL, 1, NULL, '2026-01-04 19:30:12', '2026-01-04 19:30:12'),
(72, 4, 115, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(73, 4, 116, 1, NULL, NULL, NULL, 2, 1, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(74, 4, 116, 1, NULL, NULL, NULL, 3, 1, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(75, 4, 114, 1, NULL, NULL, NULL, 4, 1, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(76, 4, 118, 1, NULL, NULL, NULL, 5, 1, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(77, 4, 123, 1, NULL, NULL, NULL, 6, 1, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(78, 4, 117, 1, NULL, NULL, NULL, 7, 1, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(79, 4, 113, 1, NULL, NULL, NULL, 8, 1, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(80, 4, 113, 1, NULL, NULL, NULL, 2, 2, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(81, 4, 114, 1, NULL, NULL, NULL, 3, 2, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(82, 4, 117, 1, NULL, NULL, NULL, 4, 2, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(83, 4, 116, 1, NULL, NULL, NULL, 5, 2, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(84, 4, 116, 1, NULL, NULL, NULL, 6, 2, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(85, 4, 115, 1, NULL, NULL, NULL, 7, 2, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(86, 4, 114, 1, NULL, NULL, NULL, 2, 3, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(87, 4, 117, 1, NULL, NULL, NULL, 3, 3, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(88, 4, 113, 1, NULL, NULL, NULL, 4, 3, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(89, 4, 115, 1, NULL, NULL, NULL, 5, 3, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(90, 4, 116, 1, NULL, NULL, NULL, 6, 3, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(91, 4, 116, 1, NULL, NULL, NULL, 7, 3, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(92, 4, 123, 1, NULL, NULL, NULL, 8, 3, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(93, 4, 114, 1, NULL, NULL, NULL, 1, 4, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(94, 4, 113, 1, NULL, NULL, NULL, 3, 4, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(95, 4, 115, 1, NULL, NULL, NULL, 4, 4, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(96, 4, 116, 1, NULL, NULL, NULL, 5, 4, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(97, 4, 117, 1, NULL, NULL, NULL, 6, 4, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(98, 4, 116, 1, NULL, NULL, NULL, 7, 4, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(99, 4, 114, 1, NULL, NULL, NULL, 8, 4, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(100, 4, 117, 1, NULL, NULL, NULL, 1, 5, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(101, 4, 113, 1, NULL, NULL, NULL, 2, 5, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(102, 4, 114, 1, NULL, NULL, NULL, 3, 5, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(103, 4, 115, 1, NULL, NULL, NULL, 4, 5, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(104, 4, 116, 1, NULL, NULL, NULL, 5, 5, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(105, 4, 116, 1, NULL, NULL, NULL, 6, 5, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(106, 4, 118, 1, NULL, NULL, NULL, 7, 5, NULL, NULL, 1, NULL, '2026-01-04 19:31:37', '2026-01-04 19:31:37'),
(107, 4, 128, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:35:33'),
(108, 4, 130, 1, NULL, NULL, NULL, 2, 1, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:35:54'),
(109, 4, 128, 1, NULL, NULL, NULL, 3, 1, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(110, 4, 128, 1, NULL, NULL, NULL, 4, 1, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(111, 4, 132, 1, NULL, NULL, NULL, 5, 1, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(112, 4, 129, 1, NULL, NULL, NULL, 6, 1, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(113, 4, 130, 1, NULL, NULL, NULL, 2, 2, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(114, 4, 132, 1, NULL, NULL, NULL, 3, 2, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(115, 4, 128, 1, NULL, NULL, NULL, 4, 2, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(116, 4, 128, 1, NULL, NULL, NULL, 5, 2, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(117, 4, 131, 1, NULL, NULL, NULL, 6, 2, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(118, 4, 129, 1, NULL, NULL, NULL, 7, 2, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(119, 4, 138, 1, NULL, NULL, NULL, 8, 2, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(120, 4, 129, 1, NULL, NULL, NULL, 1, 3, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(121, 4, 130, 1, NULL, NULL, NULL, 3, 3, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(122, 4, 132, 1, NULL, NULL, NULL, 4, 3, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(123, 4, 131, 1, NULL, NULL, NULL, 5, 3, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(124, 4, 128, 1, NULL, NULL, NULL, 6, 3, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(125, 4, 128, 1, NULL, NULL, NULL, 7, 3, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(126, 4, 133, 1, NULL, NULL, NULL, 8, 3, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(127, 4, 132, 1, NULL, NULL, NULL, 1, 4, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(128, 4, 129, 1, NULL, NULL, NULL, 2, 4, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(129, 4, 130, 1, NULL, NULL, NULL, 3, 4, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(130, 4, 128, 1, NULL, NULL, NULL, 4, 4, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(131, 4, 128, 1, NULL, NULL, NULL, 5, 4, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(132, 4, 131, 1, NULL, NULL, NULL, 7, 4, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(133, 4, 138, 1, NULL, NULL, NULL, 8, 4, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(134, 4, 131, 1, NULL, NULL, NULL, 1, 5, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(135, 4, 130, 1, NULL, NULL, NULL, 2, 5, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(136, 4, 128, 1, NULL, NULL, NULL, 3, 5, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(137, 4, 128, 1, NULL, NULL, NULL, 4, 5, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(138, 4, 132, 1, NULL, NULL, NULL, 5, 5, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01'),
(139, 4, 129, 1, NULL, NULL, NULL, 6, 5, NULL, NULL, 1, NULL, '2026-01-04 19:33:01', '2026-01-04 19:33:01');

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
(4, 1, 'Schedule Main Copy', NULL, '2026-01-03', 1, 1, 1, 'active', NULL, NULL, NULL, 2, 2, '2026-01-03 02:59:54', '2026-01-03 10:02:24', NULL);

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
(1, 1, 'MSC ', 'msc ar', NULL, NULL, 1, 1, 1, 4, NULL, NULL, NULL, '2026-01-05 17:26:51');

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
(1, 'Semester 1', 1, NULL, '2025-08-24', '2026-01-10', 1, 1, NULL, 1, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
(2, 'Semester 2', 2, NULL, '2026-01-12', '2026-06-14', 1, 1, NULL, 0, NULL, '2026-01-03 01:30:12', '2026-01-03 01:30:12'),
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
('19qdXvawRdPzJm3VrLzENFRjABuCu4rUUMHVuD1x', 19, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSHBWMWpreVdXc1Y1UnpBNWtxalhXeUs2QXVPTTdtYXVMSGl4Q2F3eCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQ3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvd2Vla2x5LXN5c3RlbS9teS1zY2hlZHVsZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE5O30=', 1767647395),
('7i44MYi2lWwd8QxIP0dSInm1tu4WdfQUdAXRAAkQ', 19, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicHlpS05NN1lKUW1meEQxNlNRM3VhQ2ZaM1Y2eldKT29jbWpLaEhDZSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTk7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC93ZWVrbHktc3lzdGVtL215LXNjaGVkdWxlIjt9fQ==', 1767649513),
('Eo47ZiyELh69Kd6BGe2hWDdoJ6AI0elaxdMvJ9gH', 19, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiZ29nbUtWeVRnSk9aY2JJNVlYUmcxMGt0ckw0d1U4dExrbFpjbkxoVCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQ3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvd2Vla2x5LXN5c3RlbS9teS1zY2hlZHVsZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE5O3M6MjE6InBhc3N3b3JkX2hhc2hfc2FuY3R1bSI7czo2MDoiJDJ5JDEyJEtwMUVFRkZwTzdGU0lCRFhJRlBUd2VGczNoTUh0SVY0L3ZjZ3piOGtuemZjR1F4a3poT29DIjt9', 1767646723),
('SsDJDbmUdkOckCIZp6wQa328wisuQMF6jS47kFYS', 19, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiWEJFZmZYdnBwbnNFemJMaWNKY0dVZzNsQ3VlN0xFcnA2Rzlhb1dGaiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTk7czoyMToicGFzc3dvcmRfaGFzaF9zYW5jdHVtIjtzOjYwOiIkMnkkMTIkS3AxRUVGRnBPN0ZTSUJEWElGUFR3ZUZzM2hNSHRJVjQvdmNnemI4a256ZmNHUXhremhPb0MiO30=', 1767647497),
('tGizBk6q4psyGhuPiQUq1syRbMV94QpfdHTpBfRF', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Safari/605.1.15', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUzJUNE05RU40QTJmbE9YU0JRTHlXczhhSHR4QWZHaTd6dlBPak5TQSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3dlZWtseS1zeXN0ZW0vbXktc2NoZWR1bGUiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767648074);

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
(1, NULL, 'Super Admin', 'admin@myclass.com', NULL, '2026-01-03 01:24:40', '$2y$12$woHIUAmSa3UCZxvMAjSdE.VROSuQ.AOow1TiiNPQEbIzjb4Uwz1Ii', NULL, NULL, NULL, NULL, NULL, NULL, 'SuperAdmin', 1, NULL, NULL, 1, '2026-01-03 01:24:40', '2026-01-03 01:24:40', NULL),
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
(19, 1, 'Ahmed Mosad', 'tuhn06837', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, 'jOMVmlseshchS9lvunRw93btMCs73d4pzh1tPBDJGD9B1KZTRHaki8bXLdEF', NULL, NULL, 'teacher', 1, NULL, '2026-01-05 18:45:13', 1, '2026-01-03 02:29:42', '2026-01-05 18:45:13', NULL),
(20, 1, 'Emad Maghawry', 'tngqe2659', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(21, 1, 'Tarek Zanaty', 'tpgtx8677', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(22, 1, 'Yaser', 'tiuwd6356', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(23, 1, 'Mosaab', 'tzxjm3691', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL),
(24, 1, 'Hatem Alsawi', 'tvox02722', NULL, NULL, '$2y$12$Kp1EEFFpO7FSIBDXIFPTweFs3hMHtIV4/vcgzb8knzfcGQxkzhOoC', NULL, NULL, NULL, NULL, NULL, NULL, 'teacher', 1, NULL, NULL, 1, '2026-01-03 02:29:42', '2026-01-03 02:29:42', NULL);

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

--
-- Dumping data for table `weekly_plans`
--

INSERT INTO `weekly_plans` (`id`, `academic_year_id`, `semester_number`, `week_number`, `copy_id`, `schedule_id`, `cw`, `hw`, `notes`, `comments`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 4, 1, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(2, 1, 1, 2, 4, 2, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(3, 1, 1, 2, 4, 3, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(4, 1, 1, 2, 4, 4, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(5, 1, 1, 2, 4, 5, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(6, 1, 1, 2, 4, 6, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(7, 1, 1, 2, 4, 7, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(8, 1, 1, 2, 4, 8, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(9, 1, 1, 2, 4, 9, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(10, 1, 1, 2, 4, 10, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(11, 1, 1, 2, 4, 11, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(12, 1, 1, 2, 4, 12, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(13, 1, 1, 2, 4, 13, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(14, 1, 1, 2, 4, 14, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(15, 1, 1, 2, 4, 15, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(16, 1, 1, 2, 4, 16, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(17, 1, 1, 2, 4, 17, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(18, 1, 1, 2, 4, 18, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(19, 1, 1, 2, 4, 19, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(20, 1, 1, 2, 4, 20, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(21, 1, 1, 2, 4, 21, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(22, 1, 1, 2, 4, 22, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(23, 1, 1, 2, 4, 23, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(24, 1, 1, 2, 4, 24, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(25, 1, 1, 2, 4, 25, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(26, 1, 1, 2, 4, 26, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(27, 1, 1, 2, 4, 27, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(28, 1, 1, 2, 4, 28, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(29, 1, 1, 2, 4, 29, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(30, 1, 1, 2, 4, 30, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(31, 1, 1, 2, 4, 31, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(32, 1, 1, 2, 4, 32, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(33, 1, 1, 2, 4, 33, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(34, 1, 1, 2, 4, 34, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(35, 1, 1, 2, 4, 35, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(36, 1, 1, 2, 4, 36, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(37, 1, 1, 2, 4, 37, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(38, 1, 1, 2, 4, 38, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(39, 1, 1, 2, 4, 39, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(40, 1, 1, 2, 4, 40, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(41, 1, 1, 2, 4, 41, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(42, 1, 1, 2, 4, 42, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(43, 1, 1, 2, 4, 43, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(44, 1, 1, 2, 4, 44, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(45, 1, 1, 2, 4, 45, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(46, 1, 1, 2, 4, 46, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(47, 1, 1, 2, 4, 47, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(48, 1, 1, 2, 4, 48, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(49, 1, 1, 2, 4, 49, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(50, 1, 1, 2, 4, 50, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(51, 1, 1, 2, 4, 51, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(52, 1, 1, 2, 4, 52, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(53, 1, 1, 2, 4, 53, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(54, 1, 1, 2, 4, 54, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(55, 1, 1, 2, 4, 55, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(56, 1, 1, 2, 4, 56, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(57, 1, 1, 2, 4, 57, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(58, 1, 1, 2, 4, 58, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(59, 1, 1, 2, 4, 59, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(60, 1, 1, 2, 4, 60, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(61, 1, 1, 2, 4, 61, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(62, 1, 1, 2, 4, 62, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(63, 1, 1, 2, 4, 63, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(64, 1, 1, 2, 4, 64, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(65, 1, 1, 2, 4, 65, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(66, 1, 1, 2, 4, 66, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(67, 1, 1, 2, 4, 67, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(68, 1, 1, 2, 4, 68, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(69, 1, 1, 2, 4, 69, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(70, 1, 1, 2, 4, 70, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(71, 1, 1, 2, 4, 71, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(72, 1, 1, 2, 4, 72, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(73, 1, 1, 2, 4, 73, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(74, 1, 1, 2, 4, 74, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(75, 1, 1, 2, 4, 75, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(76, 1, 1, 2, 4, 76, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(77, 1, 1, 2, 4, 77, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(78, 1, 1, 2, 4, 78, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(79, 1, 1, 2, 4, 79, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(80, 1, 1, 2, 4, 80, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(81, 1, 1, 2, 4, 81, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(82, 1, 1, 2, 4, 82, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(83, 1, 1, 2, 4, 83, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(84, 1, 1, 2, 4, 84, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(85, 1, 1, 2, 4, 85, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(86, 1, 1, 2, 4, 86, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(87, 1, 1, 2, 4, 87, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(88, 1, 1, 2, 4, 88, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(89, 1, 1, 2, 4, 89, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(90, 1, 1, 2, 4, 90, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(91, 1, 1, 2, 4, 91, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(92, 1, 1, 2, 4, 92, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(93, 1, 1, 2, 4, 93, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(94, 1, 1, 2, 4, 94, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(95, 1, 1, 2, 4, 95, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(96, 1, 1, 2, 4, 96, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(97, 1, 1, 2, 4, 97, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(98, 1, 1, 2, 4, 98, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(99, 1, 1, 2, 4, 99, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(100, 1, 1, 2, 4, 100, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(101, 1, 1, 2, 4, 101, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(102, 1, 1, 2, 4, 102, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(103, 1, 1, 2, 4, 103, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(104, 1, 1, 2, 4, 104, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(105, 1, 1, 2, 4, 105, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(106, 1, 1, 2, 4, 106, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(107, 1, 1, 2, 4, 107, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(108, 1, 1, 2, 4, 108, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(109, 1, 1, 2, 4, 109, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(110, 1, 1, 2, 4, 110, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(111, 1, 1, 2, 4, 111, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(112, 1, 1, 2, 4, 112, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(113, 1, 1, 2, 4, 113, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(114, 1, 1, 2, 4, 114, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(115, 1, 1, 2, 4, 115, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(116, 1, 1, 2, 4, 116, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(117, 1, 1, 2, 4, 117, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(118, 1, 1, 2, 4, 118, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(119, 1, 1, 2, 4, 119, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(120, 1, 1, 2, 4, 120, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(121, 1, 1, 2, 4, 121, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(122, 1, 1, 2, 4, 122, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(123, 1, 1, 2, 4, 123, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(124, 1, 1, 2, 4, 124, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(125, 1, 1, 2, 4, 125, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(126, 1, 1, 2, 4, 126, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(127, 1, 1, 2, 4, 127, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(128, 1, 1, 2, 4, 128, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(129, 1, 1, 2, 4, 129, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(130, 1, 1, 2, 4, 130, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(131, 1, 1, 2, 4, 131, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(132, 1, 1, 2, 4, 132, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(133, 1, 1, 2, 4, 133, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(134, 1, 1, 2, 4, 134, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(135, 1, 1, 2, 4, 135, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(136, 1, 1, 2, 4, 136, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(137, 1, 1, 2, 4, 137, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(138, 1, 1, 2, 4, 138, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34'),
(139, 1, 1, 2, 4, 139, '', '', '', NULL, '2026-01-05 16:45:34', '2026-01-05 16:45:34');

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
  ADD KEY `menus_module_index` (`module`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4020;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `schedule_copies`
--
ALTER TABLE `schedule_copies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_behaviors`
--
ALTER TABLE `student_behaviors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_behaviors_mains`
--
ALTER TABLE `student_behaviors_mains`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_behaviors_point_actions`
--
ALTER TABLE `student_behaviors_point_actions`
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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

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
