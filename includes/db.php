<?php
// includes/db.php

require_once __DIR__ . '/config.php';

// Try preferred port 3307 first, then fallback to 3306
$ports = [3307, 3306];
$conn = null;
$pdo = null;

foreach ($ports as $port) {
    // Try mysqli
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
    if (!$conn->connect_error) {
        // Try PDO as well
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";port=$port;dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            break;
        } catch (PDOException $e) {
            $conn->close();
            $conn = null;
        }
    } else {
        $conn = null;
    }
}

if (!$conn || !$pdo) {
    die("Database connection failed. Please check your XAMPP/MySQL settings.");
}

if (!function_exists('clean_input')) {
    function clean_input($data) {
        if ($data === null) return '';
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}
