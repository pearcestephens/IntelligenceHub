<?php

/**
 * Enforces per-request rate limiting using shared utility.
 *
 * @package App\Http\Middleware
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\HttpRequest;
use App\Support\HttpResponse;
use App\Util\RateLimit;
use App\Config;
use App\Support\HttpResponse;

class RateLimiterMiddleware implements MiddlewareInterface
{
    public function handle(HttpRequest $request, callable $next): HttpResponse
    {
        $clientId = $request->server('REMOTE_ADDR', 'unknown');
        $window = (int) Config::get('RATE_LIMIT_WINDOW_MS', 60000);
        $maxRequests = (int) Config::get('RATE_LIMIT_MAX', 120);

        if (!RateLimit::check($clientId, $window, $maxRequests)) {
            return HttpResponse::json([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT',
                    'message' => 'Rate limit exceeded, please slow down.',
                ],
            ], 429, ['Retry-After' => (string) ceil($window / 1000)]);
        }

        return $next($request);
    }
}
