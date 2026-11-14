<!-- Snooze Custom Date Picker Modal -->
<div class="modal-backdrop" id="snoozeModal" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3>Snooze until...</h3>
            <button type="button" class="btn-icon" onclick="window.MailApp.closeSnoozeModal()" aria-label="Close dialog">&times;</button>
        </div>

        <div class="modal-body">
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                Choose when this message should wake up and return to your Inbox:
            </p>

            <!-- Quick Presets -->
            <div class="snooze-presets-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                <button type="button" class="btn-snooze-preset" onclick="window.MailApp.snoozeQuick('2min')">
                    <span class="preset-icon">⚡</span>
                    <span class="preset-label">In 2 minutes (Quick Test)</span>
                    <span class="preset-time"><?= date('g:i A', strtotime('+2 minutes')) ?></span>
                </button>
                <button type="button" class="btn-snooze-preset" onclick="window.MailApp.snoozeQuick('today')">
                    <span class="preset-icon">🕒</span>
                    <span class="preset-label">Later today</span>
                    <span class="preset-time">6:00 PM</span>
                </button>
                <button type="button" class="btn-snooze-preset" onclick="window.MailApp.snoozeQuick('tomorrow')">
                    <span class="preset-icon">🌅</span>
                    <span class="preset-label">Tomorrow morning</span>
                    <span class="preset-time">Tomorrow, 8:00 AM</span>
                </button>
                <button type="button" class="btn-snooze-preset" onclick="window.MailApp.snoozeQuick('nextweek')">
                    <span class="preset-icon">📅</span>
                    <span class="preset-label">Next week</span>
                    <span class="preset-time">Mon, 8:00 AM</span>
                </button>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; margin: 12px 0; color: var(--text-secondary); font-size: 12px;">
                <div style="flex: 1; height: 1px; background: var(--beach-border);"></div>
                <span>Or pick custom date & time</span>
                <div style="flex: 1; height: 1px; background: var(--beach-border);"></div>
            </div>

            <form id="snoozeForm" onsubmit="event.preventDefault(); window.MailApp.submitCustomSnooze();">
                <div class="form-row">
                    <label for="snoozeDate" class="modal-label">Date</label>
                    <input type="date" id="snoozeDate" class="form-input" required value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-row">
                    <label for="snoozeTime" class="modal-label">Time</label>
                    <input type="time" id="snoozeTime" class="form-input" required value="<?= date('H:i', strtotime('+1 hour')) ?>">
                </div>

                <div class="modal-footer" style="padding: 12px 0 0 0; margin-top: 12px; border-top: 1px solid var(--beach-border);">
                    <button type="button" class="btn btn-secondary" onclick="window.MailApp.closeSnoozeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Custom Snooze</button>
                </div>
            </form>
        </div>
    </div>
</div>
