<?php

declare(strict_types=1);

namespace App\Chat\Commands;

/**
 * Interface for all slash commands.
 */
interface SlashCommand
{
    /**
     * Command keyword (without leading slash) e.g. 'summarize'.
     */
    public function getName(): string;

    /**
     * One line description.
     */
    public function getDescription(): string;

    /**
     * Execute command and return a structured payload to feed model or UI.
     * @param string $arg Raw argument string after the command keyword.
     * @param array $context Current chat context/messages metadata.
     * @return array{type:string,content:mixed,metadata?:array}
     */
    public function run(string $arg, array $context): array;
}
