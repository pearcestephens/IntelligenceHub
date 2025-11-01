<?php
/**
 * File Cache Backend
 *
 * @package IntelligenceHub\MCP\Cache
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Cache;

class FileCache implements CacheInterface
{
    private string $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/');
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key)
    {
        $file = $this->getCacheFile($key);
        if (!file_exists($file)) {
            return null;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['expires'], $data['value'])) {
            return null;
        }

        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $file = $this->getCacheFile($key);
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = [
            'expires' => time() + $ttl,
            'value' => $value,
        ];

        return (bool)file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public function delete(string $key): bool
    {
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    public function clear(): bool
    {
        $this->deleteDirectory($this->cachePath);
        mkdir($this->cachePath, 0755, true);
        return true;
    }

    public function getStats(): array
    {
        $files = glob($this->cachePath . '/*/*.cache');
        $totalSize = 0;
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }

        return [
            'available' => true,
            'files' => count($files),
            'size' => $totalSize,
            'path' => $this->cachePath,
        ];
    }

    private function getCacheFile(string $key): string
    {
        $hash = md5($key);
        $dir = substr($hash, 0, 2);
        return $this->cachePath . '/' . $dir . '/' . $hash . '.cache';
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
