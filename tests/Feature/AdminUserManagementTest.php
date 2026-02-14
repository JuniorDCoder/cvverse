<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
        'onboarding_completed' => true,
    ]);
});

test('admin can view users list', function () {
    User::factory()->count(5)->create();

    $response = $this->actingAs($this->admin)->get('/admin/users');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users')
        ->has('users.data', 6) // 5 users + admin
        ->has('stats')
        ->has('filters')
    );
});

test('admin can filter users by role', function () {
    User::factory()->count(3)->create(['role' => 'user']);
    User::factory()->count(2)->create(['role' => 'admin']);

    $response = $this->actingAs($this->admin)->get('/admin/users?role=admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users')
        ->has('users.data', 3) // 2 created + 1 test admin
    );
});

test('admin can search users by name', function () {
    User::factory()->create(['name' => 'John Doe', 'role' => 'user']);
    User::factory()->create(['name' => 'Jane Smith', 'role' => 'user']);

    $response = $this->actingAs($this->admin)->get('/admin/users?search=John');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users')
        ->has('users.data', 1)
    );
});

test('admin can create a new user', function () {
    $response = $this->actingAs($this->admin)->post('/admin/users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'user',
        'send_invitation' => false,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'user',
    ]);
});

test('admin can view user details', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)->get("/admin/users/{$user->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/UserDetails')
        ->has('user')
        ->has('cvs')
        ->has('coverLetters')
        ->has('applications')
        ->has('chatSessions')
        ->has('activities')
        ->has('stats')
    );
});

test('admin can update a user', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($this->admin)->put("/admin/users/{$user->id}", [
        'name' => 'Updated Name',
        'email' => $user->email,
        'role' => 'admin',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'role' => 'admin',
    ]);
});

test('admin can delete a user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)->delete("/admin/users/{$user->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('admin cannot delete their own account', function () {
    $response = $this->actingAs($this->admin)->delete("/admin/users/{$this->admin->id}");

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('users', [
        'id' => $this->admin->id,
    ]);
});

test('admin can toggle user role', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($this->admin)->patch("/admin/users/{$user->id}/toggle-role", [
        'role' => 'admin',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'admin',
    ]);
});

test('admin cannot remove their own admin role', function () {
    $response = $this->actingAs($this->admin)->patch("/admin/users/{$this->admin->id}/toggle-role", [
        'role' => 'user',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('users', [
        'id' => $this->admin->id,
        'role' => 'admin',
    ]);
});

test('non-admin cannot access user management', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'email_verified_at' => now(),
        'onboarding_completed' => true,
    ]);

    $response = $this->actingAs($user)->get('/admin/users');

    $response->assertForbidden();
});

test('admin can export users to csv', function () {
    User::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get('/admin/users/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('create user validates required fields', function () {
    $response = $this->actingAs($this->admin)->post('/admin/users', [
        'name' => '',
        'email' => 'invalid-email',
        'role' => 'invalid',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'role']);
});

test('create user validates unique email', function () {
    $existingUser = User::factory()->create();

    $response = $this->actingAs($this->admin)->post('/admin/users', [
        'name' => 'New User',
        'email' => $existingUser->email,
        'role' => 'user',
    ]);

    $response->assertSessionHasErrors(['email']);
});
