<?php
/**
 * MCP Tool Implementations - Part 2
 * Advanced search and analysis functions
 */

    private function callTool(array $params): array
    {
        $toolName = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];
        
        return match ($toolName) {
            'semantic_search' => $this->toolSemanticSearch($arguments),
            'find_code' => $this->toolFindCode($arguments),
            'analyze_file' => $this->toolAnalyzeFile($arguments),
            'get_file_content' => $this->toolGetFileContent($arguments),
            'list_satellites' => $this->toolListSatellites($arguments),
            'sync_satellite' => $this->toolSyncSatellite($arguments),
            'find_similar' => $this->toolFindSimilar($arguments),
            'explore_by_tags' => $this->toolExploreByTags($arguments),
            'get_stats' => $this->toolGetStats($arguments),
            'top_keywords' => $this->toolTopKeywords($arguments),
            default => throw new Exception("Unknown tool: {$toolName}")
        };
    }
    
    /**
     * SEMANTIC SEARCH - Natural language search across all content
     */
    private function toolSemanticSearch(array $args): array
    {
        $query = $args['query'] ?? '';
        $unitIds = $args['unit_ids'] ?? [];
        $contentTypes = $args['content_types'] ?? [];
        $limit = $args['limit'] ?? 10;
        
        if (empty($query)) {
            return ['content' => [['type' => 'text', 'text' => 'Error: Query is required']]];
        }
        
        // Extract keywords from query
        $keywords = $this->extractQueryKeywords($query);
        
        // Build search query
        $sql = "
            SELECT 
                ic.content_id,
                ic.content_name,
                ic.content_path,
                bu.unit_name,
                ct.type_name as content_type,
                ict.word_count,
                ict.readability_score,
                ict.content_summary,
                ict.extracted_keywords,
                ict.semantic_tags,
                -- Relevance scoring
                (
                    MATCH(ict.content_text) AGAINST(? IN NATURAL LANGUAGE MODE) * 10 +
                    CASE WHEN ict.extracted_keywords LIKE ? THEN 5 ELSE 0 END +
                    CASE WHEN ict.semantic_tags LIKE ? THEN 3 ELSE 0 END +
                    CASE WHEN ic.content_name LIKE ? THEN 8 ELSE 0 END
                ) as relevance_score
            FROM intelligence_content ic
            JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
            LEFT JOIN business_units bu ON ic.unit_id = bu.unit_id
            LEFT JOIN intelligence_content_types ct ON ic.content_type_id = ct.content_type_id
            WHERE MATCH(ict.content_text) AGAINST(? IN NATURAL LANGUAGE MODE)
        ";
        
        $params = [$query, "%{$keywords[0]}%", "%{$keywords[0]}%", "%{$keywords[0]}%", $query];
        
        if (!empty($unitIds)) {
            $placeholders = implode(',', array_fill(0, count($unitIds), '?'));
            $sql .= " AND ic.unit_id IN ($placeholders)";
            $params = array_merge($params, $unitIds);
        }
        
        if (!empty($contentTypes)) {
            $placeholders = implode(',', array_fill(0, count($contentTypes), '?'));
            $sql .= " AND ct.type_name IN ($placeholders)";
            $params = array_merge($params, $contentTypes);
        }
        
        $sql .= " ORDER BY relevance_score DESC, ict.word_count DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        if (empty($results)) {
            return [
                'content' => [[
                    'type' => 'text',
                    'text' => "No results found for: {$query}\n\nTry:\n- Broader keywords\n- Different phrasing\n- Checking specific satellites with unit_ids parameter"
                ]]
            ];
        }
        
        $output = "# Search Results for: {$query}\n\n";
        $output .= "Found " . count($results) . " relevant files\n\n";
        
        foreach ($results as $i => $result) {
            $num = $i + 1;
            $keywords = json_decode($result['extracted_keywords'] ?? '[]', true);
            $tags = json_decode($result['semantic_tags'] ?? '[]', true);
            
            $output .= "## {$num}. {$result['content_name']}\n";
            $output .= "**Location:** {$result['unit_name']} - {$result['content_path']}\n";
            $output .= "**Type:** {$result['content_type']}\n";
            $output .= "**Relevance:** " . round($result['relevance_score'], 2) . "\n";
            $output .= "**Words:** " . number_format($result['word_count']) . "\n";
            $output .= "**Readability:** {$result['readability_score']}/100\n\n";
            
            if (!empty($result['content_summary'])) {
                $output .= "**Summary:**\n{$result['content_summary']}\n\n";
            }
            
            if (!empty($keywords)) {
                $output .= "**Keywords:** " . implode(', ', array_slice($keywords, 0, 10)) . "\n";
            }
            
            if (!empty($tags)) {
                $output .= "**Tags:** " . implode(', ', $tags) . "\n";
            }
            
            $output .= "\n**Content ID:** {$result['content_id']} (use with get_file_content)\n";
            $output .= "---\n\n";
        }
        
        return ['content' => [['type' => 'text', 'text' => $output]]];
    }
    
    /**
     * FIND CODE - Precise code pattern matching
     */
    private function toolFindCode(array $args): array
    {
        $pattern = $args['pattern'] ?? '';
        $searchIn = $args['search_in'] ?? 'content_text';
        $unitIds = $args['unit_ids'] ?? [];
        
        $column = match($searchIn) {
            'keywords' => 'ict.extracted_keywords',
            'semantic_tags' => 'ict.semantic_tags',
            'entities' => 'ict.entities_detected',
            default => 'ict.content_text'
        };
        
        $sql = "
            SELECT 
                ic.content_id,
                ic.content_name,
                ic.content_path,
                bu.unit_name,
                ct.type_name,
                ict.word_count,
                ict.content_summary
            FROM intelligence_content ic
            JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
            LEFT JOIN business_units bu ON ic.unit_id = bu.unit_id
            LEFT JOIN intelligence_content_types ct ON ic.content_type_id = ct.content_type_id
            WHERE {$column} LIKE ?
        ";
        
        $params = ["%{$pattern}%"];
        
        if (!empty($unitIds)) {
            $placeholders = implode(',', array_fill(0, count($unitIds), '?'));
            $sql .= " AND ic.unit_id IN ($placeholders)";
            $params = array_merge($params, $unitIds);
        }
        
        $sql .= " ORDER BY ict.word_count DESC LIMIT 20";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        if (empty($results)) {
            return ['content' => [['type' => 'text', 'text' => "No code found matching: {$pattern}"]]];
        }
        
        $output = "# Code Search: {$pattern}\n\n";
        $output .= "Found " . count($results) . " files containing this pattern\n\n";
        
        foreach ($results as $i => $result) {
            $output .= ($i + 1) . ". **{$result['content_name']}**\n";
            $output .= "   {$result['unit_name']} → {$result['content_path']}\n";
            $output .= "   Type: {$result['type_name']} | Words: " . number_format($result['word_count']) . "\n";
            $output .= "   Content ID: {$result['content_id']}\n\n";
        }
        
        return ['content' => [['type' => 'text', 'text' => $output]]];
    }
    
    /**
     * ANALYZE FILE - Deep file analysis
     */
    private function toolAnalyzeFile(array $args): array
    {
        $filePath = $args['file_path'] ?? '';
        
        $stmt = $this->pdo->prepare("
            SELECT 
                ic.*,
                ict.*,
                bu.unit_name,
                ct.type_name,
                ct.type_category
            FROM intelligence_content ic
            JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
            LEFT JOIN business_units bu ON ic.unit_id = bu.unit_id
            LEFT JOIN intelligence_content_types ct ON ic.content_type_id = ct.content_type_id
            WHERE ic.content_path LIKE ? OR ic.content_name = ?
            LIMIT 1
        ");
        $stmt->execute(["%{$filePath}%", $filePath]);
        $file = $stmt->fetch();
        
        if (!$file) {
            return ['content' => [['type' => 'text', 'text' => "File not found: {$filePath}"]]];
        }
        
        $keywords = json_decode($file['extracted_keywords'] ?? '[]', true);
        $tags = json_decode($file['semantic_tags'] ?? '[]', true);
        $entities = json_decode($file['entities_detected'] ?? '{}', true);
        
        $output = "# File Analysis: {$file['content_name']}\n\n";
        $output .= "## Basic Info\n";
        $output .= "- **Location:** {$file['unit_name']}\n";
        $output .= "- **Path:** {$file['content_path']}\n";
        $output .= "- **Type:** {$file['type_name']} ({$file['type_category']})\n";
        $output .= "- **Size:** " . number_format($file['file_size']) . " bytes\n";
        $output .= "- **Modified:** {$file['file_modified']}\n";
        $output .= "- **Content ID:** {$file['content_id']}\n\n";
        
        $output .= "## Content Metrics\n";
        $output .= "- **Lines:** " . number_format($file['line_count']) . "\n";
        $output .= "- **Words:** " . number_format($file['word_count']) . "\n";
        $output .= "- **Characters:** " . number_format($file['character_count']) . "\n";
        $output .= "- **Readability Score:** {$file['readability_score']}/100\n";
        $output .= "- **Sentiment:** {$file['sentiment_score']}\n";
        $output .= "- **Language Confidence:** {$file['language_confidence']}\n\n";
        
        if (!empty($file['content_summary'])) {
            $output .= "## Summary\n{$file['content_summary']}\n\n";
        }
        
        if (!empty($keywords)) {
            $output .= "## Top Keywords\n";
            foreach (array_slice($keywords, 0, 20) as $kw) {
                $output .= "- {$kw}\n";
            }
            $output .= "\n";
        }
        
        if (!empty($tags)) {
            $output .= "## Semantic Tags\n";
            $output .= implode(', ', $tags) . "\n\n";
        }
        
        if (!empty($entities['classes']) || !empty($entities['functions'])) {
            $output .= "## Detected Entities\n";
            if (!empty($entities['classes'])) {
                $output .= "**Classes:** " . implode(', ', array_slice($entities['classes'], 0, 10)) . "\n";
            }
            if (!empty($entities['functions'])) {
                $output .= "**Functions:** " . implode(', ', array_slice($entities['functions'], 0, 10)) . "\n";
            }
            $output .= "\n";
        }
        
        return ['content' => [['type' => 'text', 'text' => $output]]];
    }
    
    private function extractQueryKeywords(string $query): array
    {
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but', 'in', 'with', 'to', 'for', 'of', 'how', 'do', 'we', 'what', 'where', 'when'];
        $words = preg_split('/\s+/', strtolower($query));
        $keywords = array_diff($words, $stopWords);
        return array_values(array_filter($keywords, fn($w) => strlen($w) > 2));
    }
}

// Handle request
$server = new IntelligenceHubMCP($pdo);
$response = $server->handleRequest($request);
echo json_encode($response, JSON_PRETTY_PRINT);
