<?php

/**
 * AlertSlackPlugin - example handler plugin for alert.created events.
 */

declare(strict_types=1);

namespace App\Core\HandlerPlugins;

use App\Logger;

class AlertSlackPlugin
{
    public static function handle(array $event): void
    {
        $webhook = getenv('NEURO_SLACK_ALERT_WEBHOOK') ?: '';
        if ($webhook === '') {
            return; // disabled if not configured
        }
        $payload = [
            'text' => sprintf("[%s] %s %s value=%s severity=%s", strtoupper($event['payload']['source'] ?? 'alert'), $event['payload']['metric'] ?? 'metric', $event['type'], $event['payload']['value'] ?? 'n/a', $event['payload']['severity'] ?? 'n/a')
        ];
        $json = json_encode($payload);
        $ch = curl_init($webhook);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true,CURLOPT_POST => true,CURLOPT_POSTFIELDS => $json,CURLOPT_HTTPHEADER => ['Content-Type: application/json'],CURLOPT_TIMEOUT => 4]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($err || $code < 200 || $code >= 300) {
            Logger::error('alert.slack_plugin_failed', [ 'error' => $err,'code' => $code,'resp_preview' => substr((string)$resp, 0, 120)]);
        }
    }
}
