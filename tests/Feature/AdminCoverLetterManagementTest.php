<?php

use App\Models\CoverLetter;
use App\Models\Cv;
use App\Models\JobApplication;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'onboarding_completed_at' => now(),
    ]);
});

it('allows admin to view cover letters index', function () {
    $coverLetters = CoverLetter::factory()->count(3)->create();

    actingAs($this->admin)
        ->get('/admin/cover-letters')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/CoverLetters')
            ->has('coverLetters.data', 3)
            ->has('stats')
            ->has('tones')
            ->has('users')
        );
});

it('allows admin to filter cover letters by user', function () {
    $user = User::factory()->create();
    CoverLetter::factory()->create(['user_id' => $user->id]);
    CoverLetter::factory()->count(2)->create();

    actingAs($this->admin)
        ->get('/admin/cover-letters?user_id=' . $user->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/CoverLetters')
            ->has('coverLetters.data', 1)
        );
});

it('allows admin to filter cover letters by tone', function () {
    CoverLetter::factory()->create(['tone' => 'professional']);
    CoverLetter::factory()->create(['tone' => 'enthusiastic']);
    CoverLetter::factory()->create(['tone' => 'formal']);

    actingAs($this->admin)
        ->get('/admin/cover-letters?tone=professional')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/CoverLetters')
            ->has('coverLetters.data', 1)
        );
});

it('allows admin to view create cover letter page', function () {
    User::factory()->count(3)->create();

    actingAs($this->admin)
        ->get('/admin/cover-letters/create')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/CoverLetterCreate')
            ->has('users')
            ->has('tones')
        );
});

it('allows admin to create cover letter manually', function () {
    $user = User::factory()->create();

    actingAs($this->admin)
        ->post('/admin/cover-letters', [
            'user_id' => $user->id,
            'name' => 'Software Engineer Cover Letter',
            'content' => '<p>Dear Hiring Manager...</p>',
            'tone' => 'professional',
        ])
        ->assertRedirect();

    expect(CoverLetter::where('name', 'Software Engineer Cover Letter')->exists())->toBeTrue();
});

it('allows admin to view a specific cover letter', function () {
    $coverLetter = CoverLetter::factory()->create();

    actingAs($this->admin)
        ->get('/admin/cover-letters/' . $coverLetter->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/CoverLetterShow')
            ->has('coverLetter')
            ->has('user')
            ->has('tones')
        );
});

it('allows admin to view edit cover letter page', function () {
    $coverLetter = CoverLetter::factory()->create();

    actingAs($this->admin)
        ->get('/admin/cover-letters/' . $coverLetter->id . '/edit')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/CoverLetterEdit')
            ->has('coverLetter')
            ->has('user')
            ->has('tones')
            ->has('jobApplications')
        );
});

it('allows admin to update a cover letter', function () {
    $coverLetter = CoverLetter::factory()->create([
        'name' => 'Old Name',
    ]);

    actingAs($this->admin)
        ->put('/admin/cover-letters/' . $coverLetter->id, [
            'name' => 'Updated Cover Letter Name',
            'content' => '<p>Updated content...</p>',
            'tone' => 'confident',
        ])
        ->assertRedirect();

    $coverLetter->refresh();
    expect($coverLetter->name)->toBe('Updated Cover Letter Name');
    expect($coverLetter->tone)->toBe('confident');
});

it('allows admin to delete a cover letter', function () {
    $coverLetter = CoverLetter::factory()->create();
    $letterId = $coverLetter->id;

    actingAs($this->admin)
        ->delete('/admin/cover-letters/' . $coverLetter->id)
        ->assertRedirect('/admin/cover-letters');

    expect(CoverLetter::find($letterId))->toBeNull();
});

it('allows admin to duplicate a cover letter', function () {
    $coverLetter = CoverLetter::factory()->create([
        'name' => 'Original Cover Letter',
    ]);

    actingAs($this->admin)
        ->post('/admin/cover-letters/' . $coverLetter->id . '/duplicate', [
            'name' => 'Duplicated Cover Letter',
        ])
        ->assertRedirect();

    expect(CoverLetter::where('name', 'Duplicated Cover Letter')->exists())->toBeTrue();
});

it('allows admin to duplicate cover letter to different user', function () {
    $originalUser = User::factory()->create();
    $targetUser = User::factory()->create();

    $coverLetter = CoverLetter::factory()->create([
        'user_id' => $originalUser->id,
        'name' => 'Original Cover Letter',
    ]);

    actingAs($this->admin)
        ->post('/admin/cover-letters/' . $coverLetter->id . '/duplicate', [
            'user_id' => $targetUser->id,
            'name' => 'Cover Letter for New User',
        ])
        ->assertRedirect();

    $duplicated = CoverLetter::where('name', 'Cover Letter for New User')->first();
    expect($duplicated)->not->toBeNull();
    expect($duplicated->user_id)->toBe($targetUser->id);
});

it('prevents non-admin from accessing admin cover letters', function () {
    $regularUser = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    actingAs($regularUser)
        ->get('/admin/cover-letters')
        ->assertForbidden();
});

it('validates required fields when creating cover letter', function () {
    actingAs($this->admin)
        ->post('/admin/cover-letters', [])
        ->assertSessionHasErrors(['user_id', 'name', 'content']);
});

it('validates tone must be valid when creating cover letter', function () {
    $user = User::factory()->create();

    actingAs($this->admin)
        ->post('/admin/cover-letters', [
            'user_id' => $user->id,
            'name' => 'Test Cover Letter',
            'content' => '<p>Content...</p>',
            'tone' => 'invalid-tone',
        ])
        ->assertSessionHasErrors(['tone']);
});

it('allows admin to get user resources for cover letter creation', function () {
    $user = User::factory()->create();
    Cv::factory()->create(['user_id' => $user->id]);
    JobApplication::factory()->create(['user_id' => $user->id]);

    actingAs($this->admin)
        ->get('/admin/users/' . $user->id . '/resources')
        ->assertOk()
        ->assertJsonStructure([
            'applications',
            'cvs',
        ]);
});
