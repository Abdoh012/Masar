-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260814.7ff5dd5b7e
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 04, 2026 at 02:04 AM
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
-- Table structure for table `application_answers`
--
CREATE TABLE `application_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `application_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_answers`
--

INSERT INTO `application_answers` (`id`, `application_id`, `question_id`, `answer`, `created_at`) VALUES
(5604, 1669, 1635, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5605, 1669, 1636, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5606, 1669, 1637, 'No', '2026-07-19 10:00:00'),
(5607, 1669, 1638, '26-35 hours', '2026-07-19 10:00:00'),
(5608, 1669, 1639, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5609, 1670, 1635, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5610, 1670, 1636, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5611, 1670, 1637, 'No', '2026-07-19 10:00:00'),
(5612, 1670, 1638, '26-35 hours', '2026-07-19 10:00:00'),
(5613, 1670, 1639, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5614, 1671, 1635, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5615, 1671, 1636, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5616, 1671, 1637, 'No', '2026-07-19 10:00:00'),
(5617, 1671, 1638, '26-35 hours', '2026-07-19 10:00:00'),
(5618, 1671, 1639, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5619, 1672, 1635, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5620, 1672, 1636, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5621, 1672, 1637, 'No', '2026-07-19 10:00:00'),
(5622, 1672, 1638, '26-35 hours', '2026-07-19 10:00:00'),
(5623, 1672, 1639, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-19 10:00:00'),
(5624, 1673, 1640, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5625, 1673, 1641, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5626, 1673, 1642, 'No', '2026-08-12 14:00:00'),
(5627, 1673, 1643, '26-35 hours', '2026-08-12 14:00:00'),
(5628, 1673, 1644, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5629, 1674, 1640, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5630, 1674, 1641, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5631, 1674, 1642, 'No', '2026-08-11 14:00:00'),
(5632, 1674, 1643, '26-35 hours', '2026-08-11 14:00:00'),
(5633, 1674, 1644, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5634, 1675, 1640, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5635, 1675, 1641, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5636, 1675, 1642, 'No', '2026-08-10 14:00:00'),
(5637, 1675, 1643, '26-35 hours', '2026-08-10 14:00:00'),
(5638, 1675, 1644, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5639, 1676, 1640, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5640, 1676, 1641, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5641, 1676, 1642, 'No', '2026-08-09 14:00:00'),
(5642, 1676, 1643, '26-35 hours', '2026-08-09 14:00:00'),
(5643, 1676, 1644, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5644, 1677, 1640, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5645, 1677, 1641, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5646, 1677, 1642, 'No', '2026-08-08 14:00:00'),
(5647, 1677, 1643, '26-35 hours', '2026-08-08 14:00:00'),
(5648, 1677, 1644, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5649, 1678, 1645, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5650, 1678, 1646, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5651, 1678, 1647, 'No', '2026-08-11 14:00:00'),
(5652, 1678, 1648, '26-35 hours', '2026-08-11 14:00:00'),
(5653, 1678, 1649, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5654, 1679, 1645, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5655, 1679, 1646, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5656, 1679, 1647, 'No', '2026-08-10 14:00:00'),
(5657, 1679, 1648, '26-35 hours', '2026-08-10 14:00:00'),
(5658, 1679, 1649, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5659, 1680, 1645, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5660, 1680, 1646, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5661, 1680, 1647, 'No', '2026-08-09 14:00:00'),
(5662, 1680, 1648, '26-35 hours', '2026-08-09 14:00:00'),
(5663, 1680, 1649, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5664, 1681, 1645, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5665, 1681, 1646, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5666, 1681, 1647, 'No', '2026-08-08 14:00:00'),
(5667, 1681, 1648, '26-35 hours', '2026-08-08 14:00:00'),
(5668, 1681, 1649, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5669, 1682, 1645, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5670, 1682, 1646, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5671, 1682, 1647, 'No', '2026-08-07 14:00:00'),
(5672, 1682, 1648, '26-35 hours', '2026-08-07 14:00:00'),
(5673, 1682, 1649, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5674, 1683, 1650, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5675, 1683, 1651, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5676, 1683, 1652, 'No', '2026-08-10 14:00:00'),
(5677, 1683, 1653, '26-35 hours', '2026-08-10 14:00:00'),
(5678, 1683, 1654, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5679, 1684, 1650, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5680, 1684, 1651, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5681, 1684, 1652, 'No', '2026-08-09 14:00:00'),
(5682, 1684, 1653, '26-35 hours', '2026-08-09 14:00:00'),
(5683, 1684, 1654, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5684, 1685, 1650, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5685, 1685, 1651, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5686, 1685, 1652, 'No', '2026-08-08 14:00:00'),
(5687, 1685, 1653, '26-35 hours', '2026-08-08 14:00:00'),
(5688, 1685, 1654, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5689, 1686, 1650, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5690, 1686, 1651, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5691, 1686, 1652, 'No', '2026-08-07 14:00:00'),
(5692, 1686, 1653, '26-35 hours', '2026-08-07 14:00:00'),
(5693, 1686, 1654, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5694, 1687, 1650, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5695, 1687, 1651, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5696, 1687, 1652, 'No', '2026-08-06 14:00:00'),
(5697, 1687, 1653, '26-35 hours', '2026-08-06 14:00:00'),
(5698, 1687, 1654, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5699, 1688, 1655, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5700, 1688, 1656, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5701, 1688, 1657, 'No', '2026-08-09 14:00:00'),
(5702, 1688, 1658, '26-35 hours', '2026-08-09 14:00:00'),
(5703, 1688, 1659, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5704, 1689, 1655, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5705, 1689, 1656, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5706, 1689, 1657, 'No', '2026-08-08 14:00:00'),
(5707, 1689, 1658, '26-35 hours', '2026-08-08 14:00:00'),
(5708, 1689, 1659, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5709, 1690, 1655, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5710, 1690, 1656, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5711, 1690, 1657, 'No', '2026-08-07 14:00:00'),
(5712, 1690, 1658, '26-35 hours', '2026-08-07 14:00:00'),
(5713, 1690, 1659, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5714, 1691, 1655, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5715, 1691, 1656, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5716, 1691, 1657, 'No', '2026-08-06 14:00:00'),
(5717, 1691, 1658, '26-35 hours', '2026-08-06 14:00:00'),
(5718, 1691, 1659, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5719, 1692, 1655, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5720, 1692, 1656, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5721, 1692, 1657, 'No', '2026-08-05 14:00:00'),
(5722, 1692, 1658, '26-35 hours', '2026-08-05 14:00:00'),
(5723, 1692, 1659, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5724, 1693, 1660, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5725, 1693, 1661, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5726, 1693, 1662, 'No', '2026-08-08 14:00:00'),
(5727, 1693, 1663, '26-35 hours', '2026-08-08 14:00:00'),
(5728, 1693, 1664, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(5729, 1694, 1660, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5730, 1694, 1661, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5731, 1694, 1662, 'No', '2026-08-07 14:00:00'),
(5732, 1694, 1663, '26-35 hours', '2026-08-07 14:00:00'),
(5733, 1694, 1664, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(5734, 1695, 1660, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5735, 1695, 1661, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5736, 1695, 1662, 'No', '2026-08-06 14:00:00'),
(5737, 1695, 1663, '26-35 hours', '2026-08-06 14:00:00'),
(5738, 1695, 1664, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5739, 1696, 1660, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5740, 1696, 1661, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5741, 1696, 1662, 'No', '2026-08-05 14:00:00'),
(5742, 1696, 1663, '26-35 hours', '2026-08-05 14:00:00'),
(5743, 1696, 1664, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5744, 1697, 1660, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5745, 1697, 1661, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5746, 1697, 1662, 'No', '2026-08-04 14:00:00'),
(5747, 1697, 1663, '26-35 hours', '2026-08-04 14:00:00'),
(5748, 1697, 1664, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5749, 1698, 1665, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5750, 1698, 1666, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5751, 1698, 1667, 'No', '2026-07-13 10:00:00'),
(5752, 1698, 1668, '26-35 hours', '2026-07-13 10:00:00'),
(5753, 1698, 1669, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5754, 1699, 1665, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5755, 1699, 1666, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5756, 1699, 1667, 'No', '2026-07-13 10:00:00'),
(5757, 1699, 1668, '26-35 hours', '2026-07-13 10:00:00'),
(5758, 1699, 1669, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5759, 1700, 1665, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5760, 1700, 1666, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5761, 1700, 1667, 'No', '2026-07-13 10:00:00'),
(5762, 1700, 1668, '26-35 hours', '2026-07-13 10:00:00'),
(5763, 1700, 1669, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5764, 1701, 1665, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5765, 1701, 1666, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5766, 1701, 1667, 'No', '2026-07-13 10:00:00'),
(5767, 1701, 1668, '26-35 hours', '2026-07-13 10:00:00'),
(5768, 1701, 1669, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-07-13 10:00:00'),
(5769, 1702, 1670, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5770, 1702, 1671, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5771, 1702, 1672, 'No', '2026-08-06 14:00:00'),
(5772, 1702, 1673, '26-35 hours', '2026-08-06 14:00:00'),
(5773, 1702, 1674, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(5774, 1703, 1670, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5775, 1703, 1671, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5776, 1703, 1672, 'No', '2026-08-05 14:00:00'),
(5777, 1703, 1673, '26-35 hours', '2026-08-05 14:00:00'),
(5778, 1703, 1674, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5779, 1704, 1670, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5780, 1704, 1671, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5781, 1704, 1672, 'No', '2026-08-04 14:00:00'),
(5782, 1704, 1673, '26-35 hours', '2026-08-04 14:00:00'),
(5783, 1704, 1674, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5784, 1705, 1670, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5785, 1705, 1671, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5786, 1705, 1672, 'No', '2026-08-03 14:00:00'),
(5787, 1705, 1673, '26-35 hours', '2026-08-03 14:00:00'),
(5788, 1705, 1674, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5789, 1706, 1670, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5790, 1706, 1671, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5791, 1706, 1672, 'No', '2026-08-02 14:00:00'),
(5792, 1706, 1673, '26-35 hours', '2026-08-02 14:00:00'),
(5793, 1706, 1674, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5794, 1707, 1675, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5795, 1707, 1676, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5796, 1707, 1677, 'No', '2026-08-05 14:00:00'),
(5797, 1707, 1678, '26-35 hours', '2026-08-05 14:00:00'),
(5798, 1707, 1679, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(5799, 1708, 1675, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5800, 1708, 1676, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5801, 1708, 1677, 'No', '2026-08-04 14:00:00'),
(5802, 1708, 1678, '26-35 hours', '2026-08-04 14:00:00'),
(5803, 1708, 1679, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5804, 1709, 1675, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5805, 1709, 1676, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5806, 1709, 1677, 'No', '2026-08-03 14:00:00'),
(5807, 1709, 1678, '26-35 hours', '2026-08-03 14:00:00'),
(5808, 1709, 1679, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5809, 1710, 1675, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5810, 1710, 1676, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5811, 1710, 1677, 'No', '2026-08-02 14:00:00'),
(5812, 1710, 1678, '26-35 hours', '2026-08-02 14:00:00'),
(5813, 1710, 1679, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5814, 1711, 1675, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5815, 1711, 1676, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5816, 1711, 1677, 'No', '2026-08-01 14:00:00'),
(5817, 1711, 1678, '26-35 hours', '2026-08-01 14:00:00'),
(5818, 1711, 1679, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5819, 1712, 1680, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5820, 1712, 1681, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5821, 1712, 1682, 'No', '2026-08-04 14:00:00'),
(5822, 1712, 1683, '26-35 hours', '2026-08-04 14:00:00'),
(5823, 1712, 1684, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(5824, 1713, 1680, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5825, 1713, 1681, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5826, 1713, 1682, 'No', '2026-08-03 14:00:00'),
(5827, 1713, 1683, '26-35 hours', '2026-08-03 14:00:00'),
(5828, 1713, 1684, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5829, 1714, 1680, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5830, 1714, 1681, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5831, 1714, 1682, 'No', '2026-08-02 14:00:00'),
(5832, 1714, 1683, '26-35 hours', '2026-08-02 14:00:00'),
(5833, 1714, 1684, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5834, 1715, 1680, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5835, 1715, 1681, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5836, 1715, 1682, 'No', '2026-08-01 14:00:00'),
(5837, 1715, 1683, '26-35 hours', '2026-08-01 14:00:00'),
(5838, 1715, 1684, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5839, 1716, 1680, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5840, 1716, 1681, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5841, 1716, 1682, 'No', '2026-07-31 14:00:00'),
(5842, 1716, 1683, '26-35 hours', '2026-07-31 14:00:00'),
(5843, 1716, 1684, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5844, 1717, 1685, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5845, 1717, 1686, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5846, 1717, 1687, 'No', '2026-08-03 14:00:00'),
(5847, 1717, 1688, '26-35 hours', '2026-08-03 14:00:00'),
(5848, 1717, 1689, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(5849, 1718, 1685, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5850, 1718, 1686, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5851, 1718, 1687, 'No', '2026-08-02 14:00:00'),
(5852, 1718, 1688, '26-35 hours', '2026-08-02 14:00:00'),
(5853, 1718, 1689, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(5854, 1719, 1685, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5855, 1719, 1686, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5856, 1719, 1687, 'No', '2026-08-01 14:00:00'),
(5857, 1719, 1688, '26-35 hours', '2026-08-01 14:00:00'),
(5858, 1719, 1689, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5859, 1720, 1685, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5860, 1720, 1686, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5861, 1720, 1687, 'No', '2026-07-31 14:00:00'),
(5862, 1720, 1688, '26-35 hours', '2026-07-31 14:00:00'),
(5863, 1720, 1689, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5864, 1721, 1685, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5865, 1721, 1686, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5866, 1721, 1687, 'No', '2026-07-30 14:00:00'),
(5867, 1721, 1688, '26-35 hours', '2026-07-30 14:00:00'),
(5868, 1721, 1689, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5869, 1722, 1690, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5870, 1722, 1691, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5871, 1722, 1692, 'No', '2026-07-08 10:00:00'),
(5872, 1722, 1693, '26-35 hours', '2026-07-08 10:00:00'),
(5873, 1722, 1694, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5874, 1723, 1690, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5875, 1723, 1691, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5876, 1723, 1692, 'No', '2026-07-08 10:00:00'),
(5877, 1723, 1693, '26-35 hours', '2026-07-08 10:00:00'),
(5878, 1723, 1694, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5879, 1724, 1690, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5880, 1724, 1691, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5881, 1724, 1692, 'No', '2026-07-08 10:00:00'),
(5882, 1724, 1693, '26-35 hours', '2026-07-08 10:00:00'),
(5883, 1724, 1694, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5884, 1725, 1690, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5885, 1725, 1691, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5886, 1725, 1692, 'No', '2026-07-08 10:00:00'),
(5887, 1725, 1693, '26-35 hours', '2026-07-08 10:00:00'),
(5888, 1725, 1694, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-07-08 10:00:00'),
(5889, 1726, 1695, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5890, 1726, 1696, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5891, 1726, 1697, 'No', '2026-08-01 14:00:00'),
(5892, 1726, 1698, '26-35 hours', '2026-08-01 14:00:00'),
(5893, 1726, 1699, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(5894, 1727, 1695, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5895, 1727, 1696, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5896, 1727, 1697, 'No', '2026-07-31 14:00:00'),
(5897, 1727, 1698, '26-35 hours', '2026-07-31 14:00:00'),
(5898, 1727, 1699, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5899, 1728, 1695, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5900, 1728, 1696, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5901, 1728, 1697, 'No', '2026-07-30 14:00:00'),
(5902, 1728, 1698, '26-35 hours', '2026-07-30 14:00:00'),
(5903, 1728, 1699, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5904, 1729, 1695, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5905, 1729, 1696, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5906, 1729, 1697, 'No', '2026-08-13 14:00:00'),
(5907, 1729, 1698, '26-35 hours', '2026-08-13 14:00:00'),
(5908, 1729, 1699, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5909, 1730, 1695, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5910, 1730, 1696, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5911, 1730, 1697, 'No', '2026-08-12 14:00:00'),
(5912, 1730, 1698, '26-35 hours', '2026-08-12 14:00:00'),
(5913, 1730, 1699, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5914, 1731, 1700, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5915, 1731, 1701, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5916, 1731, 1702, 'No', '2026-07-31 14:00:00'),
(5917, 1731, 1703, '26-35 hours', '2026-07-31 14:00:00'),
(5918, 1731, 1704, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(5919, 1732, 1700, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5920, 1732, 1701, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5921, 1732, 1702, 'No', '2026-07-30 14:00:00'),
(5922, 1732, 1703, '26-35 hours', '2026-07-30 14:00:00'),
(5923, 1732, 1704, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(5924, 1733, 1700, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5925, 1733, 1701, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5926, 1733, 1702, 'No', '2026-08-13 14:00:00'),
(5927, 1733, 1703, '26-35 hours', '2026-08-13 14:00:00'),
(5928, 1733, 1704, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5929, 1734, 1700, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5930, 1734, 1701, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5931, 1734, 1702, 'No', '2026-08-12 14:00:00'),
(5932, 1734, 1703, '26-35 hours', '2026-08-12 14:00:00'),
(5933, 1734, 1704, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5934, 1735, 1700, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5935, 1735, 1701, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5936, 1735, 1702, 'No', '2026-08-11 14:00:00'),
(5937, 1735, 1703, '26-35 hours', '2026-08-11 14:00:00'),
(5938, 1735, 1704, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5939, 1736, 1705, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5940, 1736, 1706, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5941, 1736, 1707, 'No', '2026-07-05 10:00:00'),
(5942, 1736, 1708, '26-35 hours', '2026-07-05 10:00:00'),
(5943, 1736, 1709, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5944, 1737, 1705, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5945, 1737, 1706, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5946, 1737, 1707, 'No', '2026-07-05 10:00:00'),
(5947, 1737, 1708, '26-35 hours', '2026-07-05 10:00:00'),
(5948, 1737, 1709, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5949, 1738, 1705, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5950, 1738, 1706, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5951, 1738, 1707, 'No', '2026-07-05 10:00:00'),
(5952, 1738, 1708, '26-35 hours', '2026-07-05 10:00:00'),
(5953, 1738, 1709, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5954, 1739, 1705, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5955, 1739, 1706, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5956, 1739, 1707, 'No', '2026-07-05 10:00:00'),
(5957, 1739, 1708, '26-35 hours', '2026-07-05 10:00:00'),
(5958, 1739, 1709, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-05 10:00:00'),
(5959, 1740, 1710, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5960, 1740, 1711, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5961, 1740, 1712, 'No', '2026-08-13 14:00:00'),
(5962, 1740, 1713, '26-35 hours', '2026-08-13 14:00:00');
INSERT INTO `application_answers` (`id`, `application_id`, `question_id`, `answer`, `created_at`) VALUES
(5963, 1740, 1714, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(5964, 1741, 1710, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5965, 1741, 1711, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5966, 1741, 1712, 'No', '2026-08-12 14:00:00'),
(5967, 1741, 1713, '26-35 hours', '2026-08-12 14:00:00'),
(5968, 1741, 1714, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5969, 1742, 1710, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5970, 1742, 1711, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5971, 1742, 1712, 'No', '2026-08-11 14:00:00'),
(5972, 1742, 1713, '26-35 hours', '2026-08-11 14:00:00'),
(5973, 1742, 1714, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5974, 1743, 1710, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5975, 1743, 1711, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5976, 1743, 1712, 'No', '2026-08-10 14:00:00'),
(5977, 1743, 1713, '26-35 hours', '2026-08-10 14:00:00'),
(5978, 1743, 1714, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5979, 1744, 1710, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5980, 1744, 1711, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5981, 1744, 1712, 'No', '2026-08-09 14:00:00'),
(5982, 1744, 1713, '26-35 hours', '2026-08-09 14:00:00'),
(5983, 1744, 1714, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(5984, 1745, 1715, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5985, 1745, 1716, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5986, 1745, 1717, 'No', '2026-08-12 14:00:00'),
(5987, 1745, 1718, '26-35 hours', '2026-08-12 14:00:00'),
(5988, 1745, 1719, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(5989, 1746, 1715, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5990, 1746, 1716, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5991, 1746, 1717, 'No', '2026-08-11 14:00:00'),
(5992, 1746, 1718, '26-35 hours', '2026-08-11 14:00:00'),
(5993, 1746, 1719, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(5994, 1747, 1715, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5995, 1747, 1716, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5996, 1747, 1717, 'No', '2026-08-10 14:00:00'),
(5997, 1747, 1718, '26-35 hours', '2026-08-10 14:00:00'),
(5998, 1747, 1719, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(5999, 1748, 1715, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6000, 1748, 1716, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6001, 1748, 1717, 'No', '2026-08-09 14:00:00'),
(6002, 1748, 1718, '26-35 hours', '2026-08-09 14:00:00'),
(6003, 1748, 1719, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6004, 1749, 1715, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6005, 1749, 1716, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6006, 1749, 1717, 'No', '2026-08-08 14:00:00'),
(6007, 1749, 1718, '26-35 hours', '2026-08-08 14:00:00'),
(6008, 1749, 1719, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6009, 1750, 1720, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6010, 1750, 1721, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6011, 1750, 1722, 'No', '2026-08-11 14:00:00'),
(6012, 1750, 1723, '26-35 hours', '2026-08-11 14:00:00'),
(6013, 1750, 1724, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6014, 1751, 1720, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6015, 1751, 1721, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6016, 1751, 1722, 'No', '2026-08-10 14:00:00'),
(6017, 1751, 1723, '26-35 hours', '2026-08-10 14:00:00'),
(6018, 1751, 1724, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6019, 1752, 1720, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6020, 1752, 1721, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6021, 1752, 1722, 'No', '2026-08-09 14:00:00'),
(6022, 1752, 1723, '26-35 hours', '2026-08-09 14:00:00'),
(6023, 1752, 1724, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6024, 1753, 1720, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6025, 1753, 1721, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6026, 1753, 1722, 'No', '2026-08-08 14:00:00'),
(6027, 1753, 1723, '26-35 hours', '2026-08-08 14:00:00'),
(6028, 1753, 1724, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6029, 1754, 1720, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6030, 1754, 1721, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6031, 1754, 1722, 'No', '2026-08-07 14:00:00'),
(6032, 1754, 1723, '26-35 hours', '2026-08-07 14:00:00'),
(6033, 1754, 1724, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6034, 1755, 1725, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6035, 1755, 1726, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6036, 1755, 1727, 'No', '2026-07-01 10:00:00'),
(6037, 1755, 1728, '26-35 hours', '2026-07-01 10:00:00'),
(6038, 1755, 1729, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6039, 1756, 1725, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6040, 1756, 1726, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6041, 1756, 1727, 'No', '2026-07-01 10:00:00'),
(6042, 1756, 1728, '26-35 hours', '2026-07-01 10:00:00'),
(6043, 1756, 1729, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6044, 1757, 1725, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6045, 1757, 1726, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6046, 1757, 1727, 'No', '2026-07-01 10:00:00'),
(6047, 1757, 1728, '26-35 hours', '2026-07-01 10:00:00'),
(6048, 1757, 1729, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6049, 1758, 1725, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6050, 1758, 1726, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6051, 1758, 1727, 'No', '2026-07-01 10:00:00'),
(6052, 1758, 1728, '26-35 hours', '2026-07-01 10:00:00'),
(6053, 1758, 1729, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-07-01 10:00:00'),
(6054, 1759, 1730, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6055, 1759, 1731, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6056, 1759, 1732, 'No', '2026-08-09 14:00:00'),
(6057, 1759, 1733, '26-35 hours', '2026-08-09 14:00:00'),
(6058, 1759, 1734, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6059, 1760, 1730, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6060, 1760, 1731, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6061, 1760, 1732, 'No', '2026-08-08 14:00:00'),
(6062, 1760, 1733, '26-35 hours', '2026-08-08 14:00:00'),
(6063, 1760, 1734, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6064, 1761, 1730, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6065, 1761, 1731, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6066, 1761, 1732, 'No', '2026-08-07 14:00:00'),
(6067, 1761, 1733, '26-35 hours', '2026-08-07 14:00:00'),
(6068, 1761, 1734, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6069, 1762, 1730, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6070, 1762, 1731, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6071, 1762, 1732, 'No', '2026-08-06 14:00:00'),
(6072, 1762, 1733, '26-35 hours', '2026-08-06 14:00:00'),
(6073, 1762, 1734, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6074, 1763, 1730, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6075, 1763, 1731, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6076, 1763, 1732, 'No', '2026-08-05 14:00:00'),
(6077, 1763, 1733, '26-35 hours', '2026-08-05 14:00:00'),
(6078, 1763, 1734, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6079, 1764, 1735, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6080, 1764, 1736, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6081, 1764, 1737, 'No', '2026-08-08 14:00:00'),
(6082, 1764, 1738, '26-35 hours', '2026-08-08 14:00:00'),
(6083, 1764, 1739, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6084, 1765, 1735, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6085, 1765, 1736, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6086, 1765, 1737, 'No', '2026-08-07 14:00:00'),
(6087, 1765, 1738, '26-35 hours', '2026-08-07 14:00:00'),
(6088, 1765, 1739, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6089, 1766, 1735, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6090, 1766, 1736, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6091, 1766, 1737, 'No', '2026-08-06 14:00:00'),
(6092, 1766, 1738, '26-35 hours', '2026-08-06 14:00:00'),
(6093, 1766, 1739, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6094, 1767, 1735, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6095, 1767, 1736, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6096, 1767, 1737, 'No', '2026-08-05 14:00:00'),
(6097, 1767, 1738, '26-35 hours', '2026-08-05 14:00:00'),
(6098, 1767, 1739, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6099, 1768, 1735, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6100, 1768, 1736, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6101, 1768, 1737, 'No', '2026-08-04 14:00:00'),
(6102, 1768, 1738, '26-35 hours', '2026-08-04 14:00:00'),
(6103, 1768, 1739, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6104, 1769, 1740, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6105, 1769, 1741, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6106, 1769, 1742, 'No', '2026-08-07 14:00:00'),
(6107, 1769, 1743, '26-35 hours', '2026-08-07 14:00:00'),
(6108, 1769, 1744, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6109, 1770, 1740, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6110, 1770, 1741, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6111, 1770, 1742, 'No', '2026-08-06 14:00:00'),
(6112, 1770, 1743, '26-35 hours', '2026-08-06 14:00:00'),
(6113, 1770, 1744, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6114, 1771, 1740, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6115, 1771, 1741, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6116, 1771, 1742, 'No', '2026-08-05 14:00:00'),
(6117, 1771, 1743, '26-35 hours', '2026-08-05 14:00:00'),
(6118, 1771, 1744, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6119, 1772, 1740, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6120, 1772, 1741, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6121, 1772, 1742, 'No', '2026-08-04 14:00:00'),
(6122, 1772, 1743, '26-35 hours', '2026-08-04 14:00:00'),
(6123, 1772, 1744, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6124, 1773, 1740, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6125, 1773, 1741, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6126, 1773, 1742, 'No', '2026-08-03 14:00:00'),
(6127, 1773, 1743, '26-35 hours', '2026-08-03 14:00:00'),
(6128, 1773, 1744, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6129, 1774, 1745, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6130, 1774, 1746, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6131, 1774, 1747, 'No', '2026-08-06 14:00:00'),
(6132, 1774, 1748, '26-35 hours', '2026-08-06 14:00:00'),
(6133, 1774, 1749, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6134, 1775, 1745, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6135, 1775, 1746, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6136, 1775, 1747, 'No', '2026-08-05 14:00:00'),
(6137, 1775, 1748, '26-35 hours', '2026-08-05 14:00:00'),
(6138, 1775, 1749, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6139, 1776, 1745, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6140, 1776, 1746, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6141, 1776, 1747, 'No', '2026-08-04 14:00:00'),
(6142, 1776, 1748, '26-35 hours', '2026-08-04 14:00:00'),
(6143, 1776, 1749, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6144, 1777, 1745, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6145, 1777, 1746, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6146, 1777, 1747, 'No', '2026-08-03 14:00:00'),
(6147, 1777, 1748, '26-35 hours', '2026-08-03 14:00:00'),
(6148, 1777, 1749, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6149, 1778, 1745, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6150, 1778, 1746, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6151, 1778, 1747, 'No', '2026-08-02 14:00:00'),
(6152, 1778, 1748, '26-35 hours', '2026-08-02 14:00:00'),
(6153, 1778, 1749, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6154, 1779, 1750, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6155, 1779, 1751, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6156, 1779, 1752, 'No', '2026-08-05 14:00:00'),
(6157, 1779, 1753, '26-35 hours', '2026-08-05 14:00:00'),
(6158, 1779, 1754, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6159, 1780, 1750, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6160, 1780, 1751, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6161, 1780, 1752, 'No', '2026-08-04 14:00:00'),
(6162, 1780, 1753, '26-35 hours', '2026-08-04 14:00:00'),
(6163, 1780, 1754, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6164, 1781, 1750, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6165, 1781, 1751, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6166, 1781, 1752, 'No', '2026-08-03 14:00:00'),
(6167, 1781, 1753, '26-35 hours', '2026-08-03 14:00:00'),
(6168, 1781, 1754, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6169, 1782, 1750, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6170, 1782, 1751, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6171, 1782, 1752, 'No', '2026-08-02 14:00:00'),
(6172, 1782, 1753, '26-35 hours', '2026-08-02 14:00:00'),
(6173, 1782, 1754, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6174, 1783, 1750, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6175, 1783, 1751, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6176, 1783, 1752, 'No', '2026-08-01 14:00:00'),
(6177, 1783, 1753, '26-35 hours', '2026-08-01 14:00:00'),
(6178, 1783, 1754, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6179, 1784, 1755, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6180, 1784, 1756, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6181, 1784, 1757, 'No', '2026-08-04 14:00:00'),
(6182, 1784, 1758, '26-35 hours', '2026-08-04 14:00:00'),
(6183, 1784, 1759, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6184, 1785, 1755, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6185, 1785, 1756, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6186, 1785, 1757, 'No', '2026-08-03 14:00:00'),
(6187, 1785, 1758, '26-35 hours', '2026-08-03 14:00:00'),
(6188, 1785, 1759, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6189, 1786, 1755, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6190, 1786, 1756, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6191, 1786, 1757, 'No', '2026-08-02 14:00:00'),
(6192, 1786, 1758, '26-35 hours', '2026-08-02 14:00:00'),
(6193, 1786, 1759, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6194, 1787, 1755, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6195, 1787, 1756, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6196, 1787, 1757, 'No', '2026-08-01 14:00:00'),
(6197, 1787, 1758, '26-35 hours', '2026-08-01 14:00:00'),
(6198, 1787, 1759, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6199, 1788, 1755, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6200, 1788, 1756, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6201, 1788, 1757, 'No', '2026-07-31 14:00:00'),
(6202, 1788, 1758, '26-35 hours', '2026-07-31 14:00:00'),
(6203, 1788, 1759, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6204, 1789, 1760, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6205, 1789, 1761, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6206, 1789, 1762, 'No', '2026-06-24 10:00:00'),
(6207, 1789, 1763, '26-35 hours', '2026-06-24 10:00:00'),
(6208, 1789, 1764, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6209, 1790, 1760, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6210, 1790, 1761, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6211, 1790, 1762, 'No', '2026-06-24 10:00:00'),
(6212, 1790, 1763, '26-35 hours', '2026-06-24 10:00:00'),
(6213, 1790, 1764, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6214, 1791, 1760, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6215, 1791, 1761, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6216, 1791, 1762, 'No', '2026-06-24 10:00:00'),
(6217, 1791, 1763, '26-35 hours', '2026-06-24 10:00:00'),
(6218, 1791, 1764, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6219, 1792, 1760, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6220, 1792, 1761, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6221, 1792, 1762, 'No', '2026-06-24 10:00:00'),
(6222, 1792, 1763, '26-35 hours', '2026-06-24 10:00:00'),
(6223, 1792, 1764, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-06-24 10:00:00'),
(6224, 1793, 1765, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6225, 1793, 1766, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6226, 1793, 1767, 'No', '2026-08-02 14:00:00'),
(6227, 1793, 1768, '26-35 hours', '2026-08-02 14:00:00'),
(6228, 1793, 1769, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6229, 1794, 1765, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6230, 1794, 1766, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6231, 1794, 1767, 'No', '2026-08-01 14:00:00'),
(6232, 1794, 1768, '26-35 hours', '2026-08-01 14:00:00'),
(6233, 1794, 1769, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6234, 1795, 1765, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6235, 1795, 1766, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6236, 1795, 1767, 'No', '2026-07-31 14:00:00'),
(6237, 1795, 1768, '26-35 hours', '2026-07-31 14:00:00'),
(6238, 1795, 1769, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6239, 1796, 1765, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6240, 1796, 1766, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6241, 1796, 1767, 'No', '2026-07-30 14:00:00'),
(6242, 1796, 1768, '26-35 hours', '2026-07-30 14:00:00'),
(6243, 1796, 1769, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6244, 1797, 1765, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6245, 1797, 1766, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6246, 1797, 1767, 'No', '2026-08-13 14:00:00'),
(6247, 1797, 1768, '26-35 hours', '2026-08-13 14:00:00'),
(6248, 1797, 1769, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6249, 1798, 1770, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6250, 1798, 1771, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6251, 1798, 1772, 'No', '2026-08-01 14:00:00'),
(6252, 1798, 1773, '26-35 hours', '2026-08-01 14:00:00'),
(6253, 1798, 1774, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-01 14:00:00'),
(6254, 1799, 1770, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6255, 1799, 1771, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6256, 1799, 1772, 'No', '2026-07-31 14:00:00'),
(6257, 1799, 1773, '26-35 hours', '2026-07-31 14:00:00'),
(6258, 1799, 1774, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6259, 1800, 1770, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6260, 1800, 1771, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6261, 1800, 1772, 'No', '2026-07-30 14:00:00'),
(6262, 1800, 1773, '26-35 hours', '2026-07-30 14:00:00'),
(6263, 1800, 1774, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6264, 1801, 1770, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6265, 1801, 1771, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6266, 1801, 1772, 'No', '2026-08-13 14:00:00'),
(6267, 1801, 1773, '26-35 hours', '2026-08-13 14:00:00'),
(6268, 1801, 1774, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6269, 1802, 1770, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6270, 1802, 1771, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6271, 1802, 1772, 'No', '2026-08-12 14:00:00'),
(6272, 1802, 1773, '26-35 hours', '2026-08-12 14:00:00'),
(6273, 1802, 1774, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6274, 1803, 1775, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6275, 1803, 1776, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6276, 1803, 1777, 'No', '2026-07-31 14:00:00'),
(6277, 1803, 1778, '26-35 hours', '2026-07-31 14:00:00'),
(6278, 1803, 1779, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-07-31 14:00:00'),
(6279, 1804, 1775, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6280, 1804, 1776, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6281, 1804, 1777, 'No', '2026-07-30 14:00:00'),
(6282, 1804, 1778, '26-35 hours', '2026-07-30 14:00:00'),
(6283, 1804, 1779, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6284, 1805, 1775, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6285, 1805, 1776, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6286, 1805, 1777, 'No', '2026-08-13 14:00:00'),
(6287, 1805, 1778, '26-35 hours', '2026-08-13 14:00:00'),
(6288, 1805, 1779, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6289, 1806, 1775, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6290, 1806, 1776, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6291, 1806, 1777, 'No', '2026-08-12 14:00:00'),
(6292, 1806, 1778, '26-35 hours', '2026-08-12 14:00:00'),
(6293, 1806, 1779, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6294, 1807, 1775, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6295, 1807, 1776, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6296, 1807, 1777, 'No', '2026-08-11 14:00:00'),
(6297, 1807, 1778, '26-35 hours', '2026-08-11 14:00:00'),
(6298, 1807, 1779, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6299, 1808, 1780, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6300, 1808, 1781, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6301, 1808, 1782, 'No', '2026-07-30 14:00:00'),
(6302, 1808, 1783, '26-35 hours', '2026-07-30 14:00:00'),
(6303, 1808, 1784, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-07-30 14:00:00'),
(6304, 1809, 1780, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6305, 1809, 1781, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6306, 1809, 1782, 'No', '2026-08-13 14:00:00'),
(6307, 1809, 1783, '26-35 hours', '2026-08-13 14:00:00'),
(6308, 1809, 1784, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6309, 1810, 1780, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6310, 1810, 1781, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6311, 1810, 1782, 'No', '2026-08-12 14:00:00'),
(6312, 1810, 1783, '26-35 hours', '2026-08-12 14:00:00'),
(6313, 1810, 1784, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6314, 1811, 1780, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6315, 1811, 1781, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6316, 1811, 1782, 'No', '2026-08-11 14:00:00'),
(6317, 1811, 1783, '26-35 hours', '2026-08-11 14:00:00'),
(6318, 1811, 1784, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6319, 1812, 1780, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6320, 1812, 1781, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6321, 1812, 1782, 'No', '2026-08-10 14:00:00'),
(6322, 1812, 1783, '26-35 hours', '2026-08-10 14:00:00');
INSERT INTO `application_answers` (`id`, `application_id`, `question_id`, `answer`, `created_at`) VALUES
(6323, 1812, 1784, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6324, 1813, 1785, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6325, 1813, 1786, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6326, 1813, 1787, 'No', '2026-08-13 14:00:00'),
(6327, 1813, 1788, '26-35 hours', '2026-08-13 14:00:00'),
(6328, 1813, 1789, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-13 14:00:00'),
(6329, 1814, 1785, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6330, 1814, 1786, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6331, 1814, 1787, 'No', '2026-08-12 14:00:00'),
(6332, 1814, 1788, '26-35 hours', '2026-08-12 14:00:00'),
(6333, 1814, 1789, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6334, 1815, 1785, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6335, 1815, 1786, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6336, 1815, 1787, 'No', '2026-08-11 14:00:00'),
(6337, 1815, 1788, '26-35 hours', '2026-08-11 14:00:00'),
(6338, 1815, 1789, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6339, 1816, 1785, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6340, 1816, 1786, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6341, 1816, 1787, 'No', '2026-08-10 14:00:00'),
(6342, 1816, 1788, '26-35 hours', '2026-08-10 14:00:00'),
(6343, 1816, 1789, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6344, 1817, 1785, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6345, 1817, 1786, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6346, 1817, 1787, 'No', '2026-08-09 14:00:00'),
(6347, 1817, 1788, '26-35 hours', '2026-08-09 14:00:00'),
(6348, 1817, 1789, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6349, 1818, 1790, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6350, 1818, 1791, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6351, 1818, 1792, 'No', '2026-08-12 14:00:00'),
(6352, 1818, 1793, '26-35 hours', '2026-08-12 14:00:00'),
(6353, 1818, 1794, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-12 14:00:00'),
(6354, 1819, 1790, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6355, 1819, 1791, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6356, 1819, 1792, 'No', '2026-08-11 14:00:00'),
(6357, 1819, 1793, '26-35 hours', '2026-08-11 14:00:00'),
(6358, 1819, 1794, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6359, 1820, 1790, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6360, 1820, 1791, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6361, 1820, 1792, 'No', '2026-08-10 14:00:00'),
(6362, 1820, 1793, '26-35 hours', '2026-08-10 14:00:00'),
(6363, 1820, 1794, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6364, 1821, 1790, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6365, 1821, 1791, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6366, 1821, 1792, 'No', '2026-08-09 14:00:00'),
(6367, 1821, 1793, '26-35 hours', '2026-08-09 14:00:00'),
(6368, 1821, 1794, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6369, 1822, 1790, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6370, 1822, 1791, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6371, 1822, 1792, 'No', '2026-08-08 14:00:00'),
(6372, 1822, 1793, '26-35 hours', '2026-08-08 14:00:00'),
(6373, 1822, 1794, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6374, 1823, 1795, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6375, 1823, 1796, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6376, 1823, 1797, 'No', '2026-08-11 14:00:00'),
(6377, 1823, 1798, '26-35 hours', '2026-08-11 14:00:00'),
(6378, 1823, 1799, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-11 14:00:00'),
(6379, 1824, 1795, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6380, 1824, 1796, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6381, 1824, 1797, 'No', '2026-08-10 14:00:00'),
(6382, 1824, 1798, '26-35 hours', '2026-08-10 14:00:00'),
(6383, 1824, 1799, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-10 14:00:00'),
(6384, 1825, 1795, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6385, 1825, 1796, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6386, 1825, 1797, 'No', '2026-08-09 14:00:00'),
(6387, 1825, 1798, '26-35 hours', '2026-08-09 14:00:00'),
(6388, 1825, 1799, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6389, 1826, 1795, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6390, 1826, 1796, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6391, 1826, 1797, 'No', '2026-08-08 14:00:00'),
(6392, 1826, 1798, '26-35 hours', '2026-08-08 14:00:00'),
(6393, 1826, 1799, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6394, 1827, 1795, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6395, 1827, 1796, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6396, 1827, 1797, 'No', '2026-08-07 14:00:00'),
(6397, 1827, 1798, '26-35 hours', '2026-08-07 14:00:00'),
(6398, 1827, 1799, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6399, 1828, 1805, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6400, 1828, 1806, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6401, 1828, 1807, 'No', '2026-08-09 14:00:00'),
(6402, 1828, 1808, '26-35 hours', '2026-08-09 14:00:00'),
(6403, 1828, 1809, 'During my studies at Alexandria University I led a small project related to Financial Accounting, where coordination and disciplined delivery mattered most.', '2026-08-09 14:00:00'),
(6404, 1829, 1805, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6405, 1829, 1806, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6406, 1829, 1807, 'No', '2026-08-08 14:00:00'),
(6407, 1829, 1808, '26-35 hours', '2026-08-08 14:00:00'),
(6408, 1829, 1809, 'During my studies at Cairo University I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6409, 1830, 1805, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6410, 1830, 1806, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6411, 1830, 1807, 'No', '2026-08-07 14:00:00'),
(6412, 1830, 1808, '26-35 hours', '2026-08-07 14:00:00'),
(6413, 1830, 1809, 'During my studies at Ain Shams University I led a small project related to Web Development, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6414, 1831, 1805, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6415, 1831, 1806, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6416, 1831, 1807, 'No', '2026-08-06 14:00:00'),
(6417, 1831, 1808, '26-35 hours', '2026-08-06 14:00:00'),
(6418, 1831, 1809, 'During my studies at Alexandria University I led a small project related to Backend Development, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6419, 1832, 1805, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6420, 1832, 1806, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6421, 1832, 1807, 'No', '2026-08-05 14:00:00'),
(6422, 1832, 1808, '26-35 hours', '2026-08-05 14:00:00'),
(6423, 1832, 1809, 'During my studies at Egypt-Japan University of Science and Technology I led a small project related to Software Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6424, 1833, 1810, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6425, 1833, 1811, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6426, 1833, 1812, 'No', '2026-08-08 14:00:00'),
(6427, 1833, 1813, '26-35 hours', '2026-08-08 14:00:00'),
(6428, 1833, 1814, 'During my studies at Tanta University I led a small project related to Cyber Security, where coordination and disciplined delivery mattered most.', '2026-08-08 14:00:00'),
(6429, 1834, 1810, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6430, 1834, 1811, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6431, 1834, 1812, 'No', '2026-08-07 14:00:00'),
(6432, 1834, 1813, '26-35 hours', '2026-08-07 14:00:00'),
(6433, 1834, 1814, 'During my studies at Helwan University I led a small project related to Artificial Intelligence, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6434, 1835, 1810, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6435, 1835, 1811, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6436, 1835, 1812, 'No', '2026-08-06 14:00:00'),
(6437, 1835, 1813, '26-35 hours', '2026-08-06 14:00:00'),
(6438, 1835, 1814, 'During my studies at British University in Egypt I led a small project related to Cloud Computing, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6439, 1836, 1810, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6440, 1836, 1811, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6441, 1836, 1812, 'No', '2026-08-05 14:00:00'),
(6442, 1836, 1813, '26-35 hours', '2026-08-05 14:00:00'),
(6443, 1836, 1814, 'During my studies at Ain Shams University I led a small project related to Civil Engineering, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6444, 1837, 1810, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6445, 1837, 1811, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6446, 1837, 1812, 'No', '2026-08-04 14:00:00'),
(6447, 1837, 1813, '26-35 hours', '2026-08-04 14:00:00'),
(6448, 1837, 1814, 'During my studies at Pharos University in Alexandria I led a small project related to Architecture, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6449, 1838, 1815, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6450, 1838, 1816, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6451, 1838, 1817, 'No', '2026-08-07 14:00:00'),
(6452, 1838, 1818, '26-35 hours', '2026-08-07 14:00:00'),
(6453, 1838, 1819, 'During my studies at Cairo University I led a small project related to General Medicine, where coordination and disciplined delivery mattered most.', '2026-08-07 14:00:00'),
(6454, 1839, 1815, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6455, 1839, 1816, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6456, 1839, 1817, 'No', '2026-08-06 14:00:00'),
(6457, 1839, 1818, '26-35 hours', '2026-08-06 14:00:00'),
(6458, 1839, 1819, 'During my studies at Alexandria University I led a small project related to Clinical Pharmacy, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6459, 1840, 1815, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6460, 1840, 1816, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6461, 1840, 1817, 'No', '2026-08-05 14:00:00'),
(6462, 1840, 1818, '26-35 hours', '2026-08-05 14:00:00'),
(6463, 1840, 1819, 'During my studies at Cairo University I led a small project related to Marketing, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6464, 1841, 1815, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6465, 1841, 1816, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6466, 1841, 1817, 'No', '2026-08-04 14:00:00'),
(6467, 1841, 1818, '26-35 hours', '2026-08-04 14:00:00'),
(6468, 1841, 1819, 'During my studies at German University in Cairo I led a small project related to Business Administration, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6469, 1842, 1815, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6470, 1842, 1816, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6471, 1842, 1817, 'No', '2026-08-03 14:00:00'),
(6472, 1842, 1818, '26-35 hours', '2026-08-03 14:00:00'),
(6473, 1842, 1819, 'During my studies at Future University in Egypt I led a small project related to Sales, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6474, 1843, 1820, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6475, 1843, 1821, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6476, 1843, 1822, 'No', '2026-08-06 14:00:00'),
(6477, 1843, 1823, '26-35 hours', '2026-08-06 14:00:00'),
(6478, 1843, 1824, 'During my studies at Alexandria University I led a small project related to Commercial Law, where coordination and disciplined delivery mattered most.', '2026-08-06 14:00:00'),
(6479, 1844, 1820, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6480, 1844, 1821, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6481, 1844, 1822, 'No', '2026-08-05 14:00:00'),
(6482, 1844, 1823, '26-35 hours', '2026-08-05 14:00:00'),
(6483, 1844, 1824, 'During my studies at Cairo University I led a small project related to Journalism, where coordination and disciplined delivery mattered most.', '2026-08-05 14:00:00'),
(6484, 1845, 1820, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6485, 1845, 1821, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6486, 1845, 1822, 'No', '2026-08-04 14:00:00'),
(6487, 1845, 1823, '26-35 hours', '2026-08-04 14:00:00'),
(6488, 1845, 1824, 'During my studies at Helwan University I led a small project related to Graphic Design, where coordination and disciplined delivery mattered most.', '2026-08-04 14:00:00'),
(6489, 1846, 1820, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6490, 1846, 1821, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6491, 1846, 1822, 'No', '2026-08-03 14:00:00'),
(6492, 1846, 1823, '26-35 hours', '2026-08-03 14:00:00'),
(6493, 1846, 1824, 'During my studies at Cairo University I led a small project related to UI/UX Design, where coordination and disciplined delivery mattered most.', '2026-08-03 14:00:00'),
(6494, 1847, 1820, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6495, 1847, 1821, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00'),
(6496, 1847, 1822, 'No', '2026-08-02 14:00:00'),
(6497, 1847, 1823, '26-35 hours', '2026-08-02 14:00:00'),
(6498, 1847, 1824, 'During my studies at Cairo University I led a small project related to Auditing, where coordination and disciplined delivery mattered most.', '2026-08-02 14:00:00');

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
(1450, 100526, 'user.register', 'user', 100526, NULL, '{\"role\": \"admin\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1451, 100526, 'company.approve', 'company', 100144, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1452, 100526, 'company.approve', 'company', 100145, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1453, 100526, 'company.approve', 'company', 100146, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1454, 100526, 'company.approve', 'company', 100147, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1455, 100526, 'company.approve', 'company', 100148, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1456, 100526, 'company.approve', 'company', 100149, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1457, 100526, 'company.approve', 'company', 100150, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1458, 100526, 'company.approve', 'company', 100151, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1459, 100526, 'company.approve', 'company', 100152, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1460, 100526, 'company.approve', 'company', 100153, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1461, 100526, 'company.approve', 'company', 100154, NULL, '{\"approval_status\": \"pending\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1462, 100526, 'company.approve', 'company', 100155, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1463, 100526, 'company.approve', 'company', 100156, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1464, 100526, 'company.approve', 'company', 100157, NULL, '{\"approval_status\": \"rejected\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1465, 100526, 'company.approve', 'company', 100158, NULL, '{\"approval_status\": \"approved\"}', '127.0.0.1', 'MASAR Demo Seeder', '2026-08-03 10:00:00'),
(1466, 100526, 'login_success', 'user', 100526, '[]', '{\"ip\": \"::1\", \"role\": \"admin\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-09-02 03:55:50'),
(1467, 100526, 'login_success', 'user', 100526, '[]', '{\"ip\": \"::1\", \"role\": \"admin\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-09-02 03:59:03'),
(1468, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'node', '2026-09-02 04:01:02'),
(1469, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"student@masar.eg\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-09-02 04:06:51'),
(1470, 100526, 'login_success', 'user', 100526, '[]', '{\"ip\": \"::1\", \"role\": \"admin\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-09-02 04:07:14'),
(1471, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'node', '2026-09-02 04:09:22'),
(1472, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-09-02 04:13:19'),
(1473, 100526, 'login_success', 'user', 100526, '[]', '{\"ip\": \"::1\", \"role\": \"admin\"}', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-09-02 04:18:08'),
(1474, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'node', '2026-09-02 04:26:35'),
(1475, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"omar.shazly@gmail.com\"}', '::1', 'node', '2026-09-02 05:13:34'),
(1476, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"omar.shazly@gmail.com\"}', '::1', 'node', '2026-09-02 05:13:45'),
(1477, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"omar.shazly@gmail.com\"}', '::1', 'PostmanRuntime/2.4.3', '2026-09-02 05:13:55'),
(1478, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'node', '2026-09-02 05:14:50'),
(1479, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"omar.shazly@gmail.com\"}', '::1', 'PostmanRuntime/2.4.3', '2026-09-02 05:15:07'),
(1480, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.4.3', '2026-09-02 05:15:23'),
(1481, 100588, 'register_success', 'user', 100588, '[]', '{\"role\": \"student\", \"email\": \"mammuslim2003@gmail.com\"}', '::1', 'node', '2026-09-02 05:23:47'),
(1482, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"mammuslim2003@gmail.com\"}', '::1', 'PostmanRuntime/2.4.3', '2026-09-02 05:24:29'),
(1483, 100588, 'login_success', 'user', 100588, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.4.3', '2026-09-02 05:24:41'),
(1484, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'curl/8.13.0', '2026-09-02 05:49:25'),
(1485, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'curl/8.13.0', '2026-09-02 06:01:46'),
(1486, 100528, 'login_success', 'user', 100528, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'curl/8.13.0', '2026-09-02 06:02:40'),
(1487, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"delta.engineering@test.local\"}', '::1', 'curl/8.13.0', '2026-09-02 06:03:10'),
(1488, 100568, 'login_success', 'user', 100568, '[]', '{\"ip\": \"::1\", \"role\": \"company\"}', '::1', 'curl/8.13.0', '2026-09-02 06:03:53'),
(1489, 100588, 'login_success', 'user', 100588, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'node', '2026-09-02 06:16:46'),
(1490, 100588, 'login_success', 'user', 100588, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.4.3', '2026-09-02 06:26:30'),
(1491, 100588, 'login_success', 'user', 100588, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.4.3', '2026-09-02 07:11:21'),
(1492, 100588, 'login_success', 'user', 100588, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'node', '2026-09-02 07:43:54'),
(1493, 100588, 'login_success', 'user', 100588, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.4.3', '2026-09-02 07:46:40'),
(1494, NULL, 'login_failed', 'user', NULL, '[]', '{\"ip\": \"::1\", \"email\": \"mammuslim2003@gmail.com\"}', '::1', 'PostmanRuntime/2.5.0', '2026-09-04 04:54:17'),
(1495, 100588, 'login_success', 'user', 100588, '[]', '{\"ip\": \"::1\", \"role\": \"student\"}', '::1', 'PostmanRuntime/2.5.0', '2026-09-04 04:54:38');

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
(160, 100588, 'a5d1396bd0af64e977aa74f9331fde416539e0bf15a30e648b8a43aad8234534', '2026-09-09 02:23:47', NULL, '::1', 'node', '2026-09-02 05:23:47');

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

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `certificate_code`, `student_id`, `company_id`, `training_id`, `training_session_id`, `status`, `title`, `start_date`, `end_date`, `grade`, `grade_label`, `employment_eligible`, `requested_at`, `reviewed_at`, `approved_at`, `revoked_at`, `reviewed_by`, `rejection_reason`, `revocation_reason`, `created_at`, `updated_at`) VALUES
(27, 'MASAR-2026-DEMO0001', 1458, 100144, 100202, 225, 'valid', 'Certificate of Completion - Junior PHP Developer Track', '2026-08-08', '2026-08-23', 71.50, 'Very Good', 1, '2026-08-18 13:00:00', '2026-08-21 10:00:00', '2026-08-23 10:00:00', NULL, 100526, NULL, NULL, '2026-08-18 10:00:00', '2026-08-23 10:00:00'),
(28, 'MASAR-2026-DEMO0002', 1476, 100145, 100208, 231, 'valid', 'Certificate of Completion - Data Science Immersion', '2026-08-08', '2026-08-23', 71.50, 'Very Good', 1, '2026-08-18 13:00:00', '2026-08-21 10:00:00', '2026-08-23 10:00:00', NULL, 100526, NULL, NULL, '2026-08-18 10:00:00', '2026-08-23 10:00:00'),
(29, 'MASAR-2026-DEMO0003', 1484, 100146, 100213, 236, 'valid', 'Certificate of Completion - Clinical Rotation in Internal Medicine', '2026-08-08', '2026-08-23', 71.50, 'Very Good', 1, '2026-08-18 13:00:00', '2026-08-21 10:00:00', '2026-08-23 10:00:00', NULL, 100526, NULL, NULL, '2026-08-18 10:00:00', '2026-08-23 10:00:00'),
(30, 'MASAR-2026-DEMO0004', 1472, 100147, 100216, 239, 'valid', 'Certificate of Completion - Intro to Structural Drafting', '2026-08-08', '2026-08-23', 71.50, 'Very Good', 1, '2026-08-18 13:00:00', '2026-08-21 10:00:00', '2026-08-23 10:00:00', NULL, 100526, NULL, NULL, '2026-08-18 10:00:00', '2026-08-23 10:00:00'),
(31, 'MASAR-2026-DEMO0005', 1470, 100148, 100220, 243, 'valid', 'Certificate of Completion - Growth Marketing Campaigns', '2026-08-08', '2026-08-23', 71.50, 'Very Good', 1, '2026-08-18 13:00:00', '2026-08-21 10:00:00', '2026-08-23 10:00:00', NULL, 100526, NULL, NULL, '2026-08-18 10:00:00', '2026-08-23 10:00:00'),
(32, 'MASAR-2026-DEMO0006', 1458, 100151, 100227, 250, 'valid', 'Certificate of Completion - Junior Auditor Track', '2026-08-08', '2026-08-23', 71.50, 'Very Good', 1, '2026-08-18 13:00:00', '2026-08-21 10:00:00', '2026-08-23 10:00:00', NULL, 100526, NULL, NULL, '2026-08-18 10:00:00', '2026-08-23 10:00:00');

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
(100144, 100568, 'NileTech Solutions', 'Software house in Giza building logistics, e-commerce and fintech platforms, with 40+ engineers.', 'https://www.niletech.eg', '01011122231', 'Giza', NULL, 'Giza', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100145, 100569, 'Alexandria Digital Labs', 'Applied AI and data analytics lab serving manufacturing clients across the Delta.', 'https://www.alexdilabs.com', '01122233332', 'Alexandria', NULL, 'Alexandria', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100146, 100570, 'Cairo Medical Center', 'Multi-specialty hospital in downtown Cairo with 200 beds and a dedicated training floor.', 'https://www.cairomed.eg', '01533344433', 'Cairo', NULL, 'Cairo', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100147, 100571, 'Atlas Engineering', 'Mechanical and civil engineering consultancy with projects across the Nile Delta and the new administrative capital.', 'https://www.atlaseng.eg', '01044455534', 'Giza', NULL, 'Giza', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100148, 100572, 'BrightReach Marketing', 'Full-service marketing agency in Cairo managing digital campaigns for retail and healthcare brands.', 'https://www.brightreach.eg', '01155566635', 'Cairo', NULL, 'Cairo', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100149, 100573, 'Luxor Pharma', 'Pharmaceutical manufacturer and distributor, accredited by the Egyptian Drug Authority.', 'https://www.luxorpharma.com', '01566677736', 'Alexandria', NULL, 'Alexandria', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100150, 100574, 'Themis Law Partners', 'Corporate law firm advising startups and listed companies on contracts and compliance.', 'https://www.themis-law.com', '01077788837', 'Cairo', NULL, 'Cairo', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100151, 100575, 'LedgerPro Accounting', 'Audit and bookkeeping firm serving SMEs in Greater Cairo and the Delta.', 'https://www.ledgerpro.eg', '01188899938', 'Cairo', NULL, 'Cairo', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100152, 100576, 'FutureWorks Software', 'Product engineering studio in New Cairo building web and mobile products for international clients.', 'https://www.futureworks.io', '01599900039', 'New Cairo', NULL, 'New Cairo', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100153, 100577, 'GreenRetail Egypt', 'Specialty retail chain with 15 branches across Cairo and Giza.', 'https://www.greenretail.eg', '01011122230', 'Giza', NULL, 'Giza', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100154, 100578, 'MedPulse Diagnostics', 'New medical diagnostics laboratory opening in Dokki, currently onboarding its founding team.', 'https://www.medpulse-dx.com', '01122233331', 'Giza', NULL, 'Giza', 'pending', NULL, NULL, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100155, 100579, 'SolarOffshore Energy', 'Renewable energy developer installing solar and onshore wind projects in the Suez region.', 'https://www.solaroffshore.eg', '01533344432', 'Suez', NULL, 'Suez', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100156, 100580, 'Nile Valley Logistics', 'Freight and warehousing operator covering the Alexandria economic corridor.', 'https://www.nilevalley-log.com', '01044455533', 'Alexandria', NULL, 'Alexandria', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100157, 100581, 'CleoFashion International', 'Garment and mixed-use real estate group in Cairo with an in-house design studio.', 'https://www.cleofashion.com', '01155566634', 'Cairo', NULL, 'Cairo', 'rejected', NULL, NULL, 'Incomplete business license documentation (commercial register and tax card missing).', '2026-06-24 10:00:00', '2026-06-24 10:00:00'),
(100158, 100582, 'HR Partners Egypt', 'Human resources outsourcing and recruitment firm headquartered in Maadi.', 'https://www.hrpartners.eg', '01566677735', 'Cairo', NULL, 'Cairo', 'approved', '2026-07-04 10:00:00', 100526, NULL, '2026-06-24 10:00:00', '2026-06-24 10:00:00');

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
(100144, 103, '2026-06-24 10:00:00'),
(100144, 199, '2026-06-24 10:00:00'),
(100144, 200, '2026-06-24 10:00:00'),
(100144, 202, '2026-06-24 10:00:00'),
(100145, 103, '2026-06-24 10:00:00'),
(100145, 104, '2026-06-24 10:00:00'),
(100145, 205, '2026-06-24 10:00:00'),
(100145, 206, '2026-06-24 10:00:00'),
(100146, 96, '2026-06-24 10:00:00'),
(100146, 97, '2026-06-24 10:00:00'),
(100146, 98, '2026-06-24 10:00:00'),
(100147, 92, '2026-06-24 10:00:00'),
(100147, 93, '2026-06-24 10:00:00'),
(100147, 94, '2026-06-24 10:00:00'),
(100147, 95, '2026-06-24 10:00:00'),
(100148, 108, '2026-06-24 10:00:00'),
(100148, 118, '2026-06-24 10:00:00'),
(100149, 100, '2026-06-24 10:00:00'),
(100149, 102, '2026-06-24 10:00:00'),
(100150, 112, '2026-06-24 10:00:00'),
(100150, 114, '2026-06-24 10:00:00'),
(100151, 122, '2026-06-24 10:00:00'),
(100151, 123, '2026-06-24 10:00:00'),
(100151, 124, '2026-06-24 10:00:00'),
(100152, 103, '2026-06-24 10:00:00'),
(100152, 201, '2026-06-24 10:00:00'),
(100152, 203, '2026-06-24 10:00:00'),
(100152, 204, '2026-06-24 10:00:00'),
(100153, 108, '2026-06-24 10:00:00'),
(100153, 110, '2026-06-24 10:00:00'),
(100153, 111, '2026-06-24 10:00:00'),
(100154, 96, '2026-06-24 10:00:00'),
(100154, 97, '2026-06-24 10:00:00'),
(100155, 92, '2026-06-24 10:00:00'),
(100155, 94, '2026-06-24 10:00:00'),
(100156, 110, '2026-06-24 10:00:00'),
(100156, 111, '2026-06-24 10:00:00'),
(100157, 120, '2026-06-24 10:00:00'),
(100157, 121, '2026-06-24 10:00:00'),
(100158, 109, '2026-06-24 10:00:00'),
(100158, 110, '2026-06-24 10:00:00');

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
(193, 100144, 12, '2026-06-24 10:00:00'),
(194, 100145, 12, '2026-06-24 10:00:00'),
(195, 100146, 10, '2026-06-24 10:00:00'),
(196, 100147, 9, '2026-06-24 10:00:00'),
(197, 100148, 13, '2026-06-24 10:00:00'),
(198, 100148, 15, '2026-06-24 10:00:00'),
(199, 100149, 11, '2026-06-24 10:00:00'),
(200, 100150, 14, '2026-06-24 10:00:00'),
(201, 100151, 17, '2026-06-24 10:00:00'),
(202, 100152, 12, '2026-06-24 10:00:00'),
(203, 100153, 13, '2026-06-24 10:00:00'),
(204, 100154, 10, '2026-06-24 10:00:00'),
(205, 100155, 9, '2026-06-24 10:00:00'),
(206, 100156, 13, '2026-06-24 10:00:00'),
(207, 100157, 16, '2026-06-24 10:00:00'),
(208, 100158, 13, '2026-06-24 10:00:00');

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

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `student_id`, `company_id`, `application_id`, `created_at`, `updated_at`) VALUES
(225, 1458, 100144, 1671, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(226, 1466, 100144, 1675, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(227, 1476, 100144, 1680, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(228, 1486, 100144, 1685, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(229, 1456, 100144, 1690, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(230, 1466, 100144, 1695, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(231, 1476, 100145, 1700, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(232, 1484, 100145, 1704, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(233, 1454, 100145, 1709, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(234, 1464, 100145, 1714, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(235, 1474, 100145, 1719, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(236, 1484, 100146, 1724, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(237, 1492, 100146, 1728, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(238, 1462, 100146, 1733, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(239, 1472, 100147, 1738, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(240, 1480, 100147, 1742, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(241, 1490, 100147, 1747, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(242, 1460, 100147, 1752, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(243, 1470, 100148, 1757, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(244, 1478, 100148, 1761, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(245, 1488, 100148, 1766, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(246, 1458, 100149, 1771, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(247, 1468, 100149, 1776, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(248, 1478, 100150, 1781, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(249, 1488, 100150, 1786, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(250, 1458, 100151, 1791, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(251, 1466, 100151, 1795, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(252, 1476, 100151, 1800, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(253, 1486, 100152, 1805, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(254, 1456, 100152, 1810, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(255, 1466, 100152, 1815, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(256, 1476, 100153, 1820, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(257, 1486, 100153, 1825, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(258, 1456, 100156, 1830, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(259, 1466, 100155, 1835, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(260, 1476, 100158, 1840, '2026-08-01 11:30:00', '2026-08-13 10:00:00'),
(261, 1486, 100158, 1845, '2026-08-01 11:30:00', '2026-08-13 10:00:00');

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

--
-- Dumping data for table `degrees`
--

INSERT INTO `degrees` (`id`, `name`, `level`, `is_active`, `created_at`) VALUES
(27, 'Bachelor of Science', 'bachelor', 1, '2026-08-30 00:59:44'),
(28, 'Bachelor of Engineering', 'bachelor', 1, '2026-08-30 00:59:44'),
(29, 'Bachelor of Arts', 'bachelor', 1, '2026-08-30 00:59:44'),
(30, 'Bachelor of Commerce', 'bachelor', 1, '2026-08-30 00:59:44'),
(31, 'Bachelor of Business Administration', 'bachelor', 1, '2026-08-30 00:59:44'),
(32, 'Bachelor of Computer Science', 'bachelor', 1, '2026-08-30 00:59:44'),
(33, 'Bachelor of Information Technology', 'bachelor', 1, '2026-08-30 00:59:44'),
(34, 'Bachelor of Computer and Information Sciences', 'bachelor', 1, '2026-08-30 00:59:44'),
(35, 'Bachelor of Medicine and Surgery', 'bachelor', 1, '2026-08-30 00:59:44'),
(36, 'Bachelor of Pharmacy', 'bachelor', 1, '2026-08-30 00:59:44'),
(37, 'Bachelor of Science in Nursing', 'bachelor', 1, '2026-08-30 00:59:44'),
(38, 'Bachelor of Laws', 'bachelor', 1, '2026-08-30 00:59:44'),
(39, 'Bachelor of Fine Arts', 'bachelor', 1, '2026-08-30 00:59:44'),
(40, 'Bachelor of Architecture', 'bachelor', 1, '2026-08-30 00:59:44'),
(41, 'Bachelor of Education', 'bachelor', 1, '2026-08-30 00:59:44'),
(42, 'Master of Science', 'bachelor', 1, '2026-08-30 00:59:44'),
(43, 'Master of Engineering', 'bachelor', 1, '2026-08-30 00:59:44'),
(44, 'Master of Business Administration', 'bachelor', 1, '2026-08-30 00:59:44'),
(45, 'Master of Computer Science', 'bachelor', 1, '2026-08-30 00:59:44'),
(46, 'Doctor of Philosophy', 'bachelor', 1, '2026-08-30 00:59:44');

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

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `university_id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(59, 51, 'Faculty of Engineering', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(60, 51, 'Faculty of Computers and Artificial Intelligence', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(61, 51, 'Faculty of Commerce', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(62, 51, 'Faculty of Science', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(63, 51, 'Faculty of Medicine', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(64, 52, 'Faculty of Engineering', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(65, 52, 'Faculty of Computer and Information Sciences', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(66, 52, 'Faculty of Commerce', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(67, 52, 'Faculty of Science', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(68, 53, 'Faculty of Engineering', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(69, 53, 'Faculty of Computers and Data Science', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(70, 53, 'Faculty of Commerce', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(71, 53, 'Faculty of Science', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(72, 54, 'Faculty of Engineering', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(73, 54, 'Faculty of Computers and Information', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(74, 54, 'Faculty of Commerce', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(75, 56, 'Faculty of Engineering', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(76, 56, 'Faculty of Computers and Information', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(77, 59, 'Faculty of Engineering', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(78, 59, 'Faculty of Computers and Artificial Intelligence', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(79, 59, 'Faculty of Commerce and Business Administration', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(80, 72, 'School of Business', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(81, 72, 'School of Sciences and Engineering', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(82, 73, 'Faculty of Information Engineering and Technology', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(83, 73, 'Faculty of Management Technology', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(84, 74, 'Faculty of Informatics and Computer Science', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(85, 74, 'Faculty of Engineering', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(86, 75, 'Faculty of Computer and Information Technology', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(87, 75, 'Faculty of Commerce and Business Administration', 1, '2026-08-30 00:59:44', '2026-08-30 00:59:44'),
(233, 201, 'Faculty of Computer Science and Engineering', 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(234, 201, 'Faculty of Engineering', 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(235, 202, 'Faculty of Engineering', 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(236, 202, 'Faculty of Pharmacy', 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(643, 52, 'Faculty of Medicine', 1, '2026-09-02 03:33:54', '2026-09-02 03:33:54'),
(644, 53, 'Faculty of Pharmacy', 1, '2026-09-02 03:33:54', '2026-09-02 03:33:54'),
(645, 53, 'Faculty of Law', 1, '2026-09-02 03:33:54', '2026-09-02 03:33:54'),
(646, 51, 'Faculty of Law and Policy', 1, '2026-09-02 03:33:54', '2026-09-02 03:33:54'),
(647, 51, 'Faculty of Mass Communication', 1, '2026-09-02 03:33:54', '2026-09-02 03:33:54'),
(648, 51, 'Faculty of Applied Arts', 1, '2026-09-02 03:33:54', '2026-09-02 03:33:54'),
(649, 59, 'Faculty of Fine Arts', 1, '2026-09-02 03:33:54', '2026-09-02 03:33:54');

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
(310, 100588, 'cv', 'مووردين.xlsx', '20260902_02aeeae50a47c00cabc64c2b2794217f.xlsx', 'C:\\laragon\\www\\Masar\\backend\\app/storage/uploads\\general\\20260902_02aeeae50a47c00cabc64c2b2794217f.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 12625, '2026-09-02 08:19:37'),
(311, 100588, 'cv', 'مووردين.xlsx', '20260902_3a48534bcfdf26a627aa1cff4979d6d1.xlsx', 'C:\\laragon\\www\\Masar\\backend\\app/storage/uploads\\general\\20260902_3a48534bcfdf26a627aa1cff4979d6d1.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 12625, '2026-09-02 08:22:26'),
(312, 100588, 'cv', 'مووردين.xlsx', '20260902_580a7b9a0b3f74cbef70604d80e2676d.xlsx', 'C:\\laragon\\www\\Masar\\backend\\app/storage/uploads\\general\\20260902_580a7b9a0b3f74cbef70604d80e2676d.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 12625, '2026-09-02 08:22:42'),
(313, 100588, 'cv', '46f4ccf6-719f-4f0a-a002-57c7d5cdbda6 (2).docx', '20260902_7801f454e1ff4ad6f0d6c7b6ac8b996c.docx', 'C:\\laragon\\www\\Masar\\backend\\app/storage/uploads\\general\\20260902_7801f454e1ff4ad6f0d6c7b6ac8b996c.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 404214, '2026-09-02 08:25:30');

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

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_user_id`, `body`, `is_read`, `read_at`, `created_at`) VALUES
(1104, 225, 100568, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1105, 225, 100532, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1106, 225, 100568, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1107, 225, 100532, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1108, 225, 100568, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1109, 226, 100568, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1110, 226, 100540, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1111, 226, 100568, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1112, 226, 100540, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1113, 226, 100568, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1114, 227, 100568, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1115, 227, 100550, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1116, 227, 100568, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1117, 227, 100550, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1118, 227, 100568, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1119, 228, 100568, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1120, 228, 100560, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1121, 228, 100568, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1122, 228, 100560, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1123, 228, 100568, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1124, 229, 100568, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1125, 229, 100530, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1126, 229, 100568, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1127, 229, 100530, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1128, 229, 100568, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1129, 230, 100568, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1130, 230, 100540, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1131, 230, 100568, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1132, 230, 100540, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1133, 230, 100568, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1134, 231, 100569, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1135, 231, 100550, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1136, 231, 100569, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1137, 231, 100550, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1138, 231, 100569, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1139, 232, 100569, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1140, 232, 100558, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1141, 232, 100569, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1142, 232, 100558, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1143, 232, 100569, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1144, 233, 100569, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1145, 233, 100528, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1146, 233, 100569, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1147, 233, 100528, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1148, 233, 100569, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1149, 234, 100569, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1150, 234, 100538, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1151, 234, 100569, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1152, 234, 100538, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1153, 234, 100569, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1154, 235, 100569, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1155, 235, 100548, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1156, 235, 100569, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1157, 235, 100548, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1158, 235, 100569, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1159, 236, 100570, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1160, 236, 100558, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1161, 236, 100570, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1162, 236, 100558, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1163, 236, 100570, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1164, 237, 100570, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1165, 237, 100566, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1166, 237, 100570, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1167, 237, 100566, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1168, 237, 100570, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1169, 238, 100570, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1170, 238, 100536, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1171, 238, 100570, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1172, 238, 100536, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1173, 238, 100570, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1174, 239, 100571, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1175, 239, 100546, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1176, 239, 100571, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1177, 239, 100546, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1178, 239, 100571, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1179, 240, 100571, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1180, 240, 100554, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1181, 240, 100571, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1182, 240, 100554, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1183, 240, 100571, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1184, 241, 100571, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1185, 241, 100564, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1186, 241, 100571, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1187, 241, 100564, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1188, 241, 100571, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1189, 242, 100571, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1190, 242, 100534, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1191, 242, 100571, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1192, 242, 100534, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1193, 242, 100571, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1194, 243, 100572, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1195, 243, 100544, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1196, 243, 100572, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1197, 243, 100544, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1198, 243, 100572, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1199, 244, 100572, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1200, 244, 100552, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1201, 244, 100572, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1202, 244, 100552, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1203, 244, 100572, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1204, 245, 100572, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1205, 245, 100562, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1206, 245, 100572, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1207, 245, 100562, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1208, 245, 100572, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1209, 246, 100573, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1210, 246, 100532, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1211, 246, 100573, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1212, 246, 100532, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1213, 246, 100573, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1214, 247, 100573, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1215, 247, 100542, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1216, 247, 100573, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1217, 247, 100542, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1218, 247, 100573, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1219, 248, 100574, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1220, 248, 100552, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1221, 248, 100574, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1222, 248, 100552, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1223, 248, 100574, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1224, 249, 100574, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1225, 249, 100562, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1226, 249, 100574, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1227, 249, 100562, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1228, 249, 100574, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1229, 250, 100575, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1230, 250, 100532, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1231, 250, 100575, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1232, 250, 100532, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1233, 250, 100575, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1234, 251, 100575, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1235, 251, 100540, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1236, 251, 100575, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1237, 251, 100540, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1238, 251, 100575, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1239, 252, 100575, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1240, 252, 100550, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1241, 252, 100575, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1242, 252, 100550, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1243, 252, 100575, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1244, 253, 100576, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1245, 253, 100560, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1246, 253, 100576, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1247, 253, 100560, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1248, 253, 100576, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1249, 254, 100576, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1250, 254, 100530, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1251, 254, 100576, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1252, 254, 100530, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1253, 254, 100576, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1254, 255, 100576, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1255, 255, 100540, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1256, 255, 100576, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1257, 255, 100540, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1258, 255, 100576, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1259, 256, 100577, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1260, 256, 100550, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1261, 256, 100577, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1262, 256, 100550, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1263, 256, 100577, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1264, 257, 100577, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1265, 257, 100560, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1266, 257, 100577, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1267, 257, 100560, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1268, 257, 100577, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1269, 258, 100580, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1270, 258, 100530, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1271, 258, 100580, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1272, 258, 100530, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1273, 258, 100580, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1274, 259, 100579, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1275, 259, 100540, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1276, 259, 100579, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1277, 259, 100540, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1278, 259, 100579, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1279, 260, 100582, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1280, 260, 100550, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1281, 260, 100582, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1282, 260, 100550, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1283, 260, 100582, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00'),
(1284, 261, 100582, 'Welcome aboard! I have reviewed your profile and we are glad to have you.', 1, '2026-08-14 12:00:00', '2026-08-13 11:00:00'),
(1285, 261, 100560, 'Thank you for the warm welcome! I am very excited to start.', 1, '2026-08-14 12:00:00', '2026-08-14 11:00:00'),
(1286, 261, 100582, 'Please join the onboarding call tomorrow at 10am.', 1, '2026-08-14 12:00:00', '2026-08-15 11:00:00'),
(1287, 261, 100560, 'I will be there. Should I prepare anything in advance?', 1, '2026-08-14 12:00:00', '2026-08-16 11:00:00'),
(1288, 261, 100582, 'Just bring your laptop and any portfolio pieces you mentioned.', 0, NULL, '2026-08-17 11:00:00');

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `entity_type`, `entity_id`, `is_read`, `read_at`, `email_sent_at`, `created_at`) VALUES
(1745, 100528, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Junior PHP Developer Track was not selected this time.', 'application', 1669, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1746, 100530, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Junior PHP Developer Track was not selected this time.', 'application', 1670, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1747, 100532, 'application_accepted', 'Application Accepted', 'Congratulations! NileTech Solutions accepted your application.', 'application', 1671, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1748, 100534, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Junior PHP Developer Track was not selected this time.', 'application', 1672, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1749, 100540, 'application_accepted', 'Application Accepted', 'Congratulations! NileTech Solutions accepted your application.', 'application', 1675, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1750, 100542, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Full Stack Web Internship was not selected this time.', 'application', 1676, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1751, 100544, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Full Stack Web Internship.', 'application', 1677, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1752, 100550, 'application_accepted', 'Application Accepted', 'Congratulations! NileTech Solutions accepted your application.', 'application', 1680, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1753, 100552, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Scaling Laravel Applications was not selected this time.', 'application', 1681, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1754, 100554, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Scaling Laravel Applications.', 'application', 1682, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1755, 100560, 'application_accepted', 'Application Accepted', 'Congratulations! NileTech Solutions accepted your application.', 'application', 1685, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1756, 100562, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Frontend Craftsmanship Program was not selected this time.', 'application', 1686, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1757, 100564, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Frontend Craftsmanship Program.', 'application', 1687, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1758, 100530, 'application_accepted', 'Application Accepted', 'Congratulations! NileTech Solutions accepted your application.', 'application', 1690, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1759, 100532, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Mobile Banking UI Project was not selected this time.', 'application', 1691, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1760, 100534, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Mobile Banking UI Project.', 'application', 1692, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1761, 100540, 'application_accepted', 'Application Accepted', 'Congratulations! NileTech Solutions accepted your application.', 'application', 1695, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1762, 100542, 'application_rejected', 'Application Rejected', 'We are sorry, your application to QA Automation Essentials was not selected this time.', 'application', 1696, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1763, 100544, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to QA Automation Essentials.', 'application', 1697, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1764, 100546, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Data Science Immersion was not selected this time.', 'application', 1698, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1765, 100548, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Data Science Immersion was not selected this time.', 'application', 1699, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1766, 100550, 'application_accepted', 'Application Accepted', 'Congratulations! Alexandria Digital Labs accepted your application.', 'application', 1700, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1767, 100552, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Data Science Immersion was not selected this time.', 'application', 1701, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1768, 100558, 'application_accepted', 'Application Accepted', 'Congratulations! Alexandria Digital Labs accepted your application.', 'application', 1704, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1769, 100560, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Machine Learning Engineering Internship was not selected this time.', 'application', 1705, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1770, 100562, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Machine Learning Engineering Internship.', 'application', 1706, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1771, 100528, 'application_accepted', 'Application Accepted', 'Congratulations! Alexandria Digital Labs accepted your application.', 'application', 1709, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1772, 100530, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Business Analytics with Power BI was not selected this time.', 'application', 1710, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1773, 100532, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Business Analytics with Power BI.', 'application', 1711, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1774, 100538, 'application_accepted', 'Application Accepted', 'Congratulations! Alexandria Digital Labs accepted your application.', 'application', 1714, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1775, 100540, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Computer Vision Projects was not selected this time.', 'application', 1715, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1776, 100542, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Computer Vision Projects.', 'application', 1716, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1777, 100548, 'application_accepted', 'Application Accepted', 'Congratulations! Alexandria Digital Labs accepted your application.', 'application', 1719, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1778, 100550, 'application_rejected', 'Application Rejected', 'We are sorry, your application to ML in Production (MLOps) was not selected this time.', 'application', 1720, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1779, 100552, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to ML in Production (MLOps).', 'application', 1721, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1780, 100554, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Clinical Rotation in Internal Medicine was not selected this time.', 'application', 1722, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1781, 100556, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Clinical Rotation in Internal Medicine was not selected this time.', 'application', 1723, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1782, 100558, 'application_accepted', 'Application Accepted', 'Congratulations! Cairo Medical Center accepted your application.', 'application', 1724, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1783, 100560, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Clinical Rotation in Internal Medicine was not selected this time.', 'application', 1725, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1784, 100566, 'application_accepted', 'Application Accepted', 'Congratulations! Cairo Medical Center accepted your application.', 'application', 1728, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1785, 100528, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Pediatrics Ward Shadowing was not selected this time.', 'application', 1729, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1786, 100530, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Pediatrics Ward Shadowing.', 'application', 1730, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1787, 100536, 'application_accepted', 'Application Accepted', 'Congratulations! Cairo Medical Center accepted your application.', 'application', 1733, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1788, 100538, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Surgical Theater Observership was not selected this time.', 'application', 1734, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1789, 100540, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Surgical Theater Observership.', 'application', 1735, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1790, 100542, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Intro to Structural Drafting was not selected this time.', 'application', 1736, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1791, 100544, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Intro to Structural Drafting was not selected this time.', 'application', 1737, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1792, 100546, 'application_accepted', 'Application Accepted', 'Congratulations! Atlas Engineering accepted your application.', 'application', 1738, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1793, 100548, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Intro to Structural Drafting was not selected this time.', 'application', 1739, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1794, 100554, 'application_accepted', 'Application Accepted', 'Congratulations! Atlas Engineering accepted your application.', 'application', 1742, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1795, 100556, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Mechanical CAE Workshop was not selected this time.', 'application', 1743, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1796, 100558, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Mechanical CAE Workshop.', 'application', 1744, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1797, 100564, 'application_accepted', 'Application Accepted', 'Congratulations! Atlas Engineering accepted your application.', 'application', 1747, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1798, 100566, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Site Engineering Handbook was not selected this time.', 'application', 1748, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1799, 100528, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Site Engineering Handbook.', 'application', 1749, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1800, 100534, 'application_accepted', 'Application Accepted', 'Congratulations! Atlas Engineering accepted your application.', 'application', 1752, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1801, 100536, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Electrical Systems for Buildings was not selected this time.', 'application', 1753, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1802, 100538, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Electrical Systems for Buildings.', 'application', 1754, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1803, 100540, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Growth Marketing Campaigns was not selected this time.', 'application', 1755, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1804, 100542, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Growth Marketing Campaigns was not selected this time.', 'application', 1756, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1805, 100544, 'application_accepted', 'Application Accepted', 'Congratulations! BrightReach Marketing accepted your application.', 'application', 1757, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1806, 100546, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Growth Marketing Campaigns was not selected this time.', 'application', 1758, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1807, 100552, 'application_accepted', 'Application Accepted', 'Congratulations! BrightReach Marketing accepted your application.', 'application', 1761, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1808, 100554, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Content Studio Intensive was not selected this time.', 'application', 1762, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1809, 100556, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Content Studio Intensive.', 'application', 1763, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1810, 100562, 'application_accepted', 'Application Accepted', 'Congratulations! BrightReach Marketing accepted your application.', 'application', 1766, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1811, 100564, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Digital Campaign Analytics was not selected this time.', 'application', 1767, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1812, 100566, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Digital Campaign Analytics.', 'application', 1768, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1813, 100532, 'application_accepted', 'Application Accepted', 'Congratulations! Luxor Pharma accepted your application.', 'application', 1771, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1814, 100534, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Clinical Pharmacy Rotation was not selected this time.', 'application', 1772, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1815, 100536, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Clinical Pharmacy Rotation.', 'application', 1773, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1816, 100542, 'application_accepted', 'Application Accepted', 'Congratulations! Luxor Pharma accepted your application.', 'application', 1776, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1817, 100544, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Pharmacovigilance Fundamentals was not selected this time.', 'application', 1777, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1818, 100546, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Pharmacovigilance Fundamentals.', 'application', 1778, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1819, 100552, 'application_accepted', 'Application Accepted', 'Congratulations! Themis Law Partners accepted your application.', 'application', 1781, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1820, 100554, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Corporate Contract Review was not selected this time.', 'application', 1782, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1821, 100556, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Corporate Contract Review.', 'application', 1783, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1822, 100562, 'application_accepted', 'Application Accepted', 'Congratulations! Themis Law Partners accepted your application.', 'application', 1786, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1823, 100564, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Commercial Law Clinic was not selected this time.', 'application', 1787, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1824, 100566, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Commercial Law Clinic.', 'application', 1788, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1825, 100528, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Junior Auditor Track was not selected this time.', 'application', 1789, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1826, 100530, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Junior Auditor Track was not selected this time.', 'application', 1790, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1827, 100532, 'application_accepted', 'Application Accepted', 'Congratulations! LedgerPro Accounting accepted your application.', 'application', 1791, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1828, 100534, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Junior Auditor Track was not selected this time.', 'application', 1792, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1829, 100540, 'application_accepted', 'Application Accepted', 'Congratulations! LedgerPro Accounting accepted your application.', 'application', 1795, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1830, 100542, 'application_rejected', 'Application Rejected', 'We are sorry, your application to IFRS for SMEs was not selected this time.', 'application', 1796, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1831, 100544, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to IFRS for SMEs.', 'application', 1797, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1832, 100550, 'application_accepted', 'Application Accepted', 'Congratulations! LedgerPro Accounting accepted your application.', 'application', 1800, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1833, 100552, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Bookkeeping Essentials was not selected this time.', 'application', 1801, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1834, 100554, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Bookkeeping Essentials.', 'application', 1802, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1835, 100560, 'application_accepted', 'Application Accepted', 'Congratulations! FutureWorks Software accepted your application.', 'application', 1805, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1836, 100562, 'application_rejected', 'Application Rejected', 'We are sorry, your application to React Native Product Sprint was not selected this time.', 'application', 1806, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1837, 100564, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to React Native Product Sprint.', 'application', 1807, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1838, 100530, 'application_accepted', 'Application Accepted', 'Congratulations! FutureWorks Software accepted your application.', 'application', 1810, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1839, 100532, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Cloud Native Bootcamp was not selected this time.', 'application', 1811, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1840, 100534, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Cloud Native Bootcamp.', 'application', 1812, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1841, 100540, 'application_accepted', 'Application Accepted', 'Congratulations! FutureWorks Software accepted your application.', 'application', 1815, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1842, 100542, 'application_rejected', 'Application Rejected', 'We are sorry, your application to TypeScript Full Stack Fellowship was not selected this time.', 'application', 1816, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1843, 100544, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to TypeScript Full Stack Fellowship.', 'application', 1817, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1844, 100550, 'application_accepted', 'Application Accepted', 'Congratulations! GreenRetail Egypt accepted your application.', 'application', 1820, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1845, 100552, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Retail Operations Management was not selected this time.', 'application', 1821, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1846, 100554, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Retail Operations Management.', 'application', 1822, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1847, 100560, 'application_accepted', 'Application Accepted', 'Congratulations! GreenRetail Egypt accepted your application.', 'application', 1825, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1848, 100562, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Category Analytics Program was not selected this time.', 'application', 1826, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1849, 100564, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Category Analytics Program.', 'application', 1827, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1850, 100530, 'application_accepted', 'Application Accepted', 'Congratulations! Nile Valley Logistics accepted your application.', 'application', 1830, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1851, 100532, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Freight Operations Trainee was not selected this time.', 'application', 1831, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1852, 100534, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Freight Operations Trainee.', 'application', 1832, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1853, 100540, 'application_accepted', 'Application Accepted', 'Congratulations! SolarOffshore Energy accepted your application.', 'application', 1835, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1854, 100542, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Onshore Wind Site Inspection was not selected this time.', 'application', 1836, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1855, 100544, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Onshore Wind Site Inspection.', 'application', 1837, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1856, 100550, 'application_accepted', 'Application Accepted', 'Congratulations! HR Partners Egypt accepted your application.', 'application', 1840, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1857, 100552, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Recruitment Sourcer Program was not selected this time.', 'application', 1841, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1858, 100554, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Recruitment Sourcer Program.', 'application', 1842, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1859, 100560, 'application_accepted', 'Application Accepted', 'Congratulations! HR Partners Egypt accepted your application.', 'application', 1845, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1860, 100562, 'application_rejected', 'Application Rejected', 'We are sorry, your application to Employee Onboarding Design was not selected this time.', 'application', 1846, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1861, 100564, 'application_withdrawn', 'Application Withdrawn', 'You withdrew your application to Employee Onboarding Design.', 'application', 1847, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1862, 100532, 'new_message', 'New Message', 'NileTech Solutions sent you a message.', 'conversation', 225, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1863, 100540, 'new_message', 'New Message', 'NileTech Solutions sent you a message.', 'conversation', 226, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1864, 100550, 'new_message', 'New Message', 'NileTech Solutions sent you a message.', 'conversation', 227, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1865, 100560, 'new_message', 'New Message', 'NileTech Solutions sent you a message.', 'conversation', 228, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1866, 100530, 'new_message', 'New Message', 'NileTech Solutions sent you a message.', 'conversation', 229, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1867, 100540, 'new_message', 'New Message', 'NileTech Solutions sent you a message.', 'conversation', 230, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1868, 100550, 'new_message', 'New Message', 'Alexandria Digital Labs sent you a message.', 'conversation', 231, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1869, 100558, 'new_message', 'New Message', 'Alexandria Digital Labs sent you a message.', 'conversation', 232, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1870, 100528, 'new_message', 'New Message', 'Alexandria Digital Labs sent you a message.', 'conversation', 233, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1871, 100538, 'new_message', 'New Message', 'Alexandria Digital Labs sent you a message.', 'conversation', 234, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1872, 100548, 'new_message', 'New Message', 'Alexandria Digital Labs sent you a message.', 'conversation', 235, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1873, 100558, 'new_message', 'New Message', 'Cairo Medical Center sent you a message.', 'conversation', 236, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1874, 100566, 'new_message', 'New Message', 'Cairo Medical Center sent you a message.', 'conversation', 237, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1875, 100536, 'new_message', 'New Message', 'Cairo Medical Center sent you a message.', 'conversation', 238, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1876, 100546, 'new_message', 'New Message', 'Atlas Engineering sent you a message.', 'conversation', 239, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1877, 100554, 'new_message', 'New Message', 'Atlas Engineering sent you a message.', 'conversation', 240, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1878, 100564, 'new_message', 'New Message', 'Atlas Engineering sent you a message.', 'conversation', 241, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1879, 100534, 'new_message', 'New Message', 'Atlas Engineering sent you a message.', 'conversation', 242, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1880, 100544, 'new_message', 'New Message', 'BrightReach Marketing sent you a message.', 'conversation', 243, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1881, 100552, 'new_message', 'New Message', 'BrightReach Marketing sent you a message.', 'conversation', 244, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1882, 100562, 'new_message', 'New Message', 'BrightReach Marketing sent you a message.', 'conversation', 245, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1883, 100532, 'new_message', 'New Message', 'Luxor Pharma sent you a message.', 'conversation', 246, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1884, 100542, 'new_message', 'New Message', 'Luxor Pharma sent you a message.', 'conversation', 247, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1885, 100552, 'new_message', 'New Message', 'Themis Law Partners sent you a message.', 'conversation', 248, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1886, 100562, 'new_message', 'New Message', 'Themis Law Partners sent you a message.', 'conversation', 249, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1887, 100532, 'new_message', 'New Message', 'LedgerPro Accounting sent you a message.', 'conversation', 250, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1888, 100540, 'new_message', 'New Message', 'LedgerPro Accounting sent you a message.', 'conversation', 251, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1889, 100550, 'new_message', 'New Message', 'LedgerPro Accounting sent you a message.', 'conversation', 252, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1890, 100560, 'new_message', 'New Message', 'FutureWorks Software sent you a message.', 'conversation', 253, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1891, 100530, 'new_message', 'New Message', 'FutureWorks Software sent you a message.', 'conversation', 254, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1892, 100540, 'new_message', 'New Message', 'FutureWorks Software sent you a message.', 'conversation', 255, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1893, 100550, 'new_message', 'New Message', 'GreenRetail Egypt sent you a message.', 'conversation', 256, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1894, 100560, 'new_message', 'New Message', 'GreenRetail Egypt sent you a message.', 'conversation', 257, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1895, 100530, 'new_message', 'New Message', 'Nile Valley Logistics sent you a message.', 'conversation', 258, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1896, 100540, 'new_message', 'New Message', 'SolarOffshore Energy sent you a message.', 'conversation', 259, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1897, 100550, 'new_message', 'New Message', 'HR Partners Egypt sent you a message.', 'conversation', 260, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1898, 100560, 'new_message', 'New Message', 'HR Partners Egypt sent you a message.', 'conversation', 261, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1899, 100532, 'certificate_approved', 'Certificate Issued', 'Your certificate for \"Junior PHP Developer Track\" has been issued and approved.', 'certificate', 27, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1900, 100550, 'certificate_approved', 'Certificate Issued', 'Your certificate for \"Data Science Immersion\" has been issued and approved.', 'certificate', 28, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1901, 100558, 'certificate_approved', 'Certificate Issued', 'Your certificate for \"Clinical Rotation in Internal Medicine\" has been issued and approved.', 'certificate', 29, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1902, 100546, 'certificate_approved', 'Certificate Issued', 'Your certificate for \"Intro to Structural Drafting\" has been issued and approved.', 'certificate', 30, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1903, 100544, 'certificate_approved', 'Certificate Issued', 'Your certificate for \"Growth Marketing Campaigns\" has been issued and approved.', 'certificate', 31, 0, NULL, NULL, '2026-08-13 10:00:00'),
(1904, 100532, 'certificate_approved', 'Certificate Issued', 'Your certificate for \"Junior Auditor Track\" has been issued and approved.', 'certificate', 32, 0, NULL, NULL, '2026-08-13 10:00:00');

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

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `training_id`, `training_session_id`, `student_id`, `company_id`, `amount`, `currency`, `platform_commission_rate`, `platform_commission_amount`, `company_amount`, `payment_method`, `status`, `external_reference`, `paid_at`, `created_at`, `updated_at`) VALUES
(44, 100203, 226, 1466, 100144, 2500.00, 'EGP', 10.00, 250.00, 2250.00, 'manual', 'pending', 'MANUAL-20260902-00000', NULL, '2026-08-24 15:00:00', '2026-08-24 15:00:00'),
(45, 100208, 231, 1476, 100145, 3000.00, 'EGP', 10.00, 300.00, 2700.00, 'paymob', 'paid', 'PAYMOB-00000001', '2026-08-27 15:00:00', '2026-08-24 15:00:00', '2026-08-24 15:00:00'),
(46, 100212, 235, 1474, 100145, 3200.00, 'EGP', 10.00, 320.00, 2880.00, 'manual', 'pending', 'MANUAL-20260902-00002', NULL, '2026-08-24 15:00:00', '2026-08-24 15:00:00'),
(47, 100217, 240, 1480, 100147, 2000.00, 'EGP', 10.00, 200.00, 1800.00, 'paymob', 'pending', 'PAYMOB-00000003', NULL, '2026-08-24 15:00:00', '2026-08-24 15:00:00'),
(48, 100224, 247, 1468, 100149, 1800.00, 'EGP', 10.00, 180.00, 1620.00, 'manual', 'pending', 'MANUAL-20260902-00004', NULL, '2026-08-24 15:00:00', '2026-08-24 15:00:00'),
(49, 100228, 251, 1466, 100151, 2200.00, 'EGP', 10.00, 220.00, 1980.00, 'paymob', 'pending', 'PAYMOB-00000005', NULL, '2026-08-24 15:00:00', '2026-08-24 15:00:00'),
(50, 100231, 254, 1456, 100152, 3500.00, 'EGP', 10.00, 350.00, 3150.00, 'manual', 'pending', 'MANUAL-20260902-00006', NULL, '2026-08-24 15:00:00', '2026-08-24 15:00:00');

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
(1045, 100526, '396696d4416b5f2ee60b539828970ce22b8ad9c3a47866d6bb8727857c82eb5e', '2026-10-02 01:18:05', NULL, '2026-09-02 04:18:06'),
(1052, 100528, '4103cf549ec8dfb9ef3ed7cae70510ada4487c3796c75f9aa947d19dfaafbc1a', '2026-10-02 03:02:39', NULL, '2026-09-02 06:02:40'),
(1053, 100568, '149d0ef4051abda64eccd31521e2abce377735cfbd22b202d20fbc54e2fb6a2a', '2026-10-02 03:03:53', NULL, '2026-09-02 06:03:53'),
(1059, 100588, 'f81e5b027ce68c46c345ecc55f6e8fa91d3a8c34861404be4f02d2057b0151ce', '2026-10-04 01:54:37', NULL, '2026-09-04 04:54:37');

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

--
-- Dumping data for table `saved_trainings`
--

INSERT INTO `saved_trainings` (`id`, `student_id`, `training_id`, `created_at`) VALUES
(451, 1454, 100203, '2026-08-17 16:00:00'),
(452, 1455, 100205, '2026-08-18 16:00:00'),
(453, 1455, 100206, '2026-08-17 16:00:00'),
(454, 1456, 100209, '2026-08-17 16:00:00'),
(455, 1457, 100211, '2026-08-18 16:00:00'),
(456, 1457, 100212, '2026-08-17 16:00:00'),
(457, 1458, 100214, '2026-08-18 16:00:00'),
(458, 1458, 100215, '2026-08-17 16:00:00'),
(459, 1459, 100217, '2026-08-18 16:00:00'),
(460, 1459, 100218, '2026-08-17 16:00:00'),
(461, 1460, 100221, '2026-08-17 16:00:00'),
(462, 1461, 100223, '2026-08-18 16:00:00'),
(463, 1461, 100224, '2026-08-17 16:00:00'),
(464, 1462, 100226, '2026-08-18 16:00:00'),
(465, 1463, 100229, '2026-08-18 16:00:00'),
(466, 1463, 100230, '2026-08-17 16:00:00'),
(467, 1464, 100232, '2026-08-18 16:00:00'),
(468, 1464, 100233, '2026-08-17 16:00:00'),
(469, 1465, 100236, '2026-08-17 16:00:00'),
(470, 1466, 100238, '2026-08-18 16:00:00'),
(471, 1466, 100239, '2026-08-17 16:00:00'),
(472, 1467, 100203, '2026-08-17 16:00:00'),
(473, 1468, 100205, '2026-08-18 16:00:00'),
(474, 1468, 100206, '2026-08-17 16:00:00'),
(475, 1469, 100209, '2026-08-17 16:00:00'),
(476, 1470, 100211, '2026-08-18 16:00:00'),
(477, 1470, 100212, '2026-08-17 16:00:00'),
(478, 1471, 100214, '2026-08-18 16:00:00'),
(479, 1471, 100215, '2026-08-17 16:00:00'),
(480, 1472, 100217, '2026-08-18 16:00:00'),
(481, 1472, 100218, '2026-08-17 16:00:00'),
(482, 1473, 100221, '2026-08-17 16:00:00'),
(483, 1474, 100223, '2026-08-18 16:00:00'),
(484, 1474, 100224, '2026-08-17 16:00:00'),
(485, 1475, 100226, '2026-08-18 16:00:00'),
(486, 1476, 100229, '2026-08-18 16:00:00'),
(487, 1476, 100230, '2026-08-17 16:00:00'),
(488, 1477, 100232, '2026-08-18 16:00:00'),
(489, 1477, 100233, '2026-08-17 16:00:00'),
(490, 1478, 100236, '2026-08-17 16:00:00'),
(491, 1479, 100238, '2026-08-18 16:00:00'),
(492, 1479, 100239, '2026-08-17 16:00:00'),
(493, 1480, 100203, '2026-08-17 16:00:00'),
(494, 1481, 100205, '2026-08-18 16:00:00'),
(495, 1481, 100206, '2026-08-17 16:00:00'),
(496, 1482, 100209, '2026-08-17 16:00:00'),
(497, 1483, 100211, '2026-08-18 16:00:00'),
(498, 1483, 100212, '2026-08-17 16:00:00'),
(499, 1484, 100214, '2026-08-18 16:00:00'),
(500, 1484, 100215, '2026-08-17 16:00:00'),
(501, 1485, 100217, '2026-08-18 16:00:00'),
(502, 1485, 100218, '2026-08-17 16:00:00'),
(503, 1486, 100221, '2026-08-17 16:00:00'),
(504, 1487, 100223, '2026-08-18 16:00:00'),
(505, 1487, 100224, '2026-08-17 16:00:00'),
(506, 1488, 100226, '2026-08-18 16:00:00'),
(507, 1489, 100229, '2026-08-18 16:00:00'),
(508, 1489, 100230, '2026-08-17 16:00:00'),
(509, 1490, 100232, '2026-08-18 16:00:00'),
(510, 1490, 100233, '2026-08-17 16:00:00'),
(511, 1491, 100236, '2026-08-17 16:00:00'),
(512, 1492, 100238, '2026-08-18 16:00:00'),
(513, 1492, 100239, '2026-08-17 16:00:00'),
(514, 1493, 100203, '2026-08-17 16:00:00'),
(531, 1498, 100239, '2026-09-02 07:51:56'),
(534, 1498, 100237, '2026-09-02 08:06:18'),
(535, 1498, 100236, '2026-09-02 08:06:25'),
(537, 1498, 100238, '2026-09-02 08:06:37'),
(540, 1498, 100204, '2026-09-02 08:15:25');

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

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `is_active`, `created_at`) VALUES
(131, 'PHP', 1, '2026-08-30 00:59:43'),
(132, 'JavaScript', 1, '2026-08-30 00:59:43'),
(133, 'TypeScript', 1, '2026-08-30 00:59:43'),
(134, 'Python', 1, '2026-08-30 00:59:43'),
(135, 'Java', 1, '2026-08-30 00:59:43'),
(136, 'C++', 1, '2026-08-30 00:59:43'),
(137, 'C#', 1, '2026-08-30 00:59:43'),
(138, 'Go', 1, '2026-08-30 00:59:43'),
(139, 'Laravel', 1, '2026-08-30 00:59:43'),
(140, 'Symfony', 1, '2026-08-30 00:59:43'),
(141, 'React', 1, '2026-08-30 00:59:43'),
(142, 'Vue.js', 1, '2026-08-30 00:59:43'),
(143, 'Angular', 1, '2026-08-30 00:59:43'),
(144, 'HTML', 1, '2026-08-30 00:59:43'),
(145, 'CSS', 1, '2026-08-30 00:59:43'),
(146, 'Tailwind CSS', 1, '2026-08-30 00:59:43'),
(147, 'Bootstrap', 1, '2026-08-30 00:59:43'),
(148, 'Node.js', 1, '2026-08-30 00:59:43'),
(149, 'Express.js', 1, '2026-08-30 00:59:43'),
(150, 'REST API', 1, '2026-08-30 00:59:43'),
(151, 'GraphQL', 1, '2026-08-30 00:59:43'),
(152, 'MySQL', 1, '2026-08-30 00:59:43'),
(153, 'PostgreSQL', 1, '2026-08-30 00:59:43'),
(154, 'MongoDB', 1, '2026-08-30 00:59:43'),
(155, 'Redis', 1, '2026-08-30 00:59:43'),
(156, 'Git', 1, '2026-08-30 00:59:43'),
(157, 'GitHub', 1, '2026-08-30 00:59:43'),
(158, 'Docker', 1, '2026-08-30 00:59:43'),
(159, 'Linux', 1, '2026-08-30 00:59:43'),
(160, 'CI/CD', 1, '2026-08-30 00:59:43'),
(161, 'AWS', 1, '2026-08-30 00:59:43'),
(162, 'Microsoft Azure', 1, '2026-08-30 00:59:43'),
(163, 'Google Cloud', 1, '2026-08-30 00:59:43'),
(164, 'Data Analysis', 1, '2026-08-30 00:59:43'),
(165, 'Data Visualization', 1, '2026-08-30 00:59:43'),
(166, 'Machine Learning', 1, '2026-08-30 00:59:43'),
(167, 'Deep Learning', 1, '2026-08-30 00:59:43'),
(168, 'Natural Language Processing', 1, '2026-08-30 00:59:43'),
(169, 'Cybersecurity', 1, '2026-08-30 00:59:43'),
(170, 'Penetration Testing', 1, '2026-08-30 00:59:43'),
(171, 'UI Design', 1, '2026-08-30 00:59:43'),
(172, 'UX Design', 1, '2026-08-30 00:59:43'),
(173, 'Figma', 1, '2026-08-30 00:59:43'),
(174, 'Adobe Photoshop', 1, '2026-08-30 00:59:43'),
(175, 'Adobe Illustrator', 1, '2026-08-30 00:59:43'),
(176, 'Project Management', 1, '2026-08-30 00:59:43'),
(177, 'Business Analysis', 1, '2026-08-30 00:59:43'),
(178, 'Digital Marketing', 1, '2026-08-30 00:59:43'),
(179, 'SEO', 1, '2026-08-30 00:59:43'),
(180, 'Content Writing', 1, '2026-08-30 00:59:43'),
(181, 'Communication', 1, '2026-08-30 00:59:43'),
(182, 'Teamwork', 1, '2026-08-30 00:59:43'),
(183, 'Problem Solving', 1, '2026-08-30 00:59:43'),
(184, 'Time Management', 1, '2026-08-30 00:59:43'),
(185, 'Leadership', 1, '2026-08-30 00:59:43'),
(186, 'English', 1, '2026-08-30 00:59:43'),
(187, 'French', 1, '2026-08-30 00:59:43'),
(188, 'German', 1, '2026-08-30 00:59:43'),
(479, 'SQL', 1, '2026-09-02 03:16:52'),
(480, 'Excel', 1, '2026-09-02 03:16:52'),
(481, 'Power BI', 1, '2026-09-02 03:16:52'),
(482, 'Flutter', 1, '2026-09-02 03:16:52'),
(483, 'Dart', 1, '2026-09-02 03:16:52'),
(484, 'Next.js', 1, '2026-09-02 03:16:52'),
(485, 'Django', 1, '2026-09-02 03:16:52'),
(486, 'React Native', 1, '2026-09-02 03:16:52'),
(487, 'Kubernetes', 1, '2026-09-02 03:16:52'),
(488, 'Selenium', 1, '2026-09-02 03:16:53'),
(489, 'Swift', 1, '2026-09-02 03:16:53'),
(490, 'Kotlin', 1, '2026-09-02 03:16:53'),
(491, 'AutoCAD', 1, '2026-09-02 03:16:53'),
(492, 'SolidWorks', 1, '2026-09-02 03:16:53'),
(493, 'MATLAB', 1, '2026-09-02 03:16:53'),
(494, 'Statistics', 1, '2026-09-02 03:16:53'),
(495, 'Creativity', 1, '2026-09-02 03:16:53'),
(496, 'Negotiation', 1, '2026-09-02 03:16:53'),
(497, 'Research', 1, '2026-09-02 03:16:53'),
(498, 'Google Ads', 1, '2026-09-02 03:16:53'),
(499, 'Sales', 1, '2026-09-02 03:16:53');

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
(199, 'Backend Development', NULL, 12, NULL, 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(200, 'Frontend Development', NULL, 12, NULL, 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(201, 'Full Stack Development', NULL, 12, NULL, 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(202, 'Mobile Development', NULL, 12, NULL, 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(203, 'Cloud Computing', NULL, 12, NULL, 1, '2026-09-02 03:16:52', '2026-09-02 03:16:52'),
(204, 'DevOps', NULL, 12, NULL, 1, '2026-09-02 03:16:52', '2026-09-02 03:16:52'),
(205, 'Data Analysis', NULL, 12, NULL, 1, '2026-09-02 03:16:52', '2026-09-02 03:16:52'),
(206, 'Machine Learning', NULL, 12, NULL, 1, '2026-09-02 03:16:52', '2026-09-02 03:16:52');

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
(1454, 100528, 'Omar Khaled El-Shazly', '01223456781', 'Omar Khaled El-Shazly is a computer science student at Cairo University specializing in Software Engineering, based in New Cairo. Seeking a hands-on training opportunity in Software Engineering to complement their academic background.', 51, 60, 12, 32, 103, '2026', 'New Cairo', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1455, 100529, 'Mariam Ahmed Hassan', '01112345682', 'Mariam Ahmed Hassan is a computer science student at Cairo University specializing in Mobile Development, based in Nasr City. Seeking a hands-on training opportunity in Mobile Development to complement their academic background.', 51, 60, 12, 32, 202, '2025', 'Nasr City', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1456, 100530, 'Youssef Mohamed Farouk', '01515678983', 'Youssef Mohamed Farouk is a computer science student at Ain Shams University specializing in Web Development, based in Heliopolis. Seeking a hands-on training opportunity in Web Development to complement their academic background.', 52, 65, 12, 32, 107, '2026', 'Heliopolis', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1457, 100531, 'Farida Tarek Lotfy', '01234567884', 'Farida Tarek Lotfy is a computer science student at Ain Shams University specializing in Frontend Development, based in Maadi. Seeking a hands-on training opportunity in Frontend Development to complement their academic background.', 52, 65, 12, 32, 200, '2024', 'Maadi', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1458, 100532, 'Ahmed Nabil Samir', '01115678985', 'Ahmed Nabil Samir is a computer science student at Alexandria University specializing in Backend Development, based in Alexandria. Seeking a hands-on training opportunity in Backend Development to complement their academic background.', 53, 69, 12, 32, 199, '2025', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1459, 100533, 'Nour Mahmoud Abdelaziz', '01521234586', 'Nour Mahmoud Abdelaziz is a computer science student at Alexandria University specializing in Full Stack Development, based in Alexandria. Seeking a hands-on training opportunity in Full Stack Development to complement their academic background.', 53, 69, 12, 32, 201, '2026', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1460, 100534, 'Karim Amr El-Deeb', '01232345687', 'Karim Amr El-Deeb is a computer science student at Egypt-Japan University of Science and Technology specializing in Software Engineering, based in Alexandria. Seeking a hands-on training opportunity in Software Engineering to complement their academic background.', 201, 233, 12, 32, 103, '2026', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1461, 100535, 'Salma Hossam El-Din', '01111345688', 'Salma Hossam El-Din is a computer science student at Mansoura University specializing in Frontend Development, based in Mansoura. Seeking a hands-on training opportunity in Frontend Development to complement their academic background.', 54, 73, 12, 32, 200, '2027', 'Mansoura', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1462, 100536, 'Mahmoud Khaled Gaballah', '01534567889', 'Mahmoud Khaled Gaballah is a computer science student at Tanta University specializing in Cyber Security, based in Tanta. Seeking a hands-on training opportunity in Cyber Security to complement their academic background.', 56, 76, 12, 32, 106, '2025', 'Tanta', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1463, 100537, 'Mohamed Ashraf Khattab', '01011223390', 'Mohamed Ashraf Khattab is a computer science student at Helwan University specializing in Data Analysis, based in Helwan. Seeking a hands-on training opportunity in Data Analysis to complement their academic background.', 59, 78, 12, 32, 205, '2025', 'Helwan', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1464, 100538, 'Sara Mohamed Fahim', '01122334491', 'Sara Mohamed Fahim is a computer science student at Helwan University specializing in Artificial Intelligence, based in Helwan. Seeking a hands-on training opportunity in Artificial Intelligence to complement their academic background.', 59, 78, 12, 32, 104, '2026', 'Helwan', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1465, 100539, 'Amr Sherif Abdelghany', '01533445592', 'Amr Sherif Abdelghany is a computer science student at German University in Cairo specializing in Machine Learning, based in New Cairo. Seeking a hands-on training opportunity in Machine Learning to complement their academic background.', 73, 82, 12, 32, 206, '2026', 'New Cairo', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1466, 100540, 'Menna Amr Nassar', '01044556693', 'Menna Amr Nassar is a computer science student at British University in Egypt specializing in Cloud Computing, based in El Shorouk. Seeking a hands-on training opportunity in Cloud Computing to complement their academic background.', 74, 84, 12, 32, 203, '2024', 'El Shorouk', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1467, 100541, 'Mohamed Adel Fathy', '01155667794', 'Mohamed Adel Fathy is a engineering student at Cairo University specializing in Mechanical Engineering, based in Giza. Seeking a hands-on training opportunity in Mechanical Engineering to complement their academic background.', 51, 59, 9, 28, 92, '2025', 'Giza', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1468, 100542, 'Youssef Omar Shawky', '01566778895', 'Youssef Omar Shawky is a engineering student at Ain Shams University specializing in Civil Engineering, based in Heliopolis. Seeking a hands-on training opportunity in Civil Engineering to complement their academic background.', 52, 64, 9, 28, 93, '2026', 'Heliopolis', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1469, 100543, 'Omar Hisham Mostafa', '01077889996', 'Omar Hisham Mostafa is a engineering student at German University in Cairo specializing in Electrical Engineering, based in Zamalek. Seeking a hands-on training opportunity in Electrical Engineering to complement their academic background.', 73, 82, 9, 28, 94, '2025', 'Zamalek', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1470, 100544, 'Malak Ahmed El-Shennawy', '01188990097', 'Malak Ahmed El-Shennawy is a engineering student at Pharos University in Alexandria specializing in Architecture, based in Alexandria. Seeking a hands-on training opportunity in Architecture to complement their academic background.', 202, 235, 9, 40, 95, '2026', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1471, 100545, 'Abdallah Yasser Kamel', '01599001198', 'Abdallah Yasser Kamel is a engineering student at Alexandria University specializing in Mechanical Engineering, based in Alexandria. Seeking a hands-on training opportunity in Mechanical Engineering to complement their academic background.', 53, 68, 9, 28, 92, '2025', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1472, 100546, 'Salma Wael El-Maghraby', '01011223399', 'Salma Wael El-Maghraby is a medicine student at Cairo University specializing in General Medicine, based in Nasr City. Seeking a hands-on training opportunity in General Medicine to complement their academic background.', 51, 63, 10, 35, 96, '2026', 'Nasr City', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1473, 100547, 'Yara Ibrahim Hassan', '01122233391', 'Yara Ibrahim Hassan is a medicine student at Ain Shams University specializing in Pediatrics, based in Maadi. Seeking a hands-on training opportunity in Pediatrics to complement their academic background.', 52, 643, 10, 35, 98, '2027', 'Maadi', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1474, 100548, 'Hana Mahmoud Ezzat', '01533344492', 'Hana Mahmoud Ezzat is a pharmacy student at Alexandria University specializing in Clinical Pharmacy, based in Alexandria. Seeking a hands-on training opportunity in Clinical Pharmacy to complement their academic background.', 53, 644, 11, 36, 100, '2026', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1475, 100549, 'Omar Khaled Soliman', '01044455593', 'Omar Khaled Soliman is a pharmacy student at Pharos University in Alexandria specializing in Pharmacology, based in Alexandria. Seeking a hands-on training opportunity in Pharmacology to complement their academic background.', 202, 236, 11, 36, 102, '2025', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1476, 100550, 'Youssef Amr El-Gohary', '01155566694', 'Youssef Amr El-Gohary is a business student at Cairo University specializing in Marketing, based in Nasr City. Seeking a hands-on training opportunity in Marketing to complement their academic background.', 51, 61, 13, 30, 108, '2026', 'Nasr City', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1477, 100551, 'Alaa Mohamed Fawzy', '01566677795', 'Alaa Mohamed Fawzy is a business student at Ain Shams University specializing in Marketing, based in Heliopolis. Seeking a hands-on training opportunity in Marketing to complement their academic background.', 52, 66, 13, 30, 108, '2025', 'Heliopolis', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1478, 100552, 'Menna Hassan Ibrahim', '01077788896', 'Menna Hassan Ibrahim is a business student at German University in Cairo specializing in Business Administration, based in New Cairo. Seeking a hands-on training opportunity in Business Administration to complement their academic background.', 73, 83, 13, 31, 110, '2026', 'New Cairo', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1479, 100553, 'Omar Sherif Shahin', '01188899997', 'Omar Sherif Shahin is a business student at The American University in Cairo specializing in Human Resources, based in New Cairo. Seeking a hands-on training opportunity in Human Resources to complement their academic background.', 72, 80, 13, 31, 109, '2025', 'New Cairo', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1480, 100554, 'Nourhan Samy Abdelsalam', '01599900098', 'Nourhan Samy Abdelsalam is a business student at Future University in Egypt specializing in Sales, based in New Cairo. Seeking a hands-on training opportunity in Sales to complement their academic background.', 75, 87, 13, 31, 111, '2026', 'New Cairo', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1481, 100555, 'Aya Mostafa El-Feky', '01011122299', 'Aya Mostafa El-Feky is a law student at Cairo University specializing in Corporate Law, based in Giza. Seeking a hands-on training opportunity in Corporate Law to complement their academic background.', 51, 646, 14, 38, 112, '2026', 'Giza', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1482, 100556, 'Mariam El-Sayed Lotfy', '01122233390', 'Mariam El-Sayed Lotfy is a law student at Alexandria University specializing in Commercial Law, based in Alexandria. Seeking a hands-on training opportunity in Commercial Law to complement their academic background.', 53, 645, 14, 38, 114, '2025', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1483, 100557, 'Hager Anwar El-Sayed', '01533344491', 'Hager Anwar El-Sayed is a media student at Cairo University specializing in Digital Media, based in Nasr City. Seeking a hands-on training opportunity in Digital Media to complement their academic background.', 51, 647, 15, 29, 116, '2026', 'Nasr City', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1484, 100558, 'Ahmed Seif El-Din', '01044455592', 'Ahmed Seif El-Din is a media student at Cairo University specializing in Journalism, based in Maadi. Seeking a hands-on training opportunity in Journalism to complement their academic background.', 51, 647, 15, 29, 115, '2025', 'Maadi', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1485, 100559, 'Esraa Hany El-Badrawi', '01155566693', 'Esraa Hany El-Badrawi is a design student at Cairo University specializing in UI/UX Design, based in Nasr City. Seeking a hands-on training opportunity in UI/UX Design to complement their academic background.', 51, 648, 16, 39, 119, '2026', 'Nasr City', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1486, 100560, 'George Magdy Saad', '01566677794', 'George Magdy Saad is a design student at Helwan University specializing in Graphic Design, based in Zamalek. Seeking a hands-on training opportunity in Graphic Design to complement their academic background.', 59, 649, 16, 39, 121, '2025', 'Zamalek', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1487, 100561, 'Salma Nabil El-Din', '01077788895', 'Salma Nabil El-Din is a design student at Cairo University specializing in Product Design, based in Dokki. Seeking a hands-on training opportunity in Product Design to complement their academic background.', 51, 648, 16, 39, 120, '2026', 'Dokki', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1488, 100562, 'Mariam Tarek Nabil', '01188899996', 'Mariam Tarek Nabil is a design student at Cairo University specializing in UI/UX Design, based in 6 October City. Seeking a hands-on training opportunity in UI/UX Design to complement their academic background.', 51, 648, 16, 39, 119, '2027', '6 October City', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1489, 100563, 'Mohamed Essam El-Hadidi', '01599900097', 'Mohamed Essam El-Hadidi is a accounting student at Ain Shams University specializing in Financial Accounting, based in Nasr City. Seeking a hands-on training opportunity in Financial Accounting to complement their academic background.', 52, 66, 17, 30, 122, '2025', 'Nasr City', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1490, 100564, 'Yasmin Ahmed Talaat', '01011122298', 'Yasmin Ahmed Talaat is a accounting student at Cairo University specializing in Auditing, based in Maadi. Seeking a hands-on training opportunity in Auditing to complement their academic background.', 51, 61, 17, 30, 124, '2026', 'Maadi', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1491, 100565, 'Abdullah Mohamed El-Rahman', '01122233399', 'Abdullah Mohamed El-Rahman is a accounting student at Mansoura University specializing in Management Accounting, based in Mansoura. Seeking a hands-on training opportunity in Management Accounting to complement their academic background.', 54, 74, 17, 30, 123, '2025', 'Mansoura', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1492, 100566, 'Rania Sameh Fouad', '01533344490', 'Rania Sameh Fouad is a accounting student at Alexandria University specializing in Financial Accounting, based in Alexandria. Seeking a hands-on training opportunity in Financial Accounting to complement their academic background.', 53, 70, 17, 30, 122, '2026', 'Alexandria', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1493, 100567, 'George Farid Naguib', '01212345678', 'George Farid Naguib is a computer science student at Cairo University specializing in DevOps, based in Giza. Seeking a hands-on training opportunity in DevOps to complement their academic background.', 51, 60, 12, 32, 204, '2026', 'Giza', NULL, NULL, 1, '2026-07-24 10:00:00', '2026-07-24 10:00:00'),
(1498, 100588, 'Mohamed Ahmed', NULL, NULL, NULL, NULL, 12, NULL, 199, NULL, NULL, NULL, NULL, 0, '2026-09-02 05:23:43', '2026-09-02 05:23:43');

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

--
-- Dumping data for table `student_skills`
--

INSERT INTO `student_skills` (`student_id`, `skill_id`, `proficiency`, `created_at`) VALUES
(1454, 131, 'beginner', '2026-07-24 10:00:00'),
(1454, 132, 'intermediate', '2026-07-24 10:00:00'),
(1454, 139, 'intermediate', '2026-07-24 10:00:00'),
(1454, 150, 'expert', '2026-07-24 10:00:00'),
(1454, 152, 'advanced', '2026-07-24 10:00:00'),
(1454, 156, 'beginner', '2026-07-24 10:00:00'),
(1454, 182, 'advanced', '2026-07-24 10:00:00'),
(1455, 156, 'expert', '2026-07-24 10:00:00'),
(1455, 171, 'beginner', '2026-07-24 10:00:00'),
(1455, 181, 'intermediate', '2026-07-24 10:00:00'),
(1455, 182, 'advanced', '2026-07-24 10:00:00'),
(1455, 183, 'expert', '2026-07-24 10:00:00'),
(1455, 482, 'beginner', '2026-07-24 10:00:00'),
(1455, 483, 'intermediate', '2026-07-24 10:00:00'),
(1455, 486, 'advanced', '2026-07-24 10:00:00'),
(1456, 132, 'beginner', '2026-07-24 10:00:00'),
(1456, 133, 'advanced', '2026-07-24 10:00:00'),
(1456, 141, 'intermediate', '2026-07-24 10:00:00'),
(1456, 144, 'expert', '2026-07-24 10:00:00'),
(1456, 145, 'beginner', '2026-07-24 10:00:00'),
(1456, 148, 'intermediate', '2026-07-24 10:00:00'),
(1456, 150, 'expert', '2026-07-24 10:00:00'),
(1456, 156, 'advanced', '2026-07-24 10:00:00'),
(1457, 133, 'expert', '2026-07-24 10:00:00'),
(1457, 141, 'beginner', '2026-07-24 10:00:00'),
(1457, 146, 'advanced', '2026-07-24 10:00:00'),
(1457, 173, 'beginner', '2026-07-24 10:00:00'),
(1457, 179, 'intermediate', '2026-07-24 10:00:00'),
(1457, 181, 'advanced', '2026-07-24 10:00:00'),
(1457, 484, 'intermediate', '2026-07-24 10:00:00'),
(1458, 131, 'beginner', '2026-07-24 10:00:00'),
(1458, 139, 'intermediate', '2026-07-24 10:00:00'),
(1458, 150, 'intermediate', '2026-07-24 10:00:00'),
(1458, 152, 'advanced', '2026-07-24 10:00:00'),
(1458, 155, 'expert', '2026-07-24 10:00:00'),
(1458, 156, 'advanced', '2026-07-24 10:00:00'),
(1458, 158, 'beginner', '2026-07-24 10:00:00'),
(1458, 183, 'expert', '2026-07-24 10:00:00'),
(1459, 132, 'beginner', '2026-07-24 10:00:00'),
(1459, 141, 'intermediate', '2026-07-24 10:00:00'),
(1459, 148, 'advanced', '2026-07-24 10:00:00'),
(1459, 149, 'expert', '2026-07-24 10:00:00'),
(1459, 150, 'advanced', '2026-07-24 10:00:00'),
(1459, 154, 'beginner', '2026-07-24 10:00:00'),
(1459, 156, 'expert', '2026-07-24 10:00:00'),
(1459, 485, 'intermediate', '2026-07-24 10:00:00'),
(1460, 134, 'advanced', '2026-07-24 10:00:00'),
(1460, 135, 'beginner', '2026-07-24 10:00:00'),
(1460, 136, 'intermediate', '2026-07-24 10:00:00'),
(1460, 150, 'expert', '2026-07-24 10:00:00'),
(1460, 152, 'advanced', '2026-07-24 10:00:00'),
(1460, 156, 'beginner', '2026-07-24 10:00:00'),
(1460, 157, 'intermediate', '2026-07-24 10:00:00'),
(1460, 182, 'expert', '2026-07-24 10:00:00'),
(1461, 132, 'beginner', '2026-07-24 10:00:00'),
(1461, 141, 'intermediate', '2026-07-24 10:00:00'),
(1461, 144, 'expert', '2026-07-24 10:00:00'),
(1461, 145, 'advanced', '2026-07-24 10:00:00'),
(1461, 173, 'beginner', '2026-07-24 10:00:00'),
(1461, 181, 'intermediate', '2026-07-24 10:00:00'),
(1461, 184, 'advanced', '2026-07-24 10:00:00'),
(1462, 134, 'intermediate', '2026-07-24 10:00:00'),
(1462, 156, 'expert', '2026-07-24 10:00:00'),
(1462, 158, 'beginner', '2026-07-24 10:00:00'),
(1462, 159, 'beginner', '2026-07-24 10:00:00'),
(1462, 169, 'advanced', '2026-07-24 10:00:00'),
(1462, 170, 'expert', '2026-07-24 10:00:00'),
(1462, 183, 'advanced', '2026-07-24 10:00:00'),
(1462, 487, 'intermediate', '2026-07-24 10:00:00'),
(1463, 134, 'beginner', '2026-07-24 10:00:00'),
(1463, 164, 'beginner', '2026-07-24 10:00:00'),
(1463, 165, 'intermediate', '2026-07-24 10:00:00'),
(1463, 479, 'intermediate', '2026-07-24 10:00:00'),
(1463, 480, 'advanced', '2026-07-24 10:00:00'),
(1463, 481, 'expert', '2026-07-24 10:00:00'),
(1463, 494, 'advanced', '2026-07-24 10:00:00'),
(1464, 134, 'beginner', '2026-07-24 10:00:00'),
(1464, 156, 'intermediate', '2026-07-24 10:00:00'),
(1464, 164, 'expert', '2026-07-24 10:00:00'),
(1464, 166, 'intermediate', '2026-07-24 10:00:00'),
(1464, 167, 'advanced', '2026-07-24 10:00:00'),
(1464, 182, 'advanced', '2026-07-24 10:00:00'),
(1464, 479, 'beginner', '2026-07-24 10:00:00'),
(1465, 134, 'beginner', '2026-07-24 10:00:00'),
(1465, 156, 'advanced', '2026-07-24 10:00:00'),
(1465, 164, 'beginner', '2026-07-24 10:00:00'),
(1465, 166, 'intermediate', '2026-07-24 10:00:00'),
(1465, 167, 'advanced', '2026-07-24 10:00:00'),
(1465, 168, 'expert', '2026-07-24 10:00:00'),
(1465, 479, 'intermediate', '2026-07-24 10:00:00'),
(1466, 134, 'intermediate', '2026-07-24 10:00:00'),
(1466, 156, 'advanced', '2026-07-24 10:00:00'),
(1466, 158, 'intermediate', '2026-07-24 10:00:00'),
(1466, 159, 'expert', '2026-07-24 10:00:00'),
(1466, 160, 'beginner', '2026-07-24 10:00:00'),
(1466, 161, 'beginner', '2026-07-24 10:00:00'),
(1466, 183, 'expert', '2026-07-24 10:00:00'),
(1466, 487, 'advanced', '2026-07-24 10:00:00'),
(1467, 182, 'intermediate', '2026-07-24 10:00:00'),
(1467, 183, 'beginner', '2026-07-24 10:00:00'),
(1467, 480, 'expert', '2026-07-24 10:00:00'),
(1467, 491, 'intermediate', '2026-07-24 10:00:00'),
(1467, 492, 'beginner', '2026-07-24 10:00:00'),
(1467, 493, 'advanced', '2026-07-24 10:00:00'),
(1468, 176, 'intermediate', '2026-07-24 10:00:00'),
(1468, 181, 'expert', '2026-07-24 10:00:00'),
(1468, 182, 'intermediate', '2026-07-24 10:00:00'),
(1468, 183, 'beginner', '2026-07-24 10:00:00'),
(1468, 480, 'advanced', '2026-07-24 10:00:00'),
(1468, 491, 'beginner', '2026-07-24 10:00:00'),
(1469, 134, 'expert', '2026-07-24 10:00:00'),
(1469, 136, 'advanced', '2026-07-24 10:00:00'),
(1469, 182, 'advanced', '2026-07-24 10:00:00'),
(1469, 183, 'intermediate', '2026-07-24 10:00:00'),
(1469, 186, 'beginner', '2026-07-24 10:00:00'),
(1469, 491, 'intermediate', '2026-07-24 10:00:00'),
(1469, 493, 'beginner', '2026-07-24 10:00:00'),
(1470, 176, 'intermediate', '2026-07-24 10:00:00'),
(1470, 181, 'expert', '2026-07-24 10:00:00'),
(1470, 184, 'beginner', '2026-07-24 10:00:00'),
(1470, 186, 'intermediate', '2026-07-24 10:00:00'),
(1470, 491, 'beginner', '2026-07-24 10:00:00'),
(1470, 495, 'advanced', '2026-07-24 10:00:00'),
(1471, 182, 'intermediate', '2026-07-24 10:00:00'),
(1471, 183, 'beginner', '2026-07-24 10:00:00'),
(1471, 188, 'advanced', '2026-07-24 10:00:00'),
(1471, 480, 'expert', '2026-07-24 10:00:00'),
(1471, 491, 'intermediate', '2026-07-24 10:00:00'),
(1471, 492, 'beginner', '2026-07-24 10:00:00'),
(1471, 493, 'advanced', '2026-07-24 10:00:00'),
(1472, 181, 'beginner', '2026-07-24 10:00:00'),
(1472, 182, 'intermediate', '2026-07-24 10:00:00'),
(1472, 183, 'advanced', '2026-07-24 10:00:00'),
(1472, 184, 'beginner', '2026-07-24 10:00:00'),
(1472, 186, 'expert', '2026-07-24 10:00:00'),
(1472, 497, 'intermediate', '2026-07-24 10:00:00'),
(1473, 181, 'beginner', '2026-07-24 10:00:00'),
(1473, 182, 'intermediate', '2026-07-24 10:00:00'),
(1473, 183, 'advanced', '2026-07-24 10:00:00'),
(1473, 185, 'beginner', '2026-07-24 10:00:00'),
(1473, 186, 'expert', '2026-07-24 10:00:00'),
(1473, 497, 'intermediate', '2026-07-24 10:00:00'),
(1474, 181, 'beginner', '2026-07-24 10:00:00'),
(1474, 182, 'intermediate', '2026-07-24 10:00:00'),
(1474, 183, 'intermediate', '2026-07-24 10:00:00'),
(1474, 184, 'beginner', '2026-07-24 10:00:00'),
(1474, 186, 'advanced', '2026-07-24 10:00:00'),
(1474, 480, 'expert', '2026-07-24 10:00:00'),
(1475, 164, 'expert', '2026-07-24 10:00:00'),
(1475, 181, 'beginner', '2026-07-24 10:00:00'),
(1475, 183, 'intermediate', '2026-07-24 10:00:00'),
(1475, 186, 'advanced', '2026-07-24 10:00:00'),
(1475, 480, 'intermediate', '2026-07-24 10:00:00'),
(1475, 497, 'beginner', '2026-07-24 10:00:00'),
(1476, 178, 'beginner', '2026-07-24 10:00:00'),
(1476, 179, 'intermediate', '2026-07-24 10:00:00'),
(1476, 180, 'advanced', '2026-07-24 10:00:00'),
(1476, 181, 'beginner', '2026-07-24 10:00:00'),
(1476, 182, 'advanced', '2026-07-24 10:00:00'),
(1476, 480, 'expert', '2026-07-24 10:00:00'),
(1476, 495, 'intermediate', '2026-07-24 10:00:00'),
(1477, 178, 'beginner', '2026-07-24 10:00:00'),
(1477, 179, 'intermediate', '2026-07-24 10:00:00'),
(1477, 180, 'expert', '2026-07-24 10:00:00'),
(1477, 181, 'intermediate', '2026-07-24 10:00:00'),
(1477, 183, 'advanced', '2026-07-24 10:00:00'),
(1477, 480, 'beginner', '2026-07-24 10:00:00'),
(1477, 498, 'advanced', '2026-07-24 10:00:00'),
(1478, 176, 'expert', '2026-07-24 10:00:00'),
(1478, 177, 'advanced', '2026-07-24 10:00:00'),
(1478, 181, 'intermediate', '2026-07-24 10:00:00'),
(1478, 182, 'advanced', '2026-07-24 10:00:00'),
(1478, 185, 'beginner', '2026-07-24 10:00:00'),
(1478, 480, 'beginner', '2026-07-24 10:00:00'),
(1478, 481, 'intermediate', '2026-07-24 10:00:00'),
(1479, 177, 'advanced', '2026-07-24 10:00:00'),
(1479, 181, 'beginner', '2026-07-24 10:00:00'),
(1479, 182, 'intermediate', '2026-07-24 10:00:00'),
(1479, 184, 'beginner', '2026-07-24 10:00:00'),
(1479, 185, 'intermediate', '2026-07-24 10:00:00'),
(1479, 480, 'expert', '2026-07-24 10:00:00'),
(1480, 177, 'intermediate', '2026-07-24 10:00:00'),
(1480, 181, 'beginner', '2026-07-24 10:00:00'),
(1480, 182, 'beginner', '2026-07-24 10:00:00'),
(1480, 184, 'expert', '2026-07-24 10:00:00'),
(1480, 185, 'advanced', '2026-07-24 10:00:00'),
(1480, 496, 'intermediate', '2026-07-24 10:00:00'),
(1481, 177, 'intermediate', '2026-07-24 10:00:00'),
(1481, 181, 'beginner', '2026-07-24 10:00:00'),
(1481, 183, 'advanced', '2026-07-24 10:00:00'),
(1481, 184, 'beginner', '2026-07-24 10:00:00'),
(1481, 185, 'expert', '2026-07-24 10:00:00'),
(1481, 186, 'intermediate', '2026-07-24 10:00:00'),
(1482, 177, 'expert', '2026-07-24 10:00:00'),
(1482, 181, 'beginner', '2026-07-24 10:00:00'),
(1482, 182, 'beginner', '2026-07-24 10:00:00'),
(1482, 183, 'advanced', '2026-07-24 10:00:00'),
(1482, 184, 'intermediate', '2026-07-24 10:00:00'),
(1482, 186, 'intermediate', '2026-07-24 10:00:00'),
(1483, 174, 'expert', '2026-07-24 10:00:00'),
(1483, 178, 'beginner', '2026-07-24 10:00:00'),
(1483, 179, 'intermediate', '2026-07-24 10:00:00'),
(1483, 180, 'advanced', '2026-07-24 10:00:00'),
(1483, 181, 'intermediate', '2026-07-24 10:00:00'),
(1483, 184, 'advanced', '2026-07-24 10:00:00'),
(1483, 495, 'beginner', '2026-07-24 10:00:00'),
(1484, 180, 'beginner', '2026-07-24 10:00:00'),
(1484, 181, 'intermediate', '2026-07-24 10:00:00'),
(1484, 182, 'intermediate', '2026-07-24 10:00:00'),
(1484, 184, 'beginner', '2026-07-24 10:00:00'),
(1484, 186, 'advanced', '2026-07-24 10:00:00'),
(1484, 495, 'expert', '2026-07-24 10:00:00'),
(1485, 171, 'intermediate', '2026-07-24 10:00:00'),
(1485, 172, 'advanced', '2026-07-24 10:00:00'),
(1485, 173, 'beginner', '2026-07-24 10:00:00'),
(1485, 174, 'expert', '2026-07-24 10:00:00'),
(1485, 175, 'beginner', '2026-07-24 10:00:00'),
(1485, 181, 'advanced', '2026-07-24 10:00:00'),
(1485, 495, 'intermediate', '2026-07-24 10:00:00'),
(1486, 174, 'beginner', '2026-07-24 10:00:00'),
(1486, 175, 'intermediate', '2026-07-24 10:00:00'),
(1486, 181, 'expert', '2026-07-24 10:00:00'),
(1486, 184, 'beginner', '2026-07-24 10:00:00'),
(1486, 186, 'intermediate', '2026-07-24 10:00:00'),
(1486, 495, 'advanced', '2026-07-24 10:00:00'),
(1487, 171, 'intermediate', '2026-07-24 10:00:00'),
(1487, 173, 'beginner', '2026-07-24 10:00:00'),
(1487, 174, 'advanced', '2026-07-24 10:00:00'),
(1487, 175, 'expert', '2026-07-24 10:00:00'),
(1487, 181, 'intermediate', '2026-07-24 10:00:00'),
(1487, 182, 'advanced', '2026-07-24 10:00:00'),
(1487, 495, 'beginner', '2026-07-24 10:00:00'),
(1488, 171, 'intermediate', '2026-07-24 10:00:00'),
(1488, 172, 'advanced', '2026-07-24 10:00:00'),
(1488, 173, 'beginner', '2026-07-24 10:00:00'),
(1488, 174, 'expert', '2026-07-24 10:00:00'),
(1488, 181, 'intermediate', '2026-07-24 10:00:00'),
(1488, 183, 'advanced', '2026-07-24 10:00:00'),
(1488, 495, 'beginner', '2026-07-24 10:00:00'),
(1489, 177, 'advanced', '2026-07-24 10:00:00'),
(1489, 181, 'expert', '2026-07-24 10:00:00'),
(1489, 182, 'advanced', '2026-07-24 10:00:00'),
(1489, 183, 'beginner', '2026-07-24 10:00:00'),
(1489, 184, 'intermediate', '2026-07-24 10:00:00'),
(1489, 480, 'beginner', '2026-07-24 10:00:00'),
(1489, 481, 'intermediate', '2026-07-24 10:00:00'),
(1490, 177, 'advanced', '2026-07-24 10:00:00'),
(1490, 181, 'expert', '2026-07-24 10:00:00'),
(1490, 183, 'beginner', '2026-07-24 10:00:00'),
(1490, 184, 'intermediate', '2026-07-24 10:00:00'),
(1490, 480, 'beginner', '2026-07-24 10:00:00'),
(1490, 481, 'intermediate', '2026-07-24 10:00:00'),
(1491, 177, 'advanced', '2026-07-24 10:00:00'),
(1491, 181, 'expert', '2026-07-24 10:00:00'),
(1491, 183, 'intermediate', '2026-07-24 10:00:00'),
(1491, 185, 'beginner', '2026-07-24 10:00:00'),
(1491, 480, 'beginner', '2026-07-24 10:00:00'),
(1491, 481, 'intermediate', '2026-07-24 10:00:00'),
(1492, 177, 'advanced', '2026-07-24 10:00:00'),
(1492, 181, 'expert', '2026-07-24 10:00:00'),
(1492, 182, 'intermediate', '2026-07-24 10:00:00'),
(1492, 183, 'beginner', '2026-07-24 10:00:00'),
(1492, 480, 'beginner', '2026-07-24 10:00:00'),
(1492, 481, 'intermediate', '2026-07-24 10:00:00'),
(1493, 134, 'intermediate', '2026-07-24 10:00:00'),
(1493, 156, 'advanced', '2026-07-24 10:00:00'),
(1493, 158, 'intermediate', '2026-07-24 10:00:00'),
(1493, 159, 'beginner', '2026-07-24 10:00:00'),
(1493, 160, 'expert', '2026-07-24 10:00:00'),
(1493, 161, 'beginner', '2026-07-24 10:00:00'),
(1493, 183, 'expert', '2026-07-24 10:00:00'),
(1493, 487, 'advanced', '2026-07-24 10:00:00');

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
  `faculty_id` bigint UNSIGNED DEFAULT NULL,
  `university` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applicant_type` enum('student','graduated') DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `graduation_year` year DEFAULT NULL,
  `motivation` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `training_applications`
--

INSERT INTO `training_applications` (`id`, `training_id`, `student_id`, `company_id`, `message`, `full_name`, `email`, `phone`, `city`, `address`, `why_interested`, `what_to_learn`, `skills`, `status`, `rejection_reason`, `rejection_note`, `applied_at`, `reviewed_at`, `withdrawn_at`, `reviewed_by`, `cv_file_id`, `faculty_id`, `university`, `applicant_type`, `academic_year`, `graduation_year`, `motivation`) VALUES
(1669, 100202, 1454, 100144, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'rejected', 'candidate_not_suitable', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-19 10:00:00', '2026-08-03 11:30:00', NULL, 100568, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1670, 100202, 1456, 100144, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'rejected', 'position_filled', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-19 10:00:00', '2026-08-02 11:30:00', NULL, 100568, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1671, 100202, 1458, 100144, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'accepted', NULL, NULL, '2026-07-19 10:00:00', '2026-08-01 11:30:00', NULL, 100568, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1672, 100202, 1460, 100144, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'Java, C++, MySQL, REST API, Git, GitHub', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-19 10:00:00', '2026-07-31 11:30:00', NULL, 100568, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1673, 100203, 1462, 100144, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by NileTech Solutions.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'submitted', NULL, NULL, '2026-08-12 14:00:00', NULL, NULL, NULL, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.'),
(1674, 100203, 1464, 100144, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by NileTech Solutions.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'submitted', NULL, NULL, '2026-08-11 14:00:00', NULL, NULL, NULL, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1675, 100203, 1466, 100144, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by NileTech Solutions.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'accepted', NULL, NULL, '2026-08-10 14:00:00', '2026-08-01 11:30:00', NULL, 100568, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1676, 100203, 1468, 100144, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by NileTech Solutions.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-09 14:00:00', '2026-07-31 11:30:00', NULL, 100568, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1677, 100203, 1470, 100144, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by NileTech Solutions.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'withdrawn', NULL, NULL, '2026-08-08 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1678, 100204, 1472, 100144, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'submitted', NULL, NULL, '2026-08-11 14:00:00', NULL, NULL, NULL, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1679, 100204, 1474, 100144, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-10 14:00:00', NULL, NULL, NULL, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1680, 100204, 1476, 100144, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'accepted', NULL, NULL, '2026-08-09 14:00:00', '2026-08-01 11:30:00', NULL, 100568, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.'),
(1681, 100204, 1478, 100144, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-08 14:00:00', '2026-07-31 11:30:00', NULL, 100568, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1682, 100204, 1480, 100144, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Backend Development.', 'Hands-on knowledge of Backend Development and the workflows used by NileTech Solutions.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'withdrawn', NULL, NULL, '2026-08-07 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1683, 100205, 1482, 100144, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'submitted', NULL, NULL, '2026-08-10 14:00:00', NULL, NULL, NULL, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1684, 100205, 1484, 100144, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-09 14:00:00', NULL, NULL, NULL, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1685, 100205, 1486, 100144, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'accepted', NULL, NULL, '2026-08-08 14:00:00', '2026-08-01 11:30:00', NULL, 100568, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1686, 100205, 1488, 100144, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-07 14:00:00', '2026-07-31 11:30:00', NULL, 100568, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1687, 100205, 1490, 100144, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'withdrawn', NULL, NULL, '2026-08-06 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.'),
(1688, 100206, 1492, 100144, 'I am Rania Sameh Fouad and I would like to join this training.', 'Rania Sameh Fouad', 'rania.fouad@gmail.com', '01533344490', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Teamwork', 'submitted', NULL, NULL, '2026-08-09 14:00:00', NULL, NULL, NULL, NULL, 70, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Financial Accounting into professional competence.'),
(1689, 100206, 1454, 100144, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'submitted', NULL, NULL, '2026-08-08 14:00:00', NULL, NULL, NULL, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1690, 100206, 1456, 100144, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'accepted', NULL, NULL, '2026-08-07 14:00:00', '2026-08-01 11:30:00', NULL, 100568, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1691, 100206, 1458, 100144, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-06 14:00:00', '2026-07-31 11:30:00', NULL, 100568, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1692, 100206, 1460, 100144, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Frontend Development.', 'Hands-on knowledge of Frontend Development and the workflows used by NileTech Solutions.', 'Java, C++, MySQL, REST API, Git, GitHub', 'withdrawn', NULL, NULL, '2026-08-05 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1693, 100207, 1462, 100144, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Software Engineering.', 'Hands-on knowledge of Software Engineering and the workflows used by NileTech Solutions.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'submitted', NULL, NULL, '2026-08-08 14:00:00', NULL, NULL, NULL, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.'),
(1694, 100207, 1464, 100144, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Software Engineering.', 'Hands-on knowledge of Software Engineering and the workflows used by NileTech Solutions.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'submitted', NULL, NULL, '2026-08-07 14:00:00', NULL, NULL, NULL, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1695, 100207, 1466, 100144, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Software Engineering.', 'Hands-on knowledge of Software Engineering and the workflows used by NileTech Solutions.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'accepted', NULL, NULL, '2026-08-06 14:00:00', '2026-08-01 11:30:00', NULL, 100568, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1696, 100207, 1468, 100144, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Software Engineering.', 'Hands-on knowledge of Software Engineering and the workflows used by NileTech Solutions.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-05 14:00:00', '2026-07-31 11:30:00', NULL, 100568, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1697, 100207, 1470, 100144, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Software Engineering.', 'Hands-on knowledge of Software Engineering and the workflows used by NileTech Solutions.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'withdrawn', NULL, NULL, '2026-08-04 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1698, 100208, 1472, 100145, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'rejected', 'candidate_not_suitable', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-13 10:00:00', '2026-08-03 11:30:00', NULL, 100569, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1699, 100208, 1474, 100145, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'rejected', 'position_filled', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-13 10:00:00', '2026-08-02 11:30:00', NULL, 100569, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1700, 100208, 1476, 100145, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'accepted', NULL, NULL, '2026-07-13 10:00:00', '2026-08-01 11:30:00', NULL, 100569, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.'),
(1701, 100208, 1478, 100145, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-13 10:00:00', '2026-07-31 11:30:00', NULL, 100569, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1702, 100209, 1480, 100145, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'submitted', NULL, NULL, '2026-08-06 14:00:00', NULL, NULL, NULL, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1703, 100209, 1482, 100145, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'submitted', NULL, NULL, '2026-08-05 14:00:00', NULL, NULL, NULL, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1704, 100209, 1484, 100145, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'accepted', NULL, NULL, '2026-08-04 14:00:00', '2026-08-01 11:30:00', NULL, 100569, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1705, 100209, 1486, 100145, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-03 14:00:00', '2026-07-31 11:30:00', NULL, 100569, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1706, 100209, 1488, 100145, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'withdrawn', NULL, NULL, '2026-08-02 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1707, 100210, 1490, 100145, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'submitted', NULL, NULL, '2026-08-05 14:00:00', NULL, NULL, NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.'),
(1708, 100210, 1492, 100145, 'I am Rania Sameh Fouad and I would like to join this training.', 'Rania Sameh Fouad', 'rania.fouad@gmail.com', '01533344490', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Teamwork', 'submitted', NULL, NULL, '2026-08-04 14:00:00', NULL, NULL, NULL, NULL, 70, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Financial Accounting into professional competence.'),
(1709, 100210, 1454, 100145, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'accepted', NULL, NULL, '2026-08-03 14:00:00', '2026-08-01 11:30:00', NULL, 100569, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1710, 100210, 1456, 100145, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-02 14:00:00', '2026-07-31 11:30:00', NULL, 100569, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1711, 100210, 1458, 100145, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Data Analysis.', 'Hands-on knowledge of Data Analysis and the workflows used by Alexandria Digital Labs.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'withdrawn', NULL, NULL, '2026-08-01 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1712, 100211, 1460, 100145, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Artificial Intelligence.', 'Hands-on knowledge of Artificial Intelligence and the workflows used by Alexandria Digital Labs.', 'Java, C++, MySQL, REST API, Git, GitHub', 'submitted', NULL, NULL, '2026-08-04 14:00:00', NULL, NULL, NULL, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1713, 100211, 1462, 100145, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Artificial Intelligence.', 'Hands-on knowledge of Artificial Intelligence and the workflows used by Alexandria Digital Labs.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'submitted', NULL, NULL, '2026-08-03 14:00:00', NULL, NULL, NULL, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.'),
(1714, 100211, 1464, 100145, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Artificial Intelligence.', 'Hands-on knowledge of Artificial Intelligence and the workflows used by Alexandria Digital Labs.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'accepted', NULL, NULL, '2026-08-02 14:00:00', '2026-08-01 11:30:00', NULL, 100569, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1715, 100211, 1466, 100145, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Artificial Intelligence.', 'Hands-on knowledge of Artificial Intelligence and the workflows used by Alexandria Digital Labs.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-01 14:00:00', '2026-07-31 11:30:00', NULL, 100569, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1716, 100211, 1468, 100145, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Artificial Intelligence.', 'Hands-on knowledge of Artificial Intelligence and the workflows used by Alexandria Digital Labs.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'withdrawn', NULL, NULL, '2026-07-31 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1717, 100212, 1470, 100145, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'submitted', NULL, NULL, '2026-08-03 14:00:00', NULL, NULL, NULL, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1718, 100212, 1472, 100145, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'submitted', NULL, NULL, '2026-08-02 14:00:00', NULL, NULL, NULL, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1719, 100212, 1474, 100145, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'accepted', NULL, NULL, '2026-08-01 14:00:00', '2026-08-01 11:30:00', NULL, 100569, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1720, 100212, 1476, 100145, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-31 14:00:00', '2026-07-31 11:30:00', NULL, 100569, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.'),
(1721, 100212, 1478, 100145, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Machine Learning.', 'Hands-on knowledge of Machine Learning and the workflows used by Alexandria Digital Labs.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'withdrawn', NULL, NULL, '2026-07-30 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1722, 100213, 1480, 100146, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in General Medicine.', 'Hands-on knowledge of General Medicine and the workflows used by Cairo Medical Center.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'rejected', 'candidate_not_suitable', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-08 10:00:00', '2026-08-03 11:30:00', NULL, 100570, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1723, 100213, 1482, 100146, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in General Medicine.', 'Hands-on knowledge of General Medicine and the workflows used by Cairo Medical Center.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'rejected', 'position_filled', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-08 10:00:00', '2026-08-02 11:30:00', NULL, 100570, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1724, 100213, 1484, 100146, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in General Medicine.', 'Hands-on knowledge of General Medicine and the workflows used by Cairo Medical Center.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'accepted', NULL, NULL, '2026-07-08 10:00:00', '2026-08-01 11:30:00', NULL, 100570, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1725, 100213, 1486, 100146, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in General Medicine.', 'Hands-on knowledge of General Medicine and the workflows used by Cairo Medical Center.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-08 10:00:00', '2026-07-31 11:30:00', NULL, 100570, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1726, 100214, 1488, 100146, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Pediatrics.', 'Hands-on knowledge of Pediatrics and the workflows used by Cairo Medical Center.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'submitted', NULL, NULL, '2026-08-01 14:00:00', NULL, NULL, NULL, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1727, 100214, 1490, 100146, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Pediatrics.', 'Hands-on knowledge of Pediatrics and the workflows used by Cairo Medical Center.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'submitted', NULL, NULL, '2026-07-31 14:00:00', NULL, NULL, NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.'),
(1728, 100214, 1492, 100146, 'I am Rania Sameh Fouad and I would like to join this training.', 'Rania Sameh Fouad', 'rania.fouad@gmail.com', '01533344490', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Pediatrics.', 'Hands-on knowledge of Pediatrics and the workflows used by Cairo Medical Center.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Teamwork', 'accepted', NULL, NULL, '2026-07-30 14:00:00', '2026-08-01 11:30:00', NULL, 100570, NULL, 70, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Financial Accounting into professional competence.'),
(1729, 100214, 1454, 100146, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Pediatrics.', 'Hands-on knowledge of Pediatrics and the workflows used by Cairo Medical Center.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-13 14:00:00', '2026-07-31 11:30:00', NULL, 100570, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1730, 100214, 1456, 100146, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Pediatrics.', 'Hands-on knowledge of Pediatrics and the workflows used by Cairo Medical Center.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'withdrawn', NULL, NULL, '2026-08-12 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1731, 100215, 1458, 100146, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Surgery.', 'Hands-on knowledge of Surgery and the workflows used by Cairo Medical Center.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'submitted', NULL, NULL, '2026-07-31 14:00:00', NULL, NULL, NULL, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1732, 100215, 1460, 100146, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Surgery.', 'Hands-on knowledge of Surgery and the workflows used by Cairo Medical Center.', 'Java, C++, MySQL, REST API, Git, GitHub', 'submitted', NULL, NULL, '2026-07-30 14:00:00', NULL, NULL, NULL, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1733, 100215, 1462, 100146, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Surgery.', 'Hands-on knowledge of Surgery and the workflows used by Cairo Medical Center.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'accepted', NULL, NULL, '2026-08-13 14:00:00', '2026-08-01 11:30:00', NULL, 100570, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.'),
(1734, 100215, 1464, 100146, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Surgery.', 'Hands-on knowledge of Surgery and the workflows used by Cairo Medical Center.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-12 14:00:00', '2026-07-31 11:30:00', NULL, 100570, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1735, 100215, 1466, 100146, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Surgery.', 'Hands-on knowledge of Surgery and the workflows used by Cairo Medical Center.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'withdrawn', NULL, NULL, '2026-08-11 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1736, 100216, 1468, 100147, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'rejected', 'candidate_not_suitable', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-05 10:00:00', '2026-08-03 11:30:00', NULL, 100571, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1737, 100216, 1470, 100147, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'rejected', 'position_filled', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-05 10:00:00', '2026-08-02 11:30:00', NULL, 100571, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1738, 100216, 1472, 100147, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'accepted', NULL, NULL, '2026-07-05 10:00:00', '2026-08-01 11:30:00', NULL, 100571, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1739, 100216, 1474, 100147, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-05 10:00:00', '2026-07-31 11:30:00', NULL, 100571, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1740, 100217, 1476, 100147, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by Atlas Engineering.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'submitted', NULL, NULL, '2026-08-13 14:00:00', NULL, NULL, NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.');
INSERT INTO `training_applications` (`id`, `training_id`, `student_id`, `company_id`, `message`, `full_name`, `email`, `phone`, `city`, `address`, `why_interested`, `what_to_learn`, `skills`, `status`, `rejection_reason`, `rejection_note`, `applied_at`, `reviewed_at`, `withdrawn_at`, `reviewed_by`, `cv_file_id`, `faculty_id`, `university`, `applicant_type`, `academic_year`, `graduation_year`, `motivation`) VALUES
(1741, 100217, 1478, 100147, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by Atlas Engineering.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'submitted', NULL, NULL, '2026-08-12 14:00:00', NULL, NULL, NULL, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1742, 100217, 1480, 100147, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by Atlas Engineering.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'accepted', NULL, NULL, '2026-08-11 14:00:00', '2026-08-01 11:30:00', NULL, 100571, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1743, 100217, 1482, 100147, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by Atlas Engineering.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-10 14:00:00', '2026-07-31 11:30:00', NULL, 100571, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1744, 100217, 1484, 100147, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by Atlas Engineering.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'withdrawn', NULL, NULL, '2026-08-09 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1745, 100218, 1486, 100147, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'submitted', NULL, NULL, '2026-08-12 14:00:00', NULL, NULL, NULL, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1746, 100218, 1488, 100147, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'submitted', NULL, NULL, '2026-08-11 14:00:00', NULL, NULL, NULL, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1747, 100218, 1490, 100147, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'accepted', NULL, NULL, '2026-08-10 14:00:00', '2026-08-01 11:30:00', NULL, 100571, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.'),
(1748, 100218, 1492, 100147, 'I am Rania Sameh Fouad and I would like to join this training.', 'Rania Sameh Fouad', 'rania.fouad@gmail.com', '01533344490', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Teamwork', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-09 14:00:00', '2026-07-31 11:30:00', NULL, 100571, NULL, 70, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Financial Accounting into professional competence.'),
(1749, 100218, 1454, 100147, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Civil Engineering.', 'Hands-on knowledge of Civil Engineering and the workflows used by Atlas Engineering.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'withdrawn', NULL, NULL, '2026-08-08 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1750, 100219, 1456, 100147, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Electrical Engineering.', 'Hands-on knowledge of Electrical Engineering and the workflows used by Atlas Engineering.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'submitted', NULL, NULL, '2026-08-11 14:00:00', NULL, NULL, NULL, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1751, 100219, 1458, 100147, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Electrical Engineering.', 'Hands-on knowledge of Electrical Engineering and the workflows used by Atlas Engineering.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'submitted', NULL, NULL, '2026-08-10 14:00:00', NULL, NULL, NULL, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1752, 100219, 1460, 100147, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Electrical Engineering.', 'Hands-on knowledge of Electrical Engineering and the workflows used by Atlas Engineering.', 'Java, C++, MySQL, REST API, Git, GitHub', 'accepted', NULL, NULL, '2026-08-09 14:00:00', '2026-08-01 11:30:00', NULL, 100571, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1753, 100219, 1462, 100147, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Electrical Engineering.', 'Hands-on knowledge of Electrical Engineering and the workflows used by Atlas Engineering.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-08 14:00:00', '2026-07-31 11:30:00', NULL, 100571, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.'),
(1754, 100219, 1464, 100147, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Electrical Engineering.', 'Hands-on knowledge of Electrical Engineering and the workflows used by Atlas Engineering.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'withdrawn', NULL, NULL, '2026-08-07 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1755, 100220, 1466, 100148, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Marketing.', 'Hands-on knowledge of Marketing and the workflows used by BrightReach Marketing.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'rejected', 'candidate_not_suitable', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-01 10:00:00', '2026-08-03 11:30:00', NULL, 100572, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1756, 100220, 1468, 100148, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Marketing.', 'Hands-on knowledge of Marketing and the workflows used by BrightReach Marketing.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'rejected', 'position_filled', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-01 10:00:00', '2026-08-02 11:30:00', NULL, 100572, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1757, 100220, 1470, 100148, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Marketing.', 'Hands-on knowledge of Marketing and the workflows used by BrightReach Marketing.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'accepted', NULL, NULL, '2026-07-01 10:00:00', '2026-08-01 11:30:00', NULL, 100572, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1758, 100220, 1472, 100148, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Marketing.', 'Hands-on knowledge of Marketing and the workflows used by BrightReach Marketing.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-01 10:00:00', '2026-07-31 11:30:00', NULL, 100572, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1759, 100221, 1474, 100148, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-09 14:00:00', NULL, NULL, NULL, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1760, 100221, 1476, 100148, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'submitted', NULL, NULL, '2026-08-08 14:00:00', NULL, NULL, NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.'),
(1761, 100221, 1478, 100148, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'accepted', NULL, NULL, '2026-08-07 14:00:00', '2026-08-01 11:30:00', NULL, 100572, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1762, 100221, 1480, 100148, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-06 14:00:00', '2026-07-31 11:30:00', NULL, 100572, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1763, 100221, 1482, 100148, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'withdrawn', NULL, NULL, '2026-08-05 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1764, 100222, 1484, 100148, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-08 14:00:00', NULL, NULL, NULL, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1765, 100222, 1486, 100148, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'submitted', NULL, NULL, '2026-08-07 14:00:00', NULL, NULL, NULL, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1766, 100222, 1488, 100148, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'accepted', NULL, NULL, '2026-08-06 14:00:00', '2026-08-01 11:30:00', NULL, 100572, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1767, 100222, 1490, 100148, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-05 14:00:00', '2026-07-31 11:30:00', NULL, 100572, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.'),
(1768, 100222, 1492, 100148, 'I am Rania Sameh Fouad and I would like to join this training.', 'Rania Sameh Fouad', 'rania.fouad@gmail.com', '01533344490', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Digital Marketing.', 'Hands-on knowledge of Digital Marketing and the workflows used by BrightReach Marketing.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Teamwork', 'withdrawn', NULL, NULL, '2026-08-04 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 70, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Financial Accounting into professional competence.'),
(1769, 100223, 1454, 100149, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Clinical Pharmacy.', 'Hands-on knowledge of Clinical Pharmacy and the workflows used by Luxor Pharma.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'submitted', NULL, NULL, '2026-08-07 14:00:00', NULL, NULL, NULL, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1770, 100223, 1456, 100149, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Clinical Pharmacy.', 'Hands-on knowledge of Clinical Pharmacy and the workflows used by Luxor Pharma.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'submitted', NULL, NULL, '2026-08-06 14:00:00', NULL, NULL, NULL, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1771, 100223, 1458, 100149, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Clinical Pharmacy.', 'Hands-on knowledge of Clinical Pharmacy and the workflows used by Luxor Pharma.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'accepted', NULL, NULL, '2026-08-05 14:00:00', '2026-08-01 11:30:00', NULL, 100573, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1772, 100223, 1460, 100149, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Clinical Pharmacy.', 'Hands-on knowledge of Clinical Pharmacy and the workflows used by Luxor Pharma.', 'Java, C++, MySQL, REST API, Git, GitHub', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-04 14:00:00', '2026-07-31 11:30:00', NULL, 100573, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1773, 100223, 1462, 100149, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Clinical Pharmacy.', 'Hands-on knowledge of Clinical Pharmacy and the workflows used by Luxor Pharma.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'withdrawn', NULL, NULL, '2026-08-03 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.'),
(1774, 100224, 1464, 100149, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Pharmacology.', 'Hands-on knowledge of Pharmacology and the workflows used by Luxor Pharma.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'submitted', NULL, NULL, '2026-08-06 14:00:00', NULL, NULL, NULL, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1775, 100224, 1466, 100149, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Pharmacology.', 'Hands-on knowledge of Pharmacology and the workflows used by Luxor Pharma.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'submitted', NULL, NULL, '2026-08-05 14:00:00', NULL, NULL, NULL, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1776, 100224, 1468, 100149, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Pharmacology.', 'Hands-on knowledge of Pharmacology and the workflows used by Luxor Pharma.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'accepted', NULL, NULL, '2026-08-04 14:00:00', '2026-08-01 11:30:00', NULL, 100573, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1777, 100224, 1470, 100149, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Pharmacology.', 'Hands-on knowledge of Pharmacology and the workflows used by Luxor Pharma.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-03 14:00:00', '2026-07-31 11:30:00', NULL, 100573, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1778, 100224, 1472, 100149, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Pharmacology.', 'Hands-on knowledge of Pharmacology and the workflows used by Luxor Pharma.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'withdrawn', NULL, NULL, '2026-08-02 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1779, 100225, 1474, 100150, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Corporate Law.', 'Hands-on knowledge of Corporate Law and the workflows used by Themis Law Partners.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-05 14:00:00', NULL, NULL, NULL, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1780, 100225, 1476, 100150, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Corporate Law.', 'Hands-on knowledge of Corporate Law and the workflows used by Themis Law Partners.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'submitted', NULL, NULL, '2026-08-04 14:00:00', NULL, NULL, NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.'),
(1781, 100225, 1478, 100150, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Corporate Law.', 'Hands-on knowledge of Corporate Law and the workflows used by Themis Law Partners.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'accepted', NULL, NULL, '2026-08-03 14:00:00', '2026-08-01 11:30:00', NULL, 100574, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1782, 100225, 1480, 100150, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Corporate Law.', 'Hands-on knowledge of Corporate Law and the workflows used by Themis Law Partners.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-02 14:00:00', '2026-07-31 11:30:00', NULL, 100574, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1783, 100225, 1482, 100150, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Corporate Law.', 'Hands-on knowledge of Corporate Law and the workflows used by Themis Law Partners.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'withdrawn', NULL, NULL, '2026-08-01 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1784, 100226, 1484, 100150, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Commercial Law.', 'Hands-on knowledge of Commercial Law and the workflows used by Themis Law Partners.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-04 14:00:00', NULL, NULL, NULL, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1785, 100226, 1486, 100150, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in Commercial Law.', 'Hands-on knowledge of Commercial Law and the workflows used by Themis Law Partners.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'submitted', NULL, NULL, '2026-08-03 14:00:00', NULL, NULL, NULL, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1786, 100226, 1488, 100150, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Commercial Law.', 'Hands-on knowledge of Commercial Law and the workflows used by Themis Law Partners.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'accepted', NULL, NULL, '2026-08-02 14:00:00', '2026-08-01 11:30:00', NULL, 100574, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1787, 100226, 1490, 100150, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Commercial Law.', 'Hands-on knowledge of Commercial Law and the workflows used by Themis Law Partners.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-01 14:00:00', '2026-07-31 11:30:00', NULL, 100574, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.'),
(1788, 100226, 1492, 100150, 'I am Rania Sameh Fouad and I would like to join this training.', 'Rania Sameh Fouad', 'rania.fouad@gmail.com', '01533344490', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Commercial Law.', 'Hands-on knowledge of Commercial Law and the workflows used by Themis Law Partners.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Teamwork', 'withdrawn', NULL, NULL, '2026-07-31 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 70, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Financial Accounting into professional competence.'),
(1789, 100227, 1454, 100151, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Auditing.', 'Hands-on knowledge of Auditing and the workflows used by LedgerPro Accounting.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'rejected', 'candidate_not_suitable', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-06-24 10:00:00', '2026-08-03 11:30:00', NULL, 100575, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1790, 100227, 1456, 100151, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Auditing.', 'Hands-on knowledge of Auditing and the workflows used by LedgerPro Accounting.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'rejected', 'position_filled', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-06-24 10:00:00', '2026-08-02 11:30:00', NULL, 100575, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1791, 100227, 1458, 100151, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Auditing.', 'Hands-on knowledge of Auditing and the workflows used by LedgerPro Accounting.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'accepted', NULL, NULL, '2026-06-24 10:00:00', '2026-08-01 11:30:00', NULL, 100575, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1792, 100227, 1460, 100151, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Auditing.', 'Hands-on knowledge of Auditing and the workflows used by LedgerPro Accounting.', 'Java, C++, MySQL, REST API, Git, GitHub', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-06-24 10:00:00', '2026-07-31 11:30:00', NULL, 100575, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1793, 100228, 1462, 100151, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'submitted', NULL, NULL, '2026-08-02 14:00:00', NULL, NULL, NULL, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.'),
(1794, 100228, 1464, 100151, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'submitted', NULL, NULL, '2026-08-01 14:00:00', NULL, NULL, NULL, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1795, 100228, 1466, 100151, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'accepted', NULL, NULL, '2026-07-31 14:00:00', '2026-08-01 11:30:00', NULL, 100575, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1796, 100228, 1468, 100151, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-07-30 14:00:00', '2026-07-31 11:30:00', NULL, 100575, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1797, 100228, 1470, 100151, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'withdrawn', NULL, NULL, '2026-08-13 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1798, 100229, 1472, 100151, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'submitted', NULL, NULL, '2026-08-01 14:00:00', NULL, NULL, NULL, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1799, 100229, 1474, 100151, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-07-31 14:00:00', NULL, NULL, NULL, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1800, 100229, 1476, 100151, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'accepted', NULL, NULL, '2026-07-30 14:00:00', '2026-08-01 11:30:00', NULL, 100575, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.'),
(1801, 100229, 1478, 100151, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-13 14:00:00', '2026-07-31 11:30:00', NULL, 100575, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1802, 100229, 1480, 100151, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Financial Accounting.', 'Hands-on knowledge of Financial Accounting and the workflows used by LedgerPro Accounting.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'withdrawn', NULL, NULL, '2026-08-12 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1803, 100230, 1482, 100152, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'submitted', NULL, NULL, '2026-07-31 14:00:00', NULL, NULL, NULL, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1804, 100230, 1484, 100152, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-07-30 14:00:00', NULL, NULL, NULL, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1805, 100230, 1486, 100152, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'accepted', NULL, NULL, '2026-08-13 14:00:00', '2026-08-01 11:30:00', NULL, 100576, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1806, 100230, 1488, 100152, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-12 14:00:00', '2026-07-31 11:30:00', NULL, 100576, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1807, 100230, 1490, 100152, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'withdrawn', NULL, NULL, '2026-08-11 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.'),
(1808, 100231, 1492, 100152, 'I am Rania Sameh Fouad and I would like to join this training.', 'Rania Sameh Fouad', 'rania.fouad@gmail.com', '01533344490', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Cloud Computing.', 'Hands-on knowledge of Cloud Computing and the workflows used by FutureWorks Software.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Teamwork', 'submitted', NULL, NULL, '2026-07-30 14:00:00', NULL, NULL, NULL, NULL, 70, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Financial Accounting into professional competence.'),
(1809, 100231, 1454, 100152, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Cloud Computing.', 'Hands-on knowledge of Cloud Computing and the workflows used by FutureWorks Software.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'submitted', NULL, NULL, '2026-08-13 14:00:00', NULL, NULL, NULL, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1810, 100231, 1456, 100152, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Cloud Computing.', 'Hands-on knowledge of Cloud Computing and the workflows used by FutureWorks Software.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'accepted', NULL, NULL, '2026-08-12 14:00:00', '2026-08-01 11:30:00', NULL, 100576, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1811, 100231, 1458, 100152, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Cloud Computing.', 'Hands-on knowledge of Cloud Computing and the workflows used by FutureWorks Software.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-11 14:00:00', '2026-07-31 11:30:00', NULL, 100576, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1812, 100231, 1460, 100152, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Cloud Computing.', 'Hands-on knowledge of Cloud Computing and the workflows used by FutureWorks Software.', 'Java, C++, MySQL, REST API, Git, GitHub', 'withdrawn', NULL, NULL, '2026-08-10 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1813, 100232, 1462, 100152, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'submitted', NULL, NULL, '2026-08-13 14:00:00', NULL, NULL, NULL, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.');
INSERT INTO `training_applications` (`id`, `training_id`, `student_id`, `company_id`, `message`, `full_name`, `email`, `phone`, `city`, `address`, `why_interested`, `what_to_learn`, `skills`, `status`, `rejection_reason`, `rejection_note`, `applied_at`, `reviewed_at`, `withdrawn_at`, `reviewed_by`, `cv_file_id`, `faculty_id`, `university`, `applicant_type`, `academic_year`, `graduation_year`, `motivation`) VALUES
(1814, 100232, 1464, 100152, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'submitted', NULL, NULL, '2026-08-12 14:00:00', NULL, NULL, NULL, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1815, 100232, 1466, 100152, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'accepted', NULL, NULL, '2026-08-11 14:00:00', '2026-08-01 11:30:00', NULL, 100576, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1816, 100232, 1468, 100152, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-10 14:00:00', '2026-07-31 11:30:00', NULL, 100576, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1817, 100232, 1470, 100152, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Full Stack Development.', 'Hands-on knowledge of Full Stack Development and the workflows used by FutureWorks Software.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'withdrawn', NULL, NULL, '2026-08-09 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1818, 100233, 1472, 100153, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'submitted', NULL, NULL, '2026-08-12 14:00:00', NULL, NULL, NULL, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1819, 100233, 1474, 100153, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-11 14:00:00', NULL, NULL, NULL, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1820, 100233, 1476, 100153, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'accepted', NULL, NULL, '2026-08-10 14:00:00', '2026-08-01 11:30:00', NULL, 100577, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.'),
(1821, 100233, 1478, 100153, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-09 14:00:00', '2026-07-31 11:30:00', NULL, 100577, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1822, 100233, 1480, 100153, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'withdrawn', NULL, NULL, '2026-08-08 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1823, 100234, 1482, 100153, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'submitted', NULL, NULL, '2026-08-11 14:00:00', NULL, NULL, NULL, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1824, 100234, 1484, 100153, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-10 14:00:00', NULL, NULL, NULL, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1825, 100234, 1486, 100153, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'accepted', NULL, NULL, '2026-08-09 14:00:00', '2026-08-01 11:30:00', NULL, 100577, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1826, 100234, 1488, 100153, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-08 14:00:00', '2026-07-31 11:30:00', NULL, 100577, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1827, 100234, 1490, 100153, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by GreenRetail Egypt.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'withdrawn', NULL, NULL, '2026-08-07 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.'),
(1828, 100236, 1492, 100156, 'I am Rania Sameh Fouad and I would like to join this training.', 'Rania Sameh Fouad', 'rania.fouad@gmail.com', '01533344490', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by Nile Valley Logistics.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Teamwork', 'submitted', NULL, NULL, '2026-08-09 14:00:00', NULL, NULL, NULL, NULL, 70, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Financial Accounting into professional competence.'),
(1829, 100236, 1454, 100156, 'I am Omar Khaled El-Shazly and I would like to join this training.', 'Omar Khaled El-Shazly', 'omar.shazly@gmail.com', '01223456781', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by Nile Valley Logistics.', 'PHP, Laravel, MySQL, REST API, Git, JavaScript', 'submitted', NULL, NULL, '2026-08-08 14:00:00', NULL, NULL, NULL, NULL, 60, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1830, 100236, 1456, 100156, 'I am Youssef Mohamed Farouk and I would like to join this training.', 'Youssef Mohamed Farouk', 'youssef.farouk@outlook.com', '01515678983', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by Nile Valley Logistics.', 'JavaScript, React, TypeScript, HTML, CSS, Node.js', 'accepted', NULL, NULL, '2026-08-07 14:00:00', '2026-08-01 11:30:00', NULL, 100580, NULL, 65, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Web Development into professional competence.'),
(1831, 100236, 1458, 100156, 'I am Ahmed Nabil Samir and I would like to join this training.', 'Ahmed Nabil Samir', 'ahmed.nabil@gmail.com', '01115678985', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by Nile Valley Logistics.', 'PHP, Laravel, MySQL, Redis, Docker, REST API', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-06 14:00:00', '2026-07-31 11:30:00', NULL, 100580, NULL, 69, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Backend Development into professional competence.'),
(1832, 100236, 1460, 100156, 'I am Karim Amr El-Deeb and I would like to join this training.', 'Karim Amr El-Deeb', 'karim.eldeeb@gmail.com', '01232345687', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Business Administration.', 'Hands-on knowledge of Business Administration and the workflows used by Nile Valley Logistics.', 'Java, C++, MySQL, REST API, Git, GitHub', 'withdrawn', NULL, NULL, '2026-08-05 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 233, 'Egypt-Japan University of Science and Technology', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Software Engineering into professional competence.'),
(1833, 100237, 1462, 100155, 'I am Mahmoud Khaled Gaballah and I would like to join this training.', 'Mahmoud Khaled Gaballah', 'mahmoud.gaballah@gmail.com', '01534567889', 'Tanta', 'Tanta', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by SolarOffshore Energy.', 'Linux, Python, Cybersecurity, Penetration Testing, Docker, Kubernetes', 'submitted', NULL, NULL, '2026-08-08 14:00:00', NULL, NULL, NULL, NULL, 76, 'Tanta University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Cyber Security into professional competence.'),
(1834, 100237, 1464, 100155, 'I am Sara Mohamed Fahim and I would like to join this training.', 'Sara Mohamed Fahim', 'sara.fahim@gmail.com', '01122334491', 'Helwan', 'Helwan', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by SolarOffshore Energy.', 'Python, Machine Learning, Deep Learning, Data Analysis, SQL, Git', 'submitted', NULL, NULL, '2026-08-07 14:00:00', NULL, NULL, NULL, NULL, 78, 'Helwan University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Artificial Intelligence into professional competence.'),
(1835, 100237, 1466, 100155, 'I am Menna Amr Nassar and I would like to join this training.', 'Menna Amr Nassar', 'menna.nassar@gmail.com', '01044556693', 'El Shorouk', 'El Shorouk', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by SolarOffshore Energy.', 'AWS, Docker, Kubernetes, Linux, CI/CD, Python', 'accepted', NULL, NULL, '2026-08-06 14:00:00', '2026-08-01 11:30:00', NULL, 100579, NULL, 84, 'British University in Egypt', 'graduated', 'Graduated 2024', '2024', 'Looking to convert academic knowledge in Cloud Computing into professional competence.'),
(1836, 100237, 1468, 100155, 'I am Youssef Omar Shawky and I would like to join this training.', 'Youssef Omar Shawky', 'youssef.shawky@gmail.com', '01566778895', 'Heliopolis', 'Heliopolis', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by SolarOffshore Energy.', 'AutoCAD, Project Management, Excel, Communication, Problem Solving, Teamwork', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-05 14:00:00', '2026-07-31 11:30:00', NULL, 100579, NULL, 64, 'Ain Shams University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Civil Engineering into professional competence.'),
(1837, 100237, 1470, 100155, 'I am Malak Ahmed El-Shennawy and I would like to join this training.', 'Malak Ahmed El-Shennawy', 'malak.shennawy@gmail.com', '01188990097', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Mechanical Engineering.', 'Hands-on knowledge of Mechanical Engineering and the workflows used by SolarOffshore Energy.', 'AutoCAD, Project Management, Creativity, Communication, Time Management, English', 'withdrawn', NULL, NULL, '2026-08-04 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 235, 'Pharos University in Alexandria', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Architecture into professional competence.'),
(1838, 100238, 1472, 100158, 'I am Salma Wael El-Maghraby and I would like to join this training.', 'Salma Wael El-Maghraby', 'salma.maghraby@gmail.com', '01011223399', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Communication, Teamwork, Problem Solving, English, Time Management, Research', 'submitted', NULL, NULL, '2026-08-07 14:00:00', NULL, NULL, NULL, NULL, 63, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in General Medicine into professional competence.'),
(1839, 100238, 1474, 100158, 'I am Hana Mahmoud Ezzat and I would like to join this training.', 'Hana Mahmoud Ezzat', 'hana.ezzat@gmail.com', '01533344492', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Communication, Problem Solving, English, Excel, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-06 14:00:00', NULL, NULL, NULL, NULL, 644, 'Alexandria University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Clinical Pharmacy into professional competence.'),
(1840, 100238, 1476, 100158, 'I am Youssef Amr El-Gohary and I would like to join this training.', 'Youssef Amr El-Gohary', 'youssef.gohary@gmail.com', '01155566694', 'Nasr City', 'Nasr City', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Digital Marketing, SEO, Content Writing, Excel, Communication, Creativity', 'accepted', NULL, NULL, '2026-08-05 14:00:00', '2026-08-01 11:30:00', NULL, 100582, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Marketing into professional competence.'),
(1841, 100238, 1478, 100158, 'I am Menna Hassan Ibrahim and I would like to join this training.', 'Menna Hassan Ibrahim', 'menna.ibrahim@gmail.com', '01077788896', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Excel, Power BI, Business Analysis, Project Management, Leadership, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-04 14:00:00', '2026-07-31 11:30:00', NULL, 100582, NULL, 83, 'German University in Cairo', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Business Administration into professional competence.'),
(1842, 100238, 1480, 100158, 'I am Nourhan Samy Abdelsalam and I would like to join this training.', 'Nourhan Samy Abdelsalam', 'nourhan.abdelsalam@gmail.com', '01599900098', 'New Cairo', 'New Cairo', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Communication, Negotiation, Leadership, Time Management, Teamwork, Business Analysis', 'withdrawn', NULL, NULL, '2026-08-03 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 87, 'Future University in Egypt', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Sales into professional competence.'),
(1843, 100239, 1482, 100158, 'I am Mariam El-Sayed Lotfy and I would like to join this training.', 'Mariam El-Sayed Lotfy', 'mariam.lotfy@gmail.com', '01122233390', 'Alexandria', 'Alexandria', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Communication, English, Problem Solving, Business Analysis, Teamwork, Time Management', 'submitted', NULL, NULL, '2026-08-06 14:00:00', NULL, NULL, NULL, NULL, 645, 'Alexandria University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Commercial Law into professional competence.'),
(1844, 100239, 1484, 100158, 'I am Ahmed Seif El-Din and I would like to join this training.', 'Ahmed Seif El-Din', 'ahmed.seif@outlook.com', '01044455592', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Content Writing, Communication, English, Creativity, Time Management, Teamwork', 'submitted', NULL, NULL, '2026-08-05 14:00:00', NULL, NULL, NULL, NULL, 647, 'Cairo University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Journalism into professional competence.'),
(1845, 100239, 1486, 100158, 'I am George Magdy Saad and I would like to join this training.', 'George Magdy Saad', 'george.saad@gmail.com', '01566677794', 'Zamalek', 'Zamalek', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Adobe Photoshop, Adobe Illustrator, Creativity, Communication, Time Management, English', 'accepted', NULL, NULL, '2026-08-04 14:00:00', '2026-08-01 11:30:00', NULL, 100582, NULL, 649, 'Helwan University', 'graduated', 'Graduated 2025', '2025', 'Looking to convert academic knowledge in Graphic Design into professional competence.'),
(1846, 100239, 1488, 100158, 'I am Mariam Tarek Nabil and I would like to join this training.', 'Mariam Tarek Nabil', 'mariam.tarek@gmail.com', '01188899996', '6 October City', '6 October City', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Figma, UI Design, UX Design, Adobe Photoshop, Creativity, Communication', 'rejected', 'other', 'The committee selected candidates whose skills aligned more closely with the program requirements.', '2026-08-03 14:00:00', '2026-07-31 11:30:00', NULL, 100582, NULL, 648, 'Cairo University', 'student', 'Class of 2027', '2027', 'Looking to convert academic knowledge in UI/UX Design into professional competence.'),
(1847, 100239, 1490, 100158, 'I am Yasmin Ahmed Talaat and I would like to join this training.', 'Yasmin Ahmed Talaat', 'yasmin.talaat@gmail.com', '01011122298', 'Maadi', 'Maadi', 'I want to deepen my practical skills in Human Resources.', 'Hands-on knowledge of Human Resources and the workflows used by HR Partners Egypt.', 'Excel, Power BI, Business Analysis, Communication, Problem Solving, Time Management', 'withdrawn', NULL, NULL, '2026-08-02 14:00:00', NULL, '2026-08-17 09:00:00', NULL, NULL, 61, 'Cairo University', 'student', 'Class of 2026', '2026', 'Looking to convert academic knowledge in Auditing into professional competence.');

-- --------------------------------------------------------

--
-- Table structure for table `training_listings`
--
CREATE TABLE `training_listings` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `specialization_id` bigint UNSIGNED NOT NULL,
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

INSERT INTO `training_listings` (`id`, `company_id`, `specialization_id`, `title`, `description`, `training_type`, `mode`, `may_lead_to_employment`, `is_paid`, `compensation_amount`, `compensation_currency`, `trial_period_days`, `capacity`, `status`, `published_at`, `starts_at`, `ends_at`, `application_deadline`, `closed_at`, `location`, `created_at`, `updated_at`) VALUES
(100202, 100144, 199, 'Junior PHP Developer Track', 'A project_based training offered by NileTech Solutions in Giza for 3 students. The program covers the most requested skills in Backend Development and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 1, 0, NULL, 'EGP', NULL, 3, 'closed', NULL, '2026-08-03 09:00:00', '2026-08-28 17:00:00', '2026-07-29 23:59:59', '2026-08-31 10:00:00', 'Giza', '2026-08-28 10:00:00', '2026-09-02 06:50:31'),
(100203, 100144, 201, 'Full Stack Web Internship', 'A hands_on training offered by NileTech Solutions in Giza for 2 students. The program covers the most requested skills in Full Stack Development and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'hybrid', 0, 1, 2500.00, 'EGP', 7, 2, 'published', '2026-07-15 10:00:00', '2026-08-24 09:00:00', '2026-09-13 17:00:00', '2026-08-14 23:59:59', NULL, 'Giza', '2026-07-15 10:00:00', '2026-09-02 06:30:34'),
(100204, 100144, 199, 'Scaling Laravel Applications', 'A project_based training offered by NileTech Solutions in Giza for 2 students. The program covers the most requested skills in Backend Development and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-07-16 10:00:00', '2026-08-25 09:00:00', '2026-09-14 17:00:00', '2026-08-15 23:59:59', NULL, 'Giza', '2026-07-16 10:00:00', '2026-09-02 06:30:34'),
(100205, 100144, 200, 'Frontend Craftsmanship Program', 'A hands_on training offered by NileTech Solutions in Giza for 3 students. The program covers the most requested skills in Frontend Development and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'hybrid', 1, 0, NULL, 'EGP', NULL, 3, 'published', '2026-07-17 10:00:00', '2026-08-26 09:00:00', '2026-09-15 17:00:00', '2026-08-16 23:59:59', NULL, 'Giza', '2026-07-17 10:00:00', '2026-09-02 06:30:34'),
(100206, 100144, 200, 'Mobile Banking UI Project', 'A project_based training offered by NileTech Solutions in Giza for 2 students. The program covers the most requested skills in Frontend Development and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-07-18 10:00:00', '2026-08-27 09:00:00', '2026-09-16 17:00:00', '2026-08-17 23:59:59', NULL, 'Giza', '2026-07-18 10:00:00', '2026-09-02 06:30:34'),
(100207, 100144, 103, 'QA Automation Essentials', 'A hands_on training offered by NileTech Solutions in Giza for 2 students. The program covers the most requested skills in Software Engineering and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-07-19 10:00:00', '2026-08-28 09:00:00', '2026-09-17 17:00:00', '2026-08-18 23:59:59', NULL, 'Giza', '2026-07-19 10:00:00', '2026-09-02 06:30:34'),
(100208, 100145, 205, 'Data Science Immersion', 'A project_based training offered by Alexandria Digital Labs in Alexandria for 3 students. The program covers the most requested skills in Data Analysis and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'hybrid', 1, 1, 3000.00, 'EGP', 7, 3, 'closed', NULL, '2026-07-28 09:00:00', '2026-08-27 17:00:00', '2026-07-23 23:59:59', '2026-08-30 10:00:00', 'Alexandria', '2026-09-03 10:00:00', '2026-09-02 06:30:34'),
(100209, 100145, 206, 'Machine Learning Engineering Internship', 'A project_based training offered by Alexandria Digital Labs in Alexandria for 2 students. The program covers the most requested skills in Machine Learning and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-07-21 10:00:00', '2026-08-30 09:00:00', '2026-09-19 17:00:00', '2026-08-20 23:59:59', NULL, 'Alexandria', '2026-07-21 10:00:00', '2026-09-02 06:30:34'),
(100210, 100145, 205, 'Business Analytics with Power BI', 'A hands_on training offered by Alexandria Digital Labs in Alexandria for 4 students. The program covers the most requested skills in Data Analysis and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'remote', 0, 0, NULL, 'EGP', NULL, 4, 'published', '2026-07-22 10:00:00', '2026-08-23 09:00:00', '2026-09-20 17:00:00', '2026-08-21 23:59:59', NULL, 'Alexandria', '2026-07-22 10:00:00', '2026-09-02 06:30:34'),
(100211, 100145, 104, 'Computer Vision Projects', 'A project_based training offered by Alexandria Digital Labs in Alexandria for 2 students. The program covers the most requested skills in Artificial Intelligence and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 1, 0, NULL, 'EGP', NULL, 2, 'published', '2026-07-23 10:00:00', '2026-08-24 09:00:00', '2026-09-21 17:00:00', '2026-08-22 23:59:59', NULL, 'Alexandria', '2026-07-23 10:00:00', '2026-09-02 06:30:34'),
(100212, 100145, 206, 'ML in Production (MLOps)', 'A project_based training offered by Alexandria Digital Labs in Alexandria for 2 students. The program covers the most requested skills in Machine Learning and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'remote', 0, 1, 3200.00, 'EGP', 10, 2, 'published', '2026-07-24 10:00:00', '2026-08-25 09:00:00', '2026-09-22 17:00:00', '2026-08-13 23:59:59', NULL, 'Alexandria', '2026-07-24 10:00:00', '2026-09-02 06:30:34'),
(100213, 100146, 96, 'Clinical Rotation in Internal Medicine', 'A shadowing training offered by Cairo Medical Center in Cairo for 4 students. The program covers the most requested skills in General Medicine and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'shadowing', 'onsite', 0, 0, NULL, 'EGP', NULL, 4, 'closed', NULL, '2026-08-02 09:00:00', '2026-08-27 17:00:00', '2026-07-28 23:59:59', '2026-08-30 10:00:00', 'Cairo', '2026-09-08 10:00:00', '2026-09-02 06:30:34'),
(100214, 100146, 98, 'Pediatrics Ward Shadowing', 'A shadowing training offered by Cairo Medical Center in Cairo for 3 students. The program covers the most requested skills in Pediatrics and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'shadowing', 'onsite', 1, 0, NULL, 'EGP', NULL, 3, 'published', '2026-07-26 10:00:00', '2026-08-27 09:00:00', '2026-09-24 17:00:00', '2026-08-15 23:59:59', NULL, 'Cairo', '2026-07-26 10:00:00', '2026-09-02 06:30:34'),
(100215, 100146, 97, 'Surgical Theater Observership', 'A shadowing training offered by Cairo Medical Center in Cairo for 2 students. The program covers the most requested skills in Surgery and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'shadowing', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-07-27 10:00:00', '2026-08-28 09:00:00', '2026-09-25 17:00:00', '2026-08-16 23:59:59', NULL, 'Cairo', '2026-07-27 10:00:00', '2026-09-02 06:30:34'),
(100216, 100147, 93, 'Intro to Structural Drafting', 'A hands_on training offered by Atlas Engineering in Giza for 4 students. The program covers the most requested skills in Civil Engineering and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 4, 'closed', NULL, '2026-07-30 09:00:00', '2026-08-24 17:00:00', '2026-07-25 23:59:59', '2026-08-27 10:00:00', 'Giza', '2026-09-11 10:00:00', '2026-09-02 06:30:34'),
(100217, 100147, 92, 'Mechanical CAE Workshop', 'A hands_on training offered by Atlas Engineering in Giza for 3 students. The program covers the most requested skills in Mechanical Engineering and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 1, 1, 2000.00, 'EGP', 7, 3, 'published', '2026-07-29 10:00:00', '2026-08-30 09:00:00', '2026-09-27 17:00:00', '2026-08-18 23:59:59', NULL, 'Giza', '2026-07-29 10:00:00', '2026-09-02 06:30:34'),
(100218, 100147, 93, 'Site Engineering Handbook', 'A hands_on training offered by Atlas Engineering in Giza for 3 students. The program covers the most requested skills in Civil Engineering and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 3, 'published', '2026-07-30 10:00:00', '2026-08-23 09:00:00', '2026-09-28 17:00:00', '2026-08-19 23:59:59', NULL, 'Giza', '2026-07-30 10:00:00', '2026-09-02 06:30:34'),
(100219, 100147, 94, 'Electrical Systems for Buildings', 'A hands_on training offered by Atlas Engineering in Giza for 2 students. The program covers the most requested skills in Electrical Engineering and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-07-31 10:00:00', '2026-08-24 09:00:00', '2026-09-29 17:00:00', '2026-08-20 23:59:59', NULL, 'Giza', '2026-07-31 10:00:00', '2026-09-02 06:30:34'),
(100220, 100148, 108, 'Growth Marketing Campaigns', 'A project_based training offered by BrightReach Marketing in Cairo for 3 students. The program covers the most requested skills in Marketing and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'hybrid', 1, 0, NULL, 'EGP', NULL, 3, 'closed', NULL, '2026-07-26 09:00:00', '2026-08-25 17:00:00', '2026-07-21 23:59:59', '2026-08-28 10:00:00', 'Cairo', '2026-09-15 10:00:00', '2026-09-02 06:30:34'),
(100221, 100148, 118, 'Content Studio Intensive', 'A hands_on training offered by BrightReach Marketing in Cairo for 3 students. The program covers the most requested skills in Digital Marketing and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 3, 'published', '2026-08-02 10:00:00', '2026-08-26 09:00:00', '2026-10-01 17:00:00', '2026-08-22 23:59:59', NULL, 'Cairo', '2026-08-02 10:00:00', '2026-09-02 06:30:34'),
(100222, 100148, 118, 'Digital Campaign Analytics', 'A project_based training offered by BrightReach Marketing in Cairo for 2 students. The program covers the most requested skills in Digital Marketing and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'remote', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-08-03 10:00:00', '2026-08-27 09:00:00', '2026-09-12 17:00:00', '2026-08-13 23:59:59', NULL, 'Cairo', '2026-08-03 10:00:00', '2026-09-02 06:30:34'),
(100223, 100149, 100, 'Clinical Pharmacy Rotation', 'A shadowing training offered by Luxor Pharma in Alexandria for 3 students. The program covers the most requested skills in Clinical Pharmacy and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'shadowing', 'onsite', 1, 0, NULL, 'EGP', NULL, 3, 'published', '2026-08-04 10:00:00', '2026-08-28 09:00:00', '2026-09-13 17:00:00', '2026-08-14 23:59:59', NULL, 'Alexandria', '2026-08-04 10:00:00', '2026-09-02 06:30:34'),
(100224, 100149, 102, 'Pharmacovigilance Fundamentals', 'A hands_on training offered by Luxor Pharma in Alexandria for 2 students. The program covers the most requested skills in Pharmacology and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 1, 1800.00, 'EGP', 7, 2, 'published', '2026-08-05 10:00:00', '2026-08-29 09:00:00', '2026-09-14 17:00:00', '2026-08-15 23:59:59', NULL, 'Alexandria', '2026-08-05 10:00:00', '2026-09-02 06:30:34'),
(100225, 100150, 112, 'Corporate Contract Review', 'A project_based training offered by Themis Law Partners in Cairo for 2 students. The program covers the most requested skills in Corporate Law and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-08-06 10:00:00', '2026-08-30 09:00:00', '2026-09-15 17:00:00', '2026-08-16 23:59:59', NULL, 'Cairo', '2026-08-06 10:00:00', '2026-09-02 06:30:34'),
(100226, 100150, 114, 'Commercial Law Clinic', 'A hands_on training offered by Themis Law Partners in Cairo for 3 students. The program covers the most requested skills in Commercial Law and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'hybrid', 1, 0, NULL, 'EGP', NULL, 3, 'published', '2026-08-07 10:00:00', '2026-08-23 09:00:00', '2026-09-16 17:00:00', '2026-08-17 23:59:59', NULL, 'Cairo', '2026-08-07 10:00:00', '2026-09-02 06:30:34'),
(100227, 100151, 124, 'Junior Auditor Track', 'A project_based training offered by LedgerPro Accounting in Cairo for 3 students. The program covers the most requested skills in Auditing and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 3, 'closed', NULL, '2026-07-29 09:00:00', '2026-08-28 17:00:00', '2026-07-24 23:59:59', '2026-08-31 10:00:00', 'Cairo', '2026-09-22 10:00:00', '2026-09-02 06:30:34'),
(100228, 100151, 122, 'IFRS for SMEs', 'A hands_on training offered by LedgerPro Accounting in Cairo for 2 students. The program covers the most requested skills in Financial Accounting and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 1, 2200.00, 'EGP', 7, 2, 'published', '2026-08-09 10:00:00', '2026-08-25 09:00:00', '2026-09-18 17:00:00', '2026-08-19 23:59:59', NULL, 'Cairo', '2026-08-09 10:00:00', '2026-09-02 06:30:34'),
(100229, 100151, 122, 'Bookkeeping Essentials', 'A hands_on training offered by LedgerPro Accounting in Cairo for 3 students. The program covers the most requested skills in Financial Accounting and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 1, 0, NULL, 'EGP', NULL, 3, 'published', '2026-08-10 10:00:00', '2026-08-26 09:00:00', '2026-09-19 17:00:00', '2026-08-20 23:59:59', NULL, 'Cairo', '2026-08-10 10:00:00', '2026-09-02 06:30:34'),
(100230, 100152, 201, 'React Native Product Sprint', 'A project_based training offered by FutureWorks Software in New Cairo for 3 students. The program covers the most requested skills in Full Stack Development and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'hybrid', 0, 0, NULL, 'EGP', NULL, 3, 'published', '2026-08-11 10:00:00', '2026-08-27 09:00:00', '2026-09-20 17:00:00', '2026-08-21 23:59:59', NULL, 'New Cairo', '2026-08-11 10:00:00', '2026-09-02 06:30:34'),
(100231, 100152, 203, 'Cloud Native Bootcamp', 'A hands_on training offered by FutureWorks Software in New Cairo for 2 students. The program covers the most requested skills in Cloud Computing and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'remote', 0, 1, 3500.00, 'EGP', 10, 2, 'published', '2026-08-12 10:00:00', '2026-08-28 09:00:00', '2026-09-21 17:00:00', '2026-08-22 23:59:59', NULL, 'New Cairo', '2026-08-12 10:00:00', '2026-09-02 06:30:34'),
(100232, 100152, 201, 'TypeScript Full Stack Fellowship', 'A project_based training offered by FutureWorks Software in New Cairo for 2 students. The program covers the most requested skills in Full Stack Development and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'hybrid', 1, 0, NULL, 'EGP', NULL, 2, 'published', '2026-08-13 10:00:00', '2026-08-29 09:00:00', '2026-09-22 17:00:00', '2026-08-13 23:59:59', NULL, 'New Cairo', '2026-08-13 10:00:00', '2026-09-02 06:30:34'),
(100233, 100153, 110, 'Retail Operations Management', 'A project_based training offered by GreenRetail Egypt in Giza for 3 students. The program covers the most requested skills in Business Administration and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 3, 'published', '2026-08-14 10:00:00', '2026-08-30 09:00:00', '2026-09-23 17:00:00', '2026-08-14 23:59:59', NULL, 'Giza', '2026-08-14 10:00:00', '2026-09-02 06:30:34'),
(100234, 100153, 110, 'Category Analytics Program', 'A hands_on training offered by GreenRetail Egypt in Giza for 2 students. The program covers the most requested skills in Business Administration and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-08-15 10:00:00', '2026-08-23 09:00:00', '2026-09-24 17:00:00', '2026-08-15 23:59:59', NULL, 'Giza', '2026-08-15 10:00:00', '2026-09-02 06:30:34'),
(100235, 100154, 96, 'Founding Team: Lab Operations Draft', 'A hands_on training offered by MedPulse Diagnostics in Giza for 2 students. The program covers the most requested skills in General Medicine and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 1, 0, NULL, 'EGP', NULL, 2, 'draft', NULL, '2026-08-24 09:00:00', '2026-09-25 17:00:00', '2026-08-16 23:59:59', NULL, 'Giza', '2026-09-30 10:00:00', '2026-09-02 06:30:34'),
(100236, 100156, 110, 'Freight Operations Trainee', 'A hands_on training offered by Nile Valley Logistics in Alexandria for 3 students. The program covers the most requested skills in Business Administration and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 3, 'published', '2026-08-17 10:00:00', '2026-08-25 09:00:00', '2026-09-26 17:00:00', '2026-08-17 23:59:59', NULL, 'Alexandria', '2026-08-17 10:00:00', '2026-09-02 06:30:34'),
(100237, 100155, 92, 'Onshore Wind Site Inspection', 'A hands_on training offered by SolarOffshore Energy in Suez for 2 students. The program covers the most requested skills in Mechanical Engineering and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-08-18 10:00:00', '2026-08-26 09:00:00', '2026-09-27 17:00:00', '2026-08-18 23:59:59', NULL, 'Suez', '2026-08-18 10:00:00', '2026-09-02 06:30:34'),
(100238, 100158, 109, 'Recruitment Sourcer Program', 'A project_based training offered by HR Partners Egypt in Cairo for 3 students. The program covers the most requested skills in Human Resources and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'hybrid', 1, 0, NULL, 'EGP', NULL, 3, 'published', '2026-08-19 10:00:00', '2026-08-27 09:00:00', '2026-09-28 17:00:00', '2026-08-19 23:59:59', NULL, 'Cairo', '2026-08-19 10:00:00', '2026-09-02 06:30:34'),
(100239, 100158, 109, 'Employee Onboarding Design', 'A project_based training offered by HR Partners Egypt in Cairo for 2 students. The program covers the most requested skills in Human Resources and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'project_based', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'published', '2026-08-20 10:00:00', '2026-08-28 09:00:00', '2026-09-29 17:00:00', '2026-08-20 23:59:59', NULL, 'Cairo', '2026-08-20 10:00:00', '2026-09-02 06:30:34'),
(100240, 100157, 121, 'In-House Design Review', 'A hands_on training offered by CleoFashion International in Cairo for 2 students. The program covers the most requested skills in Graphic Design and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.', 'hands_on', 'onsite', 0, 0, NULL, 'EGP', NULL, 2, 'draft', NULL, '2026-08-29 09:00:00', '2026-09-30 17:00:00', '2026-08-21 23:59:59', NULL, 'Cairo', '2026-10-05 10:00:00', '2026-09-02 06:30:34');

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

--
-- Dumping data for table `training_questions`
--

INSERT INTO `training_questions` (`id`, `training_id`, `question`, `question_type`, `required`, `options`, `sort_order`, `created_at`) VALUES
(1635, 100202, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-28 10:00:00'),
(1636, 100202, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-28 10:00:00'),
(1637, 100202, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-28 10:00:00'),
(1638, 100202, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-28 10:00:00'),
(1639, 100202, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-28 10:00:00'),
(1640, 100203, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-15 10:00:00'),
(1641, 100203, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-15 10:00:00'),
(1642, 100203, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-15 10:00:00'),
(1643, 100203, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-15 10:00:00'),
(1644, 100203, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-15 10:00:00'),
(1645, 100204, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-16 10:00:00'),
(1646, 100204, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-16 10:00:00'),
(1647, 100204, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-16 10:00:00'),
(1648, 100204, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-16 10:00:00'),
(1649, 100204, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-16 10:00:00'),
(1650, 100205, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-17 10:00:00'),
(1651, 100205, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-17 10:00:00'),
(1652, 100205, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-17 10:00:00'),
(1653, 100205, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-17 10:00:00'),
(1654, 100205, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-17 10:00:00'),
(1655, 100206, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-18 10:00:00'),
(1656, 100206, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-18 10:00:00'),
(1657, 100206, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-18 10:00:00'),
(1658, 100206, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-18 10:00:00'),
(1659, 100206, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-18 10:00:00'),
(1660, 100207, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-19 10:00:00'),
(1661, 100207, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-19 10:00:00'),
(1662, 100207, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-19 10:00:00'),
(1663, 100207, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-19 10:00:00'),
(1664, 100207, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-19 10:00:00'),
(1665, 100208, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-09-03 10:00:00'),
(1666, 100208, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-09-03 10:00:00'),
(1667, 100208, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-09-03 10:00:00'),
(1668, 100208, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-09-03 10:00:00'),
(1669, 100208, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-09-03 10:00:00'),
(1670, 100209, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-21 10:00:00'),
(1671, 100209, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-21 10:00:00'),
(1672, 100209, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-21 10:00:00'),
(1673, 100209, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-21 10:00:00'),
(1674, 100209, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-21 10:00:00'),
(1675, 100210, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-22 10:00:00'),
(1676, 100210, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-22 10:00:00'),
(1677, 100210, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-22 10:00:00'),
(1678, 100210, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-22 10:00:00'),
(1679, 100210, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-22 10:00:00'),
(1680, 100211, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-23 10:00:00'),
(1681, 100211, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-23 10:00:00'),
(1682, 100211, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-23 10:00:00'),
(1683, 100211, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-23 10:00:00'),
(1684, 100211, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-23 10:00:00'),
(1685, 100212, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-24 10:00:00'),
(1686, 100212, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-24 10:00:00'),
(1687, 100212, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-24 10:00:00'),
(1688, 100212, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-24 10:00:00'),
(1689, 100212, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-24 10:00:00'),
(1690, 100213, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-09-08 10:00:00'),
(1691, 100213, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-09-08 10:00:00'),
(1692, 100213, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-09-08 10:00:00'),
(1693, 100213, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-09-08 10:00:00'),
(1694, 100213, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-09-08 10:00:00'),
(1695, 100214, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-26 10:00:00'),
(1696, 100214, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-26 10:00:00'),
(1697, 100214, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-26 10:00:00'),
(1698, 100214, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-26 10:00:00'),
(1699, 100214, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-26 10:00:00'),
(1700, 100215, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-27 10:00:00'),
(1701, 100215, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-27 10:00:00'),
(1702, 100215, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-27 10:00:00'),
(1703, 100215, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-27 10:00:00'),
(1704, 100215, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-27 10:00:00'),
(1705, 100216, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-09-11 10:00:00'),
(1706, 100216, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-09-11 10:00:00'),
(1707, 100216, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-09-11 10:00:00'),
(1708, 100216, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-09-11 10:00:00'),
(1709, 100216, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-09-11 10:00:00'),
(1710, 100217, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-29 10:00:00'),
(1711, 100217, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-29 10:00:00'),
(1712, 100217, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-29 10:00:00'),
(1713, 100217, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-29 10:00:00'),
(1714, 100217, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-29 10:00:00'),
(1715, 100218, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-30 10:00:00'),
(1716, 100218, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-30 10:00:00'),
(1717, 100218, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-30 10:00:00'),
(1718, 100218, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-30 10:00:00'),
(1719, 100218, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-30 10:00:00'),
(1720, 100219, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-07-31 10:00:00'),
(1721, 100219, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-07-31 10:00:00'),
(1722, 100219, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-07-31 10:00:00'),
(1723, 100219, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-07-31 10:00:00'),
(1724, 100219, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-07-31 10:00:00'),
(1725, 100220, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-09-15 10:00:00'),
(1726, 100220, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-09-15 10:00:00'),
(1727, 100220, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-09-15 10:00:00'),
(1728, 100220, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-09-15 10:00:00'),
(1729, 100220, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-09-15 10:00:00'),
(1730, 100221, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-02 10:00:00'),
(1731, 100221, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-02 10:00:00'),
(1732, 100221, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-02 10:00:00'),
(1733, 100221, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-02 10:00:00'),
(1734, 100221, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-02 10:00:00'),
(1735, 100222, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-03 10:00:00'),
(1736, 100222, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-03 10:00:00'),
(1737, 100222, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-03 10:00:00'),
(1738, 100222, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-03 10:00:00'),
(1739, 100222, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-03 10:00:00'),
(1740, 100223, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-04 10:00:00'),
(1741, 100223, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-04 10:00:00'),
(1742, 100223, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-04 10:00:00'),
(1743, 100223, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-04 10:00:00'),
(1744, 100223, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-04 10:00:00'),
(1745, 100224, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-05 10:00:00'),
(1746, 100224, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-05 10:00:00'),
(1747, 100224, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-05 10:00:00'),
(1748, 100224, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-05 10:00:00'),
(1749, 100224, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-05 10:00:00'),
(1750, 100225, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-06 10:00:00'),
(1751, 100225, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-06 10:00:00'),
(1752, 100225, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-06 10:00:00'),
(1753, 100225, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-06 10:00:00'),
(1754, 100225, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-06 10:00:00'),
(1755, 100226, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-07 10:00:00'),
(1756, 100226, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-07 10:00:00'),
(1757, 100226, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-07 10:00:00'),
(1758, 100226, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-07 10:00:00'),
(1759, 100226, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-07 10:00:00'),
(1760, 100227, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-09-22 10:00:00'),
(1761, 100227, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-09-22 10:00:00'),
(1762, 100227, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-09-22 10:00:00'),
(1763, 100227, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-09-22 10:00:00'),
(1764, 100227, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-09-22 10:00:00'),
(1765, 100228, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-09 10:00:00'),
(1766, 100228, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-09 10:00:00'),
(1767, 100228, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-09 10:00:00'),
(1768, 100228, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-09 10:00:00'),
(1769, 100228, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-09 10:00:00'),
(1770, 100229, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-10 10:00:00'),
(1771, 100229, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-10 10:00:00'),
(1772, 100229, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-10 10:00:00'),
(1773, 100229, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-10 10:00:00'),
(1774, 100229, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-10 10:00:00'),
(1775, 100230, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-11 10:00:00'),
(1776, 100230, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-11 10:00:00'),
(1777, 100230, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-11 10:00:00'),
(1778, 100230, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-11 10:00:00'),
(1779, 100230, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-11 10:00:00'),
(1780, 100231, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-12 10:00:00'),
(1781, 100231, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-12 10:00:00'),
(1782, 100231, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-12 10:00:00'),
(1783, 100231, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-12 10:00:00'),
(1784, 100231, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-12 10:00:00'),
(1785, 100232, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-13 10:00:00'),
(1786, 100232, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-13 10:00:00'),
(1787, 100232, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-13 10:00:00'),
(1788, 100232, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-13 10:00:00'),
(1789, 100232, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-13 10:00:00'),
(1790, 100233, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-14 10:00:00'),
(1791, 100233, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-14 10:00:00'),
(1792, 100233, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-14 10:00:00'),
(1793, 100233, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-14 10:00:00'),
(1794, 100233, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-14 10:00:00'),
(1795, 100234, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-15 10:00:00'),
(1796, 100234, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-15 10:00:00'),
(1797, 100234, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-15 10:00:00'),
(1798, 100234, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-15 10:00:00'),
(1799, 100234, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-15 10:00:00'),
(1800, 100235, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-09-30 10:00:00'),
(1801, 100235, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-09-30 10:00:00'),
(1802, 100235, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-09-30 10:00:00'),
(1803, 100235, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-09-30 10:00:00'),
(1804, 100235, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-09-30 10:00:00'),
(1805, 100236, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-17 10:00:00'),
(1806, 100236, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-17 10:00:00'),
(1807, 100236, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-17 10:00:00'),
(1808, 100236, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-17 10:00:00'),
(1809, 100236, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-17 10:00:00'),
(1810, 100237, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-18 10:00:00'),
(1811, 100237, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-18 10:00:00'),
(1812, 100237, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-18 10:00:00'),
(1813, 100237, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-18 10:00:00'),
(1814, 100237, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-18 10:00:00'),
(1815, 100238, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-19 10:00:00'),
(1816, 100238, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-19 10:00:00'),
(1817, 100238, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-19 10:00:00'),
(1818, 100238, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-19 10:00:00'),
(1819, 100238, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-19 10:00:00'),
(1820, 100239, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-08-20 10:00:00'),
(1821, 100239, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-08-20 10:00:00'),
(1822, 100239, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-08-20 10:00:00'),
(1823, 100239, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-08-20 10:00:00'),
(1824, 100239, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-08-20 10:00:00'),
(1825, 100240, 'Tell us about your most relevant project or experience.', 'textarea', 1, NULL, 1, '2026-10-05 10:00:00'),
(1826, 100240, 'What do you hope to learn during this training?', 'textarea', 1, NULL, 2, '2026-10-05 10:00:00'),
(1827, 100240, 'How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours', 3, '2026-10-05 10:00:00'),
(1828, 100240, 'Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No', 4, '2026-10-05 10:00:00'),
(1829, 100240, 'Describe one challenge you solved with your specialty.', 'textarea', 0, NULL, 5, '2026-10-05 10:00:00');

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

--
-- Dumping data for table `training_sessions`
--

INSERT INTO `training_sessions` (`id`, `application_id`, `training_id`, `student_id`, `company_id`, `status`, `started_at`, `trial_started_at`, `trial_ends_at`, `student_continuation_confirmed_at`, `actual_ended_at`, `employment_opportunity`, `created_at`, `updated_at`) VALUES
(225, 1671, 100202, 1458, 100144, 'completed', '2026-08-08 09:00:00', '2026-08-08 09:00:00', '2026-08-23 18:00:00', '2026-08-11 12:00:00', '2026-08-23 16:00:00', 0, '2026-08-08 09:00:00', '2026-08-08 09:00:00'),
(226, 1675, 100203, 1466, 100144, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(227, 1680, 100204, 1476, 100144, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(228, 1685, 100205, 1486, 100144, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(229, 1690, 100206, 1456, 100144, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(230, 1695, 100207, 1466, 100144, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(231, 1700, 100208, 1476, 100145, 'completed', '2026-08-08 09:00:00', '2026-08-08 09:00:00', '2026-08-23 18:00:00', '2026-08-11 12:00:00', '2026-08-23 16:00:00', 0, '2026-08-08 09:00:00', '2026-08-08 09:00:00'),
(232, 1704, 100209, 1484, 100145, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(233, 1709, 100210, 1454, 100145, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(234, 1714, 100211, 1464, 100145, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(235, 1719, 100212, 1474, 100145, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(236, 1724, 100213, 1484, 100146, 'completed', '2026-08-08 09:00:00', '2026-08-08 09:00:00', '2026-08-23 18:00:00', '2026-08-11 12:00:00', '2026-08-23 16:00:00', 0, '2026-08-08 09:00:00', '2026-08-08 09:00:00'),
(237, 1728, 100214, 1492, 100146, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(238, 1733, 100215, 1462, 100146, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(239, 1738, 100216, 1472, 100147, 'completed', '2026-08-08 09:00:00', '2026-08-08 09:00:00', '2026-08-23 18:00:00', '2026-08-11 12:00:00', '2026-08-23 16:00:00', 0, '2026-08-08 09:00:00', '2026-08-08 09:00:00'),
(240, 1742, 100217, 1480, 100147, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(241, 1747, 100218, 1490, 100147, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(242, 1752, 100219, 1460, 100147, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(243, 1757, 100220, 1470, 100148, 'completed', '2026-08-08 09:00:00', '2026-08-08 09:00:00', '2026-08-23 18:00:00', '2026-08-11 12:00:00', '2026-08-23 16:00:00', 0, '2026-08-08 09:00:00', '2026-08-08 09:00:00'),
(244, 1761, 100221, 1478, 100148, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(245, 1766, 100222, 1488, 100148, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(246, 1771, 100223, 1458, 100149, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(247, 1776, 100224, 1468, 100149, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(248, 1781, 100225, 1478, 100150, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(249, 1786, 100226, 1488, 100150, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(250, 1791, 100227, 1458, 100151, 'completed', '2026-08-08 09:00:00', '2026-08-08 09:00:00', '2026-08-23 18:00:00', '2026-08-11 12:00:00', '2026-08-23 16:00:00', 0, '2026-08-08 09:00:00', '2026-08-08 09:00:00'),
(251, 1795, 100228, 1466, 100151, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(252, 1800, 100229, 1476, 100151, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(253, 1805, 100230, 1486, 100152, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(254, 1810, 100231, 1456, 100152, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(255, 1815, 100232, 1466, 100152, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(256, 1820, 100233, 1476, 100153, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(257, 1825, 100234, 1486, 100153, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(258, 1830, 100236, 1456, 100156, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(259, 1835, 100237, 1466, 100155, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(260, 1840, 100238, 1476, 100158, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00'),
(261, 1845, 100239, 1486, 100158, 'continuing', '2026-08-27 09:00:00', '2026-08-28 09:00:00', '2026-08-23 18:00:00', '2026-08-30 12:00:00', NULL, 0, '2026-08-28 09:00:00', '2026-08-28 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `training_skills`
--
CREATE TABLE `training_skills` (
  `training_id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_skills`
--

INSERT INTO `training_skills` (`training_id`, `skill_id`) VALUES
(100202, 131),
(100204, 131),
(100203, 132),
(100205, 132),
(100206, 132),
(100207, 132),
(100230, 132),
(100205, 133),
(100232, 133),
(100207, 134),
(100208, 134),
(100209, 134),
(100211, 134),
(100212, 134),
(100219, 136),
(100202, 139),
(100204, 139),
(100203, 141),
(100205, 141),
(100206, 141),
(100230, 141),
(100232, 141),
(100205, 145),
(100206, 146),
(100203, 148),
(100232, 148),
(100203, 149),
(100202, 150),
(100203, 150),
(100206, 150),
(100230, 150),
(100202, 152),
(100204, 152),
(100232, 152),
(100204, 155),
(100202, 156),
(100211, 156),
(100230, 156),
(100232, 156),
(100204, 158),
(100207, 158),
(100212, 158),
(100231, 158),
(100231, 159),
(100212, 160),
(100231, 160),
(100231, 161),
(100208, 164),
(100209, 164),
(100211, 164),
(100222, 164),
(100224, 164),
(100234, 164),
(100210, 165),
(100208, 166),
(100212, 166),
(100209, 167),
(100211, 167),
(100209, 168),
(100205, 173),
(100206, 173),
(100221, 174),
(100240, 174),
(100240, 175),
(100216, 176),
(100225, 177),
(100227, 177),
(100228, 177),
(100239, 177),
(100220, 178),
(100220, 179),
(100222, 179),
(100220, 180),
(100221, 180),
(100213, 181),
(100214, 181),
(100215, 181),
(100218, 181),
(100223, 181),
(100225, 181),
(100226, 181),
(100227, 181),
(100229, 181),
(100233, 181),
(100235, 181),
(100236, 181),
(100238, 181),
(100239, 181),
(100214, 182),
(100236, 182),
(100218, 183),
(100226, 183),
(100213, 184),
(100215, 184),
(100223, 184),
(100229, 184),
(100238, 184),
(100233, 185),
(100239, 185),
(100213, 186),
(100214, 186),
(100215, 186),
(100223, 186),
(100224, 186),
(100225, 186),
(100226, 186),
(100235, 186),
(100208, 479),
(100210, 480),
(100216, 480),
(100222, 480),
(100224, 480),
(100227, 480),
(100228, 480),
(100229, 480),
(100233, 480),
(100234, 480),
(100236, 480),
(100237, 480),
(100238, 480),
(100210, 481),
(100227, 481),
(100228, 481),
(100234, 481),
(100230, 486),
(100212, 487),
(100231, 487),
(100207, 488),
(100216, 491),
(100217, 491),
(100218, 491),
(100219, 491),
(100217, 492),
(100237, 492),
(100217, 493),
(100219, 493),
(100237, 493),
(100208, 494),
(100210, 494),
(100221, 495),
(100220, 498),
(100222, 498);

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
(100217, 92),
(100237, 92),
(100216, 93),
(100218, 93),
(100219, 94),
(100213, 96),
(100235, 96),
(100215, 97),
(100214, 98),
(100223, 100),
(100224, 102),
(100207, 103),
(100211, 104),
(100220, 108),
(100238, 109),
(100239, 109),
(100233, 110),
(100234, 110),
(100236, 110),
(100225, 112),
(100226, 114),
(100221, 118),
(100222, 118),
(100240, 121),
(100228, 122),
(100229, 122),
(100227, 124),
(100202, 199),
(100204, 199),
(100205, 200),
(100206, 200),
(100203, 201),
(100230, 201),
(100232, 201),
(100231, 203),
(100208, 205),
(100210, 205),
(100209, 206),
(100212, 206);

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

--
-- Dumping data for table `universities`
--

INSERT INTO `universities` (`id`, `name`, `city`, `is_active`, `created_at`, `updated_at`) VALUES
(51, 'Cairo University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(52, 'Ain Shams University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(53, 'Alexandria University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(54, 'Mansoura University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(55, 'Assiut University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(56, 'Tanta University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(57, 'Zagazig University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(58, 'Suez Canal University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(59, 'Helwan University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(60, 'Fayoum University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(61, 'Minia University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(62, 'Sohag University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(63, 'Benha University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(64, 'Kafr El Sheikh University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(65, 'Port Said University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(66, 'Damietta University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(67, 'Damanhour University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(68, 'Aswan University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(69, 'Luxor University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(70, 'New Valley University', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(71, 'Arab Academy for Science, Technology and Maritime Transport', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(72, 'The American University in Cairo', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(73, 'German University in Cairo', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(74, 'British University in Egypt', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(75, 'Future University in Egypt', NULL, 1, '2026-08-30 00:59:43', '2026-08-30 00:59:43'),
(201, 'Egypt-Japan University of Science and Technology', 'Alexandria', 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51'),
(202, 'Pharos University in Alexandria', 'Alexandria', 1, '2026-09-02 03:16:51', '2026-09-02 03:16:51');

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
(100526, 'admin', 'admin@masar.eg', '$2y$12$Oz7mJ2BpVTC7vVkOP2kCBeh/kV4dCWM6pnZLwHoAZ7G7LaaVDmP1i', 'active', '2026-08-03 10:00:00', '2026-09-02 04:18:05', '2026-07-19 10:00:00', '2026-09-02 04:18:05', NULL),
(100527, 'admin', 'compliance@masar.eg', '$2y$12$h5J0KJf0wg4pHigxURCrE.CYS2/9lnlNu5Ql/xW7Xj.hHYKwPggAm', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100528, 'student', 'omar.shazly@gmail.com', '$2y$12$ngzkGbxsTfzSRPtv/b15MepKA9vqEmYrKSjto/aCADLkg95iWnxvm', 'active', '2026-08-03 10:00:00', '2026-09-02 06:02:39', '2026-07-19 10:00:00', '2026-09-02 06:02:39', NULL),
(100529, 'student', 'mariam.hassan@gmail.com', '$2y$12$B6J3OPTU0GVacs3UXKErdOTL29/ElZ4FFRTp09MSMc5pJJBCNIgvC', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100530, 'student', 'youssef.farouk@outlook.com', '$2y$12$XTa5LugEt3hw94chFcXLa.Hd/cLBthKE6kbVypZXc0zsQ2A024e/2', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100531, 'student', 'farida.lotfy@gmail.com', '$2y$12$qbmgce0kl329HsGgk7Dth.T1qyo8R4Ax8wxRgHpXdKZS5t0aQoHVC', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100532, 'student', 'ahmed.nabil@gmail.com', '$2y$12$41ObvngYglSvnVChRfjwd.wgSdYTU3OacNyfdMTAOLpZD5p3ekfqm', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100533, 'student', 'nour.abdelaziz@gmail.com', '$2y$12$oO7jyhgsB7I/mbqXlrCIZe2CFqztXFnbRHXHQI/.ey6sL/q1Wu9Ha', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100534, 'student', 'karim.eldeeb@gmail.com', '$2y$12$AQN1.2W7r/aHywnl4kr6xe.H4fvc445dErImrHnwZzeLpRrLYpfnW', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100535, 'student', 'salma.eldin@outlook.com', '$2y$12$HITuv6qJNTzEYQ5Ai3hLr.BnuN1leieIh9jb11hlFzkOtwW9EavMW', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100536, 'student', 'mahmoud.gaballah@gmail.com', '$2y$12$n8kkTCNuR2.qkn9S9csgE.f/wU6PC7YE/odY87ziVPmG6hcvzt35.', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100537, 'student', 'mohamed.khattab@gmail.com', '$2y$12$yyC1Czumo4syEQdW9rzpZ.0VTo/3G9aM6HVnbri0BQRAeQrFqaG6m', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100538, 'student', 'sara.fahim@gmail.com', '$2y$12$qwpERQcy4DZlSU3AqQJs7Ohns2m4t7pr7IDGroudfYqg2zITrKmfS', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100539, 'student', 'amr.abdulghany@gmail.com', '$2y$12$ROO6vpnTMJJCv29H9NGQJOQaxNDoUs9Onoxmx3zz7s5Npwk4JmUIa', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100540, 'student', 'menna.nassar@gmail.com', '$2y$12$RbF7zClYVm0jI535NcF53eW00h80XRJb9So0p/gbBN/.Q6sQYpICC', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100541, 'student', 'mohamed.fathy@outlook.com', '$2y$12$ulhBKxhlKpu0EvpAXUdVSOnj5ahAt7eZMJ4uYnNGnLZcFzxudgTFC', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100542, 'student', 'youssef.shawky@gmail.com', '$2y$12$coZ/1m4asPZrJQQSOCq5weVivo36tZ/abZyNyDszNMc9eovMB1DZO', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100543, 'student', 'omar.mostafa@gmail.com', '$2y$12$NdZGS5QLOoYgMFgwjB9AbuSdYiFXlXs2.hVk5kLY8kiypYOSGnoMW', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100544, 'student', 'malak.shennawy@gmail.com', '$2y$12$9pFE7AlHNqpZmCs1LPq/huOHBnapXIYvb7qADe/bWO3m50SEVqMAW', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100545, 'student', 'abdallah.kamel@gmail.com', '$2y$12$AQBk32WUaCA2eEqpvAfqkOteFdVP.OCUTxHyq6Fsm99pEzK5esyEi', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100546, 'student', 'salma.maghraby@gmail.com', '$2y$12$ShJx7WcAmmIUdWexsPNJ2uvLPnMCq7aajaeiu819vllK2Wm2dbTua', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100547, 'student', 'yara.hassan@gmail.com', '$2y$12$BfdkymoQ073/saitNh6xResaWTenSaIsE2tYxYHSEBmgWlt9iIrpe', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100548, 'student', 'hana.ezzat@gmail.com', '$2y$12$Jd99b6lsxzeGvlkHZHVOCepeTF1UfeLFtkZ6I58TV2K.NFqQURM0i', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100549, 'student', 'omar.soliman@gmail.com', '$2y$12$EjuQdREX/bHFNOvaYPPXQuxHjSXQky/cCScGaI6EvyV7RDA2FEt1C', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100550, 'student', 'youssef.gohary@gmail.com', '$2y$12$0uEqd2.IH/eBh8iaaiBI9ewBDaPAjbtcQfvTPz5iH1ZJ2tRKJmB7G', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100551, 'student', 'alaa.fawzy@gmail.com', '$2y$12$Sk3S3O9FwKVqPgNmBIuPiewVbUx2Y8Ek39SetqT3CICBdRAVx74DS', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100552, 'student', 'menna.ibrahim@gmail.com', '$2y$12$k.zHKblGdSpniWwU3vDn0u9Vc59Rpz1KhWont4Flfl4sNzBh6dkje', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100553, 'student', 'omar.shahin@gmail.com', '$2y$12$6q3uSR1RGLxbpe.760O2ZuC1XbyDfiIzBtAkhG5BsIKZpYlMTSc/W', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100554, 'student', 'nourhan.abdelsalam@gmail.com', '$2y$12$NW/7C/AGzQ803a.GAv3u9uS6d6bnGQPwo9Cr5uo2zYCq.YMNJSzH6', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100555, 'student', 'aya.feky@gmail.com', '$2y$12$lCn8GlSODpN3eUjmCedyeejCFFNBgRxRe5PwNjcpf9vsLPUsggqxS', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100556, 'student', 'mariam.lotfy@gmail.com', '$2y$12$1Xtqv237DWBCkiTuu0ZQo.io7Fy0T2L8BcPbFiR8ms2p0g4u7B8rO', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100557, 'student', 'hager.anwar@gmail.com', '$2y$12$FPmfZZotUa8wKKodvQ2iv.FNUUlTblYxDTHuuIgr.gjuERVyn2JKq', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100558, 'student', 'ahmed.seif@outlook.com', '$2y$12$nRZ/VrqwJwEaG5JhCjIlf.tpImQHrfx3sjIOi7vtrJxD4woFKyKYq', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100559, 'student', 'esraa.badrawi@gmail.com', '$2y$12$2Mjvl10clGfr2kIM324IjOv7Qgp9M2.0E3j8kE4PbjczvfDt0vNwW', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100560, 'student', 'george.saad@gmail.com', '$2y$12$fCzOxDpDjYvyp2RWw8dQJu0bxRk4UykHOLsPWBX9kNTRVF31W67Zy', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100561, 'student', 'salma.eldin2@gmail.com', '$2y$12$sq5V5N.uZFq8wOp6Xdjys.NRRY9TYn1W1RMsad5l0ywdE3HRZUGza', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100562, 'student', 'mariam.tarek@gmail.com', '$2y$12$5ustyobxP4KlzgULJ5a.AOVf9X6PUGYUlmqtD.T8NRexxs8pbksya', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100563, 'student', 'mohamed.hadidi@outlook.com', '$2y$12$kXuZLHfHlANgxjNOJvZ.XeOCpEv2lRnVqvwP/wKcao2p3.rDLErk.', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100564, 'student', 'yasmin.talaat@gmail.com', '$2y$12$gCmRYftUgrhRxUV18ursmeztxM0NZpW0eP9Q.80AbRq0koVR.3CUO', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100565, 'student', 'abdullah.rahman@gmail.com', '$2y$12$zTor5.xY/tXrBD9x76AEg.Qhw7hcuRY1I3CyOcEuJg8.OVkloNaqK', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100566, 'student', 'rania.fouad@gmail.com', '$2y$12$gc2dnSa3PNX8RbPZn5Kr4.sIwPnWljXOeSWElXVNsyVylKo7Z6hWS', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100567, 'student', 'george.farid@gmail.com', '$2y$12$ZiW3HiOQ18sFOkzeGnQCje83egb5N2YimLuyYeyBHPX9Dyt4...Lq', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100568, 'company', 'careers@niletech.eg', '$2y$12$93gtWms7JWjbtBw2t6E4fe8JFgD9yYccyEvhTkQjcHE71534QetAW', 'active', '2026-08-03 10:00:00', '2026-09-02 06:03:53', '2026-07-19 10:00:00', '2026-09-02 06:03:53', NULL),
(100569, 'company', 'careers@alexdilabs.com', '$2y$12$xiAbCvcoHRfmovZqdETI1.eRozXDxQe1J0IX3U8Fy15YGPByKTTXC', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100570, 'company', 'hr@cairomed.eg', '$2y$12$7wiHmmr1T9mhiujMLv5Oh.j.h7hbe3GKTKQb.24qsgP5krLIfVkXq', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100571, 'company', 'info@atlaseng.eg', '$2y$12$QOddQaxgpCt.hWfUuoaHauJElSGW39KBxxzS9sBXI9s7Iloqohy7C', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100572, 'company', 'careers@brightreach.eg', '$2y$12$h.6JmnlV.QwJC1oct2TR1.PzWpTLUcpHDt0FZ5Zq2eLJGsVg/ln82', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100573, 'company', 'hr@luxorpharma.com', '$2y$12$VOtJ7CNSL2mtDdEOZGww5OzMSwOt8TsyHL0/ETnhSVswZVqA6MMea', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100574, 'company', 'mail@themis-law.com', '$2y$12$lB6pcrSZ.adbZqx0iu7nje1PfxKDeDk8..ywQ2Qoi62iAz1bD22aS', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100575, 'company', 'hr@ledgerpro.eg', '$2y$12$x2ta2o2UJ77gNmOQ2xEp2.t6NyHyIi2ngABaMP6mLNXA7fgKb2jYu', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100576, 'company', 'talent@futureworks.io', '$2y$12$Z3H7Om8KOgW4lSVcYvlmpubDajk.5INZD1LOR3XPxb87cCop73QGK', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100577, 'company', 'hr@greenretail.eg', '$2y$12$P4Ea16cEVG4D9AJkPqhiNesKxkDyE2FCGsr7lCEvUhG5SqDd4vpX2', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100578, 'company', 'careers@medpulse-dx.com', '$2y$12$btmvx2fy3kn9Vj.j.jwlle7Gvh.HKJLqmevKBlOgdsKtvm5TzDVXq', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100579, 'company', 'hr@solaroffshore.eg', '$2y$12$g8QauObDtJK9XQ/pK.vnruGlqujFfRP.pPTaQfE4fUQAAjWyM4isC', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100580, 'company', 'careers@nilevalley-log.com', '$2y$12$OkQoJqfifBkQaMLakrrNOuYiOE8THmeCr5ylmqnz7I6RTjuhX8o0S', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100581, 'company', 'jobs@cleofashion.com', '$2y$12$5k9UVJCUs.r07NEJcvYgGuDq1aVmUKm3tB8GT6J6NP25QIAMaZUfC', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100582, 'company', 'hr@hrpartners.eg', '$2y$12$f8b9LxPiON112fqq/OzOi.Yz7qBls20Rbg1mWNYJYdP6RGBfSWqKS', 'active', '2026-08-03 10:00:00', '2026-09-01 10:00:00', '2026-07-19 10:00:00', '2026-07-19 10:00:00', NULL),
(100588, 'student', 'mammuslim2003@gmail.com', '$2y$12$zRtZATQwPlzfYnSKuUKAl.PM7aRG6gTAb8eowQ03XKW1aqiGjYPHu', 'active', '2026-09-02 05:23:46', '2026-09-04 04:54:36', '2026-09-02 05:23:43', '2026-09-04 04:54:36', NULL);

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
(103, 100588, '07883e5cebfdc1d0d019e317b5a84b54b6349ecca54128420c7a4788a3e6aae0', '2026-09-02 05:23:43', NULL, '2026-09-02 05:23:43');

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
  ADD KEY `idx_training_status` (`status`),
  ADD KEY `idx_training_type` (`training_type`),
  ADD KEY `idx_training_mode` (`mode`),
  ADD KEY `idx_training_ends_at` (`ends_at`),
  ADD KEY `idx_training_status_dates` (`status`,`starts_at`,`ends_at`),
  ADD KEY `fk_training_listings_company` (`company_id`),
  ADD KEY `idx_training_listings_specialization` (`specialization_id`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6499;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1496;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `certificate_appeals`
--
ALTER TABLE `certificate_appeals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100160;

--
-- AUTO_INCREMENT for table `company_work_fields`
--
ALTER TABLE `company_work_fields`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `degrees`
--
ALTER TABLE `degrees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=467;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=708;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=314;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1289;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1905;

--
-- AUTO_INCREMENT for table `oauth_states`
--
ALTER TABLE `oauth_states`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1060;

--
-- AUTO_INCREMENT for table `revoked_access_tokens`
--
ALTER TABLE `revoked_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `saved_trainings`
--
ALTER TABLE `saved_trainings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=541;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1428;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=735;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1503;

--
-- AUTO_INCREMENT for table `study_fields`
--
ALTER TABLE `study_fields`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `training_applications`
--
ALTER TABLE `training_applications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1848;

--
-- AUTO_INCREMENT for table `training_listings`
--
ALTER TABLE `training_listings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100244;

--
-- AUTO_INCREMENT for table `training_questions`
--
ALTER TABLE `training_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1830;

--
-- AUTO_INCREMENT for table `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `universities`
--
ALTER TABLE `universities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=603;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100594;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

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
  ADD CONSTRAINT `fk_training_listings_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_training_listings_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `training_questions`
--
ALTER TABLE `training_questions`
  ADD CONSTRAINT `fk_training_questions_training` FOREIGN KEY (`training_id`) REFERENCES `training_listings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
