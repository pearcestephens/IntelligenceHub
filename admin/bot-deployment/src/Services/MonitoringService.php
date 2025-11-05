<?php

namespace BotDeployment\Services;

use BotDeployment\Services\Logger;
use BotDeployment\Services\MetricsCollector;
use Exception;

/**
 * Advanced Monitoring Service
 *
 * Provides enterprise-grade monitoring capabilities:
 * - Prometheus metrics export
 * - Grafana dashboard integration
 * - APM (Application Performance Monitoring)
 * - Sentry error tracking
 * - Custom alerting
 *
 * Metrics format: Prometheus exposition format
 * Error tracking: Sentry SDK integration
 */
class MonitoringService
{
    private $logger;
    private $metrics;
    private $sentryEnabled;
    private $sentryDsn;

    public function __construct()
    {
        $this->logger = new Logger('monitoring');
        $this->metrics = new MetricsCollector();
        $this->sentryEnabled = getenv('SENTRY_ENABLED') === 'true';
        $this->sentryDsn = getenv('SENTRY_DSN') ?: '';

        $this->initializeSentry();
    }

    /**
     * Initialize Sentry error tracking
     */
    private function initializeSentry(): void
    {
        if (!$this->sentryEnabled || empty($this->sentryDsn)) {
            return;
        }

        try {
            if (function_exists('\Sentry\init')) {
                \Sentry\init([
                    'dsn' => $this->sentryDsn,
                    'environment' => getenv('APP_ENV') ?: 'production',
                    'traces_sample_rate' => (float) (getenv('SENTRY_TRACES_SAMPLE_RATE') ?: 0.1),
                    'profiles_sample_rate' => (float) (getenv('SENTRY_PROFILES_SAMPLE_RATE') ?: 0.1)
                ]);

                $this->logger->info("Sentry initialized successfully");
            }
        } catch (Exception $e) {
            $this->logger->error("Failed to initialize Sentry", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Export metrics in Prometheus format
     *
     * @return string Prometheus exposition format
     */
    public function exportPrometheusMetrics(): string
    {
        $metrics = $this->metrics->getAllMetrics();
        $output = [];

        // Add custom bot deployment metrics
        $output[] = "# HELP bot_deployment_active_bots Number of active bots";
        $output[] = "# TYPE bot_deployment_active_bots gauge";
        $output[] = "bot_deployment_active_bots " . ($metrics['counters']['active_bots'] ?? 0);

        $output[] = "# HELP bot_deployment_executions_total Total number of bot executions";
        $output[] = "# TYPE bot_deployment_executions_total counter";
        $output[] = "bot_deployment_executions_total " . ($metrics['counters']['total_executions'] ?? 0);

        $output[] = "# HELP bot_deployment_execution_success_total Total number of successful executions";
        $output[] = "# TYPE bot_deployment_execution_success_total counter";
        $output[] = "bot_deployment_execution_success_total " . ($metrics['counters']['successful_executions'] ?? 0);

        $output[] = "# HELP bot_deployment_execution_failure_total Total number of failed executions";
        $output[] = "# TYPE bot_deployment_execution_failure_total counter";
        $output[] = "bot_deployment_execution_failure_total " . ($metrics['counters']['failed_executions'] ?? 0);

        $output[] = "# HELP bot_deployment_execution_duration_seconds Execution duration in seconds";
        $output[] = "# TYPE bot_deployment_execution_duration_seconds histogram";

        // Add histogram buckets
        $durations = $metrics['timers']['execution_duration'] ?? [];
        if (!empty($durations)) {
            $percentiles = $this->calculatePercentiles($durations);
            $output[] = "bot_deployment_execution_duration_seconds{quantile=\"0.5\"} " . $percentiles['p50'];
            $output[] = "bot_deployment_execution_duration_seconds{quantile=\"0.95\"} " . $percentiles['p95'];
            $output[] = "bot_deployment_execution_duration_seconds{quantile=\"0.99\"} " . $percentiles['p99'];
            $output[] = "bot_deployment_execution_duration_seconds_sum " . array_sum($durations);
            $output[] = "bot_deployment_execution_duration_seconds_count " . count($durations);
        }

        // WebSocket metrics
        $output[] = "# HELP bot_deployment_websocket_connections Current WebSocket connections";
        $output[] = "# TYPE bot_deployment_websocket_connections gauge";
        $output[] = "bot_deployment_websocket_connections " . ($metrics['gauges']['websocket_connections'] ?? 0);

        // Notification metrics
        $output[] = "# HELP bot_deployment_notifications_sent_total Total notifications sent";
        $output[] = "# TYPE bot_deployment_notifications_sent_total counter";
        $output[] = "bot_deployment_notifications_sent_total " . ($metrics['counters']['notifications_sent'] ?? 0);

        // System metrics
        $output[] = "# HELP bot_deployment_memory_usage_bytes Memory usage in bytes";
        $output[] = "# TYPE bot_deployment_memory_usage_bytes gauge";
        $output[] = "bot_deployment_memory_usage_bytes " . memory_get_usage(true);

        $output[] = "# HELP bot_deployment_uptime_seconds System uptime in seconds";
        $output[] = "# TYPE bot_deployment_uptime_seconds counter";
        $output[] = "bot_deployment_uptime_seconds " . $this->getSystemUptime();

        return implode("\n", $output) . "\n";
    }

    /**
     * Calculate percentiles from array of values
     */
    private function calculatePercentiles(array $values): array
    {
        if (empty($values)) {
            return ['p50' => 0, 'p95' => 0, 'p99' => 0];
        }

        sort($values);
        $count = count($values);

        return [
            'p50' => $values[(int)($count * 0.50)] ?? 0,
            'p95' => $values[(int)($count * 0.95)] ?? 0,
            'p99' => $values[(int)($count * 0.99)] ?? 0
        ];
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): int
    {
        // Read uptime from /proc/uptime if available
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            return (int) explode(' ', $uptime)[0];
        }

        return 0;
    }

    /**
     * Track error in Sentry
     */
    public function trackError(\Throwable $exception, array $context = []): void
    {
        if (!$this->sentryEnabled) {
            return;
        }

        try {
            if (function_exists('\Sentry\captureException')) {
                \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($exception, $context) {
                    foreach ($context as $key => $value) {
                        $scope->setContext($key, is_array($value) ? $value : ['value' => $value]);
                    }
                    \Sentry\captureException($exception);
                });
            }
        } catch (Exception $e) {
            $this->logger->error("Failed to track error in Sentry", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Track message in Sentry
     */
    public function trackMessage(string $message, string $level = 'info', array $context = []): void
    {
        if (!$this->sentryEnabled) {
            return;
        }

        try {
            if (function_exists('\Sentry\captureMessage')) {
                \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($message, $level, $context) {
                    foreach ($context as $key => $value) {
                        $scope->setContext($key, is_array($value) ? $value : ['value' => $value]);
                    }
                    \Sentry\captureMessage($message, $this->getSentryLevel($level));
                });
            }
        } catch (Exception $e) {
            $this->logger->error("Failed to track message in Sentry", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Convert log level to Sentry level
     */
    private function getSentryLevel(string $level): string
    {
        return match (strtolower($level)) {
            'emergency', 'critical' => \Sentry\Severity::fatal(),
            'alert', 'error' => \Sentry\Severity::error(),
            'warning' => \Sentry\Severity::warning(),
            'notice', 'info' => \Sentry\Severity::info(),
            'debug' => \Sentry\Severity::debug(),
            default => \Sentry\Severity::info()
        };
    }

    /**
     * Start performance transaction (APM)
     */
    public function startTransaction(string $name, string $operation = 'task'): ?\Sentry\Tracing\Transaction
    {
        if (!$this->sentryEnabled || !function_exists('\Sentry\startTransaction')) {
            return null;
        }

        try {
            $context = new \Sentry\Tracing\TransactionContext();
            $context->setName($name);
            $context->setOp($operation);

            $transaction = \Sentry\startTransaction($context);
            \Sentry\SentrySdk::getCurrentHub()->setSpan($transaction);

            return $transaction;
        } catch (Exception $e) {
            $this->logger->error("Failed to start transaction", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Finish performance transaction
     */
    public function finishTransaction(?\Sentry\Tracing\Transaction $transaction): void
    {
        if ($transaction) {
            try {
                $transaction->finish();
            } catch (Exception $e) {
                $this->logger->error("Failed to finish transaction", ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Get Grafana dashboard JSON
     */
    public function getGrafanaDashboard(): array
    {
        return [
            'dashboard' => [
                'title' => 'Bot Deployment System',
                'tags' => ['bot-deployment', 'automation'],
                'timezone' => 'browser',
                'schemaVersion' => 16,
                'version' => 1,
                'refresh' => '5s',
                'panels' => [
                    [
                        'title' => 'Active Bots',
                        'type' => 'stat',
                        'targets' => [[
                            'expr' => 'bot_deployment_active_bots',
                            'refId' => 'A'
                        ]],
                        'gridPos' => ['h' => 4, 'w' => 6, 'x' => 0, 'y' => 0]
                    ],
                    [
                        'title' => 'Total Executions',
                        'type' => 'stat',
                        'targets' => [[
                            'expr' => 'bot_deployment_executions_total',
                            'refId' => 'A'
                        ]],
                        'gridPos' => ['h' => 4, 'w' => 6, 'x' => 6, 'y' => 0]
                    ],
                    [
                        'title' => 'Success Rate',
                        'type' => 'gauge',
                        'targets' => [[
                            'expr' => 'rate(bot_deployment_execution_success_total[5m]) / rate(bot_deployment_executions_total[5m]) * 100',
                            'refId' => 'A'
                        ]],
                        'gridPos' => ['h' => 4, 'w' => 6, 'x' => 12, 'y' => 0]
                    ],
                    [
                        'title' => 'Execution Duration (p95)',
                        'type' => 'graph',
                        'targets' => [[
                            'expr' => 'bot_deployment_execution_duration_seconds{quantile="0.95"}',
                            'refId' => 'A'
                        ]],
                        'gridPos' => ['h' => 8, 'w' => 12, 'x' => 0, 'y' => 4]
                    ],
                    [
                        'title' => 'Execution Rate',
                        'type' => 'graph',
                        'targets' => [[
                            'expr' => 'rate(bot_deployment_executions_total[5m])',
                            'refId' => 'A',
                            'legendFormat' => 'Executions/sec'
                        ]],
                        'gridPos' => ['h' => 8, 'w' => 12, 'x' => 12, 'y' => 4]
                    ],
                    [
                        'title' => 'Memory Usage',
                        'type' => 'graph',
                        'targets' => [[
                            'expr' => 'bot_deployment_memory_usage_bytes',
                            'refId' => 'A'
                        ]],
                        'gridPos' => ['h' => 8, 'w' => 12, 'x' => 0, 'y' => 12]
                    ],
                    [
                        'title' => 'WebSocket Connections',
                        'type' => 'graph',
                        'targets' => [[
                            'expr' => 'bot_deployment_websocket_connections',
                            'refId' => 'A'
                        ]],
                        'gridPos' => ['h' => 8, 'w' => 12, 'x' => 12, 'y' => 12]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get health check status
     */
    public function getHealthStatus(): array
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => time(),
            'checks' => []
        ];

        // Check database
        try {
            $db = \BotDeployment\Config\Connection::getInstance();
            $db->query("SELECT 1");
            $status['checks']['database'] = ['status' => 'ok', 'message' => 'Database connection healthy'];
        } catch (Exception $e) {
            $status['checks']['database'] = ['status' => 'error', 'message' => $e->getMessage()];
            $status['status'] = 'unhealthy';
        }

        // Check memory
        $memUsage = memory_get_usage(true) / 1024 / 1024; // MB
        $memLimit = (int) ini_get('memory_limit');
        $memPercent = ($memUsage / $memLimit) * 100;

        if ($memPercent > 90) {
            $status['checks']['memory'] = ['status' => 'warning', 'usage_mb' => $memUsage, 'percent' => $memPercent];
            $status['status'] = 'degraded';
        } else {
            $status['checks']['memory'] = ['status' => 'ok', 'usage_mb' => $memUsage, 'percent' => $memPercent];
        }

        // Check disk space
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskPercent = (($diskTotal - $diskFree) / $diskTotal) * 100;

        if ($diskPercent > 90) {
            $status['checks']['disk'] = ['status' => 'warning', 'percent_used' => $diskPercent];
            $status['status'] = 'degraded';
        } else {
            $status['checks']['disk'] = ['status' => 'ok', 'percent_used' => $diskPercent];
        }

        return $status;
    }

    /**
     * Get monitoring statistics
     */
    public function getStatistics(): array
    {
        $metrics = $this->metrics->getAllMetrics();

        return [
            'active_bots' => $metrics['counters']['active_bots'] ?? 0,
            'total_executions' => $metrics['counters']['total_executions'] ?? 0,
            'successful_executions' => $metrics['counters']['successful_executions'] ?? 0,
            'failed_executions' => $metrics['counters']['failed_executions'] ?? 0,
            'success_rate' => $this->calculateSuccessRate($metrics),
            'avg_execution_time' => $this->calculateAvgExecutionTime($metrics),
            'websocket_connections' => $metrics['gauges']['websocket_connections'] ?? 0,
            'notifications_sent' => $metrics['counters']['notifications_sent'] ?? 0,
            'memory_usage_mb' => memory_get_usage(true) / 1024 / 1024,
            'uptime_seconds' => $this->getSystemUptime()
        ];
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(array $metrics): float
    {
        $total = $metrics['counters']['total_executions'] ?? 0;
        $successful = $metrics['counters']['successful_executions'] ?? 0;

        if ($total === 0) {
            return 100.0;
        }

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Calculate average execution time
     */
    private function calculateAvgExecutionTime(array $metrics): float
    {
        $durations = $metrics['timers']['execution_duration'] ?? [];

        if (empty($durations)) {
            return 0.0;
        }

        return round(array_sum($durations) / count($durations), 2);
    }

    /**
     * Create alert rule
     */
    public function createAlertRule(array $rule): bool
    {
        try {
            // Store alert rule (would typically integrate with Prometheus Alertmanager)
            $this->logger->info("Alert rule created", ['rule' => $rule]);
            return true;
        } catch (Exception $e) {
            $this->logger->error("Failed to create alert rule", ['error' => $e->getMessage()]);
            return false;
        }
    }
}
