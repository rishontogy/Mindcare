<?php
require_once __DIR__ . '/php-backend/config/database.php';

if (!$conn) {
    die("Error: Could not establish database connection.\n");
}

try {
    // Check if column exists
    $stmt = $conn->query("SHOW COLUMNS FROM parent_student_links LIKE 'status'");
    $column = $stmt->fetch();

    if (!$column) {
        $conn->exec("ALTER TABLE parent_student_links ADD COLUMN status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending'");
        echo "✓ Column 'status' added to 'parent_student_links' table.\n";
    } else {
        echo "! Column 'status' already exists in 'parent_student_links' table.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
