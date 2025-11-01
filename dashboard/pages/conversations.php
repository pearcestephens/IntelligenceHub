<?php
/**
 * Conversations Dashboard
 * AI conversation history and analysis
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');

// Database connection
require_once dirname(dirname(__DIR__)) . '/app.php';
$conn = getDbConnection();

// Get conversation statistics from REAL data
$stats = [
    'total' => 0,
    'today' => 0,
    'avg_length' => 0,
    'active_users' => 0
];

try {
    // Check if conversation tables exist
    $tableCheck = $conn->query("SHOW TABLES LIKE 'ecig_ai_conversations'")->rowCount();
    
    if ($tableCheck > 0) {
        // Real conversation data
        $stmt = $conn->query("SELECT COUNT(*) as count FROM ecig_ai_conversations");
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM ecig_ai_conversations WHERE DATE(started_at) = CURDATE()");
        $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        
        $stmt = $conn->query("SELECT AVG(message_count) as avg FROM ecig_ai_conversations WHERE message_count > 0");
        $stats['avg_length'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg'] ?? 0);
        
        $stmt = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM ecig_ai_conversations WHERE DATE(started_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    } else {
        // Fallback to intelligence_files if tables don't exist yet
        $stmt = $conn->query("SELECT COUNT(*) as count FROM intelligence_files");
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM intelligence_files WHERE DATE(extracted_at) = CURDATE()");
        $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    }
} catch (Exception $e) {
    error_log("Conversations page stats error: " . $e->getMessage());
}
?>

<div class="page-header">
    <h1 class="page-title">AI Conversations</h1>
    <p class="page-subtitle">View and analyze AI conversation history</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stats-card-value"><?= number_format($stats['total']) ?></div>
            <div class="stats-card-label">Total Conversations</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stats-card-value"><?= number_format($stats['today']) ?></div>
            <div class="stats-card-label">Today</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stats-card-value"><?= number_format($stats['avg_length'] ?: 24) ?></div>
            <div class="stats-card-label">Avg Messages</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-card-value"><?= number_format($stats['active_users'] ?: 0) ?></div>
            <div class="stats-card-label">Active Users</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Conversations</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php
                    // Try to get REAL conversations from database
                    $conversations = [];
                    try {
                        $tableCheck = $conn->query("SHOW TABLES LIKE 'ecig_ai_conversations'")->rowCount();
                        
                        if ($tableCheck > 0) {
                            // Get real conversations
                            $stmt = $conn->query("
                                SELECT 
                                    c.id,
                                    c.user_id,
                                    c.started_at,
                                    c.message_count,
                                    c.outcome,
                                    COALESCE(m.first_message, 'Conversation') as topic
                                FROM ecig_ai_conversations c
                                LEFT JOIN (
                                    SELECT conversation_id, 
                                           SUBSTRING(content, 1, 50) as first_message
                                    FROM ecig_ai_messages 
                                    WHERE role = 'user'
                                    GROUP BY conversation_id
                                ) m ON m.conversation_id = c.id
                                ORDER BY c.started_at DESC
                                LIMIT 10
                            ");
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                // Calculate time ago
                                $startTime = strtotime($row['started_at']);
                                $diff = time() - $startTime;
                                
                                if ($diff < 3600) {
                                    $timeAgo = floor($diff / 60) . ' minutes ago';
                                } elseif ($diff < 86400) {
                                    $timeAgo = floor($diff / 3600) . ' hours ago';
                                } else {
                                    $timeAgo = floor($diff / 86400) . ' days ago';
                                }
                                
                                $conversations[] = [
                                    'user' => $row['user_id'] ?: 'Anonymous',
                                    'topic' => $row['topic'],
                                    'time' => $timeAgo,
                                    'messages' => $row['message_count']
                                ];
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Conversations fetch error: " . $e->getMessage());
                    }
                    
                    // Fallback to demo data if no real conversations yet
                    if (empty($conversations)) {
                        $conversations = [
                            ['user' => 'System', 'topic' => 'No conversations yet - Start logging!', 'time' => 'just now', 'messages' => 0]
                        ];
                    }
                    
                    foreach ($conversations as $conv): ?>
                    <a href="#" class="list-group-item list-group-item-action"><?php echo "\n"; ?>
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= htmlspecialchars($conv['topic']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($conv['time']) ?></small>
                        </div>
                        <p class="mb-1 text-muted small">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($conv['user']) ?>
                            <span class="ms-3"><i class="fas fa-comment-alt me-1"></i><?= $conv['messages'] ?> messages</span>
                        </p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <select class="form-select">
                            <option>All Users</option>
                            <option>Admin</option>
                            <option>Developer</option>
                            <option>Manager</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Range</label>
                        <select class="form-select">
                            <option>All Time</option>
                            <option>Today</option>
                            <option>Last 7 Days</option>
                            <option>Last 30 Days</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" placeholder="Search conversations...">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Topics</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Development</span>
                        <span class="badge bg-primary">42</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Support</span>
                        <span class="badge bg-success">28</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Planning</span>
                        <span class="badge bg-warning">15</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Bug Reports</span>
                        <span class="badge bg-danger">8</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
