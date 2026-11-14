<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;
use App\Models\Message;
use App\Models\Label;
use App\Services\AuthService;

class MailboxController
{
    public function index(array $params = []): void
    {
        $userId = (int)AuthService::id();
        $folder = strtolower($params['folder'] ?? 'inbox');
        $validFolders = ['inbox', 'starred', 'snoozed', 'sent', 'drafts', 'archive', 'spam', 'trash', 'all', 'purchases', 'important', 'scheduled'];
        if (!in_array($folder, $validFolders, true)) {
            $folder = 'inbox';
        }

        $category = $_GET['category'] ?? ($folder === 'inbox' ? 'primary' : '');
        $labelId = isset($_GET['label']) ? (int)$_GET['label'] : null;
        $search = $_GET['q'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));

        $counts = Message::getFolderCounts($userId);
        $labels = Label::getByUser($userId);
        $initialData = Message::getList($userId, $folder, $category, $labelId, $search, [], $page, 30);

        View::render('mailbox/index', [
            'title' => ucfirst($folder) . ' - nkinfyx groups Webmail',
            'user' => AuthService::user(),
            'currentFolder' => $folder,
            'currentCategory' => $category,
            'currentLabelId' => $labelId,
            'searchQuery' => $search,
            'counts' => $counts,
            'labels' => $labels,
            'messages' => $initialData['messages'],
            'total' => $initialData['total'],
            'page' => $initialData['page'],
            'totalPages' => $initialData['total_pages']
        ], 'layouts/main');
    }

    public function apiGetMessages(): void
    {
        $userId = (int)AuthService::id();
        $folder = strtolower($_GET['folder'] ?? 'inbox');
        $category = $_GET['category'] ?? '';
        $labelId = !empty($_GET['label_id']) ? (int)$_GET['label_id'] : null;
        $search = $_GET['q'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(10, (int)($_GET['limit'] ?? 30)));

        $advancedFilters = [
            'from' => $_GET['adv_from'] ?? '',
            'to' => $_GET['adv_to'] ?? '',
            'subject' => $_GET['adv_subject'] ?? '',
            'has_attachment' => !empty($_GET['adv_has_attachment']),
            'date_from' => $_GET['adv_date_from'] ?? '',
            'date_to' => $_GET['adv_date_to'] ?? ''
        ];

        $data = Message::getList($userId, $folder, $category, $labelId, $search, $advancedFilters, $page, $limit);
        Response::json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function apiGetCounts(): void
    {
        $userId = (int)AuthService::id();
        $restored = Message::checkSnoozeExpiries($userId);
        $counts = Message::getFolderCounts($userId);
        Response::json([
            'success' => true,
            'counts' => $counts,
            'restored_snooze_count' => $restored
        ]);
    }

    public function apiSyncInbox(): void
    {
        $userId = (int)AuthService::id();
        $restored = Message::checkSnoozeExpiries($userId);
        try {
            $syncService = new \App\Services\ImapSyncService();
            $result = $syncService->syncInbox($userId);
            $result['restored_snoozes'] = $restored;
            Response::success($result['message'], $result);
        } catch (\Exception $e) {
            Response::error('Sync notice: ' . $e->getMessage(), 200);
        }
    }

    public function apiUpdateSmtpSettings(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $enabled = !empty($data['enabled']) ? 'true' : 'false';
        $host = trim($data['host'] ?? 'smtp.gmail.com');
        $port = trim((string)($data['port'] ?? '587'));
        $encryption = trim($data['encryption'] ?? 'tls');
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');
        $sender = trim($data['sender'] ?? $username);

        $envFile = ROOT_DIR . '/.env';
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            $replacements = [
                'SMTP_ENABLED' => $enabled,
                'SMTP_HOST' => $host,
                'SMTP_PORT' => $port,
                'SMTP_ENCRYPTION' => $encryption,
                'SMTP_USERNAME' => $username,
                'SMTP_PASSWORD' => $password,
                'SMTP_FROM_ADDRESS' => $sender
            ];

            foreach ($replacements as $key => $val) {
                if (preg_match("/^{$key}=.*/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$val}", $content);
                } else {
                    $content .= "\n{$key}={$val}";
                }
            }

            file_put_contents($envFile, $content);
        }

        Response::success('SMTP Settings updated successfully.', [
            'enabled' => $enabled === 'true'
        ]);
    }
}
