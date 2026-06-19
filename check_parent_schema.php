<?php
try {
    $conn = new mysqli('localhost', 'root', '', 'mindcare', 3307);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $result = $conn->query("DESCRIBE parent_chat_messages");
    if (!$result) {
        // If it doesn't exist, create it based on ai_chat_messages structure but for parents
        echo "Table parent_chat_messages does not exist. Creating it." . PHP_EOL;
        $conn->query("CREATE TABLE IF NOT EXISTS parent_chat_messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            sender ENUM('user', 'ai') NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        echo "Table created." . PHP_EOL;
    } else {
        while($row = $result->fetch_assoc()) {
            print_r($row);
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
