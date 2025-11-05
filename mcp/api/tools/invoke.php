<?php
declare(strict_types=1);

require_once __DIR__.'/../../lib/Bootstrap.php';
require_once __DIR__.'/../../lib/Telemetry.php';

$rid = new_request_id();

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    envelope_error('METHOD_NOT_ALLOWED', 'Use POST with application/json', $rid, [], 405);
    exit;
  }

  $db   = get_pdo();
  $tele = new Telemetry($db);
  require_api_key_if_enabled($db); // optional auth

  $in = req_json();
  $tool = (string)($in['tool'] ?? $in['action'] ?? '');
  $args = is_array($in['args'] ?? null) ? $in['args'] : [];

  if ($tool === '') {
    envelope_error('INVALID_INPUT', 'tool (or action) is required', $rid, [], 422);
    exit;
  }

  $sessionId     = isset($in['session_key']) ? (string)$in['session_key'] : null;
  $conversationId= isset($in['conversation_id']) ? (int)$in['conversation_id'] : null;
  $messageId     = isset($in['message_id']) ? (int)$in['message_id'] : null;

  $toolCallId = $tele->toolStart($conversationId, $messageId, $tool, $rid, $args);
  $t0 = microtime(true);
  $result = null;

  // Simple allow-list
  $TOOLS = [
    'fs.read'   => function(array $a): array {
      $path = secure_path((string)($a['path'] ?? ''));
      if (!is_file($path)) throw new RuntimeException('FILE_NOT_FOUND: '.$path);
      $content = file_get_contents($path);
      return ['path'=>$path, 'bytes'=>strlen((string)$content), 'content'=>$content];
    },
    'fs.list'   => function(array $a): array {
      $path = secure_path((string)($a['path'] ?? ''));
      if (!is_dir($path)) throw new RuntimeException('DIR_NOT_FOUND: '.$path);
      $max = (int)($a['max'] ?? 500);
      $out = [];
      $i=0;
      $it = new DirectoryIterator($path);
      foreach ($it as $f) {
        if ($f->isDot()) continue;
        $out[] = ['name'=>$f->getFilename(), 'type'=>$f->isDir()?'dir':'file', 'bytes'=>$f->isFile() ? $f->getSize() : null];
        if (++$i >= $max) break;
      }
      return ['path'=>$path, 'entries'=>$out];
    },
    'fs.write'  => function(array $a): array {
      $path = secure_path((string)($a['path'] ?? ''));
      $mode = (string)($a['mode'] ?? 'overwrite'); // overwrite|append|insert
      $content = (string)($a['content'] ?? '');
      ensure_backup($path);
      @mkdir(dirname($path), 0775, true);
      if ($mode === 'append') {
        file_put_contents($path, $content, FILE_APPEND);
      } elseif ($mode === 'insert') {
        $off = (int)($a['insert_offset'] ?? 0);
        $old = is_file($path) ? file_get_contents($path) : '';
        $new = mb_substr((string)$old, 0, $off) . $content . mb_substr((string)$old, $off);
        file_put_contents($path, $new);
      } else {
        file_put_contents($path, $content);
      }
      return ['path'=>$path, 'bytes'=>strlen($content), 'mode'=>$mode];
    },
    'db.select' => function(array $a) use ($db): array {
      $sql = trim((string)($a['sql'] ?? ''));
      if ($sql === '' || !preg_match('/^\s*SELECT/i', $sql)) {
        throw new RuntimeException('ONLY_SELECT_ALLOWED');
      }
      $params = is_array($a['params'] ?? null) ? $a['params'] : [];
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $rows = $stmt->fetchAll();
      return ['rows'=> $rows, 'count'=> count($rows)];
    },
    'db.exec'   => function(array $a) use ($db): array {
      $allow = (bool)($a['allow_write'] ?? false);
      if (!$allow) throw new RuntimeException('ALLOW_WRITE_REQUIRED');
      $sql = trim((string)($a['sql'] ?? ''));
      if ($sql === '') throw new RuntimeException('SQL_REQUIRED');
      $params = is_array($a['params'] ?? null) ? $a['params'] : [];
      $stmt = $db->prepare($sql);
      $ok = $stmt->execute($params);
      return ['ok'=>$ok, 'affected'=>$stmt->rowCount()];
    },
    'logs.tail' => function(array $a): array {
      $path = secure_path((string)($a['path'] ?? 'logs'));
      if (is_dir($path)) {
        $files = glob(rtrim($path,'/').'/*.log');
        return ['dir'=>$path, 'files'=>$files];
      }
      if (!is_file($path)) throw new RuntimeException('LOG_NOT_FOUND: '.$path);
      $lines = (int)($a['lines'] ?? 200);
      $data = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
      $slice = array_slice($data, -$lines);
      return ['path'=>$path, 'lines'=>$slice, 'count'=>count($slice)];
    },
    'http.fetch'=> function(array $a): array {
      $url = (string)($a['url'] ?? '');
      if ($url === '' || !preg_match('#^https?://#i',$url)) throw new RuntimeException('URL_REQUIRED');
      // Allowlist: same host by default
      $host = parse_url($url, PHP_URL_HOST);
      $self = $_SERVER['HTTP_HOST'] ?? '';
      $allow = env('HTTP_FETCH_ALLOW_HOSTS','') ?: $self;
      $allowed = array_filter(array_map('trim', explode(',', (string)$allow)));
      if (!in_array($host, $allowed, true)) throw new RuntimeException('HOST_NOT_ALLOWED: '.$host);
      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20
      ]);
      $raw = curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($raw === false || $code >= 400) {
        $err = $raw ? substr((string)$raw, 0, 300) : curl_error($ch);
        throw new RuntimeException("HTTP {$code}: ".$err);
      }
      return ['url'=>$url, 'status'=>$code, 'body'=>$raw];
    },

    // Password Storage Tool
    'password.store' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/PasswordStorageTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\PasswordStorageTool();
      return $tool->execute(array_merge(['action' => 'store'], $a));
    },
    'password.retrieve' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/PasswordStorageTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\PasswordStorageTool();
      return $tool->execute(array_merge(['action' => 'retrieve'], $a));
    },
    'password.list' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/PasswordStorageTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\PasswordStorageTool();
      return $tool->execute(['action' => 'list']);
    },
    'password.delete' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/PasswordStorageTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\PasswordStorageTool();
      return $tool->execute(array_merge(['action' => 'delete'], $a));
    },

    // MySQL Query Tool
    'mysql.query' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/MySQLQueryTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\MySQLQueryTool();
      return $tool->execute($a);
    },
    'mysql.common_queries' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/MySQLQueryTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\MySQLQueryTool();
      return $tool->execute(['action' => 'common_queries']);
    },

    // Web Browser Tool
    'browser.fetch' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/WebBrowserTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\WebBrowserTool();
      return $tool->execute(array_merge(['action' => 'fetch'], $a));
    },
    'browser.extract' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/WebBrowserTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\WebBrowserTool();
      return $tool->execute(array_merge(['action' => 'extract'], $a));
    },
    'browser.headers' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/WebBrowserTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\WebBrowserTool();
      return $tool->execute(array_merge(['action' => 'headers'], $a));
    },

    // Crawler Tool
    'crawler.deep_crawl' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/CrawlerTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\CrawlerTool();
      return $tool->execute(array_merge(['action' => 'deep_crawl'], $a));
    },
    'crawler.single_page' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/CrawlerTool.php';
      $tool = new \IntelligenceHub\MCP\Tools\CrawlerTool();
      return $tool->execute(array_merge(['action' => 'single_page'], $a));
    },

    // ULTRA SCANNER - Comprehensive Code Intelligence (ALL 18 TABLES)
    'ultra_scanner.scan_file' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/UltraScanner.php';
      $tool = new \IntelligenceHub\MCP\Tools\UltraScanner();
      return $tool->execute(array_merge(['_method' => 'scan_file'], $a));
    },
    'ultra_scanner.scan_project' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/UltraScanner.php';
      $tool = new \IntelligenceHub\MCP\Tools\UltraScanner();
      return $tool->execute(array_merge(['_method' => 'scan_project'], $a));
    },
    'ultra_scanner.get_stats' => function(array $a): array {
      require_once $_SERVER['DOCUMENT_ROOT'].'/mcp/src/Tools/UltraScanner.php';
      $tool = new \IntelligenceHub\MCP\Tools\UltraScanner();
      return $tool->execute(['_method' => 'get_stats']);
    },
  ];

  if (!isset($TOOLS[$tool])) {
    throw new RuntimeException('UNKNOWN_TOOL: '.$tool);
  }

  try {
    $result = $TOOLS[$tool]($args);
    $lat = (int)((microtime(true) - $t0)*1000);
    (new Telemetry($db))->toolFinish($toolCallId, 'ok', null, $lat, null, null, $result);
    (new Telemetry($db))->toolFlatStream($tool, $args, $lat, true, null, $sessionId, $conversationId, $messageId, $rid, is_array($result) ? count($result) : 0);
    envelope_success(['tool'=>$tool,'result'=>$result,'latency_ms'=>$lat], $rid, 200);
  } catch (Throwable $te) {
    $lat = (int)((microtime(true) - $t0)*1000);
    (new Telemetry($db))->toolFinish($toolCallId, 'error', $te->getCode() ? (string)$te->getCode() : 'EX', $lat);
    (new Telemetry($db))->toolFlatStream($tool, $args, $lat, false, $te->getMessage(), $sessionId, $conversationId, $messageId, $rid, 0);
    envelope_error('TOOL_ERROR', $te->getMessage(), $rid, [], 500);
  }

} catch (Throwable $e) {
  envelope_error('INVOKE_FAILURE', $e->getMessage(), $rid, ['trace'=>substr($e->getTraceAsString(),0,800)], 500);
}
