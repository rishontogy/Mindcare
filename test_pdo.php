<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1", "root", "");
    echo "SUCCESS: Connected to MySQL server.";
    $stmt = $pdo->query("SHOW DATABASES");
    echo "\nDatabases:\n";
    while ($row = $stmt->fetch()) {
        echo "- " . $row[0] . "\n";
    }
} catch (PDOException $e) {
    echo "FAILURE: " . $e->getMessage();
}
