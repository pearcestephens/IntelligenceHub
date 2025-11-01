<?php

declare(strict_types=1);

namespace App\Tools;

use App\Logger;

final class ToolCatalog
{
    /**
     * Return catalog of tool specs using ToolRegistry definitions; includes spec() when available.
     */
    public static function getSpecs(bool $includeInternal = true): array
    {
        $all = ToolRegistry::getAll();
        $out = [];
        foreach ($all as $name => $def) {
            if (!$includeInternal && ($def['internal'] ?? false)) {
                continue;
            }
            $spec = [
                'name' => $name,
                'description' => $def['description'] ?? '',
                'category' => $def['category'] ?? 'general',
                'parameters' => $def['parameters'] ?? ['type' => 'object','properties' => [],'required' => []],
                'safety' => $def['safety'] ?? [],
                'internal' => $def['internal'] ?? false,
            ];

            // If the tool class exposes spec(), merge authoritative fields
            if (isset($def['class']) && is_string($def['class']) && method_exists($def['class'], 'spec')) {
                try {
                    $extra = $def['class']::spec();
                } catch (\Throwable $e) {
                    $extra = [];
                }
                if (is_array($extra)) {
                    $spec = array_replace_recursive($spec, $extra);
                }
            }

            $out[] = $spec;
        }
        return $out;
    }

    /**
     * JSON for OpenAI tools (function calling) – mirrors ToolRegistry::getOpenAISchema but inlined here for convenience.
     */
    public static function toOpenAITools(): array
    {
        $functions = [];
        foreach (ToolRegistry::getAll() as $name => $tool) {
            if (!($tool['enabled'] ?? true)) {
                continue;
            }
            $functions[] = [
                'type' => 'function',
                'function' => [
                    'name' => $name,
                    'description' => $tool['description'] ?? '',
                    'parameters' => $tool['parameters'] ?? ['type' => 'object','properties' => [],'required' => []]
                ]
            ];
        }
        return $functions;
    }

    /**
     * Export a YAML catalog (no external libs – simple emitter for our limited schema).
     */
    public static function toYaml(array $specs): string
    {
        $lines = [];
        $lines[] = 'tools:';
        foreach ($specs as $spec) {
            $lines[] = '  - name: ' . self::yaml($spec['name'] ?? '');
            $lines[] = '    description: ' . self::yaml($spec['description'] ?? '');
            $lines[] = '    category: ' . self::yaml($spec['category'] ?? 'general');
            $lines[] = '    internal: ' . ((($spec['internal'] ?? false) === true) ? 'true' : 'false');
            $lines[] = '    safety:';
            $safety = $spec['safety'] ?? [];
            $lines[] = '      timeout: ' . (int)($safety['timeout'] ?? 0);
            $lines[] = '      rate_limit: ' . (int)($safety['rate_limit'] ?? 0);
            $lines[] = '    parameters:';
            $params = $spec['parameters'] ?? ['type' => 'object','properties' => [],'required' => []];
            $lines[] = '      type: ' . self::yaml($params['type'] ?? 'object');
            $lines[] = '      properties:';
            $props = $params['properties'] ?? [];
            foreach ($props as $pname => $pdef) {
                $lines[] = '        ' . $pname . ':';
                foreach ($pdef as $k => $v) {
                    if (is_array($v)) {
                        $lines[] = '          ' . $k . ':';
                        foreach ($v as $iv) {
                            $lines[] = '            - ' . self::yaml($iv);
                        }
                    } else {
                        $lines[] = '          ' . $k . ': ' . self::yaml($v);
                    }
                }
            }
            $required = $params['required'] ?? [];
            $lines[] = '      required:';
            foreach ($required as $r) {
                $lines[] = '        - ' . self::yaml($r);
            }
        }
        return implode("\n", $lines) . "\n";
    }

    private static function yaml(mixed $v): string
    {
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if ($v === null) {
            return 'null';
        }
        $s = (string)$v;
        // Quote if contains special chars
        if (preg_match('/[^A-Za-z0-9_\-\. ]/', $s)) {
            $s = '"' . str_replace('"', '\\"', $s) . '"';
        }
        return $s === '' ? '""' : $s;
    }
}
