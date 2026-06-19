<?php
require_once __DIR__ . '/php-backend/init.php';
include_once 'includes/header.php';

Auth::checkParentLogin();

$user = Session::getUser();
$userId = $user['user_id'];

$activeStudentId = Session::get('active_student_id');
$studentData = [
    'name' => 'Your Child',
    'moodScore' => 7,
    'currentMood' => 'Calm',
    'lastActive' => '2 hours ago',
    'exercisesCompleted' => 3
];

if ($activeStudentId) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :id");
    $stmt->execute([':id' => $activeStudentId]);
    $student = $stmt->fetch();
    if ($student) {
        $studentData['name'] = $student['name'];

        // Fetch real mood data if available
        $moodStmt = $pdo->prepare("SELECT mood_score, created_at FROM mood_assessments WHERE user_id = :id ORDER BY created_at DESC LIMIT 1");
        $moodStmt->execute([':id' => $activeStudentId]);
        $mood = $moodStmt->fetch();
        if ($mood) {
            $studentData['moodScore'] = $mood['mood_score'];
            $studentData['lastActive'] = date('H:i', strtotime($mood['created_at']));
        }

        // Fetch exercise count
        $exStmt = $pdo->prepare("SELECT COUNT(*) FROM user_exercises WHERE user_id = :id AND DATE(completed_at) = CURDATE()");
        $exStmt->execute([':id' => $activeStudentId]);
        $studentData['exercisesCompleted'] = $exStmt->fetchColumn();
    }

    // Fetch Parent-AI chat history
    $chatHistoryStmt = $pdo->prepare("SELECT sender, message, created_at FROM parent_chat_messages WHERE parent_id = ? AND student_id = ? ORDER BY created_at ASC LIMIT 50");
    $chatHistoryStmt->execute([$userId, $activeStudentId]);
    $chatHistory = $chatHistoryStmt->fetchAll();
}
?>
<div class="flex flex-col h-[calc(100vh-64px)]">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4 shrink-0">
        <a href="parent-dashboard.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="flex-1 overflow-hidden pb-8">
        <div class="container mx-auto px-4 h-full">
            <div class="bg-white/80 backdrop-blur-xl border border-gray-200 rounded-[2rem] flex flex-col h-full overflow-hidden shadow-2xl shadow-indigo-100/50">
                <!-- Chat Header -->
                <div class="p-6 border-b border-gray-100 flex items-center gap-4 shrink-0">
                    <div class="size-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                        <i data-lucide="bot" class="size-6"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent flex items-center gap-2">
                            AI Parent Assistant
                            <span class="inline-block animate-pulse"><i data-lucide="sparkles" class="size-4 text-purple-400"></i></span>
                        </h1>
                        <p class="text-xs text-gray-500 font-medium">Insights about <span class="text-indigo-600 font-bold"><?php echo htmlspecialchars($studentData['name']); ?></span>'s wellness journey</p>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6" id="messagesArea">
                    <!-- Initial Bot Message -->
                    <div class="flex flex-col items-start gap-2 max-w-[85%] animate-in slide-in-from-left duration-500">
                        <div class="p-6 bg-white border border-purple-50 rounded-[2rem] rounded-tl-lg text-sm leading-relaxed text-gray-700 shadow-sm">
                            <p class="mb-4">Hello! I'm the MindPal AI Assistant. Here's what we've observed in <strong><?php echo htmlspecialchars($studentData['name']); ?></strong>'s wellness journey today:</p>
                            
                            <div class="space-y-4">
                                <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-2 flex items-center gap-2">
                                        <i data-lucide="bar-chart-3" class="size-3"></i> Mood Status
                                    </h4>
                                    <div class="flex items-end gap-3">
                                        <span class="text-3xl font-black text-indigo-600"><?php echo $studentData['moodScore']; ?><span class="text-sm text-indigo-300">/10</span></span>
                                        <span class="text-xs font-bold text-indigo-400 mb-1 uppercase tracking-widest"><?php echo $studentData['currentMood']; ?></span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-wider">Last active: <?php echo $studentData['lastActive']; ?></p>
                                </div>

                                <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100/50">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-emerald-500 mb-2 flex items-center gap-2">
                                        <i data-lucide="check-circle-2" class="size-3"></i> Activities
                                    </h4>
                                    <p class="text-xs font-bold text-emerald-700"><?php echo $studentData['exercisesCompleted']; ?> Exercises completed today</p>
                                    <ul class="mt-2 space-y-1 text-[11px] text-emerald-600/80 font-medium">
                                        <li class="flex items-center gap-2"><i data-lucide="check" class="size-3"></i> Deep breathing (10 min)</li>
                                        <li class="flex items-center gap-2"><i data-lucide="check" class="size-3"></i> Mindful walking (15 min)</li>
                                    </ul>
                                </div>
                            </div>

                            <p class="mt-4">Based on these patterns, <?php echo htmlspecialchars($studentData['name']); ?> is showing <?php echo $studentData['moodScore'] >= 7 ? 'excellent' : 'steady'; ?> engagement. Feel free to ask me anything about their progress!</p>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 ml-4 uppercase tracking-widest"><?php echo date('H:i'); ?></span>
                    </div>

                    <div id="typingIndicator" class="hidden animate-in fade-in duration-300">
                        <div class="flex gap-1 p-4 bg-gray-50 rounded-2xl w-16 items-center justify-center">
                            <div class="size-1.5 bg-gray-300 rounded-full animate-bounce"></div>
                            <div class="size-1.5 bg-gray-300 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                            <div class="size-1.5 bg-gray-300 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                        </div>
                    </div>

                    <?php if (isset($chatHistory)): ?>
                        <?php foreach ($chatHistory as $chat): ?>
                            <div class="flex flex-col <?php echo $chat['sender'] === 'ai' ? 'items-start' : 'items-end'; ?> gap-2 max-w-[85%] <?php echo $chat['sender'] === 'ai' ? 'mr-auto' : 'ml-auto'; ?>">
                                <div class="p-4 rounded-3xl text-sm leading-relaxed shadow-sm <?php echo $chat['sender'] === 'ai' ? 'bg-white border border-purple-50 text-gray-700 rounded-tl-lg' : 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-tr-lg'; ?>">
                                    <?php echo nl2br(htmlspecialchars($chat['message'])); ?>
                                </div>
                                <span class="text-[10px] font-bold text-gray-400 px-2 uppercase tracking-widest"><?php echo date('H:i', strtotime($chat['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-50 flex gap-2 overflow-x-auto no-scrollbar shrink-0 bg-gray-50/50">
                    <button onclick="quickAsk('How was <?php echo addslashes($studentData['name']); ?>\'s mood this week?')" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:border-purple-300 hover:text-purple-600 transition-all whitespace-nowrap shadow-sm">Weekly Trend</button>
                    <button onclick="quickAsk('What exercises has <?php echo addslashes($studentData['name']); ?> completed?')" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:border-purple-300 hover:text-purple-600 transition-all whitespace-nowrap shadow-sm">Activities</button>
                    <button onclick="quickAsk('Give me tips to support <?php echo addslashes($studentData['name']); ?> today')" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:border-purple-300 hover:text-purple-600 transition-all whitespace-nowrap shadow-sm">Support Tips</button>
                </div>

                <!-- Chat Input -->
                <div class="p-6 border-t border-gray-100 shrink-0">
                    <div class="flex gap-3">
                        <input type="text" id="chatInput" placeholder="Ask about mood patterns, exercises, or trends..." class="flex-1 bg-gray-50 border border-transparent focus:bg-white focus:border-emerald-500 rounded-2xl px-6 py-4 text-sm font-medium focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all">
                        <button id="sendBtn" class="size-14 bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-100 hover:scale-105 active:scale-95 transition-all">
                            <i data-lucide="send" class="size-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const messagesArea = document.getElementById('messagesArea');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const typingIndicator = document.getElementById('typingIndicator');

    function scrollToBottom() {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    function addMessage(content, role) {
        const wrapper = document.createElement('div');
        wrapper.className = `flex flex-col ${role === 'ai' ? 'items-start' : 'items-end'} gap-2 max-w-[85%] ${role === 'ai' ? 'mr-auto' : 'ml-auto'} animate-in slide-in-from-bottom-2 duration-300`;
        
        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const bubbleClass = role === 'ai' ? 'bg-white border border-purple-50 text-gray-700 rounded-tl-lg' : 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-tr-lg';

        wrapper.innerHTML = `
            <div class="p-4 rounded-3xl text-sm leading-relaxed shadow-sm ${bubbleClass}">
                ${content.replace(/\n/g, '<br>')}
            </div>
            <span class="text-[10px] font-bold text-gray-400 px-2 uppercase tracking-widest">${timestamp}</span>
        `;

        messagesArea.insertBefore(wrapper, typingIndicator);
        scrollToBottom();
    }

    function quickAsk(msg) {
        chatInput.value = msg;
        handleSend();
    }

    async function handleSend() {
        const text = chatInput.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        chatInput.value = '';
        chatInput.disabled = true;
        sendBtn.disabled = true;

        typingIndicator.classList.remove('hidden');
        scrollToBottom();

        try {
            const response = await fetch("parent_ai_api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ message: text })
            });
            const data = await response.json();
            
            typingIndicator.classList.add('hidden');
            if (data.status === 'success') {
                addMessage(data.reply, 'ai');
            } else {
                addMessage("I'm sorry, I'm having trouble connecting right now.", 'ai');
            }
        } catch (err) {
            typingIndicator.classList.add('hidden');
            addMessage("Network error. Please check your connection.", 'ai');
        }

        chatInput.disabled = false;
        sendBtn.disabled = false;
        chatInput.focus();
    }

    sendBtn.addEventListener('click', handleSend);
    chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') handleSend(); });

    window.addEventListener('load', scrollToBottom);
</script>

<?php include_once 'includes/footer.php'; ?>