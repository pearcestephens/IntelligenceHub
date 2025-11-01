<?php
/**
 * Cloudways Cron API Integration
 * 
 * Manages cron jobs across all Cloudways applications via API
 * 
 * @package Intelligence\Services
 * @version 1.0.0
 */

declare(strict_types=1);

class CloudwaysCronAPI
{
    private string $apiUrl = 'https://api.cloudways.com/api/v1';
    private ?string $accessToken = null;
    private string $email;
    private string $apiKey;
    
    // Application server IDs
    private array $servers = [
        'hdgwrzntwa' => [
            'server_id' => '5615757',
            'app_id' => 'hdgwrzntwa',
            'name' => 'Intelligence Hub'
        ],
        'jcepnzzkmj' => [
            'server_id' => '518184',
            'app_id' => 'jcepnzzkmj',
            'name' => 'CIS'
        ],
        'dvaxgvsxmz' => [
            'server_id' => '518184',
            'app_id' => 'dvaxgvsxmz',
            'name' => 'VapeShed Retail'
        ],
        'fhrehrpjmu' => [
            'server_id' => '518184',
            'app_id' => 'fhrehrpjmu',
            'name' => 'Wholesale Portal'
        ]
    ];
    
    public function __construct()
    {
        // Load credentials from environment
        $this->email = getenv('CLOUDWAYS_EMAIL') ?: '';
        $this->apiKey = getenv('CLOUDWAYS_API_KEY') ?: '';
        
        if (empty($this->email) || empty($this->apiKey)) {
            // Try loading from config file
            $configFile = __DIR__ . '/../config/cloudways.conf';
            if (file_exists($configFile)) {
                $config = parse_ini_file($configFile);
                $this->email = $config['email'] ?? '';
                $this->apiKey = $config['api_key'] ?? '';
            }
        }
    }
    
    /**
     * Authenticate with Cloudways API
     */
    private function authenticate(): bool
    {
        if ($this->accessToken) {
            return true; // Already authenticated
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . '/oauth/access_token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'email' => $this->email,
                'api_key' => $this->apiKey
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $this->accessToken = $data['access_token'] ?? null;
            return $this->accessToken !== null;
        }
        
        return false;
    }
    
    /**
     * Make API request
     */
    private function apiRequest(string $endpoint, string $method = 'GET', ?array $data = null): ?array
    {
        if (!$this->authenticate()) {
            return null;
        }
        
        $ch = curl_init();
        $url = $this->apiUrl . $endpoint;
        
        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ]
        ];
        
        if ($method === 'POST') {
            $curlOpts[CURLOPT_POST] = true;
            if ($data) {
                $curlOpts[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        } elseif ($method === 'DELETE') {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }
        
        curl_setopt_array($ch, $curlOpts);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Get all cron jobs for an application
     */
    public function getCronJobs(string $appId): ?array
    {
        $server = $this->servers[$appId] ?? null;
        if (!$server) {
            return null;
        }
        
        $endpoint = sprintf(
            '/app/cron/%s?server_id=%s',
            $server['app_id'],
            $server['server_id']
        );
        
        $result = $this->apiRequest($endpoint, 'GET');
        return $result['cron_jobs'] ?? null;
    }
    
    /**
     * Add a new cron job
     */
    public function addCronJob(string $appId, array $cronData): ?array
    {
        $server = $this->servers[$appId] ?? null;
        if (!$server) {
            return null;
        }
        
        $data = [
            'server_id' => $server['server_id'],
            'app_id' => $server['app_id'],
            'cron_command' => $cronData['command'],
            'cron_min' => $cronData['minute'] ?? '0',
            'cron_hour' => $cronData['hour'] ?? '*',
            'cron_day' => $cronData['day'] ?? '*',
            'cron_month' => $cronData['month'] ?? '*',
            'cron_weekday' => $cronData['weekday'] ?? '*'
        ];
        
        return $this->apiRequest('/app/cron', 'POST', $data);
    }
    
    /**
     * Delete a cron job
     */
    public function deleteCronJob(string $appId, int $cronId): bool
    {
        $server = $this->servers[$appId] ?? null;
        if (!$server) {
            return false;
        }
        
        $data = [
            'server_id' => $server['server_id'],
            'app_id' => $server['app_id'],
            'cron_id' => $cronId
        ];
        
        $result = $this->apiRequest('/app/cron', 'DELETE', $data);
        return $result !== null;
    }
    
    /**
     * Get all crons for all applications
     */
    public function getAllCrons(): array
    {
        $allCrons = [];
        
        foreach ($this->servers as $appId => $server) {
            $crons = $this->getCronJobs($appId);
            if ($crons) {
                $allCrons[$appId] = [
                    'server' => $server,
                    'crons' => $crons
                ];
            }
        }
        
        return $allCrons;
    }
    
    /**
     * Sync Intelligence Hub scanner cron to CIS
     */
    public function syncScannerCron(): bool
    {
        // Add scanner cron to CIS
        $cronData = [
            'command' => 'cd /home/master/applications/jcepnzzkmj/public_html && php scripts/scan_cis.php >> logs/scanner.log 2>&1',
            'minute' => '0',
            'hour' => '*/2', // Every 2 hours
            'day' => '*',
            'month' => '*',
            'weekday' => '*'
        ];
        
        $result = $this->addCronJob('jcepnzzkmj', $cronData);
        return $result !== null;
    }
    
    /**
     * Health check - verify API connectivity
     */
    public function healthCheck(): array
    {
        $start = microtime(true);
        $authenticated = $this->authenticate();
        $duration = round((microtime(true) - $start) * 1000);
        
        return [
            'status' => $authenticated ? 'healthy' : 'error',
            'authenticated' => $authenticated,
            'response_time_ms' => $duration,
            'api_url' => $this->apiUrl,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $api = new CloudwaysCronAPI();
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'health':
            echo "🔍 Cloudways API Health Check\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $health = $api->healthCheck();
            echo "Status: " . ($health['authenticated'] ? '✅ Connected' : '❌ Failed') . "\n";
            echo "Response Time: {$health['response_time_ms']}ms\n";
            echo "API URL: {$health['api_url']}\n";
            echo "Timestamp: {$health['timestamp']}\n";
            break;
            
        case 'list':
            $appId = $argv[2] ?? null;
            if ($appId) {
                echo "📋 Cron Jobs for {$appId}\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $crons = $api->getCronJobs($appId);
                if ($crons) {
                    print_r($crons);
                } else {
                    echo "❌ Failed to retrieve crons\n";
                }
            } else {
                echo "📋 All Cron Jobs Across Applications\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $allCrons = $api->getAllCrons();
                print_r($allCrons);
            }
            break;
            
        case 'sync':
            echo "🔄 Syncing Scanner Cron to CIS\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $result = $api->syncScannerCron();
            echo $result ? "✅ Cron synced successfully\n" : "❌ Failed to sync cron\n";
            break;
            
        case 'help':
        default:
            echo "Cloudways Cron API - Usage\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "Commands:\n";
            echo "  health              - Check API connectivity\n";
            echo "  list [app_id]       - List cron jobs (all or specific app)\n";
            echo "  sync                - Sync scanner cron to CIS\n";
            echo "\nExamples:\n";
            echo "  php cloudways_cron_api.php health\n";
            echo "  php cloudways_cron_api.php list jcepnzzkmj\n";
            echo "  php cloudways_cron_api.php sync\n";
            break;
    }
}
