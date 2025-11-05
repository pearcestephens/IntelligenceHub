#!/usr/bin/env node

/**
 * 🚀 ULTIMATE MCP STDIO SERVER - GitHub Copilot Integration
 *
 * This is the STDIO interface that GitHub Copilot connects to directly.
 * Routes ALL requests to our HTTP backend with auto-logging.
 *
 * @version 3.0.0
 * @date 2025-11-05
 */

const https = require('https');
const http = require('http');
const readline = require('readline');

// =====================================================================
// CONFIGURATION
// =====================================================================

const CONFIG = {
  MCP_SERVER_URL: process.env.MCP_SERVER_URL || 'https://gpt.ecigdis.co.nz/mcp/server_v3.php',
  MCP_API_KEY: process.env.MCP_API_KEY || '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35',
  PROJECT_ID: process.env.PROJECT_ID || '1',
  BUSINESS_UNIT_ID: process.env.BUSINESS_UNIT_ID || '1',
  WORKSPACE_ROOT: process.env.WORKSPACE_ROOT || process.cwd(),
  AUTO_LOG: process.env.AUTO_LOG !== 'false',
  DEBUG: process.env.DEBUG === 'true'
};

// Session tracking
let sessionId = `gh-copilot-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
let messageCount = 0;
let currentFile = null;

// =====================================================================
// LOGGING
// =====================================================================

function debug(...args) {
  if (CONFIG.DEBUG) {
    console.error('[MCP-DEBUG]', ...args);
  }
}

function log(...args) {
  console.error('[MCP-INFO]', ...args);
}

function error(...args) {
  console.error('[MCP-ERROR]', ...args);
}

// =====================================================================
// HTTP REQUEST TO BACKEND
// =====================================================================

function makeHTTPRequest(endpoint, data) {
  return new Promise((resolve, reject) => {
    const url = new URL(endpoint);

    const postData = JSON.stringify(data);

    const options = {
      hostname: url.hostname,
      port: url.port || (url.protocol === 'https:' ? 443 : 80),
      path: url.pathname + url.search,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(postData),
        'Authorization': `Bearer ${CONFIG.MCP_API_KEY}`,
        'X-API-Key': CONFIG.MCP_API_KEY,
        'X-Workspace-Root': CONFIG.WORKSPACE_ROOT,
        'X-Current-File': currentFile || '',
        'X-Project-ID': CONFIG.PROJECT_ID,
        'X-Business-Unit-ID': CONFIG.BUSINESS_UNIT_ID,
        'X-Session-Id': sessionId,
        'X-Auto-Log': CONFIG.AUTO_LOG ? 'true' : 'false',
        'X-Source': 'github-copilot-stdio'
      }
    };

    const lib = url.protocol === 'https:' ? https : http;

    debug('HTTP Request:', {
      url: endpoint,
      method: 'POST',
      headers: options.headers,
      body: postData.substring(0, 500)
    });

    const req = lib.request(options, (res) => {
      let body = '';

      res.on('data', (chunk) => {
        body += chunk.toString();
      });

      res.on('end', () => {
        debug('HTTP Response:', {
          status: res.statusCode,
          body: body.substring(0, 500)
        });

        try {
          const response = JSON.parse(body);
          resolve(response);
        } catch (e) {
          error('Failed to parse response:', e.message, body.substring(0, 200));
          reject(new Error(`Invalid JSON response: ${e.message}`));
        }
      });
    });

    req.on('error', (e) => {
      error('HTTP Request failed:', e.message);
      reject(e);
    });

    req.write(postData);
    req.end();
  });
}

// =====================================================================
// MCP PROTOCOL HANDLERS
// =====================================================================

async function handleInitialize(id, params) {
  log('🚀 Initializing MCP Server...');
  log(`   Session: ${sessionId}`);
  log(`   Workspace: ${CONFIG.WORKSPACE_ROOT}`);
  log(`   Project: ${CONFIG.PROJECT_ID}, Unit: ${CONFIG.BUSINESS_UNIT_ID}`);

  return {
    jsonrpc: '2.0',
    id: id,
    result: {
      protocolVersion: '2024-11-05',
      capabilities: {
        tools: {
          listChanged: true
        },
        logging: {},
        prompts: {},
        resources: {}
      },
      serverInfo: {
        name: 'IntelligenceHub MCP Master',
        version: '3.0.0'
      }
    }
  };
}

async function handleToolsList(id, params) {
  try {
    debug('Fetching tools list from backend...');

    const response = await makeHTTPRequest(
      `${CONFIG.MCP_SERVER_URL}?action=meta`,
      {}
    );

    const tools = response.tools || [];

    // Convert to MCP format (input_schema)
    const mcpTools = tools.map(tool => ({
      name: tool.name,
      description: tool.description || '',
      inputSchema: tool.inputSchema || tool.input_schema || {
        type: 'object',
        properties: {},
        required: []
      }
    }));

    log(`📋 Loaded ${mcpTools.length} tools`);

    return {
      jsonrpc: '2.0',
      id: id,
      result: {
        tools: mcpTools
      }
    };
  } catch (e) {
    error('Failed to load tools:', e.message);
    return {
      jsonrpc: '2.0',
      id: id,
      error: {
        code: -32603,
        message: `Failed to load tools: ${e.message}`
      }
    };
  }
}

async function handleToolCall(id, params) {
  try {
    const toolName = params.name;
    const args = params.arguments || {};

    messageCount++;

    log(`🔧 Tool Call #${messageCount}: ${toolName}`);
    debug('   Arguments:', JSON.stringify(args).substring(0, 200));

    // Update current file if provided
    if (args.file || args.path || args.file_path) {
      currentFile = args.file || args.path || args.file_path;
    }

    // Call backend via JSON-RPC
    const response = await makeHTTPRequest(
      `${CONFIG.MCP_SERVER_URL}?action=rpc`,
      {
        jsonrpc: '2.0',
        id: Date.now(),
        method: 'tools/call',
        params: {
          name: toolName,
          arguments: args
        }
      }
    );

    if (response.error) {
      error('Tool execution error:', response.error);
      return {
        jsonrpc: '2.0',
        id: id,
        error: {
          code: response.error.code || -32603,
          message: response.error.message || 'Tool execution failed',
          data: response.error.data
        }
      };
    }

    const result = response.result || response;

    debug('Tool result:', JSON.stringify(result).substring(0, 300));

    // Format for MCP protocol
    return {
      jsonrpc: '2.0',
      id: id,
      result: {
        content: [
          {
            type: 'text',
            text: typeof result === 'string' ? result : JSON.stringify(result, null, 2)
          }
        ]
      }
    };
  } catch (e) {
    error('Tool call failed:', e.message);
    return {
      jsonrpc: '2.0',
      id: id,
      error: {
        code: -32603,
        message: `Tool execution failed: ${e.message}`
      }
    };
  }
}

async function handleRequest(message) {
  try {
    const request = JSON.parse(message);

    debug('Received request:', {
      method: request.method,
      id: request.id
    });

    const { jsonrpc, id, method, params } = request;

    if (jsonrpc !== '2.0') {
      return {
        jsonrpc: '2.0',
        id: id,
        error: {
          code: -32600,
          message: 'Invalid JSON-RPC version'
        }
      };
    }

    switch (method) {
      case 'initialize':
        return await handleInitialize(id, params);

      case 'initialized':
        // Notification, no response needed
        log('✅ Client initialized');
        return null;

      case 'tools/list':
        return await handleToolsList(id, params);

      case 'tools/call':
        return await handleToolCall(id, params);

      case 'ping':
        return {
          jsonrpc: '2.0',
          id: id,
          result: {}
        };

      default:
        log(`⚠️  Unknown method: ${method}`);
        return {
          jsonrpc: '2.0',
          id: id,
          error: {
            code: -32601,
            message: `Method not found: ${method}`
          }
        };
    }
  } catch (e) {
    error('Request handling error:', e.message);
    return {
      jsonrpc: '2.0',
      id: null,
      error: {
        code: -32700,
        message: `Parse error: ${e.message}`
      }
    };
  }
}

// =====================================================================
// STDIO MAIN LOOP
// =====================================================================

async function main() {
  log('');
  log('╔════════════════════════════════════════════════════════════════╗');
  log('║  🚀 INTELLIGENCE HUB MCP STDIO SERVER v3.0.0                 ║');
  log('╚════════════════════════════════════════════════════════════════╝');
  log('');
  log('📡 Backend URL:', CONFIG.MCP_SERVER_URL);
  log('🔑 API Key:', CONFIG.MCP_API_KEY.substring(0, 10) + '...');
  log('📁 Workspace:', CONFIG.WORKSPACE_ROOT);
  log('🎯 Session ID:', sessionId);
  log('📝 Auto-logging:', CONFIG.AUTO_LOG ? 'ENABLED ✅' : 'DISABLED');
  log('');
  log('🎧 Listening for JSON-RPC messages on STDIN...');
  log('');

  const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout,
    terminal: false
  });

  rl.on('line', async (line) => {
    if (!line.trim()) {
      return;
    }

    try {
      const response = await handleRequest(line);

      if (response) {
        const output = JSON.stringify(response);
        console.log(output);
        debug('Sent response:', output.substring(0, 200));
      }
    } catch (e) {
      error('Fatal error processing request:', e);
      const errorResponse = {
        jsonrpc: '2.0',
        id: null,
        error: {
          code: -32603,
          message: `Internal error: ${e.message}`
        }
      };
      console.log(JSON.stringify(errorResponse));
    }
  });

  rl.on('close', () => {
    log('');
    log('👋 Connection closed');
    log(`📊 Session stats: ${messageCount} tool calls processed`);
    process.exit(0);
  });
}

// Error handlers
process.on('uncaughtException', (err) => {
  error('Uncaught exception:', err);
  process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  error('Unhandled rejection:', reason);
  process.exit(1);
});

// Start server
main().catch((err) => {
  error('Fatal startup error:', err);
  process.exit(1);
});
