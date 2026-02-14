<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoverLetter;
use App\Models\Cv;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCoverLetterController extends Controller
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Display a listing of cover letters.
     */
    public function index(Request $request): Response
    {
        $query = CoverLetter::with(['user', 'jobApplication.company']);

        // Search
        if ($request->has('search') && $request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
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

        // Filter by tone
        if ($request->has('tone') && $request->get('tone')) {
            $query->where('tone', $request->get('tone'));
        }

        $coverLetters = $query->latest()
            ->paginate(20)
            ->through(fn ($letter) => [
                'id' => $letter->id,
                'name' => $letter->name,
                'tone' => $letter->tone,
                'content_preview' => str()->limit(strip_tags($letter->content), 100),
                'user' => [
                    'id' => $letter->user->id,
                    'name' => $letter->user->name,
                    'email' => $letter->user->email,
                ],
                'job_application' => $letter->jobApplication ? [
                    'id' => $letter->jobApplication->id,
                    'title' => $letter->jobApplication->title,
                    'company_name' => $letter->jobApplication->company?->name,
                ] : null,
                'created_at' => $letter->created_at->format('M d, Y'),
                'updated_at' => $letter->updated_at->format('M d, Y'),
            ]);

        $stats = [
            'total_cover_letters' => CoverLetter::count(),
            'cover_letters_today' => CoverLetter::whereDate('created_at', today())->count(),
            'with_job_applications' => CoverLetter::whereNotNull('job_application_id')->count(),
        ];

        return Inertia::render('admin/CoverLetters', [
            'coverLetters' => $coverLetters,
            'filters' => $request->only(['search', 'user_id', 'tone']),
            'stats' => $stats,
            'tones' => CoverLetter::TONES,
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new cover letter.
     */
    public function create(Request $request): Response
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        // Pre-select user if provided
        $selectedUser = null;
        $userJobApplications = [];
        $userCvs = [];
        if ($request->has('user_id')) {
            $selectedUser = User::find($request->get('user_id'));
            if ($selectedUser) {
                $userJobApplications = $selectedUser->jobApplications()
                    ->select('id', 'title', 'company_id', 'description', 'requirements', 'skills')
                    ->with('company:id,name')
                    ->get();
                $userCvs = $selectedUser->cvs()->select('id', 'name')->get();
            }
        }

        return Inertia::render('admin/CoverLetterCreate', [
            'users' => $users,
            'tones' => CoverLetter::TONES,
            'selectedUser' => $selectedUser ? [
                'id' => $selectedUser->id,
                'name' => $selectedUser->name,
                'email' => $selectedUser->email,
            ] : null,
            'jobApplications' => $userJobApplications,
            'cvs' => $userCvs,
        ]);
    }

    /**
     * Get job applications and CVs for a specific user (AJAX).
     */
    public function getUserResources(User $user): JsonResponse
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
            ]);

        $cvs = $user->cvs()
            ->select('id', 'name')
            ->get();

        return response()->json([
            'applications' => $applications,
            'cvs' => $cvs,
        ]);
    }

    /**
     * Store a newly created cover letter.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'tone' => 'nullable|string|in:'.implode(',', array_keys(CoverLetter::TONES)),
            'job_application_id' => 'nullable|exists:job_applications,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $coverLetter = $user->coverLetters()->create([
            'name' => $validated['name'],
            'content' => $validated['content'],
            'tone' => $validated['tone'] ?? 'professional',
            'job_application_id' => $validated['job_application_id'] ?? null,
        ]);

        // Link to job application if provided
        if ($validated['job_application_id'] ?? null) {
            JobApplication::where('id', $validated['job_application_id'])
                ->update(['cover_letter_id' => $coverLetter->id]);
        }

        return redirect()->route('admin.cover-letters.show', $coverLetter)
            ->with('success', 'Cover letter created successfully!');
    }

    /**
     * Generate cover letter using AI for a user.
     */
    public function generate(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'job_application_id' => 'required|exists:job_applications,id',
            'cv_id' => 'required|exists:cvs,id',
            'tone' => 'nullable|string|in:'.implode(',', array_keys(CoverLetter::TONES)),
        ]);

        $user = User::findOrFail($validated['user_id']);
        $job = JobApplication::with('company')->findOrFail($validated['job_application_id']);
        $cv = Cv::findOrFail($validated['cv_id']);

        // Verify resources belong to user
        if ($job->user_id !== $user->id) {
            return back()->withErrors(['job_application_id' => 'This job application does not belong to the selected user.']);
        }
        if ($cv->user_id !== $user->id) {
            return back()->withErrors(['cv_id' => 'This CV does not belong to the selected user.']);
        }

        $cvData = [
            'personal_info' => $cv->personal_info,
            'experience' => $cv->experience,
            'education' => $cv->education,
            'skills' => $cv->skills,
            'summary' => $cv->summary,
        ];

        $jobData = [
            'title' => $job->title,
            'company' => $job->company?->name,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'skills' => $job->skills,
        ];

        $companyData = null;
        if ($job->company) {
            $companyData = [
                'name' => $job->company->name,
                'description' => $job->company->description ?? null,
                'industry' => $job->company->industry ?? null,
            ];
        }

        $content = $this->geminiService->generateCoverLetter(
            $cvData,
            $jobData,
            $companyData,
            $validated['tone'] ?? 'professional'
        );

        if (! $content) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate cover letter. Please try again.',
                ], 500);
            }

            return back()->withErrors(['job_application_id' => 'Failed to generate cover letter. Please try again.']);
        }

        // Create the cover letter
        $coverLetter = $user->coverLetters()->create([
            'name' => $validated['name'],
            'content' => $content,
            'tone' => $validated['tone'] ?? 'professional',
            'job_application_id' => $job->id,
        ]);

        // Link to job application
        $job->update(['cover_letter_id' => $coverLetter->id]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cover letter generated successfully!',
                'coverLetter' => $coverLetter,
                'redirect' => route('admin.cover-letters.show', $coverLetter),
            ]);
        }

        return redirect()->route('admin.cover-letters.show', $coverLetter)
            ->with('success', 'Cover letter generated successfully using AI!');
    }

    /**
     * Display the specified cover letter.
     */
    public function show(CoverLetter $coverLetter): Response
    {
        $coverLetter->load(['user', 'jobApplication.company']);

        return Inertia::render('admin/CoverLetterShow', [
            'coverLetter' => [
                'id' => $coverLetter->id,
                'name' => $coverLetter->name,
                'content' => $coverLetter->content,
                'tone' => $coverLetter->tone,
                'ai_improvements' => $coverLetter->ai_improvements,
                'job_application' => $coverLetter->jobApplication ? [
                    'id' => $coverLetter->jobApplication->id,
                    'title' => $coverLetter->jobApplication->title,
                    'company_name' => $coverLetter->jobApplication->company?->name,
                ] : null,
                'created_at' => $coverLetter->created_at->format('M d, Y H:i'),
                'updated_at' => $coverLetter->updated_at->format('M d, Y H:i'),
            ],
            'user' => [
                'id' => $coverLetter->user->id,
                'name' => $coverLetter->user->name,
                'email' => $coverLetter->user->email,
            ],
            'tones' => CoverLetter::TONES,
        ]);
    }

    /**
     * Show the form for editing the specified cover letter.
     */
    public function edit(CoverLetter $coverLetter): Response
    {
        $coverLetter->load(['user', 'jobApplication.company']);

        return Inertia::render('admin/CoverLetterEdit', [
            'coverLetter' => [
                'id' => $coverLetter->id,
                'name' => $coverLetter->name,
                'content' => $coverLetter->content,
                'tone' => $coverLetter->tone,
                'job_application_id' => $coverLetter->job_application_id,
            ],
            'user' => [
                'id' => $coverLetter->user->id,
                'name' => $coverLetter->user->name,
                'email' => $coverLetter->user->email,
            ],
            'tones' => CoverLetter::TONES,
            'jobApplications' => $coverLetter->user->jobApplications()
                ->with('company:id,name')
                ->select('id', 'title', 'company_id')
                ->get(),
        ]);
    }

    /**
     * Update the specified cover letter.
     */
    public function update(Request $request, CoverLetter $coverLetter): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'tone' => 'nullable|string|in:'.implode(',', array_keys(CoverLetter::TONES)),
            'job_application_id' => 'nullable|exists:job_applications,id',
        ]);

        $coverLetter->update($validated);

        return redirect()->route('admin.cover-letters.show', $coverLetter)
            ->with('success', 'Cover letter updated successfully!');
    }

    /**
     * Remove the specified cover letter.
     */
    public function destroy(CoverLetter $coverLetter): RedirectResponse
    {
        $userName = $coverLetter->user->name;
        $letterName = $coverLetter->name;

        $coverLetter->delete();

        return redirect()->route('admin.cover-letters.index')
            ->with('success', "Cover letter '{$letterName}' for {$userName} deleted successfully!");
    }

    /**
     * Duplicate a cover letter for the same or different user.
     */
    public function duplicate(Request $request, CoverLetter $coverLetter): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'nullable|string|max:255',
        ]);

        $targetUser = isset($validated['user_id'])
            ? User::findOrFail($validated['user_id'])
            : $coverLetter->user;

        $newCoverLetter = $targetUser->coverLetters()->create([
            'name' => $validated['name'] ?? $coverLetter->name.' (Copy)',
            'content' => $coverLetter->content,
            'tone' => $coverLetter->tone,
            'job_application_id' => null, // Don't copy job application link
        ]);

        return redirect()->route('admin.cover-letters.show', $newCoverLetter)
            ->with('success', 'Cover letter duplicated successfully!');
    }

    /**
     * Generate AI suggestions for improving a cover letter.
     */
    public function improve(CoverLetter $coverLetter, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feedback' => 'nullable|string',
        ]);

        $result = $this->geminiService->improveCoverLetter(
            $coverLetter->content,
            $validated['feedback'] ?? ''
        );

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate improvements.',
            ], 500);
        }

        $coverLetter->update(['ai_improvements' => $result]);

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }
}
