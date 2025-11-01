<?php
/**
 * _footer.php - Footer component
 */
?>
<footer class="footer bg-light border-top mt-5 py-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <p class="text-muted mb-0">
                    &copy; 2025 hdgwrzntwa Dashboard. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    <a href="#" class="text-muted text-decoration-none">Documentation</a> |
                    <a href="#" class="text-muted text-decoration-none">Support</a> |
                    <a href="#" class="text-muted text-decoration-none">Status</a>
                </small>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    margin-top: auto;
    border-top: 1px solid var(--border-color);
}

.app-wrapper {
    display: flex;
    min-height: 100vh;
}

.app-main {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.page-content {
    flex: 1;
    background-color: var(--light-bg);
}

@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        z-index: 100;
    }

    .sidebar.collapsed {
        transform: translateX(-100%);
    }

    .app-main {
        width: 100%;
    }
}
</style>
