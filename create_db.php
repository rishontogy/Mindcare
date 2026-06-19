<?php
/**
 * Create Database and Tables
 */

$host = '127.0.0.1';
$port = 3307;
$user = 'root';
$pass = '';
$dbname = 'mindcare';

try {
    // 1. Connect without DB to create it
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database `$dbname` created or exists.\n";
    
    // 2. Connect to the DB
    $pdo->exec("USE `$dbname`;");
    
    // Now include setup.php to create tables
    // We need to temporarily override the $conn in database.php or just run the logic here.
    // Let's just run the logic from setup.php directly to be safe.
    
    $tables = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
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
        )",
        "mood_assessments" => "CREATE TABLE IF NOT EXISTS mood_assessments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            mood_score INT CHECK(mood_score >= 1 AND mood_score <= 10),
            answers JSON,
            recommendations TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX(user_id), INDEX(created_at)
        )",
        "exercises" => "CREATE TABLE IF NOT EXISTS exercises (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            duration INT,
            instructions TEXT,
            benefits TEXT,
            difficulty_level VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(category), INDEX(difficulty_level)
        )",
        "user_exercises" => "CREATE TABLE IF NOT EXISTS user_exercises (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            exercise_id INT NOT NULL,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            feedback_rating INT CHECK(feedback_rating >= 1 AND feedback_rating <= 5),
            feedback_text TEXT,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
            INDEX(user_id), INDEX(exercise_id), INDEX(completed_at)
        )",
        "ai_chat_messages" => "CREATE TABLE IF NOT EXISTS ai_chat_messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            message_type VARCHAR(20),
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX(user_id), INDEX(created_at)
        )",
        "parent_student_links" => "CREATE TABLE IF NOT EXISTS parent_student_links (
            id INT PRIMARY KEY AUTO_INCREMENT,
            parent_id INT NOT NULL,
            student_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(parent_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY(parent_id, student_id),
            INDEX(parent_id), INDEX(student_id)
        )",
        "daily_chat_summaries" => "CREATE TABLE IF NOT EXISTS daily_chat_summaries (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            summary_date DATE NOT NULL,
            summary_text TEXT NOT NULL,
            mood_detected VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY(user_id, summary_date),
            INDEX(user_id), INDEX(summary_date)
        )",
        "personal_details" => "CREATE TABLE IF NOT EXISTS personal_details (
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
        )",
        "emergency_contacts" => "CREATE TABLE IF NOT EXISTS emergency_contacts (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            relationship VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX(user_id)
        )",
        "wellness_goals" => "CREATE TABLE IF NOT EXISTS wellness_goals (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            completed BOOLEAN DEFAULT false,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX(user_id)
        )"
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
        echo "✓ Table `$name` ready.\n";
    }

    echo "\n=== ALL DONE! Database and tables are ready. ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
