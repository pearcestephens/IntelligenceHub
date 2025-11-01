# Service Class Pattern

**Last Updated:** 2025-10-27

## Standard Structure

All service classes should follow this structure:

### File Location
```
services/[ServiceName].php
```

### Code Template

```php
<?php
/**
 * [ServiceName] - Brief description
 * 
 * Detailed description of what this service does
 * 
 * @package IntelligenceHub\Services
 * @version 1.0.0
 */

declare(strict_types=1);

class ServiceName
{
    // 1. Configuration constants
    private const CACHE_TTL = 3600; // 1 hour
    private const CONFIG_TABLE = 'table_name';
    
    // 2. Private properties
    private static ?PDO $pdo = null;
    private static ?array $cache = null;
    
    /**
     * Get database connection
     * Uses CredentialManager - never hardcodes credentials
     * 
     * @return PDO Database connection
     * @throws Exception If connection fails
     */
    private static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            require_once __DIR__ . '/CredentialManager.php';
            $creds = CredentialManager::getDatabaseCredentials();
            
            self::$pdo = new PDO(
                "mysql:host={$creds['host']};dbname={$creds['database']};charset=utf8mb4",
                $creds['username'],
                $creds['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        }
        
        return self::$pdo;
    }
    
    /**
     * Main public method - example
     * 
     * @param string $param Description
     * @return array Result
     * @throws Exception On error
     */
    public static function mainMethod(string $param): array
    {
        // 1. Validate input
        if (empty($param)) {
            throw new InvalidArgumentException('Parameter cannot be empty');
        }
        
        // 2. Check cache
        $cacheKey = "service_name_{$param}";
        if (self::$cache && isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }
        
        try {
            // 3. Get database connection
            $pdo = self::getConnection();
            
            // 4. Validate query with DatabaseValidator
            require_once __DIR__ . '/DatabaseValidator.php';
            $validator = new DatabaseValidator();
            
            $query = "SELECT * FROM table_name WHERE column = ?";
            $validation = $validator->validateQuery($query);
            
            if (!$validation['valid']) {
                throw new Exception("SQL validation failed: {$validation['error']}");
            }
            
            // 5. Execute query
            $stmt = $pdo->prepare($query);
            $stmt->execute([$param]);
            $result = $stmt->fetchAll();
            
            // 6. Process result
            $processed = self::processResult($result);
            
            // 7. Cache result
            self::$cache[$cacheKey] = $processed;
            
            return $processed;
            
        } catch (PDOException $e) {
            error_log("ServiceName Error: " . $e->getMessage());
            throw new Exception('Database error occurred', 500);
        }
    }
    
    /**
     * Helper method - example
     * 
     * @param array $data Input data
     * @return array Processed data
     */
    private static function processResult(array $data): array
    {
        // Processing logic here
        return $data;
    }
    
    /**
     * Clear cache
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
}
```

## Key Requirements

1. ✅ declare(strict_types=1) at top
2. ✅ Comprehensive PHPDoc comments
3. ✅ Use CredentialManager for database (never hardcode)
4. ✅ Validate SQL with DatabaseValidator
5. ✅ Use prepared statements
6. ✅ Input validation with type hints
7. ✅ Proper error handling with try-catch
8. ✅ Private getConnection() method
9. ✅ Caching for frequently accessed data
10. ✅ Static methods for utility services

## Examples

See existing implementations:
- services/CredentialManager.php
- services/DatabaseValidator.php
- services/BotPromptBuilder.php
- services/AIAgentClient.php

## Copilot Usage

```
@workspace #file:_kb/patterns/SERVICE_PATTERN.md
Build new service class for [feature] following this pattern
```
