-- ==============================================================
-- Hesabdar (حسابدار) - Personal Finance Management Database Schema
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- Character Set: utf8mb4 / Collation: utf8mb4_unicode_ci
-- Currency: Iranian Rial (Stored as BIGINT, integer only)
-- Dates: Standard ISO Gregorian (YYYY-MM-DD) for SQL indexing
-- ==============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Login Rate Limiting Table (Security protection against brute force)
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_time` (`ip_address`, `attempted_at`),
    INDEX `idx_email_time` (`email`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Accounts Table (کارت‌ها، حساب‌ها، پول نقد)
CREATE TABLE IF NOT EXISTS `accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('bank_card', 'cash', 'savings', 'other') NOT NULL DEFAULT 'bank_card',
    `card_number` VARCHAR(24) NULL DEFAULT NULL,
    `opening_balance` BIGINT NOT NULL DEFAULT 0,
    `color` VARCHAR(20) NOT NULL DEFAULT '#3b82f6',
    `icon` VARCHAR(50) NOT NULL DEFAULT 'credit-card',
    `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_accounts_user` (`user_id`, `status`),
    CONSTRAINT `fk_accounts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Categories Table (دسته‌بندی‌های درآمد و هزینه)
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `color` VARCHAR(20) NOT NULL DEFAULT '#64748b',
    `icon` VARCHAR(50) NOT NULL DEFAULT 'tag',
    `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_categories_user_type` (`user_id`, `type`, `status`),
    CONSTRAINT `fk_categories_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Transactions Table (تراکنش‌های درآمد، هزینه و انتقال)
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` ENUM('income', 'expense', 'transfer') NOT NULL,
    `amount_rial` BIGINT NOT NULL,
    `account_id` INT NOT NULL COMMENT 'For income/expense: source/target. For transfer: sender account',
    `to_account_id` INT NULL DEFAULT NULL COMMENT 'Only for transfer: recipient account',
    `category_id` INT NULL DEFAULT NULL COMMENT 'Category for income/expense; NULL for transfers',
    `transaction_date` DATE NOT NULL COMMENT 'Gregorian date for indexing and range queries',
    `description` VARCHAR(500) NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tx_user_date` (`user_id`, `transaction_date`),
    INDEX `idx_tx_account` (`account_id`),
    INDEX `idx_tx_to_account` (`to_account_id`),
    INDEX `idx_tx_category` (`category_id`),
    INDEX `idx_tx_user_type` (`user_id`, `type`),
    CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tx_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tx_to_account` FOREIGN KEY (`to_account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tx_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Settings Table (تنظیمات کاربر و سیستم)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `setting_key` VARCHAR(50) NOT NULL,
    `setting_value` TEXT NULL DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_setting` (`user_id`, `setting_key`),
    CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- Default Seed Data
-- Initial Administrator User
-- Email: admin@hesabdar.local
-- Password: AdminPassword123! (Generated with password_hash)
-- ==============================================================
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `status`, `created_at`) 
VALUES (
    1, 
    'مدیر سیستم', 
    'admin@hesabdar.local', 
    '$2y$10$wT54bZ4gE1wX1v8F9wI3aOf6GjLwzXkZ2ZkIuVzX1C2V3B4N5M6Ou', -- Will be updated dynamically by installer
    'active', 
    NOW()
) ON DUPLICATE KEY UPDATE `id` = `id`;

-- Initial Default Accounts for User 1
INSERT INTO `accounts` (`id`, `user_id`, `name`, `type`, `card_number`, `opening_balance`, `color`, `icon`, `status`) VALUES
(1, 1, 'کارت بانک ملت', 'bank_card', '6104', 50000000, '#e11d48', 'credit-card', 'active'),
(2, 1, 'کارت بانک صادرات', 'bank_card', '6037', 25000000, '#2563eb', 'credit-card', 'active'),
(3, 1, 'کیف پول نقدی', 'cash', NULL, 5000000, '#059669', 'wallet', 'active'),
(4, 1, 'حساب پس‌انداز', 'savings', NULL, 100000000, '#7c3aed', 'piggy-bank', 'active')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Initial Expense Categories for User 1
INSERT INTO `categories` (`user_id`, `name`, `type`, `color`, `icon`, `sort_order`) VALUES
(1, 'غذا و رستوران', 'expense', '#f97316', 'utensils', 1),
(1, 'حمل‌ونقل و بنزین', 'expense', '#0284c7', 'car', 2),
(1, 'خرید و پوشاک', 'expense', '#ec4899', 'shopping-bag', 3),
(1, 'قبوض و شارژ', 'expense', '#eab308', 'receipt', 4),
(1, 'اینترنت و ارتباطات', 'expense', '#6366f1', 'wifi', 5),
(1, 'تفریح و سرگرمی', 'expense', '#8b5cf6', 'film', 6),
(1, 'آموزش و کتاب', 'expense', '#14b8a6', 'book-open', 7),
(1, 'سلامت و پزشکی', 'expense', '#ef4444', 'heart-pulse', 8),
(1, 'خانه و تعمیرات', 'expense', '#84cc16', 'home', 9),
(1, 'اشتراک‌ها و نرم‌افزار', 'expense', '#06b6d4', 'repeat', 10),
(1, 'سایر هزینه‌ها', 'expense', '#64748b', 'more-horizontal', 11);

-- Initial Income Categories for User 1
INSERT INTO `categories` (`user_id`, `name`, `type`, `color`, `icon`, `sort_order`) VALUES
(1, 'حقوق ماهیانه', 'income', '#10b981', 'briefcase', 1),
(1, 'پروژه و فریلنسری', 'income', '#06b6d4', 'laptop', 2),
(1, 'فروش کالا', 'income', '#3b82f6', 'dollar-sign', 3),
(1, 'هدیه و پاداش', 'income', '#f59e0b', 'gift', 4),
(1, 'سایر درآمدها', 'income', '#64748b', 'plus-circle', 5);

-- Initial User Settings
INSERT INTO `settings` (`user_id`, `setting_key`, `setting_value`) VALUES
(1, 'app_name', 'حسابدار شخصی'),
(1, 'currency_unit', 'ریال'),
(1, 'theme', 'light'),
(1, 'records_per_page', '15')
ON DUPLICATE KEY UPDATE `user_id` = `user_id`;

SET FOREIGN_KEY_CHECKS = 1;
