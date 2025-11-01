<?php

/**
 * SecurityHeaders helper to set consistent security headers across endpoints
 *
 * @package App\Util
 */

declare(strict_types=1);

namespace App\Util;

class SecurityHeaders
{
    /**
     * Apply standard security headers for JSON endpoints.
     * Safe to call multiple times; respects headers_sent().
     */
    public static function applyJson(): void
    {
        if (php_sapi_name() === 'cli' || headers_sent()) {
            return;
        }

        header('Referrer-Policy: no-referrer');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header("Content-Security-Policy: default-src 'none'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self';");
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    /**
     * Apply minimal headers for SSE streaming to avoid interfering with event-stream.
     */
    public static function applySSE(): void
    {
        if (php_sapi_name() === 'cli' || headers_sent()) {
            return;
        }
        // We still add non-content-affecting headers
        header('Referrer-Policy: no-referrer');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
