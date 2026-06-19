<?php
require_once __DIR__ . '/php-backend/init.php';
include_once 'includes/header.php';
Auth::checkParentLogin();

$user = Session::getUser();
$pid = $_SESSION['user_id'];
$sid = (int)Session::get('active_student_id');

$reports = [];

if (isset($pdo) && $pdo instanceof PDO && $sid > 0) {
    try {
        // Fetch stored AI reports
        $stmt = $pdo->prepare("
            SELECT id, report_date, mood_score 
            FROM parent_detailed_reports 
            WHERE student_id = ? 
            ORDER BY report_date DESC
        ");
        $stmt->execute([$sid]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
    }
}
?>

<div class="min-h-screen">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4">
        <a href="parent-dashboard.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to Dashboard
        </a>
    </div>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-10">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Daily Wellness History</h2>
                <p class="text-gray-500 font-medium mt-2">View automated AI reports generated from your child's daily interactions.</p>
            </div>

            <?php if (empty($reports)): ?>
                <div class="bg-white/50 border-2 border-dashed border-indigo-200 rounded-[2.5rem] p-16 text-center">
                    <div class="size-20 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-indigo-400">
                        <i data-lucide="file-text" class="size-10"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700">No reports generated yet</h3>
                    <p class="text-gray-500 mt-2 max-w-xs mx-auto">Reports are automatically created when your child chats with MindPal or completes wellness exercises.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($reports as $r): ?>
                        <a href="parent-report-view.php?id=<?php echo $r['id']; ?>" 
                           class="flex items-center justify-between p-6 bg-white rounded-3xl border border-indigo-50 hover:border-indigo-300 hover:shadow-xl hover:shadow-indigo-100/50 transition-all group">
                            <div class="flex items-center gap-6">
                                <div class="size-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors shadow-sm">
                                    <i data-lucide="file-text" class="size-7"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-gray-800">Daily Wellness Report</h4>
                                    <p class="text-sm text-gray-500 font-bold uppercase tracking-wider mt-0.5">
                                        <?php echo date('l, F d, Y', strtotime($r['report_date'])); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-8">
                                <div class="text-right">
                                    <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Daily Mood Score</div>
                                    <div class="text-2xl font-black text-indigo-600 tracking-tight">
                                        <?php echo $r['mood_score'] ? $r['mood_score'] . '<span class="text-sm text-indigo-300">/10</span>' : 'N/A'; ?>
                                    </div>
                                </div>
                                <div class="size-10 rounded-full border border-indigo-50 flex items-center justify-center text-indigo-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-all">
                                    <i data-lucide="chevron-right" class="size-5"></i>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include_once 'includes/footer.php'; ?>
</html>
