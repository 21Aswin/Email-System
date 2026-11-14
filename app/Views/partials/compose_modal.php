<!-- Floating / Docked Compose Window -->
<div class="compose-modal" id="composeModal" style="display: none;">
    <div class="compose-header">
        <span class="compose-title" id="composeTitle">New Message</span>
        <div class="compose-header-actions">
            <button type="button" class="btn-compose-ctrl" id="btnComposeMinimize" title="Minimize" aria-label="Minimize">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13H5v-2h14v2z"/></svg>
            </button>
            <button type="button" class="btn-compose-ctrl" id="btnComposeMaximize" title="Full screen" aria-label="Maximize">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
            </button>
            <button type="button" class="btn-compose-ctrl" id="btnComposeClose" title="Save and close" aria-label="Close">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
    </div>

    <form class="compose-body" id="composeForm" onsubmit="event.preventDefault(); window.MailApp.sendEmail();">
        <input type="hidden" id="composeDraftId" value="">

        <!-- Recipients field -->
        <div class="compose-row recipients-row">
            <span class="compose-row-label">To</span>
            <input type="text" id="composeTo" class="compose-input" placeholder="Recipients (comma separated emails)" required>
            <div class="compose-row-extras">
                <button type="button" class="btn-text-ctrl" id="btnToggleCc">Cc</button>
                <button type="button" class="btn-text-ctrl" id="btnToggleBcc">Bcc</button>
            </div>
        </div>

        <div class="compose-row" id="rowCc" style="display: none;">
            <span class="compose-row-label">Cc</span>
            <input type="text" id="composeCc" class="compose-input" placeholder="Cc recipients">
        </div>

        <div class="compose-row" id="rowBcc" style="display: none;">
            <span class="compose-row-label">Bcc</span>
            <input type="text" id="composeBcc" class="compose-input" placeholder="Bcc recipients">
        </div>

        <!-- Subject field -->
        <div class="compose-row subject-row">
            <input type="text" id="composeSubject" class="compose-input-subject" placeholder="Subject">
        </div>

        <!-- Formatting Toolbar -->
        <div class="compose-toolbar">
            <button type="button" class="format-btn" onclick="document.execCommand('bold', false, null)" title="Bold">
                <strong>B</strong>
            </button>
            <button type="button" class="format-btn" onclick="document.execCommand('italic', false, null)" title="Italic">
                <em>I</em>
            </button>
            <button type="button" class="format-btn" onclick="document.execCommand('underline', false, null)" title="Underline">
                <u>U</u>
            </button>
            <div class="toolbar-sep"></div>
            <button type="button" class="format-btn" onclick="document.execCommand('insertUnorderedList', false, null)" title="Bulleted list">
                • List
            </button>
            <button type="button" class="format-btn" onclick="document.execCommand('insertOrderedList', false, null)" title="Numbered list">
                1. List
            </button>
            <div class="toolbar-sep"></div>
            <button type="button" class="format-btn" onclick="window.MailApp.insertLinkPrompt()" title="Insert Link">
                🔗 Link
            </button>
            <button type="button" class="format-btn" onclick="document.execCommand('removeFormat', false, null)" title="Clear formatting">
                Tx
            </button>
        </div>

        <!-- Rich Body Editor -->
        <div 
            id="composeBodyEditor" 
            class="compose-editor" 
            contenteditable="true" 
            placeholder="Write your email here..."
        ></div>

        <!-- Attachments Container -->
        <div class="compose-attachments-list" id="composeAttachmentsList"></div>

        <!-- Footer actions -->
        <div class="compose-footer">
            <div class="compose-footer-left">
                <button type="submit" class="btn btn-primary btn-send" id="btnSendMail">
                    <span>Send</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>

                <!-- Attachment file trigger -->
                <label class="btn-icon btn-attach-trigger" title="Attach files" aria-label="Attach files">
                    <input type="file" id="composeFileInput" multiple style="display: none;" onchange="window.MailApp.handleFileUpload(this.files)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                    </svg>
                </label>

                <!-- Draft status indicator -->
                <span class="draft-status-indicator" id="draftStatusIndicator">Draft saved</span>
            </div>

            <div class="compose-footer-right">
                <button type="button" class="btn-icon" id="btnDiscardDraft" title="Discard draft" aria-label="Discard draft">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
            </div>
        </div>
    </form>
</div>
