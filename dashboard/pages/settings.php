<?php
/**
 * Settings and Configuration Page
 * Manage dashboard settings, user preferences, and system configuration
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Settings Menu</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="pill">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                    <a href="#preferences" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-sliders-h me-2"></i>Preferences
                    </a>
                    <a href="#system" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-server me-2"></i>System
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-shield-alt me-2"></i>Security
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </a>
                    <a href="#api" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-key me-2"></i>API Keys
                    </a>
                    <a href="#backup" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-database me-2"></i>Backup & Restore
                    </a>
                    <a href="#logs" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                        <i class="fas fa-history me-2"></i>Activity Logs
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>User Profile</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['username'] ?? 'admin') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['role'] ?? 'administrator') ?>" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" placeholder="admin@example.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" placeholder="Administrator">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" placeholder="Leave blank to keep current">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" placeholder="Confirm new password">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preferences Tab -->
                <div class="tab-pane fade" id="preferences">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Dashboard Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-4">
                                    <label class="form-label">Theme</label>
                                    <select class="form-select">
                                        <option selected>Light</option>
                                        <option>Dark</option>
                                        <option>Auto (System)</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Items Per Page</label>
                                    <select class="form-select">
                                        <option>10</option>
                                        <option>25</option>
                                        <option selected>50</option>
                                        <option>100</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Default View</label>
                                    <select class="form-select">
                                        <option selected>Overview</option>
                                        <option>Files</option>
                                        <option>Search</option>
                                        <option>Analytics</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Sidebar State</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sidebarState" id="sidebar1" checked>
                                        <label class="form-check-label" for="sidebar1">Always Expanded</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sidebarState" id="sidebar2">
                                        <label class="form-check-label" for="sidebar2">Always Collapsed</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sidebarState" id="sidebar3">
                                        <label class="form-check-label" for="sidebar3">Remember Last State</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Preferences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Tab -->
                <div class="tab-pane fade" id="system">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-server me-2"></i>System Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-4">
                                    <label class="form-label">Scan Interval (minutes)</label>
                                    <input type="number" class="form-control" value="60" min="15" max="1440">
                                    <small class="text-muted">How often to automatically scan servers (15-1440 minutes)</small>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Cache Duration (hours)</label>
                                    <input type="number" class="form-control" value="24" min="1" max="168">
                                    <small class="text-muted">How long to cache results (1-168 hours)</small>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Max File Size to Index (MB)</label>
                                    <input type="number" class="form-control" value="10" min="1" max="100">
                                    <small class="text-muted">Files larger than this will be skipped</small>
                                </div>
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="autoScan" checked>
                                        <label class="form-check-label" for="autoScan">Enable Automatic Scanning</label>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="debugMode">
                                        <label class="form-check-label" for="debugMode">Enable Debug Mode</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Configuration
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>PHP Version:</strong> <?= PHP_VERSION ?></p>
                                    <p><strong>Database:</strong> MySQL <?= DB_HOST ?></p>
                                    <p><strong>Total Files Indexed:</strong> 566,016</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Dashboard Version:</strong> 1.0.0</p>
                                    <p><strong>Last Update:</strong> <?= date('Y-m-d H:i:s') ?></p>
                                    <p><strong>Server Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-4">
                                    <label class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" class="form-control" value="<?= SESSION_TIMEOUT / 60 ?>" min="15" max="1440">
                                    <small class="text-muted">Auto-logout after inactivity</small>
                                </div>
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="twoFactor">
                                        <label class="form-check-label" for="twoFactor">Enable Two-Factor Authentication</label>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="ipWhitelist">
                                        <label class="form-check-label" for="ipWhitelist">Enable IP Whitelist</label>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Allowed IP Addresses</label>
                                    <textarea class="form-control" rows="3" placeholder="127.0.0.1&#10;192.168.1.0/24"></textarea>
                                    <small class="text-muted">One IP or CIDR range per line</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Security Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-4">
                                    <h6>Email Notifications</h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailScan" checked>
                                        <label class="form-check-label" for="emailScan">Scan Completion</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailError" checked>
                                        <label class="form-check-label" for="emailError">Error Alerts</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailDaily">
                                        <label class="form-check-label" for="emailDaily">Daily Summary</label>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6>In-App Notifications</h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="appUpdates" checked>
                                        <label class="form-check-label" for="appUpdates">System Updates</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="appWarnings" checked>
                                        <label class="form-check-label" for="appWarnings">Warnings</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Notification Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- API Keys Tab -->
                <div class="tab-pane fade" id="api">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-key me-2"></i>API Keys</h5>
                            <button class="btn btn-sm btn-primary" onclick="generateApiKey()">
                                <i class="fas fa-plus me-2"></i>Generate New Key
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Key Name</th>
                                            <th>API Key</th>
                                            <th>Created</th>
                                            <th>Last Used</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Production API</td>
                                            <td><code>sk_live_xxxxxxxxxxxxxxxx</code></td>
                                            <td><?= date('Y-m-d', strtotime('-30 days')) ?></td>
                                            <td><?= date('Y-m-d H:i', strtotime('-2 hours')) ?></td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="copyApiKey(this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="revokeApiKey(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Development API</td>
                                            <td><code>sk_test_xxxxxxxxxxxxxxxx</code></td>
                                            <td><?= date('Y-m-d', strtotime('-7 days')) ?></td>
                                            <td><?= date('Y-m-d H:i', strtotime('-5 hours')) ?></td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="copyApiKey(this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="revokeApiKey(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Tab -->
                <div class="tab-pane fade" id="backup">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-database me-2"></i>Backup Management</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Last automatic backup: <?= date('Y-m-d H:i:s', strtotime('-6 hours')) ?>
                            </div>
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-primary" onclick="createBackup()">
                                    <i class="fas fa-database me-2"></i>Create Backup Now
                                </button>
                                <button class="btn btn-outline-secondary" onclick="downloadBackup()">
                                    <i class="fas fa-download me-2"></i>Download Latest
                                </button>
                            </div>
                            <h6>Recent Backups</h6>
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>backup_<?= date('Y-m-d_H-i-s', strtotime('-6 hours')) ?>.sql</strong>
                                        <br><small class="text-muted">342 MB - 6 hours ago</small>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-redo"></i> Restore
                                        </button>
                                    </div>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>backup_<?= date('Y-m-d_H-i-s', strtotime('-1 day')) ?>.sql</strong>
                                        <br><small class="text-muted">340 MB - 1 day ago</small>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-redo"></i> Restore
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Logs Tab -->
                <div class="tab-pane fade" id="logs">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $activities = [
                                            ['time' => '-5 minutes', 'action' => 'Login', 'details' => 'Successful login', 'type' => 'success'],
                                            ['time' => '-2 hours', 'action' => 'Settings Changed', 'details' => 'Updated scan interval', 'type' => 'info'],
                                            ['time' => '-6 hours', 'action' => 'Scan Started', 'details' => 'Manual scan triggered', 'type' => 'info'],
                                            ['time' => '-1 day', 'action' => 'Backup Created', 'details' => 'Automatic backup completed', 'type' => 'success'],
                                        ];
                                        foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?= date('Y-m-d H:i:s', strtotime($activity['time'])) ?></td>
                                            <td><?= htmlspecialchars($_SESSION['username'] ?? 'admin') ?></td>
                                            <td><span class="badge bg-<?= $activity['type'] ?>"><?= $activity['action'] ?></span></td>
                                            <td><?= $activity['details'] ?></td>
                                            <td><code><?= $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' ?></code></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateApiKey() {
    if (confirm('Generate a new API key?')) {
        showToast('API key generated successfully', 'success');
    }
}

function copyApiKey(btn) {
    const key = btn.closest('tr').querySelector('code').textContent;
    navigator.clipboard.writeText(key);
    showToast('API key copied to clipboard', 'success');
}

function revokeApiKey(btn) {
    if (confirm('Revoke this API key? This action cannot be undone.')) {
        btn.closest('tr').remove();
        showToast('API key revoked', 'warning');
    }
}

function createBackup() {
    showToast('Creating backup...', 'info');
    setTimeout(() => showToast('Backup created successfully', 'success'), 2000);
}

function downloadBackup() {
    showToast('Downloading backup...', 'info');
}

function showToast(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '9999';
    alert.innerHTML = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}
</script>
