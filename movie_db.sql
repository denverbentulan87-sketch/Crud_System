-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2026 at 02:55 AM
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
-- Database: `movie_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `movie_watchlist`
--

CREATE TABLE `movie_watchlist` (
  `watchlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_title` varchar(225) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT 'To Watch',
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `date_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movie_watchlist`
--

INSERT INTO `movie_watchlist` (`watchlist_id`, `user_id`, `movie_title`, `genre`, `status`, `rating`, `date_added`) VALUES
(11, 1, 'One Piece', 'Action', 'watching', 5, '2026-04-20 09:27:23'),
(12, 1, 'Spider Man', 'Action', 'watching', 5, '2026-04-20 09:28:14'),
(13, 1, 'Ant Man', 'Fiction', 'watching', 1, '2026-04-20 09:37:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'nathaliejones007@gmail.com', '$2y$10$0l1rsuCSoQ7MUcxMSNUAiOp6r53wLBZPObQll79fr87enqRh2qi9m'),
(4, 'Denver Bentulan', '$2y$10$mu9seFmadiq7.hlXv/oeSubr9y4TmqwrDu8CPbJ4mysVP472LlcFi');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movie_watchlist`
--
ALTER TABLE `movie_watchlist`
  ADD PRIMARY KEY (`watchlist_id`);

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
-- AUTO_INCREMENT for table `movie_watchlist`
--
ALTER TABLE `movie_watchlist`
  MODIFY `watchlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
