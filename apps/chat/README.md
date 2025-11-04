# On-Server Chat App

This is a lightweight chat UI that calls the existing AI Agent API at `/assets/services/ai-agent/api/chat.php`.

## Configure

Create a `.env` in either location (both are auto-loaded):

- `/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/.env` (public root)
- `/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env` (preferred)

Recommended keys:

```
# Point the UI at the API base (default already matches this app)
CHAT_API_BASE=/assets/services/ai-agent/api

# Enforce API key check at chat.php (optional)
AI_AGENT_REQUIRE_AUTH=0

# If AI_AGENT_REQUIRE_AUTH=1, add a domain row in ai_agent_domains (DB) and set this only for local testing
# AI_AGENT_TEST_KEY=your-local-test-key

# Provider secrets - stored privately via Cloudways or private_html/.env
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...

# Optional defaults
AI_DEFAULT_BOT=gpt
AI_DEFAULT_UNIT_ID=1
AI_DEFAULT_PROJECT_ID=1
```

## Use

- Visit https://gpt.ecigdis.co.nz/apps/chat/ in your browser
- Type a message; choose provider/model if desired
- If auth is enforced by `chat.php`, paste a valid API key into the Settings panel (Authorization: Bearer)

## Notes

- The app does not store any secrets. It reads a test key only for convenience when present in `.env`.
- Telemetry and conversation logs are handled by the server API; this UI is just a client.
- CSS/JS kept minimal (<25KB) and loads Bootstrap from CDN.
