-- MAIN DATABASE OF THE FINANCE APPLICATION

-- Drop the database if it exists and create a new one
DROP DATABASE IF EXISTS `portfolio`;
-- Create the database with UTF8MB4 character set and collation
CREATE DATABASE IF NOT EXISTS `portfolio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Use the database
USE `portfolio`;


-- ADMIN TABLE (Users)
CREATE TABLE `Admin` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- Créer un admin par défaut (mot de passe: admin123)
INSERT INTO `Admin` (`email`, `password`, `name`) VALUES
('admin@finance.local', '$2y$10$DCfP3XHMve74ydgi6V3ghOgR2fejcVyJcvc5bteck19UBvrfhyIbK', 'Administrateur');


-- ACCOUNTS TABLE (Multi-comptes & Cash)
CREATE TABLE `Account` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('checking', 'savings', 'cash', 'credit_card') NOT NULL DEFAULT 'checking',
    `current_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `icon` VARCHAR(50) DEFAULT 'fa-university',
    `color` VARCHAR(10) DEFAULT '#2563eb',
    `include_in_net_worth` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `Admin`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

-- Comptes par défaut
INSERT INTO `Account` (`user_id`, `name`, `type`, `current_balance`, `icon`, `color`) VALUES
(1, 'Compte Courant', 'checking', 2500.00, 'fa-university', '#2563eb'),
(1, 'Livret A', 'savings', 5000.00, 'fa-piggy-bank', '#22c55e'),
(1, 'Cash', 'cash', 150.00, 'fa-money', '#f59e0b');


-- CATEGORIES TABLE
CREATE TABLE `Category` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL DEFAULT 'expense',
    `icon` VARCHAR(50) DEFAULT 'fa-tag',
    `color` VARCHAR(10) DEFAULT '#64748b',
    `budget_amount` DECIMAL(12, 2) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `Admin`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

-- Catégories par défaut
INSERT INTO `Category` (`user_id`, `name`, `type`, `icon`, `color`, `budget_amount`) VALUES
-- Dépenses
(1, 'Alimentation', 'expense', 'fa-shopping-cart', '#ef4444', 400.00),
(1, 'Transport', 'expense', 'fa-car', '#f97316', 150.00),
(1, 'Logement', 'expense', 'fa-home', '#8b5cf6', NULL),
(1, 'Loisirs', 'expense', 'fa-gamepad', '#ec4899', 200.00),
(1, 'Santé', 'expense', 'fa-medkit', '#14b8a6', NULL),
(1, 'Shopping', 'expense', 'fa-shopping-bag', '#f59e0b', 100.00),
(1, 'Abonnements', 'expense', 'fa-repeat', '#6366f1', NULL),
(1, 'Restaurant', 'expense', 'fa-cutlery', '#ea580c', 100.00),
(1, 'Factures', 'expense', 'fa-file-text', '#64748b', NULL),
-- Revenus
(1, 'Salaire', 'income', 'fa-briefcase', '#22c55e', NULL),
(1, 'Freelance', 'income', 'fa-laptop', '#10b981', NULL),
(1, 'Investissements', 'income', 'fa-line-chart', '#059669', NULL),
(1, 'Remboursements', 'income', 'fa-undo', '#34d399', NULL);


-- TRANSACTIONS TABLE
CREATE TABLE `Transaction` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `account_id` INT NOT NULL,
    `category_id` INT NULL,
    `type` ENUM('income', 'expense', 'transfer') NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `date` DATE NOT NULL,
    `is_recurring` BOOLEAN DEFAULT FALSE,
    `subscription_id` INT DEFAULT NULL,
    `transfer_account_id` INT DEFAULT NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `Admin`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`account_id`) REFERENCES `Account`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `Category`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`transfer_account_id`) REFERENCES `Account`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB;

-- Quelques transactions d'exemple
INSERT INTO `Transaction` (`user_id`, `account_id`, `category_id`, `type`, `amount`, `description`, `date`) VALUES
(1, 1, 10, 'income', 2800.00, 'Salaire Janvier', '2026-01-05'),
(1, 1, 1, 'expense', 85.50, 'Courses Carrefour', '2026-01-10'),
(1, 1, 3, 'expense', 850.00, 'Loyer Janvier', '2026-01-01'),
(1, 1, 8, 'expense', 35.00, 'Restaurant Le Bistrot', '2026-01-15'),
(1, 1, 2, 'expense', 50.00, 'Essence', '2026-01-12'),
(1, 1, 10, 'income', 2800.00, 'Salaire Février', '2026-02-05'),
(1, 1, 1, 'expense', 92.30, 'Courses Leclerc', '2026-02-03');


-- SUBSCRIPTIONS TABLE (Abonnements & Récurrents)
CREATE TABLE `Subscription` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `category_id` INT NULL,
    `account_id` INT NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL DEFAULT 'expense',
    `frequency` ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL DEFAULT 'monthly',
    `next_due_date` DATE NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `auto_renew` BOOLEAN DEFAULT TRUE,
    `icon` VARCHAR(50) DEFAULT 'fa-repeat',
    `color` VARCHAR(10) DEFAULT '#6366f1',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `Admin`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `Category`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`account_id`) REFERENCES `Account`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

-- Abonnements d'exemple
INSERT INTO `Subscription` (`user_id`, `name`, `amount`, `category_id`, `account_id`, `type`, `frequency`, `next_due_date`, `icon`, `color`) VALUES
-- Revenus récurrents
(1, 'Salaire', 2800.00, 10, 1, 'income', 'monthly', '2026-02-05', 'fa-briefcase', '#22c55e'),
-- Dépenses récurrentes
(1, 'Loyer', 850.00, 3, 1, 'expense', 'monthly', '2026-02-01', 'fa-home', '#8b5cf6'),
(1, 'Netflix', 17.99, 7, 1, 'expense', 'monthly', '2026-02-15', 'fa-film', '#e50914'),
(1, 'Spotify', 10.99, 7, 1, 'expense', 'monthly', '2026-02-20', 'fa-music', '#1db954'),
(1, 'Internet', 39.99, 9, 1, 'expense', 'monthly', '2026-02-10', 'fa-wifi', '#0ea5e9'),
(1, 'Électricité', 75.00, 9, 1, 'expense', 'monthly', '2026-02-25', 'fa-bolt', '#eab308'),
(1, 'Salle de sport', 29.99, 4, 1, 'expense', 'monthly', '2026-02-01', 'fa-heartbeat', '#ef4444'),
(1, 'Assurance Auto', 450.00, 2, 1, 'expense', 'yearly', '2026-06-15', 'fa-car', '#f97316');
