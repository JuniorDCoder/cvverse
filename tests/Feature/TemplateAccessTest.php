<?php

use App\Models\CvTemplate;
use App\Models\PricingPlan;
use App\Models\User;

test('free users can access free templates', function () {
    $user = User::factory()->create([
        'subscription_status' => 'free',
        'pricing_plan_id' => null,
    ]);

    $freeTemplate = CvTemplate::factory()->create(['is_premium' => false, 'is_active' => true]);

    expect($user->canAccessTemplate($freeTemplate))->toBeTrue();
});

test('free users cannot access premium templates', function () {
    $user = User::factory()->create([
        'subscription_status' => 'free',
        'pricing_plan_id' => null,
    ]);

    $premiumTemplate = CvTemplate::factory()->create(['is_premium' => true, 'is_active' => true]);

    expect($user->canAccessTemplate($premiumTemplate))->toBeFalse();
    expect($user->hasPremiumAccess())->toBeFalse();
});

test('subscribed users can access premium templates', function () {
    $plan = PricingPlan::factory()->create();
    $user = User::factory()->create([
        'subscription_status' => 'active',
        'pricing_plan_id' => $plan->id,
        'subscription_ends_at' => now()->addMonth(),
    ]);

    $premiumTemplate = CvTemplate::factory()->create(['is_premium' => true, 'is_active' => true]);

    expect($user->hasPremiumAccess())->toBeTrue();
    expect($user->canAccessTemplate($premiumTemplate))->toBeTrue();
});

test('expired subscription users cannot access premium templates', function () {
    $plan = PricingPlan::factory()->create();
    $user = User::factory()->create([
        'subscription_status' => 'active',
        'pricing_plan_id' => $plan->id,
        'subscription_ends_at' => now()->subDay(),
    ]);

    $premiumTemplate = CvTemplate::factory()->create(['is_premium' => true, 'is_active' => true]);

    expect($user->hasPremiumAccess())->toBeFalse();
    expect($user->canAccessTemplate($premiumTemplate))->toBeFalse();
});

test('admins always have premium access', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'subscription_status' => 'free',
        'pricing_plan_id' => null,
    ]);

    $premiumTemplate = CvTemplate::factory()->create(['is_premium' => true, 'is_active' => true]);

    expect($user->hasPremiumAccess())->toBeTrue();
    expect($user->canAccessTemplate($premiumTemplate))->toBeTrue();
});

test('templates index page returns premium access status', function () {
    CvTemplate::factory()->count(3)->create(['is_active' => true]);

    $response = $this->get('/templates');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('landing/Templates')
        ->has('templates.data')
        ->has('hasPremiumAccess')
    );
});

test('premium template download is blocked for non-premium users', function () {
    $user = User::factory()->create([
        'subscription_status' => 'free',
        'pricing_plan_id' => null,
    ]);

    $premiumTemplate = CvTemplate::factory()->create(['is_premium' => true, 'is_active' => true]);

    $response = $this->actingAs($user)->post("/templates/{$premiumTemplate->id}/save-as-cv", [
        'cv_data' => [
            'personal_info' => [
                'full_name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'experience' => [],
            'education' => [],
            'skills' => [],
        ],
        'name' => 'My CV',
    ]);

    $response->assertForbidden();
    $response->assertJson(['success' => false]);
});

test('premium template download is allowed for premium users', function () {
    $plan = PricingPlan::factory()->create();
    $user = User::factory()->create([
        'subscription_status' => 'active',
        'pricing_plan_id' => $plan->id,
        'subscription_ends_at' => now()->addMonth(),
    ]);

    $premiumTemplate = CvTemplate::factory()->create(['is_premium' => true, 'is_active' => true]);

    $response = $this->actingAs($user)->post("/templates/{$premiumTemplate->id}/save-as-cv", [
        'cv_data' => [
            'personal_info' => [
                'full_name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'experience' => [],
            'education' => [],
            'skills' => [],
        ],
        'name' => 'My CV',
    ]);

    $response->assertSuccessful();
});
