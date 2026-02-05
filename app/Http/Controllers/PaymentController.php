<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function checkout(PricingPlan $plan)
    {
        return Inertia::render('Payment/Checkout', [
            'plan' => $plan,
        ]);
    }

    public function process(Request $request, PricingPlan $plan)
    {
        $request->validate([
            'payer' => 'required|string', // Phone number or other identifier
            'service' => 'required|string|in:MTN,ORANGE,AIRTEL', // Add other services if needed
        ]);

        // This is a simplified implementation.
        // In a real scenario you would handle the payment request to MeSomb here.
        // Documentation: https://mesomb.hachther.com/en/api/v1.1/payment/collect/

        try {
            $client = new \MeSomb\Operation\PaymentOperation(
                config('services.mesomb.application_key'),
                config('services.mesomb.access_key'),
                config('services.mesomb.secret_key')
            );

            $response = $client->makeCollect([
                'payer' => $request->payer,
                'amount' => (int) $plan->price,
                'service' => $request->service,
                'country' => 'CM',
                'currency' => 'XAF',
                'fees' => true,
                'message' => "Subscription to {$plan->name}",
                'customer' => [
                    'email' => $request->user()->email,
                    'first_name' => $request->user()->name,
                ],
            ]);

            if ($response->isOperationSuccess()) {
                // In a real app, store transaction ref: $response->getData()->id
                return redirect()->back()->with('success', 'Payment initiated successfully. Please check your phone to confirm.');
            }

            return redirect()->back()->withErrors(['service' => 'Payment initiation failed.']);

        } catch (\Exception $e) {
            Log::error('Payment failed: '.$e->getMessage());

            return redirect()->back()->withErrors(['service' => 'Payment failed: '.$e->getMessage()]);
        }
    }
}
