<?php

/**
 * Parent Login Page - parent-login.php
 */

require_once __DIR__ . '/php-backend/init.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'parent') {
    header("Location: parent-dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = Validator::sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif (!Validator::validateEmail($email)) {
        $error = 'Invalid email format.';
    } else {
        // Check database connection
        if (!$pdo) {
            $error = 'Database connection failed. Please contact support or try again later.';
        } else {
            // Check user
            $userModel = new User($pdo);
            $user = $userModel->findByEmail($email);

            if ($user && $user['user_type'] === 'parent' && Auth::verifyPassword($password, $user['password'])) {
                // Login successful
                Session::set('user_id', $user['id']);
                Session::set('user_email', $user['email']);
                Session::set('user_name', $user['name']);
                Session::set('user_type', $user['user_type']);

                header('Location: parent-dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password, or account is not a parent account.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Login - MindCare</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="assets/css/font.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Back Button -->
        <a href="index.php" class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-700 mb-8 transition-colors">
            <i data-lucide="arrow-left" class="size-4 mr-2"></i>
            Back to Home
        </a>

        <!-- Auth Card -->
        <div class="bg-white rounded-[2rem] shadow-xl shadow-emerald-100/50 border border-emerald-100 overflow-hidden">
            <div class="p-8 md:p-10">
                <!-- Branding -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="size-16 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-200 rotate-3">
                        <i data-lucide="brain" class="size-8 text-white"></i>
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                        Parent Portal
                    </h1>
                    <p class="text-gray-500 mt-2">Welcome back to MindCare</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-center gap-3 text-red-600 text-sm">
                        <i data-lucide="alert-circle" class="size-5 shrink-0"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700 ml-1">Email Address</label>
                        <div class="relative group">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors"></i>
                            <input type="email" name="email" required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-gray-800"
                                placeholder="parent@example.com"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between ml-1">
                            <label class="text-sm font-semibold text-gray-700">Password</label>
                            <a href="#" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Forgot?</a>
                        </div>
                        <div class="relative group">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors"></i>
                            <input type="password" name="password" required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-gray-800"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 text-xs text-emerald-800 flex items-start gap-3">
                        <i data-lucide="info" class="size-4 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-bold mb-1">Demo Login:</p>
                            <p>Email: parent@mindcare.app</p>
                            <p>Password: Parent123!</p>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-lg shadow-lg shadow-emerald-200 hover:opacity-90 hover:scale-[1.02] transition-all active:scale-[0.98]">
                        Sign In as Parent
                    </button>
                </form>

                <div class="mt-8 text-center text-sm">
                    <p class="text-gray-500">
                        Don't have a parent account?
                        <a href="parent-signup.php" class="text-emerald-600 font-bold hover:underline ml-1">Create Account</a>
                    </p>
                </div>
            </div>

            <div class="bg-gray-50/80 border-t border-emerald-50 p-6 text-center">
                <p class="text-xs text-gray-400 mb-3 uppercase tracking-wider font-bold">Are you a student?</p>
                <a href="login.php" class="inline-flex items-center justify-center w-full py-3 border-2 border-emerald-100 bg-white text-emerald-600 rounded-xl font-bold text-sm hover:bg-emerald-50 transition-all">
                    Go to Student Login
                </a>
            </div>
        </div>

        <p class="mt-10 text-center text-xs text-gray-400">
            © 2026 MindCare. All rights reserved.
        </p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>