<?php
/**
 * Multi-Project & Domain Configuration for Scanner v4.0
 *
 * Defines project structure, domain mappings, and organizational hierarchy
 * for staff.vapeshed.co.nz (CIS modules) and other business units
 *
 * @package Scanner
 * @version 4.0.0
 */

declare(strict_types=1);

return [
    // ========================================================================
    // PROJECT STRUCTURE
    // ========================================================================

    'projects' => [
        // Intelligence Hub (Scanner itself)
        'intelligence' => [
            'id' => 1,
            'name' => 'Intelligence Hub (Scanner)',
            'type' => 'intelligence',
            'domain' => 'gpt.ecigdis.co.nz',
            'path' => '/home/master/applications/hdgwrzntwa/public_html',
            'url' => 'https://gpt.ecigdis.co.nz/scanner',
            'color' => '#dc3545',
            'icon' => 'bi-cpu',
            'tags' => ['intelligence', 'internal-only', 'security-critical']
        ],

        // CIS Modules (all on staff.vapeshed.co.nz)
        'cis-consignments' => [
            'id' => 2,
            'name' => 'CIS - Consignments Module',
            'type' => 'module',
            'parent' => 'cis',
            'domain' => 'staff.vapeshed.co.nz',
            'subdomain' => 'consignments',
            'path' => '/home/master/applications/jcepnzzkmj/public_html/modules/consignments',
            'url' => 'https://staff.vapeshed.co.nz/modules/consignments',
            'color' => '#0d6efd',
            'icon' => 'bi-box-seam',
            'tags' => ['cis-module', 'internal-only', 'security-critical'],
            'scan_patterns' => [
                '*.php',
                'api/*.php',
                'controllers/*.php',
                'models/*.php'
            ]
        ],

        'cis-supplier' => [
            'id' => 3,
            'name' => 'CIS - Supplier Portal',
            'type' => 'module',
            'parent' => 'cis',
            'domain' => 'staff.vapeshed.co.nz',
            'subdomain' => 'supplier',
            'path' => '/home/master/applications/jcepnzzkmj/public_html/modules/supplier',
            'url' => 'https://staff.vapeshed.co.nz/modules/supplier',
            'color' => '#0d6efd',
            'icon' => 'bi-people',
            'tags' => ['cis-module', 'internal-only'],
            'scan_patterns' => [
                '*.php',
                'api/*.php',
                'portal/*.php'
            ]
        ],

        'cis-purchase-orders' => [
            'id' => 4,
            'name' => 'CIS - Purchase Orders',
            'type' => 'module',
            'parent' => 'cis',
            'domain' => 'staff.vapeshed.co.nz',
            'subdomain' => 'purchase_orders',
            'path' => '/home/master/applications/jcepnzzkmj/public_html/modules/purchase_orders',
            'url' => 'https://staff.vapeshed.co.nz/modules/purchase_orders',
            'color' => '#0d6efd',
            'icon' => 'bi-cart',
            'tags' => ['cis-module', 'internal-only'],
            'scan_patterns' => ['*.php', 'api/*.php']
        ],

        'cis-inventory' => [
            'id' => 5,
            'name' => 'CIS - Inventory Management',
            'type' => 'module',
            'parent' => 'cis',
            'domain' => 'staff.vapeshed.co.nz',
            'subdomain' => 'inventory',
            'path' => '/home/master/applications/jcepnzzkmj/public_html/modules/inventory',
            'url' => 'https://staff.vapeshed.co.nz/modules/inventory',
            'color' => '#0d6efd',
            'icon' => 'bi-boxes',
            'tags' => ['cis-module', 'internal-only', 'security-critical'],
            'scan_patterns' => ['*.php', 'api/*.php', 'controllers/*.php']
        ],

        'cis-transfers' => [
            'id' => 6,
            'name' => 'CIS - Transfers Module',
            'type' => 'module',
            'parent' => 'cis',
            'domain' => 'staff.vapeshed.co.nz',
            'subdomain' => 'transfers',
            'path' => '/home/master/applications/jcepnzzkmj/public_html/modules/transfers',
            'url' => 'https://staff.vapeshed.co.nz/modules/transfers',
            'color' => '#0d6efd',
            'icon' => 'bi-arrow-left-right',
            'tags' => ['cis-module', 'internal-only', 'security-critical'],
            'scan_patterns' => ['*.php', 'api/*.php']
        ],

        'cis-hr' => [
            'id' => 7,
            'name' => 'CIS - HR & Staff',
            'type' => 'module',
            'parent' => 'cis',
            'domain' => 'staff.vapeshed.co.nz',
            'subdomain' => 'hr',
            'path' => '/home/master/applications/jcepnzzkmj/public_html/modules/hr',
            'url' => 'https://staff.vapeshed.co.nz/modules/hr',
            'color' => '#0d6efd',
            'icon' => 'bi-person-badge',
            'tags' => ['cis-module', 'internal-only', 'security-critical'],
            'scan_patterns' => ['*.php', 'api/*.php']
        ],

        'cis-webhooks' => [
            'id' => 8,
            'name' => 'CIS - Webhooks & Integration',
            'type' => 'module',
            'parent' => 'cis',
            'domain' => 'staff.vapeshed.co.nz',
            'subdomain' => 'webhooks',
            'path' => '/home/master/applications/jcepnzzkmj/public_html/modules/webhooks',
            'url' => 'https://staff.vapeshed.co.nz/modules/webhooks',
            'color' => '#0d6efd',
            'icon' => 'bi-arrow-repeat',
            'tags' => ['cis-module', 'internal-only', 'security-critical'],
            'scan_patterns' => ['*.php', 'handlers/*.php']
        ],

        'cis-base' => [
            'id' => 9,
            'name' => 'CIS - Base Framework',
            'type' => 'module',
            'parent' => 'cis',
            'domain' => 'staff.vapeshed.co.nz',
            'subdomain' => 'base',
            'path' => '/home/master/applications/jcepnzzkmj/public_html/modules/base',
            'url' => 'https://staff.vapeshed.co.nz/modules/base',
            'color' => '#0d6efd',
            'icon' => 'bi-gear',
            'tags' => ['cis-module', 'internal-only', 'security-critical'],
            'scan_patterns' => ['*.php', 'lib/*.php', 'controllers/*.php']
        ],

        // Public-Facing Sites
        'retail' => [
            'id' => 10,
            'name' => 'VapeShed Retail - Main Site',
            'type' => 'ecommerce',
            'domain' => 'www.vapeshed.co.nz',
            'path' => '/home/master/applications/dvaxgvsxmz/public_html',
            'url' => 'https://www.vapeshed.co.nz',
            'color' => '#198754',
            'icon' => 'bi-shop',
            'tags' => ['retail', 'public-facing'],
            'scan_patterns' => ['*.php', 'wp-content/themes/**/*.php']
        ],

        'wholesale' => [
            'id' => 11,
            'name' => 'Ecigdis Wholesale Portal',
            'type' => 'wholesale',
            'domain' => 'www.ecigdis.co.nz',
            'path' => '/home/master/applications/fhrehrpjmu/public_html',
            'url' => 'https://www.ecigdis.co.nz',
            'color' => '#ffc107',
            'icon' => 'bi-building',
            'tags' => ['wholesale', 'public-facing'],
            'scan_patterns' => ['*.php', 'wp-content/themes/**/*.php']
        ]
    ],

    // ========================================================================
    // DOMAIN MAPPINGS (Primary lookup for routing)
    // ========================================================================

    'domains' => [
        'gpt.ecigdis.co.nz' => [
            'project' => 'intelligence',
            'business_unit' => 'Intelligence Hub',
            'server' => 'hdgwrzntwa',
            'type' => 'intelligence'
        ],

        'staff.vapeshed.co.nz' => [
            'project' => 'cis', // Parent project
            'business_unit' => 'CIS - Central Information System',
            'server' => 'jcepnzzkmj',
            'type' => 'internal',
            'modules' => [
                'consignments' => 2,
                'supplier' => 3,
                'purchase_orders' => 4,
                'inventory' => 5,
                'transfers' => 6,
                'hr' => 7,
                'webhooks' => 8,
                'base' => 9
            ]
        ],

        'www.vapeshed.co.nz' => [
            'project' => 'retail',
            'business_unit' => 'VapeShed Retail',
            'server' => 'dvaxgvsxmz',
            'type' => 'public'
        ],

        'www.ecigdis.co.nz' => [
            'project' => 'wholesale',
            'business_unit' => 'Ecigdis Wholesale',
            'server' => 'fhrehrpjmu',
            'type' => 'public'
        ]
    ],

    // ========================================================================
    // PROJECT HIERARCHIES
    // ========================================================================

    'hierarchies' => [
        'cis' => [
            'name' => 'CIS - Central Information System',
            'domain' => 'staff.vapeshed.co.nz',
            'children' => [
                'cis-consignments',
                'cis-supplier',
                'cis-purchase-orders',
                'cis-inventory',
                'cis-transfers',
                'cis-hr',
                'cis-webhooks',
                'cis-base'
            ]
        ]
    ],

    // ========================================================================
    // SCAN CONFIGURATIONS BY PROJECT TYPE
    // ========================================================================

    'scan_configs' => [
        'module' => [
            'frequency' => 'daily',
            'auto_scan' => false,
            'max_workers' => 4,
            'exclude_patterns' => ['vendor', 'node_modules', '_archive', 'cache'],
            'file_extensions' => ['php', 'js', 'css', 'sql'],
            'priority_rules' => ['SEC001', 'SEC002', 'SEC003']
        ],

        'ecommerce' => [
            'frequency' => 'weekly',
            'auto_scan' => false,
            'max_workers' => 2,
            'exclude_patterns' => ['vendor', 'node_modules', 'wp-admin', 'wp-includes'],
            'file_extensions' => ['php', 'js'],
            'priority_rules' => ['SEC001', 'SEC002', 'XSS001']
        ],

        'intelligence' => [
            'frequency' => 'hourly',
            'auto_scan' => true,
            'max_workers' => 8,
            'exclude_patterns' => ['vendor', 'cache', 'logs'],
            'file_extensions' => ['php', 'js', 'sql'],
            'priority_rules' => ['SEC001', 'SEC002', 'SEC003', 'PERF001']
        ]
    ],

    // ========================================================================
    // ACCESS CONTROL BY DOMAIN
    // ========================================================================

    'access_control' => [
        'gpt.ecigdis.co.nz' => [
            'allowed_ips' => ['*'], // Internal network
            'require_auth' => true,
            'admin_only' => false
        ],

        'staff.vapeshed.co.nz' => [
            'allowed_ips' => ['*'],
            'require_auth' => true,
            'admin_only' => false,
            'module_access' => 'role-based' // Check user roles
        ],

        'www.vapeshed.co.nz' => [
            'allowed_ips' => ['*'],
            'require_auth' => false,
            'admin_only' => false,
            'scanner_access' => 'admin-only' // Scanner UI not accessible
        ]
    ],

    // ========================================================================
    // PROJECT TAGS & COLORS
    // ========================================================================

    'tags' => [
        'cis-module' => ['color' => '#0d6efd', 'icon' => 'bi-building'],
        'retail' => ['color' => '#198754', 'icon' => 'bi-shop'],
        'wholesale' => ['color' => '#ffc107', 'icon' => 'bi-building'],
        'intelligence' => ['color' => '#dc3545', 'icon' => 'bi-cpu'],
        'security-critical' => ['color' => '#d63384', 'icon' => 'bi-shield-lock'],
        'public-facing' => ['color' => '#fd7e14', 'icon' => 'bi-globe'],
        'internal-only' => ['color' => '#6c757d', 'icon' => 'bi-lock']
    ],

    // ========================================================================
    // HELPER FUNCTIONS
    // ========================================================================

    'functions' => [

        /**
         * Get project configuration by ID
         */
        'getProjectById' => function(int $projectId, array $config): ?array {
            foreach ($config['projects'] as $key => $project) {
                if ($project['id'] === $projectId) {
                    return $project;
                }
            }
            return null;
        },

        /**
         * Get project configuration by domain
         */
        'getProjectByDomain' => function(string $domain, array $config): ?array {
            $domain = strtolower(trim($domain));

            if (isset($config['domains'][$domain])) {
                $domainConfig = $config['domains'][$domain];
                $projectKey = $domainConfig['project'];

                return $config['projects'][$projectKey] ?? null;
            }

            return null;
        },

        /**
         * Get all child projects of a parent
         */
        'getChildProjects' => function(string $parentKey, array $config): array {
            $children = [];

            if (isset($config['hierarchies'][$parentKey])) {
                foreach ($config['hierarchies'][$parentKey]['children'] as $childKey) {
                    if (isset($config['projects'][$childKey])) {
                        $children[] = $config['projects'][$childKey];
                    }
                }
            }

            return $children;
        },

        /**
         * Get all CIS modules (for staff.vapeshed.co.nz)
         */
        'getCISModules' => function(array $config): array {
            return array_filter($config['projects'], function($project) {
                return isset($project['parent']) && $project['parent'] === 'cis';
            });
        },

        /**
         * Detect current domain/project from request
         */
        'detectCurrentProject' => function(array $config): ?array {
            $host = $_SERVER['HTTP_HOST'] ?? 'gpt.ecigdis.co.nz';

            return ($config['functions']['getProjectByDomain'])($host, $config);
        }
    ]
];
