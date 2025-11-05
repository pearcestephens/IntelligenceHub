<?php

namespace BotDeployment\Controllers;

use BotDeployment\Config\Connection;
use BotDeployment\Repositories\BotRepository;
use BotDeployment\Services\{Logger, SecurityManager, MetricsCollector, CacheManager};
use PDO;

/**
 * DashboardController - Web UI Controller for Bot Management
 *
 * Features:
 * - Bot listing and filtering
 * - Bot details and configuration
 * - Deploy/edit/delete bots
 * - Execute bots manually
 * - View execution logs
 * - Real-time metrics
 * - System health
 */
class DashboardController
{
    private PDO $pdo;
    private BotRepository $botRepo;
    private ?Logger $logger;
    private ?SecurityManager $security;
    private ?MetricsCollector $metrics;
    private ?CacheManager $cache;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pdo = Connection::get();
        $this->botRepo = new BotRepository($this->pdo);

        // Initialize services
        $this->logger = new Logger(null, 'dashboard');
        $this->security = new SecurityManager($this->pdo, $this->logger);
        $this->metrics = new MetricsCollector($this->pdo);
        $this->cache = new CacheManager();
    }

    /**
     * Main dashboard page
     */
    public function index(): void
    {
        $this->requireAuth();

        // Get summary stats (cached for 60 seconds)
        $stats = $this->cache->remember('dashboard_stats', function() {
            return [
                'total_bots' => $this->botRepo->count(),
                'active_bots' => $this->botRepo->count(['status' => 'active']),
                'scheduled_bots' => $this->botRepo->count(['has_schedule' => true]),
                'total_executions' => $this->getExecutionCount(),
                'recent_executions' => $this->getRecentExecutions(10),
                'system_health' => $this->getSystemHealth()
            ];
        }, 60);

        $this->render('dashboard', $stats);
    }

    /**
     * List all bots with filters
     */
    public function botList(): void
    {
        $this->requireAuth();

        // Get filters from request
        $filters = [
            'status' => $_GET['status'] ?? null,
            'role' => $_GET['role'] ?? null,
            'search' => $_GET['search'] ?? null,
            'project_id' => $_GET['project_id'] ?? null
        ];

        // Remove null filters
        $filters = array_filter($filters);

        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;

        $bots = $this->botRepo->findAll($filters, [
            'order_by' => 'created_at',
            'order_dir' => 'DESC',
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage
        ]);

        $total = $this->botRepo->count($filters);
        $totalPages = ceil($total / $perPage);

        $this->render('bot-list', [
            'bots' => $bots,
            'filters' => $filters,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'per_page' => $perPage,
                'total_items' => $total
            ]
        ]);
    }

    /**
     * Show single bot details
     */
    public function botDetail(): void
    {
        $this->requireAuth();

        $botId = (int)($_GET['id'] ?? 0);

        if (!$botId) {
            $this->error('Bot ID required');
            return;
        }

        $bot = $this->botRepo->findById($botId);

        if (!$bot) {
            $this->error('Bot not found');
            return;
        }

        // Get recent executions
        $executions = $this->getBotExecutions($botId, 20);

        // Get metrics for this bot
        $metricStats = $this->getBotMetrics($botId);

        $this->render('bot-detail', [
            'bot' => $bot,
            'executions' => $executions,
            'metrics' => $metricStats
        ]);
    }

    /**
     * Show bot creation form
     */
    public function createForm(): void
    {
        $this->requireAuth();

        $this->render('bot-form', [
            'mode' => 'create',
            'bot' => null,
            'roles' => $this->getAvailableRoles(),
            'projects' => $this->getProjects()
        ]);
    }

    /**
     * Handle bot creation
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $this->validateCSRF();

        try {
            $data = $this->validateBotData($_POST);

            $botId = $this->botRepo->create($data);

            $this->logger->info("Bot created", ['bot_id' => $botId, 'name' => $data['name']]);
            $this->metrics->increment('bot.created', 1, ['role' => $data['role']]);

            $this->redirect("/dashboard/bot?id={$botId}&success=created");
        } catch (\Exception $e) {
            $this->logger->error("Bot creation failed", ['error' => $e->getMessage()]);
            $this->render('bot-form', [
                'mode' => 'create',
                'bot' => $_POST,
                'error' => $e->getMessage(),
                'roles' => $this->getAvailableRoles(),
                'projects' => $this->getProjects()
            ]);
        }
    }

    /**
     * Show bot edit form
     */
    public function editForm(): void
    {
        $this->requireAuth();

        $botId = (int)($_GET['id'] ?? 0);
        $bot = $this->botRepo->findById($botId);

        if (!$bot) {
            $this->error('Bot not found');
            return;
        }

        $this->render('bot-form', [
            'mode' => 'edit',
            'bot' => $bot,
            'roles' => $this->getAvailableRoles(),
            'projects' => $this->getProjects()
        ]);
    }

    /**
     * Handle bot update
     */
    public function update(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $this->validateCSRF();

        $botId = (int)($_POST['id'] ?? 0);

        if (!$botId) {
            $this->error('Bot ID required');
            return;
        }

        try {
            $data = $this->validateBotData($_POST);

            $this->botRepo->update($botId, $data);

            $this->logger->info("Bot updated", ['bot_id' => $botId]);
            $this->metrics->increment('bot.updated');

            $this->redirect("/dashboard/bot?id={$botId}&success=updated");
        } catch (\Exception $e) {
            $this->logger->error("Bot update failed", ['bot_id' => $botId, 'error' => $e->getMessage()]);
            $this->render('bot-form', [
                'mode' => 'edit',
                'bot' => array_merge($this->botRepo->findById($botId), $_POST),
                'error' => $e->getMessage(),
                'roles' => $this->getAvailableRoles(),
                'projects' => $this->getProjects()
            ]);
        }
    }

    /**
     * Delete bot
     */
    public function delete(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $this->validateCSRF();

        $botId = (int)($_POST['id'] ?? 0);

        if (!$botId) {
            $this->error('Bot ID required');
            return;
        }

        try {
            $bot = $this->botRepo->findById($botId);
            $this->botRepo->delete($botId);

            $this->logger->warning("Bot deleted", ['bot_id' => $botId, 'name' => $bot['name']]);
            $this->metrics->increment('bot.deleted');

            $this->redirect("/dashboard/bots?success=deleted");
        } catch (\Exception $e) {
            $this->logger->error("Bot deletion failed", ['bot_id' => $botId, 'error' => $e->getMessage()]);
            $this->redirect("/dashboard/bot?id={$botId}&error=delete_failed");
        }
    }

    /**
     * Execute bot manually
     */
    public function execute(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $this->validateCSRF();

        $botId = (int)($_POST['bot_id'] ?? 0);
        $input = $_POST['input'] ?? '';

        if (!$botId) {
            $this->json(['error' => 'Bot ID required'], 400);
            return;
        }

        $bot = $this->botRepo->findById($botId);

        if (!$bot) {
            $this->json(['error' => 'Bot not found'], 404);
            return;
        }

        try {
            $startTime = microtime(true);

            // Execute bot (simplified - would call actual AI agent)
            $result = $this->executeBotLogic($bot, $input);

            $duration = (microtime(true) - $startTime) * 1000;

            // Log execution
            $executionId = $this->logExecution($botId, $input, $result, $duration);

            // Record metrics
            $this->metrics->timing('bot.execution_time', $duration, [
                'bot_id' => $botId,
                'role' => $bot['role']
            ]);
            $this->metrics->increment('bot.executions', 1, ['role' => $bot['role']]);

            $this->logger->info("Bot executed manually", [
                'bot_id' => $botId,
                'execution_id' => $executionId,
                'duration_ms' => round($duration, 2)
            ]);

            $this->json([
                'success' => true,
                'execution_id' => $executionId,
                'result' => $result,
                'duration_ms' => round($duration, 2)
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Bot execution failed", [
                'bot_id' => $botId,
                'error' => $e->getMessage()
            ]);

            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get execution logs
     */
    public function logs(): void
    {
        $this->requireAuth();

        $botId = (int)($_GET['bot_id'] ?? 0);
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;

        if (!$botId) {
            $this->error('Bot ID required');
            return;
        }

        $executions = $this->getBotExecutions($botId, $perPage, ($page - 1) * $perPage);
        $total = $this->getExecutionCount($botId);
        $totalPages = ceil($total / $perPage);

        $this->render('bot-logs', [
            'bot_id' => $botId,
            'executions' => $executions,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'per_page' => $perPage,
                'total_items' => $total
            ]
        ]);
    }

    /**
     * Get metrics dashboard
     */
    public function metrics(): void
    {
        $this->requireAuth();

        $hours = (int)($_GET['hours'] ?? 24);
        $start = time() - ($hours * 3600);
        $end = time();

        // Get all metric names
        $metricNames = $this->metrics->getMetricNames();

        // Get stats for key metrics
        $metricStats = [];
        foreach (['bot.executions', 'bot.execution_time', 'bot.created'] as $name) {
            $metricStats[$name] = $this->metrics->getStats($name, $start, $end);
        }

        $this->render('metrics', [
            'hours' => $hours,
            'metric_names' => $metricNames,
            'metric_stats' => $metricStats
        ]);
    }

    /**
     * API endpoint for real-time metrics
     */
    public function metricsApi(): void
    {
        $this->requireAuth();

        $name = $_GET['name'] ?? '';
        $hours = (int)($_GET['hours'] ?? 1);

        if (!$name) {
            $this->json(['error' => 'Metric name required'], 400);
            return;
        }

        $start = time() - ($hours * 3600);
        $end = time();

        $values = $this->metrics->get($name, $start, $end);
        $stats = $this->metrics->getStats($name, $start, $end);

        $this->json([
            'name' => $name,
            'timeframe' => "{$hours}h",
            'values' => $values,
            'stats' => $stats
        ]);
    }

    /**
     * System health check
     */
    public function health(): void
    {
        $this->requireAuth();

        $health = $this->getSystemHealth();

        $this->json($health);
    }

    // ========== Helper Methods ==========

    /**
     * Validate bot data
     */
    private function validateBotData(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($data['role'])) {
            $errors[] = 'Role is required';
        }

        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }

        return [
            'name' => trim($data['name']),
            'role' => $data['role'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'active',
            'configuration' => $data['configuration'] ?? '{}',
            'project_id' => (int)($data['project_id'] ?? 999)
        ];
    }

    /**
     * Execute bot logic (placeholder)
     */
    private function executeBotLogic(array $bot, string $input): array
    {
        // This would call AIAgentService in production
        // For now, return mock result
        return [
            'output' => "Bot '{$bot['name']}' processed: {$input}",
            'status' => 'success',
            'tokens_used' => 150
        ];
    }

    /**
     * Log bot execution
     */
    private function logExecution(int $botId, string $input, array $result, float $duration): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO bot_executions
            (bot_id, input_data, output_data, execution_time, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $botId,
            $input,
            json_encode($result),
            $duration,
            $result['status'] ?? 'success'
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get bot executions
     */
    private function getBotExecutions(int $botId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM bot_executions
            WHERE bot_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$botId, $limit, $offset]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get execution count
     */
    private function getExecutionCount(?int $botId = null): int
    {
        if ($botId) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM bot_executions WHERE bot_id = ?");
            $stmt->execute([$botId]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM bot_executions");
        }

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get recent executions
     */
    private function getRecentExecutions(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.*, b.name as bot_name
            FROM bot_executions e
            JOIN bots b ON e.bot_id = b.id
            ORDER BY e.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get bot metrics
     */
    private function getBotMetrics(int $botId): array
    {
        $start = time() - (24 * 3600); // Last 24 hours
        $end = time();

        return [
            'executions' => $this->metrics->getStats('bot.executions', $start, $end, ['bot_id' => $botId]),
            'execution_time' => $this->metrics->getStats('bot.execution_time', $start, $end, ['bot_id' => $botId])
        ];
    }

    /**
     * Get available bot roles
     */
    private function getAvailableRoles(): array
    {
        return [
            'general' => 'General Assistant',
            'analyst' => 'Data Analyst',
            'reporter' => 'Reporter',
            'scheduler' => 'Scheduler',
            'monitor' => 'Monitor',
            'custom' => 'Custom'
        ];
    }

    /**
     * Get projects
     */
    private function getProjects(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, project_name
            FROM projects
            WHERE status = 'active'
            ORDER BY project_name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get system health
     */
    private function getSystemHealth(): array
    {
        return [
            'database' => $this->pdo ? 'connected' : 'disconnected',
            'memory' => memory_get_usage(true),
            'disk' => disk_free_space(__DIR__),
            'uptime' => null // Would need system call
        ];
    }

    // ========== Auth & Security ==========

    /**
     * Require authentication
     */
    private function requireAuth(): void
    {
        session_start();

        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Check rate limit
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!$this->security->checkRateLimit($ip, $_SERVER['REQUEST_URI'] ?? '/')) {
            $this->error('Rate limit exceeded', 429);
        }

        // Check blacklist
        if ($this->security->isBlacklisted($ip)) {
            $this->error('Access denied', 403);
        }
    }

    /**
     * Require POST method
     */
    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
        }
    }

    /**
     * Validate CSRF token
     */
    private function validateCSRF(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        $sessionId = session_id();

        if (!$this->security->validateCSRFToken($token, $sessionId)) {
            $this->error('Invalid CSRF token', 403);
        }
    }

    // ========== Response Methods ==========

    /**
     * Render view
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . "/../../views/{$view}.php";

        if (!file_exists($viewPath)) {
            $this->error("View not found: {$view}");
            return;
        }

        require $viewPath;
    }

    /**
     * JSON response
     */
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redirect
     */
    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Error response
     */
    private function error(string $message, int $status = 400): void
    {
        http_response_code($status);
        echo "<h1>Error {$status}</h1><p>{$message}</p>";
        exit;
    }
}
