<div class="settings-page">
    <!-- Settings Top Bar / Action Header -->
    <header class="settings-header">
        <div class="settings-header-left">
            <a href="/mail/inbox" class="btn-icon back-to-mail-btn" title="Back to Inbox" aria-label="Back to Inbox">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div class="settings-title-wrap">
                <h1 class="settings-title">Settings</h1>
                <p class="settings-subtitle">Manage your account, preferences, security, and email configuration</p>
            </div>
        </div>
        <div class="settings-header-right">
            <div class="settings-save-status" id="settingsSaveStatus" aria-live="polite">
                <span class="status-indicator-dot"></span>
                <span class="status-indicator-text">All changes saved</span>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" id="btnDiscardSettings" style="display: none;">Reset</button>
            <button type="button" class="btn btn-primary btn-sm" id="btnSaveSettings">Save Changes</button>
        </div>
    </header>

    <div class="settings-container">
        <!-- Settings Category Navigation (Tabs) -->
        <nav class="settings-nav" id="settingsNav" aria-label="Settings Categories">
            <button type="button" class="settings-nav-btn active" data-target="panel-general">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <span>General</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-profile">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Profile</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-account">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>Account & Password</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-appearance">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <span>Appearance</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-inbox">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-6l-2 3h-4l-2-3H2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-7z"></path><path d="M5.45 5.11L2 12v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-7l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
                <span>Inbox Preferences</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <span>Notifications</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-signature">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                <span>Signature</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-compose">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"></polyline><path d="M20 18v-2a4 4 0 0 0-4-4H4"></path></svg>
                <span>Compose & Reply</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-labels">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                <span>Labels</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-blocked">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                <span>Blocked Senders</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-security">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>Security & Sessions</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-privacy">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <span>Privacy & Data</span>
            </button>
            <button type="button" class="settings-nav-btn" data-target="panel-mail">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <span>Mail Connection</span>
            </button>
        </nav>

        <!-- Main Content Area with Setting Cards -->
        <div class="settings-content" id="settingsContent">
            
            <!-- 0. GENERAL SETTINGS (ALL-IN-ONE OVERVIEW) -->
            <section class="settings-panel active" id="panel-general">
                <!-- General Header Card -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">General Settings</h2>
                        <p class="settings-card-desc">Comprehensive overview of all mailbox configurations, profile, appearance, and mail connection in one centralized view.</p>
                    </div>
                </div>

                <!-- 1. Profile Overview -->
                <div class="settings-card">
                    <div class="settings-card-header flex-header">
                        <div>
                            <h3 class="settings-card-title">1. Profile Information</h3>
                            <p class="settings-card-desc">Public display name and verified account email address.</p>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailSettings.switchToTab('panel-profile')">Open Profile Tab</button>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingDisplayName">Full Display Name *</label>
                        <input type="text" id="genSettingDisplayName" class="form-control" value="<?= \App\Core\View::e($user['display_name'] ?? '') ?>" maxlength="100" placeholder="e.g. Shanmuga Aswin">
                        <span class="settings-hint">Your public identity attached to outbound correspondence and invitations.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingEmailDisplay">Primary Account Email</label>
                        <input type="email" id="genSettingEmailDisplay" class="form-control" value="<?= \App\Core\View::e($user['email'] ?? '') ?>" readonly disabled>
                        <span class="settings-hint">Primary verified email tied to your account login.</span>
                    </div>
                </div>

                <!-- 2. Appearance & Density -->
                <div class="settings-card">
                    <div class="settings-card-header flex-header">
                        <div>
                            <h3 class="settings-card-title">2. Appearance & Display</h3>
                            <p class="settings-card-desc">Customize theme and email list spacing density.</p>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailSettings.switchToTab('panel-appearance')">Open Appearance Tab</button>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingThemeSelect">Application Theme</label>
                        <select id="genSettingThemeSelect" class="form-select" onchange="window.MailSettings.previewTheme(this.value)">
                            <option value="light" <?= ($settings['theme'] ?? 'system') === 'light' ? 'selected' : '' ?>>☀️ Light Beach</option>
                            <option value="dark" <?= ($settings['theme'] ?? 'system') === 'dark' ? 'selected' : '' ?>>🌙 Dark Mode</option>
                            <option value="system" <?= ($settings['theme'] ?? 'system') === 'system' ? 'selected' : '' ?>>💻 System Default</option>
                        </select>
                        <span class="settings-hint">Visual theme across your entire webmail interface.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingDensitySelect">Inbox Row Spacing Density</label>
                        <select id="genSettingDensitySelect" class="form-select" onchange="window.MailSettings.previewDensity(this.value)">
                            <option value="comfortable" <?= ($settings['density'] ?? 'comfortable') === 'comfortable' ? 'selected' : '' ?>>Comfortable</option>
                            <option value="compact" <?= ($settings['density'] ?? 'comfortable') === 'compact' ? 'selected' : '' ?>>Compact</option>
                        </select>
                        <span class="settings-hint">Controls vertical padding in your mailbox lists.</span>
                    </div>
                </div>

                <!-- 3. Inbox Preferences -->
                <div class="settings-card">
                    <div class="settings-card-header flex-header">
                        <div>
                            <h3 class="settings-card-title">3. Inbox & Categories</h3>
                            <p class="settings-card-desc">Default folders, category tabs, and sidebar badge behavior.</p>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailSettings.switchToTab('panel-inbox')">Open Inbox Tab</button>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingDefaultFolder">Default Folder on Login</label>
                        <select id="genSettingDefaultFolder" class="form-select">
                            <option value="inbox" <?= ($settings['default_folder'] ?? 'inbox') === 'inbox' ? 'selected' : '' ?>>Inbox</option>
                            <option value="starred" <?= ($settings['default_folder'] ?? 'inbox') === 'starred' ? 'selected' : '' ?>>Starred</option>
                            <option value="snoozed" <?= ($settings['default_folder'] ?? 'inbox') === 'snoozed' ? 'selected' : '' ?>>Snoozed</option>
                            <option value="sent" <?= ($settings['default_folder'] ?? 'inbox') === 'sent' ? 'selected' : '' ?>>Sent</option>
                            <option value="drafts" <?= ($settings['default_folder'] ?? 'inbox') === 'drafts' ? 'selected' : '' ?>>Drafts</option>
                            <option value="purchases" <?= ($settings['default_folder'] ?? 'inbox') === 'purchases' ? 'selected' : '' ?>>Purchases</option>
                            <option value="important" <?= ($settings['default_folder'] ?? 'inbox') === 'important' ? 'selected' : '' ?>>Important</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingDefaultCategory">Default Category</label>
                        <select id="genSettingDefaultCategory" class="form-select">
                            <option value="primary" <?= ($settings['default_category'] ?? 'primary') === 'primary' ? 'selected' : '' ?>>Primary</option>
                            <option value="social" <?= ($settings['default_category'] ?? 'primary') === 'social' ? 'selected' : '' ?>>Social</option>
                            <option value="updates" <?= ($settings['default_category'] ?? 'primary') === 'updates' ? 'selected' : '' ?>>Updates</option>
                            <option value="promotions" <?= ($settings['default_category'] ?? 'primary') === 'promotions' ? 'selected' : '' ?>>Promotions</option>
                        </select>
                    </div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Show Category Tabs in Inbox</span>
                            <span class="toggle-desc">Enable smart category tabs (Primary, Social, Updates, Promotions) above the inbox list.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="genSettingShowCategoryTabs" <?= !empty($settings['show_category_tabs']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingUnreadBadgeMode">Sidebar Counter Mode</label>
                        <select id="genSettingUnreadBadgeMode" class="form-select">
                            <option value="unread" <?= ($settings['unread_badge_mode'] ?? 'unread') === 'unread' ? 'selected' : '' ?>>Unread Only</option>
                            <option value="total" <?= ($settings['unread_badge_mode'] ?? 'unread') === 'total' ? 'selected' : '' ?>>Total Count</option>
                        </select>
                        <span class="settings-hint">Controls what the number badge next to folder labels indicates.</span>
                    </div>
                </div>

                <!-- 4. Email Signature -->
                <div class="settings-card">
                    <div class="settings-card-header flex-header">
                        <div>
                            <h3 class="settings-card-title">4. Email Signature</h3>
                            <p class="settings-card-desc">Personal sign-off automatically appended to outgoing messages.</p>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailSettings.switchToTab('panel-signature')">Open Signature Tab</button>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingSignatureText">Signature Content</label>
                        <textarea id="genSettingSignatureText" class="form-control" rows="4" maxlength="1000" placeholder="Best regards,&#10;Your Name"><?= \App\Core\View::e($settings['signature'] ?? '') ?></textarea>
                        <span class="settings-hint">Formatted automatically at the end of newly composed emails and replies.</span>
                    </div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Auto-Insert Signature into New Drafts</span>
                            <span class="toggle-desc">Appends your signature separated by '--' dash lines.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="genSettingAppendSignature" <?= !empty($settings['append_signature']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>

                <!-- 5. Compose & Reply -->
                <div class="settings-card">
                    <div class="settings-card-header flex-header">
                        <div>
                            <h3 class="settings-card-title">5. Compose & Reply Behavior</h3>
                            <p class="settings-card-desc">Editor defaults, auto-saving, and discard confirmation.</p>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailSettings.switchToTab('panel-compose')">Open Compose Tab</button>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genSettingReplyBehaviorSelect">Default Reply Action</label>
                        <select id="genSettingReplyBehaviorSelect" class="form-select">
                            <option value="reply" <?= ($settings['default_reply_behavior'] ?? 'reply') === 'reply' ? 'selected' : '' ?>>Reply</option>
                            <option value="reply_all" <?= ($settings['default_reply_behavior'] ?? 'reply') === 'reply_all' ? 'selected' : '' ?>>Reply All</option>
                        </select>
                        <span class="settings-hint">Prioritize replying only to sender or all recipients.</span>
                    </div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Confirm Before Discarding Drafts</span>
                            <span class="toggle-desc">Show confirmation prompt before closing an unsaved compose modal.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="genSettingConfirmDiscard" <?= !empty($settings['confirm_discard']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Auto-Save Drafts While Typing</span>
                            <span class="toggle-desc">Periodically persist message drafts in the background.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="genSettingAutoSaveDrafts" <?= !empty($settings['auto_save_drafts']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>

                <!-- 6. Notifications & System Status -->
                <div class="settings-card">
                    <div class="settings-card-header flex-header">
                        <div>
                            <h3 class="settings-card-title">6. Notifications & Outbound Mail Status</h3>
                            <p class="settings-card-desc">In-app notifications and outbound SMTP connectivity.</p>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailSettings.switchToTab('panel-mail')">Open Mail Connection</button>
                    </div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">In-App Toast Alerts</span>
                            <span class="toggle-desc">Display feedback banners when sending emails, moving messages, or applying labels.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="genSettingInAppNotifications" <?= !empty($settings['in_app_notifications']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="settings-form-row-grid">
                        <div class="form-group">
                            <label class="form-label">Outbound SMTP Server</label>
                            <input type="text" class="form-control" readonly value="<?= \App\Core\View::e($smtp['host']) ?>:<?= \App\Core\View::e((string)$smtp['port']) ?>" style="background: var(--bg-hover); font-family: monospace;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Relay Status</label>
                            <div class="form-control" style="background: var(--bg-hover); display: flex; align-items: center; height: 42px;">
                                <?php if ($smtp['configured']): ?>
                                    <span style="color: #137333; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #188038; display: inline-block;"></span>
                                        Connected & Ready
                                    </span>
                                <?php else: ?>
                                    <span style="color: #d93025; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #d93025; display: inline-block;"></span>
                                        Incomplete
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="settings-card-actions" style="margin-top: 24px;">
                        <button type="button" class="btn btn-primary" onclick="window.MailSettings.saveAllGeneral()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            <span>Save All General Settings</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- 1. PROFILE SECTION -->
            <section class="settings-panel" id="panel-profile">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Profile Information</h2>
                        <p class="settings-card-desc">Manage your public display details, identity, and profile photo.</p>
                    </div>
                    
                    <form id="profileForm" onsubmit="event.preventDefault(); window.MailSettings.updateProfile();">
                        <div class="form-group avatar-form-group">
                            <label class="form-label">Profile Avatar</label>
                            <div class="settings-avatar-row">
                                <div class="settings-avatar-preview-wrap">
                                    <?php if (!empty($user['avatar_path'])): ?>
                                        <img src="<?= \App\Core\View::e($user['avatar_path']) ?>" alt="Avatar" class="settings-avatar-img" id="settingsAvatarPreview">
                                    <?php else: ?>
                                        <div class="settings-avatar-placeholder" id="settingsAvatarPlaceholder">
                                            <?= strtoupper(substr($user['display_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <img src="" alt="Avatar" class="settings-avatar-img" id="settingsAvatarPreview" style="display: none;">
                                    <?php endif; ?>
                                </div>
                                <div class="settings-avatar-info">
                                    <label class="btn btn-secondary btn-sm avatar-upload-btn">
                                        <input type="file" id="avatarFileInput" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none;" onchange="window.MailSettings.uploadAvatar(this)">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                        <span>Upload New Photo</span>
                                    </label>
                                    <span class="settings-hint">Supported formats: JPG, PNG, WebP or GIF. Maximum 2MB.</span>
                                    <div id="avatarUploadStatus" class="upload-status-text"></div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-divider"></div>

                        <div class="form-group">
                            <label class="form-label" for="settingDisplayName">Full Display Name *</label>
                            <input type="text" id="settingDisplayName" class="form-control" value="<?= \App\Core\View::e($user['display_name'] ?? '') ?>" required maxlength="100" placeholder="e.g. Shanmuga Aswin">
                            <span class="settings-hint">This name is attached to all outbound correspondence, invitations, and notifications.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="settingEmailDisplay">Primary Account Email</label>
                            <input type="email" id="settingEmailDisplay" class="form-control" value="<?= \App\Core\View::e($user['email'] ?? '') ?>" readonly disabled>
                            <span class="settings-hint">Primary email address is verified and tied to your account authentication.</span>
                        </div>

                        <div class="settings-card-actions">
                            <button type="submit" class="btn btn-primary" id="btnSaveProfile">Save Profile</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- 2. ACCOUNT & PASSWORD SECTION -->
            <section class="settings-panel" id="panel-account">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Security & Password</h2>
                        <p class="settings-card-desc">Update your login credentials and ensure your account has a strong passphrase.</p>
                    </div>

                    <form id="passwordForm" onsubmit="event.preventDefault(); window.MailSettings.changePassword();">
                        <div class="form-group">
                            <label class="form-label" for="currentPassword">Current Password *</label>
                            <div class="password-input-wrap">
                                <input type="password" id="currentPassword" class="form-control" required autocomplete="current-password" placeholder="Enter your current password">
                                <button type="button" class="btn-password-toggle" onclick="window.MailSettings.togglePasswordVisibility('currentPassword', this)" title="Show/hide password" aria-label="Toggle password visibility">
                                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                        </div>

                        <div class="settings-divider"></div>

                        <div class="form-group">
                            <label class="form-label" for="newPassword">New Password *</label>
                            <div class="password-input-wrap">
                                <input type="password" id="newPassword" class="form-control" required minlength="8" autocomplete="new-password" placeholder="Minimum 8 characters">
                                <button type="button" class="btn-password-toggle" onclick="window.MailSettings.togglePasswordVisibility('newPassword', this)" title="Show/hide password" aria-label="Toggle password visibility">
                                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <span class="settings-hint">Must contain at least 8 characters. Hashed securely with PHP bcrypt.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirmPassword">Confirm New Password *</label>
                            <div class="password-input-wrap">
                                <input type="password" id="confirmPassword" class="form-control" required minlength="8" autocomplete="new-password" placeholder="Re-enter your new password">
                                <button type="button" class="btn-password-toggle" onclick="window.MailSettings.togglePasswordVisibility('confirmPassword', this)" title="Show/hide password" aria-label="Toggle password visibility">
                                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                        </div>

                        <div class="settings-card-actions">
                            <button type="submit" class="btn btn-primary" id="btnUpdatePassword">Update Password</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- 3. APPEARANCE SECTION -->
            <section class="settings-panel" id="panel-appearance">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Theme & Display Preferences</h2>
                        <p class="settings-card-desc">Customize the visual theme, contrast, and spacing density across your webmail.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="settingThemeSelect">Application Theme</label>
                        <select id="settingThemeSelect" class="form-select" onchange="window.MailSettings.previewTheme(this.value)">
                            <option value="light" <?= ($settings['theme'] ?? 'system') === 'light' ? 'selected' : '' ?>>☀️ Light Beach</option>
                            <option value="dark" <?= ($settings['theme'] ?? 'system') === 'dark' ? 'selected' : '' ?>>🌙 Dark Mode</option>
                            <option value="system" <?= ($settings['theme'] ?? 'system') === 'system' ? 'selected' : '' ?>>💻 System Default</option>
                        </select>
                        <span class="settings-hint">Choose between clean light beach tones, dark mode, or automatic operating system synchronization.</span>
                    </div>

                    <!-- Hidden radio inputs to maintain backward compatibility with JS handlers -->
                    <div style="display: none;">
                        <input type="radio" name="setting_theme" value="light" id="themeRadioLight" <?= ($settings['theme'] ?? 'system') === 'light' ? 'checked' : '' ?>>
                        <input type="radio" name="setting_theme" value="dark" id="themeRadioDark" <?= ($settings['theme'] ?? 'system') === 'dark' ? 'checked' : '' ?>>
                        <input type="radio" name="setting_theme" value="system" id="themeRadioSystem" <?= ($settings['theme'] ?? 'system') === 'system' ? 'checked' : '' ?>>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="form-group">
                        <label class="form-label" for="settingDensitySelect">Inbox Message Density</label>
                        <select id="settingDensitySelect" class="form-select" onchange="window.MailSettings.previewDensity(this.value)">
                            <option value="comfortable" <?= ($settings['density'] ?? 'comfortable') === 'comfortable' ? 'selected' : '' ?>>Comfortable</option>
                            <option value="compact" <?= ($settings['density'] ?? 'comfortable') === 'compact' ? 'selected' : '' ?>>Compact</option>
                        </select>
                        <span class="settings-hint">Controls vertical padding and snippet visibility in your mailbox message rows.</span>
                    </div>

                    <!-- Hidden radio inputs to maintain backward compatibility with JS handlers -->
                    <div style="display: none;">
                        <input type="radio" name="setting_density" value="comfortable" id="densityRadioComfortable" <?= ($settings['density'] ?? 'comfortable') === 'comfortable' ? 'checked' : '' ?>>
                        <input type="radio" name="setting_density" value="compact" id="densityRadioCompact" <?= ($settings['density'] ?? 'comfortable') === 'compact' ? 'checked' : '' ?>>
                    </div>
                </div>
            </section>

            <!-- 4. INBOX PREFERENCES SECTION -->
            <section class="settings-panel" id="panel-inbox">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Inbox Configuration</h2>
                        <p class="settings-card-desc">Customize which folder opens by default and how categories and badges behave.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="settingDefaultFolder">Default Folder on Login</label>
                        <select id="settingDefaultFolder" class="form-select">
                            <option value="inbox" <?= ($settings['default_folder'] ?? 'inbox') === 'inbox' ? 'selected' : '' ?>>Inbox</option>
                            <option value="starred" <?= ($settings['default_folder'] ?? 'inbox') === 'starred' ? 'selected' : '' ?>>Starred</option>
                            <option value="snoozed" <?= ($settings['default_folder'] ?? 'inbox') === 'snoozed' ? 'selected' : '' ?>>Snoozed</option>
                            <option value="sent" <?= ($settings['default_folder'] ?? 'inbox') === 'sent' ? 'selected' : '' ?>>Sent</option>
                            <option value="drafts" <?= ($settings['default_folder'] ?? 'inbox') === 'drafts' ? 'selected' : '' ?>>Drafts</option>
                            <option value="purchases" <?= ($settings['default_folder'] ?? 'inbox') === 'purchases' ? 'selected' : '' ?>>Purchases</option>
                            <option value="important" <?= ($settings['default_folder'] ?? 'inbox') === 'important' ? 'selected' : '' ?>>Important</option>
                        </select>
                        <span class="settings-hint">The mailbox directory displayed immediately after signing in.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="settingDefaultCategory">Default Inbox Category</label>
                        <select id="settingDefaultCategory" class="form-select">
                            <option value="primary" <?= ($settings['default_category'] ?? 'primary') === 'primary' ? 'selected' : '' ?>>Primary</option>
                            <option value="social" <?= ($settings['default_category'] ?? 'primary') === 'social' ? 'selected' : '' ?>>Social</option>
                            <option value="updates" <?= ($settings['default_category'] ?? 'primary') === 'updates' ? 'selected' : '' ?>>Updates</option>
                            <option value="promotions" <?= ($settings['default_category'] ?? 'primary') === 'promotions' ? 'selected' : '' ?>>Promotions</option>
                        </select>
                    </div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Show Category Tabs in Inbox</span>
                            <span class="toggle-desc">Enable smart category tabs (Primary, Social, Updates, Promotions) above the inbox list.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="settingShowCategoryTabs" <?= !empty($settings['show_category_tabs']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="form-group">
                        <label class="form-label" for="settingUnreadBadgeMode">Sidebar Counter Mode</label>
                        <select id="settingUnreadBadgeMode" class="form-select">
                            <option value="unread" <?= ($settings['unread_badge_mode'] ?? 'unread') === 'unread' ? 'selected' : '' ?>>Unread Only</option>
                            <option value="total" <?= ($settings['unread_badge_mode'] ?? 'unread') === 'total' ? 'selected' : '' ?>>Total Count</option>
                        </select>
                        <span class="settings-hint">Controls what the number badge next to folder labels indicates.</span>
                    </div>
                </div>
            </section>

            <!-- 5. NOTIFICATIONS SECTION -->
            <section class="settings-panel" id="panel-notifications">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Notification Channels</h2>
                        <p class="settings-card-desc">Control in-app alerts and browser desktop notifications.</p>
                    </div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">In-App Toast Alerts</span>
                            <span class="toggle-desc">Display feedback banners when sending emails, moving messages, or applying labels.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="settingInAppNotifications" <?= !empty($settings['in_app_notifications']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Browser HTML5 Notifications</span>
                            <span class="toggle-desc">Trigger standard desktop alerts via your web browser when new incoming emails arrive.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="settingBrowserNotifications" <?= !empty($settings['browser_notifications']) ? 'checked' : '' ?> onchange="window.MailSettings.handleBrowserNotificationToggle(this)">
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="settings-sub-action">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailSettings.testBrowserNotification()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            <span>Test Browser Alert</span>
                        </button>
                        <span class="settings-hint">Note: Requires granting browser notification permission. Real push alerts require an open tab or active background worker.</span>
                    </div>
                </div>
            </section>

            <!-- 6. SIGNATURE SECTION -->
            <section class="settings-panel" id="panel-signature">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Email Signature</h2>
                        <p class="settings-card-desc">Create your custom personal email signature automatically appended to outgoing messages.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="settingSignatureText">Signature Content</label>
                        <textarea id="settingSignatureText" class="form-control" rows="5" maxlength="1000" placeholder="Best regards,&#10;Your Name&#10;Your Title | Novalyx Technologies"><?= \App\Core\View::e($settings['signature'] ?? '') ?></textarea>
                        <div class="textarea-counter"><span id="signatureCharCount"><?= strlen($settings['signature'] ?? '') ?></span>/1000 characters</div>
                        <span class="settings-hint">Plain-text signature formatted neatly at the conclusion of all newly composed emails and replies.</span>
                    </div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Automatically Insert into New Compose Drafts</span>
                            <span class="toggle-desc">Appends your signature separated by standard '--' dash notation. Prevents duplicating if already present.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="settingAppendSignature" <?= !empty($settings['append_signature']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </section>

            <!-- 7. COMPOSE AND REPLY SECTION -->
            <section class="settings-panel" id="panel-compose">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Compose & Reply Preferences</h2>
                        <p class="settings-card-desc">Configure editor behavior, draft auto-saving, and reply defaults.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="settingReplyBehaviorSelect">Default Reply Action</label>
                        <select id="settingReplyBehaviorSelect" class="form-select" onchange="window.MailSettings.handleReplyBehaviorChange(this.value)">
                            <option value="reply" <?= ($settings['default_reply_behavior'] ?? 'reply') === 'reply' ? 'selected' : '' ?>>Reply</option>
                            <option value="reply_all" <?= ($settings['default_reply_behavior'] ?? 'reply') === 'reply_all' ? 'selected' : '' ?>>Reply All</option>
                        </select>
                        <span class="settings-hint">Choose which button action is prioritized when replying from the reading view.</span>
                    </div>

                    <!-- Hidden inputs to maintain backward compatibility with JS handlers -->
                    <div style="display: none;">
                        <input type="radio" name="setting_reply_behavior" value="reply" id="replyRadioReply" <?= ($settings['default_reply_behavior'] ?? 'reply') === 'reply' ? 'checked' : '' ?>>
                        <input type="radio" name="setting_reply_behavior" value="reply_all" id="replyRadioReplyAll" <?= ($settings['default_reply_behavior'] ?? 'reply') === 'reply_all' ? 'checked' : '' ?>>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Confirm Before Discarding Drafts</span>
                            <span class="toggle-desc">Show a confirmation prompt when closing an unsaved or modified compose window.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="settingConfirmDiscard" <?= !empty($settings['confirm_discard']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="settings-toggle-row">
                        <div class="toggle-info">
                            <span class="toggle-title">Auto-Save Drafts While Typing</span>
                            <span class="toggle-desc">Periodically persist email drafts to the database while typing in the compose modal.</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="settingAutoSaveDrafts" <?= !empty($settings['auto_save_drafts']) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </section>

            <!-- 8. LABELS SECTION -->
            <section class="settings-panel" id="panel-labels">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Manage Labels</h2>
                        <p class="settings-card-desc">Organize and tag conversations with custom color-coded labels.</p>
                    </div>

                    <div class="settings-card-actions" style="margin-bottom: 20px;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="window.MailApp.openLabelModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Create New Label</span>
                        </button>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Configured Labels</label>
                        <div class="settings-labels-table-wrap">
                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Color</th>
                                        <th>Label Name</th>
                                        <th style="text-align: right; width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="settingsLabelsTableBody">
                                    <?php if (!empty($labels)): ?>
                                        <?php foreach ($labels as $lbl): ?>
                                            <tr data-label-id="<?= (int)$lbl['id'] ?>">
                                                <td>
                                                    <span class="label-color-preview" style="background-color: <?= \App\Core\View::e($lbl['color']) ?>;"></span>
                                                </td>
                                                <td>
                                                    <strong class="label-name-text"><?= \App\Core\View::e($lbl['name']) ?></strong>
                                                </td>
                                                <td style="text-align: right;">
                                                    <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.openLabelModal(<?= (int)$lbl['id'] ?>, '<?= \App\Core\View::e(addslashes($lbl['name'])) ?>', '<?= \App\Core\View::e($lbl['color']) ?>')" title="Edit label">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </button>
                                                    <button type="button" class="btn-icon btn-sm text-danger" onclick="window.MailSettings.deleteLabel(<?= (int)$lbl['id'] ?>)" title="Delete label">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="emptyLabelsRow">
                                            <td colspan="3" class="text-center text-muted" style="padding: 24px;">No custom labels created yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <span class="settings-hint">Labels help you filter and organize your emails into distinct project or personal categories.</span>
                    </div>
                </div>
            </section>

            <!-- 9. BLOCKED SENDERS SECTION -->
            <section class="settings-panel" id="panel-blocked">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Blocked Senders</h2>
                        <p class="settings-card-desc">Add email addresses to your blocklist to reject or filter unwanted incoming correspondence.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="blockedSenderInput">Block New Email Address</label>
                        <form id="addBlockedSenderForm" class="add-item-form" onsubmit="event.preventDefault(); window.MailSettings.addBlockedSender();">
                            <div class="input-with-button" style="display: flex; gap: 8px;">
                                <input type="email" id="blockedSenderInput" class="form-control" placeholder="e.g. spammer@example.com" required style="flex: 1;">
                                <button type="submit" class="btn btn-primary" id="btnAddBlockedSender">Block Address</button>
                            </div>
                        </form>
                        <span class="settings-hint">Incoming messages from blocked addresses are automatically routed to Trash or rejected.</span>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="form-group">
                        <label class="form-label">Currently Blocked Addresses</label>
                        <div class="blocked-senders-list-wrap">
                            <ul class="blocked-senders-list" id="blockedSendersList">
                                <?php if (!empty($blockedSenders)): ?>
                                    <?php foreach ($blockedSenders as $blocked): ?>
                                        <li class="blocked-sender-item" data-id="<?= (int)$blocked['id'] ?>">
                                            <span class="blocked-email-text"><?= \App\Core\View::e($blocked['email']) ?></span>
                                            <span class="blocked-date">Blocked on <?= date('M j, Y', strtotime($blocked['created_at'])) ?></span>
                                            <button type="button" class="btn-icon btn-sm text-danger" onclick="window.MailSettings.removeBlockedSender(<?= (int)$blocked['id'] ?>)" title="Unblock address" aria-label="Unblock address">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="empty-list-note" id="emptyBlockedNote">No blocked senders yet.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 10. SECURITY & SESSIONS SECTION -->
            <section class="settings-panel" id="panel-security">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Active Sessions & Devices</h2>
                        <p class="settings-card-desc">Review your current authenticated session details and active devices.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Current Device & Browser</label>
                        <div class="form-control" style="background: var(--bg-hover); display: flex; align-items: center; justify-content: space-between; height: auto; min-height: 42px; padding: 10px 14px;">
                            <span style="font-weight: 500;"><?= \App\Core\View::e($sessionInfo['user_agent']) ?></span>
                            <span style="background: #e6f4ea; color: #137333; font-weight: 600; font-size: 11px; padding: 3px 10px; border-radius: 12px; white-space: nowrap;">This Device (Active)</span>
                        </div>
                        <span class="settings-hint">Browser signature and operating system for this session.</span>
                    </div>

                    <div class="settings-form-row-grid equal-cols">
                        <div class="form-group">
                            <label class="form-label">Network IP Address</label>
                            <input type="text" class="form-control" readonly value="<?= \App\Core\View::e($sessionInfo['ip_address']) ?>" style="background: var(--bg-hover); font-family: monospace;">
                            <span class="settings-hint">Remote network IP address.</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Session Started At</label>
                            <input type="text" class="form-control" readonly value="<?= \App\Core\View::e($sessionInfo['started_at']) ?>" style="background: var(--bg-hover);">
                            <span class="settings-hint">Timestamp when session was created.</span>
                        </div>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="settings-card-actions">
                        <form method="POST" action="/logout" style="margin: 0;">
                            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::getCsrfToken() ?>">
                            <button type="submit" class="btn btn-secondary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span>Sign Out Current Session</span>
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- 11. PRIVACY & DATA SECTION -->
            <section class="settings-panel" id="panel-privacy">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Data Storage & Retention</h2>
                        <p class="settings-card-desc">Review how your mailbox data, storage policies, and credentials are handled.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Database Storage Policy</label>
                        <div class="form-control" style="height: auto; min-height: 44px; background: var(--bg-hover); line-height: 1.5; padding: 10px 14px; font-size: 13px; color: var(--text-secondary);">
                            Your emails and attachments are persisted in your relational MySQL database. Messages moved to Trash remain until you explicitly choose to empty Trash or permanently delete them.
                        </div>
                        <span class="settings-hint">Data is retained securely within your configured database.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Transport Security & Passwords</label>
                        <div class="form-control" style="height: auto; min-height: 44px; background: var(--bg-hover); line-height: 1.5; padding: 10px 14px; font-size: 13px; color: var(--text-secondary);">
                            Communications between your browser and the server are encrypted via TLS/HTTPS. Passwords are salted and hashed using PHP Bcrypt.
                        </div>
                        <span class="settings-hint">Strict cryptographic hashing ensures raw passwords are never stored in plaintext.</span>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="settings-card-header" style="margin-bottom: 12px;">
                        <h2 class="settings-card-title" style="color: #d93025;">Danger Zone</h2>
                        <p class="settings-card-desc">Permanently erase your account, all emails, drafts, and preferences.</p>
                    </div>

                    <div class="settings-card-actions">
                        <button type="button" class="btn btn-danger" onclick="window.MailSettings.openDeleteAccountModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            <span>Delete My Account</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- 12. MAIL CONNECTION / SMTP SECTION -->
            <section class="settings-panel" id="panel-mail">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title">Mail Connection & Outbound SMTP</h2>
                        <p class="settings-card-desc">Review your outbound mail relay configuration and server status.</p>
                    </div>

                    <div class="settings-form-row-grid">
                        <div class="form-group">
                            <label class="form-label">SMTP Relay Host</label>
                            <input type="text" class="form-control" readonly value="<?= \App\Core\View::e($smtp['host']) ?>" style="background: var(--bg-hover); font-family: monospace;">
                            <span class="settings-hint">Primary outgoing mail server (e.g. smtp.gmail.com).</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Port</label>
                            <input type="text" class="form-control" readonly value="<?= \App\Core\View::e((string)$smtp['port']) ?>" style="background: var(--bg-hover); font-family: monospace;">
                            <span class="settings-hint">Port 587 (TLS) or 465 (SSL).</span>
                        </div>
                    </div>

                    <div class="settings-form-row-grid">
                        <div class="form-group">
                            <label class="form-label">Authenticated Account / Username</label>
                            <input type="text" class="form-control" readonly value="<?= \App\Core\View::e($smtp['username'] ?: 'Not configured') ?>" style="background: var(--bg-hover); font-family: monospace;">
                            <span class="settings-hint">Sender email address used for outbound authentication.</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Relay Status</label>
                            <div class="form-control" style="background: var(--bg-hover); display: flex; align-items: center; height: 42px;">
                                <?php if ($smtp['configured']): ?>
                                    <span style="color: #137333; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #188038; display: inline-block;"></span>
                                        Connected & Ready
                                    </span>
                                <?php else: ?>
                                    <span style="color: #d93025; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #d93025; display: inline-block;"></span>
                                        Incomplete
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="settings-hint">Current SMTP connectivity state.</span>
                        </div>
                    </div>

                    <div class="settings-divider"></div>

                    <div class="settings-hint" style="margin-bottom: 20px;">
                        <strong>Security Note:</strong> SMTP connection credentials and passwords are read securely from server environment variables (<code>.env</code>) and cannot be exposed or modified directly through unprivileged web forms.
                    </div>

                    <div class="settings-card-actions">
                        <button type="button" class="btn btn-primary" onclick="window.MailApp.openSettingsModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            <span>SMTP Test & Setup Dialog</span>
                        </button>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

<!-- Account Deletion Confirmation Modal -->
<div class="modal-backdrop" id="deleteAccountModal" style="display: none;">
    <div class="modal modal-danger" role="dialog" aria-modal="true" aria-labelledby="deleteAccountModalTitle">
        <div class="modal-header">
            <h3 class="modal-title text-danger" id="deleteAccountModalTitle">Confirm Account Deletion</h3>
            <button type="button" class="btn-icon" onclick="window.MailSettings.closeDeleteAccountModal()" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form onsubmit="event.preventDefault(); window.MailSettings.confirmDeleteAccount();">
            <div class="modal-body">
                <p><strong>Warning:</strong> You are about to permanently delete your account (<strong><?= \App\Core\View::e($user['email'] ?? '') ?></strong>). All associated messages, drafts, folders, labels, and personal configurations will be permanently destroyed.</p>
                
                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label" for="deleteConfirmPassword">Please enter your current password to confirm:</label>
                    <input type="password" id="deleteConfirmPassword" class="form-control" required placeholder="Current password">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="deleteConfirmPhrase">Type <strong>DELETE</strong> in all caps to proceed:</label>
                    <input type="text" id="deleteConfirmPhrase" class="form-control" required placeholder="DELETE">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="window.MailSettings.closeDeleteAccountModal()">Cancel</button>
                <button type="submit" class="btn btn-danger" id="btnConfirmDeleteAccount">Permanently Delete Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Settings Client Script -->
<script src="/assets/js/settings.js?v=<?= time() ?>"></script>
