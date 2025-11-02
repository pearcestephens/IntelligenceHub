<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// TODO: Wire to enterprise Git connectors or GitHub MCP tools
$raw = file_get_contents('php://input');
$input = json_decode($raw ?: '[]', true) ?: [];
$action = $input['action'] ?? ($input['tool'] ?? '');

try {
    switch ($action) {
        case 'search':
            http_response_code(501);
            echo json_encode([
                'success' => false,
                'error' => 'git.search not implemented yet',
                'message' => 'GitHub code search requires GitHub App authentication. Wire this to your GitHub MCP connector.',
                'action' => $action
            ]);
            break;

        case 'open_pr':
            http_response_code(501);
            echo json_encode([
                'success' => false,
                'error' => 'git.open_pr not implemented yet',
                'message' => 'PR creation requires GitHub App authentication. Wire this to your GitHub MCP connector.',
                'action' => $action
            ]);
            break;

        default:
            http_response_code(501);
            echo json_encode(['success' => false, 'error' => 'GitTool action not implemented', 'action' => $action]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
