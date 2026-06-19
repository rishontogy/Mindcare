<?php
require_once __DIR__ . '/php-backend/init.php';
$st = $conn->query("SELECT parent_id FROM parent_student_links LIMIT 1");
$pid = $st->fetchColumn();
$_SESSION['user_id'] = $pid; 
$_SESSION['user_type'] = 'parent';
require 'parent-mood-data.php';
