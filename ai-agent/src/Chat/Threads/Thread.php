<?php

declare(strict_types=1);

namespace App\Chat\Threads;

/**
 * Represents a conversation thread with branching lineage.
 */
class Thread
{
    public string $id;
    public ?string $parentId;
    /**
     * @var array<int,array{role:string,content:string,ts:int,id:string,meta?:array}>
     */
    public array $messages = [];
    public int $createdAt;
    public array $tags = [];

    public function __construct(?string $parentId = null, ?string $id = null)
    {
        $this->id = $id ?? bin2hex(random_bytes(8));
        $this->parentId = $parentId;
        $this->createdAt = time();
    }

    /**
     * Add a message with optional metadata envelope.
     * Meta structure (extensible):
     *  - token_estimate:int
     *  - persona:string|null
     *  - command_ref:string|null (references associated command/system message id)
     *  - attachments:array<string>
     *  - latency_ms:int|null (assistant/system timing)
     */
    public function addMessage(string $role, string $content, array $meta = []): string
    {
        $mid = bin2hex(random_bytes(6));
        $defaultMeta = [
            'token_estimate' => self::estimateTokens($content),
            'persona' => $meta['persona'] ?? null,
            'command_ref' => $meta['command_ref'] ?? null,
            'attachments' => $meta['attachments'] ?? [],
            'latency_ms' => $meta['latency_ms'] ?? null
        ];
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
            'ts' => time(),
            'id' => $mid,
            'meta' => $defaultMeta
        ];
        return $mid;
    }

    public function forkFromMessage(string $messageId): self
    {
        $new = new self($this->id);
        // Copy messages up to and including messageId
        foreach ($this->messages as $m) {
            $new->messages[] = $m;
            if ($m['id'] === $messageId) {
                break;
            }
        }
        return $new;
    }

    public static function estimateTokens(string $text): int
    {
        // Lightweight heuristic: words * 1 (approx). Avoid heavy tokenizer dependencies here.
        $w = preg_split('/\s+/', trim($text));
        $w = array_filter($w, fn($x) => $x !== '');
        return max(1, count($w));
    }
}
