/**
 * Search Page JavaScript
 * Handles advanced search functionality
 */

$(document).ready(function() {
    
    let currentResults = [];
    
    // ============================================================================
    // SEARCH FORM SUBMISSION
    // ============================================================================
    
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    // Clear search
    $('#clearSearch').on('click', function() {
        $('#searchForm')[0].reset();
        $('#searchResultsContainer').hide();
        $('#searchResults').empty();
    });
    
    // ============================================================================
    // PERFORM SEARCH
    // ============================================================================
    
    function performSearch() {
        const query = $('#searchQuery').val().trim();
        
        if (!query) {
            showNotification('Please enter a search query', 'error');
            return;
        }
        
        // Show loading
        $('#searchResultsContainer').show();
        $('#searchResults').html('<div class="text-center py-5"><div class="loading"></div><p class="mt-3">Searching...</p></div>');
        
        const searchData = {
            query: query,
            type: $('#searchType').val(),
            server: $('#searchServer').val(),
            minSize: $('#minSize').val(),
            maxSize: $('#maxSize').val(),
            dateRange: $('#dateRange').val(),
            functionsOnly: $('#functionsOnly').is(':checked'),
            caseSensitive: $('#caseSensitive').is(':checked')
        };
        
        const startTime = Date.now();
        
        console.log('Performing search with data:', searchData);
        
        $.ajax({
            url: 'api/search.php',
            method: 'GET',
            data: searchData,
            dataType: 'json',
            success: function(response) {
                console.log('Search response:', response);
                const searchTime = Date.now() - startTime;
                
                if (response.success) {
                    currentResults = response.data;
                    displaySearchResults(response.data, searchTime);
                } else {
                    const errorMsg = response.error || 'Unknown error';
                    console.error('Search failed:', errorMsg);
                    showNotification('Search failed: ' + errorMsg, 'error');
                    $('#searchResults').html('<div class="alert alert-danger">Search failed: ' + errorMsg + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Search request failed:', {xhr, status, error});
                console.error('Response text:', xhr.responseText);
                showNotification('Search request failed: ' + error, 'error');
                $('#searchResults').html('<div class="alert alert-danger">Search request failed. Check console for details.</div>');
            }
        });
    }
    
    // ============================================================================
    // DISPLAY SEARCH RESULTS
    // ============================================================================
    
    function displaySearchResults(results, searchTime) {
        $('#resultCount').text(results.length + ' result' + (results.length !== 1 ? 's' : ''));
        $('#searchTime').text('(' + searchTime + ' ms)');
        
        if (results.length === 0) {
            $('#searchResults').html(`
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No results found</p>
                </div>
            `);
            return;
        }
        
        const $container = $('#searchResults');
        $container.empty();
        
        results.forEach(result => {
            const $item = createSearchResultItem(result);
            $container.append($item);
        });
    }
    
    function createSearchResultItem(result) {
        const query = $('#searchQuery').val();
        const preview = highlightText(result.content_summary || '', query);
        
        const $item = $(`
            <div class="search-result-item">
                <div class="search-result-header">
                    <div>
                        <div class="search-result-title">
                            <i class="fas fa-file-code me-2"></i>
                            ${escapeHtml(result.file_name)}
                        </div>
                        <div class="search-result-path">
                            ${escapeHtml(result.file_path)}
                        </div>
                    </div>
                    <div>
                        ${getTypeBadge(result.intelligence_type)}
                        ${getServerBadge(result.server_id)}
                    </div>
                </div>
                <div class="search-result-meta">
                    <span class="me-3">
                        <i class="fas fa-hdd me-1"></i>
                        ${formatBytes(result.file_size || 0)}
                    </span>
                    <span>
                        <i class="fas fa-clock me-1"></i>
                        ${timeAgo(result.extracted_at)}
                    </span>
                </div>
                ${preview ? `<div class="search-result-preview">${preview}</div>` : ''}
            </div>
        `);
        
        $item.on('click', () => viewFileContent(result.file_path, result.server_id));
        
        return $item;
    }
    
    function highlightText(text, query) {
        if (!text || !query) return escapeHtml(text);
        
        // Simple highlight - in production use proper regex
        const escapedQuery = escapeRegex(query);
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        
        // Get snippet around match
        const match = text.match(regex);
        if (!match) return escapeHtml(text.substring(0, 200)) + '...';
        
        const index = text.indexOf(match[0]);
        const start = Math.max(0, index - 100);
        const end = Math.min(text.length, index + 100);
        let snippet = text.substring(start, end);
        
        if (start > 0) snippet = '...' + snippet;
        if (end < text.length) snippet = snippet + '...';
        
        return escapeHtml(snippet).replace(regex, '<span class="highlight">$1</span>');
    }
    
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    
    function getServerBadge(serverId) {
        const servers = {
            'jcepnzzkmj': { name: 'CIS', color: '#667eea' },
            'dvaxgvsxmz': { name: 'Retail', color: '#f093fb' },
            'fhrehrpjmu': { name: 'Wholesale', color: '#4facfe' },
            'hdgwrzntwa': { name: 'Intelligence', color: '#43e97b' }
        };
        
        const server = servers[serverId];
        if (!server) return `<span class="badge bg-secondary">${serverId}</span>`;
        
        return `<span class="badge" style="background: ${server.color}">
                    <i class="fas fa-server me-1"></i> ${server.name}
                </span>`;
    }
    
    // ============================================================================
    // VIEW FILE CONTENT
    // ============================================================================
    
    function viewFileContent(path, serverId) {
        $('#fileViewModal').modal('show');
        $('#fileViewTitle').text('Loading...');
        $('#fileViewContent').text('Loading file content...');
        
        $.ajax({
            url: 'api/document.php',
            method: 'GET',
            data: {
                path: path,
                server: serverId
            },
            success: function(response) {
                if (response.success) {
                    const file = response.data;
                    $('#fileViewTitle').text(file.file_name);
                    $('#fileViewType').html(getTypeBadge(file.intelligence_type));
                    $('#fileViewServer').html(getServerBadge(file.server_id));
                    $('#fileViewSize').html(`<span class="badge bg-secondary">${formatBytes(file.file_size)}</span>`);
                    
                    // Set content with syntax highlighting
                    const code = escapeHtml(file.file_content || 'No content available');
                    $('#fileViewContent').html(code);
                    
                    // Apply syntax highlighting (protect against Prism internal errors)
                    if (typeof Prism !== 'undefined') {
                        try {
                            Prism.highlightElement($('#fileViewContent')[0]);
                        } catch (err) {
                            console.error('Prism highlighting failed:', err);
                        }
                    }
                    
                    // Store current file for download
                    $('#downloadFile').data('content', file.file_content);
                    $('#downloadFile').data('filename', file.file_name);
                } else {
                    $('#fileViewContent').text('Error loading file: ' + response.error);
                }
            },
            error: function() {
                $('#fileViewContent').text('Error loading file content');
            }
        });
    }
    
    // ============================================================================
    // DOWNLOAD FILE
    // ============================================================================
    
    $('#downloadFile').on('click', function() {
        const content = $(this).data('content');
        const filename = $(this).data('filename');
        
        if (!content) {
            showNotification('No file content available', 'error');
            return;
        }
        
        const blob = new Blob([content], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename || 'file.txt';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showNotification('File downloaded successfully', 'success');
    });
    
});
