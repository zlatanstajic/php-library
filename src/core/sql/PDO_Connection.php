<?php

/**
 * PDO_Connection
 *
 * Make PDO connection to a database
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\SQL;

use PDO;
use Pdo\Mysql;
use PDOException;
use PHP_Library\System\Associations\Connection;

/**
 * Make PDO connection to a database
 */
class PDO_Connection extends Connection
{
    /**
     * Constructor
     */
    public function __construct(string $host = '', string $user = '', string $pass = '', string $name = '')
    {
        $this->set_parameters($host, $user, $pass, $name);
        $this->open_connection();
    }

    /**
     * Create PDO connection string
     */
    private function connection_string(): string
    {
        $string = '';

        $string .= 'mysql:';
        $string .= 'host=';
        $string .= $this->parameters['host'];
        $string .= ';';
        $string .= 'dbname=';
        $string .= $this->parameters['name'];
        $string .= ';';

        return $string;
    }

    /**
     * Open PDO connection
     */
    private function open_connection(): void
    {
        try {
            $this->connection = new PDO(
                $this->connection_string(),
                $this->parameters['user'],
                $this->parameters['pass'],
                [
                    // PDO::MYSQL_ATTR_INIT_COMMAND is deprecated as of PHP 8.5
                    // in favour of the driver-specific constant.
                    Mysql::ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                ]
            );
        } catch (PDOException $e) {
            $this->set_error($e->getMessage());
        }
    }
}
