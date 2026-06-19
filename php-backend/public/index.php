<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindCare - Mental Wellness Companion</title>
    <link rel="stylesheet" href="<?php echo constant('APP_URL'); ?>css/style.css">
    <style>
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(59, 130, 246, 0.1), rgba(20, 184, 166, 0.1));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            text-align: center;
        }
        
        .hero-content {
            max-width: 800px;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #7c3aed, #3b82f6, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            animation: fadeInDown 0.8s ease-out;
        }
        
        .hero-content p {
            font-size: 1.25rem;
            color: var(--gray-600);
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease-out 0.2s backwards;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease-out 0.4s backwards;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
            padding: 0 2rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #7c3aed, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-menu {
            display: flex;
            gap: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            🧠 MindCare
        </div>
        <div class="navbar-menu">
            <a href="#features" class="text-decoration-none" style="color: var(--gray-700);">Features</a>
            <a href="login.php" class="btn btn-outline btn-sm">Login</a>
            <a href="signup.php" class="btn btn-primary btn-sm">Sign Up</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Your Mental Wellness Companion</h1>
            <p>A safe, private, and intuitive platform designed to support students through academic pressure, stress, and anxiety.</p>
            <p style="color: #10b981; font-weight: 600;">👨‍👩‍👧‍👦 Parents can now monitor and support their child's wellness journey!</p>
            
            <div class="hero-buttons">
                <a href="signup.php" class="btn btn-primary btn-lg">Get Started</a>
                <a href="login.php" class="btn btn-outline btn-lg">Already a Member?</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" style="padding: 4rem 2rem; background: var(--gray-50);">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem;">Why Choose MindCare?</h2>
            
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Safe & Private</h3>
                    <p>Your data is encrypted and secure. We prioritize your privacy above all.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <h3>AI-Powered Analysis</h3>
                    <p>Daily mood assessments with personalized exercise recommendations.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💪</div>
                    <h3>Guided Exercises</h3>
                    <p>Meditation, breathing, and relaxation exercises tailored to your needs.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🆘</div>
                    <h3>Crisis Support</h3>
                    <p>Emergency contact alerts and counseling resources when needed most.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 MindCare. All rights reserved.</p>
            <p>
                <a href="#">Privacy Policy</a> | 
                <a href="#">Terms of Service</a> | 
                <a href="#">Contact Us</a>
            </p>
        </div>
    </footer>
</body>
</html>
