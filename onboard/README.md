# 🚀 AI Agent Onboarding Portal

**Self-service setup wizard for new users to get started with AI development tools in 5 minutes.**

## ✨ Features

- **Automated GitHub Repository Creation** - Creates personal project repo
- **VS Code Configuration** - Generates settings.json with MCP integration
- **Complete Documentation** - Deploys all guides and references
- **Frontend Tools** - Includes 7+ testing and automation tools
- **Personalized Setup** - Custom configuration for each user
- **Download Package** - Generates backup ZIP of everything

## 🎯 What It Does

When someone visits the onboarding link:

1. **Welcome** - Explains what they'll get
2. **GitHub OAuth** - Connects their GitHub account
3. **Project Details** - They choose project name and features
4. **Configuration** - Set MCP server, editor preferences
5. **Automated Deployment** - Creates everything automatically:
   - GitHub repository
   - Configuration files
   - Documentation
   - Tools and scripts
   - Download package
6. **Complete** - Provides clone instructions and links

## 📋 Setup Requirements

### 1. Create GitHub OAuth App

1. Go to: https://github.com/settings/developers
2. Click "New OAuth App"
3. Fill in:
   - **Application name:** AI Agent Onboarding
   - **Homepage URL:** https://gpt.ecigdis.co.nz
   - **Authorization callback URL:** https://gpt.ecigdis.co.nz/onboard/callback.php
4. Save and copy:
   - Client ID
   - Client Secret

### 2. Update Configuration

Edit `/onboard/index.php` lines 15-16:

```php
define('GITHUB_CLIENT_ID', 'your_client_id_here');
define('GITHUB_CLIENT_SECRET', 'your_secret_here');
```

### 3. Set Permissions

```bash
chmod 755 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/onboard
chmod 755 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/private_html/onboarding
```

### 4. Test It

Visit: **https://gpt.ecigdis.co.nz/onboard/**

## 📁 File Structure

```
onboard/
├── index.php                  # Main portal (router)
├── callback.php              # GitHub OAuth callback (TODO: create)
├── deploy_automation.php     # Background deployment script (TODO: create)
├── stages/
│   ├── welcome.php           # Stage 1: Welcome
│   ├── github.php            # Stage 2: GitHub connect
│   ├── project.php           # Stage 3: Project details
│   ├── configure.php         # Stage 4: Configuration
│   ├── deploy.php            # Stage 5: Automated deployment
│   └── complete.php          # Stage 6: Success + instructions
└── README.md                 # This file
```

## 🔧 TODO: Missing Files

You need to create these 2 files:

### 1. `/onboard/callback.php` - GitHub OAuth Handler

```php
<?php
session_start();
require_once __DIR__ . '/index.php'; // For constants

// Verify state
if ($_GET['state'] !== $_SESSION['oauth_state']) {
    die('Invalid state');
}

// Exchange code for access token
$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'client_id' => GITHUB_CLIENT_ID,
        'client_secret' => GITHUB_CLIENT_SECRET,
        'code' => $_GET['code'],
        'state' => $_GET['state']
    ]),
    CURLOPT_HTTPHEADER => ['Accept: application/json']
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['access_token'])) {
    die('Failed to get access token');
}

$_SESSION['github_token'] = $response['access_token'];

// Get user info
$ch = curl_init('https://api.github.com/user');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $response['access_token'],
        'User-Agent: AI-Agent-Onboarding'
    ]
]);
$_SESSION['github_user'] = json_decode(curl_exec($ch), true);
curl_close($ch);

header('Location: index.php?stage=github');
```

### 2. `/onboard/deploy_automation.php` - Background Deployment

```php
<?php
// This runs in background to create everything
$onboard_id = $argv[1] ?? die('No ID');
$session_file = "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/private_html/onboarding/{$onboard_id}_session.json";
$session_data = json_decode(file_get_contents($session_file), true);

// TODO: Implement deployment steps:
// 1. Create GitHub repo via API
// 2. Generate config files
// 3. Create package
// 4. Upload to GitHub
// 5. Update status

// Update status as you go:
function updateStatus($onboard_id, $step, $status, $progress, $message = '') {
    $file = "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/private_html/onboarding/{$onboard_id}_status.json";
    $data = json_decode(file_get_contents($file), true);
    $data['steps'][$step] = compact('status', 'progress', 'message');
    $data['overall_progress'] = array_sum(array_column($data['steps'], 'progress')) / count($data['steps']);
    file_put_contents($file, json_encode($data));
}
```

## 🎁 What Gets Created

For each user, the system creates:

```
their-project-name/
├── .github/
│   ├── copilot-instructions.md       # Customized for their name
│   └── workflows/ (optional)
├── .vscode/
│   ├── settings.json                 # MCP configured
│   └── BOTS_GUIDE.md
├── ai-agent/
│   ├── src/Tools/Frontend/
│   └── config/
├── docs/
│   ├── MASTER_SYSTEM_GUIDE.md
│   ├── FRONTEND_INTEGRATION_SETUP.md
│   └── [12+ more]
├── scripts/
├── README.md                         # Personalized
└── package.json
```

## 🚀 Share This Link

Once setup, give your friend this URL:

**https://gpt.ecigdis.co.nz/onboard/**

They'll:
1. See welcome page
2. Connect GitHub
3. Enter project details
4. Click "Deploy"
5. Get their complete setup automatically!

## 🎯 Benefits

- ✅ **No manual setup** - Everything automated
- ✅ **Personalized** - Each user gets their own config
- ✅ **GitHub integrated** - Repo created automatically
- ✅ **Instant start** - Clone and go
- ✅ **Complete package** - All tools included
- ✅ **Backup available** - Download ZIP

## 📧 Support

Questions? The complete page has instructions and links to:
- Their GitHub repository
- Download package
- Documentation
- Support email

---

**Status:** ⚠️ Needs GitHub OAuth setup + 2 missing files
**Once complete:** Fully automated onboarding! 🎉
