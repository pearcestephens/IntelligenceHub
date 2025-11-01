<?php
/**
 * Web Browser Tool
 *
 * Fetches and parses web pages with content extraction
 *
 * @package IntelligenceHub\MCP\Tools
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Tools;

use Exception;

class WebBrowserTool
{
    private int $timeout = 30;
    private string $userAgent = 'Mozilla/5.0 (compatible; MCPBot/1.0; +https://gpt.ecigdis.co.nz)';
    private int $maxSize = 5242880; // 5MB

    /**
     * Execute web browsing operations
     *
     * @param array $params Operation parameters
     * @return array Result with success status and data
     */
    public function execute(array $params = []): array
    {
        $action = $params['action'] ?? 'fetch';

        return match($action) {
            'fetch' => $this->fetchPage($params),
            'extract' => $this->extractContent($params),
            'headers' => $this->getHeaders($params),
            'screenshot' => $this->takeScreenshot($params),
            default => [
                'success' => false,
                'error' => "Unknown action: {$action}. Available: fetch, extract, headers, screenshot",
            ],
        };
    }

    /**
     * Fetch a web page
     */
    private function fetchPage(array $params): array
    {
        $url = $params['url'] ?? '';
        $includeHtml = $params['include_html'] ?? false;
        $followRedirects = $params['follow_redirects'] ?? true;

        if (empty($url)) {
            return [
                'success' => false,
                'error' => 'url parameter is required',
            ];
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'error' => 'Invalid URL format',
            ];
        }

        try {
            $startTime = microtime(true);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => $followRedirects,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_USERAGENT => $this->userAgent,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_ENCODING => 'gzip,deflate',
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return [
                    'success' => false,
                    'error' => "cURL error: {$error}",
                ];
            }

            curl_close($ch);

            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Parse content
            $extracted = $this->parseHtmlContent($body);

            $result = [
                'url' => $url,
                'final_url' => $finalUrl,
                'http_code' => $httpCode,
                'content_type' => $contentType,
                'size_bytes' => strlen($body),
                'duration_ms' => $duration,
                'title' => $extracted['title'],
                'text_content' => $extracted['text'],
                'links' => $extracted['links'],
                'images' => $extracted['images'],
                'meta' => $extracted['meta'],
            ];

            if ($includeHtml) {
                $result['html'] = $body;
            }

            return [
                'success' => true,
                'data' => $result,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch page: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Extract specific content from a page
     */
    private function extractContent(array $params): array
    {
        $url = $params['url'] ?? '';
        $selector = $params['selector'] ?? 'body';
        $extractType = $params['type'] ?? 'text'; // text, html, attr

        if (empty($url)) {
            return [
                'success' => false,
                'error' => 'url parameter is required',
            ];
        }

        // First fetch the page
        $fetchResult = $this->fetchPage(['url' => $url, 'include_html' => true]);

        if (!$fetchResult['success']) {
            return $fetchResult;
        }

        $html = $fetchResult['data']['html'] ?? '';

        // Simple selector parsing (for complex selectors, would need a proper parser)
        $extracted = $this->extractBySelector($html, $selector, $extractType);

        return [
            'success' => true,
            'data' => [
                'url' => $url,
                'selector' => $selector,
                'type' => $extractType,
                'result' => $extracted,
            ],
        ];
    }

    /**
     * Get headers only (HEAD request)
     */
    private function getHeaders(array $params): array
    {
        $url = $params['url'] ?? '';

        if (empty($url)) {
            return [
                'success' => false,
                'error' => 'url parameter is required',
            ];
        }

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_USERAGENT => $this->userAgent,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return [
                    'success' => false,
                    'error' => "cURL error: {$error}",
                ];
            }

            curl_close($ch);

            // Parse headers
            $headerLines = explode("\r\n", trim($response));
            $headers = [];

            foreach ($headerLines as $line) {
                if (str_contains($line, ':')) {
                    list($key, $value) = explode(':', $line, 2);
                    $headers[trim($key)] = trim($value);
                }
            }

            return [
                'success' => true,
                'data' => [
                    'url' => $url,
                    'http_code' => $httpCode,
                    'headers' => $headers,
                ],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to get headers: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Take a screenshot (requires external service or headless browser)
     */
    private function takeScreenshot(array $params): array
    {
        return [
            'success' => false,
            'error' => 'Screenshot functionality requires headless browser setup (Puppeteer/Playwright)',
            'note' => 'Consider using external screenshot API services',
        ];
    }

    /**
     * Parse HTML content and extract useful information
     */
    private function parseHtmlContent(string $html): array
    {
        $result = [
            'title' => '',
            'text' => '',
            'links' => [],
            'images' => [],
            'meta' => [],
        ];

        // Extract title
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $result['title'] = html_entity_decode(strip_tags($matches[1]));
        }

        // Extract meta tags
        if (preg_match_all('/<meta[^>]+name=["\']([^"\']+)["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $result['meta'][$match[1]] = $match[2];
            }
        }

        // Extract links
        if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\']/i', $html, $matches)) {
            $result['links'] = array_unique(array_slice($matches[1], 0, 100));
        }

        // Extract images
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            $result['images'] = array_unique(array_slice($matches[1], 0, 50));
        }

        // Extract text content (remove scripts, styles, etc)
        $text = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $text = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $text);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $result['text'] = trim(substr($text, 0, 5000)); // First 5000 chars

        return $result;
    }

    /**
     * Extract content by CSS selector (basic implementation)
     */
    private function extractBySelector(string $html, string $selector, string $type): mixed
    {
        // For advanced selector support, would integrate DOMDocument or similar
        // This is a basic implementation

        if ($selector === 'body') {
            $parsed = $this->parseHtmlContent($html);
            return $type === 'text' ? $parsed['text'] : $html;
        }

        // Basic tag extraction
        if (preg_match("/<{$selector}[^>]*>(.*?)<\/{$selector}>/is", $html, $matches)) {
            return $type === 'text' ? strip_tags($matches[1]) : $matches[1];
        }

        return null;
    }
}
