<?php

/**
 * Password
 *
 * Works with password related data
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Data;

use Random\Randomizer;

/**
 * Works with password related data
 *
 * Every random draw uses PHP's secure random-number APIs. The previous
 * implementation relied on seeded pseudo-random functions.
 */
class Password
{
    /**
     * Method for openssl_digest in digest method
     */
    private static string $method = 'sha512';

    /**
     * Password letters
     */
    private const string LETTERS = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    /**
     * Password words
     */
    private const string WORDS = 'dog,cat,sheep,sun,sky,red,ball,happy,ice,green,blue,music,movies,radio,green,turbo,mouse,computer,paper,water,fire,storm,chicken,boot,freedom,white,nice,player,small,eyes,path,kid,box,black,flower,ping,pong,smile,coffee,colors,rainbow,plus,king,tv,ring';

    /**
     * Password sizes
     *
     * @var array{minimum: int, optimum: int}
     */
    private const array SIZES = [
        'minimum' => 6,
        'optimum' => 9,
    ];

    /**
     * Encode/decode replaceable characters
     *
     * @var array{search: string, replace: string}
     */
    private const array REPLACEABLES = [
        'search' => '+/=',
        'replace' => '-_,',
    ];

    /**
     * Cryptographically secure byte shuffle
     *
     * Drawing WITHOUT replacement, matching str_shuffle()'s permutation
     * semantics - the characters of the result stay unique.
     */
    private static function secure_shuffle(string $value): string
    {
        return (new Randomizer)->shuffleBytes($value);
    }

    /**
     * Generates new unreadable password
     */
    public static function new_unreadable(int $size_optimum = 0, string $letters = ''): string
    {
        if (empty($size_optimum)) {
            $size_optimum = self::SIZES['optimum'];
        }

        if (empty($letters)) {
            $letters = self::LETTERS;
        }

        return substr(self::secure_shuffle($letters), 0, $size_optimum);
    }

    /**
     * Generates new readable password
     */
    public static function new_readable(int $size_optimum = 0, string $words = ''): string
    {
        if (empty($size_optimum)) {
            $size_optimum = self::SIZES['optimum'];
        }

        if (empty($words)) {
            $words = self::WORDS;
        }

        $word_list = explode(',', $words);
        $new_password = '';
        $new_password_length = 0;

        while ($new_password_length < $size_optimum) {
            $new_password .= $word_list[random_int(0, count($word_list) - 1)];

            $new_password_length = strlen($new_password);
        }

        $number = random_int(1000, 9999);

        if ($size_optimum > 2) {
            return substr(
                $new_password,
                0,
                $size_optimum - strlen(strval($number))
            ).$number;
        } else {
            return substr(
                $new_password,
                0,
                $size_optimum
            );
        }
    }

    /**
     * Calculates password strength
     *
     * @deprecated This entropy heuristic does not estimate real-world password
     *             guessing effort. Use meets_policy() or a dedicated estimator.
     *
     * @return array{status: bool, strength: int|float}
     */
    public static function strength(string $string, int $minimum_strength_percent = 60): array
    {
        $strength = 0;
        $h = 0;
        $size = strlen($string);

        if ($size >= self::SIZES['minimum']) {
            foreach (count_chars($string, 1) as $v) {
                $p = $v / $size;
                $h -= $p * log($p) / log(2);
            }

            $strength = ($h / 4) * 100;

            if ($strength > 100) {
                $strength = 100;
            }
        }

        return [
            'status' => $strength > $minimum_strength_percent,
            'strength' => $strength,
        ];
    }

    /**
     * Check a password against a transparent baseline policy
     */
    public static function meets_policy(string $password, int $minimum_length = 12): bool
    {
        return mb_strlen($password, 'UTF-8') >= $minimum_length &&
            preg_match('/\p{Ll}/u', $password) === 1 &&
            preg_match('/\p{Lu}/u', $password) === 1 &&
            preg_match('/\p{N}/u', $password) === 1 &&
            preg_match('/[^\p{L}\p{N}]/u', $password) === 1;
    }

    /**
     * Hash a password using PHP's current recommended algorithm
     *
     * @param  array<string, int>  $options
     */
    public static function hash(string $plain_text, array $options = []): string
    {
        return password_hash($plain_text, PASSWORD_DEFAULT, $options);
    }

    /**
     * Verify a password against a password_hash() result
     */
    public static function verify(string $plain_text, string $hash): bool
    {
        return password_verify($plain_text, $hash);
    }

    /**
     * Base 64 encode
     *
     * @deprecated This is reversible encoding, not password handling. Use
     *             base64_encode() in a correctly named encoding component.
     */
    public static function encode(string $plain_text): string
    {
        return strtr(
            base64_encode($plain_text),
            self::REPLACEABLES['search'],
            self::REPLACEABLES['replace']
        );
    }

    /**
     * Base 64 decode
     *
     * @deprecated This is reversible encoding, not password handling. Use
     *             base64_decode() in a correctly named encoding component.
     */
    public static function decode(string $plain_text): string|false
    {
        $replaced_substring = strtr(
            $plain_text,
            self::REPLACEABLES['replace'],
            self::REPLACEABLES['search']
        );

        return base64_decode($replaced_substring);
    }

    /**
     * Computes a digest
     *
     * @deprecated Digests are not password hashes. Use hash() for passwords or
     *             PHP's hash() function for a non-password checksum.
     */
    public static function digest(string $plain_text): string|false
    {
        if (
            ! empty($plain_text) &&
            ! empty(self::$method) &&
            in_array(self::$method, hash_algos(), true)
        ) {
            return \hash(self::$method, $plain_text);
        }

        return false;
    }

    /**
     * Set method attribute
     *
     * @deprecated Digests are not password hashes. Pass an algorithm directly
     *             to PHP's hash() function for a non-password checksum.
     */
    public static function set_method(string $value): void
    {
        self::$method = $value;
    }
}
