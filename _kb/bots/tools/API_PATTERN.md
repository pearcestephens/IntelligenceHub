# API Endpoint Pattern

**Last Updated:** 2025-10-27

## Standard Structure

All API endpoints should follow this structure:

### File Location
```
api/[feature_name].php
```

### Code Template

```php
<?php
/**
 * [Feature Name] API Endpoint
 * 
 * Description of what this endpoint does
 * 
 * @endpoint GET/POST /api/[feature_name].php
 * @param type $param_name Description
 * @return array JSON response
 */

declare(strict_types=1);

// 1. Load core services
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/CredentialManager.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/DatabaseValidator.php';

// 2. Set headers
header('Content-Type: application/json');
header('X-Powered-By: Intelligence-Hub/2.0');

// 3. Initialize response
$response = [
    'success' => false,
    'data' => null,
    'message' => '',
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    // 4. Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    
    // 5. Validate input
    $input = filter_input(INPUT_POST, 'param', FILTER_SANITIZE_STRING);
    if (empty($input)) {
        throw new Exception('Missing required parameter: param', 400);
    }
    
    // 6. Get database credentials (NEVER hardcode!)
    $creds = CredentialManager::getDatabaseCredentials();
    $pdo = new PDO(
        "mysql:host={$creds['host']};dbname={$creds['database']};charset=utf8mb4",
        $creds['username'],
        $creds['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 7. Validate query with DatabaseValidator
    $validator = new DatabaseValidator();
    $query = "SELECT * FROM table_name WHERE column = ?";
    $validation = $validator->validateQuery($query);
    
    if (!$validation['valid']) {
        throw new Exception("SQL validation failed: {$validation['error']}");
    }
    
    // 8. Execute query with prepared statement
    $stmt = $pdo->prepare($query);
    $stmt->execute([$input]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 9. Set success response
    $response['success'] = true;
    $response['data'] = $result;
    $response['message'] = 'Operation completed successfully';
    
} catch (Exception $e) {
    // 10. Handle errors
    $response['success'] = false;
    $response['error'] = [
        'code' => $e->getCode() ?: 500,
        'message' => $e->getMessage()
    ];
    
    // Log error (don't expose to user)
    error_log("API Error [{$_SERVER['REQUEST_URI']}]: " . $e->getMessage());
    
    // Set appropriate HTTP status
    http_response_code($e->getCode() ?: 500);
}

// 11. Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
```

## Key Requirements

1. ✅ Always use CredentialManager (never hardcode database credentials)
2. ✅ Always validate SQL with DatabaseValidator
3. ✅ Always use prepared statements (never concatenate SQL)
4. ✅ Always validate input with filter_input()
5. ✅ Always return JSON with standard structure
6. ✅ Always use try-catch for error handling
7. ✅ Always log errors (don't expose internals to users)
8. ✅ Always set appropriate HTTP status codes
9. ✅ Always include timestamp in response
10. ✅ Always use declare(strict_types=1)

## Standard Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation completed successfully",
  "timestamp": "2025-10-27 14:30:00"
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": 400,
    "message": "User-friendly error message"
  },
  "timestamp": "2025-10-27 14:30:00"
}
```

## Examples

See existing implementations:
- api/credentials.php
- api/db-validate.php
- api/bot-prompt.php
- api/ai-chat.php

## Copilot Usage

```
@workspace #file:_kb/patterns/API_PATTERN.md
Build new API endpoint for [feature] following this pattern
```
