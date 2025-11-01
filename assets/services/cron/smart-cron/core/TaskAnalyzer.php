<?php
/**
 * Smart Cron - Task Analyzer
 * 
 * Analyzes task metrics and classifies tasks as heavy, medium, or light.
 * Used by ScheduleOptimizer to determine optimal scheduling.
 * 
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class TaskAnalyzer
{
    private Config $config;
    private MetricsCollector $metrics;
    
    public function __construct(Config $config, MetricsCollector $metrics)
    {
        $this->config = $config;
        $this->metrics = $metrics;
    }
    
    /**
     * Analyze all tasks and classify them
     */
    public function analyzeAll(): array
    {
        $tasks = $this->getAllTasks();
        
        $heavy = [];
        $medium = [];
        $light = [];
        
        foreach ($tasks as $taskName) {
            $metrics = $this->metrics->getTaskMetrics($taskName, 30);
            $classification = $this->classifyTask($metrics);
            
            $taskData = [
                'name' => $taskName,
                'avg_duration' => $metrics['avg_duration'],
                'avg_memory_mb' => $metrics['avg_memory_mb'],
                'avg_cpu' => $metrics['avg_cpu'],
                'success_rate' => $metrics['success_rate'],
            ];
            
            switch ($classification) {
                case 'heavy':
                    $heavy[] = $taskData;
                    break;
                case 'medium':
                    $medium[] = $taskData;
                    break;
                default:
                    $light[] = $taskData;
            }
        }
        
        return [
            'heavy' => $heavy,
            'medium' => $medium,
            'light' => $light,
            'analyzed_at' => date('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Classify a task based on its metrics
     */
    private function classifyTask(array $metrics): string
    {
        $heavyDuration = $this->config->get('thresholds.heavy_duration_seconds');
        $heavyMemory = $this->config->get('thresholds.heavy_memory_mb');
        $mediumDuration = $this->config->get('thresholds.medium_duration_seconds');
        $mediumMemory = $this->config->get('thresholds.medium_memory_mb');
        
        // Heavy if exceeds either threshold
        if ($metrics['avg_duration'] > $heavyDuration || $metrics['avg_memory_mb'] > $heavyMemory) {
            return 'heavy';
        }
        
        // Medium if exceeds either threshold
        if ($metrics['avg_duration'] > $mediumDuration || $metrics['avg_memory_mb'] > $mediumMemory) {
            return 'medium';
        }
        
        return 'light';
    }
    
    /**
     * Get all unique task names from metrics
     */
    private function getAllTasks(): array
    {
        $db = $this->config->getDbConnection();
        $result = $db->query("SELECT DISTINCT task_name FROM cron_metrics WHERE exit_code != 999");
        
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row['task_name'];
        }
        
        return $tasks;
    }
    
    /**
     * Get heaviest tasks
     */
    public function getHeaviestTasks(int $limit = 10): array
    {
        $db = $this->config->getDbConnection();
        
        $stmt = $db->prepare(
            "SELECT 
                task_name,
                AVG(duration_seconds) as avg_duration,
                AVG(memory_peak_mb) as avg_memory_mb,
                AVG(cpu_peak_percent) as avg_cpu
            FROM cron_metrics
            WHERE executed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND exit_code != 999
            GROUP BY task_name
            ORDER BY avg_duration DESC, avg_memory_mb DESC
            LIMIT ?"
        );
        
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = [
                'name' => $row['task_name'],
                'avg_duration' => round((float)$row['avg_duration'], 3),
                'avg_memory_mb' => round((float)$row['avg_memory_mb'], 2),
                'avg_cpu' => round((float)($row['avg_cpu'] ?? 0), 2),
            ];
        }
        
        $stmt->close();
        return $tasks;
    }
}
