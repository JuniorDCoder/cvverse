<?php

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can view team members page', function () {
    $admin = User::factory()->admin()->create();
    TeamMember::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.team-members.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/TeamMembers')
            ->has('teamMembers', 3)
            ->has('teamSectionVisible')
        );
});

test('non-admin cannot view team members page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.team-members.index'))
        ->assertForbidden();
});

test('admin can create a team member', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.team-members.store'), [
            'name' => 'Jane Doe',
            'role' => 'Designer',
            'bio' => 'A talented designer.',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(TeamMember::count())->toBe(1)
        ->and(TeamMember::first()->name)->toBe('Jane Doe');
});

test('admin can create a team member with photo', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.team-members.store'), [
            'name' => 'John Doe',
            'role' => 'Developer',
            'sort_order' => 0,
            'is_active' => true,
            'photo' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ])
        ->assertRedirect();

    $member = TeamMember::first();
    expect($member->photo)->not->toBeNull();
    Storage::disk('public')->assertExists($member->photo);
});

test('team member store validates required fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.team-members.store'), [])
        ->assertSessionHasErrors(['name', 'role']);
});

test('admin can update a team member', function () {
    $admin = User::factory()->admin()->create();
    $member = TeamMember::factory()->create(['name' => 'Old Name']);

    $this->actingAs($admin)
        ->post(route('admin.team-members.update', $member), [
            'name' => 'New Name',
            'role' => $member->role,
            'sort_order' => $member->sort_order,
            'is_active' => $member->is_active,
        ])
        ->assertRedirect();

    expect($member->fresh()->name)->toBe('New Name');
});

test('admin can delete a team member', function () {
    $admin = User::factory()->admin()->create();
    $member = TeamMember::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.team-members.destroy', $member))
        ->assertRedirect();

    expect(TeamMember::count())->toBe(0);
});

test('admin can toggle team member status', function () {
    $admin = User::factory()->admin()->create();
    $member = TeamMember::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.team-members.toggle-status', $member))
        ->assertRedirect();

    expect($member->fresh()->is_active)->toBeFalse();
});

test('admin can toggle team section visibility', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.team-members.section-visibility'), ['visible' => false])
        ->assertRedirect();

    expect(\App\Models\SiteSetting::getValue('team_section_visible'))->toBeFalsy();

    $this->actingAs($admin)
        ->put(route('admin.team-members.section-visibility'), ['visible' => true])
        ->assertRedirect();

    expect(\App\Models\SiteSetting::getValue('team_section_visible'))->toBeTruthy();
});

test('about page shows active team members when section is visible', function () {
    \App\Models\SiteSetting::setValue('team_section_visible', true, 'boolean', 'team');
    TeamMember::factory()->count(2)->create(['is_active' => true]);
    TeamMember::factory()->inactive()->create();

    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing/About')
            ->has('teamMembers', 2)
            ->where('teamSectionVisible', true)
        );
});
