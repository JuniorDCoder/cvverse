<?php

use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

test('pricing page includes current active plan for authenticated users', function () {
    $activePlan = PricingPlan::factory()->create([
        'slug' => 'pro-monthly-xaf-'.Str::uuid(),
        'currency' => 'XAF',
        'interval' => 'monthly',
        'is_active' => true,
    ]);

    PricingPlan::factory()->create([
        'slug' => 'pro-yearly-xaf-'.Str::uuid(),
        'currency' => 'XAF',
        'interval' => 'yearly',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'pricing_plan_id' => $activePlan->id,
        'subscription_status' => 'active',
        'subscription_ends_at' => now()->addMonth(),
    ]);

    $this->actingAs($user)
        ->get(route('pricing'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing/Pricing')
            ->where('currentPlan.id', $activePlan->id)
            ->where('currentPlan.status', 'active')
            ->where('currentPlan.is_free', false)
        );
});
