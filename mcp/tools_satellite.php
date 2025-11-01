<?php
/**
 * Satellite Management & Discovery Tools
 */

    /**
     * LIST SATELLITES - Get all satellite status
     */
    private function toolListSatellites(array $args): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                bu.unit_id,
                bu.unit_name,
                bu.description,
                COUNT(ic.content_id) as total_files,
                COUNT(DISTINCT CASE WHEN ict.content_id IS NOT NULL THEN ict.content_id END) as with_content,
                SUM(CASE WHEN ict.word_count IS NOT NULL THEN ict.word_count ELSE 0 END) as total_words,
                ROUND(AVG(CASE WHEN ict.readability_score IS NOT NULL THEN ict.readability_score END), 2) as avg_readability,
                MAX(ic.updated_at) as last_updated
            FROM business_units bu
            LEFT JOIN intelligence_content ic ON bu.unit_id = ic.unit_id
            LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
            GROUP BY bu.unit_id, bu.unit_name, bu.description
            ORDER BY bu.unit_id
        ");
        
        $satellites = $stmt->fetchAll();
        
        $output = "# Intelligence Hub Satellites\n\n";
        
        foreach ($satellites as $sat) {
            $coverage = $sat['total_files'] > 0 ? 
                round(($sat['with_content'] / $sat['total_files']) * 100, 1) : 0;
                
            $output .= "## {$sat['unit_name']} (ID: {$sat['unit_id']})\n";
            $output .= "{$sat['description']}\n\n";
            $output .= "- **Total Files:** " . number_format($sat['total_files']) . "\n";
            $output .= "- **With Content:** " . number_format($sat['with_content']) . " ({$coverage}%)\n";
            $output .= "- **Total Words:** " . number_format($sat['total_words']) . "\n";
            $output .= "- **Avg Readability:** {$sat['avg_readability']}/100\n";
            $output .= "- **Last Updated:** {$sat['last_updated']}\n";
            
            if (isset($this->satellites[$sat['unit_id']])) {
                $output .= "- **URL:** {$this->satellites[$sat['unit_id']]['url']}\n";
            }
            $output .= "\n";
        }
        
        return ['content' => [['type' => 'text', 'text' => $output]]];
    }
    
    /**
     * SYNC SATELLITE - Trigger satellite data pull
     */
    private function toolSyncSatellite(array $args): array
    {
        $unitId = $args['unit_id'] ?? 0;
        $batchSize = $args['batch_size'] ?? 100;
        
        if (!isset($this->satellites[$unitId])) {
            return ['content' => [['type' => 'text', 'text' => "Invalid satellite ID: {$unitId}"]]];
        }
        
        $satellite = $this->satellites[$unitId];
        $scanUrl = $satellite['url'] . '/api/scan_and_return.php';
        $receiveUrl = 'https://gpt.ecigdis.co.nz/api/receive_satellite_data.php?auth=bFUdRjh4Jx';
        
        // Trigger scan
        $ch = curl_init($scanUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['auth' => 'bFUdRjh4Jx', 'batch' => $batchSize, 'offset' => 0]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $scanResult = curl_exec($ch);
        $scanData = json_decode($scanResult, true);
        
        if (!$scanData || !$scanData['success']) {
            return ['content' => [['type' => 'text', 'text' => "Failed to scan satellite: {$satellite['name']}"]]];
        }
        
        // Send to hub
        $ch = curl_init($receiveUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $scanResult,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $receiveResult = curl_exec($ch);
        $receiveData = json_decode($receiveResult, true);
        
        $output = "# Satellite Sync: {$satellite['name']}\n\n";
        $output .= "**Scanned:** {$scanData['returned']} files\n";
        
        if ($receiveData && $receiveData['success']) {
            $stats = $receiveData['stats'];
            $output .= "**Inserted:** {$stats['inserted']}\n";
            $output .= "**Updated:** {$stats['updated']}\n";
            $output .= "**Skipped:** {$stats['skipped']}\n";
            $output .= "**Errors:** {$stats['errors']}\n\n";
            
            if ($stats['errors'] > 0 && !empty($stats['error_details'])) {
                $output .= "## Errors\n";
                foreach ($stats['error_details'] as $error) {
                    $output .= "- {$error}\n";
                }
            }
        }
        
        return ['content' => [['type' => 'text', 'text' => $output]]];
    }
    
    /**
     * FIND SIMILAR - Find files similar to reference
     */
    private function toolFindSimilar(array $args): array
    {
        $contentId = $args['content_id'] ?? 0;
        $limit = $args['limit'] ?? 10;
        
        // Get reference file's keywords and tags
        $stmt = $this->pdo->prepare("
            SELECT extracted_keywords, semantic_tags 
            FROM intelligence_content_text 
            WHERE content_id = ?
        ");
        $stmt->execute([$contentId]);
        $ref = $stmt->fetch();
        
        if (!$ref) {
            return ['content' => [['type' => 'text', 'text' => "Content ID not found: {$contentId}"]]];
        }
        
        $refKeywords = json_decode($ref['extracted_keywords'] ?? '[]', true);
        $refTags = json_decode($ref['semantic_tags'] ?? '[]', true);
        
        if (empty($refKeywords) && empty($refTags)) {
            return ['content' => [['type' => 'text', 'text' => "Reference file has no keywords or tags for comparison"]]];
        }
        
        // Find similar files
        $sql = "
            SELECT 
                ic.content_id,
                ic.content_name,
                ic.content_path,
                bu.unit_name,
                ict.word_count,
                ict.extracted_keywords,
                ict.semantic_tags,
                ict.content_summary
            FROM intelligence_content ic
            JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
            LEFT JOIN business_units bu ON ic.unit_id = bu.unit_id
            WHERE ic.content_id != ?
            ORDER BY (
                CASE WHEN ict.extracted_keywords LIKE ? THEN 5 ELSE 0 END +
                CASE WHEN ict.semantic_tags LIKE ? THEN 3 ELSE 0 END
            ) DESC
            LIMIT ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $contentId,
            '%' . $refKeywords[0] . '%',
            '%' . $refTags[0] . '%',
            $limit
        ]);
        $results = $stmt->fetchAll();
        
        $output = "# Similar Files\n\n";
        $output .= "Found " . count($results) . " similar files\n\n";
        
        foreach ($results as $i => $result) {
            $output .= ($i + 1) . ". **{$result['content_name']}**\n";
            $output .= "   {$result['unit_name']} → {$result['content_path']}\n";
            $output .= "   Words: " . number_format($result['word_count']) . "\n";
            $output .= "   Content ID: {$result['content_id']}\n\n";
        }
        
        return ['content' => [['type' => 'text', 'text' => $output]]];
    }
    
