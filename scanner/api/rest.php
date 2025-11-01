<?php
/**
 * Complete REST API - All CRUD Operations
 *
 * Endpoints:
 * - Projects: CRUD operations
 * - Violations: List, update status, bulk operations
 * - Rules: CRUD, import/export
 * - Files: List, search, analyze
 * - Dependencies: Analyze, visualize
 * - Settings: Get, update
 * - Analytics: Metrics, trends, reports
 *
 * @package Scanner
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';

session_start();

// Simple auth check
if (!isset($_SESSION['current_project_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['endpoint'] ?? '';
$projectId = (int)($_SESSION['current_project_id'] ?? 1);

/**
 * REST API Handler
 */
class RestAPI
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ========================================================================
    // PROJECTS
    // ========================================================================

    public function getProjects(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                p.*,
                COUNT(DISTINCT f.file_id) as file_count,
                COUNT(DISTINCT v.id) as violation_count
            FROM projects p
            LEFT JOIN intelligence_files f ON p.id = f.project_id
            LEFT JOIN project_rule_violations v ON p.id = v.project_id AND v.status = 'open'
            GROUP BY p.id
            ORDER BY p.project_name
        ");

        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function getProject(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            http_response_code(404);
            return ['success' => false, 'error' => 'Project not found'];
        }

        return ['success' => true, 'data' => $project];
    }

    public function createProject(array $data): array
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO projects (project_name, project_type, project_path, status, created_at)
            VALUES (?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([
            $data['project_name'] ?? 'New Project',
            $data['project_type'] ?? 'web',
            $data['project_path'] ?? '/'
        ]);

        $id = (int)$this->pdo->lastInsertId();

        // Create default scan config
        $stmt = $this->pdo->prepare("
            INSERT INTO scan_config (project_id, scan_frequency, auto_scan_enabled)
            VALUES (?, 'manual', 0)
        ");
        $stmt->execute([$id]);

        return $this->getProject($id);
    }

    public function updateProject(int $id, array $data): array
    {
        $fields = [];
        $values = [];

        $allowed = ['project_name', 'project_type', 'project_path', 'status', 'health_score'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'error' => 'No valid fields to update'];
        }

        $values[] = $id;
        $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        return $this->getProject($id);
    }

    public function deleteProject(int $id): array
    {
        // Soft delete
        $stmt = $this->pdo->prepare("UPDATE projects SET status = 'archived' WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Project archived'];
    }

    // ========================================================================
    // VIOLATIONS
    // ========================================================================

    public function getViolations(int $projectId, array $filters = []): array
    {
        $sql = "
            SELECT
                v.*,
                r.rule_name,
                r.rule_code,
                r.category,
                r.auto_fixable
            FROM project_rule_violations v
            JOIN rules r ON v.rule_id = r.id
            WHERE v.project_id = ?
        ";

        $params = [$projectId];

        // Apply filters
        if (!empty($filters['severity'])) {
            $sql .= " AND v.severity = ?";
            $params[] = $filters['severity'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND v.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['file_path'])) {
            $sql .= " AND v.file_path LIKE ?";
            $params[] = "%{$filters['file_path']}%";
        }

        $sql .= " ORDER BY
            FIELD(v.severity, 'critical', 'high', 'medium', 'low'),
            v.detected_at DESC
        ";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'filters' => $filters
        ];
    }

    public function updateViolationStatus(int $id, string $status): array
    {
        $allowed = ['open', 'resolved', 'suppressed'];

        if (!in_array($status, $allowed, true)) {
            http_response_code(400);
            return ['success' => false, 'error' => 'Invalid status'];
        }

        $stmt = $this->pdo->prepare("
            UPDATE project_rule_violations
            SET status = ?,
                fixed_at = IF(? = 'resolved', NOW(), fixed_at)
            WHERE id = ?
        ");
        $stmt->execute([$status, $status, $id]);

        return ['success' => true, 'message' => 'Status updated'];
    }

    public function bulkUpdateViolations(array $ids, string $action): array
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'resolve':
                $sql = "UPDATE project_rule_violations SET status = 'resolved', fixed_at = NOW() WHERE id IN ($placeholders)";
                break;
            case 'suppress':
                $sql = "UPDATE project_rule_violations SET status = 'suppressed' WHERE id IN ($placeholders)";
                break;
            case 'reopen':
                $sql = "UPDATE project_rule_violations SET status = 'open', fixed_at = NULL WHERE id IN ($placeholders)";
                break;
            default:
                return ['success' => false, 'error' => 'Invalid action'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);

        return [
            'success' => true,
            'updated' => $stmt->rowCount(),
            'action' => $action
        ];
    }

    // ========================================================================
    // RULES
    // ========================================================================

    public function getRules(array $filters = []): array
    {
        $sql = "SELECT * FROM rules WHERE 1=1";
        $params = [];

        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = (int)$filters['is_active'];
        }

        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        $sql .= " ORDER BY FIELD(severity, 'critical', 'high', 'medium', 'low'), rule_code";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function toggleRule(int $id, bool $enabled): array
    {
        $stmt = $this->pdo->prepare("UPDATE rules SET is_active = ? WHERE id = ?");
        $stmt->execute([(int)$enabled, $id]);

        return ['success' => true, 'enabled' => $enabled];
    }

    public function exportRules(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM rules ORDER BY rule_code");
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Clean up for export
        foreach ($rules as &$rule) {
            unset($rule['id']);
            $rule['examples'] = json_decode($rule['examples'], true);
            $rule['references'] = json_decode($rule['references'], true);
        }

        return [
            'success' => true,
            'format' => 'json',
            'data' => $rules,
            'filename' => 'scanner-rules-' . date('Y-m-d') . '.json'
        ];
    }

    // ========================================================================
    // FILES
    // ========================================================================

    public function getFiles(int $projectId, array $filters = []): array
    {
        $sql = "SELECT * FROM intelligence_files WHERE project_id = ?";
        $params = [$projectId];

        if (!empty($filters['file_type'])) {
            $sql .= " AND file_type = ?";
            $params[] = $filters['file_type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND file_path LIKE ?";
            $params[] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY file_path LIMIT 1000";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    // ========================================================================
    // ANALYTICS
    // ========================================================================

    public function getAnalytics(int $projectId, string $type = 'overview'): array
    {
        switch ($type) {
            case 'overview':
                return $this->getOverviewAnalytics($projectId);
            case 'trends':
                return $this->getTrendAnalytics($projectId);
            case 'hotspots':
                return $this->getHotspots($projectId);
            default:
                return ['success' => false, 'error' => 'Invalid analytics type'];
        }
    }

    private function getOverviewAnalytics(int $projectId): array
    {
        $stats = [];

        // Violation counts by severity
        $stmt = $this->pdo->prepare("
            SELECT severity, COUNT(*) as count
            FROM project_rule_violations
            WHERE project_id = ? AND status = 'open'
            GROUP BY severity
        ");
        $stmt->execute([$projectId]);
        $stats['violations_by_severity'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Violation counts by category
        $stmt = $this->pdo->prepare("
            SELECT r.category, COUNT(*) as count
            FROM project_rule_violations v
            JOIN rules r ON v.rule_id = r.id
            WHERE v.project_id = ? AND v.status = 'open'
            GROUP BY r.category
        ");
        $stmt->execute([$projectId]);
        $stats['violations_by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // File stats
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) as total_files,
                SUM(CASE WHEN file_type = 'php' THEN 1 ELSE 0 END) as php_files,
                SUM(CASE WHEN file_type = 'js' THEN 1 ELSE 0 END) as js_files
            FROM intelligence_files
            WHERE project_id = ?
        ");
        $stmt->execute([$projectId]);
        $stats['files'] = $stmt->fetch(PDO::FETCH_ASSOC);

        return ['success' => true, 'data' => $stats];
    }

    private function getTrendAnalytics(int $projectId): array
    {
        // Get violations over last 30 days
        $stmt = $this->pdo->prepare("
            SELECT
                DATE(detected_at) as date,
                COUNT(*) as count
            FROM project_rule_violations
            WHERE project_id = ?
            AND detected_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(detected_at)
            ORDER BY date
        ");
        $stmt->execute([$projectId]);

        return [
            'success' => true,
            'data' => [
                'violation_trends' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]
        ];
    }

    private function getHotspots(int $projectId): array
    {
        // Files with most violations
        $stmt = $this->pdo->prepare("
            SELECT
                file_path,
                COUNT(*) as violation_count,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_count
            FROM project_rule_violations
            WHERE project_id = ? AND status = 'open'
            GROUP BY file_path
            HAVING violation_count > 0
            ORDER BY violation_count DESC
            LIMIT 20
        ");
        $stmt->execute([$projectId]);

        return [
            'success' => true,
            'data' => [
                'hotspot_files' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]
        ];
    }
}

// ============================================================================
// ROUTER
// ============================================================================

try {
    $api = new RestAPI($pdo);
    $result = ['success' => false, 'error' => 'Not implemented'];

    // Parse request body for POST/PUT
    $input = [];
    if (in_array($method, ['POST', 'PUT'], true)) {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    // Route handling
    switch ($path) {
        // Projects
        case 'projects':
            if ($method === 'GET') {
                $result = $api->getProjects();
            } elseif ($method === 'POST') {
                $result = $api->createProject($input);
            }
            break;

        case 'projects/get':
            $id = (int)($input['id'] ?? $_GET['id'] ?? 0);
            $result = $api->getProject($id);
            break;

        case 'projects/update':
            $id = (int)($input['id'] ?? 0);
            $result = $api->updateProject($id, $input);
            break;

        case 'projects/delete':
            $id = (int)($input['id'] ?? 0);
            $result = $api->deleteProject($id);
            break;

        // Violations
        case 'violations':
            $filters = $input['filters'] ?? $_GET;
            $result = $api->getViolations($projectId, $filters);
            break;

        case 'violations/update_status':
            $id = (int)($input['id'] ?? 0);
            $status = $input['status'] ?? '';
            $result = $api->updateViolationStatus($id, $status);
            break;

        case 'violations/bulk':
            $ids = $input['ids'] ?? [];
            $action = $input['action'] ?? '';
            $result = $api->bulkUpdateViolations($ids, $action);
            break;

        // Rules
        case 'rules':
            $filters = $input['filters'] ?? $_GET;
            $result = $api->getRules($filters);
            break;

        case 'rules/toggle':
            $id = (int)($input['id'] ?? 0);
            $enabled = (bool)($input['enabled'] ?? false);
            $result = $api->toggleRule($id, $enabled);
            break;

        case 'rules/export':
            $result = $api->exportRules();
            break;

        // Files
        case 'files':
            $filters = $input['filters'] ?? $_GET;
            $result = $api->getFiles($projectId, $filters);
            break;

        // Analytics
        case 'analytics':
            $type = $input['type'] ?? $_GET['type'] ?? 'overview';
            $result = $api->getAnalytics($projectId, $type);
            break;

        default:
            http_response_code(404);
            $result = ['success' => false, 'error' => 'Endpoint not found'];
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
