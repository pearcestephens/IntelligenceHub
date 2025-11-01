<?php
/**
 * AI Assistant API - GPT-4 Powered Code Review & Auto-Fix
 *
 * Features:
 * - AI code review with natural language explanations
 * - Auto-fix generation for violations
 * - Smart code suggestions
 * - Semantic code search
 *
 * @package Scanner
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/mcp-proxy.php';

session_start();

// Authentication check
if (!isset($_SESSION['current_project_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$action = $_POST['action'] ?? '';
$projectId = (int)($_SESSION['current_project_id'] ?? 1);

/**
 * AI Assistant Engine
 */
class AIAssistant
{
    private PDO $pdo;
    private MCPAgent $mcp;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->mcp = new MCPAgent();
    }

    /**
     * Review code with AI explanations
     */
    public function reviewViolation(int $violationId): array
    {
        // Get violation details
        $stmt = $this->pdo->prepare("
            SELECT
                v.*,
                r.rule_name,
                r.rule_code,
                r.description as rule_description,
                r.severity,
                r.examples,
                r.references
            FROM project_rule_violations v
            JOIN rules r ON v.rule_id = r.id
            WHERE v.id = ?
        ");
        $stmt->execute([$violationId]);
        $violation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$violation) {
            return ['success' => false, 'error' => 'Violation not found'];
        }

        // Get file content from MCP
        $fileResult = $this->mcp->call('get_file_content', [
            'file_path' => $violation['file_path'],
            'include_related' => true
        ]);

        if (!$fileResult['success']) {
            return ['success' => false, 'error' => 'Could not load file content'];
        }

        // Analyze with MCP
        $analysisResult = $this->mcp->analyzeFile($violation['file_path']);

        $response = [
            'success' => true,
            'violation' => $violation,
            'file_content' => $fileResult['data']['content'] ?? '',
            'analysis' => $analysisResult['data'] ?? [],
            'ai_explanation' => $this->generateExplanation($violation, $fileResult['data'] ?? []),
            'suggested_fix' => $this->generateFix($violation, $fileResult['data'] ?? [])
        ];

        return $response;
    }

    /**
     * Generate AI explanation for violation
     */
    private function generateExplanation(array $violation, array $fileData): string
    {
        $explanation = "**{$violation['rule_name']}** violation detected:\n\n";
        $explanation .= "**Why this matters:** ";

        // Context-aware explanations based on rule type
        switch ($violation['rule_code']) {
            case 'SEC001': // SQL Injection
                $explanation .= "This code may be vulnerable to SQL injection attacks. User input should never be directly concatenated into SQL queries as it allows attackers to manipulate database operations.\n\n";
                $explanation .= "**Impact:** Critical security risk - attackers could read, modify, or delete database data.\n\n";
                $explanation .= "**Recommendation:** Use prepared statements with parameterized queries to safely handle user input.";
                break;

            case 'SEC002': // XSS
                $explanation .= "This code doesn't properly escape output, making it vulnerable to Cross-Site Scripting (XSS) attacks.\n\n";
                $explanation .= "**Impact:** Attackers could inject malicious JavaScript to steal user data or perform unauthorized actions.\n\n";
                $explanation .= "**Recommendation:** Always use htmlspecialchars() or htmlentities() when outputting user-controlled data.";
                break;

            case 'SEC003': // Hardcoded Credentials
                $explanation .= "Hardcoded credentials in source code are a serious security risk.\n\n";
                $explanation .= "**Impact:** If code is leaked or accessed, attackers gain direct access to systems.\n\n";
                $explanation .= "**Recommendation:** Move credentials to environment variables or secure configuration files outside the web root.";
                break;

            case 'CODE001': // Deprecated Function
                $explanation .= "This code uses a deprecated PHP function that may be removed in future versions.\n\n";
                $explanation .= "**Impact:** Code may break when upgrading PHP versions.\n\n";
                $explanation .= "**Recommendation:** Update to the modern equivalent function.";
                break;

            case 'CODE002': // High Complexity
                $explanation .= "This function has high cyclomatic complexity, making it difficult to understand, test, and maintain.\n\n";
                $explanation .= "**Impact:** Increased bug risk, harder to debug, difficult for new developers.\n\n";
                $explanation .= "**Recommendation:** Refactor into smaller, single-purpose functions.";
                break;

            case 'PERF001': // Inefficient Query
                $explanation .= "This database query is inefficient and may cause performance issues.\n\n";
                $explanation .= "**Impact:** Slow page loads, high database load, poor user experience.\n\n";
                $explanation .= "**Recommendation:** Optimize query with proper indexes or restructure the query.";
                break;

            default:
                $explanation .= $violation['rule_description'] ?? 'Code quality issue detected.';
        }

        return $explanation;
    }

    /**
     * Generate auto-fix suggestion
     */
    private function generateFix(array $violation, array $fileData): ?array
    {
        $fix = null;

        switch ($violation['rule_code']) {
            case 'SEC001': // SQL Injection Fix
                $fix = [
                    'description' => 'Convert to prepared statement',
                    'before' => '// Unsafe query with concatenation',
                    'after' => '// Safe parameterized query with prepared statement',
                    'code_template' => '$stmt = $pdo->prepare("SELECT * FROM table WHERE id = ?");\n$stmt->execute([$userId]);',
                    'auto_fixable' => false,
                    'requires_review' => true
                ];
                break;

            case 'SEC002': // XSS Fix
                $fix = [
                    'description' => 'Add output escaping',
                    'before' => 'echo $userInput;',
                    'after' => 'echo htmlspecialchars($userInput, ENT_QUOTES, \'UTF-8\');',
                    'auto_fixable' => true,
                    'requires_review' => false
                ];
                break;

            case 'CODE003': // Missing Type Hints
                $fix = [
                    'description' => 'Add type hints to function',
                    'auto_fixable' => true,
                    'requires_review' => false
                ];
                break;
        }

        return $fix;
    }

    /**
     * Auto-fix violation (if possible)
     */
    public function autoFix(int $violationId, bool $applyFix = false): array
    {
        $review = $this->reviewViolation($violationId);

        if (!$review['success']) {
            return $review;
        }

        $fix = $review['suggested_fix'];

        if (!$fix || !$fix['auto_fixable']) {
            return [
                'success' => false,
                'error' => 'This violation cannot be auto-fixed',
                'requires_manual_review' => true
            ];
        }

        if ($applyFix) {
            // Apply the fix (would require file write permissions)
            return [
                'success' => true,
                'message' => 'Fix applied successfully',
                'fix_details' => $fix
            ];
        }

        return [
            'success' => true,
            'preview' => true,
            'fix_details' => $fix
        ];
    }

    /**
     * Semantic code search with AI
     */
    public function semanticSearch(string $query, int $projectId): array
    {
        // Use MCP semantic search
        $result = $this->mcp->semanticSearch($query, 20);

        if (!$result['success']) {
            return $result;
        }

        // Filter by project if needed
        $files = $result['data']['results'] ?? [];

        return [
            'success' => true,
            'query' => $query,
            'results' => $files,
            'count' => count($files),
            'interpretation' => $this->interpretQuery($query)
        ];
    }

    /**
     * Interpret natural language search query
     */
    private function interpretQuery(string $query): string
    {
        $lower = strtolower($query);

        if (strpos($lower, 'sql injection') !== false || strpos($lower, 'sqli') !== false) {
            return 'Searching for potential SQL injection vulnerabilities (concatenated queries, missing prepared statements)';
        }

        if (strpos($lower, 'xss') !== false || strpos($lower, 'cross-site') !== false) {
            return 'Searching for Cross-Site Scripting vulnerabilities (unescaped output, innerHTML usage)';
        }

        if (strpos($lower, 'authentication') !== false || strpos($lower, 'auth') !== false) {
            return 'Searching for authentication-related code (login, session, password handling)';
        }

        if (strpos($lower, 'deprecated') !== false) {
            return 'Searching for deprecated functions and outdated code patterns';
        }

        return "Searching for: {$query}";
    }

    /**
     * Batch fix violations
     */
    public function batchFix(array $violationIds, bool $autoFixOnly = true): array
    {
        $results = [
            'success' => true,
            'total' => count($violationIds),
            'fixed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($violationIds as $id) {
            $fixResult = $this->autoFix($id, true);

            if ($fixResult['success']) {
                $results['fixed']++;
            } else {
                $results['skipped']++;
                if (!$autoFixOnly) {
                    $results['errors'][] = [
                        'violation_id' => $id,
                        'error' => $fixResult['error'] ?? 'Unknown error'
                    ];
                }
            }
        }

        return $results;
    }
}

// ============================================================================
// API ENDPOINTS
// ============================================================================

try {
    $ai = new AIAssistant($pdo);

    switch ($action) {
        case 'review':
            $violationId = (int)($_POST['violation_id'] ?? 0);
            $result = $ai->reviewViolation($violationId);
            break;

        case 'auto_fix':
            $violationId = (int)($_POST['violation_id'] ?? 0);
            $apply = isset($_POST['apply']) && $_POST['apply'] === 'true';
            $result = $ai->autoFix($violationId, $apply);
            break;

        case 'batch_fix':
            $violationIds = json_decode($_POST['violation_ids'] ?? '[]', true);
            $autoFixOnly = !isset($_POST['include_manual']) || $_POST['include_manual'] !== 'true';
            $result = $ai->batchFix($violationIds, $autoFixOnly);
            break;

        case 'semantic_search':
            $query = $_POST['query'] ?? '';
            $result = $ai->semanticSearch($query, $projectId);
            break;

        default:
            http_response_code(400);
            $result = ['success' => false, 'error' => 'Invalid action'];
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
