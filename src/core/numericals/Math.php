<?php

/**
 * Math
 *
 * Mathematical operations and calculations
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Numericals;

/**
 * Mathematical operations and calculations
 */
class Math
{
    /**
     * Iterated value
     */
    private static int $iterator = 0;

    /**
     * Even or odd boolean value
     */
    private static bool $bool = true;

    /**
     * Calculate percentage between two numbers
     *
     * @param  int|float|numeric-string  $smaller_number
     * @param  int|float|numeric-string  $larger_number
     * @return array{value: int|float, sign: string}
     */
    public static function percentage(int|float|string $smaller_number, int|float|string $larger_number): array
    {
        if (empty($smaller_number) || empty($larger_number)) {
            $percentage = 0;
        } else {
            $percentage = (100 * $smaller_number) / $larger_number;
        }

        return [
            'value' => $percentage,
            'sign' => $percentage.'%',
        ];
    }

    /**
     * Even or odd value return
     *
     * Alternates on every call unless a value is forced through $bool.
     *
     * @deprecated Use by_parity() with an explicit index instead of shared state.
     */
    public static function even_or_odd(mixed $value_1, mixed $value_2, ?bool $bool = null): mixed
    {
        if ($bool === null) {
            $bool = self::$bool;
        }

        self::$bool = ! self::$bool;

        return $bool ? $value_1 : $value_2;
    }

    /**
     * Select a value using an explicit, stateless index
     */
    public static function by_parity(int $index, mixed $even, mixed $odd): mixed
    {
        return $index % 2 === 0 ? $even : $odd;
    }

    /**
     * Iterates attribute
     *
     * @deprecated Keep counters in local application state.
     */
    public static function iterate(bool $to_reset = false): int
    {
        if ($to_reset) {
            self::set_iterator();
        }

        return ++self::$iterator;
    }

    /**
     * Set iterator attribute
     *
     * @deprecated Keep counters in local application state.
     */
    public static function set_iterator(int $value = 0): void
    {
        self::$iterator = $value;
    }

    /**
     * Get iterator attribute
     *
     * @deprecated Keep counters in local application state.
     */
    public static function get_iterator(): int
    {
        return self::$iterator;
    }
}
