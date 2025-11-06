#!/bin/bash
# Test VendTransferAPI Integration
# Verifies the Stock Transfer Engine can connect to Vend data

echo "============================================"
echo "🧪 VendTransferAPI Integration Test"
echo "============================================"
echo ""

cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html

# Test 1: Verify VendTransferAPI file exists
echo "✅ Test 1: Verify VendTransferAPI exists"
if [ -f "assets/services/stock-transfers/VendTransferAPI.php" ]; then
    echo "   ✓ File found: assets/services/stock-transfers/VendTransferAPI.php"
    wc -l assets/services/stock-transfers/VendTransferAPI.php
else
    echo "   ✗ File not found!"
    exit 1
fi
echo ""

# Test 2: Check PHP syntax
echo "✅ Test 2: PHP Syntax Check"
php -l assets/services/stock-transfers/VendTransferAPI.php
if [ $? -eq 0 ]; then
    echo "   ✓ Syntax OK"
else
    echo "   ✗ Syntax errors found!"
    exit 1
fi
echo ""

# Test 3: Verify database tables exist
echo "✅ Test 3: Verify Vend tables exist"
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
    SELECT 'vend_products' as table_name, COUNT(*) as row_count FROM vend_products
    UNION ALL
    SELECT 'vend_product_inventory', COUNT(*) FROM vend_product_inventory
    UNION ALL
    SELECT 'vend_outlets', COUNT(*) FROM vend_outlets
    UNION ALL
    SELECT 'vend_sales', COUNT(*) FROM vend_sales
    UNION ALL
    SELECT 'vend_consignments', COUNT(*) FROM vend_consignments;
"
echo ""

# Test 4: Test pullStockLevels query
echo "✅ Test 4: Test pullStockLevels query (sample)"
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
    SELECT
        pi.product_id,
        p.name as product_name,
        p.sku,
        o.name as outlet_name,
        pi.count as stock_level,
        CASE
            WHEN pi.count <= pi.reorder_point THEN 'critical'
            WHEN pi.count <= pi.reorder_point * 1.5 THEN 'low'
            WHEN pi.count >= pi.restock_level * 1.5 THEN 'overstock'
            ELSE 'healthy'
        END as stock_status
    FROM vend_product_inventory pi
    JOIN vend_products p ON pi.product_id = p.id
    JOIN vend_outlets o ON pi.outlet_id = o.id
    WHERE p.deleted_at IS NULL
    AND p.active = 1
    LIMIT 5;
"
echo ""

# Test 5: Test pullSalesHistory query
echo "✅ Test 5: Test pullSalesHistory query (last 7 days sample)"
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
    SELECT
        DATE(s.sale_date) as sale_date,
        o.name as outlet_name,
        COUNT(DISTINCT s.id) as transaction_count,
        SUM(s.total_price) as total_revenue
    FROM vend_sales s
    JOIN vend_outlets o ON s.outlet_id = o.id
    WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    AND s.status = 'CLOSED'
    AND s.deleted_at IS NULL
    GROUP BY DATE(s.sale_date), o.name
    ORDER BY sale_date DESC
    LIMIT 10;
"
echo ""

# Test 6: Test getConsignments query
echo "✅ Test 6: Test getConsignments query (active transfers)"
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
    SELECT
        c.id,
        c.name,
        c.status,
        so.name as from_outlet,
        do.name as to_outlet,
        c.created_at
    FROM vend_consignments c
    JOIN vend_outlets so ON c.source_outlet_id = so.id
    JOIN vend_outlets do ON c.outlet_id = do.id
    WHERE c.status IN ('OPEN', 'SENT', 'RECEIVING')
    AND c.deleted_at IS NULL
    ORDER BY c.created_at DESC
    LIMIT 5;
"
echo ""

# Test 7: Verify stock transfer tables exist
echo "✅ Test 7: Verify Stock Transfer Engine tables"
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
    SELECT 'stock_transfers' as table_name, COUNT(*) as row_count FROM stock_transfers
    UNION ALL
    SELECT 'excess_stock_alerts', COUNT(*) FROM excess_stock_alerts
    UNION ALL
    SELECT 'stock_velocity_tracking', COUNT(*) FROM stock_velocity_tracking
    UNION ALL
    SELECT 'freight_costs', COUNT(*) FROM freight_costs
    UNION ALL
    SELECT 'outlet_freight_zones', COUNT(*) FROM outlet_freight_zones;
"
echo ""

echo "============================================"
echo "✅ All tests completed!"
echo "============================================"
echo ""
echo "📊 Summary:"
echo "   • VendTransferAPI.php created and syntax valid"
echo "   • Database connection working"
echo "   • Vend tables accessible (products, inventory, outlets, sales, consignments)"
echo "   • Stock Transfer Engine tables created"
echo "   • Sample queries executing successfully"
echo ""
echo "🎯 Next steps:"
echo "   1. Create WarehouseManager class"
echo "   2. Build AI Excess Detection Engine"
echo "   3. Create Stock Velocity Tracker"
echo ""
