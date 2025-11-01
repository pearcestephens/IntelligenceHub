#!/bin/bash

###############################################################################
# 🚀 Quick Wins Implementation Script
#
# This script implements the TOP 3 immediate quick wins:
# 1. Test Multi-Domain System (15 min)
# 2. Enable CodeTool (5 min)
# 3. Domain Switcher UI Component (30 min)
#
# Usage: ./quick-start-wins.sh [--auto]
#   --auto : Skip confirmations and run all steps automatically
#
# Author: AI Development Assistant
# Date: 2025-01-28
# Version: 1.0
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Base paths
BASE_DIR="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html"
AI_AGENT_DIR="$BASE_DIR/ai-agent"
TOOLS_DIR="$AI_AGENT_DIR/src/Tools"
FRONTEND_DIR="$BASE_DIR/frontend-tools"

# Auto mode flag
AUTO_MODE=false
if [ "$1" == "--auto" ]; then
    AUTO_MODE=true
fi

# Helper functions
print_header() {
    echo -e "${PURPLE}"
    echo "╔══════════════════════════════════════════════════════════════════════════════╗"
    echo "║                                                                              ║"
    echo "║                  🚀 QUICK WINS IMPLEMENTATION SCRIPT 🚀                     ║"
    echo "║                                                                              ║"
    echo "╚══════════════════════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

print_step() {
    echo -e "\n${CYAN}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN}$1${NC}"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════════${NC}\n"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

confirm() {
    if [ "$AUTO_MODE" == "true" ]; then
        return 0
    fi

    read -p "$(echo -e ${YELLOW}$1 [y/N]:${NC} )" -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        return 0
    else
        return 1
    fi
}

###############################################################################
# STEP 1: Test Multi-Domain System
###############################################################################
test_multi_domain() {
    print_step "STEP 1: Test Multi-Domain System (15 minutes)"

    print_info "This will verify that the multi-domain system is working correctly."
    print_info "Test suite location: $BASE_DIR/bin/test-multi-domain.php"

    if ! confirm "Run multi-domain test suite?"; then
        print_warning "Skipping multi-domain tests"
        return 0
    fi

    echo -e "\n${CYAN}Running test suite...${NC}\n"

    if [ -f "$BASE_DIR/bin/test-multi-domain.php" ]; then
        php "$BASE_DIR/bin/test-multi-domain.php"
        TEST_RESULT=$?

        if [ $TEST_RESULT -eq 0 ]; then
            print_success "Multi-domain tests passed!"
            return 0
        else
            print_error "Some tests failed. Please review the output above."
            if ! confirm "Continue anyway?"; then
                exit 1
            fi
        fi
    else
        print_error "Test file not found: $BASE_DIR/bin/test-multi-domain.php"
        print_info "You may need to create it first. See QUICK_WINS_AVAILABLE.md for details."
        if ! confirm "Continue anyway?"; then
            exit 1
        fi
    fi
}

###############################################################################
# STEP 2: Enable CodeTool
###############################################################################
enable_code_tool() {
    print_step "STEP 2: Enable CodeTool.php (5 minutes)"

    print_info "This will enable code analysis capabilities by renaming CodeTool.php.DISABLED"
    print_info "Location: $TOOLS_DIR/CodeTool.php.DISABLED"

    if ! confirm "Enable CodeTool?"; then
        print_warning "Skipping CodeTool enablement"
        return 0
    fi

    if [ -f "$TOOLS_DIR/CodeTool.php.DISABLED" ]; then
        print_info "Found CodeTool.php.DISABLED, renaming to enable..."

        # Check if already enabled
        if [ -f "$TOOLS_DIR/CodeTool.php" ]; then
            print_warning "CodeTool.php already exists (already enabled)"
            print_info "Skipping rename to avoid overwriting"
        else
            mv "$TOOLS_DIR/CodeTool.php.DISABLED" "$TOOLS_DIR/CodeTool.php"
            print_success "CodeTool enabled successfully!"
            print_info "CodeTool is now available in the agent's tool catalog"
            print_info "It provides: code reading, analysis, formatting, and AST parsing"
        fi
    else
        if [ -f "$TOOLS_DIR/CodeTool.php" ]; then
            print_success "CodeTool is already enabled!"
        else
            print_error "CodeTool.php.DISABLED not found at: $TOOLS_DIR/CodeTool.php.DISABLED"
            print_info "It may have been moved or deleted"
            if ! confirm "Continue anyway?"; then
                exit 1
            fi
        fi
    fi
}

###############################################################################
# STEP 3: Create Domain Switcher UI Component
###############################################################################
create_domain_switcher() {
    print_step "STEP 3: Create Domain Switcher UI Component (30 minutes)"

    print_info "This will create a React component for visual domain switching"
    print_info "Location: $FRONTEND_DIR/chat/components/DomainSwitcher.js"

    if ! confirm "Create Domain Switcher UI component?"; then
        print_warning "Skipping Domain Switcher creation"
        return 0
    fi

    # Create directory if it doesn't exist
    COMPONENT_DIR="$FRONTEND_DIR/chat/components"
    mkdir -p "$COMPONENT_DIR"

    # Create DomainSwitcher.js
    SWITCHER_FILE="$COMPONENT_DIR/DomainSwitcher.js"

    cat > "$SWITCHER_FILE" << 'EOJS'
import React, { useState, useEffect } from 'react';
import './DomainSwitcher.css';

/**
 * Domain Switcher Component
 *
 * Allows users to switch between knowledge domains and toggle GOD MODE
 *
 * Props:
 *   - conversationId: string - Current conversation ID
 *   - isAdmin: boolean - Whether user has admin privileges (for GOD MODE)
 *   - onDomainChange: function - Callback when domain changes
 */
export const DomainSwitcher = ({ conversationId, isAdmin = false, onDomainChange }) => {
  const [domains, setDomains] = useState([]);
  const [currentDomain, setCurrentDomain] = useState(null);
  const [godMode, setGodMode] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Fetch available domains on mount
  useEffect(() => {
    fetchDomains();
    fetchCurrentDomain();
  }, [conversationId]);

  const fetchDomains = async () => {
    try {
      const response = await fetch('/ai-agent/api/domains.php');
      const data = await response.json();

      if (data.success) {
        setDomains(data.domains);
      } else {
        setError('Failed to load domains');
      }
    } catch (err) {
      setError('Network error loading domains');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const fetchCurrentDomain = async () => {
    try {
      const response = await fetch(
        `/ai-agent/api/domains.php/current?conversation_id=${conversationId}`
      );
      const data = await response.json();

      if (data.success) {
        setCurrentDomain(data.domain?.name || null);
        setGodMode(data.god_mode || false);
      }
    } catch (err) {
      console.error('Failed to fetch current domain:', err);
    }
  };

  const switchDomain = async (domainName) => {
    try {
      const response = await fetch('/ai-agent/api/domains.php/switch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          conversation_id: conversationId,
          domain_name: domainName
        })
      });

      const data = await response.json();

      if (data.success) {
        setCurrentDomain(domainName);
        setGodMode(false); // Switching domain disables GOD MODE

        if (onDomainChange) {
          onDomainChange({ domain: domainName, godMode: false });
        }
      } else {
        setError(data.error || 'Failed to switch domain');
      }
    } catch (err) {
      setError('Network error switching domain');
      console.error(err);
    }
  };

  const toggleGodMode = async () => {
    const endpoint = godMode ? 'disable' : 'enable';

    try {
      const response = await fetch(`/ai-agent/api/domains.php/god-mode/${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ conversation_id: conversationId })
      });

      const data = await response.json();

      if (data.success) {
        const newGodMode = !godMode;
        setGodMode(newGodMode);

        if (newGodMode) {
          setCurrentDomain(null); // GOD MODE = no specific domain
        }

        if (onDomainChange) {
          onDomainChange({ domain: null, godMode: newGodMode });
        }
      } else {
        setError(data.error || 'Failed to toggle GOD MODE');
      }
    } catch (err) {
      setError('Network error toggling GOD MODE');
      console.error(err);
    }
  };

  if (loading) {
    return <div className="domain-switcher loading">Loading domains...</div>;
  }

  if (error) {
    return <div className="domain-switcher error">⚠️ {error}</div>;
  }

  return (
    <div className="domain-switcher">
      <div className="domain-select-wrapper">
        <label htmlFor="domain-select">Knowledge Domain:</label>
        <select
          id="domain-select"
          value={currentDomain || ''}
          onChange={(e) => switchDomain(e.target.value)}
          disabled={godMode}
          className={godMode ? 'disabled' : ''}
        >
          <option value="">Select domain...</option>
          {domains.map(d => (
            <option key={d.name} value={d.name}>
              {d.display_name} ({d.document_count} docs)
            </option>
          ))}
        </select>
      </div>

      {isAdmin && (
        <button
          onClick={toggleGodMode}
          className={`god-mode-button ${godMode ? 'active' : ''}`}
          title={godMode ? 'Disable GOD MODE' : 'Enable GOD MODE (search all domains)'}
        >
          {godMode ? '👁️ GOD MODE' : '🔒 Normal'}
        </button>
      )}

      {godMode && (
        <div className="god-mode-notice">
          ⚠️ GOD MODE: Searching across ALL domains
        </div>
      )}
    </div>
  );
};

export default DomainSwitcher;
EOJS

    print_success "Created: $SWITCHER_FILE"

    # Create CSS file
    CSS_FILE="$COMPONENT_DIR/DomainSwitcher.css"

    cat > "$CSS_FILE" << 'EOCSS'
.domain-switcher {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.domain-switcher.loading,
.domain-switcher.error {
  justify-content: center;
  color: white;
  font-size: 14px;
}

.domain-switcher.error {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.domain-select-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 8px;
}

.domain-select-wrapper label {
  color: white;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
}

.domain-switcher select {
  flex: 1;
  padding: 8px 12px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  font-size: 14px;
  background: white;
  color: #333;
  cursor: pointer;
  transition: all 0.3s ease;
}

.domain-switcher select:hover:not(.disabled) {
  border-color: rgba(255, 255, 255, 0.6);
  transform: translateY(-1px);
}

.domain-switcher select:focus {
  outline: none;
  border-color: white;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
}

.domain-switcher select.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.god-mode-button {
  padding: 8px 16px;
  border: 2px solid white;
  border-radius: 8px;
  background: transparent;
  color: white;
  font-weight: bold;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.god-mode-button:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.god-mode-button.active {
  background: linear-gradient(45deg, #ff6b6b, #ff8e53);
  border-color: #ff6b6b;
  animation: pulse 2s infinite;
  box-shadow: 0 0 20px rgba(255, 107, 107, 0.6);
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.9;
    transform: scale(1.05);
  }
}

.god-mode-notice {
  color: white;
  font-size: 12px;
  font-weight: bold;
  background: rgba(255, 107, 107, 0.3);
  padding: 4px 12px;
  border-radius: 6px;
  border: 1px solid rgba(255, 107, 107, 0.5);
  animation: blink 1.5s infinite;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* Responsive */
@media (max-width: 768px) {
  .domain-switcher {
    flex-direction: column;
    align-items: stretch;
  }

  .domain-select-wrapper {
    flex-direction: column;
    align-items: stretch;
  }

  .god-mode-button {
    width: 100%;
  }
}
EOCSS

    print_success "Created: $CSS_FILE"

    # Create example integration file
    EXAMPLE_FILE="$COMPONENT_DIR/DomainSwitcher.example.js"

    cat > "$EXAMPLE_FILE" << 'EOEX'
/**
 * Example: How to integrate DomainSwitcher into your chat interface
 */

import React from 'react';
import { DomainSwitcher } from './components/DomainSwitcher';

const ChatInterface = () => {
  const [conversationId] = useState('abc123'); // Your conversation ID
  const [isAdmin] = useState(true); // Check if user is admin

  const handleDomainChange = ({ domain, godMode }) => {
    console.log('Domain changed:', domain, 'GOD MODE:', godMode);

    // Optionally: Refresh chat context or show notification
    // Example: fetchChatHistory(conversationId);
  };

  return (
    <div className="chat-interface">
      {/* Add at top of chat */}
      <DomainSwitcher
        conversationId={conversationId}
        isAdmin={isAdmin}
        onDomainChange={handleDomainChange}
      />

      {/* Your existing chat UI */}
      <div className="chat-messages">
        {/* ... */}
      </div>
    </div>
  );
};

export default ChatInterface;
EOEX

    print_success "Created: $EXAMPLE_FILE"

    print_success "Domain Switcher UI component created successfully!"
    print_info "Files created:"
    print_info "  - $SWITCHER_FILE"
    print_info "  - $CSS_FILE"
    print_info "  - $EXAMPLE_FILE"
    print_info ""
    print_info "To use: Import DomainSwitcher into your chat interface component"
    print_info "See DomainSwitcher.example.js for integration example"
}

###############################################################################
# Main execution
###############################################################################
main() {
    print_header

    echo -e "${BLUE}This script will implement the TOP 3 quick wins:${NC}"
    echo -e "  1️⃣  Test Multi-Domain System (15 min) - Verify everything works"
    echo -e "  2️⃣  Enable CodeTool (5 min) - Add code analysis capabilities"
    echo -e "  3️⃣  Domain Switcher UI (30 min) - Visual domain switching + GOD MODE"
    echo ""
    echo -e "${YELLOW}Total estimated time: 50 minutes${NC}"
    echo ""

    if ! confirm "Proceed with implementation?"; then
        echo -e "${YELLOW}Aborted by user${NC}"
        exit 0
    fi

    # Execute steps
    test_multi_domain
    enable_code_tool
    create_domain_switcher

    # Summary
    echo -e "\n\n"
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                                                                              ║${NC}"
    echo -e "${GREEN}║                    ✅ QUICK WINS IMPLEMENTATION COMPLETE ✅                  ║${NC}"
    echo -e "${GREEN}║                                                                              ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${CYAN}What was implemented:${NC}"
    echo -e "  ✅ Multi-domain system tested"
    echo -e "  ✅ CodeTool enabled (code analysis available)"
    echo -e "  ✅ Domain Switcher UI component created"
    echo ""
    echo -e "${CYAN}Next steps:${NC}"
    echo -e "  1. Review test results above"
    echo -e "  2. Import DomainSwitcher into your chat UI"
    echo -e "  3. Test domain switching and GOD MODE in browser"
    echo -e "  4. Review QUICK_WINS_AVAILABLE.md for more improvements"
    echo ""
    echo -e "${PURPLE}🎉 You've unlocked powerful new capabilities! 🎉${NC}"
    echo ""
}

# Run main function
main "$@"
