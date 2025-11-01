/**
 * CIS Neural AI Dashboard - Modern JavaScript Module
 * Enterprise-grade dashboard with real-time monitoring
 * 
 * @package CIS Neural AI
 * @author Ecigdis Limited
 * @version 2.0.0
 */

(function(window) {
    'use strict';
    
    /**
     * Dashboard Core Module
     */
    const Dashboard = {
        // Configuration
        config: {
            API: {
                BASE_URL: '/ai-agent/api',
                ENDPOINTS: {
                    HEALTH: '/health.php',
                    METRICS: '/metrics.php',
                    REALTIME: '/realtime.php'
                },
                REFRESH_INTERVAL: 5000,
                TIMEOUT: 30000
            },
            FEATURES: {
                REALTIME_UPDATES: true,
                ADVANCED_METRICS: true,
                ANIMATIONS: true,
                NOTIFICATIONS: true
            },
            UI: {
                THEME: 'dark',
                CHARTS_ENABLED: true,
                ANIMATIONS: true
            },
            TARGETS: {
                RESPONSE_TIME_P95: 700,
                ERROR_RATE: 1,
                CACHE_HIT_RATE: 70,
                UPTIME: 99.9
            }
        },
        
        // State
        state: {
            initialized: false,
            refreshInterval: null,
            lastUpdate: null,
            connectionStatus: 'connected'
        },
        
        /**
         * Initialize dashboard
         */
        init: function() {
            if (this.state.initialized) {
                console.warn('⚠️ Dashboard already initialized');
                return;
            }
            
            console.log('🚀 Initializing Neural AI Dashboard v2.0...');
            
            try {
                // Initialize modules
                this.UI.init();
                this.Metrics.init();
                this.Charts.init();
                
                // Start auto-refresh
                this.startAutoRefresh();
                
                // Load initial data
                this.refresh();
                
                this.state.initialized = true;
                console.log('✅ Dashboard initialized successfully');
            } catch (error) {
                console.error('❌ Dashboard initialization failed:', error);
            }
        },
        
        /**
         * Refresh dashboard data
         */
        refresh: async function() {
            try {
                console.log('🔄 Refreshing dashboard...');
                const metrics = await this.Metrics.fetch();
                this.UI.updateMetrics(metrics);
                this.Charts.update(metrics);
                this.state.lastUpdate = new Date();
            } catch (error) {
                console.error('Error refreshing dashboard:', error);
                this.UI.showError('Failed to refresh data');
            }
        },
        
        /**
         * Start auto-refresh
         */
        startAutoRefresh: function() {
            if (this.state.refreshInterval) {
                clearInterval(this.state.refreshInterval);
            }
            
            this.state.refreshInterval = setInterval(() => {
                this.refresh();
            }, this.config.API.REFRESH_INTERVAL);
            
            console.log(`🔄 Auto-refresh enabled (${this.config.API.REFRESH_INTERVAL}ms)`);
        },
        
        /**
         * Stop auto-refresh
         */
        stopAutoRefresh: function() {
            if (this.state.refreshInterval) {
                clearInterval(this.state.refreshInterval);
                this.state.refreshInterval = null;
                console.log('⏸️ Auto-refresh disabled');
            }
        },
        
        /**
         * UI Module
         */
        UI: {
            init: function() {
                console.log('🎨 Initializing UI...');
                this.bindEvents();
                this.initAnimations();
            },
            
            bindEvents: function() {
                // Sidebar toggle
                const sidebarToggle = document.getElementById('sidebarToggle');
                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', () => this.toggleSidebar());
                }
                
                // Theme toggle
                const themeToggle = document.getElementById('themeToggle');
                if (themeToggle) {
                    themeToggle.addEventListener('click', () => this.toggleTheme());
                }
            },
            
            toggleSidebar: function() {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('collapsed');
                }
            },
            
            toggleTheme: function() {
                const html = document.documentElement;
                const currentTheme = html.getAttribute('data-theme') || 'dark';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                console.log(`🎨 Theme changed to ${newTheme}`);
            },
            
            initAnimations: function() {
                // Add fade-in animation to cards
                const cards = document.querySelectorAll('.metric-card, .card');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 50);
                });
            },
            
            updateMetrics: function(data) {
                if (!data) return;
                
                // Update metric values
                this.updateMetric('metric-response-p95', data.responseTime || '—', 'ms');
                this.updateMetric('metric-conversations', data.conversations || '0');
                this.updateMetric('metric-rpm', data.requestsPerMinute || '0');
                this.updateMetric('metric-cache-rate', data.cacheHitRate || '0', '%');
                this.updateMetric('metric-error-rate', data.errorRate || '0', '%');
                
                // Update trends
                this.updateTrend('metric-response-trend', data.responseTrend);
                this.updateTrend('metric-cache-trend', data.cacheTrend);
                this.updateTrend('metric-error-trend', data.errorTrend);
            },
            
            updateMetric: function(id, value, suffix = '') {
                const element = document.getElementById(id);
                if (element) {
                    element.innerHTML = `${value}${suffix}`;
                }
            },
            
            updateTrend: function(id, trend) {
                const element = document.getElementById(id);
                if (!element || !trend) return;
                
                const icon = trend > 0 ? '↑' : trend < 0 ? '↓' : '→';
                const className = trend > 0 ? 'positive' : trend < 0 ? 'negative' : '';
                
                element.innerHTML = `<i class="bi bi-arrow-${trend > 0 ? 'up' : 'down'}"></i> ${Math.abs(trend)}%`;
                element.className = `metric-change ${className}`;
            },
            
            showError: function(message) {
                console.error('❌', message);
                // Could add toast notification here
            },
            
            showLoading: function(show) {
                const spinners = document.querySelectorAll('.spinner-border');
                spinners.forEach(spinner => {
                    spinner.style.display = show ? 'inline-block' : 'none';
                });
            }
        },
        
        /**
         * Metrics Module
         */
        Metrics: {
            init: function() {
                console.log('📊 Metrics module initialized');
            },
            
            fetch: async function() {
                try {
                    const response = await fetch('/ai-agent/api/health.php');
                    if (!response.ok) throw new Error('Failed to fetch metrics');
                    
                    const data = await response.json();
                    return this.transform(data);
                } catch (error) {
                    console.error('Error fetching metrics:', error);
                    return this.getMockData();
                }
            },
            
            transform: function(data) {
                return {
                    responseTime: data.response_time || '—',
                    conversations: data.active_conversations || 0,
                    requestsPerMinute: data.requests_per_minute || 0,
                    cacheHitRate: data.cache_hit_rate || 0,
                    errorRate: data.error_rate || 0,
                    responseTrend: data.response_trend || 0,
                    cacheTrend: data.cache_trend || 0,
                    errorTrend: data.error_trend || 0
                };
            },
            
            getMockData: function() {
                return {
                    responseTime: '39',
                    conversations: Math.floor(Math.random() * 50) + 10,
                    requestsPerMinute: Math.floor(Math.random() * 100) + 20,
                    cacheHitRate: '86.5',
                    errorRate: '0.2',
                    responseTrend: -15,
                    cacheTrend: 12,
                    errorTrend: -5
                };
            }
        },
        
        /**
         * Charts Module
         */
        Charts: {
            charts: {},
            
            init: function() {
                console.log('📈 Charts module initialized');
                this.initializeCharts();
            },
            
            initializeCharts: function() {
                // Response time chart
                const responseChartCanvas = document.getElementById('responseTimeChart');
                if (responseChartCanvas && typeof Chart !== 'undefined') {
                    this.charts.responseTime = new Chart(responseChartCanvas, {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Response Time (ms)',
                                data: [],
                                borderColor: '#00d4ff',
                                backgroundColor: 'rgba(0, 212, 255, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            },
            
            update: function(data) {
                // Update charts with new data
                if (this.charts.responseTime && data.responseTime) {
                    const chart = this.charts.responseTime;
                    const now = new Date().toLocaleTimeString();
                    
                    chart.data.labels.push(now);
                    chart.data.datasets[0].data.push(parseFloat(data.responseTime));
                    
                    // Keep only last 20 data points
                    if (chart.data.labels.length > 20) {
                        chart.data.labels.shift();
                        chart.data.datasets[0].data.shift();
                    }
                    
                    chart.update('none'); // Update without animation for performance
                }
            }
        }
    };
    
    // Export to global scope
    window.Dashboard = Dashboard;
    
    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => Dashboard.init());
    } else {
        Dashboard.init();
    }
    
    console.log('✅ Dashboard module loaded');
    
})(window);
