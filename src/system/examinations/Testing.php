<?php

/**
 * Testing
 *
 * Use when testing unreachable code
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\System\Examinations;

use PHP_Library\System\Informations\Message;

/**
 * Use when testing unreachable code
 *
 * @internal This public test seam is retained for backwards compatibility.
 */
class Testing extends Message
{
    /**
     * Indicator of testing
     */
    private bool $testing = false;

    /**
     * Turn on testing option
     *
     * @internal
     */
    public function turn_on(): void
    {
        $this->testing = true;
    }

    /**
     * Checks if testing option is turned on
     */
    protected function is_being_tested(): bool
    {
        return $this->testing;
    }

    /**
     * Test function availability
     */
    protected function is_function_available(string $function_name): bool
    {
        if (! function_exists($function_name) || $this->is_being_tested()) {
            $this->set_error(
                $function_name.
                ' function disabled in PHP'
            );

            if ($this->is_being_tested()) {
                $this->pop_error();
            }

            return false;
        } else {
            return true;
        }
    }
}
