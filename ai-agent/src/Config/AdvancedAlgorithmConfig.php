<?php

/**
 * Advanced Algorithm Configuration
 * Sophisticated settings for enterprise-grade AI intelligence algorithms
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package VapeShed Enterprise AI Platform
 * @version 2.0.0 - Algorithm Configuration
 */

declare(strict_types=1);

namespace App\Config;

class AdvancedAlgorithmConfig
{
    /**
     * Machine Learning Algorithm Settings
     */
    public const ML_ALGORITHMS = [
        'prediction_models' => [
            'linear_regression' => [
                'enabled' => true,
                'weight' => 0.25,
                'min_data_points' => 10,
                'confidence_threshold' => 0.7
            ],
            'exponential_smoothing' => [
                'enabled' => true,
                'weight' => 0.2,
                'alpha' => 0.3,
                'confidence_threshold' => 0.65
            ],
            'arima' => [
                'enabled' => true,
                'weight' => 0.2,
                'lag_periods' => 5,
                'confidence_threshold' => 0.6
            ],
            'neural_network' => [
                'enabled' => true,
                'weight' => 0.3,
                'hidden_layers' => 3,
                'activation' => 'sigmoid',
                'confidence_threshold' => 0.75
            ],
            'ensemble' => [
                'enabled' => true,
                'weight' => 0.05,
                'voting_strategy' => 'weighted_average',
                'min_models' => 3
            ]
        ],

        'clustering_algorithms' => [
            'kmeans' => [
                'enabled' => true,
                'max_clusters' => 10,
                'max_iterations' => 100,
                'tolerance' => 0.001
            ],
            'hierarchical' => [
                'enabled' => true,
                'linkage' => 'ward',
                'distance_threshold' => 0.5
            ],
            'dbscan' => [
                'enabled' => false,
                'eps' => 0.5,
                'min_samples' => 5
            ]
        ],

        'anomaly_detection' => [
            'statistical' => [
                'enabled' => true,
                'z_score_threshold' => 3.0,
                'window_size' => 50
            ],
            'isolation_forest' => [
                'enabled' => true,
                'contamination' => 0.1,
                'n_estimators' => 100
            ],
            'local_outlier_factor' => [
                'enabled' => true,
                'neighbors' => 20,
                'contamination' => 0.1
            ]
        ]
    ];

    /**
     * Vector Search Algorithm Settings
     */
    public const VECTOR_ALGORITHMS = [
        'similarity_measures' => [
            'cosine' => [
                'enabled' => true,
                'weight' => 0.4,
                'threshold' => 0.7
            ],
            'euclidean' => [
                'enabled' => true,
                'weight' => 0.2,
                'threshold' => 0.6,
                'normalize' => true
            ],
            'manhattan' => [
                'enabled' => true,
                'weight' => 0.15,
                'threshold' => 0.65
            ],
            'jaccard' => [
                'enabled' => true,
                'weight' => 0.15,
                'threshold' => 0.5,
                'binary_threshold' => 0.1
            ],
            'pearson' => [
                'enabled' => true,
                'weight' => 0.1,
                'threshold' => 0.6
            ]
        ],

        'ranking_algorithms' => [
            'bm25' => [
                'enabled' => true,
                'k1' => 1.2,
                'b' => 0.75
            ],
            'tf_idf' => [
                'enabled' => true,
                'use_log_normalization' => true
            ],
            'pagerank' => [
                'enabled' => false,
                'damping_factor' => 0.85,
                'iterations' => 50
            ]
        ],

        'embedding_optimization' => [
            'dimensionality_reduction' => [
                'enabled' => true,
                'method' => 'pca',
                'target_dimensions' => 512
            ],
            'compression' => [
                'enabled' => true,
                'method' => 'quantization',
                'bits' => 8
            ],
            'caching_strategy' => [
                'l1_cache_size' => 1000,
                'l2_cache_size' => 10000,
                'ttl' => 86400
            ]
        ]
    ];

    /**
     * Performance Monitoring Algorithm Settings
     */
    public const PERFORMANCE_ALGORITHMS = [
        'metrics_collection' => [
            'sampling_rate' => 0.1, // 10% sampling
            'aggregation_intervals' => [
                'real_time' => 60,     // 1 minute
                'short_term' => 300,   // 5 minutes
                'medium_term' => 3600, // 1 hour
                'long_term' => 86400   // 1 day
            ],
            'retention_periods' => [
                'raw_data' => 7,       // 7 days
                'aggregated' => 90,    // 90 days
                'summaries' => 365     // 1 year
            ]
        ],

        'anomaly_detection' => [
            'algorithms' => [
                'z_score' => [
                    'enabled' => true,
                    'threshold' => 3.0,
                    'window_size' => 100
                ],
                'iqr' => [
                    'enabled' => true,
                    'multiplier' => 1.5
                ],
                'isolation_forest' => [
                    'enabled' => true,
                    'contamination' => 0.05
                ],
                'lstm_autoencoder' => [
                    'enabled' => false,
                    'sequence_length' => 50,
                    'threshold' => 0.95
                ]
            ],
            'severity_thresholds' => [
                'low' => 1.0,
                'medium' => 2.0,
                'high' => 3.0,
                'critical' => 4.0
            ]
        ],

        'predictive_analytics' => [
            'capacity_planning' => [
                'forecasting_models' => ['arima', 'exponential_smoothing', 'linear_regression'],
                'prediction_horizon' => 90, // days
                'confidence_interval' => 0.95,
                'growth_rate_smoothing' => 0.3
            ],
            'performance_degradation' => [
                'trend_analysis_window' => 30, // days
                'degradation_threshold' => 0.05, // 5%
                'early_warning_days' => 7
            ]
        ]
    ];

    /**
     * Business Intelligence Algorithm Settings
     */
    public const BUSINESS_ALGORITHMS = [
        'sales_forecasting' => [
            'models' => [
                'seasonal_decomposition' => [
                    'enabled' => true,
                    'seasonal_periods' => [7, 30, 365], // weekly, monthly, yearly
                    'trend_smoothing' => 0.3
                ],
                'regression_models' => [
                    'enabled' => true,
                    'features' => ['seasonality', 'trends', 'promotions', 'external_factors']
                ],
                'time_series_models' => [
                    'enabled' => true,
                    'models' => ['arima', 'sarima', 'exponential_smoothing']
                ]
            ],
            'accuracy_thresholds' => [
                'excellent' => 0.95,
                'good' => 0.85,
                'acceptable' => 0.75,
                'poor' => 0.65
            ]
        ],

        'customer_analytics' => [
            'segmentation' => [
                'behavioral_clustering' => [
                    'enabled' => true,
                    'features' => ['purchase_frequency', 'avg_order_value', 'product_variety', 'seasonality'],
                    'num_clusters' => 5
                ],
                'rfm_analysis' => [
                    'enabled' => true,
                    'recency_bins' => 5,
                    'frequency_bins' => 5,
                    'monetary_bins' => 5
                ]
            ],
            'churn_prediction' => [
                'enabled' => true,
                'features' => ['days_since_last_purchase', 'purchase_frequency_trend', 'engagement_score'],
                'threshold' => 0.7,
                'prediction_horizon' => 30 // days
            ],
            'lifetime_value' => [
                'calculation_method' => 'cohort_based',
                'prediction_period' => 365, // days
                'discount_rate' => 0.1
            ]
        ],

        'inventory_optimization' => [
            'demand_forecasting' => [
                'models' => ['moving_average', 'exponential_smoothing', 'arima'],
                'seasonality_adjustment' => true,
                'promotion_impact_modeling' => true
            ],
            'stock_optimization' => [
                'safety_stock_calculation' => 'service_level_based',
                'service_level_target' => 0.95,
                'lead_time_variability' => true,
                'demand_variability' => true
            ],
            'reorder_point_calculation' => [
                'method' => 'probabilistic',
                'review_period' => 7, // days
                'stockout_cost_factor' => 2.0
            ]
        ]
    ];

    /**
     * Real-time Processing Settings
     */
    public const REALTIME_PROCESSING = [
        'stream_processing' => [
            'batch_size' => 100,
            'flush_interval' => 5000, // milliseconds
            'max_latency' => 1000, // milliseconds
            'parallelism' => 4
        ],

        'caching_strategies' => [
            'hot_data' => [
                'cache_type' => 'redis',
                'ttl' => 300, // 5 minutes
                'max_size' => '100MB'
            ],
            'warm_data' => [
                'cache_type' => 'redis',
                'ttl' => 3600, // 1 hour
                'max_size' => '500MB'
            ],
            'cold_data' => [
                'cache_type' => 'file',
                'ttl' => 86400, // 1 day
                'max_size' => '1GB'
            ]
        ],

        'priority_queues' => [
            'critical' => [
                'max_size' => 1000,
                'processing_timeout' => 1000 // milliseconds
            ],
            'high' => [
                'max_size' => 5000,
                'processing_timeout' => 5000 // milliseconds
            ],
            'normal' => [
                'max_size' => 10000,
                'processing_timeout' => 10000 // milliseconds
            ],
            'low' => [
                'max_size' => 50000,
                'processing_timeout' => 30000 // milliseconds
            ]
        ]
    ];

    /**
     * Security and Privacy Settings
     */
    public const SECURITY_SETTINGS = [
        'data_protection' => [
            'encryption_at_rest' => true,
            'encryption_in_transit' => true,
            'pii_anonymization' => true,
            'data_retention_policy' => [
                'raw_data' => 90,      // days
                'aggregated_data' => 365, // days
                'anonymized_data' => 2555 // 7 years
            ]
        ],

        'access_control' => [
            'role_based_access' => true,
            'api_rate_limiting' => [
                'requests_per_minute' => 1000,
                'burst_capacity' => 100
            ],
            'audit_logging' => [
                'enabled' => true,
                'log_level' => 'INFO',
                'retention_days' => 365
            ]
        ],

        'algorithm_security' => [
            'model_integrity_checking' => true,
            'adversarial_detection' => true,
            'differential_privacy' => [
                'enabled' => false,
                'epsilon' => 1.0,
                'delta' => 1e-5
            ]
        ]
    ];

    /**
     * Quality Assurance Settings
     */
    public const QUALITY_SETTINGS = [
        'model_validation' => [
            'cross_validation_folds' => 5,
            'test_set_size' => 0.2,
            'validation_metrics' => ['accuracy', 'precision', 'recall', 'f1_score'],
            'minimum_accuracy' => 0.8
        ],

        'data_quality' => [
            'completeness_threshold' => 0.95,
            'consistency_checks' => true,
            'outlier_detection' => true,
            'duplicate_detection' => true
        ],

        'algorithm_monitoring' => [
            'performance_degradation_threshold' => 0.05,
            'drift_detection' => [
                'enabled' => true,
                'window_size' => 1000,
                'drift_threshold' => 0.1
            ],
            'alert_thresholds' => [
                'accuracy_drop' => 0.1,
                'latency_increase' => 2.0,
                'error_rate_increase' => 0.05
            ]
        ]
    ];

    /**
     * Get algorithm configuration
     */
    public static function getConfig(string $category, string $algorithm = null): array
    {
        $configs = [
            'ml' => self::ML_ALGORITHMS,
            'vector' => self::VECTOR_ALGORITHMS,
            'performance' => self::PERFORMANCE_ALGORITHMS,
            'business' => self::BUSINESS_ALGORITHMS,
            'realtime' => self::REALTIME_PROCESSING,
            'security' => self::SECURITY_SETTINGS,
            'quality' => self::QUALITY_SETTINGS
        ];

        if (!isset($configs[$category])) {
            return [];
        }

        if ($algorithm === null) {
            return $configs[$category];
        }

        return $configs[$category][$algorithm] ?? [];
    }

    /**
     * Validate algorithm configuration
     */
    public static function validateConfig(string $category, array $config): bool
    {
        $defaultConfig = self::getConfig($category);

        if (empty($defaultConfig)) {
            return false;
        }

        // Basic validation - ensure required keys exist
        foreach ($defaultConfig as $key => $value) {
            if (is_array($value) && isset($value['enabled'])) {
                if (!isset($config[$key]['enabled'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get optimal algorithm settings based on current system state
     */
    public static function getOptimalSettings(string $category, array $systemMetrics = []): array
    {
        $config = self::getConfig($category);

        // Adjust settings based on system performance
        if (!empty($systemMetrics)) {
            $config = self::optimizeForPerformance($config, $systemMetrics);
        }

        return $config;
    }

    /**
     * Optimize algorithm settings for current performance
     */
    private static function optimizeForPerformance(array $config, array $metrics): array
    {
        $cpuUsage = $metrics['cpu_usage'] ?? 0.5;
        $memoryUsage = $metrics['memory_usage'] ?? 0.5;
        $latency = $metrics['avg_latency'] ?? 0.5;

        // If system is under high load, reduce algorithm complexity
        if ($cpuUsage > 0.8 || $memoryUsage > 0.8 || $latency > 2.0) {
            // Reduce sampling rates
            if (isset($config['metrics_collection']['sampling_rate'])) {
                $config['metrics_collection']['sampling_rate'] *= 0.5;
            }

            // Reduce cache sizes
            if (isset($config['caching_strategies'])) {
                foreach ($config['caching_strategies'] as &$cache) {
                    if (isset($cache['max_size'])) {
                        $cache['max_size'] = intval(preg_replace('/[^\d]/', '', $cache['max_size']) * 0.7) . 'MB';
                    }
                }
            }

            // Disable expensive algorithms
            if (isset($config['anomaly_detection']['algorithms']['lstm_autoencoder'])) {
                $config['anomaly_detection']['algorithms']['lstm_autoencoder']['enabled'] = false;
            }
        }

        return $config;
    }
}
