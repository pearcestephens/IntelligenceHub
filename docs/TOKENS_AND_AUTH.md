---
title: Tokens and Auth Keys
last_updated: 2025-11-02
---

# Tokens and Auth Keys

This project uses two simple bearer-style secrets for admin monitoring and the MCP JSON-RPC API.

- ADMIN_SSE_TOKEN – protects the Server-Sent Events (SSE) admin stream at:
  - https://staff.vapeshed.co.nz/admin/traffic/live.php
- MCP_API_KEY – protects the MCP RPC endpoint at:
  - https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php?action=rpc
- INTELLIGENCE_HUB_API_KEY – developer-side variable used by VS Code to pass the Authorization header when connecting to the MCP server (you can set it to the same value as MCP_API_KEY).

Both are single-string tokens. Generate once, keep in your password vault, and set via environment variables.

## Quick Generate (secure random)

```bash
openssl rand -hex 32
```

Examples look like: `c7d0e4b7b2f84f2a9c8c2f5be0f9c3f49b1a0e7db2f040a9f0a64c57b9ad6b3e`

## Where to set these

Preferred: use Cloudways Application Settings → Environment Variables.

Set the following keys at the application scope (no quotes):

- MCP_API_KEY=<your-hex-key>
- ADMIN_SSE_TOKEN=<your-hex-key>

Optional for editor tooling (VS Code):

- INTELLIGENCE_HUB_API_KEY=<same-as-MCP_API_KEY>

Local shell session (temporary) for testing:

```bash
export MCP_API_KEY="<your-hex-key>"
export ADMIN_SSE_TOKEN="<your-hex-key>"
export INTELLIGENCE_HUB_API_KEY="$MCP_API_KEY"
```

## How each key is used

1) SSE Admin Stream

- Endpoint: https://staff.vapeshed.co.nz/admin/traffic/live.php
- Header: `X-Admin-Token: <ADMIN_SSE_TOKEN>`
- Alternate: `?token=<ADMIN_SSE_TOKEN>` query param (header preferred)

2) MCP JSON-RPC Server

- Base: https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php
- Public metadata (no auth): `?action=meta` and `?action=health`
- RPC requires key: `?action=rpc`
- Send key via header:
  - `Authorization: Bearer <MCP_API_KEY>` or
  - `X-API-Key: <MCP_API_KEY>`

VS Code integration (.vscode/settings.json) already includes:

```json
{
  "github.copilot.advanced": {
    "mcp": {
      "servers": [{
        "url": "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
        "headers": {
          "Authorization": "Bearer ${INTELLIGENCE_HUB_API_KEY}"
        }
      }]
    }
  }
}
```

Set INTELLIGENCE_HUB_API_KEY in your environment and VS Code will pass the header automatically.

## Verify quickly

1) Check MCP is reachable (public, no key):

```bash
curl -sS "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php?action=health" | jq .
```

2) Initialize MCP (requires key):

```bash
curl -sS -X POST \
  -H "Authorization: Bearer $MCP_API_KEY" \
  -H "Content-Type: application/json" \
  "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php?action=rpc" \
  -d '{"jsonrpc":"2.0","id":"init-1","method":"initialize","params":{}}' | jq .
```

3) Probe SSE (requires ADMIN_SSE_TOKEN). You can use the helper:

```bash
bash public_html/tools/verify/sse_probe.sh "$ADMIN_SSE_TOKEN"
```

Or directly:

```bash
curl -sS -N -H "X-Admin-Token: $ADMIN_SSE_TOKEN" \
  "https://staff.vapeshed.co.nz/admin/traffic/live.php" | sed -n '1,20p'
```

Expected behaviors:

- Missing/wrong SSE token → HTTP 401 JSON error.
- Rapid reconnects → HTTP 429 with Retry-After.
- MCP meta/health respond 200 without keys; RPC returns 401 without key.

## Notes

- Keep tokens out of repos. Use environment variables (Cloudways UI) or secure vaults.
- Rotate keys if exposed. Generate new, update Cloudways env vars, and test.
- For CI/automation, supply the same headers; avoid query-string tokens in URLs.
