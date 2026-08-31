<?php

/**
 * Random
 *
 * Random-related data
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Data;

use Random\Randomizer;

/**
 * Random-related data
 *
 * All draws use PHP's secure random-number API, which is free of the modulo
 * bias that the previous rand()%strlen() approach introduced.
 */
class Random
{
    /**
     * Numeric caracters
     */
    private const string NUMBERS = '0123456789';

    /**
     * Alphanumeric caracters
     */
    private const string ALPHANUMERIC = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Consonant characters
     *
     * @var list<string>
     */
    private const array CONSONANT = [
        'b',
        'c',
        'd',
        'f',
        'g',
        'h',
        'j',
        'k',
        'l',
        'm',
        'n',
        'p',
        'r',
        's',
        't',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];

    /**
     * Vocal characters
     *
     * @var list<string>
     */
    private const array VOCAL = [
        'a',
        'e',
        'i',
        'o',
        'u',
    ];

    /**
     * Generates random sequence for given length and sequence type
     *
     * Returns FALSE for an unknown type.
     */
    public static function generate(int $length = 4, string $type = 'INT'): string|false|null
    {
        return match ($type) {
            // The INT branch seeds with NULL rather than '' so that a
            // non-positive length keeps returning NULL, exactly as before.
            'INT' => self::sequence(self::NUMBERS, $length, null),
            'STRING' => self::sequence(self::ALPHANUMERIC, $length, ''),
            'STRING_ADVANCED' => self::readable_sequence($length),
            default => false,
        };
    }

    /**
     * Build a random sequence by drawing characters out of a pool
     */
    private static function sequence(string $pool, int $length, ?string $seed): ?string
    {
        if ($length <= 0) {
            return $seed;
        }

        return $seed.(new Randomizer)->getBytesFromString($pool, $length);
    }

    /**
     * Build a pronounceable consonant/vocal sequence
     */
    private static function readable_sequence(int $length): string
    {
        $max = $length / 2;
        $randomizer = new Randomizer;

        $readable_random_string = '';

        for ($i = 1; $i <= $max; $i++) {
            $consonant = self::CONSONANT[$randomizer->pickArrayKeys(self::CONSONANT, 1)[0]];

            $readable_random_string .= $i === 1
                ? strtoupper($consonant)
                : $consonant;

            $readable_random_string .= self::VOCAL[$randomizer->pickArrayKeys(self::VOCAL, 1)[0]];
        }

        return $readable_random_string;
    }

    /**
     * Returns random element of array for given dose
     */
    public static function element(array $list, string $dose = ''): mixed
    {
        $list_size = count($list);

        if ($list_size === 0) {
            return null;
        }

        if (
            ($dose === 'DAY' && $list_size < 7) ||
            ($dose === 'MONTH' && $list_size < 31)
        ) {
            $dose = '';
        }

        $index = match ($dose) {
            'DAY' => intval(date('N') - 1),
            'MONTH' => intval(date('j') - 1),
            default => (new Randomizer)->pickArrayKeys($list, 1)[0],
        };

        return $list[$index];
    }

    /**
     * Break caching for URLs
     */
    public static function break_caching(): string
    {
        return '?break_caching='.(new Randomizer)->getInt(0, PHP_INT_MAX);
    }
}
