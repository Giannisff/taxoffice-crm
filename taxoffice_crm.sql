-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: sdb-76.hosting.stackcp.net
-- Χρόνος δημιουργίας: 17 Δεκ 2025 στις 12:49:42
-- Έκδοση διακομιστή: 10.6.18-MariaDB-log
-- Έκδοση PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `taxoffice_crm-353037334a98`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `active_professionals`
--

DROP TABLE IF EXISTS `active_professionals`;
CREATE TABLE IF NOT EXISTS `active_professionals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `has_vat` tinyint(1) NOT NULL DEFAULT 0,
  `vat_type` enum('Μηνιαίο','Τριμηνιαίο') DEFAULT NULL,
  `has_payroll` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `afm` varchar(20) DEFAULT NULL,
  `category` enum('Ιδιώτης','Επιχείρηση','Λοιπά') DEFAULT 'Ιδιώτης',
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `diary_events`
--

DROP TABLE IF EXISTS `diary_events`;
CREATE TABLE IF NOT EXISTS `diary_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_diary_events_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `diary_notes`
--

DROP TABLE IF EXISTS `diary_notes`;
CREATE TABLE IF NOT EXISTS `diary_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `note_date` date NOT NULL,
  `content` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_date` (`user_id`,`note_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_messages_sender` (`sender_id`),
  KEY `fk_messages_receiver` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `partners`
--

DROP TABLE IF EXISTS `partners`;
CREATE TABLE IF NOT EXISTS `partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `tasks`
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `client_id` int(11) NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Σε εξέλιξη',
  `fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `collected` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tasks_client` (`client_id`),
  KEY `fk_tasks_partner` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Περιορισμοί για άχρηστους πίνακες
--

--
-- Περιορισμοί για πίνακα `diary_events`
--
ALTER TABLE `diary_events`
  ADD CONSTRAINT `fk_diary_events_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `diary_notes`
--
ALTER TABLE `diary_notes`
  ADD CONSTRAINT `fk_diary_notes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tasks_partner` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
