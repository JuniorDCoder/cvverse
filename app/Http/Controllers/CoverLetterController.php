<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\JobApplication;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CoverLetterController extends Controller
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $coverLetters = Auth::user()->coverLetters()
            ->with('jobApplication.company:id,name')
            ->latest()
            ->paginate(12);

        return Inertia::render('cover-letters/Index', [
            'coverLetters' => $coverLetters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $user = Auth::user();

        return Inertia::render('cover-letters/Create', [
            'tones' => CoverLetter::TONES,
            'jobApplications' => $user->jobApplications()
                ->with('company:id,name')
                ->select('id', 'title', 'company_id')
                ->get(),
            'cvs' => $user->cvs()->select('id', 'name')->get(),
            'selectedJobId' => $request->input('job_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'tone' => 'nullable|string|in:'.implode(',', array_keys(CoverLetter::TONES)),
            'job_application_id' => 'nullable|exists:job_applications,id',
        ]);

        $coverLetter = Auth::user()->coverLetters()->create([
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

        return redirect()->route('cover-letters.show', $coverLetter)
            ->with('success', 'Cover letter created successfully!');
    }

    /**
     * Generate a cover letter with AI
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_application_id' => 'required|exists:job_applications,id',
            'cv_id' => 'required|exists:cvs,id',
            'tone' => 'nullable|string|in:'.implode(',', array_keys(CoverLetter::TONES)),
        ]);

        $user = Auth::user();
        $job = $user->jobApplications()->with('company')->findOrFail($validated['job_application_id']);
        $cv = $user->cvs()->findOrFail($validated['cv_id']);

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
                'description' => $job->company->description,
                'industry' => $job->company->industry,
                'ai_insights' => $job->company->ai_insights,
            ];
        }

        $content = $this->geminiService->generateCoverLetter(
            $cvData,
            $jobData,
            $companyData,
            $validated['tone'] ?? 'professional'
        );

        if (! $content) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate cover letter. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'content' => $content,
        ]);
    }

    /**
     * Improve cover letter with AI
     */
    public function improve(CoverLetter $coverLetter, Request $request): JsonResponse
    {
        $this->authorize('update', $coverLetter);

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
                'message' => 'Failed to improve cover letter. Please try again.',
            ], 500);
        }

        $coverLetter->update(['ai_improvements' => $result]);

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoverLetter $coverLetter): Response
    {
        $this->authorize('view', $coverLetter);

        $coverLetter->load('jobApplication.company');

        return Inertia::render('cover-letters/Show', [
            'coverLetter' => $coverLetter,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoverLetter $coverLetter): Response
    {
        $this->authorize('update', $coverLetter);

        $coverLetter->load('jobApplication.company');
        $user = Auth::user();

        return Inertia::render('cover-letters/Edit', [
            'coverLetter' => $coverLetter,
            'tones' => CoverLetter::TONES,
            'jobApplications' => $user->jobApplications()
                ->with('company:id,name')
                ->select('id', 'title', 'company_id')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoverLetter $coverLetter): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $coverLetter);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'tone' => 'nullable|string|in:'.implode(',', array_keys(CoverLetter::TONES)),
        ]);

        $coverLetter->update($validated);

        return redirect()->route('cover-letters.show', $coverLetter)
            ->with('success', 'Cover letter updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoverLetter $coverLetter): RedirectResponse
    {
        $this->authorize('delete', $coverLetter);

        $coverLetter->delete();

        return redirect()->route('cover-letters.index')
            ->with('success', 'Cover letter deleted successfully!');
    }
}
