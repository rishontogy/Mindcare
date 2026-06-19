<?php
require_once __DIR__ . '/includes/db.php';

echo "### DIAGNOSTIC REPORT ###\n\n";

// 1. Check if anon can read any profiles
echo "1. Attempting to select all profiles (as anon/public)...\n";
$res = $supabase->from('profiles')->select('*')->get();
echo "Status: " . $res['status'] . "\n";
echo "Count: " . count($res['data'] ?? []) . "\n";
echo "Data: " . json_encode($res['data']) . "\n\n";

// 2. Check for the specific emails the user mentioned (if known, otherwise just check all)
if (!empty($res['data'])) {
    echo "Summary of profiles found:\n";
    foreach ($res['data'] as $profile) {
        echo "- ID: {$profile['id']}, Email: {$profile['email']}, Name: {$profile['name']}\n";
    }
} else {
    echo "No profiles found in public.profiles. This means either:\n";
    echo "a) The table is truly empty (Trigger failed during signup).\n";
    echo "b) RLS 'SELECT' policy is active and blocking you from seeing data (Correct behavior for security).\n\n";
}

// 3. Check for specific common error codes
if ($res['status'] === 401 || $res['status'] === 403) {
    echo "ALERT: You are getting a permissions error (HTTP {$res['status']}). RLS is likely active.\n";
}

echo "\n### END OF REPORT ###\n";
?>
