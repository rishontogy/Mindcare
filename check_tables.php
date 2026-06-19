<?php
try {
    $conn = new mysqli('localhost', 'root', '', 'mindcare', 3307);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $result = $conn->query("SHOW TABLES");
    while($row = $result->fetch_array()) {
        echo $row[0] . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
