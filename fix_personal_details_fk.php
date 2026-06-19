<?php
require_once 'includes/db.php';

// Drop the foreign key constraint so personal_details can store any user_id
// regardless of whether it exists in the local MySQL users table
$sql = "ALTER TABLE personal_details DROP FOREIGN KEY personal_details_ibfk_1";

if ($conn->query($sql)) {
    echo "<p style='color:green;font-family:sans-serif;'>✅ Foreign key constraint <strong>personal_details_ibfk_1</strong> removed successfully!</p>";
    echo "<p style='font-family:sans-serif;'>You can now save personal details. <a href='details.php'>Go to details page →</a></p>";
} else {
    echo "<p style='color:orange;font-family:sans-serif;'>⚠️ Note: " . $conn->error . "</p>";
    echo "<p style='font-family:sans-serif;'>The constraint may have already been removed. <a href='details.php'>Try the details page →</a></p>";
}
$conn->close();
