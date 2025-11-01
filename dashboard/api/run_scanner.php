<?php
/**
 * API: Trigger Neural Scanner
 */
header('Content-Type: application/json');

// Trigger scanner (run in background)
$command = '/usr/bin/php /home/master/applications/hdgwrzntwa/public_html/scripts/safe_neural_scanner.php > /dev/null 2>&1 &';
exec($command);

echo json_encode([
    'success' => true,
    'message' => 'Scanner started in background. Check Scanner Status page in 5-10 minutes for results.'
]);
