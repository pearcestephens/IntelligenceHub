<?php
/**
 * AI Agent Onboarding Portal
 * Self-service setup for new users
 *
 * Features:
 * - GitHub OAuth integration
 * - Automated project creation
 * - VS Code settings generation
 * - MCP server configuration
 * - Documentation deployment
 * - Personalized setup
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration
define('GITHUB_CLIENT_ID', 'Ov23li1TEjksyfgzzxaw');
define('GITHUB_CLIENT_SECRET', '307ba7874f912cb33fb0621044c99ff2d3cc47a6');
define('MCP_SERVER_URL', 'https://gpt.ecigdis.co.nz/mcp/server_v3.php');
define('BASE_PATH', '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html');
define('ONBOARD_DATA_PATH', BASE_PATH . '/private_html/onboarding');

// Create data directory
if (!is_dir(ONBOARD_DATA_PATH)) {
    mkdir(ONBOARD_DATA_PATH, 0755, true);
}

// Handle AJAX status check BEFORE any other output
if (isset($_GET['check_status'])) {
    $onboard_id = $_SESSION['onboard_id'] ?? '';
    $status_file = ONBOARD_DATA_PATH . "/{$onboard_id}_status.json";

    header('Content-Type: application/json');

    if (file_exists($status_file)) {
        echo file_get_contents($status_file);
    } else {
        echo json_encode(['error' => 'Status file not found', 'overall_progress' => 0]);
    }
    exit;
}

// Handle different stages
$stage = $_GET['stage'] ?? 'welcome';
$session_id = $_SESSION['onboard_id'] ?? null;

// Process stage logic BEFORE any output
ob_start(); // Start output buffering
$stage_file = __DIR__ . "/stages/{$stage}.php";
if (file_exists($stage_file)) {
    include $stage_file;
    $stage_content = ob_get_clean();
} else {
    $stage_content = '<h2>Invalid stage</h2>';
    ob_end_clean();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligence Hub Onboarding | Ecigdis Limited - Enterprise AI Development Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #0891b2;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #0f172a;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(8, 145, 178, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .container {
            background: white;
            border-radius: 24px;
            box-shadow:
                0 20px 70px rgba(0,0,0,0.3),
                0 0 1px rgba(0,0,0,0.2);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .company-name {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .company-tagline {
            font-size: 14px;
            font-weight: 400;
            opacity: 0.9;
            margin-top: 5px;
        }

        .header h1 {
            font-size: 36px;
            font-weight: 800;
            margin: 20px 0 15px;
            position: relative;
            z-index: 1;
            letter-spacing: -1px;
        }

        .header p {
            font-size: 18px;
            opacity: 0.95;
            font-weight: 400;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .stats-bar {
            display: flex;
            justify-content: space-around;
            padding: 30px 20px;
            background: var(--light-gray);
            border-bottom: 1px solid var(--border);
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content {
            padding: 50px 40px;
        }

        .btn {
            display: inline-block;
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }

        .feature-card {
            padding: 25px;
            background: var(--light-gray);
            border-radius: 16px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .feature-icon {
            font-size: 36px;
            margin-bottom: 15px;
            display: block;
        }

        .feature-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .feature-desc {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.6;
        }

        .footer {
            background: var(--dark);
            color: white;
            padding: 30px 40px;
            text-align: center;
            font-size: 14px;
        }

        .footer-links {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 30px;
        }

        .footer-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-link:hover {
            color: white;
        }

        /* Progress bar */
        .progress-container {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .progress-bar {
            height: 8px;
            background: var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--gray);
            font-weight: 500;
        }

        .progress-step.active {
            color: var(--primary);
            font-weight: 700;
        }

        .stage-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .stage-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }

        .stage-item {
            text-align: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .stage-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .stage-item.active .stage-circle {
            background: #667eea;
            color: white;
            transform: scale(1.2);
        }

        .stage-item.completed .stage-circle {
            background: #4caf50;
            color: white;
        }

        .stage-label {
            font-size: 0.9em;
            color: #666;
        }

        .stage-item.active .stage-label {
            color: #667eea;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.9em;
        }

        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-github {
            background: #24292e;
            color: white;
            width: 100%;
            margin-bottom: 20px;
        }

        .btn-github:hover {
            background: #1a1e22;
        }

        .feature-list {
            list-style: none;
            margin: 30px 0;
        }

        .feature-list li {
            padding: 15px;
            margin-bottom: 10px;
            background: #f5f5f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .feature-list li::before {
            content: '✓';
            background: #4caf50;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4caf50, #8bc34a);
            transition: width 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            color: #1565c0;
        }

        .alert-success {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }

        .download-card {
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .download-card h3 {
            color: #667eea;
            margin-bottom: 15px;
        }

        .file-tree {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            font-family: monospace;
            margin: 20px 0;
        }

        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .button-group .btn {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">IH</div>
                <div>
                    <div class="company-name">Ecigdis Limited</div>
                    <div class="company-tagline">The Vape Shed | Intelligence Hub Division</div>
                </div>
            </div>
            <h1>Enterprise AI Development Platform</h1>
            <p>Complete GitHub Copilot + MCP setup with documentation, tools, and deployment scripts</p>
        </div>

        <div class="stats-bar">
            <div class="stat">
                <span class="stat-number">GPT-4o</span>
                <span class="stat-label">AI Engine</span>
            </div>
            <div class="stat">
                <span class="stat-number">16+</span>
                <span class="stat-label">MCP Tools</span>
            </div>
            <div class="stat">
                <span class="stat-number">36+</span>
                <span class="stat-label">KB Articles</span>
            </div>
            <div class="stat">
                <span class="stat-number">CIS</span>
                <span class="stat-label">Deploy Ready</span>
            </div>
        </div>

        <div class="content">
            <?php echo $stage_content; ?>
        </div>

        <div class="footer">
            <strong>Ecigdis Limited</strong> | Intelligence Hub Onboarding System v2.0<br>
            <div class="footer-links">
                <a href="https://staff.vapeshed.co.nz" class="footer-link">CIS Production</a>
                <a href="https://gpt.ecigdis.co.nz" class="footer-link">Intelligence Hub</a>
                <a href="https://www.vapeshed.co.nz" class="footer-link">The Vape Shed</a>
            </div>
            <div style="margin-top: 15px; font-size: 12px; opacity: 0.6;">
                © 2025 Ecigdis Limited. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        // Auto-submit forms with loading indicators
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const btn = form.querySelector('.btn-primary');
                if (btn) {
                    btn.innerHTML = '<div class="loading-spinner"></div>';
                    btn.disabled = true;
                }
            });
        });
    </script>
</body>
</html>
