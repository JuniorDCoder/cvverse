<?php

use App\Models\Cv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'onboarding_completed' => true,
        'email_verified_at' => now(),
    ]);

    $this->user = User::factory()->create([
        'role' => 'user',
        'onboarding_completed' => true,
        'email_verified_at' => now(),
    ]);

    $this->cv = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'personal_info' => [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'location' => 'New York, NY',
        ],
        'experience' => [
            [
                'company' => 'Acme Corp',
                'title' => 'Software Engineer',
                'start_date' => '2020-01',
                'end_date' => '2023-06',
                'description' => 'Built software',
            ],
        ],
        'skills' => ['PHP', 'Laravel', 'Vue.js'],
    ]);
});

test('user can preview their own cv as html', function () {
    $response = $this->actingAs($this->user)
        ->get("/cvs/{$this->cv->id}/preview-html");

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'text/html; charset=UTF-8');
    $response->assertSee('John Doe');
    $response->assertSee('john@example.com');
    $response->assertSee('Software Engineer');
    $response->assertSee('Acme Corp');
    $response->assertSee('PHP');
});

test('user cannot preview another users cv', function () {
    $otherUser = User::factory()->create([
        'role' => 'user',
        'onboarding_completed' => true,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($otherUser)
        ->get("/cvs/{$this->cv->id}/preview-html");

    $response->assertForbidden();
});

test('guest cannot preview cv', function () {
    $response = $this->get("/cvs/{$this->cv->id}/preview-html");

    $response->assertRedirect('/login');
});

test('admin can preview any cv via admin route', function () {
    $response = $this->actingAs($this->admin)
        ->get("/admin/cvs/{$this->cv->id}/preview-html");

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'text/html; charset=UTF-8');
    $response->assertSee('John Doe');
    $response->assertSee('Software Engineer');
});

test('non-admin cannot access admin cv preview', function () {
    $response = $this->actingAs($this->user)
        ->get("/admin/cvs/{$this->cv->id}/preview-html");

    $response->assertForbidden();
});

test('preview html contains cv sections', function () {
    $response = $this->actingAs($this->user)
        ->get("/cvs/{$this->cv->id}/preview-html");

    $response->assertSuccessful();
    $response->assertSee('PHP');
    $response->assertSee('Laravel');
    $response->assertSee('Vue.js');
});

test('preview html does not crash when experience title is missing', function () {
    $cvWithoutExperienceTitle = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'personal_info' => [
            'full_name' => 'No Title User',
            'email' => 'notitle@example.com',
        ],
        'experience' => [
            [
                'company' => 'Acme Corp',
                'start_date' => '2022-01',
                'description' => 'Worked on important features',
            ],
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->get("/cvs/{$cvWithoutExperienceTitle->id}/preview-html");

    $response->assertSuccessful();
    $response->assertSee('No Title User');
    $response->assertSee('Acme Corp');
});

test('pdf export does not crash when experience title is missing', function () {
    $cvWithoutExperienceTitle = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'personal_info' => [
            'full_name' => 'No Title User',
            'email' => 'notitle@example.com',
        ],
        'experience' => [
            [
                'company' => 'Acme Corp',
                'start_date' => '2022-01',
                'description' => 'Worked on important features',
            ],
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->get("/cvs/{$cvWithoutExperienceTitle->id}/export/pdf");

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('admin preview html does not crash with malformed cv arrays', function () {
    $cvWithMalformedArrays = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'personal_info' => [
            'full_name' => 'Admin Preview User',
            'email' => 'admin-preview@example.com',
        ],
        'experience' => [
            [
                'company' => 'Acme Corp',
                'start_date' => '2022-01',
                'description' => 'Worked on important features',
            ],
        ],
        'projects' => [
            [
                'description' => 'Project without name and title keys',
            ],
        ],
    ]);

    $response = $this->actingAs($this->admin)
        ->get("/admin/cvs/{$cvWithMalformedArrays->id}/preview-html");

    $response->assertSuccessful();
    $response->assertSee('Admin Preview User');
    $response->assertSee('Acme Corp');
});

test('preview html does not crash when cv values are arrays instead of strings', function () {
    $cvWithArrayValues = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'personal_info' => [
            'full_name' => 'Array Value User',
            'email' => ['primary' => 'array-user@example.com'],
            'location' => ['city' => 'Douala', 'country' => 'Cameroon'],
        ],
        'summary' => 'Experienced engineer',
        'experience' => [
            [
                'company' => ['Acme', 'Corp'],
                'title' => ['Senior', 'Engineer'],
                'start_date' => ['2021-01'],
                'description' => ['Built', 'and', 'maintained', 'features'],
            ],
        ],
        'skills' => [
            ['name' => 'PHP'],
            'Laravel',
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->get("/cvs/{$cvWithArrayValues->id}/preview-html");

    $response->assertSuccessful();
    $response->assertSee('Array Value User');
    $response->assertSee('array-user@example.com');
});
