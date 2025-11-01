<?php
/**
 * Footer Component
 *
 * @package Scanner
 * @version 3.0.0
 */
?>
<footer class="footer bg-light border-top mt-auto py-3">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="text-muted">
                    &copy; <?php echo date('Y'); ?> Scanner Dashboard v<?php echo SCANNER_VERSION; ?>
                    | Intelligence Hub System
                </small>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    <a href="?page=documentation" class="text-decoration-none me-3">
                        <i class="bi bi-book me-1"></i>Documentation
                    </a>
                    <a href="?page=support" class="text-decoration-none me-3">
                        <i class="bi bi-question-circle me-1"></i>Support
                    </a>
                    <a href="?page=privacy" class="text-decoration-none">
                        <i class="bi bi-shield-check me-1"></i>Privacy
                    </a>
                </small>
            </div>
        </div>
    </div>
</footer>
