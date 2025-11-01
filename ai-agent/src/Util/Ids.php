<?php

/**
 * ID generation utilities
 * Provides UUID and correlation ID generation for tracking
 *
 * @package App\Util
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Util;

use Ramsey\Uuid\Uuid;

class Ids
{
    /**
     * Generate UUID v4
     */
    public static function uuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Generate conversation ID
     */
    public static function conversation(): string
    {
        return self::uuid();
    }

    /**
     * Generate request ID with prefix
     */
    public static function request(): string
    {
        return 'req_' . uniqid('', true);
    }

    /**
     * Generate session ID
     */
    public static function session(): string
    {
        return 'sess_' . bin2hex(random_bytes(16));
    }

    /**
     * Generate tool call ID
     */
    public static function toolCall(): string
    {
        return 'call_' . bin2hex(random_bytes(12));
    }

    /**
     * Generate document ID for knowledge base
     */
    public static function document(): string
    {
        return self::uuid();
    }

    /**
     * Generate chunk ID for knowledge base
     */
    public static function chunk(): string
    {
        return self::uuid();
    }

    /**
     * Generate short random ID (for temporary use)
     */
    public static function short(int $length = 8): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $result;
    }

    /**
     * Generate numeric ID with timestamp prefix (for ordering)
     */
    public static function timestamped(): string
    {
        return (string)(time() . random_int(1000, 9999));
    }

    /**
     * Validate UUID format
     */
    public static function isValidUuid(string $id): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $id) === 1;
    }

    /**
     * Validate request ID format
     */
    public static function isValidRequestId(string $id): bool
    {
        return preg_match('/^req_[a-f0-9]{13,}[0-9]{4,}$/', $id) === 1;
    }

    /**
     * Extract timestamp from timestamped ID
     */
    public static function extractTimestamp(string $id): ?int
    {
        if (preg_match('/^(\d{10})\d{4}$/', $id, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    /**
     * Generate Redis key with namespace
     */
    public static function redisKey(string $type, string $id, ?string $suffix = null): string
    {
        $key = "{$type}:{$id}";

        if ($suffix) {
            $key .= ":{$suffix}";
        }

        return $key;
    }

    /**
     * Generate conversation Redis keys
     */
    public static function conversationKeys(string $conversationId): array
    {
        return [
            'messages' => self::redisKey('conv', $conversationId, 'messages'),
            'summary' => self::redisKey('conv', $conversationId, 'summary'),
            'metadata' => self::redisKey('conv', $conversationId, 'meta'),
            'locks' => self::redisKey('conv', $conversationId, 'lock')
        ];
    }

    /**
     * Generate rate limit key
     */
    public static function rateLimitKey(string $identifier, string $window = 'default'): string
    {
        return self::redisKey('rate', $identifier, $window);
    }

    /**
     * Generate vector index document key
     */
    public static function vectorDocKey(string $docId): string
    {
        return "doc:{$docId}";
    }

    /**
     * Parse conversation ID from various sources
     */
    public static function parseConversationId(?string $input = null): ?string
    {
        // Try input parameter first
        if ($input && self::isValidUuid($input)) {
            return $input;
        }

        // Try HTTP headers
        $headers = [
            'HTTP_X_CONVERSATION_ID',
            'HTTP_CONVERSATION_ID'
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header]) && self::isValidUuid($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }

        // Try query parameters
        if (isset($_GET['conversationId']) && self::isValidUuid($_GET['conversationId'])) {
            return $_GET['conversationId'];
        }

        return null;
    }

    /**
     * Generate user identifier from request
     */
    public static function userIdentifier(): string
    {
        // Use session ID if available
        if (session_id()) {
            return 'sess_' . session_id();
        }

        // Fall back to IP address (hashed for privacy)
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return 'anon_' . substr(hash('sha256', $ip . $userAgent), 0, 16);
    }

    /**
     * Generate correlation ID for logging
     */
    public static function correlationId(): string
    {
        return $_SERVER['REQUEST_ID'] ?? self::request();
    }
}
