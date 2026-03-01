<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterBroadcast;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AdminNewsletterController extends Controller
{
    public function __construct(
        private GeminiService $geminiService
    ) {}

    public function index(Request $request): Response
    {
        $query = NewsletterSubscriber::query()
            ->when($request->input('search'), function ($q, $search): void {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->when($request->input('status'), function ($q, $status): void {
                $q->where('status', $status);
            })
            ->latest('subscribed_at');

        $subscribers = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::where('status', 'active')->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
            'this_month' => NewsletterSubscriber::where('subscribed_at', '>=', now()->startOfMonth())->count(),
        ];

        return Inertia::render('admin/Newsletter/Index', [
            'subscribers' => $subscribers,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber removed successfully.');
    }

    public function compose(): Response
    {
        $stats = [
            'active_subscribers' => NewsletterSubscriber::where('status', 'active')->count(),
            'total_users' => User::count(),
        ];

        return Inertia::render('admin/Newsletter/Compose', [
            'stats' => $stats,
        ]);
    }

    public function generateEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
            'tone' => ['nullable', 'string', 'in:professional,friendly,casual,urgent'],
        ]);

        $tone = $validated['tone'] ?? 'professional';
        $appName = config('app.name');

        $prompt = <<<PROMPT
        You are a professional email copywriter for "{$appName}", a CV/resume building platform.

        Write a newsletter email based on the following request:
        "{$validated['prompt']}"

        Tone: {$tone}

        Requirements:
        - Write a compelling subject line
        - Write the email body in clean Markdown format
        - Keep it concise, engaging, and actionable
        - Include a clear call-to-action
        - Do NOT include unsubscribe links (those are added automatically)
        - Do NOT include "Dear subscriber" or generic greetings — start with something engaging

        Return ONLY valid JSON in this exact format:
        {
            "subject": "The email subject line",
            "body": "The email body in Markdown format"
        }
        PROMPT;

        $response = $this->geminiService->generateContent($prompt);
        $text = $this->geminiService->extractText($response);

        if (! $text) {
            return response()->json(['success' => false, 'message' => 'AI generation failed. Please try again.'], 500);
        }

        // Clean markdown code blocks
        $cleaned = preg_replace('/```(?:json)?\s*/', '', $text);
        $cleaned = trim($cleaned ?? $text);

        $parsed = json_decode($cleaned, true);

        if (! $parsed || ! isset($parsed['subject'], $parsed['body'])) {
            return response()->json(['success' => false, 'message' => 'Failed to parse AI response. Please try again.'], 500);
        }

        return response()->json([
            'success' => true,
            'subject' => $parsed['subject'],
            'body' => $parsed['body'],
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'string', 'in:subscribers,users,all'],
        ]);

        $sent = 0;
        $recipients = collect();

        // Send to newsletter subscribers
        if (in_array($validated['audience'], ['subscribers', 'all'])) {
            $subscribers = NewsletterSubscriber::where('status', 'active')->get();
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)->queue(
                    new NewsletterBroadcast($validated['subject'], $validated['body'], $subscriber)
                );
                $sent++;
            }
            $recipients = $recipients->merge($subscribers->pluck('email'));
        }

        // Send to registered users (exclude already-sent subscribers)
        if (in_array($validated['audience'], ['users', 'all'])) {
            $users = User::query()
                ->whereNotIn('email', $recipients)
                ->get();

            foreach ($users as $user) {
                Mail::to($user->email)->queue(
                    new NewsletterBroadcast($validated['subject'], $validated['body'])
                );
                $sent++;
            }
        }

        return back()->with('success', "Newsletter sent to {$sent} recipients!");
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Name', 'Status', 'Source', 'Subscribed At']);

            NewsletterSubscriber::query()
                ->orderBy('subscribed_at', 'desc')
                ->chunk(500, function ($subscribers) use ($handle): void {
                    foreach ($subscribers as $subscriber) {
                        fputcsv($handle, [
                            $subscriber->email,
                            $subscriber->name ?? '',
                            $subscriber->status,
                            $subscriber->source,
                            $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, 'newsletter-subscribers-'.date('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
