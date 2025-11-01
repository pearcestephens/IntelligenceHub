<?php
/**
 * Frontend Tester Service
 * 
 * Automated frontend testing with headless Chrome and GPT Vision analysis
 * 
 * Features:
 * - Screenshot capture with Puppeteer/Chrome
 * - GPT Vision UI/UX analysis
 * - Accessibility testing
 * - Responsive design testing
 * - Performance metrics
 * - Visual regression testing
 * - User flow testing
 * 
 * @package IntelligenceHub\Services
 * @version 1.0.0
 */

declare(strict_types=1);

class FrontendTester
{
    private PDO $pdo;
    private string $screenshotDir;
    private string $reportDir;
    private string $puppeteerScript;
    private ?string $openaiApiKey;
    
    public function __construct()
    {
        // Load credentials
        require_once __DIR__ . '/CredentialManager.php';
        $creds = CredentialManager::getAll();
        
        // Database connection
        $this->pdo = new PDO(
            "mysql:host={$creds['database']['host']};dbname={$creds['database']['database']};charset=utf8mb4",
            $creds['database']['username'],
            $creds['database']['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Set up directories
        $this->screenshotDir = $creds['paths']['root'] . '/private_html/frontend_tests/screenshots';
        $this->reportDir = $creds['paths']['root'] . '/private_html/frontend_tests/reports';
        $this->puppeteerScript = $creds['paths']['root'] . '/scripts/puppeteer_tester.js';
        
        // Create directories if they don't exist
        if (!is_dir($this->screenshotDir)) {
            mkdir($this->screenshotDir, 0755, true);
        }
        if (!is_dir($this->reportDir)) {
            mkdir($this->reportDir, 0755, true);
        }
        
        // Get OpenAI API key
        $this->openaiApiKey = $creds['api_keys']['openai'] ?? null;
    }
    
    /**
     * Test a page with full analysis
     * 
     * @param string $url URL to test
     * @param array $options Test options
     * @return array Test results
     */
    public function testPage(string $url, array $options = []): array
    {
        $testId = uniqid('test_', true);
        $timestamp = date('Y-m-d H:i:s');
        
        $results = [
            'test_id' => $testId,
            'url' => $url,
            'timestamp' => $timestamp,
            'success' => false,
            'screenshots' => [],
            'metrics' => [],
            'accessibility' => [],
            'vision_analysis' => null,
            'recommendations' => [],
            'errors' => []
        ];
        
        try {
            // 1. Capture screenshots at different viewports
            $results['screenshots'] = $this->captureScreenshots($url, $testId, $options);
            
            // 2. Collect performance metrics
            $results['metrics'] = $this->collectMetrics($url, $testId);
            
            // 3. Run accessibility tests
            $results['accessibility'] = $this->testAccessibility($url, $testId);
            
            // 4. Analyze with GPT Vision (if API key available)
            if ($this->openaiApiKey && !empty($results['screenshots'])) {
                $results['vision_analysis'] = $this->analyzeWithGPTVision(
                    $results['screenshots']['desktop'] ?? $results['screenshots'][0],
                    $url,
                    $options
                );
            }
            
            // 5. Generate recommendations
            $results['recommendations'] = $this->generateRecommendations($results);
            
            // 6. Save results to database
            $this->saveTestResults($results);
            
            // 7. Generate HTML report
            $reportPath = $this->generateReport($results);
            $results['report_path'] = $reportPath;
            
            $results['success'] = true;
            
        } catch (Exception $e) {
            $results['errors'][] = [
                'type' => 'fatal_error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
        
        return $results;
    }
    
    /**
     * Capture screenshots at different viewports
     */
    private function captureScreenshots(string $url, string $testId, array $options): array
    {
        $viewports = $options['viewports'] ?? [
            'desktop' => ['width' => 1920, 'height' => 1080],
            'laptop' => ['width' => 1366, 'height' => 768],
            'tablet' => ['width' => 768, 'height' => 1024],
            'mobile' => ['width' => 375, 'height' => 667]
        ];
        
        $screenshots = [];
        
        foreach ($viewports as $name => $viewport) {
            $filename = "{$testId}_{$name}.png";
            $filepath = $this->screenshotDir . '/' . $filename;
            
            // Call Puppeteer script to capture screenshot
            $cmd = sprintf(
                'node %s capture --url=%s --width=%d --height=%d --output=%s 2>&1',
                escapeshellarg($this->puppeteerScript),
                escapeshellarg($url),
                $viewport['width'],
                $viewport['height'],
                escapeshellarg($filepath)
            );
            
            exec($cmd, $output, $returnVar);
            
            if ($returnVar === 0 && file_exists($filepath)) {
                $screenshots[$name] = [
                    'viewport' => $viewport,
                    'path' => $filepath,
                    'filename' => $filename,
                    'size' => filesize($filepath),
                    'url' => '/private_html/frontend_tests/screenshots/' . $filename
                ];
            } else {
                $screenshots[$name] = [
                    'error' => 'Screenshot capture failed',
                    'output' => implode("\n", $output)
                ];
            }
        }
        
        return $screenshots;
    }
    
    /**
     * Collect performance metrics
     */
    private function collectMetrics(string $url, string $testId): array
    {
        $cmd = sprintf(
            'node %s metrics --url=%s 2>&1',
            escapeshellarg($this->puppeteerScript),
            escapeshellarg($url)
        );
        
        exec($cmd, $output, $returnVar);
        
        if ($returnVar === 0) {
            $metrics = json_decode(implode('', $output), true);
            return $metrics ?: [];
        }
        
        return [
            'error' => 'Failed to collect metrics',
            'output' => implode("\n", $output)
        ];
    }
    
    /**
     * Test accessibility
     */
    private function testAccessibility(string $url, string $testId): array
    {
        $cmd = sprintf(
            'node %s accessibility --url=%s 2>&1',
            escapeshellarg($this->puppeteerScript),
            escapeshellarg($url)
        );
        
        exec($cmd, $output, $returnVar);
        
        if ($returnVar === 0) {
            $accessibility = json_decode(implode('', $output), true);
            return $accessibility ?: [];
        }
        
        return [
            'error' => 'Failed to run accessibility tests',
            'output' => implode("\n", $output)
        ];
    }
    
    /**
     * Analyze UI with GPT Vision
     */
    private function analyzeWithGPTVision(array $screenshot, string $url, array $options): ?array
    {
        if (!$this->openaiApiKey || !isset($screenshot['path'])) {
            return null;
        }
        
        // Read image and encode to base64
        $imageData = file_get_contents($screenshot['path']);
        $base64Image = base64_encode($imageData);
        
        // Prepare prompt based on test type
        $testType = $options['test_type'] ?? 'general';
        $prompt = $this->getVisionPrompt($testType, $url);
        
        // Call OpenAI Vision API
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiApiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-4-vision-preview',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/png;base64,{$base64Image}"
                            ]
                        ]
                    ]
                ]
            ],
            'max_tokens' => 1000
        ]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return [
                'analysis' => $result['choices'][0]['message']['content'] ?? null,
                'model' => 'gpt-4-vision-preview',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        return [
            'error' => 'GPT Vision API call failed',
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
    
    /**
     * Get prompt for GPT Vision based on test type
     */
    private function getVisionPrompt(string $testType, string $url): string
    {
        $prompts = [
            'general' => "Analyze this web interface and provide:\n1. Overall UI/UX quality assessment\n2. Visual hierarchy and layout issues\n3. Color scheme and contrast problems\n4. Typography issues\n5. Navigation usability\n6. Mobile responsiveness concerns\n7. Accessibility issues you can see\n8. Specific recommendations for improvement\n\nBe specific and actionable.",
            
            'dashboard' => "Analyze this dashboard interface focusing on:\n1. Information density and readability\n2. Data visualization effectiveness\n3. Widget placement and prioritization\n4. Color coding and visual hierarchy\n5. Action buttons clarity\n6. Navigation and filtering options\n7. Performance indicators visibility\n8. Recommended improvements for better UX",
            
            'form' => "Analyze this form interface focusing on:\n1. Form field organization and grouping\n2. Label clarity and positioning\n3. Input field sizing and spacing\n4. Error message visibility\n5. Submit button prominence\n6. Help text and tooltips\n7. Validation feedback\n8. Recommended improvements",
            
            'mobile' => "Analyze this mobile interface focusing on:\n1. Touch target sizes\n2. Mobile-specific navigation\n3. Content readability on small screens\n4. Thumb-friendly zones\n5. Scrolling and swipe interactions\n6. Mobile-specific issues\n7. Recommended mobile improvements",
            
            'accessibility' => "Analyze this interface for accessibility focusing on:\n1. Color contrast issues\n2. Text size and readability\n3. Interactive element visibility\n4. Icon and image clarity\n5. Navigation structure\n6. Potential screen reader issues\n7. Keyboard navigation concerns\n8. WCAG 2.1 compliance issues"
        ];
        
        return $prompts[$testType] ?? $prompts['general'];
    }
    
    /**
     * Generate recommendations based on all test results
     */
    private function generateRecommendations(array $results): array
    {
        $recommendations = [];
        
        // Performance recommendations
        if (!empty($results['metrics'])) {
            $metrics = $results['metrics'];
            
            if (isset($metrics['loadTime']) && $metrics['loadTime'] > 3000) {
                $recommendations[] = [
                    'category' => 'performance',
                    'priority' => 'high',
                    'issue' => 'Slow page load time',
                    'current' => $metrics['loadTime'] . 'ms',
                    'target' => '< 3000ms',
                    'recommendation' => 'Optimize images, minify CSS/JS, enable caching'
                ];
            }
            
            if (isset($metrics['domSize']) && $metrics['domSize'] > 1500) {
                $recommendations[] = [
                    'category' => 'performance',
                    'priority' => 'medium',
                    'issue' => 'Large DOM size',
                    'current' => $metrics['domSize'] . ' nodes',
                    'target' => '< 1500 nodes',
                    'recommendation' => 'Simplify DOM structure, use virtual scrolling for lists'
                ];
            }
        }
        
        // Accessibility recommendations
        if (!empty($results['accessibility'])) {
            $a11y = $results['accessibility'];
            
            if (isset($a11y['violations']) && count($a11y['violations']) > 0) {
                foreach ($a11y['violations'] as $violation) {
                    $recommendations[] = [
                        'category' => 'accessibility',
                        'priority' => $violation['impact'] ?? 'medium',
                        'issue' => $violation['description'] ?? 'Accessibility violation',
                        'recommendation' => $violation['help'] ?? 'Fix accessibility issue'
                    ];
                }
            }
        }
        
        // Vision analysis recommendations
        if (!empty($results['vision_analysis']['analysis'])) {
            $recommendations[] = [
                'category' => 'ux',
                'priority' => 'medium',
                'issue' => 'GPT Vision Analysis',
                'recommendation' => $results['vision_analysis']['analysis']
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Save test results to database
     */
    private function saveTestResults(array $results): void
    {
        $sql = "INSERT INTO frontend_test_results 
                (test_id, url, timestamp, screenshots, metrics, accessibility, 
                 vision_analysis, recommendations, success, errors) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $results['test_id'],
            $results['url'],
            $results['timestamp'],
            json_encode($results['screenshots']),
            json_encode($results['metrics']),
            json_encode($results['accessibility']),
            json_encode($results['vision_analysis']),
            json_encode($results['recommendations']),
            $results['success'] ? 1 : 0,
            json_encode($results['errors'])
        ]);
    }
    
    /**
     * Generate HTML report
     */
    private function generateReport(array $results): string
    {
        $reportFile = $this->reportDir . '/' . $results['test_id'] . '.html';
        
        ob_start();
        include __DIR__ . '/../templates/frontend_test_report.php';
        $html = ob_get_clean();
        
        file_put_contents($reportFile, $html);
        
        return $reportFile;
    }
    
    /**
     * Test multiple pages in batch
     */
    public function batchTest(array $urls, array $options = []): array
    {
        $results = [];
        
        foreach ($urls as $url) {
            $results[$url] = $this->testPage($url, $options);
            
            // Add delay between tests to avoid overwhelming the server
            if (count($urls) > 1) {
                sleep(2);
            }
        }
        
        return $results;
    }
    
    /**
     * Get test history for a URL
     */
    public function getTestHistory(string $url, int $limit = 10): array
    {
        $sql = "SELECT test_id, url, timestamp, success, 
                       JSON_LENGTH(recommendations) as recommendation_count
                FROM frontend_test_results 
                WHERE url = ? 
                ORDER BY timestamp DESC 
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$url, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Compare two test results
     */
    public function compareTests(string $testId1, string $testId2): array
    {
        $sql = "SELECT * FROM frontend_test_results WHERE test_id IN (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$testId1, $testId2]);
        
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($tests) !== 2) {
            return ['error' => 'One or both tests not found'];
        }
        
        return [
            'test_1' => $tests[0],
            'test_2' => $tests[1],
            'comparison' => $this->generateComparison($tests[0], $tests[1])
        ];
    }
    
    /**
     * Generate comparison between two tests
     */
    private function generateComparison(array $test1, array $test2): array
    {
        $comparison = [];
        
        // Compare metrics
        $metrics1 = json_decode($test1['metrics'], true);
        $metrics2 = json_decode($test2['metrics'], true);
        
        if ($metrics1 && $metrics2) {
            $comparison['metrics'] = [
                'load_time' => [
                    'before' => $metrics1['loadTime'] ?? null,
                    'after' => $metrics2['loadTime'] ?? null,
                    'change' => ($metrics2['loadTime'] ?? 0) - ($metrics1['loadTime'] ?? 0)
                ]
            ];
        }
        
        // Compare recommendation counts
        $recs1 = json_decode($test1['recommendations'], true);
        $recs2 = json_decode($test2['recommendations'], true);
        
        $comparison['recommendations'] = [
            'before' => count($recs1 ?? []),
            'after' => count($recs2 ?? []),
            'change' => count($recs2 ?? []) - count($recs1 ?? [])
        ];
        
        return $comparison;
    }
}
