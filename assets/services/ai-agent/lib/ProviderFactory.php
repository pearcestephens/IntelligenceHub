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
        CURLOPT_TIMEOUT => 15,  // REDUCED FROM 60s - SPEED FIX
        CURLOPT_CONNECTTIMEOUT => 5  // ADD CONNECTION TIMEOUT
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
        CURLOPT_TIMEOUT => 15,  // REDUCED FROM 60s - SPEED FIX
        CURLOPT_CONNECTTIMEOUT => 5  // ADD CONNECTION TIMEOUT
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

  // Streaming support: call with a callback that receives text deltas as they arrive
  public static function openaiStream(string $apiKey, callable $onDelta): callable {
    return function(array $payload) use ($apiKey, $onDelta): array {
      $url = 'https://api.openai.com/v1/chat/completions';
      $body = [
        'model' => $payload['model'] ?? 'gpt-4o-mini',
        'messages' => $payload['messages'],
        'temperature' => $payload['temperature'] ?? 0.2,
        'stream' => true
      ];
      $t0 = microtime(true);
      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => false, // we'll stream
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
          "Authorization: Bearer {$apiKey}",
          "Content-Type: application/json",
          "Accept: text/event-stream"
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 30,  // REDUCED FROM 300s - SPEED FIX
        CURLOPT_CONNECTTIMEOUT => 5  // ADD CONNECTION TIMEOUT
      ]);

      $aggregate = '';
      $buffer = '';
      curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use (&$buffer, &$aggregate, $onDelta) {
        $buffer .= $chunk;
        // Split by SSE frame separator (\n\n)
        $parts = explode("\n\n", $buffer);
        $buffer = array_pop($parts);
        foreach ($parts as $frame) {
          foreach (explode("\n", $frame) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, ':')) continue; // comments/keepalive
            if (str_starts_with($line, 'data: ')) {
              $json = substr($line, 6);
              if ($json === '[DONE]') { continue; }
              $obj = json_decode($json, true);
              if (isset($obj['choices'][0]['delta']['content'])) {
                $delta = (string)$obj['choices'][0]['delta']['content'];
                if ($delta !== '') {
                  $aggregate .= $delta;
                  try { $onDelta($delta); } catch(\Throwable $e) { /* ignore */ }
                }
              }
            }
          }
        }
        return strlen($chunk);
      });

      $ok = curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $lat = (int)((microtime(true) - $t0) * 1000);
      if ($ok === false || $code >= 400) {
        $err = curl_error($ch);
        throw new \RuntimeException("OpenAI stream HTTP {$code}: ".$err);
      }
      curl_close($ch);
      return ['_endpoint'=>$url, '_latency_ms'=>$lat, 'content'=>$aggregate];
    };
  }

  public static function anthropicStream(string $apiKey, callable $onDelta): callable {
    return function(array $payload) use ($apiKey, $onDelta): array {
      $url = 'https://api.anthropic.com/v1/messages';
      $body = [
        'model' => $payload['model'] ?? 'claude-3-5-sonnet-latest',
        'max_tokens' => $payload['max_tokens'] ?? 2048,
        'system' => $payload['system'] ?? null,
        'messages' => $payload['messages'],
        'temperature' => $payload['temperature'] ?? 0.2,
        'stream' => true
      ];
      $t0 = microtime(true);
      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
          "x-api-key: {$apiKey}",
          "anthropic-version: 2023-06-01",
          "content-type: application/json",
          "accept: text/event-stream"
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 30,  // REDUCED FROM 300s - SPEED FIX
        CURLOPT_CONNECTTIMEOUT => 5  // ADD CONNECTION TIMEOUT
      ]);

      $aggregate = '';
      $buffer = '';
      curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use (&$buffer, &$aggregate, $onDelta) {
        $buffer .= $chunk;
        $parts = explode("\n\n", $buffer);
        $buffer = array_pop($parts);
        foreach ($parts as $frame) {
          $event = null; $data = '';
          foreach (explode("\n", $frame) as $line) {
            if (str_starts_with($line, 'event: ')) $event = trim(substr($line, 7));
            elseif (str_starts_with($line, 'data: ')) $data .= substr($line, 6);
          }
          if ($event === 'content_block_delta') {
            $obj = json_decode($data, true);
            $delta = (string)($obj['delta']['text'] ?? '');
            if ($delta !== '') { $aggregate .= $delta; try { $onDelta($delta); } catch(\Throwable $e){} }
          }
        }
        return strlen($chunk);
      });

      $ok = curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $lat = (int)((microtime(true) - $t0) * 1000);
      if ($ok === false || $code >= 400) {
        $err = curl_error($ch);
        throw new \RuntimeException("Anthropic stream HTTP {$code}: ".$err);
      }
      curl_close($ch);
      return ['_endpoint'=>$url, '_latency_ms'=>$lat, 'content'=>$aggregate];
    };
  }
}
