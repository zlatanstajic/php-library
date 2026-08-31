<?php

use PHP_Library\Core\Numericals\Math;

beforeEach(fn () => Math::set_iterator(0));

it('calculates a percentage', function () {
    // PHP's "/" yields an int when the operands divide evenly, a float otherwise.
    expect(Math::percentage(5, 10))->toBe(['value' => 50, 'sign' => '50%']);
});

it('treats an empty operand as zero percent', function () {
    expect(Math::percentage(0, 10)['value'])->toBe(0)
        ->and(Math::percentage(5, 0)['value'])->toBe(0);
});

it('keeps fractional percentages', function () {
    expect(Math::percentage(3, 7)['value'])->toBeGreaterThan(42.8)
        ->and(Math::percentage(3, 7)['value'])->toBeLessThan(42.9);
});

it('alternates between the two values on successive calls', function () {
    $first = Math::even_or_odd('a', 'b');
    $second = Math::even_or_odd('a', 'b');

    expect($first)->not->toBe($second);
});

it('returns the first value when the flag is forced', function () {
    expect(Math::even_or_odd('a', 'b', true))->toBe('a')
        ->and(Math::even_or_odd('a', 'b', false))->toBe('b');
});

it('selects by parity without shared state', function () {
    expect(Math::by_parity(0, 'even', 'odd'))->toBe('even')
        ->and(Math::by_parity(3, 'even', 'odd'))->toBe('odd');
});

it('increments the iterator', function () {
    expect(Math::iterate())->toBe(1)
        ->and(Math::iterate())->toBe(2)
        ->and(Math::get_iterator())->toBe(2);
});

it('resets the iterator on demand', function () {
    Math::iterate();
    Math::iterate();

    expect(Math::iterate(true))->toBe(1);
});

it('seeds the iterator from a given value', function () {
    Math::set_iterator(41);

    expect(Math::iterate())->toBe(42);
});
