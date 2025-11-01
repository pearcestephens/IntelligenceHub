<?php
/**
 * Bot Management API
 * Provides information about available bots and their capabilities
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$bots = [
    'general' => [
        'id' => 'general',
        'name' => 'CIS General Assistant',
        'description' => 'Helpful AI assistant for general CIS system questions and guidance',
        'avatar' => '🤖',
        'color' => '#007bff',
        'capabilities' => [
            'General questions',
            'System navigation',
            'Basic troubleshooting',
            'Documentation help'
        ],
        'response_time' => 'Fast (< 2s)',
        'status' => 'online'
    ],
    'technical' => [
        'id' => 'technical',
        'name' => 'Technical Support',
        'description' => 'Specialized in PHP, MySQL, web development, and system administration',
        'avatar' => '⚙️',
        'color' => '#28a745',
        'capabilities' => [
            'Code debugging',
            'Database optimization',
            'Server configuration',
            'API troubleshooting',
            'Performance analysis'
        ],
        'response_time' => 'Moderate (< 5s)',
        'status' => 'online'
    ],
    'business' => [
        'id' => 'business',
        'name' => 'Business Intelligence',
        'description' => 'Business analysis, reporting, and operational insights',
        'avatar' => '📊',
        'color' => '#ffc107',
        'capabilities' => [
            'Data analysis',
            'Report generation',
            'KPI tracking',
            'Trend analysis',
            'Business metrics'
        ],
        'response_time' => 'Detailed (< 8s)',
        'status' => 'online'
    ]
];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $bot_id = $_GET['bot_id'] ?? null;
        
        if ($bot_id) {
            if (!isset($bots[$bot_id])) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Bot not found'
                ]);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'bot' => $bots[$bot_id]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'bots' => array_values($bots),
                'total_count' => count($bots),
                'online_count' => count(array_filter($bots, function($bot) {
                    return $bot['status'] === 'online';
                }))
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
        break;
}
?>