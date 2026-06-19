<?php
include_once 'includes/header.php';
redirect_if_not_logged_in();

$user_id = get_current_user_id();
$loading = false;
$history = [];
$stats = [
    'avgScore' => 0,
    'trend' => 'stable',
    'currentLevel' => 'moderate',
    'totalAssessments' => 0
];

try {
    $stmt = $pdo->prepare("SELECT mood, created_at 
                        FROM mood_logs 
                        WHERE user_id = ? 
                        ORDER BY created_at ASC");

    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        // If mood stored as 1–5
        $numeric_map = [
            1 => 10,
            2 => 30,
            3 => 50,
            4 => 80,
            5 => 100
        ];

        $score = $numeric_map[$row['mood']] ?? 50;

        $history[] = [
            'date' => $row['created_at'],
            'moodScore' => $score,
            'moodLevel' => $row['mood']
        ];
    }

    if (count($history) > 0) {
        $total_score = array_sum(array_column($history, 'moodScore'));
        $stats['totalAssessments'] = count($history);
        $stats['avgScore'] = round($total_score / $stats['totalAssessments']);
        $stats['currentLevel'] = end($history)['moodLevel'];

        $recent = array_slice($history, -7);
        $older = array_slice($history, 0, -7);

        $recent_avg = count($recent) > 0 ? array_sum(array_column($recent, 'moodScore')) / count($recent) : 0;
        $older_avg = count($older) > 0 ? array_sum(array_column($older, 'moodScore')) / count($older) : $recent_avg;

        if ($recent_avg > $older_avg + 5)
            $stats['trend'] = 'improving';
        elseif ($recent_avg < $older_avg - 5)
            $stats['trend'] = 'declining';
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$motivationalQuotes = [
    'excellent' => ["You're doing amazing! Keep up the great work!", "Your positive energy is infectious. Shine on!", "You're a inspiration to those around you!"],
    'good' => ["You're on the right track. Keep going!", "Your efforts are paying off. Stay consistent!", "Small steps lead to big changes. You're doing great!"],
    'moderate' => ["Every day is a new opportunity to grow.", "You're stronger than you think. Keep pushing forward.", "Progress, not perfection. You're doing well!"],
    'low' => ["Tough times don't last, but tough people do.", "You're not alone. Reach out when you need support.", "Tomorrow is a new day with new possibilities."],
    'critical' => ["Please reach out for support. You deserve help.", "Your wellbeing matters. Consider professional support.", "Every storm runs out of rain. Better days are ahead."]
];

$quotes = $motivationalQuotes[$stats['currentLevel']] ?? $motivationalQuotes['moderate'];
$quote = $quotes[array_rand($quotes)];
?>

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4">
        <a href="dashboard.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <?php if (empty($history)): ?>
            <div class="p-12 text-center bg-white border border-gray-100 rounded-3xl shadow-sm">
                <div class="size-20 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="trending-up" class="size-10 text-purple-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">No Data Yet</h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Complete your first daily assessment to start tracking your progress and see personalized insights!
                </p>
                <a href="dashboard.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all">
                    Go to Dashboard
                </a>
            </div>
        <?php
        else: ?>
            <!-- Stats Overview -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Average Score</span>
                        <?php if ($stats['trend'] === 'improving'): ?>
                            <i data-lucide="trending-up" class="size-5 text-green-600"></i>
                        <?php
                        elseif ($stats['trend'] === 'declining'): ?>
                            <i data-lucide="trending-down" class="size-5 text-red-600"></i>
                        <?php
                        else: ?>
                            <i data-lucide="minus" class="size-5 text-gray-600"></i>
                        <?php
                        endif; ?>
                    </div>
                    <div class="text-4xl font-black text-purple-600"><?php echo $stats['avgScore']; ?></div>
                    <p class="text-xs text-gray-400 mt-2 font-medium">Out of 100 max score</p>
                </div>

                <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="mb-4">
                        <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Current Level</span>
                    </div>
                    <div class="text-3xl font-black capitalize text-gray-800"><?php echo $stats['currentLevel']; ?></div>
                    <p class="text-xs text-gray-400 mt-2 font-medium">From your latest assessment</p>
                </div>

                <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="mb-4">
                        <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Total Check-ins</span>
                    </div>
                    <div class="text-4xl font-black text-blue-600"><?php echo $stats['totalAssessments']; ?></div>
                    <p class="text-xs text-gray-400 mt-2 font-medium">Completed assessments so far</p>
                </div>
            </div>

            <!-- Trend Status -->
            <div class="p-6 mb-8 rounded-3xl shadow-sm border-2 <?php
                                                                echo $stats['trend'] === 'improving' ? 'bg-green-50 border-green-200' : ($stats['trend'] === 'declining' ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200');
                                                                ?>">
                <div class="flex items-start gap-4">
                    <div class="size-12 rounded-2xl flex items-center justify-center shrink-0 <?php
                                                                                                echo $stats['trend'] === 'improving' ? 'bg-green-100' : ($stats['trend'] === 'declining' ? 'bg-red-100' : 'bg-blue-100');
                                                                                                ?>">
                        <?php if ($stats['trend'] === 'improving'): ?>
                            <i data-lucide="trending-up" class="size-6 text-green-600"></i>
                        <?php
                        elseif ($stats['trend'] === 'declining'): ?>
                            <i data-lucide="trending-down" class="size-6 text-red-600"></i>
                        <?php
                        else: ?>
                            <i data-lucide="minus" class="size-6 text-blue-600"></i>
                        <?php
                        endif; ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-1 <?php
                                                            echo $stats['trend'] === 'improving' ? 'text-green-800' : ($stats['trend'] === 'declining' ? 'text-red-800' : 'text-blue-800');
                                                            ?>">
                            <?php
                            if ($stats['trend'] === 'improving')
                                echo 'Positive Trend!';
                            elseif ($stats['trend'] === 'declining')
                                echo 'Need Extra Support';
                            else
                                echo 'Steady Progress';
                            ?>
                        </h3>
                        <p class="text-sm opacity-80 leading-relaxed <?php
                                                                        echo $stats['trend'] === 'improving' ? 'text-green-700' : ($stats['trend'] === 'declining' ? 'text-red-700' : 'text-blue-700');
                                                                        ?>">
                            <?php
                            if ($stats['trend'] === 'improving')
                                echo 'Your mood scores have been improving. Keep up the great work with your daily exercises!';
                            elseif ($stats['trend'] === 'declining')
                                echo "Your scores have been declining. Consider reaching out to a counselor or trusted friend. You don't have to go through this alone.";
                            else
                                echo "You're maintaining a consistent level. Keep practicing your daily exercises to continue building resilience.";
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mood Chart -->
            <div class="p-8 bg-white border border-gray-100 rounded-3xl shadow-sm mb-8">
                <h3 class="font-bold text-xl mb-6 text-gray-800">Mood Trend (Last 30 Days)</h3>
                <div class="h-80 w-full">
                    <canvas id="moodChart"></canvas>
                </div>
            </div>

            <!-- Motivational Quote -->
            <div class="p-10 text-center bg-gradient-to-r from-purple-500/10 to-blue-500/10 border-2 border-purple-200 rounded-3xl mb-8">
                <h3 class="font-bold text-lg mb-4 text-purple-800 uppercase tracking-widest text-xs">Your Daily Motivation</h3>
                <p class="text-2xl italic text-gray-800 leading-snug font-medium">"<?php echo $quote; ?>"</p>
            </div>

            <!-- Insights -->
            <div class="p-8 bg-indigo-50 border border-indigo-100 rounded-3xl shadow-sm mb-12">
                <h3 class="font-bold text-xl mb-4 text-indigo-900">Insights & Recommendations</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 size-2 bg-indigo-400 rounded-full shrink-0"></div>
                        <p class="text-sm text-indigo-800">
                            <?php
                            if ($stats['avgScore'] >= 70)
                                echo "You're doing exceptionally well! Continue your daily practice to maintain this positive state.";
                            elseif ($stats['avgScore'] >= 50)
                                echo "You're making good progress. Consider increasing your exercise frequency for even better results.";
                            elseif ($stats['avgScore'] >= 30)
                                echo "Keep going! Small consistent actions lead to big improvements over time.";
                            else
                                echo "Please consider seeking additional professional support. Your mental health is important.";
                            ?>
                        </p>
                    </div>
                    <?php if ($stats['trend'] === 'improving'): ?>
                        <div class="flex items-start gap-3">
                            <div class="mt-1 size-2 bg-indigo-400 rounded-full shrink-0"></div>
                            <p class="text-sm text-indigo-800">Your upward trend suggests your coping strategies are working. Keep it up!</p>
                        </div>
                    <?php
                    endif; ?>
                    <div class="flex items-start gap-3">
                        <div class="mt-1 size-2 bg-indigo-400 rounded-full shrink-0"></div>
                        <p class="text-sm text-indigo-800">Try to complete your daily check-in at the same time each day for best results.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-1 size-2 bg-indigo-400 rounded-full shrink-0"></div>
                        <p class="text-sm text-indigo-800">Regular practice of meditation and breathing exercises can improve your baseline mood.</p>
                    </div>
                </div>
            </div>
        <?php
        endif; ?>
    </div>
</div>

<script>
    <?php
    $labels = [];
    $data = [];
    foreach ($history as $item) {
        $labels[] = date('M j', strtotime($item['date']));
        $data[] = $item['moodScore'];
    }
    ?>

    const ctx = document.getElementById('moodChart').getContext('2d');
    const moodChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Mood Score',
                data: <?php echo json_encode($data); ?>,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 4,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            family: 'Inter',
                            size: 12
                        },
                        color: '#6b7280'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter',
                            size: 12
                        },
                        color: '#6b7280'
                    }
                }
            }
        }
    });
</script>
<?php include_once 'includes/footer.php'; ?>