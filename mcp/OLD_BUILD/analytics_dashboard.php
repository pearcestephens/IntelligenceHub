<?php
/**
 * MCP Analytics Dashboard
 * 
 * Real-time analytics and insights for MCP server usage
 * 
 * @package IntelligenceHub\MCP
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

class AnalyticsDashboard {
    private PDO $db;
    
    public function __construct() {
        $this->db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    
    /**
     * Get overall statistics
     */
    public function getOverallStats(): array {
        $stats = [];
        
        // Total tool usage
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_calls,
                COUNT(DISTINCT session_id) as unique_sessions,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_calls,
                AVG(execution_time_ms) as avg_execution_time,
                SUM(results_count) as total_results_returned
            FROM mcp_tool_usage
        ");
        $stats['overall'] = $stmt->fetch();
        $stats['overall']['success_rate'] = $stats['overall']['total_calls'] > 0 
            ? round(($stats['overall']['successful_calls'] / $stats['overall']['total_calls']) * 100, 2)
            : 0;
        
        // Tool popularity
        $stmt = $this->db->query("
            SELECT 
                tool_name,
                COUNT(*) as usage_count,
                AVG(execution_time_ms) as avg_time,
                SUM(results_count) as total_results
            FROM mcp_tool_usage
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY tool_name
            ORDER BY usage_count DESC
        ");
        $stats['tool_popularity'] = $stmt->fetchAll();
        
        // Search statistics
        $stmt = $this->db->query("
            SELECT 
                query_type,
                COUNT(*) as search_count,
                AVG(results_found) as avg_results,
                AVG(avg_relevance) as avg_relevance,
                AVG(execution_time_ms) as avg_time
            FROM mcp_search_analytics
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY query_type
            ORDER BY search_count DESC
        ");
        $stats['search_by_type'] = $stmt->fetchAll();
        
        // Category usage
        $stmt = $this->db->query("
            SELECT 
                cat.category_name,
                SUM(cu.search_count) as total_searches,
                SUM(cu.results_returned) as total_results,
                AVG(cu.avg_relevance) as avg_relevance,
                MAX(cu.last_used) as last_used
            FROM mcp_category_usage cu
            JOIN kb_categories cat ON cu.category_id = cat.category_id
            WHERE cu.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY cu.category_id
            ORDER BY total_searches DESC
            LIMIT 10
        ");
        $stats['top_categories'] = $stmt->fetchAll();
        
        // Most popular queries
        $stmt = $this->db->query("
            SELECT 
                query_text,
                query_count,
                avg_execution_time_ms,
                last_executed
            FROM mcp_popular_queries
            ORDER BY query_count DESC
            LIMIT 20
        ");
        $stats['popular_queries'] = $stmt->fetchAll();
        
        // Performance trends (last 24 hours)
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00') as hour,
                metric_type,
                AVG(metric_value) as avg_value,
                MAX(metric_value) as max_value,
                MIN(metric_value) as min_value
            FROM mcp_performance_metrics
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY hour, metric_type
            ORDER BY hour DESC
        ");
        $stats['performance_trends'] = $stmt->fetchAll();
        
        // Active sessions
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_sessions,
                COUNT(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as active_last_hour,
                COUNT(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as active_last_24h,
                AVG(total_queries) as avg_queries_per_session,
                AVG(total_tools_used) as avg_tools_per_session
            FROM mcp_sessions
        ");
        $stats['sessions'] = $stmt->fetch();
        
        // Recent activity
        $stmt = $this->db->query("
            SELECT 
                tool_name,
                arguments,
                execution_time_ms,
                results_count,
                success,
                timestamp
            FROM mcp_tool_usage
            ORDER BY timestamp DESC
            LIMIT 20
        ");
        $stats['recent_activity'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Get hourly usage statistics
     */
    public function getHourlyStats(int $hours = 24): array {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00') as hour,
                COUNT(*) as total_calls,
                COUNT(DISTINCT session_id) as unique_sessions,
                AVG(execution_time_ms) as avg_execution_time,
                SUM(results_count) as total_results
            FROM mcp_tool_usage
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            GROUP BY hour
            ORDER BY hour ASC
        ");
        $stmt->execute([$hours]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get failed requests for debugging
     */
    public function getFailedRequests(int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT 
                tool_name,
                arguments,
                error_message,
                execution_time_ms,
                timestamp,
                session_id
            FROM mcp_tool_usage
            WHERE success = 0
            ORDER BY timestamp DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get slow queries
     */
    public function getSlowQueries(int $thresholdMs = 500, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT 
                tool_name,
                arguments,
                execution_time_ms,
                results_count,
                timestamp
            FROM mcp_tool_usage
            WHERE execution_time_ms > ?
            ORDER BY execution_time_ms DESC
            LIMIT ?
        ");
        $stmt->execute([$thresholdMs, $limit]);
        
        return $stmt->fetchAll();
    }
}

// Main execution
try {
    $dashboard = new AnalyticsDashboard();
    
    $action = $_GET['action'] ?? 'overview';
    
    $result = match($action) {
        'overview' => $dashboard->getOverallStats(),
        'hourly' => $dashboard->getHourlyStats((int)($_GET['hours'] ?? 24)),
        'failed' => $dashboard->getFailedRequests((int)($_GET['limit'] ?? 50)),
        'slow' => $dashboard->getSlowQueries((int)($_GET['threshold'] ?? 500), (int)($_GET['limit'] ?? 50)),
        default => ['error' => 'Unknown action'],
    };
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $result,
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}
