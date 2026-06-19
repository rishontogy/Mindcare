<?php
require_once __DIR__ . '/php-backend/init.php';
include_once 'includes/header.php';

Auth::checkParentLogin();

$user = Session::getUser();
$userId = $user['user_id'];
$userModel = new User($pdo);
$userDetails = $userModel->findById($userId);

$successMessage = '';
$errorMessage = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = Validator::sanitizeInput($_POST['name'] ?? '');
        $phoneNumber = Validator::sanitizeInput($_POST['phone'] ?? '');

        if (empty($name)) {
            $errorMessage = 'Name is required.';
        } else {
            $updateData = ['name' => $name, 'phone' => $phoneNumber];
            if ($userModel->update($userId, $updateData)) {
                $successMessage = 'Profile updated successfully!';
                $userDetails = $userModel->findById($userId);
                Session::set('user_name', $name);
            } else {
                $errorMessage = 'Error updating profile.';
            }
        }
    } elseif ($action === 'unlink_student') {
        $studentId = intval($_POST['student_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM parent_student_links WHERE parent_id = :parent_id AND student_id = :student_id");
        if ($stmt->execute([':parent_id' => $userId, ':student_id' => $studentId])) {
            $successMessage = 'Student account unlinked.';
        } else {
            $errorMessage = 'Error unlinking student.';
        }
    } elseif ($action === 'link_student') {
        $studentEmail = Validator::sanitizeInput($_POST['student_email'] ?? '');
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND user_type = 'student'");
        $stmt->execute([':email' => $studentEmail]);
        $student = $stmt->fetch();

        if ($student) {
            try {
                $insertStmt = $pdo->prepare("INSERT INTO parent_student_links (parent_id, student_id, status) VALUES (:parent_id, :student_id, 'pending')");
                $insertStmt->execute([':parent_id' => $userId, ':student_id' => $student['id']]);
                $successMessage = 'Linking request sent to student! They must accept it from their dashboard.';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errorMessage = 'A request is already pending, or this student is already linked to your account.';
                } else {
                    $errorMessage = 'Error sending linking request.';
                }
            }
        } else {
            $errorMessage = 'Student account not found with this email.';
        }
    }
}

// Fetch linked students (accepted only)
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, psl.created_at 
    FROM users u 
    JOIN parent_student_links psl ON u.id = psl.student_id 
    WHERE psl.parent_id = :parent_id AND psl.status = 'accepted'
");
$stmt->execute([':parent_id' => $userId]);
$linkedStudents = $stmt->fetchAll();

// Fetch pending requests
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, psl.created_at 
    FROM users u 
    JOIN parent_student_links psl ON u.id = psl.student_id 
    WHERE psl.parent_id = :parent_id AND psl.status = 'pending'
");
$stmt->execute([':parent_id' => $userId]);
$pendingRequests = $stmt->fetchAll();

$activeStudentId = Session::get('active_student_id');

if (isset($_GET['set_active'])) {
    $newActiveId = intval($_GET['set_active']);
    // Verify this student belongs to the parent and is accepted
    $checkStmt = $pdo->prepare("SELECT 1 FROM parent_student_links WHERE parent_id = :parent_id AND student_id = :student_id AND status = 'accepted'");
    $checkStmt->execute([':parent_id' => $userId, ':student_id' => $newActiveId]);
    if ($checkStmt->fetch()) {
        Session::set('active_student_id', $newActiveId);
        $activeStudentId = $newActiveId;
        header('Location: parent-profile.php');
        exit;
    }
}

if (!$activeStudentId && !empty($linkedStudents)) {
    $activeStudentId = $linkedStudents[0]['id'];
    Session::set('active_student_id', $activeStudentId);
}
?>

?>
<div class="min-h-screen">
    <!-- Breadcrumbs/Back -->
    <div class="container mx-auto px-4 py-4">
        <a href="parent-dashboard.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Parent Profile</h1>
            <p class="text-gray-600">Representing your account and linked student profiles</p>
        </div>

        <?php if ($successMessage): ?>
            <div class="p-4 mb-6 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-2">
                <i data-lucide="check-circle" class="size-5"></i> <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="p-4 mb-6 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-2">
                <i data-lucide="alert-circle" class="size-5"></i> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Parent Info Card -->
        <div class="p-8 bg-white border border-gray-100 rounded-[32px] shadow-sm mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="size-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                    <i data-lucide="user" class="size-6"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Your Information</h2>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <span class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Full Name</span>
                    <span class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($userDetails['name']); ?></span>
                </div>

                <div>
                    <span class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Email Address</span>
                    <span class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($userDetails['email']); ?></span>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-50">
                <span class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-full text-xs font-black uppercase tracking-widest">
                    Parent Account
                </span>
            </div>
        </div>

        <!-- Linked Students Card -->
        <div class="p-8 bg-white border border-gray-100 rounded-[32px] shadow-sm mb-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="size-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                            <i data-lucide="users" class="size-6"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Linked Student Accounts</h2>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">Manage accounts that have accepted your linking request.</p>
                </div>
                <?php if (count($linkedStudents) + count($pendingRequests) < 5): ?>
                    <button class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-2xl font-bold shadow-lg shadow-emerald-100 hover:scale-105 transition-all flex items-center gap-2" onclick="openAddModal()">
                        <i data-lucide="plus" class="size-5"></i>
                        Send Request
                    </button>
                <?php endif; ?>
            </div>

            <?php if (empty($linkedStudents)): ?>
                <div class="p-12 text-center bg-gray-50 border-2 border-dashed border-gray-200 rounded-[2rem] mb-8">
                    <p class="text-gray-500 font-medium">No accepted student accounts yet</p>
                </div>
            <?php else: ?>
                <div class="grid gap-4 mb-8">
                    <?php foreach ($linkedStudents as $student): ?>
                        <div class="p-6 border border-gray-100 rounded-3xl flex items-center justify-between bg-white hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-4">
                                <div class="size-14 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-black text-xl">
                                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                        <?php echo htmlspecialchars($student['name']); ?>
                                        <?php if ($student['id'] == $activeStudentId): ?>
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
                                                <i data-lucide="check" class="size-3"></i> Active
                                            </span>
                                        <?php endif; ?>
                                    </h3>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($student['email']); ?></p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Linked: <?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if ($student['id'] != $activeStudentId): ?>
                                    <a href="?set_active=<?php echo $student['id']; ?>" class="px-4 py-2 text-sm font-bold text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">Monitor This Child</a>
                                <?php endif; ?>
                                <form method="POST" onsubmit="return confirm('Unlink this student?');" class="m-0">
                                    <input type="hidden" name="action" value="unlink_student">
                                    <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                    <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                        <i data-lucide="trash-2" class="size-5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="mb-6 pt-8 border-t border-gray-50">
                <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-1">
                    <i data-lucide="send" class="size-5 text-indigo-500"></i>
                    Pending Requests
                </h3>
                <p class="text-sm text-gray-500 font-medium">Awaiting acceptance from the student.</p>
            </div>

            <?php if (empty($pendingRequests)): ?>
                <div class="p-8 text-center bg-gray-50 border border-dashed border-gray-200 rounded-3xl">
                    <p class="text-sm text-gray-400 font-medium italic">No pending requests</p>
                </div>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach ($pendingRequests as $student): ?>
                        <div class="p-6 border border-dashed border-indigo-100 rounded-3xl flex items-center justify-between bg-white opacity-80">
                            <div class="flex items-center gap-4">
                                <div class="size-14 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400">
                                    <i data-lucide="help-circle" class="size-8"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($student['email']); ?></h3>
                                    <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mt-1">Request Sent...</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Sent: <?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
                                </div>
                            </div>
                            <form method="POST" onsubmit="return confirm('Cancel this request?');" class="m-0">
                                <input type="hidden" name="action" value="unlink_student">
                                <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                <button type="submit" class="px-4 py-2 text-sm font-bold text-red-500 hover:bg-red-50 rounded-xl transition-colors">Cancel Request</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($linkedStudents) + count($pendingRequests) >= 5): ?>
                <div class="mt-8 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-2xl text-sm font-medium flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="size-5"></i> 
                    Maximum limit reached. You can have up to 5 linked students or pending requests.
                </div>
            <?php endif; ?>
        </div>

        <div class="p-6 bg-indigo-50 border border-indigo-100 rounded-3xl flex items-start gap-4">
            <div class="size-10 bg-white rounded-xl flex items-center justify-center text-indigo-500 shrink-0 shadow-sm shadow-indigo-100">
                <i data-lucide="info" class="size-6"></i>
            </div>
            <p class="text-sm text-indigo-800 leading-relaxed">
                <strong>How linking works:</strong> When you send a linking request to a student's email, they will see a notification on their dashboard. Once they accept the request, you'll be able to view their wellness data and monitor their progress.
            </p>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="addModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-[32px] max-w-md w-full p-8 shadow-2xl animate-in zoom-in-95 duration-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Send Linking Request</h2>
        <form action="parent-profile.php" method="POST">
            <input type="hidden" name="action" value="link_student">
            <div class="mb-8">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Student's Email Address</label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-400"></i>
                    <input type="email" name="student_email" class="w-full pl-12 pr-4 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none transition-shadow font-medium" placeholder="student@example.com" required>
                </div>
                <p class="text-xs text-gray-500 mt-3 font-medium flex items-start gap-2">
                    <i data-lucide="shield-check" class="size-4 shrink-0 mt-0.5"></i>
                    A request will be sent to the student. They must accept it for you to see their data.
                </p>
            </div>
            <div class="flex gap-3">
                <button type="button" class="flex-1 py-4 text-gray-500 font-bold hover:bg-gray-50 rounded-2xl transition-all" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="flex-2 px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-2xl font-bold shadow-lg shadow-emerald-100 hover:scale-[1.02] transition-all">Send Request</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
        document.getElementById('addModal').classList.add('flex');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
</script>

<?php include_once 'includes/footer.php'; ?>

</html>