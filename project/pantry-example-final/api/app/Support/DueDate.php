<?php

namespace App\Support;

/**
 * Best-before arithmetic. Dates come in as ISO strings ("2026-09-30") because
 * that is how they arrive from a form field and how they sit in the database.
 *
 * Both functions take "today" as an argument instead of asking the clock.
 * That is what makes them testable: a test can say what day it is.
 *
 * Everything here is `static`: you call DueDate::status(...) on the class
 * itself, never `new DueDate()`. There is no state to hold, so no object.
 */
final class DueDate
{
    // Class constants. Referred to as DueDate::EXPIRED from outside and
    // self::EXPIRED from inside. One place to spell each word, so a typo in
    // the page or a test is a compile error instead of a silent mismatch.
    public const EXPIRED = 'expired';
    public const DUE_SOON = 'due soon';
    public const FRESH = 'fresh';
    public const NONE = 'no date';

    /** Whole days from $today until $bestBefore. Negative once it has passed. */
    public static function daysLeft(string $bestBefore, string $today): int
    {
        // DateTimeImmutable parses the string. "Immutable" means methods on it
        // return a new object instead of changing this one -- safer to reason about.
        $due = new \DateTimeImmutable($bestBefore);
        $now = new \DateTimeImmutable($today);

        // diff() gives a DateInterval: ->days is always positive, ->invert is
        // 1 when $due is before $now. We turn those two facts into a signed int.
        $diff = $now->diff($due);
        return $diff->invert ? -$diff->days : $diff->days;   //  cond ? ifTrue : ifFalse
    }

    /**
     * One word for the shelf label. Products without a date are neither fresh
     * nor expired -- they are undated, and the page should say so.
     *
     * `?string` means "a string, or null". `int $warnDays = 5` is an optional
     * parameter: callers can leave it out, or name it -- status($d, $t, warnDays: 2).
     */
    public static function status(?string $bestBefore, string $today, int $warnDays = 5): string
    {
        // `===` is strict equality: same value AND same type. Always use it in
        // PHP; `==` has surprising rules ("0" == "" was true for years).
        if ($bestBefore === null || $bestBefore === '') {
            return self::NONE;
        }
        $days = self::daysLeft($bestBefore, $today);
        if ($days < 0) {
            return self::EXPIRED;
        }
        if ($days <= $warnDays) {
            return self::DUE_SOON;
        }
        return self::FRESH;
    }
}
