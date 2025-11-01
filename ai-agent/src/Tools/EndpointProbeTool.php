<?php

declare(strict_types=1);

namespace App\Tools;

use App\Tools\Contracts\ToolContract;

final class EndpointProbeTool implements ToolContract
{
    public static function run(array $params, array $context = []): array
    {
        $endpoints = $params['endpoints'] ?? [];
        if (!is_array($endpoints)) {
            $endpoints = [$endpoints];
        }
        $assertBody = $params['assert_body_contains'] ?? null; // string|array
        $timeout = (int)($params['timeout'] ?? 10);

        $results = [];
        foreach ($endpoints as $ep) {
            if (!is_array($ep)) {
                $ep = ['url' => (string)$ep];
            }
            $req = [
                'url' => (string)($ep['url'] ?? ''),
                'method' => strtoupper((string)($ep['method'] ?? 'GET')),
                'headers' => $ep['headers'] ?? [],
                'body' => $ep['body'] ?? null,
                'timeout' => $ep['timeout'] ?? $timeout,
                'follow_redirects' => $ep['follow_redirects'] ?? true,
                'max_redirects' => $ep['max_redirects'] ?? 5,
            ];
            $resp = HttpTool::run($req, $context);
            $ok = $resp['success'] ?? false;

            // Basic assertions
            $assertions = [];
            if (isset($ep['expect_status'])) {
                $assertions[] = [
                    'type' => 'status',
                    'expected' => (int)$ep['expect_status'],
                    'actual' => (int)($resp['status_code'] ?? 0),
                    'ok' => (int)($resp['status_code'] ?? 0) === (int)$ep['expect_status']
                ];
            }
            if ($assertBody) {
                $body = (string)($resp['raw_body'] ?? '');
                $needles = is_array($assertBody) ? $assertBody : [$assertBody];
                foreach ($needles as $needle) {
                    $assertions[] = [
                        'type' => 'body_contains',
                        'expected' => (string)$needle,
                        'ok' => str_contains(strtolower($body), strtolower((string)$needle))
                    ];
                }
            }
            $allOk = $ok && !in_array(false, array_column($assertions, 'ok'), true);
            $results[] = [
                'request' => $req,
                'response' => [
                    'success' => $resp['success'] ?? false,
                    'status_code' => $resp['status_code'] ?? 0,
                    'duration_ms' => $resp['duration_ms'] ?? 0,
                    'content_type' => $resp['content_type'] ?? null,
                    'size' => $resp['size'] ?? 0
                ],
                'assertions' => $assertions,
                'ok' => $allOk
            ];
        }
        $summary = [
            'total' => count($results),
            'passed' => count(array_filter($results, fn($r) => $r['ok'])),
        ];
        return ['success' => $summary['passed'] === $summary['total'], 'summary' => $summary, 'results' => $results];
    }

    public static function spec(): array
    {
        return [
            'name' => 'endpoint_probe',
            'description' => 'Probe HTTPS endpoints with simple response assertions',
            'category' => 'diagnostics',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'endpoints' => ['type' => ['array','string']],
                    'assert_body_contains' => ['type' => ['array','string']],
                    'timeout' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 120]
                ],
                'required' => ['endpoints']
            ],
            'safety' => ['timeout' => 60, 'rate_limit' => 5]
        ];
    }
}
