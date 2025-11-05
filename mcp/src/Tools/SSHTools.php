<?php

namespace IntelligenceHub\MCP\Tools;

class SSHTools extends BaseTool {

    public function getName(): string {
        return 'ssh';
    }

    public function getSchema(): array {
        return [
            'ssh.exec_allowlist' => [
                'description' => 'Execute allowed SSH commands',
                'parameters' => [
                    'command' => ['type' => 'string', 'required' => true]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'exec_allowlist';

        switch ($method) {
            case 'exec_allowlist':
                return $this->execAllowlist($args);
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    private function execAllowlist(array $args): array {
        // Check if SSH is enabled
        if (($_ENV['SSH_ENABLE'] ?? '0') !== '1') {
            return $this->fail('SSH disabled (set SSH_ENABLE=1 to enable)', 403);
        }

        $cmd = trim($args['command'] ?? '');
        if (empty($cmd)) {
            return $this->fail('command is required');
        }

        // Check allowlist
        $allowRaw = trim($_ENV['SSH_ALLOWED_CMDS'] ?? '');
        if ($allowRaw === '') {
            return $this->fail('SSH_ALLOWED_CMDS not configured', 400);
        }

        $allowed = array_filter(array_map('trim', explode('|', $allowRaw)));
        $match = false;
        foreach ($allowed as $allowedCommand) {
            if ($cmd === $allowedCommand) {
                $match = true;
                break;
            }
        }

        if (!$match) {
            return $this->fail('Command not allow-listed', 403, ['allowed' => $allowed]);
        }

        // Get SSH config
        $host = $_ENV['SSH_HOST'] ?? '';
        $user = $_ENV['SSH_USER'] ?? '';
        $keyPath = $_ENV['SSH_KEY_PATH'] ?? '';

        if ($host === '' || $user === '' || $keyPath === '') {
            return $this->fail('SSH host/user/key not configured', 400);
        }

        // Build SSH command
        $escapedCmd = escapeshellarg($cmd);
        $ssh = sprintf(
            'ssh -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s -- %s',
            escapeshellarg($keyPath),
            escapeshellarg($user),
            escapeshellarg($host),
            $escapedCmd
        );

        // Execute
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open($ssh, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            return $this->fail('Failed to open SSH process', 500);
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            return $this->fail('SSH non-zero exit', 500, [
                'stderr' => $stderr,
                'exit_code' => $exitCode
            ]);
        }

        return $this->ok([
            'stdout' => $stdout,
            'exit_code' => $exitCode
        ]);
    }
}
