<?php

/**
 * Email
 *
 * Email-related operations
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Arrangements;

/**
 * Email-related operations
 */
class Email
{
    /**
     * Show email address
     */
    public static function show(?string $email): string|false
    {
        if (self::validate($email)) {
            $escaped = htmlspecialchars(strtolower($email), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return str_replace(['@', '.'], ['&#64;', '&#46;'], $escaped);
        }

        return false;
    }

    /**
     * Formats email to mailto format
     */
    public static function mailto(
        ?string $email,
        string $link_text = '',
        string $subject = '',
        array|string $attributes = ''
    ): string|false {
        if (self::validate($email)) {
            $email = strtolower($email);

            if (empty($link_text)) {
                $link_text = $email;
            }

            $href = 'mailto:'.$email;

            if ($subject !== '') {
                $href .= '?subject='.rawurlencode($subject);
            }

            return '<a href="'.htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"'.
                self::attributes($attributes).'>'.
                htmlspecialchars($link_text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').
                '</a>';
        }

        return false;
    }

    /**
     * Validates email address
     *
     * @param  list<string>  $invalid_email_clients
     */
    public static function validate(?string $email, array $invalid_email_clients = []): string|false
    {
        if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            foreach ($invalid_email_clients as $item) {
                if (stristr($email, $item)) {
                    return false;
                }
            }

            return $email;
        }

        return false;
    }

    /**
     * Normalize safe HTML attributes for a generated link
     *
     * Event-handler attributes are intentionally discarded. String input is
     * retained for backwards compatibility; new callers should pass an array.
     *
     * @param  array<string, scalar|null>|string  $attributes
     */
    private static function attributes(array|string $attributes): string
    {
        if (is_string($attributes)) {
            preg_match_all(
                '/(?:^|\s)([a-zA-Z_:][a-zA-Z0-9_.:-]*)(?:\s*=\s*(["\'])(.*?)\2)?/',
                trim($attributes),
                $matches,
                PREG_SET_ORDER
            );

            $attributes = [];

            foreach ($matches as $match) {
                $attributes[$match[1]] = $match[3] ?? null;
            }
        }

        $html = '';

        foreach ($attributes as $name => $value) {
            if (! preg_match('/^[a-zA-Z_:][a-zA-Z0-9_.:-]*$/', $name) || preg_match('/^on/i', $name)) {
                continue;
            }

            $html .= ' '.htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            if ($value !== null) {
                $html .= '="'.htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
            }
        }

        return $html;
    }
}
