# Dashboard ↔ MCP Hub Integration Guide

**Domain:** `gpt.ecigdis.co.nz`

## Overview

Your dashboard is now configured to connect with the **MCP Intelligence Hub** at `gpt.ecigdis.co.nz` for:

- 🔍 Semantic search across codebases
- 🧠 AI-powered code analysis
- 📊 Real-time intelligence updates
- 🔐 Security scanning
- ⚡ Performance profiling

## Configuration

### Main Config File
```php
/dashboard/admin/config/mcp-hub.php
```

**Connection Details:**
- **Domain:** `gpt.ecigdis.co.nz`
- **Protocol:** HTTPS (secure)
- **Base URL:** `https://gpt.ecigdis.co.nz`

## Using MCP Hub Client

### 1. Health Check

Check if MCP Hub is reachable:

```php
<?php
require_once __DIR__ . '/config/mcp-hub.php';

$hub = mcpHub();
$health = $hub->healthCheck();

echo $health['success'] ? 'Connected ✓' : 'Offline ✗';
echo "Latency: " . $health['latency'] . "ms";
```

### 2. Semantic Search

Search across the knowledge base:

```php
$results = $hub->semanticSearch('how do we handle transfers', [
    'limit' => 20,
]);

if ($results['success']) {
    $data = $results['data'];
    // Process search results
}
```

### 3. Code Analysis

Analyze code for quality/security:

```php
$analysis = $hub->analyzeCode($phpCode, [
    'language' => 'php',
    'strict' => true,
]);

if ($analysis['success']) {
    $violations = $analysis['data']['violations'] ?? [];
}
```

### 4. Get Intelligence

Pull pre-computed intelligence:

```php
$intelligence = $hub->getIntelligence('performance', [
    'project_id' => 1,
]);
```

## API Endpoints

The MCP Hub provides these endpoints:

| Endpoint | Purpose | Example |
|----------|---------|---------|
| `/mcp/server_v2_complete.php` | Semantic search, code analysis | POST with JSON-RPC |
| `/mcp/analysis.php` | Deep code analysis | POST JSON request |
| `/mcp/intelligence.php` | Pre-computed insights | GET with parameters |
| `/health.php` | Health check | GET request |

## Dashboard Integration Points

### Overview Page
- Fetch high-level intelligence from hub
- Display project health from MCP data
- Show trend analysis

### Files Page
- Semantic search for files
- AI-powered file suggestions
- Cross-reference analysis

### Violations Page
- Pull violations from hub analysis
- Real-time security scanning
- Severity ranking

### Metrics Page
- Performance data from hub
- Trend analysis
- Comparison with standards

### Dependencies Page
- Deep dependency analysis
- Circular dependency detection
- Cross-module impact analysis

## Example Implementation

### Add MCP Hub Status to Dashboard

File: `/dashboard/admin/pages/overview.php`

```php
<?php
// At the top of the file
require_once __DIR__ . '/../config/mcp-hub.php';

// Check hub connection
$hub = mcpHub();
$hubHealth = $hub->healthCheck();
$hubStatus = $hubHealth['success'] ? 'online' : 'offline';
?>

<!-- In the dashboard HTML -->
<div class="card">
    <div class="card-header">
        <h6>Intelligence Hub Status</h6>
    </div>
    <div class="card-body">
        <p>
            <strong>MCP Hub:</strong>
            <span class="badge bg-<?php echo $hubStatus === 'online' ? 'success' : 'danger'; ?>">
                <?php echo ucfirst($hubStatus); ?>
            </span>
        </p>
        <p>
            <strong>Domain:</strong> gpt.ecigdis.co.nz
        </p>
        <p>
            <strong>Latency:</strong> <?php echo $hubHealth['latency']; ?>ms
        </p>
    </div>
</div>
```

## JavaScript Integration

Access MCP Hub from JavaScript:

```javascript
// Add to your API module
const MCP = {
    domain: 'gpt.ecigdis.co.nz',
    baseUrl: 'https://gpt.ecigdis.co.nz',

    search: function(query, callback) {
        const payload = {
            jsonrpc: '2.0',
            method: 'tools/call',
            params: {
                name: 'semantic_search',
                arguments: { query: query, limit: 10 }
            },
            id: Math.random()
        };

        fetch(this.baseUrl + '/mcp/server_v2_complete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(callback)
        .catch(err => console.error(err));
    }
};

// Usage
MCP.search('code quality issues', (results) => {
    console.log(results);
});
```

## Security Considerations

✅ **HTTPS Only** - All communication is encrypted
✅ **SSL Verified** - Certificate validation enabled
✅ **Timeout Protection** - 30s request timeout
✅ **Error Handling** - Graceful fallbacks
✅ **No Credentials** - No secrets in MCP config

## Troubleshooting

### Hub Unreachable

```bash
curl -I https://gpt.ecigdis.co.nz/health.php
```

Should return: `HTTP/1.1 200 OK`

### Slow Requests

- Check network latency to hub
- Verify MCP Hub is under load
- Implement caching for repeated queries
- Consider queuing long-running requests

### SSL Certificate Errors

```php
// In mcp-hub.php, temporarily disable SSL verification (dev only)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
```

## Performance Tips

1. **Cache Results** - Store API responses locally
2. **Batch Requests** - Group multiple queries
3. **Async Loading** - Don't block on hub requests
4. **Health Monitoring** - Periodically check hub status
5. **Fallback UI** - Show alternative when hub is offline

## Next Steps

1. ✅ Configuration file created (`config/mcp-hub.php`)
2. ⏭️ Implement health check on overview page
3. ⏭️ Add semantic search to files page
4. ⏭️ Integrate code analysis for violations
5. ⏭️ Pull intelligence for metrics
6. ⏭️ Add MCP Hub admin settings panel

## Support

For MCP Hub API documentation, visit:
```
https://gpt.ecigdis.co.nz
```

---

**Last Updated:** October 30, 2025
**Version:** 1.0.0
**Domain:** gpt.ecigdis.co.nz
