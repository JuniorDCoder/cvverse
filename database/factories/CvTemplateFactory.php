<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CvTemplate>
 */
class CvTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.Str::random(6),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['professional', 'creative', 'minimal', 'modern']),
            'is_active' => true,
            'is_premium' => false,
            'price' => null,
            'currency' => 'USD',
            'downloads_count' => fake()->numberBetween(0, 1000),
            'views_count' => fake()->numberBetween(0, 5000),
            'layout' => [
                'columns' => 1,
                'headerStyle' => 'centered',
                'sidebarPosition' => 'left',
                'sectionStyle' => 'simple',
            ],
            'styles' => [
                'primaryColor' => '#2563eb',
                'secondaryColor' => '#64748b',
                'backgroundColor' => '#ffffff',
                'textColor' => '#1f2937',
                'headingColor' => '#111827',
                'accentColor' => '#3b82f6',
                'fontFamily' => 'Inter, sans-serif',
                'headingFont' => 'Inter, sans-serif',
                'fontSize' => '14px',
                'lineHeight' => '1.6',
                'spacing' => '1rem',
                'borderRadius' => '4px',
            ],
            'sections' => [
                ['id' => 'header', 'name' => 'Header', 'enabled' => true, 'order' => 0],
                ['id' => 'summary', 'name' => 'Summary', 'enabled' => true, 'order' => 1],
                ['id' => 'experience', 'name' => 'Experience', 'enabled' => true, 'order' => 2],
                ['id' => 'education', 'name' => 'Education', 'enabled' => true, 'order' => 3],
                ['id' => 'skills', 'name' => 'Skills', 'enabled' => true, 'order' => 4],
            ],
        ];
    }

    /**
     * Indicate that the template is premium.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
            'price' => fake()->randomFloat(2, 5, 50),
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
