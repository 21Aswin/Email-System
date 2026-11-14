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
        $pdo = Database::getConnection();
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("
            SELECT id, title, event_date, start_time, end_time, is_all_day, category, color 
            FROM calendar_events 
            WHERE user_id = :uid AND event_date BETWEEN :start AND :end
            ORDER BY event_date ASC, is_all_day DESC, start_time ASC
        ");
        $stmt->execute(['uid' => $userId, 'start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming events (from today onwards, or recent + upcoming)
     */
    public static function getUpcoming(int $userId, int $limit = 35): array
    {
        $pdo = Database::getConnection();
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT * FROM calendar_events 
            WHERE user_id = :uid AND event_date >= :today
            ORDER BY event_date ASC, is_all_day DESC, start_time ASC
            LIMIT :lim
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
