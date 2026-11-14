<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Env::load(__DIR__ . '/../.env');

date_default_timezone_set('Asia/Kolkata');

echo "=== Running Calendar & Event Scheduling Migration ===\n";

try {
    $pdo = \App\Core\Database::getConnection();
    $pdo->exec("SET time_zone = '+05:30'");

    // 1. Create calendar_events table
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
    echo "[1/2] Created `calendar_events` table.\n";

    // 2. Insert initial events and Indian calendar holidays matching screenshot for User 1
    $stmt = $pdo->query("SELECT COUNT(*) FROM calendar_events WHERE user_id = 1");
    $count = (int)$stmt->fetchColumn();

    if ($count === 0) {
        $year = (int)date('Y');
        $curDate = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $dayAfter = date('Y-m-d', strtotime('+2 days'));

        $sampleEvents = [
            // Upcoming work & interview schedules
            [
                'title' => 'Technical Interview - Shanmuga Aswin (Full Stack Java)',
                'desc' => 'Candidate discussion regarding Spring Boot, React & MySQL skills. Google Meet.',
                'loc' => 'https://meet.google.com/nxw-inte-rvw',
                'date' => $curDate,
                'start' => '11:00:00',
                'end' => '12:00:00',
                'all_day' => 0,
                'cat' => 'interview',
                'color' => '#d97706' // amber/orange for interview
            ],
            [
                'title' => 'Sprint Planning & Email Architecture Review',
                'desc' => 'Sprint deliverables for Novalyx Cloud Webmail 2.0 release.',
                'loc' => 'Novalyx Conf Room A',
                'date' => $tomorrow,
                'start' => '14:30:00',
                'end' => '15:30:00',
                'all_day' => 0,
                'cat' => 'work',
                'color' => '#2563eb' // blue for work
            ],
            [
                'title' => 'HR Round 2 & Resume Verification Call',
                'desc' => 'Discussion on package, joining timeline, and background check.',
                'loc' => 'Google Meet',
                'date' => $dayAfter,
                'start' => '16:00:00',
                'end' => '16:45:00',
                'all_day' => 0,
                'cat' => 'interview',
                'color' => '#d97706'
            ],

            // Indian National & Cultural Holidays (as displayed in screenshot)
            [
                'title' => 'Bahag Bihu (Assam)',
                'desc' => 'Regional Harvest Festival',
                'loc' => 'India',
                'date' => "{$year}-04-15",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Vaisakhadi (Bengal)',
                'desc' => 'Bengali New Year',
                'loc' => 'India',
                'date' => "{$year}-04-15",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Buddha Purnima',
                'desc' => 'National Gazetted Holiday',
                'loc' => 'India',
                'date' => "{$year}-05-01",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Birthday of Rabindranath Tagore',
                'desc' => 'Cultural Observance',
                'loc' => 'India',
                'date' => "{$year}-05-09",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Bakrid / Eid al-Adha',
                'desc' => 'National Gazetted Holiday',
                'loc' => 'India',
                'date' => "{$year}-05-28",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Muharram / Ashura',
                'desc' => 'National Gazetted Holiday',
                'loc' => 'India',
                'date' => "{$year}-06-26",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Rath Yatra',
                'desc' => 'Regional Festival',
                'loc' => 'India',
                'date' => "{$year}-07-16",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Independence Day',
                'desc' => 'National Holiday',
                'loc' => 'India',
                'date' => "{$year}-08-15",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Parsi New Year',
                'desc' => 'Regional Festival',
                'loc' => 'India',
                'date' => "{$year}-08-15",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
            [
                'title' => 'Milad un-Nabi',
                'desc' => 'National Gazetted Holiday',
                'loc' => 'India',
                'date' => "{$year}-08-26",
                'start' => null,
                'end' => null,
                'all_day' => 1,
                'cat' => 'holiday',
                'color' => '#15803d'
            ],
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
        echo "[2/2] Inserted sample events & holidays into `calendar_events`.\n";
    } else {
        echo "[2/2] `calendar_events` already has {$count} events.\n";
    }

    echo "=== Migration Finished Successfully! ===\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
