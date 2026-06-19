<?php
require_once 'includes/db.php';

$sql = "CREATE TABLE IF NOT EXISTS personal_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL UNIQUE,
    parent_name VARCHAR(255) DEFAULT '',
    parent_phone VARCHAR(50) DEFAULT '',
    parent_email VARCHAR(255) DEFAULT '',
    guardian_name VARCHAR(255) DEFAULT '',
    guardian_phone VARCHAR(50) DEFAULT '',
    guardian_email VARCHAR(255) DEFAULT '',
    spouse_name VARCHAR(255) DEFAULT '',
    spouse_phone VARCHAR(50) DEFAULT '',
    spouse_email VARCHAR(255) DEFAULT '',
    emergency_contact VARCHAR(255) DEFAULT '',
    emergency_phone VARCHAR(50) DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "<p style='color:green; font-family:sans-serif;'>✅ Table <strong>personal_details</strong> created (or already exists) successfully!</p>";
} else {
    echo "<p style='color:red; font-family:sans-serif;'>❌ Error creating table: " . $conn->error . "</p>";
}

$conn->close();
