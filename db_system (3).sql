-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2025 at 05:40 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_contact` varchar(15) NOT NULL,
  `dentist_name` varchar(255) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` varchar(255) NOT NULL,
  `status` enum('scheduled','completed','canceled','re-scheduled','submitted') DEFAULT 'submitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `service_duration` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `p_name`, `p_contact`, `dentist_name`, `service_name`, `appointment_date`, `appointment_time`, `status`, `created_at`, `service_duration`, `dentist_id`, `patient_id`, `username`, `email`) VALUES
(297, 'Alexa Protoss Mercerss', '+639123456789', 'Charmaine P. Zapata', 'Tooth Filling 10000.00', '2025-03-17', '08:00 AM - 09:00 AM', 'submitted', '2025-03-02 02:04:43', 0, 0, 0, 'alexaa', 'a@gmail.com'),
(298, 'Alexa Protoss Mercerss', '+639123456789', 'Charmaine P. Zapata', 'Dental Cleaning 5000.00', '2025-03-31', '08:30 AM - 09:00 AM', 'submitted', '2025-03-02 02:09:03', 0, 0, 0, 'alexaa', 'a@gmail.com'),
(300, 'Alexa Protoss Mercerss', '+639123456789', 'Charmaine P. Zapata', 'Dental Cleaning 5000.00', '2025-03-31', '01:30 PM - 02:00 PM', 'submitted', '2025-03-02 02:13:38', 0, 0, 0, 'alexaa', 'a@gmail.com'),
(301, 'Alexa Protoss Mercerss', '+639123456789', 'Charmaine P. Zapata', 'Tooth Filling 10000.00', '2025-03-19', '12:00 PM - 01:00 PM', 'submitted', '2025-03-02 02:15:48', 0, 0, 0, 'alexaa', 'a@gmail.com'),
(302, 'Alexa Protoss Mercerss', '+639123456789', 'Charmaine P. Zapata', 'Dental Cleaning 5000.00', '2025-03-14', '08:30 AM - 09:00 AM', 'submitted', '2025-03-02 06:01:59', 0, 0, 0, 'alexaa', 'a@gmail.com'),
(305, 'Alexa Protoss Mercerss', '+639123456789', 'Charmaine P. Zapata', 'Tooth Filling 10000.00', '2025-03-24', '10:00 AM - 11:00 AM', 'submitted', '2025-03-02 16:21:50', 0, 0, 0, 'admin', 'weakadika@gmail.com'),
(306, 'Alexa Protoss Mercerss', '+639123456789', 'Charmaine P. Zapata', 'Tooth Filling 10000.00', '2025-03-24', '10:00 AM - 11:00 AM', 'submitted', '2025-03-02 16:21:50', 0, 0, 0, 'admin', 'weakadika@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `approved_requests`
--

CREATE TABLE `approved_requests` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `treatment` varchar(255) NOT NULL,
  `appointment_time` varchar(255) NOT NULL,
  `appointment_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(255) DEFAULT NULL,
  `dentist_name` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'upcoming',
  `notes` text NOT NULL DEFAULT '\'N/A\''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approved_requests`
--

INSERT INTO `approved_requests` (`id`, `patient_id`, `patient_name`, `treatment`, `appointment_time`, `appointment_date`, `created_at`, `username`, `dentist_name`, `status`, `notes`) VALUES
(284, 291, 'Renan de Los Santos Bacit', 'Dental Cleaning 5000.00', '08:00 AM - 08:30 AM', '2025-02-19', '2025-02-12 06:47:31', 'bacitrenan', 'Charmaine P. Zapata', 'completed', 'N/A'),
(285, 290, 'renan Bacit', 'Tooth Filling 10000.00', '01:00 PM - 02:00 PM', '2025-02-12', '2025-02-12 06:49:45', 'renann', 'Charmaine P. Zapata', 'completed', 'N/A'),
(286, 292, 'renan Bacit', 'Root Canal Treatment 30000.00', '03:30 PM - 05:00 PM', '2025-02-17', '2025-02-17 04:01:32', 'renann', 'Charmaine P. Zapata', 'completed', 'gfd'),
(287, 293, 'Alexa Protoss Mercerss', 'Root Canal Treatment 30000.00', '08:00 AM - 09:30 AM', '2025-05-07', '2025-02-19 15:40:23', 'admin', 'Charmaine P. Zapata', 'upcoming', 'N/A'),
(288, 294, 'Alexa Protoss Mercerss', 'Root Canal Treatment 30000.00', '08:00 AM - 09:30 AM', '2025-05-07', '2025-02-28 21:12:38', 'admin', 'Charmaine P. Zapata', 'completed', 'dsa'),
(289, 304, 'Alexa Protoss Mercerss', 'Teeth Whitening 15000.00', '12:00 PM - 01:00 PM', '2025-04-06', '2025-03-02 05:12:32', 'alexaa', 'Charmaine P. Zapata', 'rescheduled', 'N/A'),
(290, 303, 'Alexa Protoss Mercerss', 'Tooth Filling 10000.00', '11:00 AM - 12:00 PM', '2025-05-10', '2025-03-02 05:12:40', 'alexaa', 'Charmaine P. Zapata', 'cancelled', 'N/A'),
(291, 295, 'Alexa Protoss Mercerss', 'Dental Cleaning 5000.00', '01:30 PM - 02:00 PM', '2025-03-04', '2025-03-02 14:50:26', 'alexaa', 'Charmaine P. Zapata', 'upcoming', 'N/A');

-- --------------------------------------------------------

--
-- Table structure for table `companion`
--

CREATE TABLE `companion` (
  `companionid` int(11) NOT NULL,
  `name` text NOT NULL,
  `gender` text NOT NULL,
  `age` int(11) NOT NULL,
  `appointmentid` int(11) NOT NULL,
  `approved_requestsid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companion`
--

INSERT INTO `companion` (`companionid`, `name`, `gender`, `age`, `appointmentid`, `approved_requestsid`) VALUES
(1, 'Nicholas David Gawat Abuel', 'male', 22, 303, NULL),
(2, 'Try Adsa Lang', 'female', 44, 303, NULL),
(3, 'Nicholas David Gawat Abuel', 'male', 22, 304, NULL),
(4, 'Try Adsa Lang', 'female', 44, 304, NULL),
(5, 'Nicholas David Gawat Abuel', 'male', 22, 305, NULL),
(6, 'Nicholas David Gawat Abuel', 'male', 22, 306, NULL);

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
-- Table structure for table `emailsched`
--

CREATE TABLE `emailsched` (
  `id` int(11) NOT NULL,
  `patientid` int(11) NOT NULL,
  `frequency` text NOT NULL,
  `message` text NOT NULL,
  `start` date NOT NULL DEFAULT current_timestamp(),
  `last_send` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emailsched`
--

INSERT INTO `emailsched` (`id`, `patientid`, `frequency`, `message`, `start`, `last_send`) VALUES
(2, 68, 'biweekly', 'sdsad', '2025-03-03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_event` datetime NOT NULL,
  `end_event` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `start_event`, `end_event`) VALUES
(8, 'Root Canal', '2024-12-12 10:00:00', '2024-12-12 13:00:00'),
(13, 'Pasta', '2024-12-19 10:00:00', '2024-12-19 13:00:00'),
(14, 'Root Canal', '2024-12-20 10:00:00', '2024-12-20 11:30:00'),
(23, 'Root Canal', '2025-01-09 12:00:00', '2025-01-09 14:00:00'),
(35, 'dd', '2025-03-11 00:00:00', '2025-03-12 00:00:00'),
(36, 'dsad', '2025-03-11 00:00:00', '2025-03-12 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `medical`
--

CREATE TABLE `medical` (
  `medid` int(11) NOT NULL,
  `usersid` int(11) NOT NULL,
  `medcertlink` text DEFAULT NULL,
  `dateuploaded` datetime NOT NULL DEFAULT current_timestamp(),
  `disease` text DEFAULT NULL,
  `recent_surgery` text DEFAULT NULL,
  `current_disease` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical`
--

INSERT INTO `medical` (`medid`, `usersid`, `medcertlink`, `dateuploaded`, `disease`, `recent_surgery`, `current_disease`) VALUES
(1, 70, '67c3bed243aae_call.png', '2025-03-02 10:13:38', 'Peanut', 'Cataract', 'Hematoma'),
(2, 70, '67c3bf54e8b18_470854692_122206117610194661_4123440877472491240_n.jpg', '2025-03-02 10:15:48', 'Peanut', 'Cataract', 'Hematoma');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `treatment` varchar(255) NOT NULL,
  `price` int(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `name`, `date`, `treatment`, `price`, `status`, `created_at`, `archived`) VALUES
(245, 'Eduard', '2025-01-24', 'Root Canal Treatment 30000.00', 0, 'Paid', '2025-01-07 10:52:15', 0),
(462, 'Marvin Castillo', '2025-02-12', 'Root Canal Treatment 30000.00', 0, 'Paid', '2025-02-12 05:05:53', 0),
(463, 'Renan de Los Santos Bacit', '2025-02-12', 'Dental Cleaning 5000.00', 0, 'Paid', '2025-02-28 13:39:28', 0),
(464, 'renan Bacit', '2025-02-12', 'Tooth Filling 10000.00', 0, 'Paid', '2025-02-28 20:33:25', 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `treatment_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('completed','pending','refunded') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `policy_content`
--

CREATE TABLE `policy_content` (
  `id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `policy_content`
--

INSERT INTO `policy_content` (`id`, `section`, `content`, `updated_at`) VALUES
(1, 'main_policy', '<h2>Terms and Conditions</h2>\n\n<p>Welcome to our online dental clinic appointment system. By accessing and using this system, you agree to be bound by these terms and conditions. If you do not agree with any part of these terms, please do not use our online appointment system.</p>\n\n<h2>Timezone</h2>\n\n<p>All appointment times are displayed and recorded in the Philippine Time (GMT +8). It is the responsibility of the patient to ensure they book appointments in the correct time zone.</p>\n\n<h2>Reservation Policy</h2>\n\n<ul>\n	<li>Appointments are charged with 20% downpayment of service to avoid no-show appointments.</li>\n	<li>A valid email address and contact number is required to complete the booking process.</li>\n	<li>Patients must provide complete and accurate information when booking appointments.</li>\n	<li>Patients under the age of 18 must have a parent or legal guardian book their appointment.</li>\n</ul>\n\n<h2>Cancellation Policy</h2>\n\n<ul>\n	<li>Appointments must be cancelled at least 24 hours prior to the scheduled time.</li>\n	<li>Patients who fail to show up for an appointment or cancel with less than 24 hours notice will be charged at least 20% of the dental treatment fee for&nbsp;no-show.</li>\n</ul>\n\n<h2>User Data Privacy</h2>\n\n<ul>\n	<li>All patient information is kept confidential and secure.</li>\n	<li>Patient data will not be shared with any third parties without explicit consent.</li>\n	<li>According to the Data Privacy Act of 2012, personal data such as appointment records are retained for as long as necessary for the purposes for which it was collected.</li>\n	<li>Patients have the right to access their records upon request.</li>\n</ul>\n\n<p>By booking an appointment, you acknowledge that you have read, understood, and agree to abide by these terms and conditions. We reserve the right to modify these terms at any time. Your continued use of the online appointment system indicates your acceptance of any changes.</p>\n\n<p><em>If you have any questions or concerns, please contact our office at 0915-123-4567 or <u>charmingsmiledc@gmail.com</u></em></p>\n', '2025-02-19 15:56:53');

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

-- --------------------------------------------------------

--
-- Table structure for table `rejected_requests`
--

CREATE TABLE `rejected_requests` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `treatment` varchar(255) NOT NULL,
  `appointment_time` varchar(255) NOT NULL,
  `appointment_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(255) DEFAULT NULL,
  `dentist_name` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'rejected',
  `reason` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rejected_requests`
--

INSERT INTO `rejected_requests` (`id`, `patient_id`, `patient_name`, `treatment`, `appointment_time`, `appointment_date`, `created_at`, `username`, `dentist_name`, `status`, `reason`) VALUES
(11, 170, 'Harry Potter', 'Dental Cleaning 50.00', '09:00 AM - 09:30 AM', '2025-02-24', '2025-02-08 09:17:04', 'Harry', 'Charmaine P. Zapata', 'rejected', 'emergency'),
(42, 289, 'Marvin Castillo', 'Dental Cleaning 5000.00', '12:30 PM - 01:00 PM', '2025-02-12', '2025-02-12 05:10:24', 'Marvin', 'Charmaine P. Zapata', 'rejected', 'Scheduling Conflicts'),
(43, 296, 'Alexa Protoss Mercerss', 'Dental Cleaning 5000.00', '01:30 PM - 02:00 PM', '2025-03-18', '2025-03-02 16:03:08', 'alexaa', 'Charmaine P. Zapata', 'rejected', 'ss'),
(44, 299, 'Alexa Protoss Mercerss', 'Dental Cleaning 5000.00', '08:30 AM - 09:00 AM', '2025-03-31', '2025-03-02 16:03:29', 'alexaa', 'Charmaine P. Zapata', 'rejected', 's');

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
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `rate` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `duration`, `rate`, `created_at`, `updated_at`) VALUES
(1, 'Dental Cleaning', 'A thorough cleaning of teeth and gums.', 30, '5000.00', '2025-01-26 09:30:51', '2025-01-26 09:30:51'),
(2, 'Tooth Filling', 'Filling cavities in teeth.', 60, '10000.00', '2025-01-26 09:30:51', '2025-01-26 09:30:51'),
(3, 'Root Canal Treatment', 'Treatment for infected tooth pulp.', 90, '30000.00', '2025-01-26 09:30:51', '2025-01-26 09:30:51'),
(4, 'Teeth Whitening', 'Whitening treatment for discolored teeth.', 60, '15000.00', '2025-01-26 09:30:51', '2025-01-26 09:30:51'),
(5, 'Consultation', 'Initial consultation with a dentist.', 30, '200.00', '2025-01-26 09:30:51', '2025-01-26 09:30:51');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `den_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_reserved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `den_id`, `slot_date`, `start_time`, `end_time`, `is_reserved`) VALUES
(1, 1, '2025-01-30', '08:00:00', '08:30:00', 0),
(2, 1, '2025-01-30', '08:30:00', '09:00:00', 0),
(3, 1, '2025-01-30', '09:00:00', '09:30:00', 0),
(4, 1, '2025-01-30', '09:30:00', '10:00:00', 0),
(5, 1, '2025-01-30', '10:00:00', '10:30:00', 0),
(6, 1, '2025-01-30', '10:30:00', '11:00:00', 0),
(7, 1, '2025-01-30', '11:00:00', '11:30:00', 0),
(8, 1, '2025-01-30', '11:30:00', '12:00:00', 0),
(9, 1, '2025-01-30', '13:00:00', '13:30:00', 0),
(10, 1, '2025-01-30', '13:30:00', '14:00:00', 0),
(11, 1, '2025-01-30', '14:00:00', '14:30:00', 0),
(12, 1, '2025-01-30', '14:30:00', '15:00:00', 0),
(13, 1, '2025-01-30', '15:00:00', '15:30:00', 0),
(14, 1, '2025-01-30', '15:30:00', '16:00:00', 0),
(15, 1, '2025-01-30', '16:00:00', '16:30:00', 0),
(16, 1, '2025-01-30', '16:30:00', '17:00:00', 0),
(17, 1, '2025-01-31', '08:00:00', '08:30:00', 0),
(18, 1, '2025-01-31', '08:30:00', '09:00:00', 0),
(19, 1, '2025-01-31', '09:00:00', '09:30:00', 0),
(20, 1, '2025-01-31', '09:30:00', '10:00:00', 0),
(21, 1, '2025-01-31', '10:00:00', '10:30:00', 0),
(22, 1, '2025-01-31', '10:30:00', '11:00:00', 0),
(23, 1, '2025-01-31', '11:00:00', '11:30:00', 0),
(24, 1, '2025-01-31', '11:30:00', '12:00:00', 0),
(25, 1, '2025-01-31', '13:00:00', '13:30:00', 0),
(26, 1, '2025-01-31', '13:30:00', '14:00:00', 0),
(27, 1, '2025-01-31', '14:00:00', '14:30:00', 0),
(28, 1, '2025-01-31', '14:30:00', '15:00:00', 0),
(29, 1, '2025-01-31', '15:00:00', '15:30:00', 0),
(30, 1, '2025-01-31', '15:30:00', '16:00:00', 0),
(31, 1, '2025-01-31', '16:00:00', '16:30:00', 0),
(32, 1, '2025-01-31', '16:30:00', '17:00:00', 0);

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
  `usertype` enum('patient') NOT NULL DEFAULT 'patient',
  `emergencyname` text DEFAULT NULL,
  `emergencycontact` text DEFAULT NULL,
  `otp` text DEFAULT NULL,
  `status` text NOT NULL DEFAULT 'inactive',
  `last_login` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `address`, `birthdate`, `gender`, `mobile`, `email`, `username`, `password`, `created_at`, `usertype`, `emergencyname`, `emergencycontact`, `otp`, `status`, `last_login`) VALUES
(58, 'Don', '', 'Gojo', '947', '2003-09-14', 'Male', '09291744349', 'don914gojocruz@gmail.com', 'Don', '$2y$10$ZPBbE9PBa39MSJpWydrcneFqN2yYe9NAdVSZYDbRPc5FvPVHkoAm.', '2025-02-10 07:15:21', 'patient', NULL, NULL, NULL, 'inactive', '2025-03-02'),
(62, 'David', '', 'David', 'Quezon City', '1998-03-10', 'Male', '09456734829', 'david@gmail.com', 'David', '$2y$10$5J4zOuTxEWZOf8.u3XsC/uvBRXurQjkKO/64LJEeIF8RNo4IvHvrO', '2025-02-10 15:58:41', 'patient', NULL, NULL, NULL, 'inactive', '2025-03-02'),
(64, 'Mia', '', 'Tidoy', '65', '2002-02-04', 'Female', '09911771234', 'mariaadelinetidoy@gmail.com', 'mia', '$2y$10$RrFWlWdwTIJZ8Kduy/UVfOhy0nTRyjijow6CrGSGwiXZsNo/87eI.', '2025-02-11 01:20:36', 'patient', NULL, NULL, NULL, 'inactive', '2025-03-02'),
(66, 'Renan', 'de Los Santos', 'Bacit', 'CSJDM, Bulacan', '1998-02-08', 'Male', '+63-9123456789', 'bacit.renan@gmail.com', 'bacitrenan', '$2y$10$tEFn41QSLFxaGPXqm94yjOXURlHTL55hMqmukDcCaLWmyr7KFT6QG', '2025-02-12 06:24:14', 'patient', NULL, NULL, NULL, 'inactive', '2025-03-02'),
(67, 'renan', '', 'Bacit', 'Quezon City', '2013-07-12', 'Male', '0911111111111', 'dmmy36381@gmail.com', 'renann', '$2y$10$yTTm2u1x2G8ETWIzHRkPpeoRB625ZNOHUsvpULP.IEhSK49FPislW', '2025-02-12 06:25:35', 'patient', NULL, NULL, NULL, 'inactive', '2025-03-02'),
(68, 'Alexa', 'Protoss', 'Mercersssads', 'Kahit Saan St. Basta Probinsya Itooo', '2000-05-03', 'Female', '+639123456789', 'weakadika@gmail.com', 'admin', '$2y$10$ZsryEnP1yFSMxRFwgxo68OMi1g6wqRw/uVXeGxDiCp97GtRN5to2S', '2025-02-19 15:22:30', 'patient', NULL, NULL, '166097', 'active', '2025-03-03'),
(69, 'Alexa', 'Protoss', 'Mercerss', 'Kahit Saan St. Basta Probinsya Itooo', '2000-03-02', 'Male', '+639123456789', 'aa@gmail.com', 'alexa', '$2y$10$cCdKOOyQ/zn34Dk7kt5G7OGs3xvwPFWa99PPA8TJw.jCzgx4LbA0O', '2025-02-27 20:10:01', 'patient', NULL, NULL, '462292', 'active', '2025-03-02'),
(70, 'Alexa', 'Protoss', 'Mercerss', 'Kahit Saan St. Basta Probinsya Itooo', '2000-03-02', 'Male', '+639123456789', 'a@gmail.com', 'alexaa', '$2y$10$GCZFJKO5YTGu6a4LABKjq.j05Zw12vupn8Ash9j761WlgtWUnKBBa', '2025-03-01 17:37:25', 'patient', 'Wala Muna', '+639287605386', '252501', 'active', '2025-03-02');

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
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0,
  `last_login` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_employee`
--

INSERT INTO `users_employee` (`id`, `first_name`, `middle_name`, `last_name`, `address`, `birthdate`, `gender`, `mobile`, `email`, `username`, `password`, `usertype`, `status`, `created_at`, `is_archived`, `last_login`) VALUES
(29, 'Charmaine', '', 'Zapata', '947 Ilang - Ilang St. Brgy. 185 Malaria, Tala, Caloocan City', '2025-01-07', 'Female', '09216915504', 'char@gmail.com', 'Charmaine', '$2y$10$O0oLfLoB38i1UFr0zEzsxu2/C53wlBPrvV10M2GCW9JrTJZjJC1Fa', 'dentist', 'active', '2025-01-07 06:35:50', 0, '2025-03-03'),
(35, 'Arlene', '', 'Yangco', 'Quezon City', '1997-07-09', 'Female', '09456734829', 'arlene@gmail.com', 'Arlene', '$2y$10$UGrTPvDHDddfAJvZ7xEVeuB3t1X6dUJpaCwdkoTXO.RnxUUWgmFM6', 'clinic_receptionist', '1', '2025-02-09 10:48:04', 0, '2025-03-02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`);

--
-- Indexes for table `approved_requests`
--
ALTER TABLE `approved_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companion`
--
ALTER TABLE `companion`
  ADD PRIMARY KEY (`companionid`);

--
-- Indexes for table `dentists`
--
ALTER TABLE `dentists`
  ADD PRIMARY KEY (`dentist_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `emailsched`
--
ALTER TABLE `emailsched`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical`
--
ALTER TABLE `medical`
  ADD PRIMARY KEY (`medid`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `treatment_id` (`treatment_id`),
  ADD KEY `dentist_id` (`dentist_id`);

--
-- Indexes for table `policy_content`
--
ALTER TABLE `policy_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section` (`section`);

--
-- Indexes for table `receptionists`
--
ALTER TABLE `receptionists`
  ADD PRIMARY KEY (`receptionist_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rejected_requests`
--
ALTER TABLE `rejected_requests`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `den_id` (`den_id`);

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
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT for table `approved_requests`
--
ALTER TABLE `approved_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=292;

--
-- AUTO_INCREMENT for table `companion`
--
ALTER TABLE `companion`
  MODIFY `companionid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dentists`
--
ALTER TABLE `dentists`
  MODIFY `dentist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `emailsched`
--
ALTER TABLE `emailsched`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `medical`
--
ALTER TABLE `medical`
  MODIFY `medid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=465;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `policy_content`
--
ALTER TABLE `policy_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rejected_requests`
--
ALTER TABLE `rejected_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `treatment`
--
ALTER TABLE `treatment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `users_employee`
--
ALTER TABLE `users_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `receptionists`
--
ALTER TABLE `receptionists`
  ADD CONSTRAINT `receptionists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_employee` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
