<?php

namespace Database\Seeders;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Supplied -- read it, leave it alone. Fills the tables so everyone's app holds the same
// data. Two users, each with a few to-dos. One to-do's notes hold a <script> tag a stranger
// typed -- that is drill 2, and it is harmless until the page renders it unescaped.
//
//   docker compose exec app php artisan migrate:fresh --seed
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $alex = User::create([
            'name' => 'Alex Rivera',
            'email' => 'alex@example.edu',
            'password' => Hash::make('password'),
        ]);

        // The "other user". The SQL-injection drill is about reaching this row's email and
        // password hash through a search box that should only ever see to-do titles.
        User::create([
            'name' => 'Priya Shah',
            'email' => 'priya@example.edu',
            'password' => Hash::make('password'),
        ]);

        $todos = [
            ['Buy rice', true,  '2026-11-10', null],
            ['Email the TA', false, '2026-11-07', 'Ask about the midterm regrade.'],
            // A note a stranger left on a shared list. Harmless text -- until drill 2's page
            // prints it with {!! !!} and the browser runs it.
            ['Pantry potluck', false, null, '<script>alert(document.cookie)</script>Sign up here!'],
            ['Return library books', false, '2026-11-14', null],
        ];

        foreach ($todos as [$title, $done, $due, $notes]) {
            Todo::create([
                'user_id' => $alex->id,
                'title' => $title,
                'done' => $done,
                'due' => $due,
                'notes' => $notes,
            ]);
        }
    }
}
