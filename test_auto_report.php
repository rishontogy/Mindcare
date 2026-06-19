<?php
/**
 * Test Auto Report Generation
 */

require_once __DIR__ . '/php-backend/init.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/php-backend/utils/ReportGenerator.php';

// Try to find a student user to test with
try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE user_type = 'student' LIMIT 1");
    $stmt->execute();
    $studentId = $stmt->fetchColumn();

    if (!$studentId) {
        die("No student found in database to test with.");
    }

    echo "Testing report generation for student ID: $studentId" . PHP_EOL;

    // Simulate some data in daily_summary and mood_logs for today if not present
    $date = date('Y-m-d');
    
    $stmt = $conn->prepare("INSERT IGNORE INTO daily_summary (user_id, date, summary) VALUES (?, ?, 'The student is feeling quite energetic but a bit anxious about upcoming exams.')");
    $stmt->execute([$studentId, $date]);

    $stmt = $conn->prepare("INSERT INTO mood_logs (user_id, mood) VALUES (?, 7), (?, 8)");
    $stmt->execute([$studentId, $studentId]);

    echo "Generating report..." . PHP_EOL;
    $result = ReportGenerator::generateDailyReport($conn, $studentId, $date);

    if ($result) {
        echo "SUCCESS: Report generated and stored." . PHP_EOL;
        
        // Verify from DB
        $stmt = $conn->prepare("SELECT * FROM parent_detailed_reports WHERE student_id = ? AND report_date = ?");
        $stmt->execute([$studentId, $date]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($report) {
            echo "Report Data Found:" . PHP_EOL;
            echo "Mood Score: " . $report['mood_score'] . PHP_EOL;
            echo "Stress Level: " . $report['stress_level'] . PHP_EOL;
            echo "Review: " . substr($report['overall_review'], 0, 100) . "..." . PHP_EOL;
        } else {
            echo "FAILURE: Report generated but not found in database." . PHP_EOL;
        }
    } else {
        echo "FAILURE: Report generation failed. Check GROQ_API_KEY or logs." . PHP_EOL;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
