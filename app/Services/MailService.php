<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\User;
use App\Models\Attachment;
use Exception;
use PDO;

class MailService
{
    /**
     * Sanitize HTML to prevent stored XSS attacks
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Strip scripts, iframes, objects, embeds, style tags
        $clean = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $clean = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $clean);
        $clean = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $clean);
        $clean = preg_replace('#<object(.*?)>(.*?)</object>#is', '', $clean);
        $clean = preg_replace('#<embed(.*?)>(.*?)</embed>#is', '', $clean);

        // Strip inline event handlers like onclick=, onload=, onerror=, etc.
        $clean = preg_replace('/(\son[a-zA-Z]+="[^"]*")/i', '', $clean);
        $clean = preg_replace("/(\son[a-zA-Z]+='[^']*')/i", '', $clean);
        $clean = preg_replace('/(\shref="javascript:[^"]*")/i', ' href="#"', $clean);
        $clean = preg_replace("/(\shref='javascript:[^']*')/i", " href='#'", $clean);

        return strip_tags($clean, '<p><br><b><i><u><strong><em><h1><h2><h3><h4><h5><h6><ul><ol><li><a><blockquote><pre><code><hr><table><tr><td><th><thead><tbody><span><div>');
    }

    /**
     * Send or Save Draft of a message
     */
    public static function saveOrSendMessage(
        int $userId,
        array $data,
        bool $isDraft = false,
        ?int $existingDraftId = null
    ): array {
        if (!$isDraft && !empty($data['is_draft'])) {
            $isDraft = true;
        }
        $pdo = Database::getConnection();
        $user = User::findById($userId);
        if (!$user) {
            throw new Exception("Authenticated user not found.");
        }

        $senderEmail = $user['email'];
        $senderName = $user['display_name'];

        $toRaw = trim($data['to'] ?? '');
        $subject = trim($data['subject'] ?? '') ?: ($isDraft ? '(Draft without subject)' : '(no subject)');
        $bodyHtml = self::sanitizeHtml($data['body_html'] ?? '');
        $bodyText = strip_tags($bodyHtml);
        $snippet = mb_substr(trim(preg_replace('/\s+/', ' ', $bodyText)), 0, 180);

        // Parse recipients
        $recipients = [];
        if (!empty($toRaw)) {
            $toAddresses = array_filter(array_map('trim', explode(',', $toRaw)));
            foreach ($toAddresses as $addr) {
                if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = ['type' => 'to', 'email' => strtolower($addr), 'name' => ''];
                }
            }
        }

        if (!$isDraft && empty($recipients)) {
            throw new Exception("Please specify at least one valid recipient email address.");
        }

        $pdo->beginTransaction();
        try {
            $messageId = $existingDraftId;

            if ($messageId) {
                // Verify ownership of existing draft
                $check = $pdo->prepare("SELECT id FROM messages WHERE id = :id AND owner_user_id = :uid LIMIT 1");
                $check->execute(['id' => $messageId, 'uid' => $userId]);
                if (!$check->fetchColumn()) {
                    $messageId = null; // Create new if invalid
                }
            }

            if ($messageId) {
                // Update existing message
                $stmt = $pdo->prepare("
                    UPDATE messages 
                    SET subject = :subject, body_text = :body_text, body_html = :body_html, snippet = :snippet, 
                        sent_at = COALESCE(:sent_at, sent_at), updated_at = NOW()
                    WHERE id = :id AND owner_user_id = :uid
                ");
                $stmt->execute([
                    'subject' => $subject,
                    'body_text' => $bodyText,
                    'body_html' => $bodyHtml,
                    'snippet' => $snippet,
                    'sent_at' => $isDraft ? null : date('Y-m-d H:i:s'),
                    'id' => $messageId,
                    'uid' => $userId
                ]);

                // Reset recipients for draft
                $pdo->prepare("DELETE FROM message_recipients WHERE message_id = :mid")->execute(['mid' => $messageId]);
            } else {
                // Insert new message
                $stmt = $pdo->prepare("
                    INSERT INTO messages (owner_user_id, sender_name, sender_email, subject, body_text, body_html, snippet, sent_at, created_at)
                    VALUES (:owner_user_id, :sender_name, :sender_email, :subject, :body_text, :body_html, :snippet, :sent_at, NOW())
                ");
                $stmt->execute([
                    'owner_user_id' => $userId,
                    'sender_name' => $senderName,
                    'sender_email' => $senderEmail,
                    'subject' => $subject,
                    'body_text' => $bodyText,
                    'body_html' => $bodyHtml,
                    'snippet' => $snippet,
                    'sent_at' => $isDraft ? null : date('Y-m-d H:i:s')
                ]);
                $messageId = (int)$pdo->lastInsertId();
            }

            // Insert recipients
            if (!empty($recipients)) {
                $recStmt = $pdo->prepare("INSERT INTO message_recipients (message_id, recipient_type, email, display_name) VALUES (:mid, :type, :email, :name)");
                foreach ($recipients as $rec) {
                    $recStmt->execute([
                        'mid' => $messageId,
                        'type' => $rec['type'],
                        'email' => $rec['email'],
                        'name' => $rec['name']
                    ]);
                }
            }

            // Save attachments if any provided
            if (!empty($data['attachment_ids']) && is_array($data['attachment_ids'])) {
                $attStmt = $pdo->prepare("
                    UPDATE attachments 
                    SET message_id = :mid, user_id = :uid 
                    WHERE id = :att_id AND (message_id IS NULL OR message_id = 0)
                ");
                foreach ($data['attachment_ids'] as $attId) {
                    $attStmt->execute(['mid' => $messageId, 'uid' => $userId, 'att_id' => (int)$attId]);
                }
            }

            // Mailbox folder assignment
            $targetFolder = $isDraft ? 'drafts' : 'sent';
            
            // Delete old mailbox_item if it was a draft being converted to sent
            $pdo->prepare("DELETE FROM mailbox_items WHERE user_id = :uid AND message_id = :mid")->execute([
                'uid' => $userId,
                'mid' => $messageId
            ]);

            // Add to sender's mailbox
            $mbStmt = $pdo->prepare("
                INSERT INTO mailbox_items (user_id, message_id, folder, category, is_read, is_starred, created_at)
                VALUES (:uid, :mid, :folder, 'primary', 1, 0, NOW())
            ");
            $mbStmt->execute([
                'uid' => $userId,
                'mid' => $messageId,
                'folder' => $targetFolder
            ]);

            $smtpResultNote = '';

            // If this is a real Send (not draft):
            if (!$isDraft) {
                $deliveredInternally = 0;
                // If any recipient is a local user in this app, deliver to their Inbox!
                $userCheckStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                $inboxDeliverStmt = $pdo->prepare("
                    INSERT INTO mailbox_items (user_id, message_id, folder, category, is_read, is_starred, created_at)
                    VALUES (:uid, :mid, 'inbox', 'primary', 0, 0, NOW())
                ");

                foreach ($recipients as $rec) {
                    $userCheckStmt->execute(['email' => $rec['email']]);
                    $localUser = $userCheckStmt->fetch();
                    if ($localUser && (int)$localUser['id'] !== $userId) {
                        $inboxDeliverStmt->execute([
                            'uid' => (int)$localUser['id'],
                            'mid' => $messageId
                        ]);
                        $deliveredInternally++;
                    }
                }

                // Attempt SMTP send if enabled
                $smtp = new SmtpTransport();
                if ($smtp->isConfigured()) {
                    try {
                        // Gather attachments for SMTP
                        $atts = Attachment::getByMessageId($messageId);
                        $smtpAtts = [];
                        foreach ($atts as $a) {
                            $filePath = StorageService::getFilePath($a['storage_name']);
                            if ($filePath) {
                                $smtpAtts[] = [
                                    'path' => $filePath,
                                    'name' => $a['original_name'],
                                    'mime' => $a['mime_type']
                                ];
                            }
                        }

                        $smtpFrom = (string)\App\Core\Env::get('SMTP_FROM_ADDRESS', '') ?: (string)\App\Core\Env::get('SMTP_USERNAME', '') ?: $senderEmail;
                        $smtpFromName = (string)\App\Core\Env::get('SMTP_FROM_NAME', 'Novalyx Technologies');

                        $smtp->send($smtpFrom, $smtpFromName, $recipients, $subject, $bodyHtml, $bodyText, $smtpAtts);
                        $smtpResultNote = "Email sent and delivered successfully to recipient inbox via Google SMTP!";
                    } catch (Exception $e) {
                        throw new Exception("SMTP Delivery Failed: " . $e->getMessage());
                    }
                } else {
                    if ($deliveredInternally > 0) {
                        $smtpResultNote = "Message delivered to internal recipient(s) inbox.";
                    } else {
                        $smtpResultNote = "Saved to Sent folder. (External SMTP is currently disabled; configure your SMTP in Settings to deliver to real external inboxes).";
                    }
                }
            }

            $pdo->commit();

            return [
                'message_id' => $messageId,
                'status' => $isDraft ? 'draft_saved' : 'sent',
                'notice' => $smtpResultNote,
                'subject' => $subject,
                'saved_at' => date('H:i:s')
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
