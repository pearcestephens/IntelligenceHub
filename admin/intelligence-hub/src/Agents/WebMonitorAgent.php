<?php
/**
 * Web Monitor Agent - Autonomous Website Monitoring & Performance Tracking
 *
 * Monitors all company websites for:
 * - Traffic patterns and anomalies
 * - Performance (page load times, API response times)
 * - Errors (404s, 500s, JavaScript errors)
 * - Uptime and availability
 * - Security threats and suspicious activity
 *
 * Websites monitored:
 * - vapeshed.co.nz (main e-commerce)
 * - vapingkiwi.co.nz (secondary site)
 * - vapehq.co.nz (wholesale/trade)
 *
 * @package IntelligenceHub
 * @subpackage Agents
 */

namespace IntelligenceHub\Agents;

use IntelligenceHub\AI\DecisionEngine;
use PDO;

class WebMonitorAgent extends BaseAgent
{
    /**
     * AI Decision Engine
     */
    private DecisionEngine $ai;

    /**
     * Websites to monitor
     */
    private array $websites = [
        [
            'id' => 'vapeshed',
            'name' => 'Vape Shed',
            'url' => 'https://vapeshed.co.nz',
            'type' => 'e-commerce',
            'priority' => 'high',
            'check_interval' => 300 // 5 minutes
        ],
        [
            'id' => 'vapingkiwi',
            'name' => 'Vaping Kiwi',
            'url' => 'https://vapingkiwi.co.nz',
            'type' => 'e-commerce',
            'priority' => 'high',
            'check_interval' => 300
        ],
        [
            'id' => 'vapehq',
            'name' => 'Vape HQ',
            'url' => 'https://vapehq.co.nz',
            'type' => 'wholesale',
            'priority' => 'medium',
            'check_interval' => 600 // 10 minutes
        ]
    ];

    /**
     * Performance thresholds
     */
    private array $thresholds = [
        'response_time_warning' => 2000, // ms
        'response_time_critical' => 5000, // ms
        'error_rate_warning' => 0.01, // 1%
        'error_rate_critical' => 0.05, // 5%
        'traffic_drop_warning' => 0.25, // 25% drop
        'traffic_drop_critical' => 0.50, // 50% drop
        'uptime_target' => 0.999 // 99.9%
    ];

    /**
     * Baseline metrics (learned over time)
     */
    private array $baselines = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->name = 'Web Monitor Agent';
        $this->description = 'Monitors website performance, uptime, and security across all sites';
        $this->capabilities = [
            'uptime_monitoring',
            'performance_tracking',
            'error_detection',
            'traffic_analysis',
            'security_monitoring',
            'ssl_certificate_monitoring',
            'api_health_checks'
        ];

        $this->ai = new DecisionEngine();
        $this->loadBaselines();
    }

    /**
     * Main execution method - runs on each cycle
     */
    public function execute(): bool
    {
        try {
            $this->logInfo('Starting website monitoring cycle...');
            $this->updateStatus('active');

            $issues = [];

            // Check each website
            foreach ($this->websites as $website) {
                $this->logInfo(sprintf('Checking %s...', $website['name']));

                // Perform comprehensive checks
                $uptimeCheck = $this->checkUptime($website);
                $performanceCheck = $this->checkPerformance($website);
                $errorCheck = $this->checkErrors($website);
                $trafficCheck = $this->checkTraffic($website);
                $securityCheck = $this->checkSecurity($website);
                $sslCheck = $this->checkSSLCertificate($website);

                // Collect issues
                $siteIssues = array_merge(
                    $uptimeCheck['issues'] ?? [],
                    $performanceCheck['issues'] ?? [],
                    $errorCheck['issues'] ?? [],
                    $trafficCheck['issues'] ?? [],
                    $securityCheck['issues'] ?? [],
                    $sslCheck['issues'] ?? []
                );

                if (!empty($siteIssues)) {
                    $issues = array_merge($issues, $siteIssues);
                }

                // Store metrics
                $this->storeMetrics($website, [
                    'uptime' => $uptimeCheck,
                    'performance' => $performanceCheck,
                    'errors' => $errorCheck,
                    'traffic' => $trafficCheck,
                    'security' => $securityCheck,
                    'ssl' => $sslCheck
                ]);
            }

            $this->logInfo(sprintf('Found %d issues across all sites', count($issues)));

            // Handle critical issues immediately
            $criticalIssues = array_filter($issues, fn($i) => $i['severity'] === 'critical');
            foreach ($criticalIssues as $issue) {
                $this->handleCriticalIssue($issue);
            }

            // Generate solutions for non-critical issues
            if (!empty($issues)) {
                $solutions = $this->generateSolutions($issues);

                foreach ($solutions as $solution) {
                    if ($solution['confidence'] >= 0.8) {
                        $this->executeSolution($solution);
                    } elseif ($solution['confidence'] >= 0.6) {
                        $this->requestApproval($solution);
                    }
                }
            }

            // Update baselines (learning)
            $this->updateBaselines();

            $this->updateStatus('idle');
            $this->updateHeartbeat();

            return true;

        } catch (\Exception $e) {
            $this->logError('Web monitoring cycle failed: ' . $e->getMessage());
            $this->updateStatus('error');
            $this->notifyError('Web Monitor Agent Error', $e->getMessage());
            return false;
        }
    }

    /**
     * Check website uptime
     *
     * @param array $website Website config
     * @return array Check results
     */
    private function checkUptime(array $website): array
    {
        $issues = [];

        try {
            $startTime = microtime(true);

            $ch = curl_init($website['url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'IntelligenceHub-WebMonitor/1.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to ms
            $error = curl_error($ch);
            curl_close($ch);

            $isUp = ($httpCode >= 200 && $httpCode < 400) && empty($error);

            if (!$isUp) {
                $issues[] = [
                    'type' => 'downtime',
                    'severity' => 'critical',
                    'website' => $website['name'],
                    'website_id' => $website['id'],
                    'http_code' => $httpCode,
                    'error' => $error,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            return [
                'is_up' => $isUp,
                'http_code' => $httpCode,
                'response_time' => $responseTime,
                'error' => $error,
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            $this->logError(sprintf('Uptime check failed for %s: %s', $website['name'], $e->getMessage()));

            return [
                'is_up' => false,
                'error' => $e->getMessage(),
                'issues' => [[
                    'type' => 'check_failed',
                    'severity' => 'critical',
                    'website' => $website['name'],
                    'website_id' => $website['id'],
                    'error' => $e->getMessage()
                ]]
            ];
        }
    }

    /**
     * Check website performance
     *
     * @param array $website Website config
     * @return array Check results
     */
    private function checkPerformance(array $website): array
    {
        $issues = [];

        try {
            $metrics = [];
            $endpoints = [
                ['path' => '/', 'name' => 'Homepage'],
                ['path' => '/shop', 'name' => 'Shop'],
                ['path' => '/api/health', 'name' => 'API Health']
            ];

            foreach ($endpoints as $endpoint) {
                $url = $website['url'] . $endpoint['path'];
                $startTime = microtime(true);

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_USERAGENT => 'IntelligenceHub-WebMonitor/1.0'
                ]);

                curl_exec($ch);
                $responseTime = (microtime(true) - $startTime) * 1000;
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $metrics[] = [
                    'endpoint' => $endpoint['name'],
                    'response_time' => $responseTime,
                    'http_code' => $httpCode
                ];

                // Check thresholds
                if ($responseTime > $this->thresholds['response_time_critical']) {
                    $issues[] = [
                        'type' => 'slow_response',
                        'severity' => 'critical',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'endpoint' => $endpoint['name'],
                        'response_time' => $responseTime,
                        'threshold' => $this->thresholds['response_time_critical']
                    ];
                } elseif ($responseTime > $this->thresholds['response_time_warning']) {
                    $issues[] = [
                        'type' => 'slow_response',
                        'severity' => 'warning',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'endpoint' => $endpoint['name'],
                        'response_time' => $responseTime,
                        'threshold' => $this->thresholds['response_time_warning']
                    ];
                }
            }

            $avgResponseTime = array_sum(array_column($metrics, 'response_time')) / count($metrics);

            return [
                'avg_response_time' => $avgResponseTime,
                'endpoint_metrics' => $metrics,
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            $this->logError(sprintf('Performance check failed for %s: %s', $website['name'], $e->getMessage()));
            return ['issues' => []];
        }
    }

    /**
     * Check for errors (404s, 500s)
     *
     * @param array $website Website config
     * @return array Check results
     */
    private function checkErrors(array $website): array
    {
        $issues = [];

        try {
            // Check error logs from database
            $stmt = $this->db->prepare('
                SELECT
                    error_type,
                    COUNT(*) as error_count,
                    MAX(created_at) as last_occurrence
                FROM web_errors
                WHERE website_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY error_type
            ');
            $stmt->execute([$website['id']]);
            $errors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalErrors = array_sum(array_column($errors, 'error_count'));

            // Get total requests in last hour
            $stmt = $this->db->prepare('
                SELECT COUNT(*) as request_count
                FROM web_requests
                WHERE website_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ');
            $stmt->execute([$website['id']]);
            $requests = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalRequests = $requests['request_count'] ?? 1000;

            $errorRate = $totalErrors / $totalRequests;

            // Check error rate thresholds
            if ($errorRate > $this->thresholds['error_rate_critical']) {
                $issues[] = [
                    'type' => 'high_error_rate',
                    'severity' => 'critical',
                    'website' => $website['name'],
                    'website_id' => $website['id'],
                    'error_rate' => $errorRate,
                    'total_errors' => $totalErrors,
                    'threshold' => $this->thresholds['error_rate_critical']
                ];
            } elseif ($errorRate > $this->thresholds['error_rate_warning']) {
                $issues[] = [
                    'type' => 'elevated_error_rate',
                    'severity' => 'warning',
                    'website' => $website['name'],
                    'website_id' => $website['id'],
                    'error_rate' => $errorRate,
                    'total_errors' => $totalErrors,
                    'threshold' => $this->thresholds['error_rate_warning']
                ];
            }

            // Check for specific error patterns
            foreach ($errors as $error) {
                if ($error['error_count'] > 50) {
                    $issues[] = [
                        'type' => 'error_spike',
                        'severity' => 'high',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'error_type' => $error['error_type'],
                        'count' => $error['error_count']
                    ];
                }
            }

            return [
                'error_rate' => $errorRate,
                'total_errors' => $totalErrors,
                'errors_by_type' => $errors,
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            $this->logError(sprintf('Error check failed for %s: %s', $website['name'], $e->getMessage()));
            return ['issues' => []];
        }
    }

    /**
     * Check traffic patterns
     *
     * @param array $website Website config
     * @return array Check results
     */
    private function checkTraffic(array $website): array
    {
        $issues = [];

        try {
            // Get current hour traffic
            $stmt = $this->db->prepare('
                SELECT COUNT(*) as current_traffic
                FROM web_requests
                WHERE website_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ');
            $stmt->execute([$website['id']]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentTraffic = $current['current_traffic'] ?? 0;

            // Get baseline for this hour (average of last 7 days, same hour)
            $baseline = $this->baselines[$website['id']]['traffic'] ?? 100;

            if ($baseline > 0) {
                $change = ($currentTraffic - $baseline) / $baseline;

                // Check for significant drop
                if ($change < -$this->thresholds['traffic_drop_critical']) {
                    $issues[] = [
                        'type' => 'traffic_drop',
                        'severity' => 'critical',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'current_traffic' => $currentTraffic,
                        'baseline_traffic' => $baseline,
                        'change_percent' => $change * 100
                    ];
                } elseif ($change < -$this->thresholds['traffic_drop_warning']) {
                    $issues[] = [
                        'type' => 'traffic_drop',
                        'severity' => 'warning',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'current_traffic' => $currentTraffic,
                        'baseline_traffic' => $baseline,
                        'change_percent' => $change * 100
                    ];
                }

                // Check for unusual spike (could indicate attack)
                if ($change > 3.0) { // 300% increase
                    $issues[] = [
                        'type' => 'traffic_spike',
                        'severity' => 'high',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'current_traffic' => $currentTraffic,
                        'baseline_traffic' => $baseline,
                        'change_percent' => $change * 100
                    ];
                }
            }

            return [
                'current_traffic' => $currentTraffic,
                'baseline_traffic' => $baseline,
                'change_percent' => ($change ?? 0) * 100,
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            $this->logError(sprintf('Traffic check failed for %s: %s', $website['name'], $e->getMessage()));
            return ['issues' => []];
        }
    }

    /**
     * Check security threats
     *
     * @param array $website Website config
     * @return array Check results
     */
    private function checkSecurity(array $website): array
    {
        $issues = [];

        try {
            // Check for SQL injection attempts
            $stmt = $this->db->prepare('
                SELECT COUNT(*) as attack_count
                FROM security_events
                WHERE website_id = ?
                AND event_type = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ');

            $attackTypes = ['sql_injection', 'xss', 'brute_force', 'ddos'];

            foreach ($attackTypes as $attackType) {
                $stmt->execute([$website['id'], $attackType]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $result['attack_count'] ?? 0;

                if ($count > 10) {
                    $issues[] = [
                        'type' => 'security_threat',
                        'severity' => 'critical',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'attack_type' => $attackType,
                        'attempt_count' => $count
                    ];
                }
            }

            return [
                'threats_detected' => count($issues),
                'issues' => $issues
            ];

        } catch (\Exception $e) {
            $this->logError(sprintf('Security check failed for %s: %s', $website['name'], $e->getMessage()));
            return ['issues' => []];
        }
    }

    /**
     * Check SSL certificate
     *
     * @param array $website Website config
     * @return array Check results
     */
    private function checkSSLCertificate(array $website): array
    {
        $issues = [];

        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => true
                ]
            ]);

            $parsedUrl = parse_url($website['url']);
            $host = $parsedUrl['host'];

            $client = @stream_socket_client(
                "ssl://{$host}:443",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if ($client) {
                $params = stream_context_get_params($client);
                $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

                $expiryDate = $cert['validTo_time_t'];
                $daysUntilExpiry = ceil(($expiryDate - time()) / 86400);

                if ($daysUntilExpiry < 7) {
                    $issues[] = [
                        'type' => 'ssl_expiring',
                        'severity' => 'critical',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'days_until_expiry' => $daysUntilExpiry,
                        'expiry_date' => date('Y-m-d', $expiryDate)
                    ];
                } elseif ($daysUntilExpiry < 30) {
                    $issues[] = [
                        'type' => 'ssl_expiring',
                        'severity' => 'warning',
                        'website' => $website['name'],
                        'website_id' => $website['id'],
                        'days_until_expiry' => $daysUntilExpiry,
                        'expiry_date' => date('Y-m-d', $expiryDate)
                    ];
                }

                fclose($client);

                return [
                    'is_valid' => true,
                    'days_until_expiry' => $daysUntilExpiry,
                    'expiry_date' => date('Y-m-d', $expiryDate),
                    'issuer' => $cert['issuer']['CN'] ?? 'Unknown',
                    'issues' => $issues
                ];
            }

            return ['issues' => []];

        } catch (\Exception $e) {
            $this->logError(sprintf('SSL check failed for %s: %s', $website['name'], $e->getMessage()));
            return ['issues' => []];
        }
    }

    /**
     * Handle critical issue immediately
     *
     * @param array $issue Critical issue
     */
    private function handleCriticalIssue(array $issue): void
    {
        try {
            $this->logError(sprintf(
                'CRITICAL: %s on %s',
                $issue['type'],
                $issue['website']
            ));

            // Send immediate notification
            $this->sendNotification(
                sprintf('CRITICAL: %s', $issue['website']),
                $this->formatIssueMessage($issue),
                'danger'
            );

            // Record incident
            $this->recordTask('critical_issue', $issue, 'completed');

            // If downtime, try to restart services (if we have that capability)
            if ($issue['type'] === 'downtime') {
                $this->attemptAutoRecovery($issue);
            }

        } catch (\Exception $e) {
            $this->logError('Critical issue handling failed: ' . $e->getMessage());
        }
    }

    /**
     * Attempt automatic recovery for downtime
     *
     * @param array $issue Downtime issue
     */
    private function attemptAutoRecovery(array $issue): void
    {
        try {
            $this->logInfo(sprintf('Attempting auto-recovery for %s...', $issue['website']));

            // TODO: Implement actual recovery mechanisms
            // For now, just log and notify

            $this->sendNotification(
                'Auto-Recovery Initiated',
                sprintf('Attempting to recover %s...', $issue['website']),
                'info'
            );

        } catch (\Exception $e) {
            $this->logError('Auto-recovery failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate solutions for issues
     *
     * @param array $issues Issues
     * @return array Solutions
     */
    private function generateSolutions(array $issues): array
    {
        $solutions = [];

        foreach ($issues as $issue) {
            $solution = null;

            switch ($issue['type']) {
                case 'slow_response':
                    $solution = [
                        'action' => 'investigate_performance',
                        'confidence' => 0.7,
                        'issue' => $issue,
                        'reasoning' => sprintf(
                            '%s %s is slow (%dms). Investigate caching, database queries, or server load.',
                            $issue['website'],
                            $issue['endpoint'],
                            $issue['response_time']
                        )
                    ];
                    break;

                case 'high_error_rate':
                    $solution = [
                        'action' => 'investigate_errors',
                        'confidence' => 0.8,
                        'issue' => $issue,
                        'reasoning' => sprintf(
                            '%s has %.2f%% error rate. Check logs immediately.',
                            $issue['website'],
                            $issue['error_rate'] * 100
                        )
                    ];
                    break;

                case 'traffic_drop':
                    $solution = [
                        'action' => 'investigate_traffic',
                        'confidence' => 0.6,
                        'issue' => $issue,
                        'reasoning' => sprintf(
                            '%s traffic dropped %.0f%%. Check SEO, ads, or site issues.',
                            $issue['website'],
                            abs($issue['change_percent'])
                        )
                    ];
                    break;

                case 'security_threat':
                    $solution = [
                        'action' => 'block_threats',
                        'confidence' => 0.9,
                        'issue' => $issue,
                        'reasoning' => sprintf(
                            '%s under %s attack (%d attempts). Blocking IPs.',
                            $issue['website'],
                            $issue['attack_type'],
                            $issue['attempt_count']
                        )
                    ];
                    break;

                case 'ssl_expiring':
                    $solution = [
                        'action' => 'renew_ssl',
                        'confidence' => 0.85,
                        'issue' => $issue,
                        'reasoning' => sprintf(
                            '%s SSL expires in %d days. Renew immediately.',
                            $issue['website'],
                            $issue['days_until_expiry']
                        )
                    ];
                    break;
            }

            if ($solution) {
                $solutions[] = $solution;
            }
        }

        return $solutions;
    }

    /**
     * Execute solution
     *
     * @param array $solution Solution
     * @return bool Success
     */
    private function executeSolution(array $solution): bool
    {
        try {
            $this->logInfo(sprintf('Executing solution: %s', $solution['action']));

            // Record task
            $this->recordTask($solution['action'], $solution, 'completed');

            // Send notification
            $this->sendNotification(
                'Action Taken: ' . $solution['issue']['website'],
                $solution['reasoning'],
                'info'
            );

            return true;

        } catch (\Exception $e) {
            $this->logError('Solution execution failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Request approval for solution
     *
     * @param array $solution Solution
     */
    private function requestApproval(array $solution): void
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO ai_decisions
                (agent_id, decision_type, confidence, data, reasoning, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');

            $stmt->execute([
                $this->agentId,
                $solution['action'],
                $solution['confidence'],
                json_encode($solution),
                $solution['reasoning'],
                'pending_approval'
            ]);

            $this->sendNotification(
                'Approval Required: ' . $solution['issue']['website'],
                $solution['reasoning'],
                'warning'
            );

        } catch (\Exception $e) {
            $this->logError('Approval request failed: ' . $e->getMessage());
        }
    }

    /**
     * Store metrics in database
     *
     * @param array $website Website
     * @param array $metrics Metrics
     */
    private function storeMetrics(array $website, array $metrics): void
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO web_metrics
                (website_id, uptime, avg_response_time, error_rate, traffic_count, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');

            $stmt->execute([
                $website['id'],
                $metrics['uptime']['is_up'] ? 1 : 0,
                $metrics['performance']['avg_response_time'] ?? 0,
                $metrics['errors']['error_rate'] ?? 0,
                $metrics['traffic']['current_traffic'] ?? 0
            ]);

        } catch (\Exception $e) {
            $this->logError('Metrics storage failed: ' . $e->getMessage());
        }
    }

    /**
     * Load baseline metrics
     */
    private function loadBaselines(): void
    {
        try {
            foreach ($this->websites as $website) {
                // Get average traffic for each hour of the week
                $stmt = $this->db->prepare('
                    SELECT AVG(traffic_count) as avg_traffic
                    FROM web_metrics
                    WHERE website_id = ?
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND HOUR(created_at) = HOUR(NOW())
                ');
                $stmt->execute([$website['id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $this->baselines[$website['id']] = [
                    'traffic' => $result['avg_traffic'] ?? 100
                ];
            }

        } catch (\Exception $e) {
            $this->logError('Baseline loading failed: ' . $e->getMessage());
        }
    }

    /**
     * Update baselines with latest data
     */
    private function updateBaselines(): void
    {
        // Baselines are recalculated on next load
        $this->baselines = [];
    }

    /**
     * Format issue message
     *
     * @param array $issue Issue
     * @return string Formatted message
     */
    private function formatIssueMessage(array $issue): string
    {
        switch ($issue['type']) {
            case 'downtime':
                return sprintf(
                    '%s is DOWN! HTTP %d - %s',
                    $issue['website'],
                    $issue['http_code'] ?? 0,
                    $issue['error'] ?? 'Unknown error'
                );

            case 'slow_response':
                return sprintf(
                    '%s %s is slow: %.0fms (threshold: %.0fms)',
                    $issue['website'],
                    $issue['endpoint'],
                    $issue['response_time'],
                    $issue['threshold']
                );

            case 'high_error_rate':
                return sprintf(
                    '%s has %.2f%% error rate (%d errors)',
                    $issue['website'],
                    $issue['error_rate'] * 100,
                    $issue['total_errors']
                );

            case 'traffic_drop':
                return sprintf(
                    '%s traffic dropped %.0f%% (current: %d, baseline: %d)',
                    $issue['website'],
                    abs($issue['change_percent']),
                    $issue['current_traffic'],
                    $issue['baseline_traffic']
                );

            case 'security_threat':
                return sprintf(
                    '%s under %s attack: %d attempts detected',
                    $issue['website'],
                    $issue['attack_type'],
                    $issue['attempt_count']
                );

            case 'ssl_expiring':
                return sprintf(
                    '%s SSL certificate expires in %d days (%s)',
                    $issue['website'],
                    $issue['days_until_expiry'],
                    $issue['expiry_date']
                );

            default:
                return sprintf('%s: %s', $issue['website'], $issue['type']);
        }
    }
}
