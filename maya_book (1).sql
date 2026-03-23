-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2026 at 11:51 AM
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
-- Database: `maya_book`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'manoj', 'manoj123'),
(2, 'admin', 'manoj1234');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `event_id` int(11) DEFAULT NULL,
  `seat_no` int(11) DEFAULT NULL,
  `booking_ref` varchar(20) DEFAULT NULL,
  `is_present` tinyint(1) DEFAULT 0,
  `rating` int(1) DEFAULT 0,
  `booking_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `student_id`, `status`, `event_id`, `seat_no`, `booking_ref`, `is_present`, `rating`, `booking_time`) VALUES
(46, 4, 'approved', 3, 2, 'MAYA-3014', 1, 0, '2026-03-19 11:00:23'),
(49, 4, 'rejected', 4, 2, 'MAYA-0457', 0, 0, '2026-03-19 11:00:23'),
(53, 1, 'approved', 4, 1, 'MAYA-2174', 1, 4, '2026-03-20 05:27:36'),
(55, 1, 'approved', 3, 0, 'MAYA-7891', 1, 4, '2026-03-20 13:00:50');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(200) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_seats` int(11) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `venue` varchar(100) DEFAULT 'Main Auditorium',
  `booking_deadline` date DEFAULT NULL,
  `booking_time` time DEFAULT '23:59:00',
  `approval_required` tinyint(1) DEFAULT 1,
  `has_certificate` tinyint(1) DEFAULT 0,
  `certificate_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `department`, `description`, `total_seats`, `event_date`, `venue`, `booking_deadline`, `booking_time`, `approval_required`, `has_certificate`, `certificate_content`) VALUES
(3, 'ai', 'BCA', 'about', 2, '2026-03-20', 'Main Auditorium', '2026-03-20', '23:59:00', 1, 1, 'This certifies successful participation in the AI Innovation Workshop organized by [Organizer/Institution Name] on [Date/Event Period].\r\n\r\nThe participant demonstrated keen interest and active engagement in artificial intelligence concepts and hands-on applications.\r\n\r\nIssued on [Date of Issuance].'),
(4, 'boss', 'BCA,BBA', 'boss', 2, '2026-03-20', 'Main Auditorium', '2026-03-20', '20:00:00', 0, 0, NULL),
(5, 'sond', 'BCA', 'disk', 5, '2026-03-21', 'Main Auditorium', '2026-03-21', '14:59:00', 1, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `question_text` text DEFAULT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_option` char(1) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 0,
  `activated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `event_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `is_active`, `activated_at`) VALUES
(1, 3, 'wt is ai', 'q', 'w', 'w', 'e', 'A', 0, '2026-03-19 16:00:24'),
(2, 3, 'nduashkd', 'mndyaiudjdd', 'ddd', 'dd', 'dd', 'A', 0, '2026-03-19 16:00:36');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_responses`
--

CREATE TABLE `quiz_responses` (
  `id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `selected_option` char(1) DEFAULT NULL,
  `is_correct` tinyint(4) DEFAULT NULL,
  `response_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_responses`
--

INSERT INTO `quiz_responses` (`id`, `question_id`, `student_id`, `selected_option`, `is_correct`, `response_time`, `submitted_at`) VALUES
(1, 1, 1, 'A', 1, '2026-03-19 15:11:14', '2026-03-19 15:57:09'),
(2, 2, 1, 'A', 1, '2026-03-19 15:11:45', '2026-03-19 15:57:09'),
(3, 0, 1, '', 1, '2026-03-19 15:29:51', '2026-03-19 15:57:09');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `reg_no` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`, `reg_no`, `department`, `password`, `status`, `reset_token`, `token_expiry`) VALUES
(1, 'Manoj s', 'manojsm692@gmail.com', 'U03ZX24S0111', 'BCA', '$2y$10$enF3sC4FulNI1q3cB84nRuXx1GFpainZg1cH1H/S5Rvr.BKVGtwxi', 'approved', NULL, NULL),
(2, 'sai', 'sai@gmail.com', 'U03ZX24S0152', 'BBA', '$2y$10$EWtvepFDnNe5ixU2BArHo.EEb6hablyDEYs92xSavMxLCAH3Qray2', 'approved', NULL, NULL),
(3, 'pra', 'pra@gmail.com', 'U03ZX24S0153', 'BBA', '$2y$10$KmLb22LFE.bh.40gEQjfMejE6yHRrww2XnAXlrr2Log3lVx5HcIeq', 'approved', NULL, NULL),
(4, 'sowndarya', 'sow@gmail.com', 'U03ZX24S0201', 'BCA', '$2y$10$3ejZInuVabWC94WlpIrCIeVGFa8NQQwzPH1ZwT41HNGG.dkwUE8wC', 'approved', NULL, NULL),
(6, 'ravi', 'ravi@gmail.com', 'U03ZX24S0211', 'BCA', '$2y$10$bh98KlMoF3bPrC9bEfyiDeQbge1NvILxjZCC6O78zFvYlAluuowMa', 'approved', NULL, NULL),
(8, 'manu', 'manojsm6952@gmail.com', 'U03ZX24S0222', 'BCA', '$2y$10$t6zxczu/7qzsHSmmJnunLO4errwcwbPAxOBW9PTyjvPD6wqXRBini', 'approved', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

CREATE TABLE `waitlist` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking` (`student_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `waitlist`
--
ALTER TABLE `waitlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD CONSTRAINT `waitlist_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `waitlist_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
