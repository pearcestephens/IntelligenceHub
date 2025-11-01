#!/usr/bin/env bash
# Streaming tool-call smoke test: prompts model to call http_tool while streaming
# Usage: ./stream_toolcall.sh [URL]
set -euo pipefail
cd "$(dirname "$0")/.."

URL=${1:-"https://staff.vapeshed.co.nz"}
MSG="While streaming, use http_tool to GET ${URL} and then continue your answer."
ENC_MSG=$(php -r 'echo json_encode($argv[1]);' -- "$MSG")

JSON_PAYLOAD='{'
JSON_PAYLOAD+="\"message\": ${ENC_MSG}, "
JSON_PAYLOAD+='"stream": true, "enable_tools": true}'

export METHOD=POST
export JSON="$JSON_PAYLOAD"

php public/agent/api/chat.php | sed -n '1,300p'
