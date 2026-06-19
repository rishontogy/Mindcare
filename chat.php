<?php
include_once 'includes/header.php';
redirect_if_not_logged_in();

$user_id = get_current_user_id();
$history_msgs = [];

$stmt = $pdo->prepare("SELECT sender, message, created_at FROM student_chat_messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 50");
$stmt->execute([$user_id]);
$history_msgs = $stmt->fetchAll();
?>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="h-[calc(100vh-14rem)] flex flex-col shadow-2xl rounded-[40px] overflow-hidden bg-white/80 backdrop-blur-sm border border-white">
            <!-- Chat Area -->
            <div id="chat-container" class="flex-1 overflow-y-auto p-8 space-y-6">
                <!-- Initial Welcome Message -->
                <div class="flex justify-start animate-in slide-in-from-bottom-4 duration-500">
                    <div class="max-w-[85%] rounded-[32px] px-6 py-5 bg-white text-gray-800 border border-indigo-100 shadow-lg mr-12 relative group">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="size-6 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                                <i data-lucide="heart" class="size-3 text-white"></i>
                            </div>
                            <span class="text-sm font-bold text-indigo-600">MindPal</span>
                            <i data-lucide="sparkles" class="size-3 text-purple-400"></i>
                        </div>
                        <p class="leading-relaxed font-medium text-gray-700">Hey there! 👋 I'm your mental wellness buddy. I'm here to listen, chat, and help you feel better. How are you feeling today?</p>
                        <p class="text-[10px] font-bold text-gray-400 mt-3 uppercase tracking-widest"><?php echo date('g:i A'); ?></p>
                    </div>
                </div>

                <?php foreach ($history_msgs as $msg): ?>
                    <div class="flex <?php echo $msg['sender'] === 'user' ? 'justify-end' : 'justify-start'; ?> animate-in slide-in-from-bottom-2">
                        <div class="max-w-[85%] rounded-[32px] px-6 py-5 <?php echo $msg['sender'] === 'user' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-xl ml-12' : 'bg-white text-gray-800 border border-indigo-100 shadow-lg mr-12'; ?> relative">
                            <?php if ($msg['sender'] === 'ai'): ?>
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="size-6 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                                        <i data-lucide="heart" class="size-3 text-white"></i>
                                    </div>
                                    <span class="text-sm font-bold text-indigo-600">MindPal</span>
                                </div>
                            <?php endif; ?>
                            <p class="leading-relaxed font-medium"><?php echo htmlspecialchars($msg['message']); ?></p>
                            <p class="text-[10px] font-bold <?php echo $msg['sender'] === 'user' ? 'text-white/60' : 'text-gray-400'; ?> mt-3 uppercase tracking-widest">
                                <?php echo date('g:i A', strtotime($msg['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Suggestion Chips -->
            <div id="quick-prompts" class="px-8 flex gap-2 overflow-x-auto py-2 no-scrollbar">
                <button onclick="sendQuickMessage('I\'m feeling a bit anxious today')" class="shrink-0 px-4 py-2 bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-bold rounded-full hover:bg-indigo-500 hover:text-white transition-all">😌 I'm anxious</button>
                <button onclick="sendQuickMessage('Can we do some breathing exercises?')" class="shrink-0 px-4 py-2 bg-blue-50 border border-blue-100 text-blue-600 text-xs font-bold rounded-full hover:bg-blue-500 hover:text-white transition-all">🫁 Breathing</button>
                <button onclick="sendQuickMessage('I need help relaxing')" class="shrink-0 px-4 py-2 bg-teal-50 border border-teal-100 text-teal-600 text-xs font-bold rounded-full hover:bg-teal-500 hover:text-white transition-all">🌿 Relax me</button>
                <button onclick="location.href='wellness-coach.php'" class="shrink-0 px-4 py-2 bg-purple-50 border border-purple-100 text-purple-600 text-xs font-bold rounded-full hover:bg-purple-500 hover:text-white transition-all">⭐ Wellness Coach</button>
            </div>

            <!-- Input Area -->
            <div class="p-8 pb-10 bg-gradient-to-r from-indigo-50/50 to-purple-50/50 border-t border-indigo-50">
                <div class="flex gap-4 items-center">
                    <div class="flex-1 relative">
                        <input type="text" id="chat-input" placeholder="Share what's in your heart... 💜"
                            class="w-full pl-6 pr-14 py-5 bg-white border-2 border-indigo-100 rounded-[30px] shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none transition-all placeholder:text-gray-400 font-medium">
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 text-2xl"></div>
                    </div>
                    <button onclick="handleSend()" class="size-16 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-[24px] shadow-xl hover:scale-105 transition-all flex items-center justify-center">
                        <i data-lucide="send" class="size-6"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="user-msg-tmpl">
    <div class="flex justify-end animate-in slide-in-from-bottom-4 duration-500">
        <div class="max-w-[85%] rounded-[32px] px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-xl ml-12 relative overflow-hidden">
            <p class="leading-relaxed relative z-10 font-medium"></p>
            <p class="text-[10px] font-bold text-white/60 mt-3 uppercase tracking-widest relative z-10"></p>
        </div>
    </div>
</template>

<template id="ai-msg-tmpl">
    <div class="flex justify-start animate-in slide-in-from-bottom-4 duration-500">
        <div class="max-w-[85%] rounded-[32px] px-6 py-5 bg-white text-gray-800 border border-indigo-100 shadow-lg mr-12 relative">
            <div class="flex items-center gap-2 mb-3">
                <div class="size-6 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                    <i data-lucide="heart" class="size-3 text-white"></i>
                </div>
                <span class="text-sm font-bold text-indigo-600">MindPal</span>
                <i data-lucide="sparkles" class="size-3 text-purple-400"></i>
            </div>
            <p class="leading-relaxed font-medium"></p>
            <div class="exercise-btn-area hidden mt-4">
                <a href="exercises.php" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-teal-500 to-green-500 text-white text-sm font-bold rounded-xl shadow-md hover:scale-105 transition-all">
                    🌱 Start Exercises
                </a>
            </div>
            <p class="text-[10px] font-bold text-gray-400 mt-3 uppercase tracking-widest"></p>
        </div>
    </div>
</template>

<script>
    const chatContainer = document.getElementById('chat-container');
    const chatInput = document.getElementById('chat-input');
    const userTmpl = document.getElementById('user-msg-tmpl');
    const aiTmpl = document.getElementById('ai-msg-tmpl');

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleSend();
    });

    function sendQuickMessage(msg) {
        chatInput.value = msg;
        handleSend();
    }

    async function handleSend() {
        const text = chatInput.value.trim();
        if (!text) return;

        chatInput.value = '';
        appendMessage('user', text);

        // Temporary typing message


        try {
            const response = await fetch("mindpal_api.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    message: text
                })
            });

            const data = await response.json();

            // Remove typing message
            chatContainer.lastChild.remove();

            appendMessage('assistant', data.reply);

        } catch (error) {
            console.error(error);

            chatContainer.lastChild.remove();
            appendMessage('assistant', "Oops something went wrong 😔");
        }
    }

    function appendMessage(role, content, extras = {}) {
        const tmpl = (role === 'user') ? userTmpl : aiTmpl;
        const clone = tmpl.content.cloneNode(true);

        clone.querySelector('p').innerText = content;
        const timeStr = new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
        clone.querySelectorAll('p')[role === 'user' ? 1 : 1].innerText = timeStr;

        if (role === 'assistant' && extras.showExercises) {
            clone.querySelector('.exercise-btn-area').classList.remove('hidden');
        }

        chatContainer.appendChild(clone);
        lucide.createIcons();
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    window.onload = () => {
        chatContainer.scrollTop = chatContainer.scrollHeight;
        lucide.createIcons();
    };
</script>

<?php include_once 'includes/footer.php'; ?>