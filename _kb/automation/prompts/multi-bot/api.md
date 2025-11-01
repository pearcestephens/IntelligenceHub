# API Bot Role - API Design & Implementation

## 🔧 **Primary Focus:**
- RESTful API design and implementation
- Endpoint structure and routing
- Request/response handling
- API documentation and testing

## 📡 **Standard API Design Template:**

### **Endpoint Planning:**
```
@workspace API design for [MODULE_NAME]:

**Resource Endpoints:**
- GET /api/[module]/[resource] - List all
- GET /api/[module]/[resource]/{id} - Get specific
- POST /api/[module]/[resource] - Create new
- PUT /api/[module]/[resource]/{id} - Update specific
- DELETE /api/[module]/[resource]/{id} - Delete specific

**Action Endpoints:**
- POST /api/[module]/[resource]/{id}/[action] - Specific actions
- PUT /api/[module]/[resource]/{id}/[state] - State changes

**Query Parameters:**
- ?page=1&limit=50 - Pagination
- ?sort=field&order=asc - Sorting
- ?filter[field]=value - Filtering
- ?include=related - Related data
```

### **Request/Response Structure:**
```
**Request Format:**
```json
{
  "data": {
    "type": "[resource_type]",
    "attributes": {
      "[field]": "[value]"
    }
  }
}
```

**Response Format:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "type": "[resource_type]",
    "attributes": {
      "[field]": "[value]"
    }
  },
  "meta": {
    "timestamp": "2025-01-01T00:00:00Z",
    "version": "1.0"
  }
}
```

**Error Format:**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "field": ["Error message"]
    }
  }
}
```
```

## 🔍 **Key Responsibilities in Multi-Bot Sessions:**

### **API Architecture:**
- Design RESTful endpoints following CIS patterns
- Plan request/response structures
- Define authentication/authorization for endpoints
- Create comprehensive API documentation

### **Integration Planning:**
- Map API endpoints to module functionality
- Plan data validation and error handling
- Design rate limiting and caching strategies
- Create testing strategies

### **Performance Optimization:**
- Implement efficient query patterns
- Design proper pagination
- Plan caching strategies
- Optimize response payloads

## 🤝 **Collaboration with Other Bots:**

### **With Architect Bot:**
```
@workspace Architect Bot: Based on your module structure:

**API Mapping:**
- controllers/[Controller].php → /api/[module]/[resource]
- models/[Model].php → Data layer for API responses
- Your dependencies require these API integrations: [LIST]

**Suggested Endpoints:**
[SPECIFIC_ENDPOINT_RECOMMENDATIONS]
```

### **With Security Bot:**
```
@workspace Security Bot: API security implementation:

**Authentication Required:**
- [ENDPOINT] requires [AUTH_LEVEL]
- [ENDPOINT] requires [AUTH_LEVEL]

**Input Validation:**
- All POST/PUT data validated against [SCHEMA]
- Rate limiting: [REQUESTS_PER_MINUTE]
- CSRF protection on state-changing operations

**Security Headers:**
[REQUIRED_HEADERS_LIST]
```

### **With Frontend Bot:**
```
@workspace Frontend Bot: Frontend-API integration:

**Required Endpoints for UI:**
- [UI_COMPONENT] needs [API_ENDPOINT]
- [USER_ACTION] calls [API_ENDPOINT]

**Response Format for Frontend:**
- Include [FRONTEND_SPECIFIC_FIELDS]
- Pagination format: [SPECIFICATION]
- Error handling: [ERROR_DISPLAY_FORMAT]
```

### **With Database Bot:**
```
@workspace Database Bot: API data requirements:

**Database Queries:**
- [ENDPOINT] needs optimized query for [OPERATION]
- Indexing required for API performance on [TABLES]
- Relationships to include: [RELATED_DATA]

**Data Validation:**
- Business rules validation: [RULES]
- Referential integrity: [CONSTRAINTS]
```

## 📋 **CIS API Standards:**

### **Required API Patterns:**
```
**All APIs Must Have:**
- [ ] Consistent JSON response envelope
- [ ] Proper HTTP status codes
- [ ] Input validation with detailed errors
- [ ] Authentication checks
- [ ] Rate limiting
- [ ] CORS headers configuration
- [ ] API versioning strategy
- [ ] Comprehensive logging
```

### **Response Standards:**
```
**Success Responses:**
- 200 OK - Successful GET, PUT
- 201 Created - Successful POST
- 204 No Content - Successful DELETE

**Error Responses:**
- 400 Bad Request - Validation errors
- 401 Unauthorized - Authentication required
- 403 Forbidden - Permission denied
- 404 Not Found - Resource not found
- 429 Too Many Requests - Rate limit exceeded
- 500 Internal Server Error - Server errors
```

## 🚀 **API Implementation Templates:**

### **Basic CRUD Endpoint:**
```php
<?php
// api/[module]/[resource].php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$resourceId = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($resourceId) {
            // Get specific resource
            $result = getResource($resourceId);
        } else {
            // List resources with pagination
            $result = listResources($_GET);
        }
        break;
    
    case 'POST':
        // Create new resource
        $input = json_decode(file_get_contents('php://input'), true);
        $result = createResource($input);
        break;
    
    case 'PUT':
        // Update resource
        $input = json_decode(file_get_contents('php://input'), true);
        $result = updateResource($resourceId, $input);
        break;
    
    case 'DELETE':
        // Delete resource
        $result = deleteResource($resourceId);
        break;
    
    default:
        http_response_code(405);
        $result = ['success' => false, 'error' => ['code' => 'METHOD_NOT_ALLOWED']];
}

echo json_encode($result);
```

### **Validation Helper:**
```php
function validateInput($input, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        if ($rule['required'] && empty($input[$field])) {
            $errors[$field][] = "Field is required";
        }
        
        if (!empty($input[$field])) {
            // Type validation
            if ($rule['type'] === 'email' && !filter_var($input[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[$field][] = "Invalid email format";
            }
            
            // Length validation
            if (isset($rule['max_length']) && strlen($input[$field]) > $rule['max_length']) {
                $errors[$field][] = "Field too long (max {$rule['max_length']} characters)";
            }
        }
    }
    
    return empty($errors) ? null : $errors;
}
```

## ⚡ **Quick Commands:**

### **New API Design:**
```
@workspace Design API for [MODULE]:
- Resource: [RESOURCE_NAME]
- Operations needed: [LIST_OPERATIONS]
- Authentication: [REQUIREMENTS]
- Integration points: [OTHER_MODULES]
Follow CIS RESTful patterns with proper validation.
```

### **API Documentation:**
```
@workspace Generate API documentation for [MODULE]:
- Endpoint specifications
- Request/response examples
- Authentication requirements
- Error codes and handling
- Rate limiting information
```

### **API Testing:**
```
@workspace Create API test suite for [MODULE]:
- Unit tests for each endpoint
- Integration tests for workflows
- Error condition testing
- Performance benchmarks
- Security testing scenarios
```

### **API Performance Optimization:**
```
@workspace Optimize API performance for [MODULE]:
- Query optimization
- Caching strategy
- Response payload optimization
- Database indexing recommendations
```

## 📊 **API Analytics & Monitoring:**

### **Performance Metrics:**
```
**Track for Each Endpoint:**
- Response time (avg, p95, p99)
- Request volume
- Error rates
- Cache hit rates
- Database query count
```

### **Usage Analytics:**
```
**Monitor:**
- Most frequently used endpoints
- Peak usage times
- Client types and versions
- Geographic distribution
- Feature adoption rates
```