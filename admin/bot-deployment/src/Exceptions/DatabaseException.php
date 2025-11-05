<?php
/**
 * Database Exception
 *
 * Custom exception for database-related errors
 *
 * @package BotDeployment\Exceptions
 */

namespace BotDeployment\Exceptions;

use Exception;

class DatabaseException extends Exception
{
    /**
     * SQL query that caused the exception
     * @var string|null
     */
    private $query;

    /**
     * Query parameters
     * @var array
     */
    private $params;

    /**
     * Set query details
     * @param string $query
     * @param array $params
     * @return self
     */
    public function setQueryDetails(string $query, array $params = []): self
    {
        $this->query = $query;
        $this->params = $params;
        return $this;
    }

    /**
     * Get query
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * Get parameters
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get formatted error message
     * @return string
     */
    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();

        if ($this->query) {
            $message .= "\nQuery: " . $this->query;
        }

        if (!empty($this->params)) {
            $message .= "\nParams: " . json_encode($this->params);
        }

        return $message;
    }
}
