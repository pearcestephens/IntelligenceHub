#!/bin/bash

API_KEY="31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35"
SERVER="https://gpt.ecigdis.co.nz/mcp/server_v3.php"

echo "================================================================"
echo " MCP SERVER v3 - COMPREHENSIVE TOOL TEST"
echo "================================================================"
echo ""

test_tool() {
  local tool=$1
  local args=$2
  local timeout=${3:-5}

  result=$(timeout $timeout curl -s -X POST "$SERVER" \
    -H "Content-Type: application/json" \
    -H "X-API-Key: $API_KEY" \
    -d "{\"jsonrpc\":\"2.0\",\"id\":1,\"method\":\"tools/call\",\"params\":{\"name\":\"$tool\",\"arguments\":$args}}" 2>/dev/null)

  status=$(echo "$result" | jq -r '.result.status // empty' 2>/dev/null)
  error=$(echo "$result" | jq -r '.error.message // .result.data.error // empty' 2>/dev/null)

  if [ -z "$result" ]; then
    printf "%-35s ❌ TIMEOUT\n" "$tool"
  elif [ -n "$status" ]; then
    if [ "$status" = "200" ]; then
      printf "%-35s ✅ %s\n" "$tool" "$status"
    elif [ "$status" = "404" ]; then
      printf "%-35s ❌ %s (file not found)\n" "$tool" "$status"
    elif [ "$status" = "500" ]; then
      printf "%-35s ❌ %s (server error)\n" "$tool" "$status"
    else
      printf "%-35s ⚠️  %s\n" "$tool" "$status"
    fi
  elif [ -n "$error" ]; then
    printf "%-35s ❌ %s\n" "$tool" "$error"
  else
    printf "%-35s ❓ UNKNOWN\n" "$tool"
  fi
}

echo "=== AI AGENT & CHAT TOOLS ==="
test_tool "ai_agent.query" '{"query":"test","stream":false}' 10
test_tool "chat.send" '{"message":"test"}' 10
test_tool "chat.send_stream" '{"message":"test"}' 10
test_tool "chat.summarize" '{"conversation_id":"test"}' 10

echo ""
echo "=== CONVERSATION & MEMORY TOOLS ==="
test_tool "conversation.list" '{}' 5
test_tool "conversation.get_project_context" '{"limit":1}' 10
test_tool "conversation.search" '{"query":"test","limit":1}' 10
test_tool "memory.store" '{"content":"test","memory_type":"note","importance":"low","tags":["test"]}' 5
test_tool "memory.search" '{"query":"test","limit":1}' 10
test_tool "memory.get_by_tag" '{"tag":"test","limit":1}' 5

echo ""
echo "=== KNOWLEDGE BASE TOOLS ==="
test_tool "kb.add_document" '{"title":"test","content":"test content","type":"note"}' 5
test_tool "kb.search" '{"query":"test","limit":1}' 10
test_tool "kb.get_document" '{"id":1}' 5
test_tool "kb.list_documents" '{"page":1,"limit":10}' 5

echo ""
echo "=== SEMANTIC SEARCH TOOLS ==="
test_tool "semantic_search" '{"query":"database","limit":2}' 15
test_tool "analyze_file" '{"path":"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env","analysis_type":"summary"}' 10
test_tool "find_code" '{"pattern":"function","file_pattern":"*.php","limit":2}' 10

echo ""
echo "=== DATABASE TOOLS ==="
test_tool "db.query_readonly" '{"sql":"SELECT 1 as test"}' 5
test_tool "db.stats" '{}' 5
test_tool "db.explain" '{"sql":"SELECT * FROM users LIMIT 1"}' 5

echo ""
echo "=== FILE SYSTEM TOOLS ==="
test_tool "fs.list" '{"path":"/tmp","recursive":false}' 5
test_tool "fs.read" '{"path":"/tmp/test.txt","max_lines":10}' 5
test_tool "fs.write" '{"path":"/tmp/mcp_test.txt","content":"test"}' 5
test_tool "fs.delete" '{"path":"/tmp/mcp_test.txt"}' 5
test_tool "fs.mkdir" '{"path":"/tmp/mcp_test_dir"}' 5

echo ""
echo "=== SSH/EXEC TOOLS ==="
test_tool "ssh.exec" '{"command":"whoami"}' 10
test_tool "ssh.upload" '{"local_path":"/tmp/test.txt","remote_path":"/tmp/remote.txt"}' 10
test_tool "ssh.download" '{"remote_path":"/tmp/test.txt","local_path":"/tmp/downloaded.txt"}' 10

echo ""
echo "=== OPERATIONS TOOLS ==="
test_tool "ops.monitoring_snapshot" '{}' 10
test_tool "ops.service_status" '{"service":"nginx"}' 10
test_tool "ops.disk_usage" '{"path":"/home"}' 5
test_tool "ops.process_list" '{"filter":"php"}' 5

echo ""
echo "=== LOG TOOLS ==="
test_tool "logs.tail" '{"log":"/var/log/apache2/error.log","lines":10}' 5
test_tool "logs.grep" '{"log":"/var/log/apache2/error.log","pattern":"error","lines":10}' 5

echo ""
echo "=== GITHUB TOOLS ==="
test_tool "github.get_pr_info" '{"repo":"owner/repo","pr_number":1}' 5
test_tool "github.search_repos" '{"query":"test","limit":1}' 5
test_tool "github.comment_pr" '{"repo":"owner/repo","pr_number":1,"body":"test comment"}' 5
test_tool "github.label_pr" '{"repo":"owner/repo","pr_number":1,"labels":["bug"]}' 5
test_tool "github.get_pr_diff" '{"repo":"owner/repo","pr_number":1}' 5

echo ""
echo "================================================================"
echo " TEST COMPLETE"
echo "================================================================"
