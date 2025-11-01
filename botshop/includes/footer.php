    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="footer__content">
            <p>&copy; <?= date('Y') ?> CIS Intelligence Dashboard. All rights reserved.</p>
            <p class="text-sm text-muted">Version 2.0.0 | Last scan: <?= $lastScanTime ?? 'Never' ?></p>
        </div>

        <nav class="footer__links" aria-label="Footer navigation">
            <a href="?page=documentation" class="footer__link">Documentation</a>
            <a href="?page=support" class="footer__link">Support</a>
            <a href="?page=privacy" class="footer__link">Privacy Policy</a>
            <a href="?page=terms" class="footer__link">Terms of Service</a>
        </nav>
    </footer>

    <!-- Custom JavaScript -->
    <script src="/dashboard/admin/assets/js/app.js"></script>
    <script src="/dashboard/admin/assets/js/charts.js"></script>

    <!-- Page-specific JavaScript -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline page scripts -->
    <?php if (isset($inlineScript)): ?>
        <script>
            <?= $inlineScript ?>
        </script>
    <?php endif; ?>
</body>
</html>
