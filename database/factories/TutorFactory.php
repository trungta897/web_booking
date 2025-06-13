<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->create(['role' => 'tutor']);

        return [
            'user_id' => $user->id,
            'bio' => fake()->paragraph(),
            'hourly_rate' => fake()->numberBetween(20, 100),
            'is_available' => true,
            'experience_years' => fake()->numberBetween(1, 20),
            'education' => fake()->sentence(),
            'specialization' => fake()->sentence(),
        ];
    }
}
