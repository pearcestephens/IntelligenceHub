<?php
/**
 * Fuzzy Search Engine - Typo Tolerance & Phonetic Matching
 *
 * Features:
 * - Levenshtein distance for typo tolerance
 * - Soundex/Metaphone for phonetic matching
 * - Smart query suggestions (did you mean?)
 * - Common programming typos database
 *
 * @package IntelligenceHub\MCP\Search
 * @version 1.0.0
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Search;

class FuzzySearchEngine
{
    private array $config;
    private array $commonTypos = [];

    // Programming-specific term corrections
    private const PROGRAMMING_TYPOS = [
        'fucntion' => 'function',
        'funciton' => 'function',
        'fuction' => 'function',
        'retrun' => 'return',
        'reutrn' => 'return',
        'pbulic' => 'public',
        'privte' => 'private',
        'protcted' => 'protected',
        'contsructor' => 'constructor',
        'destory' => 'destroy',
        'deletd' => 'delete',
        'slect' => 'select',
        'updte' => 'update',
        'insrt' => 'insert',
        'queyr' => 'query',
        'databse' => 'database',
        'talbe' => 'table',
        'colum' => 'column',
        'indx' => 'index',
        'primry' => 'primary',
        'foregin' => 'foreign',
        'constrait' => 'constraint',
        'trnasfer' => 'transfer',
        'consginment' => 'consignment',
        'invnetory' => 'inventory',
        'validtion' => 'validation',
        'authenticaton' => 'authentication',
        'authrization' => 'authorization',
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'max_edit_distance' => 2,          // Max Levenshtein distance
            'phonetic_threshold' => 0.8,       // Soundex similarity threshold
            'suggestion_count' => 5,           // Max suggestions to return
            'min_word_length' => 3,            // Min length for fuzzy matching
            'enable_phonetic' => true,
            'enable_soundex' => true,
            'enable_metaphone' => true,
        ], $config);

        $this->commonTypos = self::PROGRAMMING_TYPOS;
    }

    /**
     * Correct common typos in query
     *
     * @param string $query Original query
     * @return array ['corrected' => string, 'corrections' => array]
     */
    public function correctTypos(string $query): array
    {
        $words = preg_split('/\s+/', strtolower($query));
        $corrections = [];
        $correctedWords = [];

        foreach ($words as $word) {
            // Check exact typo match
            if (isset($this->commonTypos[$word])) {
                $correctedWords[] = $this->commonTypos[$word];
                $corrections[] = [
                    'original' => $word,
                    'corrected' => $this->commonTypos[$word],
                    'method' => 'exact_typo',
                ];
            } else {
                // Check close matches (Levenshtein distance)
                $closest = $this->findClosestMatch($word, array_keys($this->commonTypos));
                if ($closest && $closest['distance'] <= 1) {
                    $corrected = $this->commonTypos[$closest['match']];
                    $correctedWords[] = $corrected;
                    $corrections[] = [
                        'original' => $word,
                        'corrected' => $corrected,
                        'method' => 'fuzzy_typo',
                        'distance' => $closest['distance'],
                    ];
                } else {
                    $correctedWords[] = $word;
                }
            }
        }

        return [
            'corrected' => implode(' ', $correctedWords),
            'corrections' => $corrections,
            'had_corrections' => !empty($corrections),
        ];
    }

    /**
     * Generate query suggestions (did you mean?)
     *
     * @param string $query Original query
     * @param array $vocabulary Known terms from database
     * @return array Suggestions with confidence scores
     */
    public function generateSuggestions(string $query, array $vocabulary): array
    {
        $words = preg_split('/\s+/', strtolower($query));
        $suggestions = [];

        foreach ($words as $word) {
            if (strlen($word) < $this->config['min_word_length']) {
                continue;
            }

            $matches = [];

            foreach ($vocabulary as $term) {
                if (strlen($term) < $this->config['min_word_length']) {
                    continue;
                }

                // Levenshtein distance
                $distance = levenshtein($word, $term);

                if ($distance <= $this->config['max_edit_distance']) {
                    $confidence = 1 - ($distance / max(strlen($word), strlen($term)));

                    // Phonetic boost
                    if ($this->config['enable_soundex'] && soundex($word) === soundex($term)) {
                        $confidence += 0.2;
                    }

                    if ($this->config['enable_metaphone'] && metaphone($word) === metaphone($term)) {
                        $confidence += 0.2;
                    }

                    $matches[] = [
                        'term' => $term,
                        'distance' => $distance,
                        'confidence' => min($confidence, 1.0),
                    ];
                }
            }

            // Sort by confidence
            usort($matches, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

            if (!empty($matches)) {
                $suggestions[$word] = array_slice($matches, 0, $this->config['suggestion_count']);
            }
        }

        return $this->buildQuerySuggestions($query, $suggestions);
    }

    /**
     * Perform fuzzy search on terms
     *
     * @param string $needle Search term
     * @param array $haystack Array of terms to search
     * @return array Matching terms with scores
     */
    public function fuzzyMatch(string $needle, array $haystack): array
    {
        $needle = strtolower($needle);
        $matches = [];

        foreach ($haystack as $term) {
            $term_lower = strtolower($term);

            // Exact match
            if ($needle === $term_lower) {
                $matches[] = [
                    'term' => $term,
                    'score' => 1.0,
                    'method' => 'exact',
                ];
                continue;
            }

            // Contains match
            if (strpos($term_lower, $needle) !== false) {
                $score = strlen($needle) / strlen($term_lower);
                $matches[] = [
                    'term' => $term,
                    'score' => $score * 0.9,
                    'method' => 'contains',
                ];
                continue;
            }

            // Levenshtein distance
            $distance = levenshtein($needle, $term_lower);
            $maxLen = max(strlen($needle), strlen($term_lower));

            if ($distance <= $this->config['max_edit_distance']) {
                $score = 1 - ($distance / $maxLen);
                $matches[] = [
                    'term' => $term,
                    'score' => $score * 0.8,
                    'method' => 'levenshtein',
                    'distance' => $distance,
                ];
            }

            // Phonetic match
            if ($this->config['enable_soundex']) {
                if (soundex($needle) === soundex($term_lower)) {
                    $matches[] = [
                        'term' => $term,
                        'score' => 0.7,
                        'method' => 'soundex',
                    ];
                }
            }
        }

        // Sort by score descending
        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        return $matches;
    }

    /**
     * Check if two terms are phonetically similar
     *
     * @param string $term1
     * @param string $term2
     * @return bool
     */
    public function isPhoneticallySimpler(string $term1, string $term2): bool
    {
        if (!$this->config['enable_phonetic']) {
            return false;
        }

        $similar = false;

        if ($this->config['enable_soundex']) {
            $similar = $similar || (soundex($term1) === soundex($term2));
        }

        if ($this->config['enable_metaphone']) {
            $similar = $similar || (metaphone($term1) === metaphone($term2));
        }

        return $similar;
    }

    /**
     * Find closest match using Levenshtein distance
     *
     * @param string $needle
     * @param array $haystack
     * @return array|null ['match' => string, 'distance' => int]
     */
    private function findClosestMatch(string $needle, array $haystack): ?array
    {
        $closest = null;
        $minDistance = PHP_INT_MAX;

        foreach ($haystack as $term) {
            $distance = levenshtein($needle, $term);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closest = $term;
            }
        }

        if ($closest && $minDistance <= $this->config['max_edit_distance']) {
            return [
                'match' => $closest,
                'distance' => $minDistance,
            ];
        }

        return null;
    }

    /**
     * Build full query suggestions from word suggestions
     *
     * @param string $originalQuery
     * @param array $wordSuggestions
     * @return array
     */
    private function buildQuerySuggestions(string $originalQuery, array $wordSuggestions): array
    {
        if (empty($wordSuggestions)) {
            return [];
        }

        $words = preg_split('/\s+/', $originalQuery);
        $suggestions = [];

        // Build top N complete query suggestions
        foreach ($wordSuggestions as $word => $matches) {
            foreach ($matches as $match) {
                $newWords = [];
                foreach ($words as $w) {
                    $newWords[] = (strtolower($w) === $word) ? $match['term'] : $w;
                }

                $suggestions[] = [
                    'query' => implode(' ', $newWords),
                    'confidence' => $match['confidence'],
                    'changed_words' => [$word => $match['term']],
                ];
            }
        }

        // Sort by confidence and deduplicate
        usort($suggestions, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        $unique = [];
        $seen = [];
        foreach ($suggestions as $s) {
            if (!isset($seen[$s['query']])) {
                $unique[] = $s;
                $seen[$s['query']] = true;
            }
        }

        return array_slice($unique, 0, $this->config['suggestion_count']);
    }

    /**
     * Get statistics about fuzzy matching
     *
     * @return array
     */
    public function getStats(): array
    {
        return [
            'typo_database_size' => count($this->commonTypos),
            'max_edit_distance' => $this->config['max_edit_distance'],
            'phonetic_enabled' => $this->config['enable_phonetic'],
            'soundex_enabled' => $this->config['enable_soundex'],
            'metaphone_enabled' => $this->config['enable_metaphone'],
        ];
    }
}
