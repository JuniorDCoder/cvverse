<?php

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\JobApplication;
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

    $this->template = CvTemplate::factory()->create([
        'is_active' => true,
        'slug' => 'modern',
    ]);
});

test('admin can view cvs index page', function () {
    $cv = Cv::factory()->create(['user_id' => $this->user->id, 'template' => 'modern']);

    $response = $this->actingAs($this->admin)->get('/admin/cvs');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Cvs')
        ->has('cvs')
        ->has('stats')
        ->has('templates')
        ->has('users')
    );
});

test('admin can view cv create page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/cvs/create');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/CvCreate')
        ->has('users')
        ->has('templates')
    );
});

test('admin can create a cv manually', function () {
    $response = $this->actingAs($this->admin)->post('/admin/cvs', [
        'user_id' => $this->user->id,
        'name' => 'Test CV',
        'template' => 'modern',
        'is_primary' => false,
        'personal_info' => [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
        ],
        'summary' => 'A professional summary',
        'skills' => ['PHP', 'Laravel'],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('cvs', [
        'user_id' => $this->user->id,
        'name' => 'Test CV',
        'template' => 'modern',
    ]);
});

test('admin can view a cv', function () {
    $cv = Cv::factory()->create(['user_id' => $this->user->id, 'template' => 'modern']);

    $response = $this->actingAs($this->admin)->get("/admin/cvs/{$cv->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/CvShow')
        ->has('cv')
        ->has('user')
        ->has('versions')
    );
});

test('admin can view cv edit page', function () {
    $cv = Cv::factory()->create(['user_id' => $this->user->id, 'template' => 'modern']);

    $response = $this->actingAs($this->admin)->get("/admin/cvs/{$cv->id}/edit");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/CvEdit')
        ->has('cv')
        ->has('user')
        ->has('templates')
    );
});

test('admin can update a cv', function () {
    $cv = Cv::factory()->create(['user_id' => $this->user->id, 'template' => 'modern']);

    $response = $this->actingAs($this->admin)->put("/admin/cvs/{$cv->id}", [
        'name' => 'Updated CV Name',
        'template' => 'modern',
        'is_primary' => true,
        'personal_info' => ['full_name' => 'Updated Name'],
        'summary' => 'Updated summary',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('cvs', [
        'id' => $cv->id,
        'name' => 'Updated CV Name',
        'is_primary' => true,
    ]);
});

test('admin can delete a cv', function () {
    $cv = Cv::factory()->create(['user_id' => $this->user->id, 'template' => 'modern']);

    $response = $this->actingAs($this->admin)->delete("/admin/cvs/{$cv->id}");

    $response->assertRedirect('/admin/cvs');
    $this->assertDatabaseMissing('cvs', ['id' => $cv->id]);
});

test('admin can toggle cv primary status', function () {
    $cv = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'is_primary' => false,
    ]);

    $response = $this->actingAs($this->admin)->patch("/admin/cvs/{$cv->id}/toggle-primary");

    $response->assertRedirect();

    $cv->refresh();
    expect($cv->is_primary)->toBeTrue();
});

test('admin can duplicate a cv', function () {
    $cv = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'name' => 'Original CV',
    ]);

    $response = $this->actingAs($this->admin)->post("/admin/cvs/{$cv->id}/duplicate", [
        'user_id' => $this->user->id,
        'name' => 'Duplicated CV',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('cvs', [
        'user_id' => $this->user->id,
        'name' => 'Duplicated CV',
    ]);
});

test('admin can filter cvs by user', function () {
    $cv1 = Cv::factory()->create(['user_id' => $this->user->id, 'template' => 'modern']);
    $otherUser = User::factory()->create(['onboarding_completed' => true]);
    $cv2 = Cv::factory()->create(['user_id' => $otherUser->id, 'template' => 'modern']);

    $response = $this->actingAs($this->admin)->get("/admin/cvs?user_id={$this->user->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Cvs')
        ->has('cvs.data', 1)
    );
});

test('admin can filter cvs by search', function () {
    $cv1 = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'name' => 'Software Engineer CV',
    ]);
    $cv2 = Cv::factory()->create([
        'user_id' => $this->user->id,
        'template' => 'modern',
        'name' => 'Designer Portfolio',
    ]);

    $response = $this->actingAs($this->admin)->get('/admin/cvs?search=Software');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Cvs')
        ->has('cvs.data', 1)
    );
});

test('admin can get user job applications', function () {
    $job = JobApplication::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->admin)->get("/admin/users/{$this->user->id}/job-applications");

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'applications' => [
            '*' => ['id', 'title', 'company_name'],
        ],
    ]);
});

test('non-admin cannot access admin cv pages', function () {
    $response = $this->actingAs($this->user)->get('/admin/cvs');

    $response->assertForbidden();
});
