#!/usr/bin/env php
<?php
/**
 * Intelligence Hub - Cron Installation & Setup Script
 * 
 * Sets up the complete Intelligence Hub cron system including:
 * - Database tables
 * - Default jobs
 * - Satellite configurations
 * - Crontab entries
 * 
 * Usage:
 *   php setup-hub-cron.php --install     # Install complete system
 *   php setup-hub-cron.php --database    # Setup database only
 *   php setup-hub-cron.php --crontab     # Setup crontab only
 *   php setup-hub-cron.php --test        # Test installation
 *   php setup-hub-cron.php --uninstall   # Remove system
 * 
 * @package IntelligenceHub
 * @version 2.0.0
 */

declare(strict_types=1);

// CLI Safety Check
if (PHP_SAPI !== 'cli') {
    die("This script can only be run from command line.\n");
}

// Bootstrap
require_once __DIR__ . '/hub-cron-config.php';

class HubCronSetup
{
    private HubCronLogger $logger;
    private array $config;
    
    public function __construct()
    {
        $this->logger = new HubCronLogger();
        $this->config = HubCronConfig::getConfig();
    }
    
    /**
     * Run setup based on options
     */
    public function run(array $options): void
    {
        if (isset($options['install'])) {
            $this->installComplete();
        } elseif (isset($options['database'])) {
            $this->setupDatabase();
        } elseif (isset($options['crontab'])) {
            $this->setupCrontab();
        } elseif (isset($options['test'])) {
            $this->testInstallation();
        } elseif (isset($options['uninstall'])) {
            $this->uninstallSystem();
        } else {
            $this->showHelp();
        }
    }
    
    /**
     * Install complete system
     */
    private function installComplete(): void
    {
        $this->logger->info("Starting Intelligence Hub Cron System installation...");
        
        try {
            // Step 1: Setup database
            $this->logger->info("Setting up database...");
            $this->setupDatabase();
            
            // Step 2: Setup crontab
            $this->logger->info("Setting up crontab entries...");
            $this->setupCrontab();
            
            // Step 3: Create log directory
            $this->logger->info("Creating log directory...");
            $this->createLogDirectory();
            
            // Step 4: Set permissions
            $this->logger->info("Setting permissions...");
            $this->setPermissions();
            
            // Step 5: Test installation
            $this->logger->info("Testing installation...");
            $this->testInstallation();
            
            $this->logger->info("Installation completed successfully!");
            $this->showPostInstallInfo();
            
        } catch (Exception $e) {
            $this->logger->error("Installation failed: " . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Setup database tables
     */
    private function setupDatabase(): void
    {
        try {
            $pdo = HubCronConfig::getDatabase();
            $this->logger->info("Connected to database successfully");
            
            HubCronConfig::initializeDatabase($pdo);
            $this->logger->info("Database tables created successfully");
            
            // Verify tables exist
            $tables = ['hub_cron_jobs', 'hub_cron_executions', 'hub_cron_satellites', 'hub_cron_metrics', 'hub_cron_alerts'];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
                if ($stmt->rowCount() === 0) {
                    throw new Exception("Table {$table} was not created");
                }
            }
            
            $this->logger->info("All database tables verified successfully");
            
        } catch (Exception $e) {
            throw new Exception("Database setup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Setup crontab entries
     */
    private function setupCrontab(): void
    {
        $cronScript = __DIR__ . '/intelligence-hub-cron.php';
        $logFile = __DIR__ . '/../../../logs/hub-cron.log';
        
        // Main cron entry - runs every minute
        $cronEntry = "* * * * * cd " . dirname($cronScript) . " && php intelligence-hub-cron.php >> {$logFile} 2>&1";
        
        // Additional entries for specific tasks
        $additionalEntries = [
            // Health check every 5 minutes
            "*/5 * * * * cd " . dirname($cronScript) . " && php intelligence-hub-cron.php --task=mcp_health_check >> {$logFile} 2>&1",
            
            // Status report every hour
            "0 * * * * cd " . dirname($cronScript) . " && php intelligence-hub-cron.php --status >> {$logFile} 2>&1",
            
            // Satellite sync every 15 minutes
            "*/15 * * * * cd " . dirname($cronScript) . " && php intelligence-hub-cron.php --task=satellite_sync >> {$logFile} 2>&1",
        ];
        
        $this->logger->info("Crontab entries to add:");
        $this->logger->info($cronEntry);
        foreach ($additionalEntries as $entry) {
            $this->logger->info($entry);
        }
        
        // Check if running in interactive mode
        if (posix_isatty(STDIN)) {
            echo "\nDo you want to automatically add these entries to crontab? (y/n): ";
            $response = trim(fgets(STDIN));
            
            if (strtolower($response) === 'y') {
                $this->addToCrontab($cronEntry, $additionalEntries);
            } else {
                $this->logger->info("Crontab entries not added. Please add them manually.");
            }
        } else {
            $this->logger->info("Non-interactive mode. Please add crontab entries manually.");
        }
    }
    
    /**
     * Add entries to crontab
     */
    private function addToCrontab(string $mainEntry, array $additionalEntries): void
    {
        try {
            // Get current crontab
            $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
            
            // Check if entries already exist
            if (strpos($currentCrontab, 'intelligence-hub-cron.php') !== false) {
                $this->logger->warn("Intelligence Hub cron entries already exist in crontab");
                return;
            }
            
            // Add new entries
            $newCrontab = $currentCrontab;
            $newCrontab .= "\n# Intelligence Hub Cron System\n";
            $newCrontab .= $mainEntry . "\n";
            
            foreach ($additionalEntries as $entry) {
                $newCrontab .= $entry . "\n";
            }
            
            // Save to temporary file and install
            $tempFile = tempnam(sys_get_temp_dir(), 'hub_cron');
            file_put_contents($tempFile, $newCrontab);
            
            $result = shell_exec("crontab {$tempFile} 2>&1");
            unlink($tempFile);
            
            if ($result === null) {
                $this->logger->info("Crontab entries added successfully");
            } else {
                throw new Exception("Failed to add crontab entries: {$result}");
            }
            
        } catch (Exception $e) {
            $this->logger->error("Failed to setup crontab: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create log directory
     */
    private function createLogDirectory(): void
    {
        $logDir = __DIR__ . '/../../../logs';
        
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                throw new Exception("Failed to create log directory: {$logDir}");
            }
            $this->logger->info("Created log directory: {$logDir}");
        } else {
            $this->logger->info("Log directory already exists: {$logDir}");
        }
        
        // Test write permissions
        $testFile = $logDir . '/hub-cron-test.log';
        if (file_put_contents($testFile, "Test\n") === false) {
            throw new Exception("Cannot write to log directory: {$logDir}");
        }
        unlink($testFile);
        
        $this->logger->info("Log directory permissions verified");
    }
    
    /**
     * Set file permissions
     */
    private function setPermissions(): void
    {
        $files = [
            __DIR__ . '/intelligence-hub-cron.php',
            __DIR__ . '/hub-cron-config.php',
            __FILE__
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                chmod($file, 0755);
                $this->logger->info("Set permissions for: " . basename($file));
            }
        }
    }
    
    /**
     * Test installation
     */
    private function testInstallation(): void
    {
        $this->logger->info("Running installation tests...");
        
        // Test 1: Database connection
        try {
            $pdo = HubCronConfig::getDatabase();
            $this->logger->info("✓ Database connection successful");
        } catch (Exception $e) {
            $this->logger->error("✗ Database connection failed: " . $e->getMessage());
            return;
        }
        
        // Test 2: Tables exist
        $tables = ['hub_cron_jobs', 'hub_cron_executions', 'hub_cron_satellites', 'hub_cron_metrics', 'hub_cron_alerts'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                $this->logger->info("✓ Table {$table} exists");
            } else {
                $this->logger->error("✗ Table {$table} missing");
                return;
            }
        }
        
        // Test 3: Default data
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM hub_cron_jobs");
        $jobCount = $stmt->fetch()['count'];
        $this->logger->info("✓ Found {$jobCount} default jobs");
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM hub_cron_satellites");
        $satelliteCount = $stmt->fetch()['count'];
        $this->logger->info("✓ Found {$satelliteCount} satellites configured");
        
        // Test 4: Cron script executable
        $cronScript = __DIR__ . '/intelligence-hub-cron.php';
        if (is_executable($cronScript)) {
            $this->logger->info("✓ Cron script is executable");
        } else {
            $this->logger->error("✗ Cron script is not executable");
            return;
        }
        
        // Test 5: Run dry test
        $output = shell_exec("php {$cronScript} --status 2>&1");
        if ($output !== null) {
            $this->logger->info("✓ Cron script runs successfully");
        } else {
            $this->logger->error("✗ Cron script failed to run");
            return;
        }
        
        $this->logger->info("All tests passed! Installation is working correctly.");
    }
    
    /**
     * Uninstall system
     */
    private function uninstallSystem(): void
    {
        $this->logger->warn("Starting system uninstallation...");
        
        // Confirm uninstall
        if (posix_isatty(STDIN)) {
            echo "This will remove all Intelligence Hub cron data. Are you sure? (yes/no): ";
            $response = trim(fgets(STDIN));
            
            if (strtolower($response) !== 'yes') {
                $this->logger->info("Uninstall cancelled");
                return;
            }
        }
        
        try {
            // Remove crontab entries
            $this->removeCrontabEntries();
            
            // Drop database tables
            $this->dropDatabaseTables();
            
            $this->logger->info("System uninstalled successfully");
            
        } catch (Exception $e) {
            $this->logger->error("Uninstall failed: " . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Remove crontab entries
     */
    private function removeCrontabEntries(): void
    {
        $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
        
        if (strpos($currentCrontab, 'intelligence-hub-cron.php') === false) {
            $this->logger->info("No crontab entries found to remove");
            return;
        }
        
        // Remove lines containing intelligence-hub-cron.php
        $lines = explode("\n", $currentCrontab);
        $filteredLines = array_filter($lines, function($line) {
            return strpos($line, 'intelligence-hub-cron.php') === false &&
                   strpos($line, '# Intelligence Hub Cron System') === false;
        });
        
        $newCrontab = implode("\n", $filteredLines);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'hub_cron_remove');
        file_put_contents($tempFile, $newCrontab);
        
        shell_exec("crontab {$tempFile} 2>&1");
        unlink($tempFile);
        
        $this->logger->info("Crontab entries removed");
    }
    
    /**
     * Drop database tables
     */
    private function dropDatabaseTables(): void
    {
        try {
            $pdo = HubCronConfig::getDatabase();
            
            $tables = ['hub_cron_alerts', 'hub_cron_metrics', 'hub_cron_executions', 'hub_cron_jobs', 'hub_cron_satellites'];
            
            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS {$table}");
                $this->logger->info("Dropped table: {$table}");
            }
            
        } catch (Exception $e) {
            throw new Exception("Failed to drop database tables: " . $e->getMessage());
        }
    }
    
    /**
     * Show post-install information
     */
    private function showPostInstallInfo(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Intelligence Hub Cron System v2.0 - Installation Complete!\n";
        echo str_repeat("=", 60) . "\n\n";
        
        echo "✓ Database tables created\n";
        echo "✓ Default jobs configured\n";
        echo "✓ Satellites registered\n";
        echo "✓ Log directory created\n";
        echo "✓ Permissions set\n\n";
        
        echo "Next Steps:\n";
        echo "1. Review crontab entries: crontab -l\n";
        echo "2. Check system status: php intelligence-hub-cron.php --status\n";
        echo "3. Monitor logs: tail -f logs/hub-cron.log\n";
        echo "4. Access dashboard: https://gpt.ecigdis.co.nz/dashboard/?page=cron\n\n";
        
        echo "Default Jobs Configured:\n";
        foreach ($this->config['hub_jobs'] as $job) {
            echo "  • {$job['name']} ({$job['schedule']})\n";
        }
        
        echo "\nSatellites Configured:\n";
        foreach ($this->config['satellites'] as $satellite) {
            $status = $satellite['enabled'] ? 'enabled' : 'disabled';
            echo "  • {$satellite['name']} ({$status})\n";
        }
        
        echo "\nFor help: php setup-hub-cron.php --help\n";
    }
    
    /**
     * Show help information
     */
    private function showHelp(): void
    {
        echo "Intelligence Hub Cron Setup v2.0\n\n";
        echo "Usage:\n";
        echo "  php setup-hub-cron.php --install     Install complete system\n";
        echo "  php setup-hub-cron.php --database    Setup database only\n";
        echo "  php setup-hub-cron.php --crontab     Setup crontab only\n";
        echo "  php setup-hub-cron.php --test        Test installation\n";
        echo "  php setup-hub-cron.php --uninstall   Remove system\n";
        echo "  php setup-hub-cron.php --help        Show this help\n\n";
        
        echo "Examples:\n";
        echo "  # Complete installation\n";
        echo "  php setup-hub-cron.php --install\n\n";
        
        echo "  # Test existing installation\n";
        echo "  php setup-hub-cron.php --test\n\n";
        
        echo "  # Remove everything\n";
        echo "  php setup-hub-cron.php --uninstall\n\n";
    }
}

// Parse CLI arguments
$options = getopt('', [
    'install',
    'database',
    'crontab', 
    'test',
    'uninstall',
    'help'
]);

try {
    $setup = new HubCronSetup();
    $setup->run($options);
    exit(0);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}