<?php

declare(strict_types=1);

namespace App\Chat\Persona;

/**
 * Manage role/tone personas applied as system preambles.
 */
class PersonaManager
{
    /** @var array<string,array{label:string,preamble:string}> */
    private array $personas;

    public function __construct()
    {
        $this->personas = [
            'analyst' => [
                'label' => 'Analyst',
                'preamble' => 'You are a data & risk analyst. Output structured findings, bullet lists, confidence levels.'
            ],
            'engineer' => [
                'label' => 'Engineer',
                'preamble' => 'You are a senior software engineer. Provide precise, production-grade answers with code when needed.'
            ],
            'compliance' => [
                'label' => 'Compliance',
                'preamble' => 'You are a compliance officer. Focus on regulatory risk, policy adherence, required controls.'
            ],
            'product' => [
                'label' => 'Product',
                'preamble' => 'You are a product strategist. Emphasize user value, adoption metrics, roadmap clarity.'
            ],
        ];
    }

    public function list(): array
    {
        return $this->personas;
    }

    public function get(string $key): ?array
    {
        return $this->personas[$key] ?? null;
    }
}
