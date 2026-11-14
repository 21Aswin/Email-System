<!-- Manage Labels Modal -->
<div class="modal-backdrop" id="manageLabelsModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Manage Custom Labels</h3>
            <button type="button" class="btn-icon" onclick="window.MailApp.closeManageLabelsModal()" aria-label="Close dialog">&times;</button>
        </div>

        <div class="modal-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <p style="font-size: 13px; color: var(--text-secondary);">Organize, rename, recolor, or delete your mailbox labels:</p>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.MailApp.closeManageLabelsModal(); window.MailApp.openLabelModal();">
                    + New Label
                </button>
            </div>

            <div class="manage-labels-list" id="manageLabelsListContainer" style="max-height: 280px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                <!-- Populated dynamically via JS -->
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.MailApp.closeManageLabelsModal()">Done</button>
        </div>
    </div>
</div>
