-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260804.50f7529940
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 17, 2026 at 12:40 AM
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
-- Database: `masar`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(714, NULL, 'register_success', 'user', 154, '[]', '{\"role\": \"student\", \"email\": \"mammuslim2003@gmail.com\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:22:57'),
(715, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"mammslim2003@mail.com\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:24:00'),
(716, NULL, 'login_success', 'user', 154, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:24:41'),
(717, NULL, 'password_change_success', 'user', 154, '[]', '{\"ip\": \"::1\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:26:02'),
(718, NULL, 'login_success', 'user', 154, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:26:19'),
(719, NULL, 'login_success', 'user', 154, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:28:06'),
(720, NULL, 'login_success', 'user', 154, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:57:45'),
(721, NULL, 'user_update_success', 'user', 154, '[]', '{\"ip\": \"::1\", \"fields_updated\": [\"email\"]}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:58:07'),
(722, NULL, 'user_deleted', 'user', 154, '{\"id\": 154, \"role\": \"student\", \"email\": \"student.updated@test.local\", \"status\": \"active\", \"full_name\": null, \"created_at\": \"2026-08-15 01:22:50\", \"updated_at\": \"2026-08-15 01:58:06\"}', '{\"deleted_at\": \"2026-08-14 22:59:32\", \"new_status\": \"deleted\", \"previous_status\": \"active\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:59:32'),
(723, NULL, 'account_deleted', 'user', 154, '[]', '{\"ip\": \"::1\", \"self_deletion\": true}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 01:59:32'),
(724, NULL, 'register_success', 'user', 155, '[]', '{\"role\": \"company\", \"email\": \"mediacare97@gmail.com\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:00:27'),
(725, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"mediacare97@gmail.com\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:01:12'),
(726, NULL, 'login_success', 'user', 155, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:01:53'),
(727, NULL, 'password_change_success', 'user', 155, '[]', '{\"ip\": \"::1\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:03:35'),
(728, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"mediacare97@gmail.com\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:03:42'),
(729, NULL, 'login_success', 'user', 155, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:04:04'),
(730, NULL, 'logout_success', 'user', 155, '[]', '{\"role\": \"company\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:04:22'),
(731, NULL, 'login_success', 'user', 155, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:06:00'),
(732, NULL, 'login_success', 'user', 155, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:14:46'),
(733, NULL, 'email_update_duplicate', 'user', 155, '[]', '{\"reason\": \"duplicate email\", \"attempted_email\": \"student.updated@test.local\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:14:56'),
(734, NULL, 'user_update_success', 'user', 155, '[]', '{\"ip\": \"::1\", \"fields_updated\": [\"email\"]}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:15:36'),
(735, NULL, 'user_deleted', 'user', 155, '{\"id\": 155, \"role\": \"company\", \"email\": \"student.upded@test.local\", \"status\": \"active\", \"full_name\": null, \"created_at\": \"2026-08-15 02:00:23\", \"updated_at\": \"2026-08-15 02:15:36\"}', '{\"deleted_at\": \"2026-08-14 23:15:49\", \"new_status\": \"deleted\", \"previous_status\": \"active\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:15:49'),
(736, NULL, 'account_deleted', 'user', 155, '[]', '{\"ip\": \"::1\", \"self_deletion\": true}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:15:49'),
(737, NULL, 'register_success', 'user', 156, '[]', '{\"role\": \"student\", \"email\": \"student.register@test.local\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:17:56'),
(738, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"admin@test.local\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:18:29'),
(739, NULL, 'login_success', 'user', 156, '[]', '{\"ip\": \"::1\", \"role\": \"admin\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:19:24'),
(740, NULL, 'password_change_success', 'user', 156, '[]', '{\"ip\": \"::1\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:20:38'),
(741, NULL, 'login_success', 'user', 156, '[]', '{\"ip\": \"::1\", \"role\": \"admin\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:21:03'),
(742, NULL, 'logout_success', 'user', 156, '[]', '{\"role\": \"admin\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:21:10'),
(743, NULL, 'login_success', 'user', 156, '[]', '{\"ip\": \"::1\", \"role\": \"admin\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:25:09'),
(744, NULL, 'login_success', 'user', 156, '[]', '{\"ip\": \"::1\", \"role\": \"admin\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:31:16'),
(745, NULL, 'email_update_duplicate', 'user', 156, '[]', '{\"reason\": \"duplicate email\", \"attempted_email\": \"student.updated@test.local\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:31:28'),
(746, NULL, 'user_update_success', 'user', 156, '[]', '{\"ip\": \"::1\", \"fields_updated\": [\"email\"]}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:31:45'),
(747, NULL, 'user_deleted', 'user', 156, '{\"id\": 156, \"role\": \"admin\", \"email\": \"studedated@test.local\", \"status\": \"active\", \"full_name\": null, \"created_at\": \"2026-08-15 02:17:50\", \"updated_at\": \"2026-08-15 02:31:44\"}', '{\"deleted_at\": \"2026-08-14 23:32:07\", \"new_status\": \"deleted\", \"previous_status\": \"active\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:32:07'),
(748, NULL, 'account_deleted', 'user', 156, '[]', '{\"ip\": \"::1\", \"self_deletion\": true}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:32:07'),
(749, NULL, 'user_update_success', 'user', 157, '[]', '{\"ip\": \"::1\", \"fields_updated\": [\"email\"]}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:34:32'),
(750, NULL, 'logout_success', 'user', 157, '[]', '{\"role\": \"student\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:35:16'),
(751, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"stuted@test.local\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:35:49'),
(752, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"nobody@test.local\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:35:55'),
(753, NULL, 'register_success', 'user', 158, '[]', '{\"role\": \"student\", \"email\": \"student.register@test.local\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:36:06'),
(754, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"student.register@test.local\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 02:36:34'),
(755, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"test@example.com\"}', '::1', 'curl/8.13.0', '2026-08-15 03:16:55'),
(756, NULL, 'login_success', 'user', 154, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-15 03:38:43'),
(757, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"nobody@example.com\"}', '::1', NULL, '2026-08-15 04:17:21'),
(758, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"nonexistent@example.com\"}', '::1', NULL, '2026-08-15 04:43:43'),
(759, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@example.com\"}', '::1', NULL, '2026-08-15 04:43:43'),
(760, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@example.com\"}', '::1', NULL, '2026-08-15 04:43:44'),
(761, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.register@test.local\"}', '::1', NULL, '2026-08-15 04:44:19'),
(762, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"nonexistent@test.local\"}', '::1', NULL, '2026-08-15 04:48:30'),
(763, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.register@test.local\"}', '::1', NULL, '2026-08-15 04:48:32'),
(764, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.register@test.local\"}', '::1', NULL, '2026-08-15 04:48:34'),
(765, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.register@test.local\"}', '::1', NULL, '2026-08-15 04:49:19'),
(766, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 04:57:35'),
(767, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"company@test.local\"}', '::1', NULL, '2026-08-15 05:00:53'),
(768, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"company@test.local\"}', '::1', NULL, '2026-08-15 05:01:06'),
(769, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"admin@test.local\"}', '::1', NULL, '2026-08-15 05:01:15'),
(770, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.updated@test.local\"}', '::1', NULL, '2026-08-15 05:01:33'),
(771, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@test.local\"}', '::1', NULL, '2026-08-15 05:01:41'),
(772, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.updated@test.local\"}', '::1', NULL, '2026-08-15 05:04:22'),
(773, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.updated@test.local\"}', '::1', NULL, '2026-08-15 05:04:23'),
(774, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.updated@test.local\"}', '::1', NULL, '2026-08-15 05:04:23'),
(775, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.updated@test.local\"}', '::1', NULL, '2026-08-15 05:04:24'),
(776, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:05:27'),
(777, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:05:48'),
(778, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:05:49'),
(779, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:05:50'),
(780, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:06:06'),
(781, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:06:08'),
(782, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:06:23'),
(783, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"userA.test@masar.local\"}', '::1', NULL, '2026-08-15 05:06:43'),
(784, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"userB.test@masar.local\"}', '::1', NULL, '2026-08-15 05:06:44'),
(785, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"userA.test@masar.local\"}', '::1', NULL, '2026-08-15 05:06:44'),
(786, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"userB.test@masar.local\"}', '::1', NULL, '2026-08-15 05:06:45'),
(787, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"admin@test.local\"}', '::1', NULL, '2026-08-15 05:06:59'),
(788, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@test.local\"}', '::1', NULL, '2026-08-15 05:06:59'),
(789, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"company@test.local\"}', '::1', NULL, '2026-08-15 05:06:59'),
(790, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:07:00'),
(791, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:10:30'),
(792, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.register@test.local\"}', '::1', NULL, '2026-08-15 05:10:31'),
(793, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:13:26'),
(794, NULL, 'logout_success', 'user', 158, '[]', '{\"role\": \"student\"}', '::1', NULL, '2026-08-15 05:13:27'),
(795, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:14:48'),
(796, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:15:34'),
(797, NULL, 'register_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"role\": \"student\", \"email\": \"studentA.test@masar.local\"}', '::1', NULL, '2026-08-15 05:19:47'),
(798, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:31:31'),
(799, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.register@test.local\"}', '::1', NULL, '2026-08-15 05:31:32'),
(800, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"nobody@test.local\"}', '::1', NULL, '2026-08-15 05:31:32'),
(801, NULL, 'logout_success', 'user', 158, '[]', '{\"role\": \"student\"}', '::1', NULL, '2026-08-15 05:31:33'),
(802, NULL, 'login_success', 'user', 158, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-15 05:34:48'),
(803, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student.register@test.local\"}', '::1', NULL, '2026-08-15 05:34:49'),
(804, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"nobody@test.local\"}', '::1', NULL, '2026-08-15 05:34:49'),
(805, NULL, 'logout_success', 'user', 158, '[]', '{\"role\": \"student\"}', '::1', NULL, '2026-08-15 05:34:49'),
(806, NULL, 'login_success', 'user', 163, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'curl/8.13.0', '2026-08-17 02:54:25'),
(807, NULL, 'login_success', 'user', 163, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-17 02:55:39'),
(808, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"company@masar.test\"}', '::1', NULL, '2026-08-17 02:55:40'),
(809, NULL, 'login_success', 'user', 163, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-17 02:58:07'),
(810, NULL, 'register_success', 'user', 165, '[]', '{\"role\": \"student\", \"email\": \"e2e.other.20260816235815@test.local\"}', '::1', NULL, '2026-08-17 02:58:19'),
(811, NULL, 'login_success', 'user', 165, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-17 02:58:20'),
(812, NULL, 'login_success', 'user', 163, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-17 02:59:49'),
(813, NULL, 'login_success', 'user', 163, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-17 03:02:51'),
(814, NULL, 'register_success', 'user', 166, '[]', '{\"role\": \"student\", \"email\": \"e2e.other.20260817000254@test.local\"}', '::1', NULL, '2026-08-17 03:02:58'),
(815, NULL, 'login_success', 'user', 166, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-17 03:02:58'),
(816, NULL, 'login_success', 'user', 163, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-17 03:03:27'),
(817, NULL, 'register_success', 'user', 167, '[]', '{\"role\": \"student\", \"email\": \"e2e.other.20260817000329@test.local\"}', '::1', NULL, '2026-08-17 03:03:33'),
(818, NULL, 'login_success', 'user', 167, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', NULL, '2026-08-17 03:03:34'),
(819, 168, 'register_success', 'user', 168, '[]', '{\"role\": \"student\", \"email\": \"mammuslim2003@gmail.com\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-17 03:22:18'),
(820, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"mammuslim2003@mail.com\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-17 03:22:54'),
(821, 168, 'login_success', 'user', 168, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.2.4', '2026-08-17 03:23:12');

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `revoked_at`, `ip_address`, `user_agent`, `created_at`) VALUES
(111, 168, 'd123839472a3405fcd1a0ad3d4a00ae78ce8ead4724ae5c577df822db98b6b8e', '2026-08-24 00:22:18', NULL, '::1', 'PostmanRuntime/2.2.4', '2026-08-17 03:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` bigint UNSIGNED NOT NULL,
  `certificate_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `training_id` bigint UNSIGNED NOT NULL,
  `training_session_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','issued','active','valid','revoked','expired') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `grade_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_eligible` tinyint(1) NOT NULL DEFAULT '0',
  `requested_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `revocation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_appeals`
--

CREATE TABLE `certificate_appeals` (
  `id` bigint UNSIGNED NOT NULL,
  `certificate_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('submitted','under_review','approved','rejected','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_at` datetime DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_specializations`
--

CREATE TABLE `company_specializations` (
  `company_id` bigint UNSIGNED NOT NULL,
  `specialization_id` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_work_fields`
--

CREATE TABLE `company_work_fields` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` enum('diploma','bachelor','master','doctorate','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bachelor',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `degrees`
--

INSERT INTO `degrees` (`id`, `name`, `level`, `is_active`, `created_at`) VALUES
(1, 'Bachelor of Science', 'bachelor', 1, '2026-08-07 22:23:15'),
(2, 'Bachelor of Engineering', 'bachelor', 1, '2026-08-07 22:23:15'),
(3, 'Bachelor of Commerce', 'bachelor', 1, '2026-08-07 22:23:15'),
(4, 'Bachelor of Arts', 'bachelor', 1, '2026-08-07 22:23:15'),
(5, 'Master', 'master', 1, '2026-08-07 22:23:15'),
(6, 'Doctorate', 'doctorate', 1, '2026-08-07 22:23:15'),
(11, 'Bachelor of Business Administration', 'bachelor', 1, '2026-08-17 02:52:16'),
(12, 'Bachelor of Computer Science', 'bachelor', 1, '2026-08-17 02:52:16'),
(13, 'Bachelor of Information Technology', 'bachelor', 1, '2026-08-17 02:52:16'),
(14, 'Bachelor of Computer and Information Sciences', 'bachelor', 1, '2026-08-17 02:52:16'),
(15, 'Bachelor of Medicine and Surgery', 'bachelor', 1, '2026-08-17 02:52:16'),
(16, 'Bachelor of Pharmacy', 'bachelor', 1, '2026-08-17 02:52:16'),
(17, 'Bachelor of Science in Nursing', 'bachelor', 1, '2026-08-17 02:52:16'),
(18, 'Bachelor of Laws', 'bachelor', 1, '2026-08-17 02:52:16'),
(19, 'Bachelor of Fine Arts', 'bachelor', 1, '2026-08-17 02:52:16'),
(20, 'Bachelor of Architecture', 'bachelor', 1, '2026-08-17 02:52:16'),
(21, 'Bachelor of Education', 'bachelor', 1, '2026-08-17 02:52:16'),
(22, 'Master of Science', 'bachelor', 1, '2026-08-17 02:52:16'),
(23, 'Master of Engineering', 'bachelor', 1, '2026-08-17 02:52:16'),
(24, 'Master of Business Administration', 'bachelor', 1, '2026-08-17 02:52:16'),
(25, 'Master of Computer Science', 'bachelor', 1, '2026-08-17 02:52:16'),
(26, 'Doctor of Philosophy', 'bachelor', 1, '2026-08-17 02:52:16');

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` bigint UNSIGNED NOT NULL,
  `university_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `university_id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Faculty of Engineering', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(2, 1, 'Faculty of Computers and Artificial Intelligence', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(3, 1, 'Faculty of Commerce', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(4, 1, 'Faculty of Science', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(5, 1, 'Faculty of Medicine', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(6, 2, 'Faculty of Engineering', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(7, 2, 'Faculty of Computer and Information Sciences', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(8, 2, 'Faculty of Commerce', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(9, 2, 'Faculty of Science', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(10, 3, 'Faculty of Engineering', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(11, 3, 'Faculty of Computers and Data Science', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(12, 3, 'Faculty of Commerce', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(13, 3, 'Faculty of Science', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(14, 4, 'Faculty of Engineering', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(15, 4, 'Faculty of Computers and Information', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(16, 4, 'Faculty of Commerce', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(17, 6, 'Faculty of Engineering', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(18, 6, 'Faculty of Computers and Information', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(19, 9, 'Faculty of Engineering', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(20, 9, 'Faculty of Computers and Artificial Intelligence', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(21, 9, 'Faculty of Commerce and Business Administration', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(22, 22, 'School of Business', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(23, 22, 'School of Sciences and Engineering', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(24, 23, 'Faculty of Information Engineering and Technology', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(25, 23, 'Faculty of Management Technology', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(26, 24, 'Faculty of Informatics and Computer Science', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(27, 24, 'Faculty of Engineering', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(28, 25, 'Faculty of Computer and Information Technology', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24'),
(29, 25, 'Faculty of Commerce and Business Administration', 1, '2026-08-10 21:48:24', '2026-08-10 21:48:24');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('cv','profile_image','certificate_attachment','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` bigint UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `user_id`, `type`, `original_name`, `stored_name`, `path`, `mime_type`, `size_bytes`, `created_at`) VALUES
(8, 168, 'cv', '46f4ccf6-719f-4f0a-a002-57c7d5cdbda6.docx', '20260817_e81f680878fe4b906576809ce059b4a0.docx', 'C:\\laragon\\www\\Masar\\backend\\app/storage/uploads\\general\\20260817_e81f680878fe4b906576809ce059b4a0.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 404214, '2026-08-17 03:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `conversation_id` bigint UNSIGNED NOT NULL,
  `sender_user_id` bigint UNSIGNED NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `email_sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_states`
--

CREATE TABLE `oauth_states` (
  `id` bigint UNSIGNED NOT NULL,
  `nonce` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_states`
--

INSERT INTO `oauth_states` (`id`, `nonce`, `expires_at`, `used_at`, `created_at`) VALUES
(76, 'ba7155144000444411489b75a8dc112ce0de9f8c829d8859bc2981ec041ff399', '2026-08-15 03:53:12', NULL, '2026-08-15 03:43:12');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `platform_commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `platform_commission_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `company_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('manual','paymob','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` enum('pending','paid','failed','refunded','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `external_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `revoked_at`, `created_at`) VALUES
(672, 168, '8fe1d715f0cdf05d26355e75cffea59bdca36d20cfd9014c4a0243d6d86a7bb6', '2026-09-16 00:23:12', NULL, '2026-08-17 03:23:12');

-- --------------------------------------------------------

--
-- Table structure for table `revoked_access_tokens`
--

CREATE TABLE `revoked_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `is_active`, `created_at`) VALUES
(1, 'PHP', 1, '2026-08-07 22:23:15'),
(2, 'MySQL', 1, '2026-08-07 22:23:15'),
(3, 'JavaScript', 1, '2026-08-07 22:23:15'),
(4, 'TypeScript', 1, '2026-08-07 22:23:15'),
(5, 'React', 1, '2026-08-07 22:23:15'),
(6, 'Next.js', 1, '2026-08-07 22:23:15'),
(7, 'Node.js', 1, '2026-08-07 22:23:15'),
(8, 'Python', 1, '2026-08-07 22:23:15'),
(9, 'Java', 1, '2026-08-07 22:23:15'),
(10, 'C++', 1, '2026-08-07 22:23:15'),
(11, 'HTML', 1, '2026-08-07 22:23:15'),
(12, 'CSS', 1, '2026-08-07 22:23:15'),
(13, 'UI/UX Design', 1, '2026-08-07 22:23:15'),
(14, 'Graphic Design', 1, '2026-08-07 22:23:15'),
(15, 'Digital Marketing', 1, '2026-08-07 22:23:15'),
(16, 'Data Analysis', 1, '2026-08-07 22:23:15'),
(17, 'Machine Learning', 1, '2026-08-07 22:23:15'),
(18, 'Project Management', 1, '2026-08-07 22:23:15'),
(19, 'Communication', 1, '2026-08-07 22:23:15'),
(20, 'Problem Solving', 1, '2026-08-07 22:23:15'),
(22, 'Laravel', 1, '2026-08-13 07:21:31'),
(28, 'nodejs', 1, '2026-08-13 07:21:49'),
(35, 'C#', 1, '2026-08-17 02:52:18'),
(36, 'Go', 1, '2026-08-17 02:52:18'),
(38, 'Symfony', 1, '2026-08-17 02:52:18'),
(40, 'Vue.js', 1, '2026-08-17 02:52:18'),
(41, 'Angular', 1, '2026-08-17 02:52:18'),
(44, 'Tailwind CSS', 1, '2026-08-17 02:52:18'),
(45, 'Bootstrap', 1, '2026-08-17 02:52:18'),
(47, 'Express.js', 1, '2026-08-17 02:52:18'),
(48, 'REST API', 1, '2026-08-17 02:52:18'),
(49, 'GraphQL', 1, '2026-08-17 02:52:18'),
(51, 'PostgreSQL', 1, '2026-08-17 02:52:18'),
(52, 'MongoDB', 1, '2026-08-17 02:52:18'),
(53, 'Redis', 1, '2026-08-17 02:52:18'),
(54, 'Git', 1, '2026-08-17 02:52:18'),
(55, 'GitHub', 1, '2026-08-17 02:52:18'),
(56, 'Docker', 1, '2026-08-17 02:52:18'),
(57, 'Linux', 1, '2026-08-17 02:52:18'),
(58, 'CI/CD', 1, '2026-08-17 02:52:18'),
(59, 'AWS', 1, '2026-08-17 02:52:18'),
(60, 'Microsoft Azure', 1, '2026-08-17 02:52:18'),
(61, 'Google Cloud', 1, '2026-08-17 02:52:18'),
(63, 'Data Visualization', 1, '2026-08-17 02:52:18'),
(65, 'Deep Learning', 1, '2026-08-17 02:52:18'),
(66, 'Natural Language Processing', 1, '2026-08-17 02:52:18'),
(67, 'Cybersecurity', 1, '2026-08-17 02:52:18'),
(68, 'Penetration Testing', 1, '2026-08-17 02:52:18'),
(69, 'UI Design', 1, '2026-08-17 02:52:18'),
(70, 'UX Design', 1, '2026-08-17 02:52:18'),
(71, 'Figma', 1, '2026-08-17 02:52:18'),
(72, 'Adobe Photoshop', 1, '2026-08-17 02:52:18'),
(73, 'Adobe Illustrator', 1, '2026-08-17 02:52:18'),
(75, 'Business Analysis', 1, '2026-08-17 02:52:18'),
(77, 'SEO', 1, '2026-08-17 02:52:18'),
(78, 'Content Writing', 1, '2026-08-17 02:52:18'),
(80, 'Teamwork', 1, '2026-08-17 02:52:18'),
(82, 'Time Management', 1, '2026-08-17 02:52:18'),
(83, 'Leadership', 1, '2026-08-17 02:52:18'),
(84, 'English', 1, '2026-08-17 02:52:18'),
(85, 'French', 1, '2026-08-17 02:52:18'),
(86, 'German', 1, '2026-08-17 02:52:18');

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `name`, `parent_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Software Engineering', NULL, 'Software engineering and application development', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(2, 'Web Development', NULL, 'Web and backend/frontend development', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(3, 'Mobile Development', NULL, 'Mobile application development', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(4, 'Data Science', NULL, 'Data science and analytics', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(5, 'Artificial Intelligence', NULL, 'AI and machine learning', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(6, 'Cyber Security', NULL, 'Information and cyber security', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(7, 'Mechanical Engineering', NULL, 'Mechanical engineering', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(8, 'Electrical Engineering', NULL, 'Electrical engineering', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(9, 'Civil Engineering', NULL, 'Civil engineering', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(10, 'Business Administration', NULL, 'Business and management', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(11, 'Accounting', NULL, 'Accounting and financial operations', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(12, 'Marketing', NULL, 'Marketing and digital marketing', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(13, 'Graphic Design', NULL, 'Visual and graphic design', 1, '2026-08-07 22:23:16', '2026-08-07 22:23:16'),
(14, 'Computer Science', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(15, 'Information Technology', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(16, 'Information Systems', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(19, 'Cybersecurity', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(21, 'Computer Engineering', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(22, 'Electronics and Communications Engineering', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(26, 'Architecture', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(29, 'Finance', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(31, 'Human Resources', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(32, 'Management Information Systems', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(33, 'Economics', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(34, 'Medicine', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(35, 'Pharmacy', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(36, 'Nursing', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(37, 'Law', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(38, 'Mass Communication', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(40, 'Digital Marketing', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(41, 'Content Creation', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(42, 'Languages and Translation', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(43, 'English Language', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(44, 'Mathematics', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(45, 'Physics', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(46, 'Chemistry', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22'),
(47, 'Biology', NULL, NULL, 1, '2026-08-10 21:49:22', '2026-08-10 21:49:22');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `university_id` bigint UNSIGNED DEFAULT NULL,
  `faculty_id` bigint UNSIGNED DEFAULT NULL,
  `degree_id` bigint UNSIGNED DEFAULT NULL,
  `specialization_id` bigint UNSIGNED DEFAULT NULL,
  `graduation_year` year DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image_file_id` bigint UNSIGNED DEFAULT NULL,
  `cv_file_id` bigint UNSIGNED DEFAULT NULL,
  `is_profile_complete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `full_name`, `phone`, `bio`, `university_id`, `faculty_id`, `degree_id`, `specialization_id`, `graduation_year`, `city`, `profile_image_file_id`, `cv_file_id`, `is_profile_complete`, `created_at`, `updated_at`) VALUES
(85, 168, 'Test Student', '01012345678', 'Updated bio: interested in backend development and machine learning.', 1, 2, 12, 1, '2027', 'Cairo', NULL, NULL, 0, '2026-08-17 03:22:15', '2026-08-17 03:26:04');

-- --------------------------------------------------------

--
-- Table structure for table `student_skills`
--

CREATE TABLE `student_skills` (
  `student_id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED NOT NULL,
  `proficiency` enum('beginner','intermediate','advanced','expert') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_applications`
--

CREATE TABLE `training_applications` (
  `id` bigint UNSIGNED NOT NULL,
  `training_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('submitted','accepted','rejected','withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `rejection_reason` enum('position_filled','candidate_not_suitable','requirements_not_met','training_closed','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejection_note` text COLLATE utf8mb4_unicode_ci,
  `applied_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `withdrawn_at` datetime DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `training_listings`
--

CREATE TABLE `training_listings` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `field` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `training_type` enum('shadowing','hands_on','project_based') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode` enum('onsite','remote','hybrid') COLLATE utf8mb4_unicode_ci NOT NULL,
  `may_lead_to_employment` tinyint(1) NOT NULL DEFAULT '0',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `compensation_amount` decimal(12,2) DEFAULT NULL,
  `compensation_currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `trial_period_days` int UNSIGNED DEFAULT NULL,
  `capacity` int UNSIGNED DEFAULT NULL,
  `status` enum('draft','published','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `location` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

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
  `status` enum('trial','continuing','completed','stopped','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `started_at` datetime NOT NULL,
  `trial_started_at` datetime DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `student_continuation_confirmed_at` datetime DEFAULT NULL,
  `actual_ended_at` datetime DEFAULT NULL,
  `employment_opportunity` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

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

-- --------------------------------------------------------

--
-- Table structure for table `universities`
--

CREATE TABLE `universities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `universities`
--

INSERT INTO `universities` (`id`, `name`, `city`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Cairo University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(2, 'Ain Shams University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(3, 'Alexandria University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(4, 'Mansoura University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(5, 'Assiut University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(6, 'Tanta University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(7, 'Zagazig University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(8, 'Suez Canal University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(9, 'Helwan University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(10, 'Fayoum University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(11, 'Minia University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(12, 'Sohag University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(13, 'Benha University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(14, 'Kafr El Sheikh University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(15, 'Port Said University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(16, 'Damietta University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(17, 'Damanhour University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(18, 'Aswan University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(19, 'Luxor University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(20, 'New Valley University', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(21, 'Arab Academy for Science, Technology and Maritime Transport', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(22, 'The American University in Cairo', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(23, 'German University in Cairo', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(24, 'British University in Egypt', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23'),
(25, 'Future University in Egypt', NULL, 1, '2026-08-10 21:48:23', '2026-08-10 21:48:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `role` enum('student','company','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','pending','suspended','rejected','deleted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
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
(168, 'student', 'mammuslim2003@gmail.com', '$2y$12$XuNzNWfmmooM9vIxJwP2YuvKF9E/sn3Tgty8GBUdLefCat8h329yC', 'active', '2026-08-17 03:22:17', '2026-08-17 03:23:12', '2026-08-17 03:22:15', '2026-08-17 03:23:12', NULL);

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
(37, 168, '40ec65451b8e4a4932b21e1b04496eae4275e3d7422403bd1d98c72ec6f212fe', '2026-08-17 03:22:15', NULL, '2026-08-17 03:22:15');

--
-- Indexes for dumped tables
--

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
  ADD UNIQUE KEY `uq_company_work_field` (`company_id`,`name`),
  ADD KEY `idx_company_work_fields_name` (`name`);

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
  ADD KEY `idx_specializations_name` (`name`);

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
  ADD KEY `idx_students_specialization_university` (`specialization_id`,`university_id`);

--
-- Indexes for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD PRIMARY KEY (`student_id`,`skill_id`),
  ADD KEY `fk_student_skills_skill` (`skill_id`);

--
-- Indexes for table `training_applications`
--
ALTER TABLE `training_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_training_student_application` (`training_id`,`student_id`),
  ADD KEY `idx_applications_training_status` (`training_id`,`status`),
  ADD KEY `idx_applications_student_status` (`student_id`,`status`),
  ADD KEY `idx_applications_status_reviewed` (`status`,`reviewed_at`),
  ADD KEY `idx_applications_reviewer` (`reviewed_by`);

--
-- Indexes for table `training_listings`
--
ALTER TABLE `training_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_training_company` (`company_id`),
  ADD KEY `idx_training_status` (`status`),
  ADD KEY `idx_training_field` (`field`),
  ADD KEY `idx_training_type` (`training_type`),
  ADD KEY `idx_training_mode` (`mode`),
  ADD KEY `idx_training_ends_at` (`ends_at`),
  ADD KEY `idx_training_status_dates` (`status`,`starts_at`,`ends_at`);

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
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=822;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificate_appeals`
--
ALTER TABLE `certificate_appeals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `company_work_fields`
--
ALTER TABLE `company_work_fields`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_states`
--
ALTER TABLE `oauth_states`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=673;

--
-- AUTO_INCREMENT for table `revoked_access_tokens`
--
ALTER TABLE `revoked_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `training_applications`
--
ALTER TABLE `training_applications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_listings`
--
ALTER TABLE `training_listings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `universities`
--
ALTER TABLE `universities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `fk_company_work_fields_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `fk_refresh_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `specializations`
--
ALTER TABLE `specializations`
  ADD CONSTRAINT `fk_specializations_parent` FOREIGN KEY (`parent_id`) REFERENCES `specializations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_cv_file` FOREIGN KEY (`cv_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_students_degree` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_students_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_students_profile_image_file` FOREIGN KEY (`profile_image_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_students_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_students_university` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_skills`
--
ALTER TABLE `student_skills`
  ADD CONSTRAINT `fk_student_skills_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student_skills_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_applications`
--
ALTER TABLE `training_applications`
  ADD CONSTRAINT `fk_applications_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_applications_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_applications_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_listings`
--
ALTER TABLE `training_listings`
  ADD CONSTRAINT `fk_training_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD CONSTRAINT `fk_training_sessions_application` FOREIGN KEY (`application_id`) REFERENCES `training_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_training_sessions_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_training_sessions_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_training_sessions_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `training_skills`
--
ALTER TABLE `training_skills`
  ADD CONSTRAINT `fk_training_skills_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_training_skills_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_specializations`
--
ALTER TABLE `training_specializations`
  ADD CONSTRAINT `fk_training_specializations_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_training_specializations_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD CONSTRAINT `fk_verification_token_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
