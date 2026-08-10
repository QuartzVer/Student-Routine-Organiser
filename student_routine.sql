-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2026 at 03:18 AM
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
-- Database: `student_routine`
--

-- --------------------------------------------------------

--
-- Table structure for table `exercise`
--

CREATE TABLE `exercise` (
  `exercise_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL,
  `calories_burned` int(11) NOT NULL,
  `intensity_level` varchar(20) NOT NULL,
  `exercise_date` date NOT NULL,
  `activity_status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise`
--

INSERT INTO `exercise` (`exercise_id`, `user_id`, `activity_type`, `duration`, `calories_burned`, `intensity_level`, `exercise_date`, `activity_status`) VALUES
(17, 4, 'Jog', 4, 4, 'Low', '2026-08-11', 'Scheduled'),
(18, 4, 'Gym', 4, 4, 'Medium', '2026-08-09', 'Missed'),
(19, 4, 'Cycling', 7, 4, 'High', '2026-08-08', 'Missed'),
(20, 5, 'Jog', 1, 2, 'Low', '2026-08-18', 'Missed'),
(21, 5, 'Jog', 2, 3, 'Medium', '2026-08-19', 'Completed'),
(22, 5, 'Flying', 5, 4, 'Medium', '2026-08-04', 'Missed'),
(23, 5, 'Gym', 4, 3, 'Low', '2026-08-09', 'Completed'),
(25, 5, 'Yacking', 4, 9, 'Low', '2026-08-12', 'Completed'),
(26, 5, 'Cycling', 1, 1, 'Low', '2026-08-13', 'Missed'),
(27, 5, 'Gym', 2, 4, 'Medium', '2026-08-12', 'Completed'),
(28, 5, 'Jog', 4, 4, 'Low', '2026-07-30', 'Completed'),
(29, 5, 'Jog', 3, 1, 'Low', '2026-08-05', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `exercise_goals`
--

CREATE TABLE `exercise_goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `period` varchar(20) NOT NULL,
  `target_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise_goals`
--

INSERT INTO `exercise_goals` (`goal_id`, `user_id`, `period`, `target_value`) VALUES
(1, 4, 'monthly', 12);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reg_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `username`, `full_name`, `email`, `password`, `reg_date`) VALUES
(4, 'student', 'TKH', 'TKH', 'tkh@gmail.com', 'caad76e0fa34e51d06622d2e5c1b8f30', '2026-08-09 16:32:10'),
(5, 'student', 'qt', 'qt', 'qt@gmaul.com', 'e85823b4e7db1064f4301e1c74978199', '2026-08-09 17:34:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exercise`
--
ALTER TABLE `exercise`
  ADD PRIMARY KEY (`exercise_id`);

--
-- Indexes for table `exercise_goals`
--
ALTER TABLE `exercise_goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD UNIQUE KEY `unique_user_goal` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `exercise`
--
ALTER TABLE `exercise`
  MODIFY `exercise_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `exercise_goals`
--
ALTER TABLE `exercise_goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
