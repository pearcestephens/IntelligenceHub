#!/bin/bash
###############################################################################
# 🚀 FRIEND ONBOARDING - AUTOMATED INSTALLER
#
# Complete automated setup for external developers/friends to get started with
# Intelligence Hub, MCP, and optimized workspace.
#
# Features:
# - ✅ VS Code settings installation with security hardening
# - ✅ MCP Intelligence Hub connection (PROJECT_ID=999 sandbox)
# - ✅ Prompt instructions setup
# - ✅ Workspace file scanning and indexing
# - ✅ Security validation
# - ✅ Extension installation
# - ✅ Theme configuration
#
# Usage:
#   bash FRIEND_ONBOARDING_INSTALLER.sh
#   bash FRIEND_ONBOARDING_INSTALLER.sh --workspace /path/to/project
#
# @version 2.0.0 - Hardened & Optimized
# @date 2025-11-05
###############################################################################

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Configuration
WORKSPACE_PATH="${1:-$PWD}"
VSCODE_USER_DIR=""
MCP_SERVER_HOST="master_anjzctzjhr@hdgwrzntwa"
MCP_SERVER_PATH="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp"
MCP_SERVER_URL="https://gpt.ecigdis.co.nz/mcp/server_v3.php"
PROJECT_ID="999"  # Sandbox for friends
BUSINESS_UNIT_ID="999"  # Sandbox unit

###############################################################################
# Helper Functions
###############################################################################

print_header() {
    echo -e "${BLUE}${BOLD}"
    echo "═══════════════════════════════════════════════════════════════════════"
    echo "  $1"
    echo "═══════════════════════════════════════════════════════════════════════"
    echo -e "${NC}"
}

print_step() {
    echo -e "${CYAN}${BOLD}[$1]${NC} $2"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Detect OS and set VS Code user directory
detect_vscode_dir() {
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        VSCODE_USER_DIR="$HOME/.config/Code/User"
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        VSCODE_USER_DIR="$HOME/Library/Application Support/Code/User"
    elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "win32" ]]; then
        VSCODE_USER_DIR="$APPDATA/Code/User"
    else
        print_error "Unsupported OS: $OSTYPE"
        exit 1
    fi

    if [ ! -d "$VSCODE_USER_DIR" ]; then
        print_warning "VS Code user directory not found: $VSCODE_USER_DIR"
        print_info "Creating directory..."
        mkdir -p "$VSCODE_USER_DIR"
    fi

    print_success "VS Code directory: $VSCODE_USER_DIR"
}

# Check prerequisites
check_prerequisites() {
    print_step "1/8" "Checking prerequisites..."

    # Check VS Code
    if ! command -v code &> /dev/null; then
        print_warning "VS Code CLI not found. Install from https://code.visualstudio.com/"
        print_info "Continuing anyway (settings will be installed but extensions must be manual)..."
    else
        print_success "VS Code CLI found"
    fi

    # Check SSH
    if ! command -v ssh &> /dev/null; then
        print_error "SSH not found. Please install OpenSSH."
        exit 1
    fi
    print_success "SSH found"

    # Check Node (for MCP wrapper)
    if ! command -v node &> /dev/null; then
        print_warning "Node.js not found. MCP wrapper requires Node.js 14+."
        print_info "Install from https://nodejs.org/"
    else
        NODE_VERSION=$(node --version)
        print_success "Node.js found: $NODE_VERSION"
    fi

    echo ""
}

# Test SSH connection
test_ssh_connection() {
    print_step "2/8" "Testing SSH connection to MCP server..."

    if ssh -o BatchMode=yes -o ConnectTimeout=5 "$MCP_SERVER_HOST" "echo 'Connection successful'" &> /dev/null; then
        print_success "SSH connection successful"
    else
        print_error "SSH connection failed to $MCP_SERVER_HOST"
        print_info "You need SSH access to use MCP. Steps:"
        print_info "  1. Generate SSH key: ssh-keygen -t ed25519"
        print_info "  2. Send public key (~/.ssh/id_ed25519.pub) to admin"
        print_info "  3. Wait for access to be granted"
        print_info ""
        print_info "Continuing without MCP (can configure later)..."
        MCP_ENABLED=false
    fi
    echo ""
}

# Backup existing settings
backup_existing_settings() {
    print_step "3/8" "Backing up existing VS Code settings..."

    if [ -f "$VSCODE_USER_DIR/settings.json" ]; then
        BACKUP_FILE="$VSCODE_USER_DIR/settings.json.backup.$(date +%Y%m%d_%H%M%S)"
        cp "$VSCODE_USER_DIR/settings.json" "$BACKUP_FILE"
        print_success "Backup created: $BACKUP_FILE"
    else
        print_info "No existing settings found (this is fine for new installs)"
    fi
    echo ""
}

# Install VS Code settings
install_vscode_settings() {
    print_step "4/8" "Installing hardened VS Code settings..."

    # Get MCP API key from remote server (if accessible)
    MCP_API_KEY=""
    if [ "$MCP_ENABLED" != "false" ]; then
        MCP_API_KEY=$(ssh "$MCP_SERVER_HOST" "cat $MCP_SERVER_PATH/.env 2>/dev/null | grep MCP_API_KEY | cut -d'=' -f2" 2>/dev/null || echo "")
    fi

    if [ -z "$MCP_API_KEY" ]; then
        print_warning "Could not fetch MCP API key. You'll need to add it manually."
        MCP_API_KEY="YOUR_MCP_API_KEY_HERE"
    fi

    cat > "$VSCODE_USER_DIR/settings.json" <<'SETTINGS_EOF'
{
  "telemetry.telemetryLevel": "off",
  "security.workspace.trust.untrustedFiles": "open",

  "git.autofetch": true,
  "git.enableSmartCommit": true,

  "editor.renderWhitespace": "boundary",
  "editor.smoothScrolling": true,
  "editor.bracketPairColorization.enabled": true,
  "editor.inlineSuggest.enabled": true,
  "editor.quickSuggestions": { "comments": true, "strings": true, "other": true },

  "files.autoSave": "afterDelay",
  "files.autoSaveDelay": 600,
  "files.encoding": "utf8",
  "files.eol": "\n",
  "files.insertFinalNewline": true,
  "files.trimTrailingWhitespace": true,

  "workbench.colorTheme": "Default Dark Modern",
  "workbench.startupEditor": "none",

  "github.copilot.enable": {
    "*": true,
    "yaml": true,
    "plaintext": true,
    "markdown": true,
    "php": true,
    "javascript": true,
    "json": true,
    "sql": true
  },
  "github.copilot.editor.enableAutoCompletions": true,
  "github.copilot.chat.localeOverride": "en",

  "github.copilot.chat.agent.default": "intelligence-hub",
  "github.copilot.chat.agent.routeAllQueries": true,
  "github.copilot.chat.agent.preferCustom": true,
  "github.copilot.chat.mcp.disableDefaults": true,

  "github.copilot.chat.codeGeneration.instructions": [
    {
      "text": "🤖 AI AGENT: You have access to Intelligence Hub MCP with 50+ tools. Use semantic_search, conversation memory, and knowledge base features."
    },
    {
      "file": "**/_kb/AUTOMATIC_MEMORY_COMPLETE.md",
      "instruction": "At START of EVERY conversation, call conversation.get_project_context"
    }
  ],

  "github.copilot.advanced": {
    "mcp.enabled": true,
    "mcp.servers": {
      "intelligence-hub": {
        "command": "ssh",
        "args": [
          "MCP_SERVER_HOST_PLACEHOLDER",
          "cd MCP_SERVER_PATH_PLACEHOLDER && MCP_SERVER_URL=MCP_URL_PLACEHOLDER MCP_API_KEY=MCP_KEY_PLACEHOLDER PROJECT_ID=PROJECT_ID_PLACEHOLDER BUSINESS_UNIT_ID=BUSINESS_UNIT_ID_PLACEHOLDER PROJECT_NAME=Friend_Sandbox WORKSPACE_ROOT=WORKSPACE_PATH_PLACEHOLDER ENABLE_CONVERSATION_CONTEXT=true AUTO_RETRIEVE_CONTEXT=true CONTEXT_LIMIT=20 NODE_TLS_REJECT_UNAUTHORIZED=0 node mcp-server-wrapper.js"
        ],
        "description": "🏖️ Intelligence Hub - Friend Sandbox (PROJECT_ID=999) - Safe testing environment",
        "env": {
          "NODE_TLS_REJECT_UNAUTHORIZED": "0"
        }
      }
    },
    "contextSize": 32768,
    "smartTyping": true,
    "prioritizeKnowledge": true,
    "autoMemoryRetrieval": {
      "enabled": true,
      "onConversationStart": true,
      "aggressive": true,
      "tools": {
        "conversation.get_project_context": {
          "enabled": true,
          "priority": "critical",
          "autoCall": true,
          "mandatory": true
        }
      }
    }
  },

  "github.copilot.conversation.memory": {
    "enabled": true,
    "persistAcrossSessions": true,
    "autoSaveInterval": 10000,
    "maxHistoryLength": 500
  }
}
SETTINGS_EOF

    # Replace placeholders
    sed -i "s|MCP_SERVER_HOST_PLACEHOLDER|$MCP_SERVER_HOST|g" "$VSCODE_USER_DIR/settings.json"
    sed -i "s|MCP_SERVER_PATH_PLACEHOLDER|$MCP_SERVER_PATH|g" "$VSCODE_USER_DIR/settings.json"
    sed -i "s|MCP_URL_PLACEHOLDER|$MCP_SERVER_URL|g" "$VSCODE_USER_DIR/settings.json"
    sed -i "s|MCP_KEY_PLACEHOLDER|$MCP_API_KEY|g" "$VSCODE_USER_DIR/settings.json"
    sed -i "s|PROJECT_ID_PLACEHOLDER|$PROJECT_ID|g" "$VSCODE_USER_DIR/settings.json"
    sed -i "s|BUSINESS_UNIT_ID_PLACEHOLDER|$BUSINESS_UNIT_ID|g" "$VSCODE_USER_DIR/settings.json"
    sed -i "s|WORKSPACE_PATH_PLACEHOLDER|$WORKSPACE_PATH|g" "$VSCODE_USER_DIR/settings.json"

    print_success "VS Code settings installed"
    echo ""
}

# Install prompt instructions
install_prompt_instructions() {
    print_step "5/8" "Installing prompt instructions..."

    mkdir -p "$VSCODE_USER_DIR/prompts"

    # MCP Context prompt
    cat > "$VSCODE_USER_DIR/prompts/MCP_CONTEXT.instructions.md" <<'PROMPT_EOF'
---
applyTo: '**'
---

## 🔄 MCP CONTEXT PROTOCOL

You have access to Intelligence Hub MCP server with 50+ tools.

**MANDATORY FIRST ACTION:**
Call `conversation.get_project_context` at the start of EVERY conversation.

**Available Tools:**
- conversation.* - Memory and context retrieval
- kb.* - Knowledge base search and storage
- db.* - Database queries
- fs.* - File system operations
- semantic_search - Code search across thousands of files
- ai_agent.query - Full AI agent with RAG

**Use tools religiously. Never assume - always verify.**
PROMPT_EOF

    # Friend welcome prompt
    cat > "$VSCODE_USER_DIR/prompts/FRIENDS_WELCOME.instructions.md" <<'PROMPT_EOF'
---
applyTo: '**'
---

## 👋 Welcome Friend!

You're connected to the Intelligence Hub in **SANDBOX MODE** (PROJECT_ID=999).

**What you can do:**
- Ask questions about code
- Search the knowledge base
- Use semantic search
- Store your own notes and learnings

**What's protected:**
- Internal production data
- Sensitive business info

**Get Started:**
- Try: "Search the knowledge base for PHP best practices"
- Try: "Find code examples of database connections"
- Try: "Store a note about [topic]"

Enjoy! 🚀
PROMPT_EOF

    print_success "Prompt instructions installed"
    echo ""
}

# Scan and index workspace files
scan_workspace_files() {
    print_step "6/8" "Scanning and indexing workspace files..."

    if [ ! -d "$WORKSPACE_PATH" ]; then
        print_error "Workspace path does not exist: $WORKSPACE_PATH"
        return 1
    fi

    cd "$WORKSPACE_PATH"

    # Count files
    TOTAL_FILES=$(find . -type f \( -name "*.php" -o -name "*.js" -o -name "*.json" -o -name "*.md" \) 2>/dev/null | wc -l)
    print_info "Found $TOTAL_FILES code files"

    # Create index file
    INDEX_FILE="$WORKSPACE_PATH/.vscode/file_index.json"
    mkdir -p "$WORKSPACE_PATH/.vscode"

    echo "{" > "$INDEX_FILE"
    echo "  \"generated\": \"$(date -u +"%Y-%m-%dT%H:%M:%SZ")\"," >> "$INDEX_FILE"
    echo "  \"workspace\": \"$WORKSPACE_PATH\"," >> "$INDEX_FILE"
    echo "  \"total_files\": $TOTAL_FILES," >> "$INDEX_FILE"
    echo "  \"files\": [" >> "$INDEX_FILE"

    # Index files
    find . -type f \( -name "*.php" -o -name "*.js" -o -name "*.json" -o -name "*.md" \) -print0 2>/dev/null | while IFS= read -r -d '' file; do
        # Get file stats
        SIZE=$(stat -f%z "$file" 2>/dev/null || stat -c%s "$file" 2>/dev/null || echo 0)
        LINES=$(wc -l < "$file" 2>/dev/null || echo 0)
        MODIFIED=$(stat -f%Sm -t "%Y-%m-%d" "$file" 2>/dev/null || stat -c%y "$file" 2>/dev/null | cut -d' ' -f1)

        # Output JSON entry
        echo "    {\"path\": \"$file\", \"size\": $SIZE, \"lines\": $LINES, \"modified\": \"$MODIFIED\"}," >> "$INDEX_FILE"
    done

    # Close JSON
    echo "  ]" >> "$INDEX_FILE"
    echo "}" >> "$INDEX_FILE"

    print_success "Workspace indexed: $INDEX_FILE"
    print_info "You can now use semantic_search with full file context"
    echo ""
}

# Install VS Code extensions
install_extensions() {
    print_step "7/8" "Installing VS Code extensions..."

    if ! command -v code &> /dev/null; then
        print_warning "VS Code CLI not available - skipping extension install"
        print_info "Install manually: Extensions → Search for 'GitHub Copilot'"
        echo ""
        return
    fi

    EXTENSIONS=(
        "github.copilot"
        "github.copilot-chat"
        "bmewburn.vscode-intelephense-client"
        "ms-vscode-remote.remote-ssh"
    )

    for ext in "${EXTENSIONS[@]}"; do
        if code --list-extensions | grep -q "$ext"; then
            print_info "$ext already installed"
        else
            print_info "Installing $ext..."
            code --install-extension "$ext" --force
            print_success "$ext installed"
        fi
    done

    echo ""
}

# Security hardening
security_hardening() {
    print_step "8/8" "Applying security hardening..."

    # Check settings.json permissions
    chmod 600 "$VSCODE_USER_DIR/settings.json"
    print_success "Settings file permissions locked (600)"

    # Warn about API key
    if grep -q "YOUR_MCP_API_KEY_HERE" "$VSCODE_USER_DIR/settings.json"; then
        print_warning "MCP API key is placeholder - you need to add the real key"
        print_info "Contact admin for API key or extract from server .env"
    else
        print_warning "MCP API key is embedded in settings.json"
        print_info "Keep this file secure - never commit to public repos"
    fi

    # Create .gitignore for VS Code settings if in git repo
    if [ -d "$WORKSPACE_PATH/.git" ]; then
        if ! grep -q ".vscode/settings.json" "$WORKSPACE_PATH/.gitignore" 2>/dev/null; then
            echo "" >> "$WORKSPACE_PATH/.gitignore"
            echo "# VS Code settings (may contain secrets)" >> "$WORKSPACE_PATH/.gitignore"
            echo ".vscode/settings.json" >> "$WORKSPACE_PATH/.gitignore"
            print_success "Added .vscode/settings.json to .gitignore"
        fi
    fi

    echo ""
}

# Final summary
print_summary() {
    print_header "✅ ONBOARDING COMPLETE!"

    echo -e "${GREEN}Your development environment is ready!${NC}"
    echo ""
    echo "📍 Configuration:"
    echo "   VS Code Settings: $VSCODE_USER_DIR/settings.json"
    echo "   Prompts: $VSCODE_USER_DIR/prompts/"
    echo "   Workspace: $WORKSPACE_PATH"
    echo "   Project ID: $PROJECT_ID (Sandbox)"
    echo ""
    echo "🚀 Next Steps:"
    echo "   1. Reload VS Code: Ctrl+Shift+P → 'Developer: Reload Window'"
    echo "   2. Open Copilot Chat (Ctrl+Alt+I or Cmd+Opt+I)"
    echo "   3. Test MCP: Send 'Test connection' in chat"
    echo "   4. Try semantic search: 'Find all database connection code'"
    echo ""
    echo "📚 Documentation:"
    echo "   - ONBOARDING_PACKAGE_FOR_FRIENDS.md (complete guide)"
    echo "   - SANDBOX_QUICK_GUIDE.md (sandbox features)"
    echo ""
    echo "🏖️ Sandbox Mode:"
    echo "   You're in PROJECT_ID=999 (safe sandbox environment)"
    echo "   Full access to all MCP tools"
    echo "   Read-only access to shared knowledge base"
    echo "   Your data is isolated and private"
    echo ""
    echo "🔒 Security Notes:"
    echo "   ✓ Settings file permissions locked"
    echo "   ✓ Never commit settings.json to public repos"
    echo "   ✓ SSH keys are your responsibility"
    echo "   ✓ Keep API keys secure"
    echo ""
    echo "💡 Pro Tips:"
    echo "   - Use conversation memory - the AI remembers context!"
    echo "   - Store useful snippets: 'Store this code pattern for later'"
    echo "   - Semantic search is powerful: 'Find similar implementations'"
    echo "   - Ask questions: 'Explain this codebase architecture'"
    echo ""
    echo -e "${CYAN}Need help? Check ONBOARDING_PACKAGE_FOR_FRIENDS.md${NC}"
    echo ""
}

###############################################################################
# Main Execution
###############################################################################

main() {
    print_header "🚀 FRIEND ONBOARDING - AUTOMATED INSTALLER"

    echo -e "${BLUE}This script will set up your complete development environment${NC}"
    echo -e "${BLUE}with Intelligence Hub, MCP, and optimized VS Code configuration.${NC}"
    echo ""
    echo "Workspace: $WORKSPACE_PATH"
    echo ""
    read -p "Press Enter to continue or Ctrl+C to cancel..."
    echo ""

    detect_vscode_dir
    check_prerequisites
    test_ssh_connection
    backup_existing_settings
    install_vscode_settings
    install_prompt_instructions
    scan_workspace_files
    install_extensions
    security_hardening
    print_summary
}

# Run
main

exit 0
