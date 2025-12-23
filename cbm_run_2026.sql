-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2025 at 09:37 AM
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

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `reg_id`, `total_amount`, `payment_time`, `payment_method`, `status`) VALUES
(1, 1, 1250.00, '2025-12-23 14:45:56', 'Credit Card', 'Success'),
(2, 2, 900.00, '2025-12-23 14:45:56', 'Credit Card', 'Success'),
(3, 3, 650.00, '2025-12-23 14:45:56', 'Credit Card', 'Failed'),
(4, 4, 950.00, '2025-12-23 14:45:56', 'Credit Card', 'Success'),
(6, 7, 650.00, '2025-12-23 14:49:54', 'Cash', 'Failed'),
(7, 8, 1250.00, '2025-12-23 15:13:54', 'Cash', 'Failed'),
(8, 9, 1250.00, '2025-12-23 15:19:43', 'Cash', 'Failed');

-- --------------------------------------------------------

--
-- Table structure for table `price_rate`
--

CREATE TABLE `price_rate` (
  `price_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `runner_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `price_rate`
--

INSERT INTO `price_rate` (`price_id`, `category_id`, `runner_type`, `amount`) VALUES
(1, 1, 'Standard', 1200.00),
(2, 1, 'Senior 70+', 600.00),
(3, 2, 'Standard', 900.00),
(4, 2, 'Senior 70+', 450.00),
(5, 3, 'Standard', 600.00),
(6, 3, 'Senior 70+', 300.00);

-- --------------------------------------------------------

--
-- Table structure for table `race_category`
--

CREATE TABLE `race_category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `distance_km` float NOT NULL,
  `start_time` time DEFAULT NULL,
  `time_limit` time DEFAULT NULL,
  `giveaway_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `race_category`
--

INSERT INTO `race_category` (`category_id`, `name`, `distance_km`, `start_time`, `time_limit`, `giveaway_type`) VALUES
(1, 'Marathon', 42.195, '03:00:00', '07:00:00', 'Finisher Tee'),
(2, 'Half Marathon', 21.1, '04:00:00', '03:30:00', 'Finisher Tee'),
(3, 'Mini Marathon', 10.5, '05:00:00', '02:00:00', 'None');

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

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`reg_id`, `runner_id`, `category_id`, `price_id`, `shipping_id`, `reg_date`, `shirt_size`, `bib_number`, `status`) VALUES
(1, 1, 1, 1, 1, '2025-12-23', 'L', '8343', 'Paid'),
(2, 2, 2, 3, 2, '2025-12-23', 'L', '1533', 'Paid'),
(3, 3, 1, 1, 1, '2025-12-23', 'L', '6307', 'Pending'),
(4, 4, 2, 3, 1, '2025-12-23', 'L', '8243', 'Paid'),
(6, 6, 1, 1, 1, '2025-12-23', 'L', '1519', 'Cancelled'),
(7, 7, 3, 5, 1, '2025-12-23', NULL, '5992', 'Pending'),
(8, 8, 1, 1, 1, '2025-12-23', NULL, '6961', 'Pending'),
(9, 9, 1, 1, 1, '2025-12-23', NULL, '8208', 'Pending');

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

--
-- Dumping data for table `runner`
--

INSERT INTO `runner` (`runner_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `citizen_id`, `phone`, `email`, `address`, `disabled`) VALUES
(1, 'Somsak', 'Jaidee', '1985-05-20', 'Male', '1100000000001', '0812345678', 'somsak@email.com', 'Bangkok', 0),
(2, 'Manee', 'Meela', '1990-11-15', 'Female', '1200000000002', '0898765432', 'manee@email.com', 'Chiang Mai', 0),
(3, 'Piti', 'Rakchart', '1975-03-10', 'Male', '1300000000003', '0865432109', 'piti@email.com', 'Phuket', 0),
(4, 'Chujai', 'Yindee', '1995-08-25', 'Female', '1400000000004', '0876543210', 'chujai@email.com', 'Khon Kaen', 0),
(6, 'Apassara', 'Hongsakul', '1988-02-14', 'Female', '1600000000006', '0811111111', 'apassara@email.com', 'Pattaya', 0),
(7, 'Phitharawat', 'Ketmanee', '2025-12-23', 'Male', '9337621809127', '', '', '', 0),
(8, 'boom', 'za', '2025-12-23', 'Male', '1053864384015', '6541556', 'boomza@gmail.com', '', 0),
(9, 'Phitharawat', 'Ketmanee', '2025-12-23', 'Male', '3301520611209', '1', '11@gmail.com', '1', 0);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_option`
--

CREATE TABLE `shipping_option` (
  `shipping_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `detail` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_option`
--

INSERT INTO `shipping_option` (`shipping_id`, `type`, `cost`, `detail`) VALUES
(1, 'EMS', 50.00, 'Deliver to address'),
(2, 'Pickup', 0.00, 'Pick up at Expo');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`, `created_at`) VALUES
(1, 'admin', '$2y$10$d9RnI8n5uge8jty00P7hHuNI34PhW.QNL3VbgBYFirUbAFZY21ex6', 'Admin', 'User', '2025-12-23 07:45:56'),
(2, 'a11', '$2y$10$pe2eBeU7OIol6HoEWl2I2.D01tSAze01EOZ5dk/y0k26L1RnA0ZiC', 'Phitharawat', 'Ketmanee', '2025-12-23 07:46:45'),
(3, 'fluk9', '$2y$10$DxHoNI6cBRtB4570xB7lpeNt54YLZ03zzI/zIXNOfHnPufwvvYmy.', 'Fluk', 'za', '2025-12-23 08:12:46');

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `price_rate`
--
ALTER TABLE `price_rate`
  MODIFY `price_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `race_category`
--
ALTER TABLE `race_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `reg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `runner`
--
ALTER TABLE `runner`
  MODIFY `runner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `shipping_option`
--
ALTER TABLE `shipping_option`
  MODIFY `shipping_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`reg_id`) REFERENCES `registration` (`reg_id`) ON DELETE CASCADE;

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
