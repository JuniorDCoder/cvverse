<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    /**
     * Display testimonials management page.
     */
    public function index(): Response
    {
        $testimonials = Testimonial::orderBy('sort_order')->get();
        $stats = SiteSetting::getStats();

        return Inertia::render('admin/Testimonials', [
            'testimonials' => $testimonials,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a new testimonial.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_role' => 'required|string|max:255',
            'author_company' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        Testimonial::create($validated);

        return back()->with('success', 'Testimonial created successfully.');
    }

    /**
     * Update a testimonial.
     */
    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_role' => 'required|string|max:255',
            'author_company' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $testimonial->update($validated);

        return back()->with('success', 'Testimonial updated successfully.');
    }

    /**
     * Delete a testimonial.
     */
    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return back()->with('success', 'Testimonial deleted successfully.');
    }

    /**
     * Toggle testimonial active status.
     */
    public function toggleStatus(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['is_active' => ! $testimonial->is_active]);

        $status = $testimonial->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Testimonial {$status} successfully.");
    }

    /**
     * Toggle testimonial featured status.
     */
    public function toggleFeatured(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['is_featured' => ! $testimonial->is_featured]);

        $status = $testimonial->is_featured ? 'featured' : 'unfeatured';

        return back()->with('success', "Testimonial {$status} successfully.");
    }

    /**
     * Update site stats.
     */
    public function updateStats(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'users_count' => 'nullable|string|max:50',
            'cvs_created' => 'nullable|string|max:50',
            'success_rate' => 'nullable|string|max:50',
            'countries' => 'nullable|string|max:50',
            'user_rating' => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                SiteSetting::setValue('stats_'.$key, $value, 'string', 'stats');
            }
        }

        return back()->with('success', 'Site statistics updated successfully.');
    }
}
