<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Env::load(__DIR__ . '/../.env');

echo "=== Running nkinfyx groups Webmail Automated Verification ===\n";

$passed = 0;
$total = 0;

function assertTest(string $name, bool $condition): void {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] {$name}\n";
    } else {
        echo " [FAIL] {$name}\n";
    }
}

// 1. Auth & Password verification
$adminUser = \App\Models\User::findByEmail('adminnkinflyx@gmail.com');
assertTest("Find seed user adminnkinflyx@gmail.com", $adminUser !== null);
assertTest("Verify seed password for admin", $adminUser && password_verify('password123', $adminUser['password_hash']));

// 2. Folder Counts
$counts = \App\Models\Message::getFolderCounts((int)$adminUser['id']);
assertTest("Folder counts retrieval", isset($counts['inbox']) && isset($counts['sent']));
assertTest("Inbox has items", $counts['inbox']['total'] > 0);

// 3. Message List & Labels
$inboxData = \App\Models\Message::getList((int)$adminUser['id'], 'inbox', 'primary', null, '', [], 1, 10);
assertTest("Message list query returns items", count($inboxData['messages']) > 0);
assertTest("Labels are populated on messages", isset($inboxData['messages'][0]['labels']));

// 4. HTML Sanitizer
$maliciousHtml = '<p>Normal text</p><script>alert("hacked")</script><img src="x" onerror="alert(1)"><a href="javascript:alert(2)">click</a>';
$cleanHtml = \App\Services\MailService::sanitizeHtml($maliciousHtml);
assertTest("HTML Sanitizer removes <script>", !str_contains($cleanHtml, '<script'));
assertTest("HTML Sanitizer removes onerror attribute", !str_contains($cleanHtml, 'onerror'));
assertTest("HTML Sanitizer neutralizes javascript: links", !str_contains($cleanHtml, 'javascript:'));

// 5. Draft auto-save test
$draftResult = \App\Services\MailService::saveOrSendMessage(
    (int)$adminUser['id'],
    [
        'to' => 'kavitha@nkinfyxgroups.in',
        'subject' => 'Test Automated Draft Subject',
        'body_html' => '<p>This is a test draft body from automated test suite.</p>'
    ],
    true // isDraft = true
);
assertTest("Draft creation succeeds", isset($draftResult['message_id']) && $draftResult['status'] === 'draft_saved');

// 6. Attachment security check
$attCheck = \App\Models\Attachment::findByIdForUser(1, (int)$adminUser['id']);
assertTest("Authorized attachment access", $attCheck !== null && $attCheck['original_name'] === 'Q4_Modernization_Roadmap.pdf');

$unauthorizedAtt = \App\Models\Attachment::findByIdForUser(1, 99999);
assertTest("Unauthorized user blocked from accessing another user's attachment", $unauthorizedAtt === null);

echo "\nSummary: {$passed} / {$total} tests passed.\n";

if ($passed === $total) {
    echo "==> ALL VERIFICATION CHECKS PASSED!\n";
    exit(0);
} else {
    echo "==> SOME TESTS FAILED.\n";
    exit(1);
}
