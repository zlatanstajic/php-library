<?php

use PHP_Library\System\Associations\Connection;

function connection_spy(): Connection
{
    return new class extends Connection
    {
        public function params(string $h, string $u, string $p, string $n): void
        {
            $this->set_parameters($h, $u, $p, $n);
        }

        public function raw(): array
        {
            return $this->parameters;
        }
    };
}

it('starts with an unopened connection and records an error when asked for it', function () {
    $c = new Connection;

    expect($c->get_connection())->toBeNull()
        ->and($c->has_errors())->toBeTrue()
        ->and($c->get_error())->toContain('Connection is not opened!');
});

it('defaults a blank host to localhost and a blank user to root', function () {
    $c = connection_spy();
    $c->params('', '', 'secret', 'mydb');

    expect($c->raw())->toBe([
        'host' => 'localhost',
        'user' => 'root',
        'pass' => 'secret',
        'name' => 'mydb',
    ]);
});

it('keeps explicit parameters', function () {
    $c = connection_spy();
    $c->params('db.internal', 'app', 'pw', 'shop');

    expect($c->raw()['host'])->toBe('db.internal')
        ->and($c->raw()['user'])->toBe('app');
});

it('never throws when the connection is absent', function () {
    expect(fn () => (new Connection)->get_connection())->not->toThrow(Throwable::class);
});
