<?php

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\User;
use App\Services\GeminiService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;

beforeEach(function () {
    // Ensure we have an active template
    CvTemplate::create([
        'name' => 'Modern',
        'slug' => 'modern',
        'description' => 'A modern CV template',
        'is_active' => true,
    ]);
});

it('displays the AI CV generator page for authenticated users', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    actingAs($user)
        ->get('/ai-cv-generator')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('cvs/AiGenerator'));
});

it('redirects unauthenticated users to login', function () {
    $this->get('/ai-cv-generator')
        ->assertRedirect('/login');
});

it('saves a generated CV for the user', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $cvData = [
        'personal_info' => [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'location' => 'New York, NY',
        ],
        'summary' => 'Experienced software engineer with 5+ years in full-stack development.',
        'experience' => [
            [
                'company' => 'Tech Corp',
                'title' => 'Senior Developer',
                'location' => 'New York',
                'start_date' => '2020-01',
                'end_date' => null,
                'current' => true,
                'description' => 'Led development of key features.',
            ],
        ],
        'education' => [
            [
                'institution' => 'MIT',
                'degree' => 'Bachelor of Science',
                'field' => 'Computer Science',
                'start_date' => '2015-09',
                'end_date' => '2019-05',
            ],
        ],
        'skills' => ['PHP', 'Laravel', 'Vue.js', 'JavaScript'],
        'languages' => [
            ['language' => 'English', 'proficiency' => 'Native'],
        ],
    ];

    actingAs($user)
        ->postJson('/ai-cv-generator/save', [
            'name' => 'My AI Generated CV',
            'template' => 'modern',
            'cv_data' => $cvData,
            'is_primary' => false,
        ])
        ->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'cv' => ['id', 'name', 'template'],
            'redirect_url',
        ]);

    expect(Cv::where('user_id', $user->id)->where('name', 'My AI Generated CV')->exists())->toBeTrue();
});

it('validates required fields when saving a CV', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    actingAs($user)
        ->postJson('/ai-cv-generator/save', [
            'name' => '',
            'template' => 'modern',
            'cv_data' => [],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('refines CV with AI chat', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $mockResponse = [
        'message' => 'I have updated your summary to be more impactful.',
        'updated_cv' => [
            'summary' => 'A highly accomplished software engineer with proven expertise.',
        ],
        'changes_summary' => 'Updated summary section',
    ];

    mock(GeminiService::class)
        ->shouldReceive('chatWithCv')
        ->once()
        ->andReturn($mockResponse);

    actingAs($user)
        ->postJson('/ai-cv-generator/refine', [
            'message' => 'Make my summary more impactful',
            'cv_data' => [
                'summary' => 'I am a software engineer.',
            ],
        ])
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'I have updated your summary to be more impactful.',
        ]);
});
