<?php

declare(strict_types=1);

namespace App\Chat\Commands;

class RerankCommand implements SlashCommand
{
    public function getName(): string
    {
        return 'rerank';
    }

    public function getDescription(): string
    {
        return 'Request a quality-improved / re-ranked alternative answer.';
    }

    public function run(string $arg, array $context): array
    {
        $lastAssistant = null;
        $messages = $context['messages'] ?? [];
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if (($messages[$i]['role'] ?? '') === 'assistant') {
                $lastAssistant = $messages[$i]['content'] ?? '';
                break;
            }
        }
        return [
            'type' => 'instruction',
            'content' => "Re-evaluate and improve the earlier answer. Provide a higher-quality, structured alternative. Original Answer:\n" . ($lastAssistant ?? 'N/A') . "\nRerank focus: " . $arg,
            'metadata' => ['command' => 'rerank']
        ];
    }
}
