/**
 * Novalyx Cloud Webmail - Settings Management Module
 */
(function() {
    'use strict';

    // Store initial settings state for dirty tracking / discard
    let initialSettings = {};
    let isDirty = false;

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showStatus(state, message) {
        const statusEl = document.getElementById('settingsSaveStatus');
        const discardBtn = document.getElementById('btnDiscardSettings');
        if (!statusEl) return;

        statusEl.className = 'settings-save-status ' + state;
        const textEl = statusEl.querySelector('.status-indicator-text');
        if (textEl) {
            textEl.textContent = message || (state === 'saving' ? 'Saving changes...' : (state === 'dirty' ? 'Unsaved changes' : 'All changes saved'));
        }

        if (discardBtn) {
            discardBtn.style.display = (state === 'dirty' || isDirty) ? 'inline-flex' : 'none';
        }
    }

    function markDirty() {
        isDirty = true;
        showStatus('dirty', 'Unsaved changes');
    }

    function initSettingsState() {
        const userSettings = window.CURRENT_USER_SETTINGS || {};
        initialSettings = {
            theme: userSettings.theme || 'system',
            density: userSettings.density || 'comfortable',
            default_folder: userSettings.default_folder || 'inbox',
            default_category: userSettings.default_category || 'primary',
            show_category_tabs: !!userSettings.show_category_tabs,
            unread_badge_mode: userSettings.unread_badge_mode || 'unread',
            in_app_notifications: !!userSettings.in_app_notifications,
            browser_notifications: !!userSettings.browser_notifications,
            signature: userSettings.signature || '',
            append_signature: !!userSettings.append_signature,
            default_reply_behavior: userSettings.default_reply_behavior || 'reply',
            confirm_discard: !!userSettings.confirm_discard,
            auto_save_drafts: !!userSettings.auto_save_drafts
        };
    }

    // Tab Navigation & Quick Pill Switcher
    function switchToTab(targetId) {
        if (!targetId) return;
        const navBtns = document.querySelectorAll('.settings-nav-btn');
        const panels = document.querySelectorAll('.settings-panel');
        const targetBtn = document.querySelector(`.settings-nav-btn[data-target="${targetId}"]`);
        const targetPanel = document.getElementById(targetId);

        if (targetBtn && targetPanel) {
            navBtns.forEach(b => b.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            targetBtn.classList.add('active');
            targetPanel.classList.add('active');

            if (window.innerWidth <= 768) {
                targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    function initTabs() {
        const navBtns = document.querySelectorAll('.settings-nav-btn');

        navBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                switchToTab(targetId);
            });
        });

        document.querySelectorAll('.quick-pill').forEach(pill => {
            pill.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = pill.getAttribute('data-target');
                switchToTab(targetId);
            });
        });
    }

    // Real-time Theme & Density Previews
    function previewTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.body.setAttribute('data-theme', theme);

        document.querySelectorAll('.theme-card').forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.value === theme) {
                card.classList.add('selected');
                radio.checked = true;
            } else {
                card.classList.remove('selected');
            }
        });
        markDirty();
    }

    function previewDensity(density) {
        document.documentElement.setAttribute('data-density', density);
        document.body.setAttribute('data-density', density);

        document.querySelectorAll('.density-card').forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.value === density) {
                card.classList.add('selected');
                radio.checked = true;
            } else {
                card.classList.remove('selected');
            }
        });
        markDirty();
    }

    // Save User Preferences
    async function savePreferences() {
        showStatus('saving', 'Saving changes...');

        const themeRadio = document.querySelector('input[name="setting_theme"]:checked');
        const densityRadio = document.querySelector('input[name="setting_density"]:checked');
        const replyRadio = document.querySelector('input[name="setting_reply_behavior"]:checked');

        const payload = {
            theme: themeRadio ? themeRadio.value : 'system',
            density: densityRadio ? densityRadio.value : 'comfortable',
            default_folder: document.getElementById('settingDefaultFolder') ? document.getElementById('settingDefaultFolder').value : 'inbox',
            default_category: document.getElementById('settingDefaultCategory') ? document.getElementById('settingDefaultCategory').value : 'primary',
            show_category_tabs: document.getElementById('settingShowCategoryTabs') ? (document.getElementById('settingShowCategoryTabs').checked ? 1 : 0) : 1,
            unread_badge_mode: document.getElementById('settingUnreadBadgeMode') ? document.getElementById('settingUnreadBadgeMode').value : 'unread',
            in_app_notifications: document.getElementById('settingInAppNotifications') ? (document.getElementById('settingInAppNotifications').checked ? 1 : 0) : 1,
            browser_notifications: document.getElementById('settingBrowserNotifications') ? (document.getElementById('settingBrowserNotifications').checked ? 1 : 0) : 0,
            signature: document.getElementById('settingSignatureText') ? document.getElementById('settingSignatureText').value : '',
            append_signature: document.getElementById('settingAppendSignature') ? (document.getElementById('settingAppendSignature').checked ? 1 : 0) : 1,
            default_reply_behavior: replyRadio ? replyRadio.value : 'reply',
            confirm_discard: document.getElementById('settingConfirmDiscard') ? (document.getElementById('settingConfirmDiscard').checked ? 1 : 0) : 1,
            auto_save_drafts: document.getElementById('settingAutoSaveDrafts') ? (document.getElementById('settingAutoSaveDrafts').checked ? 1 : 0) : 1,
            _csrf_token: getCsrfToken()
        };

        try {
            const res = await fetch('/api/settings/preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                isDirty = false;
                window.CURRENT_USER_SETTINGS = Object.assign({}, window.CURRENT_USER_SETTINGS, payload);
                initialSettings = Object.assign({}, payload);
                showStatus('saved', 'All changes saved');
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast('Settings saved successfully', 'success');
                }
            } else {
                showStatus('error', data.error || 'Failed to save settings');
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast(data.error || 'Failed to save settings', 'error');
                }
            }
        } catch (err) {
            console.error('Save error:', err);
            showStatus('error', 'Network error saving settings');
            if (window.MailApp && window.MailApp.showToast) {
                window.MailApp.showToast('Network error while saving settings', 'error');
            }
        }
    }

    // Reset / Discard Edits
    function discardChanges() {
        if (!initialSettings) return;

        previewTheme(initialSettings.theme || 'system');
        previewDensity(initialSettings.density || 'comfortable');

        if (document.getElementById('settingDefaultFolder')) document.getElementById('settingDefaultFolder').value = initialSettings.default_folder || 'inbox';
        if (document.getElementById('settingDefaultCategory')) document.getElementById('settingDefaultCategory').value = initialSettings.default_category || 'primary';
        if (document.getElementById('settingShowCategoryTabs')) document.getElementById('settingShowCategoryTabs').checked = !!initialSettings.show_category_tabs;
        if (document.getElementById('settingUnreadBadgeMode')) document.getElementById('settingUnreadBadgeMode').value = initialSettings.unread_badge_mode || 'unread';
        if (document.getElementById('settingInAppNotifications')) document.getElementById('settingInAppNotifications').checked = !!initialSettings.in_app_notifications;
        if (document.getElementById('settingBrowserNotifications')) document.getElementById('settingBrowserNotifications').checked = !!initialSettings.browser_notifications;
        if (document.getElementById('settingSignatureText')) {
            document.getElementById('settingSignatureText').value = initialSettings.signature || '';
            updateCharCount();
        }
        if (document.getElementById('settingAppendSignature')) document.getElementById('settingAppendSignature').checked = !!initialSettings.append_signature;
        
        const replyRadios = document.querySelectorAll('input[name="setting_reply_behavior"]');
        replyRadios.forEach(r => {
            r.checked = (r.value === (initialSettings.default_reply_behavior || 'reply'));
        });

        if (document.getElementById('settingConfirmDiscard')) document.getElementById('settingConfirmDiscard').checked = !!initialSettings.confirm_discard;
        if (document.getElementById('settingAutoSaveDrafts')) document.getElementById('settingAutoSaveDrafts').checked = !!initialSettings.auto_save_drafts;

        isDirty = false;
        showStatus('saved', 'Changes reverted');
    }

    // Update Profile Information (Display Name)
    async function updateProfile() {
        const btn = document.getElementById('btnSaveProfile');
        const nameInput = document.getElementById('settingDisplayName');
        if (!nameInput) return;

        const displayName = nameInput.value.trim();
        if (!displayName) {
            alert('Please enter a display name');
            return;
        }

        if (btn) btn.disabled = true;

        try {
            const res = await fetch('/api/settings/profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    display_name: displayName,
                    _csrf_token: getCsrfToken()
                })
            });

            const data = await res.json();
            if (data.success) {
                // Update topbar names
                const profileNames = document.querySelectorAll('.profile-name');
                profileNames.forEach(el => el.textContent = displayName);

                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast('Profile updated successfully', 'success');
                }
            } else {
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast(data.error || 'Failed to update profile', 'error');
                }
            }
        } catch (err) {
            console.error(err);
            if (window.MailApp && window.MailApp.showToast) {
                window.MailApp.showToast('Network error updating profile', 'error');
            }
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Upload Avatar
    async function uploadAvatar(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        // Client-side quick validation
        if (file.size > 2 * 1024 * 1024) {
            alert('File exceeds 2MB limit. Please select a smaller image.');
            input.value = '';
            return;
        }

        const statusEl = document.getElementById('avatarUploadStatus');
        if (statusEl) {
            statusEl.textContent = 'Uploading...';
            statusEl.className = 'upload-status-text text-muted';
        }

        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_csrf_token', getCsrfToken());

        try {
            const res = await fetch('/api/settings/avatar', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await res.json();
            if (data.success && data.avatar_url) {
                if (statusEl) {
                    statusEl.textContent = 'Photo uploaded successfully';
                    statusEl.className = 'upload-status-text text-success';
                }

                // Update settings avatar preview
                const previewImg = document.getElementById('settingsAvatarPreview');
                const placeholder = document.getElementById('settingsAvatarPlaceholder');
                if (previewImg) {
                    previewImg.src = data.avatar_url + '?t=' + Date.now();
                    previewImg.style.display = 'block';
                }
                if (placeholder) {
                    placeholder.style.display = 'none';
                }

                // Update topbar avatars
                const topbarAvatarBtn = document.getElementById('userMenuToggle');
                if (topbarAvatarBtn) {
                    topbarAvatarBtn.innerHTML = `<img src="${data.avatar_url}?t=${Date.now()}" alt="Avatar" class="user-avatar-img">`;
                }
                const dropdownAvatarLg = document.querySelector('.dropdown-menu .user-avatar-lg, .dropdown-menu .user-avatar-img-lg');
                if (dropdownAvatarLg) {
                    dropdownAvatarLg.outerHTML = `<img src="${data.avatar_url}?t=${Date.now()}" alt="Avatar" class="user-avatar-img-lg">`;
                }

                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast('Profile photo updated', 'success');
                }
            } else {
                if (statusEl) {
                    statusEl.textContent = data.error || 'Failed to upload photo';
                    statusEl.className = 'upload-status-text text-danger';
                }
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast(data.error || 'Upload failed', 'error');
                }
            }
        } catch (err) {
            console.error(err);
            if (statusEl) {
                statusEl.textContent = 'Network error during upload';
                statusEl.className = 'upload-status-text text-danger';
            }
        } finally {
            input.value = '';
        }
    }

    // Change Password
    async function changePassword() {
        const currentPass = document.getElementById('currentPassword').value;
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        const btn = document.getElementById('btnUpdatePassword');

        if (!currentPass || !newPass || !confirmPass) {
            alert('Please fill in all password fields');
            return;
        }

        if (newPass.length < 8) {
            alert('New password must be at least 8 characters long');
            return;
        }

        if (newPass !== confirmPass) {
            alert('New passwords do not match');
            return;
        }

        if (btn) btn.disabled = true;

        try {
            const res = await fetch('/api/settings/password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    current_password: currentPass,
                    new_password: newPass,
                    confirm_password: confirmPass,
                    _csrf_token: getCsrfToken()
                })
            });

            const data = await res.json();
            if (data.success) {
                document.getElementById('passwordForm').reset();
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast('Password updated securely', 'success');
                }
            } else {
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast(data.error || 'Failed to change password', 'error');
                }
            }
        } catch (err) {
            console.error(err);
            if (window.MailApp && window.MailApp.showToast) {
                window.MailApp.showToast('Network error changing password', 'error');
            }
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Toggle Password Visibility Eye Icon
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = `<svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
        } else {
            input.type = 'password';
            btn.innerHTML = `<svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        }
    }

    // Blocked Senders Management
    function renderBlockedList(list) {
        const container = document.getElementById('blockedSendersList');
        if (!container) return;

        if (!list || list.length === 0) {
            container.innerHTML = '<li class="empty-list-note" id="emptyBlockedNote">No blocked senders yet.</li>';
            return;
        }

        container.innerHTML = list.map(item => `
            <li class="blocked-sender-item" data-id="${item.id}">
                <span class="blocked-email-text">${escapeHtml(item.email)}</span>
                <span class="blocked-date">Blocked on ${escapeHtml(item.created_at ? item.created_at.substring(0, 10) : 'recent')}</span>
                <button type="button" class="btn-icon btn-sm text-danger" onclick="window.MailSettings.removeBlockedSender(${item.id})" title="Unblock address" aria-label="Unblock address">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </li>
        `).join('');
    }

    async function addBlockedSender() {
        const input = document.getElementById('blockedSenderInput');
        if (!input) return;

        const email = input.value.trim();
        if (!email) return;

        try {
            const res = await fetch('/api/settings/blocked-senders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    email: email,
                    _csrf_token: getCsrfToken()
                })
            });

            const data = await res.json();
            if (data.success && data.blocked) {
                input.value = '';
                renderBlockedList(data.blocked);

                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast(`Blocked ${email}`, 'success');
                }
            } else {
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast(data.error || 'Failed to block sender', 'error');
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function removeBlockedSender(id) {
        if (!confirm('Unblock this sender address?')) return;

        try {
            const res = await fetch('/api/settings/blocked-senders/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    id: id,
                    _csrf_token: getCsrfToken()
                })
            });

            const data = await res.json();
            if (data.success && data.blocked) {
                renderBlockedList(data.blocked);

                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast('Sender unblocked', 'success');
                }
            } else {
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast(data.error || 'Failed to remove blocked sender', 'error');
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Delete Label from settings view
    async function deleteLabel(id) {
        if (!confirm('Are you sure you want to delete this label? Emails with this label will not be deleted.')) return;

        try {
            const res = await fetch(`/api/labels/${id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    _csrf_token: getCsrfToken()
                })
            });

            const data = await res.json();
            if (data.success) {
                const row = document.querySelector(`tr[data-label-id="${id}"]`);
                if (row) row.remove();

                const sidebarLabel = document.querySelector(`.label-item[data-label-id="${id}"]`);
                if (sidebarLabel) sidebarLabel.remove();

                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast('Label deleted', 'success');
                }
            } else {
                if (window.MailApp && window.MailApp.showToast) {
                    window.MailApp.showToast(data.error || 'Failed to delete label', 'error');
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Account Deletion Flow
    function openDeleteAccountModal() {
        const modal = document.getElementById('deleteAccountModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeDeleteAccountModal() {
        const modal = document.getElementById('deleteAccountModal');
        if (modal) modal.style.display = 'none';
    }

    async function confirmDeleteAccount() {
        const password = document.getElementById('deleteConfirmPassword').value;
        const phrase = document.getElementById('deleteConfirmPhrase').value;
        const btn = document.getElementById('btnConfirmDeleteAccount');

        if (phrase !== 'DELETE') {
            alert('You must type DELETE in all caps to confirm account deletion.');
            return;
        }

        if (!password) {
            alert('Please enter your password.');
            return;
        }

        if (btn) btn.disabled = true;

        try {
            const res = await fetch('/api/settings/delete-account', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    password: password,
                    confirm_phrase: phrase,
                    _csrf_token: getCsrfToken()
                })
            });

            const data = await res.json();
            if (data.success) {
                alert('Your account has been deleted. Redirecting to login.');
                window.location.href = data.redirect || '/login';
            } else {
                alert(data.error || 'Failed to delete account');
            }
        } catch (err) {
            console.error(err);
            alert('Network error while deleting account');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Browser Notification Permission Handler
    function handleBrowserNotificationToggle(checkbox) {
        if (checkbox.checked) {
            if (!("Notification" in window)) {
                alert("This browser does not support desktop notifications.");
                checkbox.checked = false;
                return;
            }

            if (Notification.permission === "default") {
                Notification.requestPermission().then(permission => {
                    if (permission !== "granted") {
                        checkbox.checked = false;
                        if (window.MailApp && window.MailApp.showToast) {
                            window.MailApp.showToast('Browser notification permission was denied', 'warning');
                        }
                    } else {
                        markDirty();
                    }
                });
            } else if (Notification.permission === "denied") {
                alert("Browser notifications are blocked in your browser settings. Please allow notifications for this site in your browser bar.");
                checkbox.checked = false;
            } else {
                markDirty();
            }
        } else {
            markDirty();
        }
    }

    function testBrowserNotification() {
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notifications.");
            return;
        }

        if (Notification.permission === "granted") {
            new Notification("Novalyx Cloud Mail", {
                body: "Test notification: Browser alerts are working properly!",
                icon: "/assets/images/logo.svg"
            });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    new Notification("Novalyx Cloud Mail", {
                        body: "Test notification: Browser alerts are working properly!",
                        icon: "/assets/images/logo.svg"
                    });
                }
            });
        } else {
            alert("Notification permission is currently blocked in your browser settings.");
        }
    }

    function updateCharCount() {
        const textarea = document.getElementById('settingSignatureText');
        const counter = document.getElementById('signatureCharCount');
        if (textarea && counter) {
            counter.textContent = textarea.value.length;
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Attach Change Listeners for Dirty Tracking
    function attachDirtyListeners() {
        const inputs = document.querySelectorAll('#panel-inbox select, #panel-inbox input, #panel-notifications input, #panel-signature textarea, #panel-signature input, #panel-compose input');
        inputs.forEach(input => {
            input.addEventListener('change', markDirty);
            if (input.tagName === 'TEXTAREA') {
                input.addEventListener('input', () => {
                    updateCharCount();
                    markDirty();
                });
            }
        });

        const saveBtn = document.getElementById('btnSaveSettings');
        if (saveBtn) {
            saveBtn.addEventListener('click', saveAllGeneral);
        }

        const discardBtn = document.getElementById('btnDiscardSettings');
        if (discardBtn) {
            discardBtn.addEventListener('click', discardChanges);
        }
    }

    // Bidirectional Synchronization between General overview and dedicated tab fields
    function syncAllFromGeneral() {
        const genDisplayName = document.getElementById('genSettingDisplayName');
        const mainDisplayName = document.getElementById('settingDisplayName');
        if (genDisplayName && mainDisplayName) {
            mainDisplayName.value = genDisplayName.value;
        }

        const genTheme = document.getElementById('genSettingThemeSelect');
        const mainTheme = document.getElementById('settingThemeSelect');
        if (genTheme && mainTheme) {
            mainTheme.value = genTheme.value;
            const themeRadio = document.querySelector(`input[name="setting_theme"][value="${genTheme.value}"]`);
            if (themeRadio) themeRadio.checked = true;
        }

        const genDensity = document.getElementById('genSettingDensitySelect');
        const mainDensity = document.getElementById('settingDensitySelect');
        if (genDensity && mainDensity) {
            mainDensity.value = genDensity.value;
            const densityRadio = document.querySelector(`input[name="setting_density"][value="${genDensity.value}"]`);
            if (densityRadio) densityRadio.checked = true;
        }

        const syncFields = [
            ['genSettingDefaultFolder', 'settingDefaultFolder'],
            ['genSettingDefaultCategory', 'settingDefaultCategory'],
            ['genSettingUnreadBadgeMode', 'settingUnreadBadgeMode'],
            ['genSettingSignatureText', 'settingSignatureText'],
            ['genSettingReplyBehaviorSelect', 'settingReplyBehaviorSelect']
        ];
        syncFields.forEach(([genId, mainId]) => {
            const genEl = document.getElementById(genId);
            const mainEl = document.getElementById(mainId);
            if (genEl && mainEl) {
                mainEl.value = genEl.value;
            }
        });

        const syncChecks = [
            ['genSettingShowCategoryTabs', 'settingShowCategoryTabs'],
            ['genSettingAppendSignature', 'settingAppendSignature'],
            ['genSettingConfirmDiscard', 'settingConfirmDiscard'],
            ['genSettingAutoSaveDrafts', 'settingAutoSaveDrafts'],
            ['genSettingInAppNotifications', 'settingInAppNotifications']
        ];
        syncChecks.forEach(([genId, mainId]) => {
            const genEl = document.getElementById(genId);
            const mainEl = document.getElementById(mainId);
            if (genEl && mainEl) {
                mainEl.checked = genEl.checked;
            }
        });
    }

    function attachGeneralSyncListeners() {
        const textPairs = [
            ['genSettingDisplayName', 'settingDisplayName'],
            ['genSettingDefaultFolder', 'settingDefaultFolder'],
            ['genSettingDefaultCategory', 'settingDefaultCategory'],
            ['genSettingUnreadBadgeMode', 'settingUnreadBadgeMode'],
            ['genSettingSignatureText', 'settingSignatureText'],
            ['genSettingReplyBehaviorSelect', 'settingReplyBehaviorSelect']
        ];
        textPairs.forEach(([genId, mainId]) => {
            const genEl = document.getElementById(genId);
            const mainEl = document.getElementById(mainId);
            if (genEl && mainEl) {
                genEl.addEventListener('input', () => { mainEl.value = genEl.value; markDirty(); });
                mainEl.addEventListener('input', () => { genEl.value = mainEl.value; markDirty(); });
            }
        });

        const checkPairs = [
            ['genSettingShowCategoryTabs', 'settingShowCategoryTabs'],
            ['genSettingAppendSignature', 'settingAppendSignature'],
            ['genSettingConfirmDiscard', 'settingConfirmDiscard'],
            ['genSettingAutoSaveDrafts', 'settingAutoSaveDrafts'],
            ['genSettingInAppNotifications', 'settingInAppNotifications']
        ];
        checkPairs.forEach(([genId, mainId]) => {
            const genEl = document.getElementById(genId);
            const mainEl = document.getElementById(mainId);
            if (genEl && mainEl) {
                genEl.addEventListener('change', () => { mainEl.checked = genEl.checked; markDirty(); });
                mainEl.addEventListener('change', () => { genEl.checked = mainEl.checked; markDirty(); });
            }
        });

        const genTheme = document.getElementById('genSettingThemeSelect');
        const mainTheme = document.getElementById('settingThemeSelect');
        if (genTheme && mainTheme) {
            genTheme.addEventListener('change', () => {
                mainTheme.value = genTheme.value;
                previewTheme(genTheme.value);
            });
            mainTheme.addEventListener('change', () => {
                genTheme.value = mainTheme.value;
                previewTheme(mainTheme.value);
            });
        }

        const genDensity = document.getElementById('genSettingDensitySelect');
        const mainDensity = document.getElementById('settingDensitySelect');
        if (genDensity && mainDensity) {
            genDensity.addEventListener('change', () => {
                mainDensity.value = genDensity.value;
                previewDensity(genDensity.value);
            });
            mainDensity.addEventListener('change', () => {
                genDensity.value = mainDensity.value;
                previewDensity(mainDensity.value);
            });
        }
    }

    async function saveAllGeneral() {
        syncAllFromGeneral();
        const genDisplayName = document.getElementById('genSettingDisplayName');
        if (genDisplayName && genDisplayName.value.trim()) {
            await updateProfile();
        }
        await savePreferences();
    }

    /**
     * Initializes custom in-page select dropdowns for all select elements in Settings.
     * This replaces native browser select popups with responsive, strictly bounded in-page dropdowns
     * that align 100% with their input fields and never overflow the mobile viewport.
     */
    function initCustomSelects() {
        const selects = document.querySelectorAll('.settings-page select.form-select, .settings-page select');
        selects.forEach(select => {
            if (select.dataset.customized === 'true') return;
            select.dataset.customized = 'true';

            // Create container
            const container = document.createElement('div');
            container.className = 'custom-select-container';

            // Insert container before select and wrap it
            select.parentNode.insertBefore(container, select);
            container.appendChild(select);

            // Hide native select visually while keeping it fully functional in DOM
            select.style.setProperty('display', 'none', 'important');

            // Find current option label
            const currentOption = select.options[select.selectedIndex] || select.options[0];
            const currentText = currentOption ? currentOption.textContent.trim() : '';

            // Create Trigger button
            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'custom-select-trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');

            const labelSpan = document.createElement('span');
            labelSpan.className = 'custom-select-label';
            labelSpan.textContent = currentText;

            const arrowSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            arrowSvg.setAttribute('class', 'custom-select-arrow');
            arrowSvg.setAttribute('width', '16');
            arrowSvg.setAttribute('height', '16');
            arrowSvg.setAttribute('viewBox', '0 0 24 24');
            arrowSvg.setAttribute('fill', 'currentColor');
            const arrowPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            arrowPath.setAttribute('d', 'M7 10l5 5 5-5z');
            arrowSvg.appendChild(arrowPath);

            trigger.appendChild(labelSpan);
            trigger.appendChild(arrowSvg);
            container.appendChild(trigger);

            // Create Dropdown list
            const dropdown = document.createElement('div');
            dropdown.className = 'custom-select-dropdown';
            dropdown.setAttribute('role', 'listbox');

            function buildOptions() {
                dropdown.innerHTML = '';
                Array.from(select.options).forEach(opt => {
                    const optDiv = document.createElement('div');
                    optDiv.className = 'custom-select-option' + (opt.value === select.value ? ' selected' : '');
                    optDiv.dataset.value = opt.value;
                    optDiv.textContent = opt.textContent.trim();

                    optDiv.addEventListener('click', (e) => {
                        e.stopPropagation();
                        select.value = opt.value;
                        labelSpan.textContent = opt.textContent.trim();

                        dropdown.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
                        optDiv.classList.add('selected');

                        closeDropdown();

                        // Fire native change event so all handlers (theme preview, density preview, dirty state, tab sync) execute
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    dropdown.appendChild(optDiv);
                });
            }

            buildOptions();
            container.appendChild(dropdown);

            function toggleDropdown(e) {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = dropdown.classList.contains('show');
                closeAllCustomDropdowns();
                if (!isOpen) {
                    container.classList.add('open');
                    dropdown.classList.add('show');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            }

            function closeDropdown() {
                container.classList.remove('open');
                dropdown.classList.remove('show');
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', toggleDropdown);

            // Update custom dropdown when select value changes programmatically
            select.addEventListener('change', () => {
                const opt = select.options[select.selectedIndex];
                if (opt) {
                    labelSpan.textContent = opt.textContent.trim();
                    dropdown.querySelectorAll('.custom-select-option').forEach(o => {
                        if (o.dataset.value === select.value) {
                            o.classList.add('selected');
                        } else {
                            o.classList.remove('selected');
                        }
                    });
                }
            });
        });
    }

    function closeAllCustomDropdowns() {
        document.querySelectorAll('.custom-select-container.open').forEach(c => c.classList.remove('open'));
        document.querySelectorAll('.custom-select-dropdown.show').forEach(d => d.classList.remove('show'));
        document.querySelectorAll('.custom-select-trigger[aria-expanded="true"]').forEach(t => t.setAttribute('aria-expanded', 'false'));
    }

    // Close when tapping outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.custom-select-container')) {
            closeAllCustomDropdowns();
        }
    });

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        initSettingsState();
        initTabs();
        attachDirtyListeners();
        attachGeneralSyncListeners();
        updateCharCount();
        initCustomSelects();
    });

    // Public API
    window.MailSettings = {
        previewTheme,
        previewDensity,
        savePreferences,
        discardChanges,
        updateProfile,
        uploadAvatar,
        changePassword,
        togglePasswordVisibility,
        addBlockedSender,
        removeBlockedSender,
        deleteLabel,
        openDeleteAccountModal,
        closeDeleteAccountModal,
        confirmDeleteAccount,
        handleBrowserNotificationToggle,
        testBrowserNotification,
        switchToTab,
        saveAllGeneral,
        initCustomSelects
    };

})();
