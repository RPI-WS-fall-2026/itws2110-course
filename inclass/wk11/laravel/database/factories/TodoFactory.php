<?php

namespace Database\Factories;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Todo> */
class TodoFactory extends Factory
{
    protected $model = Todo::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'done' => fake()->boolean(30),
            'due' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
