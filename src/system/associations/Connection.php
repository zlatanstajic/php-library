<?php

/**
 * Connection
 *
 * Make connection to a database
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\System\Associations;

use PHP_Library\System\Informations\Message;

/**
 * Make connection to a database
 */
class Connection extends Message
{
    /**
     * Database connection
     */
    protected ?object $connection = null;

    /**
     * Database connection parameters
     *
     * @var array{host: string, user: string, pass: string, name: string}
     */
    protected array $parameters = [
        'host' => '',
        'user' => '',
        'pass' => '',
        'name' => '',
    ];

    /**
     * Set parameters attribute
     */
    protected function set_parameters(string $host, string $user, string $pass, string $name): void
    {
        if (empty($host)) {
            $host = 'localhost';
        }

        if (empty($user)) {
            $user = 'root';
        }

        $this->parameters = [
            'host' => $host,
            'user' => $user,
            'pass' => $pass,
            'name' => $name,
        ];
    }

    /**
     * Get connection to database
     */
    public function get_connection(): ?object
    {
        if (empty($this->connection)) {
            $this->set_error('Connection is not opened!');
        }

        return $this->connection;
    }
}
