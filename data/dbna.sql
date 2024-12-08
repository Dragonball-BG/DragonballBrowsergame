-- Drop existing tables if they exist
DROP TABLE IF EXISTS message_recipient;
DROP TABLE IF EXISTS message;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS shop_item;
DROP TABLE IF EXISTS guild_character;
DROP TABLE IF EXISTS guilds;
DROP TABLE IF EXISTS characters;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS attitude_effect;
DROP TABLE IF EXISTS effects;
DROP TABLE IF EXISTS attitudes;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS planets;
DROP TABLE IF EXISTS races;
DROP TABLE IF EXISTS betakey;
DROP TABLE IF EXISTS users;

-- Users Table
CREATE TABLE users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login_name VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_locked BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Betakey Table
CREATE TABLE betakey (
    key_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_value VARCHAR(255) UNIQUE NOT NULL,
    used_by INT UNSIGNED NULL,
    used_at DATETIME NULL,
    is_used BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (used_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Races Table
CREATE TABLE races (
    race_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    picture VARCHAR(255) NOT NULL,
    starting_point INT NOT NULL,
    positive_effects JSON,
    negative_effects JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Planets Table
CREATE TABLE planets (
    planet_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    picture VARCHAR(255),
    reachable_by_spaceship BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Locations Table
CREATE TABLE locations (
    location_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    planet_id INT UNSIGNED NOT NULL,
    shop_data TEXT,
    FOREIGN KEY (planet_id) REFERENCES planets(planet_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attitudes Table
CREATE TABLE attitudes (
    attitude_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Effects Table
CREATE TABLE effects (
    effect_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attitude_Effect Table
CREATE TABLE attitude_effect (
    attitude_id INT UNSIGNED NOT NULL,
    effect_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (attitude_id, effect_id),
    FOREIGN KEY (attitude_id) REFERENCES attitudes(attitude_id) ON DELETE CASCADE,
    FOREIGN KEY (effect_id) REFERENCES effects(effect_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Items Table
CREATE TABLE items (
    item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    picture VARCHAR(255),
    type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    effect TEXT NOT NULL,
    cost INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Characters Table
CREATE TABLE characters (
    character_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    exp INT DEFAULT 0,
    level INT DEFAULT 1,
    defense INT DEFAULT 0,
    ki INT DEFAULT 400,
    fame INT UNSIGNED DEFAULT 0 AFTER ki;
    health INT DEFAULT 400,
    max_health INT DEFAULT 400,
    mana INT DEFAULT 400,
    max_mana INT DEFAULT 400,
    zeni INT DEFAULT 0,
    race_id INT UNSIGNED NOT NULL,
    attitude_id INT UNSIGNED,
    location_id INT UNSIGNED,
    alive BOOLEAN NOT NULL DEFAULT TRUE,
    death_time DATETIME DEFAULT NULL,
    user_id INT UNSIGNED NOT NULL,
    train_item_id INT UNSIGNED DEFAULT NULL,
    quest_points INT UNSIGNED DEFAULT 10,
    quest_points_last_refill DATETIME DEFAULT CURRENT_TIMESTAMP,
    locked BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (race_id) REFERENCES races(race_id) ON DELETE CASCADE,
    FOREIGN KEY (attitude_id) REFERENCES attitudes(attitude_id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (train_item_id) REFERENCES items(item_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News Table
CREATE TABLE news (
    news_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    title VARCHAR(100) NOT NULL,
    text TEXT NOT NULL,
    FOREIGN KEY (author_id) REFERENCES characters(character_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Levels Table
CREATE TABLE levels (
    level INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exp_required INT UNSIGNED NOT NULL, -- Experience required for this level
    health_bonus INT NOT NULL,          -- Bonus health on reaching this level
    mana_bonus INT NOT NULL,            -- Bonus mana on reaching this level
    ki_bonus INT NOT NULL,              -- Bonus KI on reaching this level
    fame_bonus INT NOT NULL            -- Bonus fame on reaching this level
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE main_story_quests (
    quest_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    race_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT 'default_main_story_image.png',
    level_required INT UNSIGNED NOT NULL DEFAULT 1,
    fame_required INT UNSIGNED DEFAULT 0,
    exp_reward INT UNSIGNED NOT NULL,
    fame_reward INT UNSIGNED DEFAULT 0,
    zeni_reward INT UNSIGNED DEFAULT 0,
    health_reward INT UNSIGNED DEFAULT 0,
    mana_reward INT UNSIGNED DEFAULT 0,
    ki_reward INT UNSIGNED DEFAULT 0,
    location_required VARCHAR(100),
    is_time_based BOOLEAN DEFAULT FALSE,
    time_limit_seconds INT DEFAULT NULL,
    prerequisites JSON DEFAULT NULL,
    `order` INT UNSIGNED NOT NULL, -- Added the order column
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (race_id) REFERENCES races(race_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE side_quests (
    quest_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT 'default_side_quest_image.png',
    level_required INT UNSIGNED NOT NULL DEFAULT 1, -- Ensures this column exists
    fame_required INT UNSIGNED DEFAULT 0,
    exp_reward INT UNSIGNED NOT NULL,
    fame_reward INT UNSIGNED DEFAULT 0,
    zeni_reward INT UNSIGNED DEFAULT 0,
    health_reward INT UNSIGNED DEFAULT 0,
    mana_reward INT UNSIGNED DEFAULT 0,
    ki_reward INT UNSIGNED DEFAULT 0,
    location_required VARCHAR(100),
    is_time_based BOOLEAN DEFAULT FALSE,
    time_limit_seconds INT DEFAULT NULL,
    prerequisites JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE npcs (
    npc_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    location_id INT UNSIGNED NOT NULL,
    image VARCHAR(255),
    is_fightable BOOLEAN DEFAULT FALSE, -- Can this NPC be fought?
    is_hostile BOOLEAN DEFAULT FALSE,  -- Is this NPC aggressive by default?
    hostile_condition JSON DEFAULT NULL,
    item_required TEXT NULL,
    quest_required INT UNSIGNED NULL,
    health INT UNSIGNED DEFAULT 100,
    max_health INT UNSIGNED DEFAULT 100,
    ki INT UNSIGNED DEFAULT 50,         -- Combat strength
    rewards TEXT,                       -- JSON or comma-separated list of rewards with probabilities
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE npc_quests (
    npc_quest_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    npc_id INT UNSIGNED NOT NULL,
    quest_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (npc_id) REFERENCES npcs(npc_id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES side_quests(quest_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE quest_conditions (
    condition_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quest_id INT UNSIGNED NOT NULL,
    quest_type ENUM('main', 'side') NOT NULL,
    condition_type ENUM('level', 'fame', 'location', 'completed_quest', 'item'),
    condition_value VARCHAR(255),
    FOREIGN KEY (quest_id) REFERENCES side_quests(quest_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE character_npc_relationships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    character_id INT UNSIGNED NOT NULL,
    npc_id INT UNSIGNED NOT NULL,
    relationship_points INT NOT NULL DEFAULT 0, -- Tracks the relationship score
    last_interaction DATETIME DEFAULT CURRENT_TIMESTAMP, -- To track decay over time
    last_relationship_gain DATETIME DEFAULT NULL AFTER last_interaction,
    FOREIGN KEY (character_id) REFERENCES characters(character_id) ON DELETE CASCADE,
    FOREIGN KEY (npc_id) REFERENCES npcs(npc_id) ON DELETE CASCADE,
    UNIQUE KEY unique_character_npc_relationship (character_id, npc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE npc_dialogues (
    dialogue_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    npc_id INT UNSIGNED NOT NULL,
    dialogue TEXT NOT NULL,
    FOREIGN KEY (npc_id) REFERENCES npcs(npc_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE character_inventory (
    inventory_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    character_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(character_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
