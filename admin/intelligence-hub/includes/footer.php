    <!-- Main JavaScript -->
    <script src="assets/js/intelligence-hub.js"></script>

    <!-- Dashboard Common Scripts -->
    <script>
        // Initialize tooltips and other UI components
        document.addEventListener('DOMContentLoaded', function() {
            // Modal close handlers
            document.querySelectorAll('[data-modal-close]').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.modal').classList.remove('modal--open');
                });
            });

            // Mobile menu toggle
            const mobileToggle = document.querySelector('[data-mobile-toggle]');
            const sidebar = document.querySelector('.sidebar');

            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar--open');
                });
            }
        });
    </script>
</body>
</html>
