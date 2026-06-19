<?php
require_once __DIR__ . '/includes/db.php';

$email = 'test_struct_' . time() . '@gmail.com';
$password = 'TestPassword123!';

echo "Testing CORRECT GoTrue Signup structure...\n";
$res = $supabase->signUp($email, $password, ['name' => 'Tester']);

echo "Status: " . $res['status'] . "\n";
echo "Response: " . json_encode($res['data']) . "\n";
?>
