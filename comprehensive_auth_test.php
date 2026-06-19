<?php
require_once __DIR__ . '/includes/db.php';

$email = 'rishontogy5050@gmail.com';
$passwords_to_try = ['MindCare2026!', 'Password123!'];

echo "--- LOGIN-ONLY TEST --- \n";
echo "Testing Email: $email\n\n";

foreach ($passwords_to_try as $password) {
    echo "--- Testing with: $password ---\n";
    
    // Test 1: No Authorization (current state)
    echo "Attempt 1 (No Auth header): ";
    $login1 = $supabase->signIn($email, $password);
    echo "Status: " . $login1['status'] . "\n";

    // Test 2: With Authorization (previous state)
    echo "Attempt 2 (With Anon Auth header): ";
    $ch = curl_init(SUPABASE_URL . '/auth/v1/token?grant_type=password');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . SUPABASE_ANON_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'password' => $password]));
    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "Status: " . $status . " | Response: " . $res . "\n\n";
}

echo "\n--- AUTH TEST END ---\n";
?>
