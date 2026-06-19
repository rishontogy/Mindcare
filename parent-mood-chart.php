<?php
require_once __DIR__ . '/php-backend/init.php';
include_once 'includes/header.php';

// Check login
Auth::checkParentLogin();

$user = Session::getUser();

// ── Handle Student Switching on this page too ──────────────────
if (isset($_GET['set_active'])) {
    $newActiveId = intval($_GET['set_active']);
    Session::set('active_student_id', $newActiveId);
    header('Location: parent-mood-chart.php');
    exit;
}

$activeStudentId = Session::get('active_student_id');

// Fetch all linked students for the switcher
$userId = $user['user_id'];
$stmt = $pdo->prepare("
    SELECT u.id, u.name 
    FROM users u 
    JOIN parent_student_links psl ON u.id = psl.student_id 
    WHERE psl.parent_id = :parent_id AND psl.status = 'accepted'
");
$stmt->execute([':parent_id' => $userId]);
$linkedStudents = $stmt->fetchAll();

if (!$activeStudentId && !empty($linkedStudents)) {
    $activeStudentId = $linkedStudents[0]['id'];
    Session::set('active_student_id', $activeStudentId);
}

// ── Prepare Boot Data for Zero Latency ──────────────────────────
$studentName = "your child";
$weekLabels  = [];
$weekScores  = [];
$monthWeekAvgs = [0,0,0,0];
$dist = [0,0,0,0];
$statAvg = '–'; $statPeak = '–'; $statLowest = '–'; $statExercises = 0; $insightAvgPct = 0;

if ($activeStudentId) {
    // 1. Name
    foreach($linkedStudents as $s) if($s['id'] == $activeStudentId) $studentName = $s['name'];

    // 2. Weekly (Union)
    $qW = "SELECT dy, ROUND(AVG(score),1) as m FROM (
        SELECT DATE(summary_date) as dy, avg_mood as score FROM chat_summary WHERE user_id = ?
        UNION ALL
        SELECT DATE(created_at) as dy, mood_score as score FROM mood_assessments WHERE user_id = ?
    ) as c WHERE dy >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY dy ORDER BY dy ASC";
    $st = $pdo->prepare($qW); $st->execute([$activeStudentId, $activeStudentId]);
    $rows = $st->fetchAll();
    $map = []; for($i=6; $i>=0; $i--) $map[date('Y-m-d', strtotime("-$i days"))] = 0;
    foreach($rows as $r) $map[$r['dy']] = (float)$r['m'];
    foreach($map as $d => $v) { $weekLabels[] = date('D j', strtotime($d)); $weekScores[] = $v; }

    // 3. Stats & Dist (Current Month)
    $qD = "SELECT score FROM (
        SELECT avg_mood as score, summary_date as created_at FROM chat_summary WHERE user_id = ?
        UNION ALL
        SELECT mood_score as score, created_at FROM mood_assessments WHERE user_id = ?
    ) as c WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())";
    $st = $pdo->prepare($qD); $st->execute([$activeStudentId, $activeStudentId]);
    $all = $st->fetchAll(PDO::FETCH_COLUMN);
    $total = count($all);
    if($total > 0){
        $sum = 0; $max = 0; $min = 10;
        foreach($all as $v){
            $sum += $v; if($v>$max) $max = $v; if($v<$min) $min = $v;
            if($v>=8) $dist[0]++; elseif($v>=6) $dist[1]++; elseif($v>=4) $dist[2]++; else $dist[3]++;
        }
        $statAvg = round($sum/$total, 1); $statPeak = $max; $statLowest = $min;
        $insightAvgPct = round(($dist[0]+$dist[1])/$total*100);
    }
    // Exercises
    $st = $pdo->prepare("SELECT COUNT(*) FROM user_exercises WHERE user_id=? AND MONTH(completed_at)=MONTH(NOW())");
    $st->execute([$activeStudentId]); $statExercises = (int)$st->fetchColumn();
}

$jsBoot = [
    'studentName' => $studentName,
    'weekLabels' => $weekLabels,
    'weekScores' => $weekScores,
    'monthWeekAvgs' => [0,0,0, $statAvg], // Simplified for boot
    'dist' => $dist,
    'distPct' => array_map(fn($v) => ($total>0 ? round($v/$total*100).'%' : '0%'), $dist),
    'statAvg' => $statAvg,
    'statPeak' => $statPeak,
    'statLowest' => $statLowest,
    'statExercises' => $statExercises,
    'insightAvgPct' => $insightAvgPct,
    'insightPeakDay' => 'Today',
    'insightLowDay' => 'Yesterday'
];

?>

<style>
    .chart-container {
        position: relative;
        height: 320px;
        width: 100%;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 pb-12">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4 max-w-6xl">
        <a href="parent-dashboard.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Title Section -->
        <div class="mb-10 animate-fade-in">
            <div class="flex items-center gap-4 mb-2">
                <div class="size-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                    <i data-lucide="line-chart" class="size-6"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">
                        Mood Analytics
                    </h1>
                    <p class="text-gray-500 font-medium">Tracking wellness trends for <span class="text-emerald-600 font-bold"><?php echo htmlspecialchars($studentName); ?></span></p>
                </div>
            </div>
        </div>

        <!-- Weekly Mood Trend -->
        <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="trending-up" class="size-5 text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">This Week's Mood Trend</h2>
                    <p class="text-sm text-gray-600">Daily mood scores and exercise completion</p>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="weeklyChart"></canvas>
            </div>

            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 transition-all hover:shadow-md">
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Average Score</p>
                    <p id="statAvg" class="text-2xl font-bold text-gray-800">–</p>
                </div>
                <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-100 transition-all hover:shadow-md">
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Peak Score</p>
                    <p id="statPeak" class="text-2xl font-bold text-gray-800">–</p>
                </div>
                <div class="bg-amber-50 rounded-lg p-4 border border-amber-100 transition-all hover:shadow-md">
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-1">Lowest Score</p>
                    <p id="statLowest" class="text-2xl font-bold text-gray-800">–</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-100 transition-all hover:shadow-md">
                    <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider mb-1">Total Exercises</p>
                    <p id="statExercises" class="text-2xl font-bold text-gray-800">0</p>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Monthly Overview -->
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="calendar" class="size-5 text-green-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Monthly Overview</h2>
                        <p class="text-sm text-gray-600">Average mood scores per week</p>
                    </div>
                </div>

                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Mood Distribution -->
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl">📊</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Mood Distribution</h2>
                        <p class="text-sm text-gray-600">Breakdown of mood patterns this month</p>
                    </div>
                </div>

                <div class="chart-container">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Specific Stats Grid -->
        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200 text-center transition-all hover:scale-105">
                <p class="text-sm text-gray-600 mb-1">Great Days</p>
                <p id="distGreatCount" class="text-2xl font-bold text-emerald-600">0</p>
                <p id="distGreatPct" class="text-xs text-emerald-500 font-medium">0%</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200 text-center transition-all hover:scale-105">
                <p class="text-sm text-gray-600 mb-1">Good Days</p>
                <p id="distGoodCount" class="text-2xl font-bold text-blue-600">0</p>
                <p id="distGoodPct" class="text-xs text-blue-500 font-medium">0%</p>
            </div>
            <div class="bg-amber-50 rounded-xl p-4 border border-amber-200 text-center transition-all hover:scale-105">
                <p class="text-sm text-gray-600 mb-1">Okay Days</p>
                <p id="distOkayCount" class="text-2xl font-bold text-amber-600">0</p>
                <p id="distOkayPct" class="text-xs text-amber-500 font-medium">0%</p>
            </div>
            <div class="bg-rose-50 rounded-xl p-4 border border-rose-200 text-center transition-all hover:scale-105">
                <p class="text-sm text-gray-600 mb-1">Stressed Days</p>
                <p id="distStressCount" class="text-2xl font-bold text-rose-600">0</p>
                <p id="distStressPct" class="text-xs text-rose-500 font-medium">0%</p>
            </div>
        </div>

        <!-- Insights -->
        <div class="mt-8 p-6 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-lg text-white">
            <h3 class="font-bold text-xl mb-4 flex items-center gap-2">
                <i data-lucide="lightbulb" class="size-6 text-yellow-300"></i>
                Key Insights
            </h3>
            <ul class="space-y-3">
                <li class="flex items-start gap-2 bg-white/10 p-3 rounded-lg backdrop-blur-sm">
                    <span class="text-blue-200 mt-1">•</span>
                    <p class="text-sm"><span id="insightPct">0%</span> of check-ins this month were good-to-great days for <span id="insightName">your child</span>.</p>
                </li>
                <li class="flex items-start gap-2 bg-white/10 p-3 rounded-lg backdrop-blur-sm">
                    <span class="text-blue-200 mt-1">•</span>
                    <p class="text-sm"><span id="insightPeakDay">–</span> shows the highest mood score (<span id="insightPeakScore">–</span>/10) this week.</p>
                </li>
                <li class="flex items-start gap-2 bg-white/10 p-3 rounded-lg backdrop-blur-sm">
                    <span class="text-blue-200 mt-1">•</span>
                    <p class="text-sm"><span id="insightLowDay">–</span> had the lowest score (<span id="insightLowScore">–</span>/10) — consider checking in on that day.</p>
                </li>
                <li class="flex i                    <p class="text-sm"><span id="insightEx">0</span> exercise<span id="insightExS">s</span> completed this month — keep encouraging daily activity!</p>
                </li>
            </ul>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const bootData = <?php echo json_encode($jsBoot); ?>;

        function initCharts(d) {
            const colors = {
                primary: '#3b82f6',
                success: '#10b981',
                warning: '#f59e0b',
                danger: '#f43f5e'
            };

            // Remove existing charts if any
            Chart.getChart('weeklyChart')?.destroy();
            Chart.getChart('monthlyChart')?.destroy();
            Chart.getChart('distributionChart')?.destroy();

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 10, grid: { color: 'rgba(0,0,0,0.03)' } },
                    x: { grid: { display: false } }
                }
            };

            // 1. Weekly Line
            new Chart(document.getElementById('weeklyChart'), {
                type: 'line',
                data: {
                    labels: d.weekLabels,
                    datasets: [{
                        data: d.weekScores,
                        borderColor: colors.primary,
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        borderWidth: 4,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 8
                    }]
                },
                options: commonOptions
            });

            // 2. Monthly Bar
            new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        data: d.monthWeekAvgs,
                        backgroundColor: colors.success,
                        borderRadius: 10
                    }]
                },
                options: commonOptions
            });

            // 3. Distribution Doughnut (The Interactive Part)
            const total = d.dist.reduce((a,b)=>a+b, 0);
            new Chart(document.getElementById('distributionChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Great', 'Good', 'Okay', 'Stressed'],
                    datasets: [{
                        data: d.dist,
                        backgroundColor: [colors.success, colors.primary, colors.warning, colors.danger],
                        borderWidth: 0,
                        hoverOffset: 20
                    }]
                },
                options: {
                    ...commonOptions,
                    cutout: '75%',
                    plugins: { 
                        legend: { display: true, position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (c) => ` ${c.label}: ${c.parsed} logs (${total>0?Math.round(c.parsed/total*100):0}%)`
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'centerText',
                    beforeDraw(chart) {
                        const { ctx, width, height } = chart;
                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.font = 'bold 12px Inter';
                        ctx.fillStyle = '#9ca3af';
                        ctx.fillText('ACTIVITY', width/2, height/2 - 10);
                        ctx.font = 'black 28px Inter';
                        ctx.fillStyle = '#111827';
                        ctx.fillText(total, width/2, height/2 + 15);
                        ctx.restore();
                    }
                }]
            });
        }

        function updateFullUI(d) {
            // Update text elements
            document.getElementById('statAvg').textContent = d.statAvg;
            document.getElementById('statPeak').textContent = d.statPeak;
            document.getElementById('statLowest').textContent = d.statLowest;
            document.getElementById('statExercises').textContent = d.statExercises;

            const cIds = ['distGreatCount', 'distGoodCount', 'distOkayCount', 'distStressCount'];
            const pIds = ['distGreatPct', 'distGoodPct', 'distOkayPct', 'distStressPct'];
            d.dist.forEach((v, i) => {
                document.getElementById(cIds[i]).textContent = v;
                document.getElementById(pIds[i]).textContent = d.distPct[i];
            });

            document.getElementById('insightPct').textContent = d.insightAvgPct + '%';
            document.getElementById('insightName').textContent = d.studentName;
            document.getElementById('insightPeakDay').textContent = d.insightPeakDay;
            document.getElementById('insightPeakScore').textContent = d.statPeak;
            document.getElementById('insightLowDay').textContent = d.insightLowDay;
            document.getElementById('insightLowScore').textContent = d.statLowest;
            document.getElementById('insightEx').textContent = d.statExercises;
            document.getElementById('insightExS').textContent = d.statExercises != 1 ? 's' : '';

            initCharts(d);
        }

        // Initial render with boot data
        updateFullUI(bootData);

        // Fetch fresh data
        fetch('parent-mood-data.php')
            .then(r => r.json())
            .then(d => { if(!d.error) updateFullUI(d); })
            .catch(e => console.warn('Fetch error:', e));
    </script>
</body>
</html>
  .catch(() => console.warn('Could not load mood data — showing defaults.'));
        </script>
</body>

</html>


</html>