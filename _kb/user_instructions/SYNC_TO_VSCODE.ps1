# ================================================================
# VS Code Copilot Prompts - Windows Sync Script
# ================================================================
# 
# PURPOSE: Pull latest Copilot instruction files from server to your Windows VS Code
# 
# USAGE:
#   1. Save this file to your PC (anywhere you like)
#   2. Open PowerShell as Administrator
#   3. Run: Set-ExecutionPolicy RemoteSigned (first time only)
#   4. Run: .\SYNC_TO_VSCODE.ps1
#
# SCHEDULE:
#   - Run manually whenever you want latest prompts
#   - OR set up Windows Task Scheduler to run daily
#   - OR run before starting VS Code (add to startup script)
#
# ================================================================

# Configuration
$ServerURL = "https://gpt.ecigdis.co.nz/_kb/user_instructions"
$LocalVSCodePromptsPath = "$env:APPDATA\Code\User\prompts"

# Color output functions
function Write-Success { Write-Host $args -ForegroundColor Green }
function Write-Info { Write-Host $args -ForegroundColor Cyan }
function Write-Warning { Write-Host $args -ForegroundColor Yellow }
function Write-Error { Write-Host $args -ForegroundColor Red }

# ================================================================
# Main Sync Function
# ================================================================

Write-Info "================================================================"
Write-Info "VS Code Copilot Prompts Sync Tool"
Write-Info "================================================================"
Write-Info ""

# Create VS Code prompts directory if it doesn't exist
if (-not (Test-Path $LocalVSCodePromptsPath)) {
    Write-Info "Creating VS Code prompts directory..."
    New-Item -ItemType Directory -Path $LocalVSCodePromptsPath -Force | Out-Null
    Write-Success "✓ Created: $LocalVSCodePromptsPath"
} else {
    Write-Success "✓ VS Code prompts directory exists: $LocalVSCodePromptsPath"
}

Write-Info ""
Write-Info "Fetching available instruction files from server..."

# List of instruction files to sync (these are in _kb/user_instructions/)
$InstructionFiles = @(
    "KB-REFRESH-CONTEXT.instructions.md",
    "MCP-TOOLS-MANDATE.instructions.md",
    "CIS-BOT-CONSTITUTION.instructions.md",
    "AUTOMATION-SYSTEM.instructions.md",
    "SECURITY-STANDARDS.instructions.md",
    "BOT_MASTER.md.instructions.md",
    "06_company_org_pack.instructions.md",
    "BOT TOOL SET REMINDER.instructions.md",
    "cis website.instructions.md",
    "Deep Problem Solving.instructions.md",
    "Front End Speciialist.instructions.md",
    "Generic Project Builder.instructions.md",
    "High Quality.instructions.md",
    "Network Engineer.instructions.md",
    "AGENT_SYSTEM_MAINTAINER_PROMPT.instructions.md",
    "BOT_ACTIVATION_MASTER_PROMPT.instructions.md",
    "BOT_ACTIVATION_QUICK.instructions.md"
)

$SuccessCount = 0
$FailCount = 0
$SkipCount = 0

foreach ($File in $InstructionFiles) {
    $Url = "$ServerURL/$File"
    $LocalPath = Join-Path $LocalVSCodePromptsPath $File
    
    Write-Info "Processing: $File"
    
    try {
        # Download file from server
        $WebClient = New-Object System.Net.WebClient
        $WebClient.DownloadFile($Url, $LocalPath)
        
        # Verify file was downloaded and has content
        if ((Test-Path $LocalPath) -and ((Get-Item $LocalPath).Length -gt 0)) {
            Write-Success "  ✓ Downloaded successfully"
            $SuccessCount++
        } else {
            Write-Warning "  ⚠ Downloaded but file is empty"
            $SkipCount++
        }
    }
    catch {
        # Check if file already exists locally
        if (Test-Path $LocalPath) {
            Write-Warning "  ⚠ Download failed, keeping existing local copy"
            $SkipCount++
        } else {
            Write-Error "  ✗ Download failed and no local copy exists"
            Write-Error "    Error: $($_.Exception.Message)"
            $FailCount++
        }
    }
}

Write-Info ""
Write-Info "================================================================"
Write-Info "Sync Summary"
Write-Info "================================================================"
Write-Success "✓ Successfully synced: $SuccessCount files"

if ($SkipCount -gt 0) {
    Write-Warning "⚠ Skipped/Kept existing: $SkipCount files"
}

if ($FailCount -gt 0) {
    Write-Error "✗ Failed to sync: $FailCount files"
}

Write-Info ""
Write-Info "VS Code Prompts Location: $LocalVSCodePromptsPath"
Write-Info ""

# Optional: Create a "last sync" marker file
$LastSyncFile = Join-Path $LocalVSCodePromptsPath ".last-sync.txt"
Get-Date -Format "yyyy-MM-dd HH:mm:ss" | Out-File $LastSyncFile
Write-Info "Last sync timestamp saved to: $LastSyncFile"

Write-Info ""
Write-Success "================================================================"
Write-Success "Sync Complete! Restart VS Code to load new prompts."
Write-Success "================================================================"

# Optional: Pause to keep window open if run by double-click
# Write-Host ""
# Write-Host "Press any key to exit..."
# $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
