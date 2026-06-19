<?php
require_once __DIR__ . '/php-backend/init.php';
try {
    $stmt = $conn->query("DESCRIBE parent_chat_messages");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' ' . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
