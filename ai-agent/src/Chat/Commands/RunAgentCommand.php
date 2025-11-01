<?php

declare(strict_types=1);

namespace App\Chat\Commands;

class RunAgentCommand implements SlashCommand
{
    public function getName(): string
    {
        return 'run-agent';
    }

    public function getDescription(): string
    {
        return 'Invoke a named internal agent with the provided instruction.';
    }

    public function run(string $arg, array $context): array
    {
        // Arg pattern: <agentName> optional text
        $parts = preg_split('/\s+/', trim($arg), 2);
        $agent = $parts[0] ?? 'default';
        $instruction = $parts[1] ?? '';
        return [
            'type' => 'agent_invoke',
            'content' => [
                'agent' => $agent,
                'instruction' => $instruction,
                'context_window' => array_slice($context['messages'] ?? [], -15)
            ],
            'metadata' => ['command' => 'run-agent','agent' => $agent]
        ];
    }
}
