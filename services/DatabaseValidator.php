<?php
/**
 * Database Validator & Auto-Correction Tool
 * 
 * Helps bots validate MySQL tables/fields and auto-correct queries
 * 
 * Features:
 * - Validate table names exist
 * - Validate field names exist in tables
 * - Suggest corrections for typos (fuzzy matching)
 * - Cache table schemas for performance
 * - Provide field type information
 * - Auto-fix common naming mistakes
 * 
 * @package CIS\Services
 * @version 1.0.0
 */

declare(strict_types=1);

class DatabaseValidator
{
    private ?PDO $db = null;
    private array $schemaCache = [];
    private array $tableListCache = [];
    private string $cacheFile;
    private int $cacheTTL = 3600; // 1 hour
    
    /**
     * Initialize validator with database connection
     * 
     * @param PDO|null $db Database connection (optional, will load from config)
     */
    public function __construct(?PDO $db = null)
    {
        $this->cacheFile = __DIR__ . '/../private_html/cache/db_schema_cache.json';
        
        if ($db) {
            $this->db = $db;
        } else {
            $this->loadConnection();
        }
        
        $this->loadCache();
    }
    
    /**
     * Load database connection from config (uses CredentialManager)
     */
    private function loadConnection(): void
    {
        try {
            // Try CredentialManager first
            if (class_exists('CredentialManager')) {
                $credManager = new CredentialManager();
                $creds = $credManager->getDatabaseCredentials();
                
                $this->db = new PDO(
                    "mysql:host={$creds['host']};dbname={$creds['database']};charset=utf8mb4",
                    $creds['username'],
                    $creds['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_TIMEOUT => 5
                    ]
                );
                return;
            }
        } catch (Exception $e) {
            error_log("DatabaseValidator: CredentialManager failed - " . $e->getMessage());
        }
        
        // Fallback to config file
        $configFile = __DIR__ . '/../dashboard/config/config.php';
        if (file_exists($configFile)) {
            require_once $configFile;
            
            try {
                $this->db = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_TIMEOUT => 5
                    ]
                );
            } catch (PDOException $e) {
                error_log("DatabaseValidator: Connection failed - " . $e->getMessage());
            }
        }
    }
    
    /**
     * Load schema cache from file
     */
    private function loadCache(): void
    {
        if (file_exists($this->cacheFile)) {
            $cache = json_decode(file_get_contents($this->cacheFile), true);
            if ($cache && isset($cache['timestamp']) && (time() - $cache['timestamp']) < $this->cacheTTL) {
                $this->schemaCache = $cache['schemas'] ?? [];
                $this->tableListCache = $cache['tables'] ?? [];
            }
        }
    }
    
    /**
     * Save schema cache to file
     */
    private function saveCache(): void
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $cache = [
            'timestamp' => time(),
            'schemas' => $this->schemaCache,
            'tables' => $this->tableListCache
        ];
        
        file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get list of all tables in database
     * 
     * @return array List of table names
     */
    public function getAllTables(): array
    {
        if (!empty($this->tableListCache)) {
            return $this->tableListCache;
        }
        
        if (!$this->db) {
            return [];
        }
        
        try {
            $stmt = $this->db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->tableListCache = $tables;
            $this->saveCache();
            return $tables;
        } catch (PDOException $e) {
            error_log("DatabaseValidator: Failed to get tables - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get schema (fields) for a specific table
     * 
     * @param string $tableName Table name
     * @return array Field information [field_name => type, null, key, default, extra]
     */
    public function getTableSchema(string $tableName): array
    {
        if (isset($this->schemaCache[$tableName])) {
            return $this->schemaCache[$tableName];
        }
        
        if (!$this->db) {
            return [];
        }
        
        try {
            $stmt = $this->db->query("DESCRIBE `{$tableName}`");
            $fields = [];
            while ($row = $stmt->fetch()) {
                $fields[$row['Field']] = [
                    'type' => $row['Type'],
                    'null' => $row['Null'] === 'YES',
                    'key' => $row['Key'],
                    'default' => $row['Default'],
                    'extra' => $row['Extra']
                ];
            }
            $this->schemaCache[$tableName] = $fields;
            $this->saveCache();
            return $fields;
        } catch (PDOException $e) {
            error_log("DatabaseValidator: Failed to get schema for {$tableName} - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate if a table exists
     * 
     * @param string $tableName Table name to check
     * @return array ['valid' => bool, 'suggestions' => array]
     */
    public function validateTable(string $tableName): array
    {
        $tables = $this->getAllTables();
        
        if (in_array($tableName, $tables)) {
            return [
                'valid' => true,
                'table' => $tableName,
                'message' => "Table '{$tableName}' exists"
            ];
        }
        
        // Find suggestions
        $suggestions = $this->findSimilarTables($tableName);
        
        return [
            'valid' => false,
            'table' => $tableName,
            'message' => "Table '{$tableName}' does not exist",
            'suggestions' => $suggestions
        ];
    }
    
    /**
     * Validate if a field exists in a table
     * 
     * @param string $tableName Table name
     * @param string $fieldName Field name to check
     * @return array ['valid' => bool, 'field_info' => array, 'suggestions' => array]
     */
    public function validateField(string $tableName, string $fieldName): array
    {
        // First check if table exists
        $tableCheck = $this->validateTable($tableName);
        if (!$tableCheck['valid']) {
            return [
                'valid' => false,
                'table' => $tableName,
                'field' => $fieldName,
                'message' => "Cannot validate field - table '{$tableName}' does not exist",
                'table_suggestions' => $tableCheck['suggestions']
            ];
        }
        
        // Get table schema
        $schema = $this->getTableSchema($tableName);
        
        if (isset($schema[$fieldName])) {
            return [
                'valid' => true,
                'table' => $tableName,
                'field' => $fieldName,
                'field_info' => $schema[$fieldName],
                'message' => "Field '{$fieldName}' exists in table '{$tableName}'"
            ];
        }
        
        // Find suggestions
        $suggestions = $this->findSimilarFields($tableName, $fieldName);
        
        return [
            'valid' => false,
            'table' => $tableName,
            'field' => $fieldName,
            'message' => "Field '{$fieldName}' does not exist in table '{$tableName}'",
            'suggestions' => $suggestions,
            'available_fields' => array_keys($schema)
        ];
    }
    
    /**
     * Validate a full query for table/field issues
     * 
     * @param string $query SQL query to validate
     * @return array Validation results with suggestions
     */
    public function validateQuery(string $query): array
    {
        $issues = [];
        $suggestions = [];
        
        // Extract table names (FROM, JOIN, UPDATE, INSERT INTO)
        preg_match_all('/(?:FROM|JOIN|UPDATE|INTO)\s+`?(\w+)`?/i', $query, $tableMatches);
        $tables = array_unique($tableMatches[1] ?? []);
        
        foreach ($tables as $table) {
            $check = $this->validateTable($table);
            if (!$check['valid']) {
                $issues[] = $check;
                if (!empty($check['suggestions'])) {
                    $suggestions[] = [
                        'original' => $table,
                        'type' => 'table',
                        'suggestions' => $check['suggestions']
                    ];
                }
            }
        }
        
        // Extract field references (basic - SELECT, WHERE, SET)
        // This is simplified - a full SQL parser would be better
        preg_match_all('/(?:SELECT|WHERE|SET|ON)\s+(?:.*?\.)?([\w]+)/i', $query, $fieldMatches);
        $fields = array_unique($fieldMatches[1] ?? []);
        
        // Try to validate fields against tables found in query
        foreach ($tables as $table) {
            $tableCheck = $this->validateTable($table);
            if ($tableCheck['valid']) {
                foreach ($fields as $field) {
                    // Skip SQL keywords
                    if (in_array(strtoupper($field), ['SELECT', 'FROM', 'WHERE', 'SET', 'ON', 'AND', 'OR', 'AS', 'BY'])) {
                        continue;
                    }
                    
                    $fieldCheck = $this->validateField($table, $field);
                    if (!$fieldCheck['valid']) {
                        $issues[] = $fieldCheck;
                        if (!empty($fieldCheck['suggestions'])) {
                            $suggestions[] = [
                                'original' => $field,
                                'type' => 'field',
                                'table' => $table,
                                'suggestions' => $fieldCheck['suggestions']
                            ];
                        }
                    }
                }
            }
        }
        
        return [
            'valid' => empty($issues),
            'query' => $query,
            'issues' => $issues,
            'suggestions' => $suggestions,
            'tables_found' => $tables,
            'fields_found' => $fields
        ];
    }
    
    /**
     * Find similar table names using fuzzy matching
     * 
     * @param string $tableName Table name to match
     * @param int $limit Maximum suggestions
     * @return array Similar table names with similarity scores
     */
    private function findSimilarTables(string $tableName, int $limit = 5): array
    {
        $tables = $this->getAllTables();
        $suggestions = [];
        
        foreach ($tables as $table) {
            $similarity = $this->calculateSimilarity($tableName, $table);
            if ($similarity > 0.4) { // 40% similarity threshold
                $suggestions[] = [
                    'name' => $table,
                    'similarity' => round($similarity * 100, 1)
                ];
            }
        }
        
        // Sort by similarity
        usort($suggestions, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($suggestions, 0, $limit);
    }
    
    /**
     * Find similar field names in a table
     * 
     * @param string $tableName Table name
     * @param string $fieldName Field name to match
     * @param int $limit Maximum suggestions
     * @return array Similar field names with similarity scores
     */
    private function findSimilarFields(string $tableName, string $fieldName, int $limit = 5): array
    {
        $schema = $this->getTableSchema($tableName);
        $suggestions = [];
        
        foreach (array_keys($schema) as $field) {
            $similarity = $this->calculateSimilarity($fieldName, $field);
            if ($similarity > 0.4) {
                $suggestions[] = [
                    'name' => $field,
                    'type' => $schema[$field]['type'],
                    'similarity' => round($similarity * 100, 1)
                ];
            }
        }
        
        // Sort by similarity
        usort($suggestions, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($suggestions, 0, $limit);
    }
    
    /**
     * Calculate similarity between two strings (Levenshtein + similar_text)
     * 
     * @param string $str1 First string
     * @param string $str2 Second string
     * @return float Similarity score (0-1)
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);
        
        // Exact match
        if ($str1 === $str2) {
            return 1.0;
        }
        
        // Contains match
        if (strpos($str2, $str1) !== false || strpos($str1, $str2) !== false) {
            return 0.9;
        }
        
        // Levenshtein distance
        $lev = levenshtein($str1, $str2);
        $maxLen = max(strlen($str1), strlen($str2));
        $levSimilarity = 1 - ($lev / $maxLen);
        
        // similar_text percentage
        similar_text($str1, $str2, $percent);
        $similarTextScore = $percent / 100;
        
        // Average both methods
        return ($levSimilarity + $similarTextScore) / 2;
    }
    
    /**
     * Get comprehensive table information
     * 
     * @param string $tableName Table name
     * @return array Complete table info (schema, indexes, row count)
     */
    public function getTableInfo(string $tableName): array
    {
        $check = $this->validateTable($tableName);
        if (!$check['valid']) {
            return $check;
        }
        
        $schema = $this->getTableSchema($tableName);
        
        // Get row count
        $rowCount = 0;
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM `{$tableName}`");
            $rowCount = $stmt->fetch()['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("DatabaseValidator: Failed to count rows in {$tableName}");
        }
        
        // Get indexes
        $indexes = [];
        try {
            $stmt = $this->db->query("SHOW INDEXES FROM `{$tableName}`");
            while ($row = $stmt->fetch()) {
                $indexes[$row['Key_name']][] = $row['Column_name'];
            }
        } catch (PDOException $e) {
            error_log("DatabaseValidator: Failed to get indexes for {$tableName}");
        }
        
        return [
            'valid' => true,
            'table' => $tableName,
            'row_count' => $rowCount,
            'field_count' => count($schema),
            'fields' => $schema,
            'indexes' => $indexes,
            'primary_key' => $this->findPrimaryKey($schema)
        ];
    }
    
    /**
     * Find primary key field(s) in schema
     * 
     * @param array $schema Table schema
     * @return array Primary key field names
     */
    private function findPrimaryKey(array $schema): array
    {
        $pk = [];
        foreach ($schema as $field => $info) {
            if ($info['key'] === 'PRI') {
                $pk[] = $field;
            }
        }
        return $pk;
    }
    
    /**
     * Auto-correct a query with suggested fixes
     * 
     * @param string $query Original query
     * @return array ['corrected' => string, 'changes' => array]
     */
    public function autoCorrectQuery(string $query): array
    {
        $validation = $this->validateQuery($query);
        $correctedQuery = $query;
        $changes = [];
        
        if (!empty($validation['suggestions'])) {
            foreach ($validation['suggestions'] as $suggestion) {
                if (!empty($suggestion['suggestions'])) {
                    $best = $suggestion['suggestions'][0]; // Highest similarity
                    $original = $suggestion['original'];
                    $replacement = $best['name'];
                    
                    // Replace in query (case-insensitive, with word boundaries)
                    $pattern = '/\b' . preg_quote($original, '/') . '\b/i';
                    $correctedQuery = preg_replace($pattern, $replacement, $correctedQuery);
                    
                    $changes[] = [
                        'type' => $suggestion['type'],
                        'original' => $original,
                        'corrected' => $replacement,
                        'similarity' => $best['similarity'],
                        'table' => $suggestion['table'] ?? null
                    ];
                }
            }
        }
        
        return [
            'original' => $query,
            'corrected' => $correctedQuery,
            'changes' => $changes,
            'auto_corrected' => !empty($changes)
        ];
    }
    
    /**
     * Clear schema cache (force refresh)
     */
    public function clearCache(): void
    {
        $this->schemaCache = [];
        $this->tableListCache = [];
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }
    
    /**
     * Scan PHP code for SQL queries and validate them
     * 
     * @param string $code PHP code to scan
     * @param array $options Scan options
     * @return array Scan results with errors and fixes
     */
    public function scanCode(string $code, array $options = []): array
    {
        $defaultOptions = [
            'auto_fix' => true,
            'confidence_threshold' => 0.4,
            'check_security' => true,
            'track_variables' => false
        ];
        $options = array_merge($defaultOptions, $options);
        
        $queries = $this->extractQueries($code);
        $errors = [];
        $fixes = [];
        $security_issues = [];
        $fixedCode = $code;
        
        foreach ($queries as $query) {
            $validation = $this->validateQuery($query['sql']);
            
            if (!$validation['valid']) {
                $error = [
                    'line' => $query['line'],
                    'query' => $query['sql'],
                    'issues' => []
                ];
                
                // Check tables
                foreach ($validation['tables'] as $table) {
                    if (!$table['valid']) {
                        $error['issues'][] = [
                            'type' => 'table',
                            'name' => $table['name'],
                            'suggestions' => $table['suggestions'] ?? []
                        ];
                    }
                }
                
                // Check fields
                foreach ($validation['fields'] as $field) {
                    if (!$field['valid']) {
                        $error['issues'][] = [
                            'type' => 'field',
                            'name' => $field['name'],
                            'table' => $field['table'],
                            'suggestions' => $field['suggestions'] ?? []
                        ];
                    }
                }
                
                $errors[] = $error;
                
                // Auto-fix if enabled
                if ($options['auto_fix'] && !empty($error['issues'])) {
                    $autoCorrect = $this->autoCorrectQuery($query['sql']);
                    if ($autoCorrect['auto_corrected']) {
                        $fixes[] = [
                            'line' => $query['line'],
                            'original' => $query['sql'],
                            'fixed' => $autoCorrect['corrected'],
                            'changes' => $autoCorrect['changes'],
                            'confidence' => $this->calculateConfidence($autoCorrect['changes'])
                        ];
                        
                        // Replace in code
                        $fixedCode = str_replace($query['sql'], $autoCorrect['corrected'], $fixedCode);
                    }
                }
            }
            
            // Security checks
            if ($options['check_security']) {
                $securityCheck = $this->checkQuerySecurity($query);
                if (!$securityCheck['safe']) {
                    $security_issues[] = [
                        'line' => $query['line'],
                        'query' => $query['sql'],
                        'issues' => $securityCheck['issues']
                    ];
                }
            }
        }
        
        return [
            'total_queries' => count($queries),
            'errors_found' => count($errors),
            'fixes_applied' => count($fixes),
            'security_issues' => count($security_issues),
            'errors' => $errors,
            'fixes' => $fixes,
            'security' => $security_issues,
            'fixed_code' => $fixedCode,
            'code_changed' => $fixedCode !== $code
        ];
    }
    
    /**
     * Extract SQL queries from PHP code
     */
    private function extractQueries(string $code): array
    {
        $queries = [];
        $lines = explode("\n", $code);
        
        foreach ($lines as $lineNum => $line) {
            // Match various query patterns
            $patterns = [
                // $db->query("SELECT...")
                '/->query\s*\(\s*["\'](.+?)["\']\s*\)/i',
                // $pdo->prepare("SELECT...")
                '/->prepare\s*\(\s*["\'](.+?)["\']\s*\)/i',
                // mysqli_query($conn, "SELECT...")
                '/mysqli_query\s*\([^,]+,\s*["\'](.+?)["\']\s*\)/i',
                // DB::select("SELECT...")
                '/DB::(?:select|insert|update|delete)\s*\(\s*["\'](.+?)["\']\s*\)/i',
                // Direct SQL strings
                '/(?:SELECT|INSERT|UPDATE|DELETE)\s+.+?(?:FROM|INTO|SET)\s+\w+/i'
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    $sql = $matches[1] ?? $matches[0];
                    $queries[] = [
                        'line' => $lineNum + 1,
                        'sql' => trim($sql),
                        'raw' => trim($line)
                    ];
                }
            }
        }
        
        return $queries;
    }
    
    /**
     * Check query for security issues
     */
    private function checkQuerySecurity(array $query): array
    {
        $sql = $query['sql'];
        $issues = [];
        
        // Check for SQL injection risks
        if (preg_match('/\$_(GET|POST|REQUEST|COOKIE)\[/i', $query['raw'])) {
            $issues[] = 'Direct use of user input ($_GET, $_POST, etc.)';
        }
        
        // Check for string concatenation in query
        if (preg_match('/["\'].*?\.\s*\$/', $query['raw'])) {
            $issues[] = 'String concatenation with variables (SQL injection risk)';
        }
        
        // Check if query uses prepared statements
        $isPrepared = preg_match('/->prepare\(/i', $query['raw']) || 
                     preg_match('/\?|\:\w+/', $sql);
        
        if (!$isPrepared && preg_match('/\$\w+/', $query['raw'])) {
            $issues[] = 'Query uses variables but is not a prepared statement';
        }
        
        // Check for UNION injection
        if (preg_match('/UNION\s+SELECT/i', $sql)) {
            $issues[] = 'UNION statement detected (verify legitimate use)';
        }
        
        return [
            'safe' => empty($issues),
            'issues' => $issues
        ];
    }
    
    /**
     * Calculate confidence score from changes
     */
    private function calculateConfidence(array $changes): float
    {
        if (empty($changes)) {
            return 0.0;
        }
        
        $total = 0;
        foreach ($changes as $change) {
            $total += $change['similarity'] ?? 0;
        }
        
        return round($total / count($changes), 2);
    }
    
    /**
     * Scan entire PHP file
     * 
     * @param string $filePath Path to PHP file
     * @param array $options Scan options
     * @return array
     */
    public function scanFile(string $filePath, array $options = []): array
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'error' => 'File not found'
            ];
        }
        
        $code = file_get_contents($filePath);
        $result = $this->scanCode($code, $options);
        $result['file'] = $filePath;
        $result['success'] = true;
        
        return $result;
    }
    
    /**
     * Scan multiple files in a directory
     * 
     * @param string $directory Directory to scan
     * @param array $options Scan options
     * @return array
     */
    public function scanDirectory(string $directory, array $options = []): array
    {
        $defaultOptions = [
            'recursive' => true,
            'extensions' => ['php'],
            'exclude_dirs' => ['vendor', 'node_modules', 'cache']
        ];
        $options = array_merge($defaultOptions, $options);
        
        $files = $this->getPhpFiles($directory, $options);
        $results = [];
        $summary = [
            'total_files' => count($files),
            'files_with_errors' => 0,
            'total_queries' => 0,
            'total_errors' => 0,
            'total_fixes' => 0,
            'total_security_issues' => 0
        ];
        
        foreach ($files as $file) {
            $result = $this->scanFile($file, $options);
            
            if ($result['success']) {
                $summary['total_queries'] += $result['total_queries'];
                $summary['total_errors'] += $result['errors_found'];
                $summary['total_fixes'] += $result['fixes_applied'];
                $summary['total_security_issues'] += $result['security_issues'];
                
                if ($result['errors_found'] > 0 || $result['security_issues'] > 0) {
                    $summary['files_with_errors']++;
                    $results[] = $result;
                }
            }
        }
        
        return [
            'success' => true,
            'directory' => $directory,
            'summary' => $summary,
            'files' => $results
        ];
    }
    
    /**
     * Get PHP files from directory
     */
    private function getPhpFiles(string $directory, array $options): array
    {
        $files = [];
        
        if (!is_dir($directory)) {
            return $files;
        }
        
        $iterator = $options['recursive'] 
            ? new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
              )
            : new DirectoryIterator($directory);
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
                
                if (in_array($ext, $options['extensions'])) {
                    // Check if in excluded directory
                    $excluded = false;
                    foreach ($options['exclude_dirs'] as $excludeDir) {
                        if (strpos($file->getPathname(), "/{$excludeDir}/") !== false) {
                            $excluded = true;
                            break;
                        }
                    }
                    
                    if (!$excluded) {
                        $files[] = $file->getPathname();
                    }
                }
            }
        }
        
        return $files;
    }
}

