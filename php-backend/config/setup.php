<?php
/**
 * Database Setup - Standardized Schema Initialization
 */

require_once __DIR__ . '/database.php';

try {
    // 1. Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        user_type ENUM('student', 'parent') DEFAULT 'student',
        phone VARCHAR(20),
        date_of_birth DATE,
        profile_image VARCHAR(255),
        about_me TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(email), INDEX(user_type)
    )");

    // 2. Mood Logs (Historical mood scores)
    $pdo->exec("CREATE TABLE IF NOT EXISTS mood_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        mood FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(user_id), INDEX(created_at)
    )");
    
    // 3. Mood Assessments (Detailed assessments)
    $pdo->exec("CREATE TABLE IF NOT EXISTS mood_assessments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        mood_score INT CHECK(mood_score >= 1 AND mood_score <= 10),
        answers JSON,
        recommendations TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(user_id), INDEX(created_at)
    )");
    
    // 4. Exercises Master List
    $pdo->exec("CREATE TABLE IF NOT EXISTS exercises (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100),
        duration INT,
        instructions TEXT,
        benefits TEXT,
        difficulty_level VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(category)
    )");
    
    // 5. User Exercises tracking (Standard tracking)
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_exercises (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        exercise_id INT NOT NULL,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        feedback_rating INT,
        feedback_text TEXT,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(user_id), INDEX(completed_at)
    )");

    // 6. Exercise Reviews (Enhanced tracking with stories)
    $pdo->exec("CREATE TABLE IF NOT EXISTS exercise_reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        exercise_id INT NOT NULL,
        exercise_name VARCHAR(255),
        type VARCHAR(100),
        rating INT,
        feedback TEXT,
        story TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(user_id), INDEX(created_at)
    )");
    
    // 7. Student Chat Messages
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_chat_messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        sender ENUM('user', 'ai') NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(user_id), INDEX(created_at)
    )");

    // 8. Parent Chat Messages
    $pdo->exec("CREATE TABLE IF NOT EXISTS parent_chat_messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        parent_id INT NOT NULL,
        student_id INT NOT NULL,
        sender ENUM('user', 'ai') NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(parent_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(parent_id), INDEX(student_id), INDEX(created_at)
    )");
    
    // 9. Parent-Student Links
    $pdo->exec("CREATE TABLE IF NOT EXISTS parent_student_links (
        id INT PRIMARY KEY AUTO_INCREMENT,
        parent_id INT NOT NULL,
        student_id INT NOT NULL,
        status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(parent_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY(parent_id, student_id)
    )");
    
    // 10. Daily Interaction Summaries
    $pdo->exec("CREATE TABLE IF NOT EXISTS daily_summary (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        date DATE NOT NULL,
        summary TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY(user_id, date),
        INDEX(date)
    )");
 
    // 11. Personal & Emergency Details
    $pdo->exec("CREATE TABLE IF NOT EXISTS personal_details (
        user_id INT PRIMARY KEY,
        parent_name VARCHAR(255),
        parent_phone VARCHAR(20),
        parent_email VARCHAR(255),
        guardian_name VARCHAR(255),
        guardian_phone VARCHAR(20),
        guardian_email VARCHAR(255),
        spouse_name VARCHAR(255),
        spouse_phone VARCHAR(20),
        spouse_email VARCHAR(255),
        emergency_contact VARCHAR(255),
        emergency_phone VARCHAR(20),
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
 
    // 12. Wellness Goals
    $pdo->exec("CREATE TABLE IF NOT EXISTS wellness_goals (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100),
        completed BOOLEAN DEFAULT false,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(user_id)
    )");
    
    // 13. Chat Summary (Daily aggregates)
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_summary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        summary_date DATE NOT NULL,
        avg_mood FLOAT,
        mood_trend VARCHAR(50),
        stress_level VARCHAR(50),
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY(user_id, summary_date),
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(user_id), INDEX(summary_date)
    )");

    // 14. Parent Detailed AI Reports
    $pdo->exec("CREATE TABLE IF NOT EXISTS parent_detailed_reports (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        report_date DATE NOT NULL,
        mood_score FLOAT,
        stress_level VARCHAR(50),
        overall_review TEXT,
        suggestions JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY(student_id, report_date),
        INDEX(report_date)
    )");

    // 15. Seed Demo Accounts
    $demoStudent = [
        'email' => 'student@mindcare.app',
        'password' => password_hash('Student123!', PASSWORD_DEFAULT),
        'name' => 'Demo Student',
        'user_type' => 'student'
    ];

    $demoParent = [
        'email' => 'parent@mindcare.app',
        'password' => password_hash('Parent123!', PASSWORD_DEFAULT),
        'name' => 'Demo Parent',
        'user_type' => 'parent'
    ];

    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    
    foreach ([$demoStudent, $demoParent] as $user) {
        $checkStmt->execute([$user['email']]);
        if (!$checkStmt->fetch()) {
            $insertStmt = $pdo->prepare("INSERT INTO users (email, password, name, user_type) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$user['email'], $user['password'], $user['name'], $user['user_type']]);
            echo "✓ Seeded demo user: " . $user['email'] . "\n";
        }
    }

    echo "✓ MindCare Standardized Database Schema Initialized Successfully!";
    
} catch (PDOException $e) {
    echo "❌ Schema Initialization Error: " . $e->getMessage();
}
