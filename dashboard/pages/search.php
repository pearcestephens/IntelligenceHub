<?php
/**
 * Intelligence Search Page
 * Advanced search interface with filters
 */
?>

<div class="page-header">
    <h1 class="page-title">Intelligence Search</h1>
    <p class="page-subtitle">Search across <?php echo number_format(getSystemStats()['total_files']); ?> files with advanced filters</p>
</div>

<!-- Search Interface -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="searchForm">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">Search Query</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchQuery" placeholder="Enter search terms..." autofocus>
                            </div>
                        </div>
                        
                        <div class="col-lg-3">
                            <label class="form-label">File Type</label>
                            <select class="form-select form-select-lg" id="searchType">
                                <option value="">All Types</option>
                                <option value="code_php">PHP Code</option>
                                <option value="code_js">JavaScript</option>
                                <option value="code_python">Python</option>
                                <option value="documentation">Documentation</option>
                                <option value="business_data">Business Data</option>
                                <option value="config">Configuration</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-3">
                            <label class="form-label">Server</label>
                            <select class="form-select form-select-lg" id="searchServer">
                                <option value="">All Servers</option>
                                <?php foreach (SERVERS as $id => $server): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($server['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg" id="clearSearch">
                                <i class="fas fa-times me-2"></i> Clear
                            </button>
                            <button type="button" class="btn btn-outline-info btn-lg ms-2" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                <i class="fas fa-sliders-h me-2"></i> Advanced Filters
                            </button>
                        </div>
                    </div>
                    
                    <!-- Advanced Filters -->
                    <div class="collapse mt-3" id="advancedFilters">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Min File Size</label>
                                        <input type="number" class="form-control" id="minSize" placeholder="Bytes">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Max File Size</label>
                                        <input type="number" class="form-control" id="maxSize" placeholder="Bytes">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date Range</label>
                                        <select class="form-select" id="dateRange">
                                            <option value="">Any Time</option>
                                            <option value="1">Last 24 Hours</option>
                                            <option value="7">Last 7 Days</option>
                                            <option value="30">Last 30 Days</option>
                                            <option value="90">Last 90 Days</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Search in Functions Only</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="functionsOnly">
                                            <label class="form-check-label" for="functionsOnly">
                                                Only search within function definitions
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Case Sensitive</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="caseSensitive">
                                            <label class="form-check-label" for="caseSensitive">
                                                Match exact case
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Search Results -->
<div class="row mt-4" id="searchResultsContainer" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Search Results</h5>
                <div>
                    <span id="resultCount" class="badge bg-primary">0 results</span>
                    <span id="searchTime" class="text-muted ms-2"></span>
                </div>
            </div>
            <div class="card-body">
                <div id="searchResults">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File View Modal -->
<div class="modal fade" id="fileViewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileViewTitle">File Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span id="fileViewType"></span>
                    <span id="fileViewServer" class="ms-2"></span>
                    <span id="fileViewSize" class="ms-2"></span>
                </div>
                <pre><code id="fileViewContent" class="language-php">Loading...</code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadFile">
                    <i class="fas fa-download me-2"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.search-result-item {
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    margin-bottom: 15px;
    transition: all 0.3s;
    cursor: pointer;
}

.search-result-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.search-result-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 10px;
}

.search-result-title {
    font-weight: 600;
    color: var(--primary-color);
    font-size: 1.1rem;
}

.search-result-path {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.search-result-preview {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    margin-top: 10px;
    white-space: pre-wrap;
}

.highlight {
    background: yellow;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>
