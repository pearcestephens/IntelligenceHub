/**
 * 10-init.js - Initialization and startup
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Dashboard initialization started');

    // Initialize navigation
    if (typeof Nav !== 'undefined') {
        Nav.init();
    }

    // Initialize storage
    if (typeof Storage !== 'undefined') {
        const theme = Storage.get('dashboard-theme', 'local') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
    }

    // Initialize all tables
    if (typeof Tables !== 'undefined') {
        document.querySelectorAll('[data-table="true"]').forEach(table => {
            Tables.init(table.id ? '#' + table.id : null);
        });
    }

    // Initialize tooltips
    initializeTooltips();

    // Setup keyboard shortcuts
    setupKeyboardShortcuts();

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    console.log('✓ Dashboard initialization complete');
});

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    document.querySelectorAll('[title]').forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.getAttribute('title');
            tooltip.style.position = 'absolute';
            tooltip.style.zIndex = '9999';
            tooltip.style.backgroundColor = '#212529';
            tooltip.style.color = '#fff';
            tooltip.style.padding = '0.5rem';
            tooltip.style.borderRadius = '0.25rem';
            tooltip.style.fontSize = '0.875rem';
            tooltip.style.whiteSpace = 'nowrap';

            document.body.appendChild(tooltip);

            const rect = element.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
        });

        element.addEventListener('mouseleave', () => {
            document.querySelectorAll('.tooltip').forEach(t => t.remove());
        });
    });
}

/**
 * Setup keyboard shortcuts
 */
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('[data-search]');
            if (searchInput) searchInput.focus();
        }

        // Escape to close modals
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => {
                modal.classList.remove('show');
                modal.style.display = 'none';
            });
        }
    });
}

/**
 * Global error handler
 */
window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
    if (typeof Notify !== 'undefined') {
        Notify.error('An error occurred: ' + event.error.message, 10000);
    }
});

/**
 * Performance monitoring
 */
window.addEventListener('load', () => {
    if (window.performance && window.performance.timing) {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log('Page load time: ' + pageLoadTime + 'ms');
    }
});

console.log('✓ Init module loaded');
