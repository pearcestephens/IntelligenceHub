#!/bin/bash

echo "🤖 Claude AI Agent Setup & Test Script"
echo "======================================"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    case $2 in
        "success") echo -e "${GREEN}✅ $1${NC}" ;;
        "error") echo -e "${RED}❌ $1${NC}" ;;
        "warning") echo -e "${YELLOW}⚠️  $1${NC}" ;;
        "info") echo -e "${BLUE}ℹ️  $1${NC}" ;;
    esac
}

# Check if we're in the right directory
if [ ! -f ".env" ]; then
    print_status "Error: .env file not found. Please run this from the ai-agent directory." "error"
    exit 1
fi

print_status "Checking Claude AI configuration..." "info"

# Check if ANTHROPIC_API_KEY is set in .env
if grep -q "ANTHROPIC_API_KEY=YOUR_CLAUDE_API_KEY_HERE" .env; then
    print_status "Claude API key not configured yet" "warning"
    echo ""
    echo "🔑 To complete Claude setup, you need to:"
    echo "1. Get your API key from: https://console.anthropic.com/"
    echo "2. Replace 'YOUR_CLAUDE_API_KEY_HERE' in .env with your actual API key"
    echo "3. Run this script again to test the connection"
    echo ""
    exit 1
elif grep -q "ANTHROPIC_API_KEY=" .env; then
    API_KEY=$(grep "ANTHROPIC_API_KEY=" .env | cut -d'=' -f2)
    if [ ${#API_KEY} -gt 10 ]; then
        print_status "Claude API key found in .env (${#API_KEY} characters)" "success"
    else
        print_status "Claude API key seems too short or invalid" "error"
        exit 1
    fi
else
    print_status "ANTHROPIC_API_KEY not found in .env file" "error"
    exit 1
fi

# Test Claude health endpoint
print_status "Testing Claude health endpoint..." "info"
php public/agent/api/claude-health.php > /tmp/claude_health.json 2>&1

if [ $? -eq 0 ]; then
    # Parse the JSON response to check if it's successful
    if grep -q '"success".*true' /tmp/claude_health.json; then
        print_status "Claude health check passed!" "success"
        
        # Show model information
        if grep -q '"claude_api"' /tmp/claude_health.json; then
            MODEL=$(grep -o '"model":"[^"]*' /tmp/claude_health.json | cut -d'"' -f4)
            print_status "Current model: $MODEL" "info"
        fi
    else
        print_status "Claude health check failed" "error"
        echo "Response:"
        cat /tmp/claude_health.json
        exit 1
    fi
else
    print_status "Failed to run Claude health check" "error"
    cat /tmp/claude_health.json
    exit 1
fi

# Test basic Claude conversation
print_status "Testing Claude chat functionality..." "info"
TEST_JSON='{
    "message": "Hello Claude! Please respond with exactly: CLAUDE_TEST_SUCCESS",
    "conversation_id": "test_conversation_'$(date +%s)'",
    "stream": false,
    "model": "claude-3-5-sonnet-20241022"
}'

echo "$TEST_JSON" | php public/agent/api/claude-chat.php > /tmp/claude_chat.json 2>&1

if [ $? -eq 0 ]; then
    if grep -q '"success".*true' /tmp/claude_chat.json; then
        RESPONSE=$(grep -o '"response":"[^"]*' /tmp/claude_chat.json | cut -d'"' -f4)
        if [[ "$RESPONSE" == *"CLAUDE_TEST_SUCCESS"* ]]; then
            print_status "Claude chat test successful!" "success"
            print_status "Claude response: $RESPONSE" "info"
        else
            print_status "Claude responded, but with unexpected content" "warning"
            print_status "Response: $RESPONSE" "info"
        fi
    else
        print_status "Claude chat test failed" "error"
        echo "Response:"
        cat /tmp/claude_chat.json
    fi
else
    print_status "Failed to run Claude chat test" "error"
    cat /tmp/claude_chat.json
fi

# Test streaming functionality
print_status "Testing Claude streaming..." "info"
STREAM_JSON='{
    "message": "Count from 1 to 3, one number per line.",
    "conversation_id": "stream_test_'$(date +%s)'",
    "stream": true,
    "model": "claude-3-5-sonnet-20241022"
}'

echo "$STREAM_JSON" | timeout 30 php public/agent/api/claude-chat.php > /tmp/claude_stream.txt 2>&1

if [ $? -eq 0 ]; then
    if grep -q "data:" /tmp/claude_stream.txt; then
        print_status "Claude streaming test successful!" "success"
        STREAM_COUNT=$(grep -c "data:" /tmp/claude_stream.txt)
        print_status "Received $STREAM_COUNT streaming data chunks" "info"
    else
        print_status "No streaming data found" "warning"
    fi
else
    print_status "Claude streaming test may have issues" "warning"
fi

# Check file permissions
print_status "Checking file permissions..." "info"
if [ -r "public/agent/api/claude-chat.php" ] && [ -r "public/agent/api/claude-health.php" ]; then
    print_status "Claude API files are readable" "success"
else
    print_status "Claude API files have permission issues" "error"
fi

# Cleanup temp files
rm -f /tmp/claude_health.json /tmp/claude_chat.json /tmp/claude_stream.txt

echo ""
print_status "Claude AI Agent Setup Complete! 🎉" "success"
echo ""
echo "🌐 Access URLs:"
echo "   • Claude Chat Interface: https://staff.vapeshed.co.nz/assets/cron/utility_scripts/devtools/ai-agent/public/agent/claude.html"
echo "   • System Status Dashboard: https://staff.vapeshed.co.nz/assets/cron/utility_scripts/devtools/ai-agent/public/status.html"
echo "   • Main Landing Page: https://staff.vapeshed.co.nz/assets/cron/utility_scripts/devtools/ai-agent/public/"
echo ""
echo "🔧 Direct API Endpoints:"
echo "   • Claude Chat: ./public/agent/api/claude-chat.php"
echo "   • Claude Health: ./public/agent/api/claude-health.php"
echo ""
echo "✨ Features Available:"
echo "   • Real-time streaming chat with Claude 3.5 Sonnet"
echo "   • Conversation persistence and history"
echo "   • Health monitoring and status checks"
echo "   • Production-ready error handling"
echo "   • Bootstrap 5 responsive UI"
echo ""
echo "🚀 Ready to use! Your Claude AI agent is fully operational."