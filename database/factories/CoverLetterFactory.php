<?php

namespace Database\Factories;

use App\Models\CoverLetter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoverLetter>
 */
class CoverLetterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tones = array_keys(CoverLetter::TONES);

        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3).' Cover Letter',
            'content' => $this->generateCoverLetterContent(),
            'tone' => fake()->randomElement($tones),
            'job_application_id' => null,
            'ai_improvements' => null,
        ];
    }

    /**
     * Generate realistic cover letter content.
     */
    private function generateCoverLetterContent(): string
    {
        $name = fake()->name();
        $jobTitle = fake()->jobTitle();
        $company = fake()->company();
        $skill1 = fake()->randomElement(['project management', 'data analysis', 'team leadership', 'software development', 'customer relations']);
        $skill2 = fake()->randomElement(['communication', 'problem-solving', 'strategic planning', 'technical expertise', 'innovation']);
        $years = fake()->numberBetween(2, 10);

        return <<<HTML
<p>Dear Hiring Manager,</p>

<p>I am writing to express my strong interest in the {$jobTitle} position at {$company}. With {$years} years of experience in {$skill1} and a proven track record in {$skill2}, I am confident that I would be a valuable addition to your team.</p>

<p>Throughout my career, I have consistently demonstrated my ability to deliver exceptional results while maintaining the highest standards of professionalism. My experience has equipped me with the skills necessary to excel in this role and contribute to your organization's continued success.</p>

<p>Key highlights of my qualifications include:</p>
<ul>
<li>Extensive experience in {$skill1}, resulting in measurable improvements for previous employers</li>
<li>Strong {$skill2} skills that have enabled effective collaboration across diverse teams</li>
<li>A commitment to continuous learning and professional development</li>
<li>Proven ability to adapt to new challenges and deliver results under pressure</li>
</ul>

<p>I am particularly drawn to {$company}'s reputation for innovation and commitment to excellence. I believe my background and enthusiasm make me an ideal candidate for this opportunity.</p>

<p>I would welcome the opportunity to discuss how my skills and experience align with your needs. Thank you for considering my application.</p>

<p>Sincerely,<br>{$name}</p>
HTML;
    }

    /**
     * Set a specific tone for the cover letter.
     */
    public function withTone(string $tone): static
    {
        return $this->state(fn (array $attributes) => [
            'tone' => $tone,
        ]);
    }

    /**
     * Add AI improvements to the cover letter.
     */
    public function withImprovements(): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_improvements' => [
                'original_content' => $attributes['content'],
                'improved_content' => '<p>Improved version of the cover letter with better phrasing and structure.</p>',
                'suggestions' => [
                    'Consider adding more specific achievements with metrics',
                    'Tailor the opening paragraph more closely to the company values',
                    'Include a call to action in the closing paragraph',
                ],
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }
}
