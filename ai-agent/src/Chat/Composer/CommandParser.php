<?php

declare(strict_types=1);

namespace App\Chat\Composer;

use App\Chat\Commands\SlashCommand;

class CommandParser
{
    /** @var array<string,SlashCommand> */
    private array $commands = [];

    public function register(SlashCommand $cmd): void
    {
        $this->commands[strtolower($cmd->getName())] = $cmd;
    }

    public function list(): array
    {
        $out = [];
        foreach ($this->commands as $c) {
            $out[] = ['name' => $c->getName(), 'description' => $c->getDescription()];
        }
        return $out;
    }

    /**
     * If input begins with / recognized command -> returns structured payload; else null.
     */
    public function parse(string $input, array $context): ?array
    {
        $trim = ltrim($input);
        if (str_starts_with($trim, '/')) {
            $parts = preg_split('/\s+/', $trim, 2);
            $first = $parts[0] ?? '';
            $name = strtolower(ltrim($first, '/'));
            $arg = $parts[1] ?? '';
            if (isset($this->commands[$name])) {
                return $this->commands[$name]->run($arg, $context);
            }
            return [
                'type' => 'error',
                'content' => 'Unknown command: ' . $name,
                'metadata' => ['available' => $this->list()]
            ];
        }
        return null;
    }
}
