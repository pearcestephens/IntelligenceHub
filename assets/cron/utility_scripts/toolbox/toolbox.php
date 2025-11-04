<?php
declare(strict_types=1);
/**
 * toolbox.php — Modular WebDev Superuser Toolbox Loader
 * Purpose: Renders tabs + lazy-loads modules from modules/index.json
 * Security: No secrets; reads static files. Uses absolute paths. No DB.
 */

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cross-Origin-Opener-Policy: same-origin');

$base = '/assets/cron/utility_scripts/toolbox';
$absCss = $base . '/assets/main.css';
$absJs  = $base . '/assets/main.js';

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>WebDev Superuser Toolbox</title>
<link rel="preload" href="<?= htmlspecialchars($absCss, ENT_QUOTES) ?>" as="style">
<link rel="stylesheet" href="<?= htmlspecialchars($absCss, ENT_QUOTES) ?>">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>WebDev Superuser Toolbox</h1>
      <span class="badge right">ENV: PROD</span>
    </div>
    <div class="tabs" data-tabs></div>
    <div class="panel" data-panel>
      <div class="small">Loading modules…</div>
    </div>
  </div>

  <script src="<?= htmlspecialchars($absJs, ENT_QUOTES) ?>" data-base="<?= htmlspecialchars($base, ENT_QUOTES) ?>" defer></script>
</body>
</html>
