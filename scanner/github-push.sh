#!/bin/bash
# Quick GitHub Push Guide
# Scanner Dashboard Repository Setup

clear
echo "╔════════════════════════════════════════════════════════════╗"
echo "║       Scanner Dashboard - GitHub Push Guide               ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

REPO_NAME="scanner-dashboard"
CURRENT_DIR="/home/master/applications/hdgwrzntwa/public_html/scanner"

echo "📍 Current Status:"
echo "   Location: $CURRENT_DIR"
echo "   Commit: e8ec646 (Initial commit with Bootstrap 5)"
echo "   Files: 85 files, 34,656 lines"
echo "   Size: ~1.6MB (vendor excluded)"
echo ""

echo "🎯 Choose your method:"
echo ""
echo "═══════════════════════════════════════════════════════════"
echo "  METHOD 1: HTTPS (Recommended for first time)"
echo "═══════════════════════════════════════════════════════════"
echo ""
echo "1. Create repo on GitHub:"
echo "   → https://github.com/new"
echo "   → Name: $REPO_NAME"
echo "   → Description: Intelligence Scanner Dashboard with Bootstrap 5"
echo "   → Public repo"
echo "   → DON'T initialize with README"
echo ""
echo "2. Run these commands:"
echo ""
cat << 'EOF'
cd /home/master/applications/hdgwrzntwa/public_html/scanner

# Replace YOUR_USERNAME with your GitHub username
git remote add origin https://github.com/YOUR_USERNAME/scanner-dashboard.git
git branch -M main
git push -u origin main
EOF
echo ""
echo "3. Enter your GitHub username and Personal Access Token when prompted"
echo ""

echo "═══════════════════════════════════════════════════════════"
echo "  METHOD 2: SSH (If you have SSH keys configured)"
echo "═══════════════════════════════════════════════════════════"
echo ""
echo "1. Create repo on GitHub (same as above)"
echo ""
echo "2. Run these commands:"
echo ""
cat << 'EOF'
cd /home/master/applications/hdgwrzntwa/public_html/scanner

# Replace YOUR_USERNAME with your GitHub username
git remote add origin git@github.com:YOUR_USERNAME/scanner-dashboard.git
git branch -M main
git push -u origin main
EOF
echo ""

echo "═══════════════════════════════════════════════════════════"
echo "  QUICK COPY-PASTE (Update YOUR_USERNAME first!)"
echo "═══════════════════════════════════════════════════════════"
echo ""
read -p "Enter your GitHub username: " GITHUB_USER

if [ ! -z "$GITHUB_USER" ]; then
    echo ""
    echo "📋 Copy and paste these commands:"
    echo ""
    echo "cd $CURRENT_DIR"
    echo "git remote add origin https://github.com/$GITHUB_USER/$REPO_NAME.git"
    echo "git branch -M main"
    echo "git push -u origin main"
    echo ""
    echo "Or run automatically? (y/n)"
    read -p "> " AUTO_RUN
    
    if [ "$AUTO_RUN" = "y" ] || [ "$AUTO_RUN" = "Y" ]; then
        echo ""
        echo "⚠️  IMPORTANT: Make sure you've created the repo on GitHub first!"
        echo "   → https://github.com/new"
        read -p "Have you created the repo? (y/n): " REPO_CREATED
        
        if [ "$REPO_CREATED" = "y" ] || [ "$REPO_CREATED" = "Y" ]; then
            echo ""
            echo "🚀 Pushing to GitHub..."
            cd "$CURRENT_DIR"
            git remote add origin "https://github.com/$GITHUB_USER/$REPO_NAME.git"
            git branch -M main
            git push -u origin main
            
            echo ""
            echo "✅ Done! View your repo at:"
            echo "   https://github.com/$GITHUB_USER/$REPO_NAME"
        else
            echo ""
            echo "❌ Please create the repo first, then run this script again"
        fi
    fi
else
    echo ""
    echo "💡 No problem! Just follow Method 1 or 2 above"
fi

echo ""
echo "═══════════════════════════════════════════════════════════"
echo "  Need a GitHub Personal Access Token?"
echo "═══════════════════════════════════════════════════════════"
echo ""
echo "1. Go to: https://github.com/settings/tokens"
echo "2. Click: Generate new token (classic)"
echo "3. Select scopes: repo (all)"
echo "4. Click: Generate token"
echo "5. Copy the token and use it as your password when pushing"
echo ""
echo "═══════════════════════════════════════════════════════════"
echo ""
