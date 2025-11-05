# Bot Deployment Management API

## Base URL
```
https://staff.vapeshed.co.nz/admin/bot-api.php
```

## Authentication
Include API key in request header:
```
X-API-Key: your-api-key
```
Or using Bearer token:
```
Authorization: Bearer your-api-key
```

## Rate Limiting
- **Limit**: 100 requests per 60 seconds (configurable)
- **Response**: 429 Too Many Requests with `Retry-After` header

---

## Bot Endpoints

### 1. List All Bots
**GET** `/api/bots`

**Query Parameters:**
- `status` (optional): Filter by status (`active`, `paused`, `archived`)
- `role` (optional): Filter by role (`security`, `developer`, `analyst`, `monitor`, `general`)
- `search` (optional): Search by name or role
- `with_metrics` (optional): Include performance metrics (`true`/`false`)

**Response:**
```json
{
  "success": true,
  "message": "Bots retrieved successfully",
  "data": {
    "bots": [
      {
        "bot_id": 1,
        "bot_name": "Security Sentinel",
        "bot_role": "security",
        "system_prompt": "You are a security expert...",
        "schedule_cron": "0 */4 * * *",
        "status": "active",
        "config": {...},
        "created_at": "2025-11-04 10:30:00",
        "updated_at": "2025-11-04 12:00:00"
      }
    ],
    "count": 1,
    "filters": {...}
  },
  "timestamp": 1730736000
}
```

---

### 2. Get Single Bot
**GET** `/api/bots/{id}`

**Query Parameters:**
- `with_metrics` (optional): Include performance metrics

**Response:**
```json
{
  "success": true,
  "message": "Bot retrieved successfully",
  "data": {
    "bot_id": 1,
    "bot_name": "Security Sentinel",
    "bot_role": "security",
    "system_prompt": "You are a security expert...",
    "schedule_cron": "0 */4 * * *",
    "status": "active",
    "config": {
      "model": "gpt-5-turbo",
      "temperature": 0.7,
      "multi_thread_enabled": true
    },
    "metrics": {
      "total_executions": 150,
      "successful_executions": 145,
      "failed_executions": 5,
      "avg_response_time_ms": 2345.67,
      "success_rate": 96.67
    }
  },
  "timestamp": 1730736000
}
```

---

### 3. Create Bot
**POST** `/api/bots`

**Request Body:**
```json
{
  "bot_name": "Security Sentinel",
  "bot_role": "security",
  "system_prompt": "You are a security expert monitoring the system...",
  "schedule_cron": "0 */4 * * *",
  "status": "active",
  "config": {
    "model": "gpt-5-turbo",
    "temperature": 0.7,
    "multi_thread_enabled": true,
    "preferred_thread_count": 4
  }
}
```

**Validation Rules:**
- `bot_name`: required, string, 3-100 characters
- `bot_role`: required, one of: security, developer, analyst, monitor, general
- `system_prompt`: required, string, minimum 10 characters
- `schedule_cron`: optional, valid cron expression
- `status`: optional, one of: active, paused, archived
- `config`: optional, object

**Response:**
```json
{
  "success": true,
  "message": "Bot created successfully",
  "data": {
    "bot_id": 1,
    "bot_name": "Security Sentinel",
    ...
  },
  "timestamp": 1730736000
}
```

---

### 4. Update Bot
**PUT** `/api/bots/{id}`

**Request Body:** (all fields optional)
```json
{
  "bot_name": "Updated Name",
  "status": "paused",
  "config": {
    "model": "gpt-5-turbo",
    "temperature": 0.8
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Bot updated successfully",
  "data": {...},
  "timestamp": 1730736000
}
```

---

### 5. Delete Bot
**DELETE** `/api/bots/{id}`

**Response:**
```json
{
  "success": true,
  "message": "Bot deleted successfully",
  "data": null,
  "timestamp": 1730736000
}
```

---

### 6. Execute Bot
**POST** `/api/bots/{id}/execute`

**Request Body:**
```json
{
  "input": "Analyze the consignment transfer module for security vulnerabilities",
  "context": {
    "module": "consignment",
    "priority": "high"
  },
  "multi_thread": false
}
```

**Validation Rules:**
- `input`: required, string, minimum 1 character
- `context`: optional, object
- `multi_thread`: optional, boolean

**Response:**
```json
{
  "success": true,
  "message": "Bot executed successfully",
  "data": {
    "success": true,
    "execution_id": "exec_1_20251104123000_a1b2c3d4",
    "bot_id": 1,
    "bot_name": "Security Sentinel",
    "output": "Analysis complete:\n\n1. SQL Injection Risk: Low...",
    "metadata": {
      "response_time": 2345.67,
      "tools_used": ["semantic_search", "fs.read", "db.query"],
      "conversation_id": "conv_12345"
    },
    "execution_time": 2500.45,
    "mode": "single-threaded"
  },
  "timestamp": 1730736000
}
```

---

### 7. Get Bot Metrics
**GET** `/api/bots/{id}/metrics`

**Response:**
```json
{
  "success": true,
  "message": "Metrics retrieved successfully",
  "data": {
    "bot_id": 1,
    "bot_name": "Security Sentinel",
    "total_executions": 150,
    "successful_executions": 145,
    "failed_executions": 5,
    "avg_response_time_ms": 2345.67,
    "success_rate": 96.67,
    "last_execution": "2025-11-04 12:30:00",
    "executions_last_hour": 12,
    "executions_last_24h": 87
  },
  "timestamp": 1730736000
}
```

---

### 8. Get Scheduled Bots
**GET** `/api/bots/scheduled`

**Response:**
```json
{
  "success": true,
  "message": "Scheduled bots retrieved successfully",
  "data": {
    "bots": [
      {
        "bot_id": 1,
        "bot_name": "Security Sentinel",
        "schedule": {
          "cron": "0 */4 * * *",
          "description": "Runs at minute 0 every 4 hours",
          "next_run": 1730739600,
          "next_run_formatted": "2025-11-04 16:00:00"
        },
        ...
      }
    ],
    "count": 3
  },
  "timestamp": 1730736000
}
```

---

### 9. Get Due Bots
**GET** `/api/bots/due`

**Response:**
```json
{
  "success": true,
  "message": "Due bots retrieved successfully",
  "data": {
    "bots": [...],
    "count": 2
  },
  "timestamp": 1730736000
}
```

---

### 10. Pause Bot
**POST** `/api/bots/{id}/pause`

**Response:**
```json
{
  "success": true,
  "message": "Bot paused successfully",
  "data": {...},
  "timestamp": 1730736000
}
```

---

### 11. Activate Bot
**POST** `/api/bots/{id}/activate`

**Response:**
```json
{
  "success": true,
  "message": "Bot activated successfully",
  "data": {...},
  "timestamp": 1730736000
}
```

---

### 12. Archive Bot
**POST** `/api/bots/{id}/archive`

**Response:**
```json
{
  "success": true,
  "message": "Bot archived successfully",
  "data": {...},
  "timestamp": 1730736000
}
```

---

## Session Endpoints

### 13. Create Multi-Thread Session
**POST** `/api/sessions`

**Request Body:**
```json
{
  "topic": "Comprehensive security audit of payment processing module",
  "thread_count": 4,
  "metadata": {
    "priority": "high",
    "requestor": "security_team"
  }
}
```

**Validation Rules:**
- `topic`: required, string, minimum 10 characters
- `thread_count`: required, integer, 2-6
- `metadata`: optional, object

**Response:**
```json
{
  "success": true,
  "message": "Session created successfully",
  "data": {
    "session_id": 1,
    "topic": "Comprehensive security audit...",
    "thread_count": 4,
    "status": "active",
    "metadata": {...},
    "created_at": "2025-11-04 12:00:00"
  },
  "timestamp": 1730736000
}
```

---

### 14. Get Session Details
**GET** `/api/sessions/{id}`

**Response:**
```json
{
  "success": true,
  "message": "Session retrieved successfully",
  "data": {
    "session_id": 1,
    "topic": "Comprehensive security audit...",
    "thread_count": 4,
    "status": "completed",
    "analytics": {
      "total_threads": 4,
      "completed_threads": 4,
      "failed_threads": 0,
      "avg_thread_duration_seconds": 45.67,
      "total_messages": 12
    },
    "created_at": "2025-11-04 12:00:00",
    "completed_at": "2025-11-04 12:03:05"
  },
  "timestamp": 1730736000
}
```

---

### 15. List Sessions
**GET** `/api/sessions`

**Query Parameters:**
- `limit` (optional): Number of sessions to return (default: 20)
- `with_analytics` (optional): Include analytics data (`true`/`false`)

**Response:**
```json
{
  "success": true,
  "message": "Sessions retrieved successfully",
  "data": {
    "sessions": [...],
    "count": 15
  },
  "timestamp": 1730736000
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "error": "Validation failed",
  "validation_errors": {
    "bot_name": ["bot_name is required"],
    "bot_role": ["bot_role must be one of: security, developer, analyst, monitor, general"]
  },
  "timestamp": 1730736000
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "error": "Invalid API key",
  "details": {},
  "timestamp": 1730736000
}
```

### Not Found (404)
```json
{
  "success": false,
  "error": "Bot not found: 999",
  "details": {},
  "timestamp": 1730736000
}
```

### Rate Limit (429)
```json
{
  "success": false,
  "error": "Rate limit exceeded",
  "details": {
    "retry_after": 60
  },
  "timestamp": 1730736000
}
```

### Server Error (500)
```json
{
  "success": false,
  "error": "Bot execution failed",
  "details": {
    "error": "Connection timeout to AI Agent API"
  },
  "timestamp": 1730736000
}
```

---

## cURL Examples

### Create Bot
```bash
curl -X POST https://staff.vapeshed.co.nz/admin/bot-api.php/api/bots \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "bot_name": "Security Sentinel",
    "bot_role": "security",
    "system_prompt": "You are a security expert...",
    "schedule_cron": "0 */4 * * *"
  }'
```

### Execute Bot
```bash
curl -X POST https://staff.vapeshed.co.nz/admin/bot-api.php/api/bots/1/execute \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "input": "Analyze consignment transfer module",
    "multi_thread": false
  }'
```

### List Bots
```bash
curl -X GET "https://staff.vapeshed.co.nz/admin/bot-api.php/api/bots?status=active&with_metrics=true" \
  -H "X-API-Key: your-api-key"
```

---

## Configuration

Edit `/admin/bot-deployment/config/config.php`:

```php
'security' => [
    'apiAuthRequired' => true,
    'apiKeys' => [
        'your-secure-api-key-here'
    ],
    'rateLimitRequests' => 100,
    'rateLimitWindow' => 60,
    'corsOrigins' => ['*']
]
```
