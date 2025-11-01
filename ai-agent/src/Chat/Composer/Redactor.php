<?php

declare(strict_types=1);

namespace App\Chat\Composer;

/**
 * Simple PII / sensitive token redactor (extensible with patterns & policies).
 */
class Redactor
{
    /** @var array<array{pattern:string,replacement:string,description:string}> */
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            ['pattern' => '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', 'replacement' => '[REDACTED_EMAIL]', 'description' => 'Email'],
            ['pattern' => '/\b\+?\d{7,15}\b/', 'replacement' => '[REDACTED_PHONE]', 'description' => 'Phone'],
            ['pattern' => '/\b\d{3}-\d{2}-\d{4}\b/', 'replacement' => '[REDACTED_ID]', 'description' => 'Generic ID'],
        ];
    }

    public function addRule(string $pattern, string $replacement, string $description): void
    {
        $this->rules[] = compact('pattern', 'replacement', 'description');
    }

    public function process(string $text, bool $log = false): array
    {
        $original = $text;
        $applied = [];
        foreach ($this->rules as $r) {
            if (preg_match($r['pattern'], $text)) {
                $applied[] = $r['description'];
            }
            $text = preg_replace($r['pattern'], $r['replacement'], $text) ?? $text;
        }
        return [
            'redacted' => $text,
            'original_hash' => sha1($original),
            'patterns_triggered' => $applied
        ];
    }
}
