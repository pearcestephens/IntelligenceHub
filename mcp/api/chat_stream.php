<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/Bootstrap.php';
require_once __DIR__.'/../lib/Telemetry.php';
require_once __DIR__.'/../lib/MemoryStore.php';
require_once __DIR__.'/../lib/ProviderFactory.php';
require_once __DIR__.'/../lib/StreamStore.php';

$rid = new_request_id();

// SSE headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Accel-Buffering: no');
header('Connection: keep-alive');
if (ob_get_level()) { ob_end_clean(); }

function sse_send(string $event, $data): void {
  echo "event: {$event}\n";
  echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
  if (ob_get_level()) { ob_flush(); }
  flush();
}

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sse_send('error', ['code'=>'METHOD_NOT_ALLOWED','message'=>'Use POST']);
    exit;
  }

  $db = get_pdo();
  $domainId = require_api_key_if_enabled($db); // optional auth
  $tele = new Telemetry($db);
  $mem  = new MemoryStore($db);
  $streamStore = new StreamStore($db);

  $in = req_json();
  $sessionKey     = (string)($in['session_key'] ?? bin2hex(random_bytes(16)));
  $platform       = (string)($in['platform'] ?? 'github_copilot');
  $userIdentifier = isset($in['user_identifier']) ? (string)$in['user_identifier'] : null;
  $userId         = isset($in['user_id']) ? (int)$in['user_id'] : null;
  $orgId          = (int)($in['org_id'] ?? 1);
  $unitId         = isset($in['unit_id']) ? (int)$in['unit_id'] : (env('AI_DEFAULT_UNIT_ID') ? (int)env('AI_DEFAULT_UNIT_ID') : null);
  $projectId      = isset($in['project_id']) ? (int)$in['project_id'] : (env('AI_DEFAULT_PROJECT_ID') ? (int)env('AI_DEFAULT_PROJECT_ID') : null);
  $sourceBot      = isset($in['bot']) ? (string)$in['bot'] : (env('AI_DEFAULT_BOT') ?: null);
  $correlationId  = isset($in['correlation_id']) ? (string)$in['correlation_id'] : null;
  $forceConvId    = isset($in['conversation_id']) ? (int)$in['conversation_id'] : null;

  $message        = trim((string)($in['message'] ?? ''));
  $system         = isset($in['system']) ? (string)$in['system'] : ((env('AI_AGENT_DEFAULT_SYSTEM') ?: 'You are Ecigdis Assistant. Timezone: Pacific/Auckland.'));
  $history        = is_array($in['history'] ?? null) ? $in['history'] : [];
  $attachments    = is_array($in['attachments'] ?? null) ? $in['attachments'] : null;
  $provider       = (string)($in['provider'] ?? 'openai');
  $model          = (string)($in['model'] ?? ($provider === 'openai' ? 'gpt-4o-mini' : 'claude-3-5-sonnet-latest'));
  $temperature    = isset($in['temperature']) ? (float)$in['temperature'] : 0.2;

  if ($message === '') { sse_send('error', ['code'=>'INVALID_INPUT','message'=>'message is required']); exit; }

  // Upsert convo
  $conversationId = upsert_conversation_ext($db, [
    'conversation_id' => $forceConvId,
    'session_id'      => $sessionKey,
    'platform'        => $platform,
    'user_identifier' => $userIdentifier,
    'org_id'          => $orgId,
    'unit_id'         => $unitId,
    'project_id'      => $projectId,
    'source'          => $sourceBot,
    'correlation_id'  => $correlationId,
    'status'          => 'active',
  ]);
  $seq = next_message_sequence($db, $conversationId);

  // Memory injection
  $memItems = $mem->retrieveForUser($userId, 8);
  if (!empty($memItems)) { $system .= "\n\nKnown memory: ".json_encode($memItems, JSON_UNESCAPED_UNICODE); }

  // Build messages
  $messages = [];
  $messages[] = ['role'=>'system','content'=>$system];
  foreach ($history as $m) {
    if (!isset($m['role'], $m['content'])) continue;
    $r = in_array($m['role'], ['system','user','assistant'], true) ? $m['role'] : 'user';
    $messages[] = ['role'=>$r, 'content'=>(string)$m['content']];
  }
  $messages[] = ['role'=>'user','content'=>$message];

  // Log user message
  $userMsgId = $tele->logUserMessage($conversationId, $seq, $message, $attachments);

  // Announce start
  sse_send('start', [
    'request_id' => $rid,
    'conversation_id' => $conversationId,
    'user_message_id' => $userMsgId,
    'provider' => $provider,
    'model' => $model
  ]);

  // Start DB stream session
  $sessionDbId = $streamStore->start([
    'conversation_id' => $conversationId,
    'provider' => $provider,
    'model' => $model,
    'platform' => $platform,
    'session_key' => $sessionKey,
    'request_id' => $rid,
    'correlation_id' => $correlationId,
    'user_message_id' => $userMsgId,
    'unit_id' => $unitId,
    'project_id' => $projectId,
    'source' => $sourceBot,
  ]);
  $chunkSeq = 1;

  $openaiKey   = env('OPENAI_API_KEY');
  $anthropicKey= env('ANTHROPIC_API_KEY');

  $assistantText = '';
  $promptTok=null; $compTok=null; $totalTok=null; $latencyMs=0; $endpoint='';
  $providerRequestId=$rid; $providerResponseId=null;

  $t0 = microtime(true);

  if ($provider === 'openai') {
    if (!$openaiKey) throw new RuntimeException('OPENAI_API_KEY not configured');
    $streamCall = ProviderFactory::openaiStream($openaiKey, function(string $delta) use ($streamStore, $sessionDbId, &$chunkSeq) {
      $streamStore->appendChunk($sessionDbId, $chunkSeq++, $delta, 'delta');
      sse_send('delta', ['content'=>$delta]);
    });
    $resp = $streamCall([
      'model'=>$model,
      'messages'=>$messages,
      'temperature'=>$temperature
    ]);
    $endpoint = $resp['_endpoint'] ?? 'openai';
    $latencyMs = (int)($resp['_latency_ms'] ?? 0);
    $assistantText = (string)($resp['content'] ?? '');
  } else {
    if (!$anthropicKey) throw new RuntimeException('ANTHROPIC_API_KEY not configured');
    $anthropicMsgs = ProviderFactory::toAnthropicMessages($messages);
    $streamCall = ProviderFactory::anthropicStream($anthropicKey, function(string $delta) use ($streamStore, $sessionDbId, &$chunkSeq){
      $streamStore->appendChunk($sessionDbId, $chunkSeq++, $delta, 'delta');
      sse_send('delta', ['content'=>$delta]);
    });
    $resp = $streamCall([
      'model'=>$model,
      'system'=>$system,
      'messages'=>$anthropicMsgs,
      'temperature'=>$temperature
    ]);
    $endpoint = $resp['_endpoint'] ?? 'anthropic';
    $latencyMs = (int)($resp['_latency_ms'] ?? 0);
    $assistantText = (string)($resp['content'] ?? '');
  }

  // Cost calc
  $inRate  = (float)(env(strtoupper($provider).'_IN_NZD_PER_1K')  ?? '0');
  $outRate = (float)(env(strtoupper($provider).'_OUT_NZD_PER_1K') ?? '0');
  $costCents=0;
  if ($promptTok !== null || $compTok !== null) {
    $costNZD = (($promptTok ?? 0) * $inRate / 1000.0) + (($compTok ?? 0) * $outRate / 1000.0);
    $costCents = (int)round($costNZD * 100);
  }

  // Log assistant message
  $assistantSeq = $seq + 1;
  $assistantMsgId = $tele->logAssistantMessage(
    $conversationId, $assistantSeq, $provider, $model, $assistantText,
    $promptTok !== null ? (int)$promptTok : null,
    $compTok   !== null ? (int)$compTok   : null,
    $providerRequestId, $providerResponseId
  );

  // Finish DB stream session
  $streamStore->finish($sessionDbId, 'done', $assistantMsgId, null);

  // Telemetry
  $tele->logProviderRequest(
    $domainId,
    $provider,
    $conversationId,
    $userMsgId,
    $providerRequestId,
    $model,
    $endpoint,
    $promptTok, $compTok, $totalTok, $costCents,
    (int)round((microtime(true)-$t0)*1000), 'success', null,
    ['ua'=>$_SERVER['HTTP_USER_AGENT'] ?? null, 'correlation_id'=>$correlationId],
    ['temperature'=>$temperature],
    null,
    $unitId, $projectId, $sourceBot
  );

  // Done
  sse_send('done', [
    'conversation_id' => $conversationId,
    'assistant_message_id' => $assistantMsgId,
    'tokens' => ['in'=>$promptTok,'out'=>$compTok,'total'=>$totalTok],
    'cost_nzd_cents' => $costCents,
    'latency_ms' => $latencyMs
  ]);

} catch (Throwable $e) {
  try {
    if (isset($streamStore, $sessionDbId)) {
      $streamStore->finish($sessionDbId, 'error', null, $e->getMessage());
    }
  } catch (Throwable $inner) {
    // ignore secondary failure
  }
  sse_send('error', ['code'=>'CHAT_STREAM_FAILURE','message'=>$e->getMessage()]);
  sse_send('done', ['status'=>'error']);
}
