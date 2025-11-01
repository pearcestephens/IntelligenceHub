<?php

/**
 * Multi-Domain MCP Tools
 *
 * Provides MCP tools for domain management:
 * - switch_domain: Switch conversation to specific domain
 * - enable_god_mode: Enable GOD MODE (all documents accessible)
 * - disable_god_mode: Disable GOD MODE
 * - get_domain_stats: Get domain statistics
 * - domain_search: Domain-aware knowledge base search
 * - list_domains: List all available domains
 *
 * @package App\Tools
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Tools;

use App\Memory\MultiDomain;
use App\Logger;
use App\Util\Validate;

class MultiDomainTools
{
    /**
     * Register all multi-domain tools with the tool registry
     */
    public static function register(ToolRegistry $registry): void
    {
        // Switch Domain Tool
        $registry->register([
            'type' => 'function',
            'function' => [
                'name' => 'switch_domain',
                'description' => 'Switch conversation to a specific domain (global, staff, web, gpt, wiki, superadmin). Controls which documents are accessible in knowledge base searches.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'conversation_id' => [
                            'type' => 'string',
                            'description' => 'The conversation UUID to switch domains for'
                        ],
                        'domain_name' => [
                            'type' => 'string',
                            'enum' => ['global', 'staff', 'web', 'gpt', 'wiki', 'superadmin'],
                            'description' => 'The target domain name'
                        ]
                    ],
                    'required' => ['conversation_id', 'domain_name']
                ]
            ]
        ]);

        // Enable GOD MODE Tool
        $registry->register([
            'type' => 'function',
            'function' => [
                'name' => 'enable_god_mode',
                'description' => 'Enable GOD MODE for conversation - grants access to ALL 342 documents across all domains at 100% relevance. Use for admin/superadmin users only.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'conversation_id' => [
                            'type' => 'string',
                            'description' => 'The conversation UUID to enable GOD MODE for'
                        ]
                    ],
                    'required' => ['conversation_id']
                ]
            ]
        ]);

        // Disable GOD MODE Tool
        $registry->register([
            'type' => 'function',
            'function' => [
                'name' => 'disable_god_mode',
                'description' => 'Disable GOD MODE for conversation - returns to domain-restricted access.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'conversation_id' => [
                            'type' => 'string',
                            'description' => 'The conversation UUID to disable GOD MODE for'
                        ]
                    ],
                    'required' => ['conversation_id']
                ]
            ]
        ]);

        // Get Domain Stats Tool
        $registry->register([
            'type' => 'function',
            'function' => [
                'name' => 'get_domain_stats',
                'description' => 'Get statistics for a specific domain or all domains - shows document counts, query counts, and usage metrics.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'domain_name' => [
                            'type' => 'string',
                            'description' => 'Optional domain name to get stats for. If not provided, returns stats for all domains.',
                            'enum' => ['global', 'staff', 'web', 'gpt', 'wiki', 'superadmin']
                        ]
                    ],
                    'required' => []
                ]
            ]
        ]);

        // Domain-Aware Search Tool
        $registry->register([
            'type' => 'function',
            'function' => [
                'name' => 'domain_search',
                'description' => 'Search knowledge base with domain awareness - only returns documents accessible in current domain (unless GOD MODE is enabled).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'conversation_id' => [
                            'type' => 'string',
                            'description' => 'The conversation UUID for domain context'
                        ],
                        'query' => [
                            'type' => 'string',
                            'description' => 'The search query'
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum number of results to return (default: 5)',
                            'minimum' => 1,
                            'maximum' => 20
                        ]
                    ],
                    'required' => ['conversation_id', 'query']
                ]
            ]
        ]);

        // List Domains Tool
        $registry->register([
            'type' => 'function',
            'function' => [
                'name' => 'list_domains',
                'description' => 'List all available domains with their descriptions and active status.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [],
                    'required' => []
                ]
            ]
        ]);

        // Get Current Domain Tool
        $registry->register([
            'type' => 'function',
            'function' => [
                'name' => 'get_current_domain',
                'description' => 'Get the current active domain for a conversation, including GOD MODE status.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'conversation_id' => [
                            'type' => 'string',
                            'description' => 'The conversation UUID'
                        ]
                    ],
                    'required' => ['conversation_id']
                ]
            ]
        ]);
    }

    /**
     * Execute multi-domain tool
     */
    public static function execute(string $toolName, array $arguments): array
    {
        try {
            switch ($toolName) {
                case 'switch_domain':
                    return self::switchDomain($arguments);

                case 'enable_god_mode':
                    return self::enableGodMode($arguments);

                case 'disable_god_mode':
                    return self::disableGodMode($arguments);

                case 'get_domain_stats':
                    return self::getDomainStats($arguments);

                case 'domain_search':
                    return self::domainSearch($arguments);

                case 'list_domains':
                    return self::listDomains($arguments);

                case 'get_current_domain':
                    return self::getCurrentDomain($arguments);

                default:
                    return [
                        'success' => false,
                        'error' => "Unknown tool: {$toolName}"
                    ];
            }
        } catch (\Throwable $e) {
            Logger::error("Multi-domain tool execution failed", [
                'tool' => $toolName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Switch domain tool implementation
     */
    private static function switchDomain(array $args): array
    {
        Validate::required($args, ['conversation_id', 'domain_name']);

        $conversationId = $args['conversation_id'];
        $domainName = $args['domain_name'];

        $domainId = MultiDomain::getDomainIdByName($domainName);
        if ($domainId === null) {
            return [
                'success' => false,
                'error' => "Invalid domain name: {$domainName}"
            ];
        }

        $success = MultiDomain::switchDomain($conversationId, $domainId);

        if ($success) {
            $currentDomain = MultiDomain::getCurrentDomain($conversationId);
            return [
                'success' => true,
                'message' => "Successfully switched to domain: {$domainName}",
                'current_domain' => $currentDomain,
                'accessible_documents' => count(MultiDomain::getAccessibleDocuments($conversationId))
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to switch domain'
        ];
    }

    /**
     * Enable GOD MODE tool implementation
     */
    private static function enableGodMode(array $args): array
    {
        Validate::required($args, ['conversation_id']);

        $conversationId = $args['conversation_id'];
        $success = MultiDomain::enableGodMode($conversationId);

        if ($success) {
            return [
                'success' => true,
                'message' => 'GOD MODE enabled',
                'warning' => 'All 342 documents are now accessible at 100% relevance',
                'security_note' => 'This action is logged for security audit',
                'accessible_documents' => 342
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to enable GOD MODE'
        ];
    }

    /**
     * Disable GOD MODE tool implementation
     */
    private static function disableGodMode(array $args): array
    {
        Validate::required($args, ['conversation_id']);

        $conversationId = $args['conversation_id'];
        $success = MultiDomain::disableGodMode($conversationId);

        if ($success) {
            $currentDomain = MultiDomain::getCurrentDomain($conversationId);
            return [
                'success' => true,
                'message' => 'GOD MODE disabled',
                'current_domain' => $currentDomain,
                'accessible_documents' => count(MultiDomain::getAccessibleDocuments($conversationId))
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to disable GOD MODE'
        ];
    }

    /**
     * Get domain stats tool implementation
     */
    private static function getDomainStats(array $args): array
    {
        $domainName = $args['domain_name'] ?? null;
        $domainId = null;

        if ($domainName) {
            $domainId = MultiDomain::getDomainIdByName($domainName);
            if ($domainId === null) {
                return [
                    'success' => false,
                    'error' => "Invalid domain name: {$domainName}"
                ];
            }
        }

        $stats = MultiDomain::getDomainStats($domainId);

        return [
            'success' => true,
            'stats' => $stats,
            'count' => is_array($stats) && isset($stats[0]) ? count($stats) : 1
        ];
    }

    /**
     * Domain search tool implementation
     */
    private static function domainSearch(array $args): array
    {
        Validate::required($args, ['conversation_id', 'query']);

        $conversationId = $args['conversation_id'];
        $query = $args['query'];
        $limit = $args['limit'] ?? 5;

        $results = MultiDomain::domainAwareSearch($conversationId, $query, (int)$limit);
        $currentDomain = MultiDomain::getCurrentDomain($conversationId);

        return [
            'success' => true,
            'results' => $results,
            'count' => count($results),
            'current_domain' => $currentDomain,
            'query' => $query
        ];
    }

    /**
     * List domains tool implementation
     */
    private static function listDomains(array $args): array
    {
        $domains = MultiDomain::getAllDomains();

        return [
            'success' => true,
            'domains' => $domains,
            'count' => count($domains)
        ];
    }

    /**
     * Get current domain tool implementation
     */
    private static function getCurrentDomain(array $args): array
    {
        Validate::required($args, ['conversation_id']);

        $conversationId = $args['conversation_id'];
        $currentDomain = MultiDomain::getCurrentDomain($conversationId);

        if ($currentDomain) {
            $accessibleDocs = MultiDomain::getAccessibleDocuments($conversationId);

            return [
                'success' => true,
                'current_domain' => $currentDomain,
                'accessible_documents' => count($accessibleDocs),
                'god_mode_enabled' => (bool)$currentDomain['god_mode_enabled']
            ];
        }

        return [
            'success' => false,
            'error' => 'No active domain found for conversation'
        ];
    }
}
