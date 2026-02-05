<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'CVverse',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Site name',
            ],
            [
                'key' => 'site_description',
                'value' => 'Build professional CVs with AI',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Site description',
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Your Career, Your Story',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Site tagline',
            ],

            // Contact Settings
            [
                'key' => 'support_email',
                'value' => 'support@cvverse.com',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Support email address',
            ],
            [
                'key' => 'sales_email',
                'value' => 'sales@cvverse.com',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Sales email address',
            ],
            [
                'key' => 'press_email',
                'value' => 'press@cvverse.com',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Press email address',
            ],
            [
                'key' => 'partnerships_email',
                'value' => 'partners@cvverse.com',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Partnerships email address',
            ],
            [
                'key' => 'phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Contact phone number',
            ],
            [
                'key' => 'address',
                'value' => '123 Innovation Drive, Suite 400',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Office address',
            ],
            [
                'key' => 'city',
                'value' => 'San Francisco',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'City',
            ],
            [
                'key' => 'country',
                'value' => 'USA',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Country',
            ],
            [
                'key' => 'timezone',
                'value' => 'PST (UTC-8)',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Timezone',
            ],

            // Social Media Links
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/cvverse',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Twitter/X profile URL',
            ],
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/cvverse',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Facebook page URL',
            ],
            [
                'key' => 'social_linkedin',
                'value' => 'https://linkedin.com/company/cvverse',
                'type' => 'string',
                'group' => 'social',
                'description' => 'LinkedIn company URL',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/cvverse',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Instagram profile URL',
            ],
            [
                'key' => 'social_youtube',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'YouTube channel URL',
            ],
            [
                'key' => 'social_github',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'GitHub profile URL',
            ],

            // Email Settings
            [
                'key' => 'email_welcome',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send welcome email to new users',
            ],
            [
                'key' => 'email_weekly_digest',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send weekly activity digest',
            ],
            [
                'key' => 'email_marketing',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send marketing emails',
            ],

            // Stats
            [
                'key' => 'stats_users_count',
                'value' => '500,000+',
                'type' => 'string',
                'group' => 'stats',
                'description' => 'Number of users displayed on landing page',
            ],
            [
                'key' => 'stats_cvs_created',
                'value' => '500K+',
                'type' => 'string',
                'group' => 'stats',
                'description' => 'Number of CVs created',
            ],
            [
                'key' => 'stats_success_rate',
                'value' => '95%',
                'type' => 'string',
                'group' => 'stats',
                'description' => 'User success rate',
            ],
            [
                'key' => 'stats_countries',
                'value' => '150+',
                'type' => 'string',
                'group' => 'stats',
                'description' => 'Number of countries',
            ],
            [
                'key' => 'stats_user_rating',
                'value' => '4.9/5',
                'type' => 'string',
                'group' => 'stats',
                'description' => 'Average user rating',
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
