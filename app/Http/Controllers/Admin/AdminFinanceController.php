<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use MeSomb\Exception\InvalidClientRequestException;
use MeSomb\Exception\PermissionDeniedException;
use MeSomb\Exception\ServerException;
use MeSomb\Exception\ServiceNotFoundException;
use MeSomb\Operation\PaymentOperation;
use Throwable;

class AdminFinanceController extends Controller
{
    public function index(): Response
    {
        $balances = $this->fetchBalances();

        $subscriptions = User::query()
            ->whereNotNull('pricing_plan_id')
            ->where('subscription_status', '!=', 'free')
            ->with('pricingPlan')
            ->latest('updated_at')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'plan' => $user->pricingPlan?->name,
                'plan_price' => $user->pricingPlan?->price,
                'plan_currency' => $user->pricingPlan?->currency,
                'plan_interval' => $user->pricingPlan?->interval,
                'status' => $user->subscription_status,
                'expires_at' => $user->subscription_ends_at?->toIso8601String(),
            ]);

        $withdrawals = Withdrawal::with('user')
            ->latest()
            ->get()
            ->map(fn (Withdrawal $w) => [
                'id' => $w->id,
                'amount' => $w->amount,
                'currency' => $w->currency,
                'receiver' => $w->receiver,
                'service' => $w->service,
                'status' => $w->status,
                'mesomb_reference' => $w->mesomb_reference,
                'message' => $w->message,
                'failure_reason' => $w->failure_reason,
                'user_name' => $w->user?->name,
                'created_at' => $w->created_at->toIso8601String(),
            ]);

        $stats = [
            'total_subscribers' => User::where('subscription_status', 'active')->count(),
            'total_revenue' => User::whereNotNull('pricing_plan_id')
                ->where('subscription_status', '!=', 'free')
                ->join('pricing_plans', 'users.pricing_plan_id', '=', 'pricing_plans.id')
                ->sum('pricing_plans.price'),
            'total_withdrawn' => Withdrawal::where('status', 'success')->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
        ];

        return Inertia::render('admin/Finance/Index', [
            'balances' => $balances,
            'subscriptions' => $subscriptions,
            'withdrawals' => $withdrawals,
            'stats' => $stats,
        ]);
    }

    public function withdraw(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'receiver' => ['required', 'string', 'max:20', 'regex:/^\+?\d{8,15}$/'],
            'service' => ['required', 'string', 'in:MTN,ORANGE,AIRTEL'],
        ]);

        $traceId = (string) Str::uuid();
        $config = $this->getMeSombConfig();

        $missingConfig = array_keys(array_filter($config, fn ($v) => blank($v)));
        if ($missingConfig !== []) {
            Log::error('MeSomb configuration missing for withdrawal', [
                'trace_id' => $traceId,
                'missing_keys' => $missingConfig,
            ]);

            return back()->withErrors(['message' => 'Payment provider is not configured. Please check MeSomb settings.']);
        }

        $receiver = preg_replace('/\D+/', '', $validated['receiver']);

        $withdrawal = Withdrawal::create([
            'user_id' => Auth::id(),
            'amount' => $validated['amount'],
            'currency' => 'XAF',
            'receiver' => $receiver,
            'service' => $validated['service'],
            'status' => 'pending',
            'message' => 'Processing withdrawal...',
        ]);

        Log::info('Withdrawal initiated', [
            'trace_id' => $traceId,
            'withdrawal_id' => $withdrawal->id,
            'amount' => $validated['amount'],
            'service' => $validated['service'],
            'admin_id' => Auth::id(),
        ]);

        try {
            $client = new PaymentOperation(
                $config['application_key'],
                $config['access_key'],
                $config['secret_key']
            );

            $response = $client->makeDeposit([
                'amount' => (float) $validated['amount'],
                'service' => $validated['service'],
                'receiver' => $receiver,
                'country' => 'CM',
                'currency' => 'XAF',
            ]);

            if ($response->isOperationSuccess()) {
                $withdrawal->update([
                    'status' => $response->isTransactionSuccess() ? 'success' : 'pending',
                    'mesomb_reference' => $response->reference,
                    'mesomb_transaction_id' => $response->transaction->pk ?? null,
                    'message' => $response->message ?? 'Withdrawal processed',
                ]);

                Log::info('Withdrawal deposit successful', [
                    'trace_id' => $traceId,
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $response->reference,
                ]);

                return back()->with('success', $response->message ?? 'Withdrawal processed successfully.');
            }

            $withdrawal->update([
                'status' => 'failed',
                'failure_reason' => $response->message ?? 'Deposit rejected by provider',
                'mesomb_reference' => $response->reference,
            ]);

            return back()->withErrors(['message' => $response->message ?? 'Withdrawal failed. Please try again.']);
        } catch (InvalidClientRequestException|PermissionDeniedException|ServiceNotFoundException|ServerException $e) {
            Log::error('Withdrawal MeSomb error', [
                'trace_id' => $traceId,
                'withdrawal_id' => $withdrawal->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $withdrawal->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);

            return back()->withErrors(['message' => 'Withdrawal failed: '.$e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Unexpected withdrawal failure', [
                'trace_id' => $traceId,
                'withdrawal_id' => $withdrawal->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $withdrawal->update([
                'status' => 'failed',
                'failure_reason' => 'Unexpected error occurred',
            ]);

            return back()->withErrors(['message' => "Withdrawal failed due to an unexpected error. Reference: {$traceId}"]);
        }
    }

    public function balance(): JsonResponse
    {
        $balances = $this->fetchBalances();

        return response()->json(['success' => true, 'balances' => $balances]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchBalances(): array
    {
        $config = $this->getMeSombConfig();

        if (array_filter($config, fn ($v) => blank($v)) !== []) {
            return [];
        }

        try {
            $client = new PaymentOperation(
                $config['application_key'],
                $config['access_key'],
                $config['secret_key']
            );

            $app = $client->getStatus();
            $balances = $app->balances ?? [];

            return array_map(fn ($b) => [
                'country' => $b->country,
                'currency' => $b->currency,
                'provider' => $b->provider,
                'service_name' => $b->service_name,
                'value' => $b->value,
            ], $balances);
        } catch (Throwable $e) {
            Log::warning('Failed to fetch MeSomb balance', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return array<string, string|null>
     */
    private function getMeSombConfig(): array
    {
        return [
            'application_key' => config('services.mesomb.application_key'),
            'access_key' => config('services.mesomb.access_key'),
            'secret_key' => config('services.mesomb.secret_key'),
        ];
    }
}
