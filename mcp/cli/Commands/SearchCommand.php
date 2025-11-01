<?php
/**
 * Search Command - CLI Interface for Semantic Search
 *
 * Usage: php cli/mcp search "query string" [options]
 *
 * @package IntelligenceHub\MCP\CLI\Commands
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\CLI\Commands;

use IntelligenceHub\MCP\Tools\SemanticSearchTool;

class SearchCommand
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Execute search command
     *
     * @param string $query Search query
     * @return int Exit code
     */
    public function execute(?string $query): int
    {
        // Show help if no query or help flag
        if (!$query || isset($this->options['help'])) {
            $this->showHelp();
            return 0;
        }

        // Build search options
        $searchOptions = [];

        if (isset($this->options['unit_id'])) {
            $searchOptions['unit_id'] = (int)$this->options['unit_id'];
        }

        if (isset($this->options['limit'])) {
            $searchOptions['limit'] = min((int)$this->options['limit'], 100);
        } else {
            $searchOptions['limit'] = 10; // Default
        }

        if (isset($this->options['file_type'])) {
            $searchOptions['file_type'] = $this->options['file_type'];
        }

        if (isset($this->options['category'])) {
            $searchOptions['category'] = $this->options['category'];
        }

        // Execute search
        $this->info("Searching for: \"{$query}\"");

        if (!empty($searchOptions)) {
            $this->info("Options: " . json_encode($searchOptions));
        }

        echo "\n";

        $searchTool = new SemanticSearchTool();
        $startTime = microtime(true);

        try {
            $result = $searchTool->execute([
                'query' => $query,
                'options' => $searchOptions
            ]);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if (!$result['success']) {
                $this->error("Search failed: " . ($result['error'] ?? 'Unknown error'));
                return 1;
            }

            // Display results
            $this->displayResults($result, $duration);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());

            if (isset($this->options['verbose'])) {
                echo "\n" . $e->getTraceAsString() . "\n";
            }

            return 1;
        }
    }

    /**
     * Display search results
     */
    private function displayResults(array $result, float $duration): void
    {
        $results = $result['results'] ?? [];
        $totalResults = count($results);
        $cacheHit = $result['cache_hit'] ?? false;

        // Summary header
        $this->success("Found {$totalResults} results in {$duration}ms" .
                      ($cacheHit ? " (cached)" : ""));

        if ($totalResults === 0) {
            $this->warning("No results found. Try a different query.");
            return;
        }

        echo "\n";
        echo str_repeat("─", 80) . "\n\n";

        // Display each result
        foreach ($results as $index => $item) {
            $rank = $index + 1;
            $score = $item['score'] ?? 0;
            $filePath = $item['file_path'] ?? $item['content_path'] ?? 'Unknown';
            $fileName = $item['file_name'] ?? $item['content_name'] ?? basename($filePath);
            $preview = $item['preview'] ?? '';

            // Result header with rank and score
            $this->info("#{$rank}", false);
            echo " {$fileName} ";
            $this->printScore($score);
            echo "\n";

            // File path
            echo "    \033[90m{$filePath}\033[0m\n";

            // Preview (if available)
            if ($preview) {
                $previewLines = explode("\n", wordwrap($preview, 76));
                foreach (array_slice($previewLines, 0, 3) as $line) {
                    echo "    " . trim($line) . "\n";
                }
                if (count($previewLines) > 3) {
                    echo "    ...\n";
                }
            }

            // Metadata (if verbose)
            if (isset($this->options['verbose'])) {
                $metadata = [];
                if (isset($item['unit_id'])) {
                    $metadata[] = "unit:{$item['unit_id']}";
                }
                if (isset($item['complexity_score'])) {
                    $metadata[] = "complexity:{$item['complexity_score']}";
                }
                if (isset($item['quality_score'])) {
                    $metadata[] = "quality:{$item['quality_score']}";
                }

                if (!empty($metadata)) {
                    echo "    \033[90m[" . implode(", ", $metadata) . "]\033[0m\n";
                }
            }

            echo "\n";
        }

        echo str_repeat("─", 80) . "\n";
    }

    /**
     * Print relevance score with color coding
     */
    private function printScore(float $score): void
    {
        $percentage = round($score * 100);

        // Color based on score
        if ($score >= 0.7) {
            $color = "\033[32m"; // Green - high relevance
        } elseif ($score >= 0.4) {
            $color = "\033[33m"; // Yellow - medium relevance
        } else {
            $color = "\033[90m"; // Gray - low relevance
        }

        echo "{$color}({$percentage}%)\033[0m";
    }

    /**
     * Show help message
     */
    private function showHelp(): void
    {
        echo <<<HELP
\033[1mMCP Search Command\033[0m

Search the intelligence hub using semantic search.

\033[1mUsage:\033[0m
  php cli/mcp search "<query>" [options]

\033[1mArguments:\033[0m
  query                Search query string (required)

\033[1mOptions:\033[0m
  --unit-id=<id>       Filter by unit ID
  --limit=<n>          Maximum results to return (default: 10, max: 100)
  --file-type=<type>   Filter by file type (e.g., php, js, css)
  --category=<cat>     Filter by category
  --verbose            Show detailed output including metadata
  --help               Show this help message

\033[1mExamples:\033[0m
  php cli/mcp search "inventory transfer"
  php cli/mcp search "user authentication" --limit=20
  php cli/mcp search "database query" --unit-id=2 --verbose
  php cli/mcp search "API endpoint" --file-type=php

\033[1mScore Legend:\033[0m
  \033[32m(70%+)\033[0m  - High relevance
  \033[33m(40-69%)\033[0m - Medium relevance
  \033[90m(<40%)\033[0m  - Low relevance

HELP;
    }

    /**
     * Print success message (green)
     */
    private function success(string $message): void
    {
        echo "\033[32m✓\033[0m {$message}\n";
    }

    /**
     * Print info message (blue)
     */
    private function info(string $message, bool $newline = true): void
    {
        echo "\033[34mℹ\033[0m {$message}";
        if ($newline) echo "\n";
    }

    /**
     * Print warning message (yellow)
     */
    private function warning(string $message): void
    {
        echo "\033[33m⚠\033[0m {$message}\n";
    }

    /**
     * Print error message (red)
     */
    private function error(string $message): void
    {
        echo "\033[31m✗\033[0m {$message}\n";
    }
}
