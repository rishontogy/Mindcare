<?php
include_once 'includes/header.php';
redirect_if_not_logged_in();

$user_id = get_current_user_id();

// Handle Mood Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_mood'])) {
    $mood = (int)$_POST['mood'];
    $note = clean_input($_POST['note'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO mood_history (user_id, mood, note) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $mood, $note]);

        header("Location: wellness-coach.php?mood_saved=1");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle Goal Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_goal'])) {
    $goal_id = (int)$_POST['goal_id'];
    $completed = (int)$_POST['completed'];

    try {
        $stmt = $pdo->prepare("UPDATE wellness_goals SET completed = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$completed, $goal_id, $user_id]);

        echo json_encode(['success' => true]);
        exit();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
}

// Initialize Daily Goals if none exist for today
$today_date = date('Y-m-d');
try {
    $stmt = $pdo->prepare("SELECT id FROM wellness_goals WHERE user_id = ? AND DATE(created_at) = ?");
    $stmt->execute([$user_id, $today_date]);
    $goal_exists = $stmt->fetch();

    if (!$goal_exists) {
        $default_goals = [
            ['Practice Deep Breathing', 'Take 5 minutes to focus on your breath', 'mindfulness'],
            ['Take a 10-minute walk', 'Get some fresh air and gentle movement', 'physical'],
            ['Connect with a friend', 'Reach out to someone you care about', 'social'],
            ['Aim for 8 hours of sleep', 'Create a calming bedtime routine', 'sleep']
        ];

        foreach ($default_goals as $g) {
            $istmt = $pdo->prepare("INSERT INTO wellness_goals (user_id, title, description, category) VALUES (?, ?, ?, ?)");
            $istmt->execute([$user_id, $g[0], $g[1], $g[2]]);
        }
    }
} catch (Exception $e) {
    // Silent fail or log
}


// Fetch Mood History (Last 7)
$mood_history = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM mood_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 7");
    $stmt->execute([$user_id]);
    $mood_history = $stmt->fetchAll();
} catch (Exception $e) {
}

// Fetch Today's Goals
$wellness_goals = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM wellness_goals WHERE user_id = ? AND DATE(created_at) = ?");
    $stmt->execute([$user_id, $today_date]);
    $wellness_goals = $stmt->fetchAll();
} catch (Exception $e) {
}

$completed_goals = count(array_filter($wellness_goals, function ($g) {
    return $g['completed'];
}));
$total_goals = count($wellness_goals);
$completion_percentage = ($total_goals > 0) ? ($completed_goals / $total_goals) * 100 : 0;

function getMoodEmoji($mood)
{
    switch ($mood) {
        case 1: return '😢';
        case 2: return '😔';
        case 3: return '😐';
        case 4: return '🙂';
        case 5: return '😊';
        default: return '😐';
    }
}

function getMoodColor($mood)
{
    switch ($mood) {
        case 1: return 'text-red-500';
        case 2: return 'text-orange-500';
        case 3: return 'text-yellow-500';
        case 4: return 'text-blue-500';
        case 5: return 'text-green-500';
        default: return 'text-gray-500';
    }
}

function getCategoryIcon($category)
{
    switch ($category) {
        case 'mindfulness': return '🧘';
        case 'physical': return '🏃';
        case 'social': return '👥';
        case 'sleep': return '😴';
        default: return '🎯';
    }
}

$personalized_message = "Welcome! Let's start tracking your wellness journey together.";
if (!empty($mood_history)) {
    $recentMood = $mood_history[0]['mood'];
    if ($recentMood >= 4) {
        $personalized_message = "I'm so glad you're feeling good! Let's build on this positive momentum.";
    } elseif ($recentMood >= 3) {
        $personalized_message = "You're doing great taking care of yourself. Small steps lead to big changes.";
    } else {
        $personalized_message = "Remember that it's okay to have tough days. I'm here to support you through them.";
    }
}
?>
<div class="min-h-screen">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4">
        <a href="dashboard.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Welcome Message -->
                <div class="p-8 bg-white border border-indigo-100 rounded-[32px] shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-start gap-6 relative z-10">
                        <div class="size-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center shadow-lg shrink-0">
                            <i data-lucide="heart" class="size-8 text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">Hello!</h2>
                            <p class="text-gray-600 leading-relaxed text-lg"><?php echo $personalized_message; ?></p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="chat.php" class="inline-flex items-center px-6 py-3 border-2 border-indigo-500 text-indigo-600 rounded-2xl font-bold hover:bg-indigo-50 transition-all">
                                    <i data-lucide="message-circle" class="size-5 mr-2"></i> Chat with MindPal
                                </a>
                                <a href="exercises.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-500 to-green-500 text-white rounded-2xl font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                                    <i data-lucide="activity" class="size-5 mr-2"></i> Start Exercises
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mood Tracker -->
                <div class="p-8 bg-white/80 backdrop-blur-sm border border-white rounded-[32px] shadow-xl">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="size-10 bg-pink-100 rounded-2xl flex items-center justify-center">
                            <i data-lucide="smile" class="size-6 text-pink-600"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">How are you feeling today?</h3>
                    </div>

                    <form id="mood-form" method="POST" class="space-y-8">
                        <input type="hidden" name="submit_mood" value="1">
                        <div class="flex justify-between items-center gap-2 max-w-md mx-auto">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="mood" value="<?php echo $i; ?>" id="mood-<?php echo $i; ?>" class="hidden peer" required>
                                <label for="mood-<?php echo $i; ?>"
                                    class="size-14 rounded-[20px] bg-white border-2 border-gray-100 flex items-center justify-center text-3xl shadow-sm cursor-pointer hover:border-indigo-300 hover:scale-110 peer-checked:bg-indigo-500 peer-checked:border-indigo-600 peer-checked:scale-125 transition-all">
                                    <?php echo getMoodEmoji($i); ?>
                                </label>
                            <?php
                            endfor; ?>
                        </div>

                        <div id="note-section" class="space-y-3 animate-in fade-in slide-in-from-top-4">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest">Add a daily note</label>
                            <textarea name="note" rows="3" placeholder="What's contributing to how you feel today?"
                                class="w-full p-5 bg-gray-50 border border-gray-100 rounded-[24px] focus:ring-4 focus:ring-indigo-100 focus:bg-white outline-none transition-all placeholder:text-gray-400"></textarea>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-black rounded-2xl shadow-lg hover:shadow-xl transition-all">
                                Save Entry
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Daily Wellness Goals -->
                <div class="p-8 bg-white border border-gray-100 rounded-[32px] shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="size-10 bg-teal-100 rounded-2xl flex items-center justify-center">
                                <i data-lucide="target" class="size-6 text-teal-600"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Today's Progress</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-500"><?php echo $completed_goals; ?>/<?php echo $total_goals; ?> Goals</p>
                            <div class="w-32 h-2 bg-gray-100 rounded-full mt-2 overflow-hidden">
                                <div class="h-full bg-teal-500 transition-all duration-1000" style="width: <?php echo $completion_percentage; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($wellness_goals as $goal): ?>
                            <div onclick="toggleGoal('<?php echo $goal['id']; ?>', <?php echo $goal['completed'] ? '0' : '1'; ?>)"
                                class="flex items-center gap-5 p-6 rounded-[24px] border-2 cursor-pointer transition-all hover:scale-[1.01] <?php
                                                                                                                                            echo $goal['completed'] ? 'bg-green-50/50 border-green-200' : 'bg-gray-50 border-gray-100 hover:border-indigo-200';
                                                                                                                                            ?>">
                                <div class="size-8 rounded-full flex items-center justify-center border-2 transition-all <?php
                                                                                                                            echo $goal['completed'] ? 'bg-green-500 border-green-500' : 'border-indigo-200';
                                                                                                                            ?>">
                                    <?php if ($goal['completed']): ?>
                                        <i data-lucide="check" class="size-4 text-white"></i>
                                    <?php
                                    endif; ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl"><?php echo getCategoryIcon($goal['category']); ?></span>
                                        <h4 class="font-bold text-lg <?php echo $goal['completed'] ? 'text-gray-400 line-through' : 'text-gray-800'; ?>">
                                            <?php echo htmlspecialchars($goal['title']); ?>
                                        </h4>
                                    </div>
                                    <p class="text-sm mt-1 <?php echo $goal['completed'] ? 'text-gray-400' : 'text-gray-500'; ?>">
                                        <?php echo htmlspecialchars($goal['description']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php
                        endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Mood Trend -->
                <div class="p-8 bg-white border border-gray-100 rounded-[32px] shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="size-10 bg-blue-100 rounded-2xl flex items-center justify-center">
                            <i data-lucide="trending-up" class="size-6 text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Recent Moods</h3>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($mood_history)): ?>
                            <p class="text-sm text-gray-400 text-center py-4 italic">No logs yet. Start tracking today!</p>
                        <?php
                        else: ?>
                            <?php foreach ($mood_history as $entry): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl"><?php echo getMoodEmoji($entry['mood']); ?></span>
                                        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                                            <?php echo date('D, M j', strtotime($entry['created_at'])); ?>
                                        </div>
                                    </div>
                                    <span class="font-black <?php echo getMoodColor($entry['mood']); ?>">
                                        <?php echo $entry['mood']; ?>/5
                                    </span>
                                </div>
                            <?php
                            endforeach; ?>
                        <?php
                        endif; ?>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="p-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-[32px] shadow-xl text-white">
                    <div class="size-12 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="sparkles" class="size-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-black mb-3">Today's Wisdom</h3>
                    <p class="opacity-90 leading-relaxed text-sm italic">
                        "Your mental health is a priority. Your happiness is essential. Your self-care is a necessity."
                    </p>
                    <div class="mt-8 pt-6 border-t border-white/10">
                        <p class="text-xs font-bold uppercase tracking-widest opacity-60">Pro Tip</p>
                        <p class="text-sm font-medium mt-1">Try to journal for 5 minutes before bed to clear your mind.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleGoal(id, status) {
        const formData = new FormData();
        formData.append('toggle_goal', '1');
        formData.append('goal_id', id);
        formData.append('completed', status);

        fetch('wellness-coach.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => console.error('Error toggling goal:', err));
    }
</script>

<?php include_once 'includes/footer.php'; ?>