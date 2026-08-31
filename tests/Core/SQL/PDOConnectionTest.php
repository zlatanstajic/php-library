<?php

/**
 * An unreachable target that fails instantly.
 *
 * A bogus IP such as 256.256.256.256 is treated as a hostname and costs a
 * ~20s DNS timeout per test; loopback on a closed port is refused immediately
 * and stays deterministic whether or not MySQL runs on this machine. The value
 * is appended into the DSN, which is why the port rides along with the host.
 */
const UNREACHABLE = '127.0.0.1;port=1';

use PHP_Library\Core\SQL\PDO_Connection;

it('records the driver error instead of throwing when the host is unreachable', function () {
    $connection = new PDO_Connection(UNREACHABLE, 'nobody', 'nothing', 'nodb');

    expect($connection->has_errors())->toBeTrue()
        ->and($connection->get_error())->not->toBeEmpty();
});

it('never throws out of the constructor', function () {
    expect(fn () => new PDO_Connection(UNREACHABLE, 'x', 'y', 'z'))
        ->not->toThrow(Throwable::class);
});

it('returns a null connection and adds an error when the connection failed', function () {
    $connection = new PDO_Connection(UNREACHABLE, 'x', 'y', 'z');
    $before = count($connection->get_error());

    expect($connection->get_connection())->toBeNull()
        ->and(count($connection->get_error()))->toBe($before + 1);
});

it('connects to a real database', function () {
    $connection = new PDO_Connection(
        getenv('PHPLIB_MYSQL_HOST') ?: 'localhost',
        getenv('PHPLIB_MYSQL_USER') ?: 'root',
        getenv('PHPLIB_MYSQL_PASS') ?: '',
        getenv('PHPLIB_MYSQL_NAME') ?: ''
    );

    expect($connection->has_errors())->toBeFalse()
        ->and($connection->get_connection())->toBeInstanceOf(PDO::class);
})->skip(! getenv('PHPLIB_MYSQL'), 'set PHPLIB_MYSQL=1 to run live MySQL tests');
