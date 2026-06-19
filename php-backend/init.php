<?php

/**
 * Initialize Application
 */

// ✅ Ensure session folder exists FIRST
$sessionPath = __DIR__ . '/../sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// ✅ Set session path BEFORE starting session
if (session_status() === PHP_SESSION_NONE) {
    session_save_path($sessionPath);
    session_start();
}
// Load configuration
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

// Load utilities
require_once __DIR__ . '/utils/Session.php';
require_once __DIR__ . '/utils/Auth.php';
require_once __DIR__ . '/utils/Validator.php';
require_once __DIR__ . '/utils/Response.php';

// Load models
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/MoodAssessment.php';
require_once __DIR__ . '/models/Exercise.php';
require_once __DIR__ . '/models/UserExercise.php';

// Initialize session
if (!is_dir(__DIR__ . '/../sessions')) {
    mkdir(__DIR__ . '/../sessions', 0777, true);
}


// Set error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
