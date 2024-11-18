-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2024 at 06:02 PM
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
-- Database: `railway_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `pnr_number` varchar(20) NOT NULL,
  `train_number` varchar(10) NOT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `gender` char(1) DEFAULT NULL CHECK (`gender` in ('M','F','O')),
  `age` int(11) DEFAULT NULL CHECK (`age` > 0),
  `document_type` varchar(20) DEFAULT NULL,
  `document_number` varchar(50) DEFAULT NULL,
  `berth_preference` varchar(20) DEFAULT NULL,
  `allocated_seat` varchar(10) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `journey_date` date NOT NULL,
  `from_station` varchar(50) NOT NULL,
  `to_station` varchar(50) NOT NULL,
  `class` varchar(10) DEFAULT NULL CHECK (`class` in ('1A','2A','3A','SL','CC','2S')),
  `fare` decimal(10,2) DEFAULT NULL CHECK (`fare` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`pnr_number`, `train_number`, `passenger_name`, `gender`, `age`, `document_type`, `document_number`, `berth_preference`, `allocated_seat`, `booking_date`, `journey_date`, `from_station`, `to_station`, `class`, `fare`) VALUES
('9059237621', '12954', 'Arpit kumar', 'M', 15, 'Aadhar', '2341242321', '0', 'HA3-LB7', '2024-11-15', '2024-11-22', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('9059237621', '12954', 'saurav g', 'M', 19, 'Aadhar', '2341242390', '0', 'HA1-LB7', '2024-11-15', '2024-11-22', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('9976948802', '12954', 'Arpit kumar', 'M', 55, 'Aadhar', '123456789101', '0', 'HA1-LB4', '2024-11-15', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('4648847501', '12954', 'Arpit kumar', 'M', 55, 'Aadhar', '123456789101', '0', 'HA2-LB10', '2024-11-15', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('4648847501', '12954', 'yfwvebunlkm;', 'M', 49, 'Aadhar', '123456789101', '0', 'HA3-LB7', '2024-11-15', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('4648847501', '12954', 'ciduinokpl', 'M', 54, 'Aadhar', '5768900987654', '0', 'HA2-LB16', '2024-11-15', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('4648847501', '12954', 'cfvgbhjmlk', 'M', 65, 'Aadhar', '567890987654', '0', 'HA2-LB1', '2024-11-15', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('4648847501', '12954', 'dxcgbhnmlk', 'M', 46, 'Aadhar', '45678909087654', '0', 'HA1-LB7', '2024-11-15', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('4648847501', '12954', 'vfghoijk;lm', 'M', 16, 'Aadhar', '576890897655', '0', 'HA2-LB13', '2024-11-15', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('1462028241', '12954', 'qewegrhgh', 'M', 45, 'Aadhar', '2341242321', '0', 'HA1-LB4', '2024-11-15', '2024-11-29', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('1462028241', '12954', 'Arpit kumar', 'M', 28, 'Aadhar', '123456789101', '0', 'HA3-LB10', '2024-11-15', '2024-11-29', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('1462028241', '12954', 'sfdbgv', 'M', 12, 'Aadhar', '142525274', '0', 'HA2-LB7', '2024-11-15', '2024-11-29', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('1462028241', '12954', 'wdegrhtyhg', 'M', 25, 'Aadhar', '213456423345', '0', 'HA1-LB7', '2024-11-15', '2024-11-29', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('1462028241', '12954', 'dafdfgnmhbfv', 'M', 76, 'Aadhar', 'sfdfcnvg', '0', 'HA2-LB16', '2024-11-15', '2024-11-29', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('1462028241', '12954', 'ewresgfhcg', 'M', 34, 'Aadhar', 'wer3tey5', '0', 'HA2-LB4', '2024-11-15', '2024-11-29', 'Mumbai Central', 'Delhi Junction', '1A', 2900.00),
('5496820953', '', 'Arpit kumar', 'M', 9, 'Aadhar', '2341242321', '0', 'HA3-LB1', '2024-11-16', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 0.00),
('4605848368', '<br />\r\n<b', 'Arpit kumar', 'M', 91, 'Aadhar', '123456789101', '0', 'HA3-LB4', '2024-11-16', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 0.00),
('6817302119', '12952', 'Arpit kumar', 'M', 19, 'Aadhar', '123456789101', '0', 'HA1-LB4', '2024-11-16', '2024-11-23', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('1116607943', '12952', 'Arpit kumar', 'M', 19, 'Aadhar', '123456789101', '0', 'HA2-LB10', '2024-11-16', '2024-11-30', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('4282076515', '12952', 'Arpit kumar', 'M', 20, 'Aadhar', '2341242321', '0', 'HA3-LB13', '2024-11-16', '2024-12-05', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('3923533727', '12952', 'Arpit kumar', 'M', 19, 'Aadhar', '123456789101', '0', 'HA2-LB10', '2024-11-17', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('7187007344', '12952', 'Arpit kumar', 'M', 18, 'Aadhar', '2341242321', '0', 'HA1-LB1', '2024-11-17', '2024-11-19', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('2680799513', '12952', 'Arpit kumar', 'M', 19, 'Aadhar', '2341242321', '0', 'HA1-LB13', '2024-11-17', '2024-11-21', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('0469876680', '12952', 'Arpit kumar', 'M', 19, 'Aadhar', '123456789101', '0', 'HA3-LB1', '2024-11-17', '2024-11-29', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('4228409553', '12952', 'Arpit kumar', 'M', 18, 'Aadhar', '123456789101', '0', 'HA1-LB7', '2024-11-17', '2024-11-16', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('8082232182', '12952', 'Arpit kumar', 'M', 18, 'Aadhar', '123456789101', '0', 'HA3-LB13', '2024-11-17', '2024-11-16', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('5112261222', '12952', 'Arpit kumar', 'M', 18, 'Aadhar', '123456789101', '0', 'HA2-LB1', '2024-11-17', '2024-11-16', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('3222021178', '12952', 'Arpit kumar', 'M', 18, 'Aadhar', '123456789101', '0', 'HA3-LB16', '2024-11-17', '2024-11-21', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('7097410602', '12952', 'Arpit kumar', 'M', 18, 'Aadhar', '123456789101', '0', 'HA1-LB1', '2024-11-17', '2024-11-20', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('2329031926', '12952', 'Arpit kumar', 'M', 18, 'Aadhar', '123456789101', '0', 'HA1-LB13', '2024-11-17', '2024-11-22', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('8467729913', '12952', 'Arpit kumar', 'M', 19, 'Aadhar', '123456789101', '0', 'HA1-LB7', '2024-11-17', '2024-11-19', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('0885378180', '12952', 'Arpit kumar', 'M', 19, 'Aadhar', '2341242321', '0', 'HA2-LB10', '2024-11-17', '2024-11-27', 'Mumbai Central', 'Delhi Junction', '1A', 3000.00),
('1401182086', '12951', 'Arpit kumar', 'M', 19, 'Aadhar', '123456789101', '0', 'HA1-LB10', '2024-11-18', '2024-11-30', 'Mumbai CST', 'New Delhi', '1A', 4500.00);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `submission_date` datetime NOT NULL,
  `status` enum('new','read','responded') DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `submission_date`, `status`) VALUES
(1, 'Arpit Kumar', 'arpitk@iitbhilai.ac.in', 'not good servise', 'vfcyiuhndjgfghijpqklnek', '2024-11-15 01:31:32', 'new'),
(2, 'Arpit Kumar', 'arpitk@iitbhilai.ac.in', 'not good servise', 'vfcyiuhndjgfghijpqklnek', '2024-11-15 01:34:54', 'new'),
(3, 'Arpit Kumar', 'arpitk@iitbhilai.ac.in', 'not good servise', 'vfcyiuhndjgfghijpqklnek', '2024-11-15 01:35:49', 'new'),
(4, 'Arpit Kumar', 'arpitk@iitbhilai.ac.in', 'not good servise', 'vfcyiuhndjgfghijpqklnek', '2024-11-15 01:36:14', 'new'),
(5, 'arpit', 'arpitraj676@gmail.com', 'yghuikoalnbhb', 'wedinjkckdcbu  cbiuchcoc uju c hib ygnc uhccpwc cwncuigfhcacnpciugh cn ccgbckiuqoi ', '2024-11-15 01:38:12', 'new'),
(6, 'Arpit Kumar', 'arpitkumar700000@gmail.com', 'not received the cancellation price yet.', 'the quality of the website is good but i did not get the refund of my cancelled ticket.', '2024-11-15 11:37:56', 'new'),
(7, 'Arpit Kumar', 'arpitkumar700000@gmail.com', 'not received the cancellation price yet.', 'refund not received\r\n', '2024-11-15 13:47:41', 'new'),
(8, 'Arpit Kumar', 'arpitkumar700000@gmail.com', 'v hkbwejls', 'v gbqjklsam', '2024-11-15 13:49:27', 'new'),
(9, 'Arpit Kumar', 'arpitk@iitbhilai.ac.in', 'not good servise', ' bnsf;dml,', '2024-11-16 17:28:43', 'new'),
(10, 'Arpit Kumar', 'arpitk@iitbhilai.ac.in', 'yghuikoalnbhb', 'buol', '2024-11-17 11:39:42', 'new');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `feedback_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `rating` int(11) NOT NULL,
  `submission_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `feedback_type`, `message`, `rating`, `submission_date`, `created_at`) VALUES
(21, 'Arpit Kumar', 'arpitk@iitbhilai.ac.in', 'general', 'Good website. overall good experience', 4, '2024-11-18 20:09:22', '2024-11-18 14:39:22'),
(22, 'Saurav Gupta', 'sauravg@iitbhilai.ac.in', 'website', 'smooth website', 5, '2024-11-18 20:09:59', '2024-11-18 14:39:59'),
(23, 'Keshav Mishra', 'keshavm@iitbhilai.ac.in', 'suggestion', 'more travel packages should be there with lesser durations. ', 2, '2024-11-18 20:11:14', '2024-11-18 14:41:14'),
(24, 'Aryan', 'aryan@iitbhilai.ac.in', 'complaint', 'very late reply to my contact us query', 2, '2024-11-18 20:12:53', '2024-11-18 14:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `package_bookings`
--

CREATE TABLE `package_bookings` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `package_duration` varchar(50) DEFAULT NULL,
  `package_price` decimal(10,2) DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_bookings`
--

INSERT INTO `package_bookings` (`id`, `user_email`, `package_name`, `package_duration`, `package_price`, `booking_date`) VALUES
(1, 'arpitk@iitbhilai.ac.in', 'Golden Triangle Explorer', '6 Days / 5 Nights', 24999.00, '2024-11-17 17:13:26');

-- --------------------------------------------------------

--
-- Table structure for table `trains`
--

CREATE TABLE `trains` (
  `train_id` int(11) NOT NULL,
  `train_number` varchar(10) NOT NULL,
  `train_name` varchar(100) NOT NULL,
  `from_station` varchar(100) NOT NULL,
  `to_station` varchar(100) NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `duration` varchar(20) GENERATED ALWAYS AS (timediff(`arrival_time`,`departure_time`)) STORED,
  `runs_on` set('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `total_sleeper_seats` int(11) NOT NULL,
  `total_ac3_seats` int(11) NOT NULL,
  `total_ac2_seats` int(11) NOT NULL,
  `total_ac1_seats` int(11) NOT NULL,
  `available_sleeper_seats` int(11) DEFAULT NULL,
  `available_ac3_seats` int(11) DEFAULT NULL,
  `available_ac2_seats` int(11) DEFAULT NULL,
  `available_ac1_seats` int(11) DEFAULT NULL,
  `base_sleeper_fare` decimal(10,2) NOT NULL,
  `base_ac3_fare` decimal(10,2) NOT NULL,
  `base_ac2_fare` decimal(10,2) NOT NULL,
  `base_ac1_fare` decimal(10,2) NOT NULL,
  `route_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`route_info`)),
  `train_status` enum('RUNNING','CANCELLED','SCHEDULED','DELAYED') DEFAULT 'SCHEDULED',
  `delay_minutes` int(11) DEFAULT 0,
  `train_type` enum('EXPRESS','SUPERFAST','LOCAL','SHUTTLE') NOT NULL,
  `catering_available` tinyint(1) DEFAULT 0,
  `wheelchair_accessible` tinyint(1) DEFAULT 0,
  `wifi_available` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trains`
--

INSERT INTO `trains` (`train_id`, `train_number`, `train_name`, `from_station`, `to_station`, `departure_time`, `arrival_time`, `runs_on`, `total_sleeper_seats`, `total_ac3_seats`, `total_ac2_seats`, `total_ac1_seats`, `available_sleeper_seats`, `available_ac3_seats`, `available_ac2_seats`, `available_ac1_seats`, `base_sleeper_fare`, `base_ac3_fare`, `base_ac2_fare`, `base_ac1_fare`, `route_info`, `train_status`, `delay_minutes`, `train_type`, `catering_available`, `wheelchair_accessible`, `wifi_available`) VALUES
(46, '12951', 'Rajdhani Express', 'New Delhi', 'Mumbai Central', '16:25:00', '08:15:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 785.00, 2000.00, 2800.00, 3750.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 1),
(47, '12952', 'Rajdhani Express', 'Mumbai Central', 'New Delhi', '09:00:00', '23:45:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 785.00, 2000.00, 2800.00, 3750.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 1),
(48, '12343', 'Poorva Express', 'New Delhi', 'Kolkata', '17:40:00', '09:15:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 675.00, 1850.00, 2600.00, 3500.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(49, '12344', 'Poorva Express', 'Kolkata', 'New Delhi', '10:30:00', '02:15:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 675.00, 1850.00, 2600.00, 3500.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(50, '12615', 'Grand Trunk Express', 'New Delhi', 'Chennai Central', '18:05:00', '19:40:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 955.00, 2400.00, 3200.00, 4200.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 1),
(51, '12616', 'Grand Trunk Express', 'Chennai Central', 'New Delhi', '20:30:00', '22:15:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 955.00, 2400.00, 3200.00, 4200.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 1),
(52, '12649', 'Sampark Kranti Express', 'New Delhi', 'Bangalore', '20:50:00', '16:25:00', 'Monday,Wednesday,Friday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 825.00, 2200.00, 3000.00, 4000.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(53, '12650', 'Sampark Kranti Express', 'Bangalore', 'New Delhi', '17:30:00', '13:05:00', 'Tuesday,Thursday,Saturday', 180, 150, 120, 50, 180, 150, 120, 50, 825.00, 2200.00, 3000.00, 4000.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(54, '12723', 'Telangana Express', 'New Delhi', 'Hyderabad', '12:45:00', '10:25:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 895.00, 2200.00, 3000.00, 4000.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 1),
(55, '12724', 'Telangana Express', 'Hyderabad', 'New Delhi', '11:30:00', '09:10:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 895.00, 2200.00, 3000.00, 4000.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 1),
(56, '12859', 'Gitanjali Express', 'Mumbai Central', 'Kolkata', '06:05:00', '08:25:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 895.00, 2200.00, 3000.00, 4000.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 0),
(57, '12860', 'Gitanjali Express', 'Kolkata', 'Mumbai Central', '09:40:00', '12:05:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 895.00, 2200.00, 3000.00, 4000.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 0),
(58, '12163', 'Dadar Chennai Express', 'Mumbai Central', 'Chennai Central', '23:10:00', '18:45:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 925.00, 2350.00, 3150.00, 4150.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(59, '12164', 'Dadar Chennai Express', 'Chennai Central', 'Mumbai Central', '19:30:00', '15:05:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 925.00, 2350.00, 3150.00, 4150.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(60, '16587', 'Udyan Express', 'Mumbai Central', 'Bangalore', '08:10:00', '04:30:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 675.00, 1700.00, 2300.00, 3100.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(61, '16588', 'Udyan Express', 'Bangalore', 'Mumbai Central', '05:45:00', '02:10:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 675.00, 1700.00, 2300.00, 3100.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(62, '17031', 'Mumbai Express', 'Mumbai Central', 'Hyderabad', '21:45:00', '15:20:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 625.00, 1600.00, 2200.00, 2900.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(63, '17032', 'Mumbai Express', 'Hyderabad', 'Mumbai Central', '16:30:00', '10:05:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 625.00, 1600.00, 2200.00, 2900.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(64, '12841', 'Coromandel Express', 'Kolkata', 'Chennai Central', '14:50:00', '17:15:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 855.00, 2100.00, 2900.00, 3850.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 0),
(65, '12842', 'Coromandel Express', 'Chennai Central', 'Kolkata', '18:30:00', '20:55:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 855.00, 2100.00, 2900.00, 3850.00, '[]', 'SCHEDULED', 0, 'SUPERFAST', 1, 1, 0),
(66, '12626', 'East Coast Express', 'Kolkata', 'Bangalore', '20:25:00', '14:50:00', 'Monday,Wednesday,Friday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 725.00, 1850.00, 2500.00, 3350.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(67, '12627', 'East Coast Express', 'Bangalore', 'Kolkata', '15:45:00', '10:10:00', 'Tuesday,Thursday,Saturday', 180, 150, 120, 50, 180, 150, 120, 50, 725.00, 1850.00, 2500.00, 3350.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(68, '12703', 'Falaknuma Express', 'Kolkata', 'Hyderabad', '12:45:00', '15:20:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 725.00, 1800.00, 2500.00, 3300.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(69, '12704', 'Falaknuma Express', 'Hyderabad', 'Kolkata', '16:30:00', '19:05:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 725.00, 1800.00, 2500.00, 3300.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(70, '12671', 'Nilagiri Express', 'Chennai Central', 'Bangalore', '20:10:00', '04:20:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 425.00, 1100.00, 1500.00, 2000.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(71, '12672', 'Nilagiri Express', 'Bangalore', 'Chennai Central', '05:30:00', '13:40:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 425.00, 1100.00, 1500.00, 2000.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(72, '12759', 'Charminar Express', 'Chennai Central', 'Hyderabad', '06:25:00', '20:40:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 495.00, 1300.00, 1800.00, 2400.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(73, '12760', 'Charminar Express', 'Hyderabad', 'Chennai Central', '21:50:00', '12:05:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 495.00, 1300.00, 1800.00, 2400.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(74, '12785', 'Kacheguda Express', 'Bangalore', 'Hyderabad', '20:30:00', '08:40:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 555.00, 1400.00, 1900.00, 2500.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0),
(75, '12786', 'Kacheguda Express', 'Hyderabad', 'Bangalore', '09:50:00', '22:00:00', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', 180, 150, 120, 50, 180, 150, 120, 50, 555.00, 1400.00, 1900.00, 2500.00, '[]', 'SCHEDULED', 0, 'EXPRESS', 1, 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_bookings`
--
ALTER TABLE `package_bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trains`
--
ALTER TABLE `trains`
  ADD PRIMARY KEY (`train_id`),
  ADD UNIQUE KEY `train_number` (`train_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `package_bookings`
--
ALTER TABLE `package_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trains`
--
ALTER TABLE `trains`
  MODIFY `train_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
