<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterWelcome;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $existing = NewsletterSubscriber::where('email', $validated['email'])->first();

        if ($existing) {
            if ($existing->isActive()) {
                return response()->json([
                    'success' => true,
                    'message' => 'You\'re already subscribed! Thank you.',
                    'already_subscribed' => true,
                ]);
            }

            // Re-subscribe
            $existing->update([
                'status' => 'active',
                'name' => $validated['name'] ?? $existing->name,
                'unsubscribed_at' => null,
                'subscribed_at' => now(),
                'ip_address' => $request->ip(),
            ]);

            Mail::to($existing->email)->send(new NewsletterWelcome($existing));

            return response()->json([
                'success' => true,
                'message' => 'Welcome back! You\'ve been re-subscribed.',
            ]);
        }

        $subscriber = NewsletterSubscriber::create([
            'email' => $validated['email'],
            'name' => $validated['name'] ?? null,
            'source' => 'popup',
            'ip_address' => $request->ip(),
        ]);

        Mail::to($subscriber->email)->send(new NewsletterWelcome($subscriber));

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed! Check your email for confirmation.',
        ]);
    }

    public function unsubscribe(string $token): Response
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        if ($subscriber->isActive()) {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        }

        return Inertia::render('Newsletter/Unsubscribed', [
            'email' => $subscriber->email,
        ]);
    }

    public function resubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:newsletter_subscribers,email'],
        ]);

        $subscriber = NewsletterSubscriber::where('email', $validated['email'])->firstOrFail();

        $subscriber->update([
            'status' => 'active',
            'unsubscribed_at' => null,
            'subscribed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You\'ve been re-subscribed successfully!',
        ]);
    }

    public function checkSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $exists = NewsletterSubscriber::where('email', $validated['email'])
            ->where('status', 'active')
            ->exists();

        return response()->json([
            'subscribed' => $exists,
        ]);
    }
}
