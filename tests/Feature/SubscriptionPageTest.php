<?php

use App\Models\PricingPlan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('subscription page shows active subscription details', function () {
    $plan = PricingPlan::factory()->create([
        'name' => 'Pro Yearly',
        'price' => 50000,
        'currency' => 'XAF',
        'interval' => 'yearly',
        'features' => ['Unlimited CVs', 'Priority Support'],
    ]);

    $user = User::factory()->create([
        'subscription_status' => 'active',
        'pricing_plan_id' => $plan->id,
        'subscription_ends_at' => now()->addYear(),
    ]);

    $this->actingAs($user)
        ->get(route('subscription'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Subscription')
            ->where('subscription.status', 'active')
            ->where('subscription.plan.name', 'Pro Yearly')
        );
});

test('subscription page shows no active subscription for free users', function () {
    $user = User::factory()->create([
        'subscription_status' => 'free',
        'pricing_plan_id' => null,
        'subscription_ends_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('subscription'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Subscription')
            ->where('subscription', null)
        );
});
