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
