<?php
/**
 * Dashboard Footer
 */
?>
<footer class="footer">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong><?php echo APP_NAME; ?></strong> v<?php echo APP_VERSION; ?>
            &copy; <?php echo date('Y'); ?> Ecigdis Limited
        </div>
        <div>
            <a href="?page=documentation" class="text-decoration-none me-3">Documentation</a>
            <a href="?page=api" class="text-decoration-none me-3">API</a>
            <a href="?page=settings" class="text-decoration-none">Settings</a>
        </div>
    </div>
</footer>
