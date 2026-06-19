<?php
session_start();
/**
 * Student Login Page - login.php
 */

require_once __DIR__ . '/php-backend/init.php';

// Redirect if already logged in
Auth::redirectIfLoggedIn('dashboard.php');

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

            if ($user && Auth::verifyPassword($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
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
    <title>Login - MindCare</title>
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

<body class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-teal-50 flex flex-col items-center justify-center p-4 text-gray-800">
    <div class="w-full max-w-md">
        <!-- Back Button -->
        <a href="index.php" class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-700 mb-8 transition-colors">
            <i data-lucide="arrow-left" class="size-4 mr-2"></i>
            Back to Home
        </a>

        <!-- Auth Card -->
        <div class="bg-white rounded-[2rem] shadow-xl shadow-purple-100/50 border border-purple-100 overflow-hidden">
            <div class="p-8 md:p-10">
                <!-- Branding -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="size-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-purple-200 rotate-6">
                        <i data-lucide="brain" class="size-8 text-white"></i>
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                        MindCare
                    </h1>
                    <p class="text-gray-500 mt-2 italic font-medium">Student Wellness Portal</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-center gap-3 text-red-600 text-sm">
                        <i data-lucide="alert-circle" class="size-5 shrink-0"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Email Address</label>
                        <div class="relative group">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-300 group-focus-within:text-purple-500 transition-colors"></i>
                            <input type="email" name="email" required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all text-gray-800"
                                placeholder="name@example.com"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between ml-1">
                            <label class="text-sm font-bold text-gray-700">Password</label>
                            <a href="#" class="text-xs font-bold text-purple-600 hover:text-purple-700 transition-colors">Forgot?</a>
                        </div>
                        <div class="relative group">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-300 group-focus-within:text-purple-500 transition-colors"></i>
                            <input type="password" name="password" required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all text-gray-800"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 px-1">
                        <input type="checkbox" name="remember_me" class="size-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-xs text-gray-500 font-medium">Remember me on this divine device</span>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl font-bold text-lg shadow-lg shadow-purple-200 hover:opacity-90 hover:scale-[1.02] transition-all active:scale-[0.98]">
                        Sign In
                    </button>
                </form>

                <div class="mt-8 text-center text-sm">
                    <p class="text-gray-500 font-medium">
                        New to MindCare?
                        <a href="signup.php" class="text-purple-600 font-bold hover:underline ml-1">Join for Free</a>
                    </p>

                </div>
            </div>

            <div class="bg-gray-50/80 border-t border-purple-50 p-6 text-center">
                <p class="text-xs text-gray-400 mb-3 uppercase tracking-wider font-bold">Are you a parent?</p>
                <a href="parent-login.php" class="inline-flex items-center justify-center w-full py-3 border-2 border-purple-100 bg-white text-purple-600 rounded-xl font-bold text-sm hover:bg-purple-50 transition-all group">
                    Go to Parent Login
                    <i data-lucide="arrow-right" class="size-4 ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>

        <p class="mt-10 text-center text-xs text-gray-400 font-medium">
            © 2026 MindCare - Your Mental Health Companion
        </p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>