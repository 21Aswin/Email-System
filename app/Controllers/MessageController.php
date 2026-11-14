<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Message;
use App\Services\AuthService;
use App\Services\MailService;
use Exception;

class MessageController
{
    public function apiGetDetails(array $params): void
    {
        $userId = (int)AuthService::id();
        $messageId = (int)($params['id'] ?? 0);

        $message = Message::getDetails($userId, $messageId);
        if (!$message) {
            Response::error('Message not found or unauthorized access.', 404);
            return;
        }

        Response::json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function apiSendMessage(): void
    {
        $userId = (int)AuthService::id();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $isDraft = !empty($data['is_draft']);
        $draftId = !empty($data['draft_id']) ? (int)$data['draft_id'] : null;

        try {
            $result = MailService::saveOrSendMessage($userId, $data, $isDraft, $draftId);
            Response::success($isDraft ? 'Draft saved.' : 'Message sent successfully.', $result);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function apiToggleStar(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageId = (int)($data['message_id'] ?? 0);

        if (!$messageId) {
            Response::error('Message ID required.', 400);
            return;
        }

        $starred = isset($data['starred']) ? (bool)$data['starred'] : null;
        $newStatus = Message::toggleStar($userId, $messageId, $starred);

        Response::success('Updated star status', ['is_starred' => $newStatus]);
    }

    public function apiMarkRead(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageIds = array_map('intval', (array)($data['message_ids'] ?? []));
        $isRead = !empty($data['is_read']);

        if (empty($messageIds)) {
            Response::error('No messages selected.', 400);
            return;
        }

        Message::markAsRead($userId, $messageIds, $isRead);
        Response::success('Messages marked as ' . ($isRead ? 'read' : 'unread'));
    }

    public function apiMoveFolder(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageIds = array_map('intval', (array)($data['message_ids'] ?? []));
        $folder = strtolower(trim($data['folder'] ?? ''));

        $allowedFolders = ['inbox', 'archive', 'trash', 'spam', 'snoozed'];
        if (!in_array($folder, $allowedFolders, true)) {
            Response::error('Invalid target folder.', 400);
            return;
        }

        if (empty($messageIds)) {
            Response::error('No messages selected.', 400);
            return;
        }

        Message::moveToFolder($userId, $messageIds, $folder);
        Response::success("Moved messages to {$folder}.");
    }

    public function apiToggleImportant(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageId = (int)($data['message_id'] ?? 0);

        if (!$messageId) {
            Response::error('Message ID required.', 400);
            return;
        }

        $important = isset($data['important']) ? (bool)$data['important'] : null;
        $newStatus = Message::toggleImportant($userId, $messageId, $important);

        Response::success('Updated importance status', ['is_important' => $newStatus]);
    }

    public function apiSnooze(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageIds = array_map('intval', (array)($data['message_ids'] ?? []));
        $until = trim((string)($data['until'] ?? ''));

        if (empty($messageIds)) {
            Response::error('No messages selected.', 400);
            return;
        }

        if (empty($until)) {
            // Default snooze: tomorrow at 08:00 AM
            $timestamp = strtotime('+1 day 08:00:00');
        } else {
            $timestamp = strtotime($until);
            if ($timestamp === false) {
                $timestamp = strtotime('+1 day 08:00:00');
            }
        }

        $formattedUntil = date('Y-m-d H:i:s', $timestamp);
        Message::snooze($userId, $messageIds, $formattedUntil);
        Response::success("Snoozed until " . date('M j, g:i A', $timestamp), ['until' => $formattedUntil]);
    }

    public function apiUnsnooze(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageIds = array_map('intval', (array)($data['message_ids'] ?? []));

        if (empty($messageIds)) {
            Response::error('No messages selected.', 400);
            return;
        }

        Message::unsnooze($userId, $messageIds);
        Response::success('Restored to Inbox.');
    }

    public function apiSetCategory(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageIds = array_map('intval', (array)($data['message_ids'] ?? []));
        $category = strtolower(trim((string)($data['category'] ?? '')));

        if (empty($messageIds) || empty($category)) {
            Response::error('Message IDs and category required.', 400);
            return;
        }

        Message::setCategory($userId, $messageIds, $category);
        Response::success("Category set to {$category}.");
    }

    public function apiEmptyFolder(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $folder = strtolower(trim((string)($data['folder'] ?? '')));

        if (!in_array($folder, ['trash', 'spam'], true)) {
            Response::error('Can only empty Trash or Spam.', 400);
            return;
        }

        try {
            $count = Message::emptyFolder($userId, $folder);
            Response::success("Deleted {$count} item(s) from {$folder}.");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function apiDeletePermanently(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageIds = array_map('intval', (array)($data['message_ids'] ?? []));

        if (empty($messageIds)) {
            Response::error('No messages selected.', 400);
            return;
        }

        try {
            Message::deletePermanently($userId, $messageIds);
            Response::success('Messages permanently deleted.');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function apiToggleLabel(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageId = (int)($data['message_id'] ?? 0);
        $labelId = (int)($data['label_id'] ?? 0);
        $apply = !empty($data['apply']);

        if (!$messageId || !$labelId) {
            Response::error('Message and Label IDs are required.', 400);
            return;
        }

        Message::toggleLabel($userId, $messageId, $labelId, $apply);
        Response::success('Label updated successfully.');
    }
}
