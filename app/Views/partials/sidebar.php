<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-compose-container">
        <button type="button" class="btn btn-compose" id="btnComposeOpen">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14"></path>
            </svg>
            <span>Compose</span>
        </button>
    </div>

    <nav class="sidebar-nav" aria-label="Mailbox folders">
        <div class="nav-section">
            <!-- 1. Inbox -->
            <a href="/mail/inbox" class="nav-item <?= ($currentFolder === 'inbox' && empty($currentLabelId)) ? 'active' : '' ?>" data-folder="inbox">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H4.99c-1.11 0-1.98.89-1.98 2L3 19c0 1.1.88 2 1.99 2H19c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 12h-4c0 1.66-1.35 3-3 3s-3-1.34-3-3H4.99V5H19v10z"/></svg>
                </span>
                <span class="nav-label">Inbox</span>
                <span class="nav-badge" id="badge-inbox" <?= empty($counts['inbox']['unread']) ? 'style="display:none;"' : '' ?>>
                    <?= (int)($counts['inbox']['unread'] ?? 0) ?>
                </span>
            </a>

            <!-- 2. Starred -->
            <a href="/mail/starred" class="nav-item <?= ($currentFolder === 'starred') ? 'active' : '' ?>" data-folder="starred">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </span>
                <span class="nav-label">Starred</span>
                <span class="nav-badge badge-neutral" id="badge-starred" <?= empty($counts['starred']['total']) ? 'style="display:none;"' : '' ?>>
                    <?= (int)($counts['starred']['total'] ?? 0) ?>
                </span>
            </a>

            <!-- 3. Snoozed -->
            <a href="/mail/snoozed" class="nav-item <?= ($currentFolder === 'snoozed') ? 'active' : '' ?>" data-folder="snoozed">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                </span>
                <span class="nav-label">Snoozed</span>
                <span class="nav-badge badge-neutral" id="badge-snoozed" <?= empty($counts['snoozed']['total']) ? 'style="display:none;"' : '' ?>>
                    <?= (int)($counts['snoozed']['total'] ?? 0) ?>
                </span>
            </a>

            <!-- 4. Sent -->
            <a href="/mail/sent" class="nav-item <?= ($currentFolder === 'sent') ? 'active' : '' ?>" data-folder="sent">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </span>
                <span class="nav-label">Sent</span>
            </a>

            <!-- 5. Drafts -->
            <a href="/mail/drafts" class="nav-item <?= ($currentFolder === 'drafts') ? 'active' : '' ?>" data-folder="drafts">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                </span>
                <span class="nav-label">Drafts</span>
                <span class="nav-badge badge-neutral" id="badge-drafts" <?= empty($counts['drafts']['total']) ? 'style="display:none;"' : '' ?>>
                    <?= (int)($counts['drafts']['total'] ?? 0) ?>
                </span>
            </a>

            <!-- 6. Purchases (Smart Category) -->
            <a href="/mail/purchases" class="nav-item <?= ($currentFolder === 'purchases') ? 'active' : '' ?>" data-folder="purchases" title="Purchase & order related emails">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h14v12zm-7-8c-1.66 0-3-1.34-3-3H7c0 2.76 2.24 5 5 5s5-2.24 5-5h-2c0 1.66-1.34 3-3 3z"/></svg>
                </span>
                <span class="nav-label">Purchases</span>
                <span class="nav-badge" id="badge-purchases" <?= empty($counts['purchases']['unread']) ? 'style="display:none;"' : '' ?>>
                    <?= (int)($counts['purchases']['unread'] ?? 0) ?>
                </span>
            </a>

            <!-- 7. Google-Style Calendar & Agenda Link -->
            <button type="button" class="nav-item btn-sidebar-calendar" id="sidebarCalendarBtn" onclick="window.MailApp.calendar.toggle();" title="Google-style Calendar & Work Schedules">
                <span class="nav-icon" style="color: #1a73e8;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="3" stroke="#1a73e8" fill="none"></rect>
                        <line x1="16" y1="2" x2="16" y2="6" stroke="#1a73e8"></line>
                        <line x1="8" y1="2" x2="8" y2="6" stroke="#1a73e8"></line>
                        <line x1="3" y1="10" x2="21" y2="10" stroke="#1a73e8"></line>
                    </svg>
                </span>
                <span class="nav-label" style="font-weight: 600;">Calendar</span>
                <span class="nav-badge badge-neutral" style="background: #e8f0fe; color: #1a73e8; font-weight: 700;">
                    <?= date('M j') ?>
                </span>
            </button>

            <!-- 7. More / Less Expandable Toggle Button -->
            <button type="button" class="nav-item btn-toggle-more" id="btnToggleMore" aria-expanded="false" aria-controls="moreNavSection">
                <span class="nav-icon">
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/></svg>
                </span>
                <span class="nav-label" id="lblToggleMore">More</span>
            </button>

            <!-- Expandable Section (Important, Scheduled, All Mail, Spam, Trash, Subscriptions, Manage Labels) -->
            <div class="more-nav-section" id="moreNavSection" style="display: none;">
                <!-- Important -->
                <a href="/mail/important" class="nav-item <?= ($currentFolder === 'important') ? 'active' : '' ?>" data-folder="important">
                    <span class="nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7c-1.1 0-1.99.9-1.99 2L5 21l7-3 7 3V5c0-1.1-.9-2-2-2zm0 15l-5-2.18L7 18V5h10v13z"/></svg>
                    </span>
                    <span class="nav-label">Important</span>
                    <span class="nav-badge badge-neutral" id="badge-important" <?= empty($counts['important']['total']) ? 'style="display:none;"' : '' ?>>
                        <?= (int)($counts['important']['total'] ?? 0) ?>
                    </span>
                </a>

                <!-- Scheduled -->
                <a href="/mail/scheduled" class="nav-item <?= ($currentFolder === 'scheduled') ? 'active' : '' ?>" data-folder="scheduled">
                    <span class="nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5z"/></svg>
                    </span>
                    <span class="nav-label">Scheduled</span>
                    <span class="nav-badge badge-neutral" id="badge-scheduled" <?= empty($counts['scheduled']['total']) ? 'style="display:none;"' : '' ?>>
                        <?= (int)($counts['scheduled']['total'] ?? 0) ?>
                    </span>
                </a>

                <!-- All Mail (Archive) -->
                <a href="/mail/all" class="nav-item <?= ($currentFolder === 'all') ? 'active' : '' ?>" data-folder="all">
                    <span class="nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>
                    </span>
                    <span class="nav-label">All Mail</span>
                </a>

                <!-- Spam -->
                <a href="/mail/spam" class="nav-item <?= ($currentFolder === 'spam') ? 'active' : '' ?>" data-folder="spam">
                    <span class="nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z"/></svg>
                    </span>
                    <span class="nav-label">Spam</span>
                    <span class="nav-badge badge-neutral" id="badge-spam" <?= empty($counts['spam']['unread']) ? 'style="display:none;"' : '' ?>>
                        <?= (int)($counts['spam']['unread'] ?? 0) ?>
                    </span>
                </a>

                <!-- Trash -->
                <a href="/mail/trash" class="nav-item <?= ($currentFolder === 'trash') ? 'active' : '' ?>" data-folder="trash">
                    <span class="nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/></svg>
                    </span>
                    <span class="nav-label">Trash</span>
                </a>

                <div class="sidebar-divider" style="margin: 8px 12px;"></div>

                <!-- Manage Subscriptions -->
                <button type="button" class="nav-item btn-sidebar-action" onclick="window.MailApp.openSubscriptionsModal()">
                    <span class="nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                    </span>
                    <span class="nav-label">Manage subscriptions</span>
                </button>

                <!-- Manage Labels -->
                <button type="button" class="nav-item btn-sidebar-action" onclick="window.MailApp.openManageLabelsModal()">
                    <span class="nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    </span>
                    <span class="nav-label">Manage labels</span>
                </button>
            </div>
        </div>

        <div class="sidebar-divider"></div>

        <!-- Custom Labels Section -->
        <div class="nav-section labels-section">
            <div class="section-header">
                <span class="section-title">Labels</span>
                <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.openLabelModal()" title="Create New Label" aria-label="Add new label">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
            </div>

            <div class="labels-list" id="sidebarLabelsList">
                <?php if (!empty($labels)): ?>
                    <?php foreach ($labels as $lbl): ?>
                        <a href="/mail/inbox?label=<?= (int)$lbl['id'] ?>" class="nav-item label-item <?= ($currentLabelId === (int)$lbl['id']) ? 'active' : '' ?>" data-label-id="<?= (int)$lbl['id'] ?>">
                            <span class="label-dot" style="background-color: <?= \App\Core\View::e($lbl['color']) ?>;"></span>
                            <span class="nav-label"><?= \App\Core\View::e($lbl['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-labels-note">No custom labels yet</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar-divider"></div>

        <!-- Settings Section -->
        <div class="nav-section settings-nav-section">
            <a href="/settings" class="nav-item <?= (isset($currentFolder) && $currentFolder === 'settings') ? 'active' : '' ?>" id="sidebarSettingsLink" title="Application Settings">
                <span class="nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </span>
                <span class="nav-label">Settings</span>
            </a>
        </div>
    </nav>

    <!-- Sidebar footer info -->
    <div class="sidebar-footer">
        <div class="storage-indicator">
            <div class="storage-text">
                <span>Storage</span>
                <span>15.2 MB of 15 GB</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 2%;"></div>
            </div>
        </div>
    </div>
</aside>
