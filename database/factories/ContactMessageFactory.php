<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'company' => fake()->optional()->company(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraphs(2, true),
            'status' => 'new',
        ];
    }

    public function read(): static
    {
        return $this->state(['status' => 'read']);
    }

    public function replied(): static
    {
        return $this->state([
            'status' => 'replied',
            'admin_reply' => fake()->paragraphs(2, true),
            'replied_by' => User::factory(),
            'replied_at' => now(),
        ]);
    }
}
