<?php
/**
 * Bot Prompt Builder API
 * 
 * Generate custom bot prompts with enforced standards
 * 
 * Endpoints:
 * - /api/bot-prompt.php?action=list_templates
 * - /api/bot-prompt.php?action=list_standards
 * - /api/bot-prompt.php?action=generate&template=database_dev&task=...
 * - /api/bot-prompt.php?action=validate_code&standards[]=security&code=...
 * 
 * @package CIS\API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../services/BotPromptBuilder.php';

function apiResponse(bool $success, $data = null, string $message = '', int $code = 200): void
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (empty($action)) {
    apiResponse(false, null, 'Missing required parameter: action', 400);
}

try {
    $builder = new BotPromptBuilder();
    
    switch ($action) {
        case 'list_templates':
            $templates = $builder->listTemplates();
            apiResponse(true, [
                'templates' => $templates,
                'count' => count($templates)
            ], 'Templates retrieved');
            break;
            
        case 'list_standards':
            $standards = $builder->listStandards();
            apiResponse(true, [
                'standards' => $standards,
                'count' => count($standards)
            ], 'Standards retrieved');
            break;
            
        case 'get_standard':
            $key = $_GET['standard'] ?? '';
            if (empty($key)) {
                apiResponse(false, null, 'Missing parameter: standard', 400);
            }
            
            $standard = $builder->getStandard($key);
            if (empty($standard)) {
                apiResponse(false, null, "Standard '{$key}' not found", 404);
            }
            
            apiResponse(true, $standard, 'Standard details retrieved');
            break;
            
        case 'generate':
            $template = $_GET['template'] ?? $_POST['template'] ?? '';
            if (empty($template)) {
                apiResponse(false, null, 'Missing required parameter: template', 400);
            }
            
            // Parse task details from query/post params
            $taskDetails = [];
            foreach ($_REQUEST as $key => $value) {
                if (strpos($key, 'task_') === 0) {
                    $cleanKey = str_replace('task_', '', $key);
                    $taskDetails[$cleanKey] = $value;
                }
            }
            
            // Parse additional standards
            $additionalStandards = $_GET['additional_standards'] ?? $_POST['additional_standards'] ?? [];
            if (is_string($additionalStandards)) {
                $additionalStandards = explode(',', $additionalStandards);
            }
            
            try {
                $result = $builder->generatePrompt($template, $taskDetails, $additionalStandards);
                apiResponse(true, $result, 'Prompt generated successfully');
            } catch (Exception $e) {
                apiResponse(false, null, $e->getMessage(), 400);
            }
            break;
            
        case 'validate_code':
            $code = $_POST['code'] ?? '';
            $standards = $_POST['standards'] ?? $_GET['standards'] ?? [];
            
            if (empty($code)) {
                apiResponse(false, null, 'Missing required parameter: code', 400);
            }
            
            if (is_string($standards)) {
                $standards = explode(',', $standards);
            }
            
            if (empty($standards)) {
                $standards = ['security', 'code_quality', 'error_handling'];
            }
            
            $result = $builder->validateCodeAgainstStandards($code, $standards);
            apiResponse($result['compliant'], $result, 
                $result['compliant'] ? 'Code is compliant' : 'Code has violations');
            break;
            
        default:
            apiResponse(false, null, 'Invalid action. Available: list_templates, list_standards, get_standard, generate, validate_code', 400);
    }
    
} catch (Exception $e) {
    error_log("Bot Prompt API Error: " . $e->getMessage());
    apiResponse(false, null, 'Internal server error: ' . $e->getMessage(), 500);
}
