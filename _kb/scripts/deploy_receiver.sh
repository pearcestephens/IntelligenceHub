#!/bin/bash
# Intelligence Receiver Deployment Script
# Deploy this on ANY satellite application to receive intelligence from Hub
#
# Usage: bash deploy_receiver.sh [application_root]
#
# Example: bash deploy_receiver.sh /home/master/applications/jcepnzzkmj/public_html

set -e  # Exit on error

echo "════════════════════════════════════════════════════════════"
echo "   Intelligence Receiver Deployment"
echo "   Deploy KB intelligence receiver on satellite application"
echo "════════════════════════════════════════════════════════════"
echo ""

# Get application root
if [ -n "$1" ]; then
    APP_ROOT="$1"
else
    read -p "Enter application root path: " APP_ROOT
fi

# Validate path
if [ ! -d "$APP_ROOT" ]; then
    echo "❌ ERROR: Directory does not exist: $APP_ROOT"
    exit 1
fi

echo "📂 Target: $APP_ROOT"
echo ""

# Create directories
echo "📁 Creating directory structure..."
mkdir -p "$APP_ROOT/api/kb"
mkdir -p "$APP_ROOT/_kb/intelligence"
mkdir -p "$APP_ROOT/_kb/logs"
mkdir -p "$APP_ROOT/_kb/config"

# Copy receiver file
echo "📄 Installing receiver endpoint..."
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cp "$SCRIPT_DIR/../api/intelligence_receiver.php" "$APP_ROOT/api/kb/receive.php"

# Generate API key
echo "🔑 Generating API key..."
API_KEY=$(openssl rand -hex 32)
echo "$API_KEY" > "$APP_ROOT/_kb/config/api_key.txt"
chmod 600 "$APP_ROOT/_kb/config/api_key.txt"

echo "   API Key: $API_KEY"
echo "   Stored in: _kb/config/api_key.txt"
echo ""

# Create .htaccess for API endpoint (if Apache)
echo "🔒 Creating .htaccess for API security..."
cat > "$APP_ROOT/api/kb/.htaccess" <<'EOF'
# Intelligence Receiver API - Security
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Route /health to receiver
    RewriteRule ^health$ receive.php [L,QSA]
    
    # Route /status to receiver
    RewriteRule ^status$ receive.php [L,QSA]
    
    # Route /receive to receiver
    RewriteRule ^receive$ receive.php [L,QSA]
</IfModule>

# Deny access to sensitive files
<FilesMatch "\.(txt|log|json|bak)$">
    Require all denied
</FilesMatch>
EOF

# Create health check script
echo "🏥 Creating health check script..."
cat > "$APP_ROOT/_kb/scripts/check_receiver_health.sh" <<'EOF'
#!/bin/bash
# Check if intelligence receiver is healthy

API_KEY=$(cat ../_kb/config/api_key.txt 2>/dev/null || echo "")
URL="http://localhost/api/kb/health"

if [ -z "$API_KEY" ]; then
    echo "❌ ERROR: API key not found"
    exit 1
fi

RESPONSE=$(curl -s -H "X-API-Key: $API_KEY" "$URL")

if echo "$RESPONSE" | grep -q '"status":"healthy"'; then
    echo "✅ Receiver is healthy"
    exit 0
else
    echo "❌ Receiver is not healthy"
    echo "$RESPONSE"
    exit 1
fi
EOF

chmod +x "$APP_ROOT/_kb/scripts/check_receiver_health.sh"

# Create README
echo "📝 Creating deployment README..."
cat > "$APP_ROOT/_kb/RECEIVER_README.md" <<EOF
# Intelligence Receiver - Deployment Info

**Deployed:** $(date)  
**Application:** $APP_ROOT

## Endpoints

### Health Check
\`\`\`
GET /api/kb/health
X-API-Key: $API_KEY
\`\`\`

### Receive Intelligence
\`\`\`
POST /api/kb/receive
X-API-Key: $API_KEY
Content-Type: application/json
\`\`\`

### Status
\`\`\`
GET /api/kb/status
X-API-Key: $API_KEY
\`\`\`

## Configuration

**API Key Location:** \`_kb/config/api_key.txt\`  
**Storage Location:** \`_kb/intelligence/\`  
**Logs Location:** \`_kb/logs/receiver.log\`

## Testing

Test health:
\`\`\`bash
curl -H "X-API-Key: $API_KEY" https://your-domain.com/api/kb/health
\`\`\`

## Register with Intelligence Hub

Add this satellite to Intelligence Hub configuration:

\`\`\`json
{
  "id": "your_app_id",
  "name": "Your Application Name",
  "url": "https://your-domain.com/api/kb/receive",
  "api_key": "$API_KEY",
  "enabled": true,
  "priority": 1
}
\`\`\`

## Security Notes

- API key is stored in \`_kb/config/api_key.txt\` with 600 permissions
- Never commit API keys to git
- Use HTTPS in production
- Rotate API key periodically

## Maintenance

Check logs:
\`\`\`bash
tail -f _kb/logs/receiver.log
\`\`\`

View received intelligence:
\`\`\`bash
ls -lh _kb/intelligence/
\`\`\`

Check last receive:
\`\`\`bash
cat _kb/intelligence/last_receive.json
\`\`\`
EOF

# Create test script
echo "🧪 Creating test script..."
cat > "$APP_ROOT/_kb/scripts/test_receiver.sh" <<EOF
#!/bin/bash
# Test intelligence receiver

echo "Testing Intelligence Receiver..."
echo ""

API_KEY="$API_KEY"
BASE_URL="http://localhost/api/kb"

echo "1. Testing health endpoint..."
HEALTH=\$(curl -s -H "X-API-Key: \$API_KEY" "\$BASE_URL/health")
echo "\$HEALTH" | python3 -m json.tool
echo ""

echo "2. Testing status endpoint..."
STATUS=\$(curl -s -H "X-API-Key: \$API_KEY" "\$BASE_URL/status")
echo "\$STATUS" | python3 -m json.tool
echo ""

echo "3. Testing receive endpoint with sample data..."
curl -X POST "\$BASE_URL/receive" \\
  -H "Content-Type: application/json" \\
  -H "X-API-Key: \$API_KEY" \\
  -d '{
    "type": "intelligence_update",
    "timestamp": '"\$(date +%s)"',
    "source": "intelligence_hub",
    "data": {
      "summary": {
        "test": true,
        "message": "Test intelligence push"
      }
    },
    "checksum": "'"\$(echo -n '{"summary":{"test":true,"message":"Test intelligence push"}}' | md5sum | cut -d' ' -f1)"'"
  }'
echo ""
echo ""

echo "4. Checking stored files..."
ls -lh _kb/intelligence/
EOF

chmod +x "$APP_ROOT/_kb/scripts/test_receiver.sh"

# Summary
echo ""
echo "════════════════════════════════════════════════════════════"
echo "✅ DEPLOYMENT COMPLETE!"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "📍 Deployed to: $APP_ROOT"
echo ""
echo "🔗 Endpoints:"
echo "   Health:  /api/kb/health"
echo "   Receive: /api/kb/receive"
echo "   Status:  /api/kb/status"
echo ""
echo "🔑 API Key: $API_KEY"
echo "   (Stored securely in: _kb/config/api_key.txt)"
echo ""
echo "📝 Next Steps:"
echo "   1. Test the receiver:"
echo "      cd $APP_ROOT"
echo "      bash _kb/scripts/test_receiver.sh"
echo ""
echo "   2. Register with Intelligence Hub:"
echo "      Add this satellite to: _kb/config/satellites.json"
echo "      Use the API key above"
echo ""
echo "   3. Enable HTTPS for production"
echo ""
echo "   4. Set up monitoring:"
echo "      tail -f $APP_ROOT/_kb/logs/receiver.log"
echo ""
echo "📖 Documentation: $APP_ROOT/_kb/RECEIVER_README.md"
echo ""
echo "════════════════════════════════════════════════════════════"
