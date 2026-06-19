<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCare - Your Mental Wellness Companion</title>
    <!-- Tailwind CSS (Local Fallback) -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Tailwind CSS CDN (Optional for live updates) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-teal-50">
<?php 
require_once __DIR__ . '/functions.php'; 
require_once __DIR__ . '/db.php';
?>

<!-- Shared Header -->
<header class="bg-white/80 backdrop-blur-sm border-b border-purple-100 sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="size-10 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center shadow-lg shadow-purple-200">
                <i data-lucide="brain" class="size-6 text-white text-xl"></i>
            </div>
            <a href="index.php" class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                MindCare
            </a>
        </div>
        <div class="flex items-center gap-3">
            <?php if (is_logged_in()): ?>
                <div class="hidden md:flex items-center gap-6 mr-4 text-sm font-medium text-gray-600">
                    <?php if (($_SESSION['user_type'] ?? 'student') === 'parent'): ?>
                        <a href="parent-dashboard.php" class="hover:text-purple-600 transition-colors">Dashboard</a>
                        <a href="parent-ai-assessment.php" class="hover:text-purple-600 transition-colors">AI Assessment</a>
                        <a href="parent-mood-details.php" class="hover:text-purple-600 transition-colors">Reports</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="hover:text-purple-600 transition-colors">Dashboard</a>
                        <a href="chat.php" class="hover:text-purple-600 transition-colors">MindPal</a>
                        <a href="exercises.php" class="hover:text-purple-600 transition-colors">Exercises</a>
                        <a href="wellness-coach.php" class="hover:text-purple-600 transition-colors">Wellness Coach</a>
                    <?php endif; ?>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo ($_SESSION['user_type'] ?? 'student') === 'parent' ? 'parent-profile.php' : 'profile.php'; ?>" class="px-4 py-2 border border-purple-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 transition-all flex items-center gap-2">
                        <i data-lucide="user" class="size-4"></i> Profile
                    </a>
                    <a href="<?php echo ($_SESSION['user_type'] ?? 'student') === 'parent' ? 'auth/logout.php' : 'auth/logout.php'; ?>" class="px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-lg text-sm font-medium hover:bg-red-100 transition-all flex items-center gap-2">
                        <i data-lucide="log-out" class="size-4"></i> Logout
                    </a>
                </div>
            <?php else: ?>
                <div class="flex gap-2">
                    <a href="login.php" class="px-4 py-2 border border-purple-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 transition-all">
                        Login
                    </a>
                    <a href="signup.php" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg text-sm font-medium hover:opacity-90 transition-all shadow-md shadow-purple-200">
                        Sign Up
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
