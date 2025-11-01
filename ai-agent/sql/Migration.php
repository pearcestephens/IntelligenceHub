<?php
declare(strict_types=1);

namespace App\Database;

use App\DB;
use App\Logger;
use PDO;
use PDOException;

/**
 * Forward-Only Migration System for AI Agent Database
 * 
 * Features:
 * - Idempotent migrations (safe to run multiple times)
 * - Version tracking in database
 * - Rollback safety guards
 * - MySQL/MariaDB compatibility
 * - Dry-run capability
 * - Transaction safety
 */
class Migration
{
    private PDO $pdo;
    private Logger $logger;
    private bool $dryRun;
    
    private const MIGRATION_TABLE = 'migration_history';
    private const LOCK_TIMEOUT = 300; // 5 minutes
    
    public function __construct(bool $dryRun = false)
    {
        $this->pdo = DB::connection();
        $this->logger = new Logger();
        $this->dryRun = $dryRun;
        
        $this->initializeMigrationTable();
    }
    
    /**
     * Run all pending migrations
     */
    public function run(): array
    {
        $results = [];
        
        try {
            // Acquire migration lock
            $this->acquireLock();
            
            // Get list of migrations to run
            $migrations = $this->getPendingMigrations();
            
            if (empty($migrations)) {
                $this->logger->info("No pending migrations to run");
                return ['status' => 'success', 'migrations' => [], 'message' => 'No pending migrations'];
            }
            
            $this->logger->info("Running " . count($migrations) . " migration(s)", [
                'dry_run' => $this->dryRun,
                'migrations' => array_keys($migrations)
            ]);
            
            // Run each migration
            foreach ($migrations as $version => $migration) {
                $result = $this->runSingleMigration($version, $migration);
                $results[$version] = $result;
                
                if (!$result['success']) {
                    throw new \RuntimeException("Migration $version failed: " . $result['error']);
                }
            }
            
            return [
                'status' => 'success',
                'migrations' => $results,
                'message' => count($migrations) . ' migration(s) completed successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error("Migration failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'migrations' => $results
            ];
        } finally {
            $this->releaseLock();
        }
    }
    
    /**
     * Get migration status
     */
    public function getStatus(): array
    {
        $applied = $this->getAppliedMigrations();
        $available = $this->getAvailableMigrations();
        $pending = array_diff_key($available, $applied);
        
        return [
            'applied' => count($applied),
            'pending' => count($pending),
            'total' => count($available),
            'applied_migrations' => array_keys($applied),
            'pending_migrations' => array_keys($pending)
        ];
    }
    
    /**
     * Initialize migration tracking table
     */
    private function initializeMigrationTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS " . self::MIGRATION_TABLE . " (
                version VARCHAR(50) PRIMARY KEY,
                description VARCHAR(255) NOT NULL,
                applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                execution_time_ms INT NOT NULL DEFAULT 0,
                checksum VARCHAR(64) NOT NULL,
                INDEX idx_applied_at (applied_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Database migration history tracking'
        ";
        
        if (!$this->dryRun) {
            $this->pdo->exec($sql);
        }
    }
    
    /**
     * Acquire migration lock to prevent concurrent migrations
     */
    private function acquireLock(): void
    {
        $lockName = 'migration_lock';
        
        if (!$this->dryRun) {
            $stmt = $this->pdo->prepare("SELECT GET_LOCK(?, ?) as lock_result");
            $stmt->execute([$lockName, self::LOCK_TIMEOUT]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['lock_result'] != 1) {
                throw new \RuntimeException("Could not acquire migration lock. Another migration may be running.");
            }
        }
    }
    
    /**
     * Release migration lock
     */
    private function releaseLock(): void
    {
        $lockName = 'migration_lock';
        
        if (!$this->dryRun) {
            $stmt = $this->pdo->prepare("SELECT RELEASE_LOCK(?) as release_result");
            $stmt->execute([$lockName]);
        }
    }
    
    /**
     * Get migrations that haven't been applied yet
     */
    private function getPendingMigrations(): array
    {
        $available = $this->getAvailableMigrations();
        $applied = $this->getAppliedMigrations();
        
        return array_diff_key($available, $applied);
    }
    
    /**
     * Get available migration files
     */
    private function getAvailableMigrations(): array
    {
        $migrationsDir = __DIR__ . '/migrations';
        $migrations = [];
        
        if (!is_dir($migrationsDir)) {
            return [];
        }
        
        $files = glob($migrationsDir . '/*.sql');
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/^(\d{8}_\d{6})_(.+)\.sql$/', $filename, $matches)) {
                $version = $matches[1];
                $description = str_replace('_', ' ', $matches[2]);
                
                $migrations[$version] = [
                    'file' => $file,
                    'description' => $description,
                    'checksum' => md5_file($file)
                ];
            }
        }
        
        ksort($migrations);
        return $migrations;
    }
    
    /**
     * Get already applied migrations
     */
    private function getAppliedMigrations(): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT version, description, applied_at, execution_time_ms, checksum
                FROM " . self::MIGRATION_TABLE . "
                ORDER BY version
            ");
            $stmt->execute();
            
            $migrations = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $migrations[$row['version']] = $row;
            }
            
            return $migrations;
        } catch (PDOException $e) {
            // Migration table doesn't exist yet
            return [];
        }
    }
    
    /**
     * Run a single migration
     */
    private function runSingleMigration(string $version, array $migration): array
    {
        $startTime = microtime(true);
        
        try {
            $this->logger->info("Running migration: $version - {$migration['description']}", [
                'dry_run' => $this->dryRun
            ]);
            
            // Read migration content
            $sql = file_get_contents($migration['file']);
            if ($sql === false) {
                throw new \RuntimeException("Could not read migration file: {$migration['file']}");
            }
            
            // Split into individual statements
            $statements = $this->splitSqlStatements($sql);
            
            if ($this->dryRun) {
                $this->logger->info("DRY RUN: Would execute " . count($statements) . " SQL statements");
                return [
                    'success' => true,
                    'statements' => count($statements),
                    'execution_time_ms' => 0
                ];
            }
            
            // Begin transaction
            $this->pdo->beginTransaction();
            
            try {
                // Execute each statement
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $this->pdo->exec($statement);
                    }
                }
                
                // Record migration as applied
                $executionTime = (int)((microtime(true) - $startTime) * 1000);
                $this->recordMigration($version, $migration['description'], $migration['checksum'], $executionTime);
                
                // Commit transaction
                $this->pdo->commit();
                
                $this->logger->info("Migration completed: $version", [
                    'execution_time_ms' => $executionTime
                ]);
                
                return [
                    'success' => true,
                    'statements' => count($statements),
                    'execution_time_ms' => $executionTime
                ];
                
            } catch (\Exception $e) {
                $this->pdo->rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            
            $this->logger->error("Migration failed: $version", [
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime
            ];
        }
    }
    
    /**
     * Record migration as applied
     */
    private function recordMigration(string $version, string $description, string $checksum, int $executionTime): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO " . self::MIGRATION_TABLE . " 
            (version, description, checksum, execution_time_ms) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$version, $description, $checksum, $executionTime]);
    }
    
    /**
     * Split SQL into individual statements
     */
    private function splitSqlStatements(string $sql): array
    {
        // Remove comments and split by semicolon
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        $statements = explode(';', $sql);
        
        return array_filter(array_map('trim', $statements));
    }
    
    /**
     * Validate migration checksum
     */
    public function validateMigration(string $version): bool
    {
        $available = $this->getAvailableMigrations();
        $applied = $this->getAppliedMigrations();
        
        if (!isset($available[$version]) || !isset($applied[$version])) {
            return false;
        }
        
        return $available[$version]['checksum'] === $applied[$version]['checksum'];
    }
}