<?php
/**
 * Files Browser Page
 * Browse and manage intelligence files
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');

$stats = getSystemStats();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>
                        Intelligence Files Browser
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="fileSearch" placeholder="Search files...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="fileTypeFilter">
                                <option value="">All Types</option>
                                <option value="documentation">Documentation</option>
                                <option value="code_intelligence">Code Intelligence</option>
                                <option value="business_intelligence">Business Intelligence</option>
                                <option value="operational_intelligence">Operational Intelligence</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="serverFilter">
                                <option value="">All Servers</option>
                                <option value="jcepnzzkmj">CIS Main (jcepnzzkmj)</option>
                                <option value="hdgwrzntwa">Intelligence (hdgwrzntwa)</option>
                                <option value="dvaxgvsxmz">E-commerce (dvaxgvsxmz)</option>
                                <option value="fhrehrpjmu">Analytics (fhrehrpjmu)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" id="applyFilters">
                                <i class="fas fa-filter me-1"></i> Apply
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="filesTable">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Server</th>
                                    <th>Size</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="filesTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Loading files...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center" id="pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File Viewer Modal -->
<div class="modal fade" id="fileViewerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileViewerTitle">File Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="fileViewerContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadFile">
                    <i class="fas fa-download me-1"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentFilters = {};

    function loadFiles(page = 1) {
        const tbody = document.getElementById('filesTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading files...</p></td></tr>';

        // Build query parameters
        const params = new URLSearchParams({
            action: 'list',
            page: page,
            limit: 50,
            ...currentFilters
        });

        // Fetch real data from API
        fetch(`api/files.php?${params}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    displayFiles(result.data.files);
                    updatePagination(result.data.pagination);
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error: ${result.error}</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error loading files:', error);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Failed to load files</td></tr>`;
            });
    }

    function displayFiles(files) {
        const tbody = document.getElementById('filesTableBody');

        if (files.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No files found</td></tr>';
            return;
        }

        tbody.innerHTML = files.map(file => `
            <tr>
                <td>
                    <i class="fas ${file.icon} me-2"></i>
                    <span title="${file.file_path}">${file.file_name}</span>
                </td>
                <td><span class="badge bg-${file.type_badge}">${file.type_display}</span></td>
                <td>${file.server_name}</td>
                <td>${file.file_size_formatted}</td>
                <td>${formatDate(file.updated_at)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="viewFile(${file.id}, '${escapeHtml(file.file_name)}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-info" onclick="showFilePath('${escapeHtml(file.file_path)}')">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function updatePagination(pagination) {
        const paginationEl = document.getElementById('pagination');
        const { current_page, total_pages } = pagination;

        let html = '';

        // Previous button
        html += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current_page - 1}">Previous</a>
        </li>`;

        // Page numbers (show 5 max)
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(total_pages, current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === current_page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }

        // Next button
        html += `<li class="page-item ${current_page === total_pages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current_page + 1}">Next</a>
        </li>`;

        paginationEl.innerHTML = html;

        // Add click handlers
        paginationEl.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page > 0 && page <= total_pages) {
                    currentPage = page;
                    loadFiles(page);
                }
            });
        });
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleString('en-NZ', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/'/g, '&#39;');
    }

    // Apply filters button
    document.getElementById('applyFilters').addEventListener('click', function() {
        currentFilters = {
            search: document.getElementById('fileSearch').value,
            type: document.getElementById('fileTypeFilter').value,
            server: document.getElementById('serverFilter').value
        };
        currentPage = 1;
        loadFiles(1);
    });

    // Search on Enter key
    document.getElementById('fileSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilters').click();
        }
    });

    // Initial load
    loadFiles();

    // Load stats
    loadStats();
});

function loadStats() {
    fetch('api/files.php?action=stats')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // You can display stats in a summary section if desired
                console.log('File stats:', result.data);
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}

function viewFile(id, filename) {
    const modal = new bootstrap.Modal(document.getElementById('fileViewerModal'));
    const titleEl = document.getElementById('fileViewerTitle');
    const contentEl = document.getElementById('fileViewerContent');

    titleEl.textContent = `Viewing: ${filename}`;
    contentEl.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading file content...</p></div>';

    modal.show();

    fetch(`api/files.php?action=view&id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const file = result.data.file;
                const content = result.data.content;

                // Display file info
                let html = `
                    <div class="alert alert-info mb-3">
                        <strong>File:</strong> ${file.file_path}<br>
                        <strong>Size:</strong> ${result.data.file_size_formatted}<br>
                        <strong>Type:</strong> ${file.intelligence_type}<br>
                        <strong>Server:</strong> ${result.data.server_name}<br>
                        <strong>Updated:</strong> ${file.updated_at}
                    </div>
                `;

                // Display content
                if (content.startsWith('[')) {
                    html += `<div class="alert alert-warning">${content}</div>`;
                } else {
                    // Check if it's code
                    const ext = file.file_name.split('.').pop().toLowerCase();
                    const codeExts = ['php', 'js', 'css', 'html', 'json', 'xml', 'sql', 'py', 'java', 'cpp', 'c'];

                    if (codeExts.includes(ext)) {
                        html += `<pre><code class="language-${ext}">${escapeHtml(content)}</code></pre>`;
                    } else {
                        html += `<pre>${escapeHtml(content)}</pre>`;
                    }
                }

                contentEl.innerHTML = html;

                // Store file path for download
                document.getElementById('downloadFile').dataset.filePath = file.file_path;
            } else {
                contentEl.innerHTML = `<div class="alert alert-danger">Error: ${result.error}</div>`;
            }
        })
        .catch(error => {
            contentEl.innerHTML = `<div class="alert alert-danger">Failed to load file content</div>`;
            console.error('Error viewing file:', error);
        });
}

function showFilePath(path) {
    alert(`Full path:\n${path}`);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
