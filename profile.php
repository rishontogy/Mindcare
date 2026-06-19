<?php
// C:\Users\RISHON OOMMEN TOGY\Downloads\MindCare\php-app\profile.php
include_once 'includes/header.php';
redirect_if_not_logged_in();

$userName = get_current_user_name();
$userEmail = $_SESSION['user_email'] ?? 'user@example.com';

$faqs = [
    [
        'question' => 'How often should I complete the daily assessment?',
        'answer' => 'We recommend completing the assessment once daily, preferably at the same time each day. This helps track consistent trends in your mood and provides the most accurate recommendations.'
    ],
    [
        'question' => 'Is my data private and secure?',
        'answer' => 'Yes! Your data is encrypted and stored securely. We never share your personal information or assessment results with third parties. Your privacy is our top priority.'
    ],
    [
        'question' => 'What should I do if I\'m in crisis?',
        'answer' => 'If you\'re experiencing a mental health crisis, please call 988 (Suicide & Crisis Lifeline) in the US, or your local emergency services. You can also add emergency contacts in Personal Details who will be notified if needed.'
    ],
    [
        'question' => 'How do the exercises help?',
        'answer' => 'Our exercises are based on evidence-based techniques from cognitive behavioral therapy (CBT), mindfulness, and stress reduction practices. Regular practice can help reduce anxiety, improve mood, and build emotional resilience.'
    ],
    [
        'question' => 'Can this replace therapy or professional help?',
        'answer' => 'No. While MindCare is a valuable support tool, it\'s not a replacement for professional mental health care. If you\'re struggling, please consider speaking with a licensed therapist or counselor.'
    ],
    [
        'question' => 'How do I track my progress?',
        'answer' => 'Visit the "Previous Data" section from your dashboard to view your mood trends over time, statistics, and personalized insights based on your history.'
    ]
];
?>

<div class="min-h-screen">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4">
        <button onclick="navigateBack()" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to <span id="back-target-label">Dashboard</span>
        </button>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- Main View -->
        <div id="main-view" class="space-y-6">
            <!-- User Info Card -->
            <div class="p-6 bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 rounded-3xl shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="size-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                        <i data-lucide="user" class="size-8 text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold"><?php echo htmlspecialchars($userName); ?></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($userEmail); ?></p>
                    </div>
                </div>
            </div>

            <!-- Account Status Card -->
            <div class="p-6 bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 border-2 border-green-200 rounded-3xl relative overflow-hidden shadow-sm">
                <div class="absolute top-0 right-0 size-32 bg-gradient-to-br from-green-200 to-blue-200 rounded-full blur-3xl opacity-30"></div>
                <h3 class="font-semibold text-lg mb-4 flex items-center gap-2 relative">
                    <i data-lucide="shield" class="size-5 text-green-600"></i>
                    Account Status
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative">
                    <div class="flex items-start gap-3 p-3 bg-white/80 backdrop-blur rounded-2xl border border-green-200 hover:shadow-md transition-shadow">
                        <div class="size-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="check-circle" class="size-5 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Account</p>
                            <p class="font-semibold text-green-700">Active</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3 p-3 bg-white/80 backdrop-blur rounded-2xl border border-blue-200 hover:shadow-md transition-shadow">
                        <div class="size-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="trending-up" class="size-5 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Tracking</p>
                            <p class="font-semibold text-blue-700">Enabled</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3 p-3 bg-white/80 backdrop-blur rounded-2xl border border-purple-200 hover:shadow-md transition-shadow">
                        <div class="size-10 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="heart" class="size-5 text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Wellness</p>
                            <p class="font-semibold text-purple-700">Ready</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Options -->
            <div class="space-y-3">
                <div onclick="showView('settings')" class="p-4 bg-white border border-gray-100 rounded-2xl cursor-pointer hover:shadow-md transition-shadow flex items-center gap-3">
                    <div class="size-10 bg-gray-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="settings" class="size-5 text-gray-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold">Settings</h4>
                        <p class="text-sm text-gray-600">Manage app preferences</p>
                    </div>
                </div>

                <div onclick="showView('help')" class="p-4 bg-white border border-gray-100 rounded-2xl cursor-pointer hover:shadow-md transition-shadow flex items-center gap-3">
                    <div class="size-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="help-circle" class="size-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold">Help & Support</h4>
                        <p class="text-sm text-gray-600">Get assistance and resources</p>
                    </div>
                </div>

                <div onclick="showView('faq')" class="p-4 bg-white border border-gray-100 rounded-2xl cursor-pointer hover:shadow-md transition-shadow flex items-center gap-3">
                    <div class="size-10 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="message-circle" class="size-5 text-purple-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold">FAQ</h4>
                        <p class="text-sm text-gray-600">Frequently asked questions</p>
                    </div>
                </div>

                <div class="p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="size-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="log-out" class="size-5 text-red-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">Logout</h4>
                            <p class="text-sm text-gray-600">Sign out of your account</p>
                        </div>
                    </div>
                    <button onclick="confirmAction('logout')" class="px-4 py-2 border border-red-200 text-red-600 rounded-xl text-sm font-medium hover:bg-red-100 transition-colors">
                        Logout
                    </button>
                </div>

                <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="size-10 bg-gray-200 rounded-xl flex items-center justify-center">
                            <i data-lucide="trash-2" class="size-5 text-gray-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">Delete Account</h4>
                            <p class="text-sm text-gray-600">Permanently remove your data</p>
                        </div>
                    </div>
                    <button onclick="confirmAction('delete')" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-100 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings View -->
        <div id="settings-view" class="hidden space-y-4">
            <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm">
                <h3 class="font-semibold text-lg mb-4">App Settings</h3>
                <div class="space-y-4 text-sm text-gray-700">
                    <div class="flex items-center justify-between py-3 border-b border-gray-50">
                        <span>Daily reminder notifications</span>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Coming soon</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-50">
                        <span>Dark mode</span>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Coming soon</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-50">
                        <span>Data export</span>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Coming soon</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-4 italic">
                        More settings will be available in future updates.
                    </p>
                </div>
            </div>
        </div>

        <!-- Help View -->
        <div id="help-view" class="hidden space-y-4">
            <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm">
                <h3 class="font-semibold text-lg mb-4">Help & Support</h3>
                <div class="space-y-6">
                    <div>
                        <h4 class="font-semibold mb-2 text-red-600">Crisis Resources</h4>
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="p-3 bg-red-50 rounded-xl border border-red-100">
                                <p>🆘 <strong>988 Suicide & Crisis Lifeline</strong> (US)</p>
                            </div>
                            <div class="p-3 bg-red-50 rounded-xl border border-red-100">
                                <p>💬 <strong>Crisis Text Line:</strong> Text HOME to 741741</p>
                            </div>
                            <p class="text-xs text-gray-500">🌍 <strong>International:</strong> Find local resources at findahelpline.com</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-50 pt-6">
                        <h4 class="font-semibold mb-2">Contact Support</h4>
                        <p class="text-sm text-gray-700 mb-4">
                            Have questions or need technical assistance?
                        </p>
                        <a href="mailto:support@mindcare.app" class="inline-flex items-center gap-2 px-4 py-2 border border-blue-200 text-blue-600 rounded-xl text-sm font-medium hover:bg-blue-50">
                            support@mindcare.app
                        </a>
                    </div>

                    <div class="border-t border-gray-50 pt-6">
                        <h4 class="font-semibold mb-2 text-purple-600">Additional Resources</h4>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p class="flex items-center gap-2">
                                <span class="size-1.5 bg-purple-400 rounded-full"></span>
                                National Alliance on Mental Illness (NAMI): nami.org
                            </p>
                            <p class="flex items-center gap-2">
                                <span class="size-1.5 bg-purple-400 rounded-full"></span>
                                Mental Health America: mhanational.org
                            </p>
                            <p class="flex items-center gap-2">
                                <span class="size-1.5 bg-purple-400 rounded-full"></span>
                                Anxiety & Depression Association: adaa.org
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ View -->
        <div id="faq-view" class="hidden space-y-4">
            <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm">
                <h3 class="font-semibold text-lg mb-4">Frequently Asked Questions</h3>
                <div class="space-y-2">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="border border-gray-50 rounded-2xl overflow-hidden">
                            <button onclick="toggleFaq(<?php echo $index; ?>)" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <span class="font-medium text-sm"><?php echo htmlspecialchars($faq['question']); ?></span>
                                <i data-lucide="chevron-down" id="faq-icon-<?php echo $index; ?>" class="size-4 text-gray-400 transition-transform"></i>
                            </button>
                            <div id="faq-answer-<?php echo $index; ?>" class="hidden px-4 pb-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($faq['answer']); ?>
                            </div>
                        </div>
                    <?php
endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Modal -->
<div id="modal-container" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl scale-95 opacity-0 transition-all duration-300 transform" id="modal-box">
        <h3 id="modal-title" class="text-xl font-bold mb-2">Title</h3>
        <p id="modal-desc" class="text-gray-600 text-sm mb-6">Description</p>
        <div class="flex gap-3 justify-end">
            <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
            <button id="modal-action" class="px-4 py-2 text-sm font-medium text-white rounded-xl shadow-lg transition-all">Action</button>
        </div>
    </div>
</div>

<script>
    let currentActiveView = 'main';

    function showView(view) {
        // Hide current
        document.getElementById(currentActiveView + '-view').classList.add('hidden');
        
        // Show new
        document.getElementById(view + '-view').classList.remove('hidden');
        
        // Update breadcrumb label
        const backLabels = {
            'main': 'Dashboard',
            'settings': 'Profile',
            'help': 'Profile',
            'faq': 'Profile'
        };
        document.getElementById('back-target-label').innerText = backLabels[view];
        
        currentActiveView = view;
        window.scrollTo(0, 0);
    }

    function navigateBack() {
        if (currentActiveView === 'main') {
            window.location.href = 'dashboard.php';
        } else {
            showView('main');
        }
    }

    function toggleFaq(index) {
        const answer = document.getElementById('faq-answer-' + index);
        const icon = document.getElementById('faq-icon-' + index);
        
        if (answer.classList.contains('hidden')) {
            answer.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            answer.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    function confirmAction(type) {
        const modal = document.getElementById('modal-container');
        const box = document.getElementById('modal-box');
        const title = document.getElementById('modal-title');
        const desc = document.getElementById('modal-desc');
        const actionBtn = document.getElementById('modal-action');

        if (type === 'logout') {
            title.innerText = 'Confirm Logout';
            desc.innerText = 'Are you sure you want to logout? You\'ll need to sign in again to access your data.';
            actionBtn.innerText = 'Logout';
            actionBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl shadow-lg hover:bg-red-700 transition-all';
            actionBtn.onclick = () => window.location.href = 'auth/logout.php';
        } else if (type === 'delete') {
            title.innerText = 'Delete Account?';
            desc.innerText = 'This action cannot be undone. This will permanently delete your account and remove all your data from our servers.';
            actionBtn.innerText = 'Delete Account';
            actionBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl shadow-lg hover:bg-red-700 transition-all';
            actionBtn.onclick = () => alert('Account deletion would happen here in a real app.');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('modal-container');
        const box = document.getElementById('modal-box');
        
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
</script>

<?php include_once 'includes/footer.php'; ?>
