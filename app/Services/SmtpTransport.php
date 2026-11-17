<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use Exception;

/**
 * Clean native socket-based SMTP transport for sending real emails
 * without requiring third-party library dependencies.
 */
class SmtpTransport
{
    private string $host;
    private int $port;
    private string $encryption;
    private string $username;
    private string $password;
    private int $timeout = 15;

    public function __construct()
    {
        $this->host = Env::get('SMTP_HOST', 'smtp.gmail.com');
        $this->port = (int)Env::get('SMTP_PORT', 587);
        $this->encryption = strtolower((string)Env::get('SMTP_ENCRYPTION', 'tls'));
        $this->username = trim((string)Env::get('SMTP_USERNAME', ''));
        $this->password = str_replace(' ', '', trim((string)Env::get('SMTP_PASSWORD', '')));
    }

    public function isConfigured(): bool
    {
        $enabled = filter_var(Env::get('SMTP_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        return $enabled && !empty($this->host) && !empty($this->username) && !empty($this->password);
    }

    public function send(
        string $fromEmail,
        string $fromName,
        array $toRecipients,
        string $subject,
        string $bodyHtml,
        string $bodyText,
        array $attachments = []
    ): bool {
        if (!$this->isConfigured()) {
            throw new Exception("SMTP is not configured in .env. Please set SMTP_ENABLED=true, SMTP_HOST, and credentials.");
        }

        $remoteSocket = ($this->encryption === 'ssl' ? 'ssl://' : '') . $this->host . ':' . $this->port;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        $socket = @stream_socket_client($remoteSocket, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context);

        if (!$socket) {
            throw new Exception("Could not connect to SMTP host: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, $this->timeout);
        $this->readResponse($socket, 220);

        // EHLO
        $localhost = gethostname() ?: 'localhost';
        $this->sendCommand($socket, "EHLO {$localhost}", 250);

        // STARTTLS if TLS
        if ($this->encryption === 'tls') {
            $this->sendCommand($socket, "STARTTLS", 220);
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                fclose($socket);
                throw new Exception("Failed to establish TLS encryption with SMTP server.");
            }
            $this->sendCommand($socket, "EHLO {$localhost}", 250);
        }

        // AUTH LOGIN
        $this->sendCommand($socket, "AUTH LOGIN", 334);
        $this->sendCommand($socket, base64_encode($this->username), 334);
        $this->sendCommand($socket, base64_encode($this->password), 235);

        // MAIL FROM
        $this->sendCommand($socket, "MAIL FROM: <{$fromEmail}>", 250);

        // RCPT TO
        foreach ($toRecipients as $recipient) {
            $rcptEmail = is_array($recipient) ? ($recipient['email'] ?? '') : (string)$recipient;
            if (!empty($rcptEmail)) {
                $this->sendCommand($socket, "RCPT TO: <{$rcptEmail}>", 250);
            }
        }

        // DATA
        $this->sendCommand($socket, "DATA", 354);

        $boundary = "----=_Part_" . bin2hex(random_bytes(8));
        $altBoundary = "----=_Alt_" . bin2hex(random_bytes(8));

        $senderDomain = substr(strrchr($fromEmail, "@"), 1) ?: 'gmail.com';
        $messageIdString = "<" . bin2hex(random_bytes(12)) . "." . time() . "@" . $senderDomain . ">";

        $headers = [];
        $headers[] = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>";
        $headers[] = "Reply-To: <{$fromEmail}>";
        $headers[] = "Return-Path: <{$fromEmail}>";
        
        $toList = [];
        foreach ($toRecipients as $r) {
            $email = is_array($r) ? ($r['email'] ?? '') : (string)$r;
            $name = is_array($r) ? ($r['name'] ?? $r['display_name'] ?? '') : '';
            if (!empty($email)) {
                $toList[] = !empty($name) ? "=?UTF-8?B?" . base64_encode($name) . "?= <{$email}>" : "<{$email}>";
            }
        }
        $headers[] = "To: " . implode(', ', $toList);
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "Date: " . date('r');
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Message-ID: {$messageIdString}";
        $headers[] = "X-Priority: 3";

        if (empty($attachments)) {
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$altBoundary}\"";
            $messageBody = "--{$altBoundary}\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n\r\n" .
                $bodyText . "\r\n\r\n" .
                "--{$altBoundary}\r\n" .
                "Content-Type: text/html; charset=UTF-8\r\n\r\n" .
                $bodyHtml . "\r\n\r\n" .
                "--{$altBoundary}--\r\n";
        } else {
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";
            $messageBody = "--{$boundary}\r\n" .
                "Content-Type: multipart/alternative; boundary=\"{$altBoundary}\"\r\n\r\n" .
                "--{$altBoundary}\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n\r\n" .
                $bodyText . "\r\n\r\n" .
                "--{$altBoundary}\r\n" .
                "Content-Type: text/html; charset=UTF-8\r\n\r\n" .
                $bodyHtml . "\r\n\r\n" .
                "--{$altBoundary}--\r\n";

            foreach ($attachments as $att) {
                if (file_exists($att['path'])) {
                    $fileContent = chunk_split(base64_encode(file_get_contents($att['path'])));
                    $messageBody .= "--{$boundary}\r\n" .
                        "Content-Type: {$att['mime']}; name=\"{$att['name']}\"\r\n" .
                        "Content-Transfer-Encoding: base64\r\n" .
                        "Content-Disposition: attachment; filename=\"{$att['name']}\"\r\n\r\n" .
                        $fileContent . "\r\n";
                }
            }
            $messageBody .= "--{$boundary}--\r\n";
        }

        $fullPayload = implode("\r\n", $headers) . "\r\n\r\n" . $messageBody . "\r\n.\r\n";
        fwrite($socket, $fullPayload);
        $this->readResponse($socket, 250);

        // QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }

    private function sendCommand($socket, string $cmd, int $expectedCode): string
    {
        fwrite($socket, $cmd . "\r\n");
        return $this->readResponse($socket, $expectedCode);
    }

    private function readResponse($socket, int $expectedCode): string
    {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }

        $code = (int)substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new Exception("SMTP error [Expected {$expectedCode}, received {$code}]: " . trim($response));
        }

        return $response;
    }
}
