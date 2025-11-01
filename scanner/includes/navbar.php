<?php
/**
 * Top Navigation Bar Component
 *
 * @package Scanner
 * @version 3.0.0
 */

// Get available projects for selector
$availableProjects = [];
try {
    $stmt = $pdo->query("SELECT id, project_name, project_type, status FROM projects WHERE status = 'active' ORDER BY project_name");
    $availableProjects = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Navbar: Failed to load projects - " . $e->getMessage());
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">

        <!-- Mobile Menu Toggle -->
        <button class="btn btn-link d-lg-none" type="button" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Project Selector -->
        <div class="navbar-nav me-auto">
            <div class="nav-item dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="projectSelector" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-folder me-2"></i>
                    <?php
                    if (isset($currentProject['project_name'])) {
                        echo htmlspecialchars($currentProject['project_name']);
                    } else {
                        echo 'Select Project';
                    }
                    ?>
                </button>
                <ul class="dropdown-menu" aria-labelledby="projectSelector">
                    <?php if (empty($availableProjects)): ?>
                        <li><span class="dropdown-item text-muted">No projects available</span></li>
                    <?php else: ?>
                        <?php foreach ($availableProjects as $proj): ?>
                            <li>
                                <a class="dropdown-item <?php echo $proj['id'] === $projectId ? 'active' : ''; ?>"
                                   href="?page=<?php echo $page; ?>&project_id=<?php echo $proj['id']; ?>">
                                    <i class="bi bi-folder me-2"></i>
                                    <?php echo htmlspecialchars($proj['project_name']); ?>
                                    <small class="text-muted ms-2">(<?php echo htmlspecialchars($proj['project_type']); ?>)</small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?page=projects"><i class="bi bi-plus-circle me-2"></i>Manage Projects</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Semantic Search Bar -->
        <form class="d-flex me-3 flex-fill" role="search" style="max-width: 500px;" onsubmit="return handleSemanticSearch(event)">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search" id="searchIcon"></i>
                    <span class="spinner-border spinner-border-sm d-none" id="searchSpinner"></span>
                </span>
                <input type="text" class="form-control" id="globalSearch"
                       placeholder="Natural language search (e.g., 'How do we authenticate users?')"
                       aria-label="Semantic Search"
                       autocomplete="off">
                <button class="btn btn-outline-secondary" type="button" id="searchHelpBtn"
                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Use natural language: 'SQL injection fixes', 'authentication code', etc.">
                    <i class="bi bi-question-circle"></i>
                </button>
            </div>
        </form>

        <!-- Right Side Items -->
        <ul class="navbar-nav ms-auto align-items-center">

            <!-- Notifications -->
            <li class="nav-item dropdown me-2">
                <button class="btn btn-link nav-link position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;" id="notificationBadge">
                        0
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 320px;">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <div class="dropdown-item-text text-center text-muted py-3" id="noNotifications">
                            <i class="bi bi-inbox d-block fs-3 mb-2"></i>
                            No new notifications
                        </div>
                    </li>
                </ul>
            </li>

            <!-- System Status -->
            <li class="nav-item me-2">
                <div class="d-flex align-items-center">
                    <span class="badge bg-success-subtle text-success" id="systemStatus">
                        <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                        <span class="ms-1">Online</span>
                    </span>
                </div>
            </li>

            <!-- User Menu -->
            <li class="nav-item dropdown">
                <button class="btn btn-link nav-link" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-4"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><h6 class="dropdown-header">Administrator</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?page=settings"><i class="bi bi-sliders me-2"></i>Settings</a></li>
                    <li><a class="dropdown-item" href="?page=documentation"><i class="bi bi-book me-2"></i>Documentation</a></li>
                    <li><a class="dropdown-item" href="?page=support"><i class="bi bi-question-circle me-2"></i>Support</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </li>

        </ul>

    </div>
</nav>

<script>
// ============================================================================
// SEMANTIC SEARCH HANDLER (Quick Win #3)
// ============================================================================

let searchModal = null;

// Initialize search modal on page load
document.addEventListener('DOMContentLoaded', function() {
    // Create search results modal
    createSearchModal();

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Check for notifications
    checkNotifications();
    setInterval(checkNotifications, 30000);
});

// Create search results modal
function createSearchModal() {
    const modalHTML = `
        <div class="modal fade" id="searchResultsModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-search me-2"></i>
                            <span id="searchResultsTitle">Search Results</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="searchResultsBody">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-search fs-1 d-block mb-3"></i>
                            <p>Enter a search query to find code across your projects</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <small class="text-muted me-auto">Powered by MCP Intelligence Hub</small>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    searchModal = new bootstrap.Modal(document.getElementById('searchResultsModal'));
}

// Handle semantic search submission
async function handleSemanticSearch(event) {
    event.preventDefault();
    const searchTerm = document.getElementById('globalSearch').value.trim();

    if (!searchTerm) return false;

    // Show loading state
    const searchIcon = document.getElementById('searchIcon');
    const searchSpinner = document.getElementById('searchSpinner');
    searchIcon.classList.add('d-none');
    searchSpinner.classList.remove('d-none');

    try {
        // Perform semantic search via MCP API
        const response = await fetch('/scanner/api/mcp-proxy.php?action=search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                query: searchTerm,
                limit: 20,
                include_context: true
            })
        });

        const data = await response.json();

        if (data.success && data.data.results) {
            displaySearchResults(searchTerm, data.data.results);
        } else {
            displaySearchError(data.message || 'Search failed');
        }

    } catch (error) {
        console.error('Search error:', error);
        displaySearchError('Failed to perform search. Please try again.');
    } finally {
        // Restore icons
        searchIcon.classList.remove('d-none');
        searchSpinner.classList.add('d-none');
    }

    return false;
}

// Display search results in modal
function displaySearchResults(query, results) {
    const titleEl = document.getElementById('searchResultsTitle');
    const bodyEl = document.getElementById('searchResultsBody');

    titleEl.innerHTML = `Search Results for "<strong>${escapeHtml(query)}</strong>" (${results.length} found)`;

    if (results.length === 0) {
        bodyEl.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <p>No results found for "${escapeHtml(query)}"</p>
                <small>Try different keywords or a more general search term</small>
            </div>
        `;
    } else {
        let html = '<div class="list-group">';

        results.forEach((result, index) => {
            const score = Math.round(result.score * 100);
            const scoreClass = score >= 80 ? 'success' : score >= 60 ? 'warning' : 'secondary';

            html += `
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">
                                <i class="bi bi-file-code me-1"></i>
                                ${escapeHtml(result.file_name || 'Unknown File')}
                            </h6>
                            <small class="text-muted">${escapeHtml(result.file_path || '')}</small>
                        </div>
                        <span class="badge bg-${scoreClass}">${score}% match</span>
                    </div>

                    ${result.preview ? `
                        <div class="mb-2">
                            <pre class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;"><code>${escapeHtml(result.preview)}</code></pre>
                        </div>
                    ` : ''}

                    ${result.description ? `
                        <p class="mb-2 small">${escapeHtml(result.description)}</p>
                    ` : ''}

                    <div class="d-flex gap-2">
                        <a href="?page=file-viewer&file_id=${result.file_id}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i>View File
                        </a>
                        ${result.violations_count > 0 ? `
                            <a href="?page=violations&file_id=${result.file_id}" class="btn btn-sm btn-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>${result.violations_count} Issues
                            </a>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        bodyEl.innerHTML = html;
    }

    // Show modal
    searchModal.show();
}

// Display search error
function displaySearchError(message) {
    const bodyEl = document.getElementById('searchResultsBody');
    bodyEl.innerHTML = `
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${escapeHtml(message)}
        </div>
    `;
    searchModal.show();
}

// Escape HTML for XSS prevention
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Legacy search handler (fallback)
function handleSearch(event) {
    return handleSemanticSearch(event);
}

// ============================================================================
// NOTIFICATIONS
// ============================================================================

function checkNotifications() {
    // TODO: Implement notification checking via API
    // For now, just placeholder
}

// ============================================================================
// MOBILE SIDEBAR TOGGLE
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    }
});
</script>

<style>
.navbar {
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

@media (max-width: 991.98px) {
    .sidebar {
        position: fixed;
        left: -260px;
        top: 0;
        z-index: 1050;
        transition: left 0.3s ease;
    }

    .sidebar.show {
        left: 0;
    }
}
</style>
