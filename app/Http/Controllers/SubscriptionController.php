<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    /**
     * Display the user's subscription page.
     */
    public function index(Request $request)
    {
        $user = $request->user()->load('pricingPlan');

        if (
            $user->subscription_status === 'active' &&
            $user->subscription_ends_at !== null &&
            $user->subscription_ends_at->isPast()
        ) {
            $user->forceFill([
                'subscription_status' => 'expired',
            ])->save();

            $user->refresh()->load('pricingPlan');
        }

        $hasActiveSubscription = $user->subscription_status === 'active'
            && $user->pricingPlan !== null
            && ($user->subscription_ends_at === null || $user->subscription_ends_at->isFuture());

        $subscription = null;

        if ($hasActiveSubscription) {
            $subscription = [
                'name' => $user->pricingPlan->name,
                'status' => $user->subscription_status,
                'ends_at' => $user->subscription_ends_at?->toIso8601String(),
                'plan' => [
                    'name' => $user->pricingPlan->name,
                    'price' => (string) $user->pricingPlan->price,
                    'currency' => $user->pricingPlan->currency,
                    'interval' => $user->pricingPlan->interval,
                    'features' => (array) $user->pricingPlan->features,
                ],
            ];
        }

        return Inertia::render('Subscription', [
            'subscription' => $subscription,
        ]);
    }
}
