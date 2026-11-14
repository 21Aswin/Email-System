# Novalyx Cloud Mail (Enterprise Webmail Application)

A production-minded, high-fidelity responsive email web client built for **Novalyx Cloud & Tech** using PHP 8.2+, MySQL 8, and modern semantic HTML/CSS/JavaScript with full server-side PHP templating and interactive client-side async updates.

---

## 🌟 Brand & Visual Identity
- **Company Name:** `Novalyx Cloud & Tech`
- **Product Name:** `Novalyx Cloud Mail`
- **Primary Color:** Deep Tech Sapphire Blue (`#0b57d0` / `#1a73e8`)
- **Secondary Accent:** Warm Light Beach tone (`#F7F3EE` / `#FAF6F0` / `#E8DFD8`)
- **Logo Asset:** Custom SVG branding at `/assets/images/logo.svg`

---

## 🚀 Complete Sidebar & Feature Architecture

### 1. Compose
- Floating docked panel (Desktop bottom-right / Mobile full-screen) supporting `To`, optional `Cc` and `Bcc` rows, `Subject`, rich body editor, and multi-file attachment uploads.
- Auto-saves drafts debounced every 1.5 seconds into MySQL drafts mailbox.
- Real SMTP sending engine via Google SMTP (`smtp.gmail.com:587`). If credentials are not set or delivery fails, preserves the draft safely and displays clear error feedback.

### 2. Inbox
- Dynamically calculated unread counter for current user (`folder = 'inbox'` and `is_read = 0`).
- Category tabs: Primary, Updates, Promotions, and Purchases.
- Opening an email automatically marks it as read and decrements the live unread count.

### 3. Starred
- Single-flag filter (`is_starred = 1`) across all non-trash and non-spam items.
- Toggling stars available from the message list row and detail reading view with live MySQL persistence.

### 4. Snoozed & Lazy-Expiry
- Future `snoozed_until` timestamp.
- Custom date & time picker modal (`#snoozeModal`) plus quick presets.
- **Lazy-Expiry Check:** When a user opens any mailbox view, `Message::checkSnoozeExpiries($userId)` automatically restores expired items back to Inbox with zero requirement for an unconfigured daemon.
- Un-snooze action restores items immediately to Inbox.

### 5. Sent
- Displays messages dispatched by the signed-in user, newest first.
- Displays `To: <recipient>` in the message row instead of redundant sender name.

### 6. Drafts
- Displays unsaved/unfinished emails with a red `Draft` indicator chip.
- Clicking any draft row directly opens the Compose window pre-filled with draft recipient, Cc/Bcc, subject, and body for seamless resume editing.

### 7. Purchases (Smart Category)
- User-facing smart category for e-commerce, invoices, and purchase order receipts.
- Easily filterable from both sidebar navigation and Inbox category tabs.

### 8. More / Less Expandable Toggle
- Collapsible navigation grouping: Important, Scheduled, All Mail, Spam, Trash, Manage Subscriptions, and Manage Labels.
- Keyboard accessible with `aria-expanded` and rotating chevron indicator.
- Automatically expands if user navigates to any sub-folder.

### 9. Important
- Explicit user importance bookmark flag (`is_important = 1`) persisted per user in MySQL.
- Toggable from both list rows and reading header.

### 10. Scheduled
- Holds queued messages with target `scheduled_at` timestamps.
- Transparent status dialogue explaining queue state without falsely claiming delivery.

### 11. All Mail
- Complete archive view displaying all user non-trash, non-spam emails (inbox, archive, sent).

### 12. Spam
- Displays filtered spam messages with a top action banner and bulk confirmation to permanently delete all spam.

### 13. Trash
- Soft-deleted items with 1-click restore to Inbox and bulk "Empty Trash now" with confirmation.

### 14. Manage Subscriptions
- Safe dialogue reviewing detected newsletter senders with transparent handling without blindly executing external unsubscribe URLs.

### 15. Manage Labels & Create New Label
- Full CRUD modal: create label with custom color swatch, rename inline, recolor, or delete label without deleting underlying messages.
- Filter emails by label in 1-click.

### 16. Inbound IMAP Reply Sync
- Native SSL socket engine connecting to `imap.gmail.com:993` to pull replies and sync into local MySQL inbox automatically.

---

## 🚀 Features Implemented
1. **Authentication & Session Security:**
   - Registration, login, and logout with bcrypt password hashing (`PASSWORD_BCRYPT`).
   - Secure HTTP-only cookies, session regeneration on login to prevent fixation.
   - CSRF protection on all forms and async JSON requests (`X-CSRF-TOKEN`).
   - Brute-force throttling (temporary lockout after 5 consecutive failed attempts).
   - Strict User Isolation (Users can only read, write, search, and delete their own mailbox data).

2. **Full Email Organization & Views:**
   - **Folders:** Inbox (with live unread badge), Starred, Snoozed, Sent, Drafts, Archive, Spam, and Trash.
   - **Category Tabs:** Primary, Updates, and Promotions.
   - **Message Row Actions:** Select all, Star/Unstar, Mark Read/Unread, Archive, Report Spam, Move to Trash, Permanent Delete.
   - **Hover Quick Actions:** Desktop hover buttons for 1-click Archive, Delete, and Read toggles.

3. **Reading View:**
   - Sender avatar with initial, display name, email, recipient list, and full date.
   - Sanitized message body (blocking `<script>`, inline event handlers, and `javascript:` URIs).
   - Attachment download cards protected with authorization checks (preventing IDOR).
   - Quick "Reply" and "Forward" buttons pre-filling the compose window.

4. **Floating Compose Window:**
   - Bottom-right docked modal (Minimizable, Maximizable, Full-screen on mobile).
   - Real-time debounced auto-saving to Drafts (`Draft saved at HH:MM:SS`).
   - Multi-recipient inputs with optional Cc and Bcc rows.
   - Rich formatting toolbar (Bold, Italic, Underline, Bullet/Numbered lists, Link insertion, Format clear).
   - Multi-file attachment upload with type validation and safe randomized server storage.
   - SMTP sending engine with a transparent local/demo fallback notice if SMTP credentials are not configured.

5. **Search & Custom Labels:**
   - Global instant search querying sender name, email, subject, and message snippets.
   - Advanced Search Dialog: filters by From, To, Subject, Has Attachment, Date Range, and Mailbox folder.
   - Custom Labels: Create new labels with customizable color palettes, tag messages, and filter by label in sidebar.

6. **Responsive UX:**
   - Mobile navigation drawer with hamburger toggle.
   - Touch-friendly action bars with no horizontal overflow.

---

## 🛠️ Prerequisites & Local Setup

### 1. Requirements
- **PHP 8.2+** (with `pdo`, `pdo_mysql`, `fileinfo`, `mbstring` extensions enabled).
- **MySQL 8.0+** or **MariaDB 10.4+**.

### 2. Environment Configuration (`.env`)
The repository includes a ready `.env` file configured for your local MySQL instance. You can modify credentials if needed:
```ini
APP_NAME="nkinfyx groups Webmail"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nkinfyx_email
DB_USERNAME=root
DB_PASSWORD=Aswin@21

# SMTP Settings (Optional - set to true for live email delivery)
SMTP_ENABLED=false
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=adminnkinflyx@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_FROM_ADDRESS=adminnkinflyx@gmail.com
SMTP_FROM_NAME="nkinfyx groups"
```

### 3. Database Migration & Seed
Run the migration script to create all tables and populate initial demo data:
```bash
# Using XAMPP PHP:
& "C:\xampp\php\php.exe" database/migrate.php

# Or standard PHP in PATH:
php database/migrate.php
```

### 4. Running the Application
Start the built-in development server from the project directory:
```bash
# Using XAMPP PHP:
& "C:\xampp\php\php.exe" -S localhost:8000 server.php

# Or standard PHP in PATH:
php -S localhost:8000 server.php
```
Open your browser and navigate to: **[http://localhost:8000](http://localhost:8000)**

---

## 👤 Seed Demo Credentials

| Role | Email Address | Password |
|---|---|---|
| **Admin Account** | `adminnkinflyx@gmail.com` | `password123` |
| **Secondary User** | `kavitha@nkinfyxgroups.in` | `password123` |

> *Note: You can also register a brand new account anytime via `/register`.*

---

## 🧪 Automated Testing
Run the automated test suite verifying password verification, folder counts, message queries, draft auto-saving, attachment authorization, and XSS sanitization:
```bash
& "C:\xampp\php\php.exe" tests/test_flow.php
```

---

## 📁 Project Directory Structure
```
c:\Users\Sampath\Desktop\Email\
├── app/
│   ├── Controllers/
│   │   ├── AttachmentController.php
│   │   ├── AuthController.php
│   │   ├── LabelController.php
│   │   ├── MailboxController.php
│   │   └── MessageController.php
│   ├── Core/
│   │   ├── Autoloader.php
│   │   ├── Database.php
│   │   ├── Env.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   ├── Session.php
│   │   └── View.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   └── GuestMiddleware.php
│   ├── Models/
│   │   ├── Attachment.php
│   │   ├── Label.php
│   │   ├── Message.php
│   │   └── User.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── MailService.php
│   │   ├── SmtpTransport.php
│   │   └── StorageService.php
│   └── Views/
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── errors/
│       │   └── 404.php
│       ├── layouts/
│       │   ├── auth.php
│       │   └── main.php
│       ├── mailbox/
│       │   └── index.php
│       └── partials/
│           ├── advanced_search_modal.php
│           ├── compose_modal.php
│           ├── label_modal.php
│           ├── sidebar.php
│           └── topbar.php
├── database/
│   ├── migrate.php
│   ├── migrate_attachments.php
│   ├── migrate_calendar.php
│   ├── migrate_settings.php
│   ├── migrate_sidebar.php
│   ├── run_all_migrations.php
│   ├── schema.sql
│   └── seed.sql
├── docker/
│   ├── apache.conf
│   └── entrypoint.sh
├── Dockerfile
├── render.yaml
├── DEPLOY_RENDER.md
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css
│   │   └── js/
│   │       ├── app.js
│   │       └── settings.js
│   ├── uploads/
│   │   └── avatars/
│   ├── .htaccess
│   └── index.php
├── storage/
│   ├── .htaccess
│   └── attachments/
├── tests/
│   └── test_flow.php
├── .env
├── .env.example
├── composer.json
├── README.md
└── server.php
```

---

## ⚙️ Settings Area Architecture
The app features an integrated Settings hub (`/settings`) matching the Novalyx design system with 12 distinct setting panels:
1. **Profile:** Display name updates and secure avatar photo upload (MIME validation, 2MB limit, randomized hash storage).
2. **Account:** Read-only verified email display, password change with current password bcrypt verification.
3. **Appearance:** Light / Dark / System theme switching and Comfortable / Compact density toggles (persisted to MySQL).
4. **Inbox Preferences:** Default login folder, default category, category tabs toggle, and unread vs total counter badges.
5. **Notifications:** In-app toast banners and HTML5 desktop browser notification permissions.
6. **Signature:** Plain-text custom signature automatically inserted into new compose drafts separated by standard `--`.
7. **Compose & Reply:** Default reply vs reply-all behavior, draft discard confirmation prompt, auto-save toggle.
8. **Labels:** Integrated label manager with color swatches and instant label deletion.
9. **Blocked Senders:** Block unwanted sender email addresses with automatic database persistence.
10. **Security & Sessions:** Active session metadata (IP address, browser user-agent, login timestamp) and sign out.
11. **Privacy & Data:** Clear data retention statement, transport encryption disclosure, and password-protected account deletion cascade.
12. **Mail Connection:** Outbound SMTP configuration status and test connection utility.

---

## 🚢 Render.com Deployment
The application is ready to deploy directly on **Render.com** with zero extra configuration:
1. Push this repository to GitHub.
2. In Render Dashboard, click **New +** -> **Web Service** and choose **Docker**.
3. Set your remote MySQL database environment variables (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` or `DATABASE_URL`).
4. Render builds the container and executes `database/run_all_migrations.php` automatically on startup!
5. See [DEPLOY_RENDER.md](DEPLOY_RENDER.md) for full instructions and free MySQL provider setup.

---

## 🔒 Security Summary
- **No IDOR:** Attachments are served only via `/api/attachments/{id}/download` after confirming that the file is linked to an email in the requesting user's mailbox. Direct web access to `storage/` is restricted by `.htaccess`.
- **Prepared Statements:** 100% of SQL queries use PDO parameterized prepared statements.
- **XSS Escaping:** Output in PHP views is escaped with `View::e()`, and email HTML bodies are sanitized with custom regex and allowed tag filtering in `MailService::sanitizeHtml()`.
- **CSRF Tokens:** All state-changing POST requests validate session CSRF tokens.
