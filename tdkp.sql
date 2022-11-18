-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 18, 2022 at 05:32 PM
-- Server version: 8.0.21
-- PHP Version: 7.0.33-0+deb9u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tdkp`
--
CREATE DATABASE IF NOT EXISTS `tdkp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tdkp`;

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
CREATE TABLE `applicants` (
  `id` int NOT NULL,
  `loot` int NOT NULL,
  `server` int NOT NULL,
  `clan` int NOT NULL,
  `user` int NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `applicants`:
--   `loot`
--       `loot` -> `id`
--   `user`
--       `users` -> `id`
--   `server`
--       `servers` -> `id`
--   `clan`
--       `clans` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int NOT NULL,
  `event` int NOT NULL,
  `boss` int NOT NULL,
  `server` int NOT NULL,
  `clan` int NOT NULL,
  `user` int NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `attendance`:
--   `event`
--       `events` -> `id`
--   `user`
--       `users` -> `id`
--   `server`
--       `servers` -> `id`
--   `boss`
--       `bosses` -> `id`
--   `clan`
--       `clans` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `bosses`
--

DROP TABLE IF EXISTS `bosses`;
CREATE TABLE `bosses` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `respawn` int NOT NULL,
  `restart` int NOT NULL DEFAULT '0',
  `dkp` int NOT NULL DEFAULT '5',
  `chance` int DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_spawn_E1` datetime DEFAULT NULL,
  `next_spawn_E1` datetime DEFAULT NULL,
  `last_spawn_E2` datetime DEFAULT NULL,
  `next_spawn_E2` datetime DEFAULT NULL,
  `last_spawn_E3` datetime DEFAULT NULL,
  `next_spawn_E3` datetime DEFAULT NULL,
  `last_spawn_E4` datetime DEFAULT NULL,
  `next_spawn_E4` datetime DEFAULT NULL,
  `last_spawn_E5` datetime DEFAULT NULL,
  `next_spawn_E5` datetime DEFAULT NULL,
  `last_spawn_E6` datetime DEFAULT NULL,
  `next_spawn_E6` datetime DEFAULT NULL,
  `last_spawn_L1` datetime DEFAULT NULL,
  `next_spawn_L1` datetime DEFAULT NULL,
  `last_spawn_L2` datetime DEFAULT NULL,
  `next_spawn_L2` datetime DEFAULT NULL,
  `last_spawn_L3` datetime DEFAULT NULL,
  `next_spawn_L3` datetime DEFAULT NULL,
  `last_spawn_L4` datetime DEFAULT NULL,
  `next_spawn_L4` datetime DEFAULT NULL,
  `last_spawn_L5` datetime DEFAULT NULL,
  `next_spawn_L5` datetime DEFAULT NULL,
  `last_spawn_L6` datetime DEFAULT NULL,
  `next_spawn_L6` datetime DEFAULT NULL,
  `last_spawn_Z1` datetime DEFAULT NULL,
  `next_spawn_Z1` datetime DEFAULT NULL,
  `last_spawn_Z2` datetime DEFAULT NULL,
  `next_spawn_Z2` datetime DEFAULT NULL,
  `last_spawn_Z3` datetime DEFAULT NULL,
  `next_spawn_Z3` datetime DEFAULT NULL,
  `last_spawn_Z4` datetime DEFAULT NULL,
  `next_spawn_Z4` datetime DEFAULT NULL,
  `last_spawn_Z5` datetime DEFAULT NULL,
  `next_spawn_Z5` datetime DEFAULT NULL,
  `last_spawn_Z6` datetime DEFAULT NULL,
  `next_spawn_Z6` datetime DEFAULT NULL,
  `last_spawn_B1` datetime DEFAULT NULL,
  `next_spawn_B1` datetime DEFAULT NULL,
  `last_spawn_B2` datetime DEFAULT NULL,
  `next_spawn_B2` datetime DEFAULT NULL,
  `last_spawn_B3` datetime DEFAULT NULL,
  `next_spawn_B3` datetime DEFAULT NULL,
  `last_spawn_B4` datetime DEFAULT NULL,
  `next_spawn_B4` datetime DEFAULT NULL,
  `last_spawn_B5` datetime DEFAULT NULL,
  `next_spawn_B5` datetime DEFAULT NULL,
  `last_spawn_B6` datetime DEFAULT NULL,
  `next_spawn_B6` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `bosses`:
--

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

DROP TABLE IF EXISTS `channels`;
CREATE TABLE `channels` (
  `id` int NOT NULL,
  `server` int NOT NULL,
  `type` enum('boss','drop','loot') COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_code` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `channels`:
--   `server`
--       `servers` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `clans`
--

DROP TABLE IF EXISTS `clans`;
CREATE TABLE `clans` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server` int DEFAULT NULL,
  `total_bosses` int NOT NULL DEFAULT '0',
  `total_bosses_last` int NOT NULL DEFAULT '0',
  `total_bosses_epic` int NOT NULL DEFAULT '0',
  `total_bosses_epic_last` int NOT NULL DEFAULT '0',
  `total_items` int NOT NULL DEFAULT '0',
  `total_items_last` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `clans`:
--   `server`
--       `servers` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_code` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `classes`:
--

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
CREATE TABLE `drivers` (
  `id` int NOT NULL,
  `dsk_id` bigint NOT NULL,
  `adsk_id` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `drivers`:
--

-- --------------------------------------------------------

--
-- Table structure for table `droplist`
--

DROP TABLE IF EXISTS `droplist`;
CREATE TABLE `droplist` (
  `id` int NOT NULL,
  `boss` int NOT NULL,
  `item` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `droplist`:
--   `boss`
--       `bosses` -> `id`
--   `item`
--       `items` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int NOT NULL,
  `server` int DEFAULT NULL,
  `boss` int NOT NULL,
  `admin` int DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `awakened` tinyint(1) NOT NULL DEFAULT '0',
  `checked_id` int DEFAULT NULL,
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `pvp_id` int DEFAULT NULL,
  `pvp` tinyint(1) NOT NULL DEFAULT '0',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `close` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `events`:
--   `admin`
--       `users` -> `id`
--   `boss`
--       `bosses` -> `id`
--   `server`
--       `servers` -> `id`
--   `checked_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `holdings`
--

DROP TABLE IF EXISTS `holdings`;
CREATE TABLE `holdings` (
  `id` int NOT NULL,
  `user` int NOT NULL,
  `item` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `holdings`:
--

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rarity` enum('Другое','Редкий','Героический','Легендарный') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Другое',
  `dkp` int NOT NULL DEFAULT '5',
  `type` enum('Надеваемое','Скилл','Оружие','Душа') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class` int DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `items`:
--   `class`
--       `classes` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `loot`
--

DROP TABLE IF EXISTS `loot`;
CREATE TABLE `loot` (
  `id` int NOT NULL,
  `server` int DEFAULT NULL,
  `item` int NOT NULL,
  `event` int DEFAULT NULL,
  `clan` int NOT NULL,
  `user` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `points` int DEFAULT NULL,
  `admin` int NOT NULL,
  `salary` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `checked_id` int DEFAULT NULL,
  `discount` tinyint(1) NOT NULL DEFAULT '0',
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `loot`:
--   `admin`
--       `users` -> `id`
--   `user`
--       `users` -> `id`
--   `event`
--       `events` -> `id`
--   `item`
--       `items` -> `id`
--   `server`
--       `servers` -> `id`
--   `clan`
--       `clans` -> `id`
--   `checked_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

DROP TABLE IF EXISTS `parties`;
CREATE TABLE `parties` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `server` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `parties`:
--   `server`
--       `servers` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `points`
--

DROP TABLE IF EXISTS `points`;
CREATE TABLE `points` (
  `id` int NOT NULL,
  `type` enum('Penalty','Award','Transaction','Event','Bonus') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Event',
  `user` int NOT NULL,
  `event` int DEFAULT NULL,
  `admin` int NOT NULL,
  `dkp` int NOT NULL DEFAULT '2',
  `multiplier` float DEFAULT '1',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `points`:
--   `admin`
--       `users` -> `id`
--   `event`
--       `events` -> `id`
--   `user`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_code` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `roles`:
--

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
CREATE TABLE `servers` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_code` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `servers`:
--

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE `skills` (
  `id` int NOT NULL,
  `user` int NOT NULL,
  `item` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `skills`:
--

-- --------------------------------------------------------

--
-- Table structure for table `souls`
--

DROP TABLE IF EXISTS `souls`;
CREATE TABLE `souls` (
  `id` int NOT NULL,
  `user` int NOT NULL,
  `soul` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `souls`:
--   `soul`
--       `items` -> `id`
--   `user`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `timezone`
--

DROP TABLE IF EXISTS `timezone`;
CREATE TABLE `timezone` (
  `id` int NOT NULL,
  `label` varchar(67) DEFAULT NULL,
  `value` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONSHIPS FOR TABLE `timezone`:
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL,
  `dsk_id` bigint NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dkp_rating` int NOT NULL DEFAULT '0',
  `dkp` int DEFAULT '0',
  `level` int NOT NULL DEFAULT '60',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server` int DEFAULT NULL,
  `clan` int DEFAULT NULL,
  `class` int DEFAULT NULL,
  `party` int DEFAULT NULL,
  `dpl` tinyint(1) NOT NULL DEFAULT '0',
  `adm` tinyint(1) NOT NULL DEFAULT '0',
  `md` tinyint(1) NOT NULL DEFAULT '0',
  `rating_ps` int NOT NULL DEFAULT '0',
  `rating_ps_class` int NOT NULL DEFAULT '0',
  `ps` int NOT NULL DEFAULT '0',
  `defence` int NOT NULL DEFAULT '0',
  `reduction` int NOT NULL DEFAULT '0',
  `resistance` int NOT NULL DEFAULT '0',
  `seal` int NOT NULL DEFAULT '0',
  `awakening` int NOT NULL DEFAULT '0',
  `souls` int NOT NULL DEFAULT '0',
  `mastery` int NOT NULL DEFAULT '0',
  `heroes_all` int NOT NULL DEFAULT '0',
  `agations_all` int NOT NULL DEFAULT '0',
  `agations_hr` int NOT NULL DEFAULT '0',
  `agations_lg` int NOT NULL DEFAULT '0',
  `points_cards` int NOT NULL DEFAULT '0',
  `heroes_hr` int NOT NULL DEFAULT '0',
  `heroes_lg` int NOT NULL DEFAULT '0',
  `rating_cards` int NOT NULL DEFAULT '0',
  `collections` int NOT NULL DEFAULT '0',
  `rating_collections` int NOT NULL DEFAULT '0',
  `rating_bonus` int NOT NULL DEFAULT '0',
  `points_bonus` int NOT NULL DEFAULT '0',
  `rating_boss` int NOT NULL DEFAULT '0',
  `total_bosses` int NOT NULL DEFAULT '0',
  `total_bosses_last` int NOT NULL DEFAULT '0',
  `total_bosses_epic` int NOT NULL DEFAULT '0',
  `total_bosses_epic_last` int NOT NULL DEFAULT '0',
  `total_items` int NOT NULL DEFAULT '0',
  `total_items_last` int NOT NULL DEFAULT '0',
  `lead` tinyint(1) NOT NULL DEFAULT '0',
  `additional` tinyint(1) NOT NULL DEFAULT '0',
  `ready` tinyint(1) NOT NULL DEFAULT '0',
  `prime` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `timezone` int NOT NULL DEFAULT '45',
  `last_login` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `users`:
--   `clan`
--       `clans` -> `id`
--   `class`
--       `classes` -> `id`
--   `party`
--       `parties` -> `id`
--   `server`
--       `servers` -> `id`
--   `timezone`
--       `timezone` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` int NOT NULL,
  `user` int NOT NULL,
  `item` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `wishlist`:
--   `user`
--       `users` -> `id`
--   `item`
--       `items` -> `id`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`loot`,`user`) USING BTREE,
  ADD KEY `user` (`user`),
  ADD KEY `server` (`server`),
  ADD KEY `id` (`id`),
  ADD KEY `clan` (`clan`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`event`,`user`),
  ADD KEY `attendance_ibfk_2` (`user`),
  ADD KEY `server` (`server`),
  ADD KEY `id` (`id`),
  ADD KEY `boss` (`boss`),
  ADD KEY `clan` (`clan`);

--
-- Indexes for table `bosses`
--
ALTER TABLE `bosses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server` (`server`);

--
-- Indexes for table `clans`
--
ALTER TABLE `clans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`server`,`name`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adsk_id` (`adsk_id`),
  ADD KEY `dsk_id` (`dsk_id`);

--
-- Indexes for table `droplist`
--
ALTER TABLE `droplist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`boss`,`item`),
  ADD KEY `droplist_ibfk_2` (`item`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_ibfk_1` (`admin`),
  ADD KEY `events_ibfk_2` (`boss`),
  ADD KEY `server` (`server`),
  ADD KEY `checked_id` (`checked_id`);

--
-- Indexes for table `holdings`
--
ALTER TABLE `holdings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`user`,`item`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `class` (`class`);

--
-- Indexes for table `loot`
--
ALTER TABLE `loot`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`item`,`event`),
  ADD KEY `loot_ibfk_1` (`admin`),
  ADD KEY `loot_ibfk_3` (`user`),
  ADD KEY `loot_ibfk_4` (`event`),
  ADD KEY `server` (`server`),
  ADD KEY `clan_from` (`clan`),
  ADD KEY `checked_id` (`checked_id`);

--
-- Indexes for table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `server` (`server`);

--
-- Indexes for table `points`
--
ALTER TABLE `points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`user`,`event`),
  ADD KEY `points_ibfk_1` (`admin`),
  ADD KEY `points_ibfk_2` (`event`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`user`,`item`);

--
-- Indexes for table `souls`
--
ALTER TABLE `souls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`,`soul`) USING BTREE,
  ADD KEY `soul` (`soul`);

--
-- Indexes for table `timezone`
--
ALTER TABLE `timezone`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dsk_id` (`dsk_id`),
  ADD KEY `users_ibfk_1` (`clan`),
  ADD KEY `users_ibfk_2` (`class`),
  ADD KEY `users_ibfk_3` (`party`),
  ADD KEY `users_ibfk_4` (`server`),
  ADD KEY `timezone` (`timezone`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `item` (`item`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bosses`
--
ALTER TABLE `bosses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clans`
--
ALTER TABLE `clans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `droplist`
--
ALTER TABLE `droplist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holdings`
--
ALTER TABLE `holdings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loot`
--
ALTER TABLE `loot`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parties`
--
ALTER TABLE `parties`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `points`
--
ALTER TABLE `points`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servers`
--
ALTER TABLE `servers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `souls`
--
ALTER TABLE `souls`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timezone`
--
ALTER TABLE `timezone`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applicants`
--
ALTER TABLE `applicants`
  ADD CONSTRAINT `applicants_ibfk_1` FOREIGN KEY (`loot`) REFERENCES `loot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `applicants_ibfk_3` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `applicants_ibfk_4` FOREIGN KEY (`server`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `applicants_ibfk_5` FOREIGN KEY (`clan`) REFERENCES `clans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`event`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`server`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_4` FOREIGN KEY (`boss`) REFERENCES `bosses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_5` FOREIGN KEY (`clan`) REFERENCES `clans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `channels`
--
ALTER TABLE `channels`
  ADD CONSTRAINT `channels_ibfk_1` FOREIGN KEY (`server`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `clans`
--
ALTER TABLE `clans`
  ADD CONSTRAINT `clans_ibfk_1` FOREIGN KEY (`server`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `droplist`
--
ALTER TABLE `droplist`
  ADD CONSTRAINT `droplist_ibfk_1` FOREIGN KEY (`boss`) REFERENCES `bosses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `droplist_ibfk_2` FOREIGN KEY (`item`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`admin`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`boss`) REFERENCES `bosses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`server`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_4` FOREIGN KEY (`checked_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`class`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `loot`
--
ALTER TABLE `loot`
  ADD CONSTRAINT `loot_ibfk_1` FOREIGN KEY (`admin`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loot_ibfk_3` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loot_ibfk_4` FOREIGN KEY (`event`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loot_ibfk_5` FOREIGN KEY (`item`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loot_ibfk_7` FOREIGN KEY (`server`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loot_ibfk_8` FOREIGN KEY (`clan`) REFERENCES `clans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loot_ibfk_9` FOREIGN KEY (`checked_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `parties`
--
ALTER TABLE `parties`
  ADD CONSTRAINT `parties_ibfk_1` FOREIGN KEY (`server`) REFERENCES `servers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `points`
--
ALTER TABLE `points`
  ADD CONSTRAINT `points_ibfk_1` FOREIGN KEY (`admin`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `points_ibfk_2` FOREIGN KEY (`event`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `points_ibfk_3` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `souls`
--
ALTER TABLE `souls`
  ADD CONSTRAINT `souls_ibfk_1` FOREIGN KEY (`soul`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `souls_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`clan`) REFERENCES `clans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`class`) REFERENCES `classes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`party`) REFERENCES `parties` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_4` FOREIGN KEY (`server`) REFERENCES `servers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_5` FOREIGN KEY (`timezone`) REFERENCES `timezone` (`id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`item`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
