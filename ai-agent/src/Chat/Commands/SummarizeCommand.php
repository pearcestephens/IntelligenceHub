<?php

declare(strict_types=1);

namespace App\Chat\Commands;

class SummarizeCommand implements SlashCommand
{
    public function getName(): string
    {
        return 'summarize';
    }

    public function getDescription(): string
    {
        return 'Generate a concise summary of the current thread context.';
    }

    public function run(string $arg, array $context): array
    {
        $joined = [];
        foreach ($context['messages'] ?? [] as $m) {
            $joined[] = ($m['role'] ?? 'user') . ':' . substr($m['content'] ?? '', 0, 400);
        }
        $payload = implode("\n", $joined);
        return [
            'type' => 'instruction',
            'content' => "Summarize the following conversation focusing on key decisions, open questions, and action items.\n\n" . $payload,
            'metadata' => ['command' => 'summarize']
        ];
    }
}
