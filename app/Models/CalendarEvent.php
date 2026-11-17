<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class CalendarEvent
{
    /**
     * Get events for a specific date
     */
    public static function getByDate(int $userId, string $date): array
    {
        self::ensureUserHasEvents($userId);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM calendar_events 
            WHERE user_id = :uid AND event_date = :dt 
            ORDER BY is_all_day DESC, start_time ASC, id ASC
        ");
        $stmt->execute(['uid' => $userId, 'dt' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all events for a month (used for dot badges on mini calendar)
     */
    public static function getByMonth(int $userId, int $year, int $month): array
    {
        self::ensureUserHasEvents($userId);
        $pdo = Database::getConnection();
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("
            SELECT id, title, description, location, event_date, start_time, end_time, is_all_day, category, color 
            FROM calendar_events 
            WHERE user_id = :uid AND event_date BETWEEN :start AND :end
            ORDER BY event_date ASC, is_all_day DESC, start_time ASC
        ");
        $stmt->execute(['uid' => $userId, 'start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming events (from a given date or today onwards)
     */
    public static function getUpcoming(int $userId, int $limit = 40, string $fromDate = ''): array
    {
        self::ensureUserHasEvents($userId);
        $pdo = Database::getConnection();
        $start = (!empty($fromDate) && strtotime($fromDate)) ? $fromDate : date('Y-m-d');

        $stmt = $pdo->prepare("
            SELECT * FROM calendar_events 
            WHERE user_id = :uid AND event_date >= :start
            ORDER BY event_date ASC, is_all_day DESC, start_time ASC
            LIMIT :lim
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':start', $start, PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ensure user has standard national holidays & demo work events populated
     */
    public static function ensureUserHasEvents(int $userId): void
    {
        if ($userId <= 0) return;

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM calendar_events WHERE user_id = :uid");
            $stmt->execute(['uid' => $userId]);
            $count = (int)$stmt->fetchColumn();

            if ($count >= 5) {
                return;
            }

            $year = (int)date('Y');
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $dayAfter = date('Y-m-d', strtotime('+2 days'));
            $nextWeek = date('Y-m-d', strtotime('+5 days'));

            $events = [
                // Active work & interview events
                ['title' => 'Technical Interview - Shanmuga Aswin (Full Stack Java)', 'desc' => 'Candidate discussion regarding Spring Boot, React & MySQL skills. Google Meet.', 'loc' => 'https://meet.google.com/nxw-inte-rvw', 'date' => $today, 'start' => '11:00:00', 'end' => '12:00:00', 'all_day' => 0, 'cat' => 'interview', 'color' => '#d97706'],
                ['title' => 'Sprint Planning & Email Architecture Review', 'desc' => 'Sprint deliverables for Novalyx Cloud Webmail release.', 'loc' => 'Novalyx Conf Room A', 'date' => $tomorrow, 'start' => '14:30:00', 'end' => '15:30:00', 'all_day' => 0, 'cat' => 'work', 'color' => '#2563eb'],
                ['title' => 'HR Round 2 & Resume Verification Call', 'desc' => 'Discussion on package and joining timeline.', 'loc' => 'Google Meet', 'date' => $dayAfter, 'start' => '16:00:00', 'end' => '16:45:00', 'all_day' => 0, 'cat' => 'interview', 'color' => '#d97706'],
                ['title' => 'Client Project Demo & Feature Review', 'desc' => 'Live demonstration of webmail enhancements.', 'loc' => 'Zoom Meeting', 'date' => $nextWeek, 'start' => '15:00:00', 'end' => '16:00:00', 'all_day' => 0, 'cat' => 'meeting', 'color' => '#9333ea'],

                // Indian Gazetted Holidays matching user reference photo
                ['title' => 'Bahag Bihu (Assam)', 'desc' => 'Regional Harvest Festival', 'loc' => 'India', 'date' => "{$year}-04-15", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Vaisakhadi (Bengal)', 'desc' => 'Bengali New Year', 'loc' => 'India', 'date' => "{$year}-04-15", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Buddha Purnima', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-05-01", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Birthday of Rabindranath Tagore', 'desc' => 'Cultural Observance', 'loc' => 'India', 'date' => "{$year}-05-09", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Bakrid / Eid al-Adha', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-05-28", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Muharram / Ashura', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-06-26", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Rath Yatra', 'desc' => 'Regional Festival', 'loc' => 'India', 'date' => "{$year}-07-16", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Independence Day', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-08-15", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Parsi New Year', 'desc' => 'Regional Observance', 'loc' => 'India', 'date' => "{$year}-08-15", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Milad un-Nabi', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-08-26", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Gandhi Jayanti', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-10-02", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Dussehra / Vijayadashami', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-10-20", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Diwali / Deepavali', 'desc' => 'Festival of Lights', 'loc' => 'India', 'date' => "{$year}-11-08", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Children\'s Day', 'desc' => 'National Observance', 'loc' => 'India', 'date' => "{$year}-11-14", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Guru Nanak Jayanti', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-11-24", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Christmas Day', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => "{$year}-12-25", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'New Year\'s Eve', 'desc' => 'Celebration', 'loc' => 'Global', 'date' => "{$year}-12-31", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'New Year\'s Day', 'desc' => 'Global Observance', 'loc' => 'Global', 'date' => ($year + 1) . "-01-01", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Pongal / Makar Sankranti', 'desc' => 'Harvest Festival', 'loc' => 'Tamil Nadu / India', 'date' => ($year + 1) . "-01-14", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
                ['title' => 'Republic Day', 'desc' => 'National Gazetted Holiday', 'loc' => 'India', 'date' => ($year + 1) . "-01-26", 'start' => null, 'end' => null, 'all_day' => 1, 'cat' => 'holiday', 'color' => '#15803d'],
            ];

            $ins = $pdo->prepare("
                INSERT INTO calendar_events (user_id, title, description, location, event_date, start_time, end_time, is_all_day, category, color)
                VALUES (:uid, :title, :desc, :loc, :dt, :start, :end, :all_day, :cat, :col)
            ");

            foreach ($events as $ev) {
                $ins->execute([
                    'uid'     => $userId,
                    'title'   => $ev['title'],
                    'desc'    => $ev['desc'],
                    'loc'     => $ev['loc'],
                    'dt'      => $ev['date'],
                    'start'   => $ev['start'],
                    'end'     => $ev['end'],
                    'all_day' => $ev['all_day'],
                    'cat'     => $ev['cat'],
                    'col'     => $ev['color']
                ]);
            }
        } catch (\Exception $e) {
            // Silently continue if DB temporarily unavailable
        }
    }

    /**
     * Create a new calendar event
     */
    public static function create(int $userId, array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO calendar_events (
                user_id, message_id, title, description, location, 
                event_date, start_time, end_time, is_all_day, category, color
            ) VALUES (
                :uid, :mid, :title, :desc, :loc, 
                :dt, :start, :end, :all_day, :cat, :col
            )
        ");

        $colors = [
            'interview' => '#d97706', // amber/orange
            'work'      => '#2563eb', // blue
            'meeting'   => '#9333ea', // purple
            'holiday'   => '#15803d', // green
            'personal'  => '#ea580c', // orange/red
        ];

        $cat = strtolower((string)($data['category'] ?? 'interview'));
        if (!array_key_exists($cat, $colors)) {
            $cat = 'interview';
        }
        $color = $data['color'] ?? $colors[$cat];

        $stmt->execute([
            'uid'      => $userId,
            'mid'      => !empty($data['message_id']) ? (int)$data['message_id'] : null,
            'title'    => trim((string)$data['title']),
            'desc'     => !empty($data['description']) ? trim((string)$data['description']) : null,
            'loc'      => !empty($data['location']) ? trim((string)$data['location']) : null,
            'dt'       => $data['event_date'],
            'start'    => !empty($data['start_time']) ? $data['start_time'] : null,
            'end'      => !empty($data['end_time']) ? $data['end_time'] : null,
            'all_day'  => !empty($data['is_all_day']) ? 1 : 0,
            'cat'      => $cat,
            'col'      => $color,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Delete an event owned by user
     */
    public static function delete(int $id, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update an existing event owned by user
     */
    public static function update(int $id, int $userId, array $data): bool
    {
        $pdo = Database::getConnection();
        $colors = [
            'interview' => '#d97706',
            'work'      => '#2563eb',
            'meeting'   => '#9333ea',
            'holiday'   => '#15803d',
            'personal'  => '#ea580c',
        ];

        $cat = strtolower((string)($data['category'] ?? 'interview'));
        if (!array_key_exists($cat, $colors)) {
            $cat = 'interview';
        }
        $color = $data['color'] ?? $colors[$cat];

        $stmt = $pdo->prepare("
            UPDATE calendar_events
            SET title = :title,
                description = :desc,
                location = :loc,
                event_date = :dt,
                start_time = :start,
                end_time = :end,
                is_all_day = :all_day,
                category = :cat,
                color = :col,
                updated_at = NOW()
            WHERE id = :id AND user_id = :uid
        ");

        return $stmt->execute([
            'id'       => $id,
            'uid'      => $userId,
            'title'    => trim((string)$data['title']),
            'desc'     => !empty($data['description']) ? trim((string)$data['description']) : null,
            'loc'      => !empty($data['location']) ? trim((string)$data['location']) : null,
            'dt'       => $data['event_date'],
            'start'    => !empty($data['start_time']) ? $data['start_time'] : null,
            'end'      => !empty($data['end_time']) ? $data['end_time'] : null,
            'all_day'  => !empty($data['is_all_day']) ? 1 : 0,
            'cat'      => $cat,
            'col'      => $color,
        ]);
    }

    /**
     * Get single event by id
     */
    public static function getById(int $id, int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
