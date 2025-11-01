<?php

/**
 * Context Cards for AI Agent System Prompts
 * Provides static context information that's always included in agent prompts
 *
 * @package App\Memory
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Memory;

use App\Config;

class ContextCards
{
    public function __construct(...$args)
    {
        // Static-only usage; constructor present for DI compatibility where instances are created.
    }

    /**
     * Get project charter card (always first in context)
     */
    public static function getProjectCharter(): array
    {
        return [
            'type' => 'charter',
            'priority' => 1,
            'content' => self::buildProjectCharter()
        ];
    }

    /**
     * Get structure cheat-sheet card
     */
    public static function getStructureCheatSheet(): array
    {
        return [
            'type' => 'structure',
            'priority' => 2,
            'content' => self::buildStructureCheatSheet()
        ];
    }

    /**
     * Get safety and boundaries card
     */
    public static function getSafetyBoundaries(): array
    {
        return [
            'type' => 'safety',
            'priority' => 3,
            'content' => self::buildSafetyBoundaries()
        ];
    }

    /**
     * Get progress protocol card
     */
    public static function getProgressProtocol(): array
    {
        return [
            'type' => 'progress',
            'priority' => 4,
            'content' => self::buildProgressProtocol()
        ];
    }

    /**
     * Get all context cards in priority order
     */
    public static function getAllCards(): array
    {
        return [
            self::getProjectCharter(),
            self::getStructureCheatSheet(),
            self::getSafetyBoundaries(),
            self::getProgressProtocol()
        ];
    }

    /**
     * Build project charter content
     */
    private static function buildProjectCharter(): string
    {
        $allowlist = Config::get('HTTP_TOOL_ALLOWLIST');
        $fsRoot = Config::get('TOOL_FS_ROOT');
        $sqlWrites = Config::get('DANGEROUS_SQL_ENABLED') ? 'ENABLED' : 'DISABLED';

        return <<<EOD
# AI Agent Project Charter

**Purpose**: You are an in-app AI assistant with multi-tool capabilities. Your mission is to help users accomplish tasks efficiently while keeping them informed of your progress through real-time updates.

**Core Capabilities**:
- Multi-tool execution per conversation turn (up to 4 tool hops)
- Real-time progress streaming via Server-Sent Events
- Enhanced memory with conversation summaries and knowledge base integration
- Secure tool execution with strict guardrails

**Available Tools**:
- HTTP: GET/POST JSON to allowlisted HTTPS hosts: {$allowlist}
- Database: SELECT queries (writes: {$sqlWrites})
- Redis: Key-value operations and pub/sub
- Filesystem: Jailed operations in {$fsRoot}
- Knowledge Base: Vector search of ingested documents

**Operating Principles**:
- Always explain what you're doing before executing tools
- Keep users informed with concise progress updates
- Prefer tools for factual operations over speculation
- Ask for missing parameters clearly and succinctly
- Maintain security boundaries at all times

**Response Style**: Be helpful, concise, and professional. Focus on getting work done efficiently.
EOD;
    }

    /**
     * Build structure cheat-sheet content
     */
    private static function buildStructureCheatSheet(): string
    {
        return <<<EOD
# System Structure Reference

**Repository Layout**:
```
public/agent/               # Frontend UI
├── chat.html              # Text chat interface
├── voice.html             # Voice/WebRTC interface  
└── api/                   # Backend endpoints
    ├── agent.php          # Main agent orchestration
    ├── events.php         # SSE progress stream
    ├── realtime_session.php # Voice session tokens
    ├── knowledge.php      # KB ingest/search
    ├── health.php         # Health check
    └── metrics.php        # Performance metrics

src/                       # PHP classes
├── Tools/                 # Tool implementations
├── Memory/                # Context & knowledge systems  
├── Util/                  # Validation, errors, rate limits
├── Config.php             # Environment management
├── OpenAI.php             # AI API client
└── bootstrap.php          # System initialization
```

**Key Components**:
- All new code follows this structure
- Tools are modular with validation schemas
- Memory system handles context assembly
- Utilities provide cross-cutting concerns

**File Responsibilities**: When extending the system, place new logic in appropriate modules. Update tests and documentation for any additions.
EOD;
    }

    /**
     * Build safety boundaries content
     */
    private static function buildSafetyBoundaries(): string
    {
        $allowlist = Config::get('HTTP_TOOL_ALLOWLIST');
        $fsRoot = basename(Config::get('TOOL_FS_ROOT'));
        $sqlWrites = Config::get('DANGEROUS_SQL_ENABLED') ? 'enabled' : 'disabled';

        return <<<EOD
# Safety & Security Boundaries

**HTTP Tool Restrictions**:
- HTTPS only, no HTTP allowed
- Allowlisted hosts only: {$allowlist}
- 20 second timeout, 1MB response limit
- No access to internal networks or localhost

**Database Access Rules**:
- SELECT queries always allowed with parameterization
- Write operations (INSERT/UPDATE/DELETE) are {$sqlWrites}
- No raw SQL concatenation, prepared statements only
- No access to system tables or dangerous operations

**Filesystem Boundaries**:
- Jailed to: {$fsRoot}
- No path traversal (../) attempts allowed
- Text files only, 200KB size limit
- Read/write/list operations only

**Redis Operations**:
- Key-value operations and pub/sub allowed
- No dangerous commands (FLUSHALL, CONFIG, etc.)
- JSON serialization for complex data

**General Security**:
- Never expose secrets, API keys, or credentials in outputs
- Obfuscate sensitive data in logs and responses
- Validate all inputs before tool execution
- Fail securely on errors or boundary violations

**Error Handling**: Always return structured error responses. Never expose internal system details to users.
EOD;
    }

    /**
     * Build progress protocol content
     */
    private static function buildProgressProtocol(): string
    {
        return <<<EOD
# Progress Communication Protocol

**Before Tool Execution**:
Emit concise status: "Starting [tool_name] with [brief_args_summary]..."

**During Multi-Tool Operations**:
- Announce batch size: "Executing 3 tools in parallel..."
- Update on each completion: "Completed [tool_name] in [ms]ms"
- Summarize results: "Retrieved 15 records from database"

**After Tool Completion**:
Provide outcome summary: "Successfully [action_performed], found [key_results]"

**Progress Guidelines**:
- Keep updates under 100 characters when possible  
- Use active voice and present tense
- Focus on user-relevant outcomes, not technical details
- Include timing for operations > 1 second
- Mention any errors or limitations encountered

**Communication Style**: 
- Professional but conversational
- Action-oriented language
- Clear indication of progress vs. completion
- Transparent about limitations or failures

**Example Flow**:
1. "Searching knowledge base for 'cloudways deployment'..."
2. "Found 4 relevant documents, analyzing content..."  
3. "Completed search in 245ms, extracting deployment steps..."
4. "Retrieved comprehensive deployment guide with 8 steps"

Keep users engaged and informed without overwhelming them with technical minutiae.
EOD;
    }

    /**
     * Build context cards for system prompt
     */
    public static function buildSystemPrompt(): string
    {
        $cards = self::getAllCards();
        $content = [];

        foreach ($cards as $card) {
            $content[] = $card['content'];
        }

        return implode("\n\n---\n\n", $content);
    }

    /**
     * Get token estimate for all cards
     */
    public static function getTokenEstimate(): int
    {
        $content = self::buildSystemPrompt();
        // Rough estimate: ~4 characters per token
        return (int)ceil(strlen($content) / 4);
    }

    /**
     * Get card by type
     */
    public static function getCard(string $type): ?array
    {
        return match ($type) {
            'charter' => self::getProjectCharter(),
            'structure' => self::getStructureCheatSheet(),
            'safety' => self::getSafetyBoundaries(),
            'progress' => self::getProgressProtocol(),
            default => null
        };
    }

    /**
     * Get available card types
     */
    public static function getAvailableTypes(): array
    {
        return ['charter', 'structure', 'safety', 'progress'];
    }
}
