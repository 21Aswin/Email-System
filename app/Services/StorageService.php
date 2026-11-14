<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use Exception;

class StorageService
{
    private static function getStorageDir(): string
    {
        $base = dirname(__DIR__, 2);
        $dir = $base . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'attachments';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public static function storeUploadedFile(array $file): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception("Invalid file upload parameters.");
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception("Uploaded file exceeds allowed size limit.");
            default:
                throw new Exception("Failed to upload file. Error code: " . $file['error']);
        }

        $maxMb = (int)Env::get('MAX_ATTACHMENT_SIZE_MB', 15);
        if ($file['size'] > ($maxMb * 1024 * 1024)) {
            throw new Exception("File size exceeds maximum allowed {$maxMb}MB limit.");
        }

        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedExts = explode(',', (string)Env::get('ALLOWED_EXTENSIONS', 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif,webp,zip,txt'));
        $allowedExts = array_map('trim', $allowedExts);

        if (!in_array($ext, $allowedExts, true)) {
            throw new Exception("File extension .{$ext} is not allowed for security reasons.");
        }

        // Validate MIME type safely
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']) ?: 'application/octet-stream';
        finfo_close($finfo);

        // Disallow executable MIME types
        $dangerousMimes = [
            'application/x-php', 'text/x-php', 'application/x-executable',
            'application/x-msdos-program', 'application/x-sh', 'application/x-bat'
        ];
        if (in_array(strtolower($mimeType), $dangerousMimes, true)) {
            throw new Exception("Executable files cannot be uploaded.");
        }

        // Generate safe randomized filename
        $storageName = 'att_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = self::getStorageDir() . DIRECTORY_SEPARATOR . $storageName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to save uploaded attachment to disk.");
        }

        return [
            'original_name' => $originalName,
            'storage_name' => $storageName,
            'mime_type' => $mimeType,
            'size_bytes' => $file['size'],
            'path' => $targetPath
        ];
    }

    public static function getFilePath(string $storageName): ?string
    {
        $safeName = basename($storageName);
        $path = self::getStorageDir() . DIRECTORY_SEPARATOR . $safeName;
        return file_exists($path) ? $path : null;
    }
}
