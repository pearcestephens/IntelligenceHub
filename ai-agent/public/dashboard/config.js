/**
 * CIS Neural AI Dashboard - Core Configuration
 * Production-grade modular dashboard system
 */

const DASHBOARD_CONFIG = {
    // API Endpoints
    API: {
        BASE_URL: '/assets/neuro/ai-agent/public/api',
        ENDPOINTS: {
            REALTIME_METRICS: '/realtime-metrics.php',
            BOT_MANAGEMENT: '/bot-management.php',
            EVENT_CHAINS: '/event-chains.php',
            TOOLS: '/tools.php',
            LOGS: '/logs.php',
            CONFIG: '/config.php'
        },
        REFRESH_INTERVAL: 5000, // 5 seconds
        TIMEOUT: 30000 // 30 seconds
    },

    // Feature Flags
    FEATURES: {
        REALTIME_UPDATES: true,
        EVENT_CHAINING: true,
        BOT_MANAGEMENT: true,
        ADVANCED_METRICS: true,
        LOG_STREAMING: true,
        WORKFLOW_BUILDER: true
    },

    // UI Settings
    UI: {
        THEME: 'dark',
        SIDEBAR_COLLAPSED: false,
        CHARTS_ENABLED: true,
        ANIMATIONS: true,
        NOTIFICATIONS: true
    },

    // Performance Targets
    TARGETS: {
        RESPONSE_TIME_P95: 700, // ms
        ERROR_RATE: 1, // percentage
        CACHE_HIT_RATE: 70, // percentage
        UPTIME: 99.9 // percentage
    }
};

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DASHBOARD_CONFIG;
}
