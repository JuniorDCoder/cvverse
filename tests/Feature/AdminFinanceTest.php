<?php

use App\Models\PricingPlan;
use App\Models\User;
use App\Models\Withdrawal;

use function Pest\Laravel\actingAs;

function createAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
        'onboarding_completed_at' => now(),
    ]);
}

function createRegularUser(): User
{
    return User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
}

it('denies non-admin users access to finance page', function () {
    $user = createRegularUser();

    actingAs($user)
        ->get('/admin/finance')
        ->assertForbidden();
});

it('allows admin to view the finance page', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->get('/admin/finance')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Finance/Index')
            ->has('balances')
            ->has('subscriptions')
            ->has('withdrawals')
            ->has('stats')
        );
});

it('shows subscriptions on the finance page', function () {
    $admin = createAdmin();
    $plan = PricingPlan::factory()->create(['price' => 5000, 'currency' => 'XAF']);
    User::factory()->create([
        'pricing_plan_id' => $plan->id,
        'subscription_status' => 'active',
        'subscription_ends_at' => now()->addMonth(),
        'onboarding_completed_at' => now(),
    ]);

    actingAs($admin)
        ->get('/admin/finance')
        ->assertInertia(fn ($page) => $page
            ->has('subscriptions', 1)
            ->where('stats.total_subscribers', 1)
        );
});

it('shows withdrawal history on the finance page', function () {
    $admin = createAdmin();
    Withdrawal::factory()->count(3)->create(['user_id' => $admin->id]);

    actingAs($admin)
        ->get('/admin/finance')
        ->assertInertia(fn ($page) => $page
            ->has('withdrawals', 3)
        );
});

it('validates withdrawal request', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->postJson('/admin/finance/withdraw', [
            'amount' => 50,
            'receiver' => 'abc',
            'service' => 'INVALID',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount', 'receiver', 'service']);
});

it('validates minimum withdrawal amount', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->postJson('/admin/finance/withdraw', [
            'amount' => 10,
            'receiver' => '237670000000',
            'service' => 'MTN',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount']);
});

it('requires all withdrawal fields', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->postJson('/admin/finance/withdraw', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount', 'receiver', 'service']);
});

it('creates a withdrawal record on request', function () {
    $admin = createAdmin();

    // This will fail at MeSomb API level, but the withdrawal record should be created
    actingAs($admin)
        ->postJson('/admin/finance/withdraw', [
            'amount' => 1000,
            'receiver' => '237670000000',
            'service' => 'MTN',
        ]);

    expect(Withdrawal::where('user_id', $admin->id)->count())->toBe(1);
    expect(Withdrawal::first()->amount)->toBe('1000.00');
});

it('denies non-admin users from withdrawing', function () {
    $user = createRegularUser();

    actingAs($user)
        ->postJson('/admin/finance/withdraw', [
            'amount' => 1000,
            'receiver' => '237670000000',
            'service' => 'MTN',
        ])
        ->assertForbidden();
});
