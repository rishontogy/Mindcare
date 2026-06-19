<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include_once 'includes/header.php';
redirect_if_not_logged_in();

$user_id = get_current_user_id();
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_name     = clean_input($_POST['parentName'] ?? '');
    $parent_phone    = clean_input($_POST['parentPhone'] ?? '');
    $parent_email    = clean_input($_POST['parentEmail'] ?? '');
    $guardian_name   = clean_input($_POST['guardianName'] ?? '');
    $guardian_phone  = clean_input($_POST['guardianPhone'] ?? '');
    $guardian_email  = clean_input($_POST['guardianEmail'] ?? '');
    $spouse_name     = clean_input($_POST['spouseName'] ?? '');
    $spouse_phone    = clean_input($_POST['spousePhone'] ?? '');
    $spouse_email    = clean_input($_POST['spouseEmail'] ?? '');
    $emergency_contact = clean_input($_POST['emergencyContact'] ?? '');
    $emergency_phone   = clean_input($_POST['emergencyPhone'] ?? '');

    // Use INSERT ... ON DUPLICATE KEY UPDATE (upsert) so it works for both new and existing records
    $stmt = $pdo->prepare("
        INSERT INTO personal_details
            (user_id, parent_name, parent_phone, parent_email,
             guardian_name, guardian_phone, guardian_email,
             spouse_name, spouse_phone, spouse_email,
             emergency_contact, emergency_phone)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            parent_name      = VALUES(parent_name),
            parent_phone     = VALUES(parent_phone),
            parent_email     = VALUES(parent_email),
            guardian_name    = VALUES(guardian_name),
            guardian_phone   = VALUES(guardian_phone),
            guardian_email   = VALUES(guardian_email),
            spouse_name      = VALUES(spouse_name),
            spouse_phone     = VALUES(spouse_phone),
            spouse_email     = VALUES(spouse_email),
            emergency_contact = VALUES(emergency_contact),
            emergency_phone  = VALUES(emergency_phone),
            updated_at       = CURRENT_TIMESTAMP
    ");

    if ($stmt->execute([
        $user_id,
        $parent_name,
        $parent_phone,
        $parent_email,
        $guardian_name,
        $guardian_phone,
        $guardian_email,
        $spouse_name,
        $spouse_phone,
        $spouse_email,
        $emergency_contact,
        $emergency_phone
    ])) {
        $success = 'Emergency contacts saved successfully!';
    } else {
        $error = 'Failed to save details. Please try again.';
    }
}

// Fetch existing details
$details = [
    'parent_name' => '',
    'parent_phone' => '',
    'parent_email' => '',
    'guardian_name' => '',
    'guardian_phone' => '',
    'guardian_email' => '',
    'spouse_name' => '',
    'spouse_phone' => '',
    'spouse_email' => '',
    'emergency_contact' => '',
    'emergency_phone' => ''
];

$stmt = $pdo->prepare("SELECT * FROM personal_details WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
if ($row = $stmt->fetch()) {
    $details = $row;
}
?>

<div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-teal-50">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-sm border-b border-purple-100 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="arrow-left" class="size-4"></i>
                </a>
                <div class="flex items-center gap-2">
                    <div class="size-8 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                        <i data-lucide="brain" class="size-5 text-white"></i>
                    </div>
                    <h1 class="text-lg font-bold">Emergency Contacts</h1>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <div class="p-6 mb-6 bg-amber-50 border border-amber-200 rounded-3xl shadow-sm">
            <div class="flex items-start gap-3">
                <i data-lucide="alert-circle" class="size-5 text-amber-600 shrink-0 mt-0.5"></i>
                <div>
                    <h3 class="font-semibold text-amber-900 mb-1">Why This Matters</h3>
                    <p class="text-sm text-amber-800 leading-relaxed">
                        If you're experiencing a crisis, the system can alert your trusted contacts. These contacts
                        will only be reached in emergency situations based on your assessment results. Your privacy is protected.
                    </p>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl flex items-center gap-2 text-green-800 animate-in fade-in slide-in-from-top-2">
                <i data-lucide="check-circle" class="size-5"></i>
                <p class="text-sm font-medium"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-2 text-red-800">
                <i data-lucide="alert-circle" class="size-5"></i>
                <p class="text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Parent Details -->
            <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm">
                <h3 class="font-semibold text-lg mb-4 text-gray-800">Parent/Guardian Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Name</label>
                        <input type="text" name="parentName" value="<?php echo htmlspecialchars($details['parent_name'] ?? ''); ?>" placeholder="e.g., John Doe"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="parentPhone" value="<?php echo htmlspecialchars($details['parent_phone'] ?? ''); ?>" placeholder="(555) 123-4567"
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="parentEmail" value="<?php echo htmlspecialchars($details['parent_email'] ?? ''); ?>" placeholder="parent@example.com"
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guardian Details -->
            <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm">
                <h3 class="font-semibold text-lg mb-4 text-gray-800">Secondary Guardian</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Guardian Name</label>
                        <input type="text" name="guardianName" value="<?php echo htmlspecialchars($details['guardian_name'] ?? ''); ?>" placeholder="e.g., Jane Smith"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="guardianPhone" value="<?php echo htmlspecialchars($details['guardian_phone'] ?? ''); ?>" placeholder="(555) 123-4567"
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="guardianEmail" value="<?php echo htmlspecialchars($details['guardian_email'] ?? ''); ?>" placeholder="guardian@example.com"
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spouse/Partner Details -->
            <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-sm">
                <h3 class="font-semibold text-lg mb-4 text-gray-800">Spouse/Partner (Optional)</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Spouse/Partner Name</label>
                        <input type="text" name="spouseName" value="<?php echo htmlspecialchars($details['spouse_name'] ?? ''); ?>" placeholder="e.g., Alex Johnson"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="spousePhone" value="<?php echo htmlspecialchars($details['spouse_phone'] ?? ''); ?>" placeholder="(555) 123-4567"
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="spouseEmail" value="<?php echo htmlspecialchars($details['spouse_email'] ?? ''); ?>" placeholder="spouse@example.com"
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Primary Emergency Contact -->
            <div class="p-6 bg-red-50 border border-red-100 rounded-3xl shadow-sm">
                <h3 class="font-semibold text-lg mb-4 text-red-900">
                    Primary Emergency Contact
                </h3>
                <p class="text-sm text-red-800 mb-4 font-medium italic">
                    This person will be contacted first in case of a crisis situation.
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-red-900 mb-1">Full Name *</label>
                        <input type="text" name="emergencyContact" value="<?php echo htmlspecialchars($details['emergency_contact'] ?? ''); ?>" placeholder="e.g., Sarah Williams" required
                            class="w-full px-4 py-2 border border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition-all bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-red-900 mb-1">Phone Number *</label>
                        <input type="tel" name="emergencyPhone" value="<?php echo htmlspecialchars($details['emergency_phone'] ?? ''); ?>" placeholder="(555) 123-4567" required
                            class="w-full px-4 py-2 border border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition-all bg-white">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 pb-8">
                <p class="text-xs text-gray-500 font-medium">
                    * Required fields
                </p>
                <button type="submit"
                    class="bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold px-8 py-3 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-[1.02]">
                    <i data-lucide="save" class="inline-block size-4 mr-2 mb-0.5"></i>
                    Save Contacts
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>