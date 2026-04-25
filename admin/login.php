<?php
// Start session with security settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => false, // Set to true if using HTTPS
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Regenerate session ID to prevent fixation
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
}

// Rate limiting configuration
$rate_limit_file = __DIR__ . '/../logs/rate_limit.json';
$max_attempts = 5; // Maximum attempts
$decay_minutes = 15; // Time window in minutes

// Function to check rate limiting
function isRateLimited($ip) {
    global $rate_limit_file, $max_attempts, $decay_minutes;
    
    if (!file_exists(dirname($rate_limit_file))) {
        mkdir(dirname($rate_limit_file), 0755, true);
    }
    
    $attempts = [];
    if (file_exists($rate_limit_file)) {
        $attempts = json_decode(file_get_contents($rate_limit_file), true) ?: [];
    }
    
    // Clean old attempts
    $cutoff = time() - ($decay_minutes * 60);
    foreach ($attempts as $key => $attempt) {
        if ($attempt['time'] < $cutoff) {
            unset($attempts[$key]);
        }
    }
    
    // Check attempts for this IP
    $ip_attempts = 0;
    foreach ($attempts as $attempt) {
        if ($attempt['ip'] === $ip) {
            $ip_attempts++;
        }
    }
    
    return $ip_attempts >= $max_attempts;
}

// Function to log failed attempt
function logFailedAttempt($ip, $email) {
    global $rate_limit_file;
    
    $attempts = [];
    if (file_exists($rate_limit_file)) {
        $attempts = json_decode(file_get_contents($rate_limit_file), true) ?: [];
    }
    
    $attempts[] = [
        'ip' => $ip,
        'email' => $email,
        'time' => time(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ];
    
    // Keep only last 100 attempts
    if (count($attempts) > 100) {
        $attempts = array_slice($attempts, -100);
    }
    
    file_put_contents($rate_limit_file, json_encode($attempts));
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle login form submission
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Invalid security token. Please refresh the page.';
    } else {
        // Check rate limiting
        $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        if (isRateLimited($user_ip)) {
            $error_message = 'Too many login attempts. Please try again after ' . $decay_minutes . ' minutes.';
        } else {
            // Sanitize and validate inputs
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Invalid email format.';
                logFailedAttempt($user_ip, $email);
            } elseif (empty($password)) {
                $error_message = 'Password is required.';
                logFailedAttempt($user_ip, $email);
            } else {
                // Database connection
                require_once __DIR__ . '/../api/config/database.php';
                
                try {
                    $conn = getDB();
                    
                    // Use prepared statement to prevent SQL injection
                    $stmt = $conn->prepare("SELECT id, name, email, password, role, store_id, is_active FROM users WHERE email = :email");
                    $stmt->execute([':email' => $email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user && password_verify($password, $user['password'])) {
                        // Check if account is active
                        if ($user['is_active'] != 1) {
                            $error_message = 'Your account has been deactivated. Please contact administrator.';
                            logFailedAttempt($user_ip, $email);
                        } else {
                            // Regenerate session ID after successful login (security)
                            session_regenerate_id(true);
                            
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_role'] = $user['role'];
                            $_SESSION['store_id'] = $user['store_id'];
                            $_SESSION['login_time'] = time();
                            $_SESSION['ip_address'] = $user_ip;
                            
                            // Update last login timestamp
                            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                            $updateStmt->execute([':id' => $user['id']]);
                            
                            // Log successful login
                            $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip)");
                            $logStmt->execute([
                                ':user_id' => $user['id'],
                                ':action' => 'LOGIN_SUCCESS',
                                ':details' => 'User logged in successfully',
                                ':ip' => $user_ip
                            ]);
                            
                            // Clear rate limit attempts on success
                            if (file_exists($rate_limit_file)) {
                                $attempts = json_decode(file_get_contents($rate_limit_file), true) ?: [];
                                $attempts = array_filter($attempts, function($attempt) use ($user_ip) {
                                    return $attempt['ip'] !== $user_ip;
                                });
                                file_put_contents($rate_limit_file, json_encode(array_values($attempts)));
                            }
                            
                            // Redirect based on role
                            if ($user['role'] === 'admin' || $user['role'] === 'supervisor') {
                                header('Location: dashboard.php');
                            } else {
                                header('Location: pos.php');
                            }
                            exit();
                        }
                    } else {
                        $error_message = 'Invalid email or password.';
                        logFailedAttempt($user_ip, $email);
                    }
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    $error_message = 'System error. Please try again later.';
                }
            }
        }
    }
    
    // Generate new CSRF token for next attempt
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['login_time'])) {
    // Session timeout after 8 hours
    $session_timeout = 8 * 60 * 60; // 8 hours
    if (time() - $_SESSION['login_time'] < $session_timeout) {
        // Redirect based on role
        if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'supervisor') {
            header('Location: dashboard.php');
        } else {
            header('Location: pos.php');
        }
        exit();
    } else {
        // Session expired
        session_destroy();
        session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPOS - Admin Login | Ultimate POS System</title>
    
    <!-- Security Headers -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <!-- Tailwind CSS + Bootstrap Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            background: #0a0a2a;
        }
        
        /* Background Image with Overlay */
        .hero-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            object-fit: cover;
            object-position: center;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, 
                rgba(0, 0, 0, 0.75) 0%, 
                rgba(0, 0, 0, 0.6) 50%,
                rgba(102, 126, 234, 0.4) 100%);
            z-index: 1;
        }
        
        /* Animated background particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 2;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Content Container */
        .content-container {
            position: relative;
            z-index: 3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(10px);
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.6);
        }
        
        .input-group-custom {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .input-group-custom input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .input-group-custom input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .input-group-custom i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            transition: color 0.3s ease;
        }
        
        .input-group-custom:focus-within i {
            color: #667eea;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-gradient:hover::before {
            left: 100%;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.5);
        }
        
        .btn-gradient:active {
            transform: translateY(0);
        }
        
        /* Alert animations */
        .alert {
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Loading spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Floating animation for logo */
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .floating-logo {
            animation: floatLogo 3s ease-in-out infinite;
        }
        
        .pulse-logo {
            animation: pulse 2s infinite;
        }
        
        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
        }
        
        /* Responsive */
        @media (max-width: 640px) {
            .login-card {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background Image - Happy people using POS system -->
    <img src="https://images.pexels.com/photos/4427658/pexels-photo-4427658.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" 
         alt="Happy business owners using POS system" 
         class="hero-background">
    
    <!-- Alternative local image if needed (uncomment and use your own image) -->
    <!-- 
    <img src="../assets/images/pos-happy-customers.jpg" 
         alt="Happy business owners using POS system" 
         class="hero-background">
    -->
    
    <!-- Overlay for better readability -->
    <div class="overlay"></div>
    
    <!-- Animated Particles -->
    <div class="particles" id="particles"></div>
    
    <div class="content-container">
        <div class="w-full max-w-md">
            <!-- Logo & Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-2xl shadow-xl mb-4 pulse-logo floating-logo">
                    <i class="bi bi-cart4 text-5xl bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2 drop-shadow-lg">SmartPOS</h1>
                <p class="text-white/90 text-lg drop-shadow">Ultimate Point of Sale System</p>
            </div>
            
            <!-- Login Card -->
            <div class="login-card p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Welcome Back!</h2>
                    <p class="text-gray-500 text-sm mt-2">Login to access your dashboard</p>
                </div>
                
                <?php if ($error_message): ?>
                    <div class="alert mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm flex items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-600 text-sm flex items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>
                
                <form id="loginForm" method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <div class="mb-5">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            <i class="bi bi-envelope me-1"></i> Email Address
                        </label>
                        <div class="input-group-custom">
                            <i class="bi bi-envelope-fill"></i>
                            <input type="email" 
                                   id="email" 
                                   name="email"
                                   class="w-full"
                                   placeholder="admin@smartpos.com" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required 
                                   autocomplete="email">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            <i class="bi bi-lock me-1"></i> Password
                        </label>
                        <div class="input-group-custom">
                            <i class="bi bi-lock-fill"></i>
                            <input type="password" 
                                   id="password" 
                                   name="password"
                                   class="w-full"
                                   placeholder="••••••••" 
                                   required
                                   autocomplete="current-password">
                            <i class="bi bi-eye-slash toggle-password" style="left: auto; right: 1rem; cursor: pointer;"></i>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="text-sm text-purple-600 hover:text-purple-700 transition">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" 
                            id="submitBtn"
                            class="btn-gradient w-full py-3 text-white font-bold rounded-xl flex items-center justify-center gap-2">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login to Dashboard</span>
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        <i class="bi bi-shield-check me-1"></i>
                        Secure login with 256-bit encryption
                    </p>
                </div>
                
                <!-- Features Badges -->
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs flex items-center gap-1">
                        <i class="bi bi-check-circle-fill text-xs"></i> Real-time Analytics
                    </span>
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs flex items-center gap-1">
                        <i class="bi bi-check-circle-fill text-xs"></i> Inventory Management
                    </span>
                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs flex items-center gap-1">
                        <i class="bi bi-check-circle-fill text-xs"></i> Customer Loyalty
                    </span>
                </div>
                
                <!-- Demo Credentials -->
                <div class="mt-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl border border-purple-100">
                    <p class="text-xs text-gray-500 text-center mb-2 flex items-center justify-center gap-1">
                        <i class="bi bi-info-circle-fill"></i> Demo Credentials
                    </p>
                    <div class="flex justify-center gap-6 text-xs">
                        <div class="text-center">
                            <span class="font-semibold text-purple-600">Admin:</span>
                            <span class="text-gray-600 block text-xs">admin@smartpos.com</span>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-purple-600">Password:</span>
                            <span class="text-gray-600 block text-xs">admin123</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-8 text-white/80 text-sm">
                <p>&copy; 2024 SmartPOS - Made with <i class="bi bi-heart-fill text-red-400"></i> in Tanzania</p>
                <p class="text-xs mt-2 opacity-75">Protected against SQL Injection, XSS, CSRF, and Brute Force attacks</p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input[type="password"], input[type="text"]');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        });
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div> Logging in...';
        });
        
        // Create particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 60;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 15 + 3;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = Math.random() * 25 + 10 + 's';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.opacity = Math.random() * 0.4 + 0.1;
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize
        createParticles();
        
        // Remember me functionality
        const rememberCheckbox = document.getElementById('remember');
        const emailInput = document.getElementById('email');
        
        // Load saved email if remember me was checked
        if (localStorage.getItem('remembered_email')) {
            emailInput.value = localStorage.getItem('remembered_email');
            rememberCheckbox.checked = true;
        }
        
        rememberCheckbox.addEventListener('change', function() {
            if (this.checked) {
                localStorage.setItem('remembered_email', emailInput.value);
            } else {
                localStorage.removeItem('remembered_email');
            }
        });
        
        emailInput.addEventListener('change', function() {
            if (rememberCheckbox.checked) {
                localStorage.setItem('remembered_email', this.value);
            }
        });
        
        // Preload background image for smooth appearance
        const img = new Image();
        img.src = "https://images.pexels.com/photos/4427658/pexels-photo-4427658.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop";
    </script>
</body>
</html>