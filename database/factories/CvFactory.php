<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cv>
 */
class CvFactory extends Factory
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
            'name' => fake()->words(3, true).' CV',
            'template' => 'modern',
            'is_primary' => false,
            'personal_info' => [
                'full_name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'location' => fake()->city().', '.fake()->country(),
            ],
            'summary' => fake()->paragraph(),
            'experience' => [
                [
                    'company' => fake()->company(),
                    'title' => fake()->jobTitle(),
                    'location' => fake()->city(),
                    'start_date' => fake()->date('Y-m'),
                    'end_date' => fake()->date('Y-m'),
                    'current' => false,
                    'description' => fake()->paragraph(),
                ],
            ],
            'education' => [
                [
                    'institution' => fake()->company().' University',
                    'degree' => "Bachelor's",
                    'field' => 'Computer Science',
                    'start_date' => fake()->date('Y-m'),
                    'end_date' => fake()->date('Y-m'),
                ],
            ],
            'skills' => ['PHP', 'Laravel', 'JavaScript', 'Vue.js'],
            'languages' => [
                [
                    'language' => 'English',
                    'proficiency' => 'Fluent',
                ],
            ],
        ];
    }

    /**
     * Set as primary CV.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
