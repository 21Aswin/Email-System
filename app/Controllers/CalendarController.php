<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\CalendarEvent;
use App\Services\AuthService;

class CalendarController
{
    /**
     * Get events for a specific date or an entire month
     */
    public function apiGetEvents(): void
    {
        $userId = (int)AuthService::id();
        CalendarEvent::ensureUserHasEvents($userId);

        $date = trim((string)($_GET['date'] ?? ''));
        $month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
        $year = isset($_GET['year']) ? (int)$_GET['year'] : 0;

        if (!empty($date)) {
            $events = CalendarEvent::getByDate($userId, $date);
            $upcoming = CalendarEvent::getUpcoming($userId, 40, $date);
            Response::json([
                'success' => true,
                'events' => $events,
                'upcoming' => $upcoming,
                'date' => $date
            ]);
            return;
        }

        if ($month > 0 && $year > 0) {
            $events = CalendarEvent::getByMonth($userId, $year, $month);
            $monthStart = sprintf('%04d-%02d-01', $year, $month);
            $today = date('Y-m-d');
            $currentYearMonth = date('Y-m');
            $viewYearMonth = sprintf('%04d-%02d', $year, $month);
            $agendaStart = ($viewYearMonth === $currentYearMonth) ? $today : $monthStart;
            $upcoming = CalendarEvent::getUpcoming($userId, 40, $agendaStart);

            Response::json([
                'success' => true,
                'events' => $events,
                'upcoming' => $upcoming,
                'year' => $year,
                'month' => $month
            ]);
            return;
        }

        // Default: upcoming events
        $events = CalendarEvent::getUpcoming($userId);
        Response::json([
            'success' => true,
            'events' => $events,
            'upcoming' => $events
        ]);
    }

    /**
     * Get upcoming agenda events
     */
    public function apiGetUpcoming(): void
    {
        $userId = (int)AuthService::id();
        CalendarEvent::ensureUserHasEvents($userId);
        $limit = min(50, max(5, (int)($_GET['limit'] ?? 40)));
        $from = trim((string)($_GET['from'] ?? ''));
        $events = CalendarEvent::getUpcoming($userId, $limit, $from);
        Response::json([
            'success' => true,
            'events' => $events
        ]);
    }

    /**
     * Create a new event (e.g. from interview email or custom scheduling)
     */
    public function apiCreateEvent(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $title = trim((string)($data['title'] ?? ''));
        $eventDate = trim((string)($data['event_date'] ?? ''));

        if (empty($title)) {
            Response::error('Event title is required.', 400);
            return;
        }

        if (empty($eventDate) || !strtotime($eventDate)) {
            $eventDate = date('Y-m-d');
        }

        $eventId = CalendarEvent::create($userId, [
            'title'       => $title,
            'description' => $data['description'] ?? '',
            'location'    => $data['location'] ?? '',
            'event_date'  => $eventDate,
            'start_time'  => !empty($data['start_time']) ? trim((string)$data['start_time']) : null,
            'end_time'    => !empty($data['end_time']) ? trim((string)$data['end_time']) : null,
            'is_all_day'  => !empty($data['is_all_day']) ? 1 : 0,
            'category'    => $data['category'] ?? 'interview',
            'message_id'  => !empty($data['message_id']) ? (int)$data['message_id'] : null,
        ]);

        $created = CalendarEvent::getById($eventId, $userId);
        Response::success('Event scheduled successfully!', [
            'event' => $created
        ]);
    }

    /**
     * Delete an event
     */
    public function apiDeleteEvent(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            Response::error('Invalid event ID.', 400);
            return;
        }

        $deleted = CalendarEvent::delete($id, $userId);
        if ($deleted) {
            Response::success('Event deleted successfully.');
        } else {
            Response::error('Event not found or unauthorized.', 404);
        }
    }
}
