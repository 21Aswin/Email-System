<?php
declare(strict_types=1);

// Configure Timezone to local IST (+05:30)
date_default_timezone_set('Asia/Kolkata');

// Error reporting configuration
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);

// Root directories
define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
define('PUBLIC_DIR', __DIR__);

// Autoloader & Environment
require_once APP_DIR . '/Core/Autoloader.php';
\App\Core\Autoloader::register();

\App\Core\Env::load(ROOT_DIR . '/.env');

// If in debug mode, show errors
if (filter_var(\App\Core\Env::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', '1');
}

// Initialize View Renderer
\App\Core\View::init(APP_DIR . '/Views');

// Start Router
$router = new \App\Core\Router();

// -------------------------------------------------------------
// Authentication Routes
// -------------------------------------------------------------
$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin'], [\App\Middleware\GuestMiddleware::class]);
$router->post('/login', [\App\Controllers\AuthController::class, 'login'], [\App\Middleware\CsrfMiddleware::class]);

$router->get('/register', [\App\Controllers\AuthController::class, 'showRegister'], [\App\Middleware\GuestMiddleware::class]);
$router->post('/register', [\App\Controllers\AuthController::class, 'register'], [\App\Middleware\CsrfMiddleware::class]);

$router->post('/logout', [\App\Controllers\AuthController::class, 'logout'], [\App\Middleware\CsrfMiddleware::class]);

// Default redirect root to inbox or login
$router->get('/', function() {
    \App\Core\Session::start();
    if (\App\Core\Session::get('user')) {
        \App\Core\Response::redirect('/mail/inbox');
    } else {
        \App\Core\Response::redirect('/login');
    }
});

// -------------------------------------------------------------
// Mailbox & Settings Views (Protected with AuthMiddleware)
// -------------------------------------------------------------
$router->get('/settings', [\App\Controllers\SettingsController::class, 'index'], [\App\Middleware\AuthMiddleware::class]);
$router->get('/mail/{folder}', [\App\Controllers\MailboxController::class, 'index'], [\App\Middleware\AuthMiddleware::class]);

// -------------------------------------------------------------
// Settings APIs
// -------------------------------------------------------------
$router->post('/api/settings/profile', [\App\Controllers\SettingsController::class, 'apiUpdateProfile'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/settings/avatar', [\App\Controllers\SettingsController::class, 'apiUploadAvatar'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/settings/preferences', [\App\Controllers\SettingsController::class, 'apiUpdateSettings'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/settings/password', [\App\Controllers\SettingsController::class, 'apiChangePassword'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/settings/blocked-senders', [\App\Controllers\SettingsController::class, 'apiAddBlockedSender'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/settings/blocked-senders/remove', [\App\Controllers\SettingsController::class, 'apiRemoveBlockedSender'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/settings/delete-account', [\App\Controllers\SettingsController::class, 'apiDeleteAccount'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);

// -------------------------------------------------------------
// Asynchronous JSON API Routes
// -------------------------------------------------------------
// Message list & counts
$router->get('/api/messages', [\App\Controllers\MailboxController::class, 'apiGetMessages'], [\App\Middleware\AuthMiddleware::class]);
$router->get('/api/counts', [\App\Controllers\MailboxController::class, 'apiGetCounts'], [\App\Middleware\AuthMiddleware::class]);
$router->post('/api/sync', [\App\Controllers\MailboxController::class, 'apiSyncInbox'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/settings/smtp', [\App\Controllers\MailboxController::class, 'apiUpdateSmtpSettings'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);

// Single message detail & actions
$router->get('/api/messages/{id}', [\App\Controllers\MessageController::class, 'apiGetDetails'], [\App\Middleware\AuthMiddleware::class]);
$router->post('/api/messages/send', [\App\Controllers\MessageController::class, 'apiSendMessage'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/star', [\App\Controllers\MessageController::class, 'apiToggleStar'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/important', [\App\Controllers\MessageController::class, 'apiToggleImportant'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/snooze', [\App\Controllers\MessageController::class, 'apiSnooze'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/unsnooze', [\App\Controllers\MessageController::class, 'apiUnsnooze'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/category', [\App\Controllers\MessageController::class, 'apiSetCategory'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/mark-read', [\App\Controllers\MessageController::class, 'apiMarkRead'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/move', [\App\Controllers\MessageController::class, 'apiMoveFolder'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/empty-folder', [\App\Controllers\MessageController::class, 'apiEmptyFolder'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/messages/permanent-delete', [\App\Controllers\MessageController::class, 'apiDeletePermanently'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);

// Labels API
$router->get('/api/labels', [\App\Controllers\LabelController::class, 'apiGetLabels'], [\App\Middleware\AuthMiddleware::class]);
$router->post('/api/labels', [\App\Controllers\LabelController::class, 'apiCreateLabel'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/labels/{id}', [\App\Controllers\LabelController::class, 'apiUpdateLabel'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/labels/{id}/delete', [\App\Controllers\LabelController::class, 'apiDeleteLabel'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);

// Attachments API
$router->post('/api/attachments/upload', [\App\Controllers\AttachmentController::class, 'upload'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->get('/api/attachments/{id}/download', [\App\Controllers\AttachmentController::class, 'download'], [\App\Middleware\AuthMiddleware::class]);

// Calendar & Events API
$router->get('/api/calendar/events', [\App\Controllers\CalendarController::class, 'apiGetEvents'], [\App\Middleware\AuthMiddleware::class]);
$router->get('/api/calendar/upcoming', [\App\Controllers\CalendarController::class, 'apiGetUpcoming'], [\App\Middleware\AuthMiddleware::class]);
$router->post('/api/calendar/events', [\App\Controllers\CalendarController::class, 'apiCreateEvent'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/calendar/events/delete', [\App\Controllers\CalendarController::class, 'apiDeleteEvent'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);

// Dispatch incoming request
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
