<?php
/**
 * Tool Interface
 *
 * Standard interface for all AI Agent tools
 *
 * @package App\Tools
 * @version 1.0.0
 * @date 2025-11-04
 */

declare(strict_types=1);

namespace App\Tools;

interface ToolInterface
{
    /**
     * Get the unique name/identifier of this tool
     *
     * @return string Tool name (e.g., 'frontend_audit_page')
     */
    public function getName(): string;

    /**
     * Get a human-readable description of what this tool does
     *
     * @return string Tool description
     */
    public function getDescription(): string;

    /**
     * Get the parameters schema for this tool
     *
     * @return array<string, array{type: string, required: bool, description: string, default?: mixed}>
     */
    public function getParameters(): array;

    /**
     * Execute the tool with given parameters
     *
     * @param array<string, mixed> $params Input parameters
     * @return array{success: bool, data?: mixed, error?: string, message?: string}
     */
    public function execute(array $params): array;
}
