<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/Bootstrap.php';
require_once __DIR__.'/../lib/Telemetry.php';
require_once __DIR__.'/../lib/MemoryStore.php';
require_once __DIR__.'/../lib/ProviderFactory.php';

$rid = new_request_id();

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    envelope_error('METHOD_NOT_ALLOWED', 'Use POST with application/json', $rid, [], 405);
    exit;
  }

  $db = get_pdo();
  $domainId = require_api_key_if_enabled($db); // no-op if not enforced
  $tele = new Telemetry($db);
  $mem  = new MemoryStore($db);

  $in = req_json();
  $sessionKey     = (string)($in['session_key'] ?? bin2hex(random_bytes(16)));
  $platform       = (string)($in['platform'] ?? 'github_copilot');
  $userIdentifier = isset($in['user_identifier']) ? (string)$in['user_identifier'] : null;
  $userId         = isset($in['user_id']) ? (int)$in['user_id'] : null;
  // Org context / identifiers
  $orgId          = isset($in['org_id']) ? (int)$in['org_id'] : 1;
  $unitId         = isset($in['unit_id']) ? (int)$in['unit_id'] : null;
  $projectId      = isset($in['project_id']) ? (int)$in['project_id'] : null;
  $sourceBot      = isset($in['bot']) ? (string)$in['bot'] : (isset($in['source']) ? (string)$in['source'] : null);
  $correlationId  = isset($in['correlation_id']) ? (string)$in['correlation_id'] : null;
  $forceConvId    = isset($in['conversation_id']) ? (int)$in['conversation_id'] : null;

  // Fallbacks from environment (per-domain or global) if client didn't provide
  if ($unitId === null && env('AI_DEFAULT_UNIT_ID')) {
    $unitId = (int)env('AI_DEFAULT_UNIT_ID');
  }
  if ($projectId === null && env('AI_DEFAULT_PROJECT_ID')) {
    $projectId = (int)env('AI_DEFAULT_PROJECT_ID');
  }
  if ($sourceBot === null && env('AI_DEFAULT_BOT')) {
    $sourceBot = (string)env('AI_DEFAULT_BOT');
  }
  $message        = trim((string)($in['message'] ?? ''));
  // System prompt: client-provided takes priority; otherwise allow env override before falling back
  $system         = isset($in['system'])
    ? (string)$in['system']
    : ((env('AI_AGENT_DEFAULT_SYSTEM') ?: 'You are Ecigdis Assistant. Timezone: Pacific/Auckland.'));
  $history        = is_array($in['history'] ?? null) ? $in['history'] : [];
  $attachments    = is_array($in['attachments'] ?? null) ? $in['attachments'] : null; // array of attachment metadata from upload.php
  $provider       = (string)($in['provider'] ?? 'openai'); // openai | anthropic
  $model          = (string)($in['model'] ?? ($provider === 'openai' ? 'gpt-4o-mini' : 'claude-3-5-sonnet-latest'));
  $temperature    = isset($in['temperature']) ? (float)$in['temperature'] : 0.2;

  if ($message === '') {
    envelope_error('INVALID_INPUT', 'message is required', $rid, [], 422);
    exit;
  }

  // Conversation
  // Upsert conversation with extended identifiers
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

  // Optional: memory injection
  $memItems = $mem->retrieveForUser($userId, 8);
  if (!empty($memItems)) {
    $system .= "\n\nKnown memory: ".json_encode($memItems, JSON_UNESCAPED_UNICODE);
  }

  // Build OpenAI-style messages (system + history + user)
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

  // Provider call
  $openaiKey   = env('OPENAI_API_KEY');
  $anthropicKey= env('ANTHROPIC_API_KEY');

  $resp = [];
  $latencyMs = 0;
  $endpoint  = '';
  $assistantText = '';
  $promptTok = null; $compTok = null; $totalTok = null;
  $providerRequestId = $rid; $providerResponseId = null;

  if ($provider === 'openai') {
    if (!$openaiKey) throw new RuntimeException('OPENAI_API_KEY not configured');
    $call = ProviderFactory::openai($openaiKey);
    $resp = $call([
      'apiKey' => $openaiKey,
      'model'  => $model,
      'messages'=> $messages,
      'temperature' => $temperature
    ]);
    $endpoint = $resp['_endpoint'] ?? 'openai';
    $latencyMs= (int)($resp['_latency_ms'] ?? 0);
    $assistantText = (string)($resp['choices'][0]['message']['content'] ?? '');
    $promptTok = $resp['usage']['prompt_tokens'] ?? null;
    $compTok   = $resp['usage']['completion_tokens'] ?? null;
    $totalTok  = $resp['usage']['total_tokens'] ?? (($promptTok ?? 0) + ($compTok ?? 0));
    $providerRequestId  = (string)($resp['id'] ?? $rid);
    $providerResponseId = $providerRequestId;
  } else {
    if (!$anthropicKey) throw new RuntimeException('ANTHROPIC_API_KEY not configured');
    $anthropicMsgs = ProviderFactory::toAnthropicMessages($messages);
    $call = ProviderFactory::anthropic($anthropicKey);
    $resp = $call([
      'apiKey' => $anthropicKey,
      'model'  => $model,
      'system' => $system,
      'messages'=> $anthropicMsgs,
      'temperature' => $temperature
    ]);
    $endpoint  = $resp['_endpoint'] ?? 'anthropic';
    $latencyMs = (int)($resp['_latency_ms'] ?? 0);
    $assistantText = (string)($resp['content'][0]['text'] ?? '');
    $promptTok = $resp['usage']['input_tokens']  ?? null;
    $compTok   = $resp['usage']['output_tokens'] ?? null;
    $totalTok  = $promptTok !== null && $compTok !== null ? ($promptTok + $compTok) : null;
    $providerRequestId  = $rid; // Anthropic doesn't echo an ID the same way; keep rid
    $providerResponseId = $resp['id'] ?? null;
  }

  // Costing (optional): set env OPENAI_IN_NZD_PER_1K, OPENAI_OUT_NZD_PER_1K, ANTHROPIC_IN_* / OUT_*
  $inRate  = (float)(env(strtoupper($provider).'_IN_NZD_PER_1K')  ?? '0');
  $outRate = (float)(env(strtoupper($provider).'_OUT_NZD_PER_1K') ?? '0');
  $costCents = 0;
  if ($promptTok !== null || $compTok !== null) {
    $costNZD = (($promptTok ?? 0) * $inRate / 1000.0) + (($compTok ?? 0) * $outRate / 1000.0);
    $costCents = (int)round($costNZD * 100);
  }

  // Log provider request
  $tele->logProviderRequest(
    $domainId,
    $provider,
    $conversationId,
    $userMsgId,
    $providerRequestId,
    $model,
    $endpoint,
    $promptTok, $compTok, $totalTok, $costCents,
    $latencyMs, 'success', null,
    ['ua' => $_SERVER['HTTP_USER_AGENT'] ?? null, 'correlation_id' => $correlationId],
    ['temperature' => $temperature],
    $resp,
    $unitId, $projectId, $sourceBot
  );

  // Log assistant message
  $assistantSeq = $seq + 1;
  $assistantMsgId = $tele->logAssistantMessage(
    $conversationId, $assistantSeq, $provider, $model, $assistantText,
    $promptTok !== null ? (int)$promptTok : null,
    $compTok   !== null ? (int)$compTok   : null,
    $providerRequestId, $providerResponseId
  );

  // Optional memory upserts from request
  if (!empty($in['memory_upserts']) && is_array($in['memory_upserts'])) {
    foreach ($in['memory_upserts'] as $mu) {
      if (!is_array($mu)) continue;
      $scope = (string)($mu['scope'] ?? 'user');
      $key   = (string)($mu['key'] ?? '');
      $val   = $mu['value'] ?? null;
      if ($key !== '' && $val !== null) {
        $mem->upsert($scope, $userId, $conversationId, $mu['project'] ?? null, $key, (array)$val, 'assistant', (int)($mu['confidence'] ?? 80));
      }
    }
  }

  envelope_success([
    'session_key' => $sessionKey,
    'conversation_id' => $conversationId,
    'correlation_id' => $correlationId,
    'user_message_id' => $userMsgId,
    'assistant_message_id' => $assistantMsgId,
    'unit_id' => $unitId,
    'project_id' => $projectId,
    'bot' => $sourceBot,
    'provider' => $provider,
    'model'    => $model,
    'tokens'   => ['in'=>$promptTok, 'out'=>$compTok, 'total'=>$totalTok],
    'cost_nzd_cents' => $costCents,
    'latency_ms' => $latencyMs,
    'content'  => $assistantText
  ], $rid, 200);

} catch (Throwable $e) {
  envelope_error('CHAT_FAILURE', $e->getMessage(), $rid, ['trace'=>substr($e->getTraceAsString(),0,800)], 500);
}
