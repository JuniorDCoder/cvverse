<?php

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\JobApplication;
use App\Models\PricingPlan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin analytics page returns real aggregated data', function () {
    $admin = User::factory()->admin()->create();

    $plan = PricingPlan::factory()->create([
        'slug' => 'pro-monthly-xaf-test',
        'price' => 3500,
        'currency' => 'XAF',
        'interval' => 'monthly',
        'is_active' => true,
    ]);

    $subscriber = User::factory()->create([
        'industry' => 'technology',
        'onboarding_completed' => true,
        'onboarding_completed_at' => now()->subDay(),
        'pricing_plan_id' => $plan->id,
        'subscription_status' => 'active',
        'subscription_ends_at' => now()->addMonth(),
        'interests' => ['job_search', 'skill_showcase'],
    ]);

    User::factory()->create([
        'industry' => 'technology',
        'onboarding_completed' => true,
        'onboarding_completed_at' => now()->subDays(2),
        'interests' => ['job_search'],
    ]);

    User::factory()->create([
        'industry' => 'finance',
        'onboarding_completed' => true,
        'onboarding_completed_at' => now()->subDays(3),
        'interests' => ['career_change'],
    ]);

    CvTemplate::factory()->create([
        'name' => 'Modern Professional',
        'slug' => 'modern-pro',
        'is_active' => true,
    ]);

    $cvA = Cv::create([
        'user_id' => $subscriber->id,
        'name' => 'CV A',
        'template' => 'modern-pro',
    ]);
    $cvB = Cv::create([
        'user_id' => $subscriber->id,
        'name' => 'CV B',
        'template' => 'modern-pro',
    ]);

    $cvA->forceFill(['created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)])->saveQuietly();
    $cvB->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->saveQuietly();

    $application = JobApplication::create([
        'user_id' => $subscriber->id,
        'title' => 'Backend Engineer',
        'status' => JobApplication::STATUS_APPLIED,
    ]);
    $application->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->saveQuietly();

    $this->actingAs($admin)
        ->get(route('admin.analytics', ['period' => 'year', 'currency' => 'XAF']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Analytics')
            ->where('filters.currency', 'XAF')
            ->where('overview.active_subscribers', 1)
            ->where('overview.estimated_mrr', 3500)
            ->where('topTemplates.0.slug', 'modern-pro')
            ->where('topIndustries.0.industry', 'technology')
        );
});

test('admin analytics day filter limits counts to current day', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'industry' => 'technology',
        'onboarding_completed' => true,
        'onboarding_completed_at' => now(),
    ]);

    $todayCv = Cv::create([
        'user_id' => $user->id,
        'name' => 'Today CV',
        'template' => 'modern',
    ]);
    $oldCv = Cv::create([
        'user_id' => $user->id,
        'name' => 'Old CV',
        'template' => 'modern',
    ]);

    $todayCv->forceFill(['created_at' => now(), 'updated_at' => now()])->saveQuietly();
    $oldCv->forceFill(['created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)])->saveQuietly();

    $this->actingAs($admin)
        ->get(route('admin.analytics', ['period' => 'day', 'currency' => 'ALL']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Analytics')
            ->where('filters.period', 'day')
            ->where('overview.total_cvs', 1)
        );
});
