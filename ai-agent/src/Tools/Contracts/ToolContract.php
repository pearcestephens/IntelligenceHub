<?php

declare(strict_types=1);

namespace App\Tools\Contracts;

/**
 * ToolContract defines the strict interface all tools should follow.
 * Static methods are used because tools are invoked without instantiation.
 */
interface ToolContract
{
    /**
     * Execute the tool.
     * @param array $params Validated parameters
     * @param array $context Ambient context (conversation_id, user, etc.)
     * @return array Structured JSON-safe result envelope
     */
    public static function run(array $params, array $context = []): array;

    /**
     * Return tool specification (self-documentation):
     * - name, description, category
     * - parameters (JSON Schema)
     * - safety (timeouts, rate limits)
     * - internal (bot-only)
     */
    public static function spec(): array;
}
