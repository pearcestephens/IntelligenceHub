<?php
/**
 * KB Configuration Manager
 * 
 * Centralized configuration system for ALL KB scanners and tools
 * This ensures consistent behavior across all scanning operations
 * 
 * @package CIS\KB\Config
 * @version 2.0.0
 */

declare(strict_types=1);

class KBConfigManager
{
    private static ?self $instance = null;
    private array $config;
    private string $configPath;
    
    private function __construct()
    {
        $this->configPath = '/home/master/applications/hdgwrzntwa/public_html/_kb/config/scan_config.json';
        $this->loadConfig();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load configuration from JSON file
     */
    private function loadConfig(): void
    {
        if (!file_exists($this->configPath)) {
            throw new RuntimeException("Config file not found: {$this->configPath}");
        }
        
        $configContent = file_get_contents($this->configPath);
        $this->config = json_decode($configContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in config file: " . json_last_error_msg());
        }
    }
    
    /**
     * Get scan paths for all scanners
     */
    public function getScanPaths(): array
    {
        return $this->config['scan_paths'] ?? [];
    }
    
    /**
     * Get global exclusion patterns
     */
    public function getGlobalExclusions(): array
    {
        return $this->config['global_exclusions'] ?? [];
    }
    
    /**
     * Get exclusions for specific file type
     */
    public function getFileTypeExclusions(string $fileType): array
    {
        $globalExclusions = $this->getGlobalExclusions();
        $typeConfig = $this->config['file_type_configs'][$fileType] ?? [];
        
        $exclusions = array_merge(
            $globalExclusions['directories'] ?? [],
            $typeConfig['specific_exclusions'] ?? []
        );
        
        return array_unique($exclusions);
    }
    
    /**
     * Check if file should be included based on global rules
     */
    public function shouldIncludeFile(string $filePath, string $fileType = 'general'): bool
    {
        // Check file readability
        if (!is_readable($filePath)) {
            return false;
        }
        
        // Check size limits
        $sizeLimit = $this->config['global_exclusions']['size_limits'] ?? [];
        $fileSize = filesize($filePath);
        
        if (isset($sizeLimit['max_file_size_mb'])) {
            $maxBytes = $sizeLimit['max_file_size_mb'] * 1024 * 1024;
            if ($fileSize > $maxBytes) {
                return false;
            }
        }
        
        if (isset($sizeLimit['min_file_size_bytes'])) {
            if ($fileSize < $sizeLimit['min_file_size_bytes']) {
                return false;
            }
        }
        
        // Check against exclusion patterns
        $exclusions = $this->getFileTypeExclusions($fileType);
        
        foreach ($exclusions as $pattern) {
            if (fnmatch($pattern, $filePath)) {
                return false;
            }
        }
        
        // Check file pattern exclusions
        $filePatterns = $this->config['global_exclusions']['file_patterns'] ?? [];
        $fileName = basename($filePath);
        
        foreach ($filePatterns as $pattern) {
            if (fnmatch($pattern, $fileName)) {
                return false;
            }
        }
        
        // Check if libraries should be included for this file type
        $typeConfig = $this->config['file_type_configs'][$fileType] ?? [];
        $includeLibraries = $typeConfig['include_libraries'] ?? false;
        
        if (!$includeLibraries) {
            if (strpos($filePath, '/node_modules/') !== false || 
                strpos($filePath, '/vendor/') !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get find command for specific file type
     */
    public function getFindCommand(string $fileType): string
    {
        $typeConfig = $this->config['file_type_configs'][$fileType] ?? [];
        $extensions = $typeConfig['extensions'] ?? ['*'];
        
        $scanPaths = $this->getScanPaths();
        $exclusions = $this->getFileTypeExclusions($fileType);
        
        // Build find command
        $pathsStr = "'" . implode("' '", $scanPaths) . "'";
        
        // Build extension pattern
        if (count($extensions) === 1) {
            $extPattern = "-name '*.{$extensions[0]}'";
        } else {
            $extPattern = "\\( " . implode(" -o ", array_map(function($ext) {
                return "-name '*.{$ext}'";
            }, $extensions)) . " \\)";
        }
        
        // Build exclusion patterns
        $exclusionStr = '';
        foreach ($exclusions as $exclusion) {
            $exclusionStr .= " -not -path '{$exclusion}'";
        }
        
        return "find {$pathsStr} -type f {$extPattern}{$exclusionStr} 2>/dev/null";
    }
    
    /**
     * Get batch size for file type
     */
    public function getBatchSize(string $fileType): int
    {
        $typeConfig = $this->config['file_type_configs'][$fileType] ?? [];
        return $typeConfig['max_batch_size'] ?? 100;
    }
    
    /**
     * Get performance settings
     */
    public function getPerformanceSettings(): array
    {
        return $this->config['performance_settings'] ?? [];
    }
    
    /**
     * Get output settings
     */
    public function getOutputSettings(): array
    {
        return $this->config['output_settings'] ?? [];
    }
    
    /**
     * Get base output directory
     */
    public function getOutputDir(string $subdirectory = ''): string
    {
        $baseDir = $this->config['output_settings']['base_output_dir'] ?? '_kb';
        $projectRoot = '/home/master/applications/hdgwrzntwa/public_html';
        
        $outputDir = $projectRoot . '/' . $baseDir;
        
        if (!empty($subdirectory)) {
            $outputDir .= '/' . $subdirectory;
        }
        
        return $outputDir;
    }
    
    /**
     * Check if file has high priority based on patterns
     */
    public function isHighPriorityFile(string $filePath): bool
    {
        $priorityPatterns = $this->config['smart_filtering']['priority_patterns'] ?? [];
        $fileName = basename($filePath);
        
        foreach ($priorityPatterns as $pattern) {
            if (fnmatch($pattern, $fileName)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get cache TTL in seconds
     */
    public function getCacheTTL(): int
    {
        $hours = $this->config['performance_settings']['cache_ttl_hours'] ?? 6;
        return $hours * 3600;
    }
    
    /**
     * Get configuration value by path (dot notation)
     */
    public function get(string $path, $default = null)
    {
        $keys = explode('.', $path);
        $value = $this->config;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
    
    /**
     * Get statistics about current configuration
     */
    public function getConfigStats(): array
    {
        return [
            'version' => $this->config['version'] ?? 'unknown',
            'last_updated' => $this->config['last_updated'] ?? 'unknown',
            'scan_paths_count' => count($this->getScanPaths()),
            'file_types_configured' => count($this->config['file_type_configs'] ?? []),
            'global_exclusions_count' => count($this->getGlobalExclusions()['directories'] ?? []),
            'config_file' => $this->configPath,
            'config_size_kb' => round(filesize($this->configPath) / 1024, 2)
        ];
    }
    
    /**
     * Validate configuration
     */
    public function validateConfig(): array
    {
        $errors = [];
        $warnings = [];
        
        // Check required sections
        $requiredSections = ['scan_paths', 'global_exclusions', 'file_type_configs'];
        foreach ($requiredSections as $section) {
            if (!isset($this->config[$section])) {
                $errors[] = "Missing required section: {$section}";
            }
        }
        
        // Check scan paths exist
        foreach ($this->getScanPaths() as $path) {
            if (!is_dir($path)) {
                $warnings[] = "Scan path does not exist: {$path}";
            }
        }
        
        // Check file type configurations
        $fileTypes = $this->config['file_type_configs'] ?? [];
        foreach ($fileTypes as $type => $config) {
            if (!isset($config['extensions'])) {
                $errors[] = "File type '{$type}' missing extensions";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Log configuration usage
     */
    public function logUsage(string $scanner, string $operation): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'scanner' => $scanner,
            'operation' => $operation,
            'config_version' => $this->config['version'] ?? 'unknown'
        ];
        
        $logFile = $this->getOutputDir('logs') . '/config_usage.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
    }
}

// Helper function for easy access
function kb_config(): KBConfigManager 
{
    return KBConfigManager::getInstance();
}

// Command line interface for config management
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['validate', 'stats', 'test-file:', 'help']);
    
    if (isset($options['help'])) {
        echo "KB Configuration Manager\n";
        echo "Usage:\n";
        echo "  --validate     Validate configuration\n";
        echo "  --stats        Show configuration statistics\n";
        echo "  --test-file=X  Test if file would be included\n";
        exit(0);
    }
    
    $config = kb_config();
    
    if (isset($options['validate'])) {
        $validation = $config->validateConfig();
        
        echo "Configuration Validation:\n";
        echo "Status: " . ($validation['valid'] ? "✅ VALID" : "❌ INVALID") . "\n\n";
        
        if (!empty($validation['errors'])) {
            echo "Errors:\n";
            foreach ($validation['errors'] as $error) {
                echo "  ❌ {$error}\n";
            }
            echo "\n";
        }
        
        if (!empty($validation['warnings'])) {
            echo "Warnings:\n";
            foreach ($validation['warnings'] as $warning) {
                echo "  ⚠️  {$warning}\n";
            }
            echo "\n";
        }
        
        if ($validation['valid'] && empty($validation['warnings'])) {
            echo "✅ Configuration is perfect!\n";
        }
    }
    
    if (isset($options['stats'])) {
        $stats = $config->getConfigStats();
        
        echo "Configuration Statistics:\n";
        echo str_repeat("=", 40) . "\n";
        
        foreach ($stats as $key => $value) {
            echo sprintf("%-25s: %s\n", ucwords(str_replace('_', ' ', $key)), $value);
        }
    }
    
    if (isset($options['test-file'])) {
        $testFile = $options['test-file'];
        $included = $config->shouldIncludeFile($testFile, 'markdown');
        
        echo "File Test: {$testFile}\n";
        echo "Result: " . ($included ? "✅ INCLUDED" : "❌ EXCLUDED") . "\n";
        
        if ($config->isHighPriorityFile($testFile)) {
            echo "Priority: 🏆 HIGH PRIORITY\n";
        }
    }
}