<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Attachment;
use App\Services\AuthService;
use App\Services\StorageService;
use Exception;

class AttachmentController
{
    public function upload(): void
    {
        $userId = (int)AuthService::id();
        
        if (empty($_FILES['attachment'])) {
            Response::error('No attachment file received.', 400);
            return;
        }

        try {
            $saved = StorageService::storeUploadedFile($_FILES['attachment']);
            
            // Insert attachment record linked initially to message_id = null (unattached draft)
            $attId = Attachment::create(
                null,
                $saved['original_name'],
                $saved['storage_name'],
                $saved['mime_type'],
                $saved['size_bytes'],
                $userId
            );

            Response::success('File uploaded successfully.', [
                'attachment_id' => $attId,
                'name' => $saved['original_name'],
                'size' => $saved['size_bytes'],
                'mime' => $saved['mime_type']
            ]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function download(array $params): void
    {
        $userId = (int)AuthService::id();
        $attachmentId = (int)($params['id'] ?? 0);

        if (!$attachmentId) {
            Response::error('Invalid attachment ID.', 400);
            return;
        }

        // Authorize: attachment must belong to a message accessible by this user
        $attachment = Attachment::findByIdForUser($attachmentId, $userId);
        if (!$attachment) {
            Response::error('Attachment not found or access denied.', 403);
            return;
        }

        $filePath = StorageService::getFilePath($attachment['storage_name']);
        if (!$filePath || !file_exists($filePath)) {
            Response::error('File not found on storage.', 404);
            return;
        }

        // Send headers for secure download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $attachment['mime_type']);
        header('Content-Disposition: attachment; filename="' . addslashes($attachment['original_name']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        // Prevent output buffering interference
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        readfile($filePath);
        exit;
    }
}
