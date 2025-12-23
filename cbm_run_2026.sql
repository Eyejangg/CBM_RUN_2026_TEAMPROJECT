-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2025 at 08:04 AM
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
-- Database: `cbm_run_2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `age_group`
--

CREATE TABLE `age_group` (
  `group_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `min_age` int(11) DEFAULT NULL,
  `max_age` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `reg_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_time` datetime DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('Success','Failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price_rate`
--

CREATE TABLE `price_rate` (
  `price_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `runner_type` varchar(50) NOT NULL COMMENT 'Standard, Senior 70+, Disabled',
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `race_category`
--

CREATE TABLE `race_category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Marathon, Half, Mini',
  `distance_km` float NOT NULL,
  `start_time` time DEFAULT NULL,
  `time_limit` time DEFAULT NULL,
  `giveaway_type` varchar(50) DEFAULT NULL COMMENT 'Vest Type'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `reg_id` int(11) NOT NULL,
  `runner_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price_id` int(11) NOT NULL,
  `shipping_id` int(11) NOT NULL,
  `reg_date` date NOT NULL,
  `shirt_size` varchar(10) DEFAULT NULL,
  `bib_number` varchar(20) DEFAULT NULL,
  `status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `runner`
--

CREATE TABLE `runner` (
  `runner_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `citizen_id` varchar(13) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT 0 COMMENT 'สถานะผู้พิการ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_option`
--

CREATE TABLE `shipping_option` (
  `shipping_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'EMS, Pickup',
  `cost` decimal(10,2) DEFAULT 0.00,
  `detail` varchar(255) DEFAULT NULL COMMENT 'Address or Pickup Date'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `age_group`
--
ALTER TABLE `age_group`
  ADD PRIMARY KEY (`group_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `reg_id` (`reg_id`);

--
-- Indexes for table `price_rate`
--
ALTER TABLE `price_rate`
  ADD PRIMARY KEY (`price_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `race_category`
--
ALTER TABLE `race_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`reg_id`),
  ADD KEY `runner_id` (`runner_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `price_id` (`price_id`),
  ADD KEY `shipping_id` (`shipping_id`);

--
-- Indexes for table `runner`
--
ALTER TABLE `runner`
  ADD PRIMARY KEY (`runner_id`);

--
-- Indexes for table `shipping_option`
--
ALTER TABLE `shipping_option`
  ADD PRIMARY KEY (`shipping_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `age_group`
--
ALTER TABLE `age_group`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `price_rate`
--
ALTER TABLE `price_rate`
  MODIFY `price_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `race_category`
--
ALTER TABLE `race_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `reg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `runner`
--
ALTER TABLE `runner`
  MODIFY `runner_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_option`
--
ALTER TABLE `shipping_option`
  MODIFY `shipping_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `age_group`
--
ALTER TABLE `age_group`
  ADD CONSTRAINT `age_group_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `race_category` (`category_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`reg_id`) REFERENCES `registration` (`reg_id`);

--
-- Constraints for table `price_rate`
--
ALTER TABLE `price_rate`
  ADD CONSTRAINT `price_rate_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `race_category` (`category_id`);

--
-- Constraints for table `registration`
--
ALTER TABLE `registration`
  ADD CONSTRAINT `registration_ibfk_1` FOREIGN KEY (`runner_id`) REFERENCES `runner` (`runner_id`),
  ADD CONSTRAINT `registration_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `race_category` (`category_id`),
  ADD CONSTRAINT `registration_ibfk_3` FOREIGN KEY (`price_id`) REFERENCES `price_rate` (`price_id`),
  ADD CONSTRAINT `registration_ibfk_4` FOREIGN KEY (`shipping_id`) REFERENCES `shipping_option` (`shipping_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
