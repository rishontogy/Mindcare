<?php

/**
 * Student Signup Page - signup.php
 */

require_once __DIR__ . '/php-backend/init.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student') {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = Validator::sanitizeInput($_POST['name'] ?? '');
    $email = Validator::sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    $errors = [];
    if (empty($name) || !Validator::validateString($name, 3, 100)) {
        $errors[] = 'Name must be between 3 and 100 characters.';
    }
    if (empty($email) || !Validator::validateEmail($email)) {
        $errors[] = 'Invalid email format.';
    }
    if (empty($password) || !Validator::validatePassword($password)) {
        $errors[] = 'Password must be at least 8 characters with 1 uppercase letter and 1 digit.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (count($errors) === 0) {
        if (!$pdo) {
            $error = 'Database connection failed. Please contact support or try again later.';
        } else {
            $userModel = new User($pdo);
            if ($userModel->emailExists($email)) {
                $error = 'Email already exists. Please login or use a different email.';
            } else {
                if ($userModel->create($email, $password, $name, 'student')) {
                    $success = 'Account created successfully! Redirecting to login...';
                    header('Refresh: 2; url=login.php');
                } else {
                    $error = 'Error creating account. Please try again.';
                }
            }
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MindCare</title>
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

<body class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-teal-50 flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-md my-8">
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
                    <div class="size-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-purple-200 -rotate-3">
                        <i data-lucide="brain" class="size-8 text-white"></i>
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                        Start Your Journey
                    </h1>
                    <p class="text-gray-500 mt-2 italic font-medium">Create your MindCare student account</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-center gap-3 text-red-600 text-sm">
                        <i data-lucide="alert-circle" class="size-5 shrink-0"></i>
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-3 text-emerald-600 text-sm">
                        <i data-lucide="check-circle" class="size-5 shrink-0"></i>
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Full Name</label>
                        <div class="relative group">
                            <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-300 group-focus-within:text-purple-500 transition-colors"></i>
                            <input type="text" name="name" required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all text-gray-800"
                                placeholder="Enter your full name"
                                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>
                    </div>

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
                        <label class="text-sm font-bold text-gray-700 ml-1">Password</label>
                        <div class="relative group">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-300 group-focus-within:text-purple-500 transition-colors"></i>
                            <input type="password" name="password" required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all text-gray-800"
                                placeholder="••••••••">
                        </div>
                        <p class="text-[10px] text-gray-400 ml-1 italic font-medium tracking-tight">At least 8 chars, 1 uppercase, 1 digit</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Confirm Password</label>
                        <div class="relative group">
                            <i data-lucide="shield-check" class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-300 group-focus-within:text-purple-500 transition-colors"></i>
                            <input type="password" name="confirm_password" required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all text-gray-800"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-start gap-3 px-1 py-1">
                        <input type="checkbox" name="agree_terms" required
                            class="mt-1 size-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <label class="text-[10px] text-gray-500 font-medium leading-tight">
                            By joining MindCare, I agree to the <a href="#" class="text-purple-600 font-bold hover:underline">Terms of Service</a> and
                            <a href="#" class="text-purple-600 font-bold hover:underline">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl font-bold text-lg shadow-lg shadow-purple-200 hover:opacity-90 hover:scale-[1.02] transition-all active:scale-[0.98]">
                        Create Account
                    </button>
                </form>

                <div class="mt-8 text-center text-sm">
                    <p class="text-gray-500 font-medium">
                        Already part of the community?
                        <a href="login.php" class="text-purple-600 font-bold hover:underline ml-1">Log in here</a>
                    </p>

                </div>
            </div>
        </div>

        <p class="mt-10 text-center text-xs text-gray-400 font-medium">
            © 2026 MindCare - Dedicated to Your Growth
        </p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>