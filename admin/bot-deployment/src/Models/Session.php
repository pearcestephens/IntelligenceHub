<?php
/**
 * Multi-Thread Session Model
 *
 * Represents a multi-threaded conversation session
 *
 * @package BotDeployment\Models
 */

namespace BotDeployment\Models;

use DateTime;
use InvalidArgumentException;

class Session
{
    /**
     * Session ID
     * @var string
     */
    private $session_id;

    /**
     * Topic
     * @var string
     */
    private $topic;

    /**
     * Thread count
     * @var int
     */
    private $thread_count;

    /**
     * Status
     * @var string
     */
    private $status;

    /**
     * Metadata
     * @var array
     */
    private $metadata;

    /**
     * Started at
     * @var DateTime
     */
    private $started_at;

    /**
     * Completed at
     * @var DateTime|null
     */
    private $completed_at;

    /**
     * Threads
     * @var array
     */
    private $threads = [];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ABANDONED = 'abandoned';

    /**
     * Constructor
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Hydrate from array
     * @param array $data
     * @return self
     */
    public function hydrate(array $data): self
    {
        if (isset($data['session_id'])) {
            $this->session_id = $data['session_id'];
        }

        if (isset($data['topic'])) {
            $this->setTopic($data['topic']);
        }

        if (isset($data['thread_count'])) {
            $this->thread_count = (int) $data['thread_count'];
        }

        if (isset($data['status'])) {
            $this->setStatus($data['status']);
        }

        if (isset($data['metadata'])) {
            $metadata = is_string($data['metadata'])
                ? json_decode($data['metadata'], true)
                : $data['metadata'];
            $this->metadata = $metadata ?: [];
        }

        if (isset($data['started_at'])) {
            $this->started_at = $data['started_at'] instanceof DateTime
                ? $data['started_at']
                : new DateTime($data['started_at']);
        }

        if (isset($data['completed_at']) && $data['completed_at']) {
            $this->completed_at = $data['completed_at'] instanceof DateTime
                ? $data['completed_at']
                : new DateTime($data['completed_at']);
        }

        return $this;
    }

    /**
     * Convert to array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'session_id' => $this->session_id,
            'topic' => $this->topic,
            'thread_count' => $this->thread_count,
            'status' => $this->status,
            'metadata' => json_encode($this->metadata),
            'started_at' => $this->started_at ? $this->started_at->format('Y-m-d H:i:s') : null,
            'completed_at' => $this->completed_at ? $this->completed_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Validate session
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate(): bool
    {
        if (empty($this->topic)) {
            throw new InvalidArgumentException('Session topic is required');
        }

        if ($this->thread_count < 2 || $this->thread_count > 6) {
            throw new InvalidArgumentException('Thread count must be between 2 and 6');
        }

        if (!in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_COMPLETED, self::STATUS_ABANDONED])) {
            throw new InvalidArgumentException('Invalid status: ' . $this->status);
        }

        return true;
    }

    /**
     * Generate session ID
     * @return string
     */
    public static function generateId(): string
    {
        return 'session_' . uniqid() . '_' . bin2hex(random_bytes(4));
    }

    // Getters and Setters

    public function getSessionId(): string
    {
        return $this->session_id ?? self::generateId();
    }

    public function setSessionId(string $session_id): self
    {
        $this->session_id = $session_id;
        return $this;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = trim($topic);
        return $this;
    }

    public function getThreadCount(): int
    {
        return $this->thread_count ?? 4;
    }

    public function setThreadCount(int $thread_count): self
    {
        $this->thread_count = $thread_count;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status ?? self::STATUS_ACTIVE;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata ?? [];
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getMeta(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    public function setMeta(string $key, $value): self
    {
        if ($this->metadata === null) {
            $this->metadata = [];
        }
        $this->metadata[$key] = $value;
        return $this;
    }

    public function getStartedAt(): ?DateTime
    {
        return $this->started_at;
    }

    public function setStartedAt(DateTime $started_at): self
    {
        $this->started_at = $started_at;
        return $this;
    }

    public function getCompletedAt(): ?DateTime
    {
        return $this->completed_at;
    }

    public function setCompletedAt(?DateTime $completed_at): self
    {
        $this->completed_at = $completed_at;
        return $this;
    }

    public function getThreads(): array
    {
        return $this->threads;
    }

    public function setThreads(array $threads): self
    {
        $this->threads = $threads;
        return $this;
    }

    public function addThread($thread): self
    {
        $this->threads[] = $thread;
        return $this;
    }

    /**
     * Check if session is active
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if session is completed
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if session is abandoned
     * @return bool
     */
    public function isAbandoned(): bool
    {
        return $this->status === self::STATUS_ABANDONED;
    }

    /**
     * Get session duration in seconds
     * @return int|null
     */
    public function getDuration(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->completed_at ?? new DateTime();
        return $end->getTimestamp() - $this->started_at->getTimestamp();
    }
}
