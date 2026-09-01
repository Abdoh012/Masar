-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260814.7ff5dd5b7e
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 25, 2026 at 08:13 PM
-- Server version: 9.1.0
-- PHP Version: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
create Database `masar`;
use `masar`;
--

-- --------------------------------------------------------

--
-- Table structure for table `application_answers`
--
CREATE TABLE `application_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `application_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--
CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1022, NULL, 'register_success', 'user', 2025, '[]', '{\"role\": \"student\", \"email\": \"httptest.valid.student+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:21'),
(1023, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bad.field+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:22'),
(1024, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bad.spec+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:23'),
(1025, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.mismatch+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:24'),
(1026, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bypass+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:24'),
(1027, NULL, 'register_success', 'user', 2030, '[]', '{\"role\": \"student\", \"email\": \"httptest.ids.student+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:28'),
(1028, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.ids.mismatch+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:29'),
(1029, NULL, 'register_success', 'user', 2032, '[]', '{\"role\": \"company\", \"email\": \"httptest.company+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:33'),
(1030, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.bad.industry+160517@masar.test\"}', '::1', NULL, '2026-08-21 19:05:34'),
(1031, NULL, 'register_success', 'user', 2034, '[]', '{\"role\": \"student\", \"email\": \"httptest.valid.student+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:34'),
(1032, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bad.field+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:35'),
(1033, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bad.spec+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:35'),
(1034, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.mismatch+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:36'),
(1035, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bypass+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:37'),
(1036, NULL, 'register_success', 'user', 2039, '[]', '{\"role\": \"student\", \"email\": \"httptest.ids.student+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:41'),
(1037, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.ids.mismatch+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:42'),
(1038, NULL, 'register_success', 'user', 2041, '[]', '{\"role\": \"company\", \"email\": \"httptest.company+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:46'),
(1039, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.bad.industry+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:46'),
(1040, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.multi+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:47'),
(1041, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.mixedids+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:06:48'),
(1042, NULL, 'register_success', 'user', 2045, '[]', '{\"role\": \"student\", \"email\": \"httptest.valid.student+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:14'),
(1043, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bad.field+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:15'),
(1044, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bad.spec+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:16'),
(1045, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.mismatch+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:17'),
(1046, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bypass+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:17'),
(1047, NULL, 'register_success', 'user', 2050, '[]', '{\"role\": \"student\", \"email\": \"httptest.ids.student+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:22'),
(1048, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.ids.mismatch+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:22'),
(1049, NULL, 'register_success', 'user', 2052, '[]', '{\"role\": \"company\", \"email\": \"httptest.company+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:26'),
(1050, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.bad.industry+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:27'),
(1051, NULL, 'register_success', 'user', 2054, '[]', '{\"role\": \"company\", \"email\": \"httptest.multi+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:31'),
(1052, NULL, 'register_success', 'user', 2055, '[]', '{\"role\": \"company\", \"email\": \"httptest.mixedids+160810@masar.test\"}', '::1', NULL, '2026-08-21 19:08:35'),
(1053, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.rollback@masar.test\"}', '::1', NULL, '2026-08-21 19:10:40'),
(1054, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"httptest.rollback@masar.test\"}', '::1', NULL, '2026-08-21 19:10:40'),
(1055, 2019, 'login_success', 'user', 2019, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-21 19:10:41'),
(1056, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"httptest.company+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:10:41'),
(1057, 2007, 'login_success', 'user', 2007, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', NULL, '2026-08-21 19:10:42'),
(1058, 2020, 'login_success', 'user', 2020, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-21 19:10:44'),
(1059, NULL, 'register_success', 'user', 2057, '[]', '{\"role\": \"student\", \"email\": \"httptest.valid.student+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:37'),
(1060, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bad.field+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:38'),
(1061, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bad.spec+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:39'),
(1062, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.mismatch+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:40'),
(1063, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.bypass+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:40'),
(1064, NULL, 'register_success', 'user', 2062, '[]', '{\"role\": \"student\", \"email\": \"httptest.ids.student+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:45'),
(1065, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"httptest.ids.mismatch+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:46'),
(1066, NULL, 'register_success', 'user', 2064, '[]', '{\"role\": \"company\", \"email\": \"httptest.company+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:50'),
(1067, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.bad.industry+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:51'),
(1068, NULL, 'register_success', 'user', 2066, '[]', '{\"role\": \"company\", \"email\": \"httptest.multi+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:11:55'),
(1069, NULL, 'register_success', 'user', 2067, '[]', '{\"role\": \"company\", \"email\": \"httptest.mixedids+161133@masar.test\"}', '::1', NULL, '2026-08-21 19:12:00'),
(1070, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.rollback@masar.test\"}', '::1', NULL, '2026-08-21 19:12:01'),
(1071, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"httptest.rollback@masar.test\"}', '::1', NULL, '2026-08-21 19:12:01'),
(1072, 2019, 'login_success', 'user', 2019, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-21 19:12:02'),
(1073, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"httptest.company+160630@masar.test\"}', '::1', NULL, '2026-08-21 19:12:03'),
(1074, 2007, 'login_success', 'user', 2007, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', NULL, '2026-08-21 19:12:04'),
(1075, 2020, 'login_success', 'user', 2020, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-21 19:12:06'),
(1076, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"company\", \"email\": \"httptest.rollback@masar.test\"}', '::1', NULL, '2026-08-21 19:12:34'),
(1077, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"httptest.rollback@masar.test\"}', '::1', NULL, '2026-08-21 19:12:34'),
(1078, 2019, 'login_success', 'user', 2019, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-21 19:12:35'),
(1079, NULL, 'register_success', 'user', 2070, '[]', '{\"role\": \"company\", \"email\": \"httptest.pending.login@masar.test\"}', '::1', NULL, '2026-08-21 19:12:40'),
(1080, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"httptest.pending.login@masar.test\"}', '::1', NULL, '2026-08-21 19:12:40'),
(1081, 2007, 'login_success', 'user', 2007, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', NULL, '2026-08-21 19:12:41'),
(1082, 2020, 'login_success', 'user', 2020, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-21 19:12:43'),
(1083, 2071, 'register_success', 'user', 2071, '[]', '{\"role\": \"student\", \"email\": \"student.register@test.local\"}', '::1', 'PostmanRuntime/2.3.0', '2026-08-21 19:28:52'),
(1084, 2072, 'register_success', 'user', 2072, '[]', '{\"role\": \"company\", \"email\": \"company.register@test.local\"}', '::1', 'PostmanRuntime/2.3.0', '2026-08-21 19:32:23'),
(1085, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:06'),
(1086, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:07'),
(1087, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:08'),
(1088, 2092, 'login_success', 'user', 2092, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:09'),
(1089, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:10'),
(1090, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:11'),
(1091, 2088, 'login_success', 'user', 2088, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:16'),
(1092, 2099, 'login_success', 'user', 2099, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:17'),
(1093, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:18'),
(1094, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:19'),
(1095, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:20'),
(1096, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:21'),
(1097, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:22'),
(1098, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:23'),
(1099, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:12:59'),
(1100, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:13:51'),
(1101, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:20'),
(1102, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:21'),
(1103, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:22'),
(1104, 2092, 'login_success', 'user', 2092, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:24'),
(1105, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:25'),
(1106, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:26'),
(1107, 2088, 'login_success', 'user', 2088, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:28'),
(1108, 2099, 'login_success', 'user', 2099, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:29'),
(1109, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:30'),
(1110, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:31'),
(1111, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:33'),
(1112, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:34'),
(1113, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:35'),
(1114, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:15:37'),
(1115, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:36'),
(1116, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:37'),
(1117, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:38'),
(1118, 2092, 'login_success', 'user', 2092, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:39'),
(1119, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:40'),
(1120, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:41'),
(1121, 2088, 'login_success', 'user', 2088, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:42'),
(1122, 2099, 'login_success', 'user', 2099, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:43'),
(1123, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:44'),
(1124, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:45'),
(1125, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:46'),
(1126, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:47'),
(1127, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:48'),
(1128, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:18:49'),
(1129, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:44:08'),
(1130, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:46:23'),
(1131, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:46:24'),
(1132, 2103, 'login_success', 'user', 2103, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:46:26'),
(1133, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:46:28'),
(1134, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:46:29'),
(1135, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:47:06'),
(1136, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:47:47'),
(1137, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:48:22'),
(1138, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:48:23'),
(1139, 2103, 'login_success', 'user', 2103, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:48:25'),
(1140, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:48:26'),
(1141, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:48:27'),
(1142, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:49:16'),
(1143, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:49:20'),
(1144, 2103, 'login_success', 'user', 2103, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:49:23'),
(1145, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:49:25'),
(1146, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:49:26'),
(1147, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:21'),
(1148, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:22'),
(1149, 2103, 'login_success', 'user', 2103, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:24'),
(1150, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:25'),
(1151, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:26'),
(1152, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:49'),
(1153, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:50'),
(1154, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:51'),
(1155, 2092, 'login_success', 'user', 2092, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:52'),
(1156, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:53'),
(1157, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:54'),
(1158, 2088, 'login_success', 'user', 2088, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:55'),
(1159, 2099, 'login_success', 'user', 2099, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:56'),
(1160, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:57'),
(1161, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:58'),
(1162, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:52:59'),
(1163, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:01'),
(1164, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:02'),
(1165, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:03'),
(1166, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:44'),
(1167, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:46'),
(1168, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:47'),
(1169, 2092, 'login_success', 'user', 2092, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:48'),
(1170, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:49'),
(1171, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:50'),
(1172, 2088, 'login_success', 'user', 2088, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:51'),
(1173, 2099, 'login_success', 'user', 2099, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:52'),
(1174, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:53'),
(1175, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:55'),
(1176, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:56'),
(1177, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:57'),
(1178, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:53:58'),
(1179, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 20:54:00'),
(1180, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@test.local\"}', '::1', 'PostmanRuntime/2.3.0', '2026-08-21 20:59:47'),
(1181, 2071, 'login_success', 'user', 2071, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.3.0', '2026-08-21 21:00:45'),
(1182, 2071, 'login_success', 'user', 2071, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.3.0', '2026-08-21 21:13:10'),
(1183, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:28:43'),
(1184, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:13'),
(1185, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:15'),
(1186, 2073, 'login_success', 'user', 2073, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:16'),
(1187, 2083, 'login_success', 'user', 2083, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:16'),
(1188, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:29'),
(1189, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:30'),
(1190, 2103, 'login_success', 'user', 2103, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:32'),
(1191, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:33'),
(1192, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:36:34'),
(1193, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:20'),
(1194, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:21'),
(1195, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:22'),
(1196, 2092, 'login_success', 'user', 2092, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:23'),
(1197, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:24'),
(1198, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:24'),
(1199, 2088, 'login_success', 'user', 2088, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:25'),
(1200, 2099, 'login_success', 'user', 2099, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:26'),
(1201, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:27'),
(1202, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:28'),
(1203, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:29'),
(1204, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:30'),
(1205, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:31'),
(1206, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 21:37:32'),
(1207, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:06:30'),
(1208, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:08:54'),
(1209, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:09:46'),
(1210, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:14:46'),
(1211, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:22:17'),
(1212, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:23:07'),
(1213, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:25:38'),
(1214, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:26:16'),
(1215, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:27:53'),
(1216, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:29:03'),
(1217, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:30:13'),
(1218, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:30:15'),
(1219, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:32:26'),
(1220, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:32:30'),
(1221, 2104, 'login_success', 'user', 2104, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:34:36'),
(1222, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:34:40'),
(1223, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:33'),
(1224, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:35'),
(1225, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:41'),
(1226, 2092, 'login_success', 'user', 2092, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:43'),
(1227, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:44'),
(1228, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:45'),
(1229, 2088, 'login_success', 'user', 2088, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:49'),
(1230, 2099, 'login_success', 'user', 2099, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:50'),
(1231, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:51'),
(1232, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:53'),
(1233, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:54'),
(1234, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:56'),
(1235, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:36:58'),
(1236, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:00'),
(1237, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:45'),
(1238, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:46'),
(1239, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:47'),
(1240, 2092, 'login_success', 'user', 2092, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:48'),
(1241, 2091, 'login_success', 'user', 2091, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:49'),
(1242, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:50'),
(1243, 2088, 'login_success', 'user', 2088, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:51'),
(1244, 2099, 'login_success', 'user', 2099, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:52'),
(1245, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:54'),
(1246, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:55'),
(1247, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:56'),
(1248, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:57'),
(1249, 2094, 'login_success', 'user', 2094, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:37:59'),
(1250, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:38:00'),
(1251, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:38:04'),
(1252, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:38:05'),
(1253, 2103, 'login_success', 'user', 2103, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:38:08'),
(1254, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:38:10'),
(1255, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:38:11'),
(1256, 2100, 'login_success', 'user', 2100, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:38:59'),
(1257, 2101, 'login_success', 'user', 2101, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:39:01'),
(1258, 2103, 'login_success', 'user', 2103, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:39:03'),
(1259, 2090, 'login_success', 'user', 2090, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:39:04'),
(1260, 2087, 'login_success', 'user', 2087, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-21 22:39:05'),
(1261, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@test.local\"}', '::1', 'PostmanRuntime/2.3.4', '2026-08-22 13:49:48'),
(1262, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@test.local\"}', '::1', 'PostmanRuntime/2.3.4', '2026-08-22 13:50:38'),
(1263, 2071, 'login_success', 'user', 2071, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.3.4', '2026-08-22 13:53:12'),
(1264, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@test.local\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-22 14:44:21'),
(1265, 2071, 'login_success', 'user', 2071, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-08-22 14:45:19'),
(1266, 2150, 'register_success', 'user', 2150, '[]', '{\"role\": \"company\", \"email\": \"mediacare97@gmail.com\"}', '::1', 'PostmanRuntime/2.4.0', '2026-08-25 01:17:19'),
(1267, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"mmosalem105@gmail.com\"}', '::1', 'node', '2026-08-25 02:35:50'),
(1268, 2151, 'register_success', 'user', 2151, '[]', '{\"role\": \"student\", \"email\": \"mammuslim2003@gmail.com\"}', '::1', 'PostmanRuntime/2.4.0', '2026-08-25 17:00:32'),
(1269, 2152, 'register_success', 'user', 2152, '[]', '{\"role\": \"company\", \"email\": \"mediacare@gmail.com\"}', '::1', 'PostmanRuntime/2.4.0', '2026-08-25 17:01:56');

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--
CREATE TABLE `auth_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `revoked_at`, `ip_address`, `user_agent`, `created_at`) VALUES
(143, 2071, '1058e7f86bff6553c0d18cf24c178a1dbd2c3580a9609057797b82919d129ea9', '2026-08-28 16:28:52', NULL, '::1', 'PostmanRuntime/2.3.0', '2026-08-21 19:28:52'),
(144, 2151, 'fa3eddc0ff9865b8fbca4b724425627dc56b38c1017380994db05f55f9047e07', '2026-09-01 14:00:31', NULL, '::1', 'PostmanRuntime/2.4.0', '2026-08-25 17:00:31');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--
CREATE TABLE `certificates` (
  `id` bigint UNSIGNED NOT NULL,
  `certificate_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `training_id` bigint UNSIGNED NOT NULL,
  `training_session_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','issued','active','valid','revoked','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `grade_label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_eligible` tinyint(1) NOT NULL DEFAULT '0',
  `requested_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revocation_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_appeals`
--
CREATE TABLE `certificate_appeals` (
  `id` bigint UNSIGNED NOT NULL,
  `certificate_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('submitted','under_review','approved','rejected','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--
CREATE TABLE `companies` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `legal_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_logo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_at` datetime DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `user_id`, `legal_name`, `description`, `website`, `phone`, `city`, `company_logo`, `address`, `approval_status`, `approved_at`, `approved_by`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(900, 2007, '[AuthTest] CodeWave Technologies', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:35', NULL, NULL, '2026-08-21 18:56:35', '2026-08-21 18:56:35'),
(901, 2008, '[AuthTest] DataPulse AI', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:35', NULL, NULL, '2026-08-21 18:56:35', '2026-08-21 18:56:35'),
(902, 2009, '[AuthTest] TechNova Labs', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:36', NULL, NULL, '2026-08-21 18:56:36', '2026-08-21 18:56:36'),
(903, 2010, '[AuthTest] Delta Engineering', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:36', NULL, NULL, '2026-08-21 18:56:36', '2026-08-21 18:56:36'),
(904, 2011, '[AuthTest] BuildCore Engineering', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:37', NULL, NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37'),
(905, 2012, '[AuthTest] MediCare Center', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:37', NULL, NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37'),
(906, 2013, '[AuthTest] PharmaLife', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:37', NULL, NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37'),
(907, 2014, '[AuthTest] MarketPro', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:38', NULL, NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38'),
(908, 2015, '[AuthTest] DesignHub', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:38', NULL, NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38'),
(909, 2016, '[AuthTest] LawBridge', 'Development test company seeded by auth_registration_test_seeder.', NULL, NULL, NULL, NULL, NULL, 'approved', '2026-08-21 18:56:38', NULL, NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38'),
(919, 2072, 'MASAR Test Solutions', 'A test company used to verify company registration.', NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, '2026-08-21 19:32:19', '2026-08-21 19:34:28'),
(920, 2073, '[TrainingMatchTest] Delta Mechanics Co.', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://delta-mechanics.test', '+201000000053', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:08:59', NULL, NULL, '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(921, 2074, '[TrainingMatchTest] CodeWave Software', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://codewave.test', '+201000000029', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:08:59', NULL, NULL, '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(922, 2075, '[TrainingMatchTest] DataPulse Analytics', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://datapulse.test', '+201000000075', 'Giza', NULL, NULL, 'approved', '2026-08-21 17:08:59', NULL, NULL, '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(923, 2076, '[TrainingMatchTest] SecureShield Systems', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://secureshield.test', '+201000000040', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:08:59', NULL, NULL, '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(924, 2077, '[TrainingMatchTest] Nile Build Construction', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://nilebuild.test', '+201000000070', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:09:00', NULL, NULL, '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(925, 2078, '[TrainingMatchTest] VoltEdge Electrical', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://voltedge.test', '+201000000014', 'Alexandria', NULL, NULL, 'approved', '2026-08-21 17:09:00', NULL, NULL, '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(926, 2079, '[TrainingMatchTest] BrightReach Marketing', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://brightreach.test', '+201000000039', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:09:00', NULL, NULL, '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(927, 2080, '[TrainingMatchTest] TalentBridge HR', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://talentbridge.test', '+201000000012', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:09:01', NULL, NULL, '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(928, 2081, '[TrainingMatchTest] PixelCraft Studio', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://pixelcraft.test', '+201000000002', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:09:01', NULL, NULL, '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(929, 2082, '[TrainingMatchTest] LedgerPro Accounting', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://ledgerpro.test', '+201000000074', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:09:01', NULL, NULL, '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(930, 2083, '[TrainingMatchTest] TechNova Solutions', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://technova.test', '+201000000078', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:09:01', NULL, NULL, '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(931, 2084, '[TrainingMatchTest] Union Industries Group', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://unionind.test', '+201000000052', 'Alexandria', NULL, NULL, 'approved', '2026-08-21 17:09:02', NULL, NULL, '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(932, 2085, '[TrainingMatchTest] MediaHub Agency', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://mediahub.test', '+201000000028', 'Cairo', NULL, NULL, 'approved', '2026-08-21 17:09:02', NULL, NULL, '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(933, 2086, '[TrainingMatchTest] Ghost Startup Labs', 'Development test company seeded by training_matching_seeder for specialization-based matching verification.', 'https://ghostlabs.test', '+201000000034', 'Cairo', NULL, NULL, 'pending', NULL, NULL, NULL, '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(934, 2100, '[TrainingSpecTest] Company A Mechanics', 'Development test company seeded by training_spec_inheritance_seeder.', NULL, NULL, 'Cairo', NULL, NULL, 'approved', '2026-08-21 20:42:32', NULL, NULL, '2026-08-21 20:42:32', '2026-08-21 20:42:32'),
(935, 2101, '[TrainingSpecTest] Company B Software', 'Development test company seeded by training_spec_inheritance_seeder.', NULL, NULL, 'Cairo', NULL, NULL, 'approved', '2026-08-21 20:42:32', NULL, NULL, '2026-08-21 20:42:32', '2026-08-21 20:42:32'),
(936, 2102, '[TrainingSpecTest] Company C Business', 'Development test company seeded by training_spec_inheritance_seeder.', NULL, NULL, 'Giza', NULL, NULL, 'approved', '2026-08-21 20:42:33', NULL, NULL, '2026-08-21 20:42:33', '2026-08-21 20:42:33'),
(937, 2103, '[TrainingSpecTest] Company D NoSpecialization', 'Development test company seeded by training_spec_inheritance_seeder.', NULL, NULL, 'Cairo', NULL, NULL, 'approved', '2026-08-21 20:42:33', NULL, NULL, '2026-08-21 20:42:33', '2026-08-21 20:42:33'),
(938, 2104, '[CompanyLogoTest] Engineering Corp', 'Development test company seeded by company_logo_seeder.', NULL, NULL, 'Cairo', 'company-logo-test/engineering.png', NULL, 'approved', '2026-08-21 22:04:04', NULL, NULL, '2026-08-21 22:04:04', '2026-08-21 22:34:42'),
(939, 2105, '[CompanyLogoTest] Software Corp', 'Development test company seeded by company_logo_seeder.', NULL, NULL, 'Cairo', 'company-logo-test/software.png', NULL, 'approved', '2026-08-21 22:04:04', NULL, NULL, '2026-08-21 22:04:04', '2026-08-21 22:04:04'),
(940, 2106, '[CompanyLogoTest] Data Corp', 'Development test company seeded by company_logo_seeder.', NULL, NULL, 'Giza', 'company-logo-test/data.png', NULL, 'approved', '2026-08-21 22:04:04', NULL, NULL, '2026-08-21 22:04:04', '2026-08-21 22:04:04'),
(959, 2150, 'MASAR Test Solutions', 'A test company used to verify company registration.', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, '2026-08-25 01:17:13', '2026-08-25 01:17:13'),
(960, 2152, 'MASAR Test Solutions', 'A test company used to verify company registration.', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, '2026-08-25 17:01:52', '2026-08-25 17:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `company_specializations`
--
CREATE TABLE `company_specializations` (
  `company_id` bigint UNSIGNED NOT NULL,
  `specialization_id` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_specializations`
--

INSERT INTO `company_specializations` (`company_id`, `specialization_id`, `created_at`) VALUES
(900, 103, '2026-08-21 18:56:35'),
(901, 105, '2026-08-21 18:56:35'),
(902, 103, '2026-08-21 18:56:36'),
(902, 104, '2026-08-21 18:56:36'),
(902, 105, '2026-08-21 18:56:36'),
(902, 107, '2026-08-21 18:56:36'),
(903, 92, '2026-08-21 18:56:36'),
(904, 93, '2026-08-21 18:56:37'),
(905, 96, '2026-08-21 18:56:37'),
(906, 100, '2026-08-21 18:56:37'),
(907, 108, '2026-08-21 18:56:38'),
(907, 118, '2026-08-21 18:56:38'),
(908, 119, '2026-08-21 18:56:38'),
(909, 112, '2026-08-21 18:56:38'),
(919, 103, '2026-08-21 19:32:19'),
(920, 92, '2026-08-21 20:08:59'),
(921, 103, '2026-08-21 20:08:59'),
(922, 105, '2026-08-21 20:08:59'),
(923, 106, '2026-08-21 20:08:59'),
(924, 93, '2026-08-21 20:09:00'),
(925, 94, '2026-08-21 20:09:00'),
(926, 108, '2026-08-21 20:09:00'),
(927, 109, '2026-08-21 20:09:01'),
(928, 119, '2026-08-21 20:09:01'),
(929, 122, '2026-08-21 20:09:01'),
(930, 103, '2026-08-21 20:09:02'),
(930, 104, '2026-08-21 20:09:02'),
(930, 105, '2026-08-21 20:09:02'),
(930, 107, '2026-08-21 20:09:02'),
(931, 92, '2026-08-21 20:09:02'),
(931, 94, '2026-08-21 20:09:02'),
(932, 108, '2026-08-21 20:09:02'),
(932, 118, '2026-08-21 20:09:02'),
(932, 130, '2026-08-21 20:09:02'),
(933, 103, '2026-08-21 20:09:02'),
(934, 92, '2026-08-21 20:42:32'),
(935, 103, '2026-08-21 20:51:12'),
(935, 105, '2026-08-21 20:51:43'),
(936, 108, '2026-08-21 20:42:33'),
(936, 126, '2026-08-21 20:42:33'),
(936, 127, '2026-08-21 20:42:33'),
(938, 92, '2026-08-21 22:04:04'),
(939, 103, '2026-08-21 22:04:04'),
(940, 105, '2026-08-21 22:04:04'),
(959, 103, '2026-08-25 01:17:13'),
(960, 103, '2026-08-25 17:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `company_work_fields`
--
CREATE TABLE `company_work_fields` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `field_id` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_work_fields`
--

INSERT INTO `company_work_fields` (`id`, `company_id`, `field_id`, `created_at`) VALUES
(33, 920, 9, '2026-08-21 20:08:59'),
(34, 921, 12, '2026-08-21 20:08:59'),
(35, 922, 12, '2026-08-21 20:08:59'),
(36, 923, 12, '2026-08-21 20:08:59'),
(37, 924, 9, '2026-08-21 20:09:00'),
(38, 925, 9, '2026-08-21 20:09:00'),
(39, 926, 13, '2026-08-21 20:09:00'),
(40, 927, 13, '2026-08-21 20:09:01'),
(41, 928, 16, '2026-08-21 20:09:01'),
(42, 929, 17, '2026-08-21 20:09:01'),
(43, 930, 12, '2026-08-21 20:09:02'),
(44, 931, 9, '2026-08-21 20:09:02'),
(45, 932, 15, '2026-08-21 20:09:02'),
(46, 933, 12, '2026-08-21 20:09:02');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--
CREATE TABLE `conversations` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `application_id` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `degrees`
--
CREATE TABLE `degrees` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` enum('diploma','bachelor','master','doctorate','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bachelor',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--
CREATE TABLE `faculties` (
  `id` bigint UNSIGNED NOT NULL,
  `university_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--
CREATE TABLE `files` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('cv','profile_image','certificate_attachment','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `user_id`, `type`, `original_name`, `stored_name`, `path`, `mime_type`, `size_bytes`, `created_at`) VALUES
(291, 2104, 'profile_image', 'brand-new-logo.png', '20260821_489d66ef25c7ebacf4b1b25aeecaca8c.png', 'C:\\laragon\\www\\Masar\\backend\\app/storage/uploads\\companies\\20260821_489d66ef25c7ebacf4b1b25aeecaca8c.png', 'image/png', 70, '2026-08-21 22:34:39');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--
CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `conversation_id` bigint UNSIGNED NOT NULL,
  `sender_user_id` bigint UNSIGNED NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--
CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `email_sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_states`
--
CREATE TABLE `oauth_states` (
  `id` bigint UNSIGNED NOT NULL,
  `nonce` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--
CREATE TABLE `password_resets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--
CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `training_id` bigint UNSIGNED NOT NULL,
  `training_session_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `platform_commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `platform_commission_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `company_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('manual','paymob','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` enum('pending','paid','failed','refunded','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `external_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--
CREATE TABLE `refresh_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `revoked_at`, `created_at`) VALUES
(808, 2019, '210a63b8df52f367980681d6dcfac3da96ab976b286dd843f5a81bcac7a8b819', '2026-09-20 16:12:35', NULL, '2026-08-21 19:12:35'),
(809, 2007, '35a1cc83e0622311289743638d52c2298247b4a9e4f61008b90c048007db5d38', '2026-09-20 16:12:41', NULL, '2026-08-21 19:12:41'),
(810, 2020, '355e9fd2398ee8fda07764eb69568e29cb03120ce2152d8e7b8148262119c13f', '2026-09-20 16:12:42', NULL, '2026-08-21 19:12:43'),
(911, 2073, '64d705b9d639f0dbd0e18d964d786593595713bc51c19ba0caad3bbf403fca43', '2026-09-20 18:36:15', NULL, '2026-08-21 21:36:15'),
(912, 2083, '16bd958de1cd92a9a34e303eddecf739d79489cb44e929e07793595a707be70e', '2026-09-20 18:36:16', NULL, '2026-08-21 21:36:16'),
(946, 2104, 'e5433a8f049d4c41a502435147c36c740c4295ce371e2ea58ed8c6be2aea937d', '2026-09-20 19:34:35', NULL, '2026-08-21 22:34:35'),
(965, 2092, 'b41f2eb3337db5123ce6fb7ae6cfba09e236f5f3e95354f9e1482f1acdc770cc', '2026-09-20 19:37:48', NULL, '2026-08-21 22:37:48'),
(966, 2091, '79cf84aab82b51fd663bea8a526884b4ab651fea74a9efbb379f5b6e12f551bc', '2026-09-20 19:37:49', NULL, '2026-08-21 22:37:49'),
(968, 2088, 'bc1f8a7ee47ade8b4c94685c576b9b3e3dfebd3e03a872566f033679f725fe1f', '2026-09-20 19:37:51', NULL, '2026-08-21 22:37:51'),
(969, 2099, '449f3ac34bf5d6f0530d80b86ff1eb890321f606ea6dc80821fc952f942178ac', '2026-09-20 19:37:52', NULL, '2026-08-21 22:37:52'),
(974, 2094, '646cdc6f473b0bfa4ebbe41a767279b428933b89e160d0e3ace5763a2b88c1ee', '2026-09-20 19:37:59', NULL, '2026-08-21 22:37:59'),
(981, 2100, 'e198c9971ceb6a7557b909d79ddf841c7997640daa805badce112746c692ad9d', '2026-09-20 19:38:58', NULL, '2026-08-21 22:38:58'),
(982, 2101, '63a3ed540cd23c0e4c1abcf7a846142f103020da00302d3a0371e1a0bd10eb42', '2026-09-20 19:39:00', NULL, '2026-08-21 22:39:00'),
(983, 2103, '5b9ca3d70de540ed918ac5a3c1de244386ea3c306f8149bef9c81194e68cdd3b', '2026-09-20 19:39:02', NULL, '2026-08-21 22:39:02'),
(984, 2090, '10a275a856d1de2b819b42e4996a69214f2161f05897abb0748429df3d50a382', '2026-09-20 19:39:04', NULL, '2026-08-21 22:39:04'),
(985, 2087, 'fdd5f6696116a690f81d2ddece0227bab27579baaff081a0219a11df91184572', '2026-09-20 19:39:05', NULL, '2026-08-21 22:39:05'),
(987, 2071, 'e6d9f0229c7fadb3595abba0e77d07b3b238742da7ba40434e0cce2436430e03', '2026-09-21 11:45:18', NULL, '2026-08-22 14:45:19');

-- --------------------------------------------------------

--
-- Table structure for table `revoked_access_tokens`
--
CREATE TABLE `revoked_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_trainings`
--
CREATE TABLE `saved_trainings` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `training_id` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--
CREATE TABLE `skills` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--
CREATE TABLE `specializations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `field_id` bigint UNSIGNED DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `name`, `parent_id`, `field_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(92, 'Mechanical Engineering', NULL, 9, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(93, 'Civil Engineering', NULL, 9, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(94, 'Electrical Engineering', NULL, 9, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(95, 'Architecture', NULL, 9, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(96, 'General Medicine', NULL, 10, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(97, 'Surgery', NULL, 10, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(98, 'Pediatrics', NULL, 10, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(99, 'Cardiology', NULL, 10, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(100, 'Clinical Pharmacy', NULL, 11, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(101, 'Pharmaceutical Industry', NULL, 11, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(102, 'Pharmacology', NULL, 11, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(103, 'Software Engineering', NULL, 12, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(104, 'Artificial Intelligence', NULL, 12, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(105, 'Data Science', NULL, 12, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(106, 'Cyber Security', NULL, 12, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(107, 'Web Development', NULL, 12, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(108, 'Marketing', NULL, 13, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(109, 'Human Resources', NULL, 13, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(110, 'Business Administration', NULL, 13, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(111, 'Sales', NULL, 13, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(112, 'Corporate Law', NULL, 14, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(113, 'Criminal Law', NULL, 14, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(114, 'Commercial Law', NULL, 14, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(115, 'Journalism', NULL, 15, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(116, 'Digital Media', NULL, 15, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(117, 'Broadcasting', NULL, 15, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(118, 'Digital Marketing', NULL, 15, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(119, 'UI/UX Design', NULL, 16, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(120, 'Product Design', NULL, 16, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(121, 'Graphic Design', NULL, 16, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(122, 'Financial Accounting', NULL, 17, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(123, 'Management Accounting', NULL, 17, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(124, 'Auditing', NULL, 17, NULL, 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(125, 'Computer Engineering', NULL, NULL, NULL, 1, '2026-08-21 20:08:58', '2026-08-21 20:08:58'),
(126, 'Accounting', NULL, NULL, NULL, 1, '2026-08-21 20:08:58', '2026-08-21 20:08:58'),
(127, 'Finance', NULL, NULL, NULL, 1, '2026-08-21 20:08:58', '2026-08-21 20:08:58'),
(128, 'Dentistry', NULL, NULL, NULL, 1, '2026-08-21 20:08:58', '2026-08-21 20:08:58'),
(129, 'Pharmacy', NULL, NULL, NULL, 1, '2026-08-21 20:08:58', '2026-08-21 20:08:58'),
(130, 'Content Creation', NULL, NULL, NULL, 1, '2026-08-21 20:08:58', '2026-08-21 20:08:58');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--
CREATE TABLE `students` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `university_id` bigint UNSIGNED DEFAULT NULL,
  `faculty_id` bigint UNSIGNED DEFAULT NULL,
  `field_id` bigint UNSIGNED DEFAULT NULL,
  `degree_id` bigint UNSIGNED DEFAULT NULL,
  `specialization_id` bigint UNSIGNED DEFAULT NULL,
  `graduation_year` year DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image_file_id` bigint UNSIGNED DEFAULT NULL,
  `cv_file_id` bigint UNSIGNED DEFAULT NULL,
  `is_profile_complete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `full_name`, `phone`, `bio`, `university_id`, `faculty_id`, `field_id`, `degree_id`, `specialization_id`, `graduation_year`, `city`, `profile_image_file_id`, `cv_file_id`, `is_profile_complete`, `created_at`, `updated_at`) VALUES
(1043, 2017, '[AuthTest] Ahmed Hassan', NULL, NULL, NULL, NULL, 9, NULL, 92, NULL, NULL, NULL, NULL, 1, '2026-08-21 18:56:38', '2026-08-21 18:56:38'),
(1044, 2018, '[AuthTest] Sara Mohamed', NULL, NULL, NULL, NULL, 9, NULL, 93, NULL, NULL, NULL, NULL, 1, '2026-08-21 18:56:39', '2026-08-21 18:56:39'),
(1045, 2019, '[AuthTest] Omar Ali', NULL, NULL, NULL, NULL, 12, NULL, 103, NULL, NULL, NULL, NULL, 1, '2026-08-21 18:56:39', '2026-08-21 19:12:42'),
(1046, 2020, '[AuthTest] Laila Mostafa', NULL, NULL, NULL, NULL, 12, NULL, 105, NULL, NULL, NULL, NULL, 1, '2026-08-21 18:56:39', '2026-08-21 18:56:39'),
(1047, 2021, '[AuthTest] Karim Adel', NULL, NULL, NULL, NULL, 12, NULL, 104, NULL, NULL, NULL, NULL, 1, '2026-08-21 18:56:40', '2026-08-21 18:56:40'),
(1048, 2022, '[AuthTest] Mona Samir', NULL, NULL, NULL, NULL, 10, NULL, 99, NULL, NULL, NULL, NULL, 1, '2026-08-21 18:56:40', '2026-08-21 18:56:40'),
(1049, 2023, '[AuthTest] Youssef Tarek', NULL, NULL, NULL, NULL, 13, NULL, 108, NULL, NULL, NULL, NULL, 1, '2026-08-21 18:56:40', '2026-08-21 18:56:40'),
(1050, 2024, '[AuthTest] Dina Ahmed', NULL, NULL, NULL, NULL, 14, NULL, 112, NULL, NULL, NULL, NULL, 1, '2026-08-21 18:56:40', '2026-08-21 18:56:40'),
(1059, 2071, 'Test Student', NULL, NULL, NULL, NULL, 9, NULL, 92, NULL, NULL, NULL, NULL, 0, '2026-08-21 19:28:48', '2026-08-21 19:28:48'),
(1060, 2087, '[TrainingMatchTest] Omar Hassan', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 9, NULL, 92, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:03', '2026-08-21 20:09:03'),
(1061, 2088, '[TrainingMatchTest] Nada Sherif', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 9, NULL, 94, '2027', 'Alexandria', NULL, NULL, 1, '2026-08-21 20:09:03', '2026-08-21 20:09:03'),
(1062, 2089, '[TrainingMatchTest] Sara Adel', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 9, NULL, 93, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:03', '2026-08-21 20:09:03'),
(1063, 2090, '[TrainingMatchTest] Karim Fouad', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 12, NULL, 103, '2027', 'Giza', NULL, NULL, 1, '2026-08-21 20:09:03', '2026-08-21 20:09:03'),
(1064, 2091, '[TrainingMatchTest] Laila Mostafa', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 12, NULL, 105, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:04', '2026-08-21 20:09:04'),
(1065, 2092, '[TrainingMatchTest] Ahmed Zaki', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 12, NULL, 104, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:04', '2026-08-21 20:09:04'),
(1066, 2093, '[TrainingMatchTest] Mona Said', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 12, NULL, 107, '2027', 'Alexandria', NULL, NULL, 1, '2026-08-21 20:09:04', '2026-08-21 20:09:04'),
(1067, 2094, '[TrainingMatchTest] Youssef Nabil', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 13, NULL, 108, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:05', '2026-08-21 20:09:05'),
(1068, 2095, '[TrainingMatchTest] Hala Ramzy', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 13, NULL, 109, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:05', '2026-08-21 20:09:05'),
(1069, 2096, '[TrainingMatchTest] Tarek Samir', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 17, NULL, 122, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:05', '2026-08-21 20:09:05'),
(1070, 2097, '[TrainingMatchTest] Nour Khaled', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 16, NULL, 119, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:05', '2026-08-21 20:09:05'),
(1071, 2098, '[TrainingMatchTest] Rami Anwar', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 14, NULL, 112, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:06', '2026-08-21 20:09:06'),
(1072, 2099, '[TrainingMatchTest] Dina Ehab', NULL, 'Development test student seeded by training_matching_seeder.', NULL, NULL, 10, NULL, NULL, '2027', 'Cairo', NULL, NULL, 1, '2026-08-21 20:09:06', '2026-08-21 20:09:06'),
(1098, 2151, 'Test Student', NULL, NULL, NULL, NULL, 9, NULL, 92, NULL, NULL, NULL, NULL, 0, '2026-08-25 17:00:25', '2026-08-25 17:00:25');

-- --------------------------------------------------------

--
-- Table structure for table `student_skills`
--
CREATE TABLE `student_skills` (
  `student_id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED NOT NULL,
  `proficiency` enum('beginner','intermediate','advanced','expert') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `study_fields`
--
CREATE TABLE `study_fields` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `study_fields`
--

INSERT INTO `study_fields` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(9, 'Engineering', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(10, 'Medicine', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(11, 'Pharmacy', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(12, 'Computer Science', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(13, 'Business', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(14, 'Law', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(15, 'Media', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(16, 'Design', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34'),
(17, 'Accounting', 1, '2026-08-21 18:56:34', '2026-08-21 18:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `training_applications`
--
CREATE TABLE `training_applications` (
  `id` bigint UNSIGNED NOT NULL,
  `training_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `why_interested` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `what_to_learn` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `skills` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('submitted','accepted','rejected','withdrawn') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `rejection_reason` enum('position_filled','candidate_not_suitable','requirements_not_met','training_closed','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejection_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `applied_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `withdrawn_at` datetime DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `cv_file_id` bigint UNSIGNED DEFAULT NULL,
  `university` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `faculty_id` bigint UNSIGNED DEFAULT NULL,
  `applicant_type` enum('student','graduated') DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `graduation_year` year DEFAULT NULL,
  `motivation` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_listings`
--
CREATE TABLE `training_listings` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `training_type` enum('shadowing','hands_on','project_based') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode` enum('onsite','remote','hybrid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `may_lead_to_employment` tinyint(1) NOT NULL DEFAULT '0',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `compensation_amount` decimal(12,2) DEFAULT NULL,
  `compensation_currency` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `trial_period_days` int UNSIGNED DEFAULT NULL,
  `capacity` int UNSIGNED DEFAULT NULL,
  `status` enum('draft','published','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `application_deadline` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `location` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `training_listings`
--

INSERT INTO `training_listings` (`id`, `company_id`, `title`, `description`, `training_type`, `mode`, `may_lead_to_employment`, `is_paid`, `compensation_amount`, `compensation_currency`, `trial_period_days`, `capacity`, `status`, `published_at`, `starts_at`, `ends_at`, `application_deadline`, `closed_at`, `location`, `created_at`, `updated_at`) VALUES
(773, 900, 'Full-Stack Web Development Internship', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:35', '2026-09-04 18:56:35', '2026-11-04 18:56:35', '2026-08-31 18:56:35', NULL, NULL, '2026-08-21 18:56:35', '2026-08-21 18:56:35'),
(774, 901, 'Data Analytics Summer Training', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:35', '2026-09-04 18:56:35', '2026-11-04 18:56:35', '2026-08-31 18:56:35', NULL, NULL, '2026-08-21 18:56:35', '2026-08-21 18:56:35'),
(775, 902, 'AI Research Shadowing Program', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:36', '2026-09-04 18:56:36', '2026-11-04 18:56:36', '2026-08-31 18:56:36', NULL, NULL, '2026-08-21 18:56:36', '2026-08-21 18:56:36'),
(776, 903, 'Mechanical Design Hands-On Training', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:36', '2026-09-04 18:56:36', '2026-11-04 18:56:36', '2026-08-31 18:56:36', NULL, NULL, '2026-08-21 18:56:36', '2026-08-21 18:56:36'),
(777, 904, 'Site Engineering Field Training', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:37', '2026-09-04 18:56:37', '2026-11-04 18:56:37', '2026-08-31 18:56:37', NULL, NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37'),
(778, 905, 'Clinical Rotation Shadowing', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:37', '2026-09-04 18:56:37', '2026-11-04 18:56:37', '2026-08-31 18:56:37', NULL, NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37'),
(779, 906, 'Clinical Pharmacy Practical Training', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:37', '2026-09-04 18:56:37', '2026-11-04 18:56:37', '2026-08-31 18:56:37', NULL, NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37'),
(780, 907, 'Digital Marketing Campaign Project', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:38', '2026-09-04 18:56:38', '2026-11-04 18:56:38', '2026-08-31 18:56:38', NULL, NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38'),
(781, 908, 'UI/UX Design Studio Training', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:38', '2026-09-04 18:56:38', '2026-11-04 18:56:38', '2026-08-31 18:56:38', NULL, NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38'),
(782, 909, 'Corporate Law Office Shadowing', 'Development test training listing seeded by auth_registration_test_seeder.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 10, 'published', '2026-08-21 18:56:38', '2026-09-04 18:56:38', '2026-11-04 18:56:38', '2026-08-31 18:56:38', NULL, NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38'),
(783, 920, 'Mechanical Design Intern', 'Realistic test opportunity: Mechanical Design Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'onsite', 0, 1, 3000.00, 'EGP', 7, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(784, 920, 'CAD Engineering Trainee', 'Realistic test opportunity: CAD Engineering Trainee. Part of the MASAR matching-test dataset.', 'project_based', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(785, 920, 'Manufacturing Engineering Intern', 'Realistic test opportunity: Manufacturing Engineering Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'onsite', 0, 1, 2500.00, 'EGP', 7, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(786, 920, 'HVAC Engineering Trainee', 'Realistic test opportunity: HVAC Engineering Trainee. Part of the MASAR matching-test dataset.', 'shadowing', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(787, 921, 'Backend PHP Developer Intern', 'Realistic test opportunity: Backend PHP Developer Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'remote', 0, 1, 4000.00, 'EGP', 7, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(788, 921, 'React Frontend Intern', 'Realistic test opportunity: React Frontend Intern. Part of the MASAR matching-test dataset.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(789, 921, 'Laravel Developer Trainee', 'Realistic test opportunity: Laravel Developer Trainee. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 1, 3500.00, 'EGP', 7, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(790, 921, 'Full Stack Developer Intern', 'Realistic test opportunity: Full Stack Developer Intern. Part of the MASAR matching-test dataset.', 'project_based', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(791, 922, 'Data Analyst Intern', 'Realistic test opportunity: Data Analyst Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 1, 3000.00, 'EGP', 7, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Giza, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(792, 922, 'Machine Learning Trainee', 'Realistic test opportunity: Machine Learning Trainee. Part of the MASAR matching-test dataset.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Giza, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(793, 922, 'Python Data Science Intern', 'Realistic test opportunity: Python Data Science Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'remote', 0, 1, 3200.00, 'EGP', 7, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Giza, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(794, 923, 'SOC Analyst Intern', 'Realistic test opportunity: SOC Analyst Intern. Part of the MASAR matching-test dataset.', 'shadowing', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(795, 923, 'Cyber Security Trainee', 'Realistic test opportunity: Cyber Security Trainee. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 1, 3500.00, 'EGP', 7, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(796, 923, 'Penetration Testing Intern', 'Realistic test opportunity: Penetration Testing Intern. Part of the MASAR matching-test dataset.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:08:59', '2026-08-28 17:08:59', '2026-11-19 17:08:59', '2026-09-20 17:08:59', NULL, 'Cairo, Egypt', '2026-08-21 20:08:59', '2026-08-21 20:08:59'),
(797, 924, 'Civil Site Engineer Intern', 'Realistic test opportunity: Civil Site Engineer Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'onsite', 0, 1, 2800.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', NULL, 'Cairo, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(798, 924, 'Structural Engineering Trainee', 'Realistic test opportunity: Structural Engineering Trainee. Part of the MASAR matching-test dataset.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', NULL, 'Cairo, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(799, 924, 'Construction Management Intern', 'Realistic test opportunity: Construction Management Intern. Part of the MASAR matching-test dataset.', 'shadowing', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', NULL, 'Cairo, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(800, 925, 'Electrical Design Intern', 'Realistic test opportunity: Electrical Design Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 1, 3000.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', NULL, 'Alexandria, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(801, 925, 'Power Systems Trainee', 'Realistic test opportunity: Power Systems Trainee. Part of the MASAR matching-test dataset.', 'shadowing', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', NULL, 'Alexandria, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(802, 925, 'Embedded Systems Intern', 'Realistic test opportunity: Embedded Systems Intern. Part of the MASAR matching-test dataset.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', NULL, 'Alexandria, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(803, 926, 'Digital Marketing Intern', 'Realistic test opportunity: Digital Marketing Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 1, 2000.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', NULL, 'Cairo, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(804, 926, 'Marketing Specialist Trainee', 'Realistic test opportunity: Marketing Specialist Trainee. Part of the MASAR matching-test dataset.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', NULL, 'Cairo, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(805, 926, 'Social Media Marketing Intern', 'Realistic test opportunity: Social Media Marketing Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'closed', '2026-06-22 17:09:00', '2026-08-28 17:09:00', '2026-11-19 17:09:00', '2026-09-20 17:09:00', '2026-08-21 17:09:00', 'Cairo, Egypt', '2026-08-21 20:09:00', '2026-08-21 20:09:00'),
(806, 927, 'HR Intern', 'Realistic test opportunity: HR Intern. Part of the MASAR matching-test dataset.', 'shadowing', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:01', '2026-08-28 17:09:01', '2026-11-19 17:09:01', '2026-09-20 17:09:01', NULL, 'Cairo, Egypt', '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(807, 927, 'Talent Acquisition Trainee', 'Realistic test opportunity: Talent Acquisition Trainee. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 1, 2200.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:01', '2026-08-28 17:09:01', '2026-11-19 17:09:01', '2026-09-20 17:09:01', NULL, 'Cairo, Egypt', '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(808, 928, 'UI/UX Designer Intern', 'Realistic test opportunity: UI/UX Designer Intern. Part of the MASAR matching-test dataset.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:01', '2026-08-28 17:09:01', '2026-11-19 17:09:01', '2026-09-20 17:09:01', NULL, 'Cairo, Egypt', '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(809, 928, 'Product Design Trainee', 'Realistic test opportunity: Product Design Trainee. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 1, 2600.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:01', '2026-08-28 17:09:01', '2026-11-19 17:09:01', '2026-09-20 17:09:01', NULL, 'Cairo, Egypt', '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(810, 929, 'Accounting Intern', 'Realistic test opportunity: Accounting Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:01', '2026-08-28 17:09:01', '2026-11-19 17:09:01', '2026-09-20 17:09:01', NULL, 'Cairo, Egypt', '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(811, 929, 'Financial Reporting Trainee', 'Realistic test opportunity: Financial Reporting Trainee. Part of the MASAR matching-test dataset.', 'project_based', 'hybrid', 0, 1, 2400.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:01', '2026-08-28 17:09:01', '2026-11-19 17:09:01', '2026-09-20 17:09:01', NULL, 'Cairo, Egypt', '2026-08-21 20:09:01', '2026-08-21 20:09:01'),
(812, 930, 'Backend Microservices Intern', 'Realistic test opportunity: Backend Microservices Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'remote', 0, 1, 4500.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(813, 930, 'Web Development Intern', 'Realistic test opportunity: Web Development Intern. Part of the MASAR matching-test dataset.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(814, 930, 'AI Engineer Intern', 'Realistic test opportunity: AI Engineer Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 1, 5000.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(815, 930, 'Machine Learning Intern', 'Realistic test opportunity: Machine Learning Intern. Part of the MASAR matching-test dataset.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(816, 930, 'NLP Research Trainee', 'Realistic test opportunity: NLP Research Trainee. Part of the MASAR matching-test dataset.', 'shadowing', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(817, 930, 'Data Insights Engineer Intern', 'Realistic test opportunity: Data Insights Engineer Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'remote', 0, 1, 4200.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(818, 931, 'CNC Maintenance Intern', 'Realistic test opportunity: CNC Maintenance Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'onsite', 0, 1, 2700.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Alexandria, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(819, 931, 'Control Systems Trainee', 'Realistic test opportunity: Control Systems Trainee. Part of the MASAR matching-test dataset.', 'shadowing', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Alexandria, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(820, 931, 'Robotics Engineering Intern', 'Realistic test opportunity: Robotics Engineering Intern. Part of the MASAR matching-test dataset.', 'project_based', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Alexandria, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(821, 932, 'Brand Campaign Intern', 'Realistic test opportunity: Brand Campaign Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(822, 932, 'SEO Content Intern', 'Realistic test opportunity: SEO Content Intern. Part of the MASAR matching-test dataset.', 'project_based', 'remote', 0, 1, 1800.00, 'EGP', 7, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(823, 932, 'Video Content Creation Intern', 'Realistic test opportunity: Video Content Creation Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 17:09:02', '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(824, 933, 'Backend PHP Developer Intern', 'Realistic test opportunity: Backend PHP Developer Intern. Part of the MASAR matching-test dataset.', 'hands_on', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'draft', NULL, '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(825, 933, 'React Frontend Intern', 'Realistic test opportunity: React Frontend Intern. Part of the MASAR matching-test dataset.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'draft', NULL, '2026-08-28 17:09:02', '2026-11-19 17:09:02', '2026-09-20 17:09:02', NULL, 'Cairo, Egypt', '2026-08-21 20:09:02', '2026-08-21 20:09:02'),
(826, 934, 'Mechanical Design Training', 'Realistic test opportunity: Mechanical Design Training. Part of the MASAR training-specialization-inheritance dataset.', 'hands_on', 'onsite', 0, 1, 3000.00, 'EGP', 7, 5, 'published', '2026-08-21 20:42:32', '2026-08-28 17:42:32', '2026-11-19 17:42:32', '2026-09-20 17:42:32', NULL, 'Cairo, Egypt', '2026-08-21 20:42:32', '2026-08-21 20:42:32'),
(827, 934, 'CAD Engineering Training', 'Realistic test opportunity: CAD Engineering Training. Part of the MASAR training-specialization-inheritance dataset.', 'project_based', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 20:42:32', '2026-08-28 17:42:32', '2026-11-19 17:42:32', '2026-09-20 17:42:32', NULL, 'Cairo, Egypt', '2026-08-21 20:42:32', '2026-08-21 20:42:32'),
(828, 935, 'Backend Development Internship', 'Realistic test opportunity: Backend Development Internship. Part of the MASAR training-specialization-inheritance dataset.', 'hands_on', 'remote', 0, 1, 4000.00, 'EGP', 7, 5, 'published', '2026-08-21 20:42:32', '2026-08-28 17:42:32', '2026-11-19 17:42:32', '2026-09-20 17:42:32', NULL, 'Cairo, Egypt', '2026-08-21 20:42:32', '2026-08-21 20:42:32'),
(829, 935, 'Data Science Internship', 'Realistic test opportunity: Data Science Internship. Part of the MASAR training-specialization-inheritance dataset.', 'project_based', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 20:42:32', '2026-08-28 17:42:32', '2026-11-19 17:42:32', '2026-09-20 17:42:32', NULL, 'Cairo, Egypt', '2026-08-21 20:42:32', '2026-08-21 20:42:32'),
(830, 935, 'AI/Software Training', 'Realistic test opportunity: AI/Software Training. Part of the MASAR training-specialization-inheritance dataset.', 'hands_on', 'onsite', 0, 1, 3500.00, 'EGP', 7, 5, 'published', '2026-08-21 20:42:32', '2026-08-28 17:42:32', '2026-11-19 17:42:32', '2026-09-20 17:42:32', NULL, 'Cairo, Egypt', '2026-08-21 20:42:32', '2026-08-21 20:42:32'),
(831, 936, 'Digital Marketing Training', 'Realistic test opportunity: Digital Marketing Training. Part of the MASAR training-specialization-inheritance dataset.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 20:42:33', '2026-08-28 17:42:33', '2026-11-19 17:42:33', '2026-09-20 17:42:33', NULL, 'Giza, Egypt', '2026-08-21 20:42:33', '2026-08-21 20:42:33'),
(832, 936, 'Finance Internship', 'Realistic test opportunity: Finance Internship. Part of the MASAR training-specialization-inheritance dataset.', 'shadowing', 'hybrid', 0, 1, 2500.00, 'EGP', 7, 5, 'published', '2026-08-21 20:42:33', '2026-08-28 17:42:33', '2026-11-19 17:42:33', '2026-09-20 17:42:33', NULL, 'Giza, Egypt', '2026-08-21 20:42:33', '2026-08-21 20:42:33'),
(839, 934, '[HTTPTest2] Diag Training', 'diag', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 20:47:47', '2026-08-21 20:47:47'),
(850, 934, '[HTTPTest] A Single Spec Training', 'Created by inheritance HTTP test for Company A.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 20:52:21', '2026-08-21 20:52:21'),
(853, 937, '[HTTPTest] D No Spec Training', 'Company without company_specializations.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 20:52:24', '2026-08-21 20:52:24'),
(856, 935, '[FieldRemovalTest] Client Override Attempt', 'Client tries to control specializations.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 21:36:14', '2026-08-21 21:36:14'),
(857, 934, '[FieldRemovalTest] A No Field Training', 'Created without any field.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 21:36:15', '2026-08-21 21:36:15'),
(858, 930, '[FieldRemovalTest] E Multi Spec', 'Multi company creation.', 'hands_on', 'hybrid', 0, 1, NULL, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 21:36:16', '2026-08-21 21:36:16'),
(859, 934, '[HTTPTest] A Single Spec Training', 'Created by inheritance HTTP test for Company A.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 21:36:29', '2026-08-21 21:36:29'),
(862, 937, '[HTTPTest] D No Spec Training', 'Company without company_specializations.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 21:36:32', '2026-08-21 21:36:32'),
(864, 938, '[CompanyLogoTest] Mechanical Design Training', 'Realistic test opportunity: [CompanyLogoTest] Mechanical Design Training. Part of the MASAR company-logo dataset.', 'hands_on', 'onsite', 0, 1, 2500.00, 'EGP', 7, 5, 'published', '2026-08-21 22:04:04', '2026-08-28 19:04:04', '2026-11-19 19:04:04', '2026-09-20 19:04:04', NULL, 'Cairo, Egypt', '2026-08-21 22:04:04', '2026-08-21 22:04:04'),
(865, 938, '[CompanyLogoTest] CAD Engineering Training', 'Realistic test opportunity: [CompanyLogoTest] CAD Engineering Training. Part of the MASAR company-logo dataset.', 'project_based', 'hybrid', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 22:04:04', '2026-08-28 19:04:04', '2026-11-19 19:04:04', '2026-09-20 19:04:04', NULL, 'Cairo, Egypt', '2026-08-21 22:04:04', '2026-08-21 22:04:04'),
(866, 939, '[CompanyLogoTest] Backend Internship', 'Realistic test opportunity: [CompanyLogoTest] Backend Internship. Part of the MASAR company-logo dataset.', 'hands_on', 'remote', 0, 1, 2500.00, 'EGP', 7, 5, 'published', '2026-08-21 22:04:04', '2026-08-28 19:04:04', '2026-11-19 19:04:04', '2026-09-20 19:04:04', NULL, 'Cairo, Egypt', '2026-08-21 22:04:04', '2026-08-21 22:04:04'),
(867, 939, '[CompanyLogoTest] Frontend Internship', 'Realistic test opportunity: [CompanyLogoTest] Frontend Internship. Part of the MASAR company-logo dataset.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 5, 'published', '2026-08-21 22:04:04', '2026-08-28 19:04:04', '2026-11-19 19:04:04', '2026-09-20 19:04:04', NULL, 'Cairo, Egypt', '2026-08-21 22:04:04', '2026-08-21 22:04:04'),
(868, 940, '[CompanyLogoTest] Data Analyst Training', 'Realistic test opportunity: [CompanyLogoTest] Data Analyst Training. Part of the MASAR company-logo dataset.', 'hands_on', 'hybrid', 0, 1, 2500.00, 'EGP', 7, 5, 'published', '2026-08-21 22:04:04', '2026-08-28 19:04:04', '2026-11-19 19:04:04', '2026-09-20 19:04:04', NULL, 'Giza, Egypt', '2026-08-21 22:04:04', '2026-08-21 22:04:04'),
(869, 934, '[HTTPTest] A Single Spec Training', 'Created by inheritance HTTP test for Company A.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 22:38:04', '2026-08-21 22:38:04'),
(872, 937, '[HTTPTest] D No Spec Training', 'Company without company_specializations.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 22:38:08', '2026-08-21 22:38:08'),
(874, 934, '[HTTPTest] A Single Spec Training', 'Created by inheritance HTTP test for Company A.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 22:38:59', '2026-08-21 22:38:59'),
(875, 935, '[HTTPTest] B Multi Spec Training', 'Created by inheritance HTTP test for Company B.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 22:39:01', '2026-08-21 22:39:01'),
(876, 935, '[HTTPTest] B Second Training', 'Second creation for the same company.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 22:39:01', '2026-08-21 22:39:01'),
(877, 937, '[HTTPTest] D No Spec Training', 'Company without company_specializations.', 'hands_on', 'remote', 0, 1, 1000.00, 'EGP', 7, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 22:39:03', '2026-08-21 22:39:03');

-- --------------------------------------------------------

--
-- Table structure for table `training_questions`
--
CREATE TABLE `training_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `training_id` bigint UNSIGNED NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_type` enum('text','textarea','select','radio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `options` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_sessions`
--
CREATE TABLE `training_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `application_id` bigint UNSIGNED NOT NULL,
  `training_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `status` enum('trial','continuing','completed','stopped','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `started_at` datetime NOT NULL,
  `trial_started_at` datetime DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `student_continuation_confirmed_at` datetime DEFAULT NULL,
  `actual_ended_at` datetime DEFAULT NULL,
  `employment_opportunity` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_skills`
--
CREATE TABLE `training_skills` (
  `training_id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_specializations`
--
CREATE TABLE `training_specializations` (
  `training_id` bigint UNSIGNED NOT NULL,
  `specialization_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_specializations`
--

INSERT INTO `training_specializations` (`training_id`, `specialization_id`) VALUES
(776, 92),
(783, 92),
(784, 92),
(785, 92),
(786, 92),
(818, 92),
(820, 92),
(826, 92),
(827, 92),
(839, 92),
(857, 92),
(864, 92),
(865, 92),
(874, 92),
(777, 93),
(797, 93),
(798, 93),
(799, 93),
(800, 94),
(801, 94),
(802, 94),
(819, 94),
(778, 96),
(779, 100),
(773, 103),
(775, 103),
(787, 103),
(788, 103),
(789, 103),
(790, 103),
(812, 103),
(824, 103),
(825, 103),
(828, 103),
(829, 103),
(830, 103),
(856, 103),
(858, 103),
(866, 103),
(867, 103),
(875, 103),
(876, 103),
(775, 104),
(814, 104),
(815, 104),
(816, 104),
(858, 104),
(774, 105),
(775, 105),
(791, 105),
(792, 105),
(793, 105),
(817, 105),
(828, 105),
(829, 105),
(830, 105),
(856, 105),
(858, 105),
(868, 105),
(875, 105),
(876, 105),
(794, 106),
(795, 106),
(796, 106),
(775, 107),
(813, 107),
(858, 107),
(780, 108),
(803, 108),
(804, 108),
(805, 108),
(821, 108),
(831, 108),
(832, 108),
(806, 109),
(807, 109),
(782, 112),
(780, 118),
(822, 118),
(781, 119),
(808, 119),
(809, 119),
(810, 122),
(811, 122),
(831, 126),
(832, 126),
(831, 127),
(832, 127),
(823, 130);

-- --------------------------------------------------------

--
-- Table structure for table `universities`
--
CREATE TABLE `universities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `role` enum('student','company','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','pending','suspended','rejected','deleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `email`, `password_hash`, `status`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2007, 'company', 'authtest.codewave@masar.test', '$2y$12$VEqHQchzJ6pQ9LVumqn7Dujc8UwzuF5qsNsL6cElVS8RABknut0L6', 'active', '2026-08-21 18:56:35', '2026-08-21 19:12:41', '2026-08-21 18:56:35', '2026-08-21 19:12:41', NULL),
(2008, 'company', 'authtest.datapulse@masar.test', '$2y$12$mxozJiTqQk3Ec0WtDoK5xuLIszJm13ykQ.sn1LxNqL0xSLXkafPaO', 'active', '2026-08-21 18:56:35', NULL, '2026-08-21 18:56:35', '2026-08-21 18:56:35', NULL),
(2009, 'company', 'authtest.technova@masar.test', '$2y$12$y1ipdgMoq7rdL/6UWAqALultjwXVCPt2I9BABf/0f7sMP57GBFwD.', 'active', '2026-08-21 18:56:36', NULL, '2026-08-21 18:56:36', '2026-08-21 18:56:36', NULL),
(2010, 'company', 'authtest.delta@masar.test', '$2y$12$0elDFgeMAX0TIj.9G5yRyecsCD18JGVtsurY5yd2KW8wfoAYnJCY6', 'active', '2026-08-21 18:56:36', NULL, '2026-08-21 18:56:36', '2026-08-21 18:56:36', NULL),
(2011, 'company', 'authtest.buildcore@masar.test', '$2y$12$tQNtDcqswUE/UYLIKCdrCO66/xS7/JNjPngkDGmgLLg07V9gFmXDi', 'active', '2026-08-21 18:56:37', NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37', NULL),
(2012, 'company', 'authtest.medicare@masar.test', '$2y$12$zmT3hCbSOiqXsz8wX7w4/uj0w.LIVImM/9HJkokApz18.eoPk26nK', 'active', '2026-08-21 18:56:37', NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37', NULL),
(2013, 'company', 'authtest.pharmalife@masar.test', '$2y$12$LtyoJ/59dkjU7mgg7PXA3.Z15tUBqC1aHXoHRKcxBWaModMNKjmwa', 'active', '2026-08-21 18:56:37', NULL, '2026-08-21 18:56:37', '2026-08-21 18:56:37', NULL),
(2014, 'company', 'authtest.marketpro@masar.test', '$2y$12$JGqDh1.UyCr/.H09jASGG.KiF.3NDR11PA6OR/koAVUHmQoCY67sS', 'active', '2026-08-21 18:56:38', NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38', NULL),
(2015, 'company', 'authtest.designhub@masar.test', '$2y$12$HfUE/d2hlq0hDmu1B2pLSOsJBzhEzxb70qtbPUUTC5o1qe3C7Pwei', 'active', '2026-08-21 18:56:38', NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38', NULL),
(2016, 'company', 'authtest.lawbridge@masar.test', '$2y$12$3FBzhD2cKTLQVo5XdgH1u.ZUfvKAKqDUbyAbHqOCaMD7RDHSXyIRC', 'active', '2026-08-21 18:56:38', NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38', NULL),
(2017, 'student', 'authtest.ahmed@masar.test', '$2y$12$FlrqUeNVMBKO0fQMUpN6OuPzRRf3SFn/HDc8xXASNjy/LPlxw3Nay', 'active', '2026-08-21 18:56:38', NULL, '2026-08-21 18:56:38', '2026-08-21 18:56:38', NULL),
(2018, 'student', 'authtest.sara@masar.test', '$2y$12$12E3iewzy.hp/VnYoi.EBeRTmA63QcJgrCgzIdxjRSEJgDhaBGhsq', 'active', '2026-08-21 18:56:39', NULL, '2026-08-21 18:56:39', '2026-08-21 18:56:39', NULL),
(2019, 'student', 'authtest.omar@masar.test', '$2y$12$nUOaQrnMRcOUMRSt3rDJgufEUaJJfB5F0RyhAlr4Ad7LEtJoFH6AS', 'active', '2026-08-21 18:56:39', '2026-08-21 19:12:35', '2026-08-21 18:56:39', '2026-08-21 19:12:35', NULL),
(2020, 'student', 'authtest.laila@masar.test', '$2y$12$PuW86Wm2GvK2IkbwkM4W7eLOGVzJBAHHSS3LzZTeqHXXSqeYvR2O6', 'active', '2026-08-21 18:56:39', '2026-08-21 19:12:42', '2026-08-21 18:56:39', '2026-08-21 19:12:42', NULL),
(2021, 'student', 'authtest.karim@masar.test', '$2y$12$MYR.pSQEfRfjsS.7ZvFMBeQgHkdSq7NY6HG4juRUJhV0JyN/6xhNe', 'active', '2026-08-21 18:56:40', NULL, '2026-08-21 18:56:40', '2026-08-21 18:56:40', NULL),
(2022, 'student', 'authtest.mona@masar.test', '$2y$12$7ytWz4Qs/HHf7.c.sdT7peflvji/0ce33jj1MnVO5IZTvLs40h/Ci', 'active', '2026-08-21 18:56:40', NULL, '2026-08-21 18:56:40', '2026-08-21 18:56:40', NULL),
(2023, 'student', 'authtest.youssef@masar.test', '$2y$12$xsjqAlL6DYPkqb7/ftAlo.MZwMYb8iQuzZQQJdqeVn3Sgik.KLFyC', 'active', '2026-08-21 18:56:40', NULL, '2026-08-21 18:56:40', '2026-08-21 18:56:40', NULL),
(2024, 'student', 'authtest.dina@masar.test', '$2y$12$vojR9o153yqaP7aXrD/nVeT/Xke6GJTCqWDf5Kii3NwcfTNlmuHH6', 'active', '2026-08-21 18:56:40', NULL, '2026-08-21 18:56:40', '2026-08-21 18:56:40', NULL),
(2071, 'student', 'student.register@test.local', '$2y$12$NJMPU56kfRHlW0K5UY8TeOFxfjIyRjBUDdrfu/e.WldwkmpCuvOVS', 'active', '2026-08-21 19:28:50', '2026-08-22 14:45:18', '2026-08-21 19:28:48', '2026-08-22 14:45:18', NULL),
(2072, 'company', 'company.register@test.local', '$2y$12$wW2wZ5GRda0g7KoFaXlBNugJFm4mUxgjNqWtikgdHsXbDRv15t0U2', 'active', NULL, NULL, '2026-08-21 19:32:19', '2026-08-21 19:33:53', NULL),
(2073, 'company', 'matchtest.delta@masar.test', '$2y$12$WcEK6VGVWMNalKIHnqDXyO4htmTLB322mMImLRRDCqTPZFmCY.Kou', 'active', '2026-08-21 20:08:59', '2026-08-21 21:36:15', '2026-08-21 20:08:59', '2026-08-21 21:36:15', NULL),
(2074, 'company', 'matchtest.codewave@masar.test', '$2y$12$Mr.0WaV4N.xWqeZpWDijguYm10c2zV0wMEqDQ7xZXmPQkRSgonpzi', 'active', '2026-08-21 20:08:59', NULL, '2026-08-21 20:08:59', '2026-08-21 20:08:59', NULL),
(2075, 'company', 'matchtest.datapulse@masar.test', '$2y$12$k.Hd0bU7LOhM8lVEhN/kWumC1XHuDY4Ki.yeXqe5xowlcqK8nxyKS', 'active', '2026-08-21 20:08:59', NULL, '2026-08-21 20:08:59', '2026-08-21 20:08:59', NULL),
(2076, 'company', 'matchtest.secureshield@masar.test', '$2y$12$WFm3o.Oc86yWVKt9v/S2OuhD0xgl8i7I1kTVcUFdD88MbagzrvhUi', 'active', '2026-08-21 20:08:59', NULL, '2026-08-21 20:08:59', '2026-08-21 20:08:59', NULL),
(2077, 'company', 'matchtest.nilebuild@masar.test', '$2y$12$SeQMzkjw/WYHwPjQC8H62eEvTTqMJyuiiwhCfJtz/e6Ydrbxz54gK', 'active', '2026-08-21 20:09:00', NULL, '2026-08-21 20:09:00', '2026-08-21 20:09:00', NULL),
(2078, 'company', 'matchtest.voltedge@masar.test', '$2y$12$tFFQ2WRLsLE6waqdqh76Z.MwqLGfoPaM08mPzzrHt3AbUJ9je1qs.', 'active', '2026-08-21 20:09:00', NULL, '2026-08-21 20:09:00', '2026-08-21 20:09:00', NULL),
(2079, 'company', 'matchtest.brightreach@masar.test', '$2y$12$lG.SV9o.Y0DOaX1UgDyAfOgdII8a.jR2.Wy30jTI5PXk0LoU/6y3O', 'active', '2026-08-21 20:09:00', NULL, '2026-08-21 20:09:00', '2026-08-21 20:09:00', NULL),
(2080, 'company', 'matchtest.talentbridge@masar.test', '$2y$12$9YyA7VDrsj7lXs6E0XAyOuZvNb7OpXnigFQD1OOZ2HO.0uEQqidVO', 'active', '2026-08-21 20:09:01', NULL, '2026-08-21 20:09:01', '2026-08-21 20:09:01', NULL),
(2081, 'company', 'matchtest.pixelcraft@masar.test', '$2y$12$BD1HvrUh8FbGPslZ5PTImOVmQa2OoNQENjOt9Zl.u642/mbV2Lv7y', 'active', '2026-08-21 20:09:01', NULL, '2026-08-21 20:09:01', '2026-08-21 20:09:01', NULL),
(2082, 'company', 'matchtest.ledgerpro@masar.test', '$2y$12$nipzg6QNgyA86KCuKCSzLeNBea3Su8J5V9SRvkgjMq2JngoDznl5y', 'active', '2026-08-21 20:09:01', NULL, '2026-08-21 20:09:01', '2026-08-21 20:09:01', NULL),
(2083, 'company', 'matchtest.technova@masar.test', '$2y$12$EkX8VeH.DA3TDyzHhvy.e.1G1Ef6YWnaivPTE9fngeVHTUoI7fKd2', 'active', '2026-08-21 20:09:01', '2026-08-21 21:36:16', '2026-08-21 20:09:01', '2026-08-21 21:36:16', NULL),
(2084, 'company', 'matchtest.unionind@masar.test', '$2y$12$3Hf0kTe/r.39XUzq/9t/xOkHa92ht2lBJe86XFA8dab7TvBsDHoQG', 'active', '2026-08-21 20:09:02', NULL, '2026-08-21 20:09:02', '2026-08-21 20:09:02', NULL),
(2085, 'company', 'matchtest.mediahub@masar.test', '$2y$12$q9PekpoBxfu3H0jQ0OihG.eslzNjKIVbc7Bu5CBKMTjLiAJBVn3ae', 'active', '2026-08-21 20:09:02', NULL, '2026-08-21 20:09:02', '2026-08-21 20:09:02', NULL),
(2086, 'company', 'matchtest.ghostlabs@masar.test', '$2y$12$oYwbARZpd1KGWoPKuc0LD.L5XOOT7/t8bsF1xmXbOKxMlooxMjmUu', 'active', '2026-08-21 20:09:02', NULL, '2026-08-21 20:09:02', '2026-08-21 20:09:02', NULL),
(2087, 'student', 'matchtest.omar@masar.test', '$2y$12$nIBH.fDWCifBEaCPYhidUuqizt6Hzeje7CW05RZB0QQqqdFtoQT2u', 'active', '2026-08-21 20:09:03', '2026-08-21 22:39:05', '2026-08-21 20:09:03', '2026-08-21 22:39:05', NULL),
(2088, 'student', 'matchtest.nada@masar.test', '$2y$12$66UpFjetsmxDyDmf.Nvi7.RxKsR94eTKDki2pFnKpvV/4//PUhtT.', 'active', '2026-08-21 20:09:03', '2026-08-21 22:37:51', '2026-08-21 20:09:03', '2026-08-21 22:37:51', NULL),
(2089, 'student', 'matchtest.sara@masar.test', '$2y$12$ZvuYRAMzOUucsr75SF7OP.wVXAc0Iny414E8cXP/gHYAepYXDyme6', 'active', '2026-08-21 20:09:03', NULL, '2026-08-21 20:09:03', '2026-08-21 20:09:03', NULL),
(2090, 'student', 'matchtest.karim@masar.test', '$2y$12$NRuql3wJDSoHPd4QJlSYSO5si6EyUaPuZiNZ1V.lM8LmQzknDCe.O', 'active', '2026-08-21 20:09:03', '2026-08-21 22:39:04', '2026-08-21 20:09:03', '2026-08-21 22:39:04', NULL),
(2091, 'student', 'matchtest.laila@masar.test', '$2y$12$97i5GKgWY42ou32HprReiO.6wfoV6jSLMtLYEjRq9I0Ig76hEFN2i', 'active', '2026-08-21 20:09:04', '2026-08-21 22:37:49', '2026-08-21 20:09:04', '2026-08-21 22:37:49', NULL),
(2092, 'student', 'matchtest.ahmedzaki@masar.test', '$2y$12$aj4L0HbmrqYzY9gC0.3vhe01miBJikvxUAbl7edG7s7Ye70Uok8pq', 'active', '2026-08-21 20:09:04', '2026-08-21 22:37:48', '2026-08-21 20:09:04', '2026-08-21 22:37:48', NULL),
(2093, 'student', 'matchtest.mona@masar.test', '$2y$12$erBrbb1/iuej3zGRJ/iPVuTFnPXhgQtiZzAaLiv3EqNMBfWOKQ6Iy', 'active', '2026-08-21 20:09:04', NULL, '2026-08-21 20:09:04', '2026-08-21 20:09:04', NULL),
(2094, 'student', 'matchtest.youssef@masar.test', '$2y$12$Ey5JmyuYG/jYwWU7KA3SPeSqXGg1bmcW9atraVnwA/.QasyY0VpyW', 'active', '2026-08-21 20:09:05', '2026-08-21 22:37:59', '2026-08-21 20:09:05', '2026-08-21 22:37:59', NULL),
(2095, 'student', 'matchtest.hala@masar.test', '$2y$12$nt9xew1FutpJiqpNWg2TA.LtgJR6ToILlW/iR4Nz.MPUdk6ceG6Ci', 'active', '2026-08-21 20:09:05', NULL, '2026-08-21 20:09:05', '2026-08-21 20:09:05', NULL),
(2096, 'student', 'matchtest.tarek@masar.test', '$2y$12$YvAG1O2UjJWq.2gafKwTCOR6sDtQD3MVTkW/KWuJhNIMYDbA.G0ae', 'active', '2026-08-21 20:09:05', NULL, '2026-08-21 20:09:05', '2026-08-21 20:09:05', NULL),
(2097, 'student', 'matchtest.nour@masar.test', '$2y$12$Ic0jq4TQg04Sknt4tyKH5ussF8TWCPF1pSgnAYFnSFBY8h8pvrZX.', 'active', '2026-08-21 20:09:05', NULL, '2026-08-21 20:09:05', '2026-08-21 20:09:05', NULL),
(2098, 'student', 'matchtest.rami@masar.test', '$2y$12$ntr2UjsaqaqJFzpARQtH0ucvO9qTbQJkTy0TtDpShLn1Am48fb.wi', 'active', '2026-08-21 20:09:06', NULL, '2026-08-21 20:09:06', '2026-08-21 20:09:06', NULL),
(2099, 'student', 'matchtest.dina@masar.test', '$2y$12$GxmFUYhW4I1H3MlP5WQuA.OQAClqiCaczqq4Nhim760xyHDtryXvK', 'active', '2026-08-21 20:09:06', '2026-08-21 22:37:52', '2026-08-21 20:09:06', '2026-08-21 22:37:52', NULL),
(2100, 'company', 'tspec.a@masar.test', '$2y$12$VJ9SSzIfitpMY65BfSWgSeaVVt0lSolgbBtY9Z4KKzdATLAw75IKy', 'active', '2026-08-21 20:42:32', '2026-08-21 22:38:58', '2026-08-21 20:42:32', '2026-08-21 22:38:58', NULL),
(2101, 'company', 'tspec.b@masar.test', '$2y$12$8UC4RocNUQVXiGmgY0QZXOc33xr1o78V8t9wYpyqit/q3fz3am3Vi', 'active', '2026-08-21 20:42:32', '2026-08-21 22:39:00', '2026-08-21 20:42:32', '2026-08-21 22:39:00', NULL),
(2102, 'company', 'tspec.c@masar.test', '$2y$12$9VMwlfAbOkA0w7/WM7ydtuca/tRd3EdKT7TsV7Rlw53nLmQzY0DCy', 'active', '2026-08-21 20:42:32', NULL, '2026-08-21 20:42:32', '2026-08-21 20:42:32', NULL),
(2103, 'company', 'tspec.d@masar.test', '$2y$12$dJXMMO051e1ot6OtoD66UuziG2PuPUh4pIZjxkDpE/Adc9Ftq.Ezi', 'active', '2026-08-21 20:42:33', '2026-08-21 22:39:02', '2026-08-21 20:42:33', '2026-08-21 22:39:02', NULL),
(2104, 'company', 'logotest.engineering@masar.test', '$2y$12$rzqHx/7Ea.W4DEF6Eax3IeVriaeNCYCTuMfRI7kVRUdLRgYyLgBpK', 'active', '2026-08-21 22:04:04', '2026-08-21 22:34:33', '2026-08-21 22:04:04', '2026-08-21 22:34:33', NULL),
(2105, 'company', 'logotest.software@masar.test', '$2y$12$.Qumd2XbZJvtJtDJifR13uCyotlJPN4/bIO63Zi9dQ74hrxtYFuuG', 'active', '2026-08-21 22:04:04', NULL, '2026-08-21 22:04:04', '2026-08-21 22:04:04', NULL),
(2106, 'company', 'logotest.data@masar.test', '$2y$12$8lyco5jQ9GzwuqJb/CnBce2x1xaCO9FrRoCaVE.cI/337DUcqplsC', 'active', '2026-08-21 22:04:04', NULL, '2026-08-21 22:04:04', '2026-08-21 22:04:04', NULL),
(2150, 'company', 'mediacare97@gmail.com', '$2y$12$WQYMVITXI7hfBQfI.6QmveSKXOH/rH05rbgedwFG7eVoJrl2SNChi', 'pending', NULL, NULL, '2026-08-25 01:17:13', '2026-08-25 01:17:13', NULL),
(2151, 'student', 'mammuslim2003@gmail.com', '$2y$12$jZklDs2SfwzE4xs2NawbtOdfqxbECicG7AJcE/T7u7eJoZvPFihc.', 'active', '2026-08-25 17:00:29', NULL, '2026-08-25 17:00:25', '2026-08-25 17:54:35', NULL),
(2152, 'company', 'mediacare@gmail.com', '$2y$12$etOBYi1sHVokBdV01lMQ/euHk6.oKlrq/EDSOKZHEIaCiFpBidVAu', 'pending', NULL, NULL, '2026-08-25 17:01:52', '2026-08-25 17:01:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `verification_tokens`
--
CREATE TABLE `verification_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `verification_tokens`
--

INSERT INTO `verification_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(87, 2071, '0db273e217f4fbfa2bb30dc0e32f4bbb6a9b8e1119e74bc4a45a15de75af0265', '2026-08-21 19:28:48', NULL, '2026-08-21 19:28:48'),
(88, 2072, '0acdbaa9022d42d93732651111bf22f4dd56ecfdec597317b76b6a6ec0ed62e1', '2026-08-21 19:32:19', NULL, '2026-08-21 19:32:19'),
(89, 2150, '6f5920e7c04c11784be1c9e5733541f800bd2c2a1d82d5595a877cca63bc73ee', '2026-08-25 01:17:13', NULL, '2026-08-25 01:17:13'),
(90, 2151, '490e4778d9f0e33de83206f65a3a46a094fd8f513dc938b90823b6f3f8405124', '2026-08-25 17:00:26', NULL, '2026-08-25 17:00:26'),
(91, 2152, '46d3a0833e8d4836f36fe0d76b9866bed45cc7689660347e931046e8fdbe0004', '2026-08-25 17:01:52', NULL, '2026-08-25 17:01:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application_answers`
--
ALTER TABLE `application_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application_answers_application` (`application_id`),
  ADD KEY `idx_application_answers_question` (`question_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created` (`created_at`),
  ADD KEY `idx_audit_entity_created` (`entity_type`,`entity_id`,`created_at`);

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_auth_tokens_token_hash` (`token_hash`),
  ADD KEY `idx_auth_tokens_user_id` (`user_id`),
  ADD KEY `idx_auth_tokens_expires_at` (`expires_at`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_code` (`certificate_code`),
  ADD UNIQUE KEY `training_session_id` (`training_session_id`),
  ADD KEY `fk_certificates_training` (`training_id`),
  ADD KEY `idx_certificates_student` (`student_id`),
  ADD KEY `idx_certificates_company` (`company_id`),
  ADD KEY `idx_certificates_status` (`status`),
  ADD KEY `idx_certificates_reviewer` (`reviewed_by`);

--
-- Indexes for table `certificate_appeals`
--
ALTER TABLE `certificate_appeals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_certificate_appeals_certificate` (`certificate_id`),
  ADD KEY `idx_certificate_appeals_student` (`student_id`),
  ADD KEY `idx_certificate_appeals_status` (`status`),
  ADD KEY `idx_certificate_appeals_reviewer` (`reviewed_by`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_companies_approved_by` (`approved_by`),
  ADD KEY `idx_companies_status` (`approval_status`),
  ADD KEY `idx_companies_city` (`city`),
  ADD KEY `idx_companies_approval_city` (`approval_status`,`city`);

--
-- Indexes for table `company_specializations`
--
ALTER TABLE `company_specializations`
  ADD PRIMARY KEY (`company_id`,`specialization_id`),
  ADD KEY `idx_company_specializations_specialization` (`specialization_id`);

--
-- Indexes for table `company_work_fields`
--
ALTER TABLE `company_work_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_company_work_field` (`company_id`,`field_id`),
  ADD KEY `idx_company_work_fields_field` (`field_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD KEY `idx_conversations_student` (`student_id`),
  ADD KEY `idx_conversations_company` (`company_id`);

--
-- Indexes for table `degrees`
--
ALTER TABLE `degrees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_degree_name_level` (`name`,`level`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_faculty_university_name` (`university_id`,`name`),
  ADD KEY `idx_faculties_university` (`university_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_files_user_type` (`user_id`,`type`),
  ADD KEY `idx_files_type` (`type`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_conversation_created` (`conversation_id`,`created_at`),
  ADD KEY `idx_messages_sender` (`sender_user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_notifications_email_queue` (`email_sent_at`,`created_at`);

--
-- Indexes for table `oauth_states`
--
ALTER TABLE `oauth_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_oauth_states_nonce` (`nonce`),
  ADD KEY `idx_oauth_states_expires` (`expires_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_password_resets_user` (`user_id`),
  ADD KEY `idx_password_resets_expires` (`expires_at`),
  ADD KEY `idx_password_reset_cleanup` (`expires_at`,`used_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_training` (`training_id`),
  ADD KEY `fk_payments_session` (`training_session_id`),
  ADD KEY `idx_payments_student` (`student_id`),
  ADD KEY `idx_payments_company` (`company_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_external_reference` (`external_reference`);

--
-- Indexes for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_refresh_user` (`user_id`),
  ADD KEY `idx_refresh_expires` (`expires_at`),
  ADD KEY `idx_refresh_token_cleanup` (`expires_at`,`revoked_at`);

--
-- Indexes for table `revoked_access_tokens`
--
ALTER TABLE `revoked_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_revoked_access_user` (`user_id`),
  ADD KEY `idx_revoked_access_expires` (`expires_at`);

--
-- Indexes for table `saved_trainings`
--
ALTER TABLE `saved_trainings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_saved_student_training` (`student_id`,`training_id`),
  ADD KEY `idx_saved_trainings_training` (`training_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_skill_name` (`name`),
  ADD KEY `idx_skills_name` (`name`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_specialization_name` (`name`),
  ADD KEY `idx_specializations_parent` (`parent_id`),
  ADD KEY `idx_specializations_name` (`name`),
  ADD KEY `idx_specializations_field` (`field_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_students_university` (`university_id`),
  ADD KEY `idx_students_faculty` (`faculty_id`),
  ADD KEY `idx_students_degree` (`degree_id`),
  ADD KEY `idx_students_specialization` (`specialization_id`),
  ADD KEY `idx_students_city` (`city`),
  ADD KEY `fk_students_profile_image_file` (`profile_image_file_id`),
  ADD KEY `fk_students_cv_file` (`cv_file_id`),
  ADD KEY `idx_students_specialization_university` (`specialization_id`,`university_id`),
  ADD KEY `idx_students_field` (`field_id`);

--
-- Indexes for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD PRIMARY KEY (`student_id`,`skill_id`),
  ADD KEY `fk_student_skills_skill` (`skill_id`);

--
-- Indexes for table `study_fields`
--
ALTER TABLE `study_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_study_field_name` (`name`),
  ADD KEY `idx_study_fields_name` (`name`);

--
-- Indexes for table `training_applications`
--
ALTER TABLE `training_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_training_student_application` (`training_id`,`student_id`),
  ADD KEY `idx_applications_training_status` (`training_id`,`status`),
  ADD KEY `idx_applications_student_status` (`student_id`,`status`),
  ADD KEY `idx_applications_status_reviewed` (`status`,`reviewed_at`),
  ADD KEY `idx_applications_reviewer` (`reviewed_by`),
  ADD KEY `fk_applications_cv_file` (`cv_file_id`),
  ADD KEY `fk_applications_faculty` (`faculty_id`),
  ADD KEY `idx_applications_company` (`company_id`);

--
-- Indexes for table `training_listings`
--
ALTER TABLE `training_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_training_listings_company` (`company_id`),
  ADD KEY `idx_training_status` (`status`),
  ADD KEY `idx_training_type` (`training_type`),
  ADD KEY `idx_training_mode` (`mode`),
  ADD KEY `idx_training_ends_at` (`ends_at`),
  ADD KEY `idx_training_status_dates` (`status`,`starts_at`,`ends_at`);

--
-- Indexes for table `training_questions`
--
ALTER TABLE `training_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_training_questions_training` (`training_id`);

--
-- Indexes for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD KEY `fk_training_sessions_training` (`training_id`),
  ADD KEY `idx_training_sessions_student` (`student_id`),
  ADD KEY `idx_training_sessions_company` (`company_id`),
  ADD KEY `idx_training_sessions_status` (`status`),
  ADD KEY `idx_training_sessions_trial_end` (`trial_ends_at`),
  ADD KEY `idx_training_sessions_active` (`status`,`started_at`,`actual_ended_at`);

--
-- Indexes for table `training_skills`
--
ALTER TABLE `training_skills`
  ADD PRIMARY KEY (`training_id`,`skill_id`),
  ADD KEY `idx_training_skills_skill` (`skill_id`);

--
-- Indexes for table `training_specializations`
--
ALTER TABLE `training_specializations`
  ADD PRIMARY KEY (`training_id`,`specialization_id`),
  ADD KEY `idx_training_specializations_specialization` (`specialization_id`);

--
-- Indexes for table `universities`
--
ALTER TABLE `universities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_university_name` (`name`),
  ADD KEY `idx_universities_city` (`city`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_email_status` (`email`,`status`);

--
-- Indexes for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_verification_token_user` (`user_id`,`used_at`),
  ADD KEY `idx_verification_token_user` (`user_id`),
  ADD KEY `idx_verification_token_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application_answers`
--
ALTER TABLE `application_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1270;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificate_appeals`
--
ALTER TABLE `certificate_appeals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=961;

--
-- AUTO_INCREMENT for table `company_work_fields`
--
ALTER TABLE `company_work_fields`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `degrees`
--
ALTER TABLE `degrees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=778;

--
-- AUTO_INCREMENT for table `oauth_states`
--
ALTER TABLE `oauth_states`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=988;

--
-- AUTO_INCREMENT for table `revoked_access_tokens`
--
ALTER TABLE `revoked_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `saved_trainings`
--
ALTER TABLE `saved_trainings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1099;

--
-- AUTO_INCREMENT for table `study_fields`
--
ALTER TABLE `study_fields`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `training_applications`
--
ALTER TABLE `training_applications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=548;

--
-- AUTO_INCREMENT for table `training_listings`
--
ALTER TABLE `training_listings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=879;

--
-- AUTO_INCREMENT for table `training_questions`
--
ALTER TABLE `training_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=277;

--
-- AUTO_INCREMENT for table `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `universities`
--
ALTER TABLE `universities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2153;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application_answers`
--
ALTER TABLE `application_answers`
  ADD CONSTRAINT `fk_application_answers_application` FOREIGN KEY (`application_id`) REFERENCES `training_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_application_answers_question` FOREIGN KEY (`question_id`) REFERENCES `training_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `fk_auth_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_certificates_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_certificates_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_certificates_session` FOREIGN KEY (`training_session_id`) REFERENCES `training_sessions` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_certificates_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_certificates_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `certificate_appeals`
--
ALTER TABLE `certificate_appeals`
  ADD CONSTRAINT `fk_certificate_appeals_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_certificate_appeals_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_certificate_appeals_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `fk_companies_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_companies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_specializations`
--
ALTER TABLE `company_specializations`
  ADD CONSTRAINT `fk_company_specializations_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_company_specializations_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_work_fields`
--
ALTER TABLE `company_work_fields`
  ADD CONSTRAINT `fk_company_work_fields_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_company_work_fields_field` FOREIGN KEY (`field_id`) REFERENCES `study_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `fk_conversations_application` FOREIGN KEY (`application_id`) REFERENCES `training_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversations_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversations_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculties`
--
ALTER TABLE `faculties`
  ADD CONSTRAINT `fk_faculties_university` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `fk_files_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_payments_session` FOREIGN KEY (`training_session_id`) REFERENCES `training_sessions` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_payments_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_payments_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `saved_trainings`
--
ALTER TABLE `saved_trainings`
  ADD CONSTRAINT `fk_saved_trainings_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_saved_trainings_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `specializations`
--
ALTER TABLE `specializations`
  ADD CONSTRAINT `fk_specializations_field` FOREIGN KEY (`field_id`) REFERENCES `study_fields` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_field` FOREIGN KEY (`field_id`) REFERENCES `study_fields` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_applications`
--
ALTER TABLE `training_applications`
  ADD CONSTRAINT `fk_applications_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_applications_cv_file` FOREIGN KEY (`cv_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_applications_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_listings`
--
ALTER TABLE `training_listings`
  ADD CONSTRAINT `fk_training_listings_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_questions`
--
ALTER TABLE `training_questions`
  ADD CONSTRAINT `fk_training_questions_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
