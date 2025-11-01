#!/usr/bin/env bash
# Streaming smoke test for chat endpoint via CLI (mysecureshell-safe)
# Usage: ./stream.sh "Your message here" [conversation_id]
set -euo pipefail
cd "$(dirname "$0")/.."

MSG=${1:-"Quick test: say hi."}
CONV_ID=${2:-}

# Safely JSON-encode the message via PHP to avoid shell escaping pitfalls
ENC_MSG=$(php -r 'echo json_encode($argv[1]);' -- "$MSG")

if [ -n "$CONV_ID" ]; then
  JSON_PAYLOAD='{'
  JSON_PAYLOAD+="\"message\": ${ENC_MSG}, "
  JSON_PAYLOAD+="\"conversation_id\": \"${CONV_ID}\", "
  JSON_PAYLOAD+='"stream": true}'
else
  JSON_PAYLOAD='{'
  JSON_PAYLOAD+="\"message\": ${ENC_MSG}, "
  JSON_PAYLOAD+='"stream": true}'
fi

export METHOD=POST
export JSON="$JSON_PAYLOAD"

php public/agent/api/chat.php