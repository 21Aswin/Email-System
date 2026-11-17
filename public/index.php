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
$router->post('/api/messages/mark-all-read', [\App\Controllers\MessageController::class, 'apiMarkAllRead'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
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
$router->post('/api/calendar/events/update', [\App\Controllers\CalendarController::class, 'apiUpdateEvent'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);
$router->post('/api/calendar/events/delete', [\App\Controllers\CalendarController::class, 'apiDeleteEvent'], [\App\Middleware\AuthMiddleware::class, \App\Middleware\CsrfMiddleware::class]);

// Dispatch incoming request with robust error handling
try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (\Throwable $e) {
    error_log("UNCAUGHT EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    $showDetails = filter_var(\App\Core\Env::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN) || isset($_GET['debug']);
    if ($showDetails) {
        echo "<div style='font-family:-apple-system,BlinkMacSystemFont,sans-serif; padding:30px; background:#fff1f0; border:1px solid #ffa39e; margin:20px; border-radius:8px;'>";
        echo "<h2 style='color:#cf1322; margin-top:0;'>Application Error (500)</h2>";
        echo "<p style='font-size:16px;'><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Location:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")</p>";
        echo "<h3 style='margin-top:20px;'>Stack Trace:</h3>";
        echo "<pre style='background:#ffffff; padding:15px; border:1px solid #d9d9d9; border-radius:6px; overflow:auto; font-size:13px; line-height:1.5;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='font-family:-apple-system,BlinkMacSystemFont,sans-serif; text-align:center; padding:80px 20px;'>";
        echo "<h1 style='color:#1a73e8; font-size:56px; margin:0;'>500</h1>";
        echo "<h2 style='color:#202124; margin:12px 0;'>Internal Server Error</h2>";
        echo "<p style='color:#5f6368; max-width:480px; margin:0 auto 24px;'>An unexpected server error occurred. Please try again or contact support.</p>";
        echo "<a href='/login' style='display:inline-block; padding:10px 24px; background:#1a73e8; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600;'>Return to Sign In</a>";
        echo "</div>";
    }
}
