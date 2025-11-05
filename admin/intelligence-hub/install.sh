#!/bin/bash

# Intelligence Hub - Quick Installation Script
# Run this to set up Phase 1A

echo "================================================"
echo "  Intelligence Hub - Phase 1A Installation"
echo "================================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo -e "${YELLOW}Step 1:${NC} Creating directories..."
mkdir -p logs
mkdir -p config
mkdir -p src/{AI,Agents,Config,Data,Services}
chmod 755 logs/
echo -e "${GREEN}✓${NC} Directories created"

echo ""
echo -e "${YELLOW}Step 2:${NC} Checking .env configuration..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${YELLOW}⚠${NC}  Created .env from template"
        echo -e "    ${RED}IMPORTANT:${NC} Edit .env and add your API keys:"
        echo "    - OPENAI_API_KEY (required for AI Brain)"
        echo "    - VEND_API_URL and VEND_API_TOKEN (required for inventory)"
        echo "    - Database credentials"
    else
        echo -e "${RED}✗${NC} .env.example not found!"
        exit 1
    fi
else
    echo -e "${GREEN}✓${NC} .env file exists"
fi

echo ""
echo -e "${YELLOW}Step 3:${NC} Checking database connection..."
if [ -f ".env" ]; then
    # Source .env to get DB credentials
    export $(cat .env | grep -v '^#' | xargs)

    # Test MySQL connection
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} Database connection successful"
    else
        echo -e "${RED}✗${NC} Database connection failed"
        echo "    Please check your .env database credentials"
        exit 1
    fi
fi

echo ""
echo -e "${YELLOW}Step 4:${NC} Running database migration..."
if [ -f "migrations/001_intelligence_hub_core.sql" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < migrations/001_intelligence_hub_core.sql

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} Database tables created successfully"

        # Count tables created
        TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE 'agents'" 2>/dev/null | wc -l)
        if [ $TABLE_COUNT -gt 0 ]; then
            echo "    - Created 11 tables"
            echo "    - Inserted 3 default agents"
            echo "    - Inserted 3 automation rules"
            echo "    - Inserted 5 dashboard widgets"
        fi
    else
        echo -e "${RED}✗${NC} Migration failed"
        exit 1
    fi
else
    echo -e "${RED}✗${NC} Migration file not found"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 5:${NC} Verifying file permissions..."
chmod 644 .env
chmod 755 config/
chmod 644 config/*.php 2>/dev/null
chmod 755 api/
chmod 644 api/*.php 2>/dev/null
chmod 755 assets/js/
chmod 644 assets/js/*.js 2>/dev/null
echo -e "${GREEN}✓${NC} Permissions set"

echo ""
echo -e "${YELLOW}Step 6:${NC} Testing API keys..."

# Check OpenAI API key
if [ ! -z "$OPENAI_API_KEY" ] && [ "$OPENAI_API_KEY" != "sk-your-openai-api-key-here" ]; then
    echo -e "${GREEN}✓${NC} OpenAI API key configured"
else
    echo -e "${YELLOW}⚠${NC}  OpenAI API key not configured"
    echo "    AI Brain will not work without this"
fi

# Check Vend API credentials
if [ ! -z "$VEND_API_URL" ] && [ ! -z "$VEND_API_TOKEN" ]; then
    echo -e "${GREEN}✓${NC} Vend API credentials configured"
else
    echo -e "${YELLOW}⚠${NC}  Vend API credentials not configured"
    echo "    Inventory features will not work without this"
fi

echo ""
echo "================================================"
echo -e "${GREEN}  Installation Complete!${NC}"
echo "================================================"
echo ""
echo "Next Steps:"
echo ""
echo "1. ${YELLOW}Edit .env file${NC} with your API keys:"
echo "   nano .env"
echo ""
echo "2. ${YELLOW}Access the dashboard:${NC}"
echo "   https://your-domain.com/admin/intelligence-hub/dashboard.php"
echo ""
echo "3. ${YELLOW}Test the AI:${NC}"
echo "   Type in the command box: 'What needs my attention?'"
echo ""
echo "4. ${YELLOW}Read the documentation:${NC}"
echo "   cat PHASE_1A_COMPLETE.md"
echo ""
echo "================================================"
echo ""
echo "For support or to proceed with Phase 1B (Inventory Agent),"
echo "contact your development team."
echo ""
