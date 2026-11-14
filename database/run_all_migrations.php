<?php
declare(strict_types=1);

/**
 * Novalyx Cloud Webmail - Unified Master Migration & Seeder
 * Idempotent: Can be run multiple times safely on fresh or existing databases.
 */

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();

if (file_exists(__DIR__ . '/../.env')) {
    \App\Core\Env::load(__DIR__ . '/../.env');
}

echo "=========================================================\n";
echo " Novalyx Cloud Webmail - Automated Database Migration\n";
echo "=========================================================\n";

try {
    // 1. Establish database connection
    try {
        $pdo = \App\Core\Database::getConnection();
        echo "[1/6] Connected to target database successfully.\n";
    } catch (\PDOException $e) {
        echo "[1/6] Target database not found or cannot connect directly. Attempting to create...\n";
        try {
            $rootPdo = \App\Core\Database::getRootConnection();
            $dbName = \App\Core\Env::get('DB_DATABASE', 'nkinfyx_email');
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            echo "      Database `{$dbName}` verified/created.\n";
            $pdo = \App\Core\Database::getConnection();
        } catch (\Exception $ex) {
            throw new \Exception("Could not connect or create database: " . $ex->getMessage());
        }
    }

    // 2. Base tables (users, messages, message_recipients, mailbox_items, labels, message_labels)
    echo "[2/6] Verifying core tables...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `display_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(191) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `avatar_path` VARCHAR(255) NULL,
            `status` ENUM('active', 'suspended', 'pending') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_users_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `messages` (
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
            INDEX `idx_messages_subject` (`subject`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `message_recipients` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `message_id` INT UNSIGNED NOT NULL,
            `recipient_type` ENUM('to', 'cc', 'bcc') NOT NULL DEFAULT 'to',
            `email` VARCHAR(191) NOT NULL,
            `display_name` VARCHAR(120) DEFAULT NULL,
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
            INDEX `idx_recipients_message` (`message_id`),
            INDEX `idx_recipients_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `mailbox_items` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `message_id` INT UNSIGNED NOT NULL,
            `folder` ENUM('inbox', 'sent', 'drafts', 'archive', 'spam', 'trash', 'snoozed', 'scheduled', 'purchases', 'important') NOT NULL DEFAULT 'inbox',
            `category` ENUM('primary', 'updates', 'promotions', 'purchases', 'social') NOT NULL DEFAULT 'primary',
            `is_read` TINYINT(1) NOT NULL DEFAULT 0,
            `is_starred` TINYINT(1) NOT NULL DEFAULT 0,
            `is_important` TINYINT(1) NOT NULL DEFAULT 0,
            `is_draft` TINYINT(1) NOT NULL DEFAULT 0,
            `snoozed_until` DATETIME NULL DEFAULT NULL,
            `scheduled_at` DATETIME NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_message` (`user_id`, `message_id`),
            INDEX `idx_mailbox_user_folder` (`user_id`, `folder`, `category`),
            INDEX `idx_mailbox_snooze` (`snoozed_until`),
            INDEX `idx_mailbox_scheduled` (`scheduled_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Self-healing check for mailbox_items snoozed_until column
    try {
        $hasSnoozedCol = $pdo->query("SHOW COLUMNS FROM mailbox_items LIKE 'snoozed_until'")->fetchAll();
        if (empty($hasSnoozedCol)) {
            $hasSnoozeNoD = $pdo->query("SHOW COLUMNS FROM mailbox_items LIKE 'snooze_until'")->fetchAll();
            if (!empty($hasSnoozeNoD)) {
                $pdo->exec("ALTER TABLE mailbox_items CHANGE COLUMN snooze_until snoozed_until DATETIME NULL DEFAULT NULL;");
            } else {
                $pdo->exec("ALTER TABLE mailbox_items ADD COLUMN snoozed_until DATETIME NULL DEFAULT NULL AFTER is_important;");
            }
        }
    } catch (\Exception $e) {}

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `labels` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `color` VARCHAR(20) NOT NULL DEFAULT '#1a73e8',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_label` (`user_id`, `name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `message_labels` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `message_id` INT UNSIGNED NOT NULL,
            `label_id` INT UNSIGNED NOT NULL,
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_message_label` (`message_id`, `label_id`),
            INDEX `idx_msg_labels_label` (`label_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Self-healing check for message_labels schema
    try {
        $hasIdCol = $pdo->query("SHOW COLUMNS FROM message_labels LIKE 'id'")->fetchAll();
        if (empty($hasIdCol)) {
            $pdo->exec("DROP TABLE IF EXISTS message_labels;");
            $pdo->exec("
                CREATE TABLE `message_labels` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `message_id` INT UNSIGNED NOT NULL,
                    `label_id` INT UNSIGNED NOT NULL,
                    FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE,
                    UNIQUE KEY `uniq_message_label` (`message_id`, `label_id`),
                    INDEX `idx_msg_labels_label` (`label_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    } catch (\Exception $e) {}

    // 3. Attachments table
    echo "[3/6] Verifying attachments table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `attachments` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NULL,
            `message_id` INT UNSIGNED NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `storage_name` VARCHAR(255) NOT NULL,
            `mime_type` VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
            `size_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            INDEX `idx_attachments_message` (`message_id`),
            INDEX `idx_attachments_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Self-healing check for attachments table (ensures storage_name exists)
    try {
        $hasStorageName = $pdo->query("SHOW COLUMNS FROM attachments LIKE 'storage_name'")->fetchAll();
        if (empty($hasStorageName)) {
            $pdo->exec("DROP TABLE IF EXISTS attachments;");
            $pdo->exec("
                CREATE TABLE `attachments` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT UNSIGNED NULL,
                    `message_id` INT UNSIGNED NULL,
                    `original_name` VARCHAR(255) NOT NULL,
                    `storage_name` VARCHAR(255) NOT NULL,
                    `mime_type` VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
                    `size_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                    INDEX `idx_attachments_message` (`message_id`),
                    INDEX `idx_attachments_user` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    } catch (\Exception $e) {}

    // 4. Calendar events table
    echo "[4/6] Verifying calendar events table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `calendar_events` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `message_id` INT UNSIGNED NULL DEFAULT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `location` VARCHAR(255) NULL,
            `event_date` DATE NOT NULL,
            `start_time` TIME NULL,
            `end_time` TIME NULL,
            `is_all_day` TINYINT(1) NOT NULL DEFAULT 0,
            `category` ENUM('interview', 'work', 'meeting', 'holiday', 'personal') NOT NULL DEFAULT 'interview',
            `color` VARCHAR(30) NOT NULL DEFAULT '#10b981',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL,
            INDEX `idx_events_user_date` (`user_id`, `event_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Self-healing check for calendar_events table (ensures is_all_day exists)
    try {
        $hasAllDay = $pdo->query("SHOW COLUMNS FROM calendar_events LIKE 'is_all_day'")->fetchAll();
        if (empty($hasAllDay)) {
            $pdo->exec("DROP TABLE IF EXISTS calendar_events;");
            $pdo->exec("
                CREATE TABLE `calendar_events` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT UNSIGNED NOT NULL,
                    `message_id` INT UNSIGNED NULL DEFAULT NULL,
                    `title` VARCHAR(255) NOT NULL,
                    `description` TEXT NULL,
                    `location` VARCHAR(255) NULL,
                    `event_date` DATE NOT NULL,
                    `start_time` TIME NULL,
                    `end_time` TIME NULL,
                    `is_all_day` TINYINT(1) NOT NULL DEFAULT 0,
                    `category` ENUM('interview', 'work', 'meeting', 'holiday', 'personal') NOT NULL DEFAULT 'interview',
                    `color` VARCHAR(30) NOT NULL DEFAULT '#10b981',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL,
                    INDEX `idx_events_user_date` (`user_id`, `event_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // Seed initial calendar events if empty
        $calCount = (int)$pdo->query("SELECT COUNT(*) FROM calendar_events")->fetchColumn();
        if ($calCount === 0) {
            $year = (int)date('Y');
            $curDate = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $dayAfter = date('Y-m-d', strtotime('+2 days'));

            $sampleEvents = [
                ['title' => 'Technical Interview - Shanmuga Aswin (Full Stack Java)', 'desc' => 'Candidate discussion regarding Spring Boot, React & MySQL skills. Google Meet.', 'loc' => 'https://meet.google.com/nxw-inte-rvw', 'date' => $curDate, 'start' => '11:00:00', 'end' => '12:00:00', 'all_day' => 0, 'cat' => 'interview', 'color' => '#d97706'],
                ['title' => 'Sprint Planning & Email Architecture Review', 'desc' => 'Sprint deliverables for Novalyx Cloud Webmail release.', 'loc' => 'Novalyx Conf Room A', 'date' => $tomorrow, 'start' => '14:30:00', 'end' => '15:30:00', 'all_day' => 0, 'cat' => 'work', 'color' => '#2563eb'],
                ['title' => 'HR Round 2 & Resume Verification Call', 'desc' => 'Discussion on package and joining timeline.', 'loc' => 'Google Meet', 'date' => $dayAfter, 'start' => '16:00:00', 'end' => '16:45:00', 'all_day' => 0, 'cat' => 'interview', 'color' => '#d97706'],
                ['title' => 'Bahag Bihu (Assam)', 'desc' => 'Regional Harvest Festival', 'loc' => 'India', 'date' => "{$year}-04-15", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Vaisakhadi (Bengal)', 'desc' => 'Bengali New Year', 'loc' => 'India', 'date' => "{$year}-04-15", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Buddha Purnima', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-05-01", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Birthday of Rabindranath Tagore', 'desc' => 'Cultural Observance', 'loc' => 'India', 'date' => "{$year}-05-09", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Bakrid / Eid al-Adha', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-05-28", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Muharram / Ashura', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-06-26", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Rath Yatra', 'desc' => 'Regional Festival', 'loc' => 'India', 'date' => "{$year}-07-16", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Independence Day', 'desc' => 'National Holiday', 'loc' => 'India', 'date' => "{$year}-08-15", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Parsi New Year', 'desc' => 'Regional Festival', 'loc' => 'India', 'date' => "{$year}-08-15", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Milad un-Nabi', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-08-26", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
            ];

            $ins = $pdo->prepare("
                INSERT INTO calendar_events (user_id, title, description, location, event_date, start_time, end_time, is_all_day, category, color)
                VALUES (1, :title, :desc, :loc, :dt, :start, :end, :all_day, :cat, :col)
            ");

            foreach ($sampleEvents as $ev) {
                $ins->execute([
                    'title' => $ev['title'],
                    'desc' => $ev['desc'],
                    'loc' => $ev['loc'],
                    'dt' => $ev['date'],
                    'start' => $ev['start'],
                    'end' => $ev['end'],
                    'all_day' => $ev['all_day'],
                    'cat' => $ev['cat'],
                    'col' => $ev['color']
                ]);
            }
            echo "      Seeded " . count($sampleEvents) . " initial calendar events & holidays.\n";
        }
    } catch (\Exception $e) {
        echo "      Calendar setup note: " . $e->getMessage() . "\n";
    }

    // 5. User settings & Blocked senders tables
    echo "[5/6] Verifying user settings and blocked senders tables...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_settings` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `theme` ENUM('light', 'dark', 'system') NOT NULL DEFAULT 'light',
            `density` ENUM('comfortable', 'compact') NOT NULL DEFAULT 'comfortable',
            `default_folder` VARCHAR(40) NOT NULL DEFAULT 'inbox',
            `default_category` VARCHAR(40) NOT NULL DEFAULT 'primary',
            `show_category_tabs` TINYINT(1) NOT NULL DEFAULT 1,
            `unread_badge_mode` ENUM('unread', 'total') NOT NULL DEFAULT 'unread',
            `in_app_notifications` TINYINT(1) NOT NULL DEFAULT 1,
            `browser_notifications` TINYINT(1) NOT NULL DEFAULT 0,
            `signature` TEXT NULL,
            `append_signature` TINYINT(1) NOT NULL DEFAULT 1,
            `default_reply_behavior` ENUM('reply', 'reply_all') NOT NULL DEFAULT 'reply',
            `confirm_discard` TINYINT(1) NOT NULL DEFAULT 1,
            `auto_save_drafts` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_settings_uid` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `blocked_senders` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `email` VARCHAR(191) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_blocked_email` (`user_id`, `email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Safe column migrations in case of pre-existing older tables
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar_path'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL AFTER display_name");
    }

    // 6. Seed demo data if database is fresh
    echo "[6/6] Checking seed accounts...\n";
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount === 0) {
        echo "      Database is empty. Seeding initial accounts and sample emails...\n";
        $seedSql = file_get_contents(__DIR__ . '/seed.sql');
        if ($seedSql) {
            $pdo->exec($seedSql);
            echo "      Seeding complete! Default admin: adminnkinflyx@gmail.com / password123\n";
        }
    } else {
        echo "      Existing users found ({$userCount} users). Skipping seed data.\n";
    }

    echo "=========================================================\n";
    echo " SUCCESS: All migrations applied cleanly!\n";
    echo "=========================================================\n";
} catch (\Exception $e) {
    echo "=========================================================\n";
    echo " MIGRATION ERROR: " . $e->getMessage() . "\n";
    echo "=========================================================\n";
    exit(1);
}
