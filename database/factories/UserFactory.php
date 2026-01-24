<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'user',
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user has not completed onboarding.
     */
    public function needsOnboarding(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed' => false,
            'onboarding_completed_at' => null,
        ]);
    }

    /**
     * Indicate that the user has a complete profile.
     */
    public function withProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'job_title' => fake()->jobTitle(),
            'industry' => fake()->randomElement(['technology', 'finance', 'marketing', 'healthcare', 'education']),
            'experience_level' => fake()->randomElement(['entry', 'mid', 'senior', 'lead', 'executive']),
            'interests' => fake()->randomElements(['job_search', 'career_change', 'networking', 'skill_showcase'], 2),
            'phone' => fake()->phoneNumber(),
            'location' => fake()->city().', '.fake()->countryCode(),
            'bio' => fake()->paragraph(),
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
