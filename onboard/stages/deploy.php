<?php
/**
 * Automated Deployment Engine
 *
 * This page handles:
 * 1. Creating GitHub repository
 * 2. Generating configuration files
 * 3. Deploying documentation
 * 4. Setting up project structure
 * 5. Creating download package
 */

// Must have session data
if (!isset($_SESSION['project'], $_SESSION['config'], $_SESSION['github_user'])) {
    header('Location: index.php?stage=welcome');
    exit;
}

$project = $_SESSION['project'];
$config = $_SESSION['config'];
$github_user = $_SESSION['github_user'];
$onboard_id = $_SESSION['onboard_id'];

// Check if deployment already started
$status_file = ONBOARD_DATA_PATH . "/{$onboard_id}_status.json";
$deployment_status = file_exists($status_file) ? json_decode(file_get_contents($status_file), true) : [];

// Handle AJAX status check
if (isset($_GET['check_status'])) {
    header('Content-Type: application/json');
    echo json_encode($deployment_status);
    exit;
}

// Handle deployment trigger
if (isset($_POST['start_deployment']) && empty($deployment_status)) {
    // Save session data for background script
    $session_file = ONBOARD_DATA_PATH . "/{$onboard_id}_session.json";
    file_put_contents($session_file, json_encode([
        'project' => $project,
        'config' => $config,
        'github_user' => $github_user,
        'github_token' => $_SESSION['github_token']
    ]));

    // Initialize status
    $deployment_status = [
        'started' => time(),
        'steps' => [
            'github_repo' => ['status' => 'pending', 'progress' => 0],
            'generate_config' => ['status' => 'pending', 'progress' => 0],
            'create_package' => ['status' => 'pending', 'progress' => 0],
            'deploy_docs' => ['status' => 'pending', 'progress' => 0],
            'finalize' => ['status' => 'pending', 'progress' => 0]
        ],
        'overall_progress' => 0
    ];
    file_put_contents($status_file, json_encode($deployment_status));

    // Trigger background deployment using cURL to self
    $deploy_url = 'https://gpt.ecigdis.co.nz/onboard/deploy_worker.php?id=' . urlencode($onboard_id);

    // Non-blocking cURL request
    $ch = curl_init($deploy_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT_MS => 100, // Timeout quickly to not block
        CURLOPT_NOSIGNAL => 1
    ]);
    @curl_exec($ch); // Suppress errors since we're timing out intentionally
    curl_close($ch);
}
?>

<div class="stage-indicator">
    <div class="stage-item completed">
        <div class="stage-circle">✓</div>
        <div class="stage-label">Welcome</div>
    </div>
    <div class="stage-item completed">
        <div class="stage-circle">✓</div>
        <div class="stage-label">GitHub</div>
    </div>
    <div class="stage-item completed">
        <div class="stage-circle">✓</div>
        <div class="stage-label">Project</div>
    </div>
    <div class="stage-item completed">
        <div class="stage-circle">✓</div>
        <div class="stage-label">Configure</div>
    </div>
    <div class="stage-item active">
        <div class="stage-circle">5</div>
        <div class="stage-label">Deploy</div>
    </div>
</div>

<h2>Automated Deployment</h2>

<?php if (empty($deployment_status)): ?>
    <div class="alert alert-info">
        <strong>Ready to deploy!</strong> Click below to start the automated setup process.
    </div>

    <div class="file-tree">
<strong>What will be created:</strong>

<?php echo htmlspecialchars($project['name']); ?>/
├── .github/
│   ├── copilot-instructions.md
│   └── workflows/ (if selected)
├── .vscode/
│   ├── settings.json
│   └── BOTS_GUIDE.md
├── ai-agent/
│   ├── src/Tools/Frontend/
│   └── config/
├── docs/
│   ├── MASTER_SYSTEM_GUIDE.md
│   ├── FRONTEND_INTEGRATION_SETUP.md
│   └── [12+ more guides]
├── scripts/
│   └── deployment scripts
├── README.md
└── package.json
    </div>

    <form method="POST" id="deployForm">
        <input type="hidden" name="start_deployment" value="1">
        <div class="button-group">
            <a href="?stage=configure" class="btn" style="background: #e0e0e0; color: #666;">← Back</a>
            <button type="submit" class="btn btn-primary">🚀 Start Automated Deployment</button>
        </div>
    </form>

<?php else: ?>
    <div class="progress-bar">
        <div class="progress-fill" id="overallProgress" style="width: <?php echo $deployment_status['overall_progress']; ?>%">
            <span id="progressText"><?php echo $deployment_status['overall_progress']; ?>%</span>
        </div>
    </div>

    <div id="deploymentSteps">
        <?php
        $step_labels = [
            'github_repo' => '📦 Creating GitHub Repository',
            'generate_config' => '⚙️ Generating Configuration Files',
            'create_package' => '📦 Creating Download Package',
            'deploy_docs' => '📚 Deploying Documentation',
            'finalize' => '✅ Finalizing Setup'
        ];

        foreach ($deployment_status['steps'] as $step_key => $step):
            $icon = $step['status'] === 'completed' ? '✅' :
                   ($step['status'] === 'running' ? '⏳' : '⏸️');
        ?>
            <div class="alert <?php echo $step['status'] === 'completed' ? 'alert-success' : 'alert-info'; ?>"
                 id="step_<?php echo $step_key; ?>">
                <?php echo $icon; ?> <strong><?php echo $step_labels[$step_key]; ?></strong>
                <?php if (isset($step['message'])): ?>
                    <br><small><?php echo htmlspecialchars($step['message']); ?></small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($deployment_status['overall_progress'] >= 100): ?>
        <form method="GET" action="index.php">
            <input type="hidden" name="stage" value="complete">
            <div class="button-group">
                <button type="submit" class="btn btn-primary">View Results →</button>
            </div>
        </form>
    <?php else: ?>
        <p style="text-align: center; color: #666; margin-top: 20px;">
            <small>⏳ Please wait while we set everything up automatically...</small>
        </p>
    <?php endif; ?>

    <script>
        // Auto-refresh status every 2 seconds
        <?php if ($deployment_status['overall_progress'] < 100): ?>
        setInterval(function() {
            fetch('?check_status=1')
                .then(response => response.json())
                .then(data => {
                    // Update progress bar
                    document.getElementById('overallProgress').style.width = data.overall_progress + '%';
                    document.getElementById('progressText').textContent = data.overall_progress + '%';

                    // Update step statuses
                    Object.keys(data.steps).forEach(step_key => {
                        const step = data.steps[step_key];
                        const stepEl = document.getElementById('step_' + step_key);
                        if (stepEl && step.status === 'completed') {
                            stepEl.className = 'alert alert-success';
                            stepEl.querySelector('strong').innerHTML = '✅ ' + stepEl.querySelector('strong').textContent.replace(/^[⏳⏸️✅]\s*/, '');
                        }
                    });

                    // Reload if complete
                    if (data.overall_progress >= 100) {
                        location.reload();
                    }
                });
        }, 2000);
        <?php endif; ?>
    </script>
<?php endif; ?>
