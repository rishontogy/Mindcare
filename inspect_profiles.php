<?php
require_once __DIR__ . '/includes/db.php';

echo "Inspecting 'profiles' table...\n";

// Try to select columns using a query that might bypass some RLS (selecting from information_schema won't work via PostgREST easily)
// Instead, let's just try to select one row and see the keys
$res = $supabase->from('profiles')->select('*')->limit(1)->get();

if ($res['status'] === 200) {
    echo "Columns found in a row: " . implode(', ', array_keys($res['data'][0] ?? [])) . "\n";
    echo "Data: " . json_encode($res['data']) . "\n";
} else {
    echo "Status: " . $res['status'] . "\n";
    echo "Error: " . json_encode($res['data']) . "\n";
}

// Try to search for our test user
echo "\nSearching for 'Test User' (00000000-0000-0000-0000-000000000000)...\n";
$res = $supabase->from('profiles')->select('*')->eq('id', '00000000-0000-0000-0000-000000000000')->get();
echo "Found: " . json_encode($res['data']) . "\n";
?>
