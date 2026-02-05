<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'author_name' => 'Sarah Chen',
                'author_role' => 'Software Engineer',
                'author_company' => 'Google',
                'quote' => 'CVverse helped me land my dream job at a Fortune 500 company. The AI suggestions were incredibly helpful!',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'author_name' => 'Michael Rodriguez',
                'author_role' => 'Marketing Manager',
                'author_company' => 'Spotify',
                'quote' => 'The templates are stunning and the ATS optimization gave me peace of mind. Got 3 interviews in my first week!',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'author_name' => 'Emma Thompson',
                'author_role' => 'UX Designer',
                'author_company' => 'Apple',
                'quote' => 'Best CV builder I\'ve ever used. The real-time preview feature saved me hours of formatting headaches.',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'author_name' => 'James Wilson',
                'author_role' => 'Product Manager',
                'author_company' => 'Microsoft',
                'quote' => 'The AI writing assistant helped me articulate my achievements perfectly. Highly recommend to any job seeker!',
                'rating' => 5,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'author_name' => 'Lisa Park',
                'author_role' => 'Data Scientist',
                'author_company' => 'Netflix',
                'quote' => 'Finally, a CV builder that understands what recruiters are looking for. The ATS optimization is a game-changer.',
                'rating' => 5,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['author_name' => $testimonial['author_name']],
                $testimonial
            );
        }
    }
}
