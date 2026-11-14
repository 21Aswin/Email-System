<!-- SMTP & Email Transport Settings Modal -->
<div class="modal-backdrop" id="settingsModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Email Delivery & SMTP Settings</h3>
            <button type="button" class="btn-icon" onclick="window.MailApp.closeSettingsModal()" aria-label="Close dialog">&times;</button>
        </div>

        <form id="settingsForm" onsubmit="event.preventDefault(); window.MailApp.saveSmtpSettings();">
            <div class="modal-body">
                <div class="smtp-status-banner <?= filter_var(\App\Core\Env::get('SMTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN) ? 'smtp-active' : 'smtp-disabled' ?>">
                    <div class="status-icon-circle">
                        <?php if (filter_var(\App\Core\Env::get('SMTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN)): ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <?php else: ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="status-desc">
                        <strong>Delivery Mode: <?= filter_var(\App\Core\Env::get('SMTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN) ? 'Live SMTP Transport Enabled' : 'Internal Mailbox Delivery (Demo Mode)' ?></strong>
                        <p>
                            <?= filter_var(\App\Core\Env::get('SMTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN) 
                                ? 'Real emails are transmitted through your configured SMTP server.' 
                                : 'Messages are delivered to users registered inside this app. To send real emails to real Gmail/Yahoo/Outlook inboxes, enable SMTP below.' ?>
                        </p>
                    </div>
                </div>

                <div class="form-row">
                    <label class="checkbox-label" style="font-weight: 600; color: var(--text-primary);">
                        <input type="checkbox" id="settingSmtpEnabled" <?= filter_var(\App\Core\Env::get('SMTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?> onchange="window.MailApp.toggleSmtpFields(this.checked)">
                        <span>Enable Real External SMTP Sending</span>
                    </label>
                </div>

                <div id="smtpFieldsContainer" style="<?= filter_var(\App\Core\Env::get('SMTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN) ? '' : 'opacity: 0.6;' ?>">
                    <div class="form-row-grid">
                        <div class="form-col">
                            <label for="settingSmtpHost" class="modal-label">SMTP Host</label>
                            <input type="text" id="settingSmtpHost" class="form-input" value="<?= \App\Core\View::e(\App\Core\Env::get('SMTP_HOST', 'smtp.gmail.com')) ?>" placeholder="smtp.gmail.com">
                        </div>
                        <div class="form-col">
                            <label for="settingSmtpPort" class="modal-label">SMTP Port</label>
                            <input type="number" id="settingSmtpPort" class="form-input" value="<?= \App\Core\View::e(\App\Core\Env::get('SMTP_PORT', '587')) ?>" placeholder="587">
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-col">
                            <label for="settingSmtpEncryption" class="modal-label">Encryption</label>
                            <select id="settingSmtpEncryption" class="form-input">
                                <option value="tls" <?= strtolower(\App\Core\Env::get('SMTP_ENCRYPTION', 'tls')) === 'tls' ? 'selected' : '' ?>>TLS (Recommended - 587)</option>
                                <option value="ssl" <?= strtolower(\App\Core\Env::get('SMTP_ENCRYPTION', 'tls')) === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                                <option value="none" <?= strtolower(\App\Core\Env::get('SMTP_ENCRYPTION', 'tls')) === 'none' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                        <div class="form-col">
                            <label for="settingSmtpSender" class="modal-label">From Address</label>
                            <input type="email" id="settingSmtpSender" class="form-input" value="<?= \App\Core\View::e(\App\Core\Env::get('SMTP_FROM_ADDRESS', 'novalyx.workspace@gmail.com')) ?>" placeholder="novalyx.workspace@gmail.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="settingSmtpUsername" class="modal-label">SMTP Username (Email)</label>
                        <input type="text" id="settingSmtpUsername" class="form-input" value="<?= \App\Core\View::e(\App\Core\Env::get('SMTP_USERNAME', 'adminnkinflyx@gmail.com')) ?>" placeholder="your-email@gmail.com">
                    </div>

                    <div class="form-row">
                        <label for="settingSmtpPassword" class="modal-label">SMTP Password / Google App Password</label>
                        <input type="password" id="settingSmtpPassword" class="form-input" placeholder="Enter Gmail App Password (16 chars)" value="<?= \App\Core\View::e(\App\Core\Env::get('SMTP_PASSWORD', '')) ?>">
                        <small class="form-hint" style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">
                            For Gmail: generate an <strong>App Password</strong> at <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: var(--brand-primary);">myaccount.google.com/apppasswords</a> with 2-Step Verification on.
                        </small>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="window.MailApp.closeSettingsModal()">Close</button>
                <button type="submit" class="btn btn-primary" id="btnSaveSmtpSettings">Save Settings</button>
            </div>
        </form>
    </div>
</div>
