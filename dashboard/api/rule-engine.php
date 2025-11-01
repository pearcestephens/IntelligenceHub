<?php
/**
 * Rule Engine API
 *
 * Backend for rule management, learning, and generation
 * Handles CRUD operations for rules, categories, violations, and auto-learning
 *
 * @package CIS\Dashboard\API
 * @version 2.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
require_once __DIR__ . '/../../app.php';

class RuleEngine {
    private PDO $db;
    private const TABLES_CREATED_FLAG = '/tmp/cis_rules_tables_created';

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
        $this->ensureTablesExist();
    }

    /**
     * Ensure all required tables exist
     */
    private function ensureTablesExist(): void {
        // Check if tables already created
        if (file_exists(self::TABLES_CREATED_FLAG)) {
            return;
        }

        try {
            // 1. Rule Categories Table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cis_rule_categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    description TEXT,
                    icon VARCHAR(50),
                    color VARCHAR(20),
                    priority INT DEFAULT 50,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_priority (priority)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 2. Rules Table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cis_rules (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    category_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    rule_text TEXT NOT NULL,
                    example_good TEXT,
                    example_bad TEXT,
                    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
                    applies_to JSON COMMENT 'File patterns, languages, etc.',
                    is_active BOOLEAN DEFAULT TRUE,
                    is_cis_specific BOOLEAN DEFAULT FALSE,
                    auto_learned BOOLEAN DEFAULT FALSE,
                    confidence_score DECIMAL(3,2) DEFAULT 1.00,
                    usage_count INT DEFAULT 0,
                    violation_count INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    created_by INT,
                    FOREIGN KEY (category_id) REFERENCES cis_rule_categories(id) ON DELETE CASCADE,
                    INDEX idx_category (category_id),
                    INDEX idx_priority (priority),
                    INDEX idx_active (is_active),
                    INDEX idx_cis_specific (is_cis_specific),
                    FULLTEXT idx_search (title, description, rule_text)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 3. Rule Violations Log
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cis_rule_violations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    rule_id INT NOT NULL,
                    file_path VARCHAR(500),
                    line_number INT,
                    violation_text TEXT,
                    context JSON COMMENT 'Surrounding code, user correction, etc.',
                    severity ENUM('critical', 'high', 'medium', 'low', 'info') DEFAULT 'medium',
                    was_corrected BOOLEAN DEFAULT FALSE,
                    correction_applied TEXT,
                    detected_by ENUM('bot', 'user', 'scanner', 'ai') DEFAULT 'bot',
                    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    corrected_at TIMESTAMP NULL,
                    FOREIGN KEY (rule_id) REFERENCES cis_rules(id) ON DELETE CASCADE,
                    INDEX idx_rule (rule_id),
                    INDEX idx_detected (detected_at),
                    INDEX idx_corrected (was_corrected)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 4. Rule Learning Log
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cis_rule_learning_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    pattern_detected TEXT NOT NULL,
                    context JSON COMMENT 'Where pattern was found, frequency, etc.',
                    suggested_rule TEXT,
                    confidence DECIMAL(3,2),
                    occurrences INT DEFAULT 1,
                    status ENUM('pending', 'approved', 'rejected', 'auto_applied') DEFAULT 'pending',
                    rule_id INT NULL COMMENT 'If approved, link to created rule',
                    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    reviewed_at TIMESTAMP NULL,
                    reviewed_by INT NULL,
                    FOREIGN KEY (rule_id) REFERENCES cis_rules(id) ON DELETE SET NULL,
                    INDEX idx_status (status),
                    INDEX idx_confidence (confidence),
                    INDEX idx_detected (detected_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 5. User Preferences
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cis_user_rule_preferences (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    rule_id INT NOT NULL,
                    is_enabled BOOLEAN DEFAULT TRUE,
                    custom_priority ENUM('critical', 'high', 'medium', 'low') NULL,
                    notes TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (rule_id) REFERENCES cis_rules(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_user_rule (user_id, rule_id),
                    INDEX idx_user (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 6. MCP Tool Usage Tracking
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cis_mcp_tool_usage (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tool_name VARCHAR(50) NOT NULL,
                    context TEXT COMMENT 'When/why tool was used',
                    success BOOLEAN DEFAULT TRUE,
                    execution_time_ms INT,
                    user_rating TINYINT NULL COMMENT '1-5 stars',
                    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_tool (tool_name),
                    INDEX idx_used (used_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Seed initial categories
            $this->seedInitialCategories();

            // Seed initial CIS rules
            $this->seedCISRules();

            // Mark tables as created
            touch(self::TABLES_CREATED_FLAG);

        } catch (Exception $e) {
            error_log("Rule Engine table creation error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Seed initial rule categories
     */
    private function seedInitialCategories(): void {
        $categories = [
            ['name' => 'Security', 'description' => 'Security best practices and vulnerability prevention', 'icon' => 'shield-alt', 'color' => '#dc3545', 'priority' => 100],
            ['name' => 'Performance', 'description' => 'Performance optimization and efficiency', 'icon' => 'tachometer-alt', 'color' => '#28a745', 'priority' => 90],
            ['name' => 'Database', 'description' => 'Database queries, indexes, and optimization', 'icon' => 'database', 'color' => '#007bff', 'priority' => 85],
            ['name' => 'Code Quality', 'description' => 'Code style, naming, and maintainability', 'icon' => 'code', 'color' => '#6f42c1', 'priority' => 80],
            ['name' => 'Architecture', 'description' => 'System design and structural patterns', 'icon' => 'sitemap', 'color' => '#fd7e14', 'priority' => 75],
            ['name' => 'Error Handling', 'description' => 'Exception handling and error recovery', 'icon' => 'exclamation-triangle', 'color' => '#ffc107', 'priority' => 70],
            ['name' => 'Testing', 'description' => 'Unit tests, integration tests, coverage', 'icon' => 'vial', 'color' => '#20c997', 'priority' => 65],
            ['name' => 'Documentation', 'description' => 'Comments, PHPDoc, README files', 'icon' => 'book', 'color' => '#17a2b8', 'priority' => 60],
            ['name' => 'CIS Specific', 'description' => 'Vape Shed / CIS project-specific rules', 'icon' => 'building', 'color' => '#e83e8c', 'priority' => 95],
            ['name' => 'MCP Tools', 'description' => 'How to use MCP dispatcher tools effectively', 'icon' => 'tools', 'color' => '#6610f2', 'priority' => 90],
        ];

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO cis_rule_categories (name, description, icon, color, priority)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($categories as $cat) {
            $stmt->execute([$cat['name'], $cat['description'], $cat['icon'], $cat['color'], $cat['priority']]);
        }
    }

    /**
     * Seed initial CIS-specific rules
     */
    private function seedCISRules(): void {
        // Get category IDs
        $catIds = [];
        $result = $this->db->query("SELECT id, name FROM cis_rule_categories");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $catIds[$row['name']] = $row['id'];
        }

        $rules = [
            // Security Rules
            [
                'category' => 'Security',
                'title' => 'Always use prepared statements for SQL queries',
                'description' => 'Never concatenate user input into SQL queries. Use PDO prepared statements with parameter binding.',
                'rule_text' => '✅ DO: $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?"); $stmt->execute([$email]);
❌ DON\'T: $result = $pdo->query("SELECT * FROM users WHERE email = \'" . $email . "\'");',
                'example_good' => '$stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
$stmt->execute([$name, $price]);',
                'example_bad' => '$pdo->query("INSERT INTO products (name, price) VALUES (\'" . $name . "\', " . $price . ")");',
                'priority' => 'critical',
                'applies_to' => json_encode(['*.php', 'SQL']),
            ],
            [
                'category' => 'Security',
                'title' => 'Escape all HTML output to prevent XSS',
                'description' => 'Use htmlspecialchars() with ENT_QUOTES for all user-generated content displayed in HTML.',
                'rule_text' => '✅ DO: echo htmlspecialchars($userInput, ENT_QUOTES, \'UTF-8\');
❌ DON\'T: echo $userInput;',
                'example_good' => '<h1><?php echo htmlspecialchars($title, ENT_QUOTES, \'UTF-8\'); ?></h1>',
                'example_bad' => '<h1><?php echo $title; ?></h1>',
                'priority' => 'critical',
                'applies_to' => json_encode(['*.php', 'HTML']),
            ],

            // CIS Specific Rules
            [
                'category' => 'CIS Specific',
                'title' => 'All scripts must include app.php at the top',
                'description' => 'Every PHP file requiring database access or core functions must include app.php',
                'rule_text' => '✅ DO: require_once $_SERVER[\'DOCUMENT_ROOT\'] . \'/app.php\';
❌ DON\'T: Skip this include or use relative paths',
                'example_good' => '<?php
require_once $_SERVER[\'DOCUMENT_ROOT\'] . \'/app.php\';
// Now you have $pdo, CISLib, etc.',
                'example_bad' => '<?php
// Direct database connection - WRONG!
$pdo = new PDO(...);',
                'priority' => 'high',
                'applies_to' => json_encode(['modules/**/*.php', 'api/**/*.php']),
                'is_cis_specific' => true,
            ],
            [
                'category' => 'CIS Specific',
                'title' => 'Use CIS module structure: controllers, models, views, lib, api',
                'description' => 'All modules must follow standardized directory structure',
                'rule_text' => 'Standard structure:
modules/{module_name}/
├── controllers/    (HTTP request handlers)
├── models/         (Data access layer)
├── views/          (UI templates)
├── lib/            (Module utilities)
├── api/            (JSON endpoints)
└── tests/          (Unit tests)',
                'example_good' => 'modules/transfers/
├── controllers/TransferController.php
├── models/Transfer.php
├── views/pack.php
├── lib/Validation.php
├── api/submit.php',
                'example_bad' => 'modules/transfers/
├── everything_in_root.php
├── random_structure.php',
                'priority' => 'high',
                'applies_to' => json_encode(['modules/**']),
                'is_cis_specific' => true,
            ],

            // Database Rules
            [
                'category' => 'Database',
                'title' => 'Add indexes for all foreign keys',
                'description' => 'Every foreign key column must have an index for join performance',
                'rule_text' => '✅ DO: CREATE INDEX idx_outlet_id ON table_name(outlet_id);
❌ DON\'T: Create foreign keys without indexes',
                'example_good' => 'ALTER TABLE stock_transfers ADD INDEX idx_outlet_from (outlet_from);',
                'example_bad' => 'ALTER TABLE stock_transfers ADD COLUMN outlet_from INT; -- Missing index!',
                'priority' => 'high',
                'applies_to' => json_encode(['*.sql', 'migrations']),
            ],

            // Performance Rules
            [
                'category' => 'Performance',
                'title' => 'Avoid SELECT * in production queries',
                'description' => 'Always specify exact columns needed to reduce memory usage and improve performance',
                'rule_text' => '✅ DO: SELECT id, name, email FROM users WHERE active = 1
❌ DON\'T: SELECT * FROM users WHERE active = 1',
                'example_good' => '$stmt = $pdo->prepare("SELECT id, name, created_at FROM products WHERE active = 1");',
                'example_bad' => '$stmt = $pdo->prepare("SELECT * FROM products WHERE active = 1");',
                'priority' => 'medium',
                'applies_to' => json_encode(['*.php', 'SQL']),
            ],

            // MCP Tool Rules
            [
                'category' => 'MCP Tools',
                'title' => 'Use CrawlerTool for comprehensive website testing',
                'description' => 'When asked to test a website comprehensively, use CrawlerTool with mode=full',
                'rule_text' => 'Available modes:
- quick: Basic crawl only (30 sec)
- authenticated: Login + crawl
- interactive: Full interaction (clicks, forms)
- full: Complete audit (crawl, login, interact, screenshots, errors, GPT Vision) ⭐
- errors_only: Check 404s and JS errors only',
                'example_good' => 'POST https://gpt.ecigdis.co.nz/mcp/dispatcher.php
tool=crawler&mode=full&url=https://staff.vapeshed.co.nz&profile=cis_desktop',
                'example_bad' => 'Manually navigating and testing - use the tool!',
                'priority' => 'high',
                'applies_to' => json_encode(['testing', 'QA', 'website-audit']),
            ],
        ];

        $stmt = $this->db->prepare("
            INSERT INTO cis_rules (
                category_id, title, description, rule_text, example_good, example_bad,
                priority, applies_to, is_cis_specific, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");

        foreach ($rules as $rule) {
            if (!isset($catIds[$rule['category']])) continue;

            $stmt->execute([
                $catIds[$rule['category']],
                $rule['title'],
                $rule['description'],
                $rule['rule_text'],
                $rule['example_good'] ?? null,
                $rule['example_bad'] ?? null,
                $rule['priority'],
                $rule['applies_to'],
                $rule['is_cis_specific'] ?? false
            ]);
        }
    }

    /**
     * Get all categories with rule counts
     */
    public function getCategories(): array {
        $stmt = $this->db->query("
            SELECT
                c.*,
                COUNT(r.id) as rule_count,
                SUM(CASE WHEN r.is_active = 1 THEN 1 ELSE 0 END) as active_count
            FROM cis_rule_categories c
            LEFT JOIN cis_rules r ON c.id = r.category_id
            GROUP BY c.id
            ORDER BY c.priority DESC, c.name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all rules, optionally filtered by category
     */
    public function getRules(?int $categoryId = null, ?string $search = null): array {
        $sql = "
            SELECT
                r.*,
                c.name as category_name,
                c.icon as category_icon,
                c.color as category_color
            FROM cis_rules r
            JOIN cis_rule_categories c ON r.category_id = c.id
            WHERE 1=1
        ";

        $params = [];

        if ($categoryId) {
            $sql .= " AND r.category_id = ?";
            $params[] = $categoryId;
        }

        if ($search) {
            $sql .= " AND MATCH(r.title, r.description, r.rule_text) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $search;
        }

        $sql .= " ORDER BY
            FIELD(r.priority, 'critical', 'high', 'medium', 'low'),
            r.usage_count DESC,
            r.title
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new rule
     */
    public function createRule(array $data): array {
        $stmt = $this->db->prepare("
            INSERT INTO cis_rules (
                category_id, title, description, rule_text, example_good, example_bad,
                priority, applies_to, is_cis_specific, is_active, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['category_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['rule_text'],
            $data['example_good'] ?? null,
            $data['example_bad'] ?? null,
            $data['priority'] ?? 'medium',
            json_encode($data['applies_to'] ?? []),
            $data['is_cis_specific'] ?? false,
            $data['is_active'] ?? true,
            $data['user_id'] ?? null
        ]);

        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    /**
     * Log rule violation for learning
     */
    public function logViolation(array $data): array {
        $stmt = $this->db->prepare("
            INSERT INTO cis_rule_violations (
                rule_id, file_path, line_number, violation_text, context,
                severity, detected_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['rule_id'],
            $data['file_path'] ?? null,
            $data['line_number'] ?? null,
            $data['violation_text'],
            json_encode($data['context'] ?? []),
            $data['severity'] ?? 'medium',
            $data['detected_by'] ?? 'bot'
        ]);

        // Increment violation count on rule
        $this->db->prepare("UPDATE cis_rules SET violation_count = violation_count + 1 WHERE id = ?")
            ->execute([$data['rule_id']]);

        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    /**
     * Learn pattern and suggest new rule
     */
    public function learnPattern(array $data): array {
        // Check if similar pattern already exists
        $stmt = $this->db->prepare("
            SELECT * FROM cis_rule_learning_log
            WHERE pattern_detected = ?
            AND status IN ('pending', 'approved')
            LIMIT 1
        ");
        $stmt->execute([$data['pattern']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Increment occurrences
            $this->db->prepare("
                UPDATE cis_rule_learning_log
                SET occurrences = occurrences + 1
                WHERE id = ?
            ")->execute([$existing['id']]);

            return ['success' => true, 'existing' => true, 'id' => $existing['id']];
        }

        // Create new learning entry
        $stmt = $this->db->prepare("
            INSERT INTO cis_rule_learning_log (
                pattern_detected, context, suggested_rule, confidence, status
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['pattern'],
            json_encode($data['context'] ?? []),
            $data['suggested_rule'] ?? null,
            $data['confidence'] ?? 0.5,
            'pending'
        ]);

        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    /**
     * Get MCP tool usage statistics
     */
    public function getToolUsageStats(): array {
        $stmt = $this->db->query("
            SELECT
                tool_name,
                COUNT(*) as usage_count,
                AVG(execution_time_ms) as avg_time,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100 as success_rate,
                AVG(user_rating) as avg_rating
            FROM cis_mcp_tool_usage
            GROUP BY tool_name
            ORDER BY usage_count DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle API requests
try {
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';
    $engine = new RuleEngine();

    switch ($action) {
        case 'categories':
            echo json_encode(['success' => true, 'data' => $engine->getCategories()]);
            break;

        case 'rules':
            $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            $search = $_GET['search'] ?? null;
            echo json_encode(['success' => true, 'data' => $engine->getRules($categoryId, $search)]);
            break;

        case 'create_rule':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($engine->createRule($data));
            break;

        case 'log_violation':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($engine->logViolation($data));
            break;

        case 'learn_pattern':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($engine->learnPattern($data));
            break;

        case 'tool_stats':
            echo json_encode(['success' => true, 'data' => $engine->getToolUsageStats()]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
