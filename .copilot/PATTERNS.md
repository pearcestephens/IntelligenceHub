# Discovered Code Patterns

**Source:** Analyzed from actual codebase
**Date:** 2025-10-30

## Api endpoint

### JSON API endpoint
- **Quality:** good
- **Found in:** `ai-agent/backend.php`

### JSON API endpoint
- **Quality:** good
- **Found in:** `ai-agent/cis-project-management.php`

### JSON API endpoint
- **Quality:** good
- **Found in:** `ai-agent/api/bot-info.php`

### JSON API endpoint
- **Quality:** good
- **Found in:** `ai-agent/api/chat-v2.php`

### JSON API endpoint
- **Quality:** good
- **Found in:** `ai-agent/api/chat.php`

## Database

### PDO prepared statement
- **Quality:** good
- **Found in:** `ai-agent/ops/deployment-manager.php`
- **Example:** `INSERT INTO migrations (migration) VALUES (?)`

### PDO prepared statement
- **Quality:** good
- **Found in:** `api/save_conversation.php`
- **Example:** `SELECT conversation_id FROM ai_conversations WHERE session_id = ?`

### PDO prepared statement
- **Quality:** good
- **Found in:** `api/save_conversation.php`
- **Example:** `DELETE FROM ai_conversation_messages WHERE conversation_id = ?`

### PDO prepared statement
- **Quality:** good
- **Found in:** `api/save_conversation.php`
- **Example:** `DELETE FROM ai_conversation_topics WHERE conversation_id = ?`

### PDO prepared statement
- **Quality:** good
- **Found in:** `api/satellite-deploy.php`
- **Example:** `SELECT * FROM multi_bot_sessions WHERE session_id = ?`

## Form handler

### POST request handler
- **Quality:** good
- **Found in:** `api/satellite-deploy.php`

### POST request handler
- **Quality:** good
- **Found in:** `dashboard/api/sse-proxy.php`

### POST request handler
- **Quality:** good
- **Found in:** `dashboard/api/sse-proxy-HARDENED.php`

### POST request handler
- **Quality:** good
- **Found in:** `dashboard/_archived/test-scripts/test-login.php`

### POST request handler
- **Quality:** good
- **Found in:** `dashboard/_archived/test-scripts/test-login-simple.php`

