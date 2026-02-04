-- MAIN DATABASE OF THE PORTFOLIO APPLICATION

-- Drop the database if it exists and create a new one
DROP DATABASE IF EXISTS `finance_manager`;
-- Create the database with UTF8MB4 character set and collation
CREATE DATABASE IF NOT EXISTS `finance_manager` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Use the database
USE `finance_manager`;


-- ADMIN TABLE
CREATE TABLE `Admin`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- Créer un admin par défaut (mot de passe: admin123)
INSERT INTO `Admin` (`email`, `password`, `name`) VALUES
('admin@portfolio.local', '$2y$10$DCfP3XHMve74ydgi6V3ghOgR2fejcVyJcvc5bteck19UBvrfhyIbK', 'Administrateur');

-- TABLE FOR ACCOUNTS
CREATE TABLE `Account`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `admin_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `balance` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    `accounting_balance` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    `instant_balance` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    `color` VARCHAR(7) NOT NULL DEFAULT '#000000',
    `type` ENUM('checking', 'savings', 'credit_card', 'cash', 'other') NOT NULL DEFAULT 'other',
    `id_bridge` VARCHAR(255) DEFAULT NULL
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

-- TABLE FOR CATEGORIES
CREATE TABLE `Category`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `admin_id` INT DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

-- TABLE FOR TRANSACTIONS
CREATE TABLE `Transaction`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_bridge` VARCHAR(255) DEFAULT NULL,
    `admin_id` INT NOT NULL,
    `account_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `amount` DECIMAL(15, 2) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `date` DATE NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`account_id`) REFERENCES `Account`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `Category`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

-- TABLE FOR SUBSCRIPTIONS
CREATE TABLE `Subscription`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `admin_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(15, 2) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `type_period` ENUM('weekly', 'monthly', 'yearly') NOT NULL,
    `day_of_month` TINYINT,
    `day_of_week` TINYINT,
    `date_of_year` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

-- INSERT SOME SAMPLE CATEGORIES
INSERT INTO `Category` (`name`, `type`) VALUES
('Salaire', 'income'),
('Loyer', 'expense'),
('Alimentation', 'expense'),
('Divertissement', 'expense'),
('Investissements', 'income'),
('Freelance', 'income'),
('Nourriture', 'expense'),
('Transport', 'expense'),
('Santé', 'expense'),
('Autres', 'expense'),
('Autres', 'income');