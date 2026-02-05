<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingPlan>
 */
class PricingPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Basic', 'Pro', 'Premium', 'Enterprise']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => fake()->randomFloat(2, 9.99, 99.99),
            'currency' => 'USD',
            'interval' => fake()->randomElement(['monthly', 'yearly']),
            'features' => [
                'Unlimited CVs',
                'Premium templates',
                'AI assistance',
                'PDF & DOCX export',
            ],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
