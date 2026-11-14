<!-- Sender Contact Profile Hovercard (Gmail Style) -->
<div class="sender-hovercard" id="senderHovercard" style="display: none;" role="dialog" aria-label="Contact Details">
    <div class="hovercard-top">
        <div class="hovercard-avatar" id="hovercardAvatar">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="#0b57d0">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        </div>
        <div class="hovercard-info">
            <div class="hovercard-name-row">
                <span class="hovercard-name" id="hovercardName">Sender Name</span>
                <button type="button" class="btn-icon btn-sm hovercard-add-contact" id="hovercardAddContact" title="Add to contacts" aria-label="Add to contacts">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </button>
            </div>
            <div class="hovercard-email" id="hovercardEmail">sender@example.com</div>
        </div>
    </div>

    <div class="hovercard-actions">
        <button type="button" class="btn-hovercard-send" id="hovercardSendMail" title="Send email">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
            </svg>
            <span>Send Mail</span>
        </button>

        <div class="hovercard-icon-actions">
            <button type="button" class="btn-hovercard-circle" id="hovercardChat" title="Send a chat message" aria-label="Send a chat message">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </button>
            <button type="button" class="btn-hovercard-circle" id="hovercardMeet" title="Start video call" aria-label="Start video call">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="23 7 16 12 23 17 23 7"></polygon>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                </svg>
            </button>
            <button type="button" class="btn-hovercard-circle" id="hovercardSchedule" title="Schedule an event" aria-label="Schedule an event">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </button>
        </div>
    </div>
</div>
