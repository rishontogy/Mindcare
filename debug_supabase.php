<?php
require_once __DIR__ . '/includes/db.php';
echo "Supabase exists: " . (isset($supabase) ? "Yes" : "No") . "\n";
if (isset($supabase)) {
    $res = $supabase->from('profiles')->select()->limit(1)->get();
    echo "Status: " . $res['status'] . "\n";
    echo "Error: " . json_encode($res['data']) . "\n";
}
?>
