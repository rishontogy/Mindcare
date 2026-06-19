<?php
/**
 * Application Configuration
 */

// Application settings
define('APP_NAME', 'MindCare');
define('APP_URL', 'http://localhost/MindCare/');
define('APP_ENV', 'development'); // development or production

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('REMEMBER_ME_DURATION', 2592000); // 30 days in seconds

// Security
define('SECRET_KEY', 'your-secret-key-here-change-in-production');
define('HASH_ALGO', 'bcrypt');

// File uploads
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Timezone
date_default_timezone_set('UTC');
