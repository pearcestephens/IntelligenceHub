/**
 * DevIDE Auto-Minimize Script
 * Automatically minimizes DevIDE on specific pages but allows reopening
 */

(function() {
    'use strict';

    // Pages where DevIDE should auto-minimize
    const autoMinimizePages = [
        '/store-view.php',
        '/order',
        '/orders',
        'vend',
        'staff.vapeshed.co.nz'
    ];

    // Check if current page should auto-minimize
    function shouldAutoMinimize() {
        const currentUrl = window.location.href.toLowerCase();
        const currentPath = window.location.pathname.toLowerCase();

        return autoMinimizePages.some(page =>
            currentUrl.includes(page.toLowerCase()) ||
            currentPath.includes(page.toLowerCase())
        );
    }

    // Minimize DevIDE iframe if present
    function minimizeDevIDE() {
        // Find DevIDE iframe
        const ideFrame = document.querySelector('iframe[src*="devide"]');

        if (ideFrame) {
            // Create minimize button if not exists
            let minimizeBtn = document.getElementById('devide-minimize-btn');

            if (!minimizeBtn) {
                minimizeBtn = document.createElement('button');
                minimizeBtn.id = 'devide-minimize-btn';
                minimizeBtn.innerHTML = '<i class="fas fa-code"></i> DevIDE';
                minimizeBtn.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #007acc;
                    color: white;
                    border: none;
                    padding: 12px 20px;
                    border-radius: 50px;
                    cursor: pointer;
                    z-index: 99999;
                    font-size: 14px;
                    font-weight: 600;
                    box-shadow: 0 4px 12px rgba(0,122,204,0.4);
                    transition: all 0.3s;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                `;

                // Hover effect
                minimizeBtn.addEventListener('mouseenter', () => {
                    minimizeBtn.style.background = '#1177bb';
                    minimizeBtn.style.transform = 'scale(1.05)';
                });

                minimizeBtn.addEventListener('mouseleave', () => {
                    minimizeBtn.style.background = '#007acc';
                    minimizeBtn.style.transform = 'scale(1)';
                });

                // Click to toggle
                minimizeBtn.addEventListener('click', () => {
                    if (ideFrame.style.display === 'none') {
                        // Restore
                        ideFrame.style.display = 'block';
                        minimizeBtn.innerHTML = '<i class="fas fa-minus"></i> Minimize DevIDE';
                        minimizeBtn.style.background = '#f48771';
                    } else {
                        // Minimize
                        ideFrame.style.display = 'none';
                        minimizeBtn.innerHTML = '<i class="fas fa-code"></i> DevIDE';
                        minimizeBtn.style.background = '#007acc';
                    }
                });

                document.body.appendChild(minimizeBtn);
            }

            // Auto-minimize on load if on specific pages
            if (shouldAutoMinimize()) {
                ideFrame.style.display = 'none';
                minimizeBtn.innerHTML = '<i class="fas fa-code"></i> DevIDE';
                minimizeBtn.style.background = '#007acc';
                console.log('DevIDE auto-minimized on this page');
            }
        }
    }

    // Run on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', minimizeDevIDE);
    } else {
        minimizeDevIDE();
    }

    // Also check after 1 second in case iframe loads late
    setTimeout(minimizeDevIDE, 1000);

})();
