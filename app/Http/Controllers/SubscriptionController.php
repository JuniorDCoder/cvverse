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
        // Mock subscription data for now since we don't have the full billing system connected yet
        // In a real app, this would come from the database/Cashier/Paddle/etc.
        $subscription = null; // $request->user()->subscription();

        // Check if user has a mock subscription in session or DB logic
        // For now, let's return null to show the "Upgrade" state, or mock it if needed.

        return Inertia::render('Subscription', [
            'subscription' => $subscription,
        ]);
    }
}
