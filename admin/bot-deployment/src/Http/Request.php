<?php
/**
 * HTTP Request Handler
 *
 * Handles HTTP request parsing, validation, and sanitization
 *
 * @package BotDeployment\Http
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Http;

class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $query;
    private array $body;
    private array $params;
    private array $files;
    private array $server;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->headers = $this->parseHeaders();
        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->params = [];
        $this->files = $_FILES;
        $this->server = $_SERVER;
    }

    /**
     * Create request from globals
     */
    public static function capture(): self
    {
        return new self();
    }

    /**
     * Get HTTP method
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get request URI
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get header value
     */
    public function header(string $key, $default = null)
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get all headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get query parameter
     */
    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get all query parameters
     */
    public function allQuery(): array
    {
        return $this->query;
    }

    /**
     * Get input from body
     */
    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Get all body inputs
     */
    public function all(): array
    {
        return $this->body;
    }

    /**
     * Get only specified inputs
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->body, array_flip($keys));
    }

    /**
     * Get all except specified inputs
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->body, array_flip($keys));
    }

    /**
     * Check if input exists
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body);
    }

    /**
     * Check if input is filled (exists and not empty)
     */
    public function filled(string $key): bool
    {
        return $this->has($key) && !empty($this->body[$key]);
    }

    /**
     * Get route parameter
     */
    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Set route parameters
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Get uploaded file
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Validate request inputs
     *
     * @param array $rules Validation rules
     * @return array Validated data
     * @throws \Exception If validation fails
     */
    public function validate(array $rules): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $this->input($field);
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($fieldRules as $rule) {
                $result = $this->validateRule($field, $value, $rule);

                if ($result !== true) {
                    $errors[$field][] = $result;
                    break; // Stop on first error for this field
                }
            }

            if (!isset($errors[$field])) {
                $validated[$field] = $value;
            }
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode([
                'validation_errors' => $errors
            ]));
        }

        return $validated;
    }

    /**
     * Validate single rule
     */
    private function validateRule(string $field, $value, string $rule)
    {
        // Parse rule with parameters (e.g., "max:255")
        $ruleParts = explode(':', $rule, 2);
        $ruleName = $ruleParts[0];
        $ruleParam = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return "{$field} is required";
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    return "{$field} must be a string";
                }
                break;

            case 'integer':
            case 'int':
                if (!is_numeric($value) || (int)$value != $value) {
                    return "{$field} must be an integer";
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    return "{$field} must be numeric";
                }
                break;

            case 'boolean':
            case 'bool':
                if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
                    return "{$field} must be boolean";
                }
                break;

            case 'array':
                if (!is_array($value)) {
                    return "{$field} must be an array";
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "{$field} must be a valid email";
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return "{$field} must be a valid URL";
                }
                break;

            case 'min':
                if (is_string($value) && strlen($value) < $ruleParam) {
                    return "{$field} must be at least {$ruleParam} characters";
                }
                if (is_numeric($value) && $value < $ruleParam) {
                    return "{$field} must be at least {$ruleParam}";
                }
                break;

            case 'max':
                if (is_string($value) && strlen($value) > $ruleParam) {
                    return "{$field} must not exceed {$ruleParam} characters";
                }
                if (is_numeric($value) && $value > $ruleParam) {
                    return "{$field} must not exceed {$ruleParam}";
                }
                break;

            case 'in':
                $allowed = explode(',', $ruleParam);
                if (!in_array($value, $allowed, true)) {
                    return "{$field} must be one of: " . implode(', ', $allowed);
                }
                break;

            case 'regex':
                if (!preg_match($ruleParam, $value)) {
                    return "{$field} format is invalid";
                }
                break;
        }

        return true;
    }

    /**
     * Parse request headers
     */
    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($header)] = $value;
            }
        }

        // Add content type if present
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        return $headers;
    }

    /**
     * Parse request body
     */
    private function parseBody(): array
    {
        $contentType = $this->header('content-type', '');

        // JSON request
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $decoded = json_decode($json, true);
            return $decoded ?? [];
        }

        // Form data
        if ($this->method === 'POST' || $this->method === 'PUT' || $this->method === 'PATCH') {
            return $_POST;
        }

        return [];
    }

    /**
     * Check if request expects JSON
     */
    public function expectsJson(): bool
    {
        $accept = $this->header('accept', '');
        return strpos($accept, 'application/json') !== false;
    }

    /**
     * Check if request is JSON
     */
    public function isJson(): bool
    {
        $contentType = $this->header('content-type', '');
        return strpos($contentType, 'application/json') !== false;
    }

    /**
     * Get client IP address
     */
    public function ip(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get user agent
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
}
