-- ============================================================================
-- Haustor.ba - Database Schema & Seed Data (Milestone 2)
-- Course: IT-2001 Web Programming
-- Database Engine: MySQL 8.0+ / MariaDB (InnoDB, utf8mb4_unicode_ci)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `haustor_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `haustor_db`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `maintenance_requests`;
DROP TABLE IF EXISTS `apartments`;
DROP TABLE IF EXISTS `buildings`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- ============================================================================
-- 1. Table: users
-- ============================================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `role` ENUM('resident', 'manager') NOT NULL DEFAULT 'resident',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. Table: buildings
-- ============================================================================
CREATE TABLE `buildings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20) NOT NULL,
  `latitude` DECIMAL(10,7) DEFAULT NULL,
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  `total_floors` INT NOT NULL DEFAULT 1,
  `manager_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_buildings_manager_id` (`manager_id`),
  CONSTRAINT `fk_buildings_manager`
    FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. Table: apartments
-- ============================================================================
CREATE TABLE `apartments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `building_id` INT UNSIGNED NOT NULL,
  `resident_id` INT UNSIGNED DEFAULT NULL,
  `apartment_number` VARCHAR(20) NOT NULL,
  `floor` INT NOT NULL DEFAULT 0,
  `area_sqm` DECIMAL(6,2) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_building_apartment` (`building_id`, `apartment_number`),
  KEY `idx_apartments_resident_id` (`resident_id`),
  CONSTRAINT `fk_apartments_building`
    FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_apartments_resident`
    FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. Table: categories
-- ============================================================================
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `default_priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. Table: maintenance_requests
-- ============================================================================
CREATE TABLE `maintenance_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `building_id` INT UNSIGNED NOT NULL,
  `apartment_id` INT UNSIGNED DEFAULT NULL,
  `reported_by_user_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  `status` ENUM('pending', 'in_progress', 'resolved', 'rejected') NOT NULL DEFAULT 'pending',
  `resolution_notes` TEXT DEFAULT NULL,
  `resolved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_requests_building_id` (`building_id`),
  KEY `idx_requests_apartment_id` (`apartment_id`),
  KEY `idx_requests_reported_by` (`reported_by_user_id`),
  KEY `idx_requests_category_id` (`category_id`),
  KEY `idx_requests_status` (`status`),
  CONSTRAINT `fk_requests_building`
    FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_requests_apartment`
    FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_requests_reported_by_user`
    FOREIGN KEY (`reported_by_user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_requests_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Seed Data (Bosnian Language - Sarajevo Residential Examples)
-- ============================================================================

-- 1. Seed Users (Managers & Residents)
-- Password hashes correspond to bcrypt hash of 'Haustor123!'
INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `phone`, `role`) VALUES
  (1, 'Tarik Sorguč', 'tarik.sorguc@haustor.ba', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+387 61 111 222', 'manager'),
  (2, 'Adnan Kovačević', 'adnan.kovacevic@haustor.ba', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+387 61 222 333', 'manager'),
  (3, 'Damir Sorguč', 'damir.sorguc@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+387 62 333 444', 'resident'),
  (4, 'Amal Sorguč', 'amal.sorguc@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+387 61 444 555', 'resident'),
  (5, 'Lejla Hadžić', 'lejla.hadzic@bih.net.ba', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+387 63 555 666', 'resident');

-- 2. Seed Buildings
INSERT INTO `buildings` (`id`, `name`, `address`, `city`, `postal_code`, `latitude`, `longitude`, `total_floors`, `manager_id`) VALUES
  (1, 'Haustor Grbavička 14', 'Grbavička 14', 'Sarajevo', '71000', 43.8516000, 18.3964000, 6, 1),
  (2, 'Haustor Zmaja od Bosne 34', 'Zmaja od Bosne 34', 'Sarajevo', '71000', 43.8548000, 18.3991000, 8, 1),
  (3, 'Haustor Maršala Tita 22', 'Maršala Tita 22', 'Sarajevo', '71000', 43.8589000, 18.4214000, 5, 2);

-- 3. Seed Apartments
INSERT INTO `apartments` (`id`, `building_id`, `resident_id`, `apartment_number`, `floor`, `area_sqm`) VALUES
  (1, 1, 3, '4B', 2, 64.50),
  (2, 2, 1, '8', 3, 72.00),
  (3, 3, 4, '12', 4, 58.25),
  (4, 1, 5, '9A', 5, 81.00),
  (5, 2, NULL, '15', 6, 49.00);

-- 4. Seed Categories
INSERT INTO `categories` (`id`, `name`, `description`, `default_priority`) VALUES
  (1, 'Vodoinstalacije', 'Kvarovi na vodovodnim cijevima, ventilima, odvodima i curenje vode u stanovima ili podrumu.', 'urgent'),
  (2, 'Elektroinstalacije', 'Kvarovi na rasvjeti u stubištu, osiguračima, interfonima i zajedničkim elektroinstalacijama.', 'high'),
  (3, 'Lift', 'Zastoj lifta, kvarovi na vratima kabine, dugmadima za poziv ili sigurnosnim senzorima.', 'urgent'),
  (4, 'Krov i zajedničke prostorije', 'Prokišnjavanje krova, oštećenja ulaznih vrata haustora, gelendera, fasade i podruma.', 'medium'),
  (5, 'Grijanje', 'Problemi sa centralnim grijanjem, radijatorima, podstanicom ili dimnjacima.', 'high');

-- 5. Seed Maintenance Requests
INSERT INTO `maintenance_requests` (
  `id`, `building_id`, `apartment_id`, `reported_by_user_id`, `category_id`,
  `title`, `description`, `priority`, `status`, `resolution_notes`, `resolved_at`
) VALUES
  (
    1, 1, 1, 3, 5,
    'Curenje radijatora u dnevnom boravku',
    'Na spoju ventila glavnog radijatora u dnevnom boravku primijećeno je stalno kapanje vode pri punom pritisku grijanja.',
    'urgent', 'pending',
    NULL, NULL
  ),
  (
    2, 3, 3, 4, 3,
    'Lift zaglavljen između 2. i 3. sprata',
    'Kabina lifta se zaustavila između drugog i trećeg sprata, displej na prizemlju ne pokazuje sprat a vrata se ne otvaraju.',
    'urgent', 'in_progress',
    'Dežurna ekipa servisa za liftove je obaviještena i izašla na teren.', NULL
  ),
  (
    3, 2, 2, 1, 2,
    'Kvar na interfonu na ulazu',
    'Vanjska jedinica interfona na ulaznim vratima haustora ne prenosi zvuk prema stanovima na trećem spratu.',
    'medium', 'resolved',
    'Zamijenjen audio modul i očišćeni kontakti na glavnoj ploči interfona.', '2026-10-28 14:30:00'
  ),
  (
    4, 1, NULL, 5, 4,
    'Kvar na ulaznim vratima haustora',
    'Hidraulični zatvarač (pumpa) na glavnim ulaznim vratima zgrade je popustio pa se vrata ne zatvaraju do kraja.',
    'medium', 'in_progress',
    'Naručen novi hidraulični zatvarač vrata; ugradnja planirana u četvrtak.', NULL
  );

SET FOREIGN_KEY_CHECKS = 1;

