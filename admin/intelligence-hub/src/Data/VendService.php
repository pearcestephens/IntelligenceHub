<?php

namespace IntelligenceHub\Data;

use IntelligenceHub\Config\Connection;
use IntelligenceHub\Services\Logger;
use Exception;

/**
 * Vend API Integration Service
 *
 * Provides unified access to Vend/Lightspeed Retail data including:
 * - Products and inventory
 * - Sales and transactions
 * - Customers
 * - Outlets/locations
 * - Consignments and transfers
 */
class VendService
{
    private $logger;
    private $db;
    private $apiUrl;
    private $apiToken;
    private $cacheEnabled = true;
    private $cacheTTL = 300; // 5 minutes

    public function __construct()
    {
        $this->logger = new Logger('vend-service');
        $this->db = Connection::getInstance();
        $this->apiUrl = getenv('VEND_API_URL') ?: '';
        $this->apiToken = getenv('VEND_API_TOKEN') ?: '';

        if (empty($this->apiUrl) || empty($this->apiToken)) {
            $this->logger->warning("Vend API credentials not configured");
        }
    }

    /**
     * Get all outlets/locations
     */
    public function getOutlets(): array
    {
        return $this->getCached('outlets', function() {
            return $this->apiGet('outlets');
        });
    }

    /**
     * Get specific outlet by ID
     */
    public function getOutlet(string $outletId): ?array
    {
        return $this->getCached("outlet_{$outletId}", function() use ($outletId) {
            return $this->apiGet("outlets/{$outletId}");
        });
    }

    /**
     * Get product by ID or SKU
     */
    public function getProduct($identifier): ?array
    {
        if (is_numeric($identifier)) {
            return $this->getCached("product_{$identifier}", function() use ($identifier) {
                return $this->apiGet("products/{$identifier}");
            });
        }

        // Search by SKU
        $products = $this->searchProducts(['sku' => $identifier]);
        return $products[0] ?? null;
    }

    /**
     * Search products
     */
    public function searchProducts(array $filters = []): array
    {
        $cacheKey = 'products_' . md5(json_encode($filters));

        return $this->getCached($cacheKey, function() use ($filters) {
            $query = http_build_query($filters);
            return $this->apiGet("products?{$query}");
        });
    }

    /**
     * Get inventory levels for product across all outlets
     */
    public function getInventoryLevels(string $productId): array
    {
        return $this->getCached("inventory_{$productId}", function() use ($productId) {
            try {
                $stmt = $this->db->prepare("
                    SELECT
                        o.id as outlet_id,
                        o.name as outlet_name,
                        pi.count as quantity,
                        pi.reorder_point,
                        pi.restock_level,
                        CASE
                            WHEN pi.count <= pi.reorder_point THEN 'critical'
                            WHEN pi.count <= pi.reorder_point * 1.5 THEN 'low'
                            WHEN pi.count >= pi.restock_level * 1.5 THEN 'overstock'
                            ELSE 'healthy'
                        END as status
                    FROM vend_product_inventory pi
                    JOIN vend_outlets o ON pi.outlet_id = o.id
                    WHERE pi.product_id = ?
                    ORDER BY o.name
                ");

                $stmt->execute([$productId]);

                return $stmt->fetchAll(\PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                $this->logger->error("Failed to get inventory levels", [
                    'product_id' => $productId,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(?string $outletId = null): array
    {
        try {
            $sql = "
                SELECT
                    p.id as product_id,
                    p.name as product_name,
                    p.sku,
                    o.id as outlet_id,
                    o.name as outlet_name,
                    pi.count as current_stock,
                    pi.reorder_point,
                    pi.restock_level,
                    (pi.restock_level - pi.count) as needed_quantity,
                    ROUND((pi.count / NULLIF(pi.reorder_point, 0)) * 100, 2) as stock_percentage
                FROM vend_product_inventory pi
                JOIN vend_products p ON pi.product_id = p.id
                JOIN vend_outlets o ON pi.outlet_id = o.id
                WHERE pi.count <= pi.reorder_point
                AND p.active = 1
            ";

            if ($outletId) {
                $sql .= " AND o.id = ?";
            }

            $sql .= " ORDER BY stock_percentage ASC, o.name ASC";

            $stmt = $this->db->prepare($sql);

            if ($outletId) {
                $stmt->execute([$outletId]);
            } else {
                $stmt->execute();
            }

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to get low stock products", [
                'outlet_id' => $outletId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get sales data
     */
    public function getSales(array $params = []): array
    {
        $defaults = [
            'date_from' => date('Y-m-d', strtotime('-7 days')),
            'date_to' => date('Y-m-d'),
            'outlet_id' => null,
            'product_id' => null
        ];

        $params = array_merge($defaults, $params);

        try {
            $sql = "
                SELECT
                    s.id as sale_id,
                    s.sale_date,
                    s.total_price,
                    s.total_tax,
                    s.status,
                    o.name as outlet_name,
                    c.first_name,
                    c.last_name,
                    COUNT(sl.id) as line_items
                FROM vend_sales s
                JOIN vend_outlets o ON s.outlet_id = o.id
                LEFT JOIN vend_customers c ON s.customer_id = c.id
                LEFT JOIN vend_sale_lines sl ON s.id = sl.sale_id
                WHERE s.sale_date >= ?
                AND s.sale_date <= ?
            ";

            $bindings = [$params['date_from'], $params['date_to']];

            if ($params['outlet_id']) {
                $sql .= " AND s.outlet_id = ?";
                $bindings[] = $params['outlet_id'];
            }

            if ($params['product_id']) {
                $sql .= " AND sl.product_id = ?";
                $bindings[] = $params['product_id'];
            }

            $sql .= " GROUP BY s.id ORDER BY s.sale_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($bindings);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to get sales", [
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get sales analytics
     */
    public function getSalesAnalytics(array $params = []): array
    {
        $defaults = [
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to' => date('Y-m-d'),
            'outlet_id' => null
        ];

        $params = array_merge($defaults, $params);

        try {
            $sql = "
                SELECT
                    DATE(s.sale_date) as date,
                    COUNT(DISTINCT s.id) as total_sales,
                    SUM(s.total_price) as revenue,
                    AVG(s.total_price) as avg_sale_value,
                    COUNT(DISTINCT s.customer_id) as unique_customers
                FROM vend_sales s
                WHERE s.sale_date >= ?
                AND s.sale_date <= ?
                AND s.status = 'CLOSED'
            ";

            $bindings = [$params['date_from'], $params['date_to']];

            if ($params['outlet_id']) {
                $sql .= " AND s.outlet_id = ?";
                $bindings[] = $params['outlet_id'];
            }

            $sql .= " GROUP BY DATE(s.sale_date) ORDER BY date ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($bindings);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to get sales analytics", [
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(int $limit = 10, array $params = []): array
    {
        $defaults = [
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to' => date('Y-m-d'),
            'outlet_id' => null
        ];

        $params = array_merge($defaults, $params);

        try {
            $sql = "
                SELECT
                    p.id as product_id,
                    p.name as product_name,
                    p.sku,
                    SUM(sl.quantity) as units_sold,
                    SUM(sl.price * sl.quantity) as revenue,
                    AVG(sl.price) as avg_price,
                    COUNT(DISTINCT s.id) as transactions
                FROM vend_sale_lines sl
                JOIN vend_sales s ON sl.sale_id = s.id
                JOIN vend_products p ON sl.product_id = p.id
                WHERE s.sale_date >= ?
                AND s.sale_date <= ?
                AND s.status = 'CLOSED'
            ";

            $bindings = [$params['date_from'], $params['date_to']];

            if ($params['outlet_id']) {
                $sql .= " AND s.outlet_id = ?";
                $bindings[] = $params['outlet_id'];
            }

            $sql .= "
                GROUP BY p.id
                ORDER BY units_sold DESC
                LIMIT ?
            ";

            $bindings[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($bindings);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to get top products", [
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Create consignment (transfer order)
     */
    public function createConsignment(array $data): array
    {
        try {
            $this->logger->info("Creating consignment", ['data' => $data]);

            $consignment = [
                'name' => $data['name'] ?? 'AI Generated Transfer',
                'type' => $data['type'] ?? 'SUPPLIER',
                'status' => 'OPEN',
                'source_outlet_id' => $data['from_outlet_id'],
                'outlet_id' => $data['to_outlet_id'],
                'due_at' => $data['due_at'] ?? date('Y-m-d H:i:s', strtotime('+2 days')),
                'consignment_products' => []
            ];

            foreach ($data['products'] as $product) {
                $consignment['consignment_products'][] = [
                    'product_id' => $product['product_id'],
                    'count' => $product['quantity']
                ];
            }

            $result = $this->apiPost('consignments', $consignment);

            if ($result) {
                $this->logger->info("Consignment created successfully", [
                    'consignment_id' => $result['id'] ?? 'unknown'
                ]);

                $this->clearCache();

                return [
                    'success' => true,
                    'consignment' => $result
                ];
            }

            throw new Exception("Failed to create consignment");

        } catch (Exception $e) {
            $this->logger->error("Consignment creation failed", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make API GET request
     */
    private function apiGet(string $endpoint): ?array
    {
        return $this->apiRequest('GET', $endpoint);
    }

    /**
     * Make API POST request
     */
    private function apiPost(string $endpoint, array $data): ?array
    {
        return $this->apiRequest('POST', $endpoint, $data);
    }

    /**
     * Make API request
     */
    private function apiRequest(string $method, string $endpoint, array $data = null): ?array
    {
        if (empty($this->apiUrl) || empty($this->apiToken)) {
            throw new Exception("Vend API not configured");
        }

        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 201) {
            $this->logger->error("Vend API request failed", [
                'method' => $method,
                'endpoint' => $endpoint,
                'http_code' => $httpCode,
                'response' => $response
            ]);
            return null;
        }

        $result = json_decode($response, true);

        return $result['data'] ?? $result ?? null;
    }

    /**
     * Get from cache or execute callback
     */
    private function getCached(string $key, callable $callback)
    {
        if (!$this->cacheEnabled) {
            return $callback();
        }

        $cacheKey = 'vend_' . $key;

        // Try to get from cache
        $cached = apcu_fetch($cacheKey, $success);
        if ($success) {
            return $cached;
        }

        // Execute callback and cache result
        $result = $callback();
        apcu_store($cacheKey, $result, $this->cacheTTL);

        return $result;
    }

    /**
     * Clear all cache
     */
    public function clearCache(): void
    {
        apcu_clear_cache();
    }
}
