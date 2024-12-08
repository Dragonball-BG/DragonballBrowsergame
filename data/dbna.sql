-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 08. Dez 2024 um 01:05
-- Server-Version: 10.4.24-MariaDB
-- PHP-Version: 8.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `dragonball`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attitudes`
--

CREATE TABLE `attitudes` (
  `attitude_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `attitude_effect`
--

CREATE TABLE `attitude_effect` (
  `attitude_id` int(10) UNSIGNED NOT NULL,
  `effect_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `betakey`
--

CREATE TABLE `betakey` (
  `key_id` int(10) UNSIGNED NOT NULL,
  `key_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `used_by` int(10) UNSIGNED DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `characters`
--

CREATE TABLE `characters` (
  `character_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exp` int(11) DEFAULT 0,
  `level` int(11) DEFAULT 1,
  `defense` int(11) DEFAULT 0,
  `ki` int(11) DEFAULT 400,
  `fame` int(10) UNSIGNED DEFAULT 0,
  `health` int(11) DEFAULT 400,
  `max_health` int(11) DEFAULT 400,
  `mana` int(11) DEFAULT 400,
  `max_mana` int(11) DEFAULT 400,
  `zeni` int(11) DEFAULT 0,
  `race_id` int(10) UNSIGNED NOT NULL,
  `attitude_id` int(10) UNSIGNED DEFAULT NULL,
  `location_id` int(10) UNSIGNED DEFAULT NULL,
  `alive` tinyint(1) NOT NULL DEFAULT 1,
  `death_time` datetime DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `train_item_id` int(10) UNSIGNED DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `quest_points` int(10) UNSIGNED DEFAULT 10,
  `quest_points_last_refill` datetime DEFAULT current_timestamp(),
  `last_quest_point_refill` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `character_inventory`
--

CREATE TABLE `character_inventory` (
  `inventory_id` int(10) UNSIGNED NOT NULL,
  `character_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `character_npc_relationships`
--

CREATE TABLE `character_npc_relationships` (
  `id` int(10) UNSIGNED NOT NULL,
  `character_id` int(10) UNSIGNED NOT NULL,
  `npc_id` int(10) UNSIGNED NOT NULL,
  `relationship_points` int(11) NOT NULL DEFAULT 0,
  `last_interaction` datetime DEFAULT current_timestamp(),
  `last_relationship_gain` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `character_quests`
--

CREATE TABLE `character_quests` (
  `character_quest_id` int(10) UNSIGNED NOT NULL,
  `character_id` int(10) UNSIGNED NOT NULL,
  `quest_id` int(10) UNSIGNED NOT NULL,
  `quest_type` enum('main','side') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` datetime DEFAULT current_timestamp(),
  `completed_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `fail_time` datetime DEFAULT NULL,
  `fail_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`progress`)),
  `status` enum('ongoing','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `effects`
--

CREATE TABLE `effects` (
  `effect_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `items`
--

CREATE TABLE `items` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `effect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `levels`
--

CREATE TABLE `levels` (
  `level` int(10) UNSIGNED NOT NULL,
  `exp_required` int(10) UNSIGNED NOT NULL,
  `health_bonus` int(11) NOT NULL,
  `mana_bonus` int(11) NOT NULL,
  `ki_bonus` int(11) NOT NULL,
  `fame_bonus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `locations`
--

CREATE TABLE `locations` (
  `location_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `planet_id` int(10) UNSIGNED NOT NULL,
  `shop_data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `main_story_quests`
--

CREATE TABLE `main_story_quests` (
  `quest_id` int(10) UNSIGNED NOT NULL,
  `race_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_required` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `fame_required` int(10) UNSIGNED DEFAULT 0,
  `exp_reward` int(10) UNSIGNED NOT NULL,
  `fame_reward` int(10) UNSIGNED DEFAULT 0,
  `zeni_reward` int(10) UNSIGNED DEFAULT 0,
  `health_reward` int(10) UNSIGNED DEFAULT 0,
  `mana_reward` int(10) UNSIGNED DEFAULT 0,
  `ki_reward` int(10) UNSIGNED DEFAULT 0,
  `location_required` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_time_based` tinyint(1) DEFAULT 0,
  `time_limit_seconds` int(11) DEFAULT NULL,
  `prerequisites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prerequisites`)),
  `order` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_main_story_image.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `news`
--

CREATE TABLE `news` (
  `news_id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `npcs`
--

CREATE TABLE `npcs` (
  `npc_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location_id` int(10) UNSIGNED NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_fightable` tinyint(1) DEFAULT 0,
  `is_hostile` tinyint(1) DEFAULT 0,
  `health` int(10) UNSIGNED DEFAULT 100,
  `max_health` int(10) UNSIGNED DEFAULT 100,
  `ki` int(10) UNSIGNED DEFAULT 50,
  `rewards` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `hostile_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`hostile_condition`)),
  `item_required` text DEFAULT NULL,
  `quest_required` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `npc_dialogues`
--

CREATE TABLE `npc_dialogues` (
  `dialogue_id` int(10) UNSIGNED NOT NULL,
  `npc_id` int(10) UNSIGNED NOT NULL,
  `dialogue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship_level` enum('vertraut','freundlich','neutral','misstrauisch') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `npc_quests`
--

CREATE TABLE `npc_quests` (
  `npc_quest_id` int(10) UNSIGNED NOT NULL,
  `npc_id` int(10) UNSIGNED NOT NULL,
  `quest_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `planets`
--

CREATE TABLE `planets` (
  `planet_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reachable_by_spaceship` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `quest_conditions`
--

CREATE TABLE `quest_conditions` (
  `condition_id` int(10) UNSIGNED NOT NULL,
  `quest_id` int(10) UNSIGNED NOT NULL,
  `quest_type` enum('main','side') NOT NULL,
  `condition_type` enum('level','fame','location','completed_quest','item') DEFAULT NULL,
  `condition_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `races`
--

CREATE TABLE `races` (
  `race_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `starting_point` int(11) NOT NULL,
  `positive_effects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`positive_effects`)),
  `negative_effects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`negative_effects`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `side_quests`
--

CREATE TABLE `side_quests` (
  `quest_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_side_quest_image.png',
  `level_required` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `fame_required` int(10) UNSIGNED DEFAULT 0,
  `exp_reward` int(10) UNSIGNED NOT NULL,
  `fame_reward` int(10) UNSIGNED DEFAULT 0,
  `zeni_reward` int(10) UNSIGNED DEFAULT 0,
  `relationship_required` int(10) UNSIGNED DEFAULT 0,
  `health_reward` int(10) UNSIGNED DEFAULT 0,
  `mana_reward` int(10) UNSIGNED DEFAULT 0,
  `ki_reward` int(10) UNSIGNED DEFAULT 0,
  `location_required` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_time_based` tinyint(1) DEFAULT 0,
  `time_limit_seconds` int(11) DEFAULT NULL,
  `prerequisites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prerequisites`)),
  `is_repeatable` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `completion_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Conditions for quest completion, such as items or fights' CHECK (json_valid(`completion_requirements`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `login_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `attitudes`
--
ALTER TABLE `attitudes`
  ADD PRIMARY KEY (`attitude_id`);

--
-- Indizes für die Tabelle `attitude_effect`
--
ALTER TABLE `attitude_effect`
  ADD PRIMARY KEY (`attitude_id`,`effect_id`),
  ADD KEY `effect_id` (`effect_id`);

--
-- Indizes für die Tabelle `betakey`
--
ALTER TABLE `betakey`
  ADD PRIMARY KEY (`key_id`),
  ADD UNIQUE KEY `key_value` (`key_value`),
  ADD KEY `used_by` (`used_by`);

--
-- Indizes für die Tabelle `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`character_id`),
  ADD KEY `race_id` (`race_id`),
  ADD KEY `attitude_id` (`attitude_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `train_item_id` (`train_item_id`);

--
-- Indizes für die Tabelle `character_inventory`
--
ALTER TABLE `character_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `character_id` (`character_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indizes für die Tabelle `character_npc_relationships`
--
ALTER TABLE `character_npc_relationships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_character_npc_relationship` (`character_id`,`npc_id`),
  ADD KEY `npc_id` (`npc_id`);

--
-- Indizes für die Tabelle `character_quests`
--
ALTER TABLE `character_quests`
  ADD PRIMARY KEY (`character_quest_id`),
  ADD KEY `character_id` (`character_id`),
  ADD KEY `quest_id` (`quest_id`);

--
-- Indizes für die Tabelle `effects`
--
ALTER TABLE `effects`
  ADD PRIMARY KEY (`effect_id`);

--
-- Indizes für die Tabelle `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indizes für die Tabelle `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`level`);

--
-- Indizes für die Tabelle `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `planet_id` (`planet_id`);

--
-- Indizes für die Tabelle `main_story_quests`
--
ALTER TABLE `main_story_quests`
  ADD PRIMARY KEY (`quest_id`),
  ADD KEY `race_id` (`race_id`);

--
-- Indizes für die Tabelle `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`news_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indizes für die Tabelle `npcs`
--
ALTER TABLE `npcs`
  ADD PRIMARY KEY (`npc_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indizes für die Tabelle `npc_dialogues`
--
ALTER TABLE `npc_dialogues`
  ADD PRIMARY KEY (`dialogue_id`),
  ADD KEY `npc_id` (`npc_id`);

--
-- Indizes für die Tabelle `npc_quests`
--
ALTER TABLE `npc_quests`
  ADD PRIMARY KEY (`npc_quest_id`),
  ADD KEY `npc_id` (`npc_id`),
  ADD KEY `quest_id` (`quest_id`);

--
-- Indizes für die Tabelle `planets`
--
ALTER TABLE `planets`
  ADD PRIMARY KEY (`planet_id`);

--
-- Indizes für die Tabelle `quest_conditions`
--
ALTER TABLE `quest_conditions`
  ADD PRIMARY KEY (`condition_id`),
  ADD KEY `quest_id` (`quest_id`);

--
-- Indizes für die Tabelle `races`
--
ALTER TABLE `races`
  ADD PRIMARY KEY (`race_id`);

--
-- Indizes für die Tabelle `side_quests`
--
ALTER TABLE `side_quests`
  ADD PRIMARY KEY (`quest_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `login_name` (`login_name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `attitudes`
--
ALTER TABLE `attitudes`
  MODIFY `attitude_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `betakey`
--
ALTER TABLE `betakey`
  MODIFY `key_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `characters`
--
ALTER TABLE `characters`
  MODIFY `character_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `character_inventory`
--
ALTER TABLE `character_inventory`
  MODIFY `inventory_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `character_npc_relationships`
--
ALTER TABLE `character_npc_relationships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `character_quests`
--
ALTER TABLE `character_quests`
  MODIFY `character_quest_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `effects`
--
ALTER TABLE `effects`
  MODIFY `effect_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `levels`
--
ALTER TABLE `levels`
  MODIFY `level` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `main_story_quests`
--
ALTER TABLE `main_story_quests`
  MODIFY `quest_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `news`
--
ALTER TABLE `news`
  MODIFY `news_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `npcs`
--
ALTER TABLE `npcs`
  MODIFY `npc_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `npc_dialogues`
--
ALTER TABLE `npc_dialogues`
  MODIFY `dialogue_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `npc_quests`
--
ALTER TABLE `npc_quests`
  MODIFY `npc_quest_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `planets`
--
ALTER TABLE `planets`
  MODIFY `planet_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `quest_conditions`
--
ALTER TABLE `quest_conditions`
  MODIFY `condition_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `races`
--
ALTER TABLE `races`
  MODIFY `race_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `side_quests`
--
ALTER TABLE `side_quests`
  MODIFY `quest_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `attitude_effect`
--
ALTER TABLE `attitude_effect`
  ADD CONSTRAINT `attitude_effect_ibfk_1` FOREIGN KEY (`attitude_id`) REFERENCES `attitudes` (`attitude_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attitude_effect_ibfk_2` FOREIGN KEY (`effect_id`) REFERENCES `effects` (`effect_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `betakey`
--
ALTER TABLE `betakey`
  ADD CONSTRAINT `betakey_ibfk_1` FOREIGN KEY (`used_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `races` (`race_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `characters_ibfk_2` FOREIGN KEY (`attitude_id`) REFERENCES `attitudes` (`attitude_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `characters_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `characters_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `characters_ibfk_5` FOREIGN KEY (`train_item_id`) REFERENCES `items` (`item_id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `character_inventory`
--
ALTER TABLE `character_inventory`
  ADD CONSTRAINT `character_inventory_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`character_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `character_inventory_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `character_npc_relationships`
--
ALTER TABLE `character_npc_relationships`
  ADD CONSTRAINT `character_npc_relationships_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`character_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `character_npc_relationships_ibfk_2` FOREIGN KEY (`npc_id`) REFERENCES `npcs` (`npc_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `character_quests`
--
ALTER TABLE `character_quests`
  ADD CONSTRAINT `character_quests_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`character_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `character_quests_ibfk_2` FOREIGN KEY (`quest_id`) REFERENCES `side_quests` (`quest_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`planet_id`) REFERENCES `planets` (`planet_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `main_story_quests`
--
ALTER TABLE `main_story_quests`
  ADD CONSTRAINT `main_story_quests_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `races` (`race_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `characters` (`character_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `npcs`
--
ALTER TABLE `npcs`
  ADD CONSTRAINT `npcs_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `npc_dialogues`
--
ALTER TABLE `npc_dialogues`
  ADD CONSTRAINT `npc_dialogues_ibfk_1` FOREIGN KEY (`npc_id`) REFERENCES `npcs` (`npc_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `npc_quests`
--
ALTER TABLE `npc_quests`
  ADD CONSTRAINT `npc_quests_ibfk_1` FOREIGN KEY (`npc_id`) REFERENCES `npcs` (`npc_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `npc_quests_ibfk_2` FOREIGN KEY (`quest_id`) REFERENCES `side_quests` (`quest_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `quest_conditions`
--
ALTER TABLE `quest_conditions`
  ADD CONSTRAINT `quest_conditions_ibfk_1` FOREIGN KEY (`quest_id`) REFERENCES `side_quests` (`quest_id`) ON DELETE CASCADE;
COMMIT;
