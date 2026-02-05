<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companies = ['Google', 'Apple', 'Microsoft', 'Amazon', 'Meta', 'Spotify', 'Netflix', 'Adobe', 'Salesforce', 'Stripe'];
        $roles = ['Software Engineer', 'Product Manager', 'UX Designer', 'Marketing Manager', 'Data Scientist', 'DevOps Engineer'];

        return [
            'author_name' => fake()->name(),
            'author_role' => fake()->randomElement($roles),
            'author_company' => fake()->randomElement($companies),
            'author_avatar' => null,
            'quote' => fake()->paragraph(2),
            'rating' => fake()->numberBetween(4, 5),
            'is_featured' => fake()->boolean(30),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the testimonial is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the testimonial is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
