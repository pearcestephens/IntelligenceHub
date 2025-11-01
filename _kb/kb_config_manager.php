<?php
/**
 * CIS Knowledge Base - Centralized Configuration Manager
 * 
 * Provides consistent ignore lists and configuration across all KB scanners
 * Single source of truth for what should be included/excluded
 * 
 * @package CIS\KB\Config
 * @version 1.0.0
 */

declare(strict_types=1);

class KBConfigManager
{
    private static ?array $config = null;
    private static string $configPath = '/home/master/applications/hdgwrzntwa/public_html/_kb/kb_ignore_config.json';
    
    /**
     * Load configuration from central config file
     */
    public static function loadConfig(): array
    {
        if (self::$config === null) {
            if (!file_exists(self::$configPath)) {
                throw new Exception("KB config file not found: " . self::$configPath);
            }
            
            $configContent = file_get_contents(self::$configPath);
            self::$config = json_decode($configContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON in KB config file: " . json_last_error_msg());
            }
        }
        
        return self::$config;
    }
    
    /**
     * Get ignore patterns for a specific scanner type
     */
    public static function getIgnorePatterns(string $scannerType = 'comprehensive'): array
    {
        $config = self::loadConfig();
        $basePatterns = $config['ignore_patterns'];
        
        // Add scanner-specific patterns if they exist
        if (isset($config['scanner_specific'][$scannerType]['additional_ignores'])) {
            $basePatterns['path_patterns'] = array_merge(
                $basePatterns['path_patterns'],
                $config['scanner_specific'][$scannerType]['additional_ignores']
            );
        }
        
        return $basePatterns;
    }
    
    /**
     * Get scan paths
     */
    public static function getScanPaths(): array
    {
        $config = self::loadConfig();
        return $config['scan_paths'];
    }
    
    /**
     * Get file extensions for a scanner type
     */
    public static function getFileExtensions(string $scannerType): array
    {
        $config = self::loadConfig();
        
        if (isset($config['scanner_specific'][$scannerType]['extensions'])) {
            return $config['scanner_specific'][$scannerType]['extensions'];
        }
        
        // Default to all files if not specified
        return ['*'];
    }
    
    /**
     * Check if a file should be ignored based on centralized rules
     */
    public static function shouldIgnoreFile(string $filePath, string $scannerType = 'comprehensive'): bool
    {
        $patterns = self::getIgnorePatterns($scannerType);
        $config = self::loadConfig();
        
        // Check if file is not readable
        if (!is_readable($filePath)) {
            return true;
        }
        
        // Check file size limits
        $fileSize = filesize($filePath);
        $maxSizeMB = $config['file_size_limits']['max_file_size_mb'];
        if ($fileSize > ($maxSizeMB * 1024 * 1024)) {
            return true;
        }
        
        // Skip empty files if configured
        if ($config['file_size_limits']['skip_empty_files'] && $fileSize === 0) {
            return true;
        }
        
        // Check priority inclusion patterns first (these override ignores)
        foreach ($config['priority_inclusion']['patterns'] as $pattern) {
            if (fnmatch($pattern, $filePath)) {
                return false; // Don't ignore priority files
            }
        }
        
        // Check directory patterns
        foreach ($patterns['directories'] as $dir) {
            if (strpos($filePath, "/{$dir}/") !== false || strpos($filePath, "/{$dir}") !== false) {
                return true;
            }
        }
        
        // Check path patterns
        foreach ($patterns['path_patterns'] as $pathPattern) {
            if (fnmatch($pathPattern, $filePath)) {
                return true;
            }
        }
        
        // Check file extensions
        $fileExt = '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        if (in_array($fileExt, $patterns['file_extensions'])) {
            return true;
        }
        
        // Check specific files
        $fileName = basename($filePath);
        if (in_array($fileName, $patterns['specific_files'])) {
            return true;
        }
        
        return false; // Don't ignore by default
    }
    
    /**
     * Get statistics about what would be ignored vs included
     */
    public static function analyzeIgnoreImpact(array $allFiles, string $scannerType = 'comprehensive'): array
    {
        $stats = [
            'total_files' => count($allFiles),
            'ignored_files' => 0,
            'included_files' => 0,
            'ignored_by_reason' => [
                'directory' => 0,
                'path_pattern' => 0,
                'file_extension' => 0,
                'specific_file' => 0,
                'file_size' => 0,
                'not_readable' => 0
            ],
            'priority_included' => 0
        ];
        
        $config = self::loadConfig();
        $patterns = self::getIgnorePatterns($scannerType);
        
        foreach ($allFiles as $file) {
            if (self::shouldIgnoreFile($file, $scannerType)) {
                $stats['ignored_files']++;
                
                // Determine reason for ignoring
                if (!is_readable($file)) {
                    $stats['ignored_by_reason']['not_readable']++;
                } elseif (filesize($file) > ($config['file_size_limits']['max_file_size_mb'] * 1024 * 1024)) {
                    $stats['ignored_by_reason']['file_size']++;
                } else {
                    // Check other reasons
                    foreach ($patterns['directories'] as $dir) {
                        if (strpos($file, "/{$dir}/") !== false) {
                            $stats['ignored_by_reason']['directory']++;
                            break;
                        }
                    }
                }
            } else {
                $stats['included_files']++;
                
                // Check if it's priority included
                foreach ($config['priority_inclusion']['patterns'] as $pattern) {
                    if (fnmatch($pattern, $file)) {
                        $stats['priority_included']++;
                        break;
                    }
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Update configuration (for management scripts)
     */
    public static function updateConfig(array $newConfig): bool
    {
        $jsonContent = json_encode($newConfig, JSON_PRETTY_PRINT);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid configuration data: " . json_last_error_msg());
        }
        
        // Backup current config
        if (file_exists(self::$configPath)) {
            $backupPath = self::$configPath . '.backup.' . date('Y-m-d_H-i-s');
            copy(self::$configPath, $backupPath);
        }
        
        $result = file_put_contents(self::$configPath, $jsonContent);
        
        if ($result !== false) {
            self::$config = null; // Force reload
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate ignore statistics report
     */
    public static function generateIgnoreReport(): string
    {
        $config = self::loadConfig();
        
        $report = "# CIS KB Ignore Configuration Report\n\n";
        $report .= "**Generated:** " . date('Y-m-d H:i:s') . "\n";
        $report .= "**Config Version:** " . $config['version'] . "\n";
        $report .= "**Last Updated:** " . $config['last_updated'] . "\n\n";
        
        $report .= "## Ignore Patterns Summary\n\n";
        $report .= "**Directories:** " . count($config['ignore_patterns']['directories']) . "\n";
        $report .= "**File Extensions:** " . count($config['ignore_patterns']['file_extensions']) . "\n";
        $report .= "**Specific Files:** " . count($config['ignore_patterns']['specific_files']) . "\n";
        $report .= "**Path Patterns:** " . count($config['ignore_patterns']['path_patterns']) . "\n\n";
        
        $report .= "## Ignored Directories\n\n";
        foreach ($config['ignore_patterns']['directories'] as $dir) {
            $report .= "- `{$dir}`\n";
        }
        
        $report .= "\n## Ignored File Extensions\n\n";
        foreach ($config['ignore_patterns']['file_extensions'] as $ext) {
            $report .= "- `{$ext}`\n";
        }
        
        $report .= "\n## Priority Inclusion Patterns\n\n";
        foreach ($config['priority_inclusion']['patterns'] as $pattern) {
            $report .= "- `{$pattern}`\n";
        }
        
        $report .= "\n## Scanner-Specific Settings\n\n";
        foreach ($config['scanner_specific'] as $scanner => $settings) {
            $report .= "### {$scanner}\n";
            $report .= "**Extensions:** " . implode(', ', $settings['extensions']) . "\n";
            if (!empty($settings['additional_ignores'])) {
                $report .= "**Additional Ignores:** " . count($settings['additional_ignores']) . "\n";
            }
            $report .= "\n";
        }
        
        return $report;
    }
}

// Command line interface for testing
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['test', 'report', 'analyze', 'help']);
    
    if (isset($options['help'])) {
        echo "KB Config Manager\n";
        echo "Usage: php kb_config_manager.php [OPTIONS]\n";
        echo "  --test      Test configuration loading\n";
        echo "  --report    Generate ignore report\n";
        echo "  --analyze   Analyze ignore impact on current files\n";
        exit(0);
    }
    
    if (isset($options['test'])) {
        try {
            $config = KBConfigManager::loadConfig();
            echo "✅ Configuration loaded successfully\n";
            echo "Scan paths: " . count(KBConfigManager::getScanPaths()) . "\n";
            echo "Ignore directories: " . count($config['ignore_patterns']['directories']) . "\n";
            echo "Path patterns: " . count($config['ignore_patterns']['path_patterns']) . "\n";
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    if (isset($options['report'])) {
        echo KBConfigManager::generateIgnoreReport();
    }
    
    if (isset($options['analyze'])) {
        echo "Analyzing ignore impact...\n";
        $allFiles = [];
        
        foreach (KBConfigManager::getScanPaths() as $path) {
            if (is_dir($path)) {
                $cmd = "find '{$path}' -type f 2>/dev/null";
                $output = shell_exec($cmd);
                if ($output) {
                    $files = array_filter(explode("\n", trim($output)));
                    $allFiles = array_merge($allFiles, $files);
                }
            }
        }
        
        $stats = KBConfigManager::analyzeIgnoreImpact($allFiles);
        
        echo "\n📊 Ignore Impact Analysis\n";
        echo "========================\n";
        echo "Total files found: " . number_format($stats['total_files']) . "\n";
        echo "Files to include: " . number_format($stats['included_files']) . "\n";
        echo "Files to ignore: " . number_format($stats['ignored_files']) . "\n";
        echo "Ignore percentage: " . round(($stats['ignored_files'] / $stats['total_files']) * 100, 1) . "%\n";
        echo "Priority included: " . number_format($stats['priority_included']) . "\n";
        
        echo "\nIgnore reasons:\n";
        foreach ($stats['ignored_by_reason'] as $reason => $count) {
            if ($count > 0) {
                echo "  {$reason}: " . number_format($count) . "\n";
            }
        }
    }
}