<!-- ================================================================
     CIS NEURAL AI DASHBOARD - FOOTER TEMPLATE
     Modular footer component with scripts
     ================================================================ -->
            </div>
            <!-- End Content Container -->
            
            <!-- Footer -->
            <footer class="dashboard-footer" style="padding: 24px; border-top: 1px solid var(--border-color); margin-top: 48px;">
                <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-muted); font-size: 0.9rem;">
                    <div>
                        © <?php echo date('Y'); ?> Ecigdis Limited - The Vape Shed. All rights reserved.
                    </div>
                    <div style="display: flex; gap: 24px;">
                        <a href="docs/" style="color: var(--text-muted); text-decoration: none;">Documentation</a>
                        <a href="health.php" style="color: var(--text-muted); text-decoration: none;">System Status</a>
                        <a href="support.php" style="color: var(--text-muted); text-decoration: none;">Support</a>
                    </div>
                </div>
            </footer>
        </div>
        <!-- End Main Content -->
    </div>
    <!-- End Dashboard Wrapper -->
    
    <!-- ============================================================
         GLOBAL JAVASCRIPT
         ============================================================ -->
    
    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dashboard Config -->
    <script src="config.js"></script>
    
    <!-- Dashboard Core JS -->
    <script src="js/dashboard.js"></script>
    
    <!-- Page-specific scripts -->
    <?php if (isset($additionalJS)): ?>
    <?php foreach ($additionalJS as $js): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline scripts -->
    <script>
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    
                    // Store preference
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                });
                
                // Restore sidebar state
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === 'true') {
                    sidebar.classList.add('collapsed');
                }
            }
            
            // Refresh button
            const refreshBtn = document.getElementById('refreshBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    this.classList.add('rotating');
                    if (typeof window.refreshDashboard === 'function') {
                        window.refreshDashboard();
                    } else {
                        location.reload();
                    }
                    setTimeout(() => this.classList.remove('rotating'), 1000);
                });
            }
            
            // User menu dropdown (simple implementation)
            const userMenu = document.getElementById('userMenu');
            if (userMenu) {
                userMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Toggle dropdown (implement as needed)
                    console.log('User menu clicked');
                });
            }
            
            // Notifications
            const notificationsBtn = document.getElementById('notificationsBtn');
            if (notificationsBtn) {
                notificationsBtn.addEventListener('click', function() {
                    // Implement notifications panel
                    console.log('Notifications clicked');
                });
            }
            
            // Settings
            const settingsBtn = document.getElementById('settingsBtn');
            if (settingsBtn) {
                settingsBtn.addEventListener('click', function() {
                    window.location.href = 'config.php';
                });
            }
            
            // Search functionality
            const searchInput = document.querySelector('.topbar-search input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const query = e.target.value.toLowerCase();
                    if (query.length > 2) {
                        // Implement search (debounced)
                        console.log('Search query:', query);
                    }
                });
            }
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Log page view
            console.log('✅ Dashboard initialized:', {
                page: '<?php echo $currentPage ?? 'unknown'; ?>',
                timestamp: new Date().toISOString()
            });
        });
        
        // Add rotating animation for refresh button
        const style = document.createElement('style');
        style.textContent = `
            @keyframes rotate {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .rotating {
                animation: rotate 1s linear;
            }
        `;
        document.head.appendChild(style);
    </script>
    
    <!-- Inline page scripts (if any) -->
    <?php if (isset($inlineScript)): ?>
    <script>
        <?php echo $inlineScript; ?>
    </script>
    <?php endif; ?>
    
</body>
</html>
