<!-- Create / Edit Label Modal -->
<div class="modal-backdrop" id="labelModal" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3 id="labelModalTitle">Create New Label</h3>
            <button type="button" class="btn-icon" onclick="window.MailApp.closeLabelModal()" aria-label="Close dialog">&times;</button>
        </div>

        <form id="labelForm" onsubmit="event.preventDefault(); window.MailApp.saveLabel();">
            <input type="hidden" id="editLabelId" value="">

            <div class="modal-body">
                <div class="form-row">
                    <label for="labelNameInput" class="modal-label">Label Name</label>
                    <input type="text" id="labelNameInput" class="form-input" placeholder="e.g. Invoices, Priority, Project X" required autofocus>
                </div>

                <div class="form-row">
                    <label class="modal-label">Choose Color</label>
                    <div class="color-palette-picker" id="colorPalettePicker">
                        <label class="color-swatch-label">
                            <input type="radio" name="label_color" value="#1a73e8" checked>
                            <span class="color-swatch" style="background-color: #1a73e8;"></span>
                        </label>
                        <label class="color-swatch-label">
                            <input type="radio" name="label_color" value="#188038">
                            <span class="color-swatch" style="background-color: #188038;"></span>
                        </label>
                        <label class="color-swatch-label">
                            <input type="radio" name="label_color" value="#d93025">
                            <span class="color-swatch" style="background-color: #d93025;"></span>
                        </label>
                        <label class="color-swatch-label">
                            <input type="radio" name="label_color" value="#f29900">
                            <span class="color-swatch" style="background-color: #f29900;"></span>
                        </label>
                        <label class="color-swatch-label">
                            <input type="radio" name="label_color" value="#a142f4">
                            <span class="color-swatch" style="background-color: #a142f4;"></span>
                        </label>
                        <label class="color-swatch-label">
                            <input type="radio" name="label_color" value="#fa7b17">
                            <span class="color-swatch" style="background-color: #fa7b17;"></span>
                        </label>
                        <label class="color-swatch-label">
                            <input type="radio" name="label_color" value="#007b83">
                            <span class="color-swatch" style="background-color: #007b83;"></span>
                        </label>
                        <label class="color-swatch-label">
                            <input type="radio" name="label_color" value="#5f6368">
                            <span class="color-swatch" style="background-color: #5f6368;"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="window.MailApp.closeLabelModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveLabel">Save Label</button>
            </div>
        </form>
    </div>
</div>
