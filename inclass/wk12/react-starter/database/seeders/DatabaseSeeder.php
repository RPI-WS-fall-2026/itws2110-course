<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Sample logins for class demos. Both use the factory's default password ("password")
        // and are already email-verified, so they can log in without the confirmation step.
        foreach ([
            ['name' => 'Test User', 'email' => 'test@example.com'],
            ['name' => 'Alex Rivera', 'email' => 'alex@example.edu'],
        ] as $sample) {
            if (! User::where('email', $sample['email'])->exists()) {
                User::factory()->create($sample);
            }
        }
    }
}
