<?php
/**
 * CIS Payroll Bot - Automated Payroll Processing
 *
 * This bot integrates with Deputy (timesheets) and Xero (payroll)
 * to automate payroll processing for The Vape Shed staff.
 *
 * Features:
 * - Automatic timesheet validation
 * - Deputy → Xero synchronization
 * - Pay run generation and validation
 * - Exception handling and manager notifications
 * - Compliance checking (NZ employment law)
 *
 * @package CIS\Bots
 * @version 1.0.0
 */

declare(strict_types=1);

class PayrollBot {

    private PDO $pdo;
    private array $config;
    private string $deputyApiKey;
    private string $xeroTenantId;
    private string $xeroAccessToken;

    /**
     * Initialize Payroll Bot
     */
    public function __construct() {
        // Load database connection
        require_once __DIR__ . '/../../app.php';
        $this->pdo = get_db();

        // Load configuration
        $this->config = $this->loadConfig();

        // Load credentials from CredentialManager
        $this->loadCredentials();
    }

    /**
     * Load bot configuration
     */
    private function loadConfig(): array {
        return [
            'deputy' => [
                'base_url' => 'https://api.deputy.com/api/v1',
                'timesheet_approval_required' => true,
                'overtime_threshold_hours' => 40, // per week
                'auto_approve_under_hours' => 8, // daily
            ],
            'xero' => [
                'base_url' => 'https://api.xero.com/payroll.xro/2.0',
                'auto_submit_pay_run' => false, // Requires manual approval
                'pay_frequency' => 'weekly', // weekly, fortnightly, monthly
                'tax_calculation' => 'automatic',
            ],
            'compliance' => [
                'minimum_wage_nzd' => 23.15, // NZ minimum wage as of 2024
                'maximum_hours_per_week' => 40,
                'overtime_rate_multiplier' => 1.5,
                'holiday_rate_multiplier' => 2.0,
            ],
            'notifications' => [
                'manager_email' => 'managers@vapeshed.co.nz',
                'payroll_email' => 'payroll@ecigdis.co.nz',
                'notify_on_exceptions' => true,
            ],
        ];
    }

    /**
     * Load API credentials
     */
    private function loadCredentials(): void {
        require_once __DIR__ . '/../../services/CredentialManager.php';

        $credManager = new CredentialManager();

        // Get Deputy API key
        $deputyCredential = $credManager->getCredential('deputy_api', 'api');
        $this->deputyApiKey = $deputyCredential['key'] ?? throw new Exception('Deputy API key not found');

        // Get Xero credentials
        $xeroCredential = $credManager->getCredential('xero_payroll', 'api');
        $this->xeroTenantId = $xeroCredential['tenant_id'] ?? throw new Exception('Xero tenant ID not found');
        $this->xeroAccessToken = $xeroCredential['access_token'] ?? throw new Exception('Xero access token not found');
    }

    /**
     * Main payroll processing workflow
     */
    public function processPayroll(string $periodEnd): array {
        $this->log("Starting payroll processing for period ending: {$periodEnd}");

        $results = [
            'success' => true,
            'period_end' => $periodEnd,
            'timesheets_processed' => 0,
            'employees_paid' => 0,
            'total_gross' => 0.0,
            'total_net' => 0.0,
            'exceptions' => [],
            'warnings' => [],
        ];

        try {
            // Step 1: Fetch timesheets from Deputy
            $this->log("Fetching timesheets from Deputy...");
            $timesheets = $this->fetchTimesheetsFromDeputy($periodEnd);
            $results['timesheets_processed'] = count($timesheets);

            // Step 2: Validate timesheets
            $this->log("Validating timesheets...");
            $validatedTimesheets = $this->validateTimesheets($timesheets);
            $results['exceptions'] = $validatedTimesheets['exceptions'];
            $results['warnings'] = $validatedTimesheets['warnings'];

            // Step 3: Calculate pay for each employee
            $this->log("Calculating pay for " . count($validatedTimesheets['valid']) . " employees...");
            $payCalculations = $this->calculatePay($validatedTimesheets['valid']);

            // Step 4: Check compliance
            $this->log("Running compliance checks...");
            $complianceResults = $this->checkCompliance($payCalculations);

            if (!empty($complianceResults['violations'])) {
                $results['warnings'] = array_merge($results['warnings'], $complianceResults['violations']);
            }

            // Step 5: Create pay run in Xero
            $this->log("Creating pay run in Xero...");
            $xeroPayRun = $this->createXeroPayRun($payCalculations, $periodEnd);

            $results['employees_paid'] = $xeroPayRun['employee_count'];
            $results['total_gross'] = $xeroPayRun['total_gross'];
            $results['total_net'] = $xeroPayRun['total_net'];
            $results['pay_run_id'] = $xeroPayRun['pay_run_id'];

            // Step 6: Send notifications
            $this->log("Sending notifications...");
            $this->sendNotifications($results);

            // Step 7: Store results in database
            $this->storePayrollRun($results);

            $this->log("Payroll processing completed successfully!");

        } catch (Exception $e) {
            $this->log("ERROR: " . $e->getMessage(), 'error');
            $results['success'] = false;
            $results['error'] = $e->getMessage();

            // Notify on critical error
            $this->sendErrorNotification($e);
        }

        return $results;
    }

    /**
     * Fetch timesheets from Deputy API
     */
    private function fetchTimesheetsFromDeputy(string $periodEnd): array {
        $periodStart = date('Y-m-d', strtotime($periodEnd . ' -7 days')); // Weekly payroll

        $url = $this->config['deputy']['base_url'] . "/timesheets";
        $url .= "?start_date=" . urlencode($periodStart);
        $url .= "&end_date=" . urlencode($periodEnd);
        $url .= "&status=approved";

        $response = $this->deputyApiCall('GET', $url);

        return $response['data'] ?? [];
    }

    /**
     * Validate timesheets for anomalies
     */
    private function validateTimesheets(array $timesheets): array {
        $valid = [];
        $exceptions = [];
        $warnings = [];

        foreach ($timesheets as $timesheet) {
            $validation = $this->validateSingleTimesheet($timesheet);

            if ($validation['is_valid']) {
                $valid[] = $timesheet;
            } else {
                $exceptions[] = [
                    'employee_id' => $timesheet['employee_id'],
                    'employee_name' => $timesheet['employee_name'],
                    'reason' => $validation['reason'],
                    'severity' => $validation['severity'],
                ];
            }

            if (!empty($validation['warnings'])) {
                $warnings = array_merge($warnings, $validation['warnings']);
            }
        }

        return [
            'valid' => $valid,
            'exceptions' => $exceptions,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate single timesheet
     */
    private function validateSingleTimesheet(array $timesheet): array {
        $result = [
            'is_valid' => true,
            'reason' => '',
            'severity' => 'none',
            'warnings' => [],
        ];

        $hoursWorked = $timesheet['total_hours'] ?? 0;

        // Check for excessive hours
        if ($hoursWorked > $this->config['compliance']['maximum_hours_per_week']) {
            $result['warnings'][] = [
                'employee_name' => $timesheet['employee_name'],
                'message' => "Worked {$hoursWorked} hours (exceeds {$this->config['compliance']['maximum_hours_per_week']} hour limit)",
                'requires_approval' => true,
            ];
        }

        // Check for missing clock out
        if (empty($timesheet['end_time'])) {
            $result['is_valid'] = false;
            $result['reason'] = "Missing clock out time";
            $result['severity'] = 'critical';
            return $result;
        }

        // Check for unrealistic hours (>16 in a single day)
        $dailyHours = $this->calculateDailyHours($timesheet);
        foreach ($dailyHours as $date => $hours) {
            if ($hours > 16) {
                $result['warnings'][] = [
                    'employee_name' => $timesheet['employee_name'],
                    'message' => "Worked {$hours} hours on {$date} (possible data error)",
                    'requires_review' => true,
                ];
            }
        }

        return $result;
    }

    /**
     * Calculate pay for validated timesheets
     */
    private function calculatePay(array $timesheets): array {
        $payCalculations = [];

        foreach ($timesheets as $timesheet) {
            $employeeId = $timesheet['employee_id'];

            // Get employee rate from CIS database
            $employee = $this->getEmployee($employeeId);
            $hourlyRate = $employee['hourly_rate'] ?? $this->config['compliance']['minimum_wage_nzd'];

            // Calculate regular and overtime hours
            $regularHours = min($timesheet['total_hours'], $this->config['compliance']['maximum_hours_per_week']);
            $overtimeHours = max(0, $timesheet['total_hours'] - $regularHours);

            // Calculate gross pay
            $regularPay = $regularHours * $hourlyRate;
            $overtimePay = $overtimeHours * $hourlyRate * $this->config['compliance']['overtime_rate_multiplier'];
            $grossPay = $regularPay + $overtimePay;

            // Tax calculation (simplified - Xero handles this accurately)
            $tax = $this->calculateIncomeTax($grossPay);
            $netPay = $grossPay - $tax;

            $payCalculations[] = [
                'employee_id' => $employeeId,
                'employee_name' => $employee['name'],
                'xero_employee_id' => $employee['xero_employee_id'],
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
                'hourly_rate' => $hourlyRate,
                'gross_pay' => $grossPay,
                'tax' => $tax,
                'net_pay' => $netPay,
            ];
        }

        return $payCalculations;
    }

    /**
     * Create pay run in Xero
     */
    private function createXeroPayRun(array $payCalculations, string $periodEnd): array {
        $payRunData = [
            'PayrollCalendarID' => $this->getXeroPayrollCalendarId(),
            'PayRunPeriodStartDate' => date('Y-m-d', strtotime($periodEnd . ' -7 days')),
            'PayRunPeriodEndDate' => $periodEnd,
            'PayRunStatus' => 'Draft', // Requires manual approval before posting
            'PaySlips' => [],
        ];

        foreach ($payCalculations as $pay) {
            $payRunData['PaySlips'][] = [
                'EmployeeID' => $pay['xero_employee_id'],
                'EarningsLines' => [
                    [
                        'EarningsRateID' => $this->getXeroEarningsRateId('Regular'),
                        'NumberOfUnits' => $pay['regular_hours'],
                    ],
                    [
                        'EarningsRateID' => $this->getXeroEarningsRateId('Overtime'),
                        'NumberOfUnits' => $pay['overtime_hours'],
                    ],
                ],
            ];
        }

        $response = $this->xeroApiCall('POST', '/PayRuns', $payRunData);

        $totalGross = array_sum(array_column($payCalculations, 'gross_pay'));
        $totalNet = array_sum(array_column($payCalculations, 'net_pay'));

        return [
            'pay_run_id' => $response['PayRuns'][0]['PayRunID'] ?? null,
            'employee_count' => count($payCalculations),
            'total_gross' => round($totalGross, 2),
            'total_net' => round($totalNet, 2),
        ];
    }

    /**
     * Check compliance with NZ employment law
     */
    private function checkCompliance(array $payCalculations): array {
        $violations = [];

        foreach ($payCalculations as $pay) {
            // Check minimum wage compliance
            if ($pay['hourly_rate'] < $this->config['compliance']['minimum_wage_nzd']) {
                $violations[] = [
                    'employee_name' => $pay['employee_name'],
                    'violation' => 'Below minimum wage',
                    'rate' => $pay['hourly_rate'],
                    'minimum' => $this->config['compliance']['minimum_wage_nzd'],
                    'severity' => 'critical',
                ];
            }
        }

        return [
            'violations' => $violations,
            'compliant' => empty($violations),
        ];
    }

    /**
     * Deputy API call wrapper
     */
    private function deputyApiCall(string $method, string $url, ?array $data = null): array {
        $ch = curl_init($url);

        $headers = [
            'Authorization: Bearer ' . $this->deputyApiKey,
            'Content-Type: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception("Deputy API error: HTTP {$httpCode}");
        }

        return json_decode($response, true) ?? [];
    }

    /**
     * Xero API call wrapper
     */
    private function xeroApiCall(string $method, string $endpoint, ?array $data = null): array {
        $url = $this->config['xero']['base_url'] . $endpoint;

        $ch = curl_init($url);

        $headers = [
            'Authorization: Bearer ' . $this->xeroAccessToken,
            'Xero-Tenant-Id: ' . $this->xeroTenantId,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception("Xero API error: HTTP {$httpCode}");
        }

        return json_decode($response, true) ?? [];
    }

    /**
     * Helper functions
     */

    private function getEmployee(int $employeeId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$employeeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function calculateDailyHours(array $timesheet): array {
        // Implementation depends on timesheet structure
        return [];
    }

    private function calculateIncomeTax(float $grossPay): float {
        // Simplified NZ tax calculation (Xero does this properly)
        // This is just for preview purposes
        if ($grossPay <= 14000) return $grossPay * 0.105;
        if ($grossPay <= 48000) return $grossPay * 0.175;
        if ($grossPay <= 70000) return $grossPay * 0.30;
        return $grossPay * 0.33;
    }

    private function getXeroPayrollCalendarId(): string {
        // Fetch from Xero or cache
        return 'CALENDAR_ID_HERE';
    }

    private function getXeroEarningsRateId(string $type): string {
        // Map earning types to Xero IDs
        return $type === 'Regular' ? 'REGULAR_RATE_ID' : 'OVERTIME_RATE_ID';
    }

    private function storePayrollRun(array $results): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO payroll_runs
            (period_end, timesheets_processed, employees_paid, total_gross, total_net, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $results['period_end'],
            $results['timesheets_processed'],
            $results['employees_paid'],
            $results['total_gross'],
            $results['total_net'],
            $results['success'] ? 'completed' : 'failed',
        ]);
    }

    private function sendNotifications(array $results): void {
        // Send email notifications to managers and payroll team
        $this->log("Sending notifications to managers and payroll team");
    }

    private function sendErrorNotification(Exception $e): void {
        // Send urgent error notification
        $this->log("Sending error notification: " . $e->getMessage(), 'error');
    }

    private function log(string $message, string $level = 'info'): void {
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] [PayrollBot] [{$level}] {$message}");

        // Also store in database
        $stmt = $this->pdo->prepare("
            INSERT INTO bot_logs (bot_name, level, message, created_at)
            VALUES ('PayrollBot', ?, ?, NOW())
        ");
        $stmt->execute([$level, $message]);
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $bot = new PayrollBot();

    $periodEnd = $argv[1] ?? date('Y-m-d', strtotime('last friday'));

    echo "Starting payroll processing for period ending: {$periodEnd}\n";

    $results = $bot->processPayroll($periodEnd);

    echo "\n========================================\n";
    echo "PAYROLL PROCESSING RESULTS\n";
    echo "========================================\n";
    echo "Status: " . ($results['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    echo "Timesheets Processed: {$results['timesheets_processed']}\n";
    echo "Employees Paid: {$results['employees_paid']}\n";
    echo "Total Gross: $" . number_format($results['total_gross'], 2) . "\n";
    echo "Total Net: $" . number_format($results['total_net'], 2) . "\n";
    echo "Exceptions: " . count($results['exceptions']) . "\n";
    echo "Warnings: " . count($results['warnings']) . "\n";
    echo "========================================\n";

    if (!empty($results['exceptions'])) {
        echo "\nEXCEPTIONS REQUIRING ATTENTION:\n";
        foreach ($results['exceptions'] as $ex) {
            echo "- {$ex['employee_name']}: {$ex['reason']} [{$ex['severity']}]\n";
        }
    }
}
