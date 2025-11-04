<?php
/**
 * Background Deployment Automation
 * Runs in background to create GitHub repo and deploy everything
 *
 * Usage: php deploy_automation.php <onboard_id>
 */

// Get onboard ID from command line
$onboard_id = $argv[1] ?? die("Usage: php deploy_automation.php <onboard_id>\n");

// Load session data
$session_file = "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/private_html/onboarding/{$onboard_id}_session.json";
if (!file_exists($session_file)) {
    die("Session file not found\n");
}

$session_data = json_decode(file_get_contents($session_file), true);
$project = $session_data['project'];
$config = $session_data['config'];
$github_user = $session_data['github_user'];
$github_token = $session_data['github_token'];

// Helper function to update deployment status
function updateStatus($onboard_id, $step, $status, $progress, $message = '') {
    $file = "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/private_html/onboarding/{$onboard_id}_status.json";
    $data = json_decode(file_get_contents($file), true);
    $data['steps'][$step] = compact('status', 'progress', 'message');
    $data['overall_progress'] = array_sum(array_column($data['steps'], 'progress')) / count($data['steps']);
    file_put_contents($file, json_encode($data));
    echo "[" . date('H:i:s') . "] $step: $message\n";
}

// Helper function for GitHub API calls
function githubAPI($endpoint, $method = 'GET', $data = null, $token) {
    $ch = curl_init("https://api.github.com{$endpoint}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'User-Agent: AI-Agent-Onboarding',
            'Accept: application/vnd.github.v3+json'
        ]
    ]);

    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $http_code, 'data' => json_decode($response, true)];
}

echo "Starting deployment for {$project['name']}...\n";

// STEP 1: Create GitHub Repository
updateStatus($onboard_id, 'github_repo', 'running', 10, 'Creating repository...');

$repo_data = [
    'name' => $project['name'],
    'description' => $project['description'],
    'private' => $project['visibility'] === 'private',
    'auto_init' => true,
    'gitignore_template' => 'Node'
];

$result = githubAPI('/user/repos', 'POST', $repo_data, $github_token);

if ($result['code'] !== 201) {
    updateStatus($onboard_id, 'github_repo', 'failed', 0, 'Failed to create repo: ' . ($result['data']['message'] ?? 'Unknown error'));
    die("Failed to create GitHub repo\n");
}

$repo_url = $result['data']['html_url'];
updateStatus($onboard_id, 'github_repo', 'completed', 100, 'Repository created: ' . $repo_url);

// STEP 2: Generate Configuration Files
updateStatus($onboard_id, 'generate_config', 'running', 20, 'Generating configuration files...');

$temp_dir = "/tmp/onboard_{$onboard_id}";
mkdir($temp_dir, 0755, true);

// Generate settings.json
$settings = [
    'github.copilot.enable' => ['*' => true],
    'github.copilot.advanced' => [
        'debug.overrideEngine' => 'gpt-4',
        'mcp.enabled' => true,
        'mcp.servers' => [
            'intelligence-hub' => [
                'url' => $config['mcp_server'],
                'transport' => 'http'
            ]
        ]
    ]
];

mkdir("$temp_dir/.vscode", 0755, true);
file_put_contents("$temp_dir/.vscode/settings.json", json_encode($settings, JSON_PRETTY_PRINT));

// Generate copilot-instructions.md
mkdir("$temp_dir/.github", 0755, true);
$instructions = "# GitHub Copilot Instructions\n\n";
$instructions .= "Project: {$project['name']}\n";
$instructions .= "Owner: {$github_user['login']}\n\n";
$instructions .= "Use MCP server at: {$config['mcp_server']}\n";
file_put_contents("$temp_dir/.github/copilot-instructions.md", $instructions);

// Generate README.md
$readme = "# {$project['name']}\n\n";
$readme .= "{$project['description']}\n\n";
$readme .= "## Quick Start\n\n";
$readme .= "```bash\ngit clone {$repo_url}.git\ncd {$project['name']}\nnpm install\ncode .\n```\n";
file_put_contents("$temp_dir/README.md", $readme);

updateStatus($onboard_id, 'generate_config', 'completed', 100, 'Configuration files generated');

// STEP 3: Copy Documentation
updateStatus($onboard_id, 'deploy_docs', 'running', 40, 'Copying documentation...');

$doc_source = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/downloads';
$docs_to_copy = [
    'MASTER_SYSTEM_GUIDE.md',
    'FRONTEND_INTEGRATION_SETUP.md',
    'FRONTEND_TOOLS_BREAKDOWN.md',
    'COMPLETE_SETUP_STATUS.md'
];

mkdir("$temp_dir/docs", 0755, true);
foreach ($docs_to_copy as $doc) {
    $source = "$doc_source/../$doc";
    if (file_exists($source)) {
        copy($source, "$temp_dir/docs/$doc");
    }
}

updateStatus($onboard_id, 'deploy_docs', 'completed', 100, 'Documentation copied');

// STEP 4: Create Package
updateStatus($onboard_id, 'create_package', 'running', 60, 'Creating download package...');

$package_path = "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/onboard/packages/{$onboard_id}.tar.gz";
$packages_dir = dirname($package_path);
if (!is_dir($packages_dir)) {
    mkdir($packages_dir, 0755, true);
}

exec("cd $temp_dir && tar -czf $package_path .", $output, $return);

if ($return === 0) {
    updateStatus($onboard_id, 'create_package', 'completed', 100, 'Package created');
} else {
    updateStatus($onboard_id, 'create_package', 'failed', 0, 'Failed to create package');
}

// STEP 5: Upload to GitHub
updateStatus($onboard_id, 'finalize', 'running', 80, 'Uploading files to GitHub...');

// Get default branch SHA
$result = githubAPI("/repos/{$github_user['login']}/{$project['name']}/git/refs/heads/main", 'GET', null, $github_token);
$branch_sha = $result['data']['object']['sha'];

// Create blob for each file and build tree
$tree_items = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp_dir));

foreach ($files as $file) {
    if ($file->isFile()) {
        $relative_path = str_replace($temp_dir . '/', '', $file->getPathname());
        $content = base64_encode(file_get_contents($file->getPathname()));

        $blob_result = githubAPI("/repos/{$github_user['login']}/{$project['name']}/git/blobs", 'POST', [
            'content' => $content,
            'encoding' => 'base64'
        ], $github_token);

        if ($blob_result['code'] === 201) {
            $tree_items[] = [
                'path' => $relative_path,
                'mode' => '100644',
                'type' => 'blob',
                'sha' => $blob_result['data']['sha']
            ];
        }
    }
}

// Create tree
$tree_result = githubAPI("/repos/{$github_user['login']}/{$project['name']}/git/trees", 'POST', [
    'base_tree' => $branch_sha,
    'tree' => $tree_items
], $github_token);

// Create commit
$commit_result = githubAPI("/repos/{$github_user['login']}/{$project['name']}/git/commits", 'POST', [
    'message' => 'Initial setup via AI Agent Onboarding',
    'tree' => $tree_result['data']['sha'],
    'parents' => [$branch_sha]
], $github_token);

// Update reference
githubAPI("/repos/{$github_user['login']}/{$project['name']}/git/refs/heads/main", 'PATCH', [
    'sha' => $commit_result['data']['sha']
], $github_token);

updateStatus($onboard_id, 'finalize', 'completed', 100, 'Deployment complete!');

// Cleanup
exec("rm -rf $temp_dir");

echo "Deployment completed successfully!\n";
echo "Repository: $repo_url\n";
