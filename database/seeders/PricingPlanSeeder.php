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
        // Monthly Pro Plan
        PricingPlan::firstOrCreate(
            ['slug' => 'pro-monthly-xaf'],
            [
                'name' => 'Pro Plan',
                'price' => 5000,
                'currency' => 'XAF',
                'interval' => 'monthly',
                'features' => [
                    'Unlimited CVs',
                    'Unlimited Cover Letters',
                    'Advanced AI Suggestions',
                    'Priority Support',
                    'PDF & Word Exports',
                    'Link Sharing',
                ],
                'is_active' => true,
            ]
        );

        // Yearly Pro Plan
        PricingPlan::firstOrCreate(
            ['slug' => 'pro-yearly-xaf'],
            [
                'name' => 'Pro Yearly',
                'price' => 50000,
                'currency' => 'XAF',
                'interval' => 'yearly',
                'features' => [
                    'All Pro Features',
                    '2 Months Free',
                    'Priority Access to New Features',
                    'Dedicated Account Manager',
                ],
                'is_active' => true,
            ]
        );

        // Lifetime Plan
        PricingPlan::firstOrCreate(
            ['slug' => 'lifetime-xaf'],
            [
                'name' => 'Lifetime Access',
                'price' => 150000,
                'currency' => 'XAF',
                'interval' => 'one_time',
                'features' => [
                    'One-time Payment',
                    'Forever Access to All Features',
                    'Future Updates Included',
                    'VIP Support',
                    'Early Access Beta Features',
                ],
                'is_active' => true,
            ]
        );
    }
}
