/**
 * nkinfyx groups Webmail - Client Controller
 */
window.togglePasswordVisibility = function(targetId, btn) {
    const input = document.getElementById(targetId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    btn.title = isPassword ? 'Hide password' : 'Show password';
    const iconEye = btn.querySelector('.icon-eye');
    const iconEyeOff = btn.querySelector('.icon-eye-off');
    if (iconEye && iconEyeOff) {
        iconEye.style.display = isPassword ? 'none' : 'block';
        iconEyeOff.style.display = isPassword ? 'block' : 'none';
    }
};

(function() {
    'use strict';

    const MailApp = {
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        currentFolder: document.getElementById('mailboxView')?.dataset.folder || 'inbox',
        currentCategory: 'primary',
        currentLabelId: null,
        activeMessageId: null,
        draftTimeout: null,
        selectedAttachmentIds: [],

        init() {
            this.bindEvents();
            this.bindComposeEvents();
            this.bindSenderHovercard();
            this.updateLiveFolderCounts();
            this.calendar.init();
        },

        bindEvents() {
            // Profile menu toggle
            const userMenuToggle = document.getElementById('userMenuToggle');
            const userDropdown = document.getElementById('userDropdown');
            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                });
                document.addEventListener('click', () => userDropdown.classList.remove('show'));
            }

            // Close custom select dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('#customAdvFolderContainer')) {
                    this.closeCustomAdvFolder();
                }
            });

            // Sidebar toggle (Desktop: Collapse/Expand mini bar | Mobile: Open/Close drawer)
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            // Restore desktop preference from localStorage
            if (window.innerWidth > 768 && localStorage.getItem('sidebar_collapsed') === 'true') {
                sidebar?.classList.add('collapsed');
            }

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (window.innerWidth <= 768) {
                        // Mobile drawer toggle
                        const isOpen = sidebar.classList.contains('open');
                        sidebar.classList.toggle('open', !isOpen);
                        backdrop?.classList.toggle('active', !isOpen);
                    } else {
                        // Desktop compact/expanded toggle
                        const isCollapsed = sidebar.classList.toggle('collapsed');
                        localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
                    }
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', () => {
                    sidebar?.classList.remove('open');
                    backdrop.classList.remove('active');
                });
            }

            // Close sidebar when clicking any navigation link on mobile
            document.querySelectorAll('.app-sidebar .nav-item').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        sidebar?.classList.remove('open');
                        backdrop?.classList.remove('active');
                    }
                });
            });

            // Mobile Compose FAB
            const mobileFab = document.getElementById('mobileComposeFab');
            if (mobileFab) {
                mobileFab.addEventListener('click', () => {
                    this.openCompose();
                });
            }

            // Category tabs
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    this.currentCategory = tab.dataset.category;
                    this.fetchMessages();
                });
            });

            // Select all checkbox
            const selectAll = document.getElementById('selectAllCheckbox');
            if (selectAll) {
                selectAll.addEventListener('change', (e) => {
                    const checked = e.target.checked;
                    document.querySelectorAll('.msg-select-box').forEach(cb => cb.checked = checked);
                    this.updateBulkToolbar();
                });
            }

            // Message row selections
            document.addEventListener('change', (e) => {
                if (e.target.classList.contains('msg-select-box')) {
                    this.updateBulkToolbar();
                }
            });

            // Row click to open reading pane or resume editing draft
            document.addEventListener('click', (e) => {
                const row = e.target.closest('.message-row');
                if (row && !e.target.closest('.cell-check') && !e.target.closest('.cell-star') && !e.target.closest('.cell-important') && !e.target.closest('.row-actions')) {
                    const messageId = row.dataset.messageId;
                    if (this.currentFolder === 'drafts') {
                        this.resumeDraft(messageId);
                    } else {
                        this.openReadingPane(messageId);
                    }
                }
            });

            // Sidebar More / Less toggle
            const btnToggleMore = document.getElementById('btnToggleMore');
            const moreNavSection = document.getElementById('moreNavSection');
            const lblToggleMore = document.getElementById('lblToggleMore');

            if (btnToggleMore && moreNavSection) {
                // Auto-expand if the active folder is inside More
                const moreFolders = ['important', 'scheduled', 'all', 'spam', 'trash'];
                if (moreFolders.includes(this.currentFolder)) {
                    moreNavSection.style.display = 'block';
                    btnToggleMore.setAttribute('aria-expanded', 'true');
                    btnToggleMore.classList.add('expanded');
                    if (lblToggleMore) lblToggleMore.textContent = 'Less';
                }

                btnToggleMore.addEventListener('click', (e) => {
                    e.preventDefault();
                    const isClosed = moreNavSection.style.display === 'none' || moreNavSection.style.display === '';
                    moreNavSection.style.display = isClosed ? 'block' : 'none';
                    btnToggleMore.setAttribute('aria-expanded', isClosed ? 'true' : 'false');
                    btnToggleMore.classList.toggle('expanded', isClosed);
                    if (lblToggleMore) lblToggleMore.textContent = isClosed ? 'Less' : 'More';
                });
            }

            // Back button in reading pane
            const btnBack = document.getElementById('btnBackToList');
            if (btnBack) {
                btnBack.addEventListener('click', () => this.closeReadingPane());
            }

            // Refresh button
            const btnRefresh = document.getElementById('btnRefresh');
            if (btnRefresh) {
                btnRefresh.addEventListener('click', () => {
                    this.syncInbox(true);
                });
            }

            // Periodic auto-check for incoming replies every 45 seconds
            setInterval(() => {
                if (document.visibilityState === 'visible') {
                    this.syncInbox(false);
                }
            }, 45000);

            // Periodic auto-check for snooze expiries and live folder counts every 12 seconds
            setInterval(() => {
                if (document.visibilityState === 'visible') {
                    this.pollSnoozeAndCounts();
                }
            }, 12000);

            // Instant snooze/counts refresh when user switches back to this browser tab
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    this.pollSnoozeAndCounts();
                }
            });

            // Bulk action buttons
            document.getElementById('bulkArchiveBtn')?.addEventListener('click', () => this.bulkMove('archive'));
            document.getElementById('bulkSpamBtn')?.addEventListener('click', () => this.bulkMove('spam'));
            document.getElementById('bulkDeleteBtn')?.addEventListener('click', () => this.bulkMove('trash'));
            document.getElementById('bulkMarkReadBtn')?.addEventListener('click', () => this.bulkMarkRead(true));
            document.getElementById('bulkMarkUnreadBtn')?.addEventListener('click', () => this.bulkMarkRead(false));
            document.getElementById('bulkSnoozeBtn')?.addEventListener('click', () => this.openBulkSnooze());
            document.getElementById('bulkRestoreBtn')?.addEventListener('click', () => {
                const selected = Array.from(document.querySelectorAll('.msg-select-box:checked')).map(cb => parseInt(cb.value));
                if (selected.length) {
                    this.bulkMove('inbox', selected);
                }
            });

            // Reading pane actions
            document.getElementById('btnReadingArchive')?.addEventListener('click', () => {
                if (this.activeMessageId) {
                    this.archiveSingle(this.activeMessageId);
                    this.closeReadingPane();
                }
            });
            document.getElementById('btnReadingDelete')?.addEventListener('click', () => {
                if (this.activeMessageId) {
                    this.deleteSingle(this.activeMessageId);
                    this.closeReadingPane();
                }
            });
            document.getElementById('btnReadingSpam')?.addEventListener('click', () => {
                if (this.activeMessageId) {
                    this.bulkMove('spam', [this.activeMessageId]);
                    this.closeReadingPane();
                }
            });
            document.getElementById('btnReadingUnread')?.addEventListener('click', () => {
                if (this.activeMessageId) {
                    this.toggleReadSingle(this.activeMessageId, 0);
                    this.closeReadingPane();
                }
            });
            document.getElementById('btnReadingSnooze')?.addEventListener('click', () => {
                if (this.activeMessageId) {
                    this.openSnoozeMenu(this.activeMessageId);
                }
            });

            // Advanced search button
            document.getElementById('openAdvancedSearchBtn')?.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.openAdvancedSearch();
            });
        },

        bindComposeEvents() {
            const btnOpen = document.getElementById('btnComposeOpen');
            const modal = document.getElementById('composeModal');
            const btnClose = document.getElementById('btnComposeClose');
            const btnMinimize = document.getElementById('btnComposeMinimize');
            const btnMaximize = document.getElementById('btnComposeMaximize');
            const btnDiscard = document.getElementById('btnDiscardDraft');

            if (btnOpen) {
                btnOpen.addEventListener('click', () => this.openCompose());
            }

            if (btnClose) {
                btnClose.addEventListener('click', () => {
                    this.saveDraftDebounced(true);
                    modal.style.display = 'none';
                });
            }

            if (btnMinimize) {
                btnMinimize.addEventListener('click', () => modal.classList.toggle('minimized'));
            }

            if (btnMaximize) {
                btnMaximize.addEventListener('click', () => modal.classList.toggle('maximized'));
            }

            if (btnDiscard) {
                btnDiscard.addEventListener('click', () => this.discardCurrentDraft());
            }

            // Cc / Bcc toggle
            document.getElementById('btnToggleCc')?.addEventListener('click', () => {
                const row = document.getElementById('rowCc');
                row.style.display = row.style.display === 'none' ? 'flex' : 'none';
            });
            document.getElementById('btnToggleBcc')?.addEventListener('click', () => {
                const row = document.getElementById('rowBcc');
                row.style.display = row.style.display === 'none' ? 'flex' : 'none';
            });

            // Auto-save triggers on input
            const editor = document.getElementById('composeBodyEditor');
            const subj = document.getElementById('composeSubject');
            const to = document.getElementById('composeTo');

            [editor, subj, to].forEach(el => {
                if (el) {
                    el.addEventListener('input', () => this.triggerAutoSave());
                }
            });
        },

        /* ==========================================================
           Compose and Draft Auto-saving
           ========================================================== */
        openCompose(replyData = null) {
            const modal = document.getElementById('composeModal');
            modal.style.display = 'flex';
            modal.classList.remove('minimized');

            const userSettings = window.CURRENT_USER_SETTINGS || {};
            const signature = (userSettings.signature || '').trim();
            const shouldAppend = (userSettings.append_signature !== undefined) ? !!userSettings.append_signature : true;

            if (replyData) {
                document.getElementById('composeTo').value = replyData.to || '';
                document.getElementById('composeSubject').value = replyData.subject || '';
                let bodyContent = replyData.body ? `<br><br><blockquote>--- Original Message ---<br>${replyData.body}</blockquote>` : '';
                if (shouldAppend && signature) {
                    bodyContent = `<br><br>--<br>${this.escapeHtml(signature).replace(/\n/g, '<br>')}` + bodyContent;
                }
                document.getElementById('composeBodyEditor').innerHTML = bodyContent;
                document.getElementById('composeDraftId').value = '';
                document.getElementById('composeTitle').textContent = replyData.title || 'Reply Message';
            } else {
                document.getElementById('composeForm').reset();
                if (shouldAppend && signature) {
                    document.getElementById('composeBodyEditor').innerHTML = `<br><br>--<br>${this.escapeHtml(signature).replace(/\n/g, '<br>')}`;
                } else {
                    document.getElementById('composeBodyEditor').innerHTML = '';
                }
                document.getElementById('composeDraftId').value = '';
                document.getElementById('composeAttachmentsList').innerHTML = '';
                this.selectedAttachmentIds = [];
                document.getElementById('composeTitle').textContent = 'New Message';
            }

            document.getElementById('draftStatusIndicator').textContent = '';
            document.getElementById('composeTo').focus();
        },

        triggerAutoSave() {
            const userSettings = window.CURRENT_USER_SETTINGS || {};
            const autoSave = (userSettings.auto_save_drafts !== undefined) ? !!userSettings.auto_save_drafts : true;
            if (!autoSave) return;

            const statusEl = document.getElementById('draftStatusIndicator');
            statusEl.textContent = 'Saving draft...';
            clearTimeout(this.draftTimeout);
            this.draftTimeout = setTimeout(() => this.saveDraftDebounced(), 1500);
        },

        async saveDraftDebounced(force = false) {
            const to = document.getElementById('composeTo').value.trim();
            const subject = document.getElementById('composeSubject').value.trim();
            const bodyHtml = document.getElementById('composeBodyEditor').innerHTML.trim();
            const draftId = document.getElementById('composeDraftId').value;

            if (!to && !subject && !bodyHtml) {
                document.getElementById('draftStatusIndicator').textContent = '';
                return;
            }

            try {
                const res = await fetch('/api/messages/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        _csrf_token: this.csrfToken,
                        is_draft: 1,
                        draft_id: draftId || null,
                        to: to,
                        subject: subject,
                        body_html: bodyHtml,
                        attachment_ids: this.selectedAttachmentIds
                    })
                });

                const data = await res.json();
                if (data.success) {
                    document.getElementById('composeDraftId').value = data.message_id;
                    document.getElementById('draftStatusIndicator').textContent = `Draft saved at ${data.saved_at}`;
                    this.updateLiveFolderCounts();
                }
            } catch (err) {
                console.error("Draft autosave error:", err);
            }
        },

        async sendEmail() {
            const to = document.getElementById('composeTo').value.trim();
            const subject = document.getElementById('composeSubject').value.trim();
            const bodyHtml = document.getElementById('composeBodyEditor').innerHTML.trim();
            const draftId = document.getElementById('composeDraftId').value;
            const btnSend = document.getElementById('btnSendMail');

            if (!to) {
                alert('Please enter at least one recipient email address.');
                return;
            }

            btnSend.disabled = true;
            btnSend.textContent = 'Sending...';

            try {
                const res = await fetch('/api/messages/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        _csrf_token: this.csrfToken,
                        is_draft: 0,
                        draft_id: draftId || null,
                        to: to,
                        subject: subject,
                        body_html: bodyHtml,
                        attachment_ids: this.selectedAttachmentIds
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast(data.notice || 'Message sent successfully.');
                    document.getElementById('composeModal').style.display = 'none';
                    document.getElementById('composeForm').reset();
                    document.getElementById('composeBodyEditor').innerHTML = '';
                    this.selectedAttachmentIds = [];
                    this.fetchMessages();
                    this.updateLiveFolderCounts();
                } else {
                    this.showToast(data.message || 'Error sending message', 'error');
                }
            } catch (err) {
                this.showToast('Network error while sending', 'error');
            } finally {
                btnSend.disabled = false;
                btnSend.innerHTML = `<span>Send</span><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>`;
            }
        },

        discardCurrentDraft() {
            const userSettings = window.CURRENT_USER_SETTINGS || {};
            const confirmDiscard = (userSettings.confirm_discard !== undefined) ? !!userSettings.confirm_discard : true;

            const to = document.getElementById('composeTo').value.trim();
            const subject = document.getElementById('composeSubject').value.trim();
            const body = document.getElementById('composeBodyEditor').innerHTML.trim();

            if (confirmDiscard && (to || subject || (body && !body.startsWith('<br><br>--<br>')))) {
                if (!confirm('Discard this draft? Any unsaved edits will be lost.')) {
                    return;
                }
            }

            const draftId = document.getElementById('composeDraftId').value;
            if (draftId) {
                this.bulkMove('trash', [draftId]);
            }
            document.getElementById('composeModal').style.display = 'none';
            document.getElementById('composeForm').reset();
            document.getElementById('composeBodyEditor').innerHTML = '';
            this.selectedAttachmentIds = [];
            this.showToast('Draft discarded');
        },

        async handleFileUpload(files) {
            if (!files || !files.length) return;
            const listEl = document.getElementById('composeAttachmentsList');

            for (const file of files) {
                const formData = new FormData();
                formData.append('attachment', file);
                formData.append('_csrf_token', this.csrfToken);

                const chip = document.createElement('span');
                chip.className = 'compose-att-chip';
                chip.textContent = `Uploading ${file.name}...`;
                listEl.appendChild(chip);

                try {
                    const res = await fetch('/api/attachments/upload', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken },
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        const attId = parseInt(data.attachment_id);
                        this.selectedAttachmentIds.push(attId);
                        chip.innerHTML = `<span>📎 ${this.escapeHtml(data.name)}</span> <span class="btn-remove-att" title="Remove attachment" style="cursor:pointer; margin-left:6px; font-weight:bold;">&times;</span>`;
                        chip.querySelector('.btn-remove-att').addEventListener('click', () => {
                            this.selectedAttachmentIds = this.selectedAttachmentIds.filter(id => id !== attId);
                            chip.remove();
                        });
                    } else {
                        chip.innerHTML = `<span style="color:#d93025">Failed: ${this.escapeHtml(data.message)}</span> <span class="btn-remove-att" style="cursor:pointer; margin-left:6px;" onclick="this.parentElement.remove();">&times;</span>`;
                    }
                } catch (err) {
                    chip.innerHTML = `<span style="color:red">Upload failed</span>`;
                }
            }
        },

        insertLinkPrompt() {
            const url = prompt('Enter URL (e.g. https://example.com):', 'https://');
            if (url) {
                document.execCommand('createLink', false, url);
            }
        },

        /* ==========================================================
           Reading View Pane
           ========================================================== */
        async openReadingPane(messageId) {
            this.activeMessageId = messageId;
            const pane = document.getElementById('readingPane');
            const container = document.getElementById('readingPaneContent');
            const listContainer = document.getElementById('messageListContainer');
            const categoryTabs = document.getElementById('categoryTabs');

            if (listContainer) listContainer.style.display = 'none';
            if (categoryTabs) categoryTabs.style.display = 'none';
            pane.style.display = 'flex';
            container.innerHTML = '<div style="padding:40px; text-align:center; color:#888;">Loading message...</div>';

            try {
                const res = await fetch(`/api/messages/${messageId}`);
                const data = await res.json();

                if (!data.success || !data.message) {
                    container.innerHTML = '<div style="padding:40px; color:red;">Failed to load message.</div>';
                    return;
                }

                const msg = data.message;
                this.currentActiveMessage = msg;
                const senderInitial = (msg.sender_name || msg.sender_email || 'U').charAt(0).toUpperCase();

                // Smart Interview / Meeting Detection
                const combinedText = ((msg.subject || '') + ' ' + (msg.body_text || '') + ' ' + (msg.snippet || '')).toLowerCase();
                const isInterviewMeeting = /interview|meeting|schedule|candidate|discussion|apply for|shortlist|hiring|job/i.test(combinedText);

                let interviewBannerHtml = '';
                if (isInterviewMeeting) {
                    interviewBannerHtml = `
                        <div class="interview-event-banner">
                            <div class="interview-event-left">
                                <span class="interview-event-icon">💼</span>
                                <div>
                                    <div class="interview-event-title">Interview / Meeting Discussion Detected</div>
                                    <div class="interview-event-subtitle">Add this candidate / work schedule directly to your Google-style Calendar</div>
                                </div>
                            </div>
                            <button type="button" class="btn-interview-schedule" onclick="window.MailApp.calendar.scheduleFromActiveMessage()">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <span>Schedule on Calendar</span>
                            </button>
                        </div>
                    `;
                }

                let attachmentsHtml = '';
                if (msg.attachments && msg.attachments.length) {
                    attachmentsHtml = `
                        <div class="reading-attachments-box">
                            <div class="reading-attachments-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                <span>${msg.attachments.length} Attachment(s)</span>
                            </div>
                            <div class="attachments-grid">
                                ${msg.attachments.map(att => `
                                    <a href="/api/attachments/${att.id}/download" class="attachment-card" target="_blank" download>
                                        <div class="att-icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                        </div>
                                        <div class="att-details">
                                            <div class="att-name">${this.escapeHtml(att.original_name)}</div>
                                            <div class="att-size">${(att.size_bytes / 1024).toFixed(1)} KB</div>
                                        </div>
                                    </a>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                container.innerHTML = `
                    <div class="email-reading-header">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                            <h2 class="reading-subject" style="margin-bottom: 0;">${this.escapeHtml(msg.subject || '(no subject)')}</h2>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button type="button" class="btn-star ${msg.is_starred ? 'starred' : ''}" onclick="window.MailApp.toggleStar(${msg.message_id || msg.id}, this);" title="Star message">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="${msg.is_starred ? '#f4b400' : 'none'}" stroke="${msg.is_starred ? '#f4b400' : 'currentColor'}" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                </button>
                                <button type="button" class="btn-important ${msg.is_important ? 'important' : ''}" onclick="window.MailApp.toggleImportant(${msg.message_id || msg.id}, this);" title="Mark as important">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="${msg.is_important ? '#f29900' : 'none'}" stroke="${msg.is_important ? '#f29900' : 'currentColor'}" stroke-width="2">
                                        <polygon points="5 3 19 3 15 12 19 21 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="reading-sender-row" style="margin-top: 14px;">
                            <div class="sender-identity" data-name="${this.escapeHtml(msg.sender_name || msg.sender_email)}" data-email="${this.escapeHtml(msg.sender_email)}" style="cursor: pointer;">
                                <div class="sender-avatar">${senderInitial}</div>
                                <div class="sender-meta">
                                    <span class="sender-name-full">${this.escapeHtml(msg.sender_name || msg.sender_email)}</span>
                                    <span class="sender-email-sub">&lt;${this.escapeHtml(msg.sender_email)}&gt;</span>
                                    <span class="sender-email-sub">To: ${msg.recipients.map(r => this.escapeHtml(r.email)).join(', ')}</span>
                                </div>
                            </div>
                            <div class="reading-date-full">
                                ${new Date(msg.message_date).toLocaleString()}
                            </div>
                        </div>
                    </div>

                    ${interviewBannerHtml}

                    <div class="email-reading-body">
                        ${msg.body_html || `<p>${this.escapeHtml(msg.body_text || '')}</p>`}
                    </div>

                    ${attachmentsHtml}

                    <div class="reading-quick-reply">
                        <button type="button" class="btn btn-secondary" onclick="window.MailApp.replyToCurrent(false)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>
                            <span>Reply</span>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.MailApp.replyToCurrent(true)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M14 9V5l7 7-7 7v-4.1c-5 0-8.5 1.6-11 5.1 1-5 4-10 11-11z"/></svg>
                            <span>Forward</span>
                        </button>
                    </div>
                `;

                // Update row in list to read state
                const row = document.querySelector(`.message-row[data-message-id="${messageId}"]`);
                if (row) {
                    row.classList.remove('unread');
                    row.classList.add('read');
                }
                this.updateLiveFolderCounts();
            } catch (err) {
                container.innerHTML = '<div style="padding:40px; color:red;">Network error loading email.</div>';
            }
        },

        closeReadingPane() {
            this.activeMessageId = null;
            document.getElementById('readingPane').style.display = 'none';
            document.getElementById('messageListContainer').style.display = 'block';
            if (this.currentFolder === 'inbox' && !this.currentLabelId) {
                document.getElementById('categoryTabs').style.display = 'flex';
            }
        },

        replyToCurrent(isForward = false) {
            if (!this.activeMessageId) return;
            fetch(`/api/messages/${this.activeMessageId}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.message) {
                        const m = res.message;
                        const userSettings = window.CURRENT_USER_SETTINGS || {};
                        let recipient = m.sender_email;
                        if (!isForward && userSettings.default_reply_behavior === 'reply_all') {
                            const recipients = [m.sender_email];
                            if (m.recipient_email && m.recipient_email !== userSettings.email) {
                                recipients.push(m.recipient_email);
                            }
                            recipient = recipients.join(', ');
                        }
                        this.openCompose({
                            to: isForward ? '' : recipient,
                            subject: (isForward ? 'Fwd: ' : 'Re: ') + (m.subject || ''),
                            body: m.body_html || m.body_text,
                            title: isForward ? 'Forward Message' : (userSettings.default_reply_behavior === 'reply_all' ? 'Reply All' : 'Reply Message')
                        });
                    }
                });
        },

        /* ==========================================================
           Star, Read, Move, Delete Single & Bulk Actions
           ========================================================== */
        async toggleStar(messageId, element) {
            try {
                const res = await fetch('/api/messages/star', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ message_id: messageId, _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    const btn = element.querySelector('.btn-star') || element;
                    const svg = btn.querySelector('svg');
                    if (data.is_starred) {
                        btn.classList.add('starred');
                        if (svg) {
                            svg.setAttribute('fill', '#f4b400');
                            svg.setAttribute('stroke', '#f4b400');
                        }
                    } else {
                        btn.classList.remove('starred');
                        if (svg) {
                            svg.setAttribute('fill', 'none');
                            svg.setAttribute('stroke', 'currentColor');
                        }
                    }
                    this.updateLiveFolderCounts();
                }
            } catch (err) {
                console.error(err);
            }
        },

        async toggleReadSingle(messageId, isRead) {
            await fetch('/api/messages/mark-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify({ message_ids: [messageId], is_read: isRead, _csrf_token: this.csrfToken })
            });
            this.fetchMessages();
            this.updateLiveFolderCounts();
        },

        async archiveSingle(messageId) {
            await this.bulkMove('archive', [messageId]);
        },

        async deleteSingle(messageId) {
            await this.bulkMove('trash', [messageId]);
        },

        async bulkMove(targetFolder, explicitIds = null) {
            const ids = explicitIds || this.getSelectedMessageIds();
            if (!ids.length) return;

            try {
                const res = await fetch('/api/messages/move', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ message_ids: ids, folder: targetFolder, _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(`Moved ${ids.length} message(s) to ${targetFolder}`);
                    this.fetchMessages();
                    this.updateLiveFolderCounts();
                }
            } catch (err) {
                this.showToast('Failed to move messages', 'error');
            }
        },

        async bulkMarkRead(isRead) {
            const ids = this.getSelectedMessageIds();
            if (!ids.length) return;

            await fetch('/api/messages/mark-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify({ message_ids: ids, is_read: isRead ? 1 : 0, _csrf_token: this.csrfToken })
            });

            this.showToast(`Marked ${ids.length} as ${isRead ? 'read' : 'unread'}`);
            this.fetchMessages();
            this.updateLiveFolderCounts();
        },

        getSelectedMessageIds() {
            return Array.from(document.querySelectorAll('.msg-select-box:checked')).map(cb => parseInt(cb.value, 10));
        },

        updateBulkToolbar() {
            const selected = this.getSelectedMessageIds();
            const group = document.getElementById('bulkActionsGroup');
            if (group) {
                group.style.display = selected.length > 0 ? 'flex' : 'none';
            }
        },

        /* ==========================================================
           Fetch Messages & Folder Counts
           ========================================================== */
        async syncInbox(showFeedback = true) {
            const btnRefresh = document.getElementById('btnRefresh');
            if (btnRefresh) {
                btnRefresh.style.animation = 'spin 0.8s linear infinite';
            }

            try {
                const res = await fetch('/api/sync', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ _csrf_token: this.csrfToken })
                });
                const data = await res.json();

                if (data.success) {
                    const restoredCount = data.data?.restored_snoozes || 0;
                    if (data.synced > 0 || restoredCount > 0) {
                        if (restoredCount > 0) {
                            this.showToast(`⏰ ${restoredCount} snoozed email(s) returned to Inbox!`);
                        } else {
                            this.showToast(data.message || `Received ${data.synced} new email(s)!`);
                        }
                        this.fetchMessages();
                        this.updateLiveFolderCounts();
                    } else if (showFeedback) {
                        this.showToast(data.message || 'Mailbox is up to date.');
                        this.fetchMessages();
                        this.updateLiveFolderCounts();
                    }
                } else if (showFeedback && data.message) {
                    this.showToast(data.message, 'error');
                }
            } catch (err) {
                if (showFeedback) this.showToast('Updated local mailbox.');
                this.fetchMessages();
                this.updateLiveFolderCounts();
            } finally {
                if (btnRefresh) {
                    btnRefresh.style.animation = '';
                }
            }
        },

        async fetchMessages() {
            const query = new URLSearchParams({
                folder: this.currentFolder,
                category: this.currentFolder === 'inbox' ? this.currentCategory : '',
                label_id: this.currentLabelId || '',
                q: document.getElementById('globalSearchInput')?.value.trim() || ''
            });

            try {
                const res = await fetch(`/api/messages?${query.toString()}`);
                const data = await res.json();
                if (data.success && data.data) {
                    this.renderMessageList(data.data.messages);
                    const info = document.getElementById('paginationInfo');
                    if (info) {
                        info.textContent = data.data.total > 0 ? `1–${Math.min(data.data.total, 30)} of ${data.data.total}` : '0 of 0';
                    }
                }
            } catch (err) {
                console.error("Error fetching messages:", err);
            }
        },

        renderMessageList(messages) {
            const container = document.getElementById('messageListContainer');
            if (!container) return;

            if (!messages || !messages.length) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon-circle">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 12h-6l-2 3h-4l-2-3H2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6z"></path><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
                        </div>
                        <h3>Your ${this.currentFolder} is empty</h3>
                        <p>No messages found in this mailbox.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="message-list" id="messageList">
                    ${messages.map(msg => `
                        <div class="message-row ${msg.is_read ? 'read' : 'unread'}" data-message-id="${msg.message_id}">
                            <div class="row-cell cell-check" onclick="event.stopPropagation();">
                                <input type="checkbox" class="app-checkbox msg-select-box" value="${msg.message_id}">
                            </div>
                            <div class="row-cell cell-star" onclick="event.stopPropagation(); window.MailApp.toggleStar(${msg.message_id}, this);">
                                <button type="button" class="btn-star ${msg.is_starred ? 'starred' : ''}" title="Star message">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="${msg.is_starred ? '#f4b400' : 'none'}" stroke="${msg.is_starred ? '#f4b400' : 'currentColor'}" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                </button>
                            </div>
                            <div class="row-cell cell-important" onclick="event.stopPropagation(); window.MailApp.toggleImportant(${msg.message_id}, this);">
                                <button type="button" class="btn-important ${msg.is_important ? 'important' : ''}" title="Mark as important">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="${msg.is_important ? '#f29900' : 'none'}" stroke="${msg.is_important ? '#f29900' : 'currentColor'}" stroke-width="2">
                                        <polygon points="5 3 19 3 15 12 19 21 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                            <div class="row-cell cell-sender" 
                                 data-name="${this.escapeHtml(msg.sender_name || msg.sender_email)}" 
                                 data-email="${this.escapeHtml(msg.sender_email)}" 
                                 title="${this.escapeHtml(msg.sender_email)}">
                                ${this.currentFolder === 'sent' && msg.recipient_display 
                                    ? `<span class="sender-text" style="color: var(--brand-primary);">To: ${this.escapeHtml(msg.recipient_display)}</span>`
                                    : `<span class="sender-text">${this.escapeHtml(msg.sender_name || msg.sender_email)}</span>`}
                            </div>
                            <div class="row-cell cell-content">
                                ${this.currentFolder === 'drafts' ? `<span class="row-draft-chip">Draft</span>` : ''}
                                ${msg.category === 'purchases' ? `<span class="row-label-chip" style="background-color: #e6f4ea; color: #137333; border: 1px solid #ceead6;">Purchase</span>` : ''}
                                ${msg.labels && msg.labels.length ? `
                                    <span class="row-labels">
                                        ${msg.labels.map(l => `<span class="row-label-chip" style="background-color: ${l.color}18; color:${l.color}; border: 1px solid ${l.color}40;">${this.escapeHtml(l.name)}</span>`).join('')}
                                    </span>
                                ` : ''}
                                <span class="subject-text">${this.escapeHtml(msg.subject || '(no subject)')}</span>
                                <span class="snippet-delimiter">-</span>
                                <span class="snippet-text">${this.escapeHtml(msg.snippet || '')}</span>
                            </div>
                            <div class="row-cell cell-attachment">
                                ${msg.attachment_count > 0 ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>` : ''}
                            </div>
                            <div class="row-cell cell-date" style="${this.currentFolder === 'snoozed' ? 'color: #d97706; font-weight: 500;' : ''}">
                                <span>${this.currentFolder === 'snoozed' && msg.snoozed_until ? `Until ${this.formatSnoozeDate(msg.snoozed_until)}` : new Date(msg.message_date).toLocaleDateString(undefined, {month: 'short', day: 'numeric'})}</span>
                            </div>
                            <div class="row-actions" onclick="event.stopPropagation();">
                                <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.archiveSingle(${msg.message_id})" title="Archive">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>
                                </button>
                                <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.deleteSingle(${msg.message_id})" title="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                </button>
                                <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.openSnoozeMenu(${msg.message_id})" title="Snooze">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                </button>
                                ${this.currentFolder === 'snoozed' ? `
                                    <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.unsnoozeSingle(${msg.message_id})" title="Un-snooze / Move to Inbox">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
            this.updateBulkToolbar();
        },

        async updateLiveFolderCounts() {
            try {
                const res = await fetch('/api/counts');
                const data = await res.json();
                if (data.success && data.counts) {
                    const c = data.counts;
                    this.setBadge('badge-inbox', c.inbox?.unread);
                    this.setBadge('badge-starred', c.starred?.total);
                    this.setBadge('badge-snoozed', c.snoozed?.total);
                    this.setBadge('badge-drafts', c.drafts?.total);
                    this.setBadge('badge-purchases', c.purchases?.unread);
                    this.setBadge('badge-important', c.important?.total);
                    this.setBadge('badge-scheduled', c.scheduled?.total);
                    this.setBadge('badge-spam', c.spam?.unread);
                }
            } catch (err) {
                console.error("Counts update error:", err);
            }
        },

        setBadge(elementId, count) {
            const el = document.getElementById(elementId);
            if (!el) return;
            if (count > 0) {
                el.textContent = count;
                el.style.display = 'inline-block';
            } else {
                el.style.display = 'none';
            }
        },

        /* ==========================================================
           Search and Advanced Filters
           ========================================================== */
        triggerSearch() {
            const q = document.getElementById('globalSearchInput').value.trim();
            window.location.href = `/mail/inbox?q=${encodeURIComponent(q)}`;
        },

        openAdvancedSearch() {
            document.getElementById('advancedSearchModal').style.display = 'flex';
        },

        closeAdvancedSearch() {
            document.getElementById('advancedSearchModal').style.display = 'none';
            this.closeCustomAdvFolder();
        },

        toggleCustomAdvFolder(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const dropdown = document.getElementById('customAdvFolderDropdown');
            const trigger = document.getElementById('customAdvFolderTrigger');
            const container = document.getElementById('customAdvFolderContainer');
            if (!dropdown || !trigger || !container) return;

            const isOpen = dropdown.classList.contains('show');
            if (isOpen) {
                dropdown.classList.remove('show');
                dropdown.classList.remove('open-up');
                trigger.setAttribute('aria-expanded', 'false');
            } else {
                // Auto detect if it should open upwards
                const rect = container.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom;
                if (spaceBelow < 220 && rect.top > 200) {
                    dropdown.classList.add('open-up');
                } else {
                    dropdown.classList.remove('open-up');
                }
                dropdown.classList.add('show');
                trigger.setAttribute('aria-expanded', 'true');
            }
        },

        selectCustomAdvFolder(val, label) {
            const selectEl = document.getElementById('advFolder');
            const labelEl = document.getElementById('customAdvFolderLabel');
            const dropdown = document.getElementById('customAdvFolderDropdown');
            const trigger = document.getElementById('customAdvFolderTrigger');

            if (selectEl) selectEl.value = val;
            if (labelEl) labelEl.textContent = label;

            if (dropdown) {
                dropdown.querySelectorAll('.custom-select-option').forEach(opt => {
                    opt.classList.toggle('selected', opt.dataset.value === val);
                });
                dropdown.classList.remove('show');
            }
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        },

        closeCustomAdvFolder() {
            const dropdown = document.getElementById('customAdvFolderDropdown');
            const trigger = document.getElementById('customAdvFolderTrigger');
            if (dropdown) dropdown.classList.remove('show');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        },

        resetAdvancedSearch() {
            document.getElementById('advancedSearchForm').reset();
            this.selectCustomAdvFolder('inbox', 'Inbox');
        },

        submitAdvancedSearch() {
            const from = document.getElementById('advFrom').value.trim();
            const to = document.getElementById('advTo').value.trim();
            const subject = document.getElementById('advSubject').value.trim();
            const hasAtt = document.getElementById('advHasAttachment').checked ? 1 : 0;
            const folder = document.getElementById('advFolder').value;

            this.closeAdvancedSearch();
            const query = new URLSearchParams({
                folder: folder,
                adv_from: from,
                adv_to: to,
                adv_subject: subject,
                adv_has_attachment: hasAtt
            });

            window.location.href = `/mail/${folder}?${query.toString()}`;
        },

        /* ==========================================================
           SMTP Settings Modal
           ========================================================== */
        openSettingsModal() {
            document.getElementById('settingsModal').style.display = 'flex';
        },

        closeSettingsModal() {
            document.getElementById('settingsModal').style.display = 'none';
        },

        toggleSmtpFields(enabled) {
            const container = document.getElementById('smtpFieldsContainer');
            if (container) {
                container.style.opacity = enabled ? '1' : '0.5';
            }
        },

        async saveSmtpSettings() {
            const enabled = document.getElementById('settingSmtpEnabled').checked;
            const host = document.getElementById('settingSmtpHost').value.trim();
            const port = document.getElementById('settingSmtpPort').value.trim();
            const encryption = document.getElementById('settingSmtpEncryption').value;
            const sender = document.getElementById('settingSmtpSender').value.trim();
            const username = document.getElementById('settingSmtpUsername').value.trim();
            const password = document.getElementById('settingSmtpPassword').value;

            const btnSave = document.getElementById('btnSaveSmtpSettings');
            btnSave.disabled = true;
            btnSave.textContent = 'Saving...';

            try {
                const res = await fetch('/api/settings/smtp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({
                        _csrf_token: this.csrfToken,
                        enabled: enabled ? 1 : 0,
                        host: host,
                        port: port,
                        encryption: encryption,
                        sender: sender,
                        username: username,
                        password: password
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast('SMTP settings saved successfully!');
                    this.closeSettingsModal();
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    alert(data.message || 'Error saving settings');
                }
            } catch (err) {
                alert('Network error while saving settings');
            } finally {
                btnSave.disabled = false;
                btnSave.textContent = 'Save Settings';
            }
        },

        /* ==========================================================
           Custom Labels Modal
           ========================================================== */
        openLabelModal() {
            document.getElementById('labelModal').style.display = 'flex';
            document.getElementById('labelForm').reset();
            document.getElementById('editLabelId').value = '';
            document.getElementById('labelNameInput').focus();
        },

        closeLabelModal() {
            document.getElementById('labelModal').style.display = 'none';
        },

        async saveLabel() {
            const name = document.getElementById('labelNameInput').value.trim();
            const color = document.querySelector('input[name="label_color"]:checked')?.value || '#1a73e8';

            if (!name) return;

            try {
                const res = await fetch('/api/labels', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ name: name, color: color, _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast('Label created!');
                    this.closeLabelModal();
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    alert(data.message || 'Error creating label');
                }
            } catch (err) {
                alert('Network error creating label');
            }
        },

        /* ==========================================================
           Manage Labels Modal & CRUD
           ========================================================== */
        openManageLabelsModal() {
            const m = document.getElementById('manageLabelsModal');
            if (m) {
                m.style.display = 'flex';
                this.loadManageLabels();
            }
        },

        closeManageLabelsModal() {
            const m = document.getElementById('manageLabelsModal');
            if (m) m.style.display = 'none';
        },

        async loadManageLabels() {
            const container = document.getElementById('manageLabelsListContainer');
            if (!container) return;
            container.innerHTML = '<div style="font-size:12px; color:var(--text-muted); padding:10px;">Loading labels...</div>';
            try {
                const res = await fetch('/api/labels');
                const data = await res.json();
                if (data.success && data.labels) {
                    if (!data.labels.length) {
                        container.innerHTML = '<div style="font-size:13px; color:var(--text-secondary); padding:12px 0;">No labels created yet. Click "+ New Label" to create one.</div>';
                        return;
                    }
                    container.innerHTML = data.labels.map(lbl => `
                        <div class="manage-label-row" style="display:flex; align-items:center; justify-content:space-between; padding:8px 10px; background:#fff; border:1px solid var(--border-color); border-radius:var(--radius-sm); gap:8px;">
                            <div style="display:flex; align-items:center; gap:8px; flex:1;">
                                <input type="color" value="${this.escapeHtml(lbl.color)}" id="labelColor_${lbl.id}" style="width:24px; height:24px; border:none; border-radius:50%; cursor:pointer; padding:0; background:none;">
                                <input type="text" value="${this.escapeHtml(lbl.name)}" id="labelName_${lbl.id}" class="form-input" style="padding:4px 8px; font-size:13px; height:30px; flex:1;">
                            </div>
                            <div style="display:flex; align-items:center; gap:4px;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailApp.saveEditLabel(${lbl.id})" style="font-size:11px; padding:4px 8px;">Save</button>
                                <button type="button" class="btn-icon btn-sm" onclick="window.MailApp.deleteLabelFromManage(${lbl.id})" title="Delete label" style="color:var(--status-danger);">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                </button>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (e) {
                container.innerHTML = '<div style="color:red; font-size:12px;">Failed to load labels.</div>';
            }
        },

        async saveEditLabel(id) {
            const nameInput = document.getElementById(`labelName_${id}`);
            const colorInput = document.getElementById(`labelColor_${id}`);
            if (!nameInput || !nameInput.value.trim()) return;
            try {
                const res = await fetch(`/api/labels/${id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ name: nameInput.value.trim(), color: colorInput?.value || '#1a73e8', _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast('Label updated');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    alert(data.message || 'Error updating label');
                }
            } catch (e) {
                alert('Network error updating label');
            }
        },

        async deleteLabelFromManage(id) {
            if (!confirm('Are you sure you want to delete this label? (Your messages will NOT be deleted).')) {
                return;
            }
            try {
                const res = await fetch(`/api/labels/${id}/delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast('Label deleted');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    alert(data.message || 'Error deleting label');
                }
            } catch (e) {
                alert('Network error deleting label');
            }
        },

        /* ==========================================================
           Important Toggle Action
           ========================================================== */
        async toggleImportant(messageId, el) {
            try {
                const res = await fetch('/api/messages/important', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ message_id: messageId, _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    const btn = el ? el.querySelector('button') || el : null;
                    if (btn) {
                        const isImp = !!data.data?.is_important;
                        btn.classList.toggle('important', isImp);
                        const svg = btn.querySelector('svg');
                        if (svg) {
                            svg.setAttribute('fill', isImp ? '#f29900' : 'none');
                            svg.setAttribute('stroke', isImp ? '#f29900' : 'currentColor');
                        }
                    }
                    this.showToast(data.data?.is_important ? 'Marked as important' : 'Unmarked important');
                    this.updateLiveFolderCounts();
                }
            } catch (e) {
                console.error(e);
            }
        },

        /* ==========================================================
           Resume Editing Draft
           ========================================================== */
        async resumeDraft(messageId) {
            try {
                const res = await fetch(`/api/messages/${messageId}`);
                const data = await res.json();
                if (data.success && data.message) {
                    const msg = data.message;
                    this.openCompose();
                    document.getElementById('composeDraftId').value = msg.message_id || msg.id || messageId;
                    const toEmails = (msg.recipients && msg.recipients.to) ? msg.recipients.to.map(r => r.email).join(', ') : (msg.recipient_display || '');
                    document.getElementById('composeTo').value = toEmails;
                    if (msg.recipients && msg.recipients.cc && msg.recipients.cc.length) {
                        document.getElementById('composeCc').value = msg.recipients.cc.map(r => r.email).join(', ');
                        document.getElementById('rowCc').style.display = 'flex';
                    }
                    if (msg.recipients && msg.recipients.bcc && msg.recipients.bcc.length) {
                        document.getElementById('composeBcc').value = msg.recipients.bcc.map(r => r.email).join(', ');
                        document.getElementById('rowBcc').style.display = 'flex';
                    }
                    document.getElementById('composeSubject').value = msg.subject || '';
                    document.getElementById('composeBodyEditor').innerHTML = msg.body_html || msg.body_plain || '';
                    document.getElementById('composeTitle').textContent = 'Edit Draft';
                    document.getElementById('draftStatusIndicator').textContent = 'Draft loaded';
                }
            } catch (e) {
                console.error("Failed to load draft:", e);
            }
        },

        /* ==========================================================
           Snooze Actions & Dialogs
           ========================================================== */
        snoozeTargetIds: [],

        openSnoozeMenu(messageId) {
            this.snoozeTargetIds = [messageId];
            this.openSnoozeModal([messageId]);
        },

        openBulkSnooze() {
            const selected = Array.from(document.querySelectorAll('.msg-select-box:checked')).map(cb => parseInt(cb.value));
            if (!selected.length) {
                alert('Please select at least one message to snooze.');
                return;
            }
            this.openSnoozeModal(selected);
        },

        openSnoozeModal(ids = null) {
            if (ids) this.snoozeTargetIds = ids;
            const modal = document.getElementById('snoozeModal');
            if (modal) {
                // Update 2-minute dynamic label to show exact upcoming time
                const now = new Date();
                const in2Min = new Date(now.getTime() + 2 * 60 * 1000);
                const timeStr = in2Min.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                const preset2El = modal.querySelector('.btn-snooze-preset .preset-time');
                if (preset2El) preset2El.textContent = timeStr;

                // Pre-fill custom date & time with today and +1 hour
                const dateInput = document.getElementById('snoozeDate');
                const timeInput = document.getElementById('snoozeTime');
                if (dateInput) {
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const d = String(now.getDate()).padStart(2, '0');
                    dateInput.value = `${y}-${m}-${d}`;
                }
                if (timeInput) {
                    const in1Hour = new Date(now.getTime() + 60 * 60 * 1000);
                    const h = String(in1Hour.getHours()).padStart(2, '0');
                    const min = String(in1Hour.getMinutes()).padStart(2, '0');
                    timeInput.value = `${h}:${min}`;
                }

                modal.style.display = 'flex';
            }
        },

        closeSnoozeModal() {
            const modal = document.getElementById('snoozeModal');
            if (modal) modal.style.display = 'none';
        },

        async snoozeQuick(preset) {
            const now = new Date();
            let targetDate = new Date();

            if (preset === '2min') {
                targetDate = new Date(now.getTime() + 2 * 60 * 1000);
            } else if (preset === 'today') {
                if (now.getHours() >= 17) {
                    targetDate = new Date(now.getTime() + 3 * 60 * 60 * 1000);
                } else {
                    targetDate.setHours(18, 0, 0, 0);
                }
            } else if (preset === 'tomorrow') {
                targetDate.setDate(now.getDate() + 1);
                targetDate.setHours(8, 0, 0, 0);
            } else if (preset === 'nextweek') {
                const day = now.getDay();
                const daysUntilMon = ((1 + 7 - day) % 7) || 7;
                targetDate.setDate(now.getDate() + daysUntilMon);
                targetDate.setHours(8, 0, 0, 0);
            }

            const y = targetDate.getFullYear();
            const m = String(targetDate.getMonth() + 1).padStart(2, '0');
            const d = String(targetDate.getDate()).padStart(2, '0');
            const h = String(targetDate.getHours()).padStart(2, '0');
            const min = String(targetDate.getMinutes()).padStart(2, '0');
            const s = String(targetDate.getSeconds()).padStart(2, '0');
            const until = `${y}-${m}-${d} ${h}:${min}:${s}`;

            this.closeSnoozeModal();
            await this.executeSnooze(this.snoozeTargetIds, until);
        },

        async submitCustomSnooze() {
            const date = document.getElementById('snoozeDate')?.value;
            const time = document.getElementById('snoozeTime')?.value;
            if (!date || !time) return;
            const until = `${date} ${time}:00`;
            this.closeSnoozeModal();
            await this.executeSnooze(this.snoozeTargetIds, until);
        },

        async executeSnooze(ids, until) {
            if (!ids || !ids.length) return;
            try {
                const res = await fetch('/api/messages/snooze', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ message_ids: ids, until: until, _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message || 'Snoozed successfully');
                    this.updateLiveFolderCounts();
                    if (this.currentFolder === 'inbox') {
                        ids.forEach(id => {
                            document.querySelector(`.message-row[data-message-id="${id}"]`)?.remove();
                        });
                    } else {
                        setTimeout(() => window.location.reload(), 400);
                    }
                } else {
                    alert(data.message || 'Error snoozing message');
                }
            } catch (e) {
                alert('Network error while snoozing');
            }
        },

        async unsnoozeSingle(messageId) {
            try {
                const res = await fetch('/api/messages/unsnooze', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ message_ids: [messageId], _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast('Restored to Inbox');
                    this.updateLiveFolderCounts();
                    document.querySelector(`.message-row[data-message-id="${messageId}"]`)?.remove();
                }
            } catch (e) {
                console.error(e);
            }
        },

        formatSnoozeDate(dateStr) {
            if (!dateStr) return '';
            const dt = new Date(dateStr.replace(' ', 'T'));
            if (isNaN(dt.getTime())) return dateStr;
            const now = new Date();
            const isToday = dt.toDateString() === now.toDateString();
            const timeStr = dt.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
            if (isToday) {
                return timeStr;
            }
            return `${dt.toLocaleDateString([], { month: 'short', day: 'numeric' })}, ${timeStr}`;
        },

        async pollSnoozeAndCounts() {
            try {
                const res = await fetch('/api/counts');
                const data = await res.json();
                if (data.success && data.counts) {
                    const prevSnoozedTotal = parseInt(document.getElementById('badge-snoozed')?.textContent || '0');
                    const newSnoozedTotal = data.counts.snoozed?.total || 0;

                    this.setBadge('badge-inbox', data.counts.inbox?.unread);
                    this.setBadge('badge-starred', data.counts.starred?.total);
                    this.setBadge('badge-snoozed', data.counts.snoozed?.total);
                    this.setBadge('badge-drafts', data.counts.drafts?.total);
                    this.setBadge('badge-purchases', data.counts.purchases?.unread);
                    this.setBadge('badge-important', data.counts.important?.total);
                    this.setBadge('badge-scheduled', data.counts.scheduled?.total);
                    this.setBadge('badge-spam', data.counts.spam?.unread);

                    // If any snoozed messages expired and woke up to inbox
                    if ((data.restored_snooze_count && data.restored_snooze_count > 0) || (prevSnoozedTotal > newSnoozedTotal)) {
                        this.showToast('⏰ Snoozed email woke up & returned to your Inbox!');
                        if (this.currentFolder === 'inbox' || this.currentFolder === 'snoozed') {
                            this.fetchMessages();
                        }
                    }
                }
            } catch (err) {}
        },

        /* ==========================================================
           Empty Folder (Trash / Spam)
           ========================================================== */
        async confirmEmptyFolder(folder) {
            const name = folder === 'trash' ? 'Trash' : 'Spam';
            if (!confirm(`Are you sure you want to permanently delete all messages in ${name}? This action cannot be undone.`)) {
                return;
            }
            try {
                const res = await fetch('/api/messages/empty-folder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ folder: folder, _csrf_token: this.csrfToken })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message || `Emptied ${name}`);
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    alert(data.message || 'Failed to empty folder');
                }
            } catch (e) {
                alert('Network error emptying folder');
            }
        },

        /* ==========================================================
           Subscriptions & Scheduled Info Modals
           ========================================================== */
        openSubscriptionsModal() {
            const m = document.getElementById('subscriptionsModal');
            if (m) m.style.display = 'flex';
        },

        closeSubscriptionsModal() {
            const m = document.getElementById('subscriptionsModal');
            if (m) m.style.display = 'none';
        },

        openScheduledInfoModal() {
            const m = document.getElementById('scheduledInfoModal');
            if (m) m.style.display = 'flex';
        },

        closeScheduledInfoModal() {
            const m = document.getElementById('scheduledInfoModal');
            if (m) m.style.display = 'none';
        },

        /* ==========================================================
           Sender Contact Profile Hovercard
           ========================================================== */
        hovercardTimer: null,
        hovercardHideTimer: null,
        currentHoverTarget: null,
        activeContactEmail: '',

        bindSenderHovercard() {
            const card = document.getElementById('senderHovercard');
            if (!card) return;

            // When hovering over sender element in message rows or reading view
            document.addEventListener('mouseover', (e) => {
                const target = e.target.closest('.cell-sender, .sender-identity');
                if (target) {
                    clearTimeout(this.hovercardHideTimer);
                    if (this.currentHoverTarget === target && card.style.display !== 'none') {
                        return;
                    }
                    clearTimeout(this.hovercardTimer);
                    this.hovercardTimer = setTimeout(() => {
                        this.showSenderHovercard(target);
                    }, 220);
                }
            });

            document.addEventListener('mouseout', (e) => {
                const target = e.target.closest('.cell-sender, .sender-identity');
                if (target) {
                    clearTimeout(this.hovercardTimer);
                    this.hovercardHideTimer = setTimeout(() => {
                        this.hideSenderHovercard();
                    }, 280);
                }
            });

            // Prevent closing while mouse is over the hovercard itself
            card.addEventListener('mouseenter', () => {
                clearTimeout(this.hovercardHideTimer);
            });

            card.addEventListener('mouseleave', () => {
                this.hovercardHideTimer = setTimeout(() => {
                    this.hideSenderHovercard();
                }, 280);
            });

            // Action: Send Mail
            document.getElementById('hovercardSendMail')?.addEventListener('click', () => {
                const email = this.activeContactEmail;
                this.hideSenderHovercard();
                this.openCompose({ to: email });
            });

            // Action: Add Contact
            document.getElementById('hovercardAddContact')?.addEventListener('click', () => {
                const name = document.getElementById('hovercardName')?.textContent || '';
                this.showToast(`Contact "${name}" saved to contacts!`, 'success');
            });

            // Action: Chat
            document.getElementById('hovercardChat')?.addEventListener('click', () => {
                this.showToast(`Starting chat with ${this.activeContactEmail}...`, 'info');
            });

            // Action: Video Meeting
            document.getElementById('hovercardMeet')?.addEventListener('click', () => {
                this.showToast(`Starting video meeting with ${this.activeContactEmail}...`, 'info');
            });

            // Action: Schedule Event
            document.getElementById('hovercardSchedule')?.addEventListener('click', () => {
                const name = this.activeContactName || 'Candidate';
                const email = this.activeContactEmail || '';
                this.hideSenderHovercard();
                this.calendar.open({
                    title: `Discussion / Interview with ${name}`,
                    description: `Participant: ${name} <${email}>`,
                    location: 'Google Meet',
                    category: 'interview'
                });
            });
        },

        showSenderHovercard(el) {
            const card = document.getElementById('senderHovercard');
            if (!card || !el) return;

            let name = el.dataset.name || el.querySelector('.sender-text, .sender-name-full')?.textContent || el.textContent || 'Unknown';
            let email = el.dataset.email || el.getAttribute('title') || '';
            
            // Clean display strings
            name = name.replace(/^To:\s*/i, '').trim();
            if (!email || !email.includes('@')) {
                email = name.includes('@') ? name : `${name.toLowerCase().replace(/\s+/g, '.')}@example.com`;
            }

            this.activeContactName = name;
            this.activeContactEmail = email;
            this.currentHoverTarget = el;

            const nameEl = document.getElementById('hovercardName');
            const emailEl = document.getElementById('hovercardEmail');
            const avatarEl = document.getElementById('hovercardAvatar');

            if (nameEl) nameEl.textContent = name;
            if (emailEl) emailEl.textContent = email;

            if (avatarEl) {
                avatarEl.innerHTML = `
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="#0b57d0">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                `;
            }

            // Calculate position
            const rect = el.getBoundingClientRect();
            const cardWidth = 320;
            const cardHeight = 135;

            let left = rect.left;
            let top = rect.bottom + 6;

            // Ensure card fits inside viewport
            if (left + cardWidth > window.innerWidth - 12) {
                left = window.innerWidth - cardWidth - 16;
            }
            if (left < 12) {
                left = 12;
            }

            if (top + cardHeight > window.innerHeight - 12) {
                top = rect.top - cardHeight - 6;
            }
            if (top < 12) {
                top = 12;
            }

            card.style.left = `${Math.round(left)}px`;
            card.style.top = `${Math.round(top)}px`;
            card.style.display = 'flex';
        },

        hideSenderHovercard() {
            const card = document.getElementById('senderHovercard');
            if (card) {
                card.style.display = 'none';
            }
            this.currentHoverTarget = null;
        },

        /* ==========================================================
           Google-Style Calendar & Work Schedule Side Panel
           ========================================================== */
        calendar: {
            isOpen: false,
            currentYear: new Date().getFullYear(),
            currentMonth: new Date().getMonth() + 1, // 1 - 12
            selectedDate: null,
            eventsCache: [],

            init() {
                const today = new Date();
                const y = today.getFullYear();
                const m = String(today.getMonth() + 1).padStart(2, '0');
                const d = String(today.getDate()).padStart(2, '0');
                this.selectedDate = `${y}-${m}-${d}`;
            },

            toggle() {
                if (this.isOpen) {
                    this.close();
                } else {
                    this.open();
                }
            },

            async open(prefill = null) {
                this.isOpen = true;
                const panel = document.getElementById('calendarSidepanel');
                const railBtn = document.getElementById('railCalendarBtn');
                const topbarBtn = document.getElementById('topbarCalendarBtn');
                const sidebarBtn = document.getElementById('sidebarCalendarBtn');
                const calBackdrop = document.getElementById('calendarBackdrop');

                // If mobile drawer was open, close it
                const sidebar = document.getElementById('appSidebar');
                const sidebarBackdrop = document.getElementById('sidebarBackdrop');
                if (sidebar) sidebar.classList.remove('open');
                if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');

                if (panel) panel.style.display = 'flex';
                if (calBackdrop) calBackdrop.classList.add('show');
                if (railBtn) railBtn.classList.add('active');
                if (topbarBtn) topbarBtn.classList.add('active');
                if (sidebarBtn) sidebarBtn.classList.add('active');

                if (!this.selectedDate) this.init();

                await this.loadMonthEvents();

                if (prefill) {
                    this.showCreateForm(prefill);
                }
            },

            close() {
                this.isOpen = false;
                const panel = document.getElementById('calendarSidepanel');
                const railBtn = document.getElementById('railCalendarBtn');
                const topbarBtn = document.getElementById('topbarCalendarBtn');
                const sidebarBtn = document.getElementById('sidebarCalendarBtn');
                const calBackdrop = document.getElementById('calendarBackdrop');

                if (panel) panel.style.display = 'none';
                if (calBackdrop) calBackdrop.classList.remove('show');
                if (railBtn) railBtn.classList.remove('active');
                if (topbarBtn) topbarBtn.classList.remove('active');
                if (sidebarBtn) sidebarBtn.classList.remove('active');
                this.hideCreateForm();
            },

            async refresh() {
                await this.loadMonthEvents();
                window.MailApp.showToast('Calendar updated');
            },

            prevMonth() {
                this.currentMonth--;
                if (this.currentMonth < 1) {
                    this.currentMonth = 12;
                    this.currentYear--;
                }
                this.loadMonthEvents();
            },

            nextMonth() {
                this.currentMonth++;
                if (this.currentMonth > 12) {
                    this.currentMonth = 1;
                    this.currentYear++;
                }
                this.loadMonthEvents();
            },

            async loadMonthEvents() {
                try {
                    const res = await fetch(`/api/calendar/events?year=${this.currentYear}&month=${this.currentMonth}`);
                    const data = await res.json();
                    if (data.success && data.events) {
                        this.eventsCache = data.events;
                        this.renderMiniCalendar();
                        const agendaList = (data.upcoming && data.upcoming.length) ? data.upcoming : this.eventsCache;
                        this.renderAgenda(agendaList);
                    }
                } catch (e) {
                    console.error("Calendar fetch error:", e);
                }
            },

            async loadUpcoming() {
                try {
                    const res = await fetch(`/api/calendar/upcoming?limit=40`);
                    const data = await res.json();
                    if (data.success && data.events) {
                        this.eventsCache = data.events;
                        this.renderAgenda(this.eventsCache, true);
                    }
                } catch (e) {
                    console.error(e);
                }
            },

            async selectDate(dateStr) {
                this.selectedDate = dateStr;
                const dt = new Date(dateStr + 'T00:00:00');
                const headerDateEl = document.getElementById('calSelectedDateHeader');
                if (headerDateEl) {
                    headerDateEl.textContent = dt.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) + ' -';
                }

                // Update input in create form if open
                const formDateInput = document.getElementById('calEventDate');
                if (formDateInput) formDateInput.value = dateStr;

                this.renderMiniCalendar();

                try {
                    const res = await fetch(`/api/calendar/upcoming?limit=40&from=${dateStr}`);
                    const data = await res.json();
                    if (data.success && data.events && data.events.length) {
                        this.renderAgenda(data.events);
                    } else {
                        const matchingEvents = this.eventsCache.filter(e => e.event_date === dateStr);
                        this.renderAgenda(matchingEvents.length > 0 ? matchingEvents : this.eventsCache);
                    }
                } catch (e) {
                    const matchingEvents = this.eventsCache.filter(e => e.event_date === dateStr);
                    this.renderAgenda(matchingEvents.length > 0 ? matchingEvents : this.eventsCache);
                }
            },

            renderMiniCalendar() {
                const titleEl = document.getElementById('calMonthYearTitle');
                const gridEl = document.getElementById('calDaysGrid');
                if (!gridEl) return;

                const monthDate = new Date(this.currentYear, this.currentMonth - 1, 1);
                if (titleEl) {
                    titleEl.textContent = monthDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                }

                const firstDayIndex = monthDate.getDay(); // 0 is Sun
                const totalDays = new Date(this.currentYear, this.currentMonth, 0).getDate();
                const prevMonthDays = new Date(this.currentYear, this.currentMonth - 1, 0).getDate();

                const todayObj = new Date();
                const todayStr = `${todayObj.getFullYear()}-${String(todayObj.getMonth() + 1).padStart(2, '0')}-${String(todayObj.getDate()).padStart(2, '0')}`;

                let html = '';

                // Previous month padding days
                for (let i = firstDayIndex - 1; i >= 0; i--) {
                    const dayNum = prevMonthDays - i;
                    html += `<div class="cal-day-cell other-month">${dayNum}</div>`;
                }

                // Current month days
                for (let d = 1; d <= totalDays; d++) {
                    const dateStr = `${this.currentYear}-${String(this.currentMonth).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    const isToday = (dateStr === todayStr);
                    const isSelected = (dateStr === this.selectedDate);
                    const hasEvents = this.eventsCache.some(e => e.event_date === dateStr);

                    let classes = ['cal-day-cell'];
                    if (isToday) classes.push('today');
                    if (isSelected) classes.push('selected');
                    if (hasEvents) classes.push('has-events');

                    html += `<div class="${classes.join(' ')}" onclick="window.MailApp.calendar.selectDate('${dateStr}')">${d}</div>`;
                }

                // Next month padding days to complete grid
                const totalCells = firstDayIndex + totalDays;
                const nextDays = (totalCells % 7 === 0) ? 0 : 7 - (totalCells % 7);
                for (let n = 1; n <= nextDays; n++) {
                    html += `<div class="cal-day-cell other-month">${n}</div>`;
                }

                gridEl.innerHTML = html;
            },

            renderAgenda(eventsToRender = null, isAll = false) {
                const listEl = document.getElementById('calAgendaList');
                if (!listEl) return;

                const events = eventsToRender || this.eventsCache;

                if (!events || !events.length) {
                    listEl.innerHTML = `
                        <div style="padding: 24px 0; text-align: center; color: #94a3b8; font-size: 13px;">
                            No events scheduled.
                            <div style="margin-top: 8px;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailApp.calendar.showCreateForm()">+ Add Work / Interview</button>
                            </div>
                        </div>
                    `;
                    return;
                }

                // Group events by date
                const groups = {};
                events.forEach(e => {
                    const dt = e.event_date;
                    if (!groups[dt]) groups[dt] = [];
                    groups[dt].push(e);
                });

                let html = '';
                Object.keys(groups).sort().forEach(dateStr => {
                    const dtObj = new Date(dateStr + 'T00:00:00');
                    const headerText = dtObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });

                    html += `<div class="cal-group-date-header">${headerText}</div>`;

                    groups[dateStr].forEach(ev => {
                        const bg = ev.color || '#15803d';
                        let timeStr = '';
                        if (ev.is_all_day == 1) {
                            timeStr = 'All day';
                        } else if (ev.start_time) {
                            const timeParts = ev.start_time.split(':');
                            const h = parseInt(timeParts[0]);
                            const m = timeParts[1];
                            const ampm = h >= 12 ? 'PM' : 'AM';
                            const h12 = h % 12 || 12;
                            timeStr = `${h12}:${m} ${ampm}`;
                        }

                        html += `
                            <div class="cal-event-pill" style="background-color: ${bg};" title="${window.MailApp.escapeHtml(ev.description || ev.title)}">
                                <div class="cal-event-pill-content">
                                    <span>${window.MailApp.escapeHtml(ev.title)}</span>
                                </div>
                                ${timeStr ? `<span class="cal-event-pill-time">${timeStr}</span>` : ''}
                                <button type="button" class="cal-event-del-btn" title="Delete Event" onclick="event.stopPropagation(); window.MailApp.calendar.deleteEvent(${ev.id});">&times;</button>
                            </div>
                        `;
                    });
                });

                listEl.innerHTML = html;
            },

            showCreateForm(prefill = null) {
                const card = document.getElementById('calCreateCard');
                if (!card) return;

                card.style.display = 'block';
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                if (prefill) {
                    const titleInput = document.getElementById('calEventTitle');
                    const dateInput = document.getElementById('calEventDate');
                    const catInput = document.getElementById('calEventCategory');
                    const descInput = document.getElementById('calEventDesc');
                    const locInput = document.getElementById('calEventLocation');
                    const msgIdInput = document.getElementById('calEventMessageId');

                    if (titleInput) titleInput.value = prefill.title || '';
                    if (dateInput) dateInput.value = prefill.date || this.selectedDate || new Date().toISOString().split('T')[0];
                    if (catInput) catInput.value = prefill.category || 'interview';
                    if (descInput) descInput.value = prefill.description || '';
                    if (locInput) locInput.value = prefill.location || 'Google Meet';
                    if (msgIdInput) msgIdInput.value = prefill.message_id || '';
                } else {
                    document.getElementById('calEventForm')?.reset();
                    const dateInput = document.getElementById('calEventDate');
                    if (dateInput) dateInput.value = this.selectedDate || new Date().toISOString().split('T')[0];
                    const msgIdInput = document.getElementById('calEventMessageId');
                    if (msgIdInput) msgIdInput.value = '';
                }
            },

            hideCreateForm() {
                const card = document.getElementById('calCreateCard');
                if (card) card.style.display = 'none';
            },

            async submitEvent() {
                const title = document.getElementById('calEventTitle')?.value.trim();
                const event_date = document.getElementById('calEventDate')?.value;
                const category = document.getElementById('calEventCategory')?.value || 'interview';
                const start_time = document.getElementById('calEventStartTime')?.value || null;
                const end_time = document.getElementById('calEventEndTime')?.value || null;
                const location = document.getElementById('calEventLocation')?.value.trim() || null;
                const description = document.getElementById('calEventDesc')?.value.trim() || null;
                const message_id = document.getElementById('calEventMessageId')?.value || null;

                if (!title || !event_date) return;

                const btnSave = document.getElementById('calSaveEventBtn');
                if (btnSave) {
                    btnSave.disabled = true;
                    btnSave.textContent = 'Scheduling...';
                }

                try {
                    const res = await fetch('/api/calendar/events', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.MailApp.csrfToken
                        },
                        body: JSON.stringify({
                            title,
                            event_date,
                            category,
                            start_time,
                            end_time,
                            location,
                            description,
                            message_id,
                            _csrf_token: window.MailApp.csrfToken
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        window.MailApp.showToast('📅 Scheduled successfully on Calendar!');
                        this.hideCreateForm();
                        this.selectedDate = event_date;
                        await this.loadMonthEvents();
                    } else {
                        alert(data.message || 'Error creating event');
                    }
                } catch (e) {
                    alert('Network error while scheduling event');
                } finally {
                    if (btnSave) {
                        btnSave.disabled = false;
                        btnSave.textContent = 'Save Event';
                    }
                }
            },

            async deleteEvent(id) {
                if (!confirm('Are you sure you want to delete this event from your calendar?')) return;
                try {
                    const res = await fetch('/api/calendar/events/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.MailApp.csrfToken
                        },
                        body: JSON.stringify({ id, _csrf_token: window.MailApp.csrfToken })
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.MailApp.showToast('Event removed from calendar');
                        await this.loadMonthEvents();
                    }
                } catch (e) {
                    console.error(e);
                }
            },

            scheduleFromActiveMessage() {
                const msg = window.MailApp.currentActiveMessage;
                if (!msg) {
                    this.open();
                    return;
                }

                const today = new Date();
                let targetDate = new Date();
                if (today.getHours() >= 15) {
                    targetDate.setDate(today.getDate() + 1);
                }
                const dateStr = targetDate.toISOString().split('T')[0];

                const cleanSubj = (msg.subject || 'Interview Discussion').replace(/^(Re:\s*)+/i, '');
                const prefill = {
                    title: `Interview: ${cleanSubj}`,
                    date: dateStr,
                    category: 'interview',
                    location: 'Google Meet',
                    description: `Candidate: ${msg.sender_name || ''} <${msg.sender_email || ''}>\nNotes: ${msg.snippet || ''}`,
                    message_id: msg.id || msg.message_id
                };

                this.open(prefill);
            }
        },

        /* ==========================================================
           Utilities
           ========================================================== */
        showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<span>${this.escapeHtml(message)}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        },

        escapeHtml(str) {
            const p = document.createElement('p');
            p.textContent = str;
            return p.innerHTML;
        }
    };

    window.MailApp = MailApp;
    document.addEventListener('DOMContentLoaded', () => MailApp.init());
})();
