<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free-plan',
                'name' => 'Free Plan',
                'price' => 0,
                'currency' => 'XAF',
                'interval' => 'monthly',
                'features' => [
                    '1 CV',
                    '2 Cover Letters',
                    '10 Job Applications',
                    'Basic AI Assistant',
                    'Community Support',
                ],
            ],
            [
                'slug' => 'starter-monthly-xaf',
                'name' => 'Starter',
                'price' => 1500,
                'currency' => 'XAF',
                'interval' => 'monthly',
                'features' => [
                    '5 CVs',
                    '10 Cover Letters',
                    '50 Job Applications',
                    'AI CV & Cover Letter Generation',
                    'PDF & DOCX Exports',
                ],
            ],
            [
                'slug' => 'pro-monthly-xaf',
                'name' => 'Pro',
                'price' => 3500,
                'currency' => 'XAF',
                'interval' => 'monthly',
                'features' => [
                    'Unlimited CVs',
                    'Unlimited Cover Letters',
                    'Unlimited Job Tracking',
                    'Advanced AI Suggestions & Analysis',
                    'Priority Support',
                ],
            ],
            [
                'slug' => 'pro-yearly-xaf',
                'name' => 'Pro Yearly',
                'price' => 35000,
                'currency' => 'XAF',
                'interval' => 'yearly',
                'features' => [
                    'Everything in Pro Monthly',
                    'Save ~2 months',
                    'Annual Billing',
                    'Priority Support',
                ],
            ],
            [
                'slug' => 'lifetime-xaf',
                'name' => 'Lifetime Access',
                'price' => 99000,
                'currency' => 'XAF',
                'interval' => 'one_time',
                'features' => [
                    'One-time payment',
                    'Lifetime premium access',
                    'All future premium features',
                    'VIP support',
                ],
            ],
            [
                'slug' => 'free-plan-usd',
                'name' => 'Free Plan',
                'price' => 0,
                'currency' => 'USD',
                'interval' => 'monthly',
                'features' => [
                    '1 CV',
                    '2 Cover Letters',
                    '10 Job Applications',
                    'Basic AI Assistant',
                    'Community Support',
                ],
            ],
            [
                'slug' => 'starter-monthly-usd',
                'name' => 'Starter',
                'price' => 4.99,
                'currency' => 'USD',
                'interval' => 'monthly',
                'features' => [
                    '5 CVs',
                    '10 Cover Letters',
                    '50 Job Applications',
                    'AI CV & Cover Letter Generation',
                    'PDF & DOCX Exports',
                ],
            ],
            [
                'slug' => 'pro-monthly-usd',
                'name' => 'Pro',
                'price' => 9.99,
                'currency' => 'USD',
                'interval' => 'monthly',
                'features' => [
                    'Unlimited CVs',
                    'Unlimited Cover Letters',
                    'Unlimited Job Tracking',
                    'Advanced AI Suggestions & Analysis',
                    'Priority Support',
                ],
            ],
            [
                'slug' => 'pro-yearly-usd',
                'name' => 'Pro Yearly',
                'price' => 89.99,
                'currency' => 'USD',
                'interval' => 'yearly',
                'features' => [
                    'Everything in Pro Monthly',
                    'Save ~25%',
                    'Annual Billing',
                    'Priority Support',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    'name' => $plan['name'],
                    'price' => $plan['price'],
                    'currency' => $plan['currency'],
                    'interval' => $plan['interval'],
                    'features' => $plan['features'],
                    'is_active' => true,
                ]
            );
        }

        PricingPlan::whereNotIn('slug', array_column($plans, 'slug'))
            ->update(['is_active' => false]);
    }
}
