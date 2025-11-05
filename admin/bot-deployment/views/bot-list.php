<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bots - Bot Deployment Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3">
                    <i class="bi bi-robot"></i> Bots
                </h1>
                <p class="text-muted">Manage your deployed bots</p>
            </div>
            <div class="col-auto">
                <a href="/dashboard/bots/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Bot
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="/dashboard/bots" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control"
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                               placeholder="Bot name...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="">All</option>
                            <option value="general" <?= ($filters['role'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                            <option value="analyst" <?= ($filters['role'] ?? '') === 'analyst' ? 'selected' : '' ?>>Analyst</option>
                            <option value="reporter" <?= ($filters['role'] ?? '') === 'reporter' ? 'selected' : '' ?>>Reporter</option>
                            <option value="scheduler" <?= ($filters['role'] ?? '') === 'scheduler' ? 'selected' : '' ?>>Scheduler</option>
                            <option value="monitor" <?= ($filters['role'] ?? '') === 'monitor' ? 'selected' : '' ?>>Monitor</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="/dashboard/bots" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bots Table -->
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($bots)): ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-robot" style="font-size: 4rem;"></i>
                        <p class="mt-3">No bots found</p>
                        <a href="/dashboard/bots/create" class="btn btn-primary">Create Your First Bot</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Schedule</th>
                                    <th>Last Execution</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bots as $bot): ?>
                                    <tr>
                                        <td><?= $bot['id'] ?></td>
                                        <td>
                                            <a href="/dashboard/bot?id=<?= $bot['id'] ?>" class="text-decoration-none fw-bold">
                                                <?= htmlspecialchars($bot['name']) ?>
                                            </a>
                                            <?php if (!empty($bot['description'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars(substr($bot['description'], 0, 50)) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= htmlspecialchars($bot['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($bot['status'] === 'active'): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-pause-circle"></i> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($bot['cron_schedule'])): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock"></i> <?= htmlspecialchars($bot['cron_schedule']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Manual only</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($bot['last_execution'])): ?>
                                                <small><?= date('Y-m-d H:i', strtotime($bot['last_execution'])) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= date('Y-m-d', strtotime($bot['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/dashboard/bot?id=<?= $bot['id'] ?>" class="btn btn-outline-primary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/dashboard/bots/edit?id=<?= $bot['id'] ?>" class="btn btn-outline-secondary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-success" title="Execute"
                                                        onclick="executeBot(<?= $bot['id'] ?>, '<?= htmlspecialchars($bot['name']) ?>')">
                                                    <i class="bi bi-play-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total'] > 1): ?>
                        <div class="card-footer bg-white">
                            <nav>
                                <ul class="pagination pagination-sm mb-0 justify-content-center">
                                    <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $pagination['current'] - 1 ?><?= http_build_query($filters, '', '&') ?>">
                                            Previous
                                        </a>
                                    </li>

                                    <?php for ($i = max(1, $pagination['current'] - 2); $i <= min($pagination['total'], $pagination['current'] + 2); $i++): ?>
                                        <li class="page-item <?= $i === $pagination['current'] ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= http_build_query($filters, '', '&') ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?= $pagination['current'] >= $pagination['total'] ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $pagination['current'] + 1 ?><?= http_build_query($filters, '', '&') ?>">
                                            Next
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <p class="text-center text-muted small mt-2 mb-0">
                                Showing <?= count($bots) ?> of <?= $pagination['total_items'] ?> bots
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Execute Bot Modal -->
    <div class="modal fade" id="executeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Execute Bot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="executeForm">
                        <input type="hidden" id="executeBotId" name="bot_id">
                        <input type="hidden" id="csrfToken" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">Bot: <strong id="executeBotName"></strong></label>
                        </div>
                        <div class="mb-3">
                            <label for="executeInput" class="form-label">Input (optional)</label>
                            <textarea class="form-control" id="executeInput" name="input" rows="3"
                                      placeholder="Enter input for the bot..."></textarea>
                        </div>
                        <div id="executeResult" class="alert" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitExecute()">
                        <i class="bi bi-play-circle"></i> Execute
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let executeModal;

        document.addEventListener('DOMContentLoaded', function() {
            executeModal = new bootstrap.Modal(document.getElementById('executeModal'));
        });

        function executeBot(botId, botName) {
            document.getElementById('executeBotId').value = botId;
            document.getElementById('executeBotName').textContent = botName;
            document.getElementById('executeInput').value = '';
            document.getElementById('executeResult').style.display = 'none';
            executeModal.show();
        }

        function submitExecute() {
            const formData = new FormData(document.getElementById('executeForm'));
            const resultDiv = document.getElementById('executeResult');

            resultDiv.innerHTML = '<i class="bi bi-hourglass-split"></i> Executing...';
            resultDiv.className = 'alert alert-info';
            resultDiv.style.display = 'block';

            fetch('/dashboard/execute', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <i class="bi bi-check-circle"></i> <strong>Success!</strong><br>
                        Duration: ${data.duration_ms}ms<br>
                        <a href="/dashboard/bot?id=${formData.get('bot_id')}" class="alert-link">View details</a>
                    `;
                    resultDiv.className = 'alert alert-success';
                } else {
                    resultDiv.innerHTML = `<i class="bi bi-x-circle"></i> <strong>Error:</strong> ${data.error}`;
                    resultDiv.className = 'alert alert-danger';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<i class="bi bi-x-circle"></i> <strong>Error:</strong> ${error.message}`;
                resultDiv.className = 'alert alert-danger';
            });
        }
    </script>
</body>
</html>
