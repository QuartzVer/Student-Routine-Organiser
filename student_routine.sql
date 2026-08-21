-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2026 at 10:31 AM
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
(5, 5, 'Good day?', 'Yup good day', 'Happy', '2026-08-21'),
(9, 5, 'Feeling great', 'Had a good lunch', 'Neutral', '2026-08-10'),
(10, 7, 'Good day', 'Had a productive day today', 'Happy', '2026-08-20'),
(11, 7, 'Tired today', 'Feeling a little tired but okay', 'Neutral', '2026-08-19'),
(12, 8, 'Nice day', 'Everything went well today', 'Happy', '2026-08-20'),
(13, 8, 'Busy day', 'Had many things to do today', 'Neutral', '2026-08-18');

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
(20, 5, 'Jog', 1, 2, 'Low', '2026-08-18', 'Missed'),
(21, 5, 'Jog', 2, 3, 'Medium', '2026-08-19', 'Completed'),
(26, 5, 'Cycling', 1, 1, 'Low', '2026-08-13', 'Missed'),
(34, 5, 'Jog', 67, 1498, 'High', '2026-08-20', 'Completed'),
(35, 5, 'Gym', 22, 237, 'Low', '2026-08-21', 'Completed'),
(47, 7, 'Jog', 30, 250, 'Medium', '2026-08-18', 'Completed'),
(48, 7, 'Cycling', 45, 350, 'High', '2026-08-19', 'Completed'),
(49, 7, 'Gym', 40, 300, 'Medium', '2026-08-20', 'Completed'),
(50, 8, 'Jog', 20, 150, 'Low', '2026-08-18', 'Completed'),
(51, 8, 'Gym', 35, 280, 'Medium', '2026-08-19', 'Missed'),
(52, 8, 'Cycling', 30, 220, 'Medium', '2026-08-20', 'Completed'),
(53, 5, 'Jog', 20, 100, 'Medium', '2026-08-21', 'Scheduled');

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
(2, 5, 'monthly', 2000),
(4, 7, 'weekly', 1500),
(5, 8, 'monthly', 5000);

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
(1, 5, 'Drink 2L Water', 'Daily', 'Completed', '2026-08-18', 'Feeling more energetic', '2026-08-20 16:06:22'),
(2, 5, 'Read 20 Pages', 'Daily', 'Pending', '2026-08-18', 'Fighting!', '2026-08-20 16:06:22'),
(3, 5, 'Gym Workout', '3x per week', 'Pending', '2026-08-18', 'Leg day', '2026-08-20 16:06:22'),
(10, 5, 'Eat snack', 'Daily', 'Completed', '2026-08-20', '', '2026-08-20 16:08:37'),
(11, 5, 'Eat snack', '2 times', 'Pending', '2026-08-20', '', '2026-08-20 16:09:22'),
(12, 5, 'Eat snack', 'Many times', 'Completed', '2026-08-21', '', '2026-08-20 16:09:38'),
(13, 7, 'Drink 2L Water', 'Daily', 'Completed', '2026-08-18', 'Feeling good', '2026-08-20 16:18:03'),
(14, 7, 'Read 20 Pages', 'Daily', 'Pending', '2026-08-19', 'Need to finish reading', '2026-08-20 16:18:03'),
(15, 7, 'Morning Exercise', '3x per week', 'Completed', '2026-08-20', 'Good workout', '2026-08-20 16:18:03'),
(16, 8, 'Drink 2L Water', 'Daily', 'Completed', '2026-08-18', 'Stayed hydrated', '2026-08-20 16:18:03'),
(17, 8, 'Read 20 Pages', 'Daily', 'Pending', '2026-08-19', 'Will continue tomorrow', '2026-08-20 16:18:03'),
(18, 8, 'Exercise', '3x per week', 'Pending', '2026-08-20', 'Need more consistency', '2026-08-20 16:18:03');

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
(2, 5, 100.00, 8, 2026),
(3, 7, 200.00, 8, 2026),
(4, 8, 150.00, 8, 2026);

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
(5, 'qt@gmail.com', '62695974f9e2dc0d23d9377d8dc60c0ed3ff087ecb35c96c04e39e5e84d840ba', '2026-08-20 16:18:34'),
(7, 'abc@gmail.com', '9c00faf48e4b91627b74c118e536c56971f6ba4dc0fcf5faa623759e1cda2d88', '2026-08-20 17:42:05');

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
(6, 5, 'expense', 2.50, 'Food', 'yummy', '2026-08-20', '2026-08-20 16:07:42'),
(7, 5, 'income', 300.00, 'Part Time', 'Good job\r\n', '2026-08-20', '2026-08-20 16:08:00'),
(8, 5, 'expense', 12.00, 'Stationery', '', '2026-08-20', '2026-08-20 16:10:59'),
(9, 7, 'expense', 15.00, 'Food', 'Lunch', '2026-08-18', '2026-08-20 16:18:03'),
(10, 7, 'income', 250.00, 'Allowance', 'Monthly allowance', '2026-08-19', '2026-08-20 16:18:03'),
(11, 7, 'expense', 20.00, 'Stationery', 'Bought notebooks', '2026-08-20', '2026-08-20 16:18:03'),
(12, 8, 'expense', 10.00, 'Food', 'Breakfast', '2026-08-18', '2026-08-20 16:18:03'),
(13, 8, 'income', 300.00, 'Allowance', 'Monthly allowance', '2026-08-19', '2026-08-20 16:18:03'),
(14, 8, 'expense', 25.00, 'Transport', 'Bus and train', '2026-08-20', '2026-08-20 16:18:03');

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
(5, 'admin', 'qt', 'qt', 'qt@gmail.com', 'e85823b4e7db1064f4301e1c74978199', '2026-08-09 17:34:21', '2026-08-21 14:33:03'),
(7, 'student', 'oreo', 'oreo cookie cream', 'oreo@gmail.com', 'd0585ffddbc10793f5e3817424f08fa4', '2026-08-20 16:39:05', '2026-08-21 02:10:22'),
(8, 'student', '123', '123', '123@email.com', 'c06bd8424a3a2e717ffae750e0bd2fed', '2026-08-20 23:42:08', '2026-08-20 23:42:21'),
(9, 'student', 'abc', 'abc', 'abc@gmail.com', 'c06bd8424a3a2e717ffae750e0bd2fed', '2026-08-21 00:33:29', NULL),
(10, 'student', 'abcd', 'abcd', 'abcd@gmail.com', 'c06bd8424a3a2e717ffae750e0bd2fed', '2026-08-21 00:39:20', NULL);

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
  MODIFY `diary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `exercise`
--
ALTER TABLE `exercise`
  MODIFY `exercise_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `exercise_goals`
--
ALTER TABLE `exercise_goals`
  MODIFY `exercise_goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `habits`
--
ALTER TABLE `habits`
  MODIFY `habit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `money_goals`
--
ALTER TABLE `money_goals`
  MODIFY `money_goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  ADD CONSTRAINT `password_resets_email_fk` FOREIGN KEY (`email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_trans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
