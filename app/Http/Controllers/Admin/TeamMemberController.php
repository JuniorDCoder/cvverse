<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TeamMemberController extends Controller
{
    /**
     * Display team members management page.
     */
    public function index(): Response
    {
        return Inertia::render('admin/TeamMembers', [
            'teamMembers' => TeamMember::orderBy('sort_order')->get(),
            'teamSectionVisible' => \App\Models\SiteSetting::getValue('team_section_visible', true),
        ]);
    }

    /**
     * Store a new team member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('team-members', 'public');
        }

        TeamMember::create($validated);

        return back()->with('success', 'Team member added successfully.');
    }

    /**
     * Update a team member.
     */
    public function update(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($teamMember->photo) {
                Storage::disk('public')->delete($teamMember->photo);
            }
            $validated['photo'] = $request->file('photo')->store('team-members', 'public');
        }

        $teamMember->update($validated);

        return back()->with('success', 'Team member updated successfully.');
    }

    /**
     * Delete a team member.
     */
    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        if ($teamMember->photo) {
            Storage::disk('public')->delete($teamMember->photo);
        }

        $teamMember->delete();

        return back()->with('success', 'Team member deleted successfully.');
    }

    /**
     * Toggle team member active status.
     */
    public function toggleStatus(TeamMember $teamMember): RedirectResponse
    {
        $teamMember->update(['is_active' => ! $teamMember->is_active]);

        $status = $teamMember->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Team member {$status} successfully.");
    }

    /**
     * Toggle team section visibility on About page.
     */
    public function toggleSectionVisibility(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'visible' => ['required', 'boolean'],
        ]);

        \App\Models\SiteSetting::setValue('team_section_visible', $validated['visible'], 'boolean', 'general');

        $status = $validated['visible'] ? 'visible' : 'hidden';

        return back()->with('success', "Team section is now {$status} on the About page.");
    }
}
