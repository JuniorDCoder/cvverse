<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CvTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Modern',
                'slug' => 'modern',
                'description' => 'A clean and contemporary design suitable for most industries.',
                'image' => '/assets/images/templates/modern.png',
            ],
            [
                'name' => 'Classic',
                'slug' => 'classic',
                'description' => 'Traditional and elegant, perfect for corporate and academic roles.',
                'image' => '/assets/images/templates/classic.png',
            ],
            [
                'name' => 'Minimal',
                'slug' => 'minimal',
                'description' => 'Simple and widely spaced, focusing purely on the content.',
                'image' => '/assets/images/templates/minimal.png',
            ],
            [
                'name' => 'Creative',
                'slug' => 'creative',
                'description' => 'Bold colors and unique layout for creative professionals.',
                'image' => '/assets/images/templates/creative.png',
            ],
            [
                'name' => 'Executive',
                'slug' => 'executive',
                'description' => 'Sophisticated and authoritative, designed for senior roles.',
                'image' => '/assets/images/templates/executive.png',
            ],
        ];

        foreach ($templates as $template) {
            \App\Models\CvTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
