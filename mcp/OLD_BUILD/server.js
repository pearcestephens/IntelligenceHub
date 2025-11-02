#!/usr/bin/env node
/**
 * CIS Intelligence - Model Context Protocol Server
 * 
 * Provides GitHub Copilot with direct access to KB system
 * 
 * Features:
 * - Full KB search capabilities
 * - File correlation and dependency tracking
 * - Function/class lookup
 * - Real-time code indexing
 * - AI-learned pattern access
 * 
 * Protocol: JSON-RPC 2.0 over stdio
 * Standard: Model Context Protocol (MCP)
 * 
 * @package CIS\MCP
 * @version 2.0.0
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListResourcesRequestSchema,
  ListToolsRequestSchema,
  ReadResourceRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import mysql from 'mysql2/promise';
import Redis from 'ioredis';

// =============================================================================
// CONFIGURATION
// =============================================================================

const config = {
  db: {
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'hdgwrzntwa',
    password: process.env.DB_PASS || 'bFUdRjh4Jx',
    database: process.env.DB_NAME || 'hdgwrzntwa',
  },
  redis: {
    host: process.env.REDIS_HOST || '127.0.0.1',
    port: parseInt(process.env.REDIS_PORT || '6379'),
  },
  limits: {
    maxResults: 100,
    cacheTTL: 300, // 5 minutes
  }
};

// =============================================================================
// DATABASE & CACHE CONNECTIONS
// =============================================================================

let dbPool;
let redis;

async function initConnections() {
  // MySQL connection pool
  dbPool = mysql.createPool({
    ...config.db,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
  });

  // Redis connection
  redis = new Redis({
    host: config.redis.host,
    port: config.redis.port,
    retryStrategy: (times) => Math.min(times * 50, 2000),
  });

  console.error('[MCP] Connections initialized');
}

// =============================================================================
// MCP SERVER SETUP
// =============================================================================

const server = new Server(
  {
    name: 'cis-intelligence',
    version: '2.0.0',
  },
  {
    capabilities: {
      tools: {},
      resources: {},
    },
  }
);

// =============================================================================
// TOOLS IMPLEMENTATION
// =============================================================================

server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: 'kb_search',
        description: 'Search the knowledge base for files, functions, classes, or code patterns. Supports filters: type:php, folder:assets, size:>1000, etc.',
        inputSchema: {
          type: 'object',
          properties: {
            query: {
              type: 'string',
              description: 'Search query with optional filters (e.g., "type:php function:process")',
            },
            limit: {
              type: 'number',
              description: 'Maximum results to return (default: 20)',
              default: 20,
            },
          },
          required: ['query'],
        },
      },
      {
        name: 'kb_get_file',
        description: 'Get complete details about a specific file including content, metadata, and relationships',
        inputSchema: {
          type: 'object',
          properties: {
            path: {
              type: 'string',
              description: 'File path relative to project root',
            },
          },
          required: ['path'],
        },
      },
      {
        name: 'kb_correlate',
        description: 'Find all files related to a given file (includes, imports, dependencies, usage)',
        inputSchema: {
          type: 'object',
          properties: {
            file: {
              type: 'string',
              description: 'File path to analyze',
            },
          },
          required: ['file'],
        },
      },
      {
        name: 'kb_function_lookup',
        description: 'Find function definition and all places where it is called',
        inputSchema: {
          type: 'object',
          properties: {
            name: {
              type: 'string',
              description: 'Function name to look up',
            },
          },
          required: ['name'],
        },
      },
      {
        name: 'kb_class_lookup',
        description: 'Find class definition, methods, and usage throughout codebase',
        inputSchema: {
          type: 'object',
          properties: {
            name: {
              type: 'string',
              description: 'Class name to look up',
            },
          },
          required: ['name'],
        },
      },
      {
        name: 'kb_recent_changes',
        description: 'Get files that changed recently (useful for detecting breaking changes)',
        inputSchema: {
          type: 'object',
          properties: {
            hours: {
              type: 'number',
              description: 'Look back this many hours (default: 24)',
              default: 24,
            },
          },
        },
      },
      {
        name: 'kb_code_examples',
        description: 'Get working code examples for a specific technology or pattern from the codebase',
        inputSchema: {
          type: 'object',
          properties: {
            technology: {
              type: 'string',
              description: 'Technology or pattern to find examples of (e.g., "mysqli prepared statement")',
            },
          },
          required: ['technology'],
        },
      },
      {
        name: 'kb_dependencies',
        description: 'Get full dependency tree for a file',
        inputSchema: {
          type: 'object',
          properties: {
            file: {
              type: 'string',
              description: 'File path to analyze',
            },
          },
          required: ['file'],
        },
      },
    ],
  };
});

server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    switch (name) {
      case 'kb_search':
        return await handleKBSearch(args.query, args.limit || 20);
      
      case 'kb_get_file':
        return await handleGetFile(args.path);
      
      case 'kb_correlate':
        return await handleCorrelate(args.file);
      
      case 'kb_function_lookup':
        return await handleFunctionLookup(args.name);
      
      case 'kb_class_lookup':
        return await handleClassLookup(args.name);
      
      case 'kb_recent_changes':
        return await handleRecentChanges(args.hours || 24);
      
      case 'kb_code_examples':
        return await handleCodeExamples(args.technology);
      
      case 'kb_dependencies':
        return await handleDependencies(args.file);
      
      default:
        throw new Error(`Unknown tool: ${name}`);
    }
  } catch (error) {
    return {
      content: [
        {
          type: 'text',
          text: `Error: ${error.message}`,
        },
      ],
      isError: true,
    };
  }
});

// =============================================================================
// TOOL HANDLERS
// =============================================================================

async function handleKBSearch(query, limit) {
  const cacheKey = `kb:search:${query}:${limit}`;
  
  // Check cache
  const cached = await redis.get(cacheKey);
  if (cached) {
    return {
      content: [
        {
          type: 'text',
          text: cached,
        },
      ],
    };
  }

  // Parse query filters
  const filters = parseSearchQuery(query);
  
  // Build SQL query
  let sql = 'SELECT file_id, file_path, file_name, file_type, line_count, file_size_bytes FROM ecig_kb_files WHERE is_deleted = 0';
  const params = [];
  
  if (filters.type) {
    sql += ' AND file_type = ?';
    params.push(filters.type);
  }
  
  if (filters.folder) {
    sql += ' AND file_path LIKE ?';
    params.push(`${filters.folder}%`);
  }
  
  if (filters.text) {
    sql += ' AND (file_name LIKE ? OR content_preview LIKE ?)';
    params.push(`%${filters.text}%`, `%${filters.text}%`);
  }
  
  if (filters.minSize) {
    sql += ' AND file_size_bytes >= ?';
    params.push(filters.minSize);
  }
  
  sql += ' ORDER BY file_size_bytes DESC LIMIT ?';
  params.push(limit);
  
  const [rows] = await dbPool.query(sql, params);
  
  const result = JSON.stringify({
    query: query,
    count: rows.length,
    results: rows,
  }, null, 2);
  
  // Cache result
  await redis.setex(cacheKey, config.limits.cacheTTL, result);
  
  return {
    content: [
      {
        type: 'text',
        text: result,
      },
    ],
  };
}

async function handleGetFile(path) {
  const cacheKey = `kb:file:${path}`;
  
  const cached = await redis.get(cacheKey);
  if (cached) {
    return { content: [{ type: 'text', text: cached }] };
  }

  // Get file details
  const [files] = await dbPool.query(
    'SELECT * FROM ecig_kb_files WHERE file_path LIKE ? AND is_deleted = 0 LIMIT 1',
    [`%${path}%`]
  );
  
  if (files.length === 0) {
    throw new Error(`File not found: ${path}`);
  }
  
  const file = files[0];
  
  // Get correlations
  const [correlations] = await dbPool.query(
    'SELECT correlation_type, target_path, target_name FROM ecig_kb_file_correlations WHERE source_file_id = ?',
    [file.file_id]
  );
  
  const result = JSON.stringify({
    file: {
      path: file.file_path,
      name: file.file_name,
      type: file.file_type,
      size: file.file_size_bytes,
      lines: file.line_count,
      language: file.language,
      hash: file.file_hash,
      preview: file.content_preview,
    },
    correlations: correlations,
    updated_at: file.updated_at,
  }, null, 2);
  
  await redis.setex(cacheKey, config.limits.cacheTTL, result);
  
  return { content: [{ type: 'text', text: result }] };
}

async function handleCorrelate(filePath) {
  const [files] = await dbPool.query(
    'SELECT file_id FROM ecig_kb_files WHERE file_path LIKE ? AND is_deleted = 0 LIMIT 1',
    [`%${filePath}%`]
  );
  
  if (files.length === 0) {
    throw new Error(`File not found: ${filePath}`);
  }
  
  const fileId = files[0].file_id;
  
  // Get all correlations
  const [correlations] = await dbPool.query(`
    SELECT 
      c.correlation_type,
      c.target_path,
      c.target_name,
      f.file_name as target_file_name,
      f.file_path as target_file_path
    FROM ecig_kb_file_correlations c
    LEFT JOIN ecig_kb_files f ON c.target_file_id = f.file_id
    WHERE c.source_file_id = ?
  `, [fileId]);
  
  // Group by type
  const grouped = {};
  for (const corr of correlations) {
    if (!grouped[corr.correlation_type]) {
      grouped[corr.correlation_type] = [];
    }
    grouped[corr.correlation_type].push({
      target: corr.target_file_name || corr.target_path || corr.target_name,
      path: corr.target_file_path,
    });
  }
  
  const result = JSON.stringify({
    file: filePath,
    correlations: grouped,
    total: correlations.length,
  }, null, 2);
  
  return { content: [{ type: 'text', text: result }] };
}

async function handleFunctionLookup(functionName) {
  const cacheKey = `kb:function:${functionName}`;
  
  const cached = await redis.get(cacheKey);
  if (cached) {
    return { content: [{ type: 'text', text: cached }] };
  }

  // Search for function in correlations
  const [definitions] = await dbPool.query(`
    SELECT DISTINCT
      f.file_path,
      f.file_name,
      f.line_count,
      c.target_name
    FROM ecig_kb_file_correlations c
    JOIN ecig_kb_files f ON c.source_file_id = f.file_id
    WHERE c.correlation_type = 'calls_function' 
    AND c.target_name LIKE ?
    AND f.is_deleted = 0
  `, [`%${functionName}%`]);
  
  const result = JSON.stringify({
    function: functionName,
    found_in: definitions.length,
    call_sites: definitions.map(d => ({
      file: d.file_path,
      name: d.file_name,
    })),
  }, null, 2);
  
  await redis.setex(cacheKey, config.limits.cacheTTL, result);
  
  return { content: [{ type: 'text', text: result }] };
}

async function handleClassLookup(className) {
  // Similar to function lookup but for classes
  const [usage] = await dbPool.query(`
    SELECT DISTINCT
      f.file_path,
      f.file_name,
      c.correlation_type
    FROM ecig_kb_file_correlations c
    JOIN ecig_kb_files f ON c.source_file_id = f.file_id
    WHERE c.correlation_type IN ('uses_class', 'extends_class')
    AND c.target_name LIKE ?
    AND f.is_deleted = 0
  `, [`%${className}%`]);
  
  const result = JSON.stringify({
    class: className,
    usage_count: usage.length,
    used_in: usage,
  }, null, 2);
  
  return { content: [{ type: 'text', text: result }] };
}

async function handleRecentChanges(hours) {
  const [changes] = await dbPool.query(`
    SELECT file_path, file_name, file_type, updated_at
    FROM ecig_kb_files
    WHERE updated_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
    AND is_deleted = 0
    ORDER BY updated_at DESC
    LIMIT 50
  `, [hours]);
  
  const result = JSON.stringify({
    period: `${hours} hours`,
    changed_files: changes.length,
    files: changes,
  }, null, 2);
  
  return { content: [{ type: 'text', text: result }] };
}

async function handleCodeExamples(technology) {
  // Search for files containing the technology
  const [examples] = await dbPool.query(`
    SELECT file_path, file_name, file_type, line_count, content_preview
    FROM ecig_kb_files
    WHERE (content_preview LIKE ? OR file_name LIKE ?)
    AND is_deleted = 0
    ORDER BY line_count DESC
    LIMIT 10
  `, [`%${technology}%`, `%${technology}%`]);
  
  const result = JSON.stringify({
    technology: technology,
    examples_found: examples.length,
    examples: examples.map(ex => ({
      file: ex.file_path,
      type: ex.file_type,
      lines: ex.line_count,
      preview: ex.content_preview?.substring(0, 200),
    })),
  }, null, 2);
  
  return { content: [{ type: 'text', text: result }] };
}

async function handleDependencies(filePath) {
  // Recursive dependency resolution
  const deps = await resolveDependencies(filePath, new Set(), 0);
  
  const result = JSON.stringify({
    file: filePath,
    dependency_tree: deps,
  }, null, 2);
  
  return { content: [{ type: 'text', text: result }] };
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

function parseSearchQuery(query) {
  const filters = {
    text: query,
  };
  
  // Extract type: filter
  const typeMatch = query.match(/type:(\w+)/);
  if (typeMatch) {
    filters.type = typeMatch[1];
    filters.text = filters.text.replace(/type:\w+/, '').trim();
  }
  
  // Extract folder: filter
  const folderMatch = query.match(/folder:([\w\/]+)/);
  if (folderMatch) {
    filters.folder = folderMatch[1];
    filters.text = filters.text.replace(/folder:[\w\/]+/, '').trim();
  }
  
  // Extract size: filter
  const sizeMatch = query.match(/size:>(\d+)/);
  if (sizeMatch) {
    filters.minSize = parseInt(sizeMatch[1]);
    filters.text = filters.text.replace(/size:>\d+/, '').trim();
  }
  
  return filters;
}

async function resolveDependencies(filePath, visited, depth) {
  if (depth > 5 || visited.has(filePath)) {
    return null;
  }
  
  visited.add(filePath);
  
  const [files] = await dbPool.query(
    'SELECT file_id FROM ecig_kb_files WHERE file_path LIKE ? AND is_deleted = 0 LIMIT 1',
    [`%${filePath}%`]
  );
  
  if (files.length === 0) return null;
  
  const [deps] = await dbPool.query(`
    SELECT target_path
    FROM ecig_kb_file_correlations
    WHERE source_file_id = ? AND correlation_type IN ('includes', 'imports', 'requires')
  `, [files[0].file_id]);
  
  const children = [];
  for (const dep of deps) {
    if (dep.target_path) {
      const child = await resolveDependencies(dep.target_path, visited, depth + 1);
      if (child) children.push(child);
    }
  }
  
  return {
    file: filePath,
    dependencies: children,
  };
}

// =============================================================================
// RESOURCES IMPLEMENTATION
// =============================================================================

server.setRequestHandler(ListResourcesRequestSchema, async () => {
  return {
    resources: [
      {
        uri: 'kb://files/*',
        name: 'All indexed files',
        description: 'Access any file in the knowledge base',
        mimeType: 'application/json',
      },
      {
        uri: 'kb://functions/*',
        name: 'Function registry',
        description: 'All functions in the codebase',
        mimeType: 'application/json',
      },
      {
        uri: 'kb://correlations/*',
        name: 'File correlations',
        description: 'File relationships and dependencies',
        mimeType: 'application/json',
      },
    ],
  };
});

server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const uri = request.params.uri;
  
  if (uri.startsWith('kb://files/')) {
    const path = uri.replace('kb://files/', '');
    return await handleGetFile(path);
  }
  
  throw new Error(`Unknown resource URI: ${uri}`);
});

// =============================================================================
// START SERVER
// =============================================================================

async function main() {
  await initConnections();
  
  const transport = new StdioServerTransport();
  await server.connect(transport);
  
  console.error('[MCP] CIS Intelligence server started');
  console.error('[MCP] Database: Connected');
  console.error('[MCP] Redis: Connected');
  console.error('[MCP] Ready for Copilot requests');
}

main().catch((error) => {
  console.error('[MCP] Fatal error:', error);
  process.exit(1);
});
