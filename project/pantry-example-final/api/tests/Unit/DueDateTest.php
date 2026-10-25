<?php

use App\Support\DueDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// DueDate::daysLeft() and DueDate::status() take "today" as an argument. That
// is not an accident: a function that reads the clock itself cannot be tested,
// because its answer changes every day. Here, the test decides what day it is.
//
// Arrange, act, assert here look different from StockTest. These are pure
// functions -- a query with a return value and no object to set up -- so the
// arrange is just the literal inputs, and the act sits inside the assertion:
//
//     assertSame(10, DueDate::daysLeft('2026-09-18', '2026-09-08'))
//               ^ assert   ^ act                    ^ arrange (the inputs)
//
// Three beats, one line. Spread them out when the act changes something
// (StockTest) or when the inputs need a name (the data-provider test below).

final class DueDateTest extends TestCase
{
    public function test_days_left_counts_forward_to_the_date(): void
    {
        // Static method: called on the class with ::, no object needed.
        $this->assertSame(10, DueDate::daysLeft('2026-09-18', '2026-09-08'));   // assert( act( arrange ) )
    }

    public function test_days_left_is_zero_on_the_day(): void
    {
        $this->assertSame(0, DueDate::daysLeft('2026-09-08', '2026-09-08'));
    }

    public function test_days_left_goes_negative_once_the_date_has_passed(): void
    {
        $this->assertSame(-3, DueDate::daysLeft('2026-09-05', '2026-09-08'));
    }

    public function test_days_left_crosses_month_boundaries(): void
    {
        $this->assertSame(4, DueDate::daysLeft('2026-10-02', '2026-09-28'));
    }

    public function test_a_product_with_no_date_has_no_status(): void
    {
        // Two assertions, one idea: "no date" whether it arrives as null or ''.
        // Comparing against the constant, not the string 'no date', so the
        // test cannot drift from the code by a typo.
        $this->assertSame(DueDate::NONE, DueDate::status(null, '2026-09-08'));
        $this->assertSame(DueDate::NONE, DueDate::status('', '2026-09-08'));
    }

    // One test, run once per row below. PHPUnit reports each row separately,
    // so a failure names the row: "with data set 'the day after'".
    //
    // `#[DataProvider('shelfLabels')]` is an attribute (PHP 8 syntax, the
    // `#[...]`). It tells PHPUnit: call shelfLabels(), and run this test once
    // for each row it returns, passing the row's values as the arguments.
    #[DataProvider('shelfLabels')]
    public function test_the_shelf_label_depends_on_how_many_days_are_left(string $bestBefore, string $expected): void
    {
        $today = '2026-09-08';                                              // arrange (the row supplies the rest)

        $this->assertSame($expected, DueDate::status($bestBefore, $today)); // act + assert
    }

    /**
     * A data provider must be public static and return an iterable of rows.
     * Using string keys names each row in the output. Note the rows on either
     * side of the boundary: five days is "due soon", six is "fresh".
     *
     * @return array<string, array{string, string}>
     */
    public static function shelfLabels(): array
    {
        return [
            'last week'          => ['2026-09-01', DueDate::EXPIRED],
            'yesterday'          => ['2026-09-07', DueDate::EXPIRED],
            'today'              => ['2026-09-08', DueDate::DUE_SOON],
            'in five days'       => ['2026-09-13', DueDate::DUE_SOON],
            'in six days'        => ['2026-09-14', DueDate::FRESH],
            'next year'          => ['2027-09-08', DueDate::FRESH],
        ];
    }

    public function test_the_warning_window_can_be_changed(): void
    {
        // `warnDays: 2` is a named argument -- you can skip optional parameters
        // and say which one you mean. Same call twice, different window.
        $this->assertSame(DueDate::FRESH, DueDate::status('2026-09-13', '2026-09-08', warnDays: 2));
        $this->assertSame(DueDate::DUE_SOON, DueDate::status('2026-09-13', '2026-09-08', warnDays: 5));
    }
}
