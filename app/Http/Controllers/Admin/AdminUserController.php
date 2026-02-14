<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->get('role') !== 'all') {
            $query->where('role', $request->get('role'));
        }

        // Filter by email verification status
        if ($request->has('status') && $request->get('status') !== 'all') {
            if ($request->get('status') === 'verified') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter by onboarding status
        if ($request->has('onboarding') && $request->get('onboarding') !== 'all') {
            $query->where('onboarding_completed', $request->get('onboarding') === 'completed');
        }

        $users = $query->latest()
            ->paginate(20)
            ->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'location' => $user->location,
                'job_title' => $user->job_title,
                'bio' => $user->bio,
                'onboarding_completed' => $user->onboarding_completed,
                'email_verified_at' => $user->email_verified_at?->format('M d, Y'),
                'cvs_count' => $user->cvs()->count(),
                'applications_count' => $user->jobApplications()->count(),
                'cover_letters_count' => $user->coverLetters()->count(),
                'chat_sessions_count' => $user->chatSessions()->count(),
                'created_at' => $user->created_at->format('M d, Y'),
            ]);

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('onboarding_completed', true)->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'pending_onboarding' => User::where('onboarding_completed', false)->count(),
        ];

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status', 'onboarding']),
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $password = Str::random(16);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => $validated['role'],
        ]);

        if ($request->boolean('send_invitation')) {
            // Send password reset email so user can set their own password
            Password::sendResetLink(['email' => $user->email]);
        }

        // Trigger registered event for email verification
        event(new Registered($user));

        return redirect()->back()->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $cvs = $user->cvs()->latest()->get()->map(fn ($cv) => [
            'id' => $cv->id,
            'name' => $cv->name,
            'template' => $cv->template,
            'is_primary' => $cv->is_primary,
            'created_at' => $cv->created_at->diffForHumans(),
        ]);

        $coverLetters = $user->coverLetters()->latest()->get()->map(fn ($letter) => [
            'id' => $letter->id,
            'name' => $letter->name,
            'tone' => $letter->tone,
            'created_at' => $letter->created_at->diffForHumans(),
        ]);

        $applications = $user->jobApplications()->with('company')->latest()->get()->map(fn ($app) => [
            'id' => $app->id,
            'company_name' => $app->company?->name ?? $app->company_name ?? 'Unknown Company',
            'job_title' => $app->title ?? $app->job_title ?? 'Unknown Position',
            'status' => $app->status,
            'applied_at' => $app->created_at->diffForHumans(),
        ]);

        $chatSessions = $user->chatSessions()->withCount('messages')->latest()->get()->map(fn ($session) => [
            'id' => $session->id,
            'title' => $session->title,
            'messages_count' => $session->messages_count,
            'created_at' => $session->created_at->diffForHumans(),
        ]);

        // Build activity timeline from various sources
        $activities = collect();

        // Add CV creations
        foreach ($user->cvs()->latest()->take(10)->get() as $cv) {
            $activities->push([
                'id' => 'cv_'.$cv->id,
                'type' => 'cv_created',
                'description' => "Created CV: {$cv->name}",
                'created_at' => $cv->created_at->diffForHumans(),
                'timestamp' => $cv->created_at,
            ]);
        }

        // Add job applications
        foreach ($user->jobApplications()->latest()->take(10)->get() as $app) {
            $activities->push([
                'id' => 'app_'.$app->id,
                'type' => 'application_created',
                'description' => "Applied to {$app->title} at {$app->company_name}",
                'created_at' => $app->created_at->diffForHumans(),
                'timestamp' => $app->created_at,
            ]);
        }

        // Add cover letters
        foreach ($user->coverLetters()->latest()->take(10)->get() as $letter) {
            $activities->push([
                'id' => 'letter_'.$letter->id,
                'type' => 'cover_letter_created',
                'description' => "Created cover letter: {$letter->name}",
                'created_at' => $letter->created_at->diffForHumans(),
                'timestamp' => $letter->created_at,
            ]);
        }

        // Add chat sessions
        foreach ($user->chatSessions()->latest()->take(10)->get() as $session) {
            $activities->push([
                'id' => 'chat_'.$session->id,
                'type' => 'chat_session_started',
                'description' => 'Started chat: '.($session->title ?? 'Untitled'),
                'created_at' => $session->created_at->diffForHumans(),
                'timestamp' => $session->created_at,
            ]);
        }

        // Sort activities by timestamp
        $activities = $activities->sortByDesc('timestamp')->take(20)->values()->toArray();

        $stats = [
            'total_cvs' => $user->cvs()->count(),
            'total_cover_letters' => $user->coverLetters()->count(),
            'total_applications' => $user->jobApplications()->count(),
            'total_chat_sessions' => $user->chatSessions()->count(),
        ];

        return Inertia::render('admin/UserDetails', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'location' => $user->location,
                'job_title' => $user->job_title,
                'industry' => $user->industry,
                'experience_level' => $user->experience_level,
                'bio' => $user->bio,
                'avatar' => $user->avatar,
                'onboarding_completed' => $user->onboarding_completed,
                'onboarding_completed_at' => $user->onboarding_completed_at?->format('M d, Y'),
                'email_verified_at' => $user->email_verified_at?->format('M d, Y'),
                'created_at' => $user->created_at->format('M d, Y'),
                'updated_at' => $user->updated_at->format('M d, Y'),
                'pricing_plan' => $user->pricingPlan ? [
                    'id' => $user->pricingPlan->id,
                    'name' => $user->pricingPlan->name,
                    'slug' => $user->pricingPlan->slug,
                ] : null,
                'subscription_status' => $user->subscription_status,
                'subscription_ends_at' => $user->subscription_ends_at?->format('M d, Y'),
            ],
            'cvs' => $cvs,
            'coverLetters' => $coverLetters,
            'applications' => $applications,
            'chatSessions' => $chatSessions,
            'activities' => $activities,
            'stats' => $stats,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update($validated);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Delete related data
        $user->cvs()->delete();
        $user->coverLetters()->delete();
        $user->jobApplications()->delete();
        $user->chatSessions()->each(function ($session) {
            $session->messages()->delete();
            $session->delete();
        });

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user role between admin and user.
     */
    public function toggleRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:user,admin'],
        ]);

        // Prevent removing your own admin role
        if ($user->id === auth()->id() && $validated['role'] === 'user') {
            return redirect()->back()->with('error', 'You cannot remove your own admin privileges.');
        }

        $user->update(['role' => $validated['role']]);

        return redirect()->back()->with('success', "User role changed to {$validated['role']}.");
    }

    /**
     * Resend verification email to user.
     */
    public function resendVerification(User $user): RedirectResponse
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->back()->with('error', 'This user has already verified their email.');
        }

        $user->sendEmailVerificationNotification();

        return redirect()->back()->with('success', 'Verification email sent.');
    }

    /**
     * Clear user activity data.
     */
    public function clearActivity(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:cvs,cover_letters,applications,chat_sessions,all'],
        ]);

        $type = $validated['type'];

        if ($type === 'cvs' || $type === 'all') {
            $user->cvs()->delete();
        }

        if ($type === 'cover_letters' || $type === 'all') {
            $user->coverLetters()->delete();
        }

        if ($type === 'applications' || $type === 'all') {
            $user->jobApplications()->delete();
        }

        if ($type === 'chat_sessions' || $type === 'all') {
            $user->chatSessions()->each(function ($session) {
                $session->messages()->delete();
                $session->delete();
            });
        }

        return redirect()->back()->with('success', 'User data cleared successfully.');
    }

    /**
     * Export users to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = User::query();

        // Apply same filters as index
        if ($request->has('search') && $request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->get('role')) {
            $query->where('role', $request->get('role'));
        }

        if ($request->has('status') && $request->get('status')) {
            if ($request->get('status') === 'verified') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($request->has('onboarding') && $request->get('onboarding')) {
            $query->where('onboarding_completed', $request->get('onboarding') === 'completed');
        }

        $users = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Role',
                'Phone',
                'Location',
                'Job Title',
                'Email Verified',
                'Onboarding Completed',
                'CVs Count',
                'Applications Count',
                'Cover Letters Count',
                'Created At',
            ]);

            // Data rows
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->phone ?? '',
                    $user->location ?? '',
                    $user->job_title ?? '',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->onboarding_completed ? 'Yes' : 'No',
                    $user->cvs()->count(),
                    $user->jobApplications()->count(),
                    $user->coverLetters()->count(),
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
