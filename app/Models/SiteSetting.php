<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("site_setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'number' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value.
     */
    public static function setValue(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        $storedValue = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type, 'group' => $group]
        );

        Cache::forget("site_setting_{$key}");
    }

    /**
     * Set multiple settings at once.
     */
    public static function setMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $data) {
            $value = is_array($data) && isset($data['value']) ? $data['value'] : $data;
            $type = is_array($data) && isset($data['type']) ? $data['type'] : 'string';

            static::setValue($key, $value, $type, $group);
        }
    }

    /**
     * Get all settings by group.
     */
    public static function getByGroup(string $group): array
    {
        $settings = Cache::remember("site_settings_group_{$group}", 3600, function () use ($group) {
            return static::where('group', $group)->get();
        });

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = match ($setting->type) {
                'number' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        }

        return $result;
    }

    /**
     * Clear cache for a group.
     */
    public static function clearGroupCache(string $group): void
    {
        Cache::forget("site_settings_group_{$group}");

        // Also clear individual keys in this group
        $settings = static::where('group', $group)->get();
        foreach ($settings as $setting) {
            Cache::forget("site_setting_{$setting->key}");
        }
    }

    /**
     * Get all stats for the landing page.
     */
    public static function getStats(): array
    {
        return [
            'users_count' => static::getValue('stats_users_count', '500,000+'),
            'cvs_created' => static::getValue('stats_cvs_created', '500K+'),
            'success_rate' => static::getValue('stats_success_rate', '95%'),
            'countries' => static::getValue('stats_countries', '150+'),
            'user_rating' => static::getValue('stats_user_rating', '4.9/5'),
        ];
    }

    /**
     * Get general site settings.
     */
    public static function getGeneralSettings(): array
    {
        return [
            'site_name' => static::getValue('site_name', 'CVverse'),
            'site_description' => static::getValue('site_description', 'Build professional CVs with AI'),
            'site_tagline' => static::getValue('site_tagline', 'Your Career, Your Story'),
        ];
    }

    /**
     * Get contact information settings.
     */
    public static function getContactSettings(): array
    {
        return [
            'support_email' => static::getValue('support_email', 'support@cvverse.com'),
            'sales_email' => static::getValue('sales_email', 'sales@cvverse.com'),
            'press_email' => static::getValue('press_email', 'press@cvverse.com'),
            'partnerships_email' => static::getValue('partnerships_email', 'partners@cvverse.com'),
            'phone' => static::getValue('phone', '+1 (555) 123-4567'),
            'address' => static::getValue('address', '123 Innovation Drive, Suite 400'),
            'city' => static::getValue('city', 'San Francisco'),
            'country' => static::getValue('country', 'USA'),
            'timezone' => static::getValue('timezone', 'PST (UTC-8)'),
        ];
    }

    /**
     * Get social media links.
     */
    public static function getSocialLinks(): array
    {
        return [
            'twitter' => static::getValue('social_twitter', 'https://twitter.com/cvverse'),
            'facebook' => static::getValue('social_facebook', 'https://facebook.com/cvverse'),
            'linkedin' => static::getValue('social_linkedin', 'https://linkedin.com/company/cvverse'),
            'instagram' => static::getValue('social_instagram', 'https://instagram.com/cvverse'),
            'youtube' => static::getValue('social_youtube', ''),
            'github' => static::getValue('social_github', ''),
        ];
    }

    /**
     * Get email notification settings.
     */
    public static function getEmailSettings(): array
    {
        return [
            'welcome_emails' => static::getValue('email_welcome', true),
            'weekly_digest' => static::getValue('email_weekly_digest', true),
            'marketing_emails' => static::getValue('email_marketing', false),
        ];
    }

    /**
     * Get all settings for admin settings page.
     */
    public static function getAllForAdmin(): array
    {
        return [
            'general' => static::getGeneralSettings(),
            'contact' => static::getContactSettings(),
            'social' => static::getSocialLinks(),
            'email' => static::getEmailSettings(),
            'stats' => static::getStats(),
        ];
    }

    /**
     * Get contact page data (for frontend).
     */
    public static function getContactPageData(): array
    {
        $contact = static::getContactSettings();
        $social = static::getSocialLinks();
        $general = static::getGeneralSettings();

        return [
            'site_name' => $general['site_name'],
            'support_email' => $contact['support_email'],
            'phone' => $contact['phone'],
            'address' => $contact['address'],
            'city' => $contact['city'],
            'country' => $contact['country'],
            'timezone' => $contact['timezone'],
            'departments' => [
                [
                    'name' => 'Sales',
                    'email' => $contact['sales_email'],
                    'description' => 'For pricing and enterprise inquiries',
                ],
                [
                    'name' => 'Support',
                    'email' => $contact['support_email'],
                    'description' => 'For technical help and questions',
                ],
                [
                    'name' => 'Press',
                    'email' => $contact['press_email'],
                    'description' => 'For media and press inquiries',
                ],
                [
                    'name' => 'Partnerships',
                    'email' => $contact['partnerships_email'],
                    'description' => 'For collaboration opportunities',
                ],
            ],
            'social' => array_filter($social, fn ($url) => ! empty($url)),
        ];
    }
}
