<?php
$layoutUser = $user ?? \App\Services\AuthService::user();
$layoutUserId = (int)($layoutUser['id'] ?? 0);
$layoutSettings = $settings ?? ($layoutUserId > 0 ? \App\Models\UserSettings::get($layoutUserId) : []);
$activeTheme = $layoutSettings['theme'] ?? 'system';
$activeDensity = $layoutSettings['density'] ?? 'comfortable';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= \App\Core\View::e($activeTheme) ?>" data-density="<?= \App\Core\View::e($activeDensity) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Core\Session::getCsrfToken() ?>">
    <title><?= \App\Core\View::e($title ?? 'Novalyx Cloud Mail') ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo.svg">
    
    <!-- Google Fonts: Inter / Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- App Styles -->
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="nkinfyx-app" data-theme="<?= \App\Core\View::e($activeTheme) ?>" data-density="<?= \App\Core\View::e($activeDensity) ?>">
    <!-- Sidebar Backdrop Overlay for Mobile -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Calendar Backdrop Overlay for Mobile -->
    <div class="calendar-backdrop" id="calendarBackdrop" onclick="window.MailApp.calendar.close()"></div>

    <div class="app-container">
        <!-- Top Navigation Bar -->
        <?php require dirname(__DIR__) . '/partials/topbar.php'; ?>

        <div class="app-body">
            <!-- Left Sidebar Navigation -->
            <?php require dirname(__DIR__) . '/partials/sidebar.php'; ?>

            <!-- Main Content Area -->
            <main class="app-main" id="appMain">
                <?= $content ?? '' ?>
            </main>

            <!-- Right Calendar Side Panel -->
            <?php require dirname(__DIR__) . '/partials/calendar_panel.php'; ?>
        </div>
    </div>

    <!-- Mobile Floating Compose Action Button (FAB) -->
    <button type="button" class="mobile-compose-fab" id="mobileComposeFab" title="Compose Email" aria-label="Compose">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 5v14M5 12h14"></path>
        </svg>
    </button>

    <!-- Floating Compose Modal -->
    <?php require dirname(__DIR__) . '/partials/compose_modal.php'; ?>

    <!-- Advanced Search Modal -->
    <?php require dirname(__DIR__) . '/partials/advanced_search_modal.php'; ?>

    <!-- Create/Edit Label Modal -->
    <?php require dirname(__DIR__) . '/partials/label_modal.php'; ?>

    <!-- Manage Labels Modal -->
    <?php require dirname(__DIR__) . '/partials/manage_labels_modal.php'; ?>

    <!-- Manage Subscriptions Modal -->
    <?php require dirname(__DIR__) . '/partials/subscriptions_modal.php'; ?>

    <!-- Snooze Custom Date Picker Modal -->
    <?php require dirname(__DIR__) . '/partials/snooze_modal.php'; ?>

    <!-- Scheduled Sending Modal -->
    <?php require dirname(__DIR__) . '/partials/scheduled_modal.php'; ?>

    <!-- SMTP Delivery Settings Modal -->
    <?php require dirname(__DIR__) . '/partials/settings_modal.php'; ?>

    <!-- Sender Contact Profile Hovercard -->
    <?php require dirname(__DIR__) . '/partials/sender_hovercard.php'; ?>

    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer" aria-live="polite"></div>

    <!-- Inject User Settings into Global Client Scope -->
    <script>
        window.CURRENT_USER_SETTINGS = <?= json_encode($layoutSettings, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>

    <!-- App Scripts -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
