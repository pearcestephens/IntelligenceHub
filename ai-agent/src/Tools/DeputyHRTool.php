<?php

declare(strict_types=1);

namespace App\Tools;

use App\Tools\Contracts\Tool;
use Exception;

/**
 * Deputy Integration Tool for AI Agent System
 *
 * Provides AI agents with access to live Deputy HR data including:
 * - Staff rosters and schedules
 * - Employee information and analytics
 * - Leave requests and approvals
 * - Timesheet and payroll data
 * - Attendance and performance metrics
 *
 * This tool connects the AI Agent system to The Vape Shed's existing
 * Deputy integration for intelligent HR assistance.
 */
class DeputyHRTool implements Tool
{
    private $deputy_api;
    private $cache_ttl = 300; // 5 minutes cache

    public function __construct()
    {
        // Initialize Deputy API integration
        $deputy_file = $_SERVER['DOCUMENT_ROOT'] . '/assets/neuro/deputy_api_integration.php';
        if (file_exists($deputy_file)) {
            require_once $deputy_file;
            $this->deputy_api = new \DeputyAPIIntegration();
        } else {
            throw new Exception('Deputy API integration not found');
        }
    }

    public static function spec(): array
    {
        return [
            'name' => 'deputy_hr',
            'description' => 'Access live Deputy HR data including rosters, staff info, leave requests, and analytics for The Vape Shed',
            'category' => 'hr',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'description' => 'HR action to perform',
                        'enum' => [
                            'get_todays_roster',
                            'get_weekly_roster',
                            'get_employees',
                            'get_employee_by_id',
                            'get_leave_requests',
                            'get_leave_balances',
                            'get_attendance_summary',
                            'get_payroll_status',
                            'analyze_staffing',
                            'get_performance_metrics',
                            'check_deputy_connection'
                        ]
                    ],
                    'employee_id' => [
                        'type' => 'integer',
                        'description' => 'Specific employee ID (when getting employee details)'
                    ],
                    'date_range' => [
                        'type' => 'object',
                        'description' => 'Date range for historical data',
                        'properties' => [
                            'start_date' => ['type' => 'string', 'format' => 'date'],
                            'end_date' => ['type' => 'string', 'format' => 'date']
                        ]
                    ],
                    'location_id' => [
                        'type' => 'integer',
                        'description' => 'Specific store location (outlet ID)'
                    ],
                    'status_filter' => [
                        'type' => 'string',
                        'description' => 'Filter for leave requests',
                        'enum' => ['Pending', 'Approved', 'Declined', 'All']
                    ]
                ],
                'required' => ['action']
            ],
            'safety' => [
                'read_only' => true,
                'requires_auth' => true,
                'rate_limit' => 60, // per minute
                'sensitive_data' => true
            ]
        ];
    }

    public function execute(array $params): array
    {
        try {
            $action = $params['action'] ?? '';

            switch ($action) {
                case 'get_todays_roster':
                    return $this->getTodaysRoster($params);

                case 'get_weekly_roster':
                    return $this->getWeeklyRoster($params);

                case 'get_employees':
                    return $this->getEmployees($params);

                case 'get_employee_by_id':
                    return $this->getEmployeeById($params);

                case 'get_leave_requests':
                    return $this->getLeaveRequests($params);

                case 'get_leave_balances':
                    return $this->getLeaveBalances($params);

                case 'get_attendance_summary':
                    return $this->getAttendanceSummary($params);

                case 'get_payroll_status':
                    return $this->getPayrollStatus($params);

                case 'analyze_staffing':
                    return $this->analyzeStaffing($params);

                case 'get_performance_metrics':
                    return $this->getPerformanceMetrics($params);

                case 'check_deputy_connection':
                    return $this->checkConnection();

                default:
                    return [
                        'success' => false,
                        'error' => 'Unknown action: ' . $action,
                        'available_actions' => [
                            'get_todays_roster', 'get_weekly_roster', 'get_employees',
                            'get_employee_by_id', 'get_leave_requests', 'get_leave_balances',
                            'get_attendance_summary', 'get_payroll_status', 'analyze_staffing',
                            'get_performance_metrics', 'check_deputy_connection'
                        ]
                    ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Deputy tool error: ' . $e->getMessage(),
                'action' => $action ?? 'unknown'
            ];
        }
    }

    private function getTodaysRoster($params): array
    {
        $roster = $this->deputy_api->getTodaysRoster();

        if (isset($roster['error'])) {
            return ['success' => false, 'error' => $roster['error']];
        }

        // Enhance with analytics
        $analytics = [
            'total_shifts' => count($roster),
            'coverage_by_location' => $this->analyzeRosterCoverage($roster),
            'shift_distribution' => $this->analyzeShiftDistribution($roster)
        ];

        return [
            'success' => true,
            'data' => $roster,
            'analytics' => $analytics,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function getWeeklyRoster($params): array
    {
        $weekly_roster = $this->deputy_api->getWeeklyRoster();

        if (isset($weekly_roster['error'])) {
            return ['success' => false, 'error' => $weekly_roster['error']];
        }

        return [
            'success' => true,
            'data' => $weekly_roster,
            'week_summary' => $this->analyzeWeeklyPattern($weekly_roster),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function getEmployees($params): array
    {
        $employees = $this->deputy_api->getEmployees();

        if (isset($employees['error'])) {
            return ['success' => false, 'error' => $employees['error']];
        }

        // Add employee analytics
        $analytics = [
            'total_employees' => count($employees),
            'active_employees' => count(array_filter($employees, function ($emp) {
                return $emp['Active'] ?? false;
            })),
            'by_location' => $this->groupEmployeesByLocation($employees)
        ];

        return [
            'success' => true,
            'data' => $employees,
            'analytics' => $analytics,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function getEmployeeById($params): array
    {
        $employee_id = $params['employee_id'] ?? null;

        if (!$employee_id) {
            return ['success' => false, 'error' => 'employee_id parameter required'];
        }

        $employee = $this->deputy_api->getEmployee($employee_id);

        if (isset($employee['error'])) {
            return ['success' => false, 'error' => $employee['error']];
        }

        return [
            'success' => true,
            'data' => $employee,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function getLeaveRequests($params): array
    {
        $status = $params['status_filter'] ?? 'All';
        $leave_requests = $this->deputy_api->getLeaveRequests($status);

        if (isset($leave_requests['error'])) {
            return ['success' => false, 'error' => $leave_requests['error']];
        }

        // Analyze leave patterns
        $analytics = [
            'total_requests' => count($leave_requests),
            'by_status' => $this->groupLeaveByStatus($leave_requests),
            'upcoming_absences' => $this->getUpcomingAbsences($leave_requests),
            'requires_attention' => $this->flagUrgentLeave($leave_requests)
        ];

        return [
            'success' => true,
            'data' => $leave_requests,
            'analytics' => $analytics,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function analyzeStaffing($params): array
    {
        // Get comprehensive staffing analysis
        $roster = $this->deputy_api->getTodaysRoster();
        $employees = $this->deputy_api->getEmployees();
        $leave_requests = $this->deputy_api->getLeaveRequests('Approved');

        $analysis = [
            'current_coverage' => $this->calculateCurrentCoverage($roster),
            'understaffed_locations' => $this->findUnderstaffedLocations($roster),
            'upcoming_absences' => $this->getUpcomingAbsences($leave_requests),
            'recommendations' => $this->generateStaffingRecommendations($roster, $employees, $leave_requests)
        ];

        return [
            'success' => true,
            'analysis' => $analysis,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function checkConnection(): array
    {
        try {
            $connection = $this->deputy_api->testConnection();

            return [
                'success' => true,
                'connection_status' => 'connected',
                'deputy_response' => $connection,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'connection_status' => 'failed',
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    // Helper methods for analytics

    private function analyzeRosterCoverage($roster): array
    {
        $coverage = [];
        foreach ($roster as $shift) {
            $location = $shift['CompanyName'] ?? 'Unknown';
            if (!isset($coverage[$location])) {
                $coverage[$location] = 0;
            }
            $coverage[$location]++;
        }
        return $coverage;
    }

    private function analyzeShiftDistribution($roster): array
    {
        $distribution = ['morning' => 0, 'afternoon' => 0, 'evening' => 0];

        foreach ($roster as $shift) {
            $start_time = $shift['StartTime'] ?? '';
            if (strpos($start_time, '0') === 0 && strpos($start_time, '12') === false) {
                $distribution['morning']++;
            } elseif (strpos($start_time, '1') === 0 && in_array(substr($start_time, 1, 1), ['0', '1', '2', '3', '4', '5'])) {
                $distribution['afternoon']++;
            } else {
                $distribution['evening']++;
            }
        }

        return $distribution;
    }

    private function groupLeaveByStatus($leave_requests): array
    {
        $by_status = [];
        foreach ($leave_requests as $request) {
            $status = $request['Status'] ?? 'Unknown';
            if (!isset($by_status[$status])) {
                $by_status[$status] = 0;
            }
            $by_status[$status]++;
        }
        return $by_status;
    }

    private function getUpcomingAbsences($leave_requests): array
    {
        $upcoming = [];
        $today = date('Y-m-d');

        foreach ($leave_requests as $request) {
            $start_date = $request['StartDate'] ?? '';
            if ($start_date >= $today) {
                $upcoming[] = [
                    'employee' => $request['EmployeeName'] ?? 'Unknown',
                    'start_date' => $start_date,
                    'end_date' => $request['EndDate'] ?? '',
                    'type' => $request['LeaveType'] ?? 'Leave'
                ];
            }
        }

        // Sort by start date
        usort($upcoming, function ($a, $b) {
            return strcmp($a['start_date'], $b['start_date']);
        });

        return array_slice($upcoming, 0, 10); // Next 10 absences
    }

    private function generateStaffingRecommendations($roster, $employees, $leave_requests): array
    {
        $recommendations = [];

        // Check for understaffing
        if (count($roster) < 15) { // Assuming minimum 15 shifts needed
            $recommendations[] = [
                'priority' => 'high',
                'type' => 'understaffing',
                'message' => 'Today appears understaffed with only ' . count($roster) . ' scheduled shifts.',
                'action' => 'Review coverage and consider calling in additional staff'
            ];
        }

        // Check for upcoming leave conflicts
        $upcoming_leave = $this->getUpcomingAbsences($leave_requests);
        if (count($upcoming_leave) > 5) {
            $recommendations[] = [
                'priority' => 'medium',
                'type' => 'leave_planning',
                'message' => count($upcoming_leave) . ' upcoming absences may impact staffing.',
                'action' => 'Review and plan temporary coverage'
            ];
        }

        return $recommendations;
    }

    // Additional helper methods would go here...

    private function calculateCurrentCoverage($roster): array
    {
        return [
            'total_shifts' => count($roster),
            'coverage_ratio' => count($roster) / 17, // 17 locations
            'status' => count($roster) >= 15 ? 'adequate' : 'understaffed'
        ];
    }

    private function findUnderstaffedLocations($roster): array
    {
        $coverage = $this->analyzeRosterCoverage($roster);
        $understaffed = [];

        foreach ($coverage as $location => $count) {
            if ($count < 1) { // Less than 1 person per location
                $understaffed[] = $location;
            }
        }

        return $understaffed;
    }

    private function groupEmployeesByLocation($employees): array
    {
        $by_location = [];
        foreach ($employees as $employee) {
            $location = $employee['CompanyName'] ?? 'Unknown';
            if (!isset($by_location[$location])) {
                $by_location[$location] = 0;
            }
            $by_location[$location]++;
        }
        return $by_location;
    }

    // Stub methods for features that could be expanded
    private function getLeaveBalances($params): array
    {
        return ['success' => true, 'message' => 'Leave balances feature coming soon'];
    }

    private function getAttendanceSummary($params): array
    {
        return ['success' => true, 'message' => 'Attendance summary feature coming soon'];
    }

    private function getPayrollStatus($params): array
    {
        return ['success' => true, 'message' => 'Payroll status feature coming soon'];
    }

    private function getPerformanceMetrics($params): array
    {
        return ['success' => true, 'message' => 'Performance metrics feature coming soon'];
    }

    private function analyzeWeeklyPattern($weekly_roster): array
    {
        return ['message' => 'Weekly pattern analysis coming soon'];
    }

    private function flagUrgentLeave($leave_requests): array
    {
        return []; // Would flag leave requests requiring immediate attention
    }
}
