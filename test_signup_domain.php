<?php
require_once __DIR__ . '/includes/db.php';

$test_emails = [
    'test' . time() . '@gmail.com',
    'test' . time() . '@outlook.com',
    'test' . time() . '@mindcare.app'
];

foreach ($test_emails as $email) {
    echo "Testing signup with: $email\n";
    $res = $supabase->signUp($email, 'Password123!', ['name' => 'Tester']);
    echo "Status: " . $res['status'] . "\n";
    echo "Response: " . json_encode($res['data']) . "\n\n";
}
?>
