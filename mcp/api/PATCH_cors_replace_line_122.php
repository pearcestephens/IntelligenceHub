##################################################################
# CORS RESTRICTION PATCH FOR chat-enterprise.php
# Location: Line 122 (replace existing CORS header)
# Purpose: Restrict CORS to known domains only
##################################################################

// ═══════════════════════════════════════════════════════════════
// CORS - RESTRICTED TO KNOWN DOMAINS (UPDATED BY P0 FIX)
// ═══════════════════════════════════════════════════════════════

/**
 * Whitelist of allowed origins for CORS
 * Only these domains can make cross-origin requests
 */
$allowedOrigins = [
    'https://staff.vapeshed.co.nz',      // CIS Staff Portal
    'https://gpt.ecigdis.co.nz',         // AI Control Panel (Intelligence Hub)
    'https://www.vapeshed.co.nz',        // Public Website
    'https://wiki.vapeshed.co.nz',       // Internal Wiki
    'https://vapehq.co.nz',              // VapeHQ
    'https://www.vapingkiwi.co.nz',      // Vaping Kiwi
    'http://localhost:3000',             // Development (remove in production)
    'http://localhost:8080'              // Development (remove in production)
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    // Origin is whitelisted - allow CORS
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');

    // Log successful CORS
    error_log(sprintf('[CORS] Allowed origin: %s', $origin));
} else {
    // Origin not whitelisted - deny CORS
    if (!empty($origin)) {
        error_log(sprintf('[CORS] Blocked unauthorized origin: %s from IP: %s',
            $origin,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
    }

    // Don't set CORS headers - browser will block the request
    // Note: For non-CORS requests (same-origin), this is fine
}

// Continue with other CORS headers (already present in original)
