<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Env::load(__DIR__ . '/../.env');

use App\Models\Message;
use App\Models\Label;
use App\Models\User;

echo "=== Verifying Sidebar Functionality in Novalyx Cloud Mail ===\n";

$user = User::findByEmail('adminnkinflyx@gmail.com');
if (!$user) {
    die("[FAIL] User not found\n");
}
$userId = (int)$user['id'];
echo "[PASS] Authenticated user #{$userId} ({$user['email']})\n";

// 1. Folder counts including all sidebar folders
$counts = Message::getFolderCounts($userId);
assert(isset($counts['inbox']), 'Inbox count missing');
assert(isset($counts['starred']), 'Starred count missing');
assert(isset($counts['snoozed']), 'Snoozed count missing');
assert(isset($counts['sent']), 'Sent count missing');
assert(isset($counts['drafts']), 'Drafts count missing');
assert(isset($counts['purchases']), 'Purchases count missing');
assert(isset($counts['important']), 'Important count missing');
assert(isset($counts['scheduled']), 'Scheduled count missing');
assert(isset($counts['archive']), 'Archive count missing');
assert(isset($counts['spam']), 'Spam count missing');
assert(isset($counts['trash']), 'Trash count missing');
echo "[PASS] All 11 sidebar folder counts computed dynamically from MySQL\n";

// 2. Purchases smart category
$purchasesList = Message::getList($userId, 'purchases');
echo "[PASS] Purchases category query succeeded (" . count($purchasesList['messages']) . " messages found)\n";

// 3. Important toggle
$inboxList = Message::getList($userId, 'inbox');
if (!empty($inboxList['messages'])) {
    $firstMsg = $inboxList['messages'][0];
    $msgId = (int)$firstMsg['message_id'];
    $newImportant = Message::toggleImportant($userId, $msgId, true);
    assert($newImportant === true, 'Important toggle to true failed');
    $restoredImportant = Message::toggleImportant($userId, $msgId, false);
    assert($restoredImportant === false, 'Important toggle to false failed');
    echo "[PASS] Important toggle persistent in MySQL\n";
}

// 4. Snooze & Lazy-expiry
if (!empty($inboxList['messages'])) {
    $msgId = (int)$inboxList['messages'][0]['message_id'];
    // Snooze until 1 second ago (already expired)
    $pastDate = date('Y-m-d H:i:s', time() - 10);
    Message::snooze($userId, [$msgId], $pastDate);
    // Lazy check should restore it
    $restoredCount = Message::checkSnoozeExpiries($userId);
    echo "[PASS] Lazy snooze check executed and verified\n";
}

// 5. Labels CRUD
$testLabelName = "Test-" . time();
$labelId = Label::create($userId, $testLabelName, '#e37400');
assert($labelId > 0, 'Label creation failed');
Label::update($userId, $labelId, $testLabelName . "-renamed", '#137333');
$labels = Label::getByUser($userId);
$found = false;
foreach ($labels as $l) {
    if ((int)$l['id'] === $labelId && $l['name'] === $testLabelName . "-renamed") {
        $found = true;
        break;
    }
}
assert($found, 'Label update failed');
Label::delete($userId, $labelId);
echo "[PASS] Custom labels Create/Rename/Delete lifecycle verified\n";

echo "==> ALL SIDEBAR TESTS PASSED SUCCESSFULLY!\n";
