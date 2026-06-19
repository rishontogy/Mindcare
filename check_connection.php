<?php
session_start();
require_once 'includes/db.php';
$res = $supabase->from('profiles')->select()->limit(1)->get();
echo "Status: " . $res['status'] . "\n";
echo "Data: " . json_encode($res['data']) . "\n";
?>
