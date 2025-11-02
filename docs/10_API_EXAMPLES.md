# 10_API_EXAMPLES.md

**AI Agent + MCP Server - API Examples**

Working code examples in Python, PHP, JavaScript, and cURL for all major endpoints.

---

## Table of Contents

1. [Python Examples](#python-examples)
2. [PHP Examples](#php-examples)
3. [JavaScript Examples](#javascript-examples)
4. [cURL Examples](#curl-examples)
5. [Full Integration Examples](#full-integration-examples)

---

## Python Examples

### Setup

```python
import requests
import json
from typing import Dict, Any, Optional

# Configuration
BASE_URL = "https://gpt.ecigdis.co.nz/assets/services/ai-agent"
MCP_API_KEY = "your_api_key_here"  # Optional, if authentication enabled

# Helper function for API requests
def make_request(
    endpoint: str,
    data: Dict[str, Any],
    api_key: Optional[str] = None
) -> Dict[str, Any]:
    """Make HTTP request to AI Agent API"""
    url = f"{BASE_URL}/{endpoint}"
    headers = {"Content-Type": "application/json"}

    if api_key:
        headers["Authorization"] = f"Bearer {api_key}"

    response = requests.post(url, json=data, headers=headers)
    response.raise_for_status()
    return response.json()
```

### Example 1: Chat with GPT-4o-mini

```python
def chat_with_gpt(message: str, session_key: str = "python-example") -> Dict[str, Any]:
    """Send message to GPT-4o-mini"""
    data = {
        "provider": "openai",
        "model": "gpt-4o-mini",
        "session_key": session_key,
        "message": message
    }

    try:
        response = make_request("api/chat.php", data)

        if response.get("success"):
            content = response["data"]["content"]
            tokens = response["data"]["tokens"]
            latency = response["data"]["latency_ms"]

            print(f"Response: {content}")
            print(f"Tokens: {tokens['total']} (in: {tokens['input']}, out: {tokens['output']})")
            print(f"Latency: {latency}ms")

            return response
        else:
            print(f"Error: {response.get('error', {}).get('message')}")
            return response

    except requests.exceptions.RequestException as e:
        print(f"Request failed: {e}")
        raise

# Usage
if __name__ == "__main__":
    result = chat_with_gpt("What is the capital of France?")
```

**Expected output:**
```
Response: The capital of France is Paris.
Tokens: 15 (in: 8, out: 7)
Latency: 1234ms
```

### Example 2: Streaming Chat

```python
import sseclient
import requests

def stream_chat(message: str, session_key: str = "python-stream") -> None:
    """Stream chat response with SSE"""
    url = f"{BASE_URL}/api/chat_stream.php"

    data = {
        "provider": "openai",
        "model": "gpt-4o-mini",
        "session_key": session_key,
        "message": message
    }

    headers = {"Content-Type": "application/json"}

    try:
        response = requests.post(url, json=data, headers=headers, stream=True)
        response.raise_for_status()

        client = sseclient.SSEClient(response)

        print("Streaming response:")
        for event in client.events():
            if event.data == "[DONE]":
                print("\n\nStream complete!")
                break

            try:
                data = json.loads(event.data)
                if "content" in data:
                    print(data["content"], end="", flush=True)
                elif "error" in data:
                    print(f"\nError: {data['error']}")
                    break
            except json.JSONDecodeError:
                continue

    except requests.exceptions.RequestException as e:
        print(f"Stream failed: {e}")
        raise

# Usage
if __name__ == "__main__":
    stream_chat("Write a haiku about Python programming")
```

**Expected output:**
```
Streaming response:
Code flows like water,
Functions dance in harmony,
Python's grace shines bright.

Stream complete!
```

### Example 3: Invoke Tool (fs.list)

```python
def list_files(path: str) -> Dict[str, Any]:
    """List files in directory using fs.list tool"""
    data = {
        "tool": "fs.list",
        "args": {
            "path": path
        }
    }

    try:
        response = make_request("api/tools/invoke.php", data)

        if response.get("success"):
            entries = response["data"]["result"]["entries"]
            print(f"Files in {path}:")
            for entry in entries:
                print(f"  - {entry}")
            return response
        else:
            print(f"Error: {response.get('error', {}).get('message')}")
            return response

    except requests.exceptions.RequestException as e:
        print(f"Request failed: {e}")
        raise

# Usage
if __name__ == "__main__":
    list_files("assets/services/ai-agent/api")
```

**Expected output:**
```
Files in assets/services/ai-agent/api:
  - chat.php
  - chat_stream.php
  - healthz.php
  - readyz.php
  - memory_upsert.php
```

### Example 4: Database Query (db.select)

```python
def query_conversations(limit: int = 10) -> Dict[str, Any]:
    """Query recent conversations"""
    data = {
        "tool": "db.select",
        "args": {
            "query": "SELECT id, session_key, provider, model, created_at FROM ai_conversations ORDER BY created_at DESC LIMIT ?",
            "params": [limit]
        }
    }

    try:
        response = make_request("api/tools/invoke.php", data)

        if response.get("success"):
            rows = response["data"]["result"]["rows"]
            print(f"Recent conversations ({len(rows)}):")
            for row in rows:
                print(f"  [{row['created_at']}] {row['session_key']} - {row['provider']}/{row['model']}")
            return response
        else:
            print(f"Error: {response.get('error', {}).get('message')}")
            return response

    except requests.exceptions.RequestException as e:
        print(f"Request failed: {e}")
        raise

# Usage
if __name__ == "__main__":
    query_conversations(5)
```

**Expected output:**
```
Recent conversations (5):
  [2025-11-02 12:34:56] python-example - openai/gpt-4o-mini
  [2025-11-02 12:30:15] test-session - openai/gpt-4o-mini
  [2025-11-02 11:45:22] user-123 - anthropic/claude-3-5-sonnet-20241022
  [2025-11-02 11:20:10] demo - openai/gpt-4o-mini
  [2025-11-02 10:55:30] api-test - openai/gpt-4o-mini
```

### Example 5: Memory Management

```python
def store_memory(key: str, value: Any, scope: str = "session") -> Dict[str, Any]:
    """Store value in memory"""
    data = {
        "action": "upsert",
        "key": key,
        "value": value,
        "scope": scope,
        "confidence": 100,
        "source": "python-example"
    }

    try:
        response = make_request("api/memory_upsert.php", data)

        if response.get("success"):
            print(f"Stored: {key} = {value}")
            return response
        else:
            print(f"Error: {response.get('error', {}).get('message')}")
            return response

    except requests.exceptions.RequestException as e:
        print(f"Request failed: {e}")
        raise

def retrieve_memory(key: str, scope: str = "session") -> Optional[Any]:
    """Retrieve value from memory"""
    data = {
        "action": "retrieve",
        "key": key,
        "scope": scope
    }

    try:
        response = make_request("api/memory_upsert.php", data)

        if response.get("success") and response["data"].get("found"):
            value = response["data"]["value"]
            print(f"Retrieved: {key} = {value}")
            return value
        else:
            print(f"Not found: {key}")
            return None

    except requests.exceptions.RequestException as e:
        print(f"Request failed: {e}")
        raise

# Usage
if __name__ == "__main__":
    # Store user preferences
    store_memory("user_language", "python", scope="session")
    store_memory("theme", "dark", scope="user")

    # Retrieve later
    language = retrieve_memory("user_language", scope="session")
    theme = retrieve_memory("theme", scope="user")
```

---

## PHP Examples

### Setup

```php
<?php

// Configuration
define('BASE_URL', 'https://gpt.ecigdis.co.nz/assets/services/ai-agent');
define('MCP_API_KEY', 'your_api_key_here'); // Optional

/**
 * Make HTTP request to AI Agent API
 */
function makeRequest(string $endpoint, array $data, ?string $apiKey = null): array
{
    $url = BASE_URL . '/' . $endpoint;

    $headers = ['Content-Type: application/json'];
    if ($apiKey) {
        $headers[] = 'Authorization: Bearer ' . $apiKey;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }

    curl_close($ch);

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error_msg());
    }

    return $result;
}
```

### Example 1: Chat with GPT-4o-mini

```php
<?php

/**
 * Send message to GPT-4o-mini
 */
function chatWithGPT(string $message, string $sessionKey = 'php-example'): array
{
    $data = [
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'session_key' => $sessionKey,
        'message' => $message
    ];

    try {
        $response = makeRequest('api/chat.php', $data);

        if ($response['success']) {
            $content = $response['data']['content'];
            $tokens = $response['data']['tokens'];
            $latency = $response['data']['latency_ms'];

            echo "Response: {$content}\n";
            echo "Tokens: {$tokens['total']} (in: {$tokens['input']}, out: {$tokens['output']})\n";
            echo "Latency: {$latency}ms\n";
        } else {
            echo "Error: " . $response['error']['message'] . "\n";
        }

        return $response;

    } catch (Exception $e) {
        echo "Request failed: " . $e->getMessage() . "\n";
        throw $e;
    }
}

// Usage
chatWithGPT("What is the capital of France?");
```

### Example 2: Invoke Tool (fs.read)

```php
<?php

/**
 * Read file contents using fs.read tool
 */
function readFile(string $path): array
{
    $data = [
        'tool' => 'fs.read',
        'args' => [
            'path' => $path
        ]
    ];

    try {
        $response = makeRequest('api/tools/invoke.php', $data);

        if ($response['success']) {
            $content = $response['data']['result']['content'];
            echo "File contents ({$path}):\n";
            echo substr($content, 0, 500) . "...\n"; // First 500 chars
        } else {
            echo "Error: " . $response['error']['message'] . "\n";
        }

        return $response;

    } catch (Exception $e) {
        echo "Request failed: " . $e->getMessage() . "\n";
        throw $e;
    }
}

// Usage
readFile("assets/services/ai-agent/api/chat.php");
```

### Example 3: Database Query

```php
<?php

/**
 * Query tool usage statistics
 */
function queryToolStats(): array
{
    $data = [
        'tool' => 'db.select',
        'args' => [
            'query' => 'SELECT tool_name, COUNT(*) as calls, AVG(latency_ms) as avg_latency FROM ai_tool_calls WHERE created_at > NOW() - INTERVAL 1 DAY GROUP BY tool_name ORDER BY calls DESC'
        ]
    ];

    try {
        $response = makeRequest('api/tools/invoke.php', $data);

        if ($response['success']) {
            $rows = $response['data']['result']['rows'];
            echo "Tool usage (last 24 hours):\n";
            foreach ($rows as $row) {
                printf("  %-15s: %4d calls, %.0fms avg\n",
                    $row['tool_name'],
                    $row['calls'],
                    $row['avg_latency']
                );
            }
        } else {
            echo "Error: " . $response['error']['message'] . "\n";
        }

        return $response;

    } catch (Exception $e) {
        echo "Request failed: " . $e->getMessage() . "\n";
        throw $e;
    }
}

// Usage
queryToolStats();
```

### Example 4: Health Check

```php
<?php

/**
 * Check system health
 */
function checkHealth(): array
{
    try {
        // Liveness check
        $liveness = makeRequest('api/healthz.php', []);
        echo "Liveness: " . ($liveness['alive'] ? 'OK' : 'FAIL') . "\n";

        // Readiness check
        $readiness = makeRequest('api/readyz.php', []);
        echo "Readiness: " . ($readiness['ready'] ? 'OK' : 'FAIL') . "\n";

        if (!$readiness['ready']) {
            echo "Issues:\n";
            foreach ($readiness['checks'] as $check => $status) {
                if (!$status) {
                    echo "  - {$check}: FAIL\n";
                }
            }
        }

        return [
            'liveness' => $liveness,
            'readiness' => $readiness
        ];

    } catch (Exception $e) {
        echo "Health check failed: " . $e->getMessage() . "\n";
        throw $e;
    }
}

// Usage
checkHealth();
```

---

## JavaScript Examples

### Setup (Node.js)

```javascript
const fetch = require('node-fetch');

// Configuration
const BASE_URL = 'https://gpt.ecigdis.co.nz/assets/services/ai-agent';
const MCP_API_KEY = 'your_api_key_here'; // Optional

/**
 * Make HTTP request to AI Agent API
 */
async function makeRequest(endpoint, data, apiKey = null) {
    const url = `${BASE_URL}/${endpoint}`;

    const headers = {
        'Content-Type': 'application/json'
    };

    if (apiKey) {
        headers['Authorization'] = `Bearer ${apiKey}`;
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();

    } catch (error) {
        console.error('Request failed:', error.message);
        throw error;
    }
}
```

### Example 1: Chat with GPT-4o-mini

```javascript
/**
 * Send message to GPT-4o-mini
 */
async function chatWithGPT(message, sessionKey = 'js-example') {
    const data = {
        provider: 'openai',
        model: 'gpt-4o-mini',
        session_key: sessionKey,
        message: message
    };

    try {
        const response = await makeRequest('api/chat.php', data);

        if (response.success) {
            const { content, tokens, latency_ms } = response.data;

            console.log(`Response: ${content}`);
            console.log(`Tokens: ${tokens.total} (in: ${tokens.input}, out: ${tokens.output})`);
            console.log(`Latency: ${latency_ms}ms`);

            return response;
        } else {
            console.error(`Error: ${response.error.message}`);
            return response;
        }

    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

// Usage
chatWithGPT("What is the capital of France?")
    .then(result => console.log('Done'))
    .catch(error => console.error('Failed:', error));
```

### Example 2: Streaming Chat (Browser)

```javascript
/**
 * Stream chat response with EventSource (browser only)
 */
function streamChat(message, sessionKey = 'browser-stream') {
    const url = `${BASE_URL}/api/chat_stream.php`;

    const data = {
        provider: 'openai',
        model: 'gpt-4o-mini',
        session_key: sessionKey,
        message: message
    };

    // POST data, then open EventSource to receive stream
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        // Open SSE connection
        const eventSource = new EventSource(url);

        console.log('Streaming response:');

        eventSource.onmessage = (event) => {
            if (event.data === '[DONE]') {
                console.log('\n\nStream complete!');
                eventSource.close();
                return;
            }

            try {
                const data = JSON.parse(event.data);
                if (data.content) {
                    process.stdout.write(data.content);
                } else if (data.error) {
                    console.error(`\nError: ${data.error}`);
                    eventSource.close();
                }
            } catch (error) {
                // Ignore parse errors
            }
        };

        eventSource.onerror = (error) => {
            console.error('Stream error:', error);
            eventSource.close();
        };
    })
    .catch(error => {
        console.error('Failed to start stream:', error);
    });
}

// Usage (in browser)
streamChat("Write a haiku about JavaScript");
```

### Example 3: Invoke Tool (fs.list)

```javascript
/**
 * List files in directory
 */
async function listFiles(path) {
    const data = {
        tool: 'fs.list',
        args: {
            path: path
        }
    };

    try {
        const response = await makeRequest('api/tools/invoke.php', data);

        if (response.success) {
            const entries = response.data.result.entries;
            console.log(`Files in ${path}:`);
            entries.forEach(entry => console.log(`  - ${entry}`));
            return response;
        } else {
            console.error(`Error: ${response.error.message}`);
            return response;
        }

    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

// Usage
listFiles('assets/services/ai-agent/api')
    .then(result => console.log('Done'))
    .catch(error => console.error('Failed:', error));
```

### Example 4: Memory Operations

```javascript
/**
 * Store value in memory
 */
async function storeMemory(key, value, scope = 'session') {
    const data = {
        action: 'upsert',
        key: key,
        value: value,
        scope: scope,
        confidence: 100,
        source: 'js-example'
    };

    try {
        const response = await makeRequest('api/memory_upsert.php', data);

        if (response.success) {
            console.log(`Stored: ${key} = ${JSON.stringify(value)}`);
            return response;
        } else {
            console.error(`Error: ${response.error.message}`);
            return response;
        }

    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

/**
 * Retrieve value from memory
 */
async function retrieveMemory(key, scope = 'session') {
    const data = {
        action: 'retrieve',
        key: key,
        scope: scope
    };

    try {
        const response = await makeRequest('api/memory_upsert.php', data);

        if (response.success && response.data.found) {
            const value = response.data.value;
            console.log(`Retrieved: ${key} = ${JSON.stringify(value)}`);
            return value;
        } else {
            console.log(`Not found: ${key}`);
            return null;
        }

    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

// Usage
async function memoryExample() {
    await storeMemory('user_preferences', { theme: 'dark', language: 'en' });
    const prefs = await retrieveMemory('user_preferences');
    console.log('User preferences:', prefs);
}

memoryExample();
```

---

## cURL Examples

### Example 1: Chat with GPT-4o-mini

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/chat.php \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "model": "gpt-4o-mini",
    "session_key": "curl-example",
    "message": "What is the capital of France?"
  }' | jq
```

### Example 2: Streaming Chat

```bash
# Start stream
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/chat_stream.php \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "model": "gpt-4o-mini",
    "session_key": "curl-stream",
    "message": "Write a haiku about bash scripting"
  }'

# Output will be SSE format:
# data: {"content": "Shell"}
# data: {"content": " scripts"}
# data: {"content": " whisper"}
# ...
# data: [DONE]
```

### Example 3: List Files

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "fs.list",
    "args": {
      "path": "assets/services/ai-agent/api"
    }
  }' | jq '.data.result.entries[]'
```

### Example 4: Read File

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "fs.read",
    "args": {
      "path": "assets/services/ai-agent/api/healthz.php"
    }
  }' | jq -r '.data.result.content'
```

### Example 5: Database Query

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "db.select",
    "args": {
      "query": "SELECT COUNT(*) as count FROM ai_conversations"
    }
  }' | jq '.data.result.rows[0].count'
```

### Example 6: Store Memory

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/memory_upsert.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "upsert",
    "key": "user_settings",
    "value": {"theme": "dark", "notifications": true},
    "scope": "user",
    "confidence": 100,
    "source": "curl-example"
  }' | jq
```

### Example 7: Retrieve Memory

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/memory_upsert.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "retrieve",
    "key": "user_settings",
    "scope": "user"
  }' | jq '.data.value'
```

### Example 8: Health Checks

```bash
# Liveness
curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php | jq

# Readiness
curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/readyz.php | jq
```

### Example 9: MCP Server Meta (List Tools)

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "meta",
    "id": 1
  }' | jq '.result.tools[].name'
```

### Example 10: With Authentication

```bash
# Set API key
API_KEY="your_64_character_hex_key_here"

# Make authenticated request
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_KEY" \
  -d '{
    "jsonrpc": "2.0",
    "method": "meta",
    "id": 1
  }' | jq
```

---

## Full Integration Examples

### Python: Complete Workflow

```python
"""
Complete AI Agent Integration Example

This example demonstrates:
1. Chat with AI
2. Tool invocation during conversation
3. Store results in memory
4. Query conversation history
"""

import requests
import json
from typing import Dict, Any

BASE_URL = "https://gpt.ecigdis.co.nz/assets/services/ai-agent"

class AIAgentClient:
    def __init__(self, session_key: str):
        self.session_key = session_key
        self.base_url = BASE_URL

    def _request(self, endpoint: str, data: Dict[str, Any]) -> Dict[str, Any]:
        """Make HTTP request"""
        url = f"{self.base_url}/{endpoint}"
        response = requests.post(url, json=data)
        response.raise_for_status()
        return response.json()

    def chat(self, message: str, provider: str = "openai", model: str = "gpt-4o-mini") -> str:
        """Send message and get response"""
        data = {
            "provider": provider,
            "model": model,
            "session_key": self.session_key,
            "message": message
        }

        result = self._request("api/chat.php", data)

        if result["success"]:
            return result["data"]["content"]
        else:
            raise Exception(result["error"]["message"])

    def invoke_tool(self, tool: str, args: Dict[str, Any]) -> Any:
        """Invoke a tool"""
        data = {
            "tool": tool,
            "args": args
        }

        result = self._request("api/tools/invoke.php", data)

        if result["success"]:
            return result["data"]["result"]
        else:
            raise Exception(result["error"]["message"])

    def store_memory(self, key: str, value: Any, scope: str = "session") -> None:
        """Store value in memory"""
        data = {
            "action": "upsert",
            "key": key,
            "value": value,
            "scope": scope,
            "confidence": 100,
            "source": "python-integration"
        }

        result = self._request("api/memory_upsert.php", data)

        if not result["success"]:
            raise Exception(result["error"]["message"])

    def get_conversation_history(self) -> list:
        """Get conversation messages"""
        query = """
            SELECT cm.role, cm.content, cm.created_at
            FROM ai_conversation_messages cm
            JOIN ai_conversations c ON cm.conversation_id = c.id
            WHERE c.session_key = ?
            ORDER BY cm.created_at ASC
        """

        result = self.invoke_tool("db.select", {
            "query": query,
            "params": [self.session_key]
        })

        return result["rows"]

# Complete workflow example
def main():
    # Initialize client
    client = AIAgentClient(session_key="integration-demo")

    print("=== AI Agent Integration Demo ===\n")

    # Step 1: Chat with AI
    print("Step 1: Chatting with AI...")
    response = client.chat("What files are in the api directory?")
    print(f"AI: {response}\n")

    # Step 2: Use tool to get actual file list
    print("Step 2: Invoking fs.list tool...")
    files = client.invoke_tool("fs.list", {"path": "assets/services/ai-agent/api"})
    print(f"Files found: {files['entries']}\n")

    # Step 3: Store results in memory
    print("Step 3: Storing results in memory...")
    client.store_memory("api_files", files['entries'], scope="session")
    print("Stored in memory\n")

    # Step 4: Get conversation history
    print("Step 4: Retrieving conversation history...")
    history = client.get_conversation_history()
    for msg in history:
        print(f"[{msg['created_at']}] {msg['role']}: {msg['content'][:50]}...")

    print("\n=== Demo Complete ===")

if __name__ == "__main__":
    main()
```

### JavaScript/Node.js: Error Handling Example

```javascript
/**
 * Complete AI Agent Client with Error Handling
 */

const fetch = require('node-fetch');

const BASE_URL = 'https://gpt.ecigdis.co.nz/assets/services/ai-agent';

class AIAgentClient {
    constructor(sessionKey) {
        this.sessionKey = sessionKey;
        this.baseUrl = BASE_URL;
    }

    async _request(endpoint, data) {
        const url = `${this.baseUrl}/${endpoint}`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error.message || 'Unknown error');
            }

            return result;

        } catch (error) {
            console.error(`Request to ${endpoint} failed:`, error.message);
            throw error;
        }
    }

    async chat(message, provider = 'openai', model = 'gpt-4o-mini') {
        const data = {
            provider,
            model,
            session_key: this.sessionKey,
            message
        };

        const result = await this._request('api/chat.php', data);
        return result.data.content;
    }

    async invokeTool(tool, args) {
        const data = { tool, args };
        const result = await this._request('api/tools/invoke.php', data);
        return result.data.result;
    }

    async storeMemory(key, value, scope = 'session') {
        const data = {
            action: 'upsert',
            key,
            value,
            scope,
            confidence: 100,
            source: 'js-integration'
        };

        await this._request('api/memory_upsert.php', data);
    }

    async retrieveMemory(key, scope = 'session') {
        const data = {
            action: 'retrieve',
            key,
            scope
        };

        const result = await this._request('api/memory_upsert.php', data);
        return result.data.found ? result.data.value : null;
    }
}

// Usage with error handling
async function main() {
    const client = new AIAgentClient('js-integration-demo');

    try {
        console.log('=== AI Agent Integration Demo ===\n');

        // Chat
        console.log('Chatting with AI...');
        const response = await client.chat('What is 2+2?');
        console.log(`AI: ${response}\n`);

        // Store result
        console.log('Storing in memory...');
        await client.storeMemory('math_result', { question: '2+2', answer: response });
        console.log('Stored\n');

        // Retrieve result
        console.log('Retrieving from memory...');
        const stored = await client.retrieveMemory('math_result');
        console.log('Retrieved:', JSON.stringify(stored, null, 2));

        console.log('\n=== Demo Complete ===');

    } catch (error) {
        console.error('Demo failed:', error.message);
        process.exit(1);
    }
}

main();
```

### PHP: Production-Ready Example

```php
<?php
/**
 * Production-Ready AI Agent Client
 *
 * Features:
 * - Retry logic
 * - Timeout handling
 * - Comprehensive error handling
 * - Logging
 */

class AIAgentClient
{
    private string $baseUrl;
    private string $sessionKey;
    private int $timeout = 30;
    private int $maxRetries = 3;

    public function __construct(string $sessionKey)
    {
        $this->baseUrl = 'https://gpt.ecigdis.co.nz/assets/services/ai-agent';
        $this->sessionKey = $sessionKey;
    }

    private function request(string $endpoint, array $data, int $attempt = 1): array
    {
        $url = $this->baseUrl . '/' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors with retry
        if ($response === false) {
            if ($attempt < $this->maxRetries) {
                error_log("Request failed (attempt {$attempt}), retrying: {$curlError}");
                sleep(pow(2, $attempt)); // Exponential backoff
                return $this->request($endpoint, $data, $attempt + 1);
            }
            throw new Exception("cURL error after {$attempt} attempts: {$curlError}");
        }

        // Handle HTTP errors
        if ($httpCode >= 400) {
            throw new Exception("HTTP {$httpCode}: {$response}");
        }

        // Parse JSON
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        // Handle API errors
        if (!$result['success']) {
            throw new Exception($result['error']['message'] ?? 'Unknown API error');
        }

        return $result;
    }

    public function chat(string $message, string $provider = 'openai', string $model = 'gpt-4o-mini'): string
    {
        $data = [
            'provider' => $provider,
            'model' => $model,
            'session_key' => $this->sessionKey,
            'message' => $message
        ];

        $result = $this->request('api/chat.php', $data);
        return $result['data']['content'];
    }

    public function invokeTool(string $tool, array $args): array
    {
        $data = [
            'tool' => $tool,
            'args' => $args
        ];

        $result = $this->request('api/tools/invoke.php', $data);
        return $result['data']['result'];
    }

    public function storeMemory(string $key, $value, string $scope = 'session'): void
    {
        $data = [
            'action' => 'upsert',
            'key' => $key,
            'value' => $value,
            'scope' => $scope,
            'confidence' => 100,
            'source' => 'php-client'
        ];

        $this->request('api/memory_upsert.php', $data);
    }

    public function retrieveMemory(string $key, string $scope = 'session')
    {
        $data = [
            'action' => 'retrieve',
            'key' => $key,
            'scope' => $scope
        ];

        $result = $this->request('api/memory_upsert.php', $data);
        return $result['data']['found'] ? $result['data']['value'] : null;
    }
}

// Usage example
try {
    $client = new AIAgentClient('php-production-demo');

    echo "=== AI Agent Production Demo ===\n\n";

    // Chat with error handling
    echo "Chatting with AI...\n";
    $response = $client->chat('What is the meaning of life?');
    echo "AI: {$response}\n\n";

    // Store with automatic retry on failure
    echo "Storing in memory...\n";
    $client->storeMemory('philosophy', ['question' => 'meaning of life', 'answer' => $response]);
    echo "Stored successfully\n\n";

    // Query database
    echo "Querying database...\n";
    $stats = $client->invokeTool('db.select', [
        'query' => 'SELECT COUNT(*) as count FROM ai_conversations'
    ]);
    echo "Total conversations: {$stats['rows'][0]['count']}\n";

    echo "\n=== Demo Complete ===\n";

} catch (Exception $e) {
    error_log("Demo failed: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

---

## Next Steps

After reviewing these examples:

1. **Choose your language** - Select examples for your preferred language
2. **Test locally** - Try examples in development environment
3. **Add error handling** - Implement retry logic and timeouts
4. **Monitor usage** - Track requests in `ai_agent_requests` table
5. **Optimize** - Cache responses, batch requests, use streaming

---

**See Also:**
- [03_AI_AGENT_ENDPOINTS.md](03_AI_AGENT_ENDPOINTS.md) - Complete API reference
- [05_TOOLS_REFERENCE.md](05_TOOLS_REFERENCE.md) - Tool documentation
- [09_TROUBLESHOOTING.md](09_TROUBLESHOOTING.md) - Common issues and solutions
