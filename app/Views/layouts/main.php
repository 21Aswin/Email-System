<!DOCTYPE html>
<html lang="en">
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
<body class="nkinfyx-app">
    <!-- Sidebar Backdrop Overlay for Mobile -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

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

            <!-- Right Companion Rail -->
            <aside class="app-companion-rail" id="appCompanionRail" aria-label="Side Panel Tools">
                <button type="button" class="companion-icon-btn" id="railCalendarBtn" title="Calendar & Schedules" onclick="window.MailApp.calendar.toggle()">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <text x="12" y="18" font-size="7" font-weight="bold" fill="#2563eb" text-anchor="middle" stroke="none"><?= date('j') ?></text>
                    </svg>
                </button>
                <button type="button" class="companion-icon-btn" title="Keep Notes" onclick="window.MailApp.showToast('Google Keep Notes integration active')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#eab308" stroke-width="2">
                        <path d="M12 2a7 7 0 0 0-7 7c0 2.38 1.19 4.47 3 5.74V17a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-2.26c1.81-1.27 3-3.36 3-5.74a7 7 0 0 0-7-7z"></path>
                        <line x1="10" y1="22" x2="14" y2="22"></line>
                    </svg>
                </button>
                <button type="button" class="companion-icon-btn" title="Tasks" onclick="window.MailApp.showToast('Tasks integration active')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16 10 11 15 8 12"></polyline>
                    </svg>
                </button>
                <button type="button" class="companion-icon-btn" title="Contacts" onclick="window.MailApp.showToast('Contacts integration active')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </button>
            </aside>
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

    <!-- App Scripts -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
