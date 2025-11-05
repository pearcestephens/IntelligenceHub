<?php
/**
 * Bot Controller
 *
 * RESTful API endpoints for bot management and execution
 *
 * @package BotDeployment\Controllers
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Controllers;

use BotDeployment\Config\Config;
use BotDeployment\Http\Request;
use BotDeployment\Http\Response;
use BotDeployment\Models\Bot;
use BotDeployment\Models\Session;
use BotDeployment\Repositories\BotRepository;
use BotDeployment\Repositories\SessionRepository;
use BotDeployment\Services\BotExecutionService;
use BotDeployment\Services\SchedulerService;
use BotDeployment\Database\Connection;

class BotController
{
    private Config $config;
    private Connection $db;
    private BotRepository $botRepo;
    private SessionRepository $sessionRepo;
    private BotExecutionService $botExecution;
    private SchedulerService $scheduler;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->db = Connection::getInstance();
        $this->botRepo = new BotRepository($this->db);
        $this->sessionRepo = new SessionRepository($this->db);
        $this->botExecution = new BotExecutionService();
        $this->scheduler = new SchedulerService();
    }

    /**
     * GET /api/bots
     * List all bots with optional filters
     */
    public function index(Request $request): void
    {
        try {
            $filters = [];

            // Status filter
            if ($request->query('status')) {
                $filters['status'] = $request->query('status');
            }

            // Role filter
            if ($request->query('role')) {
                $filters['role'] = $request->query('role');
            }

            // Search query
            $search = $request->query('search');
            if ($search) {
                $bots = $this->botRepo->search($search, $filters);
            } else {
                $bots = $this->botRepo->findAll($filters);
            }

            // Include metrics if requested
            $includeMetrics = $request->query('with_metrics') === 'true';
            if ($includeMetrics) {
                $bots = $this->botRepo->getAllWithMetrics();
            }

            // Convert to arrays
            $botsData = array_map(fn($bot) => $bot->toArray(), $bots);

            Response::success([
                'bots' => $botsData,
                'count' => count($botsData),
                'filters' => $filters
            ], 'Bots retrieved successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to retrieve bots', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/bots/:id
     * Get single bot details
     */
    public function show(Request $request): void
    {
        try {
            $botId = (int) $request->param('id');

            $bot = $this->botRepo->find($botId);

            if (!$bot) {
                Response::notFound("Bot not found: {$botId}");
                return;
            }

            // Include metrics if requested
            $includeMetrics = $request->query('with_metrics') === 'true';
            $data = $bot->toArray();

            if ($includeMetrics) {
                $metrics = $this->botRepo->getPerformanceMetrics($botId);
                $data['metrics'] = $metrics;
            }

            Response::success($data, 'Bot retrieved successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to retrieve bot', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/bots
     * Create new bot
     */
    public function store(Request $request): void
    {
        try {
            // Validate input
            $validated = $request->validate([
                'bot_name' => 'required|string|min:3|max:100',
                'bot_role' => 'required|in:security,developer,analyst,monitor,general',
                'system_prompt' => 'required|string|min:10',
                'schedule_cron' => 'string',
                'status' => 'in:active,paused,archived',
                'config' => 'array'
            ]);

            // Validate cron if provided
            if (!empty($validated['schedule_cron'])) {
                $cronValidation = $this->scheduler->validateCronExpression($validated['schedule_cron']);
                if (!$cronValidation['valid']) {
                    Response::validationError([
                        'schedule_cron' => [$cronValidation['error']]
                    ]);
                    return;
                }
            }

            // Create bot model
            $bot = new Bot();
            $bot->setBotName($validated['bot_name'])
                ->setBotRole($validated['bot_role'])
                ->setSystemPrompt($validated['system_prompt'])
                ->setStatus($validated['status'] ?? Bot::STATUS_ACTIVE);

            if (!empty($validated['schedule_cron'])) {
                $bot->setScheduleCron($validated['schedule_cron']);
            }

            if (!empty($validated['config'])) {
                foreach ($validated['config'] as $key => $value) {
                    $bot->setConfig($key, $value);
                }
            }

            // Save to database
            $botId = $this->botRepo->create($bot);

            // Update next execution time if scheduled
            if ($bot->isScheduled()) {
                $this->scheduler->updateNextExecutionTime($botId);
            }

            // Reload with ID
            $bot = $this->botRepo->find($botId);

            Response::created($bot->toArray(), 'Bot created successfully');

        } catch (\Exception $e) {
            // Check if validation error
            $decoded = json_decode($e->getMessage(), true);
            if (isset($decoded['validation_errors'])) {
                Response::validationError($decoded['validation_errors']);
                return;
            }

            Response::serverError('Failed to create bot', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * PUT /api/bots/:id
     * Update existing bot
     */
    public function update(Request $request): void
    {
        try {
            $botId = (int) $request->param('id');

            $bot = $this->botRepo->find($botId);

            if (!$bot) {
                Response::notFound("Bot not found: {$botId}");
                return;
            }

            // Validate input (all optional for update)
            $validated = $request->validate([
                'bot_name' => 'string|min:3|max:100',
                'bot_role' => 'in:security,developer,analyst,monitor,general',
                'system_prompt' => 'string|min:10',
                'schedule_cron' => 'string',
                'status' => 'in:active,paused,archived',
                'config' => 'array'
            ]);

            // Update fields
            if (isset($validated['bot_name'])) {
                $bot->setBotName($validated['bot_name']);
            }
            if (isset($validated['bot_role'])) {
                $bot->setBotRole($validated['bot_role']);
            }
            if (isset($validated['system_prompt'])) {
                $bot->setSystemPrompt($validated['system_prompt']);
            }
            if (isset($validated['status'])) {
                $bot->setStatus($validated['status']);
            }
            if (isset($validated['schedule_cron'])) {
                // Validate cron
                $cronValidation = $this->scheduler->validateCronExpression($validated['schedule_cron']);
                if (!$cronValidation['valid']) {
                    Response::validationError([
                        'schedule_cron' => [$cronValidation['error']]
                    ]);
                    return;
                }
                $bot->setScheduleCron($validated['schedule_cron']);
            }
            if (isset($validated['config'])) {
                foreach ($validated['config'] as $key => $value) {
                    $bot->setConfig($key, $value);
                }
            }

            // Save changes
            $this->botRepo->update($bot);

            // Update next execution time if scheduled
            if ($bot->isScheduled()) {
                $this->scheduler->updateNextExecutionTime($botId);
            }

            Response::success($bot->toArray(), 'Bot updated successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to update bot', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * DELETE /api/bots/:id
     * Delete bot
     */
    public function destroy(Request $request): void
    {
        try {
            $botId = (int) $request->param('id');

            $bot = $this->botRepo->find($botId);

            if (!$bot) {
                Response::notFound("Bot not found: {$botId}");
                return;
            }

            $this->botRepo->delete($botId);

            Response::success(null, 'Bot deleted successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to delete bot', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/bots/:id/execute
     * Execute bot with input
     */
    public function execute(Request $request): void
    {
        try {
            $botId = (int) $request->param('id');

            $bot = $this->botRepo->find($botId);

            if (!$bot) {
                Response::notFound("Bot not found: {$botId}");
                return;
            }

            // Validate input
            $validated = $request->validate([
                'input' => 'required|string|min:1',
                'context' => 'array',
                'multi_thread' => 'boolean'
            ]);

            $input = $validated['input'];
            $context = $validated['context'] ?? [];
            $multiThread = $validated['multi_thread'] ?? false;

            // Execute bot
            $result = $this->botExecution->execute($bot, $input, $context, $multiThread);

            Response::success($result, 'Bot executed successfully');

        } catch (\Exception $e) {
            Response::serverError('Bot execution failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/bots/:id/metrics
     * Get bot performance metrics
     */
    public function metrics(Request $request): void
    {
        try {
            $botId = (int) $request->param('id');

            $bot = $this->botRepo->find($botId);

            if (!$bot) {
                Response::notFound("Bot not found: {$botId}");
                return;
            }

            $metrics = $this->botRepo->getPerformanceMetrics($botId);

            if (!$metrics) {
                Response::success([
                    'bot_id' => $botId,
                    'message' => 'No execution history yet'
                ], 'No metrics available');
                return;
            }

            Response::success($metrics, 'Metrics retrieved successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to retrieve metrics', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/bots/scheduled
     * Get all scheduled bots with next run times
     */
    public function scheduled(Request $request): void
    {
        try {
            $filters = ['status' => Bot::STATUS_ACTIVE];
            $bots = $this->botRepo->findAll($filters);

            // Filter to only scheduled bots
            $scheduledBots = array_filter($bots, fn($bot) => $bot->isScheduled());

            // Add schedule info
            $botsData = array_map(function($bot) {
                $data = $bot->toArray();
                $data['schedule'] = [
                    'cron' => $bot->getScheduleCron(),
                    'description' => $this->scheduler->describeCronExpression($bot->getScheduleCron()),
                    'next_run' => $this->scheduler->getNextExecutionTime($bot),
                    'next_run_formatted' => date('Y-m-d H:i:s', $this->scheduler->getNextExecutionTime($bot))
                ];
                return $data;
            }, $scheduledBots);

            Response::success([
                'bots' => array_values($botsData),
                'count' => count($botsData)
            ], 'Scheduled bots retrieved successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to retrieve scheduled bots', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/bots/due
     * Get bots due for execution now
     */
    public function due(Request $request): void
    {
        try {
            $dueBots = $this->scheduler->getDueBots();

            $botsData = array_map(fn($bot) => $bot->toArray(), $dueBots);

            Response::success([
                'bots' => $botsData,
                'count' => count($botsData)
            ], 'Due bots retrieved successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to retrieve due bots', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/bots/:id/pause
     * Pause bot execution
     */
    public function pause(Request $request): void
    {
        try {
            $botId = (int) $request->param('id');

            $this->botRepo->pause($botId);

            $bot = $this->botRepo->find($botId);

            Response::success($bot->toArray(), 'Bot paused successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to pause bot', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/bots/:id/activate
     * Activate bot
     */
    public function activate(Request $request): void
    {
        try {
            $botId = (int) $request->param('id');

            $this->botRepo->activate($botId);

            $bot = $this->botRepo->find($botId);

            Response::success($bot->toArray(), 'Bot activated successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to activate bot', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/bots/:id/archive
     * Archive bot
     */
    public function archive(Request $request): void
    {
        try {
            $botId = (int) $request->param('id');

            $this->botRepo->archive($botId);

            $bot = $this->botRepo->find($botId);

            Response::success($bot->toArray(), 'Bot archived successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to archive bot', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/sessions
     * Create multi-thread session
     */
    public function createSession(Request $request): void
    {
        try {
            // Validate input
            $validated = $request->validate([
                'topic' => 'required|string|min:10',
                'thread_count' => 'required|integer|min:2|max:6',
                'metadata' => 'array'
            ]);

            $session = new Session();
            $session->setTopic($validated['topic'])
                    ->setThreadCount($validated['thread_count'])
                    ->setStatus(Session::STATUS_ACTIVE);

            if (!empty($validated['metadata'])) {
                $session->setMetadata($validated['metadata']);
            }

            $sessionId = $this->sessionRepo->create($session);

            // Reload with ID
            $session = $this->sessionRepo->find($sessionId);

            Response::created($session->toArray(), 'Session created successfully');

        } catch (\Exception $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (isset($decoded['validation_errors'])) {
                Response::validationError($decoded['validation_errors']);
                return;
            }

            Response::serverError('Failed to create session', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/sessions/:id
     * Get session details with analytics
     */
    public function getSession(Request $request): void
    {
        try {
            $sessionId = (int) $request->param('id');

            $session = $this->sessionRepo->find($sessionId);

            if (!$session) {
                Response::notFound("Session not found: {$sessionId}");
                return;
            }

            // Include analytics
            $analytics = $this->sessionRepo->getAnalytics($sessionId);

            $data = $session->toArray();
            $data['analytics'] = $analytics;

            Response::success($data, 'Session retrieved successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to retrieve session', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/sessions
     * List recent sessions
     */
    public function listSessions(Request $request): void
    {
        try {
            $limit = (int) $request->query('limit', 20);
            $includeAnalytics = $request->query('with_analytics') === 'true';

            if ($includeAnalytics) {
                $sessions = $this->sessionRepo->getAllWithAnalytics();
            } else {
                $sessions = $this->sessionRepo->getRecent($limit);
            }

            $sessionsData = array_map(fn($s) => is_array($s) ? $s : $s->toArray(), $sessions);

            Response::success([
                'sessions' => $sessionsData,
                'count' => count($sessionsData)
            ], 'Sessions retrieved successfully');

        } catch (\Exception $e) {
            Response::serverError('Failed to retrieve sessions', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
