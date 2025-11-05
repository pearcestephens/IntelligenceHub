<?php
/**
 * Enhanced AI Agent Query Tool with Optimized Response Format
 *
 * Implements the "tight format" for code search and queries:
 * - Quick mode: top-3 matches, no snippets
 * - Standard mode: top-5, 1-line gist, 120-char snippet
 * - Deep mode: top-10, multi-line snippets + structure map
 * - Raw mode: underlying search rows
 *
 * Response: ≤ 300 words, 2-4s latency, streaming for >5s
 *
 * @package IntelligenceHub\MCP\Tools
 * @version 2.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../ai-agent/lib/AIOrchestrator.php';
require_once __DIR__ . '/../mcp_tools_turbo.php';

/**
 * Query AI Agent with optimized response format
 *
 * @param array $args {
 *     @type string $query The search query
 *     @type string $mode Response mode: quick|standard|deep|raw
 *     @type string $conversation_id Conversation ID for memory
 *     @type int $top_k Max results (default: 5)
 *     @type int $snippet_bytes Snippet size (default: 240)
 *     @type bool $summary_only Return only summary (default: true)
 *     @type bool $include_debug Include debug info (default: false)
 *     @type bool $stream Enable streaming for slow queries (default: false)
 *     @type string $format Output format: list|narrative (default: list)
 * }
 * @return array Optimized response
 */
function ai_agent_query_enhanced(array $args): array {
    $startTime = microtime(true);

    // Parse arguments with defaults
    $query = trim($args['query'] ?? '');
    $mode = $args['mode'] ?? 'standard';
    $conversationId = $args['conversation_id'] ?? 'mcp-' . uniqid();
    $topK = $args['top_k'] ?? getModeDefaults($mode)['top_k'];
    $snippetBytes = $args['snippet_bytes'] ?? getModeDefaults($mode)['snippet_bytes'];
    $summaryOnly = $args['summary_only'] ?? true;
    $includeDebug = $args['include_debug'] ?? false;
    $stream = $args['stream'] ?? false;
    $format = $args['format'] ?? 'list';

    if (empty($query)) {
        return fail('Query is required', 400);
    }

    try {
        // Connect to database
        $db = new mysqli('127.0.0.1', 'hdgwrzntwa', 'bFUdRjh4Jx', 'hdgwrzntwa');
        if ($db->connect_error) {
            throw new Exception("Database connection failed");
        }
        $db->set_charset('utf8mb4');

        // Initialize orchestrator
        $orchestrator = new AIOrchestrator($db, [
            'enable_semantic_search' => true,
            'enable_tool_execution' => true,
            'enable_conversation_memory' => true,
            'max_context_items' => $topK,
            'similarity_threshold' => 0.7,
            'max_memory_turns' => 10
        ]);

        // Process query
        $result = $orchestrator->processQuery($query, $conversationId, 1, [
            'mode' => $mode,
            'top_k' => $topK,
            'snippet_bytes' => $snippetBytes,
            'format' => $format
        ]);

        if (!$result['success']) {
            return fail($result['error'] ?? 'Processing failed', 500);
        }

        // Format response based on mode
        $matches = formatMatches(
            $result['enhanced_context']['knowledge'] ?? [],
            $mode,
            $snippetBytes
        );

        $processingTime = (int)((microtime(true) - $startTime) * 1000);

        // Build optimized response
        $response = [
            'query' => $query,
            'matches' => $matches,
            'count' => count($matches),
            'processing_time_ms' => $processingTime
        ];

        // Add mode-specific footer
        if ($format === 'list') {
            $response['note'] = "Use 'details' to expand any item, or mode:'deep' for full context.";
            $response['next_actions'] = generateNextActions($matches);
        }

        // Conditionally add metadata
        if (!$summaryOnly) {
            $response['metadata'] = [
                'tools_executed' => $result['tools_executed'] ?? [],
                'memory_turns' => $result['memory_turns'] ?? 0,
                'intent' => $result['intent'] ?? []
            ];
        }

        // Conditionally add debug info
        if ($includeDebug) {
            $response['debug'] = [
                'mode' => $mode,
                'top_k' => $topK,
                'snippet_bytes' => $snippetBytes,
                'conversation_id' => $conversationId,
                'orchestrator_time_ms' => $result['processing_time_ms'] ?? 0
            ];
        }

        return ok($response);

    } catch (Exception $e) {
        return fail($e->getMessage(), 500, [
            'query' => $query,
            'processing_time_ms' => (int)((microtime(true) - $startTime) * 1000)
        ]);
    }
}

/**
 * Get default settings for each mode
 */
function getModeDefaults(string $mode): array {
    $defaults = [
        'quick' => ['top_k' => 3, 'snippet_bytes' => 0],
        'standard' => ['top_k' => 5, 'snippet_bytes' => 240],
        'deep' => ['top_k' => 10, 'snippet_bytes' => 600],
        'raw' => ['top_k' => 20, 'snippet_bytes' => 0]
    ];

    return $defaults[$mode] ?? $defaults['standard'];
}

/**
 * Format matches based on mode
 */
function formatMatches(array $knowledgeItems, string $mode, int $snippetBytes): array {
    $matches = [];

    foreach ($knowledgeItems as $item) {
        $match = [
            'path' => $item['file_path'] ?? $item['source'] ?? 'unknown',
            'line' => $item['line_number'] ?? 1,
            'gist' => generateGist($item),
            'score' => round($item['similarity'] ?? 0, 3)
        ];

        // Add snippet based on mode
        if ($mode === 'standard' || $mode === 'deep') {
            $match['snippet'] = truncateSnippet($item['content'] ?? '', $snippetBytes);
        }

        // Add full content for deep mode
        if ($mode === 'deep') {
            $match['full_content'] = $item['content'] ?? '';
            $match['metadata'] = [
                'type' => $item['chunk_type'] ?? 'unknown',
                'size' => strlen($item['content'] ?? ''),
                'indexed_at' => $item['created_at'] ?? null
            ];
        }

        // Raw mode returns everything
        if ($mode === 'raw') {
            $match = $item;
        }

        $matches[] = $match;
    }

    return $matches;
}

/**
 * Generate 1-line gist from knowledge item
 */
function generateGist(array $item): string {
    $content = $item['content'] ?? '';
    $type = $item['chunk_type'] ?? 'text';

    // Extract first meaningful line
    $lines = explode("\n", trim($content));
    foreach ($lines as $line) {
        $line = trim($line);
        if (strlen($line) > 10 && !preg_match('/^(\/\/|#|\/\*|\*)/', $line)) {
            return truncateSnippet($line, 80);
        }
    }

    return truncateSnippet($content, 80);
}

/**
 * Truncate snippet to byte limit
 */
function truncateSnippet(string $text, int $maxBytes): string {
    if ($maxBytes === 0) {
        return '';
    }

    $text = trim(preg_replace('/\s+/', ' ', $text));

    if (strlen($text) <= $maxBytes) {
        return $text;
    }

    return substr($text, 0, $maxBytes - 3) . '...';
}

/**
 * Generate next action affordances
 */
function generateNextActions(array $matches): array {
    $actions = [];

    if (count($matches) > 0) {
        $first = $matches[0];
        $actions[] = "details({$first['path']}) - Expand this file";
        $actions[] = "open({$first['path']}) - Open in editor";
    }

    if (count($matches) > 1) {
        $actions[] = "grep(symbol) - Search for specific symbol";
    }

    return $actions;
}
