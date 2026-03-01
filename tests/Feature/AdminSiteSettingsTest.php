<?php

use App\Models\SiteSetting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin settings page includes milestones', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Settings')
            ->has('settings.milestones')
            ->has('settings.stats')
        );
});

test('admin can update milestones', function () {
    $admin = User::factory()->admin()->create();

    $milestones = [
        ['year' => '2020', 'title' => 'Founded', 'description' => 'We started.'],
        ['year' => '2023', 'title' => 'Growth', 'description' => 'We grew.'],
    ];

    $this->actingAs($admin)
        ->put(route('admin.site-settings.milestones'), ['milestones' => $milestones])
        ->assertRedirect();

    $stored = SiteSetting::getValue('milestones');
    expect($stored)->toBeArray()
        ->toHaveCount(2)
        ->and($stored[0]['year'])->toBe('2020')
        ->and($stored[1]['title'])->toBe('Growth');
});

test('milestones validation requires at least one milestone', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.site-settings.milestones'), ['milestones' => []])
        ->assertSessionHasErrors('milestones');
});

test('milestones validation requires year, title, and description', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.site-settings.milestones'), [
            'milestones' => [
                ['year' => '', 'title' => '', 'description' => ''],
            ],
        ])
        ->assertSessionHasErrors([
            'milestones.0.year',
            'milestones.0.title',
            'milestones.0.description',
        ]);
});

test('about page receives stats and milestones from settings', function () {
    SiteSetting::setValue('stats_cvs_created', '1M+', 'string', 'stats');
    SiteSetting::setValue('stats_countries', '200+', 'string', 'stats');
    SiteSetting::setValue('milestones', [
        ['year' => '2025', 'title' => 'Milestone', 'description' => 'A test milestone.'],
    ], 'json', 'milestones');

    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing/About')
            ->where('stats.cvs_created', '1M+')
            ->where('stats.countries', '200+')
            ->has('milestones', 1)
            ->where('milestones.0.year', '2025')
        );
});

test('non-admin cannot update milestones', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('admin.site-settings.milestones'), [
            'milestones' => [
                ['year' => '2024', 'title' => 'Test', 'description' => 'Test'],
            ],
        ])
        ->assertForbidden();
});
