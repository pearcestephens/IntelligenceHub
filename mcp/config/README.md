# MCP Configuration Guide

## Setup Instructions

### 1. VS Code Workspace (Per-Project)
Copy this file to your workspace:
```bash
cp mcp.json.example /path/to/your/workspace/.vscode/mcp.json
```

### 2. Global VS Code Settings (All Projects)
```bash
mkdir -p ~/.vscode
cp mcp.json.example ~/.vscode/mcp.json
```

### 3. Set Environment Variable
Add to your shell profile (~/.bashrc or ~/.zshrc):
```bash
export INTELLIGENCE_HUB_API_KEY="31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35"
```

Or create a `.env` file in your workspace:
```bash
INTELLIGENCE_HUB_API_KEY=31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35
```

## Configuration Details

- **Server**: https://gpt.ecigdis.co.nz/mcp/server_v3.php
- **Version**: 3.0.0
- **Tools Available**: 50+
- **RAG Database**: 8,645 indexed files
- **Features**: Semantic search, database queries, file operations, git integration

## VS Code Settings (settings.json)

Also add this to `.vscode/settings.json`:
```json
{
  "github.copilot.chat.codeGeneration.useInstructionFiles": true,
  "github.copilot.chat.scopeSelection": true,
  "github.copilot.chat.temporalContext.enabled": true,
  "github.copilot.chat.useProjectTemplates": true,
  "github.copilot.enable": {
    "*": true,
    "plaintext": false,
    "markdown": true,
    "scminput": false
  },
  "chat.experimental.openAICompatible.enabled": true,
  "chat.experimental.openAICompatible.endpoint": "https://gpt.ecigdis.co.nz/api/v1/chat/completions",
  "chat.experimental.openAICompatible.apiKey": "31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35",
  "chat.experimental.openAICompatible.model": "gpt-5-turbo",
  "chat.experimental.openAICompatible.conversationHistory": true
}
```

## Testing

Test the MCP connection:
```bash
curl -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php \
  -H "Authorization: Bearer 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":{}}'
```

Expected response: List of 50+ available tools

## Troubleshooting

1. **Connection Failed**: Check API key and network connectivity
2. **Tools Not Loading**: Restart VS Code after configuration changes
3. **Timeout Errors**: Increase timeout in mcp.json (default: 30000ms)
4. **Authorization Failed**: Verify API key is correct and active

## Support

- Dashboard: https://gpt.ecigdis.co.nz/admin/bot-deployment-center.html
- AI Control Panel: https://gpt.ecigdis.co.nz
- Documentation: https://www.wiki.vapeshed.co.nz
