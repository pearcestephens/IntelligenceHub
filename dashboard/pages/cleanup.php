<?php
/**
 * Database Cleanup & Analysis Page
 * Analyze and clean up the intelligence database
 */

$db = getDbConnection();
?>

<div class="page-header">
    <h1 class="page-title">Database Cleanup & Analysis</h1>
    <p class="page-subtitle">Find and remove bloat from your intelligence database</p>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-database"></i>
            </div>
            <?php
            $totalFiles = $db->query("SELECT COUNT(*) as count FROM intelligence_files")->fetch()['count'] ?? 0;
            ?>
            <div class="stats-card-value"><?php echo number_format($totalFiles); ?></div>
            <div class="stats-card-label">Total Files</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-hdd"></i>
            </div>
            <?php
            $totalSize = $db->query("SELECT SUM(file_size) as size FROM intelligence_files")->fetch()['size'] ?? 0;
            $totalSizeMB = round($totalSize / 1024 / 1024, 2);
            ?>
            <div class="stats-card-value"><?php echo number_format($totalSizeMB); ?> MB</div>
            <div class="stats-card-label">Database Size</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-server"></i>
            </div>
            <?php
            $servers = $db->query("SELECT COUNT(DISTINCT server_id) as count FROM intelligence_files")->fetch()['count'] ?? 0;
            ?>
            <div class="stats-card-value"><?php echo $servers; ?></div>
            <div class="stats-card-label">Servers Indexed</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-trash-alt"></i>
            </div>
            <div class="stats-card-value" id="bloatPercentage">Analyzing...</div>
            <div class="stats-card-label">Potential Bloat</div>
        </div>
    </div>
</div>

<!-- Analysis Tables -->
<div class="row g-4">
    
    <!-- Top Offenders by Path Pattern -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-folder-open"></i> Top Path Patterns</h5>
                <span class="badge bg-info">Potential Bloat Sources</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pattern</th>
                                <th class="text-end">Files</th>
                                <th class="text-end">Size (MB)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Common bloat patterns
                            $patterns = [
                                'node_modules' => "file_path LIKE '%node_modules%'",
                                'vendor' => "file_path LIKE '%vendor/%'",
                                '.git' => "file_path LIKE '%.git/%'",
                                'cache' => "file_path LIKE '%cache/%'",
                                'tmp' => "file_path LIKE '%tmp/%' OR file_path LIKE '%temp/%'",
                                'backups' => "file_path LIKE '%backup%' OR file_path LIKE '%.bak'",
                                'logs' => "file_path LIKE '%.log'",
                                'test' => "file_path LIKE '%/test/%' OR file_path LIKE '%/tests/%'",
                            ];
                            
                            foreach ($patterns as $label => $condition) {
                                $stmt = $db->query("
                                    SELECT 
                                        COUNT(*) as count,
                                        COALESCE(SUM(file_size), 0) as total_size
                                    FROM intelligence_files 
                                    WHERE $condition
                                ");
                                $result = $stmt->fetch();
                                $count = $result['count'] ?? 0;
                                $sizeMB = round($result['total_size'] / 1024 / 1024, 2);
                                
                                if ($count > 0):
                            ?>
                            <tr>
                                <td><code><?php echo $label; ?></code></td>
                                <td class="text-end"><strong><?php echo number_format($count); ?></strong></td>
                                <td class="text-end"><?php echo number_format($sizeMB); ?> MB</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="deletePattern('<?php echo $label; ?>', '<?php echo htmlspecialchars($condition); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                endif;
                            } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Files by Server -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-server"></i> Files by Server</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Server ID</th>
                                <th class="text-end">Files</th>
                                <th class="text-end">Size (MB)</th>
                                <th class="text-end">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("
                                SELECT 
                                    server_id,
                                    COUNT(*) as count,
                                    SUM(file_size) as total_size
                                FROM intelligence_files
                                GROUP BY server_id
                                ORDER BY count DESC
                            ");
                            
                            while ($row = $stmt->fetch()):
                                $sizeMB = round($row['total_size'] / 1024 / 1024, 2);
                                $percentage = round(($row['count'] / $totalFiles) * 100, 1);
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['server_id']); ?></strong></td>
                                <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                <td class="text-end"><?php echo number_format($sizeMB); ?> MB</td>
                                <td class="text-end">
                                    <span class="badge bg-<?php echo $percentage > 50 ? 'danger' : ($percentage > 25 ? 'warning' : 'success'); ?>">
                                        <?php echo $percentage; ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Files by Intelligence Type -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie"></i> Files by Type</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Intelligence Type</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Size (MB)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("
                                SELECT 
                                    intelligence_type,
                                    COUNT(*) as count,
                                    SUM(file_size) as total_size
                                FROM intelligence_files
                                GROUP BY intelligence_type
                                ORDER BY count DESC
                                LIMIT 20
                            ");
                            
                            while ($row = $stmt->fetch()):
                                $sizeMB = round($row['total_size'] / 1024 / 1024, 2);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['intelligence_type']); ?></td>
                                <td class="text-end"><strong><?php echo number_format($row['count']); ?></strong></td>
                                <td class="text-end"><?php echo number_format($sizeMB); ?> MB</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Largest Individual Files -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-file-archive"></i> Largest Files</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>File Path</th>
                                <th class="text-end">Size</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("
                                SELECT 
                                    file_id,
                                    file_path,
                                    file_size,
                                    server_id
                                FROM intelligence_files
                                ORDER BY file_size DESC
                                LIMIT 20
                            ");
                            
                            while ($row = $stmt->fetch()):
                                $sizeKB = round($row['file_size'] / 1024, 2);
                                $pathParts = explode('/', $row['file_path']);
                                $fileName = end($pathParts);
                            ?>
                            <tr>
                                <td class="small" title="<?php echo htmlspecialchars($row['file_path']); ?>">
                                    <code><?php echo htmlspecialchars(strlen($row['file_path']) > 50 ? '...' . substr($row['file_path'], -47) : $row['file_path']); ?></code>
                                </td>
                                <td class="text-end text-nowrap"><?php echo number_format($sizeKB); ?> KB</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteFile(<?php echo $row['file_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Cleanup Actions -->
<div class="row g-4 mt-4">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle"></i> Danger Zone - Bulk Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <button class="btn btn-danger w-100" onclick="confirmAction('Clear All node_modules', 'clearNodeModules')">
                            <i class="fas fa-trash-alt"></i> Clear All node_modules
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-danger w-100" onclick="confirmAction('Clear All vendor/', 'clearVendor')">
                            <i class="fas fa-trash-alt"></i> Clear All vendor/
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-danger w-100" onclick="confirmAction('Clear All Logs', 'clearLogs')">
                            <i class="fas fa-trash-alt"></i> Clear All .log Files
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-danger w-100" onclick="confirmAction('Clear All Caches', 'clearCache')">
                            <i class="fas fa-trash-alt"></i> Clear All Cache
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-danger w-100" onclick="confirmAction('Clear All Backups', 'clearBackups')">
                            <i class="fas fa-trash-alt"></i> Clear All Backups
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-warning w-100" onclick="confirmAction('Optimize Database', 'optimizeDb')">
                            <i class="fas fa-tools"></i> Optimize Database
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deletePattern(label, condition) {
    if (confirm(`Delete all files matching pattern: ${label}?\n\nThis cannot be undone!`)) {
        $.ajax({
            url: 'api/cleanup_action.php',
            method: 'POST',
            data: {
                action: 'delete_pattern',
                condition: condition,
                label: label
            },
            success: function(response) {
                if (response.success) {
                    alert(`Deleted ${response.deleted} files`);
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }
        });
    }
}

function deleteFile(fileId) {
    if (confirm('Delete this file from the intelligence database?')) {
        $.ajax({
            url: 'api/cleanup_action.php',
            method: 'POST',
            data: {
                action: 'delete_file',
                file_id: fileId
            },
            success: function(response) {
                if (response.success) {
                    alert('File deleted');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            }
        });
    }
}

function confirmAction(actionName, actionType) {
    if (confirm(`Are you sure you want to: ${actionName}?\n\nThis action cannot be undone!`)) {
        const btn = event.target.closest('button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        $.ajax({
            url: 'api/cleanup_action.php',
            method: 'POST',
            data: {
                action: actionType
            },
            success: function(response) {
                if (response.success) {
                    alert(`Success! Deleted ${response.deleted} files.\nFreed ${response.size_freed_mb} MB`);
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                    btn.disabled = false;
                    btn.innerHTML = btn.getAttribute('data-original-text');
                }
            },
            error: function() {
                alert('Request failed');
                btn.disabled = false;
            }
        });
    }
}

// Calculate bloat percentage
$(document).ready(function() {
    // Calculate from patterns table
    let bloatFiles = 0;
    $('tbody tr').each(function() {
        const countText = $(this).find('td:eq(1)').text().replace(/,/g, '');
        bloatFiles += parseInt(countText) || 0;
    });
    
    const totalFiles = <?php echo $totalFiles; ?>;
    const bloatPercentage = ((bloatFiles / totalFiles) * 100).toFixed(1);
    
    $('#bloatPercentage').html(`<strong>${bloatPercentage}%</strong>`);
});
</script>
