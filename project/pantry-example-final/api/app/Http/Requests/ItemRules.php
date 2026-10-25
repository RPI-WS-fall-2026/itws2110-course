<?php

namespace App\Http\Requests;

/**
 * The field rules from the midterm design (PROJECT.pptx, section A), shared by the create
 * and update requests. Kept apart so the unit suite can check them without a database.
 */
final class ItemRules
{
    public const UNITS = ['items', 'g', 'kg', 'ml', 'L'];
    public const LOCATIONS = ['fridge', 'freezer', 'cabinet'];

    /** @return array<string, array<int, string>> */
    public static function fields(): array
    {
        // bail: stop at the first broken rule for a field, so each field gets one message.
        return [
            'name' => ['bail', 'required', 'string', 'max:80'],
            'amount' => ['bail', 'required', 'numeric', 'min:0', 'max:9999', 'decimal:0,2'],
            'unit' => ['bail', 'required', 'in:'.implode(',', self::UNITS)],
            'location' => ['bail', 'required', 'in:'.implode(',', self::LOCATIONS)],
            'best_before' => ['bail', 'nullable', 'date_format:Y-m-d'],
            'notes' => ['bail', 'nullable', 'string', 'max:500'],
        ];
    }

    /** The messages, word for word as the midterm design wrote them. */
    public static function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.max' => 'The name may not be longer than 80 characters.',
            'name.unique' => 'You already have an item called :input. Change its amount instead.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount cannot be below zero.',
            'amount.max' => 'The amount may not be more than 9999.',
            'amount.decimal' => 'The amount may have at most two decimal places.',
            'unit.required' => 'The unit must be one of: items, g, kg, ml, L.',
            'unit.in' => 'The unit must be one of: items, g, kg, ml, L.',
            'location.required' => 'Choose where it is: fridge, freezer or cabinet.',
            'location.in' => 'Choose where it is: fridge, freezer or cabinet.',
            'best_before.date_format' => 'The best-before date must be a date like 2026-10-30.',
            'notes.max' => 'The notes may not be longer than 500 characters.',
        ];
    }
}
