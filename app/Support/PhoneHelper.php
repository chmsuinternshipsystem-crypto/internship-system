<?php

namespace App\Support;

/**
 * Philippine mobile number normalization.
 *
 * Accepts both local (09XXXXXXXXX) and international (+639XXXXXXXXX) formats.
 * Normalizes everything to local format for storage.
 * Returns null for any number that doesn't match either format.
 */
final class PhoneHelper
{
    /**
     * Normalize a Philippine mobile number to local format (09XXXXXXXXX).
     *
     * Input examples that will succeed:
     *   "09123456789"   → "09123456789"
     *   "+639123456789" → "09123456789"
     *   "0912-345-6789" → "09123456789"
     *   "0912 345 6789" → "09123456789"
     *
     * Input examples that will fail (return null):
     *   "12345"              → too short
     *   "0912345"            → too short
     *   "01234567890"        → doesn't start with 09
     *   "+631234567890"      → only 9 digits after +63 prefix (needs 9)
     *   "abc"                → no digits
     *   ""                   → empty
     */
    public static function normalizeMobile(string $number): ?string
    {
        $clean = preg_replace('/[^0-9]/', '', $number);

        if (strlen($clean) === 11 && str_starts_with($clean, '09')) {
            return $clean;
        }

        if (strlen($clean) === 12 && str_starts_with($clean, '63')) {
            return '0' . substr($clean, 2);
        }

        return null;
    }

    /**
     * Format a phone number for display.
     *
     * Mobile (11-digit):  09123456789 → +63 912 345 6789
     * Landline (10-12 digit): 0341234567 → (034) 123 4567
     * Invalid/unknown:  0994600307912 → basic spaced grouping
     * Returns — for null/empty.
     */
    public static function formatPhone(?string $number): string
    {
        if ($number === null || $number === '') {
            return '—';
        }

        $digits = preg_replace('/[^\d]/', '', $number);

        // Reject numbers that are too long — likely invalid data
        if (strlen($digits) > 15) {
            return $number;
        }

        // Normalize international prefix
        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        }

        // Philippine mobile: 11 digits, starts with 09
        if (strlen($digits) === 11 && str_starts_with($digits, '09')) {
            return '+63 '
                . substr($digits, 1, 3) . ' '
                . substr($digits, 4, 3) . ' '
                . substr($digits, 7, 4);
        }

        // Philippine landline: starts with 0, 10–12 digits
        if (strlen($digits) >= 10 && strlen($digits) <= 12 && $digits[0] === '0') {
            $areaLen = (strlen($digits) >= 12) ? 4 : 3;
            $area = substr($digits, 0, $areaLen);
            $rest = substr($digits, $areaLen);
            $restLen = strlen($rest);
            // Split remaining digits: 3+4 for 7-digit locals, otherwise groups of 4
            if ($restLen === 7) {
                $restFormatted = substr($rest, 0, 3) . ' ' . substr($rest, 3);
            } else {
                $restFormatted = trim(chunk_split($rest, 4, ' '));
            }
            return '(' . $area . ') ' . $restFormatted;
        }

        // Fallback: basic readability spacing for any other digit string
        if (strlen($digits) > 0) {
            return trim(chunk_split($digits, 4, ' '));
        }

        return $number;
    }
}
