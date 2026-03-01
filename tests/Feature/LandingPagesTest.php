<?php

use App\Models\SiteSetting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('privacy policy page loads', function () {
    $this->get(route('privacy-policy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing/PrivacyPolicy')
            ->has('siteName')
        );
});

test('privacy policy page shows admin content when set', function () {
    SiteSetting::setValue('privacy_policy', 'Custom privacy policy content.', 'string', 'legal');

    $this->get(route('privacy-policy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing/PrivacyPolicy')
            ->where('content', 'Custom privacy policy content.')
        );
});

test('terms of service page loads', function () {
    $this->get(route('terms-of-service'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing/TermsOfService')
            ->has('siteName')
        );
});

test('terms of service page shows admin content when set', function () {
    SiteSetting::setValue('terms_of_service', 'Custom terms content.', 'string', 'legal');

    $this->get(route('terms-of-service'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing/TermsOfService')
            ->where('content', 'Custom terms content.')
        );
});

test('guides page loads', function () {
    $this->get(route('guides'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing/Guides')
        );
});

test('admin can update legal settings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.site-settings.legal'), [
            'privacy_policy' => 'Updated privacy policy.',
            'terms_of_service' => 'Updated terms of service.',
        ])
        ->assertRedirect();

    expect(SiteSetting::getValue('privacy_policy'))->toBe('Updated privacy policy.')
        ->and(SiteSetting::getValue('terms_of_service'))->toBe('Updated terms of service.');
});

test('non-admin cannot update legal settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('admin.site-settings.legal'), [
            'privacy_policy' => 'Hacked.',
            'terms_of_service' => 'Hacked.',
        ])
        ->assertForbidden();
});

test('legal settings validation requires both fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.site-settings.legal'), [])
        ->assertSessionHasErrors(['privacy_policy', 'terms_of_service']);
});

test('admin settings page includes legal settings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Settings')
            ->has('settings.legal')
        );
});

test('social links are shared with all pages', function () {
    SiteSetting::setValue('social_twitter', 'https://twitter.com/cvverse', 'string', 'social');
    SiteSetting::setValue('social_github', 'https://github.com/cvverse', 'string', 'social');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('socialLinks.twitter', 'https://twitter.com/cvverse')
            ->where('socialLinks.github', 'https://github.com/cvverse')
        );
});

test('empty social links are excluded from shared data', function () {
    SiteSetting::setValue('social_twitter', 'https://twitter.com/cvverse', 'string', 'social');
    SiteSetting::setValue('social_github', '', 'string', 'social');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('socialLinks.twitter', 'https://twitter.com/cvverse')
            ->missing('socialLinks.github')
        );
});
