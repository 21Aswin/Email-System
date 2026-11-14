-- nkinfyx groups Mail Database Schema
-- UTF8mb4 & MySQL 8 / MariaDB compatible

CREATE DATABASE IF NOT EXISTS `nkinfyx_email` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nkinfyx_email`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `message_labels`;
DROP TABLE IF EXISTS `labels`;
DROP TABLE IF EXISTS `attachments`;
DROP TABLE IF EXISTS `mailbox_items`;
DROP TABLE IF EXISTS `message_recipients`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users table
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `display_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('active', 'suspended', 'pending') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Core messages table (Immutable message content)
CREATE TABLE `messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_user_id` INT UNSIGNED NOT NULL,
    `sender_name` VARCHAR(120) NOT NULL,
    `sender_email` VARCHAR(191) NOT NULL,
    `subject` VARCHAR(255) NOT NULL DEFAULT '(no subject)',
    `body_text` MEDIUMTEXT,
    `body_html` MEDIUMTEXT,
    `snippet` VARCHAR(255) DEFAULT '',
    `sent_at` DATETIME NULL,
    `received_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_messages_owner` (`owner_user_id`),
    INDEX `idx_messages_sender` (`sender_email`),
    INDEX `idx_messages_subject` (`subject`),
    FULLTEXT INDEX `ft_messages_search` (`subject`, `body_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Message recipients (To, CC, BCC)
CREATE TABLE `message_recipients` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` INT UNSIGNED NOT NULL,
    `recipient_type` ENUM('to', 'cc', 'bcc') NOT NULL DEFAULT 'to',
    `email` VARCHAR(191) NOT NULL,
    `display_name` VARCHAR(120) DEFAULT NULL,
    FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
    INDEX `idx_recipients_message` (`message_id`),
    INDEX `idx_recipients_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mailbox items (User's view and state of a message)
CREATE TABLE `mailbox_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `message_id` INT UNSIGNED NOT NULL,
    `folder` ENUM('inbox', 'sent', 'drafts', 'archive', 'spam', 'trash', 'snoozed', 'scheduled') NOT NULL DEFAULT 'inbox',
    `category` ENUM('primary', 'updates', 'promotions', 'purchases') NOT NULL DEFAULT 'primary',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `is_starred` TINYINT(1) NOT NULL DEFAULT 0,
    `is_important` TINYINT(1) NOT NULL DEFAULT 0,
    `snoozed_until` DATETIME NULL DEFAULT NULL,
    `scheduled_at` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uniq_user_message_folder` (`user_id`, `message_id`, `folder`),
    INDEX `idx_mailbox_user_folder_date` (`user_id`, `folder`, `created_at` DESC),
    INDEX `idx_mailbox_user_read` (`user_id`, `is_read`),
    INDEX `idx_mailbox_user_starred` (`user_id`, `is_starred`),
    INDEX `idx_mailbox_user_important` (`user_id`, `is_important`),
    INDEX `idx_mailbox_user_category` (`user_id`, `category`),
    INDEX `idx_mailbox_scheduled` (`user_id`, `scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attachments
CREATE TABLE `attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` INT UNSIGNED NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `storage_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
    `size_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
    INDEX `idx_attachments_message` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom Labels
CREATE TABLE `labels` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(60) NOT NULL,
    `color` VARCHAR(20) NOT NULL DEFAULT '#1a73e8',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uniq_user_label_name` (`user_id`, `name`),
    INDEX `idx_labels_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Message Labels Pivot
CREATE TABLE `message_labels` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` INT UNSIGNED NOT NULL,
    `label_id` INT UNSIGNED NOT NULL,
    FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uniq_message_label` (`message_id`, `label_id`),
    INDEX `idx_msg_labels_label` (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
