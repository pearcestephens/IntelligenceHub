#!/usr/bin/env bash
# Non-streaming smoke test for chat endpoint via CLI
# Usage: ./nonstream.sh "Your message here" [conversation_id]
set -euo pipefail
cd "$(dirname "$0")/.."

MSG=${1:-"Say something witty about tools."}
CONV_ID=${2:-}
ENC_MSG=$(php -r 'echo json_encode($argv[1]);' -- "$MSG")

if [ -n "$CONV_ID" ]; then
  JSON_PAYLOAD='{'
  JSON_PAYLOAD+="\"message\": ${ENC_MSG}, "
  JSON_PAYLOAD+="\"conversation_id\": \"${CONV_ID}\", "
  JSON_PAYLOAD+='"stream": false, "enable_tools": true}'
else
  JSON_PAYLOAD='{'
  JSON_PAYLOAD+="\"message\": ${ENC_MSG}, "
  JSON_PAYLOAD+='"stream": false, "enable_tools": true}'
fi

export METHOD=POST
export JSON="$JSON_PAYLOAD"

php public/agent/api/chat.php | sed -n '1,200p'
