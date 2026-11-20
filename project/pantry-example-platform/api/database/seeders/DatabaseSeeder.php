<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The starting data: the midterm's fake data (web/src/fake-server.js), moved into MySQL
 * (HW7 taught seeders). Dates are counted from today, so "due soon" stays due soon.
 * The passwords are test values for a local copy, not real ones.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $person = fn (string $name, string $email, string $role) => User::forceCreate([
            'name' => $name, 'email' => $email, 'password' => 'password', 'role' => $role,
        ]);   // forceCreate: role is not fillable, on purpose
        $alex = $person('Alex Rivera', 'alex@example.edu', 'user');
        $priya = $person('Priya Shah', 'priya@example.edu', 'user');
        $ra = $person('Jordan Lee', 'ra@example.edu', 'admin');

        $item = function (?User $owner, string $name, float $amount, string $unit, string $location, ?int $inDays, ?string $notes, int $addedDaysAgo) {
            $row = new Item(compact('name', 'amount', 'unit', 'location', 'notes'));
            $row->best_before = $inDays === null ? null : now()->addDays($inDays)->toDateString();
            $row->user_id = $owner?->id;
            $row->created_at = now()->subDays($addedDaysAgo);
            $row->updated_at = now()->subDays(max(0, $addedDaysAgo - 1));
            $row->save();
        };

        $item($alex, 'Milk', 1, 'L', 'fridge', 3, '2%. Top shelf, door side.', 4);
        $item($priya, 'Greek yogurt', 4, 'items', 'fridge', -2, null, 9);
        $item($priya, 'Apples', 6, 'items', 'fridge', 10, 'Honeycrisp. Take one if you ask first.', 2);
        $item($alex, 'Frozen dumplings', 24, 'items', 'freezer', 60, null, 12);
        $item($priya, 'Rice', 2, 'kg', 'cabinet', 300, 'Jasmine.', 20);
        $item($alex, 'Beans', 3, 'items', 'cabinet', 400, 'Cans of black beans.', 20);
        $item(null, 'Leftover pasta', 1, 'items', 'fridge', -1, 'No name on the box.', 6);
        $item($ra, 'Coffee', 340, 'g', 'cabinet', null, 'For the floor meeting. Help yourself.', 30);
    }
}
