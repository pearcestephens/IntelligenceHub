<?php

/**
 * Input validation utilities
 * Provides schema-like validation for tool inputs and API requests
 *
 * @package App\Util
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Util;

class Validate
{
    /**
     * Validate required fields in array
     */
    public static function required(array $data, array $fields): void
    {
        $missing = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new \InvalidArgumentException('Missing required fields: ' . implode(', ', $missing));
        }
    }

    /**
     * Validate string field
     * Backwards-compatible signature: second argument may be the field name (string)
     * or the minimum length (int) for legacy call sites.
     */
    public static function string(
        mixed $value,
        string|int $nameOrMin,
        ?int $minLength = null,
        int $maxLength = PHP_INT_MAX
    ): string {
        // Normalize parameters for backward compatibility
        if (is_int($nameOrMin)) {
            $name = 'value';
            $min = $nameOrMin;
            $max = $minLength ?? PHP_INT_MAX;
        } else {
            $name = $nameOrMin;
            $min = $minLength ?? 0;
            $max = $maxLength;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException("{$name} must be a string");
        }

        $length = strlen($value);

        if ($length < $min) {
            throw new \InvalidArgumentException("{$name} must be at least {$min} characters");
        }

        if ($length > $max) {
            throw new \InvalidArgumentException("{$name} must be no more than {$max} characters");
        }

        return $value;
    }

    /**
     * Validate integer field
     */
    public static function integer(mixed $value, string $name, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("{$name} must be a number");
        }

        $int = (int)$value;

        if ($int < $min) {
            throw new \InvalidArgumentException("{$name} must be at least {$min}");
        }

        if ($int > $max) {
            throw new \InvalidArgumentException("{$name} must be no more than {$max}");
        }

        return $int;
    }

    /**
     * Validate boolean field
     */
    public static function boolean(mixed $value, string $name): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $lower = strtolower($value);
            if (in_array($lower, ['true', '1', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($lower, ['false', '0', 'no', 'off'], true)) {
                return false;
            }
        }

        if (is_numeric($value)) {
            return (bool)$value;
        }

        throw new \InvalidArgumentException("{$name} must be a boolean value");
    }

    /**
     * Validate URL
     */
    public static function url(string $value, string $name, bool $requireHttps = true): string
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("{$name} must be a valid URL");
        }

        if ($requireHttps && !str_starts_with($value, 'https://')) {
            throw new \InvalidArgumentException("{$name} must use HTTPS");
        }

        return $value;
    }

    /**
     * Validate email
     */
    public static function email(string $value, string $name): string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("{$name} must be a valid email address");
        }

        return $value;
    }

    /**
     * Validate UUID
     */
    public static function uuid(string $value, string $name): string
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        if (!preg_match($pattern, $value)) {
            throw new \InvalidArgumentException("{$name} must be a valid UUID v4");
        }

        return $value;
    }

    /**
     * Validate array field
     */
    public static function array(mixed $value, string $name, int $minItems = 0, int $maxItems = PHP_INT_MAX): array
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException("{$name} must be an array");
        }

        $count = count($value);

        if ($count < $minItems) {
            throw new \InvalidArgumentException("{$name} must have at least {$minItems} items");
        }

        if ($count > $maxItems) {
            throw new \InvalidArgumentException("{$name} must have no more than {$maxItems} items");
        }

        return $value;
    }

    /**
     * Validate enum field
     */
    public static function enum(mixed $value, string $name, array $allowedValues): string
    {
        $stringValue = (string)$value;

        if (!in_array($stringValue, $allowedValues, true)) {
            $allowed = implode(', ', $allowedValues);
            throw new \InvalidArgumentException("{$name} must be one of: {$allowed}");
        }

        return $stringValue;
    }

    /**
     * Validate file path (security check)
     */
    public static function filePath(string $value, string $name, string $basePath = ''): string
    {
        // Normalize path
        $normalized = realpath($basePath . DIRECTORY_SEPARATOR . $value);

        if ($normalized === false) {
            throw new \InvalidArgumentException("{$name} is not a valid file path");
        }

        // Ensure path is within base directory (jail check)
        if ($basePath && !str_starts_with($normalized, realpath($basePath))) {
            throw new \InvalidArgumentException("{$name} is outside allowed directory");
        }

        return $normalized;
    }

    /**
     * Validate JSON field
     */
    public static function json(mixed $value, string $name): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException("{$name} must be valid JSON");
        }

        $decoded = json_decode($value, true);

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("{$name} must be valid JSON: " . json_last_error_msg());
        }

        return $decoded ?? [];
    }

    /**
     * Validate SQL query (basic security check)
     */
    public static function sqlQuery(string $value, string $name, array $allowedOperations = ['SELECT']): string
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new \InvalidArgumentException("{$name} cannot be empty");
        }

        // Check for multiple statements
        if (str_contains($trimmed, ';')) {
            throw new \InvalidArgumentException("{$name} cannot contain multiple statements");
        }

        // Get first word (operation)
        $firstWord = strtoupper(explode(' ', $trimmed)[0]);

        if (!in_array($firstWord, $allowedOperations, true)) {
            $allowed = implode(', ', $allowedOperations);
            throw new \InvalidArgumentException("{$name} must start with one of: {$allowed}");
        }

        // Basic SQL injection protection
        $dangerous = [
            'DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE',
            'REPLACE', 'LOAD', 'OUTFILE', 'DUMPFILE', 'EXEC', 'EXECUTE',
            'UNION', 'INFORMATION_SCHEMA', 'MYSQL', 'PERFORMANCE_SCHEMA'
        ];

        if ($firstWord === 'SELECT') {
            foreach ($dangerous as $keyword) {
                if (stripos($trimmed, $keyword) !== false && $keyword !== 'SELECT') {
                    throw new \InvalidArgumentException("{$name} contains potentially dangerous SQL: {$keyword}");
                }
            }
        }

        return $trimmed;
    }

    /**
     * Sanitize string for display
     */
    public static function sanitizeString(string $value, int $maxLength = 1000): string
    {
        // Remove null bytes
        $clean = str_replace("\0", '', $value);

        // Convert to UTF-8 if needed
        if (!mb_check_encoding($clean, 'UTF-8')) {
            $clean = mb_convert_encoding($clean, 'UTF-8', 'auto');
        }

        // Truncate if too long
        if (strlen($clean) > $maxLength) {
            $clean = mb_substr($clean, 0, $maxLength - 3) . '...';
        }

        return $clean;
    }

    /**
     * Validate hostname for HTTP tool allowlist
     */
    public static function hostname(string $value, string $name): string
    {
        if (!filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new \InvalidArgumentException("{$name} must be a valid hostname");
        }

        // Reject localhost and private IPs for security
        if (in_array(strtolower($value), ['localhost', '127.0.0.1', '::1'], true)) {
            throw new \InvalidArgumentException("{$name} cannot be localhost");
        }

        return strtolower($value);
    }

    /**
     * Validate tool schema definition
     */
    public static function toolSchema(array $schema): array
    {
        self::required($schema, ['type', 'properties']);

        if ($schema['type'] !== 'object') {
            throw new \InvalidArgumentException('Tool schema type must be "object"');
        }

        if (!is_array($schema['properties'])) {
            throw new \InvalidArgumentException('Tool schema properties must be an array');
        }

        // Validate each property
        foreach ($schema['properties'] as $propName => $propSchema) {
            if (!is_array($propSchema) || !isset($propSchema['type'])) {
                throw new \InvalidArgumentException("Property '{$propName}' must have a type");
            }
        }

        return $schema;
    }
}
