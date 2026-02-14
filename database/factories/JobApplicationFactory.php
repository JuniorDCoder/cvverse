<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'requirements' => [
                fake()->sentence(),
                fake()->sentence(),
                fake()->sentence(),
            ],
            'skills' => ['PHP', 'Laravel', 'JavaScript'],
            'salary_range' => '$'.fake()->numberBetween(50, 150).'k - $'.fake()->numberBetween(150, 250).'k',
            'location' => fake()->city().', '.fake()->country(),
            'work_type' => fake()->randomElement(['remote', 'hybrid', 'onsite']),
            'experience_level' => fake()->randomElement(['entry', 'mid', 'senior', 'lead']),
            'source_url' => fake()->url(),
            'status' => JobApplication::STATUS_SAVED,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Set as applied status.
     */
    public function applied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobApplication::STATUS_APPLIED,
            'applied_at' => now(),
        ]);
    }
}
