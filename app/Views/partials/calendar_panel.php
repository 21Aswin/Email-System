<!-- Google-Style Calendar Side Panel Matching User Design -->
<aside class="calendar-sidepanel" id="calendarSidepanel" style="display: none;" aria-label="Google Calendar Panel">
    <!-- Header -->
    <div class="cal-panel-header">
        <div class="cal-header-left">
            <span class="cal-title-small">CALENDAR</span>
            <h2 class="cal-header-date" id="calSelectedDateHeader"><?= date('D, M j -') ?></h2>
        </div>
        <div class="cal-header-actions">
            <button type="button" class="cal-btn-icon" id="calRefreshBtn" title="Refresh Calendar" onclick="window.MailApp.calendar.refresh()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
            </button>
            <button type="button" class="cal-btn-icon" id="calCloseBtn" title="Close Panel" onclick="window.MailApp.calendar.close()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Panel Scrollable Body -->
    <div class="cal-panel-body" id="calPanelBody">
        <!-- Interactive Monthly Mini Calendar -->
        <div class="cal-widget-card">
            <div class="cal-month-nav">
                <span class="cal-month-title" id="calMonthYearTitle"><?= date('F Y') ?></span>
                <div class="cal-nav-arrows">
                    <button type="button" class="cal-arrow-btn" id="calPrevMonthBtn" onclick="window.MailApp.calendar.prevMonth()" aria-label="Previous Month">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    </button>
                    <button type="button" class="cal-arrow-btn" id="calNextMonthBtn" onclick="window.MailApp.calendar.nextMonth()" aria-label="Next Month">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                    </button>
                </div>
            </div>

            <!-- Day Headers S M T W T F S -->
            <div class="cal-weekdays-row">
                <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
            </div>

            <!-- Grid of Days -->
            <div class="cal-days-grid" id="calDaysGrid">
                <!-- Dynamically populated by JS -->
            </div>
        </div>

        <!-- Agenda / Schedule List -->
        <div class="cal-agenda-section">
            <div class="cal-agenda-header">
                <span class="cal-agenda-title">Agenda & Upcoming Work</span>
                <button type="button" class="cal-view-all-btn" onclick="window.MailApp.calendar.loadUpcoming()">Show All</button>
            </div>

            <div class="cal-agenda-list" id="calAgendaList">
                <!-- Dynamically populated by JS matching green/amber/blue pill screenshot -->
                <div class="cal-loading-state">Loading events...</div>
            </div>
        </div>

        <!-- Inline Event Creation Form (Toggled by '+ Create an event') -->
        <div class="cal-create-card" id="calCreateCard" style="display: none;">
            <div class="cal-create-header">
                <h4 id="calCreateFormTitle">Schedule New Event</h4>
                <button type="button" class="cal-btn-icon btn-sm" onclick="window.MailApp.calendar.hideCreateForm()">&times;</button>
            </div>
            
            <form id="calEventForm" onsubmit="event.preventDefault(); window.MailApp.calendar.submitEvent();">
                <input type="hidden" id="calEventMessageId" value="">

                <div class="cal-form-group">
                    <label for="calEventTitle" class="cal-label">Event / Interview Title *</label>
                    <input type="text" id="calEventTitle" class="form-input" required placeholder="e.g. Technical Interview Round 1" autocomplete="off">
                </div>

                <div class="cal-form-row">
                    <div class="cal-form-group" style="flex: 1.2;">
                        <label for="calEventDate" class="cal-label">Date *</label>
                        <input type="date" id="calEventDate" class="form-input" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="cal-form-group" style="flex: 1;">
                        <label for="calEventCategory" class="cal-label">Type</label>
                        <select id="calEventCategory" class="form-input">
                            <option value="interview" selected>💼 Interview</option>
                            <option value="work">💻 Work</option>
                            <option value="meeting">🤝 Meeting</option>
                            <option value="holiday">🌴 Holiday</option>
                            <option value="personal">⭐ Personal</option>
                        </select>
                    </div>
                </div>

                <div class="cal-form-row">
                    <div class="cal-form-group" style="flex: 1;">
                        <label for="calEventStartTime" class="cal-label">Start Time</label>
                        <input type="time" id="calEventStartTime" class="form-input" value="10:00">
                    </div>
                    <div class="cal-form-group" style="flex: 1;">
                        <label for="calEventEndTime" class="cal-label">End Time</label>
                        <input type="time" id="calEventEndTime" class="form-input" value="11:00">
                    </div>
                </div>

                <div class="cal-form-group">
                    <label for="calEventLocation" class="cal-label">Location / Meet Link</label>
                    <input type="text" id="calEventLocation" class="form-input" placeholder="e.g. Google Meet / Zoom / Office Room">
                </div>

                <div class="cal-form-group">
                    <label for="calEventDesc" class="cal-label">Description / Interview Notes</label>
                    <textarea id="calEventDesc" class="form-input" rows="2" placeholder="Candidate details, agenda, or discussion topics"></textarea>
                </div>

                <div class="cal-form-actions">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.MailApp.calendar.hideCreateForm()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="calSaveEventBtn">Save Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Footer Button -->
    <div class="cal-panel-footer">
        <button type="button" class="btn-cal-create-event" id="calOpenCreateBtn" onclick="window.MailApp.calendar.showCreateForm()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            <span>Create an event</span>
        </button>
    </div>
</aside>
