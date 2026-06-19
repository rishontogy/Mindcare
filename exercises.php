<?php
include_once 'includes/header.php';
redirect_if_not_logged_in();

$user_id = get_current_user_id();
$view = $_GET['view'] ?? 'list'; // 'list', 'player'
$exercise_id = $_GET['id'] ?? null;

// Exercise Definitions
$exercises = [
    1 => [
        'name' => 'Gratitude Meditation',
        'type' => 'meditation',
        'icon' => 'heart',
        'color' => 'text-pink-600',
        'bgColor' => 'bg-pink-50',
        'borderColor' => 'border-pink-200',
        'duration' => 5,
        'steps' => [
            "Find a comfortable seated position with your back straight",
            "Close your eyes gently or soften your gaze",
            "Take 3 deep breaths - inhale peace, exhale tension",
            "Think of 3 things you're grateful for today",
            "Feel the warmth of gratitude in your heart",
            "Breathe in this feeling for the remaining time",
            "When ready, slowly open your eyes with a smile"
        ],
        'guidance' => "This meditation helps shift your focus from stress to appreciation. Even on difficult days, there's always something to be grateful for.",
        'benefits' => ["Reduces stress hormones", "Improves sleep quality", "Increases positive emotions"]
    ],
    2 => [
        'name' => 'Energy Boost Breathing',
        'type' => 'breathing',
        'icon' => 'wind',
        'color' => 'text-blue-600',
        'bgColor' => 'bg-blue-50',
        'borderColor' => 'border-blue-200',
        'duration' => 3,
        'steps' => [
            "Sit or stand with your spine straight",
            "Place one hand on your belly, one on your chest",
            "Inhale deeply through your nose for 4 counts",
            "Feel your belly expand like a balloon",
            "Hold for 4 counts at the top",
            "Exhale slowly through your mouth for 6 counts",
            "Repeat this cycle, focusing on the belly"
        ],
        'guidance' => "This technique activates your parasympathetic nervous system, helping you feel more energized and calm.",
        'benefits' => ["Boosts energy levels", "Reduces anxiety", "Improves focus"]
    ],
    3 => [
        'name' => 'Mindful Breathing',
        'type' => 'breathing',
        'icon' => 'droplet',
        'color' => 'text-cyan-600',
        'bgColor' => 'bg-cyan-50',
        'borderColor' => 'border-cyan-200',
        'duration' => 5,
        'steps' => [
            "Find a quiet place to sit comfortably",
            "Notice your natural breathing pattern",
            "Don't try to change it - just observe",
            "When your mind wanders, bring it back gently",
            "Count 10 breaths, then start over",
            "End with a moment of appreciation"
        ],
        'guidance' => "Mindfulness is about being present with whatever arises. Your breath is your perfect anchor.",
        'benefits' => ["Improves emotional regulation", "Reduces mind-wandering", "Enhances awareness"]
    ],
    4 => [
        'name' => 'Body Scan Meditation',
        'type' => 'meditation',
        'icon' => 'sparkles',
        'color' => 'text-purple-600',
        'bgColor' => 'bg-purple-50',
        'borderColor' => 'border-purple-200',
        'duration' => 10,
        'steps' => [
            "Lie down or sit comfortably",
            "Bring awareness to your toes - tense and release",
            "Slowly move up through your feet, ankles, calves",
            "Continue through knees, thighs, hips",
            "Move through your back, chest, shoulders",
            "End with your neck, face, and head",
            "Notice tension and breathe into it"
        ],
        'guidance' => "Body scan helps you reconnect with your physical self and release stored tension.",
        'benefits' => ["Releases physical tension", "Improves body awareness", "Promotes relaxation"]
    ]
];

// Handle Exercise Completion (Submission of feedback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_exercise'])) {
    $ex_id = (int)$_POST['exercise_id'];
    $rating = (int)($_POST['rating'] ?? 0);
    $feedback = clean_input($_POST['feedback'] ?? '');
    $story = clean_input($_POST['story'] ?? '');

    if (isset($exercises[$ex_id])) {
        $ex_name = $exercises[$ex_id]['name'];
        $ex_type = $exercises[$ex_id]['type'];

        try {
            // Ensure table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS exercise_reviews (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                exercise_id INT NOT NULL,
                exercise_name VARCHAR(255),
                type VARCHAR(100),
                rating INT,
                feedback TEXT,
                story TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX(user_id)
            )");

            $stmt = $pdo->prepare("INSERT INTO exercise_reviews 
                (user_id, exercise_id, exercise_name, type, rating, feedback, story) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([$user_id, $ex_id, $ex_name, $ex_type, (int)$rating, $feedback, $story]);

            header("Location: exercises.php?view=list&completed=1");
            exit();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fetch Reviews for the History tab
$reviews = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM exercise_reviews WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll();
} catch (Exception $e) {
}

$stories = array_filter($reviews, function ($r) {
    return !empty($r['story']);
});
?>

<div class="min-h-screen">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4">
        <a href="<?php echo ($view === 'player') ? 'exercises.php' : 'dashboard.php'; ?>" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to <?php echo ($view === 'player') ? 'Exercises' : 'Dashboard'; ?>
        </a>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <?php if ($view === 'list'): ?>
            <!-- Tabs Navigation -->
            <div class="flex p-1 bg-white/50 border border-gray-100 rounded-2xl mb-8 max-w-md mx-auto">
                <button onclick="switchTab('all')" id="tab-all" class="flex-1 py-2 text-sm font-semibold rounded-xl bg-white shadow-sm text-purple-600 transition-all">All</button>
                <button onclick="switchTab('history')" id="tab-history" class="flex-1 py-2 text-sm font-semibold rounded-xl hover:bg-white/50 transition-all text-gray-500">History</button>
                <button onclick="switchTab('stories')" id="tab-stories" class="flex-1 py-2 text-sm font-semibold rounded-xl hover:bg-white/50 transition-all text-gray-500">Stories</button>
            </div>

            <!-- All Exercises Tab -->
            <div id="view-all" class="space-y-4">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Recommended Exercises</h2>
                <div class="grid gap-4">
                    <?php foreach ($exercises as $id => $ex): ?>
                        <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm hover:shadow-md transition-shadow flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="size-14 <?php echo $ex['bgColor']; ?> rounded-2xl flex items-center justify-center">
                                    <i data-lucide="<?php echo $ex['icon']; ?>" class="size-7 <?php echo $ex['color']; ?>"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg"><?php echo $ex['name']; ?></h3>
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <span class="capitalize"><?php echo $ex['type']; ?></span>
                                        <span>•</span>
                                        <span><?php echo $ex['duration']; ?> min</span>
                                    </div>
                                </div>
                            </div>
                            <a href="exercises.php?view=player&id=<?php echo $id; ?>" class="px-6 py-3 bg-purple-50 text-purple-600 font-bold rounded-xl hover:bg-purple-600 hover:text-white transition-all">
                                Start
                            </a>
                        </div>
                    <?php
                    endforeach; ?>
                </div>
            </div>

            <!-- History Tab -->
            <div id="view-history" class="hidden space-y-4">
                <?php if (empty($reviews)): ?>
                    <div class="p-12 text-center bg-white border border-gray-100 rounded-3xl">
                        <p class="text-gray-500">No exercises completed yet.</p>
                    </div>
                <?php
                else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="font-bold"><?php echo htmlspecialchars($rev['exercise_name']); ?></h3>
                                    <p class="text-xs text-gray-400"><?php echo date('F j, Y, g:i a', strtotime($rev['created_at'])); ?></p>
                                </div>
                                <div class="flex gap-1 text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i data-lucide="star" class="size-4 <?php echo ($i <= $rev['rating']) ? 'fill-current' : ''; ?>"></i>
                                    <?php
                                    endfor; ?>
                                </div>
                            </div>
                            <?php if ($rev['feedback']): ?>
                                <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-xl italic">"<?php echo htmlspecialchars($rev['feedback']); ?>"</p>
                            <?php
                            endif; ?>
                        </div>
                    <?php
                    endforeach; ?>
                <?php
                endif; ?>
            </div>

            <!-- Stories Tab -->
            <div id="view-stories" class="hidden space-y-4">
                <?php if (empty($stories)): ?>
                    <div class="p-12 text-center bg-white border border-gray-100 rounded-3xl">
                        <p class="text-gray-500">No success stories shared yet.</p>
                    </div>
                <?php
                else: ?>
                    <?php foreach ($stories as $st): ?>
                        <div class="p-6 bg-gradient-to-br from-purple-50 to-blue-50 border-2 border-purple-200 rounded-3xl">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="size-10 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                    <i data-lucide="brain" class="size-5 text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold"><?php echo htmlspecialchars($st['exerciseName']); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($st['timestamp'])); ?></p>
                                </div>
                            </div>
                            <p class="text-gray-700 leading-relaxed italic">"<?php echo htmlspecialchars($st['story']); ?>"</p>
                        </div>
                    <?php
                    endforeach; ?>
                <?php
                endif; ?>
            </div>

        <?php
        elseif ($view === 'player' && isset($exercises[$exercise_id])): ?>
            <!-- Exercise Player View -->
            <?php $ex = $exercises[$exercise_id]; ?>
            <div class="space-y-6 animate-in fade-in duration-500">
                <div class="p-8 <?php echo $ex['bgColor']; ?> <?php echo $ex['borderColor']; ?> border-2 rounded-3xl shadow-lg relative overflow-hidden">
                    <div class="flex items-start justify-between mb-8 relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="size-16 <?php echo $ex['bgColor']; ?> rounded-full border border-current/20 flex items-center justify-center">
                                <i data-lucide="<?php echo $ex['icon']; ?>" class="size-8 <?php echo $ex['color']; ?>"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-gray-800"><?php echo $ex['name']; ?></h2>
                                <span class="bg-white/50 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-gray-500"><?php echo $ex['type']; ?></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div id="timer" class="text-4xl font-mono font-black text-gray-800">
                                <?php echo str_pad($ex['duration'], 2, '0', STR_PAD_LEFT); ?>:00
                            </div>
                            <p class="text-xs font-bold text-gray-400 mt-1 uppercase"><?php echo $ex['duration']; ?> min session</p>
                        </div>
                    </div>

                    <div class="bg-white/60 backdrop-blur-sm p-6 rounded-2xl mb-8 relative z-10">
                        <h4 class="font-bold mb-3 flex items-center gap-2 text-gray-800">
                            <i data-lucide="brain" class="size-5 text-purple-600"></i> AI Guidance
                        </h4>
                        <p class="text-gray-700 leading-relaxed"><?php echo $ex['guidance']; ?></p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 relative z-10">
                        <button id="start-btn" onclick="toggleTimer()" class="flex items-center gap-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black px-10 py-5 rounded-2xl shadow-2xl hover:scale-105 hover:shadow-purple-200 transition-all active:scale-95 group">
                            <i data-lucide="play" id="play-icon" class="size-7 group-hover:fill-current transition-all"></i>
                            <span id="start-text" class="text-lg">Start Session</span>
                        </button>
                        <button id="voice-toggle" onclick="toggleVoice()" class="p-5 bg-white text-gray-700 font-bold rounded-2xl border border-gray-200 hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2" title="Toggle Voice Assistance">
                            <i data-lucide="volume-2" id="voice-icon" class="size-6 text-purple-600"></i>
                            <span id="voice-text" class="hidden sm:inline">Voice On</span>
                        </button>
                        <button onclick="showInstructionsDialog()" class="px-6 py-5 bg-white text-gray-500 font-bold rounded-2xl border border-gray-200 hover:bg-gray-50 transition-all">
                            View Guide
                        </button>
                    </div>

                    <div id="completion-controls" class="hidden mt-8 pt-8 border-t border-gray-200/50 animate-in slide-in-from-bottom-4">
                        <button onclick="openReviewModal()" class="w-full bg-green-600 text-white font-black py-4 rounded-2xl shadow-xl hover:bg-green-700 transition-all">
                            <i data-lucide="check" class="inline-block size-5 mr-2"></i> Close & Save Session
                        </button>
                    </div>
                </div>

                <!-- Step-by-Step Guide -->
                <div class="p-8 bg-white border border-gray-100 rounded-3xl shadow-sm">
                    <h4 class="font-bold text-lg mb-6 text-gray-800">Step-by-Step Instructions</h4>
                    <div class="space-y-4">
                        <?php foreach ($ex['steps'] as $idx => $step): ?>
                            <div id="step-<?php echo $idx; ?>" class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 border border-transparent transition-all">
                                <div id="step-num-<?php echo $idx; ?>" class="size-8 rounded-full flex items-center justify-center font-black text-xs bg-gray-200 text-gray-500">
                                    <?php echo $idx + 1; ?>
                                </div>
                                <p class="text-gray-700 flex-1"><?php echo $step; ?></p>
                            </div>
                        <?php
                        endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Review Modal -->
            <div id="review-modal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] hidden items-center justify-center p-4">
                <div class="bg-white rounded-[40px] max-w-lg w-full p-10 shadow-2xl overflow-hidden relative">
                    <h2 class="text-3xl font-black mb-2">Great Job! 🎉</h2>
                    <p class="text-gray-500 mb-8">How was the session? Your feedback helps us improve your wellness path.</p>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="exercise_id" value="<?php echo $exercise_id; ?>">
                        <input type="hidden" name="complete_exercise" value="1">

                        <div>
                            <label class="block text-sm font-black text-gray-800 uppercase tracking-widest mb-4">Your Rating</label>
                            <div class="flex gap-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="r<?php echo $i; ?>" class="hidden peer">
                                    <label for="r<?php echo $i; ?>" class="size-12 rounded-2xl bg-gray-50 border-2 border-gray-100 flex items-center justify-center cursor-pointer peer-checked:bg-yellow-100 peer-checked:border-yellow-400 peer-checked:text-yellow-600 hover:bg-gray-100 transition-all">
                                        <?php echo $i; ?>
                                    </label>
                                <?php
                                endfor; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-800 uppercase tracking-widest mb-2">Thoughts & Feedback</label>
                            <textarea name="feedback" rows="3" placeholder="I felt more relaxed after..."
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-purple-100 focus:bg-white outline-none transition-all"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-800 uppercase tracking-widest mb-2">Share a Story (Optional)</label>
                            <textarea name="story" rows="3" placeholder="This session helped me because..."
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white outline-none transition-all"></textarea>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" onclick="closeReviewModal()" class="flex-1 py-4 font-bold text-gray-500 rounded-2xl hover:bg-gray-100 transition-all">Cancel</button>
                            <button type="submit" class="flex-[2] py-4 bg-purple-600 text-white font-black rounded-2xl shadow-lg hover:bg-purple-700 transition-all">Save & Finish</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                let timerSeconds = <?php echo $ex['duration'] * 60; ?>;
                let isTimerRunning = false;
                let timerInterval;
                const totalSteps = <?php echo count($ex['steps']); ?>;
                let currentStep = -1;
                let isVoiceEnabled = true;
                const stepsText = <?php echo json_encode($ex['steps']); ?>;

                function speak(text) {
                    if (!isVoiceEnabled) return;
                    window.speechSynthesis.cancel();
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.rate = 0.9; // Slightly slower for relaxation
                    utterance.pitch = 1.1; // Softer tone
                    window.speechSynthesis.speak(utterance);
                }

                function toggleVoice() {
                    isVoiceEnabled = !isVoiceEnabled;
                    const icon = document.getElementById('voice-icon');
                    const text = document.getElementById('voice-text');
                    if (isVoiceEnabled) {
                        icon.setAttribute('data-lucide', 'volume-2');
                        text.innerText = 'Voice On';
                        speak("Voice assistance enabled");
                    } else {
                        icon.setAttribute('data-lucide', 'volume-x');
                        text.innerText = 'Voice Off';
                        window.speechSynthesis.cancel();
                    }
                    lucide.createIcons();
                }

                function updateTimerDisplay() {
                    const mins = Math.floor(timerSeconds / 60);
                    const secs = timerSeconds % 60;
                    document.getElementById('timer').innerText =
                        `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

                    if (timerSeconds === 0) {
                        finishSession();
                    }

                    // Simple logic to advance steps based on time
                    const secondsPerStep = (<?php echo $ex['duration']; ?> * 60) / totalSteps;
                    const elapsed = (<?php echo $ex['duration']; ?> * 60) - timerSeconds;
                    const stepIndex = Math.floor(elapsed / secondsPerStep);
                    if (stepIndex < totalSteps && stepIndex !== currentStep) {
                        highlightStep(stepIndex);
                    }
                }

                function highlightStep(idx) {
                    // Reset all
                    for (let i = 0; i < totalSteps; i++) {
                        const s = document.getElementById('step-' + i);
                        const n = document.getElementById('step-num-' + i);
                        s.classList.remove('bg-purple-100', 'border-purple-300', 'scale-105', 'shadow-md');
                        n.classList.remove('bg-purple-600', 'text-white');
                        if (i < idx) {
                            n.innerHTML = '<i data-lucide="check" class="size-4"></i>';
                            n.classList.add('bg-green-500', 'text-white');
                            lucide.createIcons();
                        }
                    }
                    // Highlight current
                    const cs = document.getElementById('step-' + idx);
                    const cn = document.getElementById('step-num-' + idx);
                    cs.classList.add('bg-purple-100', 'border-purple-300', 'scale-105', 'shadow-md');
                    cn.classList.add('bg-purple-600', 'text-white');
                    
                    // Voice Assistance: Speak the step
                    if (idx !== currentStep) {
                        speak("Step " + (idx + 1) + ". " + stepsText[idx]);
                    }
                    
                    currentStep = idx;
                }

                function toggleTimer() {
                    if (isTimerRunning) {
                        clearInterval(timerInterval);
                        document.getElementById('start-text').innerText = 'Resume session';
                        document.getElementById('play-icon').setAttribute('data-lucide', 'play');
                    } else {
                        timerInterval = setInterval(() => {
                            if (timerSeconds > 0) {
                                timerSeconds--;
                                updateTimerDisplay();
                            } else {
                                clearInterval(timerInterval);
                            }
                        }, 1000);
                        document.getElementById('start-text').innerText = 'Running...';
                        document.getElementById('play-icon').setAttribute('data-lucide', 'pause');
                        if (timerSeconds === <?php echo $ex['duration'] * 60; ?>) highlightStep(0);
                    }
                    isTimerRunning = !isTimerRunning;
                    lucide.createIcons();
                }

                function finishSession() {
                    clearInterval(timerInterval);
                    document.getElementById('timer').classList.add('text-green-600');
                    document.getElementById('start-btn').classList.add('hidden');
                    document.getElementById('completion-controls').classList.remove('hidden');
                    // Mark last step done
                    highlightStep(totalSteps - 1);
                }

                function openReviewModal() {
                    document.getElementById('review-modal').classList.remove('hidden');
                    document.getElementById('review-modal').classList.add('flex');
                }

                function closeReviewModal() {
                    document.getElementById('review-modal').classList.add('hidden');
                    document.getElementById('review-modal').classList.remove('flex');
                }
            </script>
        <?php
        endif; ?>
    </div>
</div>

<script>
    function switchTab(tab) {
        document.getElementById('view-all').classList.add('hidden');
        document.getElementById('view-history').classList.add('hidden');
        document.getElementById('view-stories').classList.add('hidden');

        document.getElementById('tab-all').classList.remove('bg-white', 'shadow-sm', 'text-purple-600');
        document.getElementById('tab-all').classList.add('text-gray-500', 'hover:bg-white/50');
        document.getElementById('tab-history').classList.remove('bg-white', 'shadow-sm', 'text-purple-600');
        document.getElementById('tab-history').classList.add('text-gray-500', 'hover:bg-white/50');
        document.getElementById('tab-stories').classList.remove('bg-white', 'shadow-sm', 'text-purple-600');
        document.getElementById('tab-stories').classList.add('text-gray-500', 'hover:bg-white/50');

        document.getElementById('view-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).classList.add('bg-white', 'shadow-sm', 'text-purple-600');
        document.getElementById('tab-' + tab).classList.remove('text-gray-500', 'hover:bg-white/50');
    }
</script>

<?php include_once 'includes/footer.php'; ?>