<?php
/**
 * Redis Configuration
 * 
 * Central cache layer for 10-100x performance boost
 * 
 * @package Ecigdis\Config
 * @version 1.0.0
 */

return [
    // Connection settings
    'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
    'port' => (int)(getenv('REDIS_PORT') ?: 6379),
    'password' => getenv('REDIS_PASSWORD') ?: null,
    'database' => (int)(getenv('REDIS_DATABASE') ?: 0),
    'prefix' => 'ecig:',
    'timeout' => 2.5,
    'read_timeout' => 2.5,
    
    // TTL strategies (seconds)
    'ttl' => [
        'kb_file' => 3600,        // 1 hour - file content
        'kb_search' => 900,       // 15 min - search results
        'kb_function' => 7200,    // 2 hours - function definitions
        'kb_class' => 7200,       // 2 hours - class definitions
        'api_auth' => 3600,       // 1 hour - API key validation
        'api_rate' => 3600,       // 1 hour - rate limit counters
        'chat_session' => 86400,  // 24 hours - active chat
        'ai_context' => 3600,     // 1 hour - conversation context
        'bi_metrics' => 86400,    // 24 hours - daily rollups
        'user_session' => 7200,   // 2 hours - web sessions
    ],
    
    // Cache key patterns
    'patterns' => [
        'kb_file' => 'kb:file:{hash}',
        'kb_search' => 'kb:search:{query_hash}',
        'kb_function' => 'kb:func:{id}',
        'kb_class' => 'kb:class:{id}',
        'api_key' => 'api:key:{key_id}',
        'api_rate' => 'api:rate:{key_id}:{hour}',
        'chat_session' => 'chat:session:{id}',
        'ai_conv' => 'ai:conv:{id}:messages',
        'bi_metric' => 'bi:metric:{date}:{type}',
    ],
    
    // Performance settings
    'serializer' => Redis::SERIALIZER_JSON,
    'compression' => Redis::COMPRESSION_NONE,
    
    // Health check settings
    'health_check_interval' => 60, // seconds
    'retry_interval' => 5, // seconds
    'max_retries' => 3,
];
