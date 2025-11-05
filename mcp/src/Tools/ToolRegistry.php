<?php
namespace IntelligenceHub\MCP\Tools;

class ToolRegistry {
    private array $tools = [];
    private array $routes = [];

    public function registerTool(ToolInterface $tool): void {
        $this->tools[$tool->getName()] = $tool;
    }

    public function registerRoute(string $name, string $url): void {
        $this->routes[$name] = $url;
    }

    public function execute(string $name, array $args): array {
        // Check if it's a dotted name (db.query_readonly, fs.write)
        if (strpos($name, '.') !== false) {
            [$prefix, $method] = explode('.', $name, 2);

            // Check if we have the tool registered
            if (isset($this->tools[$prefix])) {
                // Add method to args
                $args['_method'] = $method;
                return $this->tools[$prefix]->execute($args);
            }
        }

        // Check direct tool name
        if (isset($this->tools[$name])) {
            return $this->tools[$name]->execute($args);
        }

        // Check HTTP routes
        if (isset($this->routes[$name])) {
            return $this->proxyHttp($this->routes[$name], $args);
        }

        return ['status' => 404, 'data' => ['error' => "Tool not found: $name"]];
    }

    public function getAllTools(): array {
        return array_keys($this->tools);
    }

    private function proxyHttp(string $url, array $args): array {
        // HTTP proxy logic here
        return ['status' => 404, 'data' => ['error' => 'HTTP proxy not implemented']];
    }
}
