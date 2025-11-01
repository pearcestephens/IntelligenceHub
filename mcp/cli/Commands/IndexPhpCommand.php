<?php
/**
 * CLI Command: Index PHP Files
 *
 * Indexes PHP files in a directory or single file
 * Usage: php cli/mcp index:php <path> [options]
 *
 * @package IntelligenceHub\MCP\CLI
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\CLI\Commands;

use IntelligenceHub\MCP\Indexing\PHPIndexer;

class IndexPhpCommand
{
    private array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'unit_id' => 1,
            'exclude' => ['vendor', 'node_modules', '.git', 'tests', 'backups'],
            'max_depth' => null,
            'verbose' => false,
        ], $options);
    }

    /**
     * Execute the indexing command
     *
     * @param string $path Path to index (file or directory)
     * @return int Exit code
     */
    public function execute(string $path): int
    {
        if (empty($path)) {
            $this->error("Error: Path is required");
            $this->showUsage();
            return 1;
        }

        if (!file_exists($path)) {
            $this->error("Error: Path does not exist: {$path}");
            return 1;
        }

        $this->info("═══════════════════════════════════════════════════════════════");
        $this->info("  PHP CODE INDEXER");
        $this->info("═══════════════════════════════════════════════════════════════\n");

        $indexer = new PHPIndexer((int)$this->options['unit_id']);
        $startTime = microtime(true);

        try {
            if (is_file($path)) {
                $this->info("Indexing single file: {$path}");
                $indexer->indexFile($path);
                $stats = $indexer->getStats();
                $stats['files_processed'] = 1;
            } else {
                $this->info("Indexing directory: {$path}");
                $this->info("Unit ID: {$this->options['unit_id']}");
                $this->info("Exclude patterns: " . implode(', ', $this->options['exclude']));
                $this->info("");

                $stats = $indexer->indexDirectory($path, [
                    'exclude' => $this->options['exclude'],
                    'max_depth' => $this->options['max_depth'],
                ]);
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->info("\n═══════════════════════════════════════════════════════════════");
            $this->info("  INDEXING COMPLETE");
            $this->info("═══════════════════════════════════════════════════════════════\n");

            $this->success("✓ Files processed: {$stats['files_processed']}");
            $this->info("  Files skipped: {$stats['files_skipped']}");
            $this->success("✓ Functions found: {$stats['functions_found']}");
            $this->success("✓ Classes found: {$stats['classes_found']}");
            $this->success("✓ Methods found: {$stats['methods_found']}");
            $this->info("  Lines indexed: {$stats['lines_indexed']}");
            $this->info("  Duration: {$duration}ms");

            if (!empty($stats['errors'])) {
                $this->warning("\n⚠ Errors encountered: " . count($stats['errors']));
                if ($this->options['verbose']) {
                    foreach ($stats['errors'] as $error) {
                        $this->error("  - {$error['file']}: {$error['error']}");
                    }
                }
            }

            $this->info("\n═══════════════════════════════════════════════════════════════\n");

            return 0;

        } catch (\Exception $e) {
            $this->error("\n✗ Indexing failed: " . $e->getMessage());
            if ($this->options['verbose']) {
                $this->error("\nStack trace:");
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }

    /**
     * Show usage information
     */
    private function showUsage(): void
    {
        echo <<<USAGE

Usage:
  php cli/mcp index:php <path> [options]

Arguments:
  path                  Path to PHP file or directory to index

Options:
  --unit-id=<id>       Unit ID to assign (default: 1)
  --exclude=<pattern>  Comma-separated exclude patterns (default: vendor,node_modules,.git,tests,backups)
  --max-depth=<n>      Maximum directory depth (default: unlimited)
  --verbose            Show detailed output including errors

Examples:
  php cli/mcp index:php /path/to/code
  php cli/mcp index:php /path/to/file.php --unit-id=2
  php cli/mcp index:php /path/to/code --exclude=vendor,tests --verbose

USAGE;
    }

    /**
     * Print info message
     */
    private function info(string $message): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Print success message (green)
     */
    private function success(string $message): void
    {
        echo "\033[32m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Print warning message (yellow)
     */
    private function warning(string $message): void
    {
        echo "\033[33m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Print error message (red)
     */
    private function error(string $message): void
    {
        echo "\033[31m" . $message . "\033[0m" . PHP_EOL;
    }
}
