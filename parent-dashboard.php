<?php
require_once __DIR__ . '/php-backend/init.php';
include_once 'includes/header.php';

// Check login (role check already in init.php or manual)
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'parent') {
    header("Location: parent-login.php");
    exit();
}

$user = Session::getUser();
$userId = $user['user_id'];
$parentName = $user['user_name'] ?? 'Parent';
$today = date('l, F d, Y');

// Fetch linked students (accepted only) - Use $pdo
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email 
    FROM users u 
    JOIN parent_student_links psl ON u.id = psl.student_id 
    WHERE psl.parent_id = :parent_id AND psl.status = 'accepted'
");
$stmt->execute([':parent_id' => $userId]);
$linkedStudents = $stmt->fetchAll();

// Handle active student switching
if (isset($_GET['set_active'])) {
    $newActiveId = intval($_GET['set_active']);
    // Verify this student belongs to the parent and is accepted
    $checkStmt = $pdo->prepare("SELECT 1 FROM parent_student_links WHERE parent_id = :parent_id AND student_id = :student_id AND status = 'accepted'");
    $checkStmt->execute([':parent_id' => $userId, ':student_id' => $newActiveId]);
    if ($checkStmt->fetch()) {
        Session::set('active_student_id', $newActiveId);
        header('Location: parent-dashboard.php');
        exit;
    }
}

$activeStudentId = Session::get('active_student_id');

// If no active student is set, pick the first one from the linked list
if (!$activeStudentId && !empty($linkedStudents)) {
    $activeStudentId = $linkedStudents[0]['id'];
    Session::set('active_student_id', $activeStudentId);
}

// Get active student details
$activeStudentName = 'Select a student';
if ($activeStudentId) {
    foreach ($linkedStudents as $s) {
        if ($s['id'] == $activeStudentId) {
            $activeStudentName = $s['name'];
            break;
        }
    }
}
?>

<div class="min-h-screen">
    <!-- Sub-header for student switching -->
    <div class="bg-indigo-50 border-b border-indigo-100">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Monitoring:</span>
                <select onchange="window.location.href='?set_active='+this.value" class="bg-transparent text-sm font-bold text-indigo-700 outline-none cursor-pointer">
                    <?php if (empty($linkedStudents)): ?>
                        <option>No students linked</option>
                    <?php else: ?>
                        <?php foreach ($linkedStudents as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $s['id'] == $activeStudentId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="text-xs text-indigo-400 font-medium">
                Viewing data for <span class="font-bold"><?php echo htmlspecialchars($activeStudentName); ?></span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="mb-10">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2">
                Welcome, <?php echo htmlspecialchars($parentName); ?>! 👋
            </h2>
            <p class="text-gray-600 max-w-2xl font-medium italic">
                Supporting your child's mental wellness journey with love, data, and cosmic insights.
            </p>
        </div>

        <!-- Dashboard Widgets -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Mood Trends Widget -->
            <div onclick="window.location.href='parent-mood-chart.php'"
                class="p-8 bg-white rounded-[2rem] border-2 border-indigo-100 hover:border-indigo-400 transition-all cursor-pointer hover:shadow-2xl shadow-indigo-100/50 group relative overflow-hidden">
                <div class="absolute -top-12 -right-12 size-32 bg-indigo-50 rounded-full transition-transform group-hover:scale-150 duration-500"></div>
                <div class="relative z-10">
                    <div class="flex items-start justify-between mb-6">
                        <div class="size-14 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors shadow-lg shadow-indigo-100">
                            <i data-lucide="line-chart" class="size-7"></i>
                        </div>
                        <span class="text-[10px] bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full font-black uppercase tracking-widest">Wellness</span>
                    </div>
                    <h3 class="font-black text-xl mb-2 text-gray-800 tracking-tight">Mood Trends</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed font-medium">
                        Visualize your child's mood patterns and progress over the weeks.
                    </p>
                    <button class="w-full py-4 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-2xl font-black shadow-xl shadow-indigo-200 hover:scale-[1.02] active:scale-95 transition-all">
                        View Charts
                    </button>
                </div>
            </div>

            <!-- Exercise History -->
            <div onclick="window.location.href='parent-exercise-history.php'"
                class="p-8 bg-white rounded-[2rem] border-2 border-emerald-100 hover:border-emerald-400 transition-all cursor-pointer hover:shadow-2xl shadow-emerald-100/50 group relative overflow-hidden">
                <div class="absolute -bottom-16 -left-16 size-40 bg-emerald-50 rounded-full transition-transform group-hover:scale-125 duration-700"></div>
                <div class="relative z-10">
                    <div class="flex items-start justify-between mb-6">
                        <div class="size-14 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors shadow-lg shadow-emerald-100">
                            <i data-lucide="activity" class="size-7"></i>
                        </div>
                        <span class="text-[10px] bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-black uppercase tracking-widest">Activities</span>
                    </div>
                    <h3 class="font-black text-xl mb-2 text-gray-800 tracking-tight">Exercise History</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed font-medium">
                        See which guided meditations and exercises your child has completed.
                    </p>
                    <button class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-black shadow-xl shadow-emerald-200 hover:scale-[1.02] active:scale-95 transition-all">
                        View History
                    </button>
                </div>
            </div>

            <!-- AI Assessment -->
            <div onclick="window.location.href='parent-ai-assessment.php'"
                class="p-8 bg-white rounded-[2rem] border-2 border-purple-100 hover:border-purple-400 transition-all cursor-pointer hover:shadow-2xl shadow-purple-100/50 group relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-start justify-between mb-6">
                        <div class="size-14 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-200 group-hover:rotate-12 transition-transform">
                            <i data-lucide="sparkles" class="size-7"></i>
                        </div>
                        <span class="text-[10px] bg-purple-100 text-purple-700 px-3 py-1 rounded-full font-black uppercase tracking-widest">AI Insights</span>
                    </div>
                    <h3 class="font-black text-xl mb-2 text-gray-800 tracking-tight">AI Assessment</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed font-medium">
                        Get advanced analysis and recommendations powered by our AI assistant.
                    </p>
                    <button class="w-full py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-purple-200 hover:scale-[1.02] active:scale-95 transition-all">
                        Analyze Now
                    </button>
                </div>
            </div>

            <!-- Mood Reports -->
            <div onclick="window.location.href='parent-mood-details.php'"
                class="p-8 bg-white rounded-[2rem] border-2 border-amber-100 hover:border-amber-400 transition-all cursor-pointer hover:shadow-2xl shadow-amber-100/50 group">
                <div class="flex items-start justify-between mb-6">
                    <div class="size-14 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all shadow-lg shadow-amber-100">
                        <i data-lucide="file-text" class="size-7"></i>
                    </div>
                    <span class="text-[10px] bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-black uppercase tracking-widest">Reports</span>
                </div>
                <h3 class="font-black text-xl mb-2 text-gray-800 tracking-tight">Detailed Reports</h3>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed font-medium">
                    Access deep-dive reports on mood variance and behavioral trends.
                </p>
                <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100 mb-4 group-hover:bg-amber-100 transition-colors">
                    <div class="text-2xl font-black text-amber-600">Weekly Summary</div>
                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">Available Every Sunday</div>
                </div>
                <button class="w-full py-3 border-2 border-amber-100 text-amber-600 rounded-2xl font-black hover:bg-amber-50 transition-all flex items-center justify-center gap-2">
                    View Reports <i data-lucide="chevron-right" class="size-4"></i>
                </button>
            </div>

            <!-- Profile Settings -->
            <div onclick="window.location.href='parent-profile.php'"
                class="p-8 bg-white rounded-[2rem] border-2 border-blue-100 hover:border-blue-400 transition-all cursor-pointer hover:shadow-2xl shadow-blue-100/50 group">
                <div class="flex items-start justify-between mb-6">
                    <div class="size-14 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-lg shadow-blue-100">
                        <i data-lucide="settings" class="size-7"></i>
                    </div>
                    <span class="text-[10px] bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-black uppercase tracking-widest">Settings</span>
                </div>
                <h3 class="font-black text-xl mb-2 text-gray-800 tracking-tight">My Profile</h3>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed font-medium">
                    Manage your notification preferences, child's data access, and account.
                </p>
                <br><br>
                <button class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-blue-100 hover:scale-[1.02] transition-all">
                    Account Settings
                </button>
            </div>

            <!-- Emergency / Help -->
            <div class="p-8 bg-gradient-to-br from-red-600/10 to-orange-600/10 rounded-[2rem] border-2 border-red-200 flex flex-col justify-between">
                <div>
                    <h3 class="font-black text-xl mb-4 text-gray-800 flex items-center gap-2">
                        <i data-lucide="life-buoy" class="text-red-600 font-black"></i>
                        Support Resources
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm font-bold text-gray-700">
                            <span class="size-2 bg-red-500 rounded-full shrink-0"></span>
                            Emergency Crisis Lines
                        </li>
                        <li class="flex items-center gap-3 text-sm font-bold text-gray-700">
                            <span class="size-2 bg-orange-500 rounded-full shrink-0"></span>
                            Parent Support Groups
                        </li>
                        <li class="flex items-center gap-3 text-sm font-bold text-gray-700">
                            <span class="size-2 bg-yellow-500 rounded-full shrink-0"></span>
                            Professional Referrals
                        </li>
                    </ul>
                </div>
                <button class="mt-8 py-3 bg-red-600 text-white rounded-2xl font-black shadow-lg shadow-red-200 hover:bg-red-700 transition-all flex items-center justify-center gap-2">
                    Get Help Now <i data-lucide="arrow-right-circle" class="size-4"></i>
                </button>
            </div>
        </div>

        <!-- Summary Stats Section -->
        <?php
        $qs_avg_mood = '–';
        $qs_exercises = 0;
        $qs_assessments = 0;
        $qs_streak = 0;

        if (isset($pdo) && $pdo instanceof PDO) {
            $sid = (int)($activeStudentId ?: 0);

            if ($sid > 0) {
                // Avg Mood (0-10, last 7 days)
                $s = $pdo->prepare("
                    SELECT ROUND(AVG(score),1) 
                    FROM (
                        SELECT avg_mood AS score, summary_date as created_at FROM chat_summary WHERE user_id = :sid
                        UNION ALL
                        SELECT mood_score AS score, created_at FROM mood_assessments WHERE user_id = :sid
                    ) AS c
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ");
                $s->execute(['sid' => $sid]);
                $m = $s->fetchColumn();
                if ($m !== null) $qs_avg_mood = $m;

                // Exercises this week (using user_exercises table)
                $s = $pdo->prepare("SELECT COUNT(*) FROM user_exercises WHERE user_id=? AND completed_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)");
                $s->execute([$sid]);
                $qs_exercises = (int)$s->fetchColumn();

                // Check-in days this week
                $s = $pdo->prepare("
                    SELECT COUNT(DISTINCT DATE(created_at)) 
                    FROM (
                        SELECT summary_date as created_at FROM chat_summary WHERE user_id = :sid
                        UNION ALL
                        SELECT created_at FROM mood_assessments WHERE user_id = :sid
                    ) AS unified
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ");
                $s->execute(['sid' => $sid]);
                $qs_assessments = (int)$s->fetchColumn();

                // Streak (last 30 days)
                $s = $pdo->prepare("
                    SELECT DISTINCT DATE(created_at) AS d 
                    FROM (
                        SELECT summary_date as created_at FROM chat_summary WHERE user_id = :sid
                        UNION ALL
                        SELECT created_at FROM mood_assessments WHERE user_id = :sid
                    ) AS u
                    ORDER BY d DESC
                ");
                $s->execute(['sid' => $sid]);
                $prev = date('Y-m-d');
                while ($r = $s->fetch()) {
                    $cur = $r['d'];
                    if ($cur === $prev || $cur === date('Y-m-d', strtotime($prev . ' -1 day'))) {
                        $qs_streak++;
                        $prev = $cur;
                    } else break;
                }
            }
        }
        ?>
        <div class="mt-12 bg-white rounded-[2.5rem] border border-gray-100 p-8 shadow-xl shadow-gray-100/50">
            <h3 class="font-black text-2xl mb-8 text-gray-800 tracking-tight">Quick Summary for <?php echo htmlspecialchars($activeStudentName); ?></h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="p-6 bg-indigo-50 rounded-3xl border border-indigo-100">
                    <div class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-2">Avg Mood (Out of 10)</div>
                    <div class="text-4xl font-black text-indigo-600 tracking-tighter"><?php echo $qs_avg_mood; ?><span class="text-lg text-indigo-300">/10</span></div>
                </div>
                <div class="p-6 bg-emerald-50 rounded-3xl border border-emerald-100">
                    <div class="text-[10px] text-emerald-400 font-black uppercase tracking-widest mb-2">Exercised</div>
                    <div class="text-4xl font-black text-emerald-600 tracking-tighter"><?php echo $qs_exercises; ?><span class="text-lg text-emerald-300">/WK</span></div>
                </div>
                <div class="p-6 bg-purple-50 rounded-3xl border border-purple-100">
                    <div class="text-[10px] text-purple-400 font-black uppercase tracking-widest mb-2">Assessments</div>
                    <div class="text-4xl font-black text-purple-600 tracking-tighter"><?php echo $qs_assessments; ?><span class="text-lg text-purple-300">/7</span></div>
                </div>
                <div class="p-6 bg-blue-50 rounded-3xl border border-blue-100">
                    <div class="text-[10px] text-blue-400 font-black uppercase tracking-widest mb-2">Streak</div>
                    <div class="text-4xl font-black text-blue-600 tracking-tighter"><?php echo $qs_streak; ?><span class="text-lg text-blue-300">DAYS</span></div>
                </div>
            </div>
        </div>
    </div>

<?php include_once 'includes/footer.php'; ?>
</body>

</html>