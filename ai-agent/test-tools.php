<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Tools/Frontend/FrontendToolRegistry.php';

use App\Tools\Frontend\FrontendToolRegistry;
use App\Logger;

$logger = new Logger('test');
$registry = new FrontendToolRegistry($logger);
$tools = $registry->getTools();

echo "✅ Frontend tools registered:\n\n";
foreach ($tools as $name => $tool) {
    echo "  - $name: " . $tool->getDescription() . "\n";
}
echo "\nTotal: " . count($tools) . " tools\n";
