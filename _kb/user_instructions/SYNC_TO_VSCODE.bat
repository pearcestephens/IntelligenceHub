@echo off
REM ================================================================
REM VS Code Copilot Prompts - Quick Sync (Windows Batch)
REM ================================================================
REM 
REM USAGE: Just double-click this file to sync latest prompts!
REM
REM ================================================================

echo.
echo ================================================================
echo VS Code Copilot Prompts Quick Sync
echo ================================================================
echo.

REM Configuration
set "SERVER_URL=https://gpt.ecigdis.co.nz/_kb/user_instructions"
set "LOCAL_PATH=%APPDATA%\Code\User\prompts"

REM Create directory if it doesn't exist
if not exist "%LOCAL_PATH%" (
    echo Creating VS Code prompts directory...
    mkdir "%LOCAL_PATH%"
    echo [OK] Created: %LOCAL_PATH%
) else (
    echo [OK] Directory exists: %LOCAL_PATH%
)

echo.
echo Downloading latest instruction files from server...
echo.

REM Download files using PowerShell (available on all modern Windows)
powershell -Command "& { $files = @('KB-REFRESH-CONTEXT.instructions.md', 'MCP-TOOLS-MANDATE.instructions.md', 'CIS-BOT-CONSTITUTION.instructions.md', 'AUTOMATION-SYSTEM.instructions.md', 'SECURITY-STANDARDS.instructions.md', 'BOT_MASTER.md.instructions.md', 'AGENT_SYSTEM_MAINTAINER_PROMPT.instructions.md', 'BOT_ACTIVATION_MASTER_PROMPT.instructions.md', 'BOT_ACTIVATION_QUICK.instructions.md'); $success = 0; foreach ($file in $files) { try { $url = 'https://gpt.ecigdis.co.nz/_kb/user_instructions/' + $file; $dest = Join-Path '%LOCAL_PATH%' $file; (New-Object System.Net.WebClient).DownloadFile($url, $dest); Write-Host '[OK]' $file; $success++ } catch { Write-Host '[FAIL]' $file } } Write-Host ''; Write-Host 'Synced' $success 'of' $files.Count 'files' }"

echo.
echo ================================================================
echo Sync Complete!
echo ================================================================
echo.
echo Location: %LOCAL_PATH%
echo.
echo Restart VS Code to load the new prompts.
echo.
pause
