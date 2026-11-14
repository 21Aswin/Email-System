<!-- Manage Subscriptions Modal -->
<div class="modal-backdrop" id="subscriptionsModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Manage Subscriptions & Newsletters</h3>
            <button type="button" class="btn-icon" onclick="window.MailApp.closeSubscriptionsModal()" aria-label="Close dialog">&times;</button>
        </div>

        <div class="modal-body">
            <div class="info-callout" style="background-color: var(--beach-card); border: 1px solid var(--beach-border); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 14px;">
                <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span>Subscription Management Status</span>
                </div>
                <p style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">
                    Novalyx Webmail tracks newsletters and recurring marketing senders in your mailbox. One-click RFC 8058 List-Unsubscribe is safely handled through confirmed sender routing.
                </p>
            </div>

            <h4 style="font-size: 13px; font-weight: 700; color: var(--text-secondary); margin-bottom: 10px;">Detected Senders & Newsletters:</h4>
            <div class="subscriptions-list" id="subscriptionsListContainer" style="max-height: 220px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                <div class="sub-item" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                    <div>
                        <strong style="font-size: 13px; color: var(--text-primary);">Tech Trends Weekly Digest</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">digest@techtrends-weekly.com • Updates</div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailApp.bulkMove('spam', [9]); window.MailApp.closeSubscriptionsModal();" style="font-size: 11px;">
                        Move to Spam
                    </button>
                </div>

                <div class="sub-item" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                    <div>
                        <strong style="font-size: 13px; color: var(--text-primary);">Enterprise Cloud Deals</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">deals@enterprise-partners.com • Promotions</div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailApp.bulkMove('trash', [4]); window.MailApp.closeSubscriptionsModal();" style="font-size: 11px;">
                        Remove from Inbox
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.MailApp.closeSubscriptionsModal()">Close</button>
        </div>
    </div>
</div>
