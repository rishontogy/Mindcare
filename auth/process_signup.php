<?php
// C:\Users\RISHON OOMMEN TOGY\Downloads\MindCare\php-app\auth\process_signup.php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../signup.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters.";
        header("Location: ../signup.php");
        exit();
    }

    try {
        // 1. Sign up using Supabase Auth
        $result = $supabase->signUp($email, $password, ['name' => $name]);

        // DEBUG LOGGING
        file_put_contents(__DIR__ . '/signup_debug.log', "[" . date('Y-m-d H:i:s') . "] Auth Result: " . json_encode($result) . "\n", FILE_APPEND);

        if ($result['status'] >= 400) {
            $error_msg = $result['data']['msg'] ?? $result['data']['error_description'] ?? "Auth error";

            // Helpful message for rate limits
            if (strpos(strtolower($error_msg), 'rate limit') !== false) {
                $error_msg .= ". Please use a different email address or wait 15-30 minutes.";
            }

            $_SESSION['error'] = "Signup failed: " . $error_msg;
            header("Location: ../signup.php");
            exit();
        }

        if (isset($result['data']['confirmation_sent_at'])) {
            $_SESSION['success'] = "Account created! **Please check your email** for a confirmation link before logging in.";
        } else {
            $_SESSION['success'] = "Account created successfully! You can now log in.";
        }

        // 2. ALSO SAVE TO LOCAL MYSQL
        $user_id = $result['data']['id'] ?? null;
        if ($user_id) {
            // Hash password for MySQL (standard practice, though Supabase handles auth)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $created_at = date('Y-m-d H:i:s');

            // Insert into users table
            $stmt = $pdo->prepare("INSERT INTO users (id, email, password, created_at) VALUES (?, ?, ?, ?)");
            try {
                $stmt->execute([$user_id, $email, $hashed_password, $created_at]);
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/signup_debug.log', "[" . date('Y-m-d H:i:s') . "] MySQL Users Insert Failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }

            // Insert into profiles table
            $stmt_p = $pdo->prepare("INSERT INTO profiles (user_id, name, email, updated_at) VALUES (?, ?, ?, ?)");
            try {
                $stmt_p->execute([$user_id, $name, $email, $created_at]);
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/signup_debug.log', "[" . date('Y-m-d H:i:s') . "] MySQL Profiles Insert Failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
        header("Location: ../login.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../signup.php");
        exit();
    }
} else {
    header("Location: ../signup.php");
    exit();
}
