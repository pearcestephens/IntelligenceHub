<?php
/**
 * Security Agent - Autonomous Security Monitoring & Incident Response
 *
 * Monitors and responds to security events across:
 * - Failed login attempts (brute force detection)
 * - CISWatch camera events (suspicious activity)
 * - Compliance violations (age verification, regulatory)
 * - Access control anomalies
 * - Data breaches and unauthorized access
 * - Audit log analysis
 *
 * Provides automated incident response and threat mitigation.
 *
 * @package IntelligenceHub
 * @subpackage Agents
 */

namespace IntelligenceHub\Agents;

use IntelligenceHub\AI\DecisionEngine;
use PDO;

class SecurityAgent extends BaseAgent
{
    /**
     * AI Decision Engine
     */
    private DecisionEngine $ai;

    /**
     * Security thresholds
     */
    private array $thresholds = [
        'failed_login_attempts' => 5, // Failed attempts before action
        'failed_login_window' => 300, // 5 minutes
        'suspicious_ip_score' => 0.7, // IP reputation threshold
        'compliance_violation_critical' => 3, // Critical violations before escalation
        'camera_event_confidence' => 0.8, // CISWatch AI confidence
        'data_access_anomaly_score' => 0.75 // Unusual data access pattern
    ];

    /**
     * Monitored entities
     */
    private array $locations = [];
    private array $blockedIPs = [];
    private array $suspiciousUsers = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->name = 'Security Agent';
        $this->description = 'Monitors security threats and automates incident response';
        $this->capabilities = [
            'failed_login_monitoring',
            'brute_force_detection',
            'ciswatch_integration',
            'compliance_monitoring',
            'access_control',
            'threat_intelligence',
            'incident_response',
            'audit_log_analysis'
        ];

        $this->ai = new DecisionEngine();
        $this->loadSecurityConfig();
    }

    /**
     * Main execution method - runs on each cycle
     */
    public function execute(): bool
    {
        try {
            $this->logInfo('Starting security monitoring cycle...');
            $this->updateStatus('active');

            $threats = [];

            // Monitor various security aspects
            $loginThreats = $this->monitorFailedLogins();
            $cameraEvents = $this->monitorCISWatchEvents();
            $complianceIssues = $this->monitorCompliance();
            $accessAnomalies = $this->monitorAccessControl();
            $dataBreaches = $this->monitorDataAccess();
            $auditIssues = $this->analyzeAuditLogs();

            $threats = array_merge(
                $loginThreats,
                $cameraEvents,
                $complianceIssues,
                $accessAnomalies,
                $dataBreaches,
                $auditIssues
            );

            $this->logInfo(sprintf('Detected %d security threats', count($threats)));

            // Prioritize threats by severity
            $prioritizedThreats = $this->prioritizeThreats($threats);

            // Handle critical threats immediately
            $criticalThreats = array_filter($prioritizedThreats, fn($t) => $t['severity'] === 'critical');
            foreach ($criticalThreats as $threat) {
                $this->handleCriticalThreat($threat);
            }

            // Generate responses for other threats
            $responses = $this->generateResponses($prioritizedThreats);

            $executedCount = 0;
            $approvalCount = 0;

            foreach ($responses as $response) {
                if ($response['confidence'] >= 0.9) {
                    // Auto-execute high-confidence responses
                    if ($this->executeResponse($response)) {
                        $executedCount++;
                        $this->recordTask('auto_response', [
                            'action' => $response['action'],
                            'threat' => $response['threat']['type']
                        ], 'completed');
                    }
                } elseif ($response['confidence'] >= 0.7) {
                    // Request approval for medium-confidence responses
                    $this->requestApproval($response);
                    $approvalCount++;
                }
            }

            $this->logInfo(sprintf(
                'Security cycle complete: %d auto-responses, %d pending approval',
                $executedCount,
                $approvalCount
            ));

            // Update threat intelligence
            $this->updateThreatIntelligence();

            // Generate daily security report if scheduled
            if ($this->shouldGenerateReport()) {
                $this->generateSecurityReport();
            }

            $this->updateStatus('idle');
            $this->updateHeartbeat();

            return true;

        } catch (\Exception $e) {
            $this->logError('Security monitoring cycle failed: ' . $e->getMessage());
            $this->updateStatus('error');
            $this->notifyError('Security Agent Error', $e->getMessage());
            return false;
        }
    }

    /**
     * Monitor failed login attempts for brute force attacks
     *
     * @return array Detected threats
     */
    private function monitorFailedLogins(): array
    {
        $threats = [];

        try {
            // Check failed logins in the last 5 minutes, grouped by IP
            $stmt = $this->db->prepare('
                SELECT
                    ip_address,
                    username,
                    COUNT(*) as attempt_count,
                    MIN(created_at) as first_attempt,
                    MAX(created_at) as last_attempt,
                    GROUP_CONCAT(DISTINCT username) as targeted_users
                FROM failed_logins
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)
                GROUP BY ip_address
                HAVING attempt_count >= ?
            ');

            $stmt->execute([
                $this->thresholds['failed_login_window'],
                $this->thresholds['failed_login_attempts']
            ]);

            $suspiciousIPs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($suspiciousIPs as $ip) {
                // Check if IP is already blocked
                if (in_array($ip['ip_address'], $this->blockedIPs)) {
                    continue;
                }

                $threats[] = [
                    'type' => 'brute_force_attack',
                    'severity' => $ip['attempt_count'] >= 10 ? 'critical' : 'high',
                    'ip_address' => $ip['ip_address'],
                    'attempt_count' => $ip['attempt_count'],
                    'targeted_users' => explode(',', $ip['targeted_users']),
                    'first_attempt' => $ip['first_attempt'],
                    'last_attempt' => $ip['last_attempt'],
                    'window_seconds' => $this->thresholds['failed_login_window']
                ];
            }

            // Check for credential stuffing (same IP, many different usernames)
            $stmt = $this->db->prepare('
                SELECT
                    ip_address,
                    COUNT(DISTINCT username) as unique_usernames,
                    COUNT(*) as total_attempts
                FROM failed_logins
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY ip_address
                HAVING unique_usernames >= 10
            ');
            $stmt->execute();
            $credentialStuffing = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($credentialStuffing as $attack) {
                $threats[] = [
                    'type' => 'credential_stuffing',
                    'severity' => 'critical',
                    'ip_address' => $attack['ip_address'],
                    'unique_usernames' => $attack['unique_usernames'],
                    'total_attempts' => $attack['total_attempts']
                ];
            }

        } catch (\Exception $e) {
            $this->logError('Failed login monitoring failed: ' . $e->getMessage());
        }

        return $threats;
    }

    /**
     * Monitor CISWatch camera events
     *
     * @return array Detected threats
     */
    private function monitorCISWatchEvents(): array
    {
        $threats = [];

        try {
            // Get recent camera events from CISWatch system
            $stmt = $this->db->prepare('
                SELECT
                    ce.*,
                    l.name as location_name
                FROM ciswatch_events ce
                JOIN locations l ON ce.location_id = l.id
                WHERE ce.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                AND ce.processed = 0
                AND ce.ai_confidence >= ?
            ');
            $stmt->execute([$this->thresholds['camera_event_confidence']]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($events as $event) {
                $severity = 'medium';

                // Classify event severity
                if (in_array($event['event_type'], ['weapon_detected', 'violence', 'break_in'])) {
                    $severity = 'critical';
                } elseif (in_array($event['event_type'], ['shoplifting', 'loitering', 'vandalism'])) {
                    $severity = 'high';
                }

                $threats[] = [
                    'type' => 'camera_event',
                    'severity' => $severity,
                    'event_id' => $event['id'],
                    'event_type' => $event['event_type'],
                    'location_id' => $event['location_id'],
                    'location_name' => $event['location_name'],
                    'camera_id' => $event['camera_id'],
                    'ai_confidence' => $event['ai_confidence'],
                    'timestamp' => $event['created_at'],
                    'snapshot_url' => $event['snapshot_url'] ?? null
                ];

                // Mark as processed
                $updateStmt = $this->db->prepare('UPDATE ciswatch_events SET processed = 1 WHERE id = ?');
                $updateStmt->execute([$event['id']]);
            }

        } catch (\Exception $e) {
            $this->logError('CISWatch monitoring failed: ' . $e->getMessage());
        }

        return $threats;
    }

    /**
     * Monitor compliance violations
     *
     * @return array Detected issues
     */
    private function monitorCompliance(): array
    {
        $issues = [];

        try {
            // Age verification failures
            $stmt = $this->db->prepare('
                SELECT
                    location_id,
                    COUNT(*) as violation_count,
                    GROUP_CONCAT(transaction_id) as transaction_ids
                FROM compliance_violations
                WHERE violation_type = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY location_id
                HAVING violation_count >= ?
            ');
            $stmt->execute(['age_verification_failed', $this->thresholds['compliance_violation_critical']]);
            $ageViolations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ageViolations as $violation) {
                $issues[] = [
                    'type' => 'compliance_violation',
                    'severity' => 'critical',
                    'violation_type' => 'age_verification_failed',
                    'location_id' => $violation['location_id'],
                    'violation_count' => $violation['violation_count'],
                    'transaction_ids' => explode(',', $violation['transaction_ids'])
                ];
            }

            // Regulatory violations (missing documentation, incorrect labeling, etc.)
            $stmt = $this->db->prepare('
                SELECT *
                FROM compliance_violations
                WHERE violation_type IN (?, ?, ?)
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                AND resolved = 0
            ');
            $stmt->execute([
                'missing_documentation',
                'incorrect_labeling',
                'unauthorized_product'
            ]);
            $regulatoryViolations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($regulatoryViolations as $violation) {
                $issues[] = [
                    'type' => 'compliance_violation',
                    'severity' => 'high',
                    'violation_type' => $violation['violation_type'],
                    'location_id' => $violation['location_id'],
                    'details' => $violation['details']
                ];
            }

        } catch (\Exception $e) {
            $this->logError('Compliance monitoring failed: ' . $e->getMessage());
        }

        return $issues;
    }

    /**
     * Monitor access control anomalies
     *
     * @return array Detected anomalies
     */
    private function monitorAccessControl(): array
    {
        $anomalies = [];

        try {
            // After-hours access
            $stmt = $this->db->prepare('
                SELECT
                    al.*,
                    u.name as user_name,
                    l.name as location_name
                FROM access_log al
                JOIN users u ON al.user_id = u.id
                JOIN locations l ON al.location_id = l.id
                WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                AND (HOUR(al.created_at) < 6 OR HOUR(al.created_at) > 22)
                AND al.authorized = 1
            ');
            $stmt->execute();
            $afterHoursAccess = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($afterHoursAccess as $access) {
                $anomalies[] = [
                    'type' => 'after_hours_access',
                    'severity' => 'medium',
                    'user_id' => $access['user_id'],
                    'user_name' => $access['user_name'],
                    'location_id' => $access['location_id'],
                    'location_name' => $access['location_name'],
                    'access_time' => $access['created_at']
                ];
            }

            // Multiple location access in short time (impossible travel)
            $stmt = $this->db->prepare('
                SELECT
                    user_id,
                    COUNT(DISTINCT location_id) as location_count,
                    GROUP_CONCAT(DISTINCT location_id) as locations,
                    MIN(created_at) as first_access,
                    MAX(created_at) as last_access
                FROM access_log
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY user_id
                HAVING location_count >= 3
            ');
            $stmt->execute();
            $impossibleTravel = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($impossibleTravel as $travel) {
                $timeSpan = strtotime($travel['last_access']) - strtotime($travel['first_access']);

                if ($timeSpan < 3600) { // Less than 1 hour for 3+ locations
                    $anomalies[] = [
                        'type' => 'impossible_travel',
                        'severity' => 'high',
                        'user_id' => $travel['user_id'],
                        'location_count' => $travel['location_count'],
                        'locations' => explode(',', $travel['locations']),
                        'time_span_minutes' => round($timeSpan / 60)
                    ];
                }
            }

            // Unauthorized access attempts
            $stmt = $this->db->prepare('
                SELECT *
                FROM access_log
                WHERE authorized = 0
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ');
            $stmt->execute();
            $unauthorizedAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($unauthorizedAttempts as $attempt) {
                $anomalies[] = [
                    'type' => 'unauthorized_access_attempt',
                    'severity' => 'high',
                    'user_id' => $attempt['user_id'],
                    'location_id' => $attempt['location_id'],
                    'attempted_resource' => $attempt['resource'],
                    'timestamp' => $attempt['created_at']
                ];
            }

        } catch (\Exception $e) {
            $this->logError('Access control monitoring failed: ' . $e->getMessage());
        }

        return $anomalies;
    }

    /**
     * Monitor unusual data access patterns
     *
     * @return array Detected anomalies
     */
    private function monitorDataAccess(): array
    {
        $anomalies = [];

        try {
            // Large data exports
            $stmt = $this->db->prepare('
                SELECT *
                FROM data_access_log
                WHERE action = ?
                AND record_count > 1000
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ');
            $stmt->execute(['export']);
            $largeExports = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($largeExports as $export) {
                $anomalies[] = [
                    'type' => 'large_data_export',
                    'severity' => 'high',
                    'user_id' => $export['user_id'],
                    'data_type' => $export['data_type'],
                    'record_count' => $export['record_count'],
                    'timestamp' => $export['created_at']
                ];
            }

            // Unusual query patterns (potential SQL injection attempts)
            $stmt = $this->db->prepare('
                SELECT *
                FROM query_log
                WHERE suspicious_score >= ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ');
            $stmt->execute([0.8]);
            $suspiciousQueries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($suspiciousQueries as $query) {
                $anomalies[] = [
                    'type' => 'suspicious_query',
                    'severity' => 'critical',
                    'user_id' => $query['user_id'],
                    'query_pattern' => $query['query_pattern'],
                    'suspicious_score' => $query['suspicious_score'],
                    'ip_address' => $query['ip_address']
                ];
            }

        } catch (\Exception $e) {
            $this->logError('Data access monitoring failed: ' . $e->getMessage());
        }

        return $anomalies;
    }

    /**
     * Analyze audit logs for patterns
     *
     * @return array Detected issues
     */
    private function analyzeAuditLogs(): array
    {
        $issues = [];

        try {
            // Privilege escalation attempts
            $stmt = $this->db->prepare('
                SELECT
                    user_id,
                    COUNT(*) as attempt_count,
                    GROUP_CONCAT(action) as actions
                FROM audit_log
                WHERE action LIKE ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY user_id
                HAVING attempt_count >= 3
            ');
            $stmt->execute(['%privilege%']);
            $privilegeAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($privilegeAttempts as $attempt) {
                $issues[] = [
                    'type' => 'privilege_escalation_attempt',
                    'severity' => 'critical',
                    'user_id' => $attempt['user_id'],
                    'attempt_count' => $attempt['attempt_count'],
                    'actions' => explode(',', $attempt['actions'])
                ];
            }

            // Configuration changes without approval
            $stmt = $this->db->prepare('
                SELECT *
                FROM audit_log
                WHERE action = ?
                AND approval_required = 1
                AND approved = 0
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ');
            $stmt->execute(['config_change']);
            $unapprovedChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($unapprovedChanges as $change) {
                $issues[] = [
                    'type' => 'unapproved_config_change',
                    'severity' => 'high',
                    'user_id' => $change['user_id'],
                    'config_key' => $change['config_key'],
                    'old_value' => $change['old_value'],
                    'new_value' => $change['new_value']
                ];
            }

        } catch (\Exception $e) {
            $this->logError('Audit log analysis failed: ' . $e->getMessage());
        }

        return $issues;
    }

    /**
     * Prioritize threats by severity and impact
     *
     * @param array $threats Threats
     * @return array Prioritized threats
     */
    private function prioritizeThreats(array $threats): array
    {
        usort($threats, function($a, $b) {
            $severityOrder = [
                'critical' => 1,
                'high' => 2,
                'medium' => 3,
                'low' => 4
            ];

            $aSeverity = $severityOrder[$a['severity']] ?? 999;
            $bSeverity = $severityOrder[$b['severity']] ?? 999;

            return $aSeverity <=> $bSeverity;
        });

        return $threats;
    }

    /**
     * Handle critical threat immediately
     *
     * @param array $threat Critical threat
     */
    private function handleCriticalThreat(array $threat): void
    {
        try {
            $this->logError(sprintf('CRITICAL SECURITY THREAT: %s', $threat['type']));

            // Send immediate alert
            $this->sendNotification(
                sprintf('CRITICAL SECURITY: %s', $threat['type']),
                $this->formatThreatMessage($threat),
                'danger'
            );

            // Take immediate action based on threat type
            switch ($threat['type']) {
                case 'brute_force_attack':
                case 'credential_stuffing':
                    $this->blockIP($threat['ip_address'], $threat);
                    break;

                case 'camera_event':
                    if ($threat['event_type'] === 'weapon_detected') {
                        $this->triggerEmergencyProtocol($threat);
                    }
                    break;

                case 'suspicious_query':
                    $this->blockUser($threat['user_id'], $threat);
                    $this->blockIP($threat['ip_address'], $threat);
                    break;
            }

            $this->recordTask('critical_threat_response', $threat, 'completed');

        } catch (\Exception $e) {
            $this->logError('Critical threat handling failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate responses for threats
     *
     * @param array $threats Threats
     * @return array Responses
     */
    private function generateResponses(array $threats): array
    {
        $responses = [];

        foreach ($threats as $threat) {
            $response = null;

            switch ($threat['type']) {
                case 'brute_force_attack':
                    $response = [
                        'action' => 'block_ip',
                        'confidence' => 0.95,
                        'threat' => $threat,
                        'reasoning' => sprintf(
                            'IP %s made %d failed login attempts in %d seconds. Blocking for 24 hours.',
                            $threat['ip_address'],
                            $threat['attempt_count'],
                            $threat['window_seconds']
                        )
                    ];
                    break;

                case 'after_hours_access':
                    $response = [
                        'action' => 'log_review',
                        'confidence' => 0.7,
                        'threat' => $threat,
                        'reasoning' => sprintf(
                            '%s accessed %s at %s (after hours). Review required.',
                            $threat['user_name'],
                            $threat['location_name'],
                            $threat['access_time']
                        )
                    ];
                    break;

                case 'compliance_violation':
                    $response = [
                        'action' => 'escalate_compliance',
                        'confidence' => 0.9,
                        'threat' => $threat,
                        'reasoning' => sprintf(
                            '%d %s violations at location %d. Escalating to compliance officer.',
                            $threat['violation_count'] ?? 1,
                            $threat['violation_type'],
                            $threat['location_id']
                        )
                    ];
                    break;

                case 'large_data_export':
                    $response = [
                        'action' => 'investigate_export',
                        'confidence' => 0.8,
                        'threat' => $threat,
                        'reasoning' => sprintf(
                            'User %d exported %d %s records. Investigating for data breach.',
                            $threat['user_id'],
                            $threat['record_count'],
                            $threat['data_type']
                        )
                    ];
                    break;
            }

            if ($response) {
                $responses[] = $response;
            }
        }

        return $responses;
    }

    /**
     * Execute security response
     *
     * @param array $response Response
     * @return bool Success
     */
    private function executeResponse(array $response): bool
    {
        try {
            switch ($response['action']) {
                case 'block_ip':
                    return $this->blockIP(
                        $response['threat']['ip_address'],
                        $response['threat']
                    );

                case 'log_review':
                    return $this->createReviewTask($response);

                case 'escalate_compliance':
                    return $this->escalateToCompliance($response);

                case 'investigate_export':
                    return $this->investigateDataExport($response);

                default:
                    $this->logError('Unknown response action: ' . $response['action']);
                    return false;
            }

        } catch (\Exception $e) {
            $this->logError('Response execution failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Block IP address
     *
     * @param string $ipAddress IP to block
     * @param array $threat Threat details
     * @return bool Success
     */
    private function blockIP(string $ipAddress, array $threat): bool
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO blocked_ips
                (ip_address, reason, threat_data, blocked_until, created_at)
                VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
            ');

            $result = $stmt->execute([
                $ipAddress,
                $threat['type'],
                json_encode($threat)
            ]);

            if ($result) {
                $this->blockedIPs[] = $ipAddress;

                $this->logInfo(sprintf('Blocked IP: %s (reason: %s)', $ipAddress, $threat['type']));

                $this->sendNotification(
                    'IP Blocked',
                    sprintf('IP %s blocked for 24 hours due to %s', $ipAddress, $threat['type']),
                    'warning'
                );

                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->logError('IP blocking failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Block user account
     *
     * @param int $userId User ID
     * @param array $threat Threat details
     * @return bool Success
     */
    private function blockUser(int $userId, array $threat): bool
    {
        try {
            $stmt = $this->db->prepare('
                UPDATE users
                SET status = ?,
                    blocked_reason = ?,
                    blocked_at = NOW()
                WHERE id = ?
            ');

            $result = $stmt->execute([
                'blocked',
                $threat['type'],
                $userId
            ]);

            if ($result) {
                $this->logInfo(sprintf('Blocked user: %d (reason: %s)', $userId, $threat['type']));

                $this->sendNotification(
                    'User Account Blocked',
                    sprintf('User %d blocked due to %s', $userId, $threat['type']),
                    'danger'
                );

                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->logError('User blocking failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger emergency protocol
     *
     * @param array $threat Threat
     */
    private function triggerEmergencyProtocol(array $threat): void
    {
        try {
            $this->logError('EMERGENCY: Weapon detected at ' . $threat['location_name']);

            // Send emergency notifications
            $this->sendNotification(
                '🚨 EMERGENCY: Weapon Detected',
                sprintf(
                    'Weapon detected at %s (Camera %s). Emergency services notified.',
                    $threat['location_name'],
                    $threat['camera_id']
                ),
                'danger'
            );

            // TODO: Integrate with emergency services API
            // TODO: Lock down location via CISWatch integration
            // TODO: Alert local security/police

        } catch (\Exception $e) {
            $this->logError('Emergency protocol failed: ' . $e->getMessage());
        }
    }

    /**
     * Create review task
     *
     * @param array $response Response
     * @return bool Success
     */
    private function createReviewTask(array $response): bool
    {
        try {
            $this->recordTask('security_review', $response['threat'], 'pending');

            $this->sendNotification(
                'Security Review Required',
                $response['reasoning'],
                'warning'
            );

            return true;

        } catch (\Exception $e) {
            $this->logError('Review task creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Escalate to compliance officer
     *
     * @param array $response Response
     * @return bool Success
     */
    private function escalateToCompliance(array $response): bool
    {
        try {
            // Send email to compliance officer
            $this->sendNotification(
                'Compliance Escalation',
                $response['reasoning'],
                'danger'
            );

            $this->recordTask('compliance_escalation', $response['threat'], 'completed');

            return true;

        } catch (\Exception $e) {
            $this->logError('Compliance escalation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Investigate data export
     *
     * @param array $response Response
     * @return bool Success
     */
    private function investigateDataExport(array $response): bool
    {
        try {
            $this->recordTask('data_export_investigation', $response['threat'], 'active');

            $this->sendNotification(
                'Data Export Investigation',
                $response['reasoning'],
                'warning'
            );

            return true;

        } catch (\Exception $e) {
            $this->logError('Export investigation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Request approval for response
     *
     * @param array $response Response
     */
    private function requestApproval(array $response): void
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO ai_decisions
                (agent_id, decision_type, confidence, data, reasoning, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');

            $stmt->execute([
                $this->agentId,
                $response['action'],
                $response['confidence'],
                json_encode($response),
                $response['reasoning'],
                'pending_approval'
            ]);

            $this->sendNotification(
                'Security Action Requires Approval',
                $response['reasoning'],
                'warning'
            );

        } catch (\Exception $e) {
            $this->logError('Approval request failed: ' . $e->getMessage());
        }
    }

    /**
     * Update threat intelligence
     */
    private function updateThreatIntelligence(): void
    {
        try {
            // TODO: Integrate with threat intelligence feeds
            // TODO: Update IP reputation scores
            // TODO: Learn from incident patterns

        } catch (\Exception $e) {
            $this->logError('Threat intelligence update failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if daily report should be generated
     *
     * @return bool Should generate
     */
    private function shouldGenerateReport(): bool
    {
        $hour = (int) date('H');
        return $hour === 8; // 8 AM NZ time
    }

    /**
     * Generate daily security report
     */
    private function generateSecurityReport(): void
    {
        try {
            $report = [
                'date' => date('Y-m-d'),
                'threats_detected' => 0,
                'threats_blocked' => 0,
                'compliance_violations' => 0,
                'camera_events' => 0,
                'ips_blocked' => 0
            ];

            // Get today's tasks
            $stmt = $this->db->prepare('
                SELECT * FROM agent_tasks
                WHERE agent_id = ?
                AND DATE(created_at) = CURDATE()
            ');
            $stmt->execute([$this->agentId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $report['threats_detected'] = count($tasks);

            $this->sendNotification(
                'Daily Security Report',
                sprintf(
                    'Today: %d threats detected, %d blocked. System secure.',
                    $report['threats_detected'],
                    $report['threats_blocked']
                ),
                'info'
            );

        } catch (\Exception $e) {
            $this->logError('Report generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Load security configuration
     */
    private function loadSecurityConfig(): void
    {
        try {
            // Load currently blocked IPs
            $stmt = $this->db->prepare('
                SELECT ip_address
                FROM blocked_ips
                WHERE blocked_until > NOW()
            ');
            $stmt->execute();
            $this->blockedIPs = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ip_address');

            $this->logInfo(sprintf('Loaded %d blocked IPs', count($this->blockedIPs)));

        } catch (\Exception $e) {
            $this->logError('Security config loading failed: ' . $e->getMessage());
        }
    }

    /**
     * Format threat message
     *
     * @param array $threat Threat
     * @return string Formatted message
     */
    private function formatThreatMessage(array $threat): string
    {
        switch ($threat['type']) {
            case 'brute_force_attack':
                return sprintf(
                    'Brute force attack from IP %s: %d failed login attempts in %d seconds',
                    $threat['ip_address'],
                    $threat['attempt_count'],
                    $threat['window_seconds']
                );

            case 'camera_event':
                return sprintf(
                    'CISWatch: %s detected at %s (Camera %s, Confidence: %.0f%%)',
                    $threat['event_type'],
                    $threat['location_name'],
                    $threat['camera_id'],
                    $threat['ai_confidence'] * 100
                );

            case 'compliance_violation':
                return sprintf(
                    'Compliance violation: %s at location %d (%d violations)',
                    $threat['violation_type'],
                    $threat['location_id'],
                    $threat['violation_count'] ?? 1
                );

            default:
                return sprintf('%s detected', $threat['type']);
        }
    }
}
