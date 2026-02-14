<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Support\Arr;

class PlanService
{
    public function currentPlan(?User $user): ?PricingPlan
    {
        if (! $user) {
            return null;
        }

        if ($user->subscription_status !== 'active' || ! $user->pricing_plan_id) {
            return null;
        }

        if ($user->subscription_ends_at && $user->subscription_ends_at->isPast()) {
            return null;
        }

        $user->loadMissing('pricingPlan');

        return $user->pricingPlan;
    }

    public function currentPlanData(?User $user): array
    {
        if (! $user) {
            return [
                'id' => null,
                'name' => 'Free Plan',
                'slug' => 'free',
                'status' => 'guest',
                'is_free' => true,
                'subscription_ends_at' => null,
            ];
        }

        $plan = $this->currentPlan($user);

        if (! $plan) {
            return [
                'id' => null,
                'name' => 'Free Plan',
                'slug' => 'free',
                'status' => 'free',
                'is_free' => true,
                'subscription_ends_at' => null,
            ];
        }

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'status' => 'active',
            'is_free' => false,
            'subscription_ends_at' => $user->subscription_ends_at?->toIso8601String(),
        ];
    }

    public function checkLimit(User $user, string $limitKey, int $used, string $resourceLabel): array
    {
        $capabilities = $this->resolveCapabilities($user);
        $limit = $capabilities['limits'][$limitKey] ?? null;

        if ($limit === null) {
            return [
                'allowed' => true,
                'limit' => null,
                'used' => $used,
                'remaining' => null,
                'message' => null,
            ];
        }

        $remaining = max(0, (int) $limit - $used);
        $allowed = $used < (int) $limit;

        return [
            'allowed' => $allowed,
            'limit' => (int) $limit,
            'used' => $used,
            'remaining' => $remaining,
            'message' => $allowed
                ? null
                : "You've reached your {$resourceLabel} limit ({$limit}) on the current plan. Upgrade to continue.",
        ];
    }

    public function checkFeature(User $user, string $featureKey, string $featureLabel): array
    {
        $capabilities = $this->resolveCapabilities($user);
        $enabled = (bool) ($capabilities['features'][$featureKey] ?? false);

        return [
            'allowed' => $enabled,
            'message' => $enabled
                ? null
                : "{$featureLabel} is not available on your current plan. Upgrade to continue.",
        ];
    }

    public function dashboardSummary(User $user): array
    {
        $plan = $this->currentPlanData($user);
        $capabilities = $this->resolveCapabilities($user);
        $usage = $this->usage($user);

        $cvLimit = $this->checkLimit($user, 'cvs', $usage['cvs'], 'CVs');
        $coverLetterLimit = $this->checkLimit($user, 'cover_letters', $usage['cover_letters'], 'cover letters');
        $jobLimit = $this->checkLimit($user, 'job_applications', $usage['job_applications'], 'job applications');
        $aiMessageLimit = $this->checkLimit($user, 'ai_messages_per_day', $usage['ai_messages_today'], 'AI messages');

        $limitItems = [
            [
                'key' => 'cvs',
                'label' => 'CVs',
                'used' => $usage['cvs'],
                'limit' => $cvLimit['limit'],
                'remaining' => $cvLimit['remaining'],
                'reached' => ! $cvLimit['allowed'],
            ],
            [
                'key' => 'cover_letters',
                'label' => 'Cover Letters',
                'used' => $usage['cover_letters'],
                'limit' => $coverLetterLimit['limit'],
                'remaining' => $coverLetterLimit['remaining'],
                'reached' => ! $coverLetterLimit['allowed'],
            ],
            [
                'key' => 'job_applications',
                'label' => 'Job Applications',
                'used' => $usage['job_applications'],
                'limit' => $jobLimit['limit'],
                'remaining' => $jobLimit['remaining'],
                'reached' => ! $jobLimit['allowed'],
            ],
            [
                'key' => 'ai_messages_per_day',
                'label' => 'AI Messages Today',
                'used' => $usage['ai_messages_today'],
                'limit' => $aiMessageLimit['limit'],
                'remaining' => $aiMessageLimit['remaining'],
                'reached' => ! $aiMessageLimit['allowed'],
            ],
        ];

        $hasReachedLimit = collect($limitItems)->contains(fn (array $item) => $item['reached']);

        return [
            'current_plan' => $plan,
            'features' => $capabilities['features'] ?? [],
            'usage' => $limitItems,
            'should_upgrade' => $plan['is_free'] || $hasReachedLimit,
        ];
    }

    public function usage(User $user): array
    {
        return [
            'cvs' => $user->cvs()->count(),
            'cover_letters' => $user->coverLetters()->count(),
            'job_applications' => $user->jobApplications()->count(),
            'ai_messages_today' => ChatMessage::query()
                ->where('role', 'user')
                ->whereDate('created_at', now()->toDateString())
                ->whereHas('session', fn ($query) => $query->where('user_id', $user->id))
                ->count(),
        ];
    }

    private function resolveCapabilities(User $user): array
    {
        if ($user->isAdmin()) {
            return [
                'limits' => [
                    'cvs' => null,
                    'cover_letters' => null,
                    'job_applications' => null,
                    'ai_messages_per_day' => null,
                ],
                'features' => [
                    'ai_assistant' => true,
                    'ai_cv_generation' => true,
                    'ai_cover_letter_generation' => true,
                    'ai_job_analysis' => true,
                    'premium_templates' => true,
                ],
            ];
        }

        $plan = $this->currentPlan($user);

        if (! $plan) {
            return config('plans.free', []);
        }

        $capabilities = array_replace_recursive(
            config('plans.paid_default', []),
            config('plans.overrides.'.$plan->slug, [])
        );

        return array_replace_recursive($capabilities, $this->resolveDatabaseOverrides($plan));
    }

    private function resolveDatabaseOverrides(PricingPlan $plan): array
    {
        if (! is_array($plan->features) || ! Arr::isAssoc($plan->features)) {
            return [];
        }

        $overrides = [];

        if (isset($plan->features['limits']) && is_array($plan->features['limits'])) {
            $overrides['limits'] = $plan->features['limits'];
        }

        if (isset($plan->features['features']) && is_array($plan->features['features'])) {
            $overrides['features'] = $plan->features['features'];
        }

        return $overrides;
    }
}
