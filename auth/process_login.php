<?php
// C:\Users\RISHON OOMMEN TOGY\Downloads\MindCare\php-app\auth\process_login.php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required.";
        header("Location: ../login.php");
        exit();
    }

    try {
        // 1. Sign in using Supabase Auth
        $result = $supabase->signIn($email, $password);

        // DEBUG LOGGING
        file_put_contents(__DIR__ . '/login_debug.log', "[" . date('Y-m-d H:i:s') . "] Login Result: " . json_encode($result) . "\n", FILE_APPEND);

        if ($result['status'] >= 400) {
            $error_msg = $result['data']['error_description'] ?? $result['data']['error'] ?? "Login failed";
            
            // Helpful hint for unconfirmed emails
            if ($result['status'] === 400 && strpos(strtolower($error_msg), 'invalid') !== false) {
                $error_msg .= ". (Check if you need to confirm your email or if 'Confirm Email' is enabled in Supabase)";
            }
            
            $_SESSION['error'] = "Login failed: " . $error_msg;
            header("Location: ../login.php");
            exit();
        }

        // 2. Profile Check & Self-Healing
        $userId = $result['data']['user']['id'];
        
        // IMPORTANT: Set the session token NOW so subsequent database calls are authenticated
        $_SESSION['supabase_token'] = $result['data']['access_token'] ?? null;
        
        $profileCheck = $supabase->from('profiles')->select('id, name')->eq('id', $userId)->get();
        $profile_data = null;

        if ($profileCheck['status'] < 400 && !empty($profileCheck['data'])) {
            $profile_data = $profileCheck['data'][0];
        } else {
            // Self-Healing: If user is missing from profiles, try to create it
            $name = $result['data']['user']['user_metadata']['name'] ?? 'User';
            $profileCreate = $supabase->from('profiles')->insert([
                'id' => $userId,
                'name' => $name,
                'email' => $email
            ]);

            if ($profileCreate['status'] >= 200 && $profileCreate['status'] < 300) {
                // Fetch the newly created profile
                $profileCheck = $supabase->from('profiles')->select('id, name')->eq('id', $userId)->get();
                if (!empty($profileCheck['data'])) {
                    $profile_data = $profileCheck['data'][0];
                }
            }
            
            // FALLBACK: If we still don't have profile data, use metadata from Auth
            if (!$profile_data) {
                $profile_data = [
                    'id' => $userId,
                    'name' => $result['data']['user']['user_metadata']['name'] ?? 'User',
                    'email' => $result['data']['user']['email']
                ];
                // Store a flag that database sync is incomplete
                $_SESSION['db_sync_warning'] = true;
            }
        }

        // 3. Login success
        $user_data = $result['data']['user'];

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $profile_data['name'] ?? 'User';
        $_SESSION['user_email'] = $user_data['email'];

        header("Location: ../dashboard.php");
        exit();

    }
    catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../login.php");
        exit();
    }
}
else {
    header("Location: ../login.php");
    exit();
}
?>
