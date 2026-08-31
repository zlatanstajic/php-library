<?php

use PHP_Library\System\Examinations\Testing;

/**
 * Expose the protected is_being_tested() and is_function_available().
 */
function testing_spy(): Testing
{
    return new class extends Testing
    {
        public function tested(): bool
        {
            return $this->is_being_tested();
        }

        public function available(string $fn): bool
        {
            return $this->is_function_available($fn);
        }
    };
}

it('is off until turned on', function () {
    expect(testing_spy()->tested())->toBeFalse();
});

it('turns on', function () {
    $t = testing_spy();
    $t->turn_on();

    expect($t->tested())->toBeTrue();
});

it('reports a real function as available', function () {
    $t = testing_spy();

    expect($t->available('strlen'))->toBeTrue()
        ->and($t->has_errors())->toBeFalse();
});

it('records an error for a missing function', function () {
    $t = testing_spy();

    expect($t->available('definitely_not_a_php_function'))->toBeFalse()
        ->and($t->has_errors())->toBeTrue()
        ->and($t->get_error()[0])->toContain('function disabled in PHP');
});

it('forces the unavailable branch when testing is on, then pops the error', function () {
    $t = testing_spy();
    $t->turn_on();

    // strlen plainly exists - testing mode makes the failure branch reachable.
    expect($t->available('strlen'))->toBeFalse()
        ->and($t->has_errors())->toBeFalse();
});
