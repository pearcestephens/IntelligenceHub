<?php

/**
 * Support logger wrapper offering PSR-like facade over core logger.
 *
 * @package App\Support
 */

declare(strict_types=1);

namespace App\Support;

use App\Logger as CoreLogger;

class Logger
{
    public function info(string $message, array $context = []): void
    {
        CoreLogger::info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        CoreLogger::warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        CoreLogger::error($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        CoreLogger::debug($message, $context);
    }
}
