<!-- Advanced Search Dialog Modal -->
<div class="modal-backdrop" id="advancedSearchModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Advanced Search Options</h3>
            <button type="button" class="btn-icon" onclick="window.MailApp.closeAdvancedSearch()" aria-label="Close dialog">&times;</button>
        </div>

        <form id="advancedSearchForm" onsubmit="event.preventDefault(); window.MailApp.submitAdvancedSearch();">
            <div class="modal-body">
                <div class="form-row">
                    <label for="advFrom" class="modal-label">From</label>
                    <input type="text" id="advFrom" class="form-input" placeholder="Sender name or email address">
                </div>

                <div class="form-row">
                    <label for="advTo" class="modal-label">To</label>
                    <input type="text" id="advTo" class="form-input" placeholder="Recipient email address">
                </div>

                <div class="form-row">
                    <label for="advSubject" class="modal-label">Subject</label>
                    <input type="text" id="advSubject" class="form-input" placeholder="Words in subject">
                </div>

                <div class="form-row">
                    <label for="advHasAttachment" class="modal-label">Attachments</label>
                    <label class="checkbox-label">
                        <input type="checkbox" id="advHasAttachment">
                        <span>Has attachment</span>
                    </label>
                </div>

                <div class="form-row-grid">
                    <div class="form-col">
                        <label for="advDateFrom" class="modal-label">Date From</label>
                        <input type="date" id="advDateFrom" class="form-input">
                    </div>
                    <div class="form-col">
                        <label for="advDateTo" class="modal-label">Date To</label>
                        <input type="date" id="advDateTo" class="form-input">
                    </div>
                </div>

                <div class="form-row">
                    <label for="advFolder" class="modal-label">Mailbox Scope</label>
                    <div class="custom-select-container" id="customAdvFolderContainer">
                        <button type="button" class="custom-select-trigger" id="customAdvFolderTrigger" aria-haspopup="listbox" aria-expanded="false" onclick="window.MailApp.toggleCustomAdvFolder(event)">
                            <span class="custom-select-label" id="customAdvFolderLabel">Inbox</span>
                            <svg class="custom-select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </button>
                        <div class="custom-select-dropdown" id="customAdvFolderDropdown" role="listbox">
                            <div class="custom-select-option" data-value="all" onclick="window.MailApp.selectCustomAdvFolder('all', 'All Mail (Archive)')">All Mail (Archive)</div>
                            <div class="custom-select-option selected" data-value="inbox" onclick="window.MailApp.selectCustomAdvFolder('inbox', 'Inbox')">Inbox</div>
                            <div class="custom-select-option" data-value="sent" onclick="window.MailApp.selectCustomAdvFolder('sent', 'Sent')">Sent</div>
                            <div class="custom-select-option" data-value="drafts" onclick="window.MailApp.selectCustomAdvFolder('drafts', 'Drafts')">Drafts</div>
                            <div class="custom-select-option" data-value="spam" onclick="window.MailApp.selectCustomAdvFolder('spam', 'Spam')">Spam</div>
                            <div class="custom-select-option" data-value="trash" onclick="window.MailApp.selectCustomAdvFolder('trash', 'Trash')">Trash</div>
                        </div>
                        <select id="advFolder" name="folder" class="form-input" style="display: none;" tabindex="-1" aria-hidden="true">
                            <option value="all">All Mail (Archive)</option>
                            <option value="inbox" selected>Inbox</option>
                            <option value="sent">Sent</option>
                            <option value="drafts">Drafts</option>
                            <option value="spam">Spam</option>
                            <option value="trash">Trash</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="window.MailApp.resetAdvancedSearch()">Reset</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Search</span>
                </button>
            </div>
        </form>
    </div>
</div>
