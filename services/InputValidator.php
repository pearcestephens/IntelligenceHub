<?php
/**
 * Input Validation Service
 * Automatically added by Security Hardening Script
 */
declare(strict_types=1);

class InputValidator {
    public static function sanitizeString(string $input, int $maxLength = 255): string {
        $input = trim($input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return substr($input, 0, $maxLength);
    }
    
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validateInteger(mixed $value, int $min = null, int $max = null): ?int {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if ($int === false) return null;
        
        if ($min !== null && $int < $min) return null;
        if ($max !== null && $int > $max) return null;
        
        return $int;
    }
    
    public static function validateUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    public static function sanitizeFilename(string $filename): string {
        // Remove path traversal attempts
        $filename = basename($filename);
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        return substr($filename, 0, 100);
    }
    
    public static function validateApiKey(string $key): bool {
        return preg_match('/^[a-zA-Z0-9]{32,64}$/', $key) === 1;
    }
    
    public static function requireFields(array $data, array $required): void {
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => "Required field missing: $field",
                    'code' => 'MISSING_FIELD'
                ]);
                exit;
            }
        }
    }
}
