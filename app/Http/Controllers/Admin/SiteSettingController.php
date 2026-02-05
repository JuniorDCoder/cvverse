<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_description' => ['required', 'string', 'max:500'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::setValue($key, $value ?? '', 'string', 'general');
        }

        SiteSetting::clearGroupCache('general');

        return back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Update contact settings.
     */
    public function updateContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'support_email' => ['required', 'email', 'max:255'],
            'sales_email' => ['nullable', 'email', 'max:255'],
            'press_email' => ['nullable', 'email', 'max:255'],
            'partnerships_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::setValue($key, $value ?? '', 'string', 'contact');
        }

        SiteSetting::clearGroupCache('contact');

        return back()->with('success', 'Contact settings updated successfully.');
    }

    /**
     * Update social media settings.
     */
    public function updateSocial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'twitter' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'url', 'max:255'],
        ]);

        // Map keys to storage format
        $socialMap = [
            'twitter' => 'social_twitter',
            'facebook' => 'social_facebook',
            'linkedin' => 'social_linkedin',
            'instagram' => 'social_instagram',
            'youtube' => 'social_youtube',
            'github' => 'social_github',
        ];

        foreach ($validated as $key => $value) {
            $storageKey = $socialMap[$key] ?? "social_{$key}";
            SiteSetting::setValue($storageKey, $value ?? '', 'string', 'social');
        }

        SiteSetting::clearGroupCache('social');

        return back()->with('success', 'Social media links updated successfully.');
    }

    /**
     * Update email settings.
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'welcome_emails' => ['required', 'boolean'],
            'weekly_digest' => ['required', 'boolean'],
            'marketing_emails' => ['required', 'boolean'],
        ]);

        // Map keys to storage format
        $emailMap = [
            'welcome_emails' => 'email_welcome',
            'weekly_digest' => 'email_weekly_digest',
            'marketing_emails' => 'email_marketing',
        ];

        foreach ($validated as $key => $value) {
            $storageKey = $emailMap[$key] ?? $key;
            SiteSetting::setValue($storageKey, $value, 'boolean', 'email');
        }

        SiteSetting::clearGroupCache('email');

        return back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Update stats settings.
     */
    public function updateStats(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'users_count' => ['required', 'string', 'max:50'],
            'cvs_created' => ['required', 'string', 'max:50'],
            'success_rate' => ['required', 'string', 'max:50'],
            'countries' => ['required', 'string', 'max:50'],
            'user_rating' => ['required', 'string', 'max:50'],
        ]);

        // Map keys to storage format
        $statsMap = [
            'users_count' => 'stats_users_count',
            'cvs_created' => 'stats_cvs_created',
            'success_rate' => 'stats_success_rate',
            'countries' => 'stats_countries',
            'user_rating' => 'stats_user_rating',
        ];

        foreach ($validated as $key => $value) {
            $storageKey = $statsMap[$key] ?? "stats_{$key}";
            SiteSetting::setValue($storageKey, $value, 'string', 'stats');
        }

        SiteSetting::clearGroupCache('stats');

        return back()->with('success', 'Stats updated successfully.');
    }
}
