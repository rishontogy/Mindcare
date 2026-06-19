<?php
/**
 * ACCOUNT FIX SCRIPT
 * This script helps you understand why login is failing and provides solutions.
 */
require_once __DIR__ . '/includes/db.php';

echo "=== ACCOUNT DIAGNOSTIC & FIX ===\n\n";

echo "PROBLEM: Your account exists but login fails.\n";
echo "CAUSE: Your Supabase project requires email confirmation.\n\n";

echo "SOLUTION OPTIONS:\n\n";

echo "Option 1: CHECK YOUR EMAIL\n";
echo "  1. Look for an email from Supabase (check spam folder too)\n";
echo "  2. Click the confirmation link in the email\n";
echo "  3. Then try logging in again\n\n";

echo "Option 2: DISABLE EMAIL CONFIRMATION (Recommended for testing)\n";
echo "  1. Go to: https://supabase.com/dashboard/project/qzwoilsycjbhzbtwzxxe/auth/settings\n";
echo "  2. Scroll to 'Email Auth'\n";
echo "  3. Turn OFF 'Enable email confirmations'\n";
echo "  4. Go to Authentication > Users\n";
echo "  5. Delete your existing user account\n";
echo "  6. Sign up again at: http://localhost:8000/signup.php\n";
echo "  7. Login should work immediately!\n\n";

echo "Option 3: USE A DIFFERENT EMAIL\n";
echo "  1. Sign up with a NEW email you haven't used before\n";
echo "  2. If confirmation is still enabled, check that email's inbox\n\n";

echo "=== TECHNICAL DETAILS ===\n";
echo "Your Supabase Project: qzwoilsycjbhzbtwzxxe\n";
echo "Dashboard: https://supabase.com/dashboard/project/qzwoilsycjbhzbtwzxxe\n";
echo "Local App: http://localhost:8000\n\n";

echo "Once you've completed ONE of the options above, try logging in!\n";
?>
