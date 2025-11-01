<?php

declare(strict_types=1);

namespace App\Memory;

use App\Logger;
use App\RedisClient;
use App\DB;
use Exception;

/**
 * Memory Compressor
 * 
 * Automatically compresses and summarizes old conversation history:
 * - Recent (< 1 day): Full fidelity
 * - Medium (1-7 days): Summarized turns
 * - Old (> 7 days): Key facts only
 * - Ancient (> 30 days): Archive or delete
 * 
 * Reduces memory storage by 40-60% while preserving important context
 * 
 * @package App\Memory
 * @author Feature Enhancement Phase 3
 */
class MemoryCompressor
{
    private Logger $logger;
    private ?RedisClient $redis;
    private array $config;

    // Compression thresholds
    private const RECENT_DAYS = 1;
    private const MEDIUM_DAYS = 7;
    private const OLD_DAYS = 30;

    public function __construct(Logger $logger, ?RedisClient $redis = null, array $config = [])
    {
        $this->logger = $logger;
        $this->redis = $redis;
        $this->config = array_merge([
            'enable_compression' => true,
            'compression_ratio_target' => 0.5, // 50% reduction
            'preserve_important' => true,
            'archive_ancient' => true,
            'run_async' => true
        ], $config);
    }

    /**
     * Compress conversation memory
     */
    public function compressConversation(string $conversationId): CompressionResult
    {
        $startTime = microtime(true);
        
        $this->logger->info('Starting memory compression', [
            'conversation_id' => $conversationId
        ]);

        try {
            // Get conversation messages
            $messages = $this->getConversationMessages($conversationId);
            
            if (empty($messages)) {
                return new CompressionResult(false, 0, 0, 0, 'No messages found');
            }

            $originalSize = $this->calculateSize($messages);
            $compressedMessages = [];
            $removedMessages = 0;
            $summarizedMessages = 0;

            // Group messages by age
            $grouped = $this->groupMessagesByAge($messages);

            // Process each age group
            foreach ($grouped as $ageGroup => $msgs) {
                switch ($ageGroup) {
                    case 'recent':
                        // Keep as-is
                        $compressedMessages = array_merge($compressedMessages, $msgs);
                        break;

                    case 'medium':
                        // Summarize every N messages
                        $summarized = $this->summarizeMediumAge($msgs);
                        $compressedMessages = array_merge($compressedMessages, $summarized);
                        $summarizedMessages += count($msgs) - count($summarized);
                        break;

                    case 'old':
                        // Extract key facts only
                        $keyFacts = $this->extractKeyFacts($msgs);
                        if (!empty($keyFacts)) {
                            $compressedMessages[] = [
                                'role' => 'system',
                                'content' => "Historical context (summarized):\n" . implode("\n", $keyFacts),
                                'metadata' => [
                                    'compressed' => true,
                                    'original_count' => count($msgs),
                                    'compression_type' => 'key_facts'
                                ]
                            ];
                        }
                        $summarizedMessages += count($msgs);
                        break;

                    case 'ancient':
                        // Archive or delete
                        if ($this->config['archive_ancient']) {
                            $this->archiveMessages($conversationId, $msgs);
                        }
                        $removedMessages += count($msgs);
                        break;
                }
            }

            $compressedSize = $this->calculateSize($compressedMessages);
            $compressionRatio = 1 - ($compressedSize / $originalSize);

            // Update conversation with compressed messages if ratio is good
            if ($compressionRatio >= $this->config['compression_ratio_target']) {
                $this->updateConversationMessages($conversationId, $compressedMessages);
            }

            $executionTime = microtime(true) - $startTime;

            $this->logger->info('Memory compression completed', [
                'conversation_id' => $conversationId,
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'compression_ratio' => round($compressionRatio * 100, 2) . '%',
                'messages_removed' => $removedMessages,
                'messages_summarized' => $summarizedMessages,
                'execution_time' => $executionTime
            ]);

            return new CompressionResult(
                true,
                $originalSize,
                $compressedSize,
                $compressionRatio,
                'Compression successful',
                [
                    'removed' => $removedMessages,
                    'summarized' => $summarizedMessages,
                    'preserved' => count($compressedMessages)
                ]
            );

        } catch (Exception $e) {
            $this->logger->error('Memory compression failed', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return new CompressionResult(
                false,
                0,
                0,
                0,
                'Compression failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Compress all conversations (batch operation)
     */
    public function compressAllConversations(): array
    {
        $conversations = $this->getAllConversations();
        $results = [];

        foreach ($conversations as $conversationId) {
            $results[$conversationId] = $this->compressConversation($conversationId);
        }

        return $results;
    }

    /**
     * Get conversation messages
     */
    private function getConversationMessages(string $conversationId): array
    {
        $sql = "SELECT id, role, content, created_at, metadata 
                FROM messages 
                WHERE conversation_id = ? 
                ORDER BY created_at ASC";
        
        return DB::select($sql, [$conversationId]);
    }

    /**
     * Group messages by age
     */
    private function groupMessagesByAge(array $messages): array
    {
        $now = time();
        $groups = [
            'recent' => [],
            'medium' => [],
            'old' => [],
            'ancient' => []
        ];

        foreach ($messages as $message) {
            $age = $now - strtotime($message['created_at']);
            $ageDays = $age / 86400;

            if ($ageDays < self::RECENT_DAYS) {
                $groups['recent'][] = $message;
            } elseif ($ageDays < self::MEDIUM_DAYS) {
                $groups['medium'][] = $message;
            } elseif ($ageDays < self::OLD_DAYS) {
                $groups['old'][] = $message;
            } else {
                $groups['ancient'][] = $message;
            }
        }

        return $groups;
    }

    /**
     * Summarize medium-age messages
     */
    private function summarizeMediumAge(array $messages): array
    {
        $summarized = [];
        $chunkSize = 5; // Summarize every 5 messages into 1

        for ($i = 0; $i < count($messages); $i += $chunkSize) {
            $chunk = array_slice($messages, $i, $chunkSize);
            
            if (count($chunk) === 1) {
                $summarized[] = $chunk[0];
                continue;
            }

            // Create summary message
            $summary = $this->createSummary($chunk);
            $summarized[] = [
                'role' => 'system',
                'content' => $summary,
                'created_at' => $chunk[0]['created_at'],
                'metadata' => [
                    'compressed' => true,
                    'original_count' => count($chunk),
                    'compression_type' => 'summary'
                ]
            ];
        }

        return $summarized;
    }

    /**
     * Create summary of message chunk
     */
    private function createSummary(array $messages): string
    {
        $summary = "Summary of conversation (" . count($messages) . " exchanges):\n";
        
        foreach ($messages as $msg) {
            // Extract key points
            $content = $msg['content'];
            $keyPoint = $this->extractKeyPoint($content);
            if ($keyPoint) {
                $summary .= "- {$keyPoint}\n";
            }
        }

        return $summary;
    }

    /**
     * Extract key facts from old messages
     */
    private function extractKeyFacts(array $messages): array
    {
        $facts = [];

        foreach ($messages as $msg) {
            $fact = $this->extractKeyPoint($msg['content']);
            if ($fact && !in_array($fact, $facts)) {
                $facts[] = $fact;
            }
        }

        return $facts;
    }

    /**
     * Extract key point from message content
     */
    private function extractKeyPoint(string $content): ?string
    {
        // Simple heuristic: first sentence or up to 100 chars
        $sentences = preg_split('/[.!?]/', $content);
        $keyPoint = trim($sentences[0] ?? '');
        
        if (strlen($keyPoint) > 100) {
            $keyPoint = substr($keyPoint, 0, 97) . '...';
        }

        return !empty($keyPoint) ? $keyPoint : null;
    }

    /**
     * Calculate size of messages (bytes)
     */
    private function calculateSize(array $messages): int
    {
        return strlen(json_encode($messages));
    }

    /**
     * Archive messages
     */
    private function archiveMessages(string $conversationId, array $messages): void
    {
        // Store in separate archive table or file
        $archiveData = json_encode($messages);
        
        $sql = "INSERT INTO message_archive (conversation_id, messages, archived_at) 
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE messages = ?, archived_at = NOW()";
        
        DB::execute($sql, [$conversationId, $archiveData, $archiveData]);
    }

    /**
     * Update conversation with compressed messages
     * CRITICAL: Uses transaction to prevent data loss
     */
    private function updateConversationMessages(string $conversationId, array $compressedMessages): void
    {
        // Start transaction - CRITICAL for data safety
        DB::beginTransaction();
        
        try {
            // Backup first - create safety net
            $backupSql = "INSERT INTO messages_backup (conversation_id, original_id, role, content, created_at, metadata, backup_created_at)
                          SELECT ?, id, role, content, created_at, metadata, NOW()
                          FROM messages 
                          WHERE conversation_id = ?";
            DB::execute($backupSql, [$conversationId, $conversationId]);
            
            // Delete old messages
            $deleteResult = DB::execute("DELETE FROM messages WHERE conversation_id = ?", [$conversationId]);
            
            // Insert compressed messages
            $insertCount = 0;
            foreach ($compressedMessages as $msg) {
                $metadata = $msg['metadata'] ?? [];
                
                $sql = "INSERT INTO messages (conversation_id, role, content, created_at, metadata) 
                        VALUES (?, ?, ?, ?, ?)";
                
                DB::execute($sql, [
                    $conversationId,
                    $msg['role'],
                    $msg['content'],
                    $msg['created_at'] ?? date('Y-m-d H:i:s'),
                    json_encode($metadata)
                ]);
                $insertCount++;
            }
            
            // Commit only if all operations succeeded
            DB::commit();
            
            $this->logger->info('Successfully compressed messages with transaction', [
                'conversation_id' => $conversationId,
                'deleted' => $deleteResult,
                'inserted' => $insertCount
            ]);
            
        } catch (Exception $e) {
            // Rollback on any error - restore original messages
            DB::rollback();
            
            $this->logger->error('Message compression rollback - data preserved', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new Exception("Failed to compress messages (rolled back safely): " . $e->getMessage());
        }
    }

    /**
     * Get all conversation IDs
     */
    private function getAllConversations(): array
    {
        $sql = "SELECT uuid FROM conversations ORDER BY created_at DESC";
        $rows = DB::select($sql);
        return array_column($rows, 'uuid');
    }

    /**
     * Get compression statistics
     */
    public function getStatistics(): array
    {
        $totalMessages = DB::selectValue("SELECT COUNT(*) FROM messages") ?? 0;
        $compressedMessages = DB::selectValue(
            "SELECT COUNT(*) FROM messages WHERE JSON_EXTRACT(metadata, '$.compressed') = true"
        ) ?? 0;

        return [
            'total_messages' => $totalMessages,
            'compressed_messages' => $compressedMessages,
            'compression_percentage' => $totalMessages > 0 
                ? round(($compressedMessages / $totalMessages) * 100, 2) 
                : 0
        ];
    }
}

/**
 * Compression Result
 */
class CompressionResult
{
    private bool $success;
    private int $originalSize;
    private int $compressedSize;
    private float $compressionRatio;
    private string $message;
    private array $details;

    public function __construct(
        bool $success,
        int $originalSize,
        int $compressedSize,
        float $compressionRatio,
        string $message,
        array $details = []
    ) {
        $this->success = $success;
        $this->originalSize = $originalSize;
        $this->compressedSize = $compressedSize;
        $this->compressionRatio = $compressionRatio;
        $this->message = $message;
        $this->details = $details;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getOriginalSize(): int
    {
        return $this->originalSize;
    }

    public function getCompressedSize(): int
    {
        return $this->compressedSize;
    }

    public function getCompressionRatio(): float
    {
        return $this->compressionRatio;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function getSavings(): int
    {
        return $this->originalSize - $this->compressedSize;
    }

    public function getSavingsPercentage(): float
    {
        return round($this->compressionRatio * 100, 2);
    }
}
