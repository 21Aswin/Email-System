<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Env;
use App\Models\User;
use Exception;
use PDO;

class ImapSyncService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private int $timeout = 10;

    public function __construct()
    {
        $this->host = 'imap.gmail.com';
        $this->port = 993;
        $this->username = trim((string)Env::get('SMTP_USERNAME', ''));
        $this->password = str_replace(' ', '', trim((string)Env::get('SMTP_PASSWORD', '')));
    }

    public function isConfigured(): bool
    {
        $enabled = filter_var(Env::get('SMTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        return $enabled && !empty($this->username) && !empty($this->password);
    }

    /**
     * Sync incoming replies and emails from Gmail IMAP into the user's Inbox
     */
    public function syncInbox(int $userId): array
    {
        if (!$this->isConfigured()) {
            return [
                'synced' => 0,
                'message' => 'IMAP/SMTP is not enabled. Configure your credentials in Settings to sync live replies.'
            ];
        }

        $socket = @stream_socket_client(
            "ssl://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            throw new Exception("Could not connect to IMAP server: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, $this->timeout);
        fgets($socket, 512); // Banner

        // 1. Login
        fwrite($socket, "A1 LOGIN {$this->username} {$this->password}\r\n");
        $loginResp = $this->readTaggedResponse($socket, 'A1');
        if (!str_contains($loginResp, 'A1 OK')) {
            fclose($socket);
            throw new Exception("IMAP Login failed: " . trim($loginResp));
        }

        // 2. Select INBOX
        fwrite($socket, "A2 SELECT INBOX\r\n");
        $selectResp = $this->readTaggedResponse($socket, 'A2');
        
        // Extract total message count: e.g. "* 11836 EXISTS"
        $totalMessages = 0;
        if (preg_match('/\* (\d+) EXISTS/i', $selectResp, $matches)) {
            $totalMessages = (int)$matches[1];
        }

        if ($totalMessages === 0) {
            fwrite($socket, "A3 LOGOUT\r\n");
            fclose($socket);
            return ['synced' => 0, 'message' => 'No messages found in remote mailbox.'];
        }

        // Check the most recent 10 messages (e.g. from total - 9 to total)
        $startSeq = max(1, $totalMessages - 9);
        $fetchRange = "{$startSeq}:{$totalMessages}";

        // Fetch headers and peek body for the range
        fwrite($socket, "A3 FETCH {$fetchRange} (RFC822.HEADER BODY.PEEK[TEXT])\r\n");
        
        $rawFetchData = '';
        while ($line = fgets($socket, 4096)) {
            $rawFetchData .= $line;
            if (str_starts_with($line, 'A3 ')) {
                break;
            }
        }

        // Logout
        fwrite($socket, "A4 LOGOUT\r\n");
        fclose($socket);

        // Parse individual messages from fetch output
        $syncedCount = $this->processFetchedMessages($userId, $rawFetchData);

        return [
            'synced' => $syncedCount,
            'message' => $syncedCount > 0 
                ? "Synced {$syncedCount} new email(s) into your Inbox!" 
                : "Inbox is up to date (no new replies found)."
        ];
    }

    private function readTaggedResponse($socket, string $tag): string
    {
        $resp = '';
        while ($line = fgets($socket, 1024)) {
            $resp .= $line;
            if (str_starts_with($line, $tag . ' ')) {
                break;
            }
        }
        return $resp;
    }

    private function processFetchedMessages(int $userId, string $rawData): int
    {
        $pdo = Database::getConnection();
        $synced = 0;

        // Split by fetch responses: "* <id> FETCH ("
        $chunks = preg_split('/\* \d+ FETCH /i', $rawData);

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (empty($chunk)) continue;

            $headers = $this->parseHeaders($chunk);
            if (empty($headers['from']) || empty($headers['date'])) {
                continue;
            }

            $subject = $headers['subject'] ?? '(no subject)';
            $senderRaw = $headers['from'];
            $messageIdHeader = $headers['message-id'] ?? '';
            $date = date('Y-m-d H:i:s', strtotime($headers['date'] ?? 'now'));

            // Parse Sender Name and Email
            $senderEmail = '';
            $senderName = '';
            if (preg_match('/(.*?)<([^>]+)>/', $senderRaw, $m)) {
                $senderName = trim($m[1], " \t\n\r\0\x0B\"'");
                $senderEmail = strtolower(trim($m[2]));
            } else {
                $senderEmail = strtolower(trim($senderRaw));
                $senderName = $senderEmail;
            }

            if (empty($senderEmail) || !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Don't sync messages sent by our own SMTP account into the Inbox
            if (strtolower($senderEmail) === strtolower($this->username)) {
                continue;
            }

            // Extract Body text from chunk
            $rawBody = '';
            if (preg_match('/BODY\[TEXT\]\s*\{(\d+)\}\r?\n(.*?)(?=\)\r?\n|\Z)/s', $chunk, $b)) {
                $rawBody = $b[2];
            } else {
                $rawBody = strip_tags($chunk);
            }

            // Parse clean Plain text, HTML and Snippet from raw MIME / multipart
            $parsedContent = $this->parseMimeContent($rawBody, $headers);
            $bodyText = iconv('UTF-8', 'UTF-8//IGNORE', mb_convert_encoding($parsedContent['plain'], 'UTF-8', 'UTF-8'));
            $cleanHtml = iconv('UTF-8', 'UTF-8//IGNORE', mb_convert_encoding($parsedContent['html'], 'UTF-8', 'UTF-8'));
            $bodyHtml = $cleanHtml ? MailService::sanitizeHtml($cleanHtml) : nl2br(htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8'));
            $snippet = iconv('UTF-8', 'UTF-8//IGNORE', mb_convert_encoding($parsedContent['snippet'], 'UTF-8', 'UTF-8'));

            // Check if this message already exists in database for this user
            $checkStmt = $pdo->prepare("
                SELECT id FROM messages 
                WHERE owner_user_id = :uid 
                  AND sender_email = :sender 
                  AND subject = :subj 
                  AND created_at BETWEEN :dt_start AND :dt_end
                LIMIT 1
            ");
            $dtStart = date('Y-m-d H:i:s', strtotime($date) - 60);
            $dtEnd = date('Y-m-d H:i:s', strtotime($date) + 60);
            $checkStmt->execute([
                'uid' => $userId,
                'sender' => $senderEmail,
                'subj' => $subject,
                'dt_start' => $dtStart,
                'dt_end' => $dtEnd
            ]);

            if ($checkStmt->fetchColumn()) {
                continue; // Already synced!
            }

            // Insert new incoming message
            $pdo->beginTransaction();
            try {
                $insMsg = $pdo->prepare("
                    INSERT INTO messages (owner_user_id, sender_name, sender_email, subject, body_text, body_html, snippet, received_at, created_at)
                    VALUES (:uid, :sname, :semail, :subj, :btext, :bhtml, :snip, :recv, :created)
                ");
                $insMsg->execute([
                    'uid' => $userId,
                    'sname' => $senderName ?: $senderEmail,
                    'semail' => $senderEmail,
                    'subj' => $subject,
                    'btext' => $bodyText,
                    'bhtml' => $bodyHtml,
                    'snip' => $snippet,
                    'recv' => $date,
                    'created' => $date
                ]);
                $msgId = (int)$pdo->lastInsertId();

                // Recipient
                $insRec = $pdo->prepare("INSERT INTO message_recipients (message_id, recipient_type, email, display_name) VALUES (:mid, 'to', :to, '')");
                $insRec->execute([
                    'mid' => $msgId,
                    'to' => $this->username
                ]);

                // Mailbox Item (Inbox - Unread)
                $insMb = $pdo->prepare("
                    INSERT INTO mailbox_items (user_id, message_id, folder, category, is_read, is_starred, created_at)
                    VALUES (:uid, :mid, 'inbox', 'primary', 0, 0, :created)
                ");
                $insMb->execute([
                    'uid' => $userId,
                    'mid' => $msgId,
                    'created' => $date
                ]);

                $pdo->commit();
                $synced++;
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        }

        return $synced;
    }

    private function parseHeaders(string $chunk): array
    {
        $headers = [];
        $headerSection = $chunk;
        if (preg_match('/^(.*?)(?:\r?\n\r?\n|\)\r?\nBODY\[)/s', $chunk, $hm)) {
            $headerSection = $hm[1];
        }

        $lines = explode("\n", $headerSection);
        $currentKey = '';

        foreach ($lines as $line) {
            $line = rtrim($line, "\r");
            if (empty($line)) continue;

            if (preg_match('/^([a-zA-Z\-]+):\s*(.*)$/', $line, $m)) {
                $currentKey = strtolower($m[1]);
                $headers[$currentKey] = $m[2];
            } elseif (!empty($currentKey) && (str_starts_with($line, ' ') || str_starts_with($line, "\t"))) {
                $headers[$currentKey] .= ' ' . trim($line);
            }
        }

        // Decode MIME headers (e.g. =?UTF-8?B?...?=)
        foreach ($headers as $k => &$v) {
            $v = $this->decodeMimeHeader($v);
        }

        return $headers;
    }

    private function decodeMimeHeader(string $str): string
    {
        if (str_contains($str, '=?')) {
            if (function_exists('iconv_mime_decode')) {
                $decoded = iconv_mime_decode($str, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
                if ($decoded !== false) {
                    return $decoded;
                }
            }
            if (function_exists('mb_decode_mime_header')) {
                return mb_decode_mime_header($str);
            }
        }
        return $str;
    }

    private function parseMimeContent(string $rawBody, array $headers = []): array
    {
        $plainText = '';
        $htmlContent = '';

        // Check for boundary in headers or in body
        $boundary = '';
        if (!empty($headers['content-type']) && preg_match('/boundary="?([^";\s]+)"?/i', $headers['content-type'], $m)) {
            $boundary = $m[1];
        }
        if (empty($boundary) && preg_match('/^--([a-zA-Z0-9_\-\.\:\=]+)/m', $rawBody, $m)) {
            $boundary = $m[1];
        }

        if (!empty($boundary)) {
            $parts = explode("--" . $boundary, $rawBody);
            foreach ($parts as $part) {
                $part = trim($part);
                if (empty($part) || $part === '--') continue;

                // Split headers and body of this subpart
                $pieces = preg_split('/\r?\n\r?\n/', $part, 2);
                $partHeadersRaw = $pieces[0] ?? '';
                $partBody = $pieces[1] ?? '';

                $partContentType = 'text/plain';
                if (preg_match('/Content-Type:\s*([^;\r\n]+)/i', $partHeadersRaw, $cm)) {
                    $partContentType = strtolower(trim($cm[1]));
                }

                $partEncoding = '';
                if (preg_match('/Content-Transfer-Encoding:\s*([^\r\n]+)/i', $partHeadersRaw, $em)) {
                    $partEncoding = strtolower(trim($em[1]));
                }

                // Decode body
                if ($partEncoding === 'quoted-printable') {
                    $partBody = quoted_printable_decode($partBody);
                } elseif ($partEncoding === 'base64') {
                    $partBody = base64_decode(trim($partBody));
                }

                if (str_contains($partContentType, 'text/html')) {
                    $htmlContent = $partBody;
                } elseif (str_contains($partContentType, 'text/plain')) {
                    $plainText = $partBody;
                }
            }
        } else {
            // Non-multipart body
            $encoding = strtolower($headers['content-transfer-encoding'] ?? '');
            $body = $rawBody;
            if ($encoding === 'quoted-printable') {
                $body = quoted_printable_decode($body);
            } elseif ($encoding === 'base64') {
                $body = base64_decode(trim($body));
            }

            $contentType = strtolower($headers['content-type'] ?? 'text/plain');
            if (str_contains($contentType, 'text/html')) {
                $htmlContent = $body;
            } else {
                $plainText = $body;
            }
        }

        if (empty($plainText) && !empty($htmlContent)) {
            $plainText = trim(strip_tags($htmlContent));
        }
        if (empty($htmlContent) && !empty($plainText)) {
            $htmlContent = nl2br(htmlspecialchars($plainText, ENT_QUOTES, 'UTF-8'));
        }

        $snippet = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($plainText ?: $htmlContent))), 0, 180);

        return [
            'plain' => $plainText,
            'html' => $htmlContent,
            'snippet' => $snippet
        ];
    }
}
