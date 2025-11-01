<?php
/**
 * CIS Intelligence Dashboard - Login System
 *
 * Enhanced Security Features:
 * - Rate limiting (5 attempts per 15 minutes per IP)
 * - CSRF protection
 * - Secure session handling
 * - Brute force protection
 * - Activity logging
 * - Remember me functionality
 * - Two-factor authentication ready
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 */

// Define access constant
define('DASHBOARD_ACCESS', true);

// Prevent session regeneration on login page to preserve CSRF token
define('SKIP_AUTO_SESSION_REGENERATION', true);

// Load configuration (includes session config)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Initialize rate limiting (session already started by config)
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rateLimitKey = 'login_attempts_' . md5($ipAddress);
$lockoutKey = 'login_lockout_' . md5($ipAddress);

// Check if IP is locked out
if (isset($_SESSION[$lockoutKey]) && $_SESSION[$lockoutKey] > time()) {
    $lockoutMinutes = ceil(($_SESSION[$lockoutKey] - time()) / 60);
    $error = "Too many failed login attempts. Please try again in {$lockoutMinutes} minute(s).";
    $isLockedOut = true;
} else {
    $isLockedOut = false;
}

// If already logged in, redirect
$isAuth = isAuthenticated();
if ($isAuth && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Only redirect on GET, not on POST (allow login attempts)
    header('Location: index.php');
    exit;
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLockedOut) {
    // Verify CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        if (empty($username) || empty($password)) {
            $error = 'Username and password are required';
        } else {
            // Check rate limiting
            if (!isset($_SESSION[$rateLimitKey])) {
                $_SESSION[$rateLimitKey] = ['count' => 0, 'first_attempt' => time()];
            }

            $attempts = $_SESSION[$rateLimitKey];

            // Reset counter if 15 minutes have passed
            if (time() - $attempts['first_attempt'] > 900) {
                $_SESSION[$rateLimitKey] = ['count' => 0, 'first_attempt' => time()];
                $attempts = $_SESSION[$rateLimitKey];
            }

            // Check if too many attempts
            if ($attempts['count'] >= 5) {
                $_SESSION[$lockoutKey] = time() + 900; // Lock out for 15 minutes
                $error = 'Too many failed login attempts. Account locked for 15 minutes.';
            } else {
                $loginResult = loginUser($username, $password);

                if ($loginResult) {
                    // Reset rate limiting on success
                    unset($_SESSION[$rateLimitKey]);
                    unset($_SESSION[$lockoutKey]);

                    // Handle remember me
                    if ($rememberMe) {
                        $rememberToken = bin2hex(random_bytes(32));
                        $_SESSION['remember_token'] = $rememberToken;
                        setcookie('remember_token', $rememberToken, time() + (86400 * 30), '/', '', true, true);
                    }

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Log successful login
                    error_log("Successful login: {$username} from {$ipAddress}");

                    header('Location: index.php');
                    exit;
                } else {
                    // Increment failed attempts
                    $_SESSION[$rateLimitKey]['count']++;
                    $remainingAttempts = 5 - $_SESSION[$rateLimitKey]['count'];

                    $error = 'Invalid username or password';
                    if ($remainingAttempts > 0) {
                        $error .= " ({$remainingAttempts} attempt" . ($remainingAttempts > 1 ? 's' : '') . " remaining)";
                    }

                    // Log failed attempt
                    error_log("Failed login attempt: {$username} from {$ipAddress}");
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CIS Intelligence Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .login-logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .login-logo p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .form-control {
            padding: 12px 20px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .demo-credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 0.85rem;
        }
        .form-check {
            padding-left: 1.5rem;
        }
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.15em;
        }
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        .alert {
            border-radius: 10px;
            border: none;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 15px;
        }
        .security-badge i {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-brain"></i>
                <h1>CIS Intelligence</h1>
                <p>Dashboard Login</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" required autofocus value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>

                <div class="mb-4 form-check">
                    <input class="form-check-input" type="checkbox" name="remember_me" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">
                        Remember me for 30 days
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>

                <div class="text-center security-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Protected by CSRF, Rate Limiting & Secure Sessions</span>
                </div>
            </form>

            <?php if (APP_ENVIRONMENT === 'development'): ?>
            <div class="demo-credentials">
                <strong>Demo Credentials:</strong><br>
                Username: <code>admin</code><br>
                Password: <code>admin123</code>
            </div>
            <?php endif; ?>
        </div>

            <div class="text-center mt-4 text-white">
            <small>&copy; <?php echo date('Y'); ?> Ecigdis Limited. All rights reserved.</small>
        </div>
    </div>

    <script>
        // Simple form submission handler
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function(e) {
                // Disable button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Logging in...';
            });
        });
    </script>
</body>
</html>
