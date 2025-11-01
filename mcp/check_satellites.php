<?php
/**
 * Satellite Health Checker
 * 
 * Tests actual satellite endpoints (not fake endpoints)
 * Returns accurate status of all 4 units in the Intelligence Network
 * 
 * Usage: curl https://gpt.ecigdis.co.nz/mcp/check_satellites.php
 */

header('Content-Type: application/json');

$satellites = [
    [
        'unit_id' => 1,
        'name' => 'Intelligence Hub',
        'domain' => 'gpt.ecigdis.co.nz',
        'endpoints' => [
            [
                'name' => 'Neural Scanner',
                'url' => 'https://gpt.ecigdis.co.nz/api/intelligence/scan',
                'method' => 'POST',
                'headers' => [
                    'X-API-Key: master_api_key_2025',
                    'Content-Type: application/json'
                ],
                'body' => json_encode(['action' => 'status']),
                'expected_status' => 200
            ],
            [
                'name' => 'Satellite Data Receiver',
                'url' => 'https://gpt.ecigdis.co.nz/api/receive_satellite_data.php',
                'method' => 'POST',
                'headers' => [
                    'X-API-Key: satellite_master_key_2025',
                    'Content-Type: application/json'
                ],
                'body' => json_encode(['action' => 'ping']),
                'expected_status' => 200
            ],
            [
                'name' => 'MCP Server',
                'url' => 'https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php',
                'method' => 'POST',
                'headers' => ['Content-Type: application/json'],
                'body' => json_encode([
                    'jsonrpc' => '2.0',
                    'method' => 'tools/list',
                    'params' => [],
                    'id' => 1
                ]),
                'expected_status' => 200
            ]
        ]
    ],
    [
        'unit_id' => 2,
        'name' => 'CIS Staff Portal',
        'domain' => 'staff.vapeshed.co.nz',
        'endpoints' => [
            [
                'name' => 'Intelligence API',
                'url' => 'https://staff.vapeshed.co.nz/api/intelligence/scan',
                'method' => 'POST',
                'headers' => [
                    'X-API-Key: master_api_key_2025',
                    'Content-Type: application/json'
                ],
                'body' => json_encode(['action' => 'status']),
                'expected_status' => 200
            ]
        ]
    ],
    [
        'unit_id' => 3,
        'name' => 'VapeShed Retail',
        'domain' => 'vapeshed.co.nz',
        'endpoints' => [
            [
                'name' => 'Intelligence API',
                'url' => 'https://vapeshed.co.nz/api/intelligence/scan',
                'method' => 'POST',
                'headers' => [
                    'X-API-Key: master_api_key_2025',
                    'Content-Type: application/json'
                ],
                'body' => json_encode(['action' => 'status']),
                'expected_status' => 200
            ]
        ]
    ],
    [
        'unit_id' => 4,
        'name' => 'Wholesale Portal',
        'domain' => 'wholesale.ecigdis.co.nz',
        'endpoints' => [
            [
                'name' => 'Intelligence API',
                'url' => 'https://wholesale.ecigdis.co.nz/api/intelligence/scan',
                'method' => 'POST',
                'headers' => [
                    'X-API-Key: master_api_key_2025',
                    'Content-Type: application/json'
                ],
                'body' => json_encode(['action' => 'status']),
                'expected_status' => 200
            ]
        ]
    ]
];

$results = [];
$overall_status = 'healthy';

foreach ($satellites as $satellite) {
    $satellite_result = [
        'unit_id' => $satellite['unit_id'],
        'name' => $satellite['name'],
        'domain' => $satellite['domain'],
        'endpoints' => [],
        'status' => 'unknown'
    ];
    
    $all_ok = true;
    
    foreach ($satellite['endpoints'] as $endpoint) {
        $ch = curl_init($endpoint['url']);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => $endpoint['headers']
        ]);
        
        if ($endpoint['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $endpoint['body']);
        }
        
        $start_time = microtime(true);
        $response = curl_exec($ch);
        $execution_time = round((microtime(true) - $start_time) * 1000, 2);
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        $endpoint_ok = ($http_code === $endpoint['expected_status']);
        
        if (!$endpoint_ok) {
            $all_ok = false;
        }
        
        $satellite_result['endpoints'][] = [
            'name' => $endpoint['name'],
            'url' => $endpoint['url'],
            'status' => $endpoint_ok ? 'ok' : 'error',
            'http_code' => $http_code,
            'execution_time_ms' => $execution_time,
            'error' => $curl_error ?: null
        ];
    }
    
    $satellite_result['status'] = $all_ok ? 'online' : 'degraded';
    
    if (!$all_ok) {
        $overall_status = 'degraded';
    }
    
    $results[] = $satellite_result;
}

echo json_encode([
    'overall_status' => $overall_status,
    'timestamp' => date('Y-m-d H:i:s'),
    'satellites' => $results
], JSON_PRETTY_PRINT);
