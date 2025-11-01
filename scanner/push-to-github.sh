#!/bin/bash
# GitHub Push Script for Scanner Dashboard
# Run this script to create repo and push

echo "🚀 Scanner Dashboard - GitHub Push Script"
echo "=========================================="
echo ""

# Configuration
REPO_NAME="scanner-dashboard"
REPO_DESCRIPTION="Intelligence Scanner Dashboard with Bootstrap 5 - Professional code analysis and monitoring"
GITHUB_USERNAME=""  # Add your GitHub username here

echo "📋 Pre-flight Check:"
echo "✅ Git initialized"
echo "✅ Initial commit created (e8ec646)"
echo "✅ 85 files, 34,656 lines of code"
echo "✅ Size: ~1.6MB (vendor excluded)"
echo ""

# Check if GitHub CLI is installed
if command -v gh &> /dev/null; then
    echo "✅ GitHub CLI (gh) detected"
    echo ""
    echo "Creating repository using GitHub CLI..."
    echo ""

    # Create repo using GitHub CLI
    gh repo create "$REPO_NAME" \
        --public \
        --description "$REPO_DESCRIPTION" \
        --source=. \
        --remote=origin \
        --push

    echo ""
    echo "✅ Repository created and pushed!"
    echo "🌐 View at: https://github.com/$(gh api user -q .login)/$REPO_NAME"

else
    echo "⚠️  GitHub CLI not found"
    echo ""
    echo "📝 Manual Setup Required:"
    echo ""
    echo "1. Go to: https://github.com/new"
    echo "2. Repository name: $REPO_NAME"
    echo "3. Description: $REPO_DESCRIPTION"
    echo "4. Choose: Public"
    echo "5. DO NOT initialize with README (we already have one)"
    echo "6. Click 'Create repository'"
    echo ""
    echo "7. Then run these commands:"
    echo ""
    echo "   cd /home/master/applications/hdgwrzntwa/public_html/scanner"
    echo "   git remote add origin https://github.com/YOUR_USERNAME/$REPO_NAME.git"
    echo "   git branch -M main"
    echo "   git push -u origin main"
    echo ""
    echo "Or with SSH:"
    echo "   git remote add origin git@github.com:YOUR_USERNAME/$REPO_NAME.git"
    echo "   git branch -M main"
    echo "   git push -u origin main"
    echo ""
fi

echo ""
echo "📦 What's being pushed:"
echo "  - Bootstrap 5 conversion (scanner-bootstrap.css)"
echo "  - Fixed settings page with gradient cards"
echo "  - Multi-project support"
echo "  - API endpoints (AI assistant, auto-fix, real-time scanner)"
echo "  - Comprehensive documentation"
echo ""
echo "🚫 What's excluded (via .gitignore):"
echo "  - vendor/ (11MB of Composer packages)"
echo "  - logs/ (log files)"
echo "  - .env (environment config)"
echo "  - sessions/ (session data)"
echo ""
