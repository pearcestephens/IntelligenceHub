##################################################################
# AUTHENTICATION PATCH FOR chat-enterprise.php
# Location: Line 828 (after function definitions, before main logic)
# Purpose: Enable API key authentication
##################################################################

// ═══════════════════════════════════════════════════════════════
// AUTHENTICATION - API KEY VALIDATION (ADDED BY P0 FIX)
// ═══════════════════════════════════════════════════════════════

/**
 * Validate API key from request header
 * Returns true if valid, sends 401 response and exits if invalid
 */
function validateApiKey(): bool {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

    if (empty($apiKey)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'MISSING_API_KEY',
                'message' => 'API key is required. Please provide X-API-KEY header.'
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // Get valid API keys from environment
    $validKeys = explode(',', getenv('API_KEYS') ?: '');
    $validKeys = array_map('trim', $validKeys);

    if (!in_array($apiKey, $validKeys, true)) {
        // Log unauthorized attempt
        error_log(sprintf(
            '[Auth] Unauthorized API key attempt: %s... from IP: %s',
            substr($apiKey, 0, 12),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));

        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_API_KEY',
                'message' => 'The provided API key is not valid.'
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // Log successful authentication
    error_log(sprintf(
        '[Auth] Authenticated request: key=%s... ip=%s',
        substr($apiKey, 0, 12),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ));

    return true;
}

// Execute authentication check
validateApiKey();

// If we reach here, authentication passed - continue with normal processing
