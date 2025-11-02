<?php
declare(strict_types=1);

final class ProviderFactory {

  public static function openai(string $apiKey): callable {
    return function(array $payload): array {
      $url = 'https://api.openai.com/v1/chat/completions';
      $body = [
        'model' => $payload['model'] ?? 'gpt-4o-mini',
        'messages' => $payload['messages'],
        'temperature' => $payload['temperature'] ?? 0.2
      ];
      $t0 = microtime(true);
      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
          "Authorization: Bearer {$payload['apiKey']}",
          "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 60
      ]);
      $raw = curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $lat = (int)((microtime(true) - $t0) * 1000);
      if ($raw === false || $code >= 400) {
        $err = $raw ? substr((string)$raw, 0, 300) : curl_error($ch);
        throw new RuntimeException("OpenAI HTTP {$code}: ".$err);
      }
      $res = json_decode((string)$raw, true);
      if (!is_array($res)) throw new RuntimeException('OpenAI invalid JSON');
      $res['_latency_ms'] = $lat;
      $res['_endpoint'] = $url;
      return $res;
    };
  }

  public static function anthropic(string $apiKey): callable {
    return function(array $payload): array {
      $url = 'https://api.anthropic.com/v1/messages';
      $body = [
        'model' => $payload['model'] ?? 'claude-3-5-sonnet-latest',
        'max_tokens' => $payload['max_tokens'] ?? 2048,
        // Anthropic supports `system` as a separate field:
        'system' => $payload['system'] ?? null,
        'messages' => $payload['messages'],
        'temperature' => $payload['temperature'] ?? 0.2
      ];
      $t0 = microtime(true);
      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
          "x-api-key: {$payload['apiKey']}",
          "anthropic-version: 2023-06-01",
          "content-type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 60
      ]);
      $raw = curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $lat = (int)((microtime(true) - $t0) * 1000);
      if ($raw === false || $code >= 400) {
        $err = $raw ? substr((string)$raw, 0, 300) : curl_error($ch);
        throw new RuntimeException("Anthropic HTTP {$code}: ".$err);
      }
      $res = json_decode((string)$raw, true);
      if (!is_array($res)) throw new RuntimeException('Anthropic invalid JSON');
      $res['_latency_ms'] = $lat;
      $res['_endpoint'] = $url;
      return $res;
    };
  }

  public static function toAnthropicMessages(array $openaiMsgs): array {
    // Convert OpenAI-style to Anthropic messages (system goes separately)
    $out = [];
    foreach ($openaiMsgs as $m) {
      if (!isset($m['role'], $m['content'])) continue;
      $role = $m['role'] === 'assistant' ? 'assistant' : 'user';
      if ($m['role'] === 'system') {
        // handled via 'system' field; skip here
        continue;
      }
      $out[] = ['role' => $role, 'content' => (string)$m['content']];
    }
    return $out;
  }
}
