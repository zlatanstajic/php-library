<?php

use PHP_Library\Core\SQL\Dump;

beforeEach(function () {
    $this->dir = temp_dir('dump');
});

afterEach(function () {
    remove_dir($this->dir);
});

it('records an error when no databases are configured', function () {
    $dump = new Dump(['destination' => $this->dir]);

    expect($dump->has_errors())->toBeTrue()
        ->and($dump->get_error())->toContain('Set databases for dumping');
});

it('refuses to run without databases', function () {
    expect(new Dump(['destination' => $this->dir])->mysql())->toBeFalse();
});

it('does not execute a shell command injected through the database name', function () {
    $probe = $this->dir.'injected';

    $dump = new Dump([
        'command' => 'true',
        'destination' => $this->dir,
        'databases' => ['safe; touch '.$probe.' #'],
    ]);

    $dump->mysql(true);

    expect(file_exists($probe))->toBeFalse();
});

it('does not execute a shell command injected through the host', function () {
    $probe = $this->dir.'injected_host';

    $dump = new Dump([
        'command' => 'true',
        'destination' => $this->dir,
        'connection' => ['host' => 'localhost; touch '.$probe.' #'],
        'databases' => ['db'],
    ]);

    $dump->mysql(true);

    expect(file_exists($probe))->toBeFalse();
});

it('does not leak the password onto the command line', function () {
    // The password travels in MYSQL_PWD; argv would be world-readable via ps.
    $dump = new Dump([
        'command' => 'true',
        'destination' => $this->dir,
        'connection' => ['password' => 'sup3rs3cret'],
        'databases' => ['db'],
    ]);

    $dump->mysql(true);

    $recorded = implode(' ', $dump->get_file());

    expect($recorded)->not->toContain('sup3rs3cret');
});

it('reports a failed dump rather than throwing when the file never appears', function () {
    $dump = new Dump([
        'command' => 'true',
        'destination' => $this->dir,
        'databases' => ['nodb'],
    ]);

    expect($dump->mysql(true))->toBeFalse()
        ->and($dump->has_errors())->toBeTrue()
        ->and(implode(' ', $dump->get_error()))->toContain('Failed to dump');
});

it('does not warn when the dump file is missing', function () {
    // filesize() on a missing path warned before the is_file() guard.
    $dump = new Dump([
        'command' => 'true',
        'destination' => $this->dir,
        'databases' => ['nodb'],
    ]);

    expect(fn () => $dump->mysql(true))->not->toThrow(Throwable::class);
});

it('drives the unavailable-exec branch through testing mode', function () {
    $dump = new Dump(['destination' => $this->dir, 'databases' => ['db']]);
    $dump->turn_on();

    expect($dump->mysql(true))->toBeFalse();
});

it('dumps a real database', function () {
    $dump = new Dump([
        'destination' => $this->dir,
        'connection' => [
            'host' => getenv('PHPLIB_MYSQL_HOST') ?: 'localhost',
            'username' => getenv('PHPLIB_MYSQL_USER') ?: 'root',
            'password' => getenv('PHPLIB_MYSQL_PASS') ?: '',
        ],
        'databases' => [getenv('PHPLIB_MYSQL_NAME') ?: 'mysql'],
    ]);

    expect($dump->mysql(true))->toBeTrue();
})->skip(! getenv('PHPLIB_MYSQL'), 'set PHPLIB_MYSQL=1 to run live MySQL tests');
