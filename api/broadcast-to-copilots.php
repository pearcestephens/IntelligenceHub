<?php
/**
 * Broadcast Instructions to All GitHub Copilot Instances
 * 
 * This creates a shared instruction file that ALL your open VS Code windows
 * can read and execute simultaneously.
 * 
 * Usage:
 *   curl -X POST "https://gpt.ecigdis.co.nz/api/broadcast-to-copilots.php" \
 *     -d "instruction=Review all error logs and fix critical issues" \
 *     -d "priority=HIGH" \
 *     -d "target=all"
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

define('BROADCAST_DIR', $_SERVER['DOCUMENT_ROOT'] . '/../private_html/copilot-broadcasts/');
define('ACTIVE_SESSIONS_FILE', BROADCAST_DIR . 'active-sessions.json');
define('LOG_FILE', $_SERVER['DOCUMENT_ROOT'] . '/logs/copilot-broadcasts.log');

// Ensure directories exist
if (!is_dir(BROADCAST_DIR)) {
    mkdir(BROADCAST_DIR, 0755, true);
}
if (!is_dir(dirname(LOG_FILE))) {
    mkdir(dirname(LOG_FILE), 0755, true);
}

class CopilotBroadcaster
{
    private string $broadcastId;
    private array $activeSessions = [];
    
    public function __construct()
    {
        $this->broadcastId = 'broadcast_' . time() . '_' . substr(md5(uniqid()), 0, 8);
        $this->loadActiveSessions();
    }
    
    public function handleRequest(): array
    {
        $action = $_REQUEST['action'] ?? 'broadcast';
        
        switch ($action) {
            case 'broadcast':
                return $this->broadcastInstruction();
            case 'register_session':
                return $this->registerSession();
            case 'get_instructions':
                return $this->getInstructions();
            case 'mark_complete':
                return $this->markComplete();
            case 'list_active':
                return $this->listActiveSessions();
            case 'get_status':
                return $this->getBroadcastStatus();
            default:
                return ['error' => 'Unknown action'];
        }
    }
    
    private function broadcastInstruction(): array
    {
        $instruction = $_POST['instruction'] ?? '';
        $priority = $_POST['priority'] ?? 'NORMAL';
        $target = $_POST['target'] ?? 'all'; // all, specific-window, specific-task
        $context = $_POST['context'] ?? [];
        
        if (empty($instruction)) {
            return [
                'success' => false,
                'error' => 'Instruction required'
            ];
        }
        
        $broadcast = [
            'id' => $this->broadcastId,
            'instruction' => $instruction,
            'priority' => $priority,
            'target' => $target,
            'context' => $context,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'status' => 'active',
            'acknowledged_by' => [],
            'completed_by' => []
        ];
        
        // Save broadcast file
        $broadcastFile = BROADCAST_DIR . $this->broadcastId . '.json';
        file_put_contents($broadcastFile, json_encode($broadcast, JSON_PRETTY_PRINT));
        
        // Create human-readable instruction file
        $readmeFile = BROADCAST_DIR . 'CURRENT_INSTRUCTION.md';
        $readme = $this->generateReadableInstruction($broadcast);
        file_put_contents($readmeFile, $readme);
        
        // Log broadcast
        $this->log("Broadcast created: {$this->broadcastId}", [
            'instruction' => $instruction,
            'priority' => $priority,
            'target' => $target,
            'active_sessions' => count($this->activeSessions)
        ]);
        
        return [
            'success' => true,
            'broadcast_id' => $this->broadcastId,
            'instruction' => $instruction,
            'priority' => $priority,
            'target' => $target,
            'active_copilot_sessions' => count($this->activeSessions),
            'broadcast_file' => $broadcastFile,
            'readable_file' => $readmeFile,
            'how_to_read' => [
                'From VS Code Copilot: "Read /private_html/copilot-broadcasts/CURRENT_INSTRUCTION.md and execute the instruction"',
                'From Terminal: "cat ' . $readmeFile . '"',
                'Via API: "GET ' . $_SERVER['HTTP_HOST'] . '/api/broadcast-to-copilots.php?action=get_instructions"'
            ]
        ];
    }
    
    private function registerSession(): array
    {
        $sessionId = $_POST['session_id'] ?? 'session_' . uniqid();
        $windowName = $_POST['window_name'] ?? 'Unnamed Window';
        $capabilities = $_POST['capabilities'] ?? ['general'];
        
        $session = [
            'session_id' => $sessionId,
            'window_name' => $windowName,
            'capabilities' => $capabilities,
            'registered_at' => date('Y-m-d H:i:s'),
            'last_ping' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];
        
        $this->activeSessions[$sessionId] = $session;
        $this->saveActiveSessions();
        
        $this->log("Session registered: {$sessionId}", [
            'window' => $windowName,
            'capabilities' => $capabilities
        ]);
        
        return [
            'success' => true,
            'session_id' => $sessionId,
            'message' => 'Session registered successfully',
            'total_active_sessions' => count($this->activeSessions)
        ];
    }
    
    private function getInstructions(): array
    {
        $sessionId = $_GET['session_id'] ?? $_POST['session_id'] ?? null;
        
        // Get all active broadcasts
        $broadcasts = [];
        $files = glob(BROADCAST_DIR . 'broadcast_*.json');
        
        foreach ($files as $file) {
            $broadcast = json_decode(file_get_contents($file), true);
            
            // Check if expired
            if (strtotime($broadcast['expires_at']) < time()) {
                unlink($file); // Clean up expired broadcast
                continue;
            }
            
            // Check if this session already completed it
            if ($sessionId && in_array($sessionId, $broadcast['completed_by'] ?? [])) {
                continue;
            }
            
            $broadcasts[] = $broadcast;
        }
        
        // Sort by priority
        usort($broadcasts, function($a, $b) {
            $priorities = ['LOW' => 1, 'NORMAL' => 2, 'HIGH' => 3, 'CRITICAL' => 4];
            return ($priorities[$b['priority']] ?? 2) <=> ($priorities[$a['priority']] ?? 2);
        });
        
        // If there are instructions, include a reminder for Copilot
        $copilotReminder = '';
        if (!empty($broadcasts)) {
            $topInstruction = $broadcasts[0];
            $copilotReminder = "\n🔔 NEW INSTRUCTION FROM YOUR USER:\n" .
                              "Priority: {$topInstruction['priority']}\n" .
                              "Task: {$topInstruction['instruction']}\n\n" .
                              "Please acknowledge and begin working on this task.";
        }
        
        return [
            'success' => true,
            'session_id' => $sessionId,
            'instructions' => $broadcasts,
            'count' => count($broadcasts),
            'current_instruction_file' => BROADCAST_DIR . 'CURRENT_INSTRUCTION.md',
            'copilot_reminder' => $copilotReminder,
            'has_pending_work' => !empty($broadcasts)
        ];
    }
    
    private function markComplete(): array
    {
        $broadcastId = $_POST['broadcast_id'] ?? null;
        $sessionId = $_POST['session_id'] ?? null;
        $result = $_POST['result'] ?? 'completed';
        
        if (!$broadcastId) {
            return ['success' => false, 'error' => 'broadcast_id required'];
        }
        
        $broadcastFile = BROADCAST_DIR . $broadcastId . '.json';
        
        if (!file_exists($broadcastFile)) {
            return ['success' => false, 'error' => 'Broadcast not found'];
        }
        
        $broadcast = json_decode(file_get_contents($broadcastFile), true);
        
        if ($sessionId) {
            $broadcast['completed_by'][] = $sessionId;
            $broadcast['completed_by'] = array_unique($broadcast['completed_by']);
        }
        
        // If all active sessions completed, mark as done
        if (count($broadcast['completed_by']) >= count($this->activeSessions)) {
            $broadcast['status'] = 'completed';
        }
        
        file_put_contents($broadcastFile, json_encode($broadcast, JSON_PRETTY_PRINT));
        
        $this->log("Broadcast marked complete: {$broadcastId}", [
            'session' => $sessionId,
            'result' => $result,
            'completed_by' => count($broadcast['completed_by']),
            'total_sessions' => count($this->activeSessions)
        ]);
        
        return [
            'success' => true,
            'broadcast_id' => $broadcastId,
            'session_id' => $sessionId,
            'completed_by' => count($broadcast['completed_by']),
            'total_sessions' => count($this->activeSessions),
            'status' => $broadcast['status']
        ];
    }
    
    private function listActiveSessions(): array
    {
        return [
            'success' => true,
            'active_sessions' => $this->activeSessions,
            'count' => count($this->activeSessions)
        ];
    }
    
    private function getBroadcastStatus(): array
    {
        $broadcastId = $_GET['broadcast_id'] ?? null;
        
        if (!$broadcastId) {
            // Get all broadcasts
            $broadcasts = [];
            $files = glob(BROADCAST_DIR . 'broadcast_*.json');
            
            foreach ($files as $file) {
                $broadcast = json_decode(file_get_contents($file), true);
                $broadcasts[] = [
                    'id' => $broadcast['id'],
                    'instruction' => substr($broadcast['instruction'], 0, 100) . '...',
                    'priority' => $broadcast['priority'],
                    'status' => $broadcast['status'],
                    'completed' => count($broadcast['completed_by'] ?? []),
                    'total_sessions' => count($this->activeSessions),
                    'created_at' => $broadcast['created_at']
                ];
            }
            
            return [
                'success' => true,
                'broadcasts' => $broadcasts,
                'count' => count($broadcasts)
            ];
        }
        
        // Get specific broadcast
        $broadcastFile = BROADCAST_DIR . $broadcastId . '.json';
        
        if (!file_exists($broadcastFile)) {
            return ['success' => false, 'error' => 'Broadcast not found'];
        }
        
        $broadcast = json_decode(file_get_contents($broadcastFile), true);
        
        return [
            'success' => true,
            'broadcast' => $broadcast
        ];
    }
    
    private function generateReadableInstruction(array $broadcast): string
    {
        $md = "# 🔔 CURRENT INSTRUCTION FOR ALL COPILOT INSTANCES\n\n";
        $md .= "**Broadcast ID:** `{$broadcast['id']}`\n";
        $md .= "**Priority:** **{$broadcast['priority']}**\n";
        $md .= "**Created:** {$broadcast['created_at']}\n";
        $md .= "**Expires:** {$broadcast['expires_at']}\n";
        $md .= "**Target:** {$broadcast['target']}\n\n";
        $md .= "---\n\n";
        $md .= "## 📋 INSTRUCTION\n\n";
        $md .= "```\n{$broadcast['instruction']}\n```\n\n";
        
        if (!empty($broadcast['context'])) {
            $md .= "## 📦 Context\n\n";
            foreach ($broadcast['context'] as $key => $value) {
                $md .= "- **{$key}:** {$value}\n";
            }
            $md .= "\n";
        }
        
        $md .= "## ✅ Action Required\n\n";
        $md .= "1. Read and understand the instruction above\n";
        $md .= "2. Execute the task in your current window/context\n";
        $md .= "3. When complete, mark as done:\n";
        $md .= "   ```bash\n";
        $md .= "   curl -X POST \"https://gpt.ecigdis.co.nz/api/broadcast-to-copilots.php\" \\\n";
        $md .= "     -d \"action=mark_complete\" \\\n";
        $md .= "     -d \"broadcast_id={$broadcast['id']}\" \\\n";
        $md .= "     -d \"session_id=YOUR_SESSION_ID\"\n";
        $md .= "   ```\n\n";
        
        $md .= "## 📊 Status\n\n";
        $md .= "- **Acknowledged:** " . count($broadcast['acknowledged_by']) . "\n";
        $md .= "- **Completed:** " . count($broadcast['completed_by']) . "\n";
        $md .= "- **Status:** {$broadcast['status']}\n\n";
        
        $md .= "---\n\n";
        $md .= "*This instruction will automatically expire at {$broadcast['expires_at']}*\n";
        
        return $md;
    }
    
    private function loadActiveSessions(): void
    {
        if (file_exists(ACTIVE_SESSIONS_FILE)) {
            $this->activeSessions = json_decode(file_get_contents(ACTIVE_SESSIONS_FILE), true) ?? [];
            
            // Remove stale sessions (no ping in last 5 minutes)
            $cutoff = strtotime('-5 minutes');
            foreach ($this->activeSessions as $id => $session) {
                if (strtotime($session['last_ping']) < $cutoff) {
                    unset($this->activeSessions[$id]);
                }
            }
            
            $this->saveActiveSessions();
        }
    }
    
    private function saveActiveSessions(): void
    {
        file_put_contents(ACTIVE_SESSIONS_FILE, json_encode($this->activeSessions, JSON_PRETTY_PRINT));
    }
    
    private function log(string $message, array $context = []): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'context' => $context
        ];
        
        file_put_contents(LOG_FILE, json_encode($logEntry) . "\n", FILE_APPEND);
    }
}

// Handle request
try {
    $broadcaster = new CopilotBroadcaster();
    $response = $broadcaster->handleRequest();
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
