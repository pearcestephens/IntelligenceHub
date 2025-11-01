<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
use App\Tools\ToolCatalog;
file_put_contents(__DIR__ . '/../docs/TOOLS-CATALOG.yaml', ToolCatalog::toYaml(ToolCatalog::getSpecs(true)));
echo "Wrote docs/TOOLS-CATALOG.yaml\n";
