<?php
declare(strict_types=1);

/**
 * Bootstrap & helpers for AI Agent endpoints
 * - PDO connection
 * - JSON I/O
 * - Envelope responses
 * - Secure path
 * - Conversation helpers
 */

date_default_timezone_set('Pacific/Auckland');

const AI_AGENT_VERSION = '2025.11.02';

// ---------- LOAD ENV ----------
$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
$envFile = $docRoot . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments
        if (empty($line) || $line[0] === '#') continue;
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key) && !isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// ---------- ENV HELPERS ----------
function env(string $key, ?string $default=null): ?string {
  $v = getenv($key);
  if ($v !== false && $v !== '') return $v;
  if (isset($_ENV[$key]) && $_ENV[$key] !== '') return (string)$_ENV[$key];
  return $default;
}

// ---------- REQUEST/RESPONSE ----------
function req_json(): array {
  $raw = file_get_contents('php://input');
  if ($raw === false || $raw === '') return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function new_request_id(): string {
  return bin2hex(random_bytes(16));
}

function response_json(array $payload, int $status=200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  header('X-AI-Agent-Version: '.AI_AGENT_VERSION);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
}

function envelope_success(array $data, string $requestId, int $status=200): void {
  response_json([
    'success' => true,
    'request_id' => $requestId,
    'data' => $data,
    'meta' => [
      'ts' => date('c'),
      'host' => $_SERVER['HTTP_HOST'] ?? 'cli',
      'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
      'version' => AI_AGENT_VERSION
    ]
  ], $status);
}

function envelope_error(string $code, string $message, string $requestId, array $detail=[], int $status=500): void {
  response_json([
    'success' => false,
    'request_id' => $requestId,
    'error' => [
      'code' => $code,
      'message' => $message,
      'detail' => $detail
    ],
    'meta' => [
      'ts' => date('c'),
      'host' => $_SERVER['HTTP_HOST'] ?? 'cli',
      'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
      'version' => AI_AGENT_VERSION
    ]
  ], $status);
}

// ---------- PDO ----------
function get_pdo(): PDO {
  $host = env('DB_HOST') ?: '127.0.0.1';
  $db   = env('DB_NAME') ?: 'hdgwrzntwa';
  $user = env('DB_USER') ?: 'hdgwrzntwa';
  $pass = env('DB_PASS') ?: 'bFUdRjh4Jx';
  $dsn  = "mysql:host={$host};dbname={$db};charset=utf8mb4";
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
  ]);
  return $pdo;
}

// ---------- AUTH (optional) ----------
function resolve_domain_id(PDO $db): ?int {
  $host = $_SERVER['HTTP_HOST'] ?? null;
  if (!$host) return null;
  $stmt = $db->prepare("SELECT id FROM ai_agent_domains WHERE domain=? AND is_active=1 LIMIT 1");
  $stmt->execute([$host]);
  $id = $stmt->fetchColumn();
  return $id ? (int)$id : null;
}

// If you want to enforce keys, set AI_AGENT_REQUIRE_AUTH=1 and provide header:
// - Authorization: Bearer <API_KEY>  OR  x-api-key: <API_KEY>
function require_api_key_if_enabled(PDO $db): ?int {
  $enforce = (env('AI_AGENT_REQUIRE_AUTH') ?? '0') === '1';
  $domainId = resolve_domain_id($db);
  if (!$enforce) return $domainId;

  $key = null;
  if (!empty($_SERVER['HTTP_AUTHORIZATION']) && str_starts_with($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ')) {
    $key = trim(substr($_SERVER['HTTP_AUTHORIZATION'], 7));
  } elseif (!empty($_SERVER['HTTP_X_API_KEY'])) {
    $key = trim($_SERVER['HTTP_X_API_KEY']);
  }
  if (!$key) {
    envelope_error('AUTH_REQUIRED', 'Missing API key', new_request_id(), [], 401);
    exit;
  }

  $stmt = $db->prepare("SELECT id FROM ai_agent_domains WHERE api_key=? AND is_active=1 LIMIT 1");
  $stmt->execute([$key]);
  $ok = $stmt->fetchColumn();
  if (!$ok) {
    envelope_error('AUTH_INVALID', 'Invalid API key', new_request_id(), [], 403);
    exit;
  }
  return $domainId;
}

// ---------- FILESYSTEM JAIL ----------
function secure_path(string $path, ?string $base=null): string {
  $base = $base ?: (realpath($_SERVER['DOCUMENT_ROOT'] ?? getcwd()) ?: getcwd());
  $target = $path;
  if (!preg_match('#^/#', $target)) { $target = $base . '/' . $target; }
  $real = realpath($target);
  if ($real === false) {
    // allow non-existing file under base (for writes): normalize
    $real = preg_replace('#/+#','/', $base.'/'.ltrim($path,'/'));
  }
  $baseReal = realpath($base) ?: $base;
  if (strpos($real, $baseReal) !== 0) {
    throw new RuntimeException('PATH_ESCAPES_JAIL: '.$path);
  }
  return $real;
}

function ensure_backup(string $file): ?string {
  $dir = dirname($file);
  $backupDir = $dir.'/.backups';
  if (!is_dir($backupDir)) @mkdir($backupDir, 0775, true);
  if (!is_dir($backupDir) || !is_writable($backupDir)) return null;
  if (is_file($file)) {
    $stamp = date('Ymd_His');
    $bn = basename($file);
    $dest = $backupDir.'/'.$bn.'.'.$stamp.'.bak';
    @copy($file, $dest);
    return $dest;
  }
  return null;
}

// ---------- CONVERSATIONS ----------
function upsert_conversation(PDO $db, string $sessionId, string $platform='github_copilot', ?string $userIdentifier=null, int $orgId=1): int {
  // Try existing by session
  $stmt = $db->prepare("SELECT conversation_id FROM ai_conversations WHERE session_id=? AND platform=? ORDER BY started_at DESC LIMIT 1");
  $stmt->execute([$sessionId, $platform]);
  $cid = $stmt->fetchColumn();
  if ($cid) return (int)$cid;

  $stmt = $db->prepare("INSERT INTO ai_conversations (org_id, session_id, platform, user_identifier, status, started_at) VALUES (?,?,?,?, 'active', NOW())");
  $stmt->execute([$orgId, $sessionId, $platform, $userIdentifier]);
  return (int)$db->lastInsertId();
}

function next_message_sequence(PDO $db, int $conversationId): int {
  $stmt = $db->prepare("SELECT COALESCE(MAX(message_sequence),0)+1 FROM ai_conversation_messages WHERE conversation_id=?");
  $stmt->execute([$conversationId]);
  return (int)$stmt->fetchColumn();
}
