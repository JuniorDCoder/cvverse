<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'currency' => 'XAF',
            'receiver' => fake()->numerify('237#########'),
            'service' => fake()->randomElement(['MTN', 'ORANGE']),
            'status' => 'success',
            'mesomb_reference' => fake()->uuid(),
            'mesomb_transaction_id' => fake()->uuid(),
            'message' => 'Withdrawal processed successfully',
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending', 'mesomb_reference' => null]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => 'failed',
            'failure_reason' => 'Insufficient balance',
        ]);
    }
}
