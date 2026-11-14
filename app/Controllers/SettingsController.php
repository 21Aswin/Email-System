<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Label;
use App\Models\User;
use App\Models\UserSettings;
use App\Services\AuthService;
use Exception;

class SettingsController
{
    /**
     * Render main Settings Page
     */
    public function index(): void
    {
        $userId = (int)AuthService::id();
        $user = User::findById($userId);
        $settings = UserSettings::get($userId);
        $blockedSenders = UserSettings::getBlockedSenders($userId);
        $labels = Label::getByUser($userId);

        // Safe SMTP status (NEVER leak password or secrets)
        $smtpConfigured = !empty(Env::get('SMTP_HOST')) && !empty(Env::get('SMTP_USERNAME'));
        $smtpDetails = [
            'configured' => $smtpConfigured,
            'host' => Env::get('SMTP_HOST', 'smtp.gmail.com'),
            'port' => Env::get('SMTP_PORT', 587),
            'username' => Env::get('SMTP_USERNAME', ''),
            'from_address' => Env::get('SMTP_FROM_ADDRESS', Env::get('SMTP_USERNAME', '')),
            'from_name' => Env::get('SMTP_FROM_NAME', 'Novalyx Technologies')
        ];

        // Active Session Information (safe, no session IDs exposed)
        $sessionInfo = [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Web Browser',
            'started_at' => date('M j, Y, g:i A', Session::get('login_time') ?: time())
        ];

        View::render('settings/index', [
            'title' => 'Settings - Novalyx Cloud Webmail',
            'user' => $user,
            'settings' => $settings,
            'blockedSenders' => $blockedSenders,
            'labels' => $labels,
            'smtp' => $smtpDetails,
            'sessionInfo' => $sessionInfo,
            'currentFolder' => 'settings',
            'counts' => \App\Models\Message::getFolderCounts($userId)
        ], 'layouts/main');
    }

    /**
     * API: Save updated user preferences
     */
    public function apiUpdateSettings(): void
    {
        $userId = (int)AuthService::id();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        try {
            $updated = UserSettings::update($userId, $data);
            Response::success('Settings saved successfully.', ['settings' => $updated]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * API: Secure Avatar Upload
     */
    public function apiUploadAvatar(): void
    {
        $userId = (int)AuthService::id();

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            Response::error('No valid image file uploaded.', 400);
            return;
        }

        $file = $_FILES['avatar'];

        // Size limit: 2MB
        if ($file['size'] > 2 * 1024 * 1024) {
            Response::error('Image file must not exceed 2MB.', 400);
            return;
        }

        // Validate actual MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedMimes[$mime])) {
            Response::error('Only JPEG, PNG, and WebP images are allowed.', 400);
            return;
        }

        // Validate image dimensions
        $imgSize = @getimagesize($file['tmp_name']);
        if ($imgSize === false) {
            Response::error('Uploaded file is not a valid image.', 400);
            return;
        }

        // Target directory: public/uploads/avatars/
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = $allowedMimes[$mime];
        $randomName = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $randomName;
        $publicUrl = '/uploads/avatars/' . $randomName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            Response::error('Failed to store uploaded avatar image.', 500);
            return;
        }

        // Update user record
        $user = User::findById($userId);
        UserSettings::updateProfile($userId, $user['display_name'], $publicUrl);

        // Update session user cache
        $cachedUser = Session::get('user');
        if (is_array($cachedUser)) {
            $cachedUser['avatar_path'] = $publicUrl;
            Session::set('user', $cachedUser);
        }

        Response::success('Profile photo updated successfully.', ['avatar_url' => $publicUrl]);
    }

    /**
     * API: Update Display Name Profile
     */
    public function apiUpdateProfile(): void
    {
        $userId = (int)AuthService::id();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $name = trim((string)($data['display_name'] ?? ''));
        if (empty($name)) {
            Response::error('Display name cannot be empty.', 400);
            return;
        }

        try {
            UserSettings::updateProfile($userId, $name);
            $cachedUser = Session::get('user');
            if (is_array($cachedUser)) {
                $cachedUser['display_name'] = $name;
                Session::set('user', $cachedUser);
            }
            Response::success('Profile name updated successfully.');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * API: Change Account Password
     */
    public function apiChangePassword(): void
    {
        $userId = (int)AuthService::id();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $current = (string)($data['current_password'] ?? '');
        $new = (string)($data['new_password'] ?? '');
        $confirm = (string)($data['confirm_password'] ?? '');

        if ($new !== $confirm) {
            Response::error('New password and confirmation do not match.', 400);
            return;
        }

        $res = UserSettings::changePassword($userId, $current, $new);
        if ($res['success']) {
            Response::success($res['message']);
        } else {
            Response::error($res['message'], 400);
        }
    }

    /**
     * API: Add Blocked Sender
     */
    public function apiAddBlockedSender(): void
    {
        $userId = (int)AuthService::id();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $email = trim((string)($data['email'] ?? ''));

        try {
            UserSettings::addBlockedSender($userId, $email);
            Response::success('Sender added to block list.', [
                'blocked' => UserSettings::getBlockedSenders($userId)
            ]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * API: Remove Blocked Sender
     */
    public function apiRemoveBlockedSender(): void
    {
        $userId = (int)AuthService::id();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $id = $data['id'] ?? null;
        $email = $data['email'] ?? null;
        $target = $id ?: $email;

        try {
            UserSettings::removeBlockedSender($userId, $target);
            Response::success('Sender removed from block list.', [
                'blocked' => UserSettings::getBlockedSenders($userId)
            ]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * API: Permanently Delete Account
     */
    public function apiDeleteAccount(): void
    {
        $userId = (int)AuthService::id();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;
        $password = (string)($data['password'] ?? '');

        $res = UserSettings::deleteAccount($userId, $password);
        if ($res['success']) {
            Session::destroy();
            Response::success($res['message'], ['redirect' => '/login']);
        } else {
            Response::error($res['message'], 400);
        }
    }
}
