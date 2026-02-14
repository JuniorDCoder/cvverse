<?php

use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Support\Str;

test('guests are redirected when accessing checkout', function () {
    $plan = PricingPlan::factory()->create([
        'slug' => 'plan-'.Str::uuid(),
    ]);

    $response = $this->get(route('payment.checkout', $plan));

    $response->assertRedirect(route('login'));
});

test('payment process validates phone format', function () {
    $user = User::factory()->create();
    $plan = PricingPlan::factory()->create([
        'slug' => 'plan-'.Str::uuid(),
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('payment.checkout', $plan))
        ->post(route('payment.process', $plan), [
            'payer' => 'invalid-phone',
            'service' => 'MTN',
        ]);

    $response->assertRedirect(route('payment.checkout', $plan));
    $response->assertSessionHasErrors(['payer']);
});

test('payment process returns traceable error when provider is not configured', function () {
    config([
        'services.mesomb.application_key' => null,
        'services.mesomb.access_key' => null,
        'services.mesomb.secret_key' => null,
    ]);

    $user = User::factory()->create();
    $plan = PricingPlan::factory()->create([
        'currency' => 'XAF',
        'price' => 5000,
        'slug' => 'plan-'.Str::uuid(),
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('payment.checkout', $plan))
        ->post(route('payment.process', $plan), [
            'payer' => '237612345678',
            'service' => 'MTN',
        ]);

    $response->assertRedirect(route('payment.checkout', $plan));
    $response->assertSessionHasErrors(['service']);

    expect(session('errors')->first('service'))->toContain('reference');
});

test('successful payment activates subscription and redirects to subscription page', function () {
    config([
        'services.mesomb.application_key' => 'app-key',
        'services.mesomb.access_key' => 'access-key',
        'services.mesomb.secret_key' => 'secret-key',
    ]);

    $user = User::factory()->create([
        'subscription_status' => 'free',
        'pricing_plan_id' => null,
        'subscription_ends_at' => null,
    ]);

    $plan = PricingPlan::factory()->create([
        'currency' => 'XAF',
        'price' => 5000,
        'interval' => 'yearly',
        'slug' => 'plan-'.Str::uuid(),
    ]);

    $mock = \Mockery::mock('overload:MeSomb\Operation\PaymentOperation');
    $mock->shouldReceive('makeCollect')->once()->andReturn(new class
    {
        public string $status = 'SUCCESS';

        public string $message = 'The payment has been successfully done!';

        public ?string $reference = null;

        public object $transaction;

        public function __construct()
        {
            $this->transaction = (object) [
                'reference' => null,
                'status' => 'SUCCESS',
            ];
        }

        public function isOperationSuccess(): bool
        {
            return true;
        }

        public function isTransactionSuccess(): bool
        {
            return true;
        }
    });

    $response = $this
        ->actingAs($user)
        ->post(route('payment.process', $plan), [
            'payer' => '237612345678',
            'service' => 'MTN',
        ]);

    $response->assertRedirect(route('subscription'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->subscription_status)->toBe('active');
    expect($user->pricing_plan_id)->toBe($plan->id);
    expect($user->subscription_ends_at)->not->toBeNull();
});
