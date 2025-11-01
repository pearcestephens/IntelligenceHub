<?php

/**
 * Contract for HTTP middleware components.
 *
 * @package App\Http\Middleware
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\HttpRequest;
use App\Support\HttpResponse;

interface MiddlewareInterface
{
    /**
     * Process the incoming request.
     *
     * @param HttpRequest $request Incoming HTTP request wrapper
     * @param callable $next Next middleware or controller resolver
     */
    public function handle(HttpRequest $request, callable $next): HttpResponse;
}
