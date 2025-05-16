-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2024 at 02:05 AM
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
-- Database: `db_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `dentists`
--

CREATE TABLE `dentists` (
  `dentist_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentists`
--

INSERT INTO `dentists` (`dentist_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `email`, `mobile`) VALUES
(1, 9, 'Charmaine', 'P.', 'Zapata', 'drazapata@gmail.com', '+639151381175');

-- --------------------------------------------------------

--
-- Table structure for table `receptionists`
--

CREATE TABLE `receptionists` (
  `receptionist_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receptionists`
--

INSERT INTO `receptionists` (`receptionist_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `email`, `mobile`) VALUES
(0, 8, 'Test4', '', 'Huhu', 'test4@gmail.com', '+639151381175');

-- --------------------------------------------------------

--
-- Table structure for table `reminder_template`
--

CREATE TABLE `reminder_template` (
  `id` int(11) NOT NULL,
  `title` varchar(45) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `ID` int(11) NOT NULL,
  `RoleType` enum('ADMIN','DENTIST','CLINIC RECEPTIONIST') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `treatment`
--

CREATE TABLE `treatment` (
  `id` int(11) NOT NULL,
  `treatment_name` varchar(100) DEFAULT NULL,
  `treatment_description` varchar(255) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `time_consumed` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatment`
--

INSERT INTO `treatment` (`id`, `treatment_name`, `treatment_description`, `rate`, `time_consumed`, `created_at`, `updated_at`) VALUES
(1, 'Teeth Cleaning', 'Professional cleaning of teeth to remove plaque and tartar.', 1500.00, '30 minutes', '2024-12-16 23:55:41', '2024-12-16 23:55:41'),
(2, 'Dental Checkup', 'Comprehensive dental examination including X-rays.', 800.00, '1 hour', '2024-12-16 23:55:41', '2024-12-16 23:55:41'),
(3, 'Tooth Extraction', 'Removal of a decayed or damaged tooth.', 2000.00, '45 minutes', '2024-12-16 23:55:41', '2024-12-16 23:55:41'),
(4, 'Root Canal Treatment', 'Treatment for infected tooth pulp.', 3500.00, '1 hour 30 minutes', '2024-12-16 23:55:41', '2024-12-16 23:55:41'),
(5, 'Dental Filling', 'Filling cavities caused by tooth decay.', 1200.00, '30 minutes', '2024-12-16 23:55:41', '2024-12-16 23:55:41'),
(6, 'Braces Installation', 'Installation of braces for teeth alignment.', 25000.00, '2 hours', '2024-12-16 23:55:41', '2024-12-16 23:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `usertype` enum('patient') NOT NULL DEFAULT 'patient'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `address`, `birthdate`, `gender`, `mobile`, `email`, `username`, `password`, `created_at`, `usertype`) VALUES
(1, 'Alessandra Nicole', '', 'Santos', 'SM Fairview, Quezon City', '2003-09-10', 'Female', '+639151381175', 'sample1@gmail.com', 'patient1', '$2y$10$/k9cB3X5cmWwSV4lygcqNuLa0d/DrHcLEUA8sfOG2BFRGLs6/y9Qa', '2024-12-08 16:03:43', 'patient'),
(2, 'Alessandra Nicole', '', 'Santos', 'SM Fairview, Quezon City', '2003-09-10', 'Female', '+639151381175', 'sample2@gmail.com', 'sample2', '$2y$10$IgCgzg2qIXVzg7ol12ZPUOGgHvpxNksoo9vciSNfChas6H/SvkFq.', '2024-12-08 17:01:29', 'patient'),
(3, 'Juan', 'Reyes', 'Dela Cruz', 'Calamba, Laguna', '1970-12-30', 'Male', '+639935329979', 'sample3@gmail.com', 'patient3', '$2y$10$glyYQI6IkmISz45l0Pr.K.e7Mwm6PZvmUffs2zsd9gh/uFDBzEYP6', '2024-12-11 19:32:33', 'patient'),
(4, 'Alessandra Nicole', '', 'Santos', 'SM Fairview, Quezon City', '2003-09-10', 'Female', '+639151381175', 'sample4@gmail.com', 'patient4', '$2y$10$HIE/h7BSTUJYP3Nkkmo09.fQ1u5AXPs486IdSUnyj73j8Ry5SaZEW', '2024-12-12 02:21:09', 'patient'),
(5, 'Alessandra Nicole', '', 'Santos', 'Fairview', '2024-08-16', 'Female', '+639151381175', 'sample10@gmail.com', 'patient123', '$2y$10$QeSaj69sbc5jktPHPJZvLemB0rHuR.Csu70Jy.smKezzW8xuJxodi', '2024-12-15 18:39:47', 'patient'),
(6, 'Alessandra Nicole', '', 'Santos', 'SM Fairview, Quezon City', '2024-12-17', 'Female', '09151381175', 'patient11@gmail.com', 'patient11', '$2y$10$6j7b6TMeYdFSYvG2ICGkr.bW1BfefLVyN/tuz6/Fw5OxytdUcpZa6', '2024-12-16 21:09:42', 'patient');

-- --------------------------------------------------------

--
-- Table structure for table `users_employee`
--

CREATE TABLE `users_employee` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` enum('admin','dentist','clinic_receptionist') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_employee`
--

INSERT INTO `users_employee` (`id`, `first_name`, `middle_name`, `last_name`, `address`, `birthdate`, `gender`, `mobile`, `email`, `username`, `password`, `usertype`, `created_at`) VALUES
(1, 'John', 'A.', 'Doe', '123 Main St', '1990-01-01', 'Male', '+1234567890', 'john.doe@example.com', 'johndoe', 'hashed_password_here', 'admin', '2024-12-16 20:06:11'),
(2, 'admin', '1', '23', 'Quezon City', '2024-12-17', 'Male', '09151381175', 'admin1@gmail.com', 'admin1', '$2y$10$tMvVCxPEeB6imuyqYfMsgOoan5HTXylMCKMF7w45zBO3hLxGjz61e', 'admin', '2024-12-16 20:13:28'),
(3, 'John', 'A.', 'Doe', 'Quezon City', '1998-04-22', 'Male', '+639123456789', 'dentist2@gmail.com', 'dentist2', '$2y$10$lrA1nlcQI6ox4KeifLXE..sdcfOqeVMwYCuk/1/RthiZaAsPFbqAi', 'dentist', '2024-12-16 22:36:03'),
(4, 'John', 'A.', 'Doe', 'Quezon City', '1998-03-17', 'Male', '+639151381175', 'dentist3@gmail.com', 'dentist3', '$2y$10$6Acz1/t972qYalWjBMhOoOel.Pw8pW.P8ftDaxmKFssLeFhNJcSOi', 'dentist', '2024-12-16 22:42:07'),
(5, 'Test', 'A.', 'Test', 'Caloocan City', '2002-03-14', 'Male', '+639151381175', 'testdentist@gmail.com', 'testdentist', '$2y$10$A/1alGhNFHAEaV40kHBQe.BUy7PC7932elwI7bVXoqM2MekMUSajG', 'dentist', '2024-12-16 22:54:11'),
(8, 'Test4', '', 'Huhu', 'Bagong Ilog, Pasig', '1997-05-17', 'Male', '+639151381175', 'test4@gmail.com', 'test4', '$2y$10$5ms4KWZXjWCVulK8jSTScOQErp/ycdUeie9zn9bezBZjmrmSKSIGa', 'clinic_receptionist', '2024-12-16 23:29:02'),
(9, 'Charmaine', 'P.', 'Zapata', 'Quezon City', '1996-12-17', 'Female', '+639151381175', 'drazapata@gmail.com', 'drazapata', '$2y$10$CwhunVFJIqnY0sR5bOvtYOBJwMSJWRa6s9Vlh7bc8meZ9IIExm3De', 'dentist', '2024-12-17 00:39:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dentists`
--
ALTER TABLE `dentists`
  ADD PRIMARY KEY (`dentist_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `receptionists`
--
ALTER TABLE `receptionists`
  ADD PRIMARY KEY (`receptionist_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reminder_template`
--
ALTER TABLE `reminder_template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `treatment`
--
ALTER TABLE `treatment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users_employee`
--
ALTER TABLE `users_employee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dentists`
--
ALTER TABLE `dentists`
  MODIFY `dentist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reminder_template`
--
ALTER TABLE `reminder_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `treatment`
--
ALTER TABLE `treatment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users_employee`
--
ALTER TABLE `users_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dentists`
--
ALTER TABLE `dentists`
  ADD CONSTRAINT `dentists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_employee` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receptionists`
--
ALTER TABLE `receptionists`
  ADD CONSTRAINT `receptionists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_employee` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
