# Code Structure Intelligence

**Generated:** 2025-10-28 11:50:03

## Summary

- **PHP Files:** 427
- **Classes:** 226
- **Functions:** 2817
- **Dependencies:** 2

## Class Inventory

| Class | File | Extends | Implements |
|-------|------|---------|------------|
| `Color` | scripts/kb-harvest.php | - | - |
| `DualServerKBScanner` | scripts/dual_server_kb_scanner.php | - | - |
| `MDKnowledgeConsolidator` | scripts/md_knowledge_consolidator.php | - | - |
| `SafeNeuralScanner` | scripts/safe_neural_scanner.php | - | - |
| `needed` | scripts/kb_proactive_indexer.php | - | - |
| `SimpleRedis` | scripts/kb_proactive_indexer.php | - | - |
| `KBContentAnalyzer` | scripts/kb_content_analyzer.php | - | - |
| `signature` | scripts/kb_content_analyzer.php | - | - |
| `usage` | scripts/kb_content_analyzer.php | - | - |
| `EnhancedSecurityScanner` | scripts/enhanced_security_scanner.php | - | - |
| `LocalNeuralIntelligenceScanner` | scripts/local_neural_scanner.php | - | - |
| `MasterIndexGenerator` | scripts/master_index_generator.php | - | - |
| `ComprehensiveMDScanner` | scripts/comprehensive_md_scanner.php | - | - |
| `UserActivityTracker` | scripts/user_activity_tracker.php | - | - |
| `ComprehensiveReadmeGenerator` | scripts/comprehensive_readme_generator.php | - | - |
| `extraction` | scripts/kb_intelligence_engine.php | - | - |
| `KBIntelligenceEngine` | scripts/kb_intelligence_engine.php | - | - |
| `Inventory` | scripts/kb_intelligence_engine.php | - | - |
| `MDExtractor` | scripts/extract_all_md_to_kb.php | - | - |
| `dependencies` | scripts/_kb_tools_map-relationships.php | - | - |
| `dependencies` | scripts/_kb_tools_map-relationships.php | - | - |
| `dependencies` | scripts/_kb_tools_map-relationships.php | - | - |
| `usage` | scripts/_kb_tools_map-relationships.php | - | - |
| `SmartMDScanner` | scripts/smart_md_scanner.php | - | - |
| `DeepIntelligenceEngine` | scripts/kb_deep_intelligence.php | - | - |
| `KBRelationshipMapper` | scripts/kb-relationship-mapper.php | - | - |
| `definitions` | scripts/kb-relationship-mapper.php | - | - |
| `definitions` | scripts/kb-relationship-mapper.php | - | - |
| `usage` | scripts/kb-relationship-mapper.php | - | - |
| `usage` | scripts/kb-relationship-mapper.php | - | - |
| `usage` | scripts/kb-relationship-mapper.php | - | - |
| `registry` | scripts/kb-relationship-mapper.php | - | - |
| `NuclearKBCleanup` | scripts/nuclear_kb_cleanup.php | - | - |
| `CognitiveContentAnalyzer` | scripts/cognitive_content_analyzer.php | - | - |
| `KBIndexerConfig` | scripts/kb-auto-indexer.php | - | - |
| `KBAutoIndexer` | scripts/kb-auto-indexer.php | - | - |
| `NeuralIntelligenceScanner` | scripts/neural_intelligence_scanner.php | - | - |
| `definitions` | scripts/neural_intelligence_scanner.php | - | - |
| `QuickKBAccess` | scripts/neural_intelligence_scanner.php | - | - |
| `KBCleanupConfig` | scripts/kb-cleanup.php | - | - |
| `class` | scripts/kb-cleanup.php | - | - |
| `usage` | scripts/kb_correlator.php | - | - |
| `names` | scripts/process_content_text.php | - | - |
| `EnhancedKBCrawler` | scripts/enhanced_kb_crawler.php | - | - |
| `SmartKBTrigger` | scripts/smart_kb_trigger.php | - | - |
| `DatabaseSchemaScanner` | scripts/db_schema_scanner.php | - | - |
| `CodeQualityScorer` | scripts/code_quality_scorer.php | - | - |
| `MultiDatabaseScanner` | scripts/scan_all_databases.php | - | - |
| `MultiDatabaseSchemaScanner` | scripts/multi_db_schema_scanner.php | - | - |
| `SmartKBOrganizer` | scripts/smart_kb_organizer.php | - | - |
| `Color` | scripts/kb-cli.php | - | - |
| `declaration` | scripts/migrate_scanner_to_new_tables.php | - | - |
| `names` | scripts/migrate_scanner_to_new_tables.php | - | - |
| `KBIntelligenceEngineV2` | scripts/kb_intelligence_engine_v2.php | - | - |
| `KBReadmeGenerator` | scripts/kb-readme-generator.php | - | - |
| `Usage` | scripts/kb-readme-generator.php | - | - |
| `Inheritance` | scripts/kb-readme-generator.php | - | - |
| `KnowledgeBaseSDK` | scripts/kb-sdk.php | - | - |
| `RedisService` | app/Services/RedisService.php | - | - |
| `AgentKnowledgeBaseAPI` | api/agent_kb.php | - | - |
| `MultiBotCollaborationAPI` | api/multi-bot-collaboration.php | - | - |
| `NeuralIntelligenceProcessor` | api/intelligence/neural_intelligence_processor.php | - | - |
| `patterns` | api/intelligence/neural_intelligence_processor.php | - | - |
| `APINeuralScanner` | api/intelligence/api_neural_scanner.php | - | - |
| `for` | api/intelligence/api_neural_scanner.php | - | - |
| `IntelligenceAPI` | api/intelligence/index.php | - | - |
| `IntelligenceAPIClient` | api/intelligence/IntelligenceAPIClient.php | - | - |
| `IntelligenceBotCommands` | api/intelligence/IntelligenceAPIClient.php | - | - |
| `CopilotBroadcaster` | api/broadcast-to-copilots.php | - | - |
| `MultiBotConversationManager` | api/satellite-deploy.php | - | - |
| `Color` | builds/active/scripts/kb-harvest.php | - | - |
| `MDKnowledgeConsolidator` | builds/active/scripts/md_knowledge_consolidator.php | - | - |
| `SafeNeuralScanner` | builds/active/scripts/safe_neural_scanner.php | - | - |
| `LocalNeuralIntelligenceScanner` | builds/active/scripts/local_neural_scanner.php | - | - |
| `MasterIndexGenerator` | builds/active/scripts/master_index_generator.php | - | - |
| `ComprehensiveMDScanner` | builds/active/scripts/comprehensive_md_scanner.php | - | - |
| `UserActivityTracker` | builds/active/scripts/user_activity_tracker.php | - | - |
| `ComprehensiveReadmeGenerator` | builds/active/scripts/comprehensive_readme_generator.php | - | - |
| `MDExtractor` | builds/active/scripts/extract_all_md_to_kb.php | - | - |
| `SmartMDScanner` | builds/active/scripts/smart_md_scanner.php | - | - |
| `KBRelationshipMapper` | builds/active/scripts/kb-relationship-mapper.php | - | - |
| `definitions` | builds/active/scripts/kb-relationship-mapper.php | - | - |
| `definitions` | builds/active/scripts/kb-relationship-mapper.php | - | - |
| `usage` | builds/active/scripts/kb-relationship-mapper.php | - | - |
| `usage` | builds/active/scripts/kb-relationship-mapper.php | - | - |
| `usage` | builds/active/scripts/kb-relationship-mapper.php | - | - |
| `registry` | builds/active/scripts/kb-relationship-mapper.php | - | - |
| `CognitiveContentAnalyzer` | builds/active/scripts/cognitive_content_analyzer.php | - | - |
| `KBIndexerConfig` | builds/active/scripts/kb-auto-indexer.php | - | - |
| `KBAutoIndexer` | builds/active/scripts/kb-auto-indexer.php | - | - |
| `NeuralIntelligenceScanner` | builds/active/scripts/neural_intelligence_scanner.php | - | - |
| `definitions` | builds/active/scripts/neural_intelligence_scanner.php | - | - |
| `QuickKBAccess` | builds/active/scripts/neural_intelligence_scanner.php | - | - |
| `KBCleanupConfig` | builds/active/scripts/kb-cleanup.php | - | - |
| `class` | builds/active/scripts/kb-cleanup.php | - | - |
| `DatabaseSchemaScanner` | builds/active/scripts/db_schema_scanner.php | - | - |
| `CodeQualityScorer` | builds/active/scripts/code_quality_scorer.php | - | - |
| `MultiDatabaseScanner` | builds/active/scripts/scan_all_databases.php | - | - |
| `MultiDatabaseSchemaScanner` | builds/active/scripts/multi_db_schema_scanner.php | - | - |
| `Color` | builds/active/scripts/kb-cli.php | - | - |
| `KBReadmeGenerator` | builds/active/scripts/kb-readme-generator.php | - | - |
| `Usage` | builds/active/scripts/kb-readme-generator.php | - | - |
| `Inheritance` | builds/active/scripts/kb-readme-generator.php | - | - |
| `KnowledgeBaseSDK` | builds/active/scripts/kb-sdk.php | - | - |
| `RedisService` | builds/active/app/Services/RedisService.php | - | - |
| `AgentKnowledgeBaseAPI` | builds/active/api/agent_kb.php | - | - |
| `NeuralIntelligenceProcessor` | builds/active/api/intelligence/neural_intelligence_processor.php | - | - |
| `patterns` | builds/active/api/intelligence/neural_intelligence_processor.php | - | - |
| `APINeuralScanner` | builds/active/api/intelligence/api_neural_scanner.php | - | - |
| `for` | builds/active/api/intelligence/api_neural_scanner.php | - | - |
| `IntelligenceAPI` | builds/active/api/intelligence/index.php | - | - |
| `IntelligenceAPIClient` | builds/active/api/intelligence/IntelligenceAPIClient.php | - | - |
| `IntelligenceBotCommands` | builds/active/api/intelligence/IntelligenceAPIClient.php | - | - |
| `MCPAdvancedTools` | builds/active/mcp/advanced_tools.php | - | - |
| `MCPServer` | builds/active/mcp/server.php | - | - |
| `definitions` | builds/active/config/redis.php | - | - |
| `Color` | builds/active/dashboard/comprehensive_test.php | - | - |
| `DashboardTester` | builds/active/dashboard/comprehensive_test.php | - | - |
| `ConversationLogger` | builds/active/dashboard/includes/ConversationLogger.php | - | - |
| `CallGraphVisitor` | builds/active/_kb/scripts/generate_call_graph.php | NodeVisitorAbstract | - |
| `if` | builds/active/_kb/scripts/generate_call_graph.php | - | - |
| `if` | builds/active/_kb/scripts/generate_call_graph.php | - | - |
| `instanceof` | builds/active/_kb/scripts/generate_call_graph.php | - | - |
| `when` | builds/active/_kb/scripts/generate_call_graph.php | - | - |
| `name` | builds/active/_kb/scripts/generate_call_graph.php | - | - |
| `instanceof` | builds/active/_kb/scripts/generate_call_graph.php | - | - |
| `CallGraphGenerator` | builds/active/_kb/scripts/generate_call_graph.php | - | - |
| `SecurityVisitor` | builds/active/_kb/scripts/ast_security_scanner.php | NodeVisitorAbstract | - |
| `SecurityScanner` | builds/active/_kb/scripts/ast_security_scanner.php | - | - |
| `FileAnalyzer` | builds/active/_kb/scripts/analyze_single_file.php | NodeVisitorAbstract | - |
| `SingleFileAnalyzer` | builds/active/_kb/scripts/analyze_single_file.php | - | - |
| `IntelligenceReceiver` | builds/active/_kb/api/intelligence_receiver.php | - | - |
| `IntelligenceDistributor` | builds/active/_kb/api/intelligence_distributor.php | - | - |
| `MDFileAnalyzer` | builds/active/_kb/md_file_analysis.php | - | - |
| `KBConfigManager` | builds/active/_kb/config/KBConfigManager.php | - | - |
| `CronClient` | builds/active/_kb/lib/CronClient.php | - | - |
| `DualServerKBScanner` | builds/historic/v1_old/scripts/dual_server_kb_scanner.php | - | - |
| `needed` | builds/historic/v1_old/scripts/kb_proactive_indexer.php | - | - |
| `SimpleRedis` | builds/historic/v1_old/scripts/kb_proactive_indexer.php | - | - |
| `KBContentAnalyzer` | builds/historic/v1_old/scripts/kb_content_analyzer.php | - | - |
| `signature` | builds/historic/v1_old/scripts/kb_content_analyzer.php | - | - |
| `usage` | builds/historic/v1_old/scripts/kb_content_analyzer.php | - | - |
| `EnhancedSecurityScanner` | builds/historic/v1_old/scripts/enhanced_security_scanner.php | - | - |
| `extraction` | builds/historic/v1_old/scripts/kb_intelligence_engine.php | - | - |
| `KBIntelligenceEngine` | builds/historic/v1_old/scripts/kb_intelligence_engine.php | - | - |
| `Inventory` | builds/historic/v1_old/scripts/kb_intelligence_engine.php | - | - |
| `dependencies` | builds/historic/v1_old/scripts/_kb_tools_map-relationships.php | - | - |
| `dependencies` | builds/historic/v1_old/scripts/_kb_tools_map-relationships.php | - | - |
| `dependencies` | builds/historic/v1_old/scripts/_kb_tools_map-relationships.php | - | - |
| `usage` | builds/historic/v1_old/scripts/_kb_tools_map-relationships.php | - | - |
| `DeepIntelligenceEngine` | builds/historic/v1_old/scripts/kb_deep_intelligence.php | - | - |
| `NuclearKBCleanup` | builds/historic/v1_old/scripts/nuclear_kb_cleanup.php | - | - |
| `usage` | builds/historic/v1_old/scripts/kb_correlator.php | - | - |
| `EnhancedKBCrawler` | builds/historic/v1_old/scripts/enhanced_kb_crawler.php | - | - |
| `SmartKBTrigger` | builds/historic/v1_old/scripts/smart_kb_trigger.php | - | - |
| `SmartKBOrganizer` | builds/historic/v1_old/scripts/smart_kb_organizer.php | - | - |
| `KBIntelligenceEngineV2` | builds/historic/v1_old/scripts/kb_intelligence_engine_v2.php | - | - |
| `that` | builds/historic/v1_old/_archive/2025-10-25/old_files/setup_conversation_capture.php | - | - |
| `ConversationLogger` | builds/historic/v1_old/_archive/2025-10-25/old_files/setup_conversation_capture.php | - | - |
| `file` | builds/historic/v1_old/_archive/2025-10-25/old_files/setup_conversation_capture.php | - | - |
| `with` | builds/historic/v1_old/_archive/2025-10-25/old_files/setup_conversation_capture.php | - | - |
| `SecurityVisitor` | builds/historic/v1_old/_kb/scripts/enhanced_security_scanner.php | NodeVisitorAbstract | - |
| `detection` | builds/historic/v1_old/_kb/scripts/kb_intelligence_engine_v2.php | - | - |
| `CodeAnalysisVisitor` | builds/historic/v1_old/_kb/scripts/kb_intelligence_engine_v2.php | NodeVisitorAbstract | - |
| `extends` | builds/historic/v1_old/_kb/scripts/kb_intelligence_engine_v2.php | - | - |
| `KBConfigManager` | builds/historic/v1_old/_kb/kb_config_manager.php | - | - |
| `UltimateWebDevKBGenerator` | builds/historic/v1_old/_kb/ultimate_web_dev_kb_generator.php | - | - |
| `web` | builds/historic/v1_old/_kb/ultimate_web_dev_kb_generator.php | - | - |
| `UserService` | builds/historic/v1_old/_kb/ultimate_web_dev_kb_generator.php | - | - |
| `ApiController` | builds/historic/v1_old/_kb/ultimate_web_dev_kb_generator.php | - | - |
| `DatabaseManager` | builds/historic/v1_old/_kb/ultimate_web_dev_kb_generator.php | - | - |
| `BusinessUnitScanner` | audit_reports.php | - | - |
| `AuditRepository` | audit_reports.php | - | - |
| `IntelligenceHubMCP` | mcp/server_v2.php | - | - |
| `name` | mcp/server_v2.php | - | - |
| `MCPServer` | mcp/server_v2_complete.php | - | - |
| `name` | mcp/server_v2_complete.php | - | - |
| `MCPAdvancedTools` | mcp/advanced_tools.php | - | - |
| `AnalyticsDashboard` | mcp/analytics_dashboard.php | - | - |
| `MCPServer` | mcp/server.php | - | - |
| `definitions` | config/redis.php | - | - |
| `IntelligenceHubSecurityAudit` | tools/hardened-security-audit.php | - | - |
| `PlatformSecurityAudit` | tools/platform-security-audit.php | - | - |
| `FrontendTester` | services/FrontendTester.php | - | - |
| `CSRFProtection` | services/CSRFProtection.php | - | - |
| `CredentialManager` | services/CredentialManager.php | - | - |
| `InputValidator` | services/InputValidator.php | - | - |
| `DatabaseValidator` | services/DatabaseValidator.php | - | - |
| `SecurityMonitor` | services/SecurityMonitor.php | - | - |
| `RateLimiter` | services/RateLimiter.php | - | - |
| `BotStandardsExpanded` | services/BotStandardsExpanded.php | - | - |
| `callables` | services/BotStandardsExpanded.php | - | - |
| `per` | services/BotStandardsExpanded.php | - | - |
| `names` | services/BotStandardsExpanded.php | - | - |
| `functions` | services/BotStandardsExpanded.php | - | - |
| `AIAgentClient` | services/AIAgentClient.php | - | - |
| `BotPromptBuilder` | services/BotPromptBuilder.php | - | - |
| `CloudwaysCronAPI` | services/cloudways_cron_api.php | - | - |
| `names` | test_mcp_tools.php | - | - |
| `Color` | dashboard/comprehensive_test.php | - | - |
| `DashboardTester` | dashboard/comprehensive_test.php | - | - |
| `based` | dashboard/pages/crawler-monitor.php | - | - |
| `ConversationLogger` | dashboard/includes/ConversationLogger.php | - | - |
| `CallGraphVisitor` | _kb/scripts/generate_call_graph.php | NodeVisitorAbstract | - |
| `if` | _kb/scripts/generate_call_graph.php | - | - |
| `if` | _kb/scripts/generate_call_graph.php | - | - |
| `instanceof` | _kb/scripts/generate_call_graph.php | - | - |
| `when` | _kb/scripts/generate_call_graph.php | - | - |
| `name` | _kb/scripts/generate_call_graph.php | - | - |
| `instanceof` | _kb/scripts/generate_call_graph.php | - | - |
| `CallGraphGenerator` | _kb/scripts/generate_call_graph.php | - | - |
| `SecurityVisitor` | _kb/scripts/ast_security_scanner.php | NodeVisitorAbstract | - |
| `SecurityScanner` | _kb/scripts/ast_security_scanner.php | - | - |
| `FileAnalyzer` | _kb/scripts/analyze_single_file.php | NodeVisitorAbstract | - |
| `SingleFileAnalyzer` | _kb/scripts/analyze_single_file.php | - | - |
| `IntelligenceReceiver` | _kb/api/intelligence_receiver.php | - | - |
| `IntelligenceDistributor` | _kb/api/intelligence_distributor.php | - | - |
| `KBConfigManager` | _kb/kb_config_manager.php | - | - |
| `UltimateWebDevKBGenerator` | _kb/ultimate_web_dev_kb_generator.php | - | - |
| `web` | _kb/ultimate_web_dev_kb_generator.php | - | - |
| `UserService` | _kb/ultimate_web_dev_kb_generator.php | - | - |
| `ApiController` | _kb/ultimate_web_dev_kb_generator.php | - | - |
| `DatabaseManager` | _kb/ultimate_web_dev_kb_generator.php | - | - |
| `KBConfigManager` | _kb/config/KBConfigManager.php | - | - |
| `CronClient` | _kb/lib/CronClient.php | - | - |
| `EnvLoader` | _kb/lib/EnvLoader.php | - | - |

## Function Inventory

### `scripts/kb-harvest.php`

- `c(string $text, string $color): string`
- `log_message(string $message, string $level): void`
- `showHelp(): void`
- `scanForMarkdownFiles(array $config, bool $force): array`
- `extractMetadata(string $filePath, SplFileInfo $file): array`
- `detectCategory(string $filePath): string`
- `syncDocumentsToKB(array $documents, array $config): array`
- `saveDocumentIndex(array $documents, array $config): void`
- `findOrphanedDocuments(array $config): array`
- `deleteOrphanedDocuments(array $orphans, array $config): int`
- `showStatistics(array $config): void`
- `cleanupDocuments(array $config): array`
- `formatBytes(int $bytes): string`
- `watchMode(array $config): void`

### `scripts/dual_server_kb_scanner.php`

- `__construct(): void`
- `loadIgnoreConfig(): void`
- `scanBothServers(): array`
- `scanLocalDirectory(string $basePath, string $serverTag): void`
- `scanRemoteViaSSH(string $remotePath, string $serverTag): void`
- `buildRemoteFindCommand(string $basePath): string`
- `getRemoteFileInfo(string $remoteFile): ?array`
- `processRemoteFile(string $remotePath, array $fileInfo, string $serverTag): void`
- `processFile(string $filePath, string $serverTag): void`
- `insertFileRecord(string $filePath, array $fileInfo, string $serverTag): void`
- `updateFileRecord(int $fileId, array $fileInfo, string $serverTag): void`
- `shouldScanFile(string $filePath, string $extension): bool`
- `getFileType(string $extension): string`
- `log(string $message): void`

### `scripts/comprehensive_testing_suite.php`

- `runTest( $testStmt,  $category,  $name,  $description,  $testFunction,  $testResults): void`

### `scripts/md_knowledge_consolidator.php`

- `__construct(): void`
- `consolidateAllKnowledge(): array`
- `discoverMarkdownFiles(): array`
- `analyzeMarkdownContent(array $mdFiles): array`
- `extractMetadata(string $content): array`
- `analyzeStructure(string $content): array`
- `extractFacts(string $content): array`
- `extractTopics(string $content): array`
- `extractLinks(string $content): array`
- `extractCodeBlocks(string $content): array`
- `extractTables(string $content): array`
- `calculateQualityScore(string $content): float`
- `calculateComplexityScore(string $content): int`
- `buildKnowledgeGraph(array $analysisResults): void`
- `findTopicBasedConnections(): void`
- `consolidateDuplicateContent(array $analysisResults): array`
- `groupSimilarDocuments(array $analysisResults): array`
- `calculateSimilarity(array $doc1, array $doc2): float`
- `mergeDocuments(array $documents): array`
- `extractAndVerifyFacts(array $consolidatedContent): array`
- `generateConsolidatedDocuments(array $factDatabase): array`
- `generateTechnicalGuide(array $factDatabase): string`
- `createMasterIndex(array $newDocuments): void`
- `shouldSkipFile(string $path): bool`
- `categorizeFile(string $filename): string`
- `generateId(string $text): string`
- `ensureDirectories(): void`
- `log(string $message): void`
- `formatBytes(int $bytes): string`
- `deduplicateFacts(array $facts): array`
- `consolidateTopics(array $topics): array`
- `categorizeFact(string $content): string`
- `verifyFact(array $fact, array $allContent): array`
- `generateOperationalProcedures(array $factDatabase): string`
- `generateTroubleshootingGuide(array $factDatabase): string`
- `generateAPIReference(array $factDatabase): string`
- `generateSystemOverview(array $factDatabase): string`
- `generateQuickReference(array $factDatabase): string`
- `saveDuplicateReport(array $duplicates): void`

### `scripts/safe_neural_scanner.php`

- `__construct(array $config): void`
- `connectDb(): void`
- `scan(): void`
- `scanDirectory(string $serverId, string $path, int $businessUnit, int $depth): void`
- `shouldSkip(string $path, string $item): bool`
- `shouldIndex(string $path): bool`
- `indexFile(string $serverId, string $path, int $businessUnit): void`
- `getIntelligenceType(string $ext, string $content): string`
- `getFileType(string $ext): string`
- `printSummary(): void`

### `scripts/install-kb-smart-cron.php`

- `showHelp(): void`

### `scripts/kb_proactive_indexer.php`

- `__construct(): void`
- `set( $key,  $value,  $ttl): void`
- `get( $key): void`
- `scanForChanges( $db,  $redis,  $config): array`
- `extractSymbols( $db,  $changes): array`
- `learnPatterns( $db,  $changes): array`
- `rebuildCorrelations( $db,  $changes): array`
- `updateSearchIndex( $redis,  $db): void`
- `log_message(string $message): void`

### `scripts/kb_md_collector.php`

- `collectMarkdownFiles( $pdo, array $domainConfig, array $config): array`
- `updateCollectionStatus( $pdo, int $businessUnitId, string $filePath, string $collectedPath): void`
- `showStats( $pdo,  $domains): void`
- `formatBytes(int $bytes): string`
- `showHelp(): void`
- `log_msg(string $message): void`

### `scripts/ultra_tight_db_update.php`

- `shouldExcludeFile( $fileName,  $filePath,  $excludePatterns): void`

### `scripts/kb_content_analyzer.php`

- `__construct(PDO $pdo, ?Redis $redis): void`
- `analyzeFileContent(string $filePath, string $content, array $metadata): array`
- `calculateRelevanceScore(string $content, array $metadata): int`
- `calculateQualityScore(string $content, array $metadata): int`
- `calculateFreshnessScore(string $filePath, array $metadata): int`
