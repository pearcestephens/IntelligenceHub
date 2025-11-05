<?php

namespace IntelligenceHub\MCP\Tools;

class GitHubTools extends BaseTool {

    public function getName(): string {
        return 'github';
    }

    public function getSchema(): array {
        return [
            'github.get_pr_info' => [
                'description' => 'Get pull request information',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'pr_number' => ['type' => 'integer', 'required' => true]
                ]
            ],
            'github.search_repos' => [
                'description' => 'Search GitHub repositories',
                'parameters' => [
                    'query' => ['type' => 'string', 'required' => true],
                    'limit' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'github.comment_pr' => [
                'description' => 'Comment on a pull request',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'pr_number' => ['type' => 'integer', 'required' => true],
                    'body' => ['type' => 'string', 'required' => true]
                ]
            ],
            'github.label_pr' => [
                'description' => 'Add labels to a pull request',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'pr_number' => ['type' => 'integer', 'required' => true],
                    'labels' => ['type' => 'array', 'required' => true]
                ]
            ],
            'github.get_pr_diff' => [
                'description' => 'Get pull request diff',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'pr_number' => ['type' => 'integer', 'required' => true]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'get_pr_info';

        switch ($method) {
            case 'get_pr_info':
                return $this->getPrInfo($args);
            case 'search_repos':
                return $this->searchRepos($args);
            case 'comment_pr':
                return $this->commentPr($args);
            case 'label_pr':
                return $this->labelPr($args);
            case 'get_pr_diff':
                return $this->getPrDiff($args);
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    private function getPrInfo(array $args): array {
        $owner = $args['owner'] ?? '';
        $repo = $args['repo'] ?? '';
        $prNumber = $args['pr_number'] ?? 0;

        if (!$owner || !$repo || !$prNumber) {
            return $this->fail('owner, repo, and pr_number are required');
        }

        $url = "https://api.github.com/repos/{$owner}/{$repo}/pulls/{$prNumber}";
        $result = $this->githubRequest('GET', $url);

        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        return $this->ok($result['data']);
    }

    private function searchRepos(array $args): array {
        $query = $args['query'] ?? '';
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        if (!$query) {
            return $this->fail('query is required');
        }

        $url = "https://api.github.com/search/repositories?q=" . urlencode($query) . "&per_page={$limit}";
        $result = $this->githubRequest('GET', $url);

        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        return $this->ok($result['data']);
    }

    private function commentPr(array $args): array {
        $owner = $args['owner'] ?? '';
        $repo = $args['repo'] ?? '';
        $prNumber = $args['pr_number'] ?? 0;
        $body = $args['body'] ?? '';

        if (!$owner || !$repo || !$prNumber || !$body) {
            return $this->fail('owner, repo, pr_number, and body are required');
        }

        $url = "https://api.github.com/repos/{$owner}/{$repo}/issues/{$prNumber}/comments";
        $result = $this->githubRequest('POST', $url, ['body' => $body]);

        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        return $this->ok($result['data']);
    }

    private function labelPr(array $args): array {
        $owner = $args['owner'] ?? '';
        $repo = $args['repo'] ?? '';
        $prNumber = $args['pr_number'] ?? 0;
        $labels = $args['labels'] ?? [];

        if (!$owner || !$repo || !$prNumber || empty($labels)) {
            return $this->fail('owner, repo, pr_number, and labels are required');
        }

        $url = "https://api.github.com/repos/{$owner}/{$repo}/issues/{$prNumber}/labels";
        $result = $this->githubRequest('POST', $url, ['labels' => $labels]);

        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        return $this->ok($result['data']);
    }

    private function getPrDiff(array $args): array {
        $owner = $args['owner'] ?? '';
        $repo = $args['repo'] ?? '';
        $prNumber = $args['pr_number'] ?? 0;

        if (!$owner || !$repo || !$prNumber) {
            return $this->fail('owner, repo, and pr_number are required');
        }

        $url = "https://api.github.com/repos/{$owner}/{$repo}/pulls/{$prNumber}";
        $result = $this->githubRequest('GET', $url, null, 'application/vnd.github.v3.diff');

        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        return $this->ok(['diff' => $result['data']]);
    }

    private function githubRequest(string $method, string $url, ?array $payload = null, ?string $accept = null): array {
        $token = $_ENV['GITHUB_TOKEN'] ?? '';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MCP-Tools/1.0');

        $headers = [
            'Accept: ' . ($accept ?? 'application/vnd.github.v3+json')
        ];

        if ($token) {
            $headers[] = 'Authorization: token ' . $token;
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                $headers[] = 'Content-Type: application/json';
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error, 'code' => 502, 'data' => null];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['error' => "HTTP $httpCode", 'code' => $httpCode, 'data' => null];
        }

        // Try to decode JSON, otherwise return raw
        $json = json_decode($response, true);
        return ['error' => null, 'code' => $httpCode, 'data' => $json ?? $response];
    }
}
