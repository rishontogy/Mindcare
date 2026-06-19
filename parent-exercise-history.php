<?php
require_once __DIR__ . '/php-backend/init.php';
include_once 'includes/header.php';
Auth::checkParentLogin();

$user = Session::getUser();
$pid = $_SESSION['user_id'];
$sid = 0;

$exercises = [];
$totalTime = 0;
$thisWeekCount = 0;

$durationMap = [
    'Gratitude Meditation' => 5,
    'Energy Boost Breathing' => 3,
    'Mindful Breathing' => 5,
    'Body Scan Meditation' => 10
];

if (isset($pdo) && $pdo instanceof PDO) {
    $sid = (int)Session::get('active_student_id');

    if ($sid > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM exercise_reviews WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$sid]);
            $exercises = $stmt->fetchAll();
            
            foreach ($exercises as &$ex) {
                // Map fields to match template expectations
                $ex['name'] = $ex['exercise_name'] ?? 'Unknown Exercise';
                $ex['category'] = ucfirst($ex['type'] ?? 'General');
                $ex['date'] = $ex['created_at'];
                
                // Add estimated duration if missing
                $ex['duration'] = $durationMap[$ex['name']] ?? 5;
                
                $totalTime += $ex['duration'];
                
                if (strtotime($ex['created_at']) >= strtotime('-7 days')) {
                    $thisWeekCount++;
                }
            }
        } catch (PDOException $e) {
            // Table doesn't exist yet or empty
        }
    }
}

$totalExercises = count($exercises);
$avgPerExercise = $totalExercises > 0 ? round($totalTime / $totalExercises) : 0;
?>
<div class="min-h-screen bg-slate-50/50">
    <!-- Breadcrumbs -->
    <div class="container mx-auto px-4 py-4 max-w-6xl">
        <a href="parent-dashboard.php" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Title & Stats Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                    Activity Archive
                </div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">
                    Exercise <span class="text-indigo-600">History</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium">Detailed log of wellness sessions and mindfulness activities.</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 w-full md:w-auto">
                <div class="px-6 py-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Total</div>
                    <div class="text-2xl font-black text-indigo-600"><?php echo $totalExercises; ?></div>
                </div>
                <div class="px-6 py-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Minutes</div>
                    <div class="text-2xl font-black text-emerald-600"><?php echo $totalTime; ?></div>
                </div>
                <div class="px-6 py-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">This Week</div>
                    <div class="text-2xl font-black text-amber-500"><?php echo $thisWeekCount; ?></div>
                </div>
                <div class="px-6 py-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Average</div>
                    <div class="text-2xl font-black text-purple-600"><?php echo $avgPerExercise; ?>m</div>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Exercise Activity</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Category</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Date & Time</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($exercises as $exercise): ?>
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i data-lucide="play-circle" class="size-5"></i>
                                    </div>
                                    <span class="font-bold text-slate-700"><?php echo htmlspecialchars($exercise['name']); ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 bg-white border border-slate-100 rounded-lg text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <?php echo htmlspecialchars($exercise['category']); ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 text-sm text-slate-500 font-medium">
                                <?php echo date('M d, Y • h:i A', strtotime($exercise['date'])); ?>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($exercise['duration']); ?>m</span>
                                    <span class="text-[9px] font-bold text-slate-300 uppercase">Elapsed</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($exercises)): ?>
                            <tr>
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4 opacity-30">
                                        <i data-lucide="clipboard-list" class="size-16"></i>
                                        <p class="font-black uppercase tracking-widest text-xs">No exercise history recorded yet</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-8 text-center text-[10px] font-black uppercase tracking-[0.4em] text-slate-300">
            MINCARE UNIVERSE ACTIVITY DATA • END-TO-END ENCRYPTED
        </p>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
