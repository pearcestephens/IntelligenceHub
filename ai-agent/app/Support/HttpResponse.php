<?php

/**
 * HTTP response helper providing JSON and view facilities.
 *
 * @package App\Support
 */

declare(strict_types=1);

namespace App\Support;

class HttpResponse
{
    /**
     * @param array<string,string> $headers
     */
    public function __construct(
        private string $content,
        private int $status = 200,
        private array $headers = []
    ) {
    }

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        $headers = array_merge(['Content-Type' => 'application/json; charset=utf-8'], $headers);
        return new self(json_encode($payload, JSON_UNESCAPED_UNICODE), $status, $headers);
    }

    public static function view(string $viewPath, array $data = [], int $status = 200): self
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();
        return new self($content, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function text(string $content, int $status = 200, array $headers = []): self
    {
        $headers = array_merge(['Content-Type' => 'text/plain; charset=utf-8'], $headers);
        return new self($content, $status, $headers);
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string,string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value, true, $this->status);
        }
        echo $this->content;
    }
}
