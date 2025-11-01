<?php

declare(strict_types=1);

namespace App\Multi;

/**
 * Agent Roles
 * 
 * Predefined specialist roles for multi-agent systems
 * 
 * @package App\Multi
 * @author Feature Enhancement Phase 2
 */
class AgentRole
{
    /**
     * Researcher: Web search, data gathering, information synthesis
     */
    public const RESEARCHER = 'researcher';
    
    /**
     * Coder: Code generation, debugging, refactoring
     */
    public const CODER = 'coder';
    
    /**
     * Analyst: Data analysis, insights, pattern recognition
     */
    public const ANALYST = 'analyst';
    
    /**
     * Writer: Documentation, content creation, communication
     */
    public const WRITER = 'writer';
    
    /**
     * Coordinator: Task delegation, result synthesis, orchestration
     */
    public const COORDINATOR = 'coordinator';
    
    /**
     * Security: Security audits, vulnerability scanning
     */
    public const SECURITY = 'security';
    
    /**
     * Performance: Performance optimization, profiling
     */
    public const PERFORMANCE = 'performance';
    
    /**
     * Tester: Test generation, quality assurance
     */
    public const TESTER = 'tester';

    /**
     * Get all available roles
     */
    public static function getAllRoles(): array
    {
        return [
            self::RESEARCHER,
            self::CODER,
            self::ANALYST,
            self::WRITER,
            self::COORDINATOR,
            self::SECURITY,
            self::PERFORMANCE,
            self::TESTER,
        ];
    }

    /**
     * Get role description
     */
    public static function getDescription(string $role): string
    {
        return match($role) {
            self::RESEARCHER => 'Specializes in research, information gathering, and data synthesis',
            self::CODER => 'Specializes in code generation, debugging, and software development',
            self::ANALYST => 'Specializes in data analysis, insights extraction, and pattern recognition',
            self::WRITER => 'Specializes in documentation, technical writing, and clear communication',
            self::COORDINATOR => 'Specializes in task orchestration, delegation, and result synthesis',
            self::SECURITY => 'Specializes in security audits, vulnerability scanning, and secure coding',
            self::PERFORMANCE => 'Specializes in performance optimization, profiling, and efficiency',
            self::TESTER => 'Specializes in test generation, quality assurance, and validation',
            default => 'General purpose agent',
        };
    }

    /**
     * Get recommended tools for role
     */
    public static function getRecommendedTools(string $role): array
    {
        return match($role) {
            self::RESEARCHER => ['http', 'knowledge', 'code', 'memory'],
            self::CODER => ['code', 'file', 'database', 'static_analysis', 'grep'],
            self::ANALYST => ['database', 'code', 'monitoring', 'performance_test'],
            self::WRITER => ['file', 'knowledge', 'memory'],
            self::COORDINATOR => ['http', 'database', 'knowledge', 'memory'],
            self::SECURITY => ['security_scan', 'static_analysis', 'file', 'grep'],
            self::PERFORMANCE => ['performance_test', 'monitoring', 'database', 'code'],
            self::TESTER => ['code', 'file', 'static_analysis', 'ready_check'],
            default => [],
        };
    }

    /**
     * Get optimal temperature setting for role
     */
    public static function getTemperature(string $role): float
    {
        return match($role) {
            self::RESEARCHER => 0.7,  // Balanced creativity
            self::CODER => 0.3,       // Precise and deterministic
            self::ANALYST => 0.5,     // Logical and methodical
            self::WRITER => 0.8,      // Creative writing
            self::COORDINATOR => 0.6, // Balanced decision making
            self::SECURITY => 0.2,    // Very precise
            self::PERFORMANCE => 0.4, // Methodical optimization
            self::TESTER => 0.3,      // Systematic testing
            default => 0.7,
        };
    }

    /**
     * Get role expertise weight (for weighted voting)
     */
    public static function getExpertiseWeight(string $role): float
    {
        return match($role) {
            self::RESEARCHER => 1.2,
            self::CODER => 1.5,
            self::ANALYST => 1.3,
            self::WRITER => 1.0,
            self::COORDINATOR => 1.1,
            self::SECURITY => 1.4,
            self::PERFORMANCE => 1.3,
            self::TESTER => 1.2,
            default => 1.0,
        };
    }
}
