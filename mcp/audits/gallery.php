<?php
/**
 * AUDIT GALLERY VIEWER
 *
 * Web-based gallery for viewing all audit history
 * - Screenshots with thumbnails
 * - Videos with playback
 * - Audit reports with details
 * - Search and filter capabilities
 * - Project/server organization
 *
 * URL: https://gpt.ecigdis.co.nz/audits/gallery.php
 *
 * Query parameters:
 * - project: Filter by project ID
 * - server: Filter by server ID
 * - audit: View specific audit
 * - type: Filter by type (screenshot/video/report)
 * - since: Filter by date (YYYY-MM-DD)
 *
 * @version 1.0.0
 */

define('DATABASE_FILE', __DIR__ . '/audits.db');
define('FILES_BASE_URL', 'https://gpt.ecigdis.co.nz/audits/files');
define('FILES_BASE_DIR', __DIR__ . '/files');

// Get query parameters
$projectFilter = $_GET['project'] ?? null;
$serverFilter = $_GET['server'] ?? null;
$auditFilter = $_GET['audit'] ?? null;
$typeFilter = $_GET['type'] ?? null;
$sinceFilter = $_GET['since'] ?? null;
$page = (int)($_GET['page'] ?? 1);
$perPage = 50;

// Initialize database
$db = new PDO('sqlite:' . DATABASE_FILE);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Build query
$sql = "SELECT * FROM audits WHERE 1=1";
$params = [];

if ($projectFilter) {
    $sql .= " AND project_id = ?";
    $params[] = $projectFilter;
}

if ($serverFilter) {
    $sql .= " AND server_id = ?";
    $params[] = $serverFilter;
}

if ($auditFilter) {
    $sql .= " AND audit_id = ?";
    $params[] = $auditFilter;
}

if ($typeFilter) {
    $sql .= " AND file_type LIKE ?";
    $params[] = $typeFilter . '%';
}

if ($sinceFilter) {
    $sql .= " AND created_at >= ?";
    $params[] = $sinceFilter . ' 00:00:00';
}

// Count total
$countSql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get audits with pagination
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = ($page - 1) * $perPage;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$audits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique projects and servers for filters
$projects = $db->query("SELECT DISTINCT project_id FROM audits ORDER BY project_id")->fetchAll(PDO::FETCH_COLUMN);
$servers = $db->query("SELECT DISTINCT server_id FROM audits ORDER BY server_id")->fetchAll(PDO::FETCH_COLUMN);

// Calculate pagination
$totalPages = ceil($total / $perPage);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Gallery - Frontend Testing</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .subtitle {
            text-align: center;
            opacity: 0.9;
            margin-top: 10px;
        }

        .filters {
            background: #1e293b;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #94a3b8;
        }

        select, input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #334155;
            border-radius: 8px;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 1rem;
        }

        button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        button:hover {
            background: #5a67d8;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #1e293b;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .audit-card {
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        .audit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .audit-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #0f172a;
        }

        .audit-info {
            padding: 15px;
        }

        .audit-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #f1f5f9;
        }

        .audit-meta {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        .audit-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-small {
            flex: 1;
            padding: 8px 12px;
            font-size: 0.85rem;
            text-align: center;
            text-decoration: none;
            background: #334155;
            color: #e2e8f0;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-small:hover {
            background: #475569;
        }

        .btn-primary {
            background: #667eea;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }

        .page-btn {
            padding: 8px 16px;
            background: #1e293b;
            color: #e2e8f0;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .page-btn:hover {
            background: #334155;
        }

        .page-btn.active {
            background: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 5px;
        }

        .badge-screenshot {
            background: #10b981;
            color: white;
        }

        .badge-video {
            background: #f59e0b;
            color: white;
        }

        .badge-report {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>🎬 Audit Gallery</h1>
            <p class="subtitle">Frontend Testing & Error Detection History</p>
        </div>
    </header>

    <div class="container">
        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="project">Project</label>
                        <select name="project" id="project">
                            <option value="">All Projects</option>
                            <?php foreach ($projects as $proj): ?>
                                <option value="<?= htmlspecialchars($proj) ?>" <?= $projectFilter === $proj ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($proj) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="server">Server</label>
                        <select name="server" id="server">
                            <option value="">All Servers</option>
                            <?php foreach ($servers as $srv): ?>
                                <option value="<?= htmlspecialchars($srv) ?>" <?= $serverFilter === $srv ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($srv) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="type">Type</label>
                        <select name="type" id="type">
                            <option value="">All Types</option>
                            <option value="image" <?= $typeFilter === 'image' ? 'selected' : '' ?>>Screenshots</option>
                            <option value="video" <?= $typeFilter === 'video' ? 'selected' : '' ?>>Videos</option>
                            <option value="application" <?= $typeFilter === 'application' ? 'selected' : '' ?>>Reports</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="since">Since Date</label>
                        <input type="date" name="since" id="since" value="<?= htmlspecialchars($sinceFilter ?? '') ?>">
                    </div>

                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button type="submit">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?= number_format($total) ?></div>
                <div class="stat-label">Total Audits</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($projects) ?></div>
                <div class="stat-label">Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($servers) ?></div>
                <div class="stat-label">Servers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalPages ?></div>
                <div class="stat-label">Pages</div>
            </div>
        </div>

        <!-- Gallery -->
        <?php if (empty($audits)): ?>
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h2>No audits found</h2>
                <p>Try adjusting your filters or upload some screenshots!</p>
            </div>
        <?php else: ?>
            <div class="gallery">
                <?php foreach ($audits as $audit):
                    $metadata = json_decode($audit['metadata'] ?? '{}', true);
                    $fileUrl = FILES_BASE_URL . '/' . $audit['project_id'] . '/' . $audit['server_id'] . '/' . $audit['filename'];
                    $isImage = strpos($audit['file_type'], 'image') !== false;
                    $isVideo = strpos($audit['file_type'], 'video') !== false;
                    $type = $isImage ? 'screenshot' : ($isVideo ? 'video' : 'report');
                ?>
                    <div class="audit-card">
                        <?php if ($isImage): ?>
                            <img src="<?= htmlspecialchars($fileUrl) ?>" alt="Audit" class="audit-preview">
                        <?php elseif ($isVideo): ?>
                            <video src="<?= htmlspecialchars($fileUrl) ?>" class="audit-preview" controls></video>
                        <?php else: ?>
                            <div class="audit-preview" style="display: flex; align-items: center; justify-content: center; font-size: 3rem;">📄</div>
                        <?php endif; ?>

                        <div class="audit-info">
                            <div class="audit-title">
                                <span class="badge badge-<?= $type ?>"><?= strtoupper($type) ?></span>
                                <?= htmlspecialchars($audit['audit_id']) ?>
                            </div>

                            <div class="audit-meta">
                                📁 Project: <?= htmlspecialchars($audit['project_id']) ?>
                            </div>
                            <div class="audit-meta">
                                🖥️ Server: <?= htmlspecialchars($audit['server_id']) ?>
                            </div>
                            <div class="audit-meta">
                                📦 Size: <?= number_format($audit['file_size'] / 1024, 1) ?>KB
                            </div>
                            <div class="audit-meta">
                                🕐 <?= date('Y-m-d H:i:s', strtotime($audit['created_at'])) ?>
                            </div>

                            <?php if (!empty($metadata['errors'])): ?>
                                <div class="audit-meta" style="color: #ef4444;">
                                    ⚠️ Errors: <?= $metadata['errors'] ?>
                                </div>
                            <?php endif; ?>

                            <div class="audit-actions">
                                <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank" class="btn-small btn-primary">
                                    View
                                </a>
                                <a href="<?= htmlspecialchars($fileUrl) ?>" download class="btn-small">
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $projectFilter ? '&project=' . urlencode($projectFilter) : '' ?><?= $serverFilter ? '&server=' . urlencode($serverFilter) : '' ?>" class="page-btn">
                            ← Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?><?= $projectFilter ? '&project=' . urlencode($projectFilter) : '' ?><?= $serverFilter ? '&server=' . urlencode($serverFilter) : '' ?>"
                           class="page-btn <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $projectFilter ? '&project=' . urlencode($projectFilter) : '' ?><?= $serverFilter ? '&server=' . urlencode($serverFilter) : '' ?>" class="page-btn">
                            Next →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
