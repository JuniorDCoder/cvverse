<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use MeSomb\Exception\InvalidClientRequestException;
use MeSomb\Exception\PermissionDeniedException;
use MeSomb\Exception\ServerException;
use MeSomb\Exception\ServiceNotFoundException;
use Throwable;

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
        $request->merge([
            'payer' => preg_replace('/\s+/', '', (string) $request->input('payer')),
        ]);

        $validated = $request->validate([
            'payer' => ['required', 'string', 'max:20', 'regex:/^\+?\d{8,15}$/'],
            'service' => ['required', 'string', 'in:MTN,ORANGE,AIRTEL'],
        ]);

        $traceId = (string) Str::uuid();
        $config = [
            'application_key' => config('services.mesomb.application_key'),
            'access_key' => config('services.mesomb.access_key'),
            'secret_key' => config('services.mesomb.secret_key'),
        ];

        $missingConfig = array_keys(array_filter($config, fn ($value) => blank($value)));
        if ($missingConfig !== []) {
            Log::error('Payment provider configuration missing', [
                'trace_id' => $traceId,
                'missing_keys' => $missingConfig,
                'user_id' => $request->user()?->id,
                'plan_id' => $plan->id,
            ]);

            return redirect()->back()->withErrors([
                'service' => "Payment service is temporarily unavailable. Please contact support with reference {$traceId}.",
            ]);
        }

        $payer = preg_replace('/\D+/', '', $validated['payer']);
        $amount = (float) $plan->price;
        $currency = strtoupper((string) $plan->currency);

        Log::info('Payment collection requested', [
            'trace_id' => $traceId,
            'user_id' => $request->user()?->id,
            'plan_id' => $plan->id,
            'plan_slug' => $plan->slug,
            'service' => $validated['service'],
            'amount' => $amount,
            'currency' => $currency,
            'payer_masked' => $this->maskPhoneNumber($payer),
        ]);

        try {
            $client = new \MeSomb\Operation\PaymentOperation(
                $config['application_key'],
                $config['access_key'],
                $config['secret_key']
            );

            $response = $client->makeCollect([
                'payer' => $payer,
                'amount' => $amount,
                'service' => $validated['service'],
                'country' => 'CM',
                'currency' => $currency ?: 'XAF',
                'fees' => true,
                'message' => "Subscription to {$plan->name}",
                'customer' => [
                    'email' => $request->user()->email,
                    'first_name' => $request->user()->name,
                    'phone' => $payer,
                ],
            ]);

            if ($response->isOperationSuccess()) {
                Log::info('Payment collection initiated', [
                    'trace_id' => $traceId,
                    'user_id' => $request->user()?->id,
                    'plan_id' => $plan->id,
                    'provider_status' => $response->status,
                    'provider_reference' => $response->reference,
                    'provider_message' => $response->message,
                    'transaction_reference' => $response->transaction->reference ?? null,
                    'transaction_status' => $response->transaction->status ?? null,
                ]);

                $message = trim((string) ($response->message ?? ''));
                $isTransactionSuccessful = method_exists($response, 'isTransactionSuccess')
                    ? $response->isTransactionSuccess()
                    : (($response->transaction->status ?? null) === 'SUCCESS');

                if ($isTransactionSuccessful) {
                    $this->activateSubscription($request->user(), $plan);

                    Log::info('Subscription activated after successful payment', [
                        'trace_id' => $traceId,
                        'user_id' => $request->user()?->id,
                        'plan_id' => $plan->id,
                        'subscription_ends_at' => $request->user()?->fresh()?->subscription_ends_at?->toIso8601String(),
                    ]);

                    return redirect()
                        ->route('subscription')
                        ->with('success', "Payment successful. Your {$plan->name} plan is now active.");
                }

                return redirect()->back()->with(
                    'success',
                    $message !== '' ? $message : 'Payment initiated successfully. Please check your phone to confirm.'
                );
            }

            $providerMessage = trim((string) ($response->message ?? ''));

            Log::warning('Payment collection rejected by provider', [
                'trace_id' => $traceId,
                'user_id' => $request->user()?->id,
                'plan_id' => $plan->id,
                'provider_status' => $response->status,
                'provider_reference' => $response->reference,
                'provider_message' => $providerMessage,
                'transaction_reference' => $response->transaction->reference ?? null,
                'transaction_status' => $response->transaction->status ?? null,
            ]);

            return redirect()->back()->withErrors([
                'service' => $this->formatUserError(
                    $providerMessage !== '' ? $providerMessage : 'Payment initiation failed.',
                    $traceId
                ),
            ]);
        } catch (InvalidClientRequestException|PermissionDeniedException|ServiceNotFoundException|ServerException $e) {
            Log::error('Payment provider request failed', [
                'trace_id' => $traceId,
                'exception_class' => $e::class,
                'exception_code' => $e->getCode(),
                'provider_message' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'plan_id' => $plan->id,
            ]);

            return redirect()->back()->withErrors([
                'service' => $this->formatUserError($e->getMessage(), $traceId),
            ]);
        } catch (Throwable $e) {
            Log::error('Unexpected payment failure', [
                'trace_id' => $traceId,
                'exception_class' => $e::class,
                'exception_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'plan_id' => $plan->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors([
                'service' => "Payment failed due to an unexpected error. Please try again. Reference {$traceId}.",
            ]);
        }
    }

    private function formatUserError(?string $message, string $traceId): string
    {
        $cleanMessage = $this->toFriendlyPaymentMessage($message);

        return "{$cleanMessage} (Ref: {$traceId})";
    }

    private function toFriendlyPaymentMessage(?string $message): string
    {
        $cleanMessage = trim((string) $message);

        if ($cleanMessage === '') {
            return 'Payment failed. Please try again.';
        }

        $lowerMessage = strtolower($cleanMessage);

        if (
            str_contains($lowerMessage, 'insufficient funds') ||
            str_contains($lowerMessage, 'sufficient funds')
        ) {
            return 'Your mobile money account has insufficient funds. Top up and try again, or use another number.';
        }

        if (
            str_contains($lowerMessage, 'too much time') ||
            str_contains($lowerMessage, 'time to validate') ||
            str_contains($lowerMessage, 'timed out') ||
            str_contains($lowerMessage, 'timeout') ||
            str_contains($lowerMessage, 'expired')
        ) {
            return 'Payment request expired before confirmation. Retry and confirm promptly on your phone.';
        }

        if (
            str_contains($lowerMessage, 'could not connect to mesomb') ||
            str_contains($lowerMessage, 'network error') ||
            str_contains($lowerMessage, 'could not resolve host') ||
            str_contains($lowerMessage, 'connection refused')
        ) {
            return 'Unable to reach the payment provider right now. Check your internet connection and try again in a few moments.';
        }

        if (str_contains($lowerMessage, 'invalid') && str_contains($lowerMessage, 'payer')) {
            return 'The phone number appears invalid for this provider. Please verify it and try again.';
        }

        // Remove verbose provider diagnostics that are not useful for end users.
        $cleanMessage = preg_replace('/\s*If this problem persists.*$/i', '', $cleanMessage) ?? $cleanMessage;
        $cleanMessage = preg_replace('/\s*\(Network error.*$/i', '', $cleanMessage) ?? $cleanMessage;
        $cleanMessage = trim($cleanMessage);

        return $cleanMessage !== '' ? $cleanMessage : 'Payment failed. Please try again.';
    }

    private function activateSubscription(User $user, PricingPlan $plan): void
    {
        $user->forceFill([
            'pricing_plan_id' => $plan->id,
            'subscription_status' => 'active',
            'subscription_ends_at' => $this->resolveSubscriptionEndDate($plan->interval, $user),
        ])->save();
    }

    private function resolveSubscriptionEndDate(?string $interval, User $user): ?\DateTimeInterface
    {
        $normalizedInterval = strtolower((string) $interval);

        if (in_array($normalizedInterval, ['one_time', 'lifetime'], true)) {
            return null;
        }

        $baseDate = $user->subscription_ends_at && $user->subscription_ends_at->isFuture()
            ? $user->subscription_ends_at->copy()
            : now();

        return match ($normalizedInterval) {
            'yearly', 'annual' => $baseDate->addYearNoOverflow(),
            'monthly' => $baseDate->addMonthNoOverflow(),
            default => $baseDate->addMonthNoOverflow(),
        };
    }

    private function maskPhoneNumber(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return '***';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return str_repeat('*', strlen($digits) - 4).substr($digits, -4);
    }
}
