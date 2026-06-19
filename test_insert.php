<?php
require_once __DIR__ . '/includes/db.php';

$test_id = '00000000-0000-0000-0000-000000000000'; // Dummy UUID
$test_name = 'Test User';
$test_email = 'test@example.com';

echo "Attempting to insert test record into 'profiles'...\n";

$result = $supabase->from('profiles')->insert([
    'id' => $test_id,
    'name' => $test_name,
    'email' => $test_email
]);

echo "Status Code: " . $result['status'] . "\n";
echo "Response Data: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";

if ($result['status'] >= 400) {
    echo "ERROR: Table insertion failed. This is likely due to RLS policies or database constraints.\n";
} else {
    echo "SUCCESS: Record inserted (or at least no error returned).\n";
}
?>
