#!/usr/bin/env node

/**
 * MCP Server Wrapper for GitHub Copilot
 *
 * This wrapper automatically injects project context and conversation history
 * into all MCP tool calls, ensuring continuous memory across sessions.
 *
 * @version 1.0.0
 * @date 2025-11-04
 */

const https = require('https');
const http = require('http');

// Configuration from environment
const MCP_SERVER_URL = process.env.MCP_SERVER_URL || 'https://gpt.ecigdis.co.nz/mcp/server_v3.php';
const MCP_API_KEY = process.env.MCP_API_KEY || '';
const PROJECT_ID = process.env.PROJECT_ID || '2';
const BUSINESS_UNIT_ID = process.env.BUSINESS_UNIT_ID || '2';
const WORKSPACE_ROOT = process.env.WORKSPACE_ROOT || process.cwd();
const ENABLE_CONVERSATION_CONTEXT = process.env.ENABLE_CONVERSATION_CONTEXT === 'true';
const AUTO_RETRIEVE_CONTEXT = process.env.AUTO_RETRIEVE_CONTEXT === 'true';
const CONTEXT_LIMIT = parseInt(process.env.CONTEXT_LIMIT || '10', 10);

// Track current file from Copilot
let currentFile = null;

/**
 * Make HTTP request to MCP server
 * Supports chunked responses to prevent summarization
 */
function makeRequest(method, data, streamCallback = null) {
  return new Promise((resolve, reject) => {
    const url = new URL(MCP_SERVER_URL);

    const postData = JSON.stringify({
      jsonrpc: '2.0',
      method: method,
      params: data,
      id: Date.now()
    });

    const options = {
      hostname: url.hostname,
      port: url.port || (url.protocol === 'https:' ? 443 : 80),
      path: url.pathname + url.search,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(postData),
        'X-API-Key': MCP_API_KEY,
        'X-Workspace-Root': WORKSPACE_ROOT,
        'X-Current-File': currentFile || '',
        'X-Project-ID': PROJECT_ID,
        'X-Business-Unit-ID': BUSINESS_UNIT_ID,
        'X-Enable-Chunking': streamCallback ? 'true' : 'false'
      }
    };

    const lib = url.protocol === 'https:' ? https : http;

    const req = lib.request(options, (res) => {
      let body = '';

      // Handle chunked responses if callback provided
      if (streamCallback) {
        res.on('data', (chunk) => {
          const chunkStr = chunk.toString();
          body += chunkStr;

          // Try to parse and stream partial results
          try {
            const lines = chunkStr.split('\n').filter(l => l.trim());
            for (const line of lines) {
              if (line.startsWith('data: ')) {
                const data = JSON.parse(line.substring(6));
                streamCallback(data);
              }
            }
          } catch (e) {
            // Not SSE format, accumulate normally
          }
        });
      } else {
        res.on('data', (chunk) => body += chunk);
      }

      res.on('end', () => {
        try {
          const response = JSON.parse(body);
          resolve(response);
        } catch (e) {
          reject(new Error(`Failed to parse response: ${e.message}`));
        }
      });
    });

    req.on('error', reject);
    req.write(postData);
    req.end();
  });
}

/**
 * Get conversation context for current project
 */
async function getConversationContext() {
  if (!ENABLE_CONVERSATION_CONTEXT || !AUTO_RETRIEVE_CONTEXT) {
    return null;
  }

  try {
    const response = await makeRequest('tools/call', {
      name: 'conversation.get_project_context',
      arguments: {
        project_id: parseInt(PROJECT_ID, 10),
        limit: CONTEXT_LIMIT
      }
    });

    if (response.result && response.result.status === 200) {
      return response.result.data;
    }
  } catch (e) {
    console.error('[MCP Wrapper] Failed to retrieve context:', e.message);
  }

  return null;
}

/**
 * Process incoming message from GitHub Copilot
 */
async function processMessage(message) {
  try {
    const parsed = JSON.parse(message);

    // Update current file if provided
    if (parsed.params && parsed.params.currentFile) {
      currentFile = parsed.params.currentFile;
    }

    // Handle different message types
    switch (parsed.method) {
      case 'initialize':
        return {
          jsonrpc: '2.0',
          id: parsed.id,
          result: {
            protocolVersion: '2024-11-05',
            capabilities: {
              tools: {}
            },
            serverInfo: {
              name: 'intelligence-hub',
              version: '3.0.0'
            }
          }
        };

      case 'tools/list':
        // Get available tools from server
        const toolsResponse = await makeRequest('tools/list', {});

        // Inject conversation context tools prominently
        if (toolsResponse.result) {
          const tools = toolsResponse.result.tools || [];

          // Ensure conversation tools are at the top
          const conversationTools = tools.filter(t => t.name.startsWith('conversation.'));
          const otherTools = tools.filter(t => !t.name.startsWith('conversation.'));

          return {
            jsonrpc: '2.0',
            id: parsed.id,
            result: {
              tools: [...conversationTools, ...otherTools]
            }
          };
        }
        break;

      case 'tools/call':
        const { name, arguments: args } = parsed.params;

        // Auto-inject conversation context for non-conversation tools
        let enhancedArgs = { ...args };

        if (AUTO_RETRIEVE_CONTEXT && !name.startsWith('conversation.')) {
          const context = await getConversationContext();
          if (context) {
            enhancedArgs._conversation_context = context;
            enhancedArgs._project_id = PROJECT_ID;
            enhancedArgs._business_unit_id = BUSINESS_UNIT_ID;
          }
        }

        // Forward to actual MCP server with enhanced args
        const callResponse = await makeRequest('tools/call', {
          name: name,
          arguments: enhancedArgs
        });

        return {
          jsonrpc: '2.0',
          id: parsed.id,
          result: callResponse.result || callResponse
        };

      default:
        // Forward unknown methods directly
        const response = await makeRequest(parsed.method, parsed.params);
        return {
          jsonrpc: '2.0',
          id: parsed.id,
          result: response.result || response
        };
    }
  } catch (error) {
    return {
      jsonrpc: '2.0',
      id: parsed?.id || null,
      error: {
        code: -32603,
        message: error.message
      }
    };
  }
}

/**
 * Main stdio loop
 */
async function main() {
  console.error('[MCP Wrapper] Starting Intelligence Hub MCP Server Wrapper');
  console.error(`[MCP Wrapper] Server: ${MCP_SERVER_URL}`);
  console.error(`[MCP Wrapper] Project: ${PROJECT_ID} (Unit: ${BUSINESS_UNIT_ID})`);
  console.error(`[MCP Wrapper] Conversation Context: ${ENABLE_CONVERSATION_CONTEXT ? 'Enabled' : 'Disabled'}`);
  console.error(`[MCP Wrapper] Auto-Retrieve: ${AUTO_RETRIEVE_CONTEXT ? 'Yes' : 'No'}`);

  let buffer = '';

  process.stdin.on('data', async (chunk) => {
    buffer += chunk.toString();

    // Process complete messages (one per line)
    const lines = buffer.split('\n');
    buffer = lines.pop() || ''; // Keep incomplete line in buffer

    for (const line of lines) {
      if (!line.trim()) continue;

      const response = await processMessage(line);
      process.stdout.write(JSON.stringify(response) + '\n');
    }
  });

  process.stdin.on('end', () => {
    console.error('[MCP Wrapper] Connection closed');
    process.exit(0);
  });
}

// Handle errors
process.on('uncaughtException', (error) => {
  console.error('[MCP Wrapper] Uncaught exception:', error);
  process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('[MCP Wrapper] Unhandled rejection:', reason);
  process.exit(1);
});

// Start the wrapper
main();
