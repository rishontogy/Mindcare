<?php
require_once __DIR__ . '/../init.php';

// Redirect if already logged in
Auth::redirectIfLoggedIn('/dashboard.php');

$loginError = '';
$loginSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = Validator::sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (!Validator::validateEmail($email)) {
        $loginError = 'Please enter a valid email address';
    } elseif (!Validator::validateString($password, 1)) {
        $loginError = 'Password is required';
    } else {
        // Create user model and authenticate
        $userModel = new User($conn);
        $user = $userModel->findByEmail($email);
        
        if ($user && Auth::verifyPassword($password, $user['password'])) {
            // Set session
            Session::set('user_id', $user['id']);
            Session::set('user_email', $user['email']);
            Session::set('user_name', $user['name']);
            Session::set('user_type', $user['user_type']);
            
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            $loginError = 'Invalid email or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - MindCare</title>
    <link rel="stylesheet" href="<?php echo constant('APP_URL'); ?>css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(59, 130, 246, 0.1));
            padding: 2rem;
        }
        
        .login-form {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        
        .login-form h1 {
            text-align: center;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #7c3aed, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-subtitle {
            text-align: center;
            color: var(--gray-600);
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-error {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #7f1d1d;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
        }
        
        .login-button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #7c3aed, #3b82f6);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .login-button:hover {
            filter: brightness(1.1);
        }
        
        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--gray-600);
        }
        
        .login-footer a {
            color: #7c3aed;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="POST">
            <h1>Welcome Back!</h1>
            <p class="login-subtitle">Sign in to your MindCare account</p>
            
            <?php if ($loginError): ?>
                <div class="form-error">
                    ❌ <?php echo htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="your@email.com" 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="••••••••" 
                    required
                >
            </div>
            
            <button type="submit" class="login-button">Sign In</button>
            
            <div class="login-footer">
                Don't have an account? <a href="signup.php">Create one</a>
                <br><br>
                <a href="parent-login.php">Sign in as Parent?</a>
            </div>
        </form>
    </div>
</body>
</html>
