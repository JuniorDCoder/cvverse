<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'status' => 'active',
            'token' => fake()->unique()->regexify('[a-zA-Z0-9]{64}'),
            'source' => fake()->randomElement(['popup', 'footer', 'admin']),
            'ip_address' => fake()->ipv4(),
            'subscribed_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }
}
