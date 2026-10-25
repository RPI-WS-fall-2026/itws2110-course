<?php

// The field rules from the midterm design, with the exact messages. Uses Laravel's
// validator, so it extends Laravel's TestCase -- but it never touches the database.
// (The one rule that needs the database, "no two items with the same name", is in the
// api suite: web/tests/api/items.spec.js.)

use App\Http\Requests\ItemRules;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ItemRulesTest extends TestCase
{
    private const GOOD = ['name' => 'Milk', 'amount' => '1', 'unit' => 'L', 'location' => 'fridge', 'best_before' => '2026-10-30', 'notes' => null];

    private function errors(array $input): array
    {
        return Validator::make($input, ItemRules::fields(), ItemRules::messages())->errors()->toArray();
    }

    public function test_a_complete_item_passes(): void
    {
        $this->assertSame([], $this->errors(self::GOOD));
    }

    public static function badInputs(): array
    {
        return [
            'no name' => ['name', null, 'The name is required.'],
            'a long name' => ['name', str_repeat('x', 81), 'The name may not be longer than 80 characters.'],
            'no amount' => ['amount', null, 'The amount is required.'],
            'a word for an amount' => ['amount', 'two', 'The amount must be a number.'],
            'a negative amount' => ['amount', '-1', 'The amount cannot be below zero.'],
            'too much' => ['amount', '10000', 'The amount may not be more than 9999.'],
            'three decimals' => ['amount', '1.234', 'The amount may have at most two decimal places.'],
            'an unknown unit' => ['unit', 'cups', 'The unit must be one of: items, g, kg, ml, L.'],
            'an unknown place' => ['location', 'sink', 'Choose where it is: fridge, freezer or cabinet.'],
            'a US-style date' => ['best_before', '10/30/2026', 'The best-before date must be a date like 2026-10-30.'],
            'a date that does not exist' => ['best_before', '2026-02-30', 'The best-before date must be a date like 2026-10-30.'],
            'long notes' => ['notes', str_repeat('x', 501), 'The notes may not be longer than 500 characters.'],
        ];
    }

    #[DataProvider('badInputs')]
    public function test_a_broken_rule_gives_its_message(string $field, ?string $value, string $message): void
    {
        $this->assertSame([$field => [$message]], $this->errors([...self::GOOD, $field => $value]));
    }

    public function test_zero_is_a_valid_amount(): void
    {
        $this->assertSame([], $this->errors([...self::GOOD, 'amount' => '0']));
    }

    public function test_no_best_before_date_is_allowed(): void
    {
        $this->assertSame([], $this->errors([...self::GOOD, 'best_before' => null]));
    }
}
