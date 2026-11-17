-- Seed data for nkinfyx groups Mail
-- Passwords hashed using bcrypt: "password123" ($2y$10$wT33pBvh53Qx4u65eI2C9u3a8Yk4kK1gR1m5s0eJ/cWnC9/L45z2e)

USE `nkinfyx_email`;

-- 1. Create Default Users
-- Default admin / demo user: adminnkinflyx@gmail.com
-- Secondary test user: client@nkinfyxgroups.in
INSERT INTO `users` (`id`, `display_name`, `email`, `password_hash`, `status`, `created_at`) VALUES
(1, 'nkinfyx Admin', 'adminnkinflyx@gmail.com', '$2y$12$BqZUVk1uAiMRX.FvJXDM7OYuz0dwtsShimRqy1LJ5lXPWJFLjYzvO', 'active', NOW()),
(2, 'Kavitha Murugan', 'kavitha@nkinfyxgroups.in', '$2y$12$BqZUVk1uAiMRX.FvJXDM7OYuz0dwtsShimRqy1LJ5lXPWJFLjYzvO', 'active', NOW());

-- 2. Labels for User 1
INSERT INTO `labels` (`id`, `user_id`, `name`, `color`, `created_at`) VALUES
(1, 1, 'Client Projects', '#1a73e8', NOW()),
(2, 1, 'Invoices & Finance', '#188038', NOW()),
(3, 1, 'Priority High', '#d93025', NOW()),
(4, 1, 'Company Updates', '#f29900', NOW());

-- 3. Messages for User 1
-- Message 1: Welcome to nkinfyx Mail (Inbox - Primary, Unread)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `received_at`, `created_at`) VALUES
(1, 1, 'nkinfyx Systems', 'welcome@nkinfyxgroups.in', 'Welcome to your nkinfyx groups Enterprise Webmail!',
'Welcome to your new enterprise workspace. Experience high performance, secure communication, real-time draft saving, and intelligent categorization.',
'<p>Welcome to <strong>nkinfyx groups Enterprise Webmail</strong>!</p><p>We are delighted to provide you with a high-fidelity workspace tailored for seamless productivity and modern communication.</p><h3>Key Features enabled for your account:</h3><ul><li>Original brand interface with light beach accents</li><li>Real-time draft auto-saving with instant feedback</li><li>Smart categorization into Primary, Updates, and Promotions</li><li>End-to-end user isolation with robust MySQL indexing</li><li>Integrated attachment management</li></ul><p>If you have questions, reach out to our administration team at <a href="mailto:adminnkinflyx@gmail.com">adminnkinflyx@gmail.com</a>.</p><p>Best regards,<br><strong>nkinfyx Infrastructure Team</strong></p>',
'Welcome to your new enterprise workspace. Experience high performance, secure communication...',
NOW() - INTERVAL 10 MINUTE, NOW() - INTERVAL 10 MINUTE);

-- Message 2: Q4 Strategic Roadmaps (Inbox - Primary, Starred, Read)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `received_at`, `created_at`) VALUES
(2, 1, 'Kavitha Murugan', 'kavitha@nkinfyxgroups.in', 'Q4 Webmail & Portal Modernization Roadmap',
'Hi Admin, Please review the attached modernization roadmap for nkinfyx groups web solutions. We have updated timelines for client deliverables.',
'<p>Hi Admin,</p><p>Please review our finalized <strong>Q4 Modernization Roadmap</strong>. We have aligned team milestones with customer project delivery schedules.</p><p>Key highlights include:</p><ol><li>Deployment of our responsive web client</li><li>Optimized database search indices</li><li>Multi-factor authentication integration</li></ol><p>Please let me know if you would like to make any adjustments before the Friday leadership sync.</p><p>Warm regards,<br>Kavitha Murugan<br><em>Lead Solutions Architect, nkinfyx groups</em></p>',
'Hi Admin, Please review the attached modernization roadmap for nkinfyx groups web solutions...',
NOW() - INTERVAL 2 HOUR, NOW() - INTERVAL 2 HOUR);

-- Message 3: Server Maintenance Notice (Inbox - Updates, Unread)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `received_at`, `created_at`) VALUES
(3, 1, 'CloudOps Cloud Notification', 'alerts@cloudops-nkinfyx.net', '[Notice] Scheduled Routine Database Maintenance this Sunday',
'A scheduled 15-minute maintenance window is scheduled on Sunday between 02:00 UTC and 02:15 UTC for index re-indexing and security patching.',
'<p>Hello Admin,</p><p>This is an automated notification regarding scheduled maintenance on <strong>nkinfyx database cluster-01</strong>.</p><p><strong>Maintenance Window:</strong> Sunday, 02:00 AM - 02:15 AM UTC<br><strong>Impact:</strong> Read-only operations during snapshot replication</p><p>No data loss or configuration changes will occur. Services will resume automatically.</p>',
'A scheduled 15-minute maintenance window is scheduled on Sunday between 02:00 UTC...',
NOW() - INTERVAL 5 HOUR, NOW() - INTERVAL 5 HOUR);

-- Message 4: Cloud Storage Promo (Inbox - Promotions, Read)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `received_at`, `created_at`) VALUES
(4, 1, 'Enterprise Cloud Deals', 'deals@enterprise-partners.com', 'Special 30% discount on dedicated storage arrays for nkinfyx',
'Claim your enterprise disk upgrade before the end of this month with our exclusive partner tier pricing.',
'<p>Dear nkinfyx Team,</p><p>As an enterprise tier customer, take advantage of <strong>30% savings</strong> on your high-speed SSD storage volume expansion.</p><p>Use corporate discount code <code>NKINFYX30</code> at checkout.</p>',
'Claim your enterprise disk upgrade before the end of this month with our exclusive partner...',
NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY);

-- Message 5: Sent Message (Sent folder)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `sent_at`, `created_at`) VALUES
(5, 1, 'nkinfyx Admin', 'adminnkinflyx@gmail.com', 'Approved: Vendor Onboarding contracts for TechSync LLC',
'Hi Finance, I have reviewed the onboarding documentation and approved the vendor agreement.',
'<p>Hi Finance Team,</p><p>I have reviewed the proposal and approved the onboarding agreement for <strong>TechSync LLC</strong>. Please process the initial retainer invoice.</p><p>Regards,<br>Admin</p>',
'Hi Finance, I have reviewed the onboarding documentation and approved the vendor agreement...',
NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY);

-- Message 6: Draft Message (Drafts folder)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `created_at`) VALUES
(6, 1, 'nkinfyx Admin', 'adminnkinflyx@gmail.com', 'Draft: Monthly Client Status Briefing',
'Draft notes: All milestones for the web application deployment are on track. Team productivity increased by 25% this sprint.',
'<p>Draft notes:</p><p>All milestones for the web application deployment are on track. Team productivity increased by 25% this sprint.</p>',
'Draft notes: All milestones for the web application deployment are on track...',
NOW() - INTERVAL 30 MINUTE);

-- Message 7: Starred Urgent Inquiry (Inbox - Primary, Starred, Read)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `received_at`, `created_at`) VALUES
(7, 1, 'Rajesh Kumar', 'rajesh.k@innovatetech.io', 'Urgent: API Integration requirements for Mobile App',
'Good morning Admin, We are preparing the mobile backend integration with nkinfyx portal endpoints. Can we set up a 15 min call today?',
'<p>Good morning Admin,</p><p>We are finishing the preliminary mobile client screens and need to finalize the authentication payload schemas.</p><p>Could we quickly hop on a 15-minute call today at 3:00 PM?</p><p>Best regards,<br>Rajesh Kumar</p>',
'Good morning Admin, We are preparing the mobile backend integration with nkinfyx portal...',
NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY);

-- Message 8: Spam Email (Spam folder)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `received_at`, `created_at`) VALUES
(8, 1, 'Global Lottery Syndicate', 'winner@random-jackpot-service.biz', 'You have won $5,000,000! Urgent response required',
'Congratulations! You were randomly selected for our international prize pool.',
'<p>Dear winner,</p><p>Please send your personal account details immediately to claim your award.</p>',
'Congratulations! You were randomly selected for our international prize pool...',
NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY);

-- Message 9: Trash Email (Trash folder)
INSERT INTO `messages` (`id`, `owner_user_id`, `sender_name`, `sender_email`, `subject`, `body_text`, `body_html`, `snippet`, `received_at`, `created_at`) VALUES
(9, 1, 'Weekly Newsletter Digest', 'digest@techtrends-weekly.com', 'Tech Trends Edition #89: Cloud Architecture Trends',
'Here are this week top stories in distributed databases and container security.',
'<p>Here are this week top stories in distributed databases and container security.</p>',
'Here are this week top stories in distributed databases and container security...',
NOW() - INTERVAL 6 DAY, NOW() - INTERVAL 6 DAY);

-- 4. Message Recipients
INSERT INTO `message_recipients` (`message_id`, `recipient_type`, `email`, `display_name`) VALUES
(1, 'to', 'adminnkinflyx@gmail.com', 'nkinfyx Admin'),
(2, 'to', 'adminnkinflyx@gmail.com', 'nkinfyx Admin'),
(3, 'to', 'adminnkinflyx@gmail.com', 'nkinfyx Admin'),
(4, 'to', 'adminnkinflyx@gmail.com', 'nkinfyx Admin'),
(5, 'to', 'finance@nkinfyxgroups.in', 'Finance Team'),
(5, 'cc', 'kavitha@nkinfyxgroups.in', 'Kavitha Murugan'),
(6, 'to', 'stakeholders@nkinfyxgroups.in', 'Project Stakeholders'),
(7, 'to', 'adminnkinflyx@gmail.com', 'nkinfyx Admin'),
(8, 'to', 'adminnkinflyx@gmail.com', 'nkinfyx Admin'),
(9, 'to', 'adminnkinflyx@gmail.com', 'nkinfyx Admin');

-- 5. Mailbox Items (Folders & statuses for User 1)
INSERT INTO `mailbox_items` (`user_id`, `message_id`, `folder`, `category`, `is_read`, `is_starred`, `is_important`, `created_at`) VALUES
(1, 1, 'inbox', 'primary', 0, 0, 1, NOW() - INTERVAL 10 MINUTE),
(1, 2, 'inbox', 'primary', 1, 1, 1, NOW() - INTERVAL 2 HOUR),
(1, 3, 'inbox', 'updates', 1, 0, 0, NOW() - INTERVAL 5 HOUR),
(1, 4, 'inbox', 'promotions', 1, 0, 0, NOW() - INTERVAL 1 DAY),
(1, 5, 'sent', 'primary', 1, 0, 0, NOW() - INTERVAL 3 DAY),
(1, 6, 'drafts', 'primary', 1, 0, 0, NOW() - INTERVAL 30 MINUTE),
(1, 7, 'inbox', 'primary', 1, 1, 1, NOW() - INTERVAL 1 DAY),
(1, 8, 'spam', 'primary', 0, 0, 0, NOW() - INTERVAL 4 DAY),
(1, 9, 'trash', 'primary', 1, 0, 0, NOW() - INTERVAL 6 DAY);

-- 6. Attachments
INSERT INTO `attachments` (`id`, `message_id`, `original_name`, `storage_name`, `mime_type`, `size_bytes`, `created_at`) VALUES
(1, 2, 'Q4_Modernization_Roadmap.pdf', 'att_sample_q4_roadmap.pdf', 'application/pdf', 245760, NOW() - INTERVAL 2 HOUR),
(2, 2, 'Architecture_Diagram.png', 'att_sample_architecture.png', 'image/png', 512000, NOW() - INTERVAL 2 HOUR);

-- 7. Assign Labels
INSERT INTO `message_labels` (`message_id`, `label_id`) VALUES
(1, 4), -- Welcome -> Company Updates
(2, 1), -- Roadmap -> Client Projects
(2, 3), -- Roadmap -> Priority High
(7, 1), -- API Inquiry -> Client Projects
(7, 3); -- API Inquiry -> Priority High
