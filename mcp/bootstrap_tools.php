<?php
/**
 * Bootstrap Tools - Load all tool classes into registry
 * FAST: No scanning, explicit registration
 */

require_once __DIR__ . '/src/Tools/ToolInterface.php';
require_once __DIR__ . '/src/Tools/BaseTool.php';
require_once __DIR__ . '/src/Tools/ToolRegistry.php';

// Support infrastructure for legacy tools
require_once __DIR__ . '/src/Database/Connection.php';
require_once __DIR__ . '/src/Cache/CacheInterface.php';
require_once __DIR__ . '/src/Cache/RedisCache.php';
require_once __DIR__ . '/src/Cache/APCuCache.php';
require_once __DIR__ . '/src/Cache/FileCache.php';
require_once __DIR__ . '/src/Cache/CacheManager.php';
require_once __DIR__ . '/src/Search/FuzzySearchEngine.php';
require_once __DIR__ . '/src/Analytics/SearchAnalytics.php';

// Legacy tools (ending in Tool.php - old architecture)
require_once __DIR__ . '/src/Tools/PasswordStorageTool.php';
require_once __DIR__ . '/src/Tools/SemanticSearchTool.php';
require_once __DIR__ . '/src/Tools/WebBrowserTool.php';
require_once __DIR__ . '/src/Tools/CrawlerTool.php';
require_once __DIR__ . '/src/Tools/HealthCheckTool.php';
require_once __DIR__ . '/src/Tools/SystemStatsTool.php';
require_once __DIR__ . '/src/Tools/MySQLQueryTool.php';

// New adapter tools (ending in Tools.php - new architecture)
require_once __DIR__ . '/src/Tools/DatabaseTools.php';
require_once __DIR__ . '/src/Tools/FileSystemTools.php';
require_once __DIR__ . '/src/Tools/LogsTools.php';
require_once __DIR__ . '/src/Tools/OperationsTools.php';
require_once __DIR__ . '/src/Tools/SystemTools.php';
require_once __DIR__ . '/src/Tools/ChatTools.php';
require_once __DIR__ . '/src/Tools/KnowledgeTools.php';
require_once __DIR__ . '/src/Tools/GitHubTools.php';
require_once __DIR__ . '/src/Tools/SSHTools.php';
require_once __DIR__ . '/src/Tools/PasswordTools.php';
require_once __DIR__ . '/src/Tools/SemanticTools.php';
require_once __DIR__ . '/src/Tools/BrowserTools.php';
require_once __DIR__ . '/src/Tools/CrawlerTools.php';
require_once __DIR__ . '/src/Tools/HealthCheckTools.php';
require_once __DIR__ . '/src/Tools/SystemStatsTools.php';
require_once __DIR__ . '/src/Tools/MySQLTools.php';
require_once __DIR__ . '/src/Tools/IndexerTools.php';

use IntelligenceHub\MCP\Tools\ToolRegistry;
use IntelligenceHub\MCP\Tools\DatabaseTools;
use IntelligenceHub\MCP\Tools\FileSystemTools;
use IntelligenceHub\MCP\Tools\LogsTools;
use IntelligenceHub\MCP\Tools\OperationsTools;
use IntelligenceHub\MCP\Tools\SystemTools;
use IntelligenceHub\MCP\Tools\ChatTools;
use IntelligenceHub\MCP\Tools\KnowledgeTools;
use IntelligenceHub\MCP\Tools\GitHubTools;
use IntelligenceHub\MCP\Tools\SSHTools;
use IntelligenceHub\MCP\Tools\PasswordTools;
use IntelligenceHub\MCP\Tools\SemanticTools;
use IntelligenceHub\MCP\Tools\BrowserTools;
use IntelligenceHub\MCP\Tools\CrawlerTools;
use IntelligenceHub\MCP\Tools\HealthCheckTools;
use IntelligenceHub\MCP\Tools\SystemStatsTools;
use IntelligenceHub\MCP\Tools\MySQLTools;
use IntelligenceHub\MCP\Tools\IndexerTools;

$registry = new ToolRegistry();

// Register ALL tools
$registry->registerTool(new DatabaseTools());
$registry->registerTool(new FileSystemTools());
$registry->registerTool(new LogsTools());
$registry->registerTool(new OperationsTools());
$registry->registerTool(new SystemTools());
$registry->registerTool(new ChatTools());
$registry->registerTool(new KnowledgeTools());
$registry->registerTool(new GitHubTools());
$registry->registerTool(new SSHTools());
$registry->registerTool(new PasswordTools());
$registry->registerTool(new SemanticTools());
$registry->registerTool(new BrowserTools());
$registry->registerTool(new CrawlerTools());
$registry->registerTool(new HealthCheckTools());
$registry->registerTool(new SystemStatsTools());
$registry->registerTool(new MySQLTools());
$registry->registerTool(new IndexerTools());

// Register HTTP routes (external services)
$registry->registerRoute('semantic_search', 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php');
$registry->registerRoute('conversation.get_project_context', 'https://gpt.ecigdis.co.nz/api/get_project_conversations.php');
$registry->registerRoute('ai_agent.query', 'https://gpt.ecigdis.co.nz/mcp/tools/ai_agent_query_endpoint.php');

return $registry;
