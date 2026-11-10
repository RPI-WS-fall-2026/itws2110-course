<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// Supplied -- read it, leave it alone. Three accounts and their lists, the same for everyone:
//
//   alex@example.edu   / password    an ordinary user, four to-dos
//   priya@example.edu  / password    an ordinary user, two to-dos
//   admin@example.edu  / admin-pass  the admin
//
// The passwords are handed to User::create() as typed. Whether they reach the database as
// typed is up to the User model (drill 1). After you change the model, rebuild the tables so
// the rows are written again:
//
//   docker compose exec app php artisan migrate:fresh --seed
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $alex = User::create(['name' => 'Alex Rivera', 'email' => 'alex@example.edu', 'password' => 'password']);
        $priya = User::create(['name' => 'Priya Shah', 'email' => 'priya@example.edu', 'password' => 'password']);

        // forceFill: role is how an admin is made, by the people who run the app, never by a form.
        (new User)->forceFill([
            'name' => 'Sam Admin', 'email' => 'admin@example.edu', 'password' => 'admin-pass', 'role' => 'admin',
        ])->save();

        foreach ([
            ['Buy rice', true, '2026-11-10', null],
            ['Email the TA', false, '2026-11-13', 'Ask about the midterm regrade. Mention the 2 points on question 4.'],
            ['Return library books', false, '2026-11-14', null],
            ['Plan Friday dinner', false, null, 'Priya is vegetarian.'],
        ] as [$title, $done, $due, $notes]) {
            $alex->todos()->create(['title' => $title, 'done' => $done, 'due' => $due, 'notes' => $notes]);
        }

        foreach ([
            ['Call the landlord', false, '2026-11-12', 'Lease renewal: ask about the deposit, and the leak under the sink.'],
            ['Finish the lab report', false, '2026-11-16', null],
        ] as [$title, $done, $due, $notes]) {
            $priya->todos()->create(['title' => $title, 'done' => $done, 'due' => $due, 'notes' => $notes]);
        }
    }
}
