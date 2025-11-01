<?php

/**
 * Lightweight HTTP request wrapper for admin router.
 *
 * @package App\Support
 */

declare(strict_types=1);

namespace App\Support;

class HttpRequest
{
    /**
     * @param array<string,mixed> $server
     * @param array<string,mixed> $query
     * @param array<string,mixed> $body
     * @param array<string,string> $headers
     * @param array<string,mixed> $session
     */
    public function __construct(
        private array $server,
        private array $query,
        private array $body,
        private array $headers,
        private array $session
    ) {
    }

    public static function fromGlobals(): self
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[str_replace('_', '-', strtolower(substr($key, 5)))] = (string) $value;
            }
        }

        return new self($_SERVER, $_GET, $_POST, $headers, $_SESSION ?? []);
    }

    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function path(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $pos = strpos($uri, '?');
        return $pos === false ? $uri : substr($uri, 0, $pos);
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function header(string $key, ?string $default = null): ?string
    {
        $normalized = strtolower($key);
        return $this->headers[$normalized] ?? $default;
    }

    public function session(): array
    {
        return $this->session;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}
