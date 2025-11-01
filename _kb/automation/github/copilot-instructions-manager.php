<?php
/**
 * GitHub Copilot Instructions Manager
 * Auto-update .github/copilot-instructions.md with latest patterns and context
 */

declare(strict_types=1);

class CopilotInstructionsManager 
{
    private const INSTRUCTIONS_FILE = '.github/copilot-instructions.md';
    private const VERSION = '2025.10.27.1';
    
    public function updateInstructions(): array 
    {
        $content = $this->generateInstructions();
        $this->writeInstructions($content);
        
        return [
            'success' => true,
            'version' => self::VERSION,
            'size' => strlen($content),
            'sections' => $this->getSectionCount($content)
        ];
    }
    
    private function generateInstructions(): string 
    {
        return <<<'INSTRUCTIONS'
# CIS Copilot Instructions (Auto-Generated)

**Version:** 2025.10.27.1  
**Last Updated:** October 27, 2025  
**Auto-Update:** Every 4 hours via cron  

## 🎯 Your Mission

You are working on **CIS (Central Information System)** for The Vape Shed.

### Quick Context Commands

```
@workspace Show me the current project status
@workspace #file:_kb/ What patterns do we have?
@workspace #file:modules/ Which modules need attention?
@workspace Search for [function/class/issue]
```

### CIS Architecture

- **Database:** jcepnzzkmj (MySQL)
- **Modules:** modular MVC architecture in modules/
- **Knowledge Base:** _kb/ directory
- **Patterns:** _automation/templates/

### Daily Workflow

1. **Start Session:**
   ```
   @workspace #file:_automation/prompts/daily/morning-checklist.md
   ```

2. **Working on Module:**
   ```
   @workspace #file:modules/[module-name]/ Help me with [task]
   ```

3. **Debugging:**
   ```
   @workspace Search logs for [error] and help me fix it
   ```

4. **End Session:**
   ```
   @workspace Document today's work in _kb/sessions/
   ```

## 🔧 Project-Specific Rules

- Always check modules/ for existing patterns
- Use _kb/ for decisions and documentation
- Follow PSR-12 coding standards
- Security first (prepared statements, input validation)
- Update _automation/ templates when you discover new patterns

INSTRUCTIONS;
    }
}
