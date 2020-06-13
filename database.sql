-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 30, 2020 at 07:14 PM
-- Server version: 10.3.22-MariaDB-log-cll-lve
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cp39899_bot2`
--

-- --------------------------------------------------------

--
-- Table structure for table `Authentication`
--

CREATE TABLE `Authentication` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL,
  `birthdate` varchar(255) CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL,
  `PersonalID` varchar(255) CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL,
  `EvidenceImage` text CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL,
  `CardNumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL,
  `CardImage` text CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL,
  `MobileNumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL,
  `HomeNumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `Authentication`
--

INSERT INTO `Authentication` (`id`, `user_id`, `name`, `birthdate`, `PersonalID`, `EvidenceImage`, `CardNumber`, `CardImage`, `MobileNumber`, `HomeNumber`) VALUES
(23, 1006555624, 'رضا شیرازی', '1377/03/13', '1234567890', 'https://rd7.ir/bot/upload/1006555624_1590801807.jpg', '6037997312345678', 'https://rd7.ir/bot/upload/1006555624_1590801816.jpg', '121212', '12121');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `telegram_id` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL DEFAULT 'home',
  `authentication_status` tinyint(1) NOT NULL DEFAULT 0,
  `register_date` bigint(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `user_id`, `first_name`, `last_name`, `telegram_id`, `location`, `authentication_status`, `register_date`) VALUES
(12, 1006555624, 'Larateam', '', 'larateam', 'currency', 1, 1590687617),
(5, 1195809450, 'Amir', '', 'a_s_l719', 'profile', 0, 1590586749),
(6, 522426147, '.-. . --.. .-', '', 'itreza7', 'home', 0, 1590587104),
(7, 772022564, 'Mohammad', '', '', 'currency', 0, 1590587313),
(8, 397560480, 'H_saberi', '', 'H_SB97', 'currency', 0, 1590605864),
(14, 0, '', '', '', 'home', 0, 1590801105);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Authentication`
--
ALTER TABLE `Authentication`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Authentication`
--
ALTER TABLE `Authentication`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
