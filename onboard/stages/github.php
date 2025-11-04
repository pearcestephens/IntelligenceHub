<?php
// Generate state for OAuth security
if (!isset($_SESSION['oauth_state'])) {
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
}

$github_auth_url = "https://github.com/login/oauth/authorize?" . http_build_query([
    'client_id' => GITHUB_CLIENT_ID,
    'redirect_uri' => 'https://gpt.ecigdis.co.nz/onboard/callback.php',
    'scope' => 'repo,user:email',
    'state' => $_SESSION['oauth_state']
]);

// Check if already authenticated
$github_user = $_SESSION['github_user'] ?? null;
?>

<div class="stage-indicator">
    <div class="stage-item completed">
        <div class="stage-circle">✓</div>
        <div class="stage-label">Welcome</div>
    </div>
    <div class="stage-item active">
        <div class="stage-circle">2</div>
        <div class="stage-label">GitHub</div>
    </div>
    <div class="stage-item">
        <div class="stage-circle">3</div>
        <div class="stage-label">Project</div>
    </div>
    <div class="stage-item">
        <div class="stage-circle">4</div>
        <div class="stage-label">Configure</div>
    </div>
    <div class="stage-item">
        <div class="stage-circle">5</div>
        <div class="stage-circle">Deploy</div>
    </div>
</div>

<h2>Connect Your GitHub Account</h2>

<?php if (!$github_user): ?>
    <div class="alert alert-info">
        We need access to your GitHub account to create your personal project repository and configure everything automatically.
    </div>

    <p style="margin: 20px 0;">
        <strong>We'll request permission to:</strong>
    </p>
    <ul style="padding-left: 20px; margin-bottom: 30px;">
        <li>Create a private repository for your project</li>
        <li>Add files and documentation</li>
        <li>Configure repository settings</li>
        <li>Access your email (for notifications)</li>
    </ul>

    <a href="<?php echo $github_auth_url; ?>" class="btn btn-github">
        <strong>📦 Connect with GitHub</strong>
    </a>

    <p style="text-align: center; color: #666; margin-top: 20px;">
        <small>This will open GitHub's authorization page. You can revoke access anytime from your GitHub settings.</small>
    </p>

    <form method="GET" action="index.php" style="margin-top: 30px;">
        <input type="hidden" name="stage" value="welcome">
        <button type="submit" class="btn" style="background: #e0e0e0; color: #666;">← Back</button>
    </form>

<?php else: ?>
    <div class="alert alert-success">
        ✓ <strong>Connected as:</strong> <?php echo htmlspecialchars($github_user['login']); ?>
        (<?php echo htmlspecialchars($github_user['email'] ?? 'No public email'); ?>)
    </div>

    <form method="GET" action="index.php">
        <input type="hidden" name="stage" value="project">
        <div class="button-group">
            <a href="?stage=welcome" class="btn" style="background: #e0e0e0; color: #666;">← Back</a>
            <button type="submit" class="btn btn-primary">Continue →</button>
        </div>
    </form>
<?php endif; ?>
