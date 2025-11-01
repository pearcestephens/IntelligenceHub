/**
 * CIS NEURAL NETWORK FRONTEND INTEGRATION
 * Real-time neural network intelligence for CIS dashboards
 */

class CISNeuralFrontend {
    constructor() {
        this.neuralEndpoint = '/assets/neuro/ai-agent/src/cis-neural-bridge.php';
        this.updateInterval = 300000; // 5 minutes
        this.isTraining = false;
        this.confidence = 0.7;
        this.cache = new Map();
        
        this.initializeNeuralInterface();
        this.startNeuralUpdates();
    }
    
    /**
     * Initialize Neural Network Interface
     */
    initializeNeuralInterface() {
        // Create neural control panel
        this.createNeuralControlPanel();
        
        // Auto-detect dashboard type and inject neural intelligence
        this.detectAndInjectNeuralAnalysis();
        
        // Setup real-time neural updates
        this.setupNeuralWebSocket();
        
        console.log('🧠 CIS Neural Network Frontend Initialized');
    }
    
    /**
     * Create Neural Network Control Panel
     */
    createNeuralControlPanel() {
        const controlPanel = `
            <div id="neural-control-panel" class="neural-control-panel">
                <div class="card border-success">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">🧠 Neural Network Control</h6>
                        <div class="neural-status">
                            <span id="neural-status" class="badge badge-light">Initializing...</span>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="row no-gutters">
                            <div class="col-md-3">
                                <div class="form-group mb-1">
                                    <label class="small mb-1">Confidence Threshold</label>
                                    <input type="range" class="form-control-range" id="confidence-slider" 
                                           min="0.1" max="1.0" step="0.1" value="0.7">
                                    <small class="text-muted">Current: <span id="confidence-value">70%</span></small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-1">
                                    <label class="small mb-1">Neural Networks</label>
                                    <select class="form-control form-control-sm" id="neural-network-select">
                                        <option value="all">All Networks</option>
                                        <option value="inventory">Inventory Prediction</option>
                                        <option value="customer">Customer Behavior</option>
                                        <option value="sales">Sales Forecasting</option>
                                        <option value="pricing">Price Optimization</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-1">
                                    <label class="small mb-1">Actions</label>
                                    <div class="btn-group btn-group-sm d-flex">
                                        <button class="btn btn-outline-primary" id="train-neural-btn">
                                            <i class="fas fa-graduation-cap"></i> Train
                                        </button>
                                        <button class="btn btn-outline-info" id="refresh-neural-btn">
                                            <i class="fas fa-sync"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-1">
                                    <label class="small mb-1">Performance</label>
                                    <div class="progress" style="height: 20px;">
                                        <div id="neural-performance-bar" class="progress-bar bg-info" 
                                             style="width: 87%">87% Accuracy</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Inject control panel at top of main content
        const mainContent = document.querySelector('.container-fluid, .container, main, #main-content');
        if (mainContent) {
            mainContent.insertAdjacentHTML('afterbegin', controlPanel);
            this.bindNeuralControlEvents();
        }
    }
    
    /**
     * Bind Neural Control Panel Events
     */
    bindNeuralControlEvents() {
        // Confidence slider
        const confidenceSlider = document.getElementById('confidence-slider');
        const confidenceValue = document.getElementById('confidence-value');
        
        confidenceSlider?.addEventListener('input', (e) => {
            this.confidence = parseFloat(e.target.value);
            confidenceValue.textContent = Math.round(this.confidence * 100) + '%';
            this.refreshNeuralAnalysis();
        });
        
        // Train neural networks
        document.getElementById('train-neural-btn')?.addEventListener('click', () => {
            this.trainNeuralNetworks();
        });
        
        // Refresh neural analysis
        document.getElementById('refresh-neural-btn')?.addEventListener('click', () => {
            this.refreshNeuralAnalysis();
        });
        
        // Network selection
        document.getElementById('neural-network-select')?.addEventListener('change', (e) => {
            this.filterNeuralAnalysis(e.target.value);
        });
    }
    
    /**
     * Detect Dashboard Type and Inject Neural Analysis
     */
    detectAndInjectNeuralAnalysis() {
        const currentPath = window.location.pathname;
        const dashboardType = this.detectDashboardType(currentPath);
        
        if (dashboardType) {
            this.injectNeuralAnalysis(dashboardType);
        }
    }
    
    /**
     * Detect Dashboard Type from URL
     */
    detectDashboardType(path) {
        const dashboardMap = {
            '/inventory': 'inventory',
            '/stock': 'inventory',
            '/products': 'inventory',
            '/sales': 'sales',
            '/reports': 'sales',
            '/customers': 'customer',
            '/pricing': 'pricing',
            '/dashboard': 'general'
        };
        
        for (const [pattern, type] of Object.entries(dashboardMap)) {
            if (path.includes(pattern)) {
                return type;
            }
        }
        
        return 'general';
    }
    
    /**
     * Inject Neural Analysis into Current Dashboard
     */
    async injectNeuralAnalysis(dashboardType) {
        const neuralContainer = this.createNeuralAnalysisContainer(dashboardType);
        
        // Find best location to inject neural analysis
        const targetContainer = this.findBestInjectionPoint();
        if (targetContainer) {
            targetContainer.insertAdjacentHTML('afterbegin', neuralContainer);
            
            // Load neural analysis
            await this.loadNeuralAnalysis(dashboardType);
        }
    }
    
    /**
     * Create Neural Analysis Container
     */
    createNeuralAnalysisContainer(dashboardType) {
        return `
            <div id="neural-analysis-main" class="neural-analysis-main mb-4">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🧠 Neural Network Intelligence</h5>
                            <div class="neural-controls">
                                <button class="btn btn-sm btn-outline-light" id="toggle-neural-details">
                                    <i class="fas fa-chart-line"></i> Details
                                </button>
                                <button class="btn btn-sm btn-outline-light" id="export-neural-insights">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="neural-loading" class="text-center py-4">
                            <div class="spinner-border text-info" role="status">
                                <span class="sr-only">Neural networks analyzing...</span>
                            </div>
                            <p class="mt-2 text-muted">Deep learning models processing your data...</p>
                        </div>
                        <div id="neural-results" style="display: none;">
                            <!-- Neural analysis results will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Find Best Injection Point for Neural Analysis
     */
    findBestInjectionPoint() {
        const selectors = [
            '.dashboard-content',
            '.main-content',
            '.container-fluid .row:first-child',
            '.container .row:first-child',
            'main .container',
            'body .container:first-child'
        ];
        
        for (const selector of selectors) {
            const element = document.querySelector(selector);
            if (element) {
                return element;
            }
        }
        
        return document.body;
    }
    
    /**
     * Load Neural Analysis from Backend
     */
    async loadNeuralAnalysis(dashboardType) {
        try {
            const cacheKey = `neural_${dashboardType}_${this.confidence}`;
            
            // Check cache first
            if (this.cache.has(cacheKey)) {
                const cachedResult = this.cache.get(cacheKey);
                if (Date.now() - cachedResult.timestamp < 300000) { // 5 minutes
                    this.displayNeuralResults(cachedResult.data);
                    return;
                }
            }
            
            const response = await fetch(this.neuralEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    get_neural_analysis: '1',
                    dashboard_type: dashboardType,
                    confidence_threshold: this.confidence
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Cache result
                this.cache.set(cacheKey, {
                    data: result.analysis,
                    timestamp: Date.now()
                });
                
                this.displayNeuralResults(result.analysis);
                this.updateNeuralStatus('Active', 'success');
            } else {
                throw new Error(result.error || 'Neural analysis failed');
            }
            
        } catch (error) {
            console.error('Neural analysis error:', error);
            this.displayNeuralError(error.message);
            this.updateNeuralStatus('Error', 'danger');
        }
    }
    
    /**
     * Display Neural Analysis Results
     */
    displayNeuralResults(analysis) {
        const resultsContainer = document.getElementById('neural-results');
        const loadingContainer = document.getElementById('neural-loading');
        
        if (!resultsContainer) return;
        
        let html = `
            <div class="neural-analysis-results">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h6 class="text-info mb-2">${analysis.analysis_type}</h6>
                        <div class="neural-insights">
                            ${analysis.neural_insights ? analysis.neural_insights.map(insight => 
                                `<span class="badge badge-light mr-1 mb-1">${insight}</span>`
                            ).join('') : ''}
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="neural-metrics">
                            <div class="metric-item">
                                <span class="metric-label">Confidence:</span>
                                <span class="metric-value badge ${this.getConfidenceBadgeClass(analysis.confidence_score)}">
                                    ${Math.round((analysis.confidence_score || 0.5) * 100)}%
                                </span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Priority:</span>
                                <span class="metric-value badge ${this.getPriorityBadgeClass(analysis.recommendation_priority)}">
                                    ${analysis.recommendation_priority || 'MEDIUM'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
        `;
        
        // Add specific analysis content based on type
        if (analysis.predictions && analysis.predictions.length > 0) {
            html += this.renderPredictionsTable(analysis.predictions, analysis.analysis_type);
        }
        
        if (analysis.high_risk_customers) {
            html += this.renderHighRiskCustomers(analysis.high_risk_customers);
        }
        
        if (analysis.price_adjustments) {
            html += this.renderPriceAdjustments(analysis.price_adjustments);
        }
        
        html += '</div>';
        
        resultsContainer.innerHTML = html;
        loadingContainer.style.display = 'none';
        resultsContainer.style.display = 'block';
        
        // Add interactive elements
        this.bindResultInteractions();
    }
    
    /**
     * Render Predictions Table
     */
    renderPredictionsTable(predictions, analysisType) {
        const tableId = `neural-predictions-${Date.now()}`;
        
        let html = `
            <div class="neural-predictions mb-4">
                <h6 class="mb-2">Neural Network Predictions</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover" id="${tableId}">
                        <thead class="thead-light">
        `;
        
        if (predictions.length > 0) {
            // Generate table headers from first prediction
            const firstPrediction = predictions[0];
            html += '<tr>';
            for (const key in firstPrediction) {
                if (key !== 'confidence') {
                    html += `<th>${this.formatColumnHeader(key)}</th>`;
                }
            }
            html += '<th>Confidence</th></tr>';
            html += '</thead><tbody>';
            
            // Generate table rows
            predictions.forEach((prediction, index) => {
                const confidenceClass = this.getConfidenceBadgeClass(prediction.confidence || 0.5);
                html += '<tr>';
                
                for (const key in prediction) {
                    if (key !== 'confidence') {
                        html += `<td>${this.formatCellValue(prediction[key], key)}</td>`;
                    }
                }
                
                html += `<td><span class="badge ${confidenceClass}">${Math.round((prediction.confidence || 0.5) * 100)}%</span></td>`;
                html += '</tr>';
            });
        }
        
        html += '</tbody></table></div></div>';
        
        return html;
    }
    
    /**
     * Format Column Header
     */
    formatColumnHeader(key) {
        return key.replace(/_/g, ' ')
                 .replace(/\b\w/g, l => l.toUpperCase())
                 .replace('Neural ', '');
    }
    
    /**
     * Format Cell Value
     */
    formatCellValue(value, key) {
        if (typeof value === 'number') {
            if (key.includes('price') || key.includes('cost') || key.includes('value')) {
                return '$' + value.toFixed(2);
            }
            if (key.includes('risk') || key.includes('score') || key.includes('margin')) {
                return (value * 100).toFixed(1) + '%';
            }
            return value.toFixed(2);
        }
        
        return value || 'N/A';
    }
    
    /**
     * Get Confidence Badge Class
     */
    getConfidenceBadgeClass(confidence) {
        if (confidence >= 0.8) return 'badge-success';
        if (confidence >= 0.6) return 'badge-warning';
        return 'badge-danger';
    }
    
    /**
     * Get Priority Badge Class
     */
    getPriorityBadgeClass(priority) {
        switch (priority) {
            case 'HIGH': return 'badge-danger';
            case 'MEDIUM': return 'badge-warning';
            case 'LOW': return 'badge-success';
            default: return 'badge-secondary';
        }
    }
    
    /**
     * Display Neural Error
     */
    displayNeuralError(errorMessage) {
        const resultsContainer = document.getElementById('neural-results');
        const loadingContainer = document.getElementById('neural-loading');
        
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Neural Network Unavailable</h6>
                    <p class="mb-0">Unable to generate neural analysis: ${errorMessage}</p>
                    <small class="text-muted">The system will retry automatically in 5 minutes.</small>
                </div>
            `;
            
            loadingContainer.style.display = 'none';
            resultsContainer.style.display = 'block';
        }
    }
    
    /**
     * Update Neural Status
     */
    updateNeuralStatus(status, type) {
        const statusElement = document.getElementById('neural-status');
        if (statusElement) {
            statusElement.className = `badge badge-${type}`;
            statusElement.textContent = status;
        }
    }
    
    /**
     * Train Neural Networks
     */
    async trainNeuralNetworks() {
        const trainBtn = document.getElementById('train-neural-btn');
        if (!trainBtn || this.isTraining) return;
        
        this.isTraining = true;
        trainBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Training...';
        trainBtn.disabled = true;
        
        try {
            // Call neural network training endpoint
            const response = await fetch(this.neuralEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    train_neural_networks: '1'
                })
            });
            
            if (response.ok) {
                this.updateNeuralStatus('Training Complete', 'success');
                this.refreshNeuralAnalysis();
            } else {
                throw new Error('Training failed');
            }
            
        } catch (error) {
            console.error('Training error:', error);
            this.updateNeuralStatus('Training Failed', 'danger');
        } finally {
            this.isTraining = false;
            trainBtn.innerHTML = '<i class="fas fa-graduation-cap"></i> Train';
            trainBtn.disabled = false;
        }
    }
    
    /**
     * Refresh Neural Analysis
     */
    async refreshNeuralAnalysis() {
        this.cache.clear();
        const currentDashboard = this.detectDashboardType(window.location.pathname);
        
        const loadingContainer = document.getElementById('neural-loading');
        const resultsContainer = document.getElementById('neural-results');
        
        if (loadingContainer && resultsContainer) {
            loadingContainer.style.display = 'block';
            resultsContainer.style.display = 'none';
        }
        
        await this.loadNeuralAnalysis(currentDashboard);
    }
    
    /**
     * Setup Neural WebSocket for Real-time Updates
     */
    setupNeuralWebSocket() {
        // Placeholder for WebSocket implementation
        // This would connect to your Node.js AI agent for real-time updates
        console.log('🔗 Neural WebSocket setup (placeholder for real-time updates)');
    }
    
    /**
     * Start Neural Updates Timer
     */
    startNeuralUpdates() {
        setInterval(() => {
            this.refreshNeuralAnalysis();
        }, this.updateInterval);
    }
    
    /**
     * Bind Result Interactions
     */
    bindResultInteractions() {
        // Add click handlers for interactive elements in results
        document.querySelectorAll('.neural-predictions tr[data-prediction-id]').forEach(row => {
            row.addEventListener('click', (e) => {
                this.showPredictionDetails(e.target.closest('tr').dataset.predictionId);
            });
        });
    }
    
    /**
     * Show Prediction Details
     */
    showPredictionDetails(predictionId) {
        // Placeholder for detailed prediction view
        console.log('🔍 Show prediction details for:', predictionId);
    }
}

// Initialize Neural Frontend when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.cisNeural = new CISNeuralFrontend();
});

// Add CSS styles for neural interface
const neuralStyles = `
<style>
.neural-control-panel {
    position: sticky;
    top: 10px;
    z-index: 1000;
    margin-bottom: 20px;
}

.neural-analysis-main {
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.neural-predictions table tr:hover {
    background-color: rgba(23, 162, 184, 0.1) !important;
    cursor: pointer;
}

.metric-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.metric-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.neural-insights .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.neural-loading {
    min-height: 100px;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.card-header .neural-controls .btn {
    margin-left: 5px;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

#confidence-slider {
    width: 100%;
}

.form-control-sm {
    font-size: 0.75rem;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', neuralStyles);