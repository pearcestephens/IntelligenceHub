// ========================================================================
// DIRECT MYSQL KNOWLEDGE BASE INJECTION
// Retrieves relevant knowledge from ai_kb_knowledge_items table
// ========================================================================
function injectMySQLKnowledge($db, $query, &$enhancedContext, $maxItems = 15) {
    // Build search query for FULLTEXT search
    $searchTerms = preg_replace('/[^\w\s]/', '', $query);
    
    $stmt = $db->prepare("
        SELECT 
            item_key,
            item_content,
            source_file,
            importance_score,
            category,
            times_referenced
        FROM ai_kb_knowledge_items
        WHERE MATCH(item_content, item_key) AGAINST(? IN NATURAL LANGUAGE MODE)
        ORDER BY importance_score DESC, times_referenced DESC
        LIMIT ?
    ");
    
    $stmt->bind_param('si', $searchTerms, $maxItems);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $knowledgeItems = [];
    $knowledgeText = "\n\n## 📚 Knowledge Base Context:\n\n";
    $itemCount = 0;
    
    while ($row = $result->fetch_assoc()) {
        $itemCount++;
        $content = json_decode($row['item_content'], true);
        $knowledgeItems[] = $row;
        
        $knowledgeText .= "### Item {$itemCount}: {$row['item_key']}\n";
        $knowledgeText .= "**Source:** `{$row['source_file']}`\n";
        $knowledgeText .= "**Importance:** {$row['importance_score']} | **References:** {$row['times_referenced']}\n";
        $knowledgeText .= "**Category:** {$row['category']}\n\n";
        
        if (is_array($content)) {
            $knowledgeText .= json_encode($content, JSON_PRETTY_PRINT) . "\n\n";
        } else {
            $knowledgeText .= $row['item_content'] . "\n\n";
        }
        
        $knowledgeText .= "---\n\n";
    }
    
    $stmt->close();
    
    // Inject into enhanced context
    if ($itemCount > 0) {
        $enhancedContext['knowledge_context'] = ($enhancedContext['knowledge_context'] ?? '') . $knowledgeText;
        $enhancedContext['mysql_knowledge_items_count'] = $itemCount;
    }
    
    return $itemCount;
}
