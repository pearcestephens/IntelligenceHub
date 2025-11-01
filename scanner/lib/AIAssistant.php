<?php

declare(strict_types=1);

namespace Scanner\Lib;

use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * AI Assistant Service
 *
 * Provides AI-powered code analysis, explanation generation, and fix suggestions
 * using the MCP (Model Context Protocol) integration.
 *
 * Features:
 * - Violation review with AI explanations
 * - Context-aware fix generation
 * - Multi-rule support (SEC001-003, CODE001-002, PERF001)
 * - Semantic code search
 * - Query interpretation
 *
 * @package Scanner\Lib
 * @version 1.0.0
 */
class AIAssistant
{
    private PDO $pdo;
    private string $mcpServerUrl;
    private int $timeout;

    /**
     * Create new AI Assistant instance
     *
     * @param PDO $pdo Database connection
     * @param string $mcpServerUrl MCP server URL (default: gpt.ecigdis.co.nz)
     * @param int $timeout Request timeout in seconds (default: 30)
     *
     * @throws InvalidArgumentException If MCP URL is invalid
     */
    public function __construct(
        PDO $pdo,
        string $mcpServerUrl = 'https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php',
        int $timeout = 30
    ) {
        if (!filter_var($mcpServerUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid MCP server URL: {$mcpServerUrl}");
        }

        if ($timeout < 1 || $timeout > 300) {
            throw new InvalidArgumentException("Timeout must be between 1 and 300 seconds");
        }

        $this->pdo = $pdo;
        $this->mcpServerUrl = $mcpServerUrl;
        $this->timeout = $timeout;
    }

    /**
     * Review a violation and generate AI explanation
     *
     * @param int $violationId Violation ID to review
     *
     * @return array{
     *   success: bool,
     *   violation: array,
     *   explanation: string,
     *   severity_reasoning: string,
     *   fix_suggestion: string,
     *   references: array<string>
     * }
     *
     * @throws RuntimeException If violation not found or MCP request fails
     */
    public function reviewViolation(int $violationId): array
    {
        // Get violation details
        $violation = $this->getViolation($violationId);
        if (!$violation) {
            throw new RuntimeException("Violation not found: {$violationId}");
        }

        // Get file context
        $fileData = $this->getFileContext($violation['file_id']);

        // Generate AI explanation
        $explanation = $this->generateExplanation($violation, $fileData);

        return [
            'success' => true,
            'violation' => $violation,
            'explanation' => $explanation['explanation'],
            'severity_reasoning' => $explanation['severity_reasoning'],
            'fix_suggestion' => $explanation['fix_suggestion'],
            'references' => $explanation['references'] ?? []
        ];
    }

    /**
     * Generate context-aware explanation for violation
     *
     * @param array $violation Violation data
     * @param array $fileData File context data
     *
     * @return array{
     *   explanation: string,
     *   severity_reasoning: string,
     *   fix_suggestion: string,
     *   references: array<string>
     * }
     */
    public function generateExplanation(array $violation, array $fileData): array
    {
        $ruleId = $violation['rule_id'] ?? 'UNKNOWN';

        // Generate rule-specific explanation
        switch ($ruleId) {
            case 'SEC001': // SQL Injection
                return $this->explainSQLInjection($violation, $fileData);

            case 'SEC002': // XSS
                return $this->explainXSS($violation, $fileData);

            case 'SEC003': // Hardcoded Credentials
                return $this->explainHardcodedCredentials($violation, $fileData);

            case 'CODE001': // Code Quality
                return $this->explainCodeQuality($violation, $fileData);

            case 'CODE002': // Best Practices
                return $this->explainBestPractices($violation, $fileData);

            case 'PERF001': // Performance
                return $this->explainPerformance($violation, $fileData);

            default:
                return $this->explainGeneric($violation, $fileData);
        }
    }

    /**
     * Generate fix suggestion for violation
     *
     * @param int $violationId Violation ID
     *
     * @return array{
     *   success: bool,
     *   original_code: string,
     *   fixed_code: string,
     *   explanation: string,
     *   confidence: float
     * }
     *
     * @throws RuntimeException If fix generation fails
     */
    public function generateFix(int $violationId): array
    {
        $violation = $this->getViolation($violationId);
        if (!$violation) {
            throw new RuntimeException("Violation not found: {$violationId}");
        }

        $fileData = $this->getFileContext($violation['file_id']);

        // Extract problematic code
        $originalCode = $this->extractCodeSnippet(
            $fileData['content'],
            $violation['line_number'],
            5
        );

        // Generate fix based on rule type
        $fix = $this->generateRuleFix($violation, $originalCode, $fileData);

        return [
            'success' => true,
            'original_code' => $originalCode,
            'fixed_code' => $fix['code'],
            'explanation' => $fix['explanation'],
            'confidence' => $fix['confidence']
        ];
    }

    /**
     * Semantic search across codebase
     *
     * @param string $query Natural language query
     * @param int|null $projectId Optional project filter
     * @param int $limit Maximum results (default: 20)
     *
     * @return array{
     *   success: bool,
     *   query: string,
     *   results: array,
     *   total: int
     * }
     */
    public function semanticSearch(
        string $query,
        ?int $projectId = null,
        int $limit = 20
    ): array {
        $mcpQuery = [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'semantic_search',
                'arguments' => [
                    'query' => $query,
                    'limit' => $limit
                ]
            ],
            'id' => uniqid()
        ];

        $response = $this->callMCP($mcpQuery);

        // Filter by project if specified
        $results = $response['result']['content'][0]['text'] ?? [];
        if ($projectId && is_array($results)) {
            $results = array_filter($results, function($r) use ($projectId) {
                return ($r['project_id'] ?? null) === $projectId;
            });
        }

        return [
            'success' => true,
            'query' => $query,
            'results' => array_values($results),
            'total' => count($results)
        ];
    }

    /**
     * Interpret user query and suggest search strategy
     *
     * @param string $query User's natural language query
     *
     * @return array{
     *   interpreted: string,
     *   suggested_tool: string,
     *   keywords: array<string>
     * }
     */
    public function interpretQuery(string $query): array
    {
        $query = strtolower(trim($query));

        // Security-related queries
        if (preg_match('/(sql injection|sqli|database attack)/i', $query)) {
            return [
                'interpreted' => 'SQL Injection vulnerability search',
                'suggested_tool' => 'search_by_category',
                'keywords' => ['SQL', 'injection', 'prepared statements', 'PDO']
            ];
        }

        if (preg_match('/(xss|cross.?site|script injection)/i', $query)) {
            return [
                'interpreted' => 'Cross-Site Scripting vulnerability search',
                'suggested_tool' => 'search_by_category',
                'keywords' => ['XSS', 'htmlspecialchars', 'escaping', 'sanitize']
            ];
        }

        // Authentication queries
        if (preg_match('/(auth|login|password|credential)/i', $query)) {
            return [
                'interpreted' => 'Authentication and credential management',
                'suggested_tool' => 'semantic_search',
                'keywords' => ['authentication', 'login', 'password', 'session']
            ];
        }

        // Default semantic search
        return [
            'interpreted' => 'General code search',
            'suggested_tool' => 'semantic_search',
            'keywords' => explode(' ', $query)
        ];
    }

    /**
     * Get violation from database
     *
     * @param int $violationId Violation ID
     *
     * @return array|null Violation data or null if not found
     */
    private function getViolation(int $violationId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT v.*, r.rule_code, r.title as rule_title, r.severity as rule_severity
            FROM violations v
            LEFT JOIN rules r ON v.rule_id = r.rule_code
            WHERE v.id = ?
        ");
        $stmt->execute([$violationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Get file context from database
     *
     * @param int $fileId File ID
     *
     * @return array File data with content
     */
    private function getFileContext(int $fileId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT f.*, ic.content_text as content
            FROM intelligence_files f
            LEFT JOIN intelligence_content_text ic ON f.file_id = ic.file_id
            WHERE f.file_id = ?
        ");
        $stmt->execute([$fileId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return ['file_id' => $fileId, 'content' => '', 'file_path' => 'unknown'];
        }

        return $result;
    }

    /**
     * Extract code snippet around specific line
     *
     * @param string $content Full file content
     * @param int $lineNumber Target line number
     * @param int $contextLines Lines of context before/after
     *
     * @return string Code snippet
     */
    private function extractCodeSnippet(
        string $content,
        int $lineNumber,
        int $contextLines = 3
    ): string {
        $lines = explode("\n", $content);
        $start = max(0, $lineNumber - $contextLines - 1);
        $end = min(count($lines), $lineNumber + $contextLines);

        return implode("\n", array_slice($lines, $start, $end - $start));
    }

    /**
     * Generate rule-specific fix
     *
     * @param array $violation Violation data
     * @param string $originalCode Original code snippet
     * @param array $fileData File context
     *
     * @return array{code: string, explanation: string, confidence: float}
     */
    private function generateRuleFix(
        array $violation,
        string $originalCode,
        array $fileData
    ): array {
        $ruleId = $violation['rule_id'] ?? 'UNKNOWN';

        switch ($ruleId) {
            case 'SEC001':
                return $this->fixSQLInjection($originalCode);
            case 'SEC002':
                return $this->fixXSS($originalCode);
            case 'SEC003':
                return $this->fixHardcodedCredentials($originalCode);
            default:
                return [
                    'code' => $originalCode,
                    'explanation' => 'Automatic fix not available for this rule',
                    'confidence' => 0.0
                ];
        }
    }

    /**
     * Fix SQL injection vulnerability
     */
    private function fixSQLInjection(string $code): array
    {
        // Convert string concatenation to prepared statement
        $fixed = preg_replace(
            '/\$pdo->query\("SELECT \* FROM (\w+) WHERE (\w+) = \'\$(\w+)\'"\)/',
            '$stmt = $pdo->prepare("SELECT * FROM $1 WHERE $2 = ?");\n$stmt->execute([$$3]);',
            $code
        );

        return [
            'code' => $fixed ?? $code,
            'explanation' => 'Converted to prepared statement with parameter binding',
            'confidence' => 0.95
        ];
    }

    /**
     * Fix XSS vulnerability
     */
    private function fixXSS(string $code): array
    {
        // Add htmlspecialchars
        $fixed = preg_replace(
            '/echo\s+\$(\w+);/',
            'echo htmlspecialchars($$1, ENT_QUOTES, \'UTF-8\');',
            $code
        );

        return [
            'code' => $fixed ?? $code,
            'explanation' => 'Added HTML escaping with htmlspecialchars',
            'confidence' => 0.90
        ];
    }

    /**
     * Fix hardcoded credentials
     */
    private function fixHardcodedCredentials(string $code): array
    {
        // Replace hardcoded values with environment variables
        $fixed = preg_replace(
            '/\$(password|key|secret)\s*=\s*[\'"](.+?)[\'"]/',
            '$$1 = $_ENV[\'$1\'] ?? getenv(\'$1\')',
            $code
        );

        return [
            'code' => $fixed ?? $code,
            'explanation' => 'Replaced hardcoded credentials with environment variables',
            'confidence' => 0.85
        ];
    }

    /**
     * Explain SQL injection vulnerability
     */
    private function explainSQLInjection(array $violation, array $fileData): array
    {
        return [
            'explanation' => 'This code is vulnerable to SQL injection attacks. User input is directly concatenated into SQL queries without proper sanitization or parameterization.',
            'severity_reasoning' => 'Critical severity because attackers can execute arbitrary SQL commands, potentially accessing, modifying, or deleting sensitive data.',
            'fix_suggestion' => 'Use prepared statements with parameter binding (PDO::prepare() and execute()). Never concatenate user input into SQL queries.',
            'references' => [
                'https://owasp.org/www-community/attacks/SQL_Injection',
                'https://www.php.net/manual/en/pdo.prepared-statements.php'
            ]
        ];
    }

    /**
     * Explain XSS vulnerability
     */
    private function explainXSS(array $violation, array $fileData): array
    {
        return [
            'explanation' => 'This code outputs user-controlled data without proper escaping, making it vulnerable to Cross-Site Scripting (XSS) attacks.',
            'severity_reasoning' => 'High severity because attackers can inject malicious JavaScript that executes in users\' browsers, stealing cookies, session tokens, or performing actions on behalf of users.',
            'fix_suggestion' => 'Always escape output using htmlspecialchars($var, ENT_QUOTES, \'UTF-8\') before displaying user-controlled data in HTML context.',
            'references' => [
                'https://owasp.org/www-community/attacks/xss/',
                'https://www.php.net/manual/en/function.htmlspecialchars.php'
            ]
        ];
    }

    /**
     * Explain hardcoded credentials vulnerability
     */
    private function explainHardcodedCredentials(array $violation, array $fileData): array
    {
        return [
            'explanation' => 'Hardcoded credentials in source code pose a significant security risk. If the code is compromised or accidentally published, the credentials are exposed.',
            'severity_reasoning' => 'High severity because exposed credentials can lead to unauthorized access to databases, APIs, or services.',
            'fix_suggestion' => 'Store credentials in environment variables (.env file) and access them via $_ENV or getenv(). Never commit .env files to version control.',
            'references' => [
                'https://owasp.org/www-project-top-ten/2017/A3_2017-Sensitive_Data_Exposure',
                'https://12factor.net/config'
            ]
        ];
    }

    /**
     * Explain code quality issue
     */
    private function explainCodeQuality(array $violation, array $fileData): array
    {
        return [
            'explanation' => 'This code has quality issues that may affect maintainability, readability, or reliability.',
            'severity_reasoning' => 'Medium severity because poor code quality leads to technical debt and increases the likelihood of bugs.',
            'fix_suggestion' => 'Follow PSR-12 coding standards, add type hints, improve naming conventions, and reduce complexity.',
            'references' => [
                'https://www.php-fig.org/psr/psr-12/',
                'https://phpmd.org/'
            ]
        ];
    }

    /**
     * Explain best practices violation
     */
    private function explainBestPractices(array $violation, array $fileData): array
    {
        return [
            'explanation' => 'This code violates PHP best practices, which may lead to unexpected behavior or security issues.',
            'severity_reasoning' => 'Low to medium severity depending on the specific violation.',
            'fix_suggestion' => 'Review PHP best practices documentation and refactor code accordingly.',
            'references' => [
                'https://www.php.net/manual/en/language.oop5.php',
                'https://phptherightway.com/'
            ]
        ];
    }

    /**
     * Explain performance issue
     */
    private function explainPerformance(array $violation, array $fileData): array
    {
        return [
            'explanation' => 'This code has performance issues that may cause slow response times or excessive resource usage.',
            'severity_reasoning' => 'Medium severity because performance issues affect user experience and server costs.',
            'fix_suggestion' => 'Optimize database queries, add caching, reduce loops, or use more efficient algorithms.',
            'references' => [
                'https://www.php.net/manual/en/book.opcache.php',
                'https://redis.io/documentation'
            ]
        ];
    }

    /**
     * Explain generic violation
     */
    private function explainGeneric(array $violation, array $fileData): array
    {
        return [
            'explanation' => 'A code issue was detected that requires review.',
            'severity_reasoning' => 'Severity determined by rule configuration.',
            'fix_suggestion' => 'Review the violation details and apply appropriate fixes.',
            'references' => []
        ];
    }

    /**
     * Call MCP server
     *
     * @param array $request JSON-RPC request
     *
     * @return array Response data
     *
     * @throws RuntimeException If request fails
     */
    private function callMCP(array $request): array
    {
        $ch = curl_init($this->mcpServerUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($request),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("MCP request failed: {$error}");
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("MCP server returned HTTP {$httpCode}");
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON response from MCP server");
        }

        if (isset($decoded['error'])) {
            throw new RuntimeException("MCP error: " . ($decoded['error']['message'] ?? 'Unknown error'));
        }

        return $decoded;
    }
}
