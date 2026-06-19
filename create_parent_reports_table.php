<?php
require_once __DIR__ . '/php-backend/init.php';

try {
    // Standard connection for MindCare
    $conn = new mysqli('localhost', 'root', '', 'mindcare', 3307);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    echo "Checking for parent_detailed_reports table..." . PHP_EOL;

    $sql = "CREATE TABLE IF NOT EXISTS parent_detailed_reports (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        report_date DATE NOT NULL,
        mood_score DECIMAL(3,1),
        stress_level VARCHAR(50),
        overall_review TEXT,
        suggestions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY student_date (student_id, report_date),
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql) === TRUE) {
        echo "Table parent_detailed_reports created successfully." . PHP_EOL;
    } else {
        echo "Error creating table: " . $conn->error . PHP_EOL;
    }

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
