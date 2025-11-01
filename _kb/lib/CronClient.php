<?php
/**
 * Smart Cron API Client
 * 
 * PHP client for interacting with the Smart Cron Management API
 * 
 * @package IntelligenceHub\CronClient
 * @version 1.0.0
 */

declare(strict_types=1);

class CronClient
{
    private string $apiUrl;
    private string $apiKey;
    private array $defaultOptions;
    
    public function __construct(
        string $apiUrl = 'https://staff.vapeshed.co.nz/assets/services/smart-cron/api/manage.php',
        string $apiKey = ''
    ) {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey ?: (getenv('CIS_API_KEY') ?: '');
        $this->defaultOptions = [
            'timeout' => 30,
            'verify_ssl' => true
        ];
        
        if (empty($this->apiKey)) {
            throw new Exception("API key is required. Set CIS_API_KEY environment variable or pass to constructor.");
        }
    }
    
    /**
     * Add new cron job
     */
    public function addJob(array $jobData): array
    {
        return $this->request('POST', 'add', $jobData);
    }
    
    /**
     * Edit existing cron job
     */
    public function editJob(int $jobId, array $updates): array
    {
        return $this->request('PUT', "edit&id={$jobId}", $updates);
    }
    
    /**
     * Delete cron job
     */
    public function deleteJob(int $jobId): array
    {
        return $this->request('DELETE', "delete&id={$jobId}");
    }
    
    /**
     * Enable cron job
     */
    public function enableJob(int $jobId): array
    {
        return $this->request('POST', "enable&id={$jobId}");
    }
    
    /**
     * Disable cron job
     */
    public function disableJob(int $jobId): array
    {
        return $this->request('POST', "disable&id={$jobId}");
    }
    
    /**
     * Run job immediately
     */
    public function runJob(int $jobId): array
    {
        return $this->request('POST', "run&id={$jobId}");
    }
    
    /**
     * List all cron jobs
     */
    public function listJobs(array $filters = []): array
    {
        $queryString = !empty($filters) ? '&' . http_build_query($filters) : '';
        return $this->request('GET', "list{$queryString}");
    }
    
    /**
     * Get single job details
     */
    public function getJob(int $jobId): array
    {
        return $this->request('GET', "get&id={$jobId}");
    }
    
    /**
     * Get system statistics
     */
    public function getStats(): array
    {
        return $this->request('GET', 'stats');
    }
    
    /**
     * Register Intelligence Hub jobs
     */
    public function registerIntelligenceHubJobs(): array
    {
        $jobs = [
            [
                'name' => 'Intelligence Generation (Full)',
                'description' => 'Generate complete intelligence package with call graphs, security scans, and performance analysis',
                'schedule' => '0 3 * * *',  // Daily at 3 AM
                'command' => 'cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/kb_intelligence_engine_v2.php',
                'category' => 'intelligence',
                'timeout' => 600,
                'max_retries' => 2,
                'notify_on_failure' => 1
            ],
            [
                'name' => 'Intelligence Distribution (Push)',
                'description' => 'Push generated intelligence to all configured satellite systems',
                'schedule' => '0 4 * * *',  // Daily at 4 AM (1 hour after generation)
                'command' => 'cd /home/master/applications/hdgwrzntwa/public_html && export CIS_API_KEY="${CIS_API_KEY}" && php _kb/api/intelligence_distributor.php push',
                'category' => 'intelligence',
                'timeout' => 300,
                'max_retries' => 3,
                'notify_on_failure' => 1
            ],
            [
                'name' => 'Call Graph Generation',
                'description' => 'Generate function call graph for code analysis',
                'schedule' => '0 2 * * *',  // Daily at 2 AM (before intelligence generation)
                'command' => 'cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/generate_call_graph.php',
                'category' => 'intelligence',
                'timeout' => 300,
                'max_retries' => 2
            ],
            [
                'name' => 'Security Scanner',
                'description' => 'Enhanced AST-based security vulnerability scanner',
                'schedule' => '0 1 * * *',  // Daily at 1 AM
                'command' => 'cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/enhanced_security_scanner.php',
                'category' => 'security',
                'timeout' => 300,
                'max_retries' => 1,
                'notify_on_failure' => 1
            ]
        ];
        
        $results = [];
        foreach ($jobs as $job) {
            try {
                $result = $this->addJob($job);
                $results[] = [
                    'job' => $job['name'],
                    'success' => $result['success'],
                    'job_id' => $result['data']['job_id'] ?? null
                ];
            } catch (Exception $e) {
                $results[] = [
                    'job' => $job['name'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Make API request
     */
    private function request(string $method, string $action, array $data = []): array
    {
        $url = $this->apiUrl . '?action=' . $action;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->defaultOptions['timeout'],
            CURLOPT_SSL_VERIFYPEER => $this->defaultOptions['verify_ssl'],
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-Key: ' . $this->apiKey
            ]
        ]);
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: {$error}");
        }
        
        if ($httpCode >= 400) {
            $decoded = json_decode($response, true);
            $errorMsg = $decoded['error'] ?? "HTTP {$httpCode}";
            throw new Exception("API error: {$errorMsg}");
        }
        
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        return $result;
    }
}
