<?php
/**
 * VS Code Settings Manager
 * Auto-sync and version VS Code settings across projects
 */

declare(strict_types=1);

class VSCodeSettingsManager 
{
    private const SETTINGS_PATH = '.vscode/settings.json';
    private const BACKUP_PATH = '_automation/sync/settings-backup/';
    
    public function syncSettings(): array 
    {
        $timestamp = date('Y-m-d H:i:s');
        
        // Backup current settings
        $this->backupCurrentSettings();
        
        // Load template settings
        $settings = $this->loadTemplateSettings();
        
        // Add CIS-specific configuration
        $settings = $this->addCISConfiguration($settings);
        
        // Write new settings
        $this->writeSettings($settings);
        
        return [
            'success' => true,
            'timestamp' => $timestamp,
            'version' => $this->getVersion(),
            'changes' => $this->getChanges()
        ];
    }
    
    private function addCISConfiguration(array $settings): array 
    {
        // Add CIS-specific paths and configurations
        $settings['copilot.advanced'] = [
            'cis.workspaceContext' => true,
            'cis.knowledgeBase' => '_kb/',
            'cis.modulePattern' => 'modules/{name}/',
            'cis.autoInstructions' => '.github/copilot-instructions.md'
        ];
        
        $settings['files.associations'] = array_merge(
            $settings['files.associations'] ?? [],
            [
                '*.cis' => 'php',
                'module_*.php' => 'php',
                '*.kb.md' => 'markdown'
            ]
        );
        
        return $settings;
    }
    
    private function getVersion(): string 
    {
        return '2025.10.27.1';
    }
}
