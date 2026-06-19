<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $parentEmail = 'parent@mindcare.app';
    $studentEmail = 'student@mindcare.app';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    
    $stmt->execute([$parentEmail]);
    $parentId = $stmt->fetchColumn();
    
    $stmt->execute([$studentEmail]);
    $studentId = $stmt->fetchColumn();

    if ($parentId && $studentId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO parent_student_links (parent_id, student_id, status) VALUES (?, ?, 'accepted')");
        $stmt->execute([$parentId, $studentId]);
        echo "✓ Linked $parentEmail and $studentEmail successfully!\n";
    } else {
        echo "❌ Could not find demo users. Did you run setup.php?\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
