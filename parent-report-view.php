<?php
require_once __DIR__ . '/php-backend/init.php';
include_once 'includes/header.php';
Auth::checkParentLogin();

$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$report = null;

if (isset($pdo) && $pdo instanceof PDO && $reportId > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, u.name as student_name 
            FROM parent_detailed_reports r
            JOIN users u ON r.student_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reportId]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);

        // Security check: ensure this parent is linked to the student
        if ($report) {
            $checkStmt = $pdo->prepare("SELECT 1 FROM parent_student_links WHERE parent_id = ? AND student_id = ?");
            $checkStmt->execute([$_SESSION['user_id'], $report['student_id']]);
            if (!$checkStmt->fetch()) {
                $report = null; // Unauthorized
            }
        }
    } catch (PDOException $e) {
    }
}

if (!$report) {
    header("Location: parent-mood-details.php");
    exit;
}

$suggestions = json_decode($report['suggestions'], true) ?: [];
?>
<div class="min-h-screen bg-[#fcfcfc]">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4 max-w-4xl flex justify-between items-center">
        <a href="parent-mood-details.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to History
        </a>
        <button onclick="window.print()" class="px-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center gap-2 print:hidden">
            <i data-lucide="printer" class="size-4"></i> Export PDF
        </button>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- The Report Document -->
        <div class="bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-indigo-50/50 overflow-hidden">
            <!-- Header Section -->
            <div class="p-12 bg-gradient-to-br from-indigo-50 via-white to-purple-50 border-b border-slate-50 relative overflow-hidden">
                <div class="absolute top-0 right-0 size-64 bg-indigo-200/20 blur-3xl -mr-32 -mt-32 rounded-full"></div>
                <div class="absolute bottom-0 left-0 size-48 bg-purple-200/20 blur-3xl -ml-24 -mb-24 rounded-full"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div>
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-black uppercase tracking-widest mb-6">
                            Verified AI Assessment
                        </div>
                        <h1 class="text-5xl md:text-6xl text-slate-900 leading-tight font-serif">
                            Daily Wellness <br><span class="text-indigo-600">Document</span>
                        </h1>
                        <p class="mt-4 text-slate-500 font-medium">Prepared for the guardians of <span class="text-slate-900 font-bold"><?php echo htmlspecialchars($report['student_name']); ?></span></p>
                    </div>
                    
                    <div class="flex flex-col items-center justify-center p-8 bg-white rounded-[2.5rem] shadow-xl shadow-indigo-100/50 border border-indigo-50 min-w-[200px]">
                        <div class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-2">Mood Score</div>
                        <div class="text-6xl font-black text-indigo-600 tracking-tighter">
                            <?php echo $report['mood_score'] ? $report['mood_score'] : 'N/A'; ?><span class="text-xl text-indigo-300">/10</span>
                        </div>
                        <div class="mt-4 px-4 py-1 bg-green-50 text-green-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                            <?php 
                                $ms = (float)$report['mood_score'];
                                if ($ms >= 8) echo "Stable & Happy";
                                elseif ($ms >= 6) echo "Generally Positive";
                                elseif ($ms >= 4) echo "Showing Resilience";
                                else echo "Needs Attention";
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="mt-12 flex items-center gap-12 text-sm text-slate-400">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar" class="size-4"></i>
                        <span class="font-bold uppercase tracking-wider"><?php echo date('F d, Y', strtotime($report['report_date'])); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="brain-circuit" class="size-4 text-indigo-400"></i>
                        <span class="font-bold uppercase tracking-wider">MindCare Universe Core 3.3</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-12 space-y-16">
                <!-- Section 1: Overall Review -->
                <section class="max-w-2xl">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="size-2 bg-indigo-500 rounded-full"></div>
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-500">Overall Review</h3>
                    </div>
                    <p class="text-xl leading-relaxed text-slate-700 font-medium first-letter:text-5xl first-letter:font-black first-letter:float-left first-letter:mr-3 first-letter:mt-1 first-letter:text-indigo-600">
                        <?php echo nl2br(htmlspecialchars($report['overall_review'])); ?>
                    </p>
                </section>

                <div class="grid md:grid-cols-2 gap-12">
                    <!-- Section 2: Stress Level -->
                    <section class="p-8 bg-slate-50 rounded-[2rem] border border-slate-100">
                        <div class="flex items-center gap-3 mb-8">
                            <i data-lucide="activity" class="size-5 text-indigo-500"></i>
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Stress Assessment</h3>
                        </div>
                        
                        <div class="flex items-end gap-2 mb-4">
                            <span class="text-4xl font-black text-slate-900"><?php echo htmlspecialchars($report['stress_level']); ?></span>
                            <span class="text-sm font-bold text-slate-400 mb-1 uppercase tracking-widest">Zone</span>
                        </div>
                        
                        <!-- Mini Scale -->
                        <div class="flex gap-1 h-2 mt-6">
                            <div class="flex-1 rounded-full <?php echo strtolower($report['stress_level']) == 'low' ? 'bg-green-500' : 'bg-slate-200'; ?>"></div>
                            <div class="flex-1 rounded-full <?php echo strtolower($report['stress_level']) == 'moderate' ? 'bg-amber-400' : 'bg-slate-200'; ?>"></div>
                            <div class="flex-1 rounded-full <?php echo strtolower($report['stress_level']) == 'high' ? 'bg-red-500' : 'bg-slate-200'; ?>"></div>
                        </div>
                    </section>

                    <!-- Section 3: AI Confidence -->
                    <section class="p-8 border-2 border-dashed border-slate-100 rounded-[2rem] flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <i data-lucide="shield" class="size-5 text-indigo-400"></i>
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">System Integrity</h3>
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed italic">
                            Report synthesized from daily logs, chat patterns, and mood variance checks. This document is intended for supportive guidance only.
                        </p>
                    </section>
                </div>

                <hr class="border-slate-100">

                <!-- Section 4: Suggestions -->
                <section>
                    <div class="flex items-center gap-3 mb-8">
                        <i data-lucide="lightbulb" class="size-6 text-amber-500"></i>
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Parenting Suggestions</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-6">
                        <?php foreach ($suggestions as $index => $sug): ?>
                        <div class="p-6 bg-white border border-slate-100 rounded-3xl hover:border-indigo-100 hover:shadow-lg transition-all">
                            <div class="size-8 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-black text-sm mb-4">
                                0<?php echo $index + 1; ?>
                            </div>
                            <p class="text-sm font-medium leading-relaxed text-slate-600">
                                <?php echo htmlspecialchars($sug); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($suggestions)): ?>
                            <p class="text-slate-400 italic">No specific suggestions available for this period.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- Signatures / Footer -->
            <div class="p-12 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-4">
                    <div class="size-12 bg-white rounded-2xl flex items-center justify-center border border-indigo-100">
                        <i data-lucide="sparkles" class="size-6 text-indigo-500"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Signed & Generated By</div>
                        <div class="text-sm font-bold text-slate-700">MindPal AI System</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-300">Document ID</div>
                    <div class="text-[10px] font-mono text-slate-400"><?php echo strtoupper(md5($reportId . $report['report_date'])); ?></div>
                </div>
            </div>
        </div>

        <p class="mt-12 text-center text-[10px] font-black uppercase tracking-[0.4em] text-slate-300">
            CONFIDENTIAL • MINCARE UNIVERSE PARENT PORTAL
        </p>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
