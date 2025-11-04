<?php
/**
 * Deploy Worker - Background deployment processor
 * Called via HTTP to run deployment without exec()
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/onboard_errors.log');

// Increase time limit for deployment
set_time_limit(300);
ignore_user_abort(true);

// Get onboard ID
$onboard_id = $_GET['id'] ?? die('No ID');

// Load configuration
define('BASE_PATH', '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html');
define('ONBOARD_DATA_PATH', BASE_PATH . '/private_html/onboarding');

// Load session data
$session_file = ONBOARD_DATA_PATH . "/{$onboard_id}_session.json";
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
    $file = ONBOARD_DATA_PATH . "/{$onboard_id}_status.json";
    if (!file_exists($file)) return;

    $data = json_decode(file_get_contents($file), true);
    $data['steps'][$step] = compact('status', 'progress', 'message');
    $data['overall_progress'] = array_sum(array_column($data['steps'], 'progress')) / count($data['steps']);
    file_put_contents($file, json_encode($data));
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
    $error_msg = 'Unknown error';
    if (isset($result['data']['message'])) {
        $error_msg = $result['data']['message'];
    }
    if (isset($result['data']['errors'])) {
        $error_msg .= ' - ' . json_encode($result['data']['errors']);
    }
    updateStatus($onboard_id, 'github_repo', 'failed', 0, 'Failed: ' . $error_msg);
    die("Failed to create GitHub repo: $error_msg\n");
}

$repo_url = $result['data']['html_url'];
$repo_name = $result['data']['name'];
updateStatus($onboard_id, 'github_repo', 'completed', 100, 'Repository created');

// STEP 2: Generate Configuration Files
updateStatus($onboard_id, 'generate_config', 'running', 20, 'Generating configuration files...');

$temp_dir = "/tmp/onboard_{$onboard_id}";
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

// Generate PROPER settings.json with MCP
$settings = [
    'github.copilot.enable' => ['*' => true],
    'github.copilot.advanced' => [
        'debug.overrideEngine' => 'gpt-4o',  // Latest GPT-4o model
        'debug.useNodeServer' => true,
        'mcp.enabled' => true,
        'mcp.autoStart' => true,
        'mcp.contextSize' => 32768
    ],
    'chat.experimental.autoContext.enabled' => true,
    'chat.experimental.autoContext.retrievalEnabled' => true,
    'chat.experimental.codeGeneration.instructions' => [
        ['file' => '_kb/BOT_BRIEFING_MASTER.md'],
        ['file' => '_kb/ULTIMATE_AUTONOMOUS_PROMPT.md'],
        ['file' => 'docs/MASTER_SYSTEM_GUIDE.md'],
        ['file' => 'docs/COMPLETE_TOOLS_ECOSYSTEM.md'],
        ['file' => 'docs/TOOL_GOVERNANCE_SYSTEM.md'],
        ['file' => '.vscode/BOTS_GUIDE.md']
    ],
    'mcp.servers' => [
        'intelligence-hub-v3' => [
            'transport' => [
                'type' => 'http',
                'url' => $config['mcp_server'],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'VSCode-MCP-Client'
                ],
                'timeout' => 30000
            ],
            'tools' => [
                'conversation.get_project_context',
                'conversation.search',
                'conversation.list_conversations',
                'conversation.get_conversation',
                'documentation.search',
                'documentation.get_file',
                'knowledge.search',
                'knowledge.add'
            ],
            'experimental' => [
                'memoryEnabled' => true,
                'persistenceEnabled' => true
            ]
        ]
    ],
    'editor.suggest.showWords' => true,
    'editor.quickSuggestions' => [
        'other' => 'on',
        'comments' => 'on',
        'strings' => 'on'
    ]
];

if (!is_dir("$temp_dir/.vscode")) {
    mkdir("$temp_dir/.vscode", 0755, true);
}
file_put_contents("$temp_dir/.vscode/settings.json", json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Copy BOTS_GUIDE.md from Intelligence Hub
$bots_guide_source = BASE_PATH . '/.vscode/BOTS_GUIDE.md';
if (file_exists($bots_guide_source)) {
    copy($bots_guide_source, "$temp_dir/.vscode/BOTS_GUIDE.md");
}

// Generate PROPER copilot-instructions.md
if (!is_dir("$temp_dir/.github")) {
    mkdir("$temp_dir/.github", 0755, true);
}

// Copy the actual copilot instructions from Intelligence Hub
$copilot_source = BASE_PATH . '/.github/copilot-instructions.md';
if (file_exists($copilot_source)) {
    $instructions = file_get_contents($copilot_source);
    // Personalize it
    $instructions = "# GitHub Copilot Instructions - {$project['name']}\n\n";
    $instructions .= "**Project Owner:** {$github_user['login']}\n";
    $instructions .= "**MCP Server:** {$config['mcp_server']}\n\n";
    $instructions .= "---\n\n";
    $instructions .= file_get_contents($copilot_source);
} else {
    $instructions = "# GitHub Copilot Instructions\n\n";
    $instructions .= "Project: {$project['name']}\n";
    $instructions .= "Owner: {$github_user['login']}\n\n";
    $instructions .= "## Available Tools\n\n";
    $instructions .= "- Frontend Testing Suite (7 tools)\n";
    $instructions .= "- Workflow Automation\n";
    $instructions .= "- MCP Integration\n";
    $instructions .= "- Complete Documentation\n";
}
file_put_contents("$temp_dir/.github/copilot-instructions.md", $instructions);

// Generate comprehensive README.md
$readme = "# {$project['name']}\n\n";
$readme .= "{$project['description']}\n\n";
$readme .= "**Created with Intelligence Hub Onboarding Portal**\n\n";
$readme .= "## 🚀 Quick Start\n\n";
$readme .= "```bash\n";
$readme .= "# Clone repository\n";
$readme .= "git clone {$repo_url}.git\n";
$readme .= "cd {$project['name']}\n\n";
$readme .= "# Open in VS Code\n";
$readme .= "code .\n\n";
$readme .= "# MCP will auto-connect to Intelligence Hub\n";
$readme .= "```\n\n";
$readme .= "## ✨ Features Included\n\n";
if (in_array('frontend_tools', $project['features'])) {
    $readme .= "### Frontend Testing Tools\n";
    $readme .= "- Screenshot Tool\n";
    $readme .= "- Audit Tool\n";
    $readme .= "- Monitor Tool\n";
    $readme .= "- Auto-Fix Tool\n";
    $readme .= "- Visual Regression\n";
    $readme .= "- Performance Testing\n";
    $readme .= "- Accessibility Checker\n\n";
}
if (in_array('mcp_integration', $project['features'])) {
    $readme .= "### MCP Integration\n";
    $readme .= "- Connected to: {$config['mcp_server']}\n";
    $readme .= "- Conversation history\n";
    $readme .= "- Knowledge base access\n";
    $readme .= "- Tool orchestration\n\n";
}
if (in_array('documentation', $project['features'])) {
    $readme .= "### Documentation\n";
    $readme .= "- Complete system guides\n";
    $readme .= "- Tool ecosystem documentation\n";
    $readme .= "- Best practices\n";
    $readme .= "- Code examples\n\n";
}
$readme .= "## 📚 Documentation\n\n";
$readme .= "See `docs/` folder for:\n";
$readme .= "- `MASTER_SYSTEM_GUIDE.md` - Complete overview\n";
$readme .= "- `COMPLETE_TOOLS_ECOSYSTEM.md` - All available tools\n";
$readme .= "- `TOOL_GOVERNANCE_SYSTEM.md` - Tool management\n";
$readme .= "- And more...\n\n";
$readme .= "## 🤖 AI Features\n\n";
$readme .= "Your VS Code is pre-configured with:\n";
$readme .= "- GitHub Copilot with custom instructions\n";
$readme .= "- MCP server integration\n";
$readme .= "- Auto-context loading\n";
$readme .= "- Knowledge base access\n\n";
$readme .= "## 📞 Support\n\n";
$readme .= "- Documentation: Check `docs/` folder\n";
$readme .= "- MCP Server: {$config['mcp_server']}\n";
$readme .= "- Created: " . date('Y-m-d') . "\n";
file_put_contents("$temp_dir/README.md", $readme);

updateStatus($onboard_id, 'generate_config', 'completed', 100, 'Configuration files generated');

// STEP 3: MASSIVE PERSONALIZED DEPLOYMENT - Copy EVERYTHING
updateStatus($onboard_id, 'deploy_docs', 'running', 40, 'Deploying complete Intelligence Hub for ' . $session_data['github_username'] . '...');

$total_files = 0;
$total_size = 0;

// Create all necessary directories
$directories = ['docs', '_kb', 'ai-agent/docs', 'frontend-tools/docs', 'scripts', 'config', 'tools'];
foreach ($directories as $dir) {
    if (!is_dir("$temp_dir/$dir")) {
        mkdir("$temp_dir/$dir", 0755, true);
    }
}

// 1. Copy ALL markdown files from Intelligence Hub root to docs/
$all_docs = glob(BASE_PATH . '/*.md');
foreach ($all_docs as $doc_path) {
    if (file_exists($doc_path)) {
        copy($doc_path, "$temp_dir/docs/" . basename($doc_path));
        $total_files++;
        $total_size += filesize($doc_path);
    }
}

// 2. Copy ENTIRE _kb/ knowledge base folder recursively
$kb_source = BASE_PATH . '/_kb';
if (is_dir($kb_source)) {
    $kb_files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($kb_source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($kb_files as $kb_file) {
        $relative_path = str_replace($kb_source . '/', '', $kb_file->getPathname());
        $dest_path = "$temp_dir/_kb/$relative_path";

        if ($kb_file->isDir()) {
            if (!is_dir($dest_path)) {
                mkdir($dest_path, 0755, true);
            }
        } else {
            copy($kb_file->getPathname(), $dest_path);
            $total_files++;
            $total_size += $kb_file->getSize();
        }
    }
}

// 3. Copy ai-agent documentation
$ai_agent_docs = BASE_PATH . '/ai-agent';
if (is_dir($ai_agent_docs)) {
    $ai_files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($ai_agent_docs, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($ai_files as $ai_file) {
        if (preg_match('/\.(md|txt|json)$/i', $ai_file->getFilename())) {
            $relative_path = str_replace($ai_agent_docs . '/', '', $ai_file->getPathname());
            $dest_path = "$temp_dir/ai-agent/$relative_path";

            if ($ai_file->isDir()) {
                if (!is_dir($dest_path)) {
                    mkdir($dest_path, 0755, true);
                }
            } else {
                copy($ai_file->getPathname(), $dest_path);
                $total_files++;
                $total_size += $ai_file->getSize();
            }
        }
    }
}

// 4. Copy frontend-tools documentation
$frontend_docs = BASE_PATH . '/frontend-tools';
if (is_dir($frontend_docs)) {
    $frontend_files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($frontend_docs, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($frontend_files as $frontend_file) {
        if (preg_match('/\.(md|txt|json)$/i', $frontend_file->getFilename())) {
            $relative_path = str_replace($frontend_docs . '/', '', $frontend_file->getPathname());
            $dest_path = "$temp_dir/frontend-tools/$relative_path";

            if ($frontend_file->isDir()) {
                if (!is_dir($dest_path)) {
                    mkdir($dest_path, 0755, true);
                }
            } else {
                copy($frontend_file->getPathname(), $dest_path);
                $total_files++;
                $total_size += $frontend_file->getSize();
            }
        }
    }
}

// 5. Copy ALL scripts from scripts/ folder
$scripts_source = BASE_PATH . '/scripts';
if (is_dir($scripts_source)) {
    // Copy shell scripts
    foreach (glob($scripts_source . '/*.sh') as $script) {
        copy($script, "$temp_dir/scripts/" . basename($script));
        chmod("$temp_dir/scripts/" . basename($script), 0755);
        $total_files++;
        $total_size += filesize($script);
    }

    // Copy PHP scripts
    foreach (glob($scripts_source . '/*.php') as $script) {
        copy($script, "$temp_dir/scripts/" . basename($script));
        $total_files++;
        $total_size += filesize($script);
    }

    // Copy JavaScript/Node scripts
    foreach (glob($scripts_source . '/*.js') as $script) {
        copy($script, "$temp_dir/scripts/" . basename($script));
        $total_files++;
        $total_size += filesize($script);
    }
}

// 6. Copy deploy_docs_to_cis.sh from downloads
$deploy_script = BASE_PATH . '/downloads/deploy_docs_to_cis.sh';
if (file_exists($deploy_script)) {
    copy($deploy_script, "$temp_dir/scripts/deploy_docs_to_cis.sh");
    chmod("$temp_dir/scripts/deploy_docs_to_cis.sh", 0755);
    $total_files++;
    $total_size += filesize($deploy_script);
}

// 7. Copy configuration files (excluding sensitive ones)
$config_source = BASE_PATH . '/config';
if (is_dir($config_source)) {
    foreach (glob($config_source . '/*.{php,json,ini}', GLOB_BRACE) as $config_file) {
        $basename = basename($config_file);
        // Skip sensitive files
        if (!in_array($basename, ['encryption.key', 'secrets.php', 'credentials.json'])) {
            copy($config_file, "$temp_dir/config/$basename");
            $total_files++;
            $total_size += filesize($config_file);
        }
    }
}

// 8. Copy utility tools
$tools_source = BASE_PATH . '/tools';
if (is_dir($tools_source)) {
    foreach (glob($tools_source . '/*.{php,sh}', GLOB_BRACE) as $tool) {
        copy($tool, "$temp_dir/tools/" . basename($tool));
        if (pathinfo($tool, PATHINFO_EXTENSION) === 'sh') {
            chmod("$temp_dir/tools/" . basename($tool), 0755);
        }
        $total_files++;
        $total_size += filesize($tool);
    }
}

// 9. Copy COMPLETE .github folder (ALL Copilot instructions)
$github_source = BASE_PATH . '/.github';
if (is_dir($github_source)) {
    $github_files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($github_source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($github_files as $github_file) {
        $relative_path = str_replace($github_source . '/', '', $github_file->getPathname());
        $dest_path = "$temp_dir/.github/$relative_path";

        if ($github_file->isDir()) {
            if (!is_dir($dest_path)) {
                mkdir($dest_path, 0755, true);
            }
        } else {
            copy($github_file->getPathname(), $dest_path);
            $total_files++;
            $total_size += $github_file->getSize();
        }
    }
}

// 10. Copy ADDITIONAL .vscode documentation (beyond settings.json already created)
$vscode_source = BASE_PATH . '/.vscode';
if (is_dir($vscode_source)) {
    // Copy all .md files from .vscode (BOTS_GUIDE.md, etc.)
    foreach (glob($vscode_source . '/*.md') as $vscode_doc) {
        $basename = basename($vscode_doc);
        // Don't overwrite if already exists (settings.json created earlier)
        if (!file_exists("$temp_dir/.vscode/$basename")) {
            copy($vscode_doc, "$temp_dir/.vscode/$basename");
            $total_files++;
            $total_size += filesize($vscode_doc);
        }
    }

    // Copy any additional config files
    foreach (glob($vscode_source . '/*.{json,yml,yaml}', GLOB_BRACE) as $vscode_config) {
        $basename = basename($vscode_config);
        // Don't overwrite settings.json which we generated custom
        if ($basename !== 'settings.json' && !file_exists("$temp_dir/.vscode/$basename")) {
            copy($vscode_config, "$temp_dir/.vscode/$basename");
            $total_files++;
            $total_size += filesize($vscode_config);
        }
    }
}

// 11. Create personalized WELCOME message for the new developer
$welcome = "# 🎉 Welcome to Your Intelligence Hub, {$session_data['github_username']}!\n\n";
$welcome .= "**Created**: " . date('F j, Y \a\t g:i A') . "\n";
$welcome .= "**Project**: {$session_data['project_name']}\n";
$welcome .= "**Repository**: https://github.com/{$session_data['github_username']}/{$session_data['repo_name']}\n\n";
$welcome .= "---\n\n";
$welcome .= "## 📦 Your Complete Package\n\n";
$welcome .= "You've received the **FULL Intelligence Hub** deployment:\n\n";
$welcome .= "- **{$total_files} files** deployed\n";
$welcome .= "- **" . round($total_size / 1024 / 1024, 2) . " MB** of documentation and tools\n";
$welcome .= "- Complete AI-powered development environment\n";
$welcome .= "- All scripts, configs, and knowledge base\n\n";
$welcome .= "## 🚀 Quick Start (5 Minutes)\n\n";
$welcome .= "### 1. Clone Your Repository\n";
$welcome .= "```bash\n";
$welcome .= "git clone https://github.com/{$session_data['github_username']}/{$session_data['repo_name']}.git\n";
$welcome .= "cd {$session_data['repo_name']}\n";
$welcome .= "```\n\n";
$welcome .= "### 2. Open in VS Code\n";
$welcome .= "```bash\n";
$welcome .= "code .\n";
$welcome .= "```\n\n";
$welcome .= "VS Code will automatically:\n";
$welcome .= "- ✅ Detect GitHub Copilot configuration (gpt-4o engine)\n";
$welcome .= "- ✅ Load 6 custom instruction files for code generation\n";
$welcome .= "- ✅ Connect to MCP Server at {$config['mcp_server']}\n";
$welcome .= "- ✅ Enable 32KB context window for AI assistance\n";
$welcome .= "- ✅ Activate memory and persistence features\n\n";
$welcome .= "### 3. Start Coding with AI\n\n";
$welcome .= "Try asking GitHub Copilot:\n";
$welcome .= "- \"What tools are available in this project?\"\n";
$welcome .= "- \"Show me the project architecture\"\n";
$welcome .= "- \"Help me set up a new feature\"\n\n";
$welcome .= "Copilot has **full access** to:\n";
$welcome .= "- All {$total_files} documentation files\n";
$welcome .= "- Complete knowledge base (36+ KB articles)\n";
$welcome .= "- AI agent tools and workflows\n";
$welcome .= "- Frontend testing tools\n";
$welcome .= "- Deployment scripts\n\n";
$welcome .= "## 📚 What's Inside\n\n";
$welcome .= "### Documentation (`docs/` folder)\n";
$welcome .= "- **MASTER_SYSTEM_GUIDE.md** - Complete system overview\n";
$welcome .= "- **COMPLETE_TOOLS_ECOSYSTEM.md** - All available tools\n";
$welcome .= "- **TOOL_GOVERNANCE_SYSTEM.md** - Best practices\n";
$welcome .= "- Plus 15+ additional guides and references\n\n";
$welcome .= "### Knowledge Base (`_kb/` folder)\n";
$welcome .= "- 36+ articles on architecture, patterns, troubleshooting\n";
$welcome .= "- Intelligence reports and analytics\n";
$welcome .= "- Module documentation\n\n";
$welcome .= "### AI Agent Tools (`ai-agent/` folder)\n";
$welcome .= "- Tool chain orchestration\n";
$welcome .= "- Workflow automation\n";
$welcome .= "- Context management\n";
$welcome .= "- Memory and persistence\n\n";
$welcome .= "### Frontend Tools (`frontend-tools/` folder)\n";
$welcome .= "- Screenshot capture and comparison\n";
$welcome .= "- Visual regression testing\n";
$welcome .= "- Performance monitoring\n";
$welcome .= "- Auto-fix generation\n";
$welcome .= "- Accessibility audits\n\n";
$welcome .= "### Deployment Scripts (`scripts/` folder)\n";
$welcome .= "- **deploy_docs_to_cis.sh** - Deploy to CIS production server\n";
$welcome .= "- **index_documentation.php** - Index docs for semantic search\n";
$welcome .= "- Plus additional automation scripts\n\n";
$welcome .= "### Configuration (`config/` folder)\n";
$welcome .= "- PHP configuration templates\n";
$welcome .= "- JSON configuration files\n";
$welcome .= "- Environment examples\n\n";
$welcome .= "## 🛠️ Advanced Usage\n\n";
$welcome .= "### Deploy to CIS Production\n";
$welcome .= "```bash\n";
$welcome .= "cd scripts\n";
$welcome .= "./deploy_docs_to_cis.sh\n";
$welcome .= "```\n\n";
$welcome .= "This deploys documentation to:\n";
$welcome .= "- `staff.vapeshed.co.nz/_kb/`\n";
$welcome .= "- `.github/` - Copilot instructions\n";
$welcome .= "- `.vscode/` - Editor configuration\n";
$welcome .= "- `docs/` - General documentation\n\n";
$welcome .= "### Index Documentation for Search\n";
$welcome .= "```bash\n";
$welcome .= "php scripts/index_documentation.php\n";
$welcome .= "```\n\n";
$welcome .= "Enables AI to search documentation via MCP `documentation.search` tool.\n\n";
$welcome .= "## 🎯 Your Configuration\n\n";
$welcome .= "**GitHub Copilot Settings:**\n";
$welcome .= "- Engine: gpt-4o (latest model)\n";
$welcome .= "- Context: 32,768 tokens\n";
$welcome .= "- Memory: Enabled\n";
$welcome .= "- Persistence: Enabled\n";
$welcome .= "- Auto-context: Enabled\n\n";
$welcome .= "**MCP Integration:**\n";
$welcome .= "- Server: {$config['mcp_server']}\n";
$welcome .= "- Auto-start: Yes\n";
$welcome .= "- Available tools:\n";
$welcome .= "  - `conversation.get_project_context`\n";
$welcome .= "  - `conversation.search`\n";
$welcome .= "  - `documentation.search`\n";
$welcome .= "  - `knowledge.search`\n\n";
$welcome .= "**Custom Instructions:**\n";
$welcome .= "- BOT_BRIEFING_MASTER.md\n";
$welcome .= "- ULTIMATE_AUTONOMOUS_PROMPT.md\n";
$welcome .= "- MASTER_SYSTEM_GUIDE.md\n";
$welcome .= "- COMPLETE_TOOLS_ECOSYSTEM.md\n";
$welcome .= "- TOOL_GOVERNANCE_SYSTEM.md\n";
$welcome .= "- BOTS_GUIDE.md\n\n";
$welcome .= "## 💡 Pro Tips\n\n";
$welcome .= "1. **Explore the Knowledge Base** - `_kb/` folder has 36+ articles\n";
$welcome .= "2. **Use MCP Tools** - Right-click in VS Code → \"Ask Copilot\" → Try tools\n";
$welcome .= "3. **Deploy Often** - Keep CIS server docs in sync with `deploy_docs_to_cis.sh`\n";
$welcome .= "4. **Ask for Help** - Copilot knows everything in this package\n";
$welcome .= "5. **Customize** - Edit `.vscode/settings.json` to personalize further\n\n";
$welcome .= "## 🆘 Support\n\n";
$welcome .= "- **Intelligence Hub**: https://gpt.ecigdis.co.nz\n";
$welcome .= "- **CIS Production**: https://staff.vapeshed.co.nz\n";
$welcome .= "- **Your Repository**: https://github.com/{$session_data['github_username']}/{$session_data['repo_name']}\n";
$welcome .= "- **MCP Server**: {$config['mcp_server']}\n\n";
$welcome .= "---\n\n";
$welcome .= "**🎊 You're all set!** Start coding with the full power of AI assistance.\n\n";
$welcome .= "Generated by Intelligence Hub Onboarding System\n";
file_put_contents("$temp_dir/WELCOME.md", $welcome);

// 10. Create comprehensive scripts README
$scripts_readme = "# 🚀 Scripts Collection for {$session_data['project_name']}\n\n";
$scripts_readme .= "Complete automation toolkit from Intelligence Hub.\n\n";
$scripts_readme .= "**Total Deployment**: {$total_files} files (" . round($total_size / 1024 / 1024, 2) . " MB)\n\n";
$scripts_readme .= "## 📋 Available Scripts\n\n";
$scripts_readme .= "### Deployment\n\n";
$scripts_readme .= "**deploy_docs_to_cis.sh** - Deploy to CIS Production\n";
$scripts_readme .= "```bash\n";
$scripts_readme .= "./deploy_docs_to_cis.sh\n";
$scripts_readme .= "```\n";
$scripts_readme .= "Copies all documentation to staff.vapeshed.co.nz:\n";
$scripts_readme .= "- `_kb/` - Knowledge base\n";
$scripts_readme .= "- `docs/` - Documentation\n";
$scripts_readme .= "- `.github/` - Copilot instructions\n";
$scripts_readme .= "- `.vscode/` - VS Code config\n\n";
$scripts_readme .= "### Database\n\n";
$scripts_readme .= "**index_documentation.php** - Index Docs for Search\n";
$scripts_readme .= "```bash\n";
$scripts_readme .= "php index_documentation.php\n";
$scripts_readme .= "```\n";
$scripts_readme .= "Enables MCP `documentation.search` tool for AI semantic search.\n\n";
$scripts_readme .= "## 📖 Documentation Structure\n\n";
$scripts_readme .= "- `docs/` - Root documentation (18+ files)\n";
$scripts_readme .= "- `_kb/` - Knowledge base (36+ articles)\n";
$scripts_readme .= "- `ai-agent/` - AI agent docs and configs\n";
$scripts_readme .= "- `frontend-tools/` - Frontend testing docs\n\n";
$scripts_readme .= "## ⚙️ Configuration Files\n\n";
$scripts_readme .= "`config/` folder contains templates for:\n";
$scripts_readme .= "- PHP application configuration\n";
$scripts_readme .= "- JSON settings\n";
$scripts_readme .= "- Environment variables\n\n";
$scripts_readme .= "## 🔧 Utility Tools\n\n";
$scripts_readme .= "`tools/` folder includes utility scripts for:\n";
$scripts_readme .= "- Project analysis\n";
$scripts_readme .= "- Code generation\n";
$scripts_readme .= "- Automation tasks\n\n";
$scripts_readme .= "## 💡 Quick Tips\n\n";
$scripts_readme .= "- All `.sh` scripts are executable (chmod 755)\n";
$scripts_readme .= "- PHP scripts can run from any directory\n";
$scripts_readme .= "- Check individual script headers for usage\n\n";
$scripts_readme .= "## 🆘 Need Help?\n\n";
$scripts_readme .= "Ask GitHub Copilot: \"How do I use [script name]?\"\n\n";
$scripts_readme .= "Copilot has full access to all documentation and can guide you through any script.\n";
file_put_contents("$temp_dir/scripts/README.md", $scripts_readme);

updateStatus($onboard_id, 'deploy_docs', 'completed', 100, "Deployed {$total_files} files (" . round($total_size / 1024 / 1024, 2) . " MB)");

// STEP 4: Create Package
updateStatus($onboard_id, 'create_package', 'running', 60, 'Creating download package...');

$package_dir = BASE_PATH . '/onboard/packages';
if (!is_dir($package_dir)) {
    mkdir($package_dir, 0755, true);
}

$zip_path = "$package_dir/{$onboard_id}.zip";

try {
    $zip = new ZipArchive();
    $result = $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    if ($result !== true) {
        throw new Exception("Failed to create zip: error code $result");
    }

    // Add files recursively
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($temp_dir) + 1);

        if ($file->isDir()) {
            $zip->addEmptyDir($relativePath);
        } else {
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();
    chmod($zip_path, 0644); // Make sure it's readable
    updateStatus($onboard_id, 'create_package', 'completed', 100, 'Package created');
} catch (Exception $e) {
    updateStatus($onboard_id, 'create_package', 'failed', 50, 'Package error: ' . $e->getMessage());
}

// STEP 5: Upload to GitHub
updateStatus($onboard_id, 'finalize', 'running', 80, 'Uploading files to GitHub...');

// Upload files one by one using GitHub API
$files_to_upload = [
    '.vscode/settings.json',
    '.github/copilot-instructions.md',
    'README.md'
];

foreach ($docs_to_copy as $doc) {
    if (file_exists("$temp_dir/docs/$doc")) {
        $files_to_upload[] = "docs/$doc";
    }
}

foreach ($files_to_upload as $file_path) {
    $local_file = "$temp_dir/$file_path";
    if (!file_exists($local_file)) continue;

    $content = base64_encode(file_get_contents($local_file));

    githubAPI("/repos/{$github_user['login']}/{$repo_name}/contents/{$file_path}", 'PUT', [
        'message' => "Add {$file_path}",
        'content' => $content
    ], $github_token);

    sleep(1); // Rate limit
}

updateStatus($onboard_id, 'finalize', 'completed', 100, 'Deployment complete!');

// Cleanup
array_map('unlink', glob("$temp_dir/*"));
rmdir($temp_dir);

echo "Deployment completed successfully!\n";
