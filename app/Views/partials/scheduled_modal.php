<!-- Scheduled Sending Info Modal -->
<div class="modal-backdrop" id="scheduledInfoModal" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3>Scheduled Send Status</h3>
            <button type="button" class="btn-icon" onclick="window.MailApp.closeScheduledInfoModal()" aria-label="Close dialog">&times;</button>
        </div>

        <div class="modal-body">
            <div class="info-callout" style="background-color: var(--beach-card); border: 1px solid var(--beach-border); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 12px;">
                <strong style="color: var(--text-primary); font-size: 13px; display: block; margin-bottom: 4px;">Scheduled Sending Engine</strong>
                <p style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">
                    Messages scheduled for future transmission are stored securely in MySQL with their exact target timestamp. When scheduled, they are held until the queue runner processes them.
                </p>
            </div>
            <p style="font-size: 13px; color: var(--text-secondary);">
                To schedule a new message, click the arrow next to <strong>Send</strong> inside the Compose window and select <strong>Schedule send</strong>.
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.MailApp.closeScheduledInfoModal()">Got it</button>
        </div>
    </div>
</div>
