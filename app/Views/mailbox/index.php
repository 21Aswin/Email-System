<div class="mailbox-view" id="mailboxView" data-folder="<?= \App\Core\View::e($currentFolder) ?>">
    <!-- Top Toolbar for message actions & pagination -->
    <div class="mailbox-toolbar" id="mailboxToolbar">
        <div class="toolbar-left">
            <label class="toolbar-checkbox-wrapper" title="Select All">
                <input type="checkbox" id="selectAllCheckbox" class="app-checkbox">
            </label>

            <button type="button" class="btn-icon" id="btnRefresh" title="Refresh messages & check replies" aria-label="Refresh">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                </svg>
            </button>

            <!-- Bulk action buttons (hidden until at least 1 message is selected) -->
            <div class="bulk-actions-group" id="bulkActionsGroup" style="display: none;">
                <button type="button" class="btn-icon" id="bulkArchiveBtn" title="Archive" aria-label="Archive">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>
                </button>
                <button type="button" class="btn-icon" id="bulkSpamBtn" title="Report Spam" aria-label="Report Spam">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z"/></svg>
                </button>
                <button type="button" class="btn-icon" id="bulkDeleteBtn" title="Delete" aria-label="Delete">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
                <button type="button" class="btn-icon" id="bulkMarkReadBtn" title="Mark as read" aria-label="Mark as read">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                </button>
                <button type="button" class="btn-icon" id="bulkMarkUnreadBtn" title="Mark as unread" aria-label="Mark as unread">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z"/></svg>
                </button>
                <button type="button" class="btn-icon" id="bulkSnoozeBtn" title="Snooze" aria-label="Snooze">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                </button>

                <!-- Restore to Inbox button (especially in Trash / Spam / Snoozed) -->
                <?php if (in_array($currentFolder, ['trash', 'spam', 'snoozed', 'archive'], true)): ?>
                    <button type="button" class="btn-icon" id="bulkRestoreBtn" title="Restore to Inbox" aria-label="Restore to Inbox">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="toolbar-right">
            <span class="pagination-info" id="paginationInfo">
                <?= $total > 0 ? (1 + ($page - 1) * 30) . '–' . min($total, $page * 30) . ' of ' . $total : '0 of 0' ?>
            </span>
            <div class="pagination-buttons">
                <button type="button" class="btn-icon btn-sm" id="btnPrevPage" <?= $page <= 1 ? 'disabled' : '' ?> title="Newer" aria-label="Previous Page">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                </button>
                <button type="button" class="btn-icon btn-sm" id="btnNextPage" <?= $page >= $totalPages ? 'disabled' : '' ?> title="Older" aria-label="Next Page">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Category Tabs (Primary, Updates, Promotions, Purchases for Inbox) -->
    <?php if ($currentFolder === 'inbox' && empty($currentLabelId)): ?>
        <div class="category-tabs" id="categoryTabs">
            <button type="button" class="category-tab <?= ($currentCategory === 'primary' || empty($currentCategory)) ? 'active' : '' ?>" data-category="primary">
                <span class="tab-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H4.99c-1.11 0-1.98.89-1.98 2L3 19c0 1.1.88 2 1.99 2H19c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 12h-4c0 1.66-1.35 3-3 3s-3-1.34-3-3H4.99V5H19v10z"/></svg>
                </span>
                <span class="tab-label">Primary</span>
            </button>
            <button type="button" class="category-tab <?= ($currentCategory === 'updates') ? 'active' : '' ?>" data-category="updates">
                <span class="tab-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                </span>
                <span class="tab-label">Updates</span>
            </button>
            <button type="button" class="category-tab <?= ($currentCategory === 'promotions') ? 'active' : '' ?>" data-category="promotions">
                <span class="tab-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg>
                </span>
                <span class="tab-label">Promotions</span>
            </button>
            <button type="button" class="category-tab <?= ($currentCategory === 'purchases') ? 'active' : '' ?>" data-category="purchases">
                <span class="tab-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h14v12zm-7-8c-1.66 0-3-1.34-3-3H7c0 2.76 2.24 5 5 5s5-2.24 5-5h-2c0 1.66-1.34 3-3 3z"/></svg>
                </span>
                <span class="tab-label">Purchases</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Dedicated Trash Banner with Empty Trash action -->
    <?php if ($currentFolder === 'trash'): ?>
        <div class="folder-alert-banner">
            <span>Messages that have been in Trash can be permanently deleted.</span>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailApp.confirmEmptyFolder('trash')">Empty Trash now</button>
        </div>
    <?php elseif ($currentFolder === 'spam'): ?>
        <div class="folder-alert-banner warning">
            <span>Messages in Spam are considered suspicious.</span>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailApp.confirmEmptyFolder('spam')">Delete all spam messages now</button>
        </div>
    <?php endif; ?>

    <!-- Active Search Filter Badge (if searching) -->
    <?php if (!empty($searchQuery)): ?>
        <div class="search-filter-banner" id="searchFilterBanner">
            <div class="filter-pill">
                <span>Search results for: <strong>"<?= \App\Core\View::e($searchQuery) ?>"</strong></span>
                <a href="/mail/inbox" class="clear-search-btn" title="Clear Search">&times;</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Message List Rows Container -->
    <div class="message-list-container" id="messageListContainer">
        <?php if (!empty($messages)): ?>
            <div class="message-list" id="messageList">
                <?php foreach ($messages as $msg): ?>
                    <div class="message-row <?= empty($msg['is_read']) ? 'unread' : 'read' ?>" 
                         data-message-id="<?= (int)$msg['message_id'] ?>" 
                         data-mailbox-id="<?= (int)$msg['mailbox_item_id'] ?>">
                        
                        <!-- Checkbox -->
                        <div class="row-cell cell-check" onclick="event.stopPropagation();">
                            <input type="checkbox" class="app-checkbox msg-select-box" value="<?= (int)$msg['message_id'] ?>">
                        </div>

                        <!-- Star -->
                        <div class="row-cell cell-star" onclick="event.stopPropagation(); window.MailApp.toggleStar(<?= (int)$msg['message_id'] ?>, this);">
                            <button type="button" class="btn-star <?= !empty($msg['is_starred']) ? 'starred' : '' ?>" title="Star message">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= !empty($msg['is_starred']) ? '#f4b400' : 'none' ?>" stroke="<?= !empty($msg['is_starred']) ? '#f4b400' : 'currentColor' ?>" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </button>
                        </div>

                        <!-- Important Flag -->
                        <div class="row-cell cell-important" onclick="event.stopPropagation(); window.MailApp.toggleImportant(<?= (int)$msg['message_id'] ?>, this);">
                            <button type="button" class="btn-important <?= !empty($msg['is_important']) ? 'important' : '' ?>" title="Mark as important">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= !empty($msg['is_important']) ? '#f29900' : 'none' ?>" stroke="<?= !empty($msg['is_important']) ? '#f29900' : 'currentColor' ?>" stroke-width="2">
                                    <polygon points="5 3 19 3 15 12 19 21 5 21 5 3"></polygon>
                                </svg>
                            </button>
                        </div>

                        <!-- Sender Name or Recipient (if Sent folder) -->
                        <div class="row-cell cell-sender" 
                             data-name="<?= \App\Core\View::e($msg['sender_name'] ?: $msg['sender_email']) ?>" 
                             data-email="<?= \App\Core\View::e($msg['sender_email']) ?>" 
                             title="<?= \App\Core\View::e($msg['sender_email']) ?>">
                            <?php if ($currentFolder === 'sent' && !empty($msg['recipient_display'])): ?>
                                <span class="sender-text" style="color: var(--brand-primary);">To: <?= \App\Core\View::e($msg['recipient_display']) ?></span>
                            <?php else: ?>
                                <span class="sender-text"><?= \App\Core\View::e($msg['sender_name'] ?: $msg['sender_email']) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Subject and Snippet Preview -->
                        <div class="row-cell cell-content">
                            <!-- Draft indicator chip if in drafts -->
                            <?php if ($currentFolder === 'drafts'): ?>
                                <span class="row-draft-chip">Draft</span>
                            <?php endif; ?>

                            <!-- Category chip if Purchases -->
                            <?php if ($msg['category'] === 'purchases'): ?>
                                <span class="row-label-chip" style="background-color: #e6f4ea; color: #137333; border: 1px solid #ceead6;">
                                    Purchase
                                </span>
                            <?php endif; ?>

                            <!-- Label badges if any -->
                            <?php if (!empty($msg['labels'])): ?>
                                <span class="row-labels">
                                    <?php foreach ($msg['labels'] as $lbl): ?>
                                        <span class="row-label-chip" style="background-color: <?= \App\Core\View::e($lbl['color']) ?>18; color: <?= \App\Core\View::e($lbl['color']) ?>; border: 1px solid <?= \App\Core\View::e($lbl['color']) ?>40;">
                                            <?= \App\Core\View::e($lbl['name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>

                            <span class="subject-text"><?= \App\Core\View::e($msg['subject'] ?: '(no subject)') ?></span>
                            <span class="snippet-delimiter">-</span>
                            <span class="snippet-text"><?= \App\Core\View::e($msg['snippet'] ?: '') ?></span>
                        </div>

                        <!-- Attachment indicator -->
                        <div class="row-cell cell-attachment">
                            <?php if (!empty($msg['attachment_count'])): ?>
                                <span title="<?= (int)$msg['attachment_count'] ?> attachment(s)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Date -->
                        <div class="row-cell cell-date" <?= ($currentFolder === 'snoozed' && !empty($msg['snoozed_until'])) ? 'style="color: #d97706; font-weight: 500;"' : '' ?>>
                            <span>
                                <?php if ($currentFolder === 'snoozed' && !empty($msg['snoozed_until'])): ?>
                                    Until <?= date('M j, g:i A', strtotime($msg['snoozed_until'])) ?>
                                <?php else: ?>
                                    <?= date('M j', strtotime($msg['message_date'])) ?>
                                <?php endif; ?>
                            </span>
                        </div>

                        <!-- Hover Action Quick Buttons -->
                        <div class="row-actions" onclick="event.stopPropagation();">
                            <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.archiveSingle(<?= (int)$msg['message_id'] ?>)" title="Archive">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>
                            </button>
                            <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.deleteSingle(<?= (int)$msg['message_id'] ?>)" title="Delete">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </button>
                            <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.toggleReadSingle(<?= (int)$msg['message_id'] ?>, <?= !empty($msg['is_read']) ? '0' : '1' ?>)" title="<?= !empty($msg['is_read']) ? 'Mark unread' : 'Mark read' ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                            </button>
                            <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.openSnoozeMenu(<?= (int)$msg['message_id'] ?>)" title="Snooze">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                            </button>
                            <?php if ($currentFolder === 'snoozed'): ?>
                                <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.unsnoozeSingle(<?= (int)$msg['message_id'] ?>)" title="Un-snooze / Move to Inbox">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty Folder State -->
            <div class="empty-state">
                <div class="empty-icon-circle">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 12h-6l-2 3h-4l-2-3H2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6z"></path>
                        <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
                    </svg>
                </div>
                <h3>Your <?= ucfirst($currentFolder) ?> is empty</h3>
                <p>Messages in this mailbox view will appear here.</p>
                <?php if ($currentFolder === 'trash' || $currentFolder === 'spam'): ?>
                    <span class="empty-hint">Items in <?= ucfirst($currentFolder) ?> are automatically reviewed for retention.</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Reading View Pane (Single email detail view) -->
    <div class="reading-pane" id="readingPane" style="display: none;">
        <div class="reading-pane-toolbar">
            <button type="button" class="btn-icon" id="btnBackToList" title="Back to <?= ucfirst($currentFolder) ?>" aria-label="Back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </button>
            <div class="toolbar-divider"></div>
            <button type="button" class="btn-icon" id="btnReadingArchive" title="Archive">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>
            </button>
            <button type="button" class="btn-icon" id="btnReadingSpam" title="Report Spam">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z"/></svg>
            </button>
            <button type="button" class="btn-icon" id="btnReadingDelete" title="Delete">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
            </button>
            <button type="button" class="btn-icon" id="btnReadingUnread" title="Mark as unread">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z"/></svg>
            </button>
            <button type="button" class="btn-icon" id="btnReadingSnooze" title="Snooze">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
            </button>
            <button type="button" class="btn-icon" id="btnReadingSchedule" title="Schedule / Add to Calendar" onclick="window.MailApp.calendar.scheduleFromActiveMessage()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </button>
        </div>

        <div class="reading-pane-content" id="readingPaneContent">
            <!-- Dynamically populated by JS when clicking an email -->
        </div>
    </div>
</div>
