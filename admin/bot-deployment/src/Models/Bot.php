<?php
/**
 * Bot Model
 *
 * Represents a deployable bot with validation and business logic
 *
 * @package BotDeployment\Models
 */

namespace BotDeployment\Models;

use DateTime;
use InvalidArgumentException;

class Bot
{
    /**
     * Bot ID
     * @var int|null
     */
    private $bot_id;

    /**
     * Bot name
     * @var string
     */
    private $bot_name;

    /**
     * Bot role
     * @var string
     */
    private $bot_role;

    /**
     * System prompt
     * @var string
     */
    private $system_prompt;

    /**
     * Schedule cron expression
     * @var string|null
     */
    private $schedule_cron;

    /**
     * Status
     * @var string
     */
    private $status;

    /**
     * Configuration JSON
     * @var array
     */
    private $config_json;

    /**
     * Last execution timestamp
     * @var DateTime|null
     */
    private $last_execution;

    /**
     * Next execution timestamp
     * @var DateTime|null
     */
    private $next_execution;

    /**
     * Created at timestamp
     * @var DateTime
     */
    private $created_at;

    /**
     * Updated at timestamp
     * @var DateTime
     */
    private $updated_at;

    /**
     * Valid bot roles
     */
    const VALID_ROLES = [
        'architect', 'security', 'api', 'frontend', 'database',
        'devops', 'qa', 'documentation', 'monitoring', 'general'
    ];

    /**
     * Valid statuses
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_ARCHIVED = 'archived';

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
        if (isset($data['bot_id'])) {
            $this->bot_id = (int) $data['bot_id'];
        }

        if (isset($data['bot_name'])) {
            $this->setBotName($data['bot_name']);
        }

        if (isset($data['bot_role'])) {
            $this->setBotRole($data['bot_role']);
        }

        if (isset($data['system_prompt'])) {
            $this->setSystemPrompt($data['system_prompt']);
        }

        if (isset($data['schedule_cron'])) {
            $this->schedule_cron = $data['schedule_cron'];
        }

        if (isset($data['status'])) {
            $this->setStatus($data['status']);
        }

        if (isset($data['config_json'])) {
            $config = is_string($data['config_json'])
                ? json_decode($data['config_json'], true)
                : $data['config_json'];
            $this->config_json = $config ?: [];
        }

        if (isset($data['last_execution'])) {
            $this->last_execution = $data['last_execution'] instanceof DateTime
                ? $data['last_execution']
                : new DateTime($data['last_execution']);
        }

        if (isset($data['next_execution'])) {
            $this->next_execution = $data['next_execution'] instanceof DateTime
                ? $data['next_execution']
                : new DateTime($data['next_execution']);
        }

        if (isset($data['created_at'])) {
            $this->created_at = $data['created_at'] instanceof DateTime
                ? $data['created_at']
                : new DateTime($data['created_at']);
        }

        if (isset($data['updated_at'])) {
            $this->updated_at = $data['updated_at'] instanceof DateTime
                ? $data['updated_at']
                : new DateTime($data['updated_at']);
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
            'bot_id' => $this->bot_id,
            'bot_name' => $this->bot_name,
            'bot_role' => $this->bot_role,
            'system_prompt' => $this->system_prompt,
            'schedule_cron' => $this->schedule_cron,
            'status' => $this->status,
            'config_json' => json_encode($this->config_json),
            'last_execution' => $this->last_execution ? $this->last_execution->format('Y-m-d H:i:s') : null,
            'next_execution' => $this->next_execution ? $this->next_execution->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Validate bot data
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate(): bool
    {
        if (empty($this->bot_name)) {
            throw new InvalidArgumentException('Bot name is required');
        }

        if (strlen($this->bot_name) > 255) {
            throw new InvalidArgumentException('Bot name must be 255 characters or less');
        }

        if (empty($this->bot_role)) {
            throw new InvalidArgumentException('Bot role is required');
        }

        if (!in_array($this->bot_role, self::VALID_ROLES)) {
            throw new InvalidArgumentException('Invalid bot role: ' . $this->bot_role);
        }

        if (empty($this->system_prompt)) {
            throw new InvalidArgumentException('System prompt is required');
        }

        if (strlen($this->system_prompt) < 10) {
            throw new InvalidArgumentException('System prompt must be at least 10 characters');
        }

        if (!in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAUSED, self::STATUS_ARCHIVED])) {
            throw new InvalidArgumentException('Invalid status: ' . $this->status);
        }

        return true;
    }

    // Getters and Setters

    public function getBotId(): ?int
    {
        return $this->bot_id;
    }

    public function setBotId(int $bot_id): self
    {
        $this->bot_id = $bot_id;
        return $this;
    }

    public function getBotName(): string
    {
        return $this->bot_name;
    }

    public function setBotName(string $bot_name): self
    {
        $this->bot_name = trim($bot_name);
        return $this;
    }

    public function getBotRole(): string
    {
        return $this->bot_role;
    }

    public function setBotRole(string $bot_role): self
    {
        $this->bot_role = strtolower(trim($bot_role));
        return $this;
    }

    public function getSystemPrompt(): string
    {
        return $this->system_prompt;
    }

    public function setSystemPrompt(string $system_prompt): self
    {
        $this->system_prompt = trim($system_prompt);
        return $this;
    }

    public function getScheduleCron(): ?string
    {
        return $this->schedule_cron;
    }

    public function setScheduleCron(?string $schedule_cron): self
    {
        $this->schedule_cron = $schedule_cron;
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

    public function getConfigJson(): array
    {
        return $this->config_json ?? [];
    }

    public function setConfigJson(array $config_json): self
    {
        $this->config_json = $config_json;
        return $this;
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->config_json[$key] ?? $default;
    }

    public function setConfig(string $key, $value): self
    {
        if ($this->config_json === null) {
            $this->config_json = [];
        }
        $this->config_json[$key] = $value;
        return $this;
    }

    public function getLastExecution(): ?DateTime
    {
        return $this->last_execution;
    }

    public function setLastExecution(?DateTime $last_execution): self
    {
        $this->last_execution = $last_execution;
        return $this;
    }

    public function getNextExecution(): ?DateTime
    {
        return $this->next_execution;
    }

    public function setNextExecution(?DateTime $next_execution): self
    {
        $this->next_execution = $next_execution;
        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    /**
     * Check if bot is active
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if bot is paused
     * @return bool
     */
    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    /**
     * Check if bot is archived
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Check if bot is scheduled
     * @return bool
     */
    public function isScheduled(): bool
    {
        return !empty($this->schedule_cron);
    }
}
