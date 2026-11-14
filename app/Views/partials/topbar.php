<header class="app-topbar">
    <div class="topbar-left">
        <button type="button" class="btn-icon sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation Drawer">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
            </svg>
        </button>

        <a href="/mail/inbox" class="brand-link" title="Novalyx Cloud Mail">
            <img src="/assets/images/logo.svg" alt="Novalyx Logo" class="brand-logo-img" width="38" height="38">
            <div class="brand-text-container">
                <span class="brand-title">Novalyx</span>
                <span class="brand-badge">Cloud</span>
            </div>
        </a>
    </div>

    <div class="topbar-search">
        <form class="search-form" id="searchForm" onsubmit="event.preventDefault(); window.MailApp.triggerSearch();">
            <button type="button" class="btn-search-icon" aria-label="Search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
            <input 
                type="text" 
                id="globalSearchInput" 
                class="search-input" 
                placeholder="Search mail by sender, subject, or content..." 
                value="<?= \App\Core\View::e($searchQuery ?? '') ?>"
                autocomplete="off"
            >
            <button type="button" class="btn-filter-icon" id="openAdvancedSearchBtn" title="Advanced Search Options" aria-label="Show search options">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/>
                </svg>
            </button>
        </form>
    </div>

    <div class="topbar-right">
        <!-- Status indicator -->
        <div class="status-pill" id="networkStatusPill" title="Connected to nkinfyx Enterprise Server">
            <span class="status-dot"></span>
            <span class="status-label">Online</span>
        </div>

        <!-- User profile dropdown -->
        <div class="user-profile-menu">
            <button type="button" class="user-avatar-btn" id="userMenuToggle" aria-expanded="false" aria-label="User account menu">
                <span class="user-avatar-letter">
                    <?= strtoupper(substr($user['display_name'] ?? 'A', 0, 1)) ?>
                </span>
            </button>

            <div class="dropdown-menu profile-dropdown" id="userDropdown">
                <div class="profile-dropdown-header">
                    <div class="user-avatar-lg">
                        <?= strtoupper(substr($user['display_name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="profile-info">
                        <strong class="profile-name"><?= \App\Core\View::e($user['display_name'] ?? 'User') ?></strong>
                        <span class="profile-email"><?= \App\Core\View::e($user['email'] ?? 'novalyx.workspace@gmail.com') ?></span>
                        <span class="company-tag">Novalyx Cloud & Tech</span>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                <div class="profile-dropdown-actions">
                    <button type="button" class="dropdown-item" onclick="window.MailApp.openSettingsModal()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <span>Mail Delivery / SMTP</span>
                    </button>

                    <button type="button" class="dropdown-item" onclick="window.MailApp.openLabelModal()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                        <span>Manage Labels</span>
                    </button>
                    
                    <form method="POST" action="/logout" style="margin: 0;">
                        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::getCsrfToken() ?>">
                        <button type="submit" class="dropdown-item text-danger">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
