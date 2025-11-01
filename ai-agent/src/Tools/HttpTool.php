<?php

/**
 * HTTP Tool for safe external API requests
 * Provides controlled access to external APIs with rate limiting and security
 *
 * @package App\Tools
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Tools;

use App\Logger;
use App\Config;
use App\Util\Validate;
use App\Util\RateLimit;
use App\Tools\Contracts\ToolContract;

class HttpTool implements ToolContract
{
    private const DEFAULT_TIMEOUT = 30;
    private const MAX_RESPONSE_SIZE = 5 * 1024 * 1024; // 5MB
    private const USER_AGENT = 'AI-Agent/1.0 (+https://staff.vapeshed.co.nz)';

    /**
     * Make HTTP request with safety controls
     */
    public static function request(array $parameters, array $context = []): array
    {
        $url = $parameters['url'] ?? '';
        $method = strtoupper($parameters['method'] ?? 'GET');
        $headers = $parameters['headers'] ?? [];
        $body = $parameters['body'] ?? null;
        $timeout = $parameters['timeout'] ?? self::DEFAULT_TIMEOUT;
        $followRedirects = $parameters['follow_redirects'] ?? true;
        $maxRedirects = $parameters['max_redirects'] ?? 5;

        Validate::string($url, 1, 2048);

        try {
            // Validate URL
            self::validateUrl($url);

            // Check rate limits
            self::checkRateLimit($url);

            // Validate method
            if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'])) {
                throw new \InvalidArgumentException("Unsupported HTTP method: {$method}");
            }

            // Validate timeout
            if ($timeout < 1 || $timeout > 120) {
                throw new \InvalidArgumentException("Timeout must be between 1 and 120 seconds");
            }

            Logger::info('HTTP request initiated', [
                'url' => self::sanitizeUrl($url),
                'method' => $method,
                'timeout' => $timeout,
                'has_body' => $body !== null
            ]);

            // Prepare cURL
            $ch = curl_init();
            $startTime = microtime(true);

            // Basic options
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => min($timeout, 10),
                CURLOPT_USERAGENT => self::USER_AGENT,
                CURLOPT_FOLLOWLOCATION => $followRedirects,
                CURLOPT_MAXREDIRS => $maxRedirects,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADER => true,
                CURLOPT_HEADERFUNCTION => function ($ch, $header) {
                    return self::headerCallback($header);
                }
            ]);

            // Set method and body
            switch ($method) {
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    if ($body !== null) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                    }
                    break;
                case 'PUT':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    if ($body !== null) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                    }
                    break;
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
                case 'PATCH':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                    if ($body !== null) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                    }
                    break;
                case 'HEAD':
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    break;
                case 'OPTIONS':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
                    break;
            }

            // Set headers
            $curlHeaders = self::prepareHeaders($headers, $body);
            if (!empty($curlHeaders)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
            }

            // Execute request
            $response = curl_exec($ch);
            $duration = (microtime(true) - $startTime) * 1000;

            if ($response === false) {
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                curl_close($ch);

                throw new \RuntimeException("cURL error [{$errno}]: {$error}");
            }

            // Get response info
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $responseInfo = curl_getinfo($ch);
            curl_close($ch);

            // Split headers and body
            $responseHeaders = substr($response, 0, $headerSize);
            $responseBody = substr($response, $headerSize);

            // Check response size
            if (strlen($responseBody) > self::MAX_RESPONSE_SIZE) {
                throw new \RuntimeException("Response too large (max " . self::formatBytes(self::MAX_RESPONSE_SIZE) . ")");
            }

            // Parse headers
            $parsedHeaders = self::parseHeaders($responseHeaders);

            // Detect content type and try to parse JSON
            $contentType = $parsedHeaders['content-type'] ?? 'text/plain';
            $parsedBody = self::parseResponseBody($responseBody, $contentType);

            $redirects = 0;
            if (is_array($responseInfo)) {
                /** @var array{redirect_count?:int} $responseInfo */
                if (array_key_exists('redirect_count', $responseInfo)) {
                    $redirects = (int)$responseInfo['redirect_count'];
                }
            }

            $result = [
                'success' => $httpCode >= 200 && $httpCode < 400,
                'status_code' => $httpCode,
                'headers' => $parsedHeaders,
                'body' => $parsedBody,
                'raw_body' => $responseBody,
                'content_type' => $contentType,
                'size' => strlen($responseBody),
                'size_formatted' => self::formatBytes(strlen($responseBody)),
                'duration_ms' => (int)$duration,
                'request_info' => [
                    'url' => self::sanitizeUrl($url),
                    'method' => $method,
                    'redirects' => $redirects
                ]
            ];

            Logger::info('HTTP request completed', [
                'url' => self::sanitizeUrl($url),
                'method' => $method,
                'status_code' => $httpCode,
                'response_size' => strlen($responseBody),
                'duration_ms' => (int)$duration,
                'success' => $result['success']
            ]);

            return $result;
        } catch (\Throwable $e) {
            Logger::error('HTTP request failed', [
                'url' => self::sanitizeUrl($url),
                'method' => $method,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_type' => 'HttpError',
                'request_info' => [
                    'url' => self::sanitizeUrl($url),
                    'method' => $method
                ]
            ];
        }
    }

    /**
     * Contract run() proxies to request()
     */
    public static function run(array $params, array $context = []): array
    {
        return self::request($params, $context);
    }

    public static function spec(): array
    {
        return [
            'name' => 'http_tool',
            'description' => 'Make HTTPS requests with rate limiting and size/time caps',
            'category' => 'network',
            'internal' => false,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'url' => ['type' => 'string'],
                    'method' => ['type' => 'string', 'enum' => ['GET','POST','PUT','DELETE','PATCH','HEAD','OPTIONS']],
                    'headers' => ['type' => 'object'],
                    'body' => ['type' => 'string'],
                    'timeout' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 120],
                    'follow_redirects' => ['type' => 'boolean'],
                    'max_redirects' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20]
                ],
                'required' => ['url']
            ],
            'safety' => [
                'timeout' => 30,
                'rate_limit' => 50
            ]
        ];
    }

    /**
     * Make multiple HTTP requests in parallel
     */
    public static function multiRequest(array $parameters, array $context = []): array
    {
        $requests = $parameters['requests'] ?? [];
        $maxConcurrency = $parameters['max_concurrency'] ?? 5;

        if (empty($requests)) {
            return [
                'error' => 'No requests provided',
                'error_type' => 'InvalidInput',
                'results' => []
            ];
        }

        if (count($requests) > 20) {
            return [
                'error' => 'Too many requests (max 20)',
                'error_type' => 'TooManyRequests',
                'results' => []
            ];
        }

        try {
            $results = [];
            $batches = array_chunk($requests, $maxConcurrency);

            foreach ($batches as $batch) {
                $batchResults = self::executeBatch($batch);
                $results = array_merge($results, $batchResults);

                // Small delay between batches
                if (count($batches) > 1) {
                    usleep(100000); // 100ms
                }
            }

            $successful = count(array_filter($results, fn($r) => $r['success']));

            Logger::info('Multi HTTP request completed', [
                'total_requests' => count($requests),
                'successful' => $successful,
                'failed' => count($requests) - $successful
            ]);

            return [
                'results' => $results,
                'total_requests' => count($requests),
                'successful' => $successful,
                'failed' => count($requests) - $successful
            ];
        } catch (\Throwable $e) {
            Logger::error('Multi HTTP request failed', [
                'total_requests' => count($requests),
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Multi-request failed: ' . $e->getMessage(),
                'error_type' => 'MultiRequestError',
                'results' => []
            ];
        }
    }

    /**
     * Validate URL for security
     */
    private static function validateUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['scheme'], $parsed['host'])) {
            throw new \InvalidArgumentException('Invalid URL structure');
        }

        // Only allow HTTPS for security
        if (strtolower($parsed['scheme']) !== 'https') {
            throw new \InvalidArgumentException('HTTPS required');
        }

        // Get allowed domains from config
        $allowlist = array_map('trim', explode(',', Config::get('HTTP_TOOL_ALLOWLIST', '')));
        $allowedDomains = array_filter($allowlist);

        if (!empty($allowedDomains)) {
            $host = strtolower($parsed['host']);
            $allowed = false;

            foreach ($allowedDomains as $domain) {
                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                throw new \InvalidArgumentException("Domain not in allowed list: {$parsed['host']}");
            }
        }

        // Block internal/private networks
        $ip = gethostbyname($parsed['host']);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new \InvalidArgumentException("Access to private/internal networks is not allowed");
        }
    }

    /**
     * Check rate limits for domain
     */
    private static function checkRateLimit(string $url): void
    {
        $parsed = parse_url($url);
        $domain = $parsed['host'] ?? 'unknown';

        $key = "http_rate_limit:{$domain}";
        $limit = Config::get('HTTP_RATE_LIMIT', '100/hour');

        if (!RateLimit::check($key, $limit)) {
            throw new \RuntimeException("Rate limit exceeded for domain: {$domain}");
        }
    }

    /**
     * Prepare headers for cURL
     */
    private static function prepareHeaders(array $headers, ?string $body): array
    {
        $curlHeaders = [];

        // Default headers
        $defaultHeaders = [
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Cache-Control' => 'no-cache'
        ];

        // Merge with provided headers
        $allHeaders = array_merge($defaultHeaders, $headers);

        // Set content type for body requests
        if ($body !== null && !isset($allHeaders['Content-Type'])) {
            if (self::isJson($body)) {
                $allHeaders['Content-Type'] = 'application/json';
            } else {
                $allHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        // Format headers for cURL
        foreach ($allHeaders as $name => $value) {
            if (is_string($value)) {
                $curlHeaders[] = "{$name}: {$value}";
            }
        }

        return $curlHeaders;
    }

    /**
     * Parse response headers
     */
    private static function parseHeaders(string $headerString): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerString);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($name, $value) = explode(':', $line, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }

        return $headers;
    }

    /**
     * Parse response body based on content type
     */
    private static function parseResponseBody(string $body, string $contentType): mixed
    {
        if (str_contains(strtolower($contentType), 'application/json')) {
            $json = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return $body;
    }

    /**
     * Header callback for cURL to check response size
     */
    private static function headerCallback(string $header): int
    {
        if (stripos($header, 'content-length:') === 0) {
            $length = (int)trim(substr($header, 15));
            if ($length > self::MAX_RESPONSE_SIZE) {
                throw new \RuntimeException("Response content-length too large");
            }
        }

        return strlen($header);
    }

    /**
     * Execute batch of requests
     */
    private static function executeBatch(array $requests): array
    {
        $results = [];

        // For now, execute sequentially (could be improved with curl_multi_*)
        foreach ($requests as $index => $request) {
            $results[$index] = self::request($request, []);
        }

        return $results;
    }

    /**
     * Check if string is valid JSON
     */
    private static function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Sanitize URL for logging (remove sensitive parameters)
     */
    private static function sanitizeUrl(string $url): string
    {
        $parsed = parse_url($url);

        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $params);

            // Remove sensitive parameters
            $sensitiveParams = ['key', 'token', 'password', 'secret', 'auth', 'api_key'];
            foreach ($sensitiveParams as $param) {
                if (isset($params[$param])) {
                    $params[$param] = '***';
                }
            }

            $parsed['query'] = http_build_query($params);
        }

        return self::buildUrl($parsed);
    }

    /**
     * Build URL from parsed components
     */
    private static function buildUrl(array $parsed): string
    {
        $url = '';

        if (isset($parsed['scheme'])) {
            $url .= $parsed['scheme'] . '://';
        }

        if (isset($parsed['user'])) {
            $url .= $parsed['user'];
            if (isset($parsed['pass'])) {
                $url .= ':' . $parsed['pass'];
            }
            $url .= '@';
        }

        if (isset($parsed['host'])) {
            $url .= $parsed['host'];
        }

        if (isset($parsed['port'])) {
            $url .= ':' . $parsed['port'];
        }

        if (isset($parsed['path'])) {
            $url .= $parsed['path'];
        }

        if (isset($parsed['query'])) {
            $url .= '?' . $parsed['query'];
        }

        if (isset($parsed['fragment'])) {
            $url .= '#' . $parsed['fragment'];
        }

        return $url;
    }

    /**
     * Format bytes to human readable format
     */
    private static function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
