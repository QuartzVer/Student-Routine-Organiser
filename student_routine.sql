-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2026 at 05:20 PM
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
-- Table structure for table `diary`
--

CREATE TABLE `diary` (
  `diary_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `mood_status` varchar(30) NOT NULL,
  `diary_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diary`
--

INSERT INTO `diary` (`diary_id`, `user_id`, `title`, `content`, `mood_status`, `diary_date`) VALUES
(1, 4, 'just okay', 'hey', 'Neutral', '2026-08-05'),
(3, 4, 'yup', 'heyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyheyhey', 'Neutral', '2026-08-17'),
(4, 4, 'yeayea', 'hyehheyeheyehyehyey  hyehheyeheyehyehyey hyehheyeheyehyehyey hyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyeyhyehheyeheyehyehyey', 'Neutral', '2026-08-17'),
(5, 5, 'ddd', 'ddddd', 'Neutral', '2026-08-18'),
(6, 4, 'Hello me', 'Yay mee', 'Happy', '2026-08-19');

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
(29, 5, 'Jog', 3, 1, 'Low', '2026-08-05', 'Completed'),
(30, 4, 'Yacking', 8, 222, 'Medium', '2026-08-20', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `exercise_goals`
--

CREATE TABLE `exercise_goals` (
  `exercise_goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `period` varchar(20) NOT NULL,
  `target_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise_goals`
--

INSERT INTO `exercise_goals` (`exercise_goal_id`, `user_id`, `period`, `target_value`) VALUES
(1, 4, 'monthly', 12);

-- --------------------------------------------------------

--
-- Table structure for table `habits`
--

CREATE TABLE `habits` (
  `habit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `habit_name` varchar(100) NOT NULL,
  `target_frequency` varchar(50) NOT NULL,
  `completion_status` enum('Pending','Completed') NOT NULL DEFAULT 'Pending',
  `habit_date` date NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habits`
--

INSERT INTO `habits` (`habit_id`, `user_id`, `habit_name`, `target_frequency`, `completion_status`, `habit_date`, `notes`, `created_at`) VALUES
(1, 5, 'Drink 2L Water', 'Daily', 'Completed', '2026-08-18', 'Feeling more energetic', '2026-08-18 01:36:14'),
(2, 5, 'Read 20 Pages', 'Daily', 'Pending', '2026-08-18', 'Fighting!', '2026-08-18 01:36:14'),
(3, 5, 'Gym Workout', '3x per week', 'Pending', '2026-08-18', 'Leg day', '2026-08-18 01:36:14'),
(4, 4, 'Eat snack', '2 times', 'Pending', '2026-08-11', 'No junk food', '2026-08-18 01:40:16'),
(7, 4, 'Eat snack', 'Daily', 'Pending', '2026-08-29', 'yay', '2026-08-18 06:05:55');

-- --------------------------------------------------------

--
-- Table structure for table `money_goals`
--

CREATE TABLE `money_goals` (
  `money_goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goal_amount` decimal(10,2) NOT NULL,
  `goal_month` int(11) NOT NULL,
  `goal_year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `money_goals`
--

INSERT INTO `money_goals` (`money_goal_id`, `user_id`, `goal_amount`, `goal_month`, `goal_year`) VALUES
(1, 4, 111.00, 8, 2026);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`reset_id`, `email`, `token`, `created_at`) VALUES
(1, 'qt@gmaul.com', '947a410f3983b3a1394a56a698834be98b6b6f4aaf66dc9bdf6cf33cd44e943f', '2026-08-17 08:52:48');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `type`, `amount`, `category`, `description`, `transaction_date`, `created_at`) VALUES
(1, 4, 'expense', 111.00, 'Food', 'Bread', '2026-08-05', '2026-08-18 06:30:30'),
(3, 4, 'income', 1223.00, 'Allowance', 'Yay yay', '2026-08-18', '2026-08-18 06:32:10');

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
  `reg_date` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `username`, `full_name`, `email`, `password`, `reg_date`, `last_login`) VALUES
(4, 'admin', 'TKH', 'TKH', 'tkh@gmail.com', 'caad76e0fa34e51d06622d2e5c1b8f30', '2026-08-09 16:32:10', '2026-08-18 23:12:11'),
(5, 'student', 'qt', 'qt', 'qt@gmaul.com', 'e85823b4e7db1064f4301e1c74978199', '2026-08-09 17:34:21', '2026-08-18 20:43:29'),
(6, 'student', 'a', 'a', 'a@gmail.com', '0cc175b9c0f1b6a831c399e269772661', '2026-08-18 22:08:55', '2026-08-18 22:09:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `diary`
--
ALTER TABLE `diary`
  ADD PRIMARY KEY (`diary_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `exercise`
--
ALTER TABLE `exercise`
  ADD PRIMARY KEY (`exercise_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `exercise_goals`
--
ALTER TABLE `exercise_goals`
  ADD PRIMARY KEY (`exercise_goal_id`),
  ADD UNIQUE KEY `unique_user_goal` (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`habit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `money_goals`
--
ALTER TABLE `money_goals`
  ADD PRIMARY KEY (`money_goal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `password_resets_email_fk` (`email`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `diary`
--
ALTER TABLE `diary`
  MODIFY `diary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exercise`
--
ALTER TABLE `exercise`
  MODIFY `exercise_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `exercise_goals`
--
ALTER TABLE `exercise_goals`
  MODIFY `exercise_goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `habits`
--
ALTER TABLE `habits`
  MODIFY `habit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `money_goals`
--
ALTER TABLE `money_goals`
  MODIFY `money_goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `diary`
--
ALTER TABLE `diary`
  ADD CONSTRAINT `diary_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exercise`
--
ALTER TABLE `exercise`
  ADD CONSTRAINT `exercise_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exercise_goals`
--
ALTER TABLE `exercise_goals`
  ADD CONSTRAINT `exercise_goals_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `habits`
--
ALTER TABLE `habits`
  ADD CONSTRAINT `habits_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `money_goals`
--
ALTER TABLE `money_goals`
  ADD CONSTRAINT `money_goals_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_email_fk` FOREIGN KEY (`email`) REFERENCES `users` (`email`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_trans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
