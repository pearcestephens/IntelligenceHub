<?php
/**
 * admin/traffic/live.php — Secure Server-Sent Events (SSE) endpoint
 * Streams heartbeat + optional metrics for dashboards.
 *
 * Security:
 * - Requires admin token via header X-Admin-Token or query ?token=
 *   Token must match env ADMIN_SSE_TOKEN.
 * - Rate-limits 1 connection per IP every 5 seconds (APCu if available).
 * - Sets no-cache, disables proxy buffering, and uses short run loop.
 */

declare(strict_types=1);

// Optional app bootstrap (if present)
$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
if ($docRoot !== '') {
    $bootstrap = $docRoot . '/app.php';
    if (is_file($bootstrap) && is_readable($bootstrap)) {
        require_once $bootstrap;
    }
}

// --- Security: auth gate ---
$expectedToken = getenv('ADMIN_SSE_TOKEN') ?: '';
$givenToken = '';
// Header check (Apache/Nginx variations)
foreach ([
    'HTTP_X_ADMIN_TOKEN',
    'X_ADMIN_TOKEN',
] as $hdr) {
    if (!empty($_SERVER[$hdr])) {
        $givenToken = (string)$_SERVER[$hdr];
        break;
    }
}
if ($givenToken === '' && isset($_GET['token'])) {
    $givenToken = (string)$_GET['token'];
}

if ($expectedToken === '' || !hash_equals($expectedToken, $givenToken)) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'UNAUTHORIZED',
            'message' => 'Missing or invalid admin token',
        ],
        'timestamp' => date('c'),
    ]);
    exit;
}

// --- Rate limit: one connection per IP every 5 seconds ---
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rlKey = 'sse:live:ip:' . $ip;
$now = time();
$retryAfter = 5;
$okToProceed = true;
if (function_exists('apcu_fetch')) {
    $last = apcu_fetch($rlKey);
    if (is_int($last) && ($now - $last) < $retryAfter) {
        $okToProceed = false;
    } else {
        apcu_store($rlKey, $now, $retryAfter);
    }
}
if (!$okToProceed) {
    http_response_code(429);
    header('Retry-After: ' . $retryAfter);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'RATE_LIMITED',
            'message' => 'Too many connections',
        ],
        'timestamp' => date('c'),
    ]);
    exit;
}

// --- SSE headers ---
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('X-Accel-Buffering: no'); // disable buffering on Nginx
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: https://staff.vapeshed.co.nz'); // tighten as needed

// Retry suggestion if client disconnects
echo "retry: 5000\n\n";

// Flush helpers
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) {
    @ob_end_flush();
}
flush();

// --- Event helpers ---
/**
 * Send an SSE event with JSON-encoded data.
 * @param string $event
 * @param array<mixed> $data
 * @param string|null $id
 */
function sse_event(string $event, array $data, ?string $id = null): void
{
    if ($id !== null) {
        echo 'id: ' . $id . "\n";
    }
    echo 'event: ' . $event . "\n";
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    // Data lines must be prefixed with "data: " and end with a blank line
    echo 'data: ' . $json . "\n\n";
    @ob_flush();
    @flush();
}

// --- Metrics source (APCu-backed, optional) ---
// Expected keys if another process writes:
//   metrics:rps -> float
//   metrics:visitors5m -> int
//   metrics:errors5m -> int
//   metrics:last_endpoint -> string
// Fallbacks are used when not present.
function metrics_fetch(): array
{
    $get = static function (string $key, $default) {
        if (function_exists('apcu_fetch')) {
            $ok = false;
            $val = apcu_fetch($key, $ok);
            if ($ok) return $val;
        }
        return $default;
    };
    return [
        'rps' => (float)$get('metrics:rps', 0.0),
        'visitors5m' => (int)$get('metrics:visitors5m', 0),
        'errors5m' => (int)$get('metrics:errors5m', 0),
        'last_endpoint' => (string)$get('metrics:last_endpoint', ''),
    ];
}

// --- Main loop (60 seconds) ---
$deadline = microtime(true) + 60.0;
$i = 0;
while (true) {
    if (connection_aborted()) {
        break;
    }
    $nowTs = microtime(true);
    if ($nowTs > $deadline) {
        break;
    }
    // Heartbeat every 5 seconds with metrics snapshot
    $metrics = metrics_fetch();
    sse_event('heartbeat', [
        'ts' => date('c'),
        'metrics' => $metrics,
        'seq' => $i,
    ], (string)$i);
    $i++;
    usleep(5 * 1000 * 1000);
}

// Graceful end
sse_event('end', [ 'ts' => date('c') ]);
exit;
