<?php

/**
 * Dump
 *
 * Dump database from SQL server
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\SQL;

use PHP_Library\System\Examinations\Testing;

/**
 * Dump database from SQL server
 */
class Dump extends Testing
{
    /**
     * Command for dump execution
     */
    private string $command = 'mysqldump';

    /**
     * Destination folder for dumped files
     */
    private string $destination = '';

    /**
     * Override dumped files in destination folder
     */
    private bool $override = false;

    /**
     * Database connection parameters
     *
     * @var array{host: string, username: string, password: string}
     */
    private array $connection = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
    ];

    /**
     * Databases for dumping
     *
     * @var list<string>
     */
    private array $databases = [];

    /**
     * Class constructor method
     */
    public function __construct(array $params)
    {
        if (isset($params['command'])) {
            $this->command = $params['command'];
        }

        if (isset($params['destination'])) {
            $this->destination = $params['destination'];
        }

        if (isset($params['connection']['host'])) {
            $this->connection['host'] = $params['connection']['host'];
        }

        if (isset($params['connection']['username'])) {
            $this->connection['username'] = $params['connection']['username'];
        }

        if (isset($params['connection']['password'])) {
            $this->connection['password'] = $params['connection']['password'];
        }

        if (isset($params['databases'])) {
            $this->databases = $params['databases'];
        } else {
            $this->set_error('Set databases for dumping');
        }
    }

    /**
     * Check if there are databases
     * set for dumping
     */
    private function has_databases(): bool
    {
        return ! empty($this->databases);
    }

    /**
     * Creates folders in destination path
     */
    private function create_folders(string $root = 'dump'): string
    {
        $folder_name_root = $this->destination;
        $folder_name_root .= $root;
        $folder_name_root .= '/';

        if (! is_dir($folder_name_root)) {
            mkdir($folder_name_root);
        }

        $folder_name_root .= date('ym');
        $folder_name_root .= '/';

        if (! is_dir($folder_name_root)) {
            mkdir($folder_name_root);
        }

        $folder_name = $folder_name_root;
        $folder_name .= date('d');
        $folder_name .= '/';

        if (! is_dir($folder_name)) {
            mkdir($folder_name);
        }

        return $folder_name;
    }

    /**
     * Create filename for dumped file
     *
     * Returns a bare path. Quoting is the caller's job and is handled by
     * escapeshellarg() in execute_command().
     */
    private function create_filename(string $folder_name, string $database): string
    {
        $filename = '';

        $filename .= $folder_name;

        if (! $this->override) {
            $filename .= date('ymdHis');
            $filename .= '_-_';
        }

        $filename .= $database;
        $filename .= '.sql';

        return $filename;
    }

    /**
     * Check dumped file
     */
    private function check_file(string $filename, string $database): void
    {
        $this->set_file($filename);

        $size = is_file($filename)
            ? filesize($filename)
            : 0;

        if (empty($size) || $this->is_being_tested()) {
            $this->set_error(
                'Failed to dump '.
                $database.
                ' database'
            );
        } else {
            $this->set_success(
                'Database '.
                $database.
                ' is dumped'
            );
        }
    }

    /**
     * Execute dump command
     *
     * Every interpolated value is passed through escapeshellarg(), so a
     * database name, host or path can no longer break out into the shell.
     * The password travels in MYSQL_PWD rather than on the command line,
     * where it would otherwise be readable by any user via the process list.
     */
    private function execute_command(string $filename, string $database): void
    {
        $command = 'MYSQL_PWD='.escapeshellarg($this->connection['password']).' ';

        $command .= escapeshellarg($this->command);
        $command .= ' ';
        $command .= escapeshellarg($database);
        $command .= ' --user=';
        $command .= escapeshellarg($this->connection['username']);
        $command .= ' --host=';
        $command .= escapeshellarg($this->connection['host']);
        $command .= ' > ';
        $command .= escapeshellarg($filename);

        exec($command);
    }

    /**
     * MySQL dump
     */
    public function mysql(bool $override = false): bool
    {
        $this->is_function_available('exec');

        if ($this->has_databases() && ! $this->has_errors()) {
            $this->override = $override;

            $folder_name = $this->override
                ? $this->destination
                : $this->create_folders('mysqldump');

            foreach ($this->databases as $database) {
                $filename = $this->create_filename($folder_name, $database);

                $this->execute_command($filename, $database);
                $this->check_file($filename, $database);
            }

            if (! $this->has_errors()) {
                return true;
            }
        }

        return false;
    }
}
