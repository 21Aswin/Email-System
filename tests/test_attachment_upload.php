<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Env::load(__DIR__ . '/../.env');

use App\Models\Attachment;
use App\Models\Message;
use App\Models\User;
use App\Services\MailService;

echo "=== Verifying Attachment & Photo Upload Fix ===\n";

$user = User::findByEmail('adminnkinflyx@gmail.com');
if (!$user) {
    die("[FAIL] Test user adminnkinflyx@gmail.com not found\n");
}
$userId = (int)$user['id'];
echo "[PASS] Authenticated user #{$userId} ({$user['email']})\n";

// Step 1: Simulate uploading a photo/document without an initial message (message_id = null)
try {
    $attId = Attachment::create(
        null,
        'sample_photo.jpg',
        'att_test_sample_' . time() . '.jpg',
        'image/jpeg',
        204800, // 200 KB
        $userId
    );
    echo "[PASS] Attachment::create(null, ...) succeeded! Created attachment #{$attId}\n";
} catch (Exception $e) {
    die("[FAIL] Attachment::create failed: " . $e->getMessage() . "\n");
}

// Step 2: Verify database record has message_id IS NULL and user_id set
$pdo = \App\Core\Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM attachments WHERE id = :id");
$stmt->execute(['id' => $attId]);
$attRow = $stmt->fetch();
assert($attRow !== false, 'Attachment row not found');
assert($attRow['message_id'] === null, 'message_id must be null for unattached upload');
assert((int)$attRow['user_id'] === $userId, 'user_id must match uploading user');
echo "[PASS] Database record confirmed: message_id is NULL, user_id is {$userId}\n";

// Step 3: Access check before message attachment (authorized to owner)
$foundForOwner = Attachment::findByIdForUser($attId, $userId);
assert($foundForOwner !== null, 'Owner must be able to access their draft attachment');
$unauthorizedCheck = Attachment::findByIdForUser($attId, 999999);
assert($unauthorizedCheck === null, 'Unauthorized user must NOT be able to access draft attachment');
echo "[PASS] Authorization checks passed for draft attachment\n";

// Step 4: Create a draft message with this attachment
$result = MailService::saveOrSendMessage($userId, [
    'to' => 'recipient@example.com',
    'subject' => 'Test Draft with Photo Attachment',
    'body_html' => '<p>Here is the attached sample photo.</p>',
    'body_plain' => 'Here is the attached sample photo.',
    'is_draft' => true,
    'attachment_ids' => [$attId]
], true);

assert(!empty($result['message_id']), 'Message creation failed');
$msgId = (int)$result['message_id'];
echo "[PASS] Draft message created #{$msgId} with attachment #{$attId}\n";

// Step 5: Verify that the attachment is now linked to message #$msgId
$stmt->execute(['id' => $attId]);
$updatedAttRow = $stmt->fetch();
assert((int)$updatedAttRow['message_id'] === $msgId, 'Attachment must now be linked to message_id');
echo "[PASS] Attachment is now properly linked to message #{$msgId}\n";

// Step 6: Verify reading message details includes this attachment
$msgDetails = Message::getDetails($userId, $msgId);
assert(!empty($msgDetails['attachments']), 'Attachments array must not be empty');
assert((int)$msgDetails['attachments'][0]['id'] === $attId, 'Attachment ID in message details mismatch');
echo "[PASS] Message details successfully retrieved attached photo: {$msgDetails['attachments'][0]['original_name']}\n";

// Clean up test message and attachment
$pdo->prepare("DELETE FROM messages WHERE id = :id")->execute(['id' => $msgId]);
$pdo->prepare("DELETE FROM attachments WHERE id = :id")->execute(['id' => $attId]);
echo "[PASS] Cleaned up test data.\n";

echo "\n==> ALL ATTACHMENT UPLOAD TESTS PASSED SUCCESSFULLY!\n";
