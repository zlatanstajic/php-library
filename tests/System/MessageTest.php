<?php

use PHP_Library\System\Informations\Message;

/**
 * Message keeps its setters protected, so drive them through a local subclass
 * rather than reaching in with reflection.
 */
function message_spy(): Message
{
    return new class extends Message
    {
        public function success(string $t): void
        {
            $this->set_success($t);
        }

        public function error(string $t): void
        {
            $this->set_error($t);
        }

        public function file(string $t): void
        {
            $this->set_file($t);
        }

        public function pop(): void
        {
            $this->pop_error();
        }
    };
}

it('starts with three empty buckets', function () {
    $m = new Message;

    expect($m->get_message())->toBe(['success' => [], 'error' => [], 'file' => []])
        ->and($m->get_success())->toBe([])
        ->and($m->get_error())->toBe([])
        ->and($m->get_file())->toBe([])
        ->and($m->has_errors())->toBeFalse();
});

it('accumulates messages per bucket without throwing', function () {
    $m = message_spy();

    $m->success('ok');
    $m->error('bad');
    $m->file('/tmp/x');

    expect($m->get_success())->toBe(['ok'])
        ->and($m->get_error())->toBe(['bad'])
        ->and($m->get_file())->toBe(['/tmp/x'])
        ->and($m->has_errors())->toBeTrue();
});

it('keeps every error rather than replacing the last', function () {
    $m = message_spy();

    $m->error('one');
    $m->error('two');

    expect($m->get_error())->toBe(['one', 'two']);
});

it('pops the most recent error', function () {
    $m = message_spy();

    $m->error('one');
    $m->error('two');
    $m->pop();

    expect($m->get_error())->toBe(['one'])
        ->and($m->has_errors())->toBeTrue();
});

it('reports no errors once the last one is popped', function () {
    $m = message_spy();

    $m->error('only');
    $m->pop();

    expect($m->has_errors())->toBeFalse();
});
