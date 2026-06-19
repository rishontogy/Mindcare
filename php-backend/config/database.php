<?php

/**
 * Database Configuration
 * Configure your database connection here
 */

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mindcare');
define('DB_PORT', 3307);

// Create database connection
try {
    try {
        // Try preferred port 3307
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
            DB_USER,
            DB_PASS
        );
    } catch (PDOException $e) {
        // Fallback to 3306 if 3307 fails
        if (DB_PORT == 3307) {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';port=3306;dbname=' . DB_NAME,
                DB_USER,
                DB_PASS
            );
        } else {
            throw $e;
        }
    }

    if ($pdo) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $pdo = null;
    error_log('Database Connection Error (all ports): ' . $e->getMessage());
}
