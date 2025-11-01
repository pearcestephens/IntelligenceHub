<?php

/**
 * Temporary bootstrap for Claude API - bypasses Composer dependency issues
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set timezone
date_default_timezone_set('Pacific/Auckland');

// Manual class autoloader for our App namespace
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $file = __DIR__ . '/../../../src/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Load environment variables manually
$envFile = __DIR__ . '/../../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(trim($name) . '=' . trim($value));
    }
}

// Simple Claude API class
class SimpleClaudeAPI {
    private $apiKey;
    private $model;
    
    public function __construct() {
        $this->apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? '';
        $this->model = $_ENV['CLAUDE_MODEL'] ?? 'claude-3-5-sonnet-20241022';
    }
    
    public function chat($message) {
        if (!$this->apiKey) {
            return ['error' => 'ANTHROPIC_API_KEY not configured'];
        }
        
        $data = [
            'model' => $this->model,
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey,
                'Anthropic-Version: 2023-06-01'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['error' => 'API request failed with code ' . $httpCode, 'response' => $response];
        }
        
        return json_decode($response, true);
    }
}

?>