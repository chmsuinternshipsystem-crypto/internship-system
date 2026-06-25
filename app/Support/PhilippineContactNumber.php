<?php

namespace App\Support;

/**
 * Minimal helper to normalize Philippine contact numbers.
 *
 * Used by company FormRequests to validate contact_phone formatting.
 */
final class PhilippineContactNumber
{
    /**
     * Normalize a raw phone input into a digits-only string starting with `0`.
     *
     * Examples:
     * - `+639171234567` => `09171234567`
     * - `+63 34 1234567` => `0341234567`
     *
     * @return string|null Normalized digits string or null if it can't be normalized.
     */
    public static function normalize(string $value): ?string
    {
        $v = trim($value);
        if ($v === '') {
            return null;
        }

        // Keep digits and plus sign; remove spaces/dashes/parentheses/etc.
        $v = preg_replace('/[^\d+]/', '', $v);
        if ($v === null) {
            return null;
        }

        if ($v === '') {
            return null;
        }

        // Handle +63 prefix.
        if (str_starts_with($v, '+63')) {
            $rest = substr($v, 3);
            if ($rest === '') {
                return null;
            }

            return '0'.$rest;
        }

        // Handle plain 63 prefix (common when users omit the `+`).
        if (str_starts_with($v, '63') && ! str_starts_with($v, '0')) {
            $rest = substr($v, 2);
            if ($rest === '') {
                return null;
            }

            return '0'.$rest;
        }

        // If it starts with '+', we don't know the country code.
        if (str_starts_with($v, '+')) {
            return null;
        }

        // At this point we expect digits-only input.
        if (! preg_match('/^\d+$/', $v)) {
            return null;
        }

        return $v;
    }
}
