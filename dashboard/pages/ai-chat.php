<?php
/**
 * Intelligence Hub - AI Chat Interface (Dashboard Integration)
 * High-performance unified chat interface with streaming support
 */

// This is just a wrapper - the real UI is in the standalone HTML
// We'll embed it in an iframe for clean integration
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat - Intelligence Hub</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        
        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
    </style>
</head>
<body>
    <iframe src="/ai-chat.html" title="AI Chat Interface"></iframe>
</body>
</html>
