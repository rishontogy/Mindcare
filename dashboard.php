<?php
include_once 'includes/header.php';
redirect_if_not_logged_in();

$userId = get_current_user_id();
$userName = get_current_user_name();
$today = date('l, F j, Y');
?>

    <div class="container mx-auto px-4 py-8">
        <?php if (isset($success_message) && is_string($success_message)): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-700 animate-fade-in">
                <i data-lucide="check-circle" class="size-5"></i>
                <p class="text-sm font-bold"><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="mb-10 text-center md:text-left">
            <h2 class="text-4xl font-black text-gray-900 mb-2 tracking-tight">
                Welcome back, <?php echo htmlspecialchars($userName); ?>! 👋
            </h2>
            <p class="text-gray-600 font-medium italic">
                How are you feeling today? Let's check in on your mental wellness journey.
            </p>
        </div>

        <?php if (!empty($pendingRequests)): ?>
            <!-- Subtle Parent Requests Notification -->
            <div class="mb-8">
                <?php foreach ($pendingRequests as $request): ?>
                    <div class="flex items-center justify-between p-4 bg-white/50 backdrop-blur-sm border border-amber-200/50 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 mb-3 last:mb-0">
                        <div class="flex items-center gap-4">
                            <div class="size-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                                <i data-lucide="user-plus" class="size-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 leading-tight">
                                    <span class="text-amber-600 font-black">Linking Request:</span>
                                    <?php echo htmlspecialchars($request['parent_name']); ?>
                                </p>
                                <p class="text-[10px] text-gray-500 font-medium tracking-wide"><?php echo htmlspecialchars($request['parent_email']); ?></p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <form method="POST" class="m-0">
                                <input type="hidden" name="link_id" value="<?php echo $request['id']; ?>">
                                <input type="hidden" name="request_action" value="accepted">
                                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white text-xs font-black rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all active:scale-95 flex items-center gap-2">
                                    <i data-lucide="check" class="size-4"></i>
                                    Accept Request
                                </button>
                            </form>
                            <form method="POST" class="m-0">
                                <input type="hidden" name="link_id" value="<?php echo $request['id']; ?>">
                                <input type="hidden" name="request_action" value="rejected">
                                <button type="submit" class="px-4 py-2 bg-white text-gray-400 border border-gray-200 text-xs font-bold rounded-lg hover:bg-gray-50 active:scale-95 transition-all">
                                    Ignore
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Main Widgets Grid -->
        <div class="space-y-8">
            <!-- AI Chat Widget -->
            <a href="chat.php" class="block">
                <div class="p-8 border-2 border-indigo-200 hover:border-indigo-400 transition-all cursor-pointer hover:shadow-2xl relative overflow-hidden bg-gradient-to-r from-indigo-50 to-purple-50 rounded-3xl">
                    <div class="absolute top-0 right-0 size-32 bg-gradient-to-br from-indigo-200 to-purple-200 rounded-full blur-3xl opacity-30"></div>
                    <div class="absolute bottom-0 left-0 size-24 bg-gradient-to-tr from-blue-200 to-teal-200 rounded-full blur-2xl opacity-40"></div>
                    <div class="flex items-start justify-between mb-6 relative">
                        <div class="size-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <i data-lucide="message-circle" class="size-8 text-white"></i>
                        </div>
                        <span class="text-sm bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full flex items-center gap-1 font-medium">
                            <i data-lucide="sparkles" class="size-4"></i> AI Powered
                        </span>
                    </div>
                    <h3 class="font-bold text-2xl mb-3 text-gray-800">Chat with MindPal</h3>
                    <p class="text-base text-gray-600 mb-6 leading-relaxed">
                        Your AI friend, teacher & assistant. Get support, study help, and stay organized! This is your main hub for mental wellness support.
                    </p>
                    <div class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-4 rounded-xl shadow-lg text-center transform hover:scale-[1.02] transition-all">
                        Start Chatting
                    </div>
                </div>
            </a>

            <!-- Secondary Widgets Row -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- AI Wellness Coach Widget -->
                <a href="wellness-coach.php" class="p-6 border-2 border-purple-200 rounded-3xl hover:border-purple-400 transition-all cursor-pointer hover:shadow-xl hover:scale-105 transform duration-300 bg-gradient-to-br from-purple-50 to-indigo-50">
                    <div class="flex items-start justify-between mb-4">
                        <div class="size-12 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                            <i data-lucide="star" class="size-6 text-white"></i>
                        </div>
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">AI Coach</span>
                    </div>
                    <h3 class="font-semibold text-lg mb-2 text-gray-800">Wellness Coach</h3>
                    <p class="text-sm text-gray-600 mb-4">Track your mood, set goals, and get personalized wellness guidance</p>
                    <div class="w-full border border-purple-300 text-purple-600 py-2 rounded-xl text-center hover:bg-purple-50 font-medium">Start Coaching</div>
                </a>

                <!-- Progress Tracker Widget -->
                <a href="progress.php" class="p-6 border-2 border-blue-200 rounded-3xl hover:border-blue-400 transition-all cursor-pointer hover:shadow-xl hover:scale-105 transform duration-300 bg-gradient-to-br from-blue-50 to-cyan-50">
                    <div class="flex items-start justify-between mb-4">
                        <div class="size-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-md">
                            <i data-lucide="trending-up" class="size-6 text-white"></i>
                        </div>
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Analytics</span>
                    </div>
                    <h3 class="font-semibold text-lg mb-2 text-gray-800">Progress Tracker</h3>
                    <p class="text-sm text-gray-600 mb-4">View your mood trends, statistics, and insights over time</p>
                    <div class="w-full border border-blue-300 text-blue-600 py-2 rounded-xl text-center hover:bg-blue-50 font-medium">View Progress</div>
                </a>

                <!-- Exercise Log Widget -->
                <a href="exercises.php" class="p-6 border-2 border-teal-200 rounded-3xl hover:border-teal-400 transition-all cursor-pointer hover:shadow-xl hover:scale-105 transform duration-300 bg-gradient-to-br from-teal-50 to-green-50">
                    <div class="flex items-start justify-between mb-4">
                        <div class="size-12 bg-gradient-to-br from-teal-500 to-green-500 rounded-xl flex items-center justify-center shadow-md">
                            <i data-lucide="dumbbell" class="size-6 text-white"></i>
                        </div>
                        <span class="text-xs bg-teal-100 text-teal-700 px-2 py-1 rounded-full font-medium">History</span>
                    </div>
                    <h3 class="font-semibold text-lg mb-2 text-gray-800">Exercise Log</h3>
                    <p class="text-sm text-gray-600 mb-4">Review past exercises, your feedback, and success stories</p>
                    <div class="w-full border border-teal-300 text-teal-600 py-2 rounded-xl text-center hover:bg-teal-50 font-medium">View Exercises</div>
                </a>

                <!-- Profile Widget -->
                <a href="profile.php" class="p-6 border-2 border-pink-200 rounded-3xl hover:border-pink-400 transition-all cursor-pointer hover:shadow-xl hover:scale-105 transform duration-300 bg-gradient-to-br from-pink-50 to-rose-50">
                    <div class="flex items-start justify-between mb-4">
                        <div class="size-12 bg-gradient-to-br from-pink-500 to-rose-500 rounded-xl flex items-center justify-center shadow-md">
                            <i data-lucide="user" class="size-6 text-white"></i>
                        </div>
                        <span class="text-xs bg-pink-100 text-pink-700 px-2 py-1 rounded-full font-medium">Settings</span>
                    </div>
                    <h3 class="font-semibold text-lg mb-2 text-gray-800">Profile & Settings</h3>
                    <p class="text-sm text-gray-600 mb-4">Manage your account, preferences, and emergency contacts</p>
                    <div class="w-full border border-pink-300 text-pink-600 py-2 rounded-xl text-center hover:bg-pink-50 font-medium">Manage Profile</div>
                </a>
            </div>

            <!-- Bottom Row -->
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Emergency Contacts Widget -->
                <a href="details.php" class="p-6 border-2 border-amber-200 rounded-3xl hover:border-amber-400 transition-all cursor-pointer hover:shadow-xl hover:scale-105 transform duration-300 bg-gradient-to-br from-amber-50 to-orange-50">
                    <div class="flex items-start justify-between mb-4">
                        <div class="size-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center shadow-md">
                            <i data-lucide="file-text" class="size-6 text-white"></i>
                        </div>
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-full font-medium">Important</span>
                    </div>
                    <h3 class="font-semibold text-lg mb-2 text-gray-800">Emergency Contacts</h3>
                    <p class="text-sm text-gray-600 mb-4">Add trusted contacts who can be reached in crisis situations</p>
                    <div class="w-full border border-amber-300 text-amber-600 py-2 rounded-xl text-center hover:bg-amber-50 font-medium">Add Contacts</div>
                </a>

                <!-- Quick Tips Card -->
                <div class="p-6 bg-gradient-to-br from-purple-500/10 to-blue-500/10 border-2 border-purple-200 rounded-3xl">
                    <h3 class="font-semibold text-xl mb-4 text-gray-800">💡 Quick Tips</h3>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-center gap-2">
                            <div class="size-2 bg-purple-400 rounded-full"></div>
                            Take breaks every 45 minutes
                        </li>
                        <li class="flex items-center gap-2">
                            <div class="size-2 bg-blue-400 rounded-full"></div>
                            Practice deep breathing daily
                        </li>
                        <li class="flex items-center gap-2">
                            <div class="size-2 bg-teal-400 rounded-full"></div>
                            Stay hydrated (8 glasses/day)
                        </li>
                        <li class="flex items-center gap-2">
                            <div class="size-2 bg-green-400 rounded-full"></div>
                            Get 7-8 hours of sleep
                        </li>
                        <li class="flex items-center gap-2">
                            <div class="size-2 bg-pink-400 rounded-full"></div>
                            Connect with friends regularly
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Motivational Quote -->
        <div class="mt-8 p-6 bg-gradient-to-r from-purple-500/10 via-blue-500/10 to-teal-500/10 border-2 border-purple-200 rounded-3xl text-center">
            <p class="text-lg italic text-gray-700 mb-2">
                "You don't have to be positive all the time. It's perfectly okay to feel sad, angry, annoyed, frustrated, scared, or anxious. Having feelings doesn't make you a negative person. It makes you human."
            </p>
            <p class="text-sm text-gray-600">- Lori Deschene</p>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>