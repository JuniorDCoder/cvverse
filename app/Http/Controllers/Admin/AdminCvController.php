<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminCvController extends Controller
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Display a listing of CVs.
     */
    public function index(Request $request): Response
    {
        $query = Cv::with('user');

        // Search
        if ($request->has('search') && $request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by user
        if ($request->has('user_id') && $request->get('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filter by template
        if ($request->has('template') && $request->get('template')) {
            $query->where('template', $request->get('template'));
        }

        $cvs = $query->latest()
            ->paginate(20)
            ->through(fn ($cv) => [
                'id' => $cv->id,
                'name' => $cv->name,
                'template' => $cv->template,
                'is_primary' => $cv->is_primary,
                'user' => [
                    'id' => $cv->user->id,
                    'name' => $cv->user->name,
                    'email' => $cv->user->email,
                ],
                'created_at' => $cv->created_at->format('M d, Y'),
                'updated_at' => $cv->updated_at->format('M d, Y'),
            ]);

        $stats = [
            'total_cvs' => Cv::count(),
            'primary_cvs' => Cv::where('is_primary', true)->count(),
            'cvs_today' => Cv::whereDate('created_at', today())->count(),
        ];

        return Inertia::render('admin/Cvs', [
            'cvs' => $cvs,
            'filters' => $request->only(['search', 'user_id', 'template']),
            'stats' => $stats,
            'templates' => CvTemplate::where('is_active', true)->pluck('slug'),
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new CV.
     */
    public function create(Request $request): Response
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $templates = CvTemplate::where('is_active', true)->get(['id', 'name', 'slug', 'description']);

        // Pre-select user if provided
        $selectedUser = null;
        $userJobApplications = [];
        if ($request->has('user_id')) {
            $selectedUser = User::find($request->get('user_id'));
            if ($selectedUser) {
                $userJobApplications = $selectedUser->jobApplications()
                    ->select('id', 'title', 'company_id')
                    ->with('company:id,name')
                    ->get();
            }
        }

        return Inertia::render('admin/CvCreate', [
            'users' => $users,
            'templates' => $templates,
            'selectedUser' => $selectedUser ? [
                'id' => $selectedUser->id,
                'name' => $selectedUser->name,
                'email' => $selectedUser->email,
            ] : null,
            'jobApplications' => $userJobApplications,
        ]);
    }

    /**
     * Get job applications for a specific user (AJAX).
     */
    public function getUserJobApplications(User $user): JsonResponse
    {
        $applications = $user->jobApplications()
            ->select('id', 'title', 'company_id', 'description', 'requirements', 'skills')
            ->with('company:id,name')
            ->get()
            ->map(fn ($app) => [
                'id' => $app->id,
                'title' => $app->title,
                'company_name' => $app->company?->name ?? 'Unknown Company',
                'description' => $app->description,
                'requirements' => $app->requirements,
                'skills' => $app->skills,
            ]);

        return response()->json([
            'applications' => $applications,
        ]);
    }

    /**
     * Store a newly created CV.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'template' => 'nullable|string|exists:cv_templates,slug',
            'is_primary' => 'nullable|boolean',
            'personal_info' => 'nullable|array',
            'experience' => 'nullable|array',
            'education' => 'nullable|array',
            'skills' => 'nullable|array',
            'projects' => 'nullable|array',
            'certifications' => 'nullable|array',
            'languages' => 'nullable|array',
            'summary' => 'nullable|string',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // If this is set as primary, unset other primary CVs for this user
        if ($validated['is_primary'] ?? false) {
            $user->cvs()->update(['is_primary' => false]);
        }

        $cv = $user->cvs()->create($validated);

        // Create initial version
        $cv->createVersion('Created by admin');

        return redirect()->route('admin.cvs.show', $cv)
            ->with('success', 'CV created successfully!');
    }

    /**
     * Generate CV using AI for a user.
     */
    public function generate(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'template' => 'required|string|exists:cv_templates,slug',
            'job_application_id' => 'required|exists:job_applications,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $job = JobApplication::findOrFail($validated['job_application_id']);

        // Verify job belongs to user
        if ($job->user_id !== $user->id) {
            return back()->withErrors(['job_application_id' => 'This job application does not belong to the selected user.']);
        }

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'job_title' => $user->job_title,
            'industry' => $user->industry,
            'experience_level' => $user->experience_level,
            'bio' => $user->bio,
            'interests' => $user->interests,
        ];

        $jobData = [
            'title' => $job->title,
            'company' => $job->company?->name,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'skills' => $job->skills,
        ];

        $generatedCv = $this->geminiService->generateCvFromJob($userData, $jobData);

        if (! $generatedCv) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate CV. Please try again.',
                ], 500);
            }

            return back()->withErrors(['job_application_id' => 'Failed to generate CV. Please try again.']);
        }

        // Create the CV
        $cv = $user->cvs()->create([
            'name' => $validated['name'],
            'template' => $validated['template'],
            'is_primary' => false,
            'personal_info' => $generatedCv['personal_info'] ?? [],
            'experience' => $generatedCv['experience'] ?? [],
            'education' => $generatedCv['education'] ?? [],
            'skills' => $generatedCv['skills'] ?? [],
            'languages' => $generatedCv['languages'] ?? [],
            'summary' => $generatedCv['summary'] ?? null,
        ]);

        // Create initial version
        $cv->createVersion('AI Generated from Job Application: '.$job->title);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'CV generated successfully!',
                'cv' => $cv,
                'redirect' => route('admin.cvs.show', $cv),
            ]);
        }

        return redirect()->route('admin.cvs.show', $cv)
            ->with('success', 'CV generated successfully using AI!');
    }

    /**
     * Display the specified CV.
     */
    public function show(Cv $cv): Response
    {
        $cv->load('user', 'versions');

        return Inertia::render('admin/CvShow', [
            'cv' => [
                'id' => $cv->id,
                'name' => $cv->name,
                'template' => $cv->template,
                'is_primary' => $cv->is_primary,
                'personal_info' => $cv->personal_info,
                'experience' => $cv->experience,
                'education' => $cv->education,
                'skills' => $cv->skills,
                'projects' => $cv->projects,
                'certifications' => $cv->certifications,
                'languages' => $cv->languages,
                'summary' => $cv->summary,
                'created_at' => $cv->created_at->format('M d, Y H:i'),
                'updated_at' => $cv->updated_at->format('M d, Y H:i'),
            ],
            'user' => [
                'id' => $cv->user->id,
                'name' => $cv->user->name,
                'email' => $cv->user->email,
            ],
            'versions' => $cv->versions->map(fn ($v) => [
                'id' => $v->id,
                'notes' => $v->notes,
                'created_at' => $v->created_at->format('M d, Y H:i'),
            ]),
            'templates' => CvTemplate::where('is_active', true)->pluck('slug'),
        ]);
    }

    /**
     * Show the form for editing the specified CV.
     */
    public function edit(Cv $cv): Response
    {
        $cv->load('user');

        return Inertia::render('admin/CvEdit', [
            'cv' => [
                'id' => $cv->id,
                'name' => $cv->name,
                'template' => $cv->template,
                'is_primary' => $cv->is_primary,
                'personal_info' => $cv->personal_info ?? [],
                'experience' => $cv->experience ?? [],
                'education' => $cv->education ?? [],
                'skills' => $cv->skills ?? [],
                'projects' => $cv->projects ?? [],
                'certifications' => $cv->certifications ?? [],
                'languages' => $cv->languages ?? [],
                'summary' => $cv->summary,
            ],
            'user' => [
                'id' => $cv->user->id,
                'name' => $cv->user->name,
                'email' => $cv->user->email,
            ],
            'templates' => CvTemplate::where('is_active', true)->get(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Update the specified CV.
     */
    public function update(Request $request, Cv $cv): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template' => 'nullable|string|exists:cv_templates,slug',
            'is_primary' => 'nullable|boolean',
            'personal_info' => 'nullable|array',
            'experience' => 'nullable|array',
            'education' => 'nullable|array',
            'skills' => 'nullable|array',
            'projects' => 'nullable|array',
            'certifications' => 'nullable|array',
            'languages' => 'nullable|array',
            'summary' => 'nullable|string',
        ]);

        // If setting as primary, unset other primary CVs for this user
        if (($validated['is_primary'] ?? false) && ! $cv->is_primary) {
            $cv->user->cvs()->where('id', '!=', $cv->id)->update(['is_primary' => false]);
        }

        $cv->update($validated);

        // Create version for the update
        $cv->createVersion('Updated by admin');

        return redirect()->route('admin.cvs.show', $cv)
            ->with('success', 'CV updated successfully!');
    }

    /**
     * Remove the specified CV.
     */
    public function destroy(Cv $cv): RedirectResponse
    {
        $userName = $cv->user->name;
        $cvName = $cv->name;

        // Delete versions first
        $cv->versions()->delete();
        $cv->delete();

        return redirect()->route('admin.cvs.index')
            ->with('success', "CV '{$cvName}' for {$userName} deleted successfully!");
    }

    /**
     * Download CV as PDF.
     */
    public function downloadPdf(Cv $cv): HttpResponse
    {
        $cv->load('user');

        $pdf = Pdf::loadView('exports.cv-pdf', [
            'cv' => $cv,
        ]);

        $filename = str_replace(' ', '_', $cv->name).'_'.$cv->user->name.'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Toggle primary status for a CV.
     */
    public function togglePrimary(Cv $cv): RedirectResponse
    {
        if (! $cv->is_primary) {
            // Unset other primary CVs for this user
            $cv->user->cvs()->where('id', '!=', $cv->id)->update(['is_primary' => false]);
        }

        $cv->update(['is_primary' => ! $cv->is_primary]);

        return redirect()->back()
            ->with('success', $cv->is_primary ? 'CV set as primary.' : 'CV removed from primary.');
    }

    /**
     * Duplicate a CV for the same or different user.
     */
    public function duplicate(Request $request, Cv $cv): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'nullable|string|max:255',
        ]);

        $targetUser = isset($validated['user_id'])
            ? User::findOrFail($validated['user_id'])
            : $cv->user;

        $newCv = $targetUser->cvs()->create([
            'name' => $validated['name'] ?? $cv->name.' (Copy)',
            'template' => $cv->template,
            'is_primary' => false,
            'personal_info' => $cv->personal_info,
            'experience' => $cv->experience,
            'education' => $cv->education,
            'skills' => $cv->skills,
            'projects' => $cv->projects,
            'certifications' => $cv->certifications,
            'languages' => $cv->languages,
            'summary' => $cv->summary,
        ]);

        $newCv->createVersion('Duplicated from CV #'.$cv->id);

        return redirect()->route('admin.cvs.show', $newCv)
            ->with('success', 'CV duplicated successfully!');
    }

    /**
     * Generate AI suggestions for improving a CV.
     */
    public function generateSuggestions(Cv $cv): JsonResponse
    {
        $cv->load('user');

        $suggestions = $this->geminiService->generateCvSuggestions(
            $cv->toArray(),
            [
                'job_title' => $cv->user->job_title,
                'industry' => $cv->user->industry,
                'experience_level' => $cv->user->experience_level,
            ]
        );

        return response()->json([
            'success' => (bool) $suggestions,
            'suggestions' => $suggestions,
        ]);
    }
}
