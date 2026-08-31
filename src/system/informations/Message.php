<?php

/**
 * Message
 *
 * Use when working with errors, warnings and informations
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\System\Informations;

/**
 * Use when working with errors, warnings and informations
 */
class Message
{
    /**
     * Dump message
     *
     * @var array{success: array, error: array, file: array}
     */
    private array $message = [
        'success' => [],
        'error' => [],
        'file' => [],
    ];

    /**
     * Get all message
     */
    public function get_message(): array
    {
        return $this->message;
    }

    /**
     * Get success message
     */
    public function get_success(): array
    {
        return $this->message['success'];
    }

    /**
     * Get error message
     */
    public function get_error(): array
    {
        return $this->message['error'];
    }

    /**
     * Get file message
     */
    public function get_file(): array
    {
        return $this->message['file'];
    }

    /**
     * Set success message
     */
    protected function set_success(string $text): void
    {
        $this->message['success'][] = $text;
    }

    /**
     * Set error message
     */
    protected function set_error(string $text): void
    {
        $this->message['error'][] = $text;
    }

    /**
     * Set file message
     */
    protected function set_file(string $text): void
    {
        $this->message['file'][] = $text;
    }

    /**
     * Remove last error
     */
    protected function pop_error(): void
    {
        array_pop($this->message['error']);
    }

    /**
     * Check if attribute has errors
     */
    public function has_errors(): bool
    {
        return ! empty($this->message['error']);
    }
}
