<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /**
     * Industry options for the onboarding form.
     */
    private const INDUSTRIES = [
        'technology' => 'Technology & Software',
        'finance' => 'Finance & Banking',
        'healthcare' => 'Healthcare & Medical',
        'education' => 'Education & Training',
        'marketing' => 'Marketing & Advertising',
        'design' => 'Design & Creative',
        'engineering' => 'Engineering',
        'sales' => 'Sales & Business Development',
        'consulting' => 'Consulting',
        'legal' => 'Legal',
        'hr' => 'Human Resources',
        'operations' => 'Operations & Logistics',
        'research' => 'Research & Science',
        'media' => 'Media & Entertainment',
        'nonprofit' => 'Non-profit & NGO',
        'government' => 'Government & Public Sector',
        'real_estate' => 'Real Estate',
        'hospitality' => 'Hospitality & Tourism',
        'retail' => 'Retail & E-commerce',
        'other' => 'Other',
    ];

    /**
     * Experience level options.
     */
    private const EXPERIENCE_LEVELS = [
        'student' => 'Student / Intern',
        'entry' => 'Entry Level (0-2 years)',
        'mid' => 'Mid Level (3-5 years)',
        'senior' => 'Senior Level (6-10 years)',
        'lead' => 'Lead / Manager (10+ years)',
        'executive' => 'Executive / Director',
    ];

    /**
     * Interest options for CV building.
     */
    private const INTERESTS = [
        'job_search' => 'Actively job searching',
        'career_change' => 'Changing careers',
        'freelance' => 'Freelancing opportunities',
        'networking' => 'Professional networking',
        'personal_branding' => 'Building personal brand',
        'skill_showcase' => 'Showcasing skills',
        'promotion' => 'Seeking promotion',
        'academic' => 'Academic applications',
    ];

    /**
     * Show the onboarding welcome step.
     */
    public function welcome(): Response
    {
        return Inertia::render('onboarding/Welcome');
    }

    /**
     * Show the profile step.
     */
    public function profile(): Response
    {
        return Inertia::render('onboarding/Profile', [
            'industries' => self::INDUSTRIES,
            'experienceLevels' => self::EXPERIENCE_LEVELS,
        ]);
    }

    /**
     * Store profile information.
     */
    public function storeProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_title' => ['required', 'string', 'max:100'],
            'industry' => ['required', 'string', Rule::in(array_keys(self::INDUSTRIES))],
            'experience_level' => ['required', 'string', Rule::in(array_keys(self::EXPERIENCE_LEVELS))],
            'phone' => ['nullable', 'string', 'max:20'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        $request->user()->update($validated);

        return redirect()->route('onboarding.interests');
    }

    /**
     * Show the interests step.
     */
    public function interests(): Response
    {
        return Inertia::render('onboarding/Interests', [
            'availableInterests' => self::INTERESTS,
        ]);
    }

    /**
     * Store interests and complete onboarding.
     */
    public function storeInterests(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'interests' => ['required', 'array', 'min:1'],
            'interests.*' => ['string', Rule::in(array_keys(self::INTERESTS))],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->update($validated);
        $request->user()->completeOnboarding();

        return redirect()->route('onboarding.complete');
    }

    /**
     * Show the completion step.
     */
    public function complete(): Response
    {
        return Inertia::render('onboarding/Complete');
    }

    /**
     * Finish onboarding and redirect to dashboard.
     */
    public function finish(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}
