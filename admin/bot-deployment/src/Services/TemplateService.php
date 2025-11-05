<?php

namespace BotDeployment\Services;

use BotDeployment\Config\Connection;
use BotDeployment\Services\Logger;
use BotDeployment\Repositories\BotRepository;
use Exception;

/**
 * Bot Template Management Service
 *
 * Provides pre-built bot configurations for common use cases:
 * - Data sync bots
 * - Monitoring bots
 * - Reporting bots
 * - Integration bots
 * - Custom user templates
 *
 * Features:
 * - Template library
 * - One-click deployment
 * - Template marketplace
 * - Custom template creation
 * - Template sharing
 * - Version control
 */
class TemplateService
{
    private $logger;
    private $db;
    private $botRepo;

    // Template categories
    const CATEGORY_DATA_SYNC = 'data_sync';
    const CATEGORY_MONITORING = 'monitoring';
    const CATEGORY_REPORTING = 'reporting';
    const CATEGORY_INTEGRATION = 'integration';
    const CATEGORY_AUTOMATION = 'automation';
    const CATEGORY_CUSTOM = 'custom';

    // Template visibility
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_SHARED = 'shared';

    public function __construct()
    {
        $this->logger = new Logger('templates');
        $this->db = Connection::getInstance();
        $this->botRepo = new BotRepository();
    }

    /**
     * Get all templates
     *
     * @param string|null $category Filter by category
     * @param string|null $visibility Filter by visibility
     * @param bool $includeBuiltin Include built-in templates
     */
    public function getAllTemplates(
        ?string $category = null,
        ?string $visibility = null,
        bool $includeBuiltin = true
    ): array {
        try {
            $sql = "SELECT * FROM bot_templates WHERE 1=1";
            $params = [];

            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            if ($visibility) {
                $sql .= " AND visibility = ?";
                $params[] = $visibility;
            }

            if (!$includeBuiltin) {
                $sql .= " AND is_builtin = 0";
            }

            $sql .= " ORDER BY usage_count DESC, created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to get templates", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get template by ID
     */
    public function getTemplate(int $templateId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM bot_templates WHERE id = ?");
            $stmt->execute([$templateId]);

            $template = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($template) {
                // Decode JSON fields
                $template['config'] = json_decode($template['config'], true);
                $template['metadata'] = json_decode($template['metadata'], true);
                $template['variables'] = json_decode($template['variables'], true);
            }

            return $template ?: null;

        } catch (Exception $e) {
            $this->logger->error("Failed to get template", [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create new template
     */
    public function createTemplate(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO bot_templates (
                    name, description, category, visibility,
                    config, metadata, variables,
                    tags, author, version, is_builtin,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['category'] ?? self::CATEGORY_CUSTOM,
                $data['visibility'] ?? self::VISIBILITY_PRIVATE,
                json_encode($data['config'] ?? []),
                json_encode($data['metadata'] ?? []),
                json_encode($data['variables'] ?? []),
                json_encode($data['tags'] ?? []),
                $data['author'] ?? 'system',
                $data['version'] ?? '1.0.0',
                $data['is_builtin'] ?? false
            ]);

            $templateId = (int) $this->db->lastInsertId();

            $this->logger->info("Template created", ['template_id' => $templateId]);

            return $templateId;

        } catch (Exception $e) {
            $this->logger->error("Failed to create template", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Update template
     */
    public function updateTemplate(int $templateId, array $data): bool
    {
        try {
            $updates = [];
            $params = [];

            foreach (['name', 'description', 'category', 'visibility'] as $field) {
                if (isset($data[$field])) {
                    $updates[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }

            foreach (['config', 'metadata', 'variables', 'tags'] as $field) {
                if (isset($data[$field])) {
                    $updates[] = "{$field} = ?";
                    $params[] = json_encode($data[$field]);
                }
            }

            if (isset($data['version'])) {
                $updates[] = "version = ?";
                $params[] = $data['version'];
            }

            $updates[] = "updated_at = NOW()";
            $params[] = $templateId;

            $sql = "UPDATE bot_templates SET " . implode(', ', $updates) . " WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->logger->info("Template updated", ['template_id' => $templateId]);

            return true;

        } catch (Exception $e) {
            $this->logger->error("Failed to update template", [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete template
     */
    public function deleteTemplate(int $templateId): bool
    {
        try {
            // Don't allow deleting built-in templates
            $template = $this->getTemplate($templateId);
            if ($template && $template['is_builtin']) {
                throw new Exception("Cannot delete built-in template");
            }

            $stmt = $this->db->prepare("DELETE FROM bot_templates WHERE id = ? AND is_builtin = 0");
            $stmt->execute([$templateId]);

            $this->logger->info("Template deleted", ['template_id' => $templateId]);

            return true;

        } catch (Exception $e) {
            $this->logger->error("Failed to delete template", [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Deploy bot from template (one-click deployment)
     */
    public function deployFromTemplate(int $templateId, array $variables = []): ?int
    {
        try {
            $template = $this->getTemplate($templateId);
            if (!$template) {
                throw new Exception("Template not found");
            }

            // Merge template config with provided variables
            $config = $template['config'];

            // Replace variables in config
            $config = $this->replaceVariables($config, $variables, $template['variables']);

            // Create bot from template
            $botData = [
                'name' => $config['name'] ?? $template['name'],
                'description' => $config['description'] ?? $template['description'],
                'mcp_server_url' => $config['mcp_server_url'] ?? '',
                'config' => json_encode($config),
                'status' => 'active',
                'metadata' => json_encode([
                    'template_id' => $templateId,
                    'template_name' => $template['name'],
                    'deployed_at' => date('Y-m-d H:i:s')
                ])
            ];

            $botId = $this->botRepo->create($botData);

            if ($botId) {
                // Increment usage count
                $this->incrementUsageCount($templateId);

                $this->logger->info("Bot deployed from template", [
                    'template_id' => $templateId,
                    'bot_id' => $botId
                ]);
            }

            return $botId;

        } catch (Exception $e) {
            $this->logger->error("Failed to deploy from template", [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Replace variables in configuration
     */
    private function replaceVariables(array $config, array $values, array $variables): array
    {
        $configJson = json_encode($config);

        // Replace each variable
        foreach ($variables as $var) {
            $key = $var['key'] ?? '';
            $value = $values[$key] ?? $var['default'] ?? '';

            // Replace {{variable}} placeholders
            $configJson = str_replace("{{" . $key . "}}", $value, $configJson);
        }

        return json_decode($configJson, true);
    }

    /**
     * Increment usage count
     */
    private function incrementUsageCount(int $templateId): void
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE bot_templates
                SET usage_count = usage_count + 1,
                    last_used_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$templateId]);
        } catch (Exception $e) {
            $this->logger->error("Failed to increment usage count", [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create template from existing bot
     */
    public function createFromBot(int $botId, array $templateData): ?int
    {
        try {
            $bot = $this->botRepo->find($botId);
            if (!$bot) {
                throw new Exception("Bot not found");
            }

            // Extract bot configuration
            $config = json_decode($bot['config'], true);

            // Identify variables (fields that should be customizable)
            $variables = $this->extractVariables($config);

            // Create template
            $templateData['config'] = $config;
            $templateData['variables'] = $variables;
            $templateData['metadata'] = [
                'source_bot_id' => $botId,
                'created_from' => 'bot',
                'original_name' => $bot['name']
            ];

            return $this->createTemplate($templateData);

        } catch (Exception $e) {
            $this->logger->error("Failed to create template from bot", [
                'bot_id' => $botId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extract variables from configuration
     */
    private function extractVariables(array $config): array
    {
        $variables = [];

        // Common fields that should be variables
        $variableFields = [
            'name' => ['type' => 'string', 'label' => 'Bot Name', 'required' => true],
            'description' => ['type' => 'text', 'label' => 'Description', 'required' => false],
            'mcp_server_url' => ['type' => 'url', 'label' => 'MCP Server URL', 'required' => true],
            'schedule' => ['type' => 'string', 'label' => 'Schedule (cron)', 'required' => false]
        ];

        foreach ($variableFields as $field => $meta) {
            if (isset($config[$field])) {
                $variables[] = [
                    'key' => $field,
                    'label' => $meta['label'],
                    'type' => $meta['type'],
                    'required' => $meta['required'],
                    'default' => $config[$field]
                ];
            }
        }

        return $variables;
    }

    /**
     * Search templates
     */
    public function searchTemplates(string $query, ?string $category = null): array
    {
        try {
            $sql = "
                SELECT * FROM bot_templates
                WHERE (name LIKE ? OR description LIKE ? OR tags LIKE ?)
            ";
            $params = ["%{$query}%", "%{$query}%", "%{$query}%"];

            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            $sql .= " ORDER BY usage_count DESC LIMIT 50";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to search templates", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get popular templates
     */
    public function getPopularTemplates(int $limit = 10): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM bot_templates
                WHERE visibility = 'public'
                ORDER BY usage_count DESC, rating DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to get popular templates", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get template statistics
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Total templates
            $stmt = $this->db->query("SELECT COUNT(*) FROM bot_templates");
            $stats['total_templates'] = (int) $stmt->fetchColumn();

            // By category
            $stmt = $this->db->query("
                SELECT category, COUNT(*) as count
                FROM bot_templates
                GROUP BY category
            ");
            $stats['by_category'] = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            // By visibility
            $stmt = $this->db->query("
                SELECT visibility, COUNT(*) as count
                FROM bot_templates
                GROUP BY visibility
            ");
            $stats['by_visibility'] = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            // Total deployments
            $stmt = $this->db->query("SELECT SUM(usage_count) FROM bot_templates");
            $stats['total_deployments'] = (int) $stmt->fetchColumn();

            // Most popular
            $stmt = $this->db->query("
                SELECT name, usage_count
                FROM bot_templates
                ORDER BY usage_count DESC
                LIMIT 5
            ");
            $stats['most_popular'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $stats;

        } catch (Exception $e) {
            $this->logger->error("Failed to get statistics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Initialize built-in templates
     */
    public function initializeBuiltInTemplates(): void
    {
        $templates = $this->getBuiltInTemplates();

        foreach ($templates as $template) {
            try {
                // Check if already exists
                $stmt = $this->db->prepare("
                    SELECT id FROM bot_templates
                    WHERE name = ? AND is_builtin = 1
                ");
                $stmt->execute([$template['name']]);

                if (!$stmt->fetch()) {
                    $this->createTemplate($template);
                    $this->logger->info("Built-in template created", ['name' => $template['name']]);
                }
            } catch (Exception $e) {
                $this->logger->error("Failed to create built-in template", [
                    'name' => $template['name'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get built-in template definitions
     */
    private function getBuiltInTemplates(): array
    {
        return [
            [
                'name' => 'Daily Data Sync',
                'description' => 'Synchronize data between systems on a daily schedule',
                'category' => self::CATEGORY_DATA_SYNC,
                'visibility' => self::VISIBILITY_PUBLIC,
                'config' => [
                    'name' => '{{bot_name}}',
                    'description' => 'Daily data synchronization',
                    'mcp_server_url' => '{{mcp_url}}',
                    'schedule' => '0 2 * * *',
                    'timeout' => 3600,
                    'retry_attempts' => 3
                ],
                'variables' => [
                    ['key' => 'bot_name', 'label' => 'Bot Name', 'type' => 'string', 'required' => true, 'default' => 'Daily Sync Bot'],
                    ['key' => 'mcp_url', 'label' => 'MCP Server URL', 'type' => 'url', 'required' => true, 'default' => '']
                ],
                'tags' => ['data', 'sync', 'daily', 'automation'],
                'author' => 'system',
                'version' => '1.0.0',
                'is_builtin' => true
            ],
            [
                'name' => 'System Health Monitor',
                'description' => 'Monitor system health and send alerts',
                'category' => self::CATEGORY_MONITORING,
                'visibility' => self::VISIBILITY_PUBLIC,
                'config' => [
                    'name' => '{{bot_name}}',
                    'description' => 'System health monitoring',
                    'mcp_server_url' => '{{mcp_url}}',
                    'schedule' => '*/5 * * * *',
                    'timeout' => 300,
                    'retry_attempts' => 2
                ],
                'variables' => [
                    ['key' => 'bot_name', 'label' => 'Bot Name', 'type' => 'string', 'required' => true, 'default' => 'Health Monitor'],
                    ['key' => 'mcp_url', 'label' => 'MCP Server URL', 'type' => 'url', 'required' => true, 'default' => '']
                ],
                'tags' => ['monitoring', 'health', 'alerts', 'system'],
                'author' => 'system',
                'version' => '1.0.0',
                'is_builtin' => true
            ],
            [
                'name' => 'Weekly Report Generator',
                'description' => 'Generate and email weekly reports',
                'category' => self::CATEGORY_REPORTING,
                'visibility' => self::VISIBILITY_PUBLIC,
                'config' => [
                    'name' => '{{bot_name}}',
                    'description' => 'Weekly report generation',
                    'mcp_server_url' => '{{mcp_url}}',
                    'schedule' => '0 9 * * 1',
                    'timeout' => 1800,
                    'retry_attempts' => 2
                ],
                'variables' => [
                    ['key' => 'bot_name', 'label' => 'Bot Name', 'type' => 'string', 'required' => true, 'default' => 'Weekly Reports'],
                    ['key' => 'mcp_url', 'label' => 'MCP Server URL', 'type' => 'url', 'required' => true, 'default' => '']
                ],
                'tags' => ['reporting', 'weekly', 'email', 'automation'],
                'author' => 'system',
                'version' => '1.0.0',
                'is_builtin' => true
            ],
            [
                'name' => 'API Integration',
                'description' => 'Generic API integration template',
                'category' => self::CATEGORY_INTEGRATION,
                'visibility' => self::VISIBILITY_PUBLIC,
                'config' => [
                    'name' => '{{bot_name}}',
                    'description' => 'API integration',
                    'mcp_server_url' => '{{mcp_url}}',
                    'schedule' => '{{schedule}}',
                    'timeout' => 600,
                    'retry_attempts' => 3
                ],
                'variables' => [
                    ['key' => 'bot_name', 'label' => 'Bot Name', 'type' => 'string', 'required' => true, 'default' => 'API Integration'],
                    ['key' => 'mcp_url', 'label' => 'MCP Server URL', 'type' => 'url', 'required' => true, 'default' => ''],
                    ['key' => 'schedule', 'label' => 'Schedule (cron)', 'type' => 'string', 'required' => false, 'default' => '0 * * * *']
                ],
                'tags' => ['integration', 'api', 'custom', 'flexible'],
                'author' => 'system',
                'version' => '1.0.0',
                'is_builtin' => true
            ]
        ];
    }
}
